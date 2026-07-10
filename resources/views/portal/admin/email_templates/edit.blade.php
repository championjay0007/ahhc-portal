@extends('layouts.admin')

@section('title', 'Edit Email Template')

@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css" rel="stylesheet">
    <style>
        .edit-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.15);
        }
        .edit-header h1 { font-size: 1.8rem; font-weight: 700; margin: 0; }
        .edit-header p { margin: 0.5rem 0 0; opacity: 0.9; }
        
        .form-section { background: white; border-radius: 12px; padding: 2rem; margin-bottom: 1.5rem; border: 1px solid #e2e8f0; }
        .form-section-title { font-weight: 700; color: #1e293b; margin-bottom: 1.5rem; font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem; }
        .form-section-title i { color: #667eea; }
        
        .form-label { font-weight: 600; color: #1e293b; margin-bottom: 0.5rem; }
        .form-control, .form-select { border: 1px solid #cbd5e1; border-radius: 8px; transition: all 0.3s ease; }
        .form-control:focus, .form-select:focus { border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        
        .variables-section {
            background: linear-gradient(135deg, #f5f7fa 0%, #eef2ff 100%);
            border: 2px solid #c7d2fe;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1.5rem;
        }
        .variables-section strong { color: #4338ca; font-size: 1rem; display: flex; align-items: center; gap: 0.5rem; }
        .variables-section strong i { color: #667eea; }
        .variables-section span {
            display: inline-flex;
            margin: 0.5rem 0.5rem 0.5rem 0;
            padding: 0.5rem 0.9rem;
            border-radius: 24px;
            background: white;
            color: #4338ca;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
            border: 1px solid #c7d2fe;
        }
        
        .version-card { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; }
        .version-title { font-weight: 700; color: #1e293b; margin-bottom: 1rem; font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem; }
        .version-title i { color: #667eea; }
        
        .version-item { background: white; border-radius: 8px; padding: 1rem; margin-bottom: 0.75rem; border-left: 4px solid #667eea; }
        .version-item:last-child { margin-bottom: 0; }
        .version-label { font-weight: 600; color: #1e293b; }
        .version-time { font-size: 0.85rem; color: #64748b; margin-top: 0.25rem; }
        .version-subject { font-size: 0.9rem; color: #475569; margin-top: 0.5rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .version-actions { margin-top: 0.75rem; }
        
        .custom-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8'%3E%3Cpath fill='%23667eea' d='M0 0l6 8 6-8z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 36px;
        }
        
        .action-buttons { display: flex; gap: 1rem; margin-top: 2rem; flex-wrap: wrap; }
        .action-buttons .btn { border-radius: 8px; font-weight: 600; }
        
        .sample-template-select { background: linear-gradient(135deg, #f5f7fa 0%, #e0e7ff 100%); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; }
        .sample-template-select .form-label { color: #667eea; margin-bottom: 0.75rem; }
        
        .alert-errors { background: #fef2f2; border-left: 4px solid #ef4444; border-radius: 8px; }
        .alert-errors ul { margin: 0; padding-left: 1.5rem; }
        .alert-errors li { color: #7f1d1d; }
    </style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="edit-header d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-pencil-square me-2"></i>Edit Email Template</h1>
            <p>Refine this template and see the detected variables in real time.</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('portal.admin.messages.email_templates.duplicate', $emailTemplate) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-light" style="border-radius: 8px; font-weight: 600;">
                    <i class="bi bi-files me-2"></i>Clone Template
                </button>
            </form>
            <a href="{{ route('portal.admin.messages.email_templates.index') }}" class="btn btn-outline-light" style="border-radius: 8px; font-weight: 600;">
                <i class="bi bi-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-errors alert-dismissible fade show" role="alert" style="border-radius: 10px;">
            <h5 style="color: #7f1d1d; margin-bottom: 0.75rem;"><i class="bi bi-exclamation-triangle me-2"></i>Validation Errors</h5>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <form action="{{ route('portal.admin.messages.email_templates.update', $emailTemplate) }}" method="POST" id="templateForm">
                @csrf
                @method('PUT')

                <div class="form-section">
                    <div class="form-section-title"><i class="bi bi-file-text"></i>Basic Information</div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label"><i class="bi bi-type me-2"></i>Template Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $emailTemplate->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label"><i class="bi bi-tag me-2"></i>Purpose</label>
                            <input list="categoryList" type="text" id="category" name="category" class="form-control custom-select @error('category') is-invalid @enderror" value="{{ old('category', $emailTemplate->category) }}" placeholder="e.g. Onboarding invitation, Account activation, Invoice reminder">
                            <datalist id="categoryList">
                                @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->name }}"></option>
                                @endforeach
                                @foreach($purposes ?? [] as $purpose)
                                    <option value="{{ $purpose }}"></option>
                                @endforeach
                            </datalist>
                            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-circle-fill me-2" style="font-size: 0.5rem;"></i>Status</label>
                            <div class="form-check form-switch" style="padding-top: 0.35rem;">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $emailTemplate->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Publish this template</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title"><i class="bi bi-envelope"></i>Email Content</div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label"><i class="bi bi-subject me-2"></i>Email Subject <span class="text-danger">*</span></label>
                        <input type="text" id="subject" name="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject', $emailTemplate->subject) }}" required>
                        @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="form-text text-muted" style="display: block; margin-top: 0.5rem;"><i class="bi bi-info-circle me-1"></i>Use placeholders like <code>@{{name}}</code>, <code>@{{email}}</code>, <code>@{{unsubscribe_url}}</code></small>
                    </div>

                    <div class="sample-template-select">
                        <label for="sample_template" class="form-label"><i class="bi bi-layout-split me-2"></i>Load Sample Template</label>
                        <select id="sample_template" class="form-select custom-select" style="border-radius: 8px; border: 1px solid #cbd5e1;">
                            <option value="">-- Choose a sample layout --</option>
                            <option value="welcome">Welcome Email</option>
                            <option value="reminder">Reminder Notice</option>
                            <option value="unsubscribe">Unsubscribe Confirmation</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="html_body" class="form-label"><i class="bi bi-file-richtext me-2"></i>HTML Body <span class="text-danger">*</span></label>
                        <textarea id="html_body" name="html_body" class="form-control @error('html_body') is-invalid @enderror" rows="10">{{ old('html_body', $emailTemplate->html_body) }}</textarea>
                        @error('html_body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="form-text text-muted" style="display: block; margin-top: 0.5rem;"><i class="bi bi-info-circle me-1"></i>Edit with rich formatting or use Summernote code view for raw HTML.</small>
                    </div>

                    <div class="mb-3">
                        <label for="text_body" class="form-label"><i class="bi bi-file-text me-2"></i>Plain Text Body</label>
                        <textarea id="text_body" name="text_body" class="form-control @error('text_body') is-invalid @enderror" rows="5" readonly>{{ old('text_body', $emailTemplate->text_body) }}</textarea>
                        @error('text_body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="form-text text-muted" style="display: block; margin-top: 0.5rem;"><i class="bi bi-info-circle me-1"></i>This is generated from the main content and stays consistent with the editor.</small>
                    </div>

                    <div class="mb-3">
                        <label for="variables" class="form-label"><i class="bi bi-braces me-2"></i>Template Variables</label>
                        <textarea id="variables" name="variables" class="form-control @error('variables') is-invalid @enderror" rows="3" placeholder="one variable per line&#10;name&#10;email&#10;activation_code">{{ old('variables', implode($emailTemplate->variables ?? [], "\n")) }}</textarea>
                        @error('variables')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="form-text text-muted" style="display: block; margin-top: 0.5rem;"><i class="bi bi-info-circle me-1"></i>List any additional variables you want to make available for this template.</small>
                    </div>

                    <div class="variables-section">
                        <strong><i class="bi bi-braces"></i>Detected Variables</strong>
                        @php
                            $detectedVariables = $emailTemplate->variables ?? [];
                        @endphp
                        <div id="detectedVariables" class="mt-3">
                            @if (! empty($detectedVariables))
                                @foreach ($detectedVariables as $value)
                                    <span>{{ $value }}</span>
                                @endforeach
                            @else
                                No variables detected yet.
                            @endif
                        </div>
                    </div>

                    <div class="variables-section mt-3">
                        <strong><i class="bi bi-list-ul"></i>Available Variables</strong>
                        <div class="mt-3">
                            @php
                                $availableVariables = $availableVariables ?? [];
                            @endphp

                            @if(!empty($availableVariables))
                                @foreach($availableVariables as $key => $desc)
                                    <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.5rem;">
                                        <span style="padding:0.4rem 0.7rem;border-radius:6px;background:white;border:1px solid #e6eefc;color:#1e293b;font-weight:700;">{{ $key }}</span>
                                        <small style="color:#475569;">{{ $desc }}</small>
                                    </div>
                                @endforeach
                            @else
                                <div style="color:#64748b;">No shared variables defined.</div>
                            @endif
                        </div>
                    </div>

                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Update Template
                        </button>
                        <a href="{{ route('portal.admin.messages.email_templates.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="version-card">
                <div class="version-title"><i class="bi bi-clock-history"></i>Version History</div>
                @if($emailTemplate->versions->isNotEmpty())
                    <div>
                        @foreach($emailTemplate->versions as $version)
                            <div class="version-item">
                                <div class="version-label">
                                    <i class="bi bi-tag me-1" style="color: #667eea;"></i>Version {{ $version->version_number }}
                                </div>
                                <div class="version-time">{{ $version->created_at->diffForHumans() }}</div>
                                <div class="version-subject" title="{{ $version->subject }}">{{ $version->subject }}</div>
                                <div class="version-actions">
                                    <form action="{{ route('portal.admin.messages.email_templates.versions.restore', [$emailTemplate, $version]) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary" style="border-radius: 6px; font-size: 0.85rem; font-weight: 600;">
                                            <i class="bi bi-arrow-counterclockwise me-1"></i>Restore
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="background: #f8fafc; border-radius: 8px; padding: 1rem; text-align: center; color: #64748b;">
                        <i class="bi bi-inbox" style="font-size: 1.5rem; display: block; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                        <p style="margin: 0; font-size: 0.9rem;">No prior versions yet. Every save creates a new snapshot automatically.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"></script>
    <script>
        function extractVariables(value) {
            const matches = [...value.matchAll(/\{\{\s*(\w+)\s*\}\}/g)];
            return [...new Set(matches.map(m => m[1]))];
        }

        function updateDetectedVariables() {
            const subjectValue = document.getElementById('subject').value || '';
            const htmlValue = $('#html_body').summernote('code') || '';
            const textValue = document.getElementById('text_body').value || '';
            const customVariablesValue = document.getElementById('variables').value || '';
            const customVariables = [...new Set(customVariablesValue.split(/\r?\n/).map(value => value.trim()).filter(Boolean))];
            const variables = [...new Set([
                ...extractVariables(subjectValue),
                ...extractVariables(htmlValue),
                ...extractVariables(textValue),
                ...customVariables,
            ])];

            const container = document.getElementById('detectedVariables');
            container.innerHTML = variables.length
                ? variables.map(name => `<span>${name}</span>`).join('')
                : 'No variables detected yet.';
        }

        function syncPlainTextBody() {
            const htmlValue = $('#html_body').summernote('code') || '';
            const textArea = document.getElementById('text_body');
            const normalizedHtml = htmlValue
                .replace(/<br\s*\/?>/gi, '\n')
                .replace(/<\/(p|div|li|ul|ol|tr|table|section|article|header|footer|h[1-6])>/gi, '\n')
                .replace(/<[^>]+>/g, '');
            const plainText = normalizedHtml
                .replace(/&nbsp;/gi, ' ')
                .replace(/\s+/g, ' ')
                .trim();
            textArea.value = plainText.replace(/\s+/g, ' ').trim();
            updateDetectedVariables();
        }

        document.addEventListener('DOMContentLoaded', function () {
            $('#html_body').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['insert', ['link', 'picture', 'hr']],
                    ['view', ['fullscreen', 'codeview']],
                ],
                callbacks: {
                    onChange: function () {
                        syncPlainTextBody();
                    }
                }
            });

            document.getElementById('subject').addEventListener('input', updateDetectedVariables);
            document.getElementById('variables').addEventListener('input', updateDetectedVariables);

            syncPlainTextBody();
        });
    </script>
@endpush
