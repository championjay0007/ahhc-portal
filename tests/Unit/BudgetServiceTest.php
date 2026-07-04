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

    public function test_budget_alerts_are_returned_as_plain_strings()
    {
        $service = $this->app->make(BudgetService::class);

        $budget = Budget::create([
            'participant_id' => null,
            'quarter_start_date' => now()->startOfQuarter()->toDateString(),
            'quarter_end_date' => now()->endOfQuarter()->toDateString(),
            'opening_balance_cents' => 0,
            'carry_over_cents' => 0,
            'committed_cents' => 1000,
            'approved_spend_cents' => 0,
            'paid_spend_cents' => 0,
        ]);

        $alerts = $service->getBudgetAlerts($budget);

        $this->assertNotEmpty($alerts);
        $this->assertContainsOnly('string', $alerts);
    }

    public function test_fiscal_quarter_mapping_uses_jul_to_jun_periods()
    {
        $service = $this->app->make(BudgetService::class);

        $julyPeriod = $service->getQuarterPeriodForDate('2026-07-15');
        $this->assertSame('2026-07-01', $julyPeriod['quarter_start_date']);
        $this->assertSame('2026-09-30', $julyPeriod['quarter_end_date']);

        $octoberPeriod = $service->getQuarterPeriodForDate('2026-10-15');
        $this->assertSame('2026-10-01', $octoberPeriod['quarter_start_date']);
        $this->assertSame('2026-12-31', $octoberPeriod['quarter_end_date']);

        $januaryPeriod = $service->getQuarterPeriodForDate('2026-01-15');
        $this->assertSame('2026-01-01', $januaryPeriod['quarter_start_date']);
        $this->assertSame('2026-03-31', $januaryPeriod['quarter_end_date']);

        $aprilPeriod = $service->getQuarterPeriodForDate('2026-04-15');
        $this->assertSame('2026-04-01', $aprilPeriod['quarter_start_date']);
        $this->assertSame('2026-06-30', $aprilPeriod['quarter_end_date']);
    }

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
