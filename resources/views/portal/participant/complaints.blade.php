@extends('layouts.portal')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Complaints</h2>
            <p class="text-muted">Submit feedback, complaints or service issues confidentially.</p>
        </div>
    </div>

    <div class="card portal-card mb-4 p-4">
        <h5 class="mb-3">Submit a complaint</h5>
        <form method="POST" action="{{ route('portal.participant.complaints.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Category</label>
                    <input type="text" name="category" class="form-control" value="{{ old('category') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select" required>
                        <option value="low"{{ old('priority') === 'low' ? ' selected' : '' }}>Low</option>
                        <option value="medium"{{ old('priority') === 'medium' ? ' selected' : '' }}>Medium</option>
                        <option value="high"{{ old('priority') === 'high' ? ' selected' : '' }}>High</option>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="4" class="form-control" required>{{ old('description') }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Additional notes</label>
                    <textarea name="notes" rows="3" class="form-control">{{ old('notes') }}</textarea>
                </div>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Submit complaint</button>
        </form>
    </div>

    <div class="card portal-card p-4">
        <h5 class="mb-3">Complaint history</h5>
        @if($complaints->isEmpty())
            <p class="text-muted">No complaints submitted yet.</p>
        @else
            <div class="list-group">
                @foreach($complaints as $complaint)
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ ucfirst($complaint->category) }}</strong>
                                <div class="small text-muted">{{ ucfirst($complaint->priority) }} priority</div>
                            </div>
                            <span class="badge bg-{{ $complaint->status === 'open' ? 'warning' : ($complaint->status === 'closed' ? 'success' : 'secondary') }}">{{ ucfirst($complaint->status) }}</span>
                        </div>
                        <p class="mt-2 mb-0 small text-muted">{{ \Illuminate\Support\Str::limit($complaint->description, 120) }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
