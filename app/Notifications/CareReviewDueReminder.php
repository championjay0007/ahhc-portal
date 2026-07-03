<?php

namespace App\Notifications;

use App\Models\MonthlyCareReview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = new MailMessage;
        $participant = $this->review->participant;

        if ($this->daysUntilDue === 7) {
            $message->subject("Care Review Reminder: {$participant->first_name} {$participant->last_name} - Due in 7 Days");
            $message->greeting("Hello {$notifiable->name},");
            $message->line("Reminder: Monthly care review for {$participant->first_name} {$participant->last_name} is due in 7 days.");
            $message->line("Due Date: {$this->review->next_review_date->format('F j, Y')}");
        } elseif ($this->daysUntilDue === 0) {
            $message->subject("URGENT: Care Review Due Today - {$participant->first_name} {$participant->last_name}");
            $message->greeting("Hello {$notifiable->name},");
            $message->line("URGENT: Monthly care review for {$participant->first_name} {$participant->last_name} is DUE TODAY.");
            $message->line('Please complete the review as soon as possible.');
        }

        $message->action('View Review', config('app.url').'/portal/admin/care-reviews/'.$this->review->id)
            ->line('Thank you');

        return $message;
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
