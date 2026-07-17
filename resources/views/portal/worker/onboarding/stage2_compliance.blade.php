@extends('layouts.auth')

@section('content')
<div class="container py-4">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                <h2>Worker Onboarding - Stage 2: Upload Compliance Documents</h2>
                <span class="badge bg-info mt-2 mt-md-0">Stage 2 of 6</span>
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

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Required Compliance Documents</h6>
                        </div>
                        <div class="card-body">
                            <p class="text-muted mb-4">Please upload the following documents to proceed with your onboarding:</p>

                            <form method="POST" action="{{ route('worker.onboarding.stage2.submit', ['token' => $token]) }}" enctype="multipart/form-data">
                                @csrf

                                <div class="alert alert-info">
                                    <strong>Requirements:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li><strong>ABN Verification</strong> — optional Australian Business Number verification.</li>
                                        <li><strong>Police Check</strong> — required valid police clearance document.</li>
                                        <li><strong>NDIS Worker Screening</strong> — optional NDIS worker screening certificate.</li>
                                        <li><strong>Insurance</strong> — optional professional indemnity or public liability insurance.</li>
                                        <li><strong>Qualification</strong> — optional relevant qualifications and certifications.</li>
                                        <li><strong>First Aid Certificate</strong> — optional first aid certification evidence.</li>
                                        <li><strong>CPR Certificate</strong> — optional CPR training certification.</li>
                                        <li><strong>Registration</strong> — optional professional registration or licensing documents.</li>
                                        <li><strong>Marketplace Agreement</strong> — optional marketplace agreement if applicable.</li>
                                    </ul>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label" for="apn">APN number</label>
                                    <input id="apn" type="text" class="form-control" name="apn" value="{{ old('apn') }}" placeholder="Enter APN number" inputmode="numeric">
                                    <small class="text-muted d-block mt-2">Submit the APN number as a number entry, not a document upload.</small>
                                </div>

                                @foreach ($complianceTypes as $requirement)
                                    @php
                                        $uploaded = $uploadedDocuments->firstWhere('document_type', $requirement['name']);
                                    @endphp
                                    <div class="card shadow-sm mb-3">
                                        <div class="card-header bg-light">
                                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                                <div>
                                                    <strong>{{ $requirement['name'] }}</strong>
                                                    @if ($requirement['required'])
                                                        <span class="badge bg-danger">Required</span>
                                                    @endif
                                                </div>
                                                @if ($uploaded)
                                                    <span class="badge bg-{{ $uploaded->status === 'submitted' ? 'warning' : 'success' }}">
                                                        {{ ucfirst($uploaded->status) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label" for="documents_{{ $requirement['slug'] }}">Upload {{ $requirement['name'] }}</label>
                                                <input id="documents_{{ $requirement['slug'] }}" type="file" class="form-control @error('documents.' . $requirement['slug']) is-invalid @enderror" name="documents[{{ $requirement['slug'] }}]" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                                @error('documents.' . $requirement['slug'])
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                <small class="text-muted d-block mt-2">
                                                    Accepted formats: PDF, JPG, PNG, DOC, DOCX | Max 10MB
                                                </small>
                                            </div>

                                            @if ($uploaded)
                                                <div class="alert alert-light p-3">
                                                    <p class="mb-1"><strong>Uploaded file:</strong> {{ basename($uploaded->document_path) }}</p>
                                                    <p class="mb-1"><strong>Status:</strong> {{ ucfirst($uploaded->status) }}</p>
                                                    <p class="mb-0"><small>Uploaded: {{ $uploaded->created_at->format('M d, Y') }}</small></p>

                                                    <div class="mt-2 d-flex flex-wrap gap-2">
                                                        @if ($uploaded->document_path)
                                                            <a href="{{ route('worker.onboarding.document.preview', ['token' => $token, 'document' => $uploaded->id]) }}" class="btn btn-sm btn-outline-primary">Preview</a>
                                                            <a href="{{ route('worker.onboarding.document.download', ['token' => $token, 'document' => $uploaded->id]) }}" class="btn btn-sm btn-outline-secondary">Download</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach

                                <button type="submit" class="btn btn-primary btn-lg">Submit Documents</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Timeline</h6>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item completed">
                                    <div class="timeline-marker">✓</div>
                                    <div class="timeline-content">
                                        <h6>Stage 1: Invited</h6>
                                        <small class="text-muted">Account created</small>
                                    </div>
                                </div>
                                <div class="timeline-item active">
                                    <div class="timeline-marker">●</div>
                                    <div class="timeline-content">
                                        <h6>Stage 2: Upload Compliance</h6>
                                        <small class="text-muted">You are here</small>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-marker">○</div>
                                    <div class="timeline-content">
                                        <h6>Stage 3: Document Review</h6>
                                        <small class="text-muted">Allegiance Heart &amp; Home Care reviews documents</small>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-marker">○</div>
                                    <div class="timeline-content">
                                        <h6>Stage 4: Sign Declarations</h6>
                                        <small class="text-muted">Sign agreements</small>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-marker">○</div>
                                    <div class="timeline-content">
                                        <h6>Stage 5: Service Approval</h6>
                                        <small class="text-muted">Services assigned</small>
                                    </div>
                                </div>
                                <div class="timeline-item">
                                    <div class="timeline-marker">○</div>
                                    <div class="timeline-content">
                                        <h6>Stage 6: Assigned to Participant</h6>
                                        <small class="text-muted">Full access granted</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding: 10px 0;
}

.timeline-item {
    display: flex;
    margin-bottom: 20px;
    position: relative;
}

.timeline-item .timeline-marker {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    flex-shrink: 0;
    font-weight: bold;
    font-size: 12px;
}

.timeline-item.active .timeline-marker {
    background: #0d6efd;
    color: white;
}

.timeline-item.completed .timeline-marker {
    background: #198754;
    color: white;
}

.timeline-item .timeline-marker::after {
    content: '';
    position: absolute;
    width: 2px;
    height: 30px;
    background: #dee2e6;
    left: 15px;
    top: 30px;
}

.timeline-item:last-child .timeline-marker::after {
    display: none;
}

.timeline-content h6 {
    margin-bottom: 5px;
    font-weight: 600;
    color: #212529;
}
</style>
@endsection
