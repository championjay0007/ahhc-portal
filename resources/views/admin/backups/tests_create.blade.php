@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-bold mb-1">Record Disaster Recovery Test</h2>
            <p class="text-muted mb-0">Capture recovery exercise results and any issues found during testing.</p>
        </div>
        <a href="{{ route('portal.admin.backups.tests') }}" class="btn btn-outline-secondary">View Test History</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('portal.admin.backups.tests.store') }}" method="post">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Test date</label>
                        <input type="datetime-local" name="test_date" class="form-control" value="{{ old('test_date') }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            @foreach($statuses as $status)
                                <option value="{{ $status }}">{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Summary</label>
                        <textarea name="summary" class="form-control" rows="4">{{ old('summary') }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="4">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Save test record</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
