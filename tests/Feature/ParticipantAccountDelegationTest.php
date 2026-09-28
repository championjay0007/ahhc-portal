<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\ParticipantAccountDelegation;
use App\Models\User;
use App\Notifications\ParticipantAccountInvitation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class ParticipantAccountDelegationTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_invite_another_participant_account_by_email(): void
    {
        Notification::fake();
        [$owner] = $this->createParticipant('Account Owner', 'owner@example.com');

        $response = $this->actingAs($owner)->post(route('portal.participant.accounts.invite'), [
            'email' => 'child@example.com',
        ]);

        $response->assertRedirect(route('portal.participant.accounts.index'));
        $this->assertDatabaseHas('participant_account_delegations', [
            'participant_id' => $owner->participant->id,
            'invited_email' => 'child@example.com',
            'accepted_at' => null,
        ]);
        Notification::assertSentOnDemand(ParticipantAccountInvitation::class);
    }

    public function test_invited_manager_can_register_and_open_the_owner_account(): void
    {
        [$owner] = $this->createParticipant('Account Owner', 'owner@example.com');
        $token = Str::random(64);
        $invitation = ParticipantAccountDelegation::create([
            'participant_id' => $owner->participant->id,
            'invited_email' => 'child@example.com',
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(14),
        ]);

        $this->get(route('portal.participant.accounts.invitation', $token))
            ->assertOk()
            ->assertSee('Create manager account');

        $response = $this->post(route('portal.participant.accounts.register', $token), [
            'name' => 'Account Manager',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $manager = User::where('email', 'child@example.com')->firstOrFail();
        $response->assertRedirect(route('portal.manager.dashboard'));
        $this->assertAuthenticatedAs($manager);
        $this->assertSame('manager', $manager->role);
        $this->assertNull($manager->participant()->first());
        $this->assertDatabaseHas('participant_account_delegations', [
            'id' => $invitation->id,
            'manager_user_id' => $manager->id,
            'token_hash' => null,
        ]);

        $this->get(route('portal.manager.dashboard'))
            ->assertOk()
            ->assertSee('Account Owner');

        $this->post(route('portal.participant.accounts.switch'), [
            'participant_user_id' => $owner->id,
        ])->assertRedirect(route('portal.dashboard'));

        $this->get(route('portal.dashboard'))
            ->assertOk()
            ->assertSee('Welcome back, Account Owner');
    }

    public function test_invitation_cannot_be_accepted_by_a_different_email(): void
    {
        [$owner] = $this->createParticipant('Account Owner', 'owner@example.com');
        [$otherUser] = $this->createParticipant('Other Account', 'other@example.com');
        $token = Str::random(64);
        ParticipantAccountDelegation::create([
            'participant_id' => $owner->participant->id,
            'invited_email' => 'child@example.com',
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(14),
        ]);

        $this->actingAs($otherUser)
            ->post(route('portal.participant.accounts.accept', $token))
            ->assertForbidden();
    }

    public function test_delegate_cannot_invite_another_manager_or_change_the_owner_profile(): void
    {
        [$owner] = $this->createParticipant('Account Owner', 'owner@example.com');
        $manager = User::create([
            'name' => 'Account Manager',
            'email' => 'child@example.com',
            'role' => 'manager',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);
        ParticipantAccountDelegation::create([
            'participant_id' => $owner->participant->id,
            'manager_user_id' => $manager->id,
            'invited_email' => $manager->email,
            'accepted_at' => now(),
            'expires_at' => now()->addDays(14),
        ]);

        $this->actingAs($manager)
            ->withSession(['participant_account_user_id' => $owner->id])
            ->post(route('portal.participant.accounts.invite'), ['email' => 'another@example.com'])
            ->assertForbidden();

        $this->actingAs($manager)
            ->withSession(['participant_account_user_id' => $owner->id])
            ->put(route('portal.profile.update'), [
                'name' => 'Changed Owner',
                'email' => $owner->email,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('participant_account_delegations', [
            'participant_id' => $owner->participant->id,
            'invited_email' => 'another@example.com',
        ]);
        $this->assertDatabaseHas('users', ['id' => $owner->id, 'name' => 'Account Owner']);
    }

    public function test_admin_can_view_manager_directory_and_managers_cannot(): void
    {
        [$owner] = $this->createParticipant('Assigned Participant', 'assigned@example.com');
        $manager = User::create([
            'name' => 'Directory Manager',
            'email' => 'manager@example.com',
            'role' => 'manager',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);
        ParticipantAccountDelegation::create([
            'participant_id' => $owner->participant->id,
            'manager_user_id' => $manager->id,
            'invited_email' => $manager->email,
            'accepted_at' => now(),
            'expires_at' => now()->addDays(14),
        ]);
        $admin = User::create([
            'name' => 'Portal Admin',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(route('portal.admin.managers'))
            ->assertOk()
            ->assertSee('Directory Manager')
            ->assertSee('manager@example.com')
            ->assertSee('Assigned Participant');

        $this->actingAs($manager)
            ->get(route('portal.admin.managers'))
            ->assertForbidden();
    }

    private function createParticipant(string $name, string $email): array
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        Participant::create([
            'user_id' => $user->id,
            'participant_number' => 'P-'.Str::upper(Str::random(10)),
            'first_name' => $name,
            'last_name' => 'Test',
            'status' => Participant::STATUS_ACTIVE,
        ]);

        return [$user->fresh(), $user->participant()->first()];
    }
}