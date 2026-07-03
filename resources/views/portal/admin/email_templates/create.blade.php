@extends('layouts.admin')

@section('title', 'Create Email Template')

@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css" rel="stylesheet">
    <style>
        .create-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.15);
        }
        .create-header h1 { font-size: 1.8rem; font-weight: 700; margin: 0; }
        .create-header p { margin: 0.5rem 0 0; opacity: 0.9; }
        
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
        
        .custom-select {
            appearance: none;
            background-image: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8'%3E%3Cpath fill='%23667eea' d='M0 0l6 8 6-8z'/%3E%3C/svg%3E\");
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
    <div class="create-header d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-file-earmark-plus me-2"></i>Create Email Template</h1>
            <p>Save reusable email templates with sample variable detection and preview support.</p>
        </div>
        <a href="{{ route('portal.admin.messages.email_templates.index') }}" class="btn btn-outline-light" style="border-radius: 8px; font-weight: 600;">
            <i class="bi bi-arrow-left me-2"></i>Back to Templates
        </a>
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
            <form action="{{ route('portal.admin.messages.email_templates.store') }}" method="POST" id="templateForm">
                @csrf

                <div class="form-section">
                    <div class="form-section-title"><i class="bi bi-file-text"></i>Basic Information</div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label"><i class="bi bi-type me-2"></i>Template Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Welcome Email, Order Confirmation" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label"><i class="bi bi-tag me-2"></i>Category</label>
                            <input list="categoryList" type="text" id="category" name="category" class="form-control custom-select @error('category') is-invalid @enderror" value="{{ old('category') }}" placeholder="e.g. Onboarding, Newsletter, Alerts">
                            <datalist id="categoryList">
                                @foreach($categories ?? [] as $category)
                                    <option value="{{ $category->name }}"></option>
                                @endforeach
                            </datalist>
                            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-circle-fill me-2" style="font-size: 0.5rem;\"></i>Status</label>
                            <div class="form-check form-switch" style="padding-top: 0.35rem;">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Publish this template</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <div class="form-section-title"><i class="bi bi-envelope"></i>Email Content</div>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label"><i class="bi bi-subject me-2"></i>Email Subject <span class="text-danger">*</span></label>
                        <input type="text" id="subject" name="subject" class="form-control @error('subject') is-invalid @enderror" value="{{ old('subject') }}" placeholder="Welcome to our platform, {{'{{'}}name{{'}}'}}" required>
                        @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="form-text text-muted" style="display: block; margin-top: 0.5rem;"><i class="bi bi-info-circle me-1"></i>Use placeholders like <code>{{'{{'}}name{{'}}'}}</code>, <code>{{'{{'}}email{{'}}'}}</code>, <code>{{'{{'}}unsubscribe_url{{'}}'}}</code></small>
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
                        <textarea id="html_body" name="html_body" class="form-control @error('html_body') is-invalid @enderror" rows="10">{{ old('html_body') }}</textarea>
                        @error('html_body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="form-text text-muted" style="display: block; margin-top: 0.5rem;"><i class="bi bi-info-circle me-1"></i>Edit with rich formatting or use Summernote code view for raw HTML.</small>
                    </div>

                    <div class="mb-3">
                        <label for="text_body" class="form-label"><i class="bi bi-file-text me-2"></i>Plain Text Body</label>
                        <textarea id="text_body" name="text_body" class="form-control @error('text_body') is-invalid @enderror" rows="5">{{ old('text_body') }}</textarea>
                        @error('text_body')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="form-text text-muted" style="display: block; margin-top: 0.5rem;"><i class="bi bi-info-circle me-1"></i>Optional fallback content for plain-text email recipients.</small>
                    </div>

                    <div class="variables-section">
                        <strong><i class="bi bi-braces"></i>Detected Variables</strong>
                        <div id="detectedVariables" class="mt-3">No variables detected yet.</div>
                    </div>

                    <div class="action-buttons">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-2"></i>Save Template
                        </button>
                        <a href="{{ route('portal.admin.messages.email_templates.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-radius: 12px; padding: 1.5rem;">
                <div class="form-section-title"><i class="bi bi-lightbulb"></i>Tips</div>
                
                <div style="background: white; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; border-left: 4px solid #667eea;">
                    <h6 style="color: #1e293b; font-weight: 600; margin-bottom: 0.5rem;"><i class="bi bi-pin me-2"></i>Template Placeholders</h6>
                    <p style="font-size: 0.9rem; color: #64748b; margin: 0;">Use double curly braces to insert dynamic values: <code>{{'{{'}}variable_name{{'}}'}}</code></p>
                </div>

                <div style="background: white; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; border-left: 4px solid #667eea;">
                    <h6 style="color: #1e293b; font-weight: 600; margin-bottom: 0.5rem;"><i class="bi bi-variables me-2"></i>Common Variables</h6>
                    <ul style="font-size: 0.85rem; color: #64748b; margin: 0; padding-left: 1.5rem;">
                        <li><code>name</code> - Recipient's name</li>
                        <li><code>email</code> - Email address</li>
                        <li><code>company</code> - Organization</li>
                        <li><code>activation_url</code> - Link for action</li>
                    </ul>
                </div>

                <div style="background: white; border-radius: 8px; padding: 1rem; border-left: 4px solid #667eea;">
                    <h6 style="color: #1e293b; font-weight: 600; margin-bottom: 0.5rem;"><i class="bi bi-check-circle me-2"></i>Best Practices</h6>
                    <ul style="font-size: 0.85rem; color: #64748b; margin: 0; padding-left: 1.5rem;">
                        <li>Keep subject line under 50 chars</li>
                        <li>Use clear, scannable formatting</li>
                        <li>Include unsubscribe link</li>
                        <li>Test with sample values</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"></script>
    <script>
        const sampleTemplates = {
            welcome: {
                subject: 'Welcome to {{'{{'}}company{{'}}'}}, {{'{{'}}name{{'}}'}}'                html_body: '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;"><h1>Welcome!</h1><p>Hello {{'{{'}}name{{'}}'}},</p><p>We\'re excited to have you join our community. Get started by exploring our platform.</p><a href="{{'{{'}}activation_url{{'}}'}}" style="display: inline-block; padding: 10px 20px; background-color: #667eea; color: white; text-decoration: none; border-radius: 5px;">Get Started</a><br/><br/><p>Best regards,<br/>The Team</p></div>',
                text_body: 'Welcome!\n\nHello {{'{{'}}name{{'}}'}},\n\nWe\'re excited to have you join our community. Get started by exploring our platform.\n\nBest regards,\nThe Team'
            },
            reminder: {
                subject: 'Don\'t forget: {{'{{'}}action{{'}}'}}'                html_body: '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;"><h2>Reminder</h2><p>Hi {{'{{'}}name{{'}}'}},</p><p>Just a friendly reminder about {{'{{'}}action{{'}}'}}</p><a href="{{'{{'}}action_url{{'}}'}}" style="display: inline-block; padding: 10px 20px; background-color: #667eea; color: white; text-decoration: none; border-radius: 5px;">Take Action</a><br/><br/><p>Thanks!</p></div>',
                text_body: 'Reminder\n\nHi {{'{{'}}name{{'}}'}},\n\nJust a friendly reminder about {{'{{'}}action{{'}}'}}\n\nThanks!'
            },
            unsubscribe: {
                subject: 'Subscription Updated',
                html_body: '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;"><h2>Subscription Updated</h2><p>Hi {{'{{'}}name{{'}}'}},</p><p>Your email subscription has been updated successfully.</p><p>If you have any questions, feel free to contact us.</p></div>',
                text_body: 'Subscription Updated\n\nHi {{'{{'}}name{{'}}'}},\n\nYour subscription has been updated.'
            }
        };

        document.getElementById('sample_template').addEventListener('change', function() {
            const template = sampleTemplates[this.value];
            if (template) {
                document.getElementById('subject').value = template.subject;
                document.getElementById('text_body').value = template.text_body;
                $('#html_body').summernote('code', template.html_body);
                updateDetectedVariables();
            }
        });

        function extractVariables(value) {
            const matches = [...value.matchAll(/\{\{\s*(\w+)\s*\}\}/g)];
            return [...new Set(matches.map(m => m[1]))];
        }

        function updateDetectedVariables() {
            const subjectValue = document.getElementById('subject').value || '';
            const htmlValue = $('#html_body').summernote('code') || '';
            const textValue = document.getElementById('text_body').value || '';
            const variables = [...new Set([
                ...extractVariables(subjectValue),
                ...extractVariables(htmlValue),
                ...extractVariables(textValue),
            ])];

            const container = document.getElementById('detectedVariables');
            container.innerHTML = variables.length
                ? variables.map(name => `<span>${name}</span>`).join('')
                : 'No variables detected yet.';
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
                        updateDetectedVariables();
                    }
                }
            });

            document.getElementById('subject').addEventListener('input', updateDetectedVariables);
            document.getElementById('text_body').addEventListener('input', updateDetectedVariables);

            updateDetectedVariables();
        });
    </script>
