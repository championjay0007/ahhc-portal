<?php

namespace App\Notifications;

use App\Models\WorkerNomination;
use Illuminate\Bus\Queueable;
use App\Mail\StyledEmail;
use Illuminate\Notifications\Notification;

class WorkerNominationSubmitted extends Notification
{
    use Queueable;

    protected $nomination;

    public function __construct(WorkerNomination $nomination)
    {
        $this->nomination = $nomination;
    }

    public function via($notifiable)
    {
        $email = $this->resolveRecipientEmail($notifiable);

        return $email ? ['mail'] : [];
    }

    public function toMail($notifiable)
    {
        if (! $this->shouldSendMail($notifiable)) {
            return null;
        }

        $nomination = $this->nomination;
        $participant = $nomination->participant;

        $intro = 'A new worker nomination has been submitted by participant: '.$participant->user->name;

        $details = [
            'Worker Name' => $nomination->worker_full_name,
            'Email' => $nomination->worker_email,
            'Phone' => $nomination->worker_phone,
            'Type' => $nomination->worker_type,
            'Service Type' => $nomination->service_type,
        ];

        return new StyledEmail(
            "New Worker Nomination Submitted (#{$nomination->id})",
            'New Worker Nomination Submitted',
            '',
            $intro,
            $details,
            route('portal.admin.nominations.show', $nomination->id),
            'Review Nomination'
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
            'worker_email' => $this->nomination->worker_email,
            'service_type' => $this->nomination->service_type,
            'status' => $this->nomination->status,
        ];
    }
}
