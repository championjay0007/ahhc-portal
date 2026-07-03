@extends('layouts.portal')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Pending Signatures</h2>
            <p class="text-muted">Documents that require your e-signature.</p>
        </div>
        <div>
            <a href="{{ route('portal.participant.documents.index') }}" class="btn btn-outline-secondary">View all documents</a>
        </div>
    </div>

    @if($signatureRequests->isEmpty())
        <div class="card portal-card p-5 text-center">
            <i class="bi bi-check-circle-fill" style="font-size: 3rem; color: #1699A1; margin-bottom: 1rem;"></i>
            <h5 class="mb-2">All caught up!</h5>
            <p class="text-muted mb-0">You have no documents waiting for your signature. Great job staying on top of things.</p>
        </div>
    @else
        <div class="row g-3">
            @foreach($signatureRequests as $request)
                <div class="col-md-6 col-lg-4">
                    <div class="card portal-card dashboard-stat-card h-100 p-4">
                        <div class="d-flex align-items-start justify-content-between mb-3">
                            <div class="icon-circle"><i class="bi bi-file-text-fill fs-4"></i></div>
                            <span class="badge bg-warning text-dark">{{ ucfirst($request->status) }}</span>
                        </div>
                        <h6 class="text-muted mb-2 text-truncate" title="{{ $request->document->title }}">{{ $request->document->title }}</h6>
                        <h5 class="fw-bold mb-2">{{ ucfirst(str_replace('_', ' ', $request->document->document_type)) }}</h5>
                        <p class="text-muted small mb-3">
                            Assigned {{ $request->assigned_at?->diffForHumans() ?? 'recently' }}
                        </p>
                        <div class="mt-auto">
                            <a href="{{ route('portal.participant.documents.show', $request->document) }}" class="btn btn-accent btn-sm w-100">
                                <i class="bi bi-pen-fill me-2"></i>Review & Sign
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
@endsection
