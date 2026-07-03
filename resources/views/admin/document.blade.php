@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>{{ $document->title }}</h2>
            <p class="text-muted mb-0">Review document metadata and signature history.</p>
        </div>
        <div>
            <a href="{{ route('portal.admin.documents') }}" class="btn btn-outline-secondary">Back to forms</a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h5>Document details</h5>
                    <dl class="row">
                        <dt class="col-sm-4">Title</dt>
                        <dd class="col-sm-8">{{ $document->title }}</dd>

                        <dt class="col-sm-4">Type</dt>
                        <dd class="col-sm-8">{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</dd>

                        <dt class="col-sm-4">Owner</dt>
                        <dd class="col-sm-8">{{ $document->owner_label }}</dd>

                        <dt class="col-sm-4">Expires</dt>
                        <dd class="col-sm-8">
                            @if($document->expires_at)
                                <span>{{ $document->expires_at->format('Y-m-d') }}</span>
                                @if($document->isExpired())
                                    <span class="badge bg-danger ms-1">Expired</span>
                                @endif
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </dd>

                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            <span class="badge bg-{{ $document->status === 'signed' ? 'success' : 'secondary' }}">
                                {{ ucfirst($document->status) }}
                            </span>
                        </dd>

                        <dt class="col-sm-4">Current version</dt>
                        <dd class="col-sm-8">{{ optional($document->latestVersion)->version_number ?? 1 }}</dd>

                        <dt class="col-sm-4">Uploaded</dt>
                        <dd class="col-sm-8">{{ $document->created_at->format('Y-m-d H:i') }}</dd>

                        <dt class="col-sm-4">Uploaded by</dt>
                        <dd class="col-sm-8">{{ optional($document->uploader)->name ?? 'Unknown' }}</dd>

                        <dt class="col-sm-4">Agreement</dt>
                        <dd class="col-sm-8">
                            @if(isset($document->metadata['agreement_name']))
                                {{ $document->metadata['agreement_name'] }}
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </dd>

                        @if(isset($document->metadata['agreement_name']))
                            <dt class="col-sm-4">Certificate</dt>
                            <dd class="col-sm-8">
                                @if($document->signatures->first()?->certificate_path)
                                    <a href="{{ route('portal.admin.documents.signatures.certificate.download', [$document, $document->signatures->first()]) }}" class="btn btn-sm btn-outline-success" target="_blank">Download certificate</a>
                                @else
                                    <span class="text-muted">Not available</span>
                                @endif
                            </dd>
                        @endif
                    </dl>
                </div>
                <div class="col-md-6 text-end">
                    <a href="{{ route('portal.admin.documents.preview', $document) }}" class="btn btn-outline-primary me-2">Preview</a>
                    <a href="{{ route('portal.admin.documents.download', $document) }}" class="btn btn-primary">Download document</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-3">Version history</h5>
            @if($document->versions->isEmpty())
                <p class="text-muted">No version history is available for this document.</p>
            @else
                <div class="list-group mb-3">
                    @foreach($document->versions->sortByDesc('version_number') as $version)
                        <div class="list-group-item d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <strong>Version {{ $version->version_number }}</strong>
                                <div class="small text-muted">Uploaded {{ $version->created_at->format('Y-m-d H:i') }} by {{ optional($version->uploadedBy)->name ?? 'Unknown' }}</div>
                                @if($version->notes)
                                    <div class="small text-muted">Notes: {{ $version->notes }}</div>
                                @endif
                            </div>
                            <div class="text-end">
                                <span class="badge bg-secondary d-block mb-2">{{ strtoupper($version->mime_type) }}</span>
                                <a href="{{ route('portal.admin.documents.versions.download', [$document, $version]) }}" class="btn btn-sm btn-outline-secondary">Download v{{ $version->version_number }}</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('portal.admin.documents.versions.store', $document) }}" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="file" class="form-label">Upload new version</label>
                    <input id="file" name="file" type="file" class="form-control" required>
                    <div class="form-text">Accepted formats: PDF, JPG, PNG, DOC, DOCX, XLS, XLSX, CSV.</div>
                </div>
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes (optional)</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Upload new version</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h5 class="mb-3">Signature history</h5>
            @if($document->signatures->isEmpty())
                <p class="text-muted">No signatures have been recorded for this document yet.</p>
            @else
                <div class="list-group">
                    @foreach($document->signatures as $signature)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ optional($signature->signedBy)->name ?? 'Unknown signer' }}</strong>
                                <div class="small text-muted">{{ ucfirst(str_replace('_', ' ', $signature->signature_method)) }} • {{ $signature->signed_at->format('Y-m-d H:i') }}</div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if($signature->signature_path)
                                    <a href="{{ route('portal.admin.documents.signatures.download', [$document, $signature]) }}" class="btn btn-sm btn-outline-primary" target="_blank">View signature</a>
                                @endif
                                @if($signature->certificate_path)
                                    <a href="{{ route('portal.admin.documents.signatures.certificate.download', [$document, $signature]) }}" class="btn btn-sm btn-outline-success" target="_blank">Download certificate</a>
                                @endif
                                <span class="badge bg-secondary">Signed</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
