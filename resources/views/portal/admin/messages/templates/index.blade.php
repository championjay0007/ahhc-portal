@extends('layouts.admin')

@section('title', 'Message Templates')

@push('styles')
    <style>
        .header-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2.5rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-section h1 {
            font-weight: 700;
            font-size: 2rem;
            margin: 0;
        }
        .header-section p {
            opacity: 0.95;
            margin: 0.5rem 0 0 0;
        }
        .template-card {
            background: white;
            border-radius: 16px;
            border: 1px solid #e0e0e0;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        .template-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.12);
        }
        .template-card-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, #f5f7fa 0%, #f0f2f5 100%);
            border-bottom: 1px solid #e0e0e0;
        }
        .template-card-header h5 {
            margin: 0;
            font-weight: 700;
            color: #333;
        }
        .template-card-body {
            padding: 1.5rem;
        }
        .template-card-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
        }
        .meta-label {
            color: #999;
            font-weight: 500;
        }
        .template-subject {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 10px;
            margin: 1rem 0;
            border-left: 3px solid #667eea;
            font-size: 0.95rem;
            color: #333;
        }
        .template-actions {
            display: flex;
            gap: 0.5rem;
        }
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: linear-gradient(135deg, #f5f7fa 0%, #f0f2f5 100%);
            border-radius: 20px;
            border: 2px dashed #ddd;
        }
        .empty-state i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 1rem;
        }
        .btn-create {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-active {
            background: #d1f2d9;
            color: #0b5e0a;
        }
        .status-inactive {
            background: #f0f0f0;
            color: #666;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="header-section">
        <div>
            <h1><i class="bi bi-file-earmark-text"></i> Message Templates</h1>
            <p>Create and manage your reusable message templates</p>
        </div>
        <a href="{{ route('portal.admin.messages.templates.create') }}" class="btn btn-create">
            <i class="bi bi-plus-circle"></i> Create Template
        </a>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i>
            <strong>Success!</strong> {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($templates->count() > 0)
        <div class="row">
            @foreach($templates as $template)
                <div class="col-lg-6 mb-4">
                    <div class="template-card">
                        <div class="template-card-header">
                            <h5>{{ $template->name }}</h5>
                        </div>
                        <div class="template-card-body">
                            <div class="template-card-meta">
                                <div class="meta-item">
                                    <span class="meta-label"><i class="bi bi-bookmark"></i> Type:</span>
                                    <span class="badge" style="background: #667eea;">{{ ucfirst(str_replace('_', ' ', $template->type)) }}</span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-label"><i class="bi bi-folder"></i> Category:</span>
                                    <strong>{{ $template->category ?? 'Uncategorized' }}</strong>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-label"><i class="bi bi-brush"></i> Theme:</span>
                                    <strong>{{ ucfirst($template->theme ?? 'clean') }}</strong>
                                </div>
                                <div class="meta-item">
                                    <span class="status-badge {{ $template->is_active ? 'status-active' : 'status-inactive' }}">
                                        <i class="bi {{ $template->is_active ? 'bi-check-circle' : 'bi-dash-circle' }}"></i>
                                        {{ $template->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>

                            <div class="template-subject">
                                <small class="d-block mb-2" style="color: #999; font-weight: 500;"><i class="bi bi-envelope"></i> Subject Line:</small>
                                {{ $template->subject }}
                            </div>

                            <div style="margin: 1rem 0; padding: 1rem; background: #f8f9fa; border-radius: 10px; max-height: 100px; overflow: hidden; border-left: 3px solid #667eea;">
                                <small style="color: #999; font-weight: 500; display: block; margin-bottom: 0.5rem;"><i class="bi bi-chat"></i> Preview:</small>
                                <div style="font-size: 0.9rem; color: #666; line-height: 1.5;">
                                    {!! Str::limit(strip_tags($template->body), 150) !!}...
                                </div>
                            </div>

                            <div class="template-actions">
                                <a href="{{ route('portal.admin.messages.templates.edit', $template) }}" class="btn btn-sm btn-outline-primary flex-grow-1">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('portal.admin.messages.templates.delete', $template) }}" method="POST" class="flex-grow-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100" onclick="return confirm('Are you sure you want to delete this template?')">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($templates->hasPages())
            <div class="mt-4 d-flex justify-content-center">
                {{ $templates->links() }}
            </div>
        @endif
    @else
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <h4 style="color: #999; margin-bottom: 1rem;">No Templates Yet</h4>
            <p style="color: #999; margin-bottom: 1.5rem;">Create your first message template to get started</p>
            <a href="{{ route('portal.admin.messages.templates.create') }}" class="btn btn-create">
                <i class="bi bi-plus-circle"></i> Create First Template
            </a>
        </div>
    @endif
</div>
@endsection
