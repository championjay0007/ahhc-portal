@extends('layouts.public')

@section('content')
    <style>
        .enquiry-page {
            min-height: 75vh;
            padding-top: 9rem;
            background: white;
            color: var(--text);
        }

        .enquiry-form-card {
            background: white;
            border-radius: var(--radius-xl);
            padding: 2.5rem;
            box-shadow: var(--shadow-xl);
            color: var(--text);
        }

        .enquiry-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.9rem;
            margin-bottom: 1rem;
            border-radius: 999px;
            background: var(--brand-soft);
            color: var(--brand);
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .enquiry-title {
            margin-bottom: 0.6rem;
            color: var(--text);
            font-size: clamp(2rem, 4vw, 2.5rem);
            font-weight: 800;
        }

        .enquiry-intro {
            margin-bottom: 1.75rem;
            color: var(--text-secondary);
            line-height: 1.7;
        }

        .enquiry-form-card .form-label {
            display: block;
            margin-bottom: 0.4rem;
            color: var(--text);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .enquiry-form-card .form-input-custom,
        .enquiry-form-card .form-select-custom,
        .enquiry-form-card .form-textarea-custom {
            width: 100%;
            padding: 0.9rem 1.2rem;
            border: 1.5px solid var(--border);
            border-radius: 14px;
            font-size: 0.95rem;
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--surface-alt);
            transition: all 0.15s ease;
            color: var(--text);
        }

        .enquiry-form-card .form-input-custom:focus,
        .enquiry-form-card .form-select-custom:focus,
        .enquiry-form-card .form-textarea-custom:focus {
            outline: none;
            border-color: var(--accent);
            background: white;
            box-shadow: 0 0 0 4px rgba(22, 153, 161, 0.1);
        }

        .enquiry-form-card .form-textarea-custom {
            min-height: 120px;
            resize: vertical;
        }

        .enquiry-form-card .form-select-custom {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 2.5rem;
        }

        .enquiry-form-card .form-check-custom {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            cursor: pointer;
        }

        .enquiry-form-card .form-check-custom input[type="checkbox"] {
            margin-top: 0.2rem;
            width: 18px;
            height: 18px;
            accent-color: var(--accent);
            flex-shrink: 0;
        }

        .enquiry-form-card .invalid-feedback {
            color: #ef4444;
        }

        .enquiry-error-summary {
            border: 1px solid #fecaca;
            background: #fee2e2;
            color: #991b1b;
        }

        .enquiry-page-note {
            color: var(--text-muted);
        }

        @media (max-width: 576px) {
            .enquiry-form-card {
                padding: 1.5rem;
            }
        }
    </style>

    <section class="enquiry-page section-padding">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 col-xl-7">
                    <div class="enquiry-form-card">
                        <div class="mb-4">
                            <span class="enquiry-eyebrow">
                                <i class="bi bi-envelope"></i> Get in Touch
                            </span>
                            <h1 class="enquiry-title">Self-Management Enquiry</h1>
                            <p class="enquiry-intro mb-0">
                                Tell us what you’re looking for and our intake team will explain how self-management could work for you. There is no obligation to proceed.
                            </p>
                        </div>

                        @if(session('status'))
                            <div class="alert alert-success" role="status">
                                <i class="bi bi-check-circle-fill me-2"></i>{{ session('status') }}
                            </div>
                        @endif

                        @if($errors->any())
                            <div class="alert enquiry-error-summary" role="alert" aria-labelledby="enquiry-error-heading">
                                <h2 id="enquiry-error-heading" class="h6 fw-bold mb-2">
                                    Please correct the following and try again:
                                </h2>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form id="enquiryForm" method="POST" action="{{ route('public.enquiries.store') }}">
                            @csrf

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Full Name <span style="color: #ef4444;">*</span></label>
                                    <input type="text" name="name" id="name" class="form-input-custom @error('name') is-invalid @enderror"
                                           value="{{ old('name') }}" autocomplete="name" required aria-describedby="name-error">
                                    @error('name')
                                        <div id="name-error" class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="email" class="form-label">Email Address <span style="color: #ef4444;">*</span></label>
                                    <input type="email" name="email" id="email" class="form-input-custom @error('email') is-invalid @enderror"
                                           value="{{ old('email') }}" autocomplete="email" required aria-describedby="email-error">
                                    @error('email')
                                        <div id="email-error" class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="phone" class="form-label">Phone Number</label>
                                    <input type="tel" name="phone" id="phone" class="form-input-custom @error('phone') is-invalid @enderror"
                                           value="{{ old('phone') }}" autocomplete="tel" aria-describedby="phone-error">
                                    @error('phone')
                                        <div id="phone-error" class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="role" class="form-label">Your Role <span style="color: #ef4444;">*</span></label>
                                    <select name="role" id="role" class="form-select-custom @error('role') is-invalid @enderror" required aria-describedby="role-error">
                                        <option value="">Select your role...</option>
                                        <option value="participant" @selected(old('role') === 'participant')>Participant</option>
                                        <option value="family_member" @selected(old('role') === 'family_member')>Family Member / Carer</option>
                                        <option value="representative" @selected(old('role') === 'representative')>Legal Representative</option>
                                        <option value="support_coordinator" @selected(old('role') === 'support_coordinator')>Support Coordinator</option>
                                        <option value="worker" @selected(old('role') === 'worker')>Worker / Provider</option>
                                        <option value="other" @selected(old('role') === 'other')>Other</option>
                                    </select>
                                    @error('role')
                                        <div id="role-error" class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="support_status" class="form-label">Support at Home Status</label>
                                    <select name="support_status" id="support_status" class="form-select-custom @error('support_status') is-invalid @enderror" aria-describedby="support-status-error">
                                        <option value="">Select your current status...</option>
                                        <option value="have_approval" @selected(old('support_status') === 'have_approval')>I have Support at Home approval</option>
                                        <option value="awaiting_approval" @selected(old('support_status') === 'awaiting_approval')>Awaiting approval</option>
                                        <option value="exploring" @selected(old('support_status') === 'exploring')>Exploring options / Not yet applied</option>
                                        <option value="funding_assigned" @selected(old('support_status') === 'funding_assigned')>Funding assigned</option>
                                    </select>
                                    @error('support_status')
                                        <div id="support-status-error" class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label for="message" class="form-label">Message <span style="color: var(--text-muted);">(Optional)</span></label>
                                    <textarea name="message" id="message" rows="5" maxlength="2000"
                                              class="form-textarea-custom @error('message') is-invalid @enderror"
                                              aria-describedby="message-error">{{ old('message') }}</textarea>
                                    @error('message')
                                        <div id="message-error" class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-check-custom">
                                        <input type="checkbox" name="consent" value="1" id="consent"
                                               class="@error('consent') is-invalid @enderror"
                                               @checked(old('consent')) required aria-describedby="consent-error">
                                        <span>
                                            I consent to Allegiance Heart &amp; Home Care contacting me about my enquiry and understand that submitting this form does not automatically create portal access.
                                            <span style="color: #ef4444;">*</span>
                                        </span>
                                    </label>
                                    @error('consent')
                                        <div id="consent-error" class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                @error('error')
                                    <div class="col-12">
                                        <div class="alert alert-danger mb-0" role="alert">{{ $message }}</div>
                                    </div>
                                @enderror

                                <div class="col-12">
                                    <button id="enquirySubmitBtn" type="submit" class="btn-primary-custom w-100" style="justify-content: center;">
                                        <span class="submit-label"><i class="bi bi-send-fill me-2"></i>Submit My Enquiry</span>
                                        <span class="submit-spinner d-none"><span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Submitting your enquiry...</span>
                                    </button>
                                    <p class="small text-muted text-center mt-3 mb-0">
                                        Our intake team will contact you to discuss your options and next steps.
                                    </p>
                                </div>
                            </div>
                        </form>
                    </div>

                    <p class="text-center enquiry-page-note mt-4 mb-0">
                        Prefer to speak with us? Call <a href="tel:+61287309049">02 8730 9049</a>.
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('enquiryForm');
            const submitButton = document.getElementById('enquirySubmitBtn');

            if (!form || !submitButton) {
                return;
            }

            form.addEventListener('submit', function () {
                if (!form.checkValidity()) {
                    return;
                }

                submitButton.disabled = true;
                submitButton.querySelector('.submit-label').classList.add('d-none');
                submitButton.querySelector('.submit-spinner').classList.remove('d-none');
            });
        });
    </script>
@endpush
