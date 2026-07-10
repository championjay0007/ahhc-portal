@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h3 class="mb-1">Search results</h3>
            <p class="text-muted mb-0">Showing matches for "{{ $search }}".</p>
        </div>
        <div>
            <a href="{{ route('portal.admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">Back to dashboard</a>
        </div>
    </div>

    @if(empty($search))
        <div class="alert alert-info">Enter a search term in the admin topbar to search participants, workers, invoices, documents, enquiries, and incidents.</div>
    @endif

    @if(! empty($results))
        <div class="row g-4">
            @foreach($results as $result)
                <div class="col-12 col-lg-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-secondary">{{ $result['type'] }}</span>
                                <a href="{{ $result['url'] }}" class="btn btn-sm btn-outline-primary">View</a>
                            </div>
                            <h5 class="card-title">{{ $result['title'] }}</h5>
                            <p class="text-muted mb-1">{{ $result['subtitle'] }}</p>
                            <p class="text-muted small mb-0">{{ $result['description'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @elseif(! empty($search))
        <div class="alert alert-warning">No results found for "{{ $search }}".</div>
    @endif
</div>
@endsection
