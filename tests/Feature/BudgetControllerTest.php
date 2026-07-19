<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Invoice;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BudgetControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_view_budget()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $participant = Participant::factory()->create();

        $this->actingAs($user);

        $start = now()->startOfQuarter()->toDateString();
        $end = now()->endOfQuarter()->toDateString();

        if (Schema::hasColumn('budgets', 'quarter_start')) {
            $data = [
                'participant_id' => $participant->id,
                'quarter_start' => $start,
                'quarter_end' => $end,
                'opening_budget' => 1500.00,
                'carry_over' => 100.00,
            ];
        } else {
            $data = [
                'participant_id' => $participant->id,
                'quarter_start_date' => $start,
                'quarter_end_date' => $end,
                'opening_budget' => 1500.00,
                'carry_over' => 100.00,
            ];
        }

        $resp = $this->post(route('budgets.store'), $data);
        $resp->assertRedirect();
        $resp->assertSessionHasNoErrors();

        $budget = Budget::first();
        $this->assertNotNull($budget);
        $this->assertEquals(1500.00, (float) $budget->opening_budget);
        $this->assertEquals($participant->id, $budget->participant_id);

        $show = $this->get(route('budgets.show', $budget));
        $show->assertStatus(200);
        $show->assertSee('Total Available');
    }

    public function test_admin_can_edit_budget()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $participant = Participant::factory()->create();
        $budget = Budget::create([
            'participant_id' => $participant->id,
            'quarter_start_date' => now()->startOfQuarter()->toDateString(),
            'quarter_end_date' => now()->endOfQuarter()->toDateString(),
            'opening_budget' => 1000.00,
            'carry_over' => 50.00,
        ]);

        $this->actingAs($user);

        $edit = $this->get(route('budgets.edit', $budget));
        $edit->assertStatus(200);
        $edit->assertSee('Edit Budget');

        $resp = $this->put(route('budgets.update', $budget), [
            'opening_budget' => 2200.00,
            'carry_over' => 120.00,
        ]);

        $resp->assertRedirect(route('budgets.show', $budget));
        $resp->assertSessionHas('status', 'Budget updated successfully.');

        $budget->refresh();
        $this->assertEquals(2200.00, (float) $budget->opening_budget);
        $this->assertEquals(120.00, (float) $budget->carry_over);
    }

    public function test_admin_budget_list_view_opens_budget_page()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $participant = Participant::factory()->create();
        $budget = Budget::create([
            'participant_id' => $participant->id,
            'quarter_start_date' => now()->startOfQuarter()->toDateString(),
            'quarter_end_date' => now()->endOfQuarter()->toDateString(),
            'opening_budget' => 1000.00,
            'carry_over' => 50.00,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('portal.admin.budgets'));
        $response->assertStatus(200);
        $response->assertSee(route('budgets.show', $budget));
    }

    public function test_admin_budget_summary_uses_committed_metrics_from_invoices()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $participant = Participant::factory()->create();

        $budget = Budget::create([
            'participant_id' => $participant->id,
            'quarter_start_date' => now()->startOfQuarter()->toDateString(),
            'quarter_end_date' => now()->endOfQuarter()->toDateString(),
            'opening_balance_cents' => 100000,
            'carry_over_cents' => 0,
            'committed_cents' => 0,
            'approved_spend_cents' => 0,
            'paid_spend_cents' => 0,
        ]);

        Invoice::create([
            'participant_id' => $participant->id,
            'invoice_number' => 'INV-TEST-1',
            'status' => 'approved',
            'amount_cents' => 500000,
            'committed_amount_cents' => 500000,
            'invoice_date' => now()->toDateString(),
            'service_date' => now()->subDay()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'invoice_file_path' => 'invoices/invoice-test.pdf',
            'attachment_path' => 'invoices/invoice-test.pdf',
            'attachment_disk' => 'local',
            'attachment_mime_type' => 'application/pdf',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('portal.admin.budgets'));
        $response->assertStatus(200);
        $response->assertSee('$5,000.00');
        $response->assertSee('$1,000.00');

        $metrics = app(\App\Services\BudgetService::class)->getBudgetMetrics($budget);
        $this->assertSame(100000, $metrics['total_available']);
        $this->assertSame(500000, $metrics['committed']);
        $this->assertSame(-400000, $metrics['remaining']);
    }

    public function test_budget_detail_page_uses_same_remaining_formula_as_admin_summary()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $participant = Participant::factory()->create();

        $budget = Budget::create([
            'participant_id' => $participant->id,
            'quarter_start_date' => now()->startOfQuarter()->toDateString(),
            'quarter_end_date' => now()->endOfQuarter()->toDateString(),
            'opening_balance_cents' => 100000,
            'carry_over_cents' => 0,
            'committed_cents' => 0,
            'approved_spend_cents' => 0,
            'paid_spend_cents' => 0,
        ]);

        Invoice::create([
            'participant_id' => $participant->id,
            'invoice_number' => 'INV-DETAIL-1',
            'status' => 'approved',
            'amount_cents' => 500000,
            'committed_amount_cents' => 500000,
            'invoice_date' => now()->toDateString(),
            'service_date' => now()->subDay()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'invoice_file_path' => 'invoices/invoice-detail.pdf',
            'attachment_path' => 'invoices/invoice-detail.pdf',
            'attachment_disk' => 'local',
            'attachment_mime_type' => 'application/pdf',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('budgets.show', $budget));
        $response->assertStatus(200);
        $response->assertSee('$-4,000.00');
    }

    public function test_admin_can_delete_budget()
    {
        $user = User::factory()->create(['role' => 'admin']);
        $participant = Participant::factory()->create();
        $budget = Budget::create([
            'participant_id' => $participant->id,
            'quarter_start_date' => now()->startOfQuarter()->toDateString(),
            'quarter_end_date' => now()->endOfQuarter()->toDateString(),
            'opening_budget' => 1000.00,
            'carry_over' => 50.00,
        ]);

        $this->actingAs($user);

        $resp = $this->delete(route('budgets.destroy', $budget));
        $resp->assertRedirect(route('budgets.index'));
        $resp->assertSessionHas('status', 'Budget deleted successfully.');

        $this->assertDatabaseMissing('budgets', ['id' => $budget->id]);
    }
}
