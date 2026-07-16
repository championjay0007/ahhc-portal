<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;

class AccountActivated extends StyledEmail
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
    ) {
        $subject = 'Your account is now active';

        $dashboardUrl = route('portal.dashboard');
        if ($this->user->role === 'worker') {
            $dashboardUrl = route('portal.worker.dashboard');
        } elseif ($this->user->role === 'admin') {
            $dashboardUrl = route('portal.admin.dashboard');
        }

        $intro = "Hi <strong>{$this->user->name}</strong>,<br><br>" .
                 "Your account has been activated successfully. Please sign in to continue with your onboarding and complete the remaining steps.";

        $supportText = 'If you did not expect this message, please contact our support team.';

        parent::__construct(
            subjectLine: $subject,
            headline: 'Your account is now active',
            subtitle: 'You can now access the AHHC portal.',
            intro: $intro,
            details: [],
            actionUrl: route('portal.login'),
            actionText: 'Sign in to the portal',
            supportText: $supportText,
            footerNote: 'Alternatively, open your dashboard after signing in',
            badge: 'Account Activated',
            highlightPanel: null,
            warning: null,
            logo: null,
            introHtml: null,
        );
    }
}
