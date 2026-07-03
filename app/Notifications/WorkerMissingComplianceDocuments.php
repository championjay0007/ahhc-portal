<?php

namespace App\Notifications;

use App\Models\Worker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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

    public function toMail(object $notifiable): MailMessage
    {
        $message = new MailMessage;
        $message->subject("Worker Missing Compliance Documents - {$this->worker->first_name} {$this->worker->last_name}")
            ->greeting("Hello {$notifiable->name},")
            ->line("The following compliance documents are missing for worker {$this->worker->first_name} {$this->worker->last_name}:");

        foreach ($this->missingDocuments as $documentType) {
            $message->line("• {$documentType}");
        }

        $message->line('These documents are required before the worker can be assigned to tasks.')
            ->action('Review Compliance', config('app.url').'/admin/compliance/workers/'.$this->worker->id)
            ->line('Thank you');

        return $message;
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
