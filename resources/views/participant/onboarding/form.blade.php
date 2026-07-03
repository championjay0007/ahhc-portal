@extends('layouts.auth')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="mb-0">Complete Your Onboarding</h2>
                            <p class="mb-0 mt-2 small">Step {{ $currentStep ?? 1 }} of 5</p>
                        </div>
                        <div style="font-size: 3rem; opacity: 0.3;">{{ $currentStep ?? 1 }}/5</div>
                    </div>
                </div>

                <div class="card-body p-4">
                    <!-- Progress Bar -->
                    <div class="progress mb-4" style="height: 8px;">
                        <div class="progress-bar" role="progressbar" 
                             style="width: {{ (($currentStep ?? 1) / 5) * 100 }}%;"
                             aria-valuenow="{{ $currentStep ?? 1 }}" aria-valuemin="1" aria-valuemax="5"></div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Please fix these errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('participant.onboarding.submit', ['token' => $token]) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- SECTION 1: ACCOUNT SETUP -->
                        <div class="section mb-5" id="section-account">
                            <h4 class="mb-3"><i class="bi bi-shield-lock me-2"></i>Account Setup</h4>
                            <p class="text-muted mb-4">Create a secure password for your account</p>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                       id="password" name="password" required minlength="8">
                                <small class="form-text text-muted">Minimum 8 characters</small>
                                @error('password')
                                    <small class="text-danger d-block">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label">Confirm Password *</label>
                                <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                       id="password_confirmation" name="password_confirmation" required minlength="8">
                                @error('password_confirmation')
                                    <small class="text-danger d-block">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                Use a combination of letters, numbers, and special characters for security.
                            </div>
                        </div>

                        <hr class="my-5">

                        <!-- SECTION 2: PERSONAL INFORMATION -->
                        <div class="section mb-5" id="section-personal">
                            <h4 class="mb-3"><i class="bi bi-person-circle me-2"></i>Personal Information</h4>
                            <p class="text-muted mb-4">Please provide your personal details</p>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="full_name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control @error('full_name') is-invalid @enderror" 
                                           id="full_name" name="full_name" value="{{ old('full_name') }}" required>
                                    @error('full_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="preferred_name" class="form-label">Preferred Name *</label>
                                    <input type="text" class="form-control @error('preferred_name') is-invalid @enderror" 
                                           id="preferred_name" name="preferred_name" value="{{ old('preferred_name') }}" required>
                                    @error('preferred_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}" required>
                                    @error('phone')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="address" class="form-label">Address *</label>
                                    <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                           id="address" name="address" value="{{ old('address') }}" required>
                                    @error('address')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="city" class="form-label">City *</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" value="{{ old('city') }}" required>
                                    @error('city')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="state" class="form-label">State *</label>
                                    <select class="form-select @error('state') is-invalid @enderror" id="state" name="state" required>
                                        <option value="">Select</option>
                                        <option value="NSW" {{ old('state') === 'NSW' ? 'selected' : '' }}>NSW</option>
                                        <option value="VIC" {{ old('state') === 'VIC' ? 'selected' : '' }}>VIC</option>
                                        <option value="QLD" {{ old('state') === 'QLD' ? 'selected' : '' }}>QLD</option>
                                        <option value="WA" {{ old('state') === 'WA' ? 'selected' : '' }}>WA</option>
                                        <option value="SA" {{ old('state') === 'SA' ? 'selected' : '' }}>SA</option>
                                        <option value="TAS" {{ old('state') === 'TAS' ? 'selected' : '' }}>TAS</option>
                                        <option value="ACT" {{ old('state') === 'ACT' ? 'selected' : '' }}>ACT</option>
                                        <option value="NT" {{ old('state') === 'NT' ? 'selected' : '' }}>NT</option>
                                    </select>
                                    @error('state')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="postcode" class="form-label">Postcode *</label>
                                    <input type="text" class="form-control @error('postcode') is-invalid @enderror" 
                                           id="postcode" name="postcode" value="{{ old('postcode') }}" required>
                                    @error('postcode')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <h5 class="mt-4 mb-3">Emergency Contact</h5>

                            <div class="mb-3">
                                <label for="emergency_contact_name" class="form-label">Emergency Contact Name *</label>
                                <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                                       id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" required>
                                @error('emergency_contact_name')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="emergency_contact_phone" class="form-label">Phone *</label>
                                    <input type="tel" class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                                           id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" required>
                                    @error('emergency_contact_phone')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="emergency_contact_relationship" class="form-label">Relationship *</label>
                                    <input type="text" class="form-control @error('emergency_contact_relationship') is-invalid @enderror" 
                                           id="emergency_contact_relationship" name="emergency_contact_relationship" 
                                           value="{{ old('emergency_contact_relationship') }}" required>
                                    @error('emergency_contact_relationship')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="my-5">

                        <!-- SECTION 3: SUPPORT PERSON (Optional) -->
                        <div class="section mb-5" id="section-support">
                            <h4 class="mb-3"><i class="bi bi-person-plus me-2"></i>Support Person (Optional)</h4>
                            <p class="text-muted mb-4">Provide details of a support person if applicable</p>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="support_person_first_name" class="form-label">First Name</label>
                                    <input type="text" class="form-control" id="support_person_first_name" 
                                           name="support_person_first_name" value="{{ old('support_person_first_name') }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="support_person_last_name" class="form-label">Last Name</label>
                                    <input type="text" class="form-control" id="support_person_last_name" 
                                           name="support_person_last_name" value="{{ old('support_person_last_name') }}">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="support_person_email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="support_person_email" 
                                           name="support_person_email" value="{{ old('support_person_email') }}">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="support_person_phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="support_person_phone" 
                                           name="support_person_phone" value="{{ old('support_person_phone') }}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="support_person_relationship" class="form-label">Relationship</label>
                                <input type="text" class="form-control" id="support_person_relationship" 
                                       name="support_person_relationship" value="{{ old('support_person_relationship') }}">
                            </div>
                        </div>

                        <hr class="my-5">

                        <!-- SECTION 4: DOCUMENT UPLOADS -->
                        <div class="section mb-5" id="section-documents">
                            <h4 class="mb-3"><i class="bi bi-file-earmark-pdf me-2"></i>Document Uploads</h4>
                            <p class="text-muted mb-4">Upload required documents (PDF, JPG, PNG, DOC, DOCX)</p>

                            <div id="documents-container">
                                <div class="mb-3">
                                    <label for="documents" class="form-label">Upload Document *</label>
                                    <input type="file" class="form-control" id="documents" name="documents[]" 
                                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required multiple>
                                    <small class="form-text text-muted">Max 10MB per file</small>
                                </div>
                            </div>

                            <div id="document-list" class="mt-4"></div>
                        </div>

                        <hr class="my-5">

                        <!-- SECTION 5: AGREEMENT SIGNING -->
                        <div class="section mb-5" id="section-agreements">
                            <h4 class="mb-3"><i class="bi bi-pen me-2"></i>Agreements & Signatures</h4>
                            <p class="text-muted mb-4">Please review and sign all required agreements</p>

                            @if($agreements && count($agreements) > 0)
                                @foreach($agreements as $agreement)
                                    <div class="card mb-3 border">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0">
                                                <i class="bi bi-file-earmark-text me-2"></i>
                                                <a href="{{ route('participant.onboarding.agreement.show', ['token' => $token, 'agreement' => $agreement]) }}"
                                                   target="_blank" rel="noopener"
                                                   class="text-decoration-none text-dark">
                                                    {{ $agreement->title }}
                                                </a>
                                                @if($agreement->is_required)
                                                    <span class="badge bg-danger">Required</span>
                                                @endif
                                            </h5>
                                        </div>

                                        <div class="card-body">
                                            @if($agreement->description)
                                                <p class="text-muted small mb-3">{{ $agreement->description }}</p>
                                            @endif

                                            <div class="agreement-content mb-3 p-3 bg-light border rounded" 
                                                 style="max-height: 200px; overflow-y: auto; font-size: 0.9rem;">
                                                {!! nl2br(e($agreement->content)) !!}
                                            </div>

                                            <div class="mb-3">
                                                <a href="{{ route('participant.onboarding.agreement.show', ['token' => $token, 'agreement' => $agreement]) }}"
                                                   target="_blank" rel="noopener"
                                                   class="btn btn-sm btn-outline-primary me-2">
                                                    View agreement
                                                </a>
                                                <a href="{{ route('participant.onboarding.agreement.download', ['token' => $token, 'agreement' => $agreement]) }}"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    Download agreement
                                                </a>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Signature *</label>
                                                <div id="signature-pad-{{ $agreement->id }}" class="border rounded" 
                                                     style="height: 150px; background: white; cursor: crosshair;"></div>
                                                <input type="hidden" name="agreement_signatures[{{ $agreement->id }}]" 
                                                       id="signature-input-{{ $agreement->id }}">
                                                <small class="form-text text-muted d-block mt-2">Draw your signature above</small>
                                                <button type="button" class="btn btn-sm btn-secondary mt-2"
                                                        onclick="clearSignature({{ $agreement->id }})">Clear</button>
                                            </div>

                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" 
                                                       id="agree-{{ $agreement->id }}" 
                                                       name="agree[{{ $agreement->id }}]" required>
                                                <label class="form-check-label" for="agree-{{ $agreement->id }}">
                                                    I agree to this agreement
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle me-2"></i>
                                    No agreements have been assigned yet. You will be able to sign them once you've been approved.
                                </div>
                            @endif
                        </div>

                        <hr class="my-5">

                        <!-- Submit -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                                <i class="bi bi-check-circle me-2"></i>Submit Onboarding
                            </button>
                            <a href="{{ route('public.home') }}" class="btn btn-outline-secondary btn-lg">
                                Cancel
                            </a>
                        </div>

                        <small class="text-muted d-block mt-3 text-center">
                            * Required fields
                        </small>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Signature Pad Library -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

<script>
    // Initialize signature pads for each agreement
    document.addEventListener('DOMContentLoaded', function() {
        const agreements = @json($agreements ?? []);
        
        agreements.forEach(function(agreement) {
            const canvas = document.getElementById('signature-pad-' + agreement.id);
            if (canvas) {
                const signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(255, 255, 255)',
                    penColor: 'rgb(0, 0, 0)'
                });

                // Store signature on form submit
                document.querySelector('form').addEventListener('submit', function(e) {
                    if (!signaturePad.isEmpty()) {
                        document.getElementById('signature-input-' + agreement.id).value = 
                            signaturePad.toDataURL('image/png');
                    }
                });
            }
        });
    });

    function clearSignature(agreementId) {
        const canvas = document.getElementById('signature-pad-' + agreementId);
        if (canvas) {
            const signaturePad = SignaturePad.getInstance(canvas);
            if (signaturePad) {
                signaturePad.clear();
            }
        }
    }
</script>
@endsection
