<?php

namespace App\Mail;

use App\Models\PortalSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Markdown;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

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
        $logoUrl = null;
        if (empty($this->logo)) {
            $logoPath = PortalSetting::where('key', 'logo_path')->value('value');
            if (! empty($logoPath)) {
                $logoPath = ltrim($logoPath, '/');
                if (Str::startsWith($logoPath, ['http://', 'https://', 'data:'])) {
                    $logoUrl = $logoPath;
                } else {
                    // avoid double 'storage/storage' if path already contains storage/
                    if (Str::startsWith($logoPath, 'storage/')) {
                        $logoPath = substr($logoPath, strlen('storage/'));
                    }
                    $logoUrl = asset('storage/' . ltrim($logoPath, '/'));
                }
            }
        } else {
            $logoUrl = $this->logo;
        }

        $html = view('emails.shared-layout', [
            'subjectLine' => $this->subjectLine,
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
