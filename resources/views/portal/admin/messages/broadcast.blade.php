@extends('layouts.admin')

@section('title', 'Broadcast Message')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <h1 class="h2 mb-4">Broadcast Message to All Users</h1>

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Errors:</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('portal.admin.messages.broadcast.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Send To <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="role_participant" name="roles[]" value="participant" {{ in_array('participant', old('roles', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role_participant">
                                    Participants
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="role_worker" name="roles[]" value="worker" {{ in_array('worker', old('roles', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role_worker">
                                    Workers
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="role_support_person" name="roles[]" value="support_person" {{ in_array('support_person', old('roles', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="role_support_person">
                                    Support Persons
                                </label>
                            </div>
                            @error('roles')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject') }}" required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="body" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body" rows="10" required>{{ old('body') }}</textarea>
                            @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Note:</strong> This message will be sent to all active users in the selected roles.
                        </div>

                        <div class="form-group">
                            <a href="{{ route('portal.admin.messages.sent') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" onclick="return confirm('Send this broadcast message to all selected users?')">
                                <i class="bi bi-broadcast"></i> Send Broadcast
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
