@extends('layouts.admin')

@section('title', 'Email Templates')

@push('styles')
    <style>
        .template-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 16px;
            padding: 2.5rem 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.15);
        }
        .template-header h1 { font-size: 2rem; font-weight: 700; margin: 0; }
        .template-header p { margin: 0.5rem 0 0; opacity: 0.9; }
        .btn-create { background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.4); }
        .btn-create:hover { background: rgba(255,255,255,0.3); }
        
        .filter-section { background: #f8fafc; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; border: 1px solid #e2e8f0; }
        .form-label { font-weight: 600; color: #1e293b; margin-bottom: 0.5rem; }
        
        .custom-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8'%3E%3Cpath fill='%23667eea' d='M0 0l6 8 6-8z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 36px;
        }
        
        .template-card { border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; transition: all 0.3s ease; }
        .template-card:hover { box-shadow: 0 8px 24px rgba(0,0,0,0.08); border-color: #cbd5e1; }
        .template-card-header { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); padding: 1.5rem; border-bottom: 1px solid #e2e8f0; }
        .template-card-title { font-size: 1.1rem; font-weight: 700; color: #1e293b; margin: 0; }
        .template-card-category { font-size: 0.85rem; color: #64748b; margin-top: 0.5rem; }
        .template-card-body { padding: 1.5rem; }
        .template-info { display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; }
        .template-info-label { font-size: 0.85rem; font-weight: 600; color: #64748b; text-transform: uppercase; }
        .template-info-value { color: #1e293b; }
        .badge-status { font-size: 0.8rem; font-weight: 600; padding: 0.4rem 0.8rem; }
        .template-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
        .template-actions .btn { font-size: 0.85rem; padding: 0.4rem 0.8rem; }
        .empty-state { text-align: center; padding: 3rem 2rem; color: #64748b; }
        .empty-state-icon { font-size: 3rem; margin-bottom: 1rem; opacity: 0.5; }
    </style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="template-header d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="bi bi-envelope-heart me-2"></i>Email Templates</h1>
            <p>Manage reusable email content, preview output, and keep templates active.</p>
        </div>
        <a href="{{ route('portal.admin.messages.email_templates.create') }}" class="btn btn-light btn-create" style="border-radius: 8px; padding: 0.75rem 1.5rem; font-weight: 600;">
            <i class="bi bi-plus-lg me-2"></i>New Template
        </a>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 10px; border-left: 4px solid #10b981;">
            <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="filter-section">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label"><i class="bi bi-search me-2"></i>Search Templates</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" style="border-radius: 8px; border: 1px solid #cbd5e1;" placeholder="Search by name or subject...">
            </div>
            <div class="col-md-4">
                <label class="form-label"><i class="bi bi-tag me-2"></i>Category</label>
                <select name="category" class="form-select custom-select" style="border-radius: 8px; border: 1px solid #cbd5e1;">
                    <option value="">All categories</option>
                    @foreach($categories ?? [] as $category)
                        <option value="{{ $category->slug }}" {{ request('category') === $category->slug ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100" style="border-radius: 8px; font-weight: 600;"><i class="bi bi-funnel me-2"></i>Filter</button>
            </div>
        </form>
    </div>

    <div class="row g-4">
        @forelse($templates as $template)
            <div class="col-md-6 col-lg-4">
                <div class="template-card h-100 d-flex flex-column">
                    <div class="template-card-header">
                        <h3 class="template-card-title"><i class="bi bi-envelope me-2"></i>{{ $template->name }}</h3>
                        <p class="template-card-category"><i class="bi bi-tag"></i> {{ $template->category_name }}</p>
                    </div>
                    <div class="template-card-body flex-grow-1">
                        <div class="template-info">
                            <span class="template-info-label">Subject:</span>
                            <span class="template-info-value text-truncate">{{ substr($template->subject, 0, 40) }}{{ strlen($template->subject) > 40 ? '...' : '' }}</span>
                        </div>
                        <div class="template-info">
                            <span class="template-info-label">Variables:</span>
                            <span class="template-info-value">{{ count($template->variables ?? []) }}</span>
                        </div>
                        <div class="template-info">
                            <span class="template-info-label">Status:</span>
                            <span class="badge-status bg-{{ $template->is_active ? 'success' : 'secondary' }}">
                                <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>{{ $template->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="template-info">
                            <span class="template-info-label">Updated:</span>
                            <span class="template-info-value">{{ $template->updated_at->format('M d') }}</span>
                        </div>
                    </div>
                    <div class="template-actions" style="padding: 1.5rem; border-top: 1px solid #e2e8f0;">
                        <a href="{{ route('portal.admin.messages.email_templates.preview', $template) }}" class="btn btn-sm btn-outline-primary" title="Preview"><i class="bi bi-eye"></i> Preview</a>
                        <a href="{{ route('portal.admin.messages.email_templates.edit', $template) }}" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="bi bi-pencil"></i> Edit</a>
                        <form action="{{ route('portal.admin.messages.email_templates.duplicate', $template) }}" method="POST" class="d-inline-block" title="Clone">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-info"><i class="bi bi-files"></i> Clone</button>
                        </form>
                        <form action="{{ route('portal.admin.messages.email_templates.delete', $template) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete this template?');" title="Delete">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="empty-state card" style="border: 2px dashed #cbd5e1; background: #f8fafc;">
                    <div class="empty-state-icon"><i class="bi bi-inbox"></i></div>
                    <h4>No email templates created yet</h4>
                    <p>Get started by creating your first email template to manage reusable email content.</p>
                    <a href="{{ route('portal.admin.messages.email_templates.create') }}" class="btn btn-primary" style="border-radius: 8px;">
                        <i class="bi bi-plus-lg me-2"></i>Create First Template
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    @if($templates->hasPages())
        <div class="mt-4 d-flex justify-content-center">
            {{ $templates->links() }}
        </div>
    @endif
</div>
@endsection
