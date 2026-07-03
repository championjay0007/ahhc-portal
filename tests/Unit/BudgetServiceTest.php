<?php

namespace Tests\Unit;

use App\Models\Budget;
use App\Services\BudgetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BudgetServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_apply_transaction_types_update_budget_correctly()
    {
        $service = $this->app->make(BudgetService::class);

        $start = now()->startOfQuarter()->toDateString();
        $end = now()->endOfQuarter()->toDateString();

        $payload = ['participant_id' => null];
        if (Schema::hasColumn('budgets', 'quarter_start')) {
            $payload['quarter_start'] = $start;
            $payload['quarter_end'] = $end;
        } else {
            $payload['quarter_start_date'] = $start;
            $payload['quarter_end_date'] = $end;
        }

        $budget = Budget::create(array_merge($payload, [
            'opening_budget' => 0,
            'carry_over' => 0,
            'total_available' => 0,
            'committed_funds' => 0,
            'pending_invoices' => 0,
            'approved_spend' => 0,
            'paid_spend' => 0,
            'remaining_balance' => 0,
        ]));

        // Opening balance
        $service->applyTransaction($budget, 'opening_balance', 1000.00);
        $budget->refresh();
        $this->assertEquals('1000.00', number_format($budget->opening_budget, 2, '.', ''));
        $this->assertEquals('1000.00', number_format($budget->total_available, 2, '.', ''));

        // Commit funds
        $service->applyTransaction($budget, 'commitment', 200.00);
        $budget->refresh();
        $this->assertEquals('200.00', number_format($budget->committed_funds, 2, '.', ''));
        $this->assertEquals('800.00', number_format($budget->remaining_balance, 2, '.', ''));

        // Invoice approved: should reduce committed and increase approved_spend
        $service->applyTransaction($budget, 'invoice_approved', 200.00);
        $budget->refresh();
        $this->assertEquals('0.00', number_format($budget->committed_funds, 2, '.', ''));
        $this->assertEquals('200.00', number_format($budget->approved_spend, 2, '.', ''));

        // Invoice paid: move approved -> paid
        $service->applyTransaction($budget, 'invoice_paid', 200.00);
        $budget->refresh();
        $this->assertEquals('0.00', number_format($budget->approved_spend, 2, '.', ''));
        $this->assertEquals('200.00', number_format($budget->paid_spend, 2, '.', ''));

        // Adjustment: increase carry_over
        $service->applyTransaction($budget, 'adjustment', 50.00, null, ['field' => 'carry_over']);
        $budget->refresh();
        $this->assertEquals('50.00', number_format($budget->carry_over, 2, '.', ''));
    }
}
