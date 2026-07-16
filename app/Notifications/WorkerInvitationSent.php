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
        return $this->shouldSendMail($notifiable) ? ['mail'] : [];
    }

    public function toMail($notifiable)
    {
        if (! $this->shouldSendMail($notifiable)) {
            return null;
        }

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

        if (is_object($notifiable) && method_exists($notifiable, 'routeNotificationForMail')) {
            $email = $notifiable->routeNotificationForMail($this);
        }

        if (empty($email) && is_object($notifiable) && isset($notifiable->email)) {
            $email = $notifiable->email;
        } elseif (empty($email) && is_array($notifiable) && isset($notifiable['email'])) {
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
            'status' => $this->nomination->status->value,
            'action' => 'worker_invited',
        ];
    }
}
