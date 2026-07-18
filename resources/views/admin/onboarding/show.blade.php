@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1">Review Onboarding Submission</h1>
            <p class="text-muted">Review the participant's onboarding package before approving or requesting changes.</p>
        </div>
        <a href="{{ route('admin.onboarding.index') }}" class="btn btn-outline-secondary">Back to submissions</a>
    </div>

    <div class="row gy-4">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">Participant Details</h2>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Name</strong>
                            <p class="mb-0">{{ $participant->first_name }} {{ $participant->last_name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Email</strong>
                            <p class="mb-0">{{ $participant->email }}</p>
                        </div>
                    </div>

                    <h3 class="h6 mt-4 mb-3">Contact & Address</h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Preferred Name</strong>
                            <p class="mb-0">{{ $participant->preferred_name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Phone</strong>
                            <p class="mb-0">{{ $participant->phone ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Address</strong>
                            <p class="mb-0">{{ $participant->address ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>City</strong>
                            <p class="mb-0">{{ $participant->city ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>State</strong>
                            <p class="mb-0">{{ $participant->state ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Postcode</strong>
                            <p class="mb-0">{{ $participant->postcode ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <h3 class="h6 mt-4 mb-3">Personal Onboarding Details</h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Full Name</strong>
                            <p class="mb-0">{{ $submission->personal_data['full_name'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Emergency Contact</strong>
                            <p class="mb-0">{{ $submission->personal_data['emergency_contact_name'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Contact Phone</strong>
                            <p class="mb-0">{{ $submission->personal_data['emergency_contact_phone'] ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Relationship</strong>
                            <p class="mb-0">{{ $submission->personal_data['emergency_contact_relationship'] ?? 'N/A' }}</p>
                        </div>
                    </div>

                    @if($submission->support_person_data)
                        <h3 class="h6 mt-4 mb-3">Support Person</h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong>Name</strong>
                                <p class="mb-0">{{ $submission->support_person_data['first_name'] ?? '' }} {{ $submission->support_person_data['last_name'] ?? '' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Relationship</strong>
                                <p class="mb-0">{{ $submission->support_person_data['relationship'] ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Email</strong>
                                <p class="mb-0">{{ $submission->support_person_data['email'] ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong>Phone</strong>
                                <p class="mb-0">{{ $submission->support_person_data['phone'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                    @endif

                    <h3 class="h6 mt-4 mb-3">Uploaded Documents</h3>
                    @if($submission->uploaded_documents && count($submission->uploaded_documents))
                        <ul class="list-group list-group-flush mb-0">
                            @foreach($submission->uploaded_documents as $index => $document)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="d-block">{{ $document['name'] ?? 'Document '.$index }}</strong>
                                        <small class="text-muted">Uploaded at: {{ $document['uploaded_at'] ? \Illuminate\Support\Carbon::parse($document['uploaded_at'])->format('d M Y H:i') : 'Unknown' }}</small>
                                    </div>
                                    <a href="{{ route('admin.onboarding.download_document', ['submission' => $submission, 'index' => $index]) }}" class="btn btn-sm btn-outline-secondary">Download</a>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No documents uploaded.</p>
                    @endif

                    <h3 class="h6 mt-4 mb-3">Signed Agreements</h3>
                    @if($submission->signed_agreements && count($submission->signed_agreements))
                        <div class="list-group mb-4">
                            @foreach($submission->signed_agreements as $agreementId => $signatureData)
                                @php
                                    $agreement = $participant->agreements->firstWhere('id', $agreementId);
                                @endphp
                                <div class="list-group-item py-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="mb-1">{{ $agreement?->title ?? 'Agreement '.$agreementId }}</h5>
                                            <p class="mb-1 text-muted small">{{ $agreement?->description ?? 'Agreement signature submitted.' }}</p>
                                        </div>
                                        <span class="badge bg-success text-white">Signed</span>
                                    </div>
                                    @if($signatureData)
                                        <div class="mt-3">
                                            <span class="small text-muted">Signature preview</span>
                                            <div class="border rounded overflow-hidden mt-2" style="max-width: 320px;">
                                                <img src="{{ $signatureData }}" alt="Signature preview" class="img-fluid" style="display:block;" />
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No agreement signatures were submitted with this onboarding package.</p>
                    @endif

                    <h3 class="h6 mt-4 mb-3">Agreements</h3>
                    @if($participant->agreements && $participant->agreements->count())
                        <div class="list-group">
                            @foreach($participant->agreements as $agreement)
                                <div class="list-group-item py-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="mb-1">{{ $agreement->title }}</h5>
                                            <p class="mb-1 text-muted small">{{ $agreement->description }}</p>
                                        </div>
                                        <span class="badge bg-{{ $participant->signedAgreements()->where('agreement_id', $agreement->id)->exists() ? 'success' : 'secondary' }} text-white">
                                            {{ $participant->signedAgreements()->where('agreement_id', $agreement->id)->exists() ? 'Signed' : 'Pending' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted">No agreements assigned.</p>
                    @endif

                    <div class="card mt-4">
                        <div class="card-body">
                            <h3 class="h6 mb-3">Raw Submission Payload</h3>
                            <pre class="small text-muted" style="white-space: pre-wrap; word-break: break-word;">{{ e(json_encode([
                                'personal_data' => $submission->personal_data,
                                'support_person_data' => $submission->support_person_data,
                                'uploaded_documents' => $submission->uploaded_documents,
                                'signed_agreements' => $submission->signed_agreements,
                                'status' => $submission->status,
                                'admin_comments' => $submission->admin_comments,
                                'submitted_at' => optional($submission->submitted_at)->toDateTimeString(),
                                'reviewed_at' => optional($submission->reviewed_at)->toDateTimeString(),
                                'reviewed_by' => $submission->reviewer?->name,
                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) }}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h6 mb-3">Submission Summary</h3>
                    <dl class="row">
                        <dt class="col-5 text-muted">Status</dt>
                        <dd class="col-7">{{ ucfirst(str_replace('_', ' ', $submission->status)) }}</dd>
                        <dt class="col-5 text-muted">Submitted</dt>
                        <dd class="col-7">{{ optional($submission->submitted_at)->format('d M Y H:i') }}</dd>
                        <dt class="col-5 text-muted">Review notes</dt>
                        <dd class="col-7">{{ $submission->admin_comments ?? 'None' }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h3 class="h6 mb-3">Actions</h3>

                    <form action="{{ route('admin.onboarding.approve', $submission) }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">Approve</button>
                    </form>

                    <form action="{{ route('admin.onboarding.request_changes', $submission) }}" method="POST" class="mb-3">
                        @csrf
                        <div class="mb-3">
                            <textarea name="admin_comments" class="form-control" rows="4" placeholder="Explain required changes" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">Request Changes</button>
                    </form>

                    <form action="{{ route('admin.onboarding.reject', $submission) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <textarea name="rejection_reason" class="form-control" rows="4" placeholder="Reason for rejection" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Reject</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
