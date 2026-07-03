<?php

namespace App\Services;

use App\Models\Shift;

class ShiftNotificationService
{
    public function sendReminder(Shift $shift): void
    {
        if (! in_array($shift->status, [Shift::STATUS_SCHEDULED, Shift::STATUS_CONFIRMED], true)) {
            return;
        }

        $message = sprintf(
            'Reminder: your shift for %s on %s is scheduled from %s to %s.',
            $shift->service_type ?: 'the service',
            $shift->shift_date->format('d M Y'),
            $shift->start_time,
            $shift->end_time
        );

        if ($shift->worker && $shift->worker->user_id) {
            NotificationService::notify([
                'user_id' => $shift->worker->user_id,
                'type' => 'info',
                'title' => 'Shift reminder',
                'message' => $message,
                'data' => [
                    'url' => route('portal.worker.shifts'),
                    'shift_id' => $shift->id,
                ],
            ]);
        }

        if ($shift->participant && $shift->participant->user_id) {
            NotificationService::notify([
                'user_id' => $shift->participant->user_id,
                'type' => 'info',
                'title' => 'Service reminder',
                'message' => $message,
                'data' => [
                    'url' => route('portal.participant.services'),
                    'shift_id' => $shift->id,
                ],
            ]);
        }

        $shift->update(['reminder_sent_at' => now()]);
    }

    public function scanAndSendReminders(): array
    {
        $results = [
            'sent' => 0,
            'skipped' => 0,
            'processed' => 0,
        ];

        $targetDate = now()->addDay()->toDateString();

        Shift::whereIn('status', [Shift::STATUS_SCHEDULED, Shift::STATUS_CONFIRMED])
            ->whereDate('shift_date', $targetDate)
            ->whereNull('reminder_sent_at')
            ->chunk(50, function ($shifts) use (&$results) {
                foreach ($shifts as $shift) {
                    $this->sendReminder($shift);
                    $results['sent']++;
                    $results['processed']++;
                }
            });

        return $results;
    }
}
