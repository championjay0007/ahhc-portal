@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.portal')

@section('content')
<div class="container py-4">
    <h1>Edit Budget</h1>
    <p class="text-muted">Update the opening balance and carry-over for this budget.</p>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('budgets.update', $budget) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Opening Budget</label>
                    <input type="number" step="0.01" name="opening_budget" class="form-control" value="{{ old('opening_budget', $budget->opening_budget) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Carry Over</label>
                    <input type="number" step="0.01" name="carry_over" class="form-control" value="{{ old('carry_over', $budget->carry_over) }}">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('budgets.show', $budget) }}" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
