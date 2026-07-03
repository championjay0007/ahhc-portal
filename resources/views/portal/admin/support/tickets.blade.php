@extends('layouts.admin')

@section('title', 'Support Tickets')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="h2">
                <i class="bi bi-ticket-fill"></i> Support Tickets
            </h1>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Subject, user, description..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Open</option>
                        <option value="in-progress" {{ request('status') === 'in-progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="">All</option>
                        <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="normal" {{ request('priority') === 'normal' ? 'selected' : '' }}>Normal</option>
                        <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="urgent" {{ request('priority') === 'urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Subject</th>
                        <th>User</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tickets as $ticket)
                        <tr>
                            <td>#{{ str_pad($ticket->id, 6, '0', STR_PAD_LEFT) }}</td>
                            <td>{{ Str::limit($ticket->subject, 40) }}</td>
                            <td>{{ $ticket->user->name }}</td>
                            <td>
                                <span class="badge bg-{{ $ticket->priority === 'urgent' ? 'danger' : ($ticket->priority === 'high' ? 'warning' : 'info') }}">
                                    {{ ucfirst($ticket->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $ticket->status === 'open' ? 'danger' : ($ticket->status === 'in-progress' ? 'warning' : 'success') }}">
                                    {{ ucfirst($ticket->status) }}
                                </span>
                            </td>
                            <td>{{ $ticket->created_at->format('M d, Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('portal.admin.support.ticket.show', $ticket) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $tickets->links() }}
    </div>
</div>
@endsection
