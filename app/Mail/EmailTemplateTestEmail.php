<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Services\EmailBrandingService;
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

        $logoSource = EmailBrandingService::logoSource();
        $logoUrl = $logoSource ? $this->embed($logoSource) : EmailBrandingService::logoUrl();

        $html = view('emails.shared-layout', [
            'subjectLine' => $rendered['subject'],
            'headline' => $rendered['subject'],
            'subtitle' => null,
            'intro' => null,
            'details' => [],
            'actionUrl' => null,
            'actionText' => null,
            'supportText' => null,
            'footerNote' => null,
            'badge' => null,
            'highlightPanel' => $rendered['html'],
            'warning' => null,
            'logo' => $logoUrl,
        ])->render();

        return $this->subject($rendered['subject'])
            ->html($html)
            ->text('emails.email_template_test_plain', [
                'textBody' => $rendered['text'],
            ]);
    }
}
