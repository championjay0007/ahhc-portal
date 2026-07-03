<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Services\BudgetService;
use Illuminate\Http\Request;

class BudgetTransactionController extends Controller
{
    protected $service;

    public function __construct(BudgetService $service)
    {
        $this->service = $service;
        $this->middleware('auth');
    }

    public function store(Request $request, Budget $budget)
    {
        $this->authorize('update', $budget);

        $data = $request->validate([
            'type' => 'required|string',
            'amount' => 'required|numeric',
            'category_id' => 'nullable|integer',
            'meta' => 'nullable|array',
        ]);

        $tx = $this->service->applyTransaction($budget, $data['type'], (float) $data['amount'], $data['category_id'] ?? null, $data['meta'] ?? [], $request->user()->id);

        $message = $tx ? 'Transaction recorded: '.$tx->type : 'Transaction recorded successfully.';

        return redirect()->route('budgets.show', $budget)->with('status', $message);
    }
}
