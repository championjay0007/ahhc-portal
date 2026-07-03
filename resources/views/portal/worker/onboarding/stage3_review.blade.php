@extends('layouts.auth')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <h2>Worker Onboarding - Stage 3: Document Review</h2>
                <span class="badge bg-info mt-2 mt-md-0">Stage 3 of 6</span>
            </div>

            <div class="alert alert-info">
                <h6 class="alert-heading">AHHC is reviewing your documents</h6>
                <p class="mb-0">We're currently reviewing the compliance documents you submitted. You'll be notified once the review is complete and you can proceed to the next stage.</p>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Your Submitted Documents</h6>
                        </div>
                        <div class="card-body">
                            @if ($documents->isEmpty())
                                <div class="alert alert-warning">
                                    No documents submitted yet.
                                </div>
                            @else
                                <div class="list-group">
                                    @foreach ($documents as $doc)
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1">
                                                        <i class="bi bi-file-earmark-text"></i>
                                                        {{ $doc->document_type }}
                                                    </h6>
                                                    <p class="mb-1 small text-muted">{{ basename($doc->document_path ?? '') }}</p>
                                                    <small class="text-muted">
                                                        Submitted: {{ $doc->created_at->format('M d, Y') }}
                                                    </small>

                                                    @if ($doc->status === 'rejected' && $doc->rejection_reason)
                                                        <div class="mt-2 alert alert-danger py-2">
                                                            <strong>Reviewer Comment:</strong>
                                                            <div>{{ $doc->rejection_reason }}</div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="text-end">
                                                    <span class="badge bg-{{ $doc->status === 'submitted' ? 'warning' : ($doc->status === 'active' ? 'success' : ($doc->status === 'rejected' ? 'danger' : 'secondary')) }} mb-2">
                                                        {{ ucfirst($doc->status) }}
                                                    </span>
                                                    <div class="d-grid gap-2">
                                                        @if ($doc->document_path)
                                                            <a href="{{ route('worker.onboarding.document.preview', ['token' => $token, 'document' => $doc->id]) }}" class="btn btn-sm btn-outline-primary">Preview</a>
                                                            <a href="{{ route('worker.onboarding.document.download', ['token' => $token, 'document' => $doc->id]) }}" class="btn btn-sm btn-outline-secondary">Download</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if ($canProceed)
                                <div class="mt-4">
                                    <form method="POST" action="{{ route('worker.onboarding.stage3.proceed', ['token' => $token]) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-lg w-100">Proceed to Stage 4: Sign Declarations</button>
                                    </form>
                                </div>
                            @else
                                <div class="alert alert-warning mt-4">
                                    You must upload all required compliance documents before proceeding to Stage 4.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">What's Next?</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-3">Once Allegiance Heart &amp; Home Care completes the review of your documents, you'll receive an email notification and can proceed to sign the required declarations.</p>
                            <p class="small"><strong>Estimated timeframe:</strong> 3-5 business days</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
