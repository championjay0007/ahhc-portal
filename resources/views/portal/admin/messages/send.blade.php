@extends('layouts.admin')

@section('title', 'Send Message')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <h1 class="h2 mb-4">Send Message</h1>

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Errors:</strong>
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <form action="{{ route('portal.admin.messages.send.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="recipient_ids" class="form-label">Recipients <span class="text-danger">*</span></label>
                            <select class="form-select @error('recipient_ids') is-invalid @enderror" id="recipient_ids" name="recipient_ids[]" multiple required style="min-height: 150px;">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ in_array($user->id, old('recipient_ids', [])) ? 'selected' : '' }}>
                                        {{ $user->name }} ({{ ucfirst($user->role) }}) - {{ $user->email }}
                                    </option>
                                @endforeach
                            </select>
                            @error('recipient_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Hold Ctrl/Cmd to select multiple users</small>
                        </div>

                        <div class="mb-3">
                            <label for="template_id" class="form-label">Use Template (Optional)</label>
                            <select class="form-select" id="template_id" name="template_id">
                                <option value="">-- No Template --</option>
                                @foreach($templates as $template)
                                    @php
                                        $themeHtmlValue = trim($template->theme_html ?? '') ?: \App\Services\MessageService::getDefaultThemeHtml($template->theme ?? 'clean');
                                    @endphp
                                    <option value="{{ $template->id }}"
                                        data-subject="{{ e($template->subject) }}"
                                        data-body="{{ e($template->body) }}"
                                        data-theme="{{ $template->theme ?? 'clean' }}"
                                        data-custom-style="{{ e($template->custom_style) }}"
                                        data-theme-html="{{ e($themeHtmlValue) }}"
                                        {{ old('template_id') == $template->id ? 'selected' : '' }}>
                                        {{ $template->name }} ({{ ucfirst(str_replace('_', ' ', $template->type)) }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Selecting a template will use the template subject and body for the sent message.</small>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject') }}" required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="body" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body" rows="10" required>{{ old('body') }}</textarea>
                            @error('body')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Template Preview</label>
                            <div id="templatePreviewCard" class="border rounded-3 p-4" style="background: #f8f9fa; border-color: #d1d5db;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-secondary" id="templateThemeBadge">No template selected</span>
                                    <small class="text-muted" id="templateTypeLabel"></small>
                                </div>
                                <h5 id="templatePreviewSubject" class="mb-3">Subject preview appears here</h5>
                                <div id="templatePreviewBody" style="color: #495057; line-height: 1.75; white-space: pre-wrap;">Message preview appears here.</div>
                            </div>
                            <div id="templateWrapperPreview" class="border rounded-3 p-4 mt-3" style="background: #ffffff; border-color: #d1d5db; display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-light text-dark">Template Wrapper Preview</span>
                                </div>
                                <div id="templateWrapperContent" style="color: #495057; line-height: 1.75;"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <a href="{{ route('portal.admin.messages.sent') }}" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshTemplateSelection() {
    const templateSelect = document.getElementById('template_id');
    const subjectField = document.getElementById('subject');
    const bodyField = document.getElementById('body');
    const previewCard = document.getElementById('templatePreviewCard');
    const themeBadge = document.getElementById('templateThemeBadge');
    const typeLabel = document.getElementById('templateTypeLabel');
    const previewSubject = document.getElementById('templatePreviewSubject');
    const previewBody = document.getElementById('templatePreviewBody');
    const wrapperContainer = document.getElementById('templateWrapperPreview');
    const wrapperContent = document.getElementById('templateWrapperContent');
    const selected = templateSelect.options[templateSelect.selectedIndex];

    if (selected && selected.value) {
        const templateSubject = selected.getAttribute('data-subject');
        const templateBody = selected.getAttribute('data-body');
        const templateTheme = selected.getAttribute('data-theme') || 'clean';
        const customStyle = selected.getAttribute('data-custom-style') || '';
        const templateThemeHtml = selected.getAttribute('data-theme-html') || '';

        subjectField.value = templateSubject;
        bodyField.value = templateBody;
        subjectField.readOnly = true;
        bodyField.readOnly = true;

        themeBadge.textContent = templateTheme.charAt(0).toUpperCase() + templateTheme.slice(1) + ' Theme';
        typeLabel.textContent = selected.textContent.match(/\(([^)]+)\)$/)?.[1] || '';
        previewSubject.textContent = templateSubject || 'Subject preview appears here';
        previewBody.innerHTML = templateBody || 'Message preview appears here.';

        wrapperContainer.style.display = 'block';
        wrapperContent.innerHTML = buildTemplateWrapperPreview(templateThemeHtml, templateBody || '<p>No body content</p>');

        applyTemplateTheme(previewCard, templateTheme, customStyle);
    } else {
        subjectField.readOnly = false;
        bodyField.readOnly = false;

        themeBadge.textContent = 'No template selected';
        typeLabel.textContent = '';
        previewSubject.textContent = 'Subject preview appears here';
        previewBody.textContent = 'Message preview appears here.';
        wrapperContainer.style.display = 'none';
        wrapperContent.innerHTML = '';
        previewCard.style.background = '#f8f9fa';
        previewCard.style.borderColor = '#d1d5db';
        previewCard.style.boxShadow = 'none';
        previewCard.style.color = '#495057';
        previewCard.style.fontFamily = 'inherit';
        previewCard.style.borderRadius = '0.75rem';
        previewCard.removeAttribute('data-custom-style');
    }
}

function buildTemplateWrapperPreview(themeHtml, bodyContent) {
    if (!themeHtml) {
        return bodyContent;
    }

    const placeholder = '{' + '{body' + '}' + '}';

    if (themeHtml.includes(placeholder)) {
        return themeHtml.replace(new RegExp('\\{\\{body\\}\\}', 'g'), bodyContent);
    }

    return themeHtml + bodyContent;
}

function applyTemplateTheme(card, theme, customStyle) {
    const themes = {
        clean: {
            background: '#ffffff',
            borderColor: '#e5e7eb',
            boxShadow: '0 8px 30px rgba(15, 23, 42, 0.06)',
            color: '#1f2937',
            fontFamily: 'system-ui, sans-serif',
        },
        modern: {
            background: '#f8fafc',
            borderColor: '#cbd5e1',
            boxShadow: '0 8px 28px rgba(15, 23, 42, 0.07)',
            color: '#0f172a',
            fontFamily: 'system-ui, sans-serif',
        },
        warm: {
            background: '#fff7ed',
            borderColor: '#fcd9b6',
            boxShadow: '0 8px 28px rgba(124, 45, 18, 0.08)',
            color: '#7c2d12',
            fontFamily: 'system-ui, sans-serif',
        },
        elegant: {
            background: '#f5f3ff',
            borderColor: '#c7d2fe',
            boxShadow: '0 8px 28px rgba(55, 48, 163, 0.08)',
            color: '#27196b',
            fontFamily: 'system-ui, sans-serif',
        },
        corporate: {
            background: '#f3f4f6',
            borderColor: '#d1d5db',
            boxShadow: '0 8px 30px rgba(15, 23, 42, 0.06)',
            color: '#0f172a',
            fontFamily: 'system-ui, sans-serif',
        },
        minimal: {
            background: '#ffffff',
            borderColor: '#e5e7eb',
            boxShadow: '0 6px 20px rgba(15, 23, 42, 0.05)',
            color: '#111827',
            fontFamily: 'system-ui, sans-serif',
        },
        bold: {
            background: '#0f172a',
            borderColor: '#0f172a',
            boxShadow: '0 12px 36px rgba(15, 23, 42, 0.35)',
            color: '#f8fafc',
            fontFamily: 'system-ui, sans-serif',
        },
        soft: {
            background: '#fdf2f8',
            borderColor: '#fbcfe8',
            boxShadow: '0 8px 28px rgba(139, 92, 246, 0.08)',
            color: '#831843',
            fontFamily: 'system-ui, sans-serif',
        },
        premium: {
            background: '#eef2ff',
            borderColor: '#c7d2fe',
            boxShadow: '0 8px 28px rgba(79, 70, 229, 0.1)',
            color: '#312e81',
            fontFamily: 'system-ui, sans-serif',
        },
        festive: {
            background: '#fffbeb',
            borderColor: '#f59e0b',
            boxShadow: '0 8px 28px rgba(202, 138, 4, 0.14)',
            color: '#92400e',
            fontFamily: 'system-ui, sans-serif',
        },
        sunrise: {
            background: '#fff7ed',
            borderColor: '#fb923c',
            boxShadow: '0 8px 28px rgba(220, 38, 38, 0.08)',
            color: '#9a3412',
            fontFamily: 'system-ui, sans-serif',
        },
        twilight: {
            background: '#eef2ff',
            borderColor: '#6366f1',
            boxShadow: '0 8px 28px rgba(79, 70, 229, 0.12)',
            color: '#312e81',
            fontFamily: 'system-ui, sans-serif',
        },
        calm: {
            background: '#ecfdf5',
            borderColor: '#86efac',
            boxShadow: '0 8px 28px rgba(16, 185, 129, 0.08)',
            color: '#14532d',
            fontFamily: 'system-ui, sans-serif',
        },
        vibrant: {
            background: '#fef9c3',
            borderColor: '#f59e0b',
            boxShadow: '0 8px 28px rgba(217, 119, 6, 0.12)',
            color: '#92400e',
            fontFamily: 'system-ui, sans-serif',
        },
        pastel: {
            background: '#f5f3ff',
            borderColor: '#d8b4fe',
            boxShadow: '0 8px 28px rgba(139, 92, 246, 0.08)',
            color: '#6d28d9',
            fontFamily: 'system-ui, sans-serif',
        },
        classic: {
            background: '#faf5eb',
            borderColor: '#d4b483',
            boxShadow: '0 8px 28px rgba(113, 63, 18, 0.1)',
            color: '#713f12',
            fontFamily: 'system-ui, sans-serif',
        },
        tech: {
            background: '#0f172a',
            borderColor: '#334155',
            boxShadow: '0 12px 36px rgba(15, 23, 42, 0.4)',
            color: '#38bdf8',
            fontFamily: 'system-ui, sans-serif',
        },
        luxury: {
            background: '#111827',
            borderColor: '#f59e0b',
            boxShadow: '0 12px 36px rgba(0, 0, 0, 0.48)',
            color: '#fbbf24',
            fontFamily: 'system-ui, sans-serif',
        },
        natural: {
            background: '#ecfdf5',
            borderColor: '#4ade80',
            boxShadow: '0 8px 28px rgba(16, 185, 129, 0.08)',
            color: '#065f46',
            fontFamily: 'system-ui, sans-serif',
        },
        sleek: {
            background: '#111827',
            borderColor: '#374151',
            boxShadow: '0 12px 36px rgba(15, 23, 42, 0.5)',
            color: '#e2e8f0',
            fontFamily: 'system-ui, sans-serif',
        },
        custom: {
            background: '#ffffff',
            borderColor: '#e5e7eb',
            boxShadow: '0 8px 28px rgba(15, 23, 42, 0.06)',
            color: '#1f2937',
            fontFamily: 'system-ui, sans-serif',
        }
    };

    const style = themes[theme] || themes.clean;
    card.style.background = style.background;
    card.style.borderColor = style.borderColor;
    card.style.boxShadow = style.boxShadow;
    card.style.color = style.color;
    card.style.fontFamily = style.fontFamily;

    if (theme === 'custom' && customStyle) {
        let customTag = document.getElementById('sendTemplateCustomStyle');
        if (!customTag) {
            customTag = document.createElement('style');
            customTag.id = 'sendTemplateCustomStyle';
            document.body.appendChild(customTag);
        }
        customTag.textContent = customStyle;
    } else {
        const customTag = document.getElementById('sendTemplateCustomStyle');
        if (customTag) {
            customTag.textContent = '';
        }
    }
}

document.getElementById('template_id').addEventListener('change', refreshTemplateSelection);
document.getElementById('subject').addEventListener('input', refreshTemplateSelection);
document.getElementById('body').addEventListener('input', refreshTemplateSelection);
window.addEventListener('DOMContentLoaded', refreshTemplateSelection);
</script>
@endsection
