@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2>Assign New Form</h2>
            <p class="text-muted mb-0">Upload a signable form and assign it to a participant or worker.</p>
        </div>
        <div>
            <a href="{{ route('portal.admin.documents') }}" class="btn btn-outline-secondary">Back to forms</a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('portal.admin.documents.store') }}" enctype="multipart/form-data">
                @csrf

                @php
                    $selectedOwnerType = old('owner_type') ?: ($ownerType ?? 'participant');
                @endphp

                <div class="mb-3">
                    <label for="title" class="form-label">Form title</label>
                    <input id="title" name="title" type="text" class="form-control" value="{{ old('title') }}" required>
                </div>

                <div class="mb-3">
                    <label for="document_type" class="form-label">Form type</label>
                    <select id="document_type" name="document_type" class="form-select" required>
                        @foreach($documentTypes as $type => $label)
                            <option value="{{ $type }}"{{ old('document_type') === $type ? ' selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3" id="owner_type_block">
                    <label for="owner_type" class="form-label">Assign to</label>
                    <select id="owner_type" name="owner_type" class="form-select" required>
                        @foreach($ownerTypes as $type)
                            <option value="{{ $type }}"{{ $selectedOwnerType === $type ? ' selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $type)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3 owner-select owner-select-participant" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label for="owner_id_participant" class="form-label mb-0">Participants</label>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="select_all_participants">Select All</button>
                    </div>
                    <select id="owner_id_participant" name="owner_ids[]" class="form-select" multiple size="6" required {{ ($selectedOwnerType !== 'participant') ? 'disabled' : '' }}>
                        @foreach($participants as $participant)
                            <option value="{{ $participant->id }}"{{ $selectedOwnerType === 'participant' && in_array($participant->id, old('owner_ids', [])) ? ' selected' : '' }}>
                                {{ $participant->first_name }} {{ $participant->last_name }} ({{ $participant->participant_number }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Hold Ctrl/Cmd to select multiple participants.</div>
                </div>

                <div class="mb-3 owner-select owner-select-worker" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label for="owner_id_worker" class="form-label mb-0">Workers</label>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="select_all_workers">Select All</button>
                    </div>
                    <select id="owner_id_worker" name="owner_ids[]" class="form-select" multiple size="6" required {{ ($selectedOwnerType !== 'worker') ? 'disabled' : '' }}>
                        @foreach($workers as $worker)
                            <option value="{{ $worker->id }}"{{ $selectedOwnerType === 'worker' && in_array($worker->id, old('owner_ids', [])) ? ' selected' : '' }}>
                                {{ $worker->first_name }} {{ $worker->last_name }} ({{ $worker->worker_number }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Hold Ctrl/Cmd to select multiple workers.</div>
                </div>

                <div class="mb-3 owner-select owner-select-invoice" style="display: none;">
                    <label for="owner_id_invoice" class="form-label">Invoice</label>
                    <select id="owner_id_invoice" name="owner_ids[]" class="form-select" required {{ ($selectedOwnerType !== 'invoice') ? 'disabled' : '' }}>
                        <option value="">Select an invoice</option>
                        @foreach($invoices as $invoice)
                            <option value="{{ $invoice->id }}"{{ (old('owner_type') === 'invoice') && (old('owner_ids')[0] ?? null) == $invoice->id ? ' selected' : '' }}>
                                {{ $invoice->invoice_number }} • {{ ucfirst($invoice->status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3 owner-select owner-select-incident" style="display: none;">
                    <label for="owner_id_incident" class="form-label">Incident</label>
                    <select id="owner_id_incident" name="owner_ids[]" class="form-select" required {{ ($selectedOwnerType !== 'incident') ? 'disabled' : '' }}>
                        <option value="">Select an incident</option>
                        @foreach($incidents as $incident)
                            <option value="{{ $incident->id }}"{{ (old('owner_type') === 'incident') && (old('owner_ids')[0] ?? null) == $incident->id ? ' selected' : '' }}>
                                Incident #{{ $incident->id }} • {{ $incident->occurred_at->format('Y-m-d') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3 owner-select owner-select-pre_approval" style="display: none;">
                    <label for="owner_id_pre_approval" class="form-label">Pre-approval request</label>
                    <select id="owner_id_pre_approval" name="owner_ids[]" class="form-select" required {{ ($selectedOwnerType !== 'pre_approval') ? 'disabled' : '' }}>
                        <option value="">Select a pre-approval</option>
                        @foreach($preApprovals as $preApproval)
                            <option value="{{ $preApproval->id }}"{{ (old('owner_type') === 'pre_approval') && (old('owner_ids')[0] ?? null) == $preApproval->id ? ' selected' : '' }}>
                                Request #{{ $preApproval->id }} • {{ ucfirst($preApproval->status) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3 owner-select owner-select-care_review" style="display: none;">
                    <label for="owner_id_care_review" class="form-label">Care review</label>
                    <select id="owner_id_care_review" name="owner_ids[]" class="form-select" required {{ ($selectedOwnerType !== 'care_review') ? 'disabled' : '' }}>
                        <option value="">Select a care review</option>
                        @foreach($careReviews as $careReview)
                            <option value="{{ $careReview->id }}"{{ (old('owner_type') === 'care_review') && (old('owner_ids')[0] ?? null) == $careReview->id ? ' selected' : '' }}>
                                Review #{{ $careReview->id }} • {{ $careReview->created_at->format('Y-m-d') }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="expires_at" class="form-label">Expiration date</label>
                    <input id="expires_at" name="expires_at" type="date" class="form-control" value="{{ old('expires_at') }}">
                </div>

                <div class="mb-3">
                    <label for="description" class="form-label">Description (optional)</label>
                    <textarea id="description" name="description" class="form-control">{{ old('description') }}</textarea>
                </div>

                <div class="mb-3 form-check">
                    <input id="onboarding_required" name="onboarding_required" type="checkbox" value="1" class="form-check-input"{{ old('onboarding_required') ? ' checked' : '' }}>
                    <label for="onboarding_required" class="form-check-label">Assign this form as required for onboarding</label>
                </div>

                <div class="mb-3 form-check">
                    <input id="is_sensitive" name="is_sensitive" type="checkbox" value="1" class="form-check-input"{{ old('is_sensitive', '1') ? ' checked' : '' }}>
                    <label for="is_sensitive" class="form-check-label">Mark this document as sensitive</label>
                </div>

                <div class="mb-3">
                    <label for="file" class="form-label">Form file</label>
                    <input id="file" name="file" type="file" class="form-control @error('file') is-invalid @enderror" required>
                    @error('file')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Accepted formats: PDF, JPG, PNG, DOC, DOCX, XLS, XLSX, CSV. Maximum size: 10MB.</div>
                </div>

                <hr class="my-4">

                <div class="mb-3">
                    <label for="supporting_documents" class="form-label">Supporting documents (optional)</label>
                    <p class="text-muted small mb-2">Upload documents that must be viewed before signing. Users must click and review each document before the sign button becomes available.</p>
                    <input id="supporting_documents" name="supporting_documents[]" type="file" class="form-control @error('supporting_documents.*') is-invalid @enderror" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.csv">
                    @error('supporting_documents.*')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">Upload one or more supporting documents (PDFs, images, or Office files). Maximum 10MB per file.</div>
                    <div id="supporting-files-list" class="mt-2"></div>
                </div>

                <button type="submit" class="btn btn-primary">Assign form</button>
            </form>
        </div>
    </div>
</div>

<script>
    (function(){
        const ownerType = document.getElementById('owner_type');
        const ownerBlocks = Array.from(document.querySelectorAll('.owner-select'));

        function updateOwnerVisibility() {
            const value = ownerType.value;

            ownerBlocks.forEach((block) => {
                const select = block.querySelector('select');
                const isActive = block.classList.contains(`owner-select-${value}`);

                block.style.display = isActive ? 'block' : 'none';
                if (select) {
                    select.disabled = !isActive;
                }
            });
        }

        ownerType.addEventListener('change', function () {
            updateOwnerVisibility();
        });

        document.getElementById('select_all_participants').addEventListener('click', function() {
            const select = document.getElementById('owner_id_participant');
            for (let i = 0; i < select.options.length; i++) {
                select.options[i].selected = true;
            }
        });

        document.getElementById('select_all_workers').addEventListener('click', function() {
            const select = document.getElementById('owner_id_worker');
            for (let i = 0; i < select.options.length; i++) {
                select.options[i].selected = true;
            }
        });

        updateOwnerVisibility();

        const fileInput = document.getElementById('file');
        const fileErrorContainer = document.createElement('div');
        fileErrorContainer.className = 'text-danger small mt-2';
        fileInput.parentNode.appendChild(fileErrorContainer);

        fileInput.addEventListener('change', function () {
            fileErrorContainer.textContent = '';
            const file = fileInput.files[0];
            if (!file) {
                return;
            }

            if (file.size > {{ \App\Models\Document::MAX_FILE_SIZE_BYTES }}) {
                fileErrorContainer.textContent = 'File is too large. Maximum allowed size is 10MB.';
            }
        });

        const formElement = document.querySelector('form');
        formElement.addEventListener('submit', function (event) {
            const file = fileInput.files[0];
            if (file && file.size > {{ \App\Models\Document::MAX_FILE_SIZE_BYTES }}) {
                event.preventDefault();
                fileErrorContainer.textContent = 'File is too large. Maximum allowed size is 10MB.';
            }
            
            // Remove disabled attribute from all select elements before submission
            // so they are included in the form data
            ownerBlocks.forEach((block) => {
                const select = block.querySelector('select');
                if (select) {
                    select.disabled = false;
                }
            });
        });

        // Handle supporting documents
        const supportingDocsInput = document.getElementById('supporting_documents');
        const supportingFilesList = document.getElementById('supporting-files-list');

        function updateSupportingFilesList() {
            supportingFilesList.innerHTML = '';
            if (supportingDocsInput.files.length === 0) {
                return;
            }

            const ul = document.createElement('ul');
            ul.className = 'list-unstyled small';
            Array.from(supportingDocsInput.files).forEach((file, index) => {
                const li = document.createElement('li');
                li.className = 'd-flex justify-content-between align-items-center p-2 border-bottom';
                li.innerHTML = `
                    <span>
                        <i class="bi bi-file-earmark"></i>
                        ${escapeHtml(file.name)}
                        <span class="text-muted">(${(file.size / 1024 / 1024).toFixed(2)} MB)</span>
                    </span>
                    <button type="button" class="btn btn-sm btn-outline-danger" data-index="${index}">Remove</button>
                `;
                ul.appendChild(li);
            });
            supportingFilesList.appendChild(ul);

            // Add remove handlers
            document.querySelectorAll('[data-index]').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const index = parseInt(this.dataset.index);
                    const dt = new DataTransfer();
                    Array.from(supportingDocsInput.files).forEach((file, i) => {
                        if (i !== index) dt.items.add(file);
                    });
                    supportingDocsInput.files = dt.files;
                    updateSupportingFilesList();
                });
            });
        }

        supportingDocsInput.addEventListener('change', updateSupportingFilesList);
        updateSupportingFilesList();

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, m => map[m]);
        }
    })();
</script>

@endsection
