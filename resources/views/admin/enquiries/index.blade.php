@extends('layouts.admin')

@section('content')
<div class="p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h4">Communications &middot; Enquiries</h1>
            <p class="text-muted mb-0">View, assign and manage public enquiries submitted through the Allegiance Heart &amp; Home Care website.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('portal.admin.enquiries.export') }}" class="btn btn-sm btn-outline-light"><i class="bi bi-download"></i> Export</a>
        </div>
    </div>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="search" name="search" class="form-control" placeholder="Search enquiries" value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">Filter by status</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="assigned_to" class="form-select">
                <option value="">Filter by staff</option>
                @foreach($admins as $admin)
                    <option value="{{ $admin->id }}" {{ request('assigned_to') == $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button type="submit" class="btn btn-primary">Apply</button>
        </div>
    </form>

    <div class="table-responsive border rounded-4 bg-white shadow-sm">
        <table class="table align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Assigned</th>
                    <th>Received</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($enquiries as $enquiry)
                    <tr>
                        <td>#{{ $enquiry->id }}</td>
                        <td>{{ $enquiry->name }}</td>
                        <td>{{ $enquiry->email }}</td>
                        <td>{{ ucwords(str_replace('_', ' ', $enquiry->role)) }}</td>
                        <td><span class="badge bg-secondary">{{ $enquiry->status }}</span></td>
                        <td>{{ $enquiry->assignedTo?->name ?? 'Unassigned' }}</td>
                        <td>{{ $enquiry->created_at->format('d M Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('portal.admin.enquiries.show', $enquiry) }}" class="btn btn-sm btn-outline-primary">View</a>
                            <form method="POST" action="{{ route('portal.admin.enquiries.destroy', $enquiry) }}" class="d-inline" onsubmit="return confirm('Delete this enquiry?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">No enquiries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $enquiries->links() }}
    </div>
</div>
@endsection
