<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Onboarding update' }}</title>
    @php
        $portalSettings = $portalSettings ?? [];
        $logo = $logo ?? \App\Services\EmailBrandingService::logoUrl();
        $organization = $organization ?? config('app.name', 'AHHC Portal');
        $year = $year ?? now()->year;
    @endphp
    <style>
        body { margin: 0; padding: 0; background: #f6f7f9; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; color: #1f2937; }
        .wrapper { width: 100%; padding: 20px; }
        .card { max-width: 650px; margin: 0 auto; background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 12px 35px rgba(0,0,0,.08); }
        .header { background: linear-gradient(135deg,#356991,#19B0A5,#72CEAC); background-color: #356991; padding: 45px 30px; text-align: center; color: #ffffff; }
        .email-logo { width: 110px; max-width: 110px; margin: 0 auto 14px; }
        .email-logo img { width: 100%; max-width: 110px; height: auto; display: block; border: 0; outline: none; text-decoration: none; border-radius: 999px; background: #ffffff; }
        .badge { display: inline-block; padding: 6px 16px; background: rgba(255,255,255,.18); border-radius: 50px; color: #ffffff; font-size: 12px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 20px; }
        .title { margin: 20px 0 10px; font-size: 32px; line-height: 1.1; }
        .subtitle { margin: 0; font-size: 16px; line-height: 1.75; color: #eafdfc; }
        .body { padding: 45px 40px; }
        .body p { margin: 0 0 24px; font-size: 16px; line-height: 1.85; color: #4b5563; }
        .button-wrap { text-align: center; margin: 40px 0; }
        .button { display: inline-block; padding: 16px 42px; background: #19B0A5; color: #ffffff; text-decoration: none; border-radius: 10px; font-size: 16px; font-weight: 700; }
        .support-panel { width: 100%; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 12px; margin-top: 30px; }
        .support-panel .panel-inner { padding: 28px; }
        .support-panel h3 { margin: 0 0 15px; font-size: 18px; font-weight: 700; color: #356991; }
        .support-panel p { margin: 0; font-size: 15px; line-height: 1.8; color: #4b5563; }
        .footer { padding: 28px; background: #356991; text-align: center; color: #d8eef3; }
        .footer p { margin: 0; font-size: 13px; line-height: 1.85; }
        .footer-brand { margin-top: 12px; color: #ffffff; font-size: 13px; }
        @media screen and (max-width: 600px) {
            .header, .body { padding-left: 20px; padding-right: 20px; }
            .button { width: 100%; box-sizing: border-box; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="header">
                @php
                    $localBrandingPath = storage_path('app/public/branding/logo.jpg');
                    $localBrandingUrl = file_exists($localBrandingPath) ? asset('storage/branding/logo.jpg') : null;
                    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="120"><rect width="100%" height="100%" fill="#19B0A5" rx="12" ry="12"/><text x="50%" y="50%" font-family="Segoe UI, Roboto, Arial, Helvetica, sans-serif" font-size="36" fill="#ffffff" dominant-baseline="middle" text-anchor="middle">AHHC</text></svg>';
                    $inlineLogoData = 'data:image/svg+xml;base64,' . base64_encode($svg);
                    $emailLogoSrc = $localBrandingUrl ?? ($logo ?? $inlineLogoData);
                    $supportEmail = $supportEmail ?? ($portalSettings['support_email'] ?? \App\Models\PortalSetting::where('key', 'support_email')->value('value')) ?? config('app.support_email', 'support@example.com');
                @endphp
                <div class="email-logo">
                    <a href="{{ url('/') }}" style="display:block;text-align:center;">
                        <img src="{{ $emailLogoSrc }}" alt="{{ $organization ?? config('app.name', 'AHHC Portal') }} Logo" style="width:100%;max-width:110px;height:auto;display:block;border:0;outline:none;text-decoration:none;border-radius:999px;background:#ffffff;" />
                    </a>
                </div>
                <div class="badge">{{ $organization ?? config('app.name', 'AHHC Portal') }}</div>
                <h1 class="title">{{ $title ?? 'Onboarding update' }}</h1>
                <p class="subtitle">{{ $subtitle ?? 'An update on your onboarding journey.' }}</p>
            </div>
            <div class="body">
                <p>{{ $greeting ?? 'Hello,' }}</p>
                <p>{{ $intro ?? 'We have an update for your onboarding journey.' }}</p>
                <p>{{ $body ?? 'Please take a moment to review the latest status.' }}</p>
                @if(!empty($ctaLabel) && !empty($ctaUrl))
                    <div class="button-wrap">
                        <a class="button" href="{{ $ctaUrl }}">{{ $ctaLabel }}</a>
                    </div>
                @endif
                @if(!empty($secondaryLabel) && !empty($secondaryUrl))
                    <p style="margin:0 0 24px;font-size:15px;line-height:1.8;"><a href="{{ $secondaryUrl }}" style="color:#0d6efd;text-decoration:none;">{{ $secondaryLabel }}</a></p>
                @endif
                <div class="support-panel">
                    <div class="panel-inner">
                        <h3>Need help?</h3>
                        <p>If you have any questions about your onboarding status, please contact our support team for assistance.</p>
                    </div>
                </div>
                <hr style="border:none;border-top:1px solid #E5E7EB;margin:40px 0;">
                <p style="margin:0;font-size:16px;font-weight:700;color:#356991;">{{ $organization ?? config('app.name', 'AHHC Portal') }}</p>
                <p style="margin-top:8px;font-size:14px;color:#6b7280;">Compassion you can Trust.<br>Care you deserve.</p>
            </div>
            <div class="footer">
                <p>This is an automated message from {{ $organization ?? config('app.name', 'AHHC Portal') }}. Please do not reply directly to this email.</p>
                <p class="footer-brand">© {{ $year ?? now()->year }} {{ $organization ?? config('app.name', 'AHHC Portal') }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
