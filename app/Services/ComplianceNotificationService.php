<?php

namespace App\Services;

use App\Models\User;
use App\Models\WorkerComplianceAlert;
use App\Models\WorkerComplianceDocument;
use App\Notifications\ComplianceDocumentExpired;
use App\Notifications\ComplianceDocumentExpiringReminder;
use App\Notifications\WorkerMissingComplianceDocuments;
use Illuminate\Support\Facades\Notification;

class ComplianceNotificationService
{
    /**
     * Send 30-day expiring reminder
     */
    public function send30DayReminder(WorkerComplianceDocument $document): void
    {
        $this->sendReminderToAdmins($document, 30);
        $document->update(['last_notified_at' => now()]);
    }

    /**
     * Send 14-day expiring reminder
     */
    public function send14DayReminder(WorkerComplianceDocument $document): void
    {
        $this->sendReminderToAdmins($document, 14);
        $document->update(['last_notified_at' => now()]);
    }

    /**
     * Send 7-day expiring reminder
     */
    public function send7DayReminder(WorkerComplianceDocument $document): void
    {
        $this->sendReminderToAdmins($document, 7);
        $document->update(['last_notified_at' => now()]);
    }

    /**
     * Send expiry notification
     */
    public function sendExpiryNotification(WorkerComplianceDocument $document): void
    {
        $admins = $this->getAdminUsers();

        Notification::send($admins, new ComplianceDocumentExpired($document));
        WorkerComplianceAlert::create([
            'worker_id' => $document->worker_id,
            'worker_compliance_document_id' => $document->id,
            'alert_type' => 'compliance_expired',
            'alert_level' => 'critical',
            'document_type' => $document->document_type,
            'message' => "Compliance document {$document->document_type} for worker {$document->worker->first_name} {$document->worker->last_name} has expired.",
            'metadata' => [
                'expiry_date' => $document->expiry_date?->toDateString(),
                'status' => $document->status,
            ],
            'sent_at' => now(),
        ]);

        $document->update(['last_notified_at' => now()]);
    }

    /**
     * Send missing documents notification
     */
    public function sendMissingDocumentsNotification($worker, array $missingDocumentTypes): void
    {
        $admins = $this->getAdminUsers();

        Notification::send($admins, new WorkerMissingComplianceDocuments($worker, $missingDocumentTypes));
        WorkerComplianceAlert::create([
            'worker_id' => $worker->id,
            'alert_type' => 'compliance_missing',
            'alert_level' => 'warning',
            'document_type' => null,
            'message' => "Worker {$worker->first_name} {$worker->last_name} is missing compliance documents.",
            'metadata' => ['missing_documents' => $missingDocumentTypes],
            'sent_at' => now(),
        ]);
    }

    /**
     * Send reminder to admins
     */
    private function sendReminderToAdmins(WorkerComplianceDocument $document, int $days): void
    {
        $admins = $this->getAdminUsers();

        Notification::send($admins, new ComplianceDocumentExpiringReminder($document, $days));
        WorkerComplianceAlert::create([
            'worker_id' => $document->worker_id,
            'worker_compliance_document_id' => $document->id,
            'alert_type' => 'compliance_expiring',
            'alert_level' => $this->getAlertLevelForReminder($days),
            'document_type' => $document->document_type,
            'message' => "Compliance document {$document->document_type} for worker {$document->worker->first_name} {$document->worker->last_name} expires in {$days} days.",
            'metadata' => [
                'expiry_date' => $document->expiry_date?->toDateString(),
                'days_remaining' => $days,
                'status' => $document->status,
            ],
            'sent_at' => now(),
        ]);
    }

    private function getAlertLevelForReminder(int $days): string
    {
        return match ($days) {
            7 => 'critical',
            14 => 'high',
            default => 'warning',
        };
    }

    /**
     * Get all admin users
     */
    private function getAdminUsers()
    {
        return User::where('role', 'admin')->get();
    }

    /**
     * Check and send reminders based on expiry date
     */
    public function checkAndSendReminders(WorkerComplianceDocument $document): void
    {
        if (! $document->expiry_date) {
            return;
        }

        $daysUntilExpiry = now()->diffInDays($document->expiry_date);

        // Send 30-day reminder (only once)
        if ($daysUntilExpiry === 30 && ! $this->hasRecentNotification($document, 30)) {
            $this->send30DayReminder($document);
        }

        // Send 14-day reminder (only once)
        if ($daysUntilExpiry === 14 && ! $this->hasRecentNotification($document, 14)) {
            $this->send14DayReminder($document);
        }

        // Send 7-day reminder (only once)
        if ($daysUntilExpiry === 7 && ! $this->hasRecentNotification($document, 7)) {
            $this->send7DayReminder($document);
        }

        // Send expiry notification
        if ($daysUntilExpiry <= 0 && ! $this->hasRecentNotification($document, 0)) {
            $this->sendExpiryNotification($document);
        }
    }

    /**
     * Check if notification was already sent recently (within 1 day)
     */
    private function hasRecentNotification(WorkerComplianceDocument $document, int $days): bool
    {
        if (! $document->last_notified_at) {
            return false;
        }

        return now()->diffInHours($document->last_notified_at) < 24;
    }
}
