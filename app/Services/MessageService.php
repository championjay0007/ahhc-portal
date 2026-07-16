<?php

namespace App\Services;

use App\Models\Message;
use App\Models\MessageTemplate;
use App\Models\PortalNotification;
use App\Models\PortalSetting;
use App\Models\User;
use App\Services\HtmlSanitizer;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class MessageService
{
    public static function sendMessage(int $senderId, int $recipientId, string $subject, string $body, ?int $templateId = null): Message
    {
        // Sanitize user-provided message bodies. Template messages are assumed authored by admins and kept as-is.
        if (is_null($templateId)) {
            $body = HtmlSanitizer::sanitize($body);
        }

        $message = Message::create([
            'sender_id' => $senderId,
            'recipient_id' => $recipientId,
            'subject' => $subject,
            'body' => $body,
            'template_id' => $templateId,
            'type' => 'general',
        ]);

        // Send email notification
        $recipient = User::find($recipientId);
        if ($recipient && $recipient->email) {
            self::sendEmailNotification($recipient, $subject, $body);
        }

        // Create an in-app portal notification so users see the message in the UI
        try {
            PortalNotification::create([
                'user_id' => $recipientId,
                'title' => 'New message',
                'message' => substr(strip_tags($body), 0, 255),
                'type' => 'info',
                'channel' => 'in_app',
                'data' => ['message_id' => $message->id],
            ]);
        } catch (\Exception $e) {
            // don't block message creation on notification failures
        }

        // Fire a real-time broadcast event so the UI can update message icons in real time
        try {
            event(new \App\Events\NewMessage($message));
        } catch (\Exception $e) {
            // non-fatal if broadcasting isn't configured
        }

        return $message;
    }

    public static function sendMessageToMultipleUsers(int $senderId, array $recipientIds, string $subject, string $body, ?int $templateId = null): array
    {
        $messages = [];
        foreach ($recipientIds as $recipientId) {
            $messages[] = self::sendMessage($senderId, $recipientId, $subject, $body, $templateId);
        }

        return $messages;
    }

    public static function sendMessageUsingTemplate(int $senderId, int $recipientId, MessageTemplate $template, array $replacements = []): Message
    {
        $recipient = User::find($recipientId);
        $body = $template->body;
        $subject = $template->subject;

        $replacements = array_merge(
            self::getDefaultTemplateReplacements(),
            $recipient ? self::getRecipientTemplateReplacements($recipient) : [],
            $replacements
        );

        $subject = self::applyTemplateReplacements($subject, $replacements);
        $body = self::applyTemplateReplacements($body, $replacements);

        $body = self::wrapTemplateBody($template, $body);

        return self::sendMessage($senderId, $recipientId, $subject, $body, $template->id);
    }

    private static function wrapTemplateBody(MessageTemplate $template, string $body): string
    {
        $theme = $template->theme ?? 'clean';
        $customStyle = trim($template->custom_style ?? '');
        $themeHtml = trim($template->theme_html ?? '');

        $html = $themeHtml !== ''
            ? $themeHtml
            : self::getDefaultThemeHtml($theme);

        $placeholders = ['{{body}}', '*|BODY|*'];

        if (strpos($html, '{{body}}') !== false || strpos($html, '*|BODY|*') !== false) {
            $html = str_replace($placeholders, $body, $html);
        } else {
            $html .= $body;
        }

        if ($theme === 'custom' && $customStyle !== '') {
            return '<style>'.$customStyle.'</style>'.$html;
        }

        return $html;
    }

    public static function getDefaultThemeHtml(string $theme): string
    {
        $theme = $theme ?: 'clean';
        $templates = self::getDefaultThemeHtmlTemplates();

        return $templates[$theme] ?? $templates['clean'];
    }

    public static function getDefaultThemeHtmlTemplates(): array
    {
        $templates = [];
        foreach (self::getThemeWrapperStyles() as $theme => $style) {
            $templates[$theme] = '<div style="'.$style.'">{{body}}</div>';
        }

        return $templates;
    }

    private static function applyTemplateReplacements(string $text, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $text = str_replace(
                [
                    '{{'.$key.'}}',
                    '*|'.self::getMailchimpMergeTag($key).'|*',
                ],
                $value,
                $text
            );
        }

        return $text;
    }

    private static function getMailchimpMergeTag(string $key): string
    {
        $aliases = [
            'name' => 'NAME',
            'user_name' => 'USERNAME',
            'first_name' => 'FNAME',
            'last_name' => 'LNAME',
            'email' => 'EMAIL',
            'organization' => 'ORGANIZATION',
            'body' => 'BODY',
        ];

        return $aliases[$key] ?? strtoupper($key);
    }

    private static function getRecipientTemplateReplacements(User $recipient): array
    {
        [$firstName, $lastName] = self::splitName($recipient->name);

        return [
            'name' => $recipient->name,
            'user_name' => $recipient->name,
            'first_name' => $recipient->first_name ?? $firstName,
            'last_name' => $recipient->last_name ?? $lastName,
            'email' => $recipient->email,
            'organization' => self::getOrganizationName(),
        ];
    }

    private static function splitName(string $name): array
    {
        $parts = array_filter(array_map('trim', explode(' ', $name)));
        $firstName = $parts[0] ?? '';
        $lastName = count($parts) > 1 ? array_pop($parts) : '';

        return [$firstName, $lastName];
    }

    public static function getThemeWrapperStyles(): array
    {
        return [
            'clean' => 'background: #ffffff; border: 1px solid #e5e7eb; border-radius: 18px; padding: 1.5rem; color: #1f2937; font-family: system-ui, sans-serif; line-height: 1.75; max-width: 720px;',
            'modern' => 'background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 20px; padding: 1.6rem; color: #0f172a; font-family: system-ui, sans-serif; line-height: 1.75; max-width: 720px;',
            'warm' => 'background: #fff7ed; border: 1px solid #fcd9b6; border-radius: 20px; padding: 1.6rem; color: #7c2d12; font-family: system-ui, sans-serif; line-height: 1.75; max-width: 720px;',
            'elegant' => 'background: #f5f3ff; border: 1px solid #c7d2fe; border-radius: 20px; padding: 1.6rem; color: #27196b; font-family: system-ui, sans-serif; line-height: 1.75; max-width: 720px;',
            'corporate' => 'background: #f3f4f6; border: 1px solid #d1d5db; border-radius: 20px; padding: 1.6rem; color: #0f172a; font-family: system-ui, sans-serif; line-height: 1.75; max-width: 720px;',
            'minimal' => 'background: #ffffff; border: 1px solid #e5e7eb; border-radius: 18px; padding: 1.5rem; color: #111827; font-family: system-ui, sans-serif; line-height: 1.75; max-width: 720px;',
            'bold' => 'background: #0f172a; border: 1px solid #0f172a; border-radius: 18px; padding: 1.5rem; color: #f8fafc; font-family: system-ui, sans-serif; line-height: 1.75; max-width: 720px;',
            'soft' => 'background: #fdf2f8; border: 1px solid #fbcfe8; border-radius: 20px; padding: 1.6rem; color: #831843; font-family: system-ui, sans-serif; line-height: 1.75; max-width: 720px;',
            'premium' => 'background: #eef2ff; border: 1px solid #c7d2fe; border-radius: 20px; padding: 1.6rem; color: #312e81; font-family: system-ui, sans-serif; line-height: 1.75; max-width: 720px;',
            'festive' => 'background: #fffbeb; border: 1px solid #f59e0b; border-radius: 20px; padding: 1.6rem; color: #92400e; font-family: system-ui, sans-serif; line-height: 1.75; max-width: 720px;',
            'sunrise' => 'background: #fff7ed; border: 1px solid #fb923c; border-radius: 20px; padding: 1.6rem; color: #9a3412; font-family: system-ui, sans-serif; line-height: 1.75; max-width: 720px;',
            'twilight' => 'background: #eef2ff; border: 1px solid #6366f1; border-radius: 20px; padding: 1.6rem; color: #312e81; font-family: system-ui, sans-serif; line-height: 1.75; max-width: 720px;',
            'calm' => 'background: #ecfdf5; border: 1px solid #86efac; border-radius: 20px; padding: 1.6rem; color: #14532d; font-family: system-ui, sans-serif; line-height: 1.75; max-width: 720px;',
            'vibrant' => 'background: #fef9c3; border: 1px solid #f59e0b; border-radius: 20px; padding: 1.6rem; color: #92400e; font-family: system-ui, sans-serif; line-height: 1.75; max-width: 720px;',
            'pastel' => 'background: #f5f3ff; border: 1px solid #d8b4fe; border-radius: 20px; padding: 1.6rem; color: #6d28d9; font-family: system-ui, sans-serif; line-height: 1.75; max-width: 720px;',
            'classic' => 'background: #faf5eb; border: 1px solid #d4b483; border-radius: 20px; padding: 1.6rem; color: #713f12; font-family: system-ui, sans-serif; line-height: 1.75; max-width: 720px;',
            'tech' => 'background: #0f172a; border: 1px solid #334155; border-radius: 20px; padding: 1.6rem; color: #38bdf8; font-family: system-ui, sans-serif; line-height: 1.75; max-width: 720px;',
            'luxury' => 'background: #111827; border: 1px solid #f59e0b; border-radius: 20px; padding: 1.6rem; color: #fbbf24; font-family: system-ui, sans-serif; line-height: 1.75; max-width: 720px;',
            'natural' => 'background: #ecfdf5; border: 1px solid #4ade80; border-radius: 20px; padding: 1.6rem; color: #065f46; font-family: system-ui, sans-serif; line-height: 1.75; max-width: 720px;',
            'sleek' => 'background: #111827; border: 1px solid #374151; border-radius: 20px; padding: 1.6rem; color: #e2e8f0; font-family: system-ui, sans-serif; line-height: 1.75; max-width: 720px;',
            'custom' => 'background: #ffffff; border: 1px solid #e5e7eb; border-radius: 18px; padding: 1.5rem; color: #1f2937; font-family: system-ui, sans-serif; line-height: 1.75; max-width: 720px;',
        ];
    }

    private static function getDefaultTemplateReplacements(): array
    {
        return [
            'organization' => self::getOrganizationName(),
        ];
    }

    private static function getOrganizationName(): string
    {
        $defaultName = config('app.name', 'AHHC Portal');

        try {
            if (Schema::hasTable('portal_settings')) {
                $settings = PortalSetting::query()->pluck('value', 'key')->all();

                return $settings['website_name'] ?? $settings['organization_name'] ?? $defaultName;
            }
        } catch (\Throwable $e) {
            // ignore if settings table is unavailable or database is unreachable
        }

        return $defaultName;
    }

    public static function sendTemplateToMultipleUsers(int $senderId, array $recipientIds, MessageTemplate $template, array $replacements = []): array
    {
        $messages = [];
        foreach ($recipientIds as $recipientId) {
            $messages[] = self::sendMessageUsingTemplate($senderId, $recipientId, $template, $replacements);
        }

        return $messages;
    }

    private static function sendEmailNotification(User $recipient, string $subject, string $body): void
    {
        try {
            $preference = $recipient->notificationPreferences ?? [];

            // Check if user has email notifications enabled (default true)
            if (isset($preference->channel_email) && ! $preference->channel_email) {
                return;
            }

            if (! is_string($recipient->email ?? null) || ! filter_var($recipient->email, FILTER_VALIDATE_EMAIL)) {
                return;
            }

            $usesHtml = preg_match('/<\/?[a-z][\s\S]*>/i', $body);
            Mail::to($recipient->email)->send(new \App\Mail\StyledEmail(
                subjectLine: $subject,
                headline: $subject,
                subtitle: '',
                intro: $usesHtml ? '' : $body,
                actionUrl: null,
                actionText: null,
                supportText: null,
                footerNote: null,
                badge: null,
                highlightPanel: null,
                warning: null,
                logo: null,
                introHtml: $usesHtml ? $body : null
            ));
        } catch (\Exception $e) {
            // Log but don't throw - message was still created in database
        }
    }

    public static function getUserInbox(int $userId, int $perPage = 20)
    {
        return Message::where('recipient_id', $userId)
            ->whereNull('deleted_at')
            ->with(['sender', 'template'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public static function getUserSentMessages(int $userId, int $perPage = 20)
    {
        return Message::where('sender_id', $userId)
            ->with(['recipient', 'template'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public static function getUnreadCount(int $userId): int
    {
        return Message::where('recipient_id', $userId)
            ->whereNull('read_at')
            ->whereNull('deleted_at')
            ->count();
    }
}
