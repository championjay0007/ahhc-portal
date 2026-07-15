<?php

namespace App\Mail;

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
            'highlightPanel' => $this->htmlBody,
            'warning' => null,
            'logo' => $logoUrl,
        ])->render();

        return $this->subject($this->subject)
            ->html($html)
            ->text('emails.email_template_test_plain', [
                'textBody' => $this->textBody,
            ]);
    }
}
