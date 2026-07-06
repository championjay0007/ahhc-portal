@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.portal')

@section('content')
<div class="container py-4">
    <h1>Budget for {{ optional($budget->participant)->name ?? $budget->participant_id }}</h1>
    <div class="row">
        <div class="col-md-6">
            @if(!empty($alerts))
            <div class="mb-3">
                <div class="alert alert-warning">
                    <h5 class="mb-2">Budget Alerts</h5>
                    <ul class="mb-0">
                        @foreach($alerts as $a)
                            <li>{{ $a }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endif
            <ul class="list-group mb-3">
                <li class="list-group-item">Quarter: {{ $budget->quarter_start->format('Y-m-d') }} → {{ $budget->quarter_end->format('Y-m-d') }}</li>
                <li class="list-group-item">Opening Budget: ${{ number_format($budget->opening_budget,2) }}</li>
                <li class="list-group-item">Carry Over: ${{ number_format($budget->carry_over,2) }}</li>
                <li class="list-group-item">Total Available: ${{ number_format($budget->total_available,2) }}</li>
                <li class="list-group-item">Committed: ${{ number_format($budget->committed_funds,2) }}</li>
                <li class="list-group-item">Used (approved + paid): ${{ number_format($budget->approved_spend + $budget->paid_spend,2) }}</li>
                <li class="list-group-item">Remaining: ${{ number_format($budget->remaining_balance,2) }}</li>
                <li class="list-group-item">
                    <strong>Remaining formula</strong>: Total Available − Committed − Used
                </li>
                <li class="list-group-item">Pending Invoices: ${{ number_format($budget->pending_invoices,2) }}</li>
                <li class="list-group-item">Approved Spend: ${{ number_format($budget->approved_spend,2) }}</li>
                <li class="list-group-item">Paid Spend: ${{ number_format($budget->paid_spend,2) }}</li>
            </ul>
            <p>
                <a href="{{ route('budgets.export.csv', $budget) }}" class="btn btn-outline-secondary">Export CSV</a>
                <a href="{{ route('budgets.export.pdf', $budget) }}" class="btn btn-outline-secondary">Export PDF</a>
            </p>
        </div>
        <div class="col-md-6">
            <h4>Record Transaction</h4>
            <form method="POST" action="{{ route('budgets.transactions.store', $budget) }}">
                @csrf
                <div class="mb-2">
                    <label>Type</label>
                    <select name="type" class="form-select">
                        <option value="opening_balance">Opening Balance</option>
                        <option value="carry_over">Carry Over</option>
                        <option value="commitment">Commitment</option>
                        <option value="invoice_pending">Invoice Pending</option>
                        <option value="invoice_approved">Invoice Approved</option>
                        <option value="invoice_paid">Invoice Paid</option>
                        <option value="release_commitment">Release Commitment</option>
                        <option value="adjustment">Adjustment</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label>Amount</label>
                    <input type="number" step="0.01" name="amount" class="form-control" required>
                </div>
                <div class="mb-2">
                    <label>Category (optional)</label>
                    <input type="number" name="category_id" class="form-control">
                </div>
                <div class="mb-2">
                    <label>Meta JSON (optional)</label>
                    <input type="text" name="meta[field]" class="form-control" placeholder="meta[field]=opening_budget">
                </div>
                <button class="btn btn-success">Apply</button>
            </form>
        </div>
    </div>

    <hr>
    <h3>Ledger</h3>
    <table class="table">
        <thead>
            <tr><th>Date</th><th>Type</th><th>Category</th><th>Amount</th><th>Meta</th></tr>
        </thead>
        <tbody>
            @foreach($budget->transactions as $t)
            <tr>
                <td>{{ $t->created_at->format('Y-m-d H:i') }}</td>
                <td>{{ $t->type }}</td>
                <td>{{ optional($t->category)->name }}</td>
                <td>${{ number_format($t->amount,2) }}</td>
                <td>{{ json_encode($t->meta) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
