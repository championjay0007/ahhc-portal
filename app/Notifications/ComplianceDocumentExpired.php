<?php

namespace App\Notifications;

use App\Models\WorkerComplianceDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\StyledEmail;
use Illuminate\Notifications\Notification;

class ComplianceDocumentExpired extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private WorkerComplianceDocument $document) {}

    public function via(object $notifiable): array
    {
        return $this->shouldSendMail($notifiable) ? ['mail', 'database'] : ['database'];
    }

    public function toMail(object $notifiable)
    {
        if (! $this->shouldSendMail($notifiable)) {
            return null;
        }

        $intro = "CRITICAL ALERT: The {$this->document->document_type} for worker {$this->document->worker->first_name} {$this->document->worker->last_name} has EXPIRED. Expiry Date: {$this->document->expiry_date->format('F j, Y')}. The worker cannot be assigned to new tasks until this document is renewed.";

        return new StyledEmail(
            "CRITICAL: Worker Compliance Document Expired - {$this->document->document_type}",
            'Compliance Document Expired',
            '',
            $intro,
            [],
            config('app.url').'/admin/compliance',
            'Review Compliance'
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
        return [
            'type' => 'compliance_expired',
            'document_id' => $this->document->id,
            'worker_id' => $this->document->worker_id,
            'document_type' => $this->document->document_type,
            'expiry_date' => $this->document->expiry_date,
            'message' => "The {$this->document->document_type} for worker {$this->document->worker->first_name} {$this->document->worker->last_name} has EXPIRED.",
        ];
    }
}
