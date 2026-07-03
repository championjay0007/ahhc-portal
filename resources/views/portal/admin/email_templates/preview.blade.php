@extends('layouts.admin')

@section('title', 'Preview Email Template')

@push('styles')
    <style>
        .preview-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.15);
        }
        .preview-header h1 { font-size: 1.8rem; font-weight: 700; margin: 0; }
        .preview-header p { margin: 0.5rem 0 0; opacity: 0.9; }
        
        .preview-section { background: white; border-radius: 12px; padding: 2rem; margin-bottom: 1.5rem; border: 1px solid #e2e8f0; }
        .preview-label { font-weight: 700; color: #1e293b; margin-bottom: 1rem; font-size: 1rem; display: flex; align-items: center; gap: 0.5rem; }
        
        .template-details { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; }
        .detail-row { display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid rgba(255,255,255,0.5); }
        .detail-row:last-child { border-bottom: none; }
        .detail-label { font-weight: 600; color: #475569; }
        .detail-value { color: #1e293b; font-weight: 500; }
        
        .sample-values { background: #f8fafc; border-radius: 12px; padding: 1rem; margin-top: 1rem; }
        .sample-item { padding: 0.5rem 0; font-size: 0.9rem; }
        .sample-item strong { color: #667eea; }
        .sample-item code { background: white; padding: 0.2rem 0.4rem; border-radius: 4px; }
        
        .email-preview { background: white; border: 1px solid #e2e8f0; border-radius: 12px; min-height: 200px; padding: 2rem; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; }
        .email-subject { background: #f1f5f9; padding: 1rem; border-radius: 8px; word-break: break-word; font-size: 1rem; font-weight: 600; color: #1e293b; }
        
        .test-email-section { background: linear-gradient(135deg, #f5f7fa 0%, #e0e7ff 100%); border-radius: 12px; padding: 1.5rem; border: 1px solid #cbd5e1; }
        .test-email-section h5 { color: #1e293b; font-weight: 700; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem; }
        
        .btn-section { display: flex; gap: 1rem; margin-top: 1.5rem; }
        .btn-section .btn { border-radius: 8px; font-weight: 600; }
        
        .email-text-plain { background: #1e293b; color: #e2e8f0; padding: 1.5rem; border-radius: 8px; font-family: 'Courier New', monospace; font-size: 0.9rem; white-space: pre-wrap; overflow-x: auto; }
    </style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="preview-header d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-envelope-open-heart me-2"></i>Preview Email Template</h1>
            <p>Rendered output using sample values for detected variables.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('portal.admin.messages.email_templates.edit', $emailTemplate) }}" class="btn btn-light" style="border-radius: 8px; font-weight: 600;">
                <i class="bi bi-pencil me-2"></i>Edit
            </a>
            <a href="{{ route('portal.admin.messages.email_templates.index') }}" class="btn btn-outline-light" style="border-radius: 8px; font-weight: 600;">
                <i class="bi bi-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" style="border-radius: 10px; border-left: 4px solid #10b981;">
            <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" style="border-radius: 10px; border-left: 4px solid #ef4444;">
            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="template-details">
                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-file-text me-2"></i>Name</span>
                    <span class="detail-value">{{ $emailTemplate->name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-tag me-2"></i>Category</span>
                    <span class="detail-value">{{ $emailTemplate->category_name }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-circle-fill me-2" style="font-size: 0.5rem;"></i>Status</span>
                    <span class="detail-value">
                        <span class="badge bg-{{ $emailTemplate->is_active ? 'success' : 'secondary' }}">
                            {{ $emailTemplate->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><i class="bi bi-asterisk me-2"></i>Slug</span>
                    <span class="detail-value" style="font-size: 0.9rem; font-family: monospace;">{{ $emailTemplate->slug }}</span>
                </div>
            </div>

            <div class="preview-section">
                <div class="preview-label"><i class="bi bi-braces me-2" style="color: #667eea;"></i>Sample Values</div>
                <div class="sample-values">
                    @foreach($sampleValues as $key => $value)
                        <div class="sample-item">
                            <strong>{{ $key }}:</strong> <code>{{ substr($value, 0, 30) }}{{ strlen($value) > 30 ? '...' : '' }}</code>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="test-email-section">
                <h5><i class="bi bi-send me-2" style="color: #667eea;"></i>Send Test Email</h5>
                <form action="{{ route('portal.admin.messages.email_templates.send_test', $emailTemplate) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="test_email" class="form-label" style="font-weight: 600;">Recipient Email</label>
                        <input type="email" id="test_email" name="test_email" class="form-control @error('test_email') is-invalid @enderror" value="{{ old('test_email') }}" placeholder="admin@example.com" required style="border-radius: 8px; border: 1px solid #cbd5e1;">
                        @error('test_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <button type="submit" class="btn btn-primary w-100" style="border-radius: 8px; font-weight: 600;">
                        <i class="bi bi-airplane me-2"></i>Send Test Email
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="preview-section">
                <div class="preview-label"><i class="bi bi-envelope me-2" style="color: #667eea;"></i>Rendered Subject Line</div>
                <div class="email-subject">{{ $rendered['subject'] }}</div>
            </div>

            <div class="preview-section">
                <div class="preview-label"><i class="bi bi-file-richtext me-2" style="color: #667eea;"></i>Rendered HTML Preview</div>
                <div class="email-preview" style="background: #fafbfc;">
                    {!! $rendered['html'] !!}
                </div>
            </div>

            <div class="preview-section">
                <div class="preview-label"><i class="bi bi-file-text me-2" style="color: #667eea;"></i>Plain Text Version</div>
                <div class="email-text-plain">{{ $rendered['text'] }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
