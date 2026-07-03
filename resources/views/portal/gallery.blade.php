@extends('layouts.portal')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Shared media gallery</h2>
            <p class="text-muted">Browse all uploaded documents, images, and files available in the portal.</p>
        </div>
        <a href="{{ auth()->user()->role === 'worker' ? route('portal.worker.documents.upload') : route('portal.participant.documents.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-folder2-open"></i> My Documents
        </a>
    </div>

    @if($documents->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="bi bi-collection-play fs-1 mb-3"></i>
            <p class="mb-0">No shared media has been uploaded yet.</p>
        </div>
    @else
        <div class="row g-4">
            @foreach($documents as $document)
                <div class="col-12 col-sm-6 col-lg-4">
                    <div class="card portal-card h-100 shadow-sm">
                        <div class="ratio ratio-4x3 overflow-hidden rounded-top">
                            @if(str_starts_with($document->mime_type, 'image/'))
                                <img src="{{ route('portal.gallery.preview', $document) }}" alt="{{ $document->title }}" class="object-fit-cover w-100 h-100">
                            @elseif($document->mime_type === 'application/pdf')
                                <div class="d-flex align-items-center justify-content-center h-100 bg-secondary bg-opacity-10">
                                    <i class="bi bi-file-earmark-pdf-fill fs-1 text-danger"></i>
                                </div>
                            @else
                                <div class="d-flex align-items-center justify-content-center h-100 bg-secondary bg-opacity-10">
                                    <i class="bi bi-file-earmark-fill fs-1 text-primary"></i>
                                </div>
                            @endif
                        </div>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-2 text-truncate">{{ $document->title }}</h5>
                            <p class="card-text text-muted mb-3">{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</p>
                            <div class="mt-auto d-flex flex-wrap gap-2">
                                <a href="{{ route('portal.gallery.preview', $document) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> Preview
                                </a>
                                <a href="{{ route('portal.gallery.download', $document) }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-download"></i> Download
                                </a>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-0 pt-0">
                            <div class="d-flex justify-content-between text-muted small">
                                <span>{{ $document->created_at->format('M j, Y') }}</span>
                                <span>{{ $document->owner_label }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $documents->links() }}
        </div>
    @endif
@endsection