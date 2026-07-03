<?php

namespace App\Notifications;

use App\Models\WorkerNomination;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $nomination = $this->nomination;

        return (new MailMessage)
            ->subject("Worker Nomination Approved - {$nomination->worker_full_name}")
            ->greeting("Hello {$notifiable->name},")
            ->line('Good news! Your worker nomination has been approved by AHHC.')
            ->line('')
            ->line('**Nomination Details:**')
            ->line('Worker Name: '.$nomination->worker_full_name)
            ->line('Service Type: '.$nomination->service_type)
            ->line('Status: Approved')
            ->line('')
            ->line('The next step is to invite the worker to join. AHHC will send an invitation to the worker at '.$nomination->worker_email)
            ->action('View Nomination', route('portal.participant.nominations.show', $nomination->id))
            ->line('Thank you for using AHHC services.');
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
