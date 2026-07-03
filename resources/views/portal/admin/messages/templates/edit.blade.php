@extends('layouts.admin')

@section('title', 'Edit Message Template')

@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css" rel="stylesheet">
    <style>
        .template-form-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2.5rem;
            border-radius: 20px;
            color: white;
            margin-bottom: 2rem;
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.2);
        }
        .template-form-container h1 {
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .template-form-container p {
            opacity: 0.95;
            margin-bottom: 0;
        }
        .card-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            border: 1px solid #e0e0e0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .form-group-wrapper {
            margin-bottom: 1.5rem;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .form-control, .form-select {
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .hint-text {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.5rem;
            background: #f8f9fa;
            padding: 0.75rem;
            border-left: 3px solid #667eea;
            border-radius: 6px;
        }
        .variables-section {
            background: linear-gradient(135deg, #f5f7fa 0%, #f0f2f5 100%);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1rem;
            border: 1px solid #e8eaed;
        }
        .variables-section strong {
            color: #333;
            display: block;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }
        .variables-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 0.75rem;
        }
        .variable-tag {
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            font-family: 'Courier New', monospace;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .variable-tag:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .btn-group-custom {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        .btn-group-custom .btn {
            flex: 1;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .note-editor.note-frame {
            border: 1px solid #ddd !important;
            border-radius: 10px !important;
        }
        .note-toolbar {
            background: #f8f9fa !important;
            border-bottom: 1px solid #ddd !important;
            border-radius: 10px 10px 0 0 !important;
        }
        .editor-mode-tabs .btn {
            border-radius: 10px;
            padding: 0.7rem 1rem;
            min-width: 90px;
        }
        .editor-mode-tabs .btn.active {
            background: #667eea;
            color: white;
        }
        .editor-panel {
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 0.75rem;
            background: #ffffff;
        }
        .theme-mode-toggle .btn {
            min-width: 90px;
        }
        .theme-mode-toggle .btn.active {
            background: #667eea;
            color: white;
        }
        .theme-html-preview {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
            min-height: 220px;
            padding: 1rem;
            overflow: auto;
            font-family: 'Courier New', monospace;
        }
        .theme-preview-output {
            min-height: 220px;
            border-radius: 12px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 1rem;
        }
        .advanced-preview-panel h6 {
            margin-bottom: 1rem;
            font-weight: 700;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="template-form-container">
        <h1><i class="bi bi-pencil-square"></i> Edit Message Template</h1>
        <p>Update and refine your message template design</p>
    </div>

    @php
        $selectedTheme = old('theme', $template->theme ?? 'clean');
        $themeHtmlValue = old('theme_html', $template->theme_html ?? $themeHtmlDefaults[$selectedTheme] ?? \App\Services\MessageService::getDefaultThemeHtml($selectedTheme));
    @endphp

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="bi bi-exclamation-circle"></i> Validation Errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('portal.admin.messages.templates.update', $template) }}" method="POST" id="templateForm">
                @csrf
                @method('PUT')

                <!-- Basic Information Section -->
                <div class="card-section">
                    <h5 class="mb-3"><i class="bi bi-info-circle" style="color: #667eea;"></i> Basic Information</h5>
                    
                    <div class="form-group-wrapper">
                        <label for="name" class="form-label">
                            <i class="bi bi-tag"></i> Template Name
                        </label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $template->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group-wrapper">
                                <label for="type" class="form-label">
                                    <i class="bi bi-bookmark"></i> Type
                                </label>
                                <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                                    <option value="general" {{ old('type', $template->type) === 'general' ? 'selected' : '' }}>General</option>
                                    <option value="alert" {{ old('type', $template->type) === 'alert' ? 'selected' : '' }}>Alert</option>
                                    <option value="notification" {{ old('type', $template->type) === 'notification' ? 'selected' : '' }}>Notification</option>
                                    <option value="compliance" {{ old('type', $template->type) === 'compliance' ? 'selected' : '' }}>Compliance</option>
                                    <option value="care_review" {{ old('type', $template->type) === 'care_review' ? 'selected' : '' }}>Care Review</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group-wrapper">
                                <label for="category" class="form-label">
                                    <i class="bi bi-folder"></i> Category
                                </label>
                                <input type="text" class="form-control" id="category" name="category" value="{{ old('category', $template->category) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group-wrapper">
                                <label class="form-label">
                                    <i class="bi bi-toggle-on"></i> Active
                                </label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $template->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Enable this template</label>
                                </div>
                                @error('is_active')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-section">
                    <h5 class="mb-3"><i class="bi bi-brush" style="color: #667eea;"></i> Template Styling</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-wrapper">
                                <label for="theme" class="form-label">
                                    <i class="bi bi-palette"></i> Theme
                                </label>
                                <select class="form-select @error('theme') is-invalid @enderror" id="theme" name="theme" required>
                                    @foreach($themes as $themeKey => $themeLabel)
                                        <option value="{{ $themeKey }}" {{ old('theme', $template->theme ?? 'clean') === $themeKey ? 'selected' : '' }}>{{ $themeLabel }}</option>
                                    @endforeach
                                </select>
                                @error('theme')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="hint-text"><i class="bi bi-info-circle"></i> Choose a ready-made theme or switch to Custom to add your own styling.</small>
                                <div class="theme-picker-grid d-flex flex-wrap gap-2 mt-3">
                                    @foreach($themes as $themeKey => $themeLabel)
                                        <button type="button" class="theme-option btn btn-sm btn-outline-secondary d-flex align-items-center justify-content-between" data-theme="{{ $themeKey }}" style="min-width: 145px; border-radius: 12px; padding: 0.75rem 0.9rem;">
                                            <span>{{ $themeLabel }}</span>
                                            <span class="theme-chip" data-theme="{{ $themeKey }}" style="width: 26px; height: 26px; border-radius: 8px; border: 1px solid #d1d5db;"></span>
                                        </button>
                                    @endforeach
                                </div>

                                <div class="form-group-wrapper mt-3">
                                    <label for="theme_html" class="form-label">
                                        <i class="bi bi-code-square"></i> Theme HTML
                                    </label>
                                    <div class="theme-mode-toggle btn-group mb-3" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-secondary active" data-theme-mode="code">Code</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-theme-mode="preview">Preview</button>
                                    </div>
                                    <textarea class="form-control @error('theme_html') is-invalid @enderror theme-html-editor" id="theme_html" name="theme_html" rows="8" placeholder="Edit the HTML wrapper for the selected theme.">{{ $themeHtmlValue }}</textarea>
                                    <div id="themeHtmlPreview" class="theme-html-preview mt-3" style="display: none;"></div>
                                    @error('theme_html')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="d-flex justify-content-between align-items-center mt-2">
                                        <small class="hint-text"><i class="bi bi-info-circle"></i> Use <code>@{{body}}</code> or <code>*|BODY|*</code> where the message content should be rendered.</small>
                                        <button type="button" id="resetThemeHtml" class="btn btn-outline-secondary btn-sm">Reset to theme default</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-wrapper" id="customStyleSection" style="display: none;">
                                <label for="custom_style" class="form-label">
                                    <i class="bi bi-code-square"></i> Custom CSS
                                </label>
                                <textarea class="form-control @error('custom_style') is-invalid @enderror" id="custom_style" name="custom_style" rows="5" placeholder="Add custom CSS for the Custom theme.">{{ old('custom_style', $template->custom_style) }}</textarea>
                                @error('custom_style')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="hint-text"><i class="bi bi-info-circle"></i> Enter custom styles that will apply to the preview card and email wrapper when Custom is selected.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Message Content Section -->
                <div class="card-section">
                    <h5 class="mb-3"><i class="bi bi-file-earmark-text" style="color: #667eea;"></i> Message Content</h5>

                    <div class="form-group-wrapper">
                        <label for="subject" class="form-label">
                            <i class="bi bi-envelope"></i> Subject Line
                        </label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject', $template->subject) }}" required>
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="hint-text"><i class="bi bi-info-circle"></i> Use variables like <code>@{{name}}</code> and <code>@{{email}}</code> in the subject.</small>
                    </div>

                    <div class="form-group-wrapper">
                        <label for="body" class="form-label">
                            <i class="bi bi-pencil"></i> Message Body
                        </label>
                        <textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body" rows="10" required>{{ old('body', $template->body) }}</textarea>
                        @error('body')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="hint-text"><i class="bi bi-info-circle"></i> Use the Summernote toolbar or code view to edit rich HTML content.</small>
                    </div>

                    <!-- Variables Helper -->
                    <div class="variables-section">
                        <strong><i class="bi bi-tags"></i> Available Variables - Click to insert</strong>
                        <div class="variables-grid">
                            <span class="variable-tag" onclick="insertVariable('@{{name}}')"><i class="bi bi-person"></i> @{{name}}</span>
                            <span class="variable-tag" onclick="insertVariable('@{{user_name}}')"><i class="bi bi-person"></i> @{{user_name}}</span>
                            <span class="variable-tag" onclick="insertVariable('@{{first_name}}')"><i class="bi bi-person"></i> @{{first_name}}</span>
                            <span class="variable-tag" onclick="insertVariable('@{{last_name}}')"><i class="bi bi-person"></i> @{{last_name}}</span>
                            <span class="variable-tag" onclick="insertVariable('@{{email}}')"><i class="bi bi-envelope"></i> @{{email}}</span>
                            <span class="variable-tag" onclick="insertVariable('@{{date}}')"><i class="bi bi-calendar"></i> @{{date}}</span>
                            <span class="variable-tag" onclick="insertVariable('@{{participant_name}}')"><i class="bi bi-person-circle"></i> @{{participant_name}}</span>
                            <span class="variable-tag" onclick="insertVariable('@{{worker_name}}')"><i class="bi bi-person-badge"></i> @{{worker_name}}</span>
                            <span class="variable-tag" onclick="insertVariable('@{{organization}}')"><i class="bi bi-building"></i> @{{organization}}</span>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="btn-group-custom">
                    <a href="{{ route('portal.admin.messages.templates.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Update Template
                    </button>
                </div>
            </form>
        </div>

        <!-- Preview Panel -->
        <div class="col-lg-4">
            <div class="card-section" style="position: sticky; top: 20px;">
                <h5 class="mb-3"><i class="bi bi-eye" style="color: #667eea;"></i> Preview</h5>
                <div style="background: #f8f9fa; border-radius: 10px; padding: 1.5rem; border: 1px solid #ddd;">
                    <div id="templatePreviewCard" style="background: white; border-radius: 8px; padding: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #e0e0e0;">
                        <div id="previewType" style="font-weight: 600; color: #667eea; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.75rem;">{{ strtoupper($template->type) }}</div>
                        <div id="previewSubject" style="font-size: 1.25rem; font-weight: 700; color: #333; margin-bottom: 1rem; word-break: break-word;">{{ $template->subject }}</div>
                        <div id="previewBody" style="color: #666; line-height: 1.6; font-size: 0.95rem; word-break: break-word;">{!! $template->body !!}</div>
                    </div>
                    <div class="advanced-preview-panel mt-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6>Theme Preview</h6>
                            <span class="badge bg-light text-dark text-uppercase">Live</span>
                        </div>
                        <div id="themePreviewOutput" class="theme-preview-output"></div>
                    </div>
                    <style id="previewCustomStyle"></style>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"></script>
    <script>
        const themeStyles = {
            clean: {
                background: '#ffffff',
                border: '1px solid #e0e0e0',
                typeColor: '#667eea',
                subjectColor: '#1f2937',
                bodyColor: '#4b5563',
                cardShadow: '0 2px 8px rgba(0,0,0,0.08)'
            },
            modern: {
                background: '#f8fafc',
                border: '1px solid #d1d5db',
                typeColor: '#0f766e',
                subjectColor: '#111827',
                bodyColor: '#4b5563',
                cardShadow: '0 6px 20px rgba(15, 23, 42, 0.08)'
            },
            warm: {
                background: '#fff7ed',
                border: '1px solid #fcd9b6',
                typeColor: '#d97706',
                subjectColor: '#92400e',
                bodyColor: '#7c2d12',
                cardShadow: '0 6px 20px rgba(124, 45, 18, 0.08)'
            },
            elegant: {
                background: '#f5f3ff',
                border: '1px solid #c7d2fe',
                typeColor: '#7c3aed',
                subjectColor: '#3730a3',
                bodyColor: '#4c51bf',
                cardShadow: '0 6px 20px rgba(55, 48, 163, 0.08)'
            },
            custom: {
                background: '#ffffff',
                border: '1px solid #e0e0e0',
                typeColor: '#667eea',
                subjectColor: '#1f2937',
                bodyColor: '#4b5563',
                cardShadow: '0 2px 8px rgba(0,0,0,0.08)'
            }
        };

        const themeHtmlDefaults = @json($themeHtmlDefaults);
        let themeHtmlEdited = false;
        let currentThemeHtmlDefault = null;

        function initializeEditor() {
            $('#body').summernote({
                height: 300,
                minHeight: 300,
                maxHeight: 600,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'hr']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onChange: function(contents) {
                        document.getElementById('body').value = contents;
                        updatePreview();
                    }
                }
            });

            document.querySelectorAll('[data-theme-mode]').forEach(button => {
                button.addEventListener('click', () => {
                    setThemeHtmlMode(button.dataset.themeMode);
                });
            });
        }

        function getBodySourceHtml() {
            if (typeof $.fn.summernote !== 'undefined' && $('#body').closest('.note-editor').length) {
                return $('#body').summernote('code') || '';
            }
            return document.getElementById('body').value || '';
        }

        function insertVariable(variable) {
            const editor = $('#body');
            if (editor.length && typeof editor.summernote === 'function') {
                const current = editor.summernote('code') || '';
                editor.summernote('code', current + variable);
            } else {
                document.getElementById('body').value += variable;
            }
            updatePreview();
        }

        function updateThemeHtmlField() {
            const theme = document.getElementById('theme').value;
            const themeHtmlField = document.getElementById('theme_html');
            const newDefault = themeHtmlDefaults[theme] ?? themeHtmlDefaults.clean;

            if (currentThemeHtmlDefault === null) {
                if (!themeHtmlField.value.trim()) {
                    themeHtmlField.value = newDefault;
                }
            } else if (!themeHtmlEdited && (themeHtmlField.value.trim() === '' || themeHtmlField.value === currentThemeHtmlDefault)) {
                themeHtmlField.value = newDefault;
                themeHtmlEdited = false;
            }

            currentThemeHtmlDefault = newDefault;
            updateThemeHtmlPreview();
            updateThemeWrapperPreview();
        }

        function getDefaultThemeHtml(themeKey) {
            return themeHtmlDefaults[themeKey] ?? themeHtmlDefaults.clean;
        }

        function resetThemeHtmlDefault() {
            const theme = document.getElementById('theme').value;
            const themeHtmlField = document.getElementById('theme_html');
            const newDefault = getDefaultThemeHtml(theme);

            themeHtmlField.value = newDefault;
            themeHtmlEdited = false;
            currentThemeHtmlDefault = newDefault;
            updateThemeHtmlPreview();
            updateThemeWrapperPreview();
        }

        function markThemeHtmlEdited() {
            themeHtmlEdited = true;
            updateThemeHtmlPreview();
            updateThemeWrapperPreview();
        }

        function buildThemePreviewHtml(templateHtml) {
            const bodyContent = getBodySourceHtml() || '<p>Your message content will appear here...</p>';
            const placeholders = ['@{{body}}', '*|BODY|*'];
            let output = templateHtml;

            placeholders.forEach(placeholder => {
                output = output.split(placeholder).join(bodyContent);
            });

            if (output === templateHtml) {
                return `${templateHtml}${bodyContent}`;
            }

            return output;
        }

        function updateThemeHtmlPreview() {
            const previewBox = document.getElementById('themeHtmlPreview');
            const modeButton = document.querySelector('[data-theme-mode].active');
            if (!previewBox || !modeButton) {
                return;
            }
            const mode = modeButton.dataset.themeMode;
            previewBox.style.display = mode === 'preview' ? 'block' : 'none';
            if (mode === 'preview') {
                previewBox.innerHTML = buildThemePreviewHtml(document.getElementById('theme_html').value || getDefaultThemeHtml(document.getElementById('theme').value));
            }
        }

        function setThemeHtmlMode(mode) {
            document.querySelectorAll('[data-theme-mode]').forEach(button => {
                button.classList.toggle('active', button.dataset.themeMode === mode);
            });
            updateThemeHtmlPreview();
        }

        function updateThemeWrapperPreview() {
            const previewOutput = document.getElementById('themePreviewOutput');
            if (!previewOutput) {
                return;
            }
            previewOutput.innerHTML = buildThemePreviewHtml(document.getElementById('theme_html').value || getDefaultThemeHtml(document.getElementById('theme').value));
        }

        function updatePreview() {
            const theme = document.getElementById('theme').value;
            const previewCard = document.getElementById('templatePreviewCard');
            const previewType = document.getElementById('previewType');
            const previewSubject = document.getElementById('previewSubject');
            const previewBody = document.getElementById('previewBody');
            const previewCustomStyle = document.getElementById('previewCustomStyle');
            const customStyleSection = document.getElementById('customStyleSection');
            const customStyle = document.getElementById('custom_style').value;

            const currentTheme = themeStyles[theme] || themeStyles.clean;
            previewCard.style.background = currentTheme.background;
            previewCard.style.border = currentTheme.border;
            previewCard.style.boxShadow = currentTheme.cardShadow;
            previewType.style.color = currentTheme.typeColor;
            previewSubject.style.color = currentTheme.subjectColor;
            previewBody.style.color = currentTheme.bodyColor;

            previewType.textContent = document.getElementById('type').value.toUpperCase() || 'GENERAL';
            previewSubject.textContent = document.getElementById('subject').value || 'Your Subject Here';
            previewBody.innerHTML = getBodySourceHtml() || 'Your message content will appear here...';

            if (theme === 'custom') {
                customStyleSection.style.display = 'block';
                previewCustomStyle.textContent = customStyle;
            } else {
                customStyleSection.style.display = 'none';
                previewCustomStyle.textContent = '';
            }
            setActiveThemeOption(theme);
            updateThemeWrapperPreview();
        }

        function initThemePicker() {
            document.querySelectorAll('.theme-option').forEach(button => {
                const themeKey = button.dataset.theme;
                const chip = button.querySelector('.theme-chip');
                if (themeStyles[themeKey] && chip) {
                    chip.style.background = themeStyles[themeKey].background;
                    chip.style.borderColor = themeStyles[themeKey].border;
                }
                button.addEventListener('click', () => {
                    document.getElementById('theme').value = themeKey;
                    updateThemeHtmlField();
                    updatePreview();
                });
            });
            setActiveThemeOption(document.getElementById('theme').value);
        }

        function setActiveThemeOption(theme) {
            document.querySelectorAll('.theme-option').forEach(button => {
                if (button.dataset.theme === theme) {
                    button.classList.add('btn-primary');
                    button.classList.remove('btn-outline-secondary');
                    button.style.color = '#ffffff';
                } else {
                    button.classList.remove('btn-primary');
                    button.classList.add('btn-outline-secondary');
                    button.style.color = '';
                }
            });
        }

        function insertVariable(variable) {
            const editor = $('#body');
            if (editor.length && typeof editor.summernote === 'function') {
                const current = editor.summernote('code') || '';
                editor.summernote('code', current + variable);
            } else {
                document.getElementById('body').value += variable;
            }
            updatePreview();
        }

        document.addEventListener('DOMContentLoaded', function() {
            initializeEditor();
            initThemePicker();
            updateThemeHtmlField();
            document.getElementById('subject').addEventListener('input', updatePreview);
            document.getElementById('type').addEventListener('change', updatePreview);
            document.getElementById('theme').addEventListener('change', function() {
                updateThemeHtmlField();
                updatePreview();
            });
            document.getElementById('custom_style').addEventListener('input', updatePreview);
            document.getElementById('theme_html').addEventListener('input', markThemeHtmlEdited);
            document.getElementById('resetThemeHtml').addEventListener('click', function() {
                resetThemeHtmlDefault();
                updatePreview();
            });
            updatePreview();
        });
    </script>
@endpush
@endsection
