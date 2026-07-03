<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    @php
        $siteName = $portalSettings['website_name'] ?? 'AHHC Portal';
        $faviconPath = $portalSettings['favicon_path'] ?? null;
    @endphp
    <title>{{ $siteName }} | Two-Factor Challenge</title>
    <link rel="icon" href="{{ ! empty($faviconPath) ? asset('storage/' . $faviconPath) : asset('favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif; background: #f4f7fe; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .auth-card { width: min(520px, 100%); background: white; border-radius: 1.8rem; box-shadow: 0 25px 60px rgba(16, 24, 40, 0.12); padding: 2.5rem; }
        .form-control { border-radius: 0.95rem; padding: 1rem 1.1rem; }
        .btn-modern { border-radius: 1.2rem; padding: 0.95rem 1.3rem; background: linear-gradient(105deg, #3358ff 0%, #5b3eff 100%); border: none; }
        .btn-modern:hover { transform: translateY(-1px); }
        .alert-custom { border-radius: 1.15rem; }
    </style>
</head>
<body>
<div class="auth-card">
    <div class="text-center mb-4">
        <div class="mb-3"><i class="bi bi-shield-lock-fill fs-1 text-primary"></i></div>
        <h2 class="h4 fw-semibold">Two-Factor Authentication</h2>
        <p class="text-muted mb-0">Enter the code from your authenticator app or a recovery code.</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-custom">
            <ul class="mb-0 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('portal.mfa.challenge.verify') }}">
        @csrf
        <div class="mb-3">
            <label for="code" class="form-label small fw-semibold">Authentication code</label>
            <input id="code" name="code" type="text" value="{{ old('code') }}" class="form-control" placeholder="123 456" required autofocus>
        </div>

        <button type="submit" class="btn btn-modern w-100 text-white">Verify code</button>
    </form>

    <div class="mt-4 text-center text-muted small">
        If your authenticator app is unavailable, use one of your recovery codes from setup.
    </div>
</div>
</body>
</html>
