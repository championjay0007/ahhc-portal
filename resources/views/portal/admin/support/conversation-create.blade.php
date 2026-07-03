@extends('layouts.admin')

@section('title', 'Start New Conversation')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-light">
                    <h3 class="mb-0">
                        <i class="bi bi-chat-dots"></i> Start New Conversation
                    </h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('portal.admin.support.conversation.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Select User</label>
                            <select class="form-select" name="user_id" required>
                                <option value="">-- Select a user --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ $user->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                                   name="subject" placeholder="Conversation subject..." 
                                   value="{{ old('subject') }}" required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Priority</label>
                            <select class="form-select @error('priority') is-invalid @enderror" name="priority">
                                <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low</option>
                                <option value="normal" {{ old('priority') === 'normal' || !old('priority') ? 'selected' : '' }}>Normal</option>
                                <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High</option>
                            </select>
                            @error('priority')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Initial Message</label>
                            <textarea class="form-control @error('message') is-invalid @enderror" 
                                      name="message" rows="5" placeholder="Start the conversation..." 
                                      required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-chat-dots"></i> Start Conversation
                            </button>
                            <a href="{{ route('portal.admin.support.conversations') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
