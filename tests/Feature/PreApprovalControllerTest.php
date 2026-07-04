<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\PreApprovalRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PreApprovalControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_participant_can_submit_pre_approval_with_invalid_worker_id(): void
    {
        $user = User::factory()->create(['role' => 'participant']);
        $participant = Participant::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->post(route('portal.participant.pre_approvals.store'), [
            'service_type' => 'Support',
            'purpose' => 'Need support',
            'requested_amount' => '20.00',
            'supplier_id' => '999999',
            'worker_id' => '133434',
            'start_date' => now()->addDay()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'expiry_date' => now()->addDays(5)->toDateString(),
            'quote' => UploadedFile::fake()->create('quote.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertDatabaseHas('pre_approval_requests', [
            'participant_id' => $participant->id,
            'status' => PreApprovalRequest::STATUS_SUBMITTED,
        ]);
    }
}
