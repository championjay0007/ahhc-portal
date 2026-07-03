@extends('layouts.portal')

@section('title', 'My Profile')

@section('content')
    <div class="portal-page-header">
        <h1>My Profile</h1>
        <p>Update your contact details and upload a profile photo for the portal.</p>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="profile-summary mb-4 d-flex align-items-center gap-3">
        <img src="{{ auth()->user()->profile_photo_url }}" alt="Profile photo" class="rounded-circle" width="96" height="96">
        <div>
            <h2 class="mb-1">{{ auth()->user()->name }}</h2>
            <p class="text-muted mb-0">{{ ucfirst(auth()->user()->role) }} account</p>
        </div>
    </div>

    <form method="POST" action="{{ route('portal.profile.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="profile_photo" class="form-label">Profile photo</label>
            <input type="file" id="profile_photo" name="profile_photo" accept="image/*" class="form-control">
            <div class="form-text">Upload a new profile photo (PNG, JPG, GIF, max 5MB).</div>
        </div>

        <div class="mb-3">
            <label for="name" class="form-label">Full name</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ old('name', auth()->user()->name) }}" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email', auth()->user()->email) }}" required>
        </div>

        <div class="mb-3">
            <label for="phone" class="form-label">Phone</label>
            <input type="text" id="phone" name="phone" class="form-control" value="{{ old('phone', auth()->user()->phone) }}">
        </div>

        <button type="submit" class="btn btn-primary">Save changes</button>
    </form>

    <!-- Onboarding Information -->
    @php
        $participant = auth()->user()->participant;
        $onboardingSubmission = $participant?->latestOnboardingSubmission();
        $onboardingProgress = $participant ? \App\Models\OnboardingProgress::where('participant_id', $participant->id)->first() : null;
        $draftData = $onboardingProgress?->draft_data ?? [];
    @endphp
    
    @if($onboardingSubmission || ($draftData && count((array) $draftData) > 0))
        <div class="mt-5">
            <h2>Onboarding Information</h2>
            
            <!-- Personal Data -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-light border-0">
                    <h5 class="mb-0">Personal Details</h5>
                </div>
                <div class="card-body">
                    @php
                        $personalData = $onboardingSubmission?->personal_data ?? $draftData;
                    @endphp
                    @if($personalData)
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="small text-muted">Full Name</div>
                                <div class="fw-semibold">{{ $personalData['full_name'] ?? 'Not provided' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="small text-muted">Emergency Contact Name</div>
                                <div class="fw-semibold">{{ $personalData['emergency_contact_name'] ?? 'Not provided' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="small text-muted">Emergency Contact Phone</div>
                                <div class="fw-semibold">{{ $personalData['emergency_contact_phone'] ?? 'Not provided' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="small text-muted">Emergency Contact Relationship</div>
                                <div class="fw-semibold">{{ $personalData['emergency_contact_relationship'] ?? 'Not provided' }}</div>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">No personal data submitted.</p>
                    @endif
                </div>
            </div>

            <!-- Support Person Data -->
            @php
                $supportPersonData = $onboardingSubmission?->support_person_data ?? ($draftData['support_first_name'] ? [
                    'first_name' => $draftData['support_first_name'] ?? '',
                    'last_name' => $draftData['support_last_name'] ?? '',
                    'email' => $draftData['support_email'] ?? '',
                    'phone' => $draftData['support_phone'] ?? '',
                    'relationship' => $draftData['support_relationship'] ?? ''
                ] : null);
            @endphp
            @if($supportPersonData)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light border-0">
                        <h5 class="mb-0">Support Person</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="small text-muted">Name</div>
                                <div class="fw-semibold">
                                    {{ $supportPersonData['first_name'] ?? '' }} 
                                    {{ $supportPersonData['last_name'] ?? '' }}
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="small text-muted">Relationship</div>
                                <div class="fw-semibold">{{ $supportPersonData['relationship'] ?? 'Not provided' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="small text-muted">Email</div>
                                <div class="fw-semibold">{{ $supportPersonData['email'] ?? 'Not provided' }}</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="small text-muted">Phone</div>
                                <div class="fw-semibold">{{ $supportPersonData['phone'] ?? 'Not provided' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Uploaded Documents -->
            @php
                $uploadedDocs = $onboardingSubmission?->uploaded_documents ?? ($draftData['uploaded_documents'] ?? []);
            @endphp
            @if($uploadedDocs && count($uploadedDocs) > 0)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-light border-0">
                        <h5 class="mb-0">Submitted Documents</h5>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            @foreach($uploadedDocs as $doc)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-semibold">{{ $doc['name'] ?? $doc['file_name'] ?? 'Document' }}</div>
                                        <div class="small text-muted">{{ $doc['type'] ?? $doc['document_type'] ?? 'File' }}</div>
                                    </div>
                                    @if(isset($doc['uploaded_at']))
                                        <div class="text-muted small">{{ \Carbon\Carbon::parse($doc['uploaded_at'])->format('M d, Y') }}</div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Submission Status -->
            @if($onboardingSubmission)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light border-0">
                        <h5 class="mb-0">Submission Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="small text-muted">Status</div>
                                <div class="fw-semibold">
                                    <span class="badge bg-{{ $onboardingSubmission->status === 'approved' ? 'success' : ($onboardingSubmission->status === 'rejected' ? 'danger' : ($onboardingSubmission->status === 'changes_requested' ? 'warning' : 'info')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $onboardingSubmission->status)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="small text-muted">Submitted</div>
                                <div class="fw-semibold">{{ $onboardingSubmission->submitted_at?->format('M d, Y H:i') ?? 'Not submitted' }}</div>
                            </div>
                            @if($onboardingSubmission->reviewed_at)
                                <div class="col-md-6 mb-3">
                                    <div class="small text-muted">Reviewed</div>
                                    <div class="fw-semibold">{{ $onboardingSubmission->reviewed_at->format('M d, Y H:i') }}</div>
                                </div>
                            @endif
                            @if($onboardingSubmission->admin_comments)
                                <div class="col-md-6 mb-3">
                                    <div class="small text-muted">Admin Comments</div>
                                    <div class="fw-semibold">{{ $onboardingSubmission->admin_comments }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @elseif($onboardingProgress)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light border-0">
                        <h5 class="mb-0">Onboarding Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="small text-muted">Current Step</div>
                                <div class="fw-semibold">Step {{ $onboardingProgress->current_step }} of 8</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="small text-muted">Progress</div>
                                <div class="fw-semibold">
                                    <span class="badge bg-info">{{ count($onboardingProgress->completed_steps ?? []) }} steps completed</span>
                                </div>
                            </div>
                            <div class="col-md-12 mb-0">
                                <div class="small text-muted">Status</div>
                                <div class="fw-semibold">{{ ucfirst($onboardingProgress->status) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="mt-5">
        <h2>Security settings</h2>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Two-factor authentication</h5>
                @if(auth()->user()->mfa_enabled)
                    <p class="mb-1">MFA is currently <strong>enabled</strong> for your account.</p>
                    <p class="text-muted mb-3">This account is protected by two-factor authentication.</p>
                    <form method="POST" action="{{ route('portal.mfa.disable') }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger">Disable MFA</button>
                    </form>
                @else
                    <p class="mb-1">MFA is currently <strong>disabled</strong> for your account.</p>
                    <p class="text-muted mb-3">Enable two-factor authentication to add an extra layer of security.</p>
                    <a href="{{ route('portal.mfa.setup') }}" class="btn btn-primary">Enable MFA</a>
                @endif
            </div>
        </div>
    </div>
@endsection
