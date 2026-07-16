<?php

namespace App\Notifications;

use App\Models\WorkerNomination;
use Illuminate\Bus\Queueable;
use App\Mail\StyledEmail;
use Illuminate\Notifications\Notification;

class WorkerNominationApproved extends Notification
{
    use Queueable;

    protected $nomination;

    public function __construct(WorkerNomination $nomination)
    {
        $this->nomination = $nomination;
    }

    public function via($notifiable)
    {
        return $this->shouldSendMail($notifiable) ? ['mail'] : [];
    }

    public function toMail($notifiable)
    {
        if (! $this->shouldSendMail($notifiable)) {
            return null;
        }

        $nomination = $this->nomination;

        $intro = 'Good news! Your worker nomination has been approved by AHHC. The next step is to invite the worker to join at '.$nomination->worker_email;

        $details = [
            'Worker Name' => $nomination->worker_full_name,
            'Service Type' => $nomination->service_type,
            'Status' => 'Approved',
        ];

        return new StyledEmail(
            "Worker Nomination Approved - {$nomination->worker_full_name}",
            'Nomination Approved',
            '',
            $intro,
            $details,
            route('portal.participant.nominations.show', $nomination->id),
            'View Nomination'
        );
    }

    protected function shouldSendMail($notifiable): bool
    {
        return (bool) $this->resolveRecipientEmail($notifiable);
    }

    protected function resolveRecipientEmail($notifiable): ?string
    {
        if (! $notifiable) {
            return null;
        }

        $email = null;

        if (is_object($notifiable) && isset($notifiable->email)) {
            $email = $notifiable->email;
        } elseif (is_array($notifiable) && isset($notifiable['email'])) {
            $email = $notifiable['email'];
        }

        if (! is_string($email) || trim($email) === '') {
            return null;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) ?: null;
    }

    public function toArray($notifiable)
    {
        return [
            'nomination_id' => $this->nomination->id,
            'participant_id' => $this->nomination->participant_id,
            'worker_name' => $this->nomination->worker_full_name,
            'status' => $this->nomination->status->value,
            'action' => 'approved',
        ];
    }
}
