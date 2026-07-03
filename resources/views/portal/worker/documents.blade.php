@extends('layouts.portal')

@section('title', 'Upload Documents')

@section('content')
    <div class="portal-page-header">
        <h1>Upload Documents</h1>
        <p>Upload certificates, evidence, or participant documents.</p>
    </div>

    <form method="POST" action="{{ route('portal.worker.documents.store') }}" enctype="multipart/form-data">
        @csrf

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-3">
            <label for="participant_id" class="form-label">Participant</label>
            <select id="participant_id" name="participant_id" class="form-control" required>
                <option value="">Select a participant</option>
                @foreach($assignments as $assignment)
                    <option value="{{ $assignment->participant->id }}" {{ old('participant_id') == $assignment->participant->id ? 'selected' : '' }}>{{ $assignment->participant->first_name }} {{ $assignment->participant->last_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="title" class="form-label">Document Title</label>
            <input id="title" name="title" type="text" class="form-control" value="{{ old('title') }}" required>
        </div>

        <div class="mb-3">
            <label for="document_type" class="form-label">Document Type</label>
            <input id="document_type" name="document_type" type="text" class="form-control" value="{{ old('document_type') }}" placeholder="e.g. Service evidence, certificate" required>
        </div>

        <div class="mb-3">
            <label for="file" class="form-label">Choose file</label>
            <input id="file" name="file" type="file" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Upload Document</button>
    </form>
@endsection
