<section id="step-6" class="wizard-card-step">
    <div class="mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="badge bg-primary text-white wizard-step-indicator">6</div>
            <div>
                <h2 class="h5 mb-1">Documents</h2>
                <p class="text-muted mb-0">Upload any care documents or forms we need to keep on file.</p>
            </div>
        </div>
    </div>

        @php
        $documentCategories = \App\Models\Document::participantDocumentCategoryOptions();
        $requiredCategories = \App\Models\Document::mandatoryParticipantDocumentCategories();
    @endphp

    <div class="mb-3">
        <label class="form-label">Document category</label>
        <select name="document_type" class="form-select" aria-label="Document category">
            <option value="">Select a category</option>
            @foreach($documentCategories as $category)
                <option value="{{ $category }}" {{ old('document_type', $draftData['document_type'] ?? '') === $category ? 'selected' : '' }}>{{ $category }}</option>
            @endforeach
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Document title</label>
        <input type="text" name="document_title" class="form-control" value="{{ old('document_title', $draftData['document_title'] ?? '') }}">
    </div>

    <div class="mb-3">
        <label class="form-label">Document description</label>
        <textarea name="document_description" class="form-control" rows="3">{{ old('document_description', $draftData['document_description'] ?? '') }}</textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">Upload file</label>
        <input type="file" name="document_file" class="form-control">
    </div>

    <div class="alert alert-info">
        <strong>Onboarding documents:</strong> {{ implode(', ', $requiredCategories) }}.
        Upload at least one of these to complete onboarding; the others are optional.
    </div>

    <div class="form-text">Upload a PDF, image, or document file. You can save a draft and return later if needed.</div>

    @php
        $adminAssignedDocs = \App\Models\Document::where('onboarding_required', true)
            ->where('status', 'active')
            ->get();
    @endphp

    {{-- Removed admin-assigned required forms list: checkboxes with inline links are sufficient --}}
</section>
