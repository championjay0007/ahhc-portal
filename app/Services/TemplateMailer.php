<?php

namespace App\Services;

use App\Mail\AdminEmailTemplate;
use Illuminate\Support\Facades\Mail;

class TemplateMailer
{
    public static function send(string $recipientEmail, string $slug, array $variables, string $defaultSubject, string $defaultHtml, ?string $defaultText = null, ?string $name = null, ?string $category = null): void
    {
        [$subject, $html, $text] = static::render($slug, $variables, $defaultSubject, $defaultHtml, $defaultText, $name, $category);

        try {
            Mail::to($recipientEmail)->send(new AdminEmailTemplate($subject, $html, $text));
        } catch (\Throwable $e) {
            Mail::raw($text ?? strip_tags($html), function ($message) use ($recipientEmail, $subject) {
                $message->to($recipientEmail)
                    ->subject($subject);
            });
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
}
