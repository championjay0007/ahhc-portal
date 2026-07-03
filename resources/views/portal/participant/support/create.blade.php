@extends('layouts.portal')

@section('title', 'Contact Support')

@push('styles')
    <style>
        .support-hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2.5rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 20px 50px rgba(102, 126, 234, 0.2);
        }
        .support-hero h1 {
            font-weight: 700;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .support-hero p {
            opacity: 0.95;
            margin-bottom: 0;
        }
        .form-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            border: 1px solid #e0e0e0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }
        .form-section h5 {
            color: #667eea;
            font-weight: 700;
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
            transition: all 0.3s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .help-text {
            font-size: 0.85rem;
            color: #666;
            margin-top: 0.5rem;
            background: #f8f9fa;
            padding: 0.75rem;
            border-left: 3px solid #667eea;
            border-radius: 6px;
        }
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .category-option {
            position: relative;
        }
        .category-option input[type="radio"] {
            display: none;
        }
        .category-option label {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            border: 2px solid #ddd;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
        }
        .category-option input[type="radio"]:checked + label {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }
        .category-option i {
            font-size: 1.5rem;
            color: #667eea;
        }
        .priority-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
        }
        .priority-option {
            position: relative;
        }
        .priority-option input[type="radio"] {
            display: none;
        }
        .priority-option label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem;
            border: 2px solid #ddd;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            background: white;
            font-size: 0.9rem;
            font-weight: 500;
        }
        .priority-option input[type="radio"]:checked + label {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }
        .priority-low { border-color: #28a745; }
        .priority-normal { border-color: #ffc107; }
        .priority-high { border-color: #fd7e14; }
        .priority-urgent { border-color: #dc3545; }
        .priority-option input[value="low"]:checked + label { border-color: #28a745; background: rgba(40, 167, 69, 0.05); }
        .priority-option input[value="normal"]:checked + label { border-color: #ffc107; background: rgba(255, 193, 7, 0.05); }
        .priority-option input[value="high"]:checked + label { border-color: #fd7e14; background: rgba(253, 126, 20, 0.05); }
        .priority-option input[value="urgent"]:checked + label { border-color: #dc3545; background: rgba(220, 53, 69, 0.05); }
        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        .info-card {
            background: linear-gradient(135deg, #f5f7fa 0%, #f0f2f5 100%);
            border-radius: 12px;
            padding: 1.5rem;
            border-left: 4px solid #667eea;
            margin-top: 2rem;
        }
        .info-card h6 {
            color: #333;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }
        .info-card ul {
            margin: 0;
            padding-left: 1.5rem;
        }
        .info-card li {
            color: #666;
            margin-bottom: 0.5rem;
            font-size: 0.95rem;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="support-hero">
        <h1><i class="bi bi-headset"></i> Contact Support</h1>
        <p>We're here to help! Tell us what you need assistance with</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="bi bi-exclamation-circle"></i> Please fix the following errors:</strong>
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
            <form action="{{ route('portal.support.store') }}" method="POST">
                @csrf

                <!-- Subject Section -->
                <div class="form-section">
                    <h5><i class="bi bi-chat-left-text" style="color: #667eea;"></i> Issue Summary</h5>
                    
                    <div class="mb-3">
                        <label for="subject" class="form-label">
                            <i class="bi bi-pencil-square"></i> Subject
                        </label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                               id="subject" name="subject" value="{{ old('subject') }}" 
                               placeholder="Brief description of your issue" required>
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="help-text"><i class="bi bi-lightbulb"></i> Be specific and concise (5-200 characters)</div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">
                            <i class="bi bi-file-text"></i> Detailed Description
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="6" 
                                  placeholder="Please provide as much detail as possible about your issue..."
                                  required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="help-text"><i class="bi bi-info-circle"></i> Include what you were doing, what went wrong, and any error messages (20-5000 characters)</div>
                    </div>
                </div>

                <!-- Category Section -->
                <div class="form-section">
                    <h5><i class="bi bi-folder-open" style="color: #667eea;"></i> Issue Category</h5>
                    
                    <div class="category-grid">
                        <div class="category-option">
                            <input type="radio" id="category_general" name="category" value="general" 
                                   {{ old('category') === 'general' ? 'checked' : '' }} required>
                            <label for="category_general">
                                <i class="bi bi-chat-dots"></i>
                                <span>General</span>
                            </label>
                        </div>
                        <div class="category-option">
                            <input type="radio" id="category_billing" name="category" value="billing" 
                                   {{ old('category') === 'billing' ? 'checked' : '' }}>
                            <label for="category_billing">
                                <i class="bi bi-credit-card"></i>
                                <span>Billing</span>
                            </label>
                        </div>
                        <div class="category-option">
                            <input type="radio" id="category_technical" name="category" value="technical" 
                                   {{ old('category') === 'technical' ? 'checked' : '' }}>
                            <label for="category_technical">
                                <i class="bi bi-wrench"></i>
                                <span>Technical</span>
                            </label>
                        </div>
                        <div class="category-option">
                            <input type="radio" id="category_account" name="category" value="account" 
                                   {{ old('category') === 'account' ? 'checked' : '' }}>
                            <label for="category_account">
                                <i class="bi bi-person-gear"></i>
                                <span>Account</span>
                            </label>
                        </div>
                        <div class="category-option">
                            <input type="radio" id="category_other" name="category" value="other" 
                                   {{ old('category') === 'other' ? 'checked' : '' }}>
                            <label for="category_other">
                                <i class="bi bi-question-circle"></i>
                                <span>Other</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Priority Section -->
                <div class="form-section">
                    <h5><i class="bi bi-exclamation-triangle" style="color: #667eea;"></i> Priority Level</h5>
                    <p style="font-size: 0.9rem; color: #666; margin-bottom: 1rem;">How urgent is this issue?</p>
                    
                    <div class="priority-grid">
                        <div class="priority-option">
                            <input type="radio" id="priority_low" name="priority" value="low" 
                                   {{ old('priority') === 'low' ? 'checked' : '' }} required>
                            <label for="priority_low">
                                <i class="bi bi-circle"></i> Low
                            </label>
                        </div>
                        <div class="priority-option">
                            <input type="radio" id="priority_normal" name="priority" value="normal" 
                                   {{ old('priority') === 'normal' || !old('priority') ? 'checked' : '' }}>
                            <label for="priority_normal">
                                <i class="bi bi-circle-fill"></i> Normal
                            </label>
                        </div>
                        <div class="priority-option">
                            <input type="radio" id="priority_high" name="priority" value="high" 
                                   {{ old('priority') === 'high' ? 'checked' : '' }}>
                            <label for="priority_high">
                                <i class="bi bi-exclamation-circle"></i> High
                            </label>
                        </div>
                        <div class="priority-option">
                            <input type="radio" id="priority_urgent" name="priority" value="urgent" 
                                   {{ old('priority') === 'urgent' ? 'checked' : '' }}>
                            <label for="priority_urgent">
                                <i class="bi bi-exclamation-circle-fill"></i> Urgent
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <a href="{{ route('portal.support.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-submit">
                        <i class="bi bi-check-circle"></i> Submit Support Request
                    </button>
                </div>
            </form>

            <!-- Information Card -->
            <div class="info-card">
                <h6><i class="bi bi-info-circle"></i> Support Guidelines</h6>
                <ul>
                    <li><strong>Response Times:</strong> Urgent - 1 hour, High - 2 hours, Normal - 24 hours, Low - 2-3 days</li>
                    <li><strong>Be Detailed:</strong> The more information you provide, the faster we can help</li>
                    <li><strong>Include Screenshots:</strong> If applicable, screenshots help us understand the issue better</li>
                    <li><strong>One Issue Per Ticket:</strong> For multiple issues, please create separate tickets</li>
                </ul>
            </div>
        </div>

        <!-- Quick Stats Sidebar -->
        <div class="col-lg-4">
            <div class="card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 16px; color: white; box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2); margin-bottom: 1.5rem;">
                <div class="card-body">
                    <h6 class="text-white mb-3"><i class="bi bi-info-circle"></i> Getting Help</h6>
                    <p style="font-size: 0.9rem; line-height: 1.6;">
                        Our support team is ready to assist you. Create a ticket and we'll get back to you as soon as possible. Check your tickets to track responses.
                    </p>
                    <a href="{{ route('portal.support.index') }}" class="btn btn-light btn-sm" style="margin-top: 1rem;">
                        <i class="bi bi-list-check"></i> View My Tickets
                    </a>
                </div>
            </div>

            <div class="card" style="border-radius: 16px; border: 1px solid #e0e0e0; background: #f8f9fa;">
                <div class="card-body">
                    <h6 style="color: #667eea; font-weight: 700; margin-bottom: 1rem;"><i class="bi bi-question-circle"></i> FAQ</h6>
                    <p style="font-size: 0.9rem; color: #666; margin-bottom: 0.75rem;">Before contacting support, check our FAQ for quick answers to common questions.</p>
                    <a href="#" class="btn btn-sm" style="background: #667eea; color: white; border-radius: 8px;">
                        <i class="bi bi-file-text"></i> Read FAQ
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
