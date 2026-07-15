<?php

namespace App\Services;

use App\Models\PortalNotification;
use App\Models\PushSubscription;
use App\Models\User;
use App\Models\UserNotificationPreference;
use Illuminate\Support\Facades\Mail;
use Minishlink\WebPush\Subscription as WebPushSubscription;
use Minishlink\WebPush\WebPush;

class NotificationCenterService
{
    public static array $eventTemplates = [
        'new_enquiry' => ['title' => 'New Enquiry', 'message' => 'A public website enquiry has been submitted.'],
        'onboarding_submitted' => ['title' => 'Onboarding Submitted', 'message' => 'A participant has submitted their onboarding for review.'],
        'onboarding_resubmitted' => ['title' => 'Onboarding Resubmitted', 'message' => 'A participant has resubmitted onboarding documents for review.'],
        'document_resubmitted' => ['title' => 'Document Uploaded', 'message' => 'A participant uploaded an onboarding document.'],
        'portal_invitation' => ['title' => 'Portal Invitation', 'message' => 'You have been invited to the portal.'],
        'form_assigned' => ['title' => 'Form Assigned for Signing', 'message' => 'A form has been assigned to you for signing; reminders will be sent if it is not completed on time.'],
        'pre_approval_submitted' => ['title' => 'Pre-Approval Submitted', 'message' => 'A pre-approval request has been submitted.'],
        'pre_approval_approved' => ['title' => 'Pre-Approval Approved', 'message' => 'Your pre-approval request has been approved.'],
        'pre_approval_rejected' => ['title' => 'Pre-Approval Declined', 'message' => 'Your pre-approval request has been declined.'],
        'invoice_submitted' => ['title' => 'Invoice Submitted', 'message' => 'An invoice has been submitted for review.'],
        'invoice_needs_more_information' => ['title' => 'Invoice Requires More Information', 'message' => 'Additional information is required to process the invoice.'],
        'invoice_approved' => ['title' => 'Invoice Approved', 'message' => 'Your invoice was approved.'],
        'invoice_rejected' => ['title' => 'Invoice Rejected', 'message' => 'Your invoice was not approved and requires attention.'],
        'incident_created' => ['title' => 'Incident Submitted', 'message' => 'An incident report has been submitted and will be reviewed by care, quality, and management teams.'],
        'incident_submitted' => ['title' => 'Incident Submitted', 'message' => 'An incident report has been submitted and will be reviewed by care, quality, and management teams.'],
        'incident_escalated' => ['title' => 'Incident Escalated', 'message' => 'An incident has been escalated.'],
        'care_note_overdue' => ['title' => 'Care Note Overdue', 'message' => 'A care note is overdue and requires attention.'],
        'compliance_expiring' => ['title' => 'Compliance Expiry', 'message' => 'A compliance document is expiring soon.'],
        'budget_low' => ['title' => 'Budget Alert', 'message' => 'The participant budget is low or at risk of overspending.'],
        'monthly_care_management_due' => ['title' => 'Monthly Care Management Due', 'message' => 'Monthly care management is due and requires attention.'],
        'review_due' => ['title' => 'Review Due', 'message' => 'A review is due soon.'],
    ];

    public static function send(string $event, int $recipientUserId, array $data = [], ?array $channels = null): PortalNotification
    {
        $user = User::find($recipientUserId);

        $pref = UserNotificationPreference::firstOrCreate(
            ['user_id' => $recipientUserId],
            ['channel_email' => true, 'channel_in_app' => true, 'channel_push' => true, 'channel_sms' => false]
        );

        $template = static::$eventTemplates[$event] ?? ['title' => ucfirst(str_replace('_', ' ', $event)), 'message' => $data['message'] ?? 'You have a new notification.'];

        $data = static::normalizeData($event, $data, $template);

        // create in-app notification
        $notif = PortalNotification::create([
            'user_id' => $recipientUserId,
            'recipient_id' => $recipientUserId,
            'participant_id' => $data['participant_id'] ?? null,
            'worker_id' => $data['worker_id'] ?? null,
            'type' => $event,
            'title' => $data['title'],
            'message' => $data['message'],
            'data' => $data,
            'channel' => 'in_app',
        ]);

        // send email if allowed
        $sendEmail = $channels ? in_array('email', $channels, true) : $pref->channel_email;
        if ($sendEmail && $user && $user->email) {
            try {
                $emailBody = $data['message'] ?? '';
                if (! empty($data['url'])) {
                    $emailBody .= "\n\n".$data['url'];
                }

                $usesHtml = preg_match('/<\/?[a-z][\s\S]*>/i', $emailBody);
                Mail::to($user->email)->send(new \App\Mail\StyledEmail(
                    $data['title'] ?? config('app.name'),
                    $data['title'] ?? config('app.name'),
                    '',
                    $usesHtml ? '' : $emailBody,
                    $usesHtml ? $emailBody : null,
                    [],
                    $data['url'] ?? null,
                    'View details',
                    null,
                    null,
                    $event ?? null,
                    null,
                    null
                ));
            } catch (\Exception $e) {
                // ignore mail failures for now
            }
        }

        // SMS placeholder: implement provider integration as needed
        $sendSms = $channels ? in_array('sms', $channels, true) : $pref->channel_sms;
        if ($sendSms && ! empty($user->phone)) {
            // TODO: integrate SMS provider
        }

        $sendPush = $channels ? in_array('push', $channels, true) : $pref->channel_push;
        if ($sendPush) {
            self::sendPushNotification($recipientUserId, $notif);
        }

        return $notif;
    }

