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
        $inner = view('emails.portal_test', ['settings' => $this->settings])->render();

        $logoSource = EmailBrandingService::logoSource();
        $logoUrl = $logoSource ? $this->embed($logoSource) : EmailBrandingService::logoUrl();

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
}
