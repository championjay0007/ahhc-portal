@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Edit Permission Group</h2>
            <p class="text-muted mb-0">Update the group details and permission list.</p>
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
            <form method="POST" action="{{ route('portal.admin.system.permission_groups.update', $permissionGroup) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $permissionGroup->name) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3">{{ old('description', $permissionGroup->description) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Permissions</label>
                    <textarea name="permissions" class="form-control" rows="4" placeholder="One permission per line">{{ old('permissions', implode("\n", $permissionGroup->permissions ?? [])) }}</textarea>
                    <div class="form-text">Use one permission per line. Example: user.manage</div>
                </div>

                <button type="submit" class="btn btn-primary">Update group</button>
            </form>
        </div>
    </div>
</div>
@endsection
