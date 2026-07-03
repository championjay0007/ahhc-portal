<?php

namespace App\Mail;

use App\Models\Worker;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkerOnboardingInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public Worker $worker;

    public string $onboardingUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(Worker $worker)
    {
        $this->worker = $worker;
        $this->onboardingUrl = route('worker.onboarding.show', ['token' => $worker->onboarding_token]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'AHHC Portal - Worker Onboarding Invitation',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.worker_onboarding_invitation',
            with: [
                'worker' => $this->worker,
                'onboardingUrl' => $this->onboardingUrl,
                'expiresAt' => $this->worker->onboarding_expires_at,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