@endpush
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"></script>
    <script>
        const sampleTemplates = {
            welcome: `<h1>Welcome, @{{name}}!</h1><p>Thank you for joining @{{organization}}. Your login email is <strong>@{{email}}</strong>.</p><p><a href="@{{unsubscribe_url}}">Unsubscribe</a></p>`,
            reminder: `<h1>Reminder for @{{name}}</h1><p>This is a friendly reminder that your event is scheduled for @{{date}}.</p><p>If you need help, contact us at <strong>@{{email}}</strong>.</p>`,
            unsubscribe: `<h1>We're sorry to see you go</h1><p>Hello @{{name}},</p><p>You can unsubscribe by clicking the link below:</p><p><a href="@{{unsubscribe_url}}">Unsubscribe now</a></p>`,
        };

        function extractVariables(value) {
            const matches = [...value.matchAll(/\{\{\s*(\w+)\s*\}\}/g)];
            return [...new Set(matches.map(m => m[1]))];
        }

        function updateDetectedVariables() {
            const subjectValue = document.getElementById('subject').value || '';
            const htmlValue = $('#html_body').summernote('code') || '';
            const textValue = document.getElementById('text_body').value || '';
            const variables = [...new Set([
                ...extractVariables(subjectValue),
                ...extractVariables(htmlValue),
                ...extractVariables(textValue),
            ])];

            const container = document.getElementById('detectedVariables');
            container.innerHTML = variables.length
                ? variables.map(name => `<span>${name}</span>`).join('')
                : 'No variables detected yet.';
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
                        updateDetectedVariables();
                    }
                }
            });

            document.getElementById('subject').addEventListener('input', updateDetectedVariables);
            document.getElementById('text_body').addEventListener('input', updateDetectedVariables);

            document.getElementById('sample_template').addEventListener('change', function () {
                const sampleKey = this.value;
                if (!sampleKey) {
                    return;
                }
                const sample = sampleTemplates[sampleKey] || '';
                $('#html_body').summernote('code', sample);
                updateDetectedVariables();
            });

            updateDetectedVariables();
        });
    </script>
@endpush
