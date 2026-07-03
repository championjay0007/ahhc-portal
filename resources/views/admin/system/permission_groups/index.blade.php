@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Permission Groups</h2>
            <p class="text-muted mb-0">Group permissions into reusable access sets for system administrators.</p>
        </div>
        <a href="{{ route('portal.admin.system.permission_groups.create') }}" class="btn btn-primary">New permission group</a>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Permissions</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groups as $group)
                            <tr>
                                <td>{{ $group->name }}</td>
                                <td>{{ $group->description }}</td>
                                <td>{{ implode(', ', $group->permissions ?? []) }}</td>
                                <td class="text-end">
                                    <a href="{{ route('portal.admin.system.permission_groups.edit', $group) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                    <form method="POST" action="{{ route('portal.admin.system.permission_groups.destroy', $group) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this permission group?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-muted">No permission groups configured.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
