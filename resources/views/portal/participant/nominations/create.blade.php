@extends('layouts.portal')

@section('title', 'Nominate Worker')

@section('content')
    <div class="mb-4">
        <h2>Nominate a Worker or Supplier</h2>
        <p class="text-muted">Provide details about the worker you'd like to nominate. Allegiance Heart &amp; Home Care will review your nomination and contact you before they gain access to the portal.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-4">
            <h5>There were errors with your submission:</h5>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card portal-card p-5 mb-4" style="max-width: 800px;">
        <form method="POST" action="{{ route('portal.participant.nominations.store') }}" enctype="multipart/form-data">
            @csrf

            <!-- Worker Information Section -->
            <h5 class="mb-3 border-bottom pb-2">Worker Information</h5>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="worker_full_name" class="form-label">
                        <strong>Worker Name</strong> <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="text" 
                        class="form-control @error('worker_full_name') is-invalid @enderror" 
                        id="worker_full_name" 
                        name="worker_full_name" 
                        value="{{ old('worker_full_name') }}"
                        placeholder="Full name"
                        required>
                    @error('worker_full_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="worker_type" class="form-label">
                        <strong>Worker Type</strong> <span class="text-danger">*</span>
                    </label>
                    <select class="form-select @error('worker_type') is-invalid @enderror" id="worker_type" name="worker_type" required>
                        <option value="">— Select Type —</option>
                        <option value="Independent" @selected(old('worker_type') === 'Independent')>Independent Worker</option>
                        <option value="Mable" @selected(old('worker_type') === 'Mable')>Mable Provider</option>
                        <option value="Supplier" @selected(old('worker_type') === 'Supplier')>Supplier / Service Provider</option>
                        <option value="Therapist" @selected(old('worker_type') === 'Therapist')>Therapist / Allied Health</option>
                        <option value="Other" @selected(old('worker_type') === 'Other')>Other</option>
                    </select>
                    @error('worker_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="worker_email" class="form-label">
                        <strong>Email Address</strong> <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="email" 
                        class="form-control @error('worker_email') is-invalid @enderror" 
                        id="worker_email" 
                        name="worker_email" 
                        value="{{ old('worker_email') }}"
                        placeholder="email@example.com"
                        required>
                    @error('worker_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Worker will receive invitation at this email</small>
                </div>

                <div class="col-md-6">
                    <label for="worker_phone" class="form-label">
                        <strong>Phone Number</strong> <span class="text-danger">*</span>
                    </label>
                    <input 
                        type="tel" 
                        class="form-control @error('worker_phone') is-invalid @enderror" 
                        id="worker_phone" 
                        name="worker_phone" 
                        value="{{ old('worker_phone') }}"
                        placeholder="+61 2 XXXX XXXX"
                        required>
                    @error('worker_phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="worker_address" class="form-label">
                    <strong>Address</strong> <span class="text-muted">(Optional)</span>
                </label>
                <input 
                    type="text" 
                    class="form-control @error('worker_address') is-invalid @enderror" 
                    id="worker_address" 
                    name="worker_address" 
                    value="{{ old('worker_address') }}"
                    placeholder="Street address">
                @error('worker_address')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Service Details Section -->
            <h5 class="mb-3 border-bottom pb-2">Service Details</h5>

            <div class="mb-4">
                <label for="service_type" class="form-label">
                    <strong>Service Type</strong> <span class="text-danger">*</span>
                </label>
                <input 
                    type="text" 
                    class="form-control @error('service_type') is-invalid @enderror" 
                    id="service_type" 
                    name="service_type" 
                    value="{{ old('service_type') }}"
                    placeholder="e.g., Personal Care, Cleaning, Meal Preparation, Transport, etc."
                    required>
                @error('service_type')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="estimated_hours" class="form-label">
                        <strong>Estimated Hours/Week</strong> <span class="text-muted">(Optional)</span>
                    </label>
                    <div class="input-group">
                        <input 
                            type="number" 
                            step="0.5"
                            min="0"
                            class="form-control @error('estimated_hours') is-invalid @enderror" 
                            id="estimated_hours" 
                            name="estimated_hours" 
                            value="{{ old('estimated_hours') }}"
                            placeholder="Hours">
                        <span class="input-group-text">hours/week</span>
                    </div>
                    @error('estimated_hours')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <label for="estimated_cost" class="form-label">
                        <strong>Estimated Cost/Week</strong> <span class="text-muted">(Optional)</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input 
                            type="number" 
                            step="0.01"
                            min="0"
                            class="form-control @error('estimated_cost') is-invalid @enderror" 
                            id="estimated_cost" 
                            name="estimated_cost" 
                            value="{{ old('estimated_cost') }}"
                            placeholder="0.00">
                        <span class="input-group-text">/week</span>
                    </div>
                    @error('estimated_cost')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-4">
                <label for="start_date" class="form-label">
                    <strong>Proposed Start Date</strong> <span class="text-muted">(Optional)</span>
                </label>
                <input 
                    type="date" 
                    class="form-control @error('start_date') is-invalid @enderror" 
                    id="start_date" 
                    name="start_date" 
                    value="{{ old('start_date') }}"
                    min="{{ now()->addDay()->toDateString() }}">
                @error('start_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <!-- Additional Information Section -->
            <h5 class="mb-3 border-bottom pb-2">Additional Information</h5>

            <div class="mb-4">
                <label for="notes" class="form-label">
                    <strong>Notes</strong> <span class="text-muted">(Optional)</span>
                </label>
                <textarea 
                    class="form-control @error('notes') is-invalid @enderror" 
                    id="notes" 
                    name="notes" 
                    rows="3"
                    placeholder="Any additional information about this worker that Allegiance Heart &amp; Home Care should know...">{{ old('notes') }}</textarea>
                @error('notes')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">e.g., special skills, certifications, availability constraints, etc.</small>
            </div>

            <!-- Documents Section -->
            <h5 class="mb-3 border-bottom pb-2">Supporting Documents <span class="text-muted">(Optional)</span></h5>

            <div class="mb-4">
                <div class="form-group">
                    <label for="documents" class="form-label">Upload Documents</label>
                    <div class="card border-2 border-dashed p-4 text-center" id="upload-area">
                        <input 
                            type="file" 
                            class="form-control d-none" 
                            id="documents" 
                            name="documents[]" 
                            multiple
                            accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
                        <i class="bi bi-cloud-upload" style="font-size: 2rem; color: #ccc;"></i>
                        <p class="mb-0 mt-2">
                            <a href="#" id="upload-link" class="link-primary">Click to upload</a> or drag and drop
                        </p>
                        <small class="text-muted">Resume, certifications, Mable profile link, insurance docs, etc.</small>
                    </div>
                    <div id="file-list" class="mt-3"></div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex gap-2 pt-3 border-top">
                <button type="submit" class="btn btn-primary btn-lg flex-grow-1">
                    Submit Nomination
                </button>
                <a href="{{ route('portal.participant.team') }}" class="btn btn-outline-secondary btn-lg">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <style>
        /* Mobile responsive adjustments for nomination form */
        @media (max-width: 374px) {
            .card.portal-card {
                max-width: 100% !important;
            }

            .card-body {
                padding: 1rem !important;
            }

            .btn-lg {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }

            .form-control, .form-select {
                font-size: 16px;
            }

            .d-flex {
                flex-wrap: wrap;
            }

            .d-flex.gap-2 {
                gap: 0.5rem;
            }

            .flex-grow-1 {
                width: 100%;
                margin-bottom: 0.5rem;
            }

            .d-flex > a {
                width: 100%;
            }

            .d-flex.pt-3 {
                padding-top: 0.75rem !important;
            }

            #upload-area {
                padding: 2rem 0.75rem !important;
            }

            #upload-area i {
                font-size: 1.5rem;
            }

            #upload-area p {
                font-size: 0.9rem;
            }
        }

        @media (max-width: 575px) {
            .card.p-5 {
                padding: 1.25rem !important;
            }

            .d-flex {
                flex-wrap: wrap;
            }

            .flex-grow-1 {
                flex-basis: 100%;
            }
        }

        /* Ensure file upload area is mobile-friendly */
        #upload-area {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        #upload-area:hover {
            background-color: #f8f9fa;
        }

        @media (max-width: 576px) {
            .card.border-2.border-dashed {
                padding: 1.5rem !important;
            }
        }
    </style>

    <script>
        // File upload handling
        const uploadArea = document.getElementById('upload-area');
        const uploadLink = document.getElementById('upload-link');
        const fileInput = document.getElementById('documents');
        const fileList = document.getElementById('file-list');

        uploadLink.addEventListener('click', (e) => {
            e.preventDefault();
            fileInput.click();
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('bg-light');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('bg-light');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('bg-light');
            fileInput.files = e.dataTransfer.files;
            updateFileList();
        });

        fileInput.addEventListener('change', updateFileList);

        function updateFileList() {
            fileList.innerHTML = '';
            if (fileInput.files.length > 0) {
                const list = document.createElement('ul');
                list.className = 'list-group';
                Array.from(fileInput.files).forEach(file => {
                    const item = document.createElement('li');
                    item.className = 'list-group-item d-flex justify-content-between align-items-center';
                    item.innerHTML = `
                        <span>
                            <i class="bi bi-file"></i> ${file.name}
                            <small class="text-muted">(${(file.size / 1024).toFixed(2)} KB)</small>
                        </span>
                        <span class="text-success"><i class="bi bi-check-circle"></i></span>
                    `;
                    list.appendChild(item);
                });
                fileList.appendChild(list);
            }
        }
    </script>
@endsection
