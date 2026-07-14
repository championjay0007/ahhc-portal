<?php

namespace App\Notifications;

use App\Models\CareNote;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $careNote = $this->careNote;
        $participant = optional($careNote->participant);
        $worker = optional($careNote->worker);
        $shiftDate = $careNote->shift_date
            ? (is_string($careNote->shift_date)
                ? $careNote->shift_date
                : $careNote->shift_date->format('Y-m-d'))
            : '—';

        return (new MailMessage)
            ->subject("Care note submitted for {$participant->first_name} {$participant->last_name} (#{$careNote->id})")
            ->line("A care note has been submitted for participant: {$participant->first_name} {$participant->last_name}")
            ->line("Worker: {$worker->first_name} {$worker->last_name}")
            ->line("Shift date: {$shiftDate}")
            ->line("Status: ".ucfirst($careNote->status))
            ->action('Review care note', route('portal.admin.care_notes.show', $careNote))
            ->line('Please review and approve when ready.');
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
