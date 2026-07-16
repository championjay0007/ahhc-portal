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
        return $this->shouldSendMail($notifiable) ? ['mail', 'database'] : ['database'];
    }

    public function toMail($notifiable)
    {
        if (! $this->shouldSendMail($notifiable)) {
            return null;
        }

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
