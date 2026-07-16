<?php

namespace App\Mail;

use App\Models\Worker;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;

class WorkerOnboardingInvitation extends StyledEmail
{
    use Queueable, SerializesModels;

    public string $onboardingUrl;

    public function __construct(Worker $worker)
    {
        $this->onboardingUrl = route('worker.onboarding.show', ['token' => $worker->onboarding_token]);

        $subject = 'AHHC Portal - Worker Onboarding Invitation';

        $intro = "Hello <strong>{$worker->first_name ?? 'Worker'}</strong>,<br><br>" .
                 "You're invited to begin your onboarding with <strong>" . config('app.name', 'AHHC Portal') . "</strong>. This helps us verify your qualifications, confirm your background checks, and prepare your portal access as a care team member.<br><br>" .
                 "Please use the secure link below to begin. The link remains active until <strong>" . optional($worker->onboarding_expires_at)->format('d M Y H:i') . "</strong>.";

        $details = [
            'Name' => trim(($worker->first_name ?? '') . ' ' . ($worker->last_name ?? '')),
            'Email' => $worker->email ?? '—',
            'Expires' => '<span style="color: #eb3035; font-weight: bold;">' . optional($worker->onboarding_expires_at)->format('d M Y H:i') . '</span>',
        ];

        $supportText = 'If you have any questions or did not expect this invitation, please contact our support team for assistance.';

        parent::__construct(
            subjectLine: $subject,
            headline: 'Worker Onboarding Invitation',
            subtitle: 'Your secure onboarding link is ready. Complete your profile to join the care team.',
            intro: $intro,
            details: $details,
            actionUrl: $this->onboardingUrl,
            actionText: 'Begin Onboarding',
            supportText: $supportText,
            footerNote: null,
            badge: 'Worker Onboarding',
            highlightPanel: null,
            warning: null,
            logo: null,
            introHtml: null,
        );
    }
}

