<?php

namespace App\Mail;

use App\Services\EmailBrandingService;
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
        $logoSource = EmailBrandingService::logoSource();
        $logoUrl = EmailBrandingService::logoUrl();

        if ($logoSource) {
            try {
                $logoUrl = $this->embed($logoSource);
            } catch (\Throwable $e) {
                $logoUrl = EmailBrandingService::logoUrl();
            }
        }

        $introHtml = $this->htmlBody;

        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $this->htmlBody, $matches)) {
            $introHtml = $matches[1];
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
