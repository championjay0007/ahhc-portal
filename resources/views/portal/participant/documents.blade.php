@extends('layouts.portal')

@section('content')
    @php
        $categories = \App\Models\Document::participantDocumentCategories();
        $requiredCategories = \App\Models\Document::mandatoryParticipantDocumentCategories();
        $allowedTypes = implode(', ', array_map('strtoupper', \App\Models\Document::ALLOWED_FILE_EXTENSIONS));
        $maxSizeMb = \App\Models\Document::MAX_FILE_SIZE_KB / 1024;
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Upload required onboarding documents</h2>
            <p class="text-muted">Submit the documents needed to complete your onboarding and move toward portal activation.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-success" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <strong>Document uploaded successfully!</strong> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('status'))
        <div class="alert alert-info alert-dismissible fade show border-info" role="alert">
            <i class="bi bi-info-circle-fill me-2"></i>
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="alert alert-warning border-warning mb-4">
        <strong>Participant onboarding is incomplete.</strong>
        Next steps: complete the onboarding checklist (profile details, verification steps, upload required documents, and sign required agreements).
        At least one onboarding document is required before activation. Upload any one of: <strong>Care Plan, Support Plan, Identification</strong>.
    </div>

    <div class="card border-secondary bg-light p-3 mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h5 class="mb-1">Mandatory documents status</h5>
                <p class="text-muted mb-0">These are the categories needed for onboarding review.</p>
            </div>
            <span class="badge bg-{{ $missingMandatory->isEmpty() ? 'success' : 'warning' }} text-dark">
                {{ $missingMandatory->isEmpty() ? 'Ready' : 'Incomplete' }}
            </span>
        </div>
        <div class="mt-3">
            @foreach($requiredCategories as $category)
                <span class="badge rounded-pill me-2 mb-2 bg-{{ $uploadedCategories->contains($category) ? 'success' : 'secondary' }}">
                    {{ $category }}
                </span>
            @endforeach
        </div>
    </div>

    <div class="row gy-4">
        <div class="col-lg-6">
            <div class="card portal-card p-4 mb-4">
                <h5 class="mb-3">Upload new document</h5>

                @if($missingMandatory->isNotEmpty())
                    <div class="alert alert-warning">
                        <strong>Required onboarding documents still missing:</strong>
                        {{ $missingMandatory->implode(', ') }}.
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">Required onboarding document progress</label>
                    <div class="progress" style="height: 1rem;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $mandatoryCompletion }}%;" aria-valuenow="{{ $mandatoryCompletion }}" aria-valuemin="0" aria-valuemax="100">
                            {{ $mandatoryCompletion }}%
                        </div>
                    </div>
                    <div class="form-text">Upload at least one required onboarding document to begin the onboarding review process. Additional required categories are optional.</div>
                </div>

                <form id="document-upload-form" method="POST" action="{{ route('portal.participant.documents.store') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Quick upload for mandatory documents</label>
                        <div class="btn-group d-flex flex-wrap gap-2" role="group" aria-label="Quick select document category">
                            <button type="button" class="btn btn-outline-primary" onclick="selectMandatoryCategory('Care Plan')">Care Plan</button>
                            <button type="button" class="btn btn-outline-primary" onclick="selectMandatoryCategory('Support Plan')">Support Plan</button>
                            <button type="button" class="btn btn-outline-primary" onclick="selectMandatoryCategory('Identification')">Identification</button>
                        </div>
                        <small class="form-text text-muted">Use one of these buttons to quickly choose a mandatory onboarding category.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="title">Document title</label>
                        <input id="title" type="text" name="title" value="{{ old('title') }}" class="form-control @error('title') is-invalid @enderror" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="document_type">Category</label>
                        <select id="document_type" name="document_type" class="form-select @error('document_type') is-invalid @enderror" required>
                            <option value="">Select category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ old('document_type') === $category ? 'selected' : '' }}>{{ $category }}</option>
                            @endforeach
                        </select>
                        @error('document_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">File</label>
                        <div id="drop-zone" class="border rounded p-4 text-center bg-light" style="min-height: 180px; cursor: pointer;">
                            <div class="mb-2">
                                <strong>Drag & drop a file here</strong>
                            </div>
                            <div class="text-muted">or click to browse</div>
                            <input id="file" type="file" name="file" class="form-control form-control-file d-none @error('file') is-invalid @enderror" required>
                            @error('file')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div id="selected-file" class="mt-3 text-muted">No file selected.</div>
                        <div id="file-size-help" class="form-text">Accepted formats: {{ $allowedTypes }}. Max size: {{ $maxSizeMb }} MB.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="notes">Notes (optional)</label>
                        <textarea id="notes" name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                    </div>

                    <div class="mb-3">
                        <div class="progress d-none" id="upload-progress" style="height: 1rem;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <div id="upload-status" class="small text-muted mt-2"></div>
                    </div>

                    <button type="submit" class="btn btn-primary">Upload document</button>
                </form>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card portal-card p-4 mb-4">
                <h5 class="mb-3">Onboarding document checklist</h5>
                <ul class="list-group list-group-flush">
                    @foreach($requiredCategories as $category)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>{{ $category }}</span>
                            @if($uploadedCategories->contains($category))
                                <span class="badge bg-success">Uploaded</span>
                            @else
                                <span class="badge bg-secondary">Missing</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    <div class="card portal-card p-4">
        <h5 class="mb-3">My documents</h5>
        @if($documents->isEmpty())
            <p class="text-muted">No documents uploaded yet.</p>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Uploaded</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $document)
                            <tr>
                                <td>{{ $document->title }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $document->document_type)) }}</td>
                                <td>
                                    @if($document->status === 'signed')
                                        <span class="badge bg-success">Signed</span>
                                    @elseif($document->status === 'uploaded')
                                        <span class="badge bg-info">Pending Review</span>
                                    @else
                                        <span class="badge bg-secondary">{{ ucfirst($document->status) }}</span>
                                    @endif
                                </td>
                                <td>{{ $document->created_at->format('Y-m-d') }}</td>
                                <td class="text-end">
                                    @if($document->hasStoredFilePath())
                                        <a href="{{ route('portal.participant.documents.preview', $document) }}" class="btn btn-sm btn-outline-secondary me-2">Preview</a>
                                        <a href="{{ route('portal.participant.documents.download', $document) }}" class="btn btn-sm btn-outline-primary me-2">Download</a>
                                    @endif
                                    <a href="{{ route('portal.participant.documents.show', $document) }}" class="btn btn-sm btn-primary">View</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dropZone = document.getElementById('drop-zone');
            const fileInput = document.getElementById('file');
            const selectedFile = document.getElementById('selected-file');
            const fileHelp = document.getElementById('file-size-help');
            const form = document.getElementById('document-upload-form');
            const progressWrapper = document.getElementById('upload-progress');
            const progressBar = progressWrapper?.querySelector('.progress-bar');
            const uploadStatus = document.getElementById('upload-status');
            const maxSizeBytes = {{ \App\Models\Document::MAX_FILE_SIZE_BYTES }};

            if (!dropZone || !fileInput || !form) {
                return;
            }

            const updateFileDisplay = () => {
                const file = fileInput.files[0];
                if (!file) {
                    selectedFile.textContent = 'No file selected.';
                    return;
                }
                selectedFile.textContent = `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
                if (file.size > maxSizeBytes) {
                    fileHelp.textContent = 'File is too large. Maximum allowed size is 10MB.';
                    fileHelp.classList.add('text-danger');
                } else {
                    fileHelp.textContent = 'Accepted formats: {{ $allowedTypes }}. Max size: {{ $maxSizeMb }} MB.';
                    fileHelp.classList.remove('text-danger');
                }
            };

            dropZone.addEventListener('click', () => fileInput.click());

            dropZone.addEventListener('dragenter', function (event) {
                event.preventDefault();
                dropZone.classList.add('border-primary', 'bg-white');
            });
            dropZone.addEventListener('dragover', function (event) {
                event.preventDefault();
            });
            dropZone.addEventListener('dragleave', function () {
                dropZone.classList.remove('border-primary', 'bg-white');
            });
            dropZone.addEventListener('drop', function (event) {
                event.preventDefault();
                dropZone.classList.remove('border-primary', 'bg-white');
                if (event.dataTransfer.files.length) {
                    fileInput.files = event.dataTransfer.files;
                    updateFileDisplay();
                }
            });

            fileInput.addEventListener('change', updateFileDisplay);

            form.addEventListener('submit', function (event) {
                event.preventDefault();
                const file = fileInput.files[0];
                if (!file) {
                    fileHelp.textContent = 'Select a file before uploading.';
                    fileHelp.classList.add('text-danger');
                    return;
                }
                if (file.size > maxSizeBytes) {
                    fileHelp.textContent = 'File is too large. Maximum allowed size is 10MB.';
                    fileHelp.classList.add('text-danger');
                    return;
                }

                const formData = new FormData(form);
                const xhr = new XMLHttpRequest();

                xhr.open('POST', form.action);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');
                xhr.responseType = 'json';

                xhr.upload.addEventListener('progress', function (event) {
                    if (!event.lengthComputable || !progressWrapper || !progressBar) {
                        return;
                    }
                    const percent = Math.round((event.loaded / event.total) * 100);
                    progressWrapper.classList.remove('d-none');
                    progressBar.style.width = `${percent}%`;
                    progressBar.setAttribute('aria-valuenow', percent);
                    progressBar.textContent = `${percent}%`;
                });

                xhr.addEventListener('load', function () {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        uploadStatus.textContent = 'Upload complete. Reloading...';
                        window.location.reload();
                        return;
                    }

                    let message = 'Upload failed. Please try again.';
                    if (xhr.response && xhr.response.message) {
                        message = xhr.response.message;
                    }

                    uploadStatus.textContent = message;
                    uploadStatus.classList.add('text-danger');
                    progressWrapper.classList.add('d-none');
                });

                xhr.addEventListener('error', function () {
                    uploadStatus.textContent = 'Upload failed due to a network error.';
                    uploadStatus.classList.add('text-danger');
                    progressWrapper.classList.add('d-none');
                });

                uploadStatus.textContent = 'Uploading...';
                uploadStatus.classList.remove('text-danger');
                progressWrapper.classList.remove('d-none');
                progressBar.style.width = '0%';
                progressBar.textContent = '0%';
                xhr.send(formData);
            });
        });

        function selectMandatoryCategory(category) {
            const categorySelect = document.getElementById('document_type');
            const titleInput = document.getElementById('title');
            if (!categorySelect || !titleInput) {
                return;
            }

            categorySelect.value = category;
            titleInput.value = `${category} Upload`;
            titleInput.focus();
        }
    </script>
@endsection
