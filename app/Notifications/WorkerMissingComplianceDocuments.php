<?php

namespace App\Notifications;

use App\Models\Worker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\StyledEmail;
use Illuminate\Notifications\Notification;

class WorkerMissingComplianceDocuments extends Notification implements ShouldQueue
{
    use Queueable;

    public array $missingDocuments;

    public function __construct(Worker $worker, array $missingDocuments)
    {
        $this->worker = $worker;
        $this->missingDocuments = $missingDocuments;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable)
    {
        $subject = "Worker Missing Compliance Documents - {$this->worker->first_name} {$this->worker->last_name}";
        $intro = "The following compliance documents are missing for worker {$this->worker->first_name} {$this->worker->last_name}:";

        $details = [];
        foreach ($this->missingDocuments as $documentType) {
            $details[$documentType] = '';
        }

        return new StyledEmail(
            $subject,
            'Missing Compliance Documents',
            '',
            $intro,
            $details,
            config('app.url').'/admin/compliance/workers/'.$this->worker->id,
            'Review Compliance'
        );
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'compliance_missing',
            'worker_id' => $this->worker->id,
            'missing_documents' => $this->missingDocuments,
            'message' => 'Worker '.count($this->missingDocuments).' compliance documents are missing',
        ];
    }
}
