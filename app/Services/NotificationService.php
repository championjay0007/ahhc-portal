<?php

namespace App\Services;

use App\Models\PortalNotification;
use App\Models\PushSubscription;
use App\Models\User;
use App\Models\UserNotificationPreference;
use Illuminate\Support\Facades\Mail;
use Minishlink\WebPush\Subscription as WebPushSubscription;
use Minishlink\WebPush\WebPush;

class NotificationService
{
    public static function notify(array $attrs): PortalNotification
    {
        $attrs['title'] = $attrs['title'] ?? $attrs['data']['title'] ?? null;
        $attrs['message'] = $attrs['message'] ?? $attrs['data']['message'] ?? null;
        $attrs['channel'] = $attrs['channel'] ?? 'in_app';
        $attrs['type'] = $attrs['type'] ?? 'info';

        $notification = PortalNotification::create($attrs);

        if (! empty($attrs['user_id'])) {
            $user = User::find($attrs['user_id']);
            $preference = UserNotificationPreference::firstOrCreate(
                ['user_id' => $attrs['user_id']],
                ['channel_email' => true, 'channel_in_app' => true, 'channel_push' => true, 'channel_sms' => false]
            );

            if ($preference->channel_email && $user && is_string($user->email ?? null) && filter_var($user->email, FILTER_VALIDATE_EMAIL) && ($notification->title || $notification->message)) {
                $intro = trim($notification->message ?: $notification->title ?: 'You have a new notification.');
                if (! empty($attrs['data']['url'])) {
                    $intro .= "\n\n".$attrs['data']['url'];
                }

                try {
                    $usesHtml = preg_match('/<\/?[a-z][\s\S]*>/i', $intro);
                    Mail::to($user->email)->send(new \App\Mail\StyledEmail(
                        subjectLine: $notification->title ?? config('app.name').' Notification',
                        headline: $notification->title ?? config('app.name'),
                        subtitle: '',
                        intro: $usesHtml ? '' : $intro,
                        actionUrl: $attrs['data']['url'] ?? null,
                        actionText: 'View details',
                        supportText: null,
                        footerNote: null,
                        badge: $notification->type ?? null,
                        highlightPanel: null,
                        warning: null,
                        logo: null,
                        introHtml: $usesHtml ? $intro : null
                    ));
                } catch (\Exception $e) {
                    // ignore mail failures for now
                }
            }

            if ($preference->channel_push && $user) {
                self::sendPushNotification($user->id, $notification);
            }
        }

        return $notification;
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
                'open_url' => route('portal.notifications.show', $notification->id),
                'notification_id' => $notification->id,
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
                // ignore invalid subscriptions but do not stop other deliveries
            }
        }

        foreach ($webPush->flush() as $report) {
            if (! $report->isSuccess()) {
                // optionally log failed push subscriptions
            }
        }
    }

    public static function forUser($userId)
    {
        return PortalNotification::where('user_id', $userId)->orderBy('created_at', 'desc')->get();
    }
}
