<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    @php
        $siteName = $portalSettings['website_name'] ?? 'AHHC Portal';
        $faviconPath = $portalSettings['favicon_path'] ?? null;
    @endphp
    <title>{{ $siteName }} | MFA Setup</title>
    <link rel="icon" href="{{ ! empty($faviconPath) ? asset('storage/' . $faviconPath) : asset('favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif; background: #f4f7fe; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { width: min(760px, 100%); background: white; border-radius: 1.8rem; box-shadow: 0 25px 60px rgba(16, 24, 40, 0.12); padding: 2.5rem; }
        .form-control { border-radius: 0.95rem; padding: 1rem 1.1rem; }
        .btn-modern { border-radius: 1.2rem; padding: 0.95rem 1.3rem; background: linear-gradient(105deg, #3358ff 0%, #5b3eff 100%); border: none; }
        .btn-modern:hover { transform: translateY(-1px); }
        .alert-custom { border-radius: 1.15rem; }
        .qr-panel { border: 1px solid #e9edf2; border-radius: 1.25rem; padding: 1.25rem; }
        .code-badge { display: inline-flex; padding: 0.75rem 0.95rem; border: 1px solid #e2e8f0; border-radius: 0.9rem; margin: 0.2rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; font-size: 0.88rem; background: #f8fafc; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="text-center mb-4">
        <div class="mb-3"><i class="bi bi-shield-lock-fill fs-1 text-primary"></i></div>
        <h2 class="h4 fw-semibold">Secure your account with MFA</h2>
        <p class="text-muted mb-0">Scan the QR code below with your authenticator app, then confirm the code to complete setup.</p>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-custom">{{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-custom">
            <ul class="mb-0 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-md-6">
            <div class="qr-panel text-center">
                <div class="mb-3 text-secondary small text-uppercase fw-semibold">Scan this QR code</div>
                <div>{!! $qrCodeSvg !!}</div>
                <p class="mt-3 small text-muted mb-0">If you cannot scan the QR code, add the secret manually in your authenticator app.</p>
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-4">
                <h3 class="h5 fw-semibold">Complete MFA enrollment</h3>
                <p class="text-muted small">Use your authenticator app to generate a verification code and confirm below.</p>
            </div>

            <div class="mb-4 p-3 bg-light rounded-3">
                <div class="small text-uppercase text-secondary mb-2">Recovery codes</div>
                <p class="small text-muted mb-3">Store these recovery codes securely. Each code can be used once if you lose access to your authenticator app.</p>
                <div class="d-flex flex-wrap gap-2">
                    @foreach($recoveryCodes as $code)
                        <span class="code-badge">{{ $code }}</span>
                    @endforeach
                </div>
            </div>

            <form method="POST" action="{{ route('portal.mfa.confirm') }}">
                @csrf

                <div class="mb-3">
                    <label for="code" class="form-label small fw-semibold">Authenticator code</label>
                    <input id="code" name="code" type="text" value="{{ old('code') }}" class="form-control" placeholder="123 456" required autofocus>
                </div>

                <button type="submit" class="btn btn-modern w-100 text-white">Confirm MFA enrollment</button>
            </form>
        </div>
    </div>

    <div class="mt-4 small text-muted text-center">If you do not want to complete MFA setup right now, sign out and return later once your account is ready.</div>
</div>
</body>
</html>
