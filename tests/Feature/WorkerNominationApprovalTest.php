<?php

namespace Tests\Feature;

use App\Enums\WorkerNominationStatus;
use App\Mail\WorkerOnboardingInvitation;
use App\Models\Participant;
use App\Models\User;
use App\Models\WorkerNomination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WorkerNominationApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_nomination_approval_sends_worker_onboarding_invitation_email(): void
    {
        Mail::fake();

        $participantUser = User::create([
            'name' => 'Participant User',
            'email' => 'participant@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participant_number' => 'P-1001',
            'first_name' => 'Participant',
            'last_name' => 'Example',
            'status' => 'active',
            'phone' => '0400111222',
            'email' => 'participant@example.com',
        ]);

        $nomination = WorkerNomination::create([
            'participant_id' => $participant->id,
            'worker_full_name' => 'Julia Worker',
            'worker_email' => 'julia.worker@example.com',
            'worker_phone' => '0411222333',
            'worker_type' => 'Independent',
            'service_type' => 'Personal Care',
            'status' => WorkerNominationStatus::Submitted,
        ]);

        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->from(route('portal.admin.nominations.show', $nomination))
            ->post(route('portal.admin.nominations.approve', $nomination));

        $response->assertStatus(302);
        $response->assertSessionHas('status', 'Nomination approved successfully.');
        $response->assertSessionMissing('warning');

        $nomination->refresh();
        $this->assertDatabaseHas('workers', ['email' => 'julia.worker@example.com']);
        $this->assertSame(WorkerNominationStatus::WorkerInvited, $nomination->status);

        Mail::assertSent(WorkerOnboardingInvitation::class, function (WorkerOnboardingInvitation $mail) {
            return $mail->hasTo('julia.worker@example.com')
                && str_contains($mail->onboardingUrl, route('worker.onboarding.show', ['token' => $mail->worker->onboarding_token]));
        });
    }
}
