<?php

namespace App\Notifications;

use App\Models\WorkerNomination;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $nomination = $this->nomination;
        $participant = $nomination->participant;

        return (new MailMessage)
            ->subject("New Worker Nomination Submitted (#{$nomination->id})")
            ->greeting('Hello,')
            ->line('A new worker nomination has been submitted by participant: '.$participant->user->name)
            ->line('**Worker Details:**')
            ->line('Name: '.$nomination->worker_full_name)
            ->line('Email: '.$nomination->worker_email)
            ->line('Phone: '.$nomination->worker_phone)
            ->line('Type: '.$nomination->worker_type)
            ->line('Service Type: '.$nomination->service_type)
            ->action('Review Nomination', route('portal.admin.nominations.show', $nomination->id))
            ->line('Please review this nomination and take appropriate action.');
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
