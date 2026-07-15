<?php

namespace App\Notifications;

use App\Models\WorkerNomination;
use Illuminate\Bus\Queueable;
use App\Mail\StyledEmail;
use Illuminate\Notifications\Notification;

class WorkerInvitationSent extends Notification
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

        $intro = 'Excellent news! An invitation has been sent to your nominated worker. The worker will receive an email at '.$nomination->worker_email.' with instructions to join.';

        $details = [
            'Worker Name' => $nomination->worker_full_name,
            'Worker Email' => $nomination->worker_email,
            'Service Type' => $nomination->service_type,
            'Status' => 'Invitation Sent',
        ];

        return new StyledEmail(
            "Worker Invitation Sent - {$nomination->worker_full_name}",
            'Worker Invitation Sent',
            '',
            $intro,
            $details,
            route('portal.participant.nominations.show', $nomination->id),
            'View Nomination'
        );
    }

    public function toArray($notifiable)
    {
        return [
            'nomination_id' => $this->nomination->id,
            'participant_id' => $this->nomination->participant_id,
            'worker_name' => $this->nomination->worker_full_name,
            'worker_email' => $this->nomination->worker_email,
            'status' => $this->nomination->status->value,
            'action' => 'worker_invited',
        ];
    }
}
