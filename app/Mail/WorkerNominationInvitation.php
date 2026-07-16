<?php

namespace App\Mail;

use App\Models\WorkerNomination;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Queueable;

class WorkerNominationInvitation extends StyledEmail
{
    use Queueable, SerializesModels;

    public function __construct(
        public WorkerNomination $nomination,
    ) {
        $subject = 'You have been nominated to join AHHC Care Portal';

        $intro = "Hello <strong>{$this->nomination->worker_full_name}</strong>,<br><br>" .
                 "You have been nominated to join <strong>" . config('app.name', 'AHHC Portal') . "</strong> as a care worker or service provider.";

        $details = [
            'Participant' => $this->nomination->participant->first_name . ' ' . $this->nomination->participant->last_name,
            'Service Type' => $this->nomination->service_type,
            'Proposed Start Date' => $this->nomination->start_date?->format('d M Y') ?? 'TBA',
        ];

        $highlightPanel = '<div style="background-color: #f8fafc; border-left: 5px solid #356991; padding: 20px; margin-bottom: 20px;">' .
                         '<h3 style="margin: 0 0 12px 0; font-size: 16px; font-weight: bold; color: #356991;">Important information</h3>' .
                         '<p style="margin: 0; font-size: 14px; line-height: 1.6; color: #4b5563;">' .
                         '- You cannot access the portal until your onboarding invitation is sent.<br>' .
                         '- Your access will be managed by AHHC administrators.<br>' .
                         '- Confidential participant data is not available until onboarding is complete.' .
                         '</p></div>';

        $supportText = 'If you have any questions or need assistance, please contact our support team.';

        parent::__construct(
            subjectLine: $subject,
            headline: 'You\'ve Been Nominated',
            subtitle: 'Your nomination to join the AHHC care team is underway.',
            intro: $intro,
            details: $details,
            actionUrl: route('public.home'),
            actionText: 'Visit AHHC Portal Website',
            supportText: $supportText,
            footerNote: null,
            badge: 'Worker Nomination',
            highlightPanel: $highlightPanel,
            warning: null,
            logo: null,
            introHtml: null,
        );
    }
}

