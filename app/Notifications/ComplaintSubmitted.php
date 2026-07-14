<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class ComplaintSubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Complaint $complaint)
    {
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $complaint = $this->complaint;
        $participant = optional($complaint->participant);

        return (new MailMessage)
            ->subject("New complaint submitted by {$participant->first_name} {$participant->last_name}")
            ->line("A complaint has been submitted by participant: {$participant->first_name} {$participant->last_name}")
            ->line("Category: {$complaint->category}")
            ->line("Priority: ".ucfirst($complaint->priority))
            ->line("Description: ".Str::limit($complaint->description, 200))
            ->action('Review complaint', route('portal.admin.complaints.show', $complaint))
            ->line('Please review and take appropriate action.');
    }

    public function toArray($notifiable)
    {
        $complaintUrl = route('portal.admin.complaints.show', $this->complaint);

        return [
            'title' => 'Complaint Submitted',
            'message' => 'Complaint from '.optional($this->complaint->participant)->first_name.' - '.ucfirst($this->complaint->priority),
            'url' => $complaintUrl,
            'action_url' => $complaintUrl,
            'complaint_id' => $this->complaint->id,
            'participant_id' => $this->complaint->participant_id,
            'category' => $this->complaint->category,
            'priority' => $this->complaint->priority,
            'status' => $this->complaint->status,
        ];
    }
}
