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
        return $this->subject($this->subject)
            ->html($this->htmlBody)
            ->text('emails.email_template_test_plain', [
                'textBody' => $this->textBody,
            ]);
    }
}
