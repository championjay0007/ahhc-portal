<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Participant;
use App\Models\ParticipantAssignment;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkerInvoiceSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_worker_can_view_invoice_submission_page(): void
    {
        $user = User::create([
            'name' => 'Worker Invoice Viewer',
            'email' => 'worker-invoice-viewer@example.com',
            'role' => 'worker',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $worker = Worker::create([
            'user_id' => $user->id,
            'worker_number' => 'W-'.$user->id,
            'first_name' => 'Worker',
            'last_name' => 'Invoice',
            'phone' => $user->phone,
            'email' => $user->email,
            'role_type' => 'worker',
            'status' => 'active',
            'onboarding_stage' => 6,
        ]);

        $participantUser = User::create([
            'name' => 'Participant Invoice User',
            'email' => 'participant-invoice-viewer@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participant_number' => 'P-1005',
            'first_name' => 'Participant',
            'last_name' => 'Invoice',
            'status' => 'active',
        ]);

        ParticipantAssignment::create([
            'participant_id' => $participant->id,
            'worker_id' => $worker->id,
            'support_person_id' => null,
            'start_date' => now()->subDays(7)->toDateString(),
            'end_date' => null,
            'assignment_type' => 'home_care',
            'status' => 'active',
            'is_primary' => true,
        ]);

        $response = $this->actingAs($user)->get(route('portal.worker.invoices'));

        $response->assertStatus(200);
        $response->assertSee('Submit Invoice');
        $response->assertSee('Participant Invoice');
    }

    public function test_worker_can_submit_an_invoice(): void
    {
        Storage::fake('local');

        $user = User::create([
            'name' => 'Worker Invoice Submitter',
            'email' => 'worker-invoice-submitter@example.com',
            'role' => 'worker',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $worker = Worker::create([
            'user_id' => $user->id,
            'worker_number' => 'W-'.$user->id,
            'first_name' => 'Worker',
            'last_name' => 'Submitter',
            'phone' => $user->phone,
            'email' => $user->email,
            'role_type' => 'worker',
            'status' => 'active',
            'onboarding_stage' => 6,
        ]);

        $participantUser = User::create([
            'name' => 'Participant Invoice Create User',
            'email' => 'participant-invoice-creator@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participant_number' => 'P-1006',
            'first_name' => 'Participant',
            'last_name' => 'Invoice',
            'status' => 'active',
        ]);

        ParticipantAssignment::create([
            'participant_id' => $participant->id,
            'worker_id' => $worker->id,
            'support_person_id' => null,
            'start_date' => now()->subDays(7)->toDateString(),
            'end_date' => null,
            'assignment_type' => 'home_care',
            'status' => 'active',
            'is_primary' => true,
        ]);

        $file = UploadedFile::fake()->create('invoice.pdf', 120, 'application/pdf');

        $response = $this->actingAs($user)->post(route('portal.worker.invoices.store'), [
            'participant_id' => $participant->id,
            'invoice_number' => 'INV-2001',
            'invoice_date' => '2026-06-15',
            'service_date' => '2026-06-14',
            'due_date' => '2026-06-30',
            'amount' => 220.00,
            'notes' => 'Invoice for support services delivered.',
            'attachment' => $file,
        ]);

        $response->assertRedirect(route('portal.worker.invoices'));
        $response->assertSessionHas('status', 'Invoice submitted successfully.');

        $this->assertDatabaseHas('invoices', [
            'participant_id' => $participant->id,
            'worker_id' => $worker->id,
            'invoice_number' => 'INV-2001',
            'status' => 'submitted',
            'amount_cents' => 22000,
        ]);

        $invoice = Invoice::first();
        $this->assertNotNull($invoice);
        Storage::disk('local')->assertExists($invoice->attachment_path);
    }
}
