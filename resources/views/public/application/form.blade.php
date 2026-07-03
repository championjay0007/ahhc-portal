@extends('layouts.public')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-primary text-white p-4">
                    <h2 class="mb-0">Apply for Self-Management Support</h2>
                    <p class="mb-0 mt-2 small">Step 1 of 7</p>
                </div>

                <div class="card-body p-4">
                    <p class="lead mb-4">Thank you for your interest in Allegiance Heart &amp; Home Care Self-Management Support. Please complete this application form to get started.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong>Please fix the following errors:</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('public.application.submit') }}" method="POST">
                        @csrf

                        <!-- Personal Information Section -->
                        <div class="form-section mb-4">
                            <h4 class="mb-3"><i class="bi bi-person-circle me-2"></i>Personal Information</h4>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label">First Name *</label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                           id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                    @error('first_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label">Last Name *</label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                           id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                                    @error('last_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Phone Number *</label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone') }}" required>
                                @error('phone')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <!-- Address Section -->
                        <div class="form-section mb-4">
                            <h4 class="mb-3"><i class="bi bi-geo-alt-fill me-2"></i>Address</h4>

                            <div class="mb-3">
                                <label for="address" class="form-label">Street Address *</label>
                                <input type="text" class="form-control @error('address') is-invalid @enderror" 
                                       id="address" name="address" value="{{ old('address') }}" required>
                                @error('address')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="city" class="form-label">City *</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" value="{{ old('city') }}" required>
                                    @error('city')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="state" class="form-label">State *</label>
                                    <select class="form-select @error('state') is-invalid @enderror" 
                                            id="state" name="state" required>
                                        <option value="">Select State</option>
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

                                <div class="col-md-3 mb-3">
                                    <label for="postcode" class="form-label">Postcode *</label>
                                    <input type="text" class="form-control @error('postcode') is-invalid @enderror" 
                                           id="postcode" name="postcode" value="{{ old('postcode') }}" required>
                                    @error('postcode')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Support Details Section -->
                        <div class="form-section mb-4">
                            <h4 class="mb-3"><i class="bi bi-info-circle me-2"></i>Support Details</h4>

                            <div class="mb-3">
                                <label for="disability_category" class="form-label">Disability Category</label>
                                <select class="form-select @error('disability_category') is-invalid @enderror" 
                                        id="disability_category" name="disability_category">
                                    <option value="">Select Category</option>
                                    <option value="physical_disability" {{ old('disability_category') === 'physical_disability' ? 'selected' : '' }}>Physical Disability</option>
                                    <option value="sensory_disability" {{ old('disability_category') === 'sensory_disability' ? 'selected' : '' }}>Sensory Disability</option>
                                    <option value="intellectual_disability" {{ old('disability_category') === 'intellectual_disability' ? 'selected' : '' }}>Intellectual Disability</option>
                                    <option value="psychosocial_disability" {{ old('disability_category') === 'psychosocial_disability' ? 'selected' : '' }}>Psychosocial Disability</option>
                                    <option value="multiple_disabilities" {{ old('disability_category') === 'multiple_disabilities' ? 'selected' : '' }}>Multiple Disabilities</option>
                                </select>
                                @error('disability_category')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="support_needs" class="form-label">Please describe your support needs</label>
                                <textarea class="form-control @error('support_needs') is-invalid @enderror" 
                                          id="support_needs" name="support_needs" rows="4">{{ old('support_needs') }}</textarea>
                                @error('support_needs')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="funding_source" class="form-label">Funding Source</label>
                                <select class="form-select @error('funding_source') is-invalid @enderror" 
                                        id="funding_source" name="funding_source">
                                    <option value="">Select Funding Source</option>
                                    <option value="ndis" {{ old('funding_source') === 'ndis' ? 'selected' : '' }}>NDIS</option>
                                    <option value="state_funded" {{ old('funding_source') === 'state_funded' ? 'selected' : '' }}>State Funded</option>
                                    <option value="private" {{ old('funding_source') === 'private' ? 'selected' : '' }}>Private</option>
                                    <option value="other" {{ old('funding_source') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('funding_source')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-check-circle me-2"></i>Submit Application
                            </button>
                        </div>

                        <small class="text-muted d-block mt-3 text-center">
                            * Required fields
                        </small>
                    </form>
                </div>
            </div>

            <div class="mt-4 text-center">
                <p class="text-muted">Already started your application? <a href="{{ route('public.home') }}">Return to home</a></p>
            </div>
        </div>
    </div>
</div>

<style>
    .form-section {
        border-bottom: 1px solid #e9ecef;
        padding-bottom: 1.5rem;
    }

    .form-section:last-of-type {
        border-bottom: none;
    }
</style>
@endsection
