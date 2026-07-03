<?php

namespace App\Mail;

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

        return $this->subject($subject)
            ->view('emails.portal_test')
            ->with(['settings' => $this->settings]);
    }
}
