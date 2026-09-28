<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ParticipantAccountInvitation extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $participantName,
        private readonly string $token
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('portal.participant.accounts.invitation', ['token' => $this->token]);

        return (new MailMessage)
            ->subject('Invitation to manage a participant account')
            ->greeting('Participant account invitation')
            ->line($this->participantName.' invited you to help manage their AHHC participant account.')
            ->line('Sign in or create a manager account using this email address to accept. You can then access assigned participant accounts without sharing passwords.')
            ->action('Review invitation', $url)
            ->line('This invitation expires in 14 days.');
    }
}