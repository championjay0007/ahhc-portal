<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminParticipantActivationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_participant_without_onboarding_documents_when_agreements_are_signed(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin-approve@example.com',
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt('Password123!'),
        ]);

        $participantUser = User::create([
            'name' => 'Pending Review',
            'email' => 'pending.review@example.com',
            'role' => 'participant',
            'status' => 'active',
            'password' => bcrypt('Password123!'),
        ]);

        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participant_number' => 'P-4001',
            'first_name' => 'Pending',
            'last_name' => 'Review',
            'email' => 'pending.review@example.com',
            'phone' => '0400000005',
            'status' => Participant::STATUS_PENDING_ADMIN_REVIEW,
        ]);

        foreach (array_values(\App\Services\OnboardingAgreementService::requiredAgreements()) as $agreementName) {
            Document::create([
                'owner_type' => Participant::class,
                'owner_id' => $participant->id,
                'document_type' => $agreementName,
                'title' => "Signed {$agreementName}",
                'storage_disk' => 'local',
                'path' => "documents/agreements/{$participant->id}/".strtolower(str_replace(' ', '_', $agreementName)).'.pdf',
                'mime_type' => 'application/pdf',
                'size_bytes' => 1024,
                'status' => 'signed',
            ]);
        }

        $response = $this->actingAs($admin)
            ->post(route('portal.admin.participants.approve', $participant));

        $response->assertSessionHasNoErrors();
        $participant->refresh();
        $this->assertSame(Participant::STATUS_ACTIVE, $participant->status);
    }

    public function test_admin_can_update_participant_status_to_active_without_onboarding_documents_when_agreements_are_signed(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin-update@example.com',
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt('Password123!'),
        ]);

        $participantUser = User::create([
            'name' => 'Onboarding Complete',
            'email' => 'onboarding.complete@example.com',
            'role' => 'participant',
            'status' => 'active',
            'password' => bcrypt('Password123!'),
        ]);

        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participant_number' => 'P-4002',
            'first_name' => 'Onboarding',
            'last_name' => 'Complete',
            'email' => 'onboarding.complete@example.com',
            'phone' => '0400000006',
            'status' => Participant::STATUS_ONBOARDING,
        ]);

        foreach (array_values(\App\Services\OnboardingAgreementService::requiredAgreements()) as $agreementName) {
            Document::create([
                'owner_type' => Participant::class,
                'owner_id' => $participant->id,
                'document_type' => $agreementName,
                'title' => "Signed {$agreementName}",
                'storage_disk' => 'local',
                'path' => "documents/agreements/{$participant->id}/".strtolower(str_replace(' ', '_', $agreementName)).'.pdf',
                'mime_type' => 'application/pdf',
                'size_bytes' => 1024,
                'status' => 'signed',
            ]);
        }

        $response = $this->actingAs($admin)
            ->withoutExceptionHandling()
            ->put(route('portal.admin.participants.update', $participant), [
                'name' => $participant->first_name.' '.$participant->last_name,
                'email' => $participant->email,
                'phone' => $participant->phone,
                'participant_number' => $participant->participant_number,
                'first_name' => $participant->first_name,
                'last_name' => $participant->last_name,
                'status' => Participant::STATUS_ACTIVE,
            ]);

        $response->assertSessionHasNoErrors();
        $participant->refresh();
        $this->assertSame(Participant::STATUS_ACTIVE, $participant->status);
    }
}
