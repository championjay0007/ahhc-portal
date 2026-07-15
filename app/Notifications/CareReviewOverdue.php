<?php

namespace App\Notifications;

use App\Models\MonthlyCareReview;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Mail\StyledEmail;
use Illuminate\Notifications\Notification;

class CareReviewOverdue extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private MonthlyCareReview $review) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable)
    {
        $participant = $this->review->participant;
        $daysOverdue = $this->review->daysOverdue();

        $intro = "CRITICAL ALERT: Monthly care review for {$participant->first_name} {$participant->last_name} is OVERDUE. Was due: {$this->review->next_review_date->format('F j, Y')}. Days overdue: {$daysOverdue}. Immediate action required to complete this review.";

        return new StyledEmail(
            "CRITICAL: Care Review Overdue - {$participant->first_name} {$participant->last_name}",
            'Care Review Overdue',
            '',
            $intro,
            [],
            config('app.url').'/portal/admin/care-reviews/'.$this->review->id,
            'Complete Review'
        );
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'care_review_overdue',
            'review_id' => $this->review->id,
            'participant_id' => $this->review->participant_id,
            'participant_name' => $this->review->participant->first_name.' '.$this->review->participant->last_name,
            'due_date' => $this->review->next_review_date->toDateString(),
            'days_overdue' => $this->review->daysOverdue(),
            'message' => "OVERDUE: Care review for {$this->review->participant->first_name} {$this->review->participant->last_name}",
        ];
    }
}
