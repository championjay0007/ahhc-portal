<?php

namespace App\Mail;

use App\Services\EmailBrandingService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Markdown;
use Illuminate\Queue\SerializesModels;

class StyledEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $headline,
        public string $subtitle,
        public string $intro,
        public array $details = [],
        public ?string $actionUrl = null,
        public ?string $actionText = null,
        public ?string $supportText = null,
        public ?string $footerNote = null,
        public ?string $badge = null,
        public ?string $highlightPanel = null,
        public ?string $warning = null,
        public ?string $logo = null,
        public ?string $introHtml = null,
    ) {
    }

    public function build(): self
    {
        $logoSource = EmailBrandingService::logoSource($this->logo);
        $logoUrl = $logoSource ? $this->embed($logoSource) : EmailBrandingService::logoUrl($this->logo);

        $html = view('emails.shared-layout', [
            'subjectLine' => $this->subjectLine,
            'headline' => $this->headline,
            'headline' => $this->headline,
            'subtitle' => $this->subtitle,
            'intro' => $this->intro,
            'introHtml' => $this->introHtml,
            'details' => $this->details,
            'actionUrl' => $this->actionUrl,
            'actionText' => $this->actionText,
            'supportText' => $this->supportText,
            'footerNote' => $this->footerNote,
            'badge' => $this->badge,
            'highlightPanel' => $this->highlightPanel,
            'warning' => $this->warning,
            'logo' => $logoUrl,
        ])->render();

        return $this->subject($this->subjectLine)
            ->html($html);
    }
}
