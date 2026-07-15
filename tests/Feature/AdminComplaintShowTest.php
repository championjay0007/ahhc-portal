<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\Participant;
use App\Models\SupportPerson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminComplaintShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_complaint_page_loads_with_submitter_relation(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'complaint-admin@example.com',
            'role' => 'admin',
            'status' => 'active',
            'password' => bcrypt('Password123!'),
        ]);

        $submitter = User::create([
            'name' => 'Complaint Submitter',
            'email' => 'submitter@example.com',
            'role' => 'participant',
            'status' => 'active',
            'password' => bcrypt('Password123!'),
        ]);

        $participant = Participant::create([
            'user_id' => $submitter->id,
            'participant_number' => 'P-1001',
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane@example.com',
            'phone' => '0400000001',
            'status' => 'active',
        ]);

        $supportPerson = SupportPerson::create([
            'user_id' => $admin->id,
            'first_name' => 'John',
            'last_name' => 'Smith',
            'email' => 'john@example.com',
            'phone' => '0400000002',
        ]);

        $complaint = Complaint::create([
            'participant_id' => $participant->id,
            'support_person_id' => $supportPerson->id,
            'submitted_by_id' => $submitter->id,
            'category' => '1',
            'priority' => 'medium',
            'description' => 'Example complaint',
            'status' => 'open',
            'received_at' => now(),
            'notes' => 'Test note',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('portal.admin.complaints.show', $complaint));

        $response->assertOk();
        $response->assertSee('Complaint '.$complaint->id);
        $response->assertSee('Complaint Submitter');

        $complaint->load('submittedBy');
        $this->assertSame($submitter->name, $complaint->submittedBy->name);
    }
}
