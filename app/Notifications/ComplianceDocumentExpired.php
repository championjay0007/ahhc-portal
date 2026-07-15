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
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable)
    {
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
