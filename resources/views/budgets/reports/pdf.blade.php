@extends('layouts.portal')

@section('content')
<div class="container py-4">
    <h1>Budget Report - #{{ $budget->id }}</h1>
    <p>Quarter: {{ $budget->quarter_start->format('Y-m-d') }} → {{ $budget->quarter_end->format('Y-m-d') }}</p>
    <table class="table table-sm">
        <thead><tr><th>Date</th><th>Type</th><th>Category</th><th>Amount</th></tr></thead>
        <tbody>
            @foreach($budget->transactions as $t)
            <tr>
                <td>{{ $t->created_at->format('Y-m-d') }}</td>
                <td>{{ $t->type }}</td>
                <td>{{ optional($t->category)->name }}</td>
                <td>{{ number_format($t->amount,2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
