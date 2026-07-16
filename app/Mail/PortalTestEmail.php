<?php

namespace App\Mail;

use App\Services\EmailBrandingService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PortalTestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $settings;

    /**
     * Create a new message instance.
     */
    public function __construct(array $settings = [])
    {
        $this->settings = $settings;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = ($this->settings['website_name'] ?? 'Portal').' — Test Email';
        $inner = $this->extractBodyContent(view('emails.portal_test', ['settings' => $this->settings])->render());

        $logoSource = EmailBrandingService::logoSource();
        $logoUrl = EmailBrandingService::logoUrl();

        if ($logoSource) {
            try {
                $logoUrl = $this->embed($logoSource);
            } catch (\Throwable $e) {
                $logoUrl = EmailBrandingService::logoUrl();
            }
        }

        $html = view('emails.shared-layout', [
            'subjectLine' => $subject,
            'headline' => $subject,
            'subtitle' => null,
            'intro' => null,
            'details' => [],
            'actionUrl' => null,
            'actionText' => null,
            'supportText' => null,
            'footerNote' => null,
            'badge' => null,
            'highlightPanel' => $inner,
            'warning' => null,
            'logo' => $logoUrl,
        ])->render();

        return $this->subject($subject)->html($html);
    }

    protected function extractBodyContent(string $html): string
    {
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
            return $matches[1];
        }

        if (preg_match('/<html[^>]*>(.*?)<\/html>/is', $html, $matches)) {
            return $matches[1];
        }

        return $html;
    }
}
