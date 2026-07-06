<?php

namespace App\Console\Commands;

use App\Models\Budget;
use App\Models\BudgetTransaction;
use App\Models\Invoice;
use App\Services\BudgetService;
use Illuminate\Console\Command;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Support\Facades\Schema;

class NormalizeBudgets extends Command
{
    protected $signature = 'budgets:normalize {--apply : Actually write changes to the database} {--participant= : Optional participant id to restrict}';

    protected $description = 'Normalize budget cents columns and recompute totals/remaining from transactions and invoices.';

    public function handle()
    {
        $apply = $this->option('apply');
        $participantId = $this->option('participant');

        $query = Budget::query();
        if ($participantId) {
            $query->where('participant_id', $participantId);
        }

        $service = new BudgetService();

        $this->info(($apply ? 'Applying' : 'Dry-run').' normalization across budgets');

        $count = 0;
        foreach ($query->with('transactions')->cursor() as $budget) {
            $count++;
            $opening = Schema::hasColumn('budgets', 'opening_balance_cents')
                ? ($budget->opening_balance_cents ?? 0)
                : (int) round(($budget->opening_budget ?? 0) * 100);

            $carry = Schema::hasColumn('budgets', 'carry_over_cents')
                ? ($budget->carry_over_cents ?? 0)
                : (int) round(($budget->carry_over ?? 0) * 100);

            // Determine committed/approved/paid from transactions when available, otherwise fall back to invoices
            $txCount = $budget->transactions()->count();
            if ($txCount > 0) {
                $committed = (int) ($budget->transactions()->where('type', BudgetTransaction::TYPE_COMMIT)->sum('amount_cents') ?? 0);
                $approved = (int) ($budget->transactions()->where('type', BudgetTransaction::TYPE_APPROVED)->sum('amount_cents') ?? 0);
                $paid = (int) ($budget->transactions()->where('type', BudgetTransaction::TYPE_PAID)->sum('amount_cents') ?? 0);
            } else {
                // Fallback to invoices in quarter
                $approved = (int) Invoice::where('participant_id', $budget->participant_id)
                    ->where('status', 'approved')
                    ->whereBetween('invoice_date', [$budget->quarter_start_date, $budget->quarter_end_date])
                    ->sum('amount_cents');
                $paid = (int) Invoice::where('participant_id', $budget->participant_id)
                    ->where('status', 'paid')
                    ->whereBetween('invoice_date', [$budget->quarter_start_date, $budget->quarter_end_date])
                    ->sum('amount_cents');
                $committed = 0;
            }

            $totalAvailable = $opening + $carry;
            $used = $committed + $approved + $paid;
            $remaining = $totalAvailable - $used;

            $this->line("Budget {$budget->id} (participant {$budget->participant_id}): total=".($totalAvailable/100).' used='.( $used/100 ).' remaining='.( $remaining/100 ));

            if ($apply) {
                db()->transaction(function () use ($budget, $opening, $carry, $committed, $approved, $paid, $totalAvailable, $remaining) {
                    if (Schema::hasColumn('budgets', 'opening_balance_cents')) {
                        $budget->opening_balance_cents = (int) $opening;
                    } else {
                        $budget->opening_budget = number_format($opening / 100, 2, '.', '');
                    }

                    if (Schema::hasColumn('budgets', 'carry_over_cents')) {
                        $budget->carry_over_cents = (int) $carry;
                    } else {
                        $budget->carry_over = number_format($carry / 100, 2, '.', '');
                    }

                    if (Schema::hasColumn('budgets', 'committed_cents')) {
                        $budget->committed_cents = (int) $committed;
                    } else {
                        $budget->committed_funds = number_format($committed / 100, 2, '.', '');
                    }

                    if (Schema::hasColumn('budgets', 'approved_spend_cents')) {
                        $budget->approved_spend_cents = (int) $approved;
                    } else {
                        $budget->approved_spend = number_format($approved / 100, 2, '.', '');
                    }

                    if (Schema::hasColumn('budgets', 'paid_spend_cents')) {
                        $budget->paid_spend_cents = (int) $paid;
                    } else {
                        $budget->paid_spend = number_format($paid / 100, 2, '.', '');
                    }

                    // store aggregates
                    if (Schema::hasColumn('budgets', 'total_available')) {
                        $budget->total_available = number_format($totalAvailable / 100, 2, '.', '');
                    }
                    if (Schema::hasColumn('budgets', 'remaining_balance')) {
                        $budget->remaining_balance = number_format($remaining / 100, 2, '.', '');
                    }

                    $budget->save();
                });
            }
        }

        $this->info('Processed '.$count.' budgets.');

        return 0;
    }
}
