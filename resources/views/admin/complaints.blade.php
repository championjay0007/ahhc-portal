@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Complaints</h4>
                <p class="text-muted mb-0">Manage participant complaints.</p>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <form method="GET" action="{{ route('portal.admin.complaints') }}" class="d-flex gap-2">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search complaints..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Search</button>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <form method="GET" action="{{ route('portal.admin.complaints') }}" class="d-flex gap-2">
                            <select name="status" class="form-control form-control-sm" onchange="this.form.submit()">
                                <option value="">All statuses</option>
                                <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                                <option value="acknowledged" {{ request('status') === 'acknowledged' ? 'selected' : '' }}>Acknowledged</option>
                                <option value="investigating" {{ request('status') === 'investigating' ? 'selected' : '' }}>Investigating</option>
                                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </form>
                    </div>
                </div>

                @if($complaints->isEmpty())
                    <p class="text-muted">No complaints found.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Participant</th>
                                    <th>Category</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($complaints as $complaint)
                                    <tr>
                                        <td>{{ optional($complaint->created_at)->format('Y-m-d H:i') }}</td>
                                        <td>{{ optional($complaint->participant)->first_name }} {{ optional($complaint->participant)->last_name }}</td>
                                        <td>{{ ucfirst($complaint->category ?? '—') }}</td>
                                        <td>
                                            @if($complaint->priority === 'high')
                                                <span class="badge bg-danger">High</span>
                                            @elseif($complaint->priority === 'medium')
                                                <span class="badge bg-warning">Medium</span>
                                            @else
                                                <span class="badge bg-secondary">Low</span>
                                            @endif
                                        </td>
                                        <td>{{ ucfirst($complaint->status) }}</td>
                                        <td><a href="{{ route('portal.admin.complaints.show', $complaint) }}" class="btn btn-sm btn-outline-secondary">View</a></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{{ $complaints->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
