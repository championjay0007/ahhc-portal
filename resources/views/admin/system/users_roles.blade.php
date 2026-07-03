@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Users & Roles</h2>
            <p class="text-muted mb-0">Review role distribution and navigate directly to the user directory.</p>
        </div>
        <a href="{{ route('portal.admin.users') }}" class="btn btn-primary">Manage users</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Total users</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($roleSummary as $roleRow)
                            <tr>
                                <td class="text-capitalize">{{ str_replace('_', ' ', $roleRow->role) }}</td>
                                <td>{{ $roleRow->total }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
