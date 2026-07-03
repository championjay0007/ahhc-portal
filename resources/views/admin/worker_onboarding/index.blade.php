@extends('layouts.admin')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Worker Onboarding Management</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#inviteWorkerModal">
            + Invite Worker
        </button>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h6 class="mb-0">All Workers</h6>
        </div>
        <div class="card-body p-0">
            @if ($workers->isEmpty())
                <div class="p-4 text-center text-muted">
                    No workers yet. Start by inviting a worker.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Stage</th>
                                <th>Status</th>
                                <th>Invited By</th>
                                <th>Invited Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($workers as $worker)
                                <tr>
                                    <td>
                                        <strong>{{ $worker->first_name }} {{ $worker->last_name }}</strong>
                                    </td>
                                    <td>{{ $worker->email }}</td>
                                    <td>
                                        <span class="badge" style="background-color: {{ match($worker->onboarding_stage) {
                                            1 => '#0dcaf0',
                                            2 => '#0d6efd',
                                            3 => '#6f42c1',
                                            4 => '#fd7e14',
                                            5 => '#198754',
                                            6 => '#20c997',
                                            default => '#6c757d'
                                        } }}">
                                            Stage {{ $worker->onboarding_stage }}: {{ $worker->getCurrentStage()->label() }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $worker->status === 'active' ? 'success' : ($worker->status === 'pending' ? 'warning' : 'danger') }}">
                                            {{ ucfirst($worker->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if ($worker->invitedBy)
                                            {{ $worker->invitedBy->name }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($worker->invited_at)
                                            {{ $worker->invited_at->format('M d, Y') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.worker_onboarding.show', $worker) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Pagination -->
    <div class="d-flex justify-content-center mt-4">
        {{ $workers->links() }}
    </div>
</div>

<!-- Invite Worker Modal -->
<div class="modal fade" id="inviteWorkerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Invite New Worker</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.worker_onboarding.invite') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="first_name" class="form-label">First Name</label>
                        <input type="text" class="form-control" id="first_name" name="first_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="last_name" class="form-label">Last Name</label>
                        <input type="text" class="form-control" id="last_name" name="last_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="role_type" class="form-label">Role Type</label>
                        <select class="form-select" id="role_type" name="role_type" required>
                            <option value="">Select role type</option>
                            <option value="Support Worker">Support Worker</option>
                            <option value="Care Worker">Care Worker</option>
                            <option value="Specialist">Specialist</option>
                            <option value="Therapist">Therapist</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes (Optional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Invitation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
