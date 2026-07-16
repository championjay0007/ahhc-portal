<?php

namespace App\Mail;

use App\Models\PortalSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AdminEmailTemplate extends Mailable
{
    use Queueable, SerializesModels;

    public string $htmlBody;

    public string $textBody;

    public function __construct(string $subject, string $html, string $text)
    {
        $this->subject = $subject;
        $this->htmlBody = $html;
        $this->textBody = $text;
    }

    public function build()
    {
        $logoUrl = null;
        $logoPath = PortalSetting::where('key', 'logo_path')->value('value');
        if (! empty($logoPath)) {
            $logoUrl = asset('storage/' . ltrim($logoPath, '/'));
        }

        // Extract body content from the HTML if it contains a complete email template
        $introHtml = $this->htmlBody;
        
        // If the HTML contains doctype/html/head tags, extract just the body content
        if (preg_match('/<body[^>]*>(.*)<\/body>/is', $this->htmlBody, $matches)) {
            $bodyContent = $matches[1];
            // Remove the header div if present (from old email templates)
            $bodyContent = preg_replace('/<div\s+class="header"[^>]*>.*?<\/div>\s*<div\s+class="body"[^>]*>/is', '', $bodyContent);
            // Remove the footer div if present
            $bodyContent = preg_replace('/<\/div>\s*<div\s+class="footer"[^>]*>.*?<\/div>\s*<\/div>/is', '</div>', $bodyContent);
            $introHtml = $bodyContent;
        }

        $html = view('emails.shared-layout', [
            'subjectLine' => $this->subject,
            'headline' => $this->subject,
            'subtitle' => null,
            'intro' => null,
            'details' => [],
            'actionUrl' => null,
            'actionText' => null,
            'supportText' => null,
            'footerNote' => null,
            'badge' => null,
            'highlightPanel' => null,
            'warning' => null,
            'logo' => $logoUrl,
            'introHtml' => $introHtml,
        ])->render();

        return $this->subject($this->subject)
            ->html($html)
            ->text('emails.email_template_test_plain', [
                'textBody' => $this->textBody,
            ]);
    }
}
