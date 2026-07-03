@extends('layouts.admin')

@section('title', 'Ticket #' . str_pad($ticket->id, 6, '0', STR_PAD_LEFT))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8">
            <!-- Ticket Header -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <div class="row align-items-center">
                        <div class="col">
                            <h3 class="mb-0">{{ $ticket->subject }}</h3>
                            <small class="text-muted">#{{ str_pad($ticket->id, 6, '0', STR_PAD_LEFT) }} • From {{ $ticket->user->name }}</small>
                        </div>
                        <div class="col-auto">
                            <span class="badge bg-{{ $ticket->status === 'open' ? 'danger' : ($ticket->status === 'in-progress' ? 'warning' : 'success') }} fs-6">
                                {{ ucfirst($ticket->status) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <strong>Priority:</strong>
                            <span class="badge bg-{{ $ticket->priority === 'urgent' ? 'danger' : ($ticket->priority === 'high' ? 'warning' : 'info') }}">
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </div>
                        <div class="col-md-3">
                            <strong>Category:</strong> {{ ucfirst($ticket->category) }}
                        </div>
                        <div class="col-md-3">
                            <strong>Created:</strong> {{ $ticket->created_at->format('M d, Y H:i') }}
                        </div>
                        <div class="col-md-3">
                            <strong>Last Updated:</strong> {{ $ticket->updated_at->diffForHumans() }}
                        </div>
                    </div>

                    <hr>

                    <h6>Description</h6>
                    <div class="bg-light p-3 rounded">
                        {!! nl2br(e($ticket->description)) !!}
                    </div>
                </div>
            </div>

            <!-- Responses -->
            @if($responses->count() > 0)
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bi bi-chat"></i> Responses ({{ $responses->count() }})
                        </h5>
                    </div>
                    <div class="card-body">
                        @foreach($responses as $response)
                            <div class="mb-3 p-3 bg-{{ $response->is_admin ? 'info' : 'light' }} rounded">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong>{{ $response->user->name }}</strong>
                                        @if($response->is_admin)
                                            <span class="badge bg-success ms-2">Support Staff</span>
                                        @endif
                                    </div>
                                    <small class="text-muted">{{ $response->created_at->format('M d, Y H:i') }}</small>
                                </div>
                                <p class="mb-0">{{ $response->message }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Add Response Form -->
            @if($ticket->status !== 'closed' && $ticket->status !== 'resolved')
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0"><i class="bi bi-reply"></i> Add Response</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('portal.admin.support.ticket.response', $ticket) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <textarea class="form-control" name="message" rows="4" placeholder="Type your response..." required></textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Send Response
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Update -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Update Status</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('portal.admin.support.ticket.status', $ticket) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <select name="status" class="form-select">
                                <option value="open" {{ $ticket->status === 'open' ? 'selected' : '' }}>Open</option>
                                <option value="in-progress" {{ $ticket->status === 'in-progress' ? 'selected' : '' }}>In Progress</option>
                                <option value="waiting" {{ $ticket->status === 'waiting' ? 'selected' : '' }}>Waiting for User</option>
                                <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Resolved</option>
                                <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="bi bi-check"></i> Update
                        </button>
                    </form>
                </div>
            </div>

            <!-- Ticket Info -->
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Ticket Info</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-6">User:</dt>
                        <dd class="col-sm-6">
                            <a href="{{ route('portal.admin.users.show', $ticket->user) }}">
                                {{ $ticket->user->name }}
                            </a>
                        </dd>

                        <dt class="col-sm-6">Email:</dt>
                        <dd class="col-sm-6">{{ $ticket->user->email }}</dd>

                        <dt class="col-sm-6">Responses:</dt>
                        <dd class="col-sm-6">{{ $responses->count() }}</dd>

                        @if($ticket->resolved_at)
                            <dt class="col-sm-6">Resolved:</dt>
                            <dd class="col-sm-6">{{ $ticket->resolved_at->format('M d, Y') }}</dd>
                        @endif
                    </dl>

                    <a href="{{ route('portal.admin.support.tickets') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="bi bi-arrow-left"></i> Back to Tickets
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
