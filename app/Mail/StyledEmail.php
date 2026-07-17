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
        $logoSource = EmailBrandingService::logoSource($this->logo ?? null);
        $logoUrl = EmailBrandingService::logoUrl($this->logo ?? null);

        if ($logoSource) {
            try {
                $logoUrl = $this->embed($logoSource);
            } catch (\Throwable $e) {
                $logoUrl = EmailBrandingService::logoUrl($this->logo ?? null);
            }
        }

        $introHtml = $this->introHtml ? $this->extractEmailFragment($this->introHtml) : null;
        $highlightPanel = $this->highlightPanel ? $this->extractEmailFragment($this->highlightPanel) : null;

        $html = view('emails.shared-layout', [
            'subjectLine' => $this->subjectLine,
            'headline' => $this->headline,
            'subtitle' => $this->subtitle,
            'intro' => $this->intro,
            'introHtml' => $introHtml,
            'details' => $this->details,
            'actionUrl' => $this->actionUrl,
            'actionText' => $this->actionText,
            'supportText' => $this->supportText,
            'footerNote' => $this->footerNote,
            'badge' => $this->badge,
            'highlightPanel' => $highlightPanel,
            'warning' => $this->warning,
            'logo' => $logoUrl,
        ])->render();

        return $this->subject($this->subjectLine)
            ->html($html);
    }

    protected function extractEmailFragment(string $html): string
    {
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $html, $matches)) {
            return trim($matches[1]);
        }

        if (preg_match('/<html[^>]*>(.*?)<\/html>/is', $html, $matches)) {
            return trim($matches[1]);
        }

        return trim($html);
    }
}
