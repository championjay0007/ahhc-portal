<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ParticipantOnboardingDocumentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_onboarding_only_shows_documents_assigned_to_their_record(): void
    {
        Storage::fake('local');

        $userA = User::create([
            'name' => 'Alice Example',
            'email' => 'alice@example.com',
            'phone' => '0400000001',
            'role' => 'participant',
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $userB = User::create([
            'name' => 'Bob Example',
            'email' => 'bob@example.com',
            'phone' => '0400000002',
            'role' => 'participant',
            'status' => 'active',
            'password' => bcrypt('password'),
        ]);

        $participantA = Participant::create([
            'user_id' => $userA->id,
            'participant_number' => 'P-1001',
            'first_name' => 'Alice',
            'last_name' => 'Example',
            'email' => 'alice@example.com',
            'phone' => '0400000001',
            'status' => 'onboarding',
            'onboarding_token' => 'participant-a-token',
            'onboarding_expires_at' => now()->addDays(7),
        ]);

        $participantB = Participant::create([
            'user_id' => $userB->id,
            'participant_number' => 'P-1002',
            'first_name' => 'Bob',
            'last_name' => 'Example',
            'email' => 'bob@example.com',
            'phone' => '0400000002',
            'status' => 'onboarding',
            'onboarding_token' => 'participant-b-token',
            'onboarding_expires_at' => now()->addDays(7),
        ]);

        $pathA = 'documents/participant-a-agreement.pdf';
        $pathB = 'documents/participant-b-agreement.pdf';
        Storage::disk('local')->put($pathA, 'PDF content');
        Storage::disk('local')->put($pathB, 'PDF content');

        Document::create([
            'owner_type' => Participant::class,
            'owner_id' => $participantA->id,
            'document_type' => 'agreement',
            'title' => 'Participant A Agreement',
            'storage_disk' => 'local',
            'path' => $pathA,
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'status' => 'active',
            'onboarding_required' => true,
        ]);

        Document::create([
            'owner_type' => Participant::class,
            'owner_id' => $participantB->id,
            'document_type' => 'agreement',
            'title' => 'Participant B Agreement',
            'storage_disk' => 'local',
            'path' => $pathB,
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'status' => 'active',
            'onboarding_required' => true,
        ]);

        $response = $this->get(route('portal.onboarding.show', ['token' => $participantA->onboarding_token]));

        $response->assertStatus(200);
        $response->assertSee('Participant A Agreement');
        $response->assertDontSee('Participant B Agreement');
    }
}
