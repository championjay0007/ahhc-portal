<?php

namespace App\Services;

use App\Mail\AdminEmailTemplate;
use App\Models\PortalSetting;
use Illuminate\Support\Facades\Mail;

class TemplateMailer
{
    public static function send(string $recipientEmail, string $slug, array $variables, string $defaultSubject, string $defaultHtml, ?string $defaultText = null, ?string $name = null, ?string $category = null): void
    {
        $variables = static::normalizeVariables($variables);

        [$subject, $html, $text] = static::render($slug, $variables, $defaultSubject, $defaultHtml, $defaultText, $name, $category);

        try {
            Mail::to($recipientEmail)->send(new AdminEmailTemplate($subject, $html, $text));
        } catch (\Throwable $e) {
            try {
                Mail::to($recipientEmail)->send(new \App\Mail\StyledEmail(
                    subjectLine: $subject,
                    headline: $subject,
                    subtitle: '',
                    intro: strip_tags($html),
                    actionUrl: null,
                    actionText: null,
                    supportText: null,
                    footerNote: null,
                    badge: null,
                    highlightPanel: null,
                    warning: null,
                    logo: null,
                    introHtml: $html,
                ));
            } catch (\Throwable $inner) {
                // final fallback: nothing we can do here
            }
        }
    }

    public static function render(string $slug, array $variables, string $defaultSubject, string $defaultHtml, ?string $defaultText = null, ?string $name = null, ?string $category = null): array
    {
        $rendered = EmailTemplateService::renderTemplate(
            $slug,
            $variables,
            $defaultSubject,
            $defaultHtml,
            $defaultText,
            $name,
            $category
        );

        return [$rendered['subject'], $rendered['html'], $rendered['text']];
    }

    protected static function normalizeVariables(array $variables): array
    {
        if (! array_key_exists('logo', $variables)) {
            $logoPath = PortalSetting::where('key', 'logo_path')->value('value');
            if (! empty($logoPath)) {
                $variables['logo'] = asset('storage/' . ltrim($logoPath, '/'));
            } else {
                $variables['logo'] = 'https://via.placeholder.com/160x90.png?text=AHHC+Logo';
            }
        }

        if (! array_key_exists('organization', $variables)) {
            $variables['organization'] = config('app.name', 'AHHC Portal');
        }

        if (! array_key_exists('year', $variables)) {
            $variables['year'] = now()->year;
        }

        return $variables;
    }
}
