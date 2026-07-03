@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-0">Forms & E-sign</h2>
            <p class="text-muted mb-0">Manage participant documents, monitor signature status, and download completed forms.</p>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <form class="d-flex" method="GET">
                <input type="search" name="search" class="form-control me-2" placeholder="Search documents, type or status" value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="submit">Search</button>
            </form>
            <a href="{{ route('portal.admin.documents.create') }}" class="btn btn-primary">Assign new form</a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            @if($documents->isEmpty())
                <div class="p-4 text-muted">No documents found.</div>
            @else
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Owner</th>
                                <th>Expires</th>
                                <th>Status</th>
                                <th>Onboarding</th>
                                <th>Agreement</th>
                                <th>Signatures</th>
                                <th>Uploaded</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $document)
                                <tr>
                                    <td>
                                        @if($document->onboarding_required)
                                            <span class="badge bg-primary">Required</span>
                                        @endif
                                    </td>
                                    <td>{{ $document->title }}</td>
                                    <td>{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</td>
                                    <td>{{ $document->owner_label }}</td>
                                    <td>
                                        @if($document->expires_at)
                                            <span class="text-muted">{{ $document->expires_at->format('Y-m-d') }}</span>
                                            @if($document->isExpired())
                                                <span class="badge bg-danger ms-1">Expired</span>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $document->status === 'signed' ? 'success' : 'secondary' }}">
                                            {{ ucfirst($document->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if(isset($document->metadata['agreement_name']))
                                            <span class="badge bg-info text-dark">{{ $document->metadata['agreement_name'] }}</span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $document->signatures->count() }}</td>
                                    <td>{{ $document->created_at->format('Y-m-d') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('portal.admin.documents.preview', $document) }}" class="btn btn-sm btn-outline-primary">Preview</a>
                                        <a href="{{ route('portal.admin.documents.show', $document) }}" class="btn btn-sm btn-primary">View</a>
                                        <a href="{{ route('portal.admin.documents.download', $document) }}" class="btn btn-sm btn-outline-secondary">Download</a>
                                        @if(isset($document->metadata['agreement_name']) && $document->signatures->first()?->certificate_path)
                                            <a href="{{ route('portal.admin.documents.signatures.certificate.download', [$document, $document->signatures->first()]) }}" class="btn btn-sm btn-outline-success">Certificate</a>
                                        @endif
                                        <form method="POST" action="{{ route('portal.admin.documents.toggle_onboarding', $document) }}" class="d-inline ms-1">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-{{ $document->onboarding_required ? 'danger' : 'secondary' }}">
                                                {{ $document->onboarding_required ? 'Unassign' : 'Assign' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $documents->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
