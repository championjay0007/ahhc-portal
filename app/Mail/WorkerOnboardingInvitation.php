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
    public function build()
    {
        $subject = 'AHHC Portal - Worker Onboarding Invitation';

        $inner = view('mail.worker_onboarding_invitation', [
            'worker' => $this->worker,
            'onboardingUrl' => $this->onboardingUrl,
            'expiresAt' => $this->worker->onboarding_expires_at,
        ])->render();

        $logoUrl = null;
        $logoPath = \App\Models\PortalSetting::where('key', 'logo_path')->value('value');
        if (! empty($logoPath)) {
            $logoUrl = asset('storage/' . ltrim($logoPath, '/'));
        }

        $html = view('emails.shared-layout', [
            'subjectLine' => $subject,
            'headline' => $subject,
            'subtitle' => null,
            'intro' => null,
            'details' => [],
            'actionUrl' => null,
            'actionText' => null,
            'supportText' => null,
            'footerNote' => null,
            'badge' => null,
            'highlightPanel' => $inner,
            'warning' => null,
            'logo' => $logoUrl,
        ])->render();

        return $this->subject($subject)->html($html);
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}
