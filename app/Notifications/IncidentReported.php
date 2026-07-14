<?php

namespace App\Notifications;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class IncidentReported extends Notification
{
    use Queueable;

    protected $incident;

    public function __construct(Incident $incident)
    {
        $this->incident = $incident;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        $incident = $this->incident;

        return (new MailMessage)
            ->subject("High severity incident reported (#{$incident->id})")
            ->line('A high severity incident has been reported for participant: '.optional($incident->participant)->first_name)
            ->line('Type: '.$incident->incident_type)
            ->line('Severity: '.ucfirst($incident->severity))
            ->line('Description: '.Str::limit($incident->description, 200))
            ->action('View incident', route('portal.admin.incidents.show', $incident->id))
            ->line('Please review and take appropriate action.');
    }

    public function toArray($notifiable)
    {
        $incidentUrl = route('portal.admin.incidents.show', ['incident' => $this->incident->id]);
        
        return [
            'title' => 'Incident Report Submitted',
            'message' => 'Incident #'.$this->incident->id.' - '.ucfirst($this->incident->incident_type),
            'url' => $incidentUrl,
            'action_url' => $incidentUrl,
            'incident_id' => $this->incident->id,
            'participant_id' => $this->incident->participant_id,
            'worker_id' => $this->incident->worker_id,
            'type' => $this->incident->incident_type,
            'severity' => $this->incident->severity,
            'status' => $this->incident->status,
        ];
    }
}
