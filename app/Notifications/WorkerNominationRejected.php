<?php

namespace App\Notifications;

use App\Models\WorkerNomination;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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

        return (new MailMessage)
            ->subject("Worker Nomination - Unable to Proceed - {$nomination->worker_full_name}")
            ->greeting("Hello {$notifiable->name},")
            ->line('Unfortunately, your worker nomination has not been approved at this time.')
            ->line('')
            ->line('**Nomination Details:**')
            ->line('Worker Name: '.$nomination->worker_full_name)
            ->line('Service Type: '.$nomination->service_type)
            ->line('Status: Rejected')
            ->line('')
            ->line('**Reason for Rejection:**')
            ->line($nomination->rejection_reason ?? 'No reason provided.')
            ->line('')
            ->line('If you have any questions or would like to discuss this decision, please contact AHHC support.')
            ->action('View Nomination Details', route('portal.participant.nominations.show', $nomination->id))
            ->line('We appreciate your understanding.');
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
