@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Create Permission Group</h2>
            <p class="text-muted mb-0">Define a named permission grouping for administrative access control.</p>
        </div>
        <a href="{{ route('portal.admin.system.permission_groups') }}" class="btn btn-outline-secondary">Back to groups</a>
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

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('portal.admin.system.permission_groups.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Permissions</label>
                    <textarea name="permissions" class="form-control" rows="4" placeholder="One permission per line">{{ old('permissions') }}</textarea>
                    <div class="form-text">Separate permissions by commas or new lines. Example: user.manage, system.health, backup.view</div>
                </div>

                <button type="submit" class="btn btn-primary">Save group</button>
            </form>
        </div>
    </div>
</div>
@endsection
