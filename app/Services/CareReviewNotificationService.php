<?php

namespace App\Services;

use App\Models\MonthlyCareReview;
use App\Models\User;
use App\Notifications\CareReviewDueReminder;
use App\Notifications\CareReviewOverdue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Notification;

class CareReviewNotificationService
{
    /**
     * Send 7-day reminder notification
     */
    public function send7DayReminder(MonthlyCareReview $review): void
    {
        $careManagers = $this->getNotificationRecipients($review);
        Notification::send($careManagers, new CareReviewDueReminder($review, 7));

        $review->update(['due_date_reminder_sent_at' => now()]);
    }

    /**
     * Send due today reminder notification
     */
    public function sendDueTodayReminder(MonthlyCareReview $review): void
    {
        $careManagers = $this->getNotificationRecipients($review);
        Notification::send($careManagers, new CareReviewDueReminder($review, 0));

        $review->update(['today_reminder_sent_at' => now()]);
    }

    /**
     * Send overdue notification
     */
    public function sendOverdueNotification(MonthlyCareReview $review): void
    {
        $careManagers = $this->getNotificationRecipients($review);
        Notification::send($careManagers, new CareReviewOverdue($review));

        $review->update(['overdue_reminder_sent_at' => now()]);
    }

    /**
     * Check and send reminders based on due date
     */
    public function checkAndSendReminders(MonthlyCareReview $review): void
    {
        if (! $review->next_review_date) {
            return;
        }

        $daysUntilDue = now()->diffInDays($review->next_review_date);

        // Send 7-day reminder (only once)
        if ($daysUntilDue === 7 && ! $this->hasRecentNotification($review, 'due_date')) {
            $this->send7DayReminder($review);
        }

        // Send due today reminder
        if ($daysUntilDue === 0 && ! $this->hasRecentNotification($review, 'today')) {
            $this->sendDueTodayReminder($review);
        }

        // Send overdue notification
        if ($daysUntilDue < 0 && ! $this->hasRecentNotification($review, 'overdue')) {
            $this->sendOverdueNotification($review);
        }
    }

    /**
     * Check if notification was already sent recently (within 24 hours)
     */
    private function hasRecentNotification(MonthlyCareReview $review, string $type): bool
    {
        $field = match ($type) {
            'due_date' => 'due_date_reminder_sent_at',
            'today' => 'today_reminder_sent_at',
            'overdue' => 'overdue_reminder_sent_at',
            default => null,
        };

        if (! $field || ! $review->$field) {
            return false;
        }

        return now()->diffInHours($review->$field) < 24;
    }

    /**
     * Get notification recipients
     */
    private function getNotificationRecipients(MonthlyCareReview $review): Collection
    {
        // Notify: care manager, admin, and participant's support coordinator
        $recipients = collect();

        // Add care manager
        if ($review->careManager) {
            $recipients->push($review->careManager);
        }

        // Add all admin users
        $admins = User::where('role', 'admin')->get();
        $recipients = $recipients->merge($admins);

        // Add care coordinator if exists
        if ($review->participant->assigned_support_person_id) {
            $coordinator = User::whereHas('supportPerson', function ($query) {
                $query->where('support_people.id', request()->participant->assigned_support_person_id);
            })->first();

            if ($coordinator) {
                $recipients->push($coordinator);
            }
        }

        return $recipients->unique('id');
    }

    /**
     * Scan all reviews and send reminders
     */
    public function scanAndSendReminders(): array
    {
        $results = [
            'sent_7_day' => 0,
            'sent_today' => 0,
            'sent_overdue' => 0,
            'total_processed' => 0,
        ];

        MonthlyCareReview::where('status', '!=', 'Completed')
            ->chunk(50, function ($reviews) use (&$results) {
                foreach ($reviews as $review) {
                    $daysUntilDue = now()->diffInDays($review->next_review_date);

                    if ($daysUntilDue === 7 && ! $this->hasRecentNotification($review, 'due_date')) {
                        $this->send7DayReminder($review);
                        $results['sent_7_day']++;
                    }

                    if ($daysUntilDue === 0 && ! $this->hasRecentNotification($review, 'today')) {
                        $this->sendDueTodayReminder($review);
                        $results['sent_today']++;
                    }

                    if ($daysUntilDue < 0 && ! $this->hasRecentNotification($review, 'overdue')) {
                        $this->sendOverdueNotification($review);
                        $results['sent_overdue']++;
                    }

                    $results['total_processed']++;
                }
            });

        return $results;
    }
}
