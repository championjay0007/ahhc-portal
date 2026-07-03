<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmailTemplateTestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $template;

    public $variables;

    public function __construct(EmailTemplate $template, array $variables = [])
    {
        $this->template = $template;
        $this->variables = $variables;
    }

    public function build()
    {
        $rendered = $this->template->render($this->variables);

        return $this->subject($rendered['subject'])
            ->html($rendered['html'])
            ->text('emails.email_template_test_plain', [
                'textBody' => $rendered['text'],
            ]);
    }
}
