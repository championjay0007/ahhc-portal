<?php

namespace App\Mail;

use App\Models\WorkerNomination;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkerNominationInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public WorkerNomination $nomination,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'You have been nominated to join AHHC Care Portal',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.worker-nomination-invitation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
