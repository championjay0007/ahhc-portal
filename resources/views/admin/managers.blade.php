@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Participant Managers</h2>
            <p class="text-muted mb-0">View manager accounts and the participant accounts assigned to them.</p>
        </div>
        <a href="{{ route('portal.admin.users', ['role' => 'manager']) }}" class="btn btn-outline-primary">Open manager user records</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Manager</th>
                            <th scope="col">Email</th>
                            <th scope="col">Status</th>
                            <th scope="col">Active assignments</th>
                            <th scope="col">Assigned participants</th>
                            <th scope="col">Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($managers as $manager)
                            <tr>
                                <td>
                                    <a class="fw-semibold text-decoration-none" href="{{ route('portal.admin.users.show', $manager) }}">{{ $manager->name }}</a>
                                    <div class="small text-muted">{{ $manager->phone ?: 'No phone' }}</div>
                                </td>
                                <td>{{ $manager->email }}</td>
                                <td><span class="badge bg-{{ $manager->status === 'active' ? 'success' : 'secondary' }} text-capitalize">{{ $manager->status }}</span></td>
                                <td>{{ $manager->active_assignments_count }}</td>
                                <td>
                                    @forelse($manager->participantAccountDelegations as $delegation)
                                        <div>{{ $delegation->participant->first_name }} {{ $delegation->participant->last_name }}</div>
                                    @empty
                                        <span class="text-muted">No active assignments</span>
                                    @endforelse
                                </td>
                                <td>{{ $manager->created_at?->format('M j, Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No manager accounts have registered yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($managers->hasPages())
            <div class="card-footer border-top py-3">
                @include('components.admin-pagination', ['paginator' => $managers->withQueryString()])
            </div>
        @endif
    </div>
</div>
@endsection
