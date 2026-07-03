<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Services\BudgetService;
use Illuminate\Support\Facades\Response;

class BudgetReportController extends Controller
{
    protected $service;

    public function __construct(BudgetService $service)
    {
        $this->service = $service;
        $this->middleware('auth');
    }

    public function exportCsv(Budget $budget)
    {
        $this->authorize('view', $budget);
        $csv = $this->service->exportCsv($budget);

        $name = 'budget-'.$budget->id.'-transactions.csv';

        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$name}\"",
        ]);
    }

    public function exportPdf(Budget $budget)
    {
        $this->authorize('view', $budget);

        // Use a simple blade view to render PDF. If barryvdh/laravel-dompdf is installed it will work.
        $html = view('budgets.reports.pdf', compact('budget'))->render();
        if (class_exists('PDF')) {
            $pdf = \PDF::loadHTML($html);

            return $pdf->download('budget-'.$budget->id.'.pdf');
        }

        return Response::make($html, 200, ['Content-Type' => 'text/html']);
    }
}
