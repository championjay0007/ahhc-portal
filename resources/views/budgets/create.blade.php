@extends('layouts.portal')

@section('content')
<div class="container py-4">
    <h1>Create Budget</h1>
    <form method="POST" action="{{ route('budgets.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Quarter Start</label>
            <input type="date" name="quarter_start" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Quarter End</label>
            <input type="date" name="quarter_end" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Opening Budget</label>
            <input type="number" step="0.01" name="opening_budget" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Carry Over</label>
            <input type="number" step="0.01" name="carry_over" class="form-control">
        </div>
        <button class="btn btn-primary">Create</button>
    </form>
</div>
@endsection
