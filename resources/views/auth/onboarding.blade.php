@extends('layouts.auth')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-xl-10">
            <div class="card shadow-sm border-0 overflow-hidden">
                <div class="row g-0">
                    <div class="col-lg-4 bg-light p-4">
                        <div class="mb-4">
                            <h3 class="h5 mb-2">Onboarding wizard</h3>
                            <p class="text-muted small mb-0">Resume your portal setup for {{ $participant->first_name }} {{ $participant->last_name }}.</p>
                        </div>

                        <div class="mb-4">
                            <div class="wizard-progress mb-2">
                                <div class="wizard-progress-bar" role="progressbar" style="width: {{ $progress->completionPercentage() }}%;" aria-valuenow="{{ $progress->completionPercentage() }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="d-flex justify-content-between small text-muted">
                                <span>Step {{ $progress->current_step }} of 8</span>
                                <span>{{ $progress->completionPercentage() }}% complete</span>
                            </div>
                        </div>

                        <nav class="nav flex-column wizard-step-nav" aria-label="Onboarding steps">
                            @foreach(['Account', 'MFA', 'Profile', 'Emergency', 'Support', 'Documents', 'Agreements', 'Review'] as $index => $label)
                                <a href="#" class="nav-link text-start py-2 px-3 mb-2 {{ $progress->current_step === $index + 1 ? 'active' : '' }}" data-step="{{ $index + 1 }}">
                                    <strong>{{ $index + 1 }}.</strong> {{ $label }}
                                </a>
                            @endforeach
                        </nav>

                        <div class="mt-4 small text-muted">
                            <p class="mb-1"><strong>Status:</strong> {{ ucfirst($progress->status) }}</p>
                            <p class="mb-0">You can save a draft at any time and return with the same onboarding link.</p>
                        </div>
                    </div>

                    <div class="col-lg-8 p-4">
                        @if(session('status'))
                            <div class="alert alert-success">{{ session('status') }}</div>
                        @endif

                        @if(! empty($requireMfa))
                            <div class="alert alert-info">
                                <strong>MFA required:</strong> After completing onboarding, you may need to set up multi-factor authentication to access the portal.
                            </div>
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

                        <form id="onboarding-form" method="POST" action="{{ route('portal.onboarding.submit', ['token' => $token]) }}" enctype="multipart/form-data" novalidate>
                            @csrf
                            <input type="hidden" name="current_step" id="current_step" value="{{ $progress->current_step }}">
                            <input type="hidden" name="save_draft" id="save_draft" value="0">

                            @include('auth.onboarding.steps.account')
                            @include('auth.onboarding.steps.mfa')
                            @include('auth.onboarding.steps.profile')
                            @include('auth.onboarding.steps.emergency')
                            @include('auth.onboarding.steps.support')
                            @include('auth.onboarding.steps.documents')
                            @include('auth.onboarding.steps.agreements')
                            @include('auth.onboarding.steps.review')

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <button type="button" class="btn btn-outline-secondary" id="wizard-prev-btn">Back</button>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-secondary" id="save-draft-btn">Save draft</button>
                                    <button type="button" class="btn btn-primary" id="wizard-next-btn">Next step</button>
                                    <button type="submit" class="btn btn-success d-none" id="wizard-finish-btn">Finalize onboarding</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .wizard-card-step { display: none; }
    .wizard-card-step.active { display: block; }
    .wizard-step-nav .nav-link { border-radius: .75rem; background: #ffffff; color: #495057; border: 1px solid #e9ecef; }
    .wizard-step-nav .nav-link.active { background: #0d6efd; color: #ffffff; }
    .wizard-step-nav .nav-link:hover { background: #e7f1ff; }
    .wizard-progress { height: .5rem; border-radius: 999px; overflow: hidden; background: #e9ecef; }
    .wizard-progress-bar { background: linear-gradient(135deg, #0d6efd, #6610f2); transition: width .25s ease; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const totalSteps = 8;
    const form = document.getElementById('onboarding-form');
    const currentStepInput = document.getElementById('current_step');
    const saveDraftInput = document.getElementById('save_draft');
    const prevBtn = document.getElementById('wizard-prev-btn');
    const nextBtn = document.getElementById('wizard-next-btn');
    const finishBtn = document.getElementById('wizard-finish-btn');
    const saveDraftBtn = document.getElementById('save-draft-btn');
    const stepLinks = document.querySelectorAll('.wizard-step-nav .nav-link');
    const steps = Array.from(document.querySelectorAll('.wizard-card-step'));
    const progressBar = document.querySelector('.wizard-progress-bar');

    let activeStep = parseInt(currentStepInput.value, 10) || 1;

    const updateStepVisibility = () => {
        steps.forEach((step, index) => {
            const stepNumber = index + 1;
            const isActive = stepNumber === activeStep;
            step.classList.toggle('active', isActive);
        });

        stepLinks.forEach((link, index) => {
            const stepNumber = index + 1;
            link.classList.toggle('active', stepNumber === activeStep);
            link.classList.toggle('disabled', stepNumber > activeStep);
            link.setAttribute('aria-current', stepNumber === activeStep ? 'step' : 'false');
        });

        currentStepInput.value = activeStep;
        const progress = Math.round(((activeStep - 1) / (totalSteps - 1)) * 100);
        progressBar.style.width = progress + '%';

        if (activeStep === 1) {
            prevBtn.disabled = true;
        } else {
            prevBtn.disabled = false;
        }

        if (activeStep === totalSteps) {
            nextBtn.classList.add('d-none');
            finishBtn.classList.remove('d-none');
        } else {
            nextBtn.classList.remove('d-none');
            finishBtn.classList.add('d-none');
        }
    };

    const validateStep = () => {
        const currentSection = steps[activeStep - 1];
        const requiredFields = Array.from(currentSection.querySelectorAll('[data-required]'));
        let valid = true;

        requiredFields.forEach(field => {
            if (field.type === 'checkbox') {
                if (!field.checked) {
                    field.classList.add('is-invalid');
                    valid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            } else if (!field.value || !field.value.toString().trim()) {
                field.classList.add('is-invalid');
                valid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        return valid;
    };

    stepLinks.forEach(link => {
        link.addEventListener('click', event => {
            event.preventDefault();
            const step = parseInt(link.dataset.step, 10);
            if (step <= activeStep) {
                activeStep = step;
                updateStepVisibility();
            }
        });
    });

    prevBtn.addEventListener('click', () => {
        if (activeStep > 1) {
            activeStep -= 1;
            updateStepVisibility();
        }
    });

    nextBtn.addEventListener('click', () => {
        if (!validateStep()) {
            return;
        }
        if (activeStep < totalSteps) {
            activeStep += 1;
            updateStepVisibility();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    });

    saveDraftBtn.addEventListener('click', () => {
        saveDraftInput.value = '1';
        form.submit();
    });

    form.addEventListener('submit', () => {
        saveDraftInput.value = saveDraftInput.value === '1' ? '1' : '0';
    });

    updateStepVisibility();
})();
</script>
@endpush
