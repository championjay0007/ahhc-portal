<?php

namespace App\Notifications;

use App\Models\WorkerNomination;
use Illuminate\Bus\Queueable;
use App\Mail\StyledEmail;
use Illuminate\Notifications\Notification;

class WorkerNominationRejected extends Notification
{
    use Queueable;

    protected $nomination;

    public function __construct(WorkerNomination $nomination)
    {
        $this->nomination = $nomination;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $nomination = $this->nomination;

        $intro = 'Unfortunately, your worker nomination has not been approved at this time.';

        $details = [
            'Worker Name' => $nomination->worker_full_name,
            'Service Type' => $nomination->service_type,
            'Status' => 'Rejected',
            'Reason' => $nomination->rejection_reason ?? 'No reason provided.',
        ];

        return new StyledEmail(
            "Worker Nomination - Unable to Proceed - {$nomination->worker_full_name}",
            "Nomination Update",
            '',
            $intro,
            $details,
            route('portal.participant.nominations.show', $nomination->id),
            'View Nomination Details'
        );
    }

    public function toArray($notifiable)
    {
        return [
            'nomination_id' => $this->nomination->id,
            'participant_id' => $this->nomination->participant_id,
            'worker_name' => $this->nomination->worker_full_name,
            'status' => $this->nomination->status->value,
            'action' => 'rejected',
            'reason' => $this->nomination->rejection_reason,
        ];
    }
}
