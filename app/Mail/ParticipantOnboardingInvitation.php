<?php

namespace App\Mail;

use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ParticipantOnboardingInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Participant $participant,
    ) {}

    public function build()
    {
        $subject = 'Complete your AHHC portal onboarding';

        $inner = view('mail.participant-onboarding-invitation', ['participant' => $this->participant])->render();

        $logoUrl = null;
        $logoPath = \App\Models\PortalSetting::where('key', 'logo_path')->value('value');
        if (! empty($logoPath)) {
            $logoUrl = asset('storage/' . ltrim($logoPath, '/'));
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

    public function attachments(): array
    {
        return [];
    }
}
