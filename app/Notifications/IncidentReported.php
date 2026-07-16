<?php

namespace App\Notifications;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use App\Mail\StyledEmail;
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
        return $this->shouldSendMail($notifiable) ? ['mail', 'database'] : ['database'];
    }

    public function toMail($notifiable)
    {
        if (! $this->shouldSendMail($notifiable)) {
            return null;
        }

        $incident = $this->incident;
        $intro = 'A high severity incident has been reported for participant: '.optional($incident->participant)->first_name;

        $details = [
            'Type' => $incident->incident_type,
            'Severity' => ucfirst($incident->severity),
            'Description' => Str::limit($incident->description, 200),
        ];

        return new StyledEmail(
            "High severity incident reported (#{$incident->id})",
            'Incident Reported',
            '',
            $intro,
            $details,
            route('portal.admin.incidents.show', $incident),
            'View incident'
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
        $incidentUrl = route('portal.admin.incidents.show', $this->incident);
        
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
