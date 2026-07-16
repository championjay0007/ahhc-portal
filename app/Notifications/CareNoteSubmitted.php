<?php

namespace App\Notifications;

use App\Models\CareNote;
use Illuminate\Bus\Queueable;
use App\Mail\StyledEmail;
use Illuminate\Notifications\Notification;

class CareNoteSubmitted extends Notification
{
    use Queueable;

    protected $careNote;

    public function __construct(CareNote $careNote)
    {
        $this->careNote = $careNote;
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

        $careNote = $this->careNote;
        $participant = optional($careNote->participant);
        $worker = optional($careNote->worker);
        $shiftDate = $careNote->shift_date
            ? (is_string($careNote->shift_date)
                ? $careNote->shift_date
                : $careNote->shift_date->format('Y-m-d'))
            : '—';

        $intro = "A care note has been submitted for participant: {$participant->first_name} {$participant->last_name}";

        $details = [
            'Worker' => $worker->first_name.' '.$worker->last_name,
            'Shift date' => $shiftDate,
            'Status' => ucfirst($careNote->status),
        ];

        return new StyledEmail(
            "Care note submitted for {$participant->first_name} {$participant->last_name} (#{$careNote->id})",
            'Care Note Submitted',
            '',
            $intro,
            $details,
            route('portal.admin.care_notes.show', $careNote),
            'Review care note'
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
        $careNoteUrl = route('portal.admin.care_notes.show', $this->careNote);
        
        return [
            'title' => 'Care Note Submitted',
            'message' => 'Care note #'.$this->careNote->id.' from '.optional($this->careNote->worker)->first_name,
            'url' => $careNoteUrl,
            'action_url' => $careNoteUrl,
            'care_note_id' => $this->careNote->id,
            'participant_id' => $this->careNote->participant_id,
            'worker_id' => $this->careNote->worker_id,
            'shift_date' => $this->careNote->shift_date,
            'status' => $this->careNote->status,
        ];
    }
}
