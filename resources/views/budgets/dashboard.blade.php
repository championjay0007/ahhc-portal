@extends('layouts.portal')

@section('content')
<div class="container py-4">
    <h1>Your Budget Dashboard</h1>
    <div class="row">
        @foreach($budgets as $budget)
        <div class="col-md-4 mb-3">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ $budget->quarter_start->format('Y-m-d') }} → {{ $budget->quarter_end->format('Y-m-d') }}</h5>
                    <p class="mb-1">Available: ${{ number_format($budget->total_available,2) }}</p>
                    <p class="mb-1">Committed: ${{ number_format($budget->committed_funds,2) }}</p>
                    <p class="mb-1">Remaining: ${{ number_format($budget->remaining_balance,2) }}</p>
                    @if(!empty($budget->alerts))
                        <div class="mb-2">
                            @foreach($budget->alerts as $alert)
                                <span class="badge bg-warning text-dark">{{ $alert }}</span>
                            @endforeach
                        </div>
                    @endif
                    <a href="{{ route('budgets.show', $budget) }}" class="btn btn-sm btn-primary">View</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
