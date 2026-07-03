<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $siteName = $portalSettings['website_name'] ?? 'AHHC Portal';
        $faviconPath = $portalSettings['favicon_path'] ?? null;
    @endphp
    <title>@yield('title', $siteName)</title>
    <link rel="icon" href="{{ ! empty($faviconPath) ? asset('storage/' . $faviconPath) : asset('favicon.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; background: #f4f7fe; color: #1f2937; }
        .auth-shell { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem; }
        .auth-card { width: 100%; max-width: 960px; border: 0; border-radius: 1.75rem; overflow: hidden; box-shadow: 0 30px 70px rgba(15, 23, 42, 0.12); background: #ffffff; }
        .auth-card .card-body { padding: 2rem; }
        .wizard-step-nav .nav-link { border-radius: 999px; padding: .65rem 1rem; font-size: .9rem; }
        .wizard-step-nav .nav-link.active { background: #0d6efd; color: #fff; }
        .wizard-progress { height: .5rem; border-radius: 999px; overflow: hidden; background: #e9ecef; }
        .wizard-progress-bar { background: linear-gradient(135deg, #0d6efd, #6610f2); }
        .step-card { border: 1px solid #e5e7eb; border-radius: 1.25rem; background: #fafbff; }
        .step-card .card-body { padding: 1.75rem; }
        @media (max-width: 767.98px) {
            .auth-card { border-radius: 1.25rem; }
            .wizard-step-nav .nav-link { font-size: .8rem; padding: .55rem .75rem; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="auth-shell">
    <div class="card auth-card shadow-sm">
        <div class="card-body">
            @yield('content')
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+7HAuoJl+0I4a9yF8BWw7NhcWr7x9" crossorigin="anonymous"></script>
@stack('scripts')
</body>
</html>
