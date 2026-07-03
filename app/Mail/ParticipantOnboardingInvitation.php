<?php

namespace App\Mail;

use App\Models\Participant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ParticipantOnboardingInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Participant $participant,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Complete your AHHC portal onboarding',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.participant-onboarding-invitation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
