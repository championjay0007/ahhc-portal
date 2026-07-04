@extends('layouts.portal')

@section('title', 'Send Message')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="mb-4">
                <a href="{{ route($messageRoutePrefix.'inbox') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Inbox
                </a>
            </div>

            <div class="card">
                <div class="card-header bg-light border-bottom">
                    <h4 class="mb-0">Send Message</h4>
                </div>
                <div class="card-body">
                    @if($recipients->isEmpty())
                        <div class="alert alert-warning">
                            No assigned chat contacts were found. Please contact your administrator if you need to message your care team.
                        </div>
                    @else
                        <form action="{{ route($messageRoutePrefix.'send') }}" method="POST">
                            @csrf

                            @if($selectedRecipient)
                                <div class="mb-3">
                                    <label class="form-label">To</label>
                                    <input type="text" class="form-control" value="{{ $selectedRecipient->name }} ({{ $selectedRecipient->email }})" disabled>
                                    <input type="hidden" name="recipient_id" value="{{ $selectedRecipient->id }}">
                                </div>
                            @else
                                <div class="mb-3">
                                    <label for="recipient_id" class="form-label">To</label>
                                    <select name="recipient_id" id="recipient_id" class="form-select @error('recipient_id') is-invalid @enderror" required>
                                        <option value="">Choose an assigned chat contact</option>
                                        @foreach($recipients as $recipient)
                                            <option value="{{ $recipient->id }}" @selected(old('recipient_id') == $recipient->id)>
                                                {{ $recipient->name }} &ndash; {{ $recipient->email }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('recipient_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endif

                            <div class="mb-3">
                                <label for="subject" class="form-label">Subject</label>
                                <input type="text" name="subject" id="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}" required>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="body" class="form-label">Message</label>
                                <textarea name="body" id="body" class="form-control @error('body') is-invalid @enderror" rows="6" required>{{ old('body') }}</textarea>
                                @error('body')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <a href="{{ route($messageRoutePrefix.'inbox') }}" class="btn btn-outline-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary">Send Message</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
