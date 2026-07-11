@extends('layouts.admin')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-0">Admin Settings</h4>
                <p class="text-muted mb-0">Update organization defaults and portal configuration.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('portal.admin.dashboard') }}" class="btn btn-sm btn-outline-secondary">Dashboard</a>
                <a href="{{ route('portal.admin.users') }}" class="btn btn-sm btn-outline-secondary">User management</a>
            </div>
        </div>

        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="mb-1">Push notifications (VAPID)</h5>
                        <p class="text-muted small mb-0">Configure Web Push for native browser notifications.</p>
                    </div>
                    <form method="POST" action="{{ route('portal.admin.settings.generate_vapid_keys') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="vapid_subject" value="{{ old('vapid_subject', $settings['vapid_subject'] ?? 'mailto:hello@example.com') }}">
                        <button type="submit" class="btn btn-outline-primary btn-sm">Generate VAPID keys</button>
                    </form>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h5 class="mb-1">System maintenance</h5>
                        <p class="text-muted small mb-0">Clear cached application data when needed.</p>
                    </div>
                    <form method="POST" action="{{ route('portal.admin.settings.clear_cache') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-warning btn-sm">
                            <i class="bi bi-trash me-1"></i>Clear cache
                        </button>
                    </form>
                </div>

                <form id="admin-settings-form" method="POST" action="{{ route('portal.admin.settings.update') }}" enctype="multipart/form-data">
                    @csrf

                    <h5 class="mb-3">Website details</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Website name</label>
                            <input type="text" name="website_name" value="{{ old('website_name', $settings['website_name'] ?? '') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Website subtitle</label>
                            <input type="text" name="website_subtitle" value="{{ old('website_subtitle', $settings['website_subtitle'] ?? '') }}" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Website description</label>
                            <textarea name="website_description" class="form-control" rows="3">{{ old('website_description', $settings['website_description'] ?? '') }}</textarea>
                        </div>
                    </div>

                    <h5 class="mb-3">Branding</h5>
                    <p class="text-muted small mb-3">Legal documents are managed on the dedicated Legal Documents page via the admin sidebar.</p>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Logo</label>
                            <input type="file" name="logo" class="form-control">
                            @if(! empty($settings['logo_path']))
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $settings['logo_path']) }}" alt="Logo" style="max-height: 60px;">
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Favicon</label>
                            <input type="file" name="favicon" class="form-control">
                            @if(! empty($settings['favicon_path']))
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $settings['favicon_path']) }}" alt="Favicon" style="max-height: 40px;">
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">PWA icon</label>
                            <input type="file" name="pwa_icon" class="form-control">
                            <small class="text-muted d-block">Recommended PNG 512x512.</small>
                            @if(! empty($settings['pwa_icon_path']))
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $settings['pwa_icon_path']) }}" alt="PWA icon" style="max-height: 60px; border-radius: 12px;">
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Primary color</label>
                            <input type="color" name="primary_color" value="{{ old('primary_color', $settings['primary_color'] ?? '#0d6efd') }}" class="form-control form-control-color">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Secondary color</label>
                            <input type="color" name="secondary_color" value="{{ old('secondary_color', $settings['secondary_color'] ?? '#6610f2') }}" class="form-control form-control-color">
                        </div>
                    </div>

                    <h5 class="mb-3 mt-4"><i class="bi bi-palette me-2"></i>Dashboard Colors</h5>
                    <p class="text-muted small mb-3">Separate color scheme for admin and participant dashboards.</p>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Dashboard Primary Color</label>
                            <input type="color" name="dashboard_primary_color" value="{{ old('dashboard_primary_color', $settings['dashboard_primary_color'] ?? '#0E3863') }}" class="form-control form-control-color">
                            <small class="text-muted">Used for sidebar, buttons, and primary accents in dashboards.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Dashboard Secondary Color</label>
                            <input type="color" name="dashboard_secondary_color" value="{{ old('dashboard_secondary_color', $settings['dashboard_secondary_color'] ?? '#1699A1') }}" class="form-control form-control-color">
                            <small class="text-muted">Used for accent elements and highlights in dashboards.</small>
                        </div>
                    </div>

                    <h5 class="mb-3"><i class="bi bi-envelope me-2" style="color: #667eea;"></i>Email Configuration</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600;">Sender Name</label>
                            <input type="text" name="email_sender_name" value="{{ old('email_sender_name', $settings['email_sender_name'] ?? '') }}" class="form-control" style="border-radius: 8px; border: 1px solid #cbd5e1;" placeholder="e.g. Support Team">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" style="font-weight: 600;">Sender Email</label>
                            <input type="email" name="email_sender_address" value="{{ old('email_sender_address', $settings['email_sender_address'] ?? '') }}" class="form-control" style="border-radius: 8px; border: 1px solid #cbd5e1;" placeholder="noreply@example.com">
                        </div>
                        <div class="col-12">
                            <div style="background: linear-gradient(135deg, #f5f7fa 0%, #e0e7ff 100%); border-radius: 12px; padding: 1.5rem; border: 1px solid #cbd5e1;">
                                <label class="form-label" style="font-weight: 600; color: #667eea; display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1rem;"><i class="bi bi-toggle2-on"></i>Email Message Source</label>
                                <select name="email_template_source" class="form-select" style="border-radius: 8px; border: 1px solid #cbd5e1; appearance: none; background-image: url(\"data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8'%3E%3Cpath fill='%23667eea' d='M0 0l6 8 6-8z'/%3E%3C/svg%3E\"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px;">
                                    <option value="database"{{ old('email_template_source', $settings['email_template_source'] ?? 'database') === 'database' ? ' selected' : '' }}>🗄️ Use Database Message Templates (Admin-Managed)</option>
                                    <option value="code"{{ old('email_template_source', $settings['email_template_source'] ?? 'database') === 'code' ? ' selected' : '' }}>💻 Use Code-Based Email Messages (Original)</option>
                                </select>
                                <small class="text-muted" style="display: block; margin-top: 0.75rem;"><i class="bi bi-info-circle me-1"></i>Choose whether outgoing emails use the admin-managed templates or the original code-defined messages. When set to <strong>Database</strong>, the system will strictly use stored email template content and not fall back to the code defaults for active templates.</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SMTP host</label>
                            <input type="text" name="smtp_host" value="{{ old('smtp_host', $settings['smtp_host'] ?? '') }}" class="form-control">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">SMTP port</label>
                            <input type="number" name="smtp_port" value="{{ old('smtp_port', $settings['smtp_port'] ?? '') }}" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Encryption</label>
                            <select name="smtp_encryption" class="form-select">
                                <option value=""{{ old('smtp_encryption', $settings['smtp_encryption'] ?? '') === '' ? ' selected' : '' }}>None</option>
                                <option value="tls"{{ old('smtp_encryption', $settings['smtp_encryption'] ?? '') === 'tls' ? ' selected' : '' }}>TLS</option>
                                <option value="ssl"{{ old('smtp_encryption', $settings['smtp_encryption'] ?? '') === 'ssl' ? ' selected' : '' }}>SSL</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SMTP username</label>
                            <input type="text" name="smtp_username" value="{{ old('smtp_username', $settings['smtp_username'] ?? '') }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">SMTP password</label>
                            <input type="password" name="smtp_password" value="{{ old('smtp_password', $settings['smtp_password'] ?? '') }}" class="form-control">
                        </div>
                    </div>

                    <h5 class="mb-3">Platform settings</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Organization name</label>
                            <input type="text" name="organization_name" value="{{ old('organization_name', $settings['organization_name'] ?? '') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Support email</label>
                            <input type="email" name="support_email" value="{{ old('support_email', $settings['support_email'] ?? '') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Default user role</label>
                            <select name="default_user_role" class="form-select" required>
                                <option value="participant"{{ old('default_user_role', $settings['default_user_role'] ?? '') === 'participant' ? ' selected' : '' }}>Participant</option>
                                <option value="worker"{{ old('default_user_role', $settings['default_user_role'] ?? '') === 'worker' ? ' selected' : '' }}>Worker</option>
                                <option value="admin"{{ old('default_user_role', $settings['default_user_role'] ?? '') === 'admin' ? ' selected' : '' }}>Admin</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input type="hidden" name="require_mfa" value="0">
                                <input class="form-check-input" type="checkbox" id="require_mfa" name="require_mfa" value="1"{{ old('require_mfa', $settings['require_mfa'] ?? false) ? ' checked' : '' }}>
                                <label class="form-check-label" for="require_mfa">Require MFA for all users</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input type="hidden" name="report_export_emails" value="0">
                                <input class="form-check-input" type="checkbox" id="report_export_emails" name="report_export_emails" value="1"{{ old('report_export_emails', $settings['report_export_emails'] ?? false) ? ' checked' : '' }}>
                                <label class="form-check-label" for="report_export_emails">Send report export emails</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input type="hidden" name="incident_alerts" value="0">
                                <input class="form-check-input" type="checkbox" id="incident_alerts" name="incident_alerts" value="1"{{ old('incident_alerts', $settings['incident_alerts'] ?? false) ? ' checked' : '' }}>
                                <label class="form-check-label" for="incident_alerts">Enable incident alerts</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input type="hidden" name="pwa_enabled" value="0">
                                <input class="form-check-input" type="checkbox" id="pwa_enabled" name="pwa_enabled" value="1"{{ old('pwa_enabled', $settings['pwa_enabled'] ?? false) ? ' checked' : '' }}>
                                <label class="form-check-label" for="pwa_enabled">Enable Offline PWA (Service Worker)</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Session lifetime (minutes)</label>
                            <input type="number" name="session_lifetime" value="{{ old('session_lifetime', $settings['session_lifetime'] ?? 120) }}" class="form-control" min="1" max="10080">
                            <small class="text-muted">Controls how long a user session stays active before expiring.</small>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Invoice budget mode</label>
                            <select name="invoice_budget_mode" class="form-select">
                                <option value="preapproval_amount"{{ old('invoice_budget_mode', $settings['invoice_budget_mode'] ?? 'preapproval_amount') === 'preapproval_amount' ? ' selected' : '' }}>Pre-approval amount controls invoice spend</option>
                                <option value="committed_amount"{{ old('invoice_budget_mode', $settings['invoice_budget_mode'] ?? 'preapproval_amount') === 'committed_amount' ? ' selected' : '' }}>Committed amount controls budget drawdown directly</option>
                            </select>
                            <small class="text-muted">When set to committed amount, admin approval uses the invoice committed amount against the participant budget without requiring the invoice total to stay within the linked pre-approval amount.</small>
                        </div>
                    </div>

                    <hr class="my-4">
                    <p class="text-muted small mb-3"><a href="https://tools.web-push-codelab.appspot.com/" target="_blank">Generate VAPID keys</a> or use the built-in generator above.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">VAPID Public Key</label>
                            <input type="text" name="vapid_public_key" value="{{ old('vapid_public_key', $settings['vapid_public_key'] ?? '') }}" class="form-control" placeholder="Public key from VAPID key pair">
                            <small class="text-muted">Shared with browsers for subscription.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">VAPID Private Key</label>
                            <input type="password" name="vapid_private_key" value="{{ old('vapid_private_key', $settings['vapid_private_key'] ?? '') }}" class="form-control" placeholder="Private key from VAPID key pair">
                            <small class="text-muted">Keep this secret. Used to sign push messages.</small>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">VAPID Subject</label>
                            <input type="text" name="vapid_subject" value="{{ old('vapid_subject', $settings['vapid_subject'] ?? 'mailto:hello@example.com') }}" class="form-control" placeholder="mailto:admin@example.com">
                            <small class="text-muted">Contact info in case of push service issues (email or URL).</small>
                        </div>
                    </div>

                    <div class="mt-4 d-flex align-items-center gap-2">
                        <button type="submit" class="btn btn-primary">Save settings</button>
                        <div id="settings-submit-status" class="small text-muted" aria-live="polite"></div>
                    </div>
                </form>

                @if(! empty($manifest))
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">PWA Manifest Preview</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <strong>Name</strong>
                                    <p class="mb-0">{{ $manifest['name'] ?? '—' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <strong>Short name</strong>
                                    <p class="mb-0">{{ $manifest['short_name'] ?? '—' }}</p>
                                </div>
                                <div class="col-md-4">
                                    <strong>Description</strong>
                                    <p class="mb-0">{{ $manifest['description'] ?? '—' }}</p>
                                </div>
                            </div>

                            <div class="row g-3 mt-3">
                                <div class="col-md-4">
                                    <strong>Theme color</strong>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 28px; height: 28px; border-radius: 6px; background: {{ $manifest['theme_color'] ?? '#ffffff' }}; border: 1px solid #ddd"></div>
                                        <span>{{ $manifest['theme_color'] ?? '—' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <strong>Background color</strong>
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width: 28px; height: 28px; border-radius: 6px; background: {{ $manifest['background_color'] ?? '#ffffff' }}; border: 1px solid #ddd"></div>
                                        <span>{{ $manifest['background_color'] ?? '—' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <strong>Start URL</strong>
                                    <p class="mb-0">{{ $manifest['start_url'] ?? '—' }}</p>
                                </div>
                            </div>

                            @if(! empty($manifest['icons']) && is_array($manifest['icons']))
                                <div class="mt-4">
                                    <strong>Icons</strong>
                                    <div class="d-flex flex-wrap align-items-center gap-3 mt-2">
                                        @foreach($manifest['icons'] as $icon)
                                            <div class="text-center">
                                                <img src="{{ asset($icon['src']) }}" alt="PWA icon" style="width: 72px; height: 72px; object-fit: contain; border: 1px solid #dee2e6; border-radius: 12px; background: #fff; padding: 8px;">
                                                <div class="small text-muted mt-2">{{ $icon['sizes'] }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <hr class="my-4">
                <form method="POST" action="{{ route('portal.admin.settings.test_email') }}">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Send test email to</label>
                            <input type="email" name="test_email" value="{{ old('test_email') }}" class="form-control" placeholder="admin@example.com" required>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn btn-outline-primary">Send test email</button>
                        </div>
                    </div>
                </form>
                @if(session('error'))
                    <div class="alert alert-danger mt-3">{{ session('error') }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('admin-settings-form');
        const status = document.getElementById('settings-submit-status');

        if (!form || !status) {
            return;
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            const formData = new FormData(form);
            const submitButton = form.querySelector('button[type="submit"]');
            const originalText = submitButton ? submitButton.textContent : 'Save settings';

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.textContent = 'Saving...';
            }

            status.textContent = '';
            status.className = 'small text-muted';

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(async response => {
                const contentType = response.headers.get('content-type') || '';
                const payload = contentType.includes('application/json') ? await response.json() : await response.text();

                if (!response.ok) {
                    throw new Error(typeof payload === 'string' ? payload : 'Unable to save settings.');
                }

                if (typeof payload === 'object' && payload !== null) {
                    status.textContent = payload.message || 'Settings updated.';
                    status.className = 'small text-success';
                    return;
                }

                status.textContent = 'Settings updated.';
                status.className = 'small text-success';
            })
            .catch(error => {
                console.error(error);
                status.textContent = error.message || 'Unable to save settings.';
                status.className = 'small text-danger';
            })
            .finally(() => {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = originalText;
                }
            });
        });
    });
</script>
@endpush
