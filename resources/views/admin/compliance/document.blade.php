@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h3 class="mb-1">Compliance Document</h3>
            <p class="text-muted mb-0">{{ $document->document_type }} for {{ $document->worker->first_name }} {{ $document->worker->last_name }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('portal.admin.compliance.workers.show', $document->worker) }}" class="btn btn-sm btn-outline-secondary">Back to Worker</a>
            <a href="{{ route('portal.admin.compliance.dashboard') }}" class="btn btn-sm btn-outline-primary">Dashboard</a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card p-4 mb-4">
                <h5 class="mb-3">Document Summary</h5>
                <p class="mb-2"><strong>Worker:</strong> {{ $document->worker->first_name }} {{ $document->worker->last_name }}</p>
                <p class="mb-2"><strong>Type:</strong> {{ $document->document_type }}</p>
                <p class="mb-2"><strong>Status:</strong> {{ $document->status }}</p>
                <p class="mb-2"><strong>Issue Date:</strong> {{ optional($document->issue_date)->format('Y-m-d') ?? 'Not set' }}</p>
                <p class="mb-2"><strong>Expiry Date:</strong> {{ optional($document->expiry_date)->format('Y-m-d') ?? 'Not set' }}</p>
                <p class="mb-2"><strong>Verified by:</strong> {{ optional($document->verifiedBy)->name ?? 'Not verified' }}</p>
                <p class="mb-2"><strong>Last notified:</strong> {{ optional($document->last_notified_at)->format('Y-m-d H:i') ?? 'None' }}</p>
                <p class="mb-2"><strong>Rejection reason:</strong> {{ $document->rejection_reason ?? 'None' }}</p>
                @if($document->document_path)
                    <a href="{{ route('portal.admin.compliance.documents.download', $document) }}" class="btn btn-sm btn-outline-secondary">Download file</a>
                @endif
            </div>

            <div class="card p-4 mb-4">
                <h5 class="mb-3">Update Document</h5>
                <form method="POST" action="{{ route('portal.admin.compliance.documents.update', $document) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Issue Date</label>
                        <input type="date" name="issue_date" class="form-control" value="{{ old('issue_date', optional($document->issue_date)->format('Y-m-d')) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Expiry Date</label>
                        <input type="date" name="expiry_date" class="form-control" value="{{ old('expiry_date', optional($document->expiry_date)->format('Y-m-d')) }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3">{{ old('notes', $document->notes) }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary">Save changes</button>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-4 mb-4">
                <h5 class="mb-3">Upload / Replace File</h5>
                <form method="POST" action="{{ route('portal.admin.compliance.documents.upload', $document) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Document file</label>
                        <input type="file" name="file" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-sm btn-secondary">Upload file</button>
                </form>
            </div>

            <div class="card p-4 mb-4">
                <h5 class="mb-3">Verification Actions</h5>
                <form method="POST" action="{{ route('portal.admin.compliance.documents.verify', $document) }}" class="mb-3">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success">Mark as Verified</button>
                </form>

                <form method="POST" action="{{ route('portal.admin.compliance.documents.reject', $document) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Rejection reason</label>
                        <textarea name="reason" class="form-control" rows="3" required>{{ old('reason') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-sm btn-danger">Reject document</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
