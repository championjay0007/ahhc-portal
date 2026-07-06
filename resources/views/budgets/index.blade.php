@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.portal')

@section('content')
<div class="container py-4">
    <h1>Budgets</h1>
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <a href="{{ route('budgets.create') }}" class="btn btn-primary mb-3">Create Budget</a>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Participant</th>
                <th>Quarter</th>
                <th>Total Available</th>
                <th>Committed</th>
                <th>Remaining</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($budgets as $budget)
            <tr>
                <td>{{ optional($budget->participant)->name ?? $budget->participant_id }}</td>
                <td>{{ $budget->quarter_start->format('Y-m-d') }} → {{ $budget->quarter_end->format('Y-m-d') }}</td>
                <td>${{ number_format($budget->total_available,2) }}</td>
                <td>${{ number_format($budget->committed_funds,2) }}</td>
                <td>${{ number_format($budget->remaining_balance,2) }}</td>
                <td><a href="{{ route('budgets.show', $budget) }}" class="btn btn-sm btn-outline-primary">View</a></td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $budgets->links() }}
</div>
@endsection
