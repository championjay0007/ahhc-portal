@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Complaint {{ $complaint->id }}</h4>
                <p class="text-muted mb-0">Review and manage this complaint.</p>
            </div>
            <div>
                <a href="{{ route('portal.admin.complaints') }}" class="btn btn-sm btn-outline-secondary">Back to complaints</a>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h5>Details</h5>
                <dl class="row mb-4">
                    <dt class="col-sm-3">Participant</dt>
                    <dd class="col-sm-9">{{ optional($complaint->participant)->first_name }} {{ optional($complaint->participant)->last_name }}</dd>
                    <dt class="col-sm-3">Submitted by</dt>
                    <dd class="col-sm-9">{{ optional($complaint->submittedBy)->name ?? '—' }}</dd>
                    <dt class="col-sm-3">Support Person</dt>
                    <dd class="col-sm-9">{{ optional($complaint->supportPerson)->first_name }} {{ optional($complaint->supportPerson)->last_name ?? '—' }}</dd>
                    <dt class="col-sm-3">Category</dt>
                    <dd class="col-sm-9">{{ ucfirst($complaint->category ?? '—') }}</dd>
                    <dt class="col-sm-3">Priority</dt>
                    <dd class="col-sm-9">
                        @if($complaint->priority === 'high')
                            <span class="badge bg-danger">High</span>
                        @elseif($complaint->priority === 'medium')
                            <span class="badge bg-warning">Medium</span>
                        @else
                            <span class="badge bg-secondary">Low</span>
                        @endif
                    </dd>
                    <dt class="col-sm-3">Status</dt>
                    <dd class="col-sm-9">{{ ucfirst($complaint->status) }}</dd>
                    <dt class="col-sm-3">Received</dt>
                    <dd class="col-sm-9">{{ optional($complaint->received_at)->format('Y-m-d H:i') ?? optional($complaint->created_at)->format('Y-m-d H:i') }}</dd>
                </dl>

                <div class="mb-4">
                    <h6>Description</h6>
                    <p>{{ $complaint->description }}</p>
                </div>

                <div class="mb-4">
                    <h6>Notes</h6>
                    <p>{{ $complaint->notes ?? 'None' }}</p>
                </div>

                <form method="POST" action="{{ route('portal.admin.complaints.status', $complaint) }}">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-control">
                                <option value="open" {{ $complaint->status === 'open' ? 'selected' : '' }}>Open</option>
                                <option value="acknowledged" {{ $complaint->status === 'acknowledged' ? 'selected' : '' }}>Acknowledged</option>
                                <option value="investigating" {{ $complaint->status === 'investigating' ? 'selected' : '' }}>Investigating</option>
                                <option value="resolved" {{ $complaint->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ $complaint->status === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Internal Notes</label>
                        <textarea id="notes" name="notes" class="form-control" rows="4">{{ old('notes', $complaint->notes) }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Update complaint</button>
                </form>
            </div>
        </div>
    </div>
@endsection
