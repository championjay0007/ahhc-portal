<?php

namespace App\Notifications;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\StyledEmail;
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
        $intro = "A complaint has been submitted by participant: {$participant->first_name} {$participant->last_name}";

        $details = [
            'Category' => $complaint->category,
            'Priority' => ucfirst($complaint->priority),
            'Description' => Str::limit($complaint->description, 200),
        ];

        return new StyledEmail(
            "New complaint submitted by {$participant->first_name} {$participant->last_name}",
            'Complaint Submitted',
            '',
            $intro,
            $details,
            route('portal.admin.complaints.show', $complaint),
            'Review complaint'
        );
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
