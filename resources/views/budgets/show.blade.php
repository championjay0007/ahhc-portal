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
                @can('update', $budget)
                    <a href="{{ route('budgets.edit', $budget) }}" class="btn btn-outline-secondary">Edit Budget</a>
                @endcan
                @can('delete', $budget)
                    <form method="POST" action="{{ route('budgets.destroy', $budget) }}" class="d-inline-block" onsubmit="return confirm('Delete this budget?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">Delete Budget</button>
                    </form>
                @endcan
            </p>
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
