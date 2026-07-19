<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$budgetService = new App\Services\BudgetService;
$period = $budgetService->getQuarterPeriodForDate(now());
echo "period=" . $period['quarter_start_date'] . ".." . $period['quarter_end_date'] . PHP_EOL;

$budgets = App\Models\Budget::whereDate('quarter_start_date', $period['quarter_start_date'])
    ->whereDate('quarter_end_date', $period['quarter_end_date'])
    ->get();

echo "budgets=" . $budgets->count() . PHP_EOL;
foreach ($budgets as $budget) {
    $metrics = $budgetService->getBudgetMetrics($budget);
    echo "budget={$budget->id} participant={$budget->participant_id} committed={$metrics['committed']} approved={$metrics['approved']} paid={$metrics['paid']} available={$metrics['total_available']}\n";
}

echo "--- invoices ---\n";
$invoices = App\Models\Invoice::whereDate('invoice_date', '>=', $period['quarter_start_date'])
    ->whereDate('invoice_date', '<=', $period['quarter_end_date'])
    ->get();
foreach ($invoices as $invoice) {
    echo "invoice={$invoice->id} participant={$invoice->participant_id} status={$invoice->status} amount={$invoice->amount_cents} committed={$invoice->committed_amount_cents} date={$invoice->invoice_date}\n";
}
