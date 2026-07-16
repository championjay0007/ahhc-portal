<?php

namespace App\Notifications;

use App\Models\MonthlyCareReview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\StyledEmail;
use Illuminate\Notifications\Notification;

class CareReviewDueReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public int $daysUntilDue;

    public function __construct(MonthlyCareReview $review, int $daysUntilDue)
    {
        $this->review = $review;
        $this->daysUntilDue = $daysUntilDue;
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

        $participant = $this->review->participant;
        $intro = '';
        $subject = '';

        if ($this->daysUntilDue === 7) {
            $subject = "Care Review Reminder: {$participant->first_name} {$participant->last_name} - Due in 7 Days";
            $intro = "Reminder: Monthly care review for {$participant->first_name} {$participant->last_name} is due in 7 days. Due Date: {$this->review->next_review_date->format('F j, Y')}";
        } elseif ($this->daysUntilDue === 0) {
            $subject = "URGENT: Care Review Due Today - {$participant->first_name} {$participant->last_name}";
            $intro = "URGENT: Monthly care review for {$participant->first_name} {$participant->last_name} is DUE TODAY. Please complete the review as soon as possible.";
        }

        return new StyledEmail(
            $subject,
            'Care Review Reminder',
            '',
            $intro,
            [],
            config('app.url').'/portal/admin/care-reviews/'.$this->review->id,
            'View Review'
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
            'type' => 'care_review_reminder',
            'review_id' => $this->review->id,
            'participant_id' => $this->review->participant_id,
            'participant_name' => $this->review->participant->first_name.' '.$this->review->participant->last_name,
            'due_date' => $this->review->next_review_date->toDateString(),
            'days_until_due' => $this->daysUntilDue,
            'message' => "Care review reminder for {$this->review->participant->first_name} {$this->review->participant->last_name}",
        ];
    }
}
