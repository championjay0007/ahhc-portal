<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Invoice;
use App\Models\Participant;
use App\Models\PortalNotification;
use App\Models\PreApprovalRequest;
use App\Models\User;
use App\Services\BudgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminInvoiceWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_submitted_invoice_and_reconcile_budget(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin-invoice@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $participantUser = User::create([
            'name' => 'Participant Invoice',
            'email' => 'participant-invoice@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participant_number' => 'P-2001',
            'first_name' => 'Participant',
            'last_name' => 'Invoice',
            'status' => 'active',
        ]);

        $preApproval = PreApprovalRequest::create([
            'participant_id' => $participant->id,
            'request_number' => 'PA-2001',
            'service_type' => 'support',
            'purpose' => 'Hourly support',
            'requested_amount_cents' => 5000,
            'status' => 'approved',
            'submitted_at' => now(),
            'approved_at' => now(),
            'approved_by_id' => $admin->id,
        ]);

        $budgetService = new BudgetService;
        $budgetPeriod = $budgetService->getQuarterPeriodForDate(now());
        Budget::create([
            'participant_id' => $participant->id,
            'quarter_start_date' => $budgetPeriod['quarter_start_date'],
            'quarter_end_date' => $budgetPeriod['quarter_end_date'],
            'opening_balance_cents' => 5000,
            'carry_over_cents' => 0,
            'committed_cents' => 5000,
            'approved_spend_cents' => 0,
            'paid_spend_cents' => 0,
        ]);

        $invoice = Invoice::create([
            'participant_id' => $participant->id,
            'pre_approval_id' => $preApproval->id,
            'invoice_number' => 'INV-2001',
            'status' => 'submitted',
            'amount_cents' => 5000,
            'invoice_date' => now()->toDateString(),
            'service_date' => now()->subDay()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'invoice_file_path' => 'invoices/invoice-2001.pdf',
            'attachment_path' => 'invoices/invoice-2001.pdf',
            'attachment_disk' => 'local',
            'attachment_mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($admin)->post(route('portal.admin.invoices.review', $invoice));

        $response->assertSessionHas('status', 'Invoice approved.');
        $invoice->refresh();

        $this->assertSame('approved', $invoice->status);
        $this->assertNotNull($invoice->approved_at);
        $this->assertSame($admin->id, $invoice->approved_by_id);

        $budget = $budgetService->getOrCreateBudgetForParticipantQuarter($participant, now());
        $this->assertSame(0, $budget->committed_cents);
        $this->assertSame(5000, $budget->approved_spend_cents);
    }

    public function test_admin_can_pay_approved_invoice_and_update_budget(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin-pay-invoice@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $participantUser = User::create([
            'name' => 'Participant Invoice',
            'email' => 'participant-pay-invoice@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participant_number' => 'P-2002',
            'first_name' => 'Participant',
            'last_name' => 'Invoice',
            'status' => 'active',
        ]);

        $preApproval = PreApprovalRequest::create([
            'participant_id' => $participant->id,
            'request_number' => 'PA-2002',
            'service_type' => 'support',
            'purpose' => 'Daily support',
            'requested_amount_cents' => 5000,
            'status' => 'approved',
            'submitted_at' => now(),
            'approved_at' => now(),
            'approved_by_id' => $admin->id,
        ]);

        $budgetService = new BudgetService;
        $budgetPeriod = $budgetService->getQuarterPeriodForDate(now());
        Budget::create([
            'participant_id' => $participant->id,
            'quarter_start_date' => $budgetPeriod['quarter_start_date'],
            'quarter_end_date' => $budgetPeriod['quarter_end_date'],
            'opening_balance_cents' => 5000,
            'carry_over_cents' => 0,
            'committed_cents' => 0,
            'approved_spend_cents' => 5000,
            'paid_spend_cents' => 0,
        ]);

        $invoice = Invoice::create([
            'participant_id' => $participant->id,
            'pre_approval_id' => $preApproval->id,
            'invoice_number' => 'INV-2002',
            'status' => 'approved',
            'amount_cents' => 5000,
            'invoice_date' => now()->toDateString(),
            'service_date' => now()->subDay()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'invoice_file_path' => 'invoices/invoice-2002.pdf',
            'attachment_path' => 'invoices/invoice-2002.pdf',
            'attachment_disk' => 'local',
            'attachment_mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($admin)->post(route('portal.admin.invoices.pay', $invoice));

        $response->assertSessionHas('status', 'Invoice marked as paid.');
        $invoice->refresh();

        $this->assertSame('paid', $invoice->status);
        $this->assertNotNull($invoice->paid_at);

        $budget = $budgetService->getOrCreateBudgetForParticipantQuarter($participant, now());
        $this->assertSame(0, $budget->approved_spend_cents);
        $this->assertSame(5000, $budget->paid_spend_cents);
    }

    public function test_admin_approval_sends_participant_notification_and_updates_budget(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin-notify-invoice@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $participantUser = User::create([
            'name' => 'Participant Invoice',
            'email' => 'participant-notify-invoice@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participant_number' => 'P-2004',
            'first_name' => 'Participant',
            'last_name' => 'Invoice',
            'status' => 'active',
        ]);

        $preApproval = PreApprovalRequest::create([
            'participant_id' => $participant->id,
            'request_number' => 'PA-2004',
            'service_type' => 'support',
            'purpose' => 'Hourly support',
            'requested_amount_cents' => 5000,
            'status' => 'approved',
            'submitted_at' => now(),
            'approved_at' => now(),
            'approved_by_id' => $admin->id,
        ]);

        $budgetService = new BudgetService;
        $budgetPeriod = $budgetService->getQuarterPeriodForDate(now());
        Budget::create([
            'participant_id' => $participant->id,
            'quarter_start_date' => $budgetPeriod['quarter_start_date'],
            'quarter_end_date' => $budgetPeriod['quarter_end_date'],
            'opening_balance_cents' => 5000,
            'carry_over_cents' => 0,
            'committed_cents' => 5000,
            'approved_spend_cents' => 0,
            'paid_spend_cents' => 0,
        ]);

        $invoice = Invoice::create([
            'participant_id' => $participant->id,
            'pre_approval_id' => $preApproval->id,
            'invoice_number' => 'INV-2004',
            'status' => 'submitted',
            'amount_cents' => 5000,
            'invoice_date' => now()->toDateString(),
            'service_date' => now()->subDay()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'invoice_file_path' => 'invoices/invoice-2004.pdf',
            'attachment_path' => 'invoices/invoice-2004.pdf',
            'attachment_disk' => 'local',
            'attachment_mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($admin)->post(route('portal.admin.invoices.review', $invoice));

        $response->assertSessionHas('status', 'Invoice approved.');
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'approved']);
        $this->assertDatabaseHas('portal_notifications', ['participant_id' => $participant->id, 'type' => 'invoice_approved']);

        $budget = $budgetService->getOrCreateBudgetForParticipantQuarter($participant, now());
        $this->assertSame(0, $budget->committed_cents);
        $this->assertSame(5000, $budget->approved_spend_cents);
    }

    public function test_rejecting_approved_invoice_restores_budget_commitment(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin-reject-invoice@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $participantUser = User::create([
            'name' => 'Participant Invoice',
            'email' => 'participant-reject-invoice@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => 'Password123!',
            'password_changed_at' => now(),
        ]);

        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participant_number' => 'P-2003',
            'first_name' => 'Participant',
            'last_name' => 'Invoice',
            'status' => 'active',
        ]);

        $preApproval = PreApprovalRequest::create([
            'participant_id' => $participant->id,
            'request_number' => 'PA-2003',
            'service_type' => 'support',
            'purpose' => 'Hourly support',
            'requested_amount_cents' => 5000,
            'status' => 'approved',
            'submitted_at' => now(),
            'approved_at' => now(),
            'approved_by_id' => $admin->id,
        ]);

        $budgetService = new BudgetService;
        $budgetPeriod = $budgetService->getQuarterPeriodForDate(now());
        Budget::create([
            'participant_id' => $participant->id,
            'quarter_start_date' => $budgetPeriod['quarter_start_date'],
            'quarter_end_date' => $budgetPeriod['quarter_end_date'],
            'opening_balance_cents' => 5000,
            'carry_over_cents' => 0,
            'committed_cents' => 0,
            'approved_spend_cents' => 5000,
            'paid_spend_cents' => 0,
        ]);

        $invoice = Invoice::create([
            'participant_id' => $participant->id,
            'pre_approval_id' => $preApproval->id,
            'invoice_number' => 'INV-2003',
            'status' => 'approved',
            'amount_cents' => 5000,
            'invoice_date' => now()->toDateString(),
            'service_date' => now()->subDay()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'invoice_file_path' => 'invoices/invoice-2003.pdf',
            'attachment_path' => 'invoices/invoice-2003.pdf',
            'attachment_disk' => 'local',
            'attachment_mime_type' => 'application/pdf',
        ]);

        $response = $this->actingAs($admin)->post(route('portal.admin.invoices.reject', $invoice));

        $response->assertSessionHas('status', 'Invoice rejected.');
        $invoice->refresh();

        $this->assertSame('rejected', $invoice->status);

        $budget = $budgetService->getOrCreateBudgetForParticipantQuarter($participant, now());
        $this->assertSame(5000, $budget->committed_cents);
        $this->assertSame(0, $budget->approved_spend_cents);
    }
}
