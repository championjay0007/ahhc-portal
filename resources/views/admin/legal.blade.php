@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Legal Documents</h4>
                <p class="text-muted mb-0">Upload and manage the public Privacy Policy and Terms of Service documents.</p>
            </div>
            <div>
                <a href="{{ route('portal.admin.settings') }}" class="btn btn-sm btn-outline-secondary">Back to Settings</a>
            </div>
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('portal.admin.legal.update') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Privacy Policy</label>
                            <input type="file" name="privacy_policy" class="form-control">
                            @if(! empty($settings['privacy_policy_path']))
                                <div class="mt-2">
                                    <a href="{{ asset('storage/' . $settings['privacy_policy_path']) }}" target="_blank" rel="noopener">View currently uploaded Privacy Policy</a>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Terms of Service</label>
                            <input type="file" name="terms_of_service" class="form-control">
                            @if(! empty($settings['terms_of_service_path']))
                                <div class="mt-2">
                                    <a href="{{ asset('storage/' . $settings['terms_of_service_path']) }}" target="_blank" rel="noopener">View currently uploaded Terms of Service</a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Save legal documents</button>
                </form>
            </div>
        </div>
    </div>
@endsection
