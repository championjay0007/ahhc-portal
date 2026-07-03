<?php

namespace App\Notifications;

use App\Models\WorkerComplianceDocument;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ComplianceDocumentExpired extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private WorkerComplianceDocument $document) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("CRITICAL: Worker Compliance Document Expired - {$this->document->document_type}")
            ->greeting("Hello {$notifiable->name},")
            ->line("CRITICAL ALERT: The {$this->document->document_type} for worker {$this->document->worker->first_name} {$this->document->worker->last_name} has EXPIRED.")
            ->line("Expiry Date: {$this->document->expiry_date->format('F j, Y')}")
            ->line('The worker cannot be assigned to new tasks until this document is renewed.')
            ->action('Review Compliance', config('app.url').'/admin/compliance')
            ->line('Immediate action required.');
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
