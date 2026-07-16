<?php

namespace App\Notifications;

use App\Models\WorkerComplianceDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\StyledEmail;
use Illuminate\Notifications\Notification;

class ComplianceDocumentExpiringReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public int $days;

    public function __construct(WorkerComplianceDocument $document, int $daysRemaining)
    {
        $this->document = $document;
        $this->days = $daysRemaining;
    }

    public function via(object $notifiable): array
    {
        return $this->shouldSendMail($notifiable) ? ['mail', 'database'] : ['database'];
    }

    public function toMail(object $notifiable)
    {
        if (! $this->shouldSendMail($notifiable)) {
            return null;
        }

        $subject = "Worker Compliance Reminder: {$this->document->document_type} Expiring";
        $intro = '';

        if ($this->days === 30) {
            $intro = "Reminder: The {$this->document->document_type} for worker {$this->document->worker->first_name} {$this->document->worker->last_name} will expire in 30 days. Please arrange for renewal before {$this->document->expiry_date->format('F j, Y')}.";
        } elseif ($this->days === 14) {
            $intro = "URGENT: The {$this->document->document_type} for worker {$this->document->worker->first_name} {$this->document->worker->last_name} will expire in 14 days. Please prioritize renewal before {$this->document->expiry_date->format('F j, Y')}.";
        } elseif ($this->days === 7) {
            $intro = "CRITICAL: The {$this->document->document_type} for worker {$this->document->worker->first_name} {$this->document->worker->last_name} expires in 7 days! Immediate action required: Renew before {$this->document->expiry_date->format('F j, Y')}.";
        }

        return new StyledEmail(
            $subject,
            'Compliance Document Expiry',
            '',
            $intro,
            [],
            config('app.url').'/admin/compliance',
            'View Dashboard'
        );
    }

    protected function shouldSendMail(object $notifiable): bool
    {
        return (bool) $this->resolveRecipientEmail($notifiable);
    }

    protected function resolveRecipientEmail(object $notifiable): ?string
    {
        $email = null;

        if (method_exists($notifiable, 'routeNotificationForMail')) {
            $email = $notifiable->routeNotificationForMail($this);
        }

        if (empty($email) && isset($notifiable->email)) {
            $email = $notifiable->email;
        }

        if (! is_string($email) || trim($email) === '') {
            return null;
        }

        return filter_var($email, FILTER_VALIDATE_EMAIL) ?: null;
    }

    public function toDatabase(object $notifiable): array
    {
        $message = "The {$this->document->document_type} for worker {$this->document->worker->first_name} {$this->document->worker->last_name}";

        if ($this->days === 30) {
            $message .= ' will expire in 30 days.';
        } elseif ($this->days === 14) {
            $message .= ' will expire in 14 days (URGENT).';
        } elseif ($this->days === 7) {
            $message .= ' expires in 7 days (CRITICAL).';
        }

        return [
            'type' => 'compliance_expiring',
            'document_id' => $this->document->id,
            'worker_id' => $this->document->worker_id,
            'document_type' => $this->document->document_type,
            'expiry_date' => $this->document->expiry_date,
            'days_remaining' => $this->days,
            'message' => $message,
        ];
    }
}