    private static function sendPushNotification(int $userId, PortalNotification $notification): void
    {
        $publicKey = is_callable(config('push.vapid.public_key')) ? call_user_func(config('push.vapid.public_key')) : config('push.vapid.public_key');
        $privateKey = is_callable(config('push.vapid.private_key')) ? call_user_func(config('push.vapid.private_key')) : config('push.vapid.private_key');

        if (empty($publicKey) || empty($privateKey)) {
            return;
        }

        $subscriptions = PushSubscription::where('user_id', $userId)->get();
        if ($subscriptions->isEmpty()) {
            return;
        }

        $subject = is_callable(config('push.vapid.subject')) ? call_user_func(config('push.vapid.subject')) : config('push.vapid.subject');
        $webPush = new WebPush([
            'VAPID' => [
                'subject' => $subject,
                'publicKey' => $publicKey,
                'privateKey' => $privateKey,
            ],
        ]);

        $payload = json_encode([
            'title' => $notification->title ?? config('app.name'),
            'body' => $notification->message ?? '',
            'icon' => asset('icons/icon-192.png'),
            'badge' => asset('icons/icon-192.png'),
            'data' => [
                'url' => $notification->data['url'] ?? route('portal.notifications'),
            ],
        ]);

        foreach ($subscriptions as $subscription) {
            try {
                $webPush->queueNotification(
                    WebPushSubscription::create([
                        'endpoint' => $subscription->endpoint,
                        'publicKey' => $subscription->public_key,
                        'authToken' => $subscription->auth_token,
                        'contentEncoding' => $subscription->content_encoding,
                    ]),
                    $payload
                );
            } catch (\Throwable $e) {
                // ignore invalid subscriptions but continue delivery
            }
        }

        foreach ($webPush->flush() as $report) {
            if (! $report->isSuccess()) {
                // optionally log failed push subscriptions
            }
        }
    }

    private static function normalizeData(string $event, array $data, array $template): array
    {
        if (isset($data['data']) && is_array($data['data'])) {
            $data = array_merge($data['data'], $data);
            unset($data['data']);
        }

        $data['title'] = $data['title'] ?? $template['title'];
        $data['message'] = $data['message'] ?? $template['message'];

        if (! array_key_exists('url', $data) || empty($data['url'])) {
            if ($event === 'new_enquiry' && isset($data['enquiry_id'])) {
                $data['url'] = route('portal.admin.enquiries.show', $data['enquiry_id']);
                $data['reference_id'] = $data['reference_id'] ?? $data['enquiry_id'];
            }

            if (in_array($event, ['onboarding_submitted', 'onboarding_resubmitted'], true) && isset($data['participant_id'])) {
                if (isset($data['submission_id'])) {
                    $data['url'] = route('admin.onboarding.show', $data['submission_id']);
                    $data['reference_id'] = $data['reference_id'] ?? $data['submission_id'];
                } else {
                    $data['url'] = route('portal.admin.participants.show', $data['participant_id']);
                    $data['reference_id'] = $data['reference_id'] ?? $data['participant_id'];
                }
            }

            if ($event === 'document_resubmitted') {
                if (isset($data['document_id'])) {
                    $data['url'] = route('portal.admin.documents.show', $data['document_id']);
                    $data['reference_id'] = $data['reference_id'] ?? $data['document_id'];
                } elseif (isset($data['participant_id'])) {
                    $data['url'] = route('portal.admin.participants.show', $data['participant_id']);
                    $data['reference_id'] = $data['reference_id'] ?? $data['participant_id'];
                }
            }

            if (empty($data['url']) && isset($data['participant_id'])) {
                $data['url'] = route('portal.admin.participants.show', $data['participant_id']);
                $data['reference_id'] = $data['reference_id'] ?? $data['participant_id'];
            }
        }

        if (empty($data['url'])) {
            $data['url'] = route('portal.notifications');
        }

        if (! empty($data['enquiry_id']) && empty($data['reference_id'])) {
            $data['reference_id'] = $data['enquiry_id'];
        }
        if (! empty($data['document_id']) && empty($data['reference_id'])) {
            $data['reference_id'] = $data['document_id'];
        }
        if (! empty($data['participant_id']) && empty($data['reference_id'])) {
            $data['reference_id'] = $data['participant_id'];
        }

        return $data;
    }
}
