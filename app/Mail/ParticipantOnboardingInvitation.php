<?php

namespace App\Mail;

use App\Models\Participant;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;

class ParticipantOnboardingInvitation extends StyledEmail
{
    use Queueable, SerializesModels;

    public function __construct(
        public Participant $participant,
    ) {
        $subject = 'Complete your AHHC portal onboarding';

        $name = $this->participant->first_name ?? 'Participant';
        $intro = "Hello <strong>{$name}</strong>,<br><br>" .
                 "You're invited to begin your onboarding with <strong>" . config('app.name', 'AHHC Portal') . "</strong>. This helps us confirm your details, review your documentation, and prepare your portal access.<br><br>" .
                 "Please use the secure link below to continue. The link remains active until <strong>" . optional($this->participant->onboarding_expires_at)->format('d M Y H:i') . "</strong>";

        $details = [
            'Name' => trim(($this->participant->first_name ?? '') . ' ' . ($this->participant->last_name ?? '')),
            'Email' => $this->participant->email ?? '—',
            'Expires' => '<span style="color: #eb3035; font-weight: bold;">' . optional($this->participant->onboarding_expires_at)->format('d M Y H:i') . '</span>',
        ];

        $supportText = 'If you have any questions or did not expect this invitation, please contact our support team for assistance.';

        parent::__construct(
            subjectLine: $subject,
            headline: 'Complete your AHHC onboarding',
            subtitle: 'Your secure onboarding link is ready. Please follow the steps to activate your portal access.',
            intro: $intro,
            details: $details,
            actionUrl: route('portal.onboarding.show', ['token' => $this->participant->onboarding_token]),
            actionText: 'Continue onboarding',
            supportText: $supportText,
            footerNote: null,
            badge: 'Onboarding Invitation',
            highlightPanel: null,
            warning: null,
            logo: null,
            introHtml: null,
        );
    }
}

