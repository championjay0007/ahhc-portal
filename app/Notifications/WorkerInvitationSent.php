<?php

namespace App\Notifications;

use App\Models\WorkerNomination;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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

        return (new MailMessage)
            ->subject("Worker Invitation Sent - {$nomination->worker_full_name}")
            ->greeting("Hello {$notifiable->name},")
            ->line('Excellent news! An invitation has been sent to your nominated worker.')
            ->line('')
            ->line('**Invitation Details:**')
            ->line('Worker Name: '.$nomination->worker_full_name)
            ->line('Worker Email: '.$nomination->worker_email)
            ->line('Service Type: '.$nomination->service_type)
            ->line('Status: Invitation Sent')
            ->line('')
            ->line('The worker will receive an invitation email at '.$nomination->worker_email.' with instructions to join the AHHC platform.')
            ->line('Once the worker accepts and completes their profile, they will be assigned to provide you with services.')
            ->action('View Nomination', route('portal.participant.nominations.show', $nomination->id))
            ->line('Thank you for your patience!');
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
