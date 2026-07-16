<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your account is now active</title>
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
        .header { background: linear-gradient(135deg,#356991,#19B0A5,#72CEAC); padding: 45px 30px; text-align: center; color: #ffffff; }
        .header img { width: 90px; margin-bottom: 20px; }
        .badge { display: inline-block; padding: 6px 16px; background: rgba(255,255,255,.18); border-radius: 50px; color: #ffffff; font-size: 12px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 20px; }
        .title { margin: 20px 0 10px; font-size: 32px; line-height: 1.1; }
        .subtitle { margin: 0; font-size: 16px; line-height: 1.75; color: #eafdfc; }
        .body { padding: 45px 40px; }
        .body p { margin: 0 0 24px; font-size: 16px; line-height: 1.85; color: #4b5563; }
        .button-wrap { text-align: center; margin: 40px 0; }
        .button { display: inline-block; padding: 16px 42px; background: #19B0A5; color: #ffffff; text-decoration: none; border-radius: 10px; font-size: 16px; font-weight: 700; }
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
                <a href="{{ url('/') }}" style="display:inline-block;">
                    <img src="{{ asset('storage/branding/logo.jpg') }}" alt="{{ $organization ?? config('app.name', 'AHHC Portal') }} Logo">
                </a>
                <div class="badge">Account Activated</div>
                <h1 class="title">Your account is now active</h1>
                <p class="subtitle">You can now access the AHHC portal.</p>
            </div>
            <div class="body">
                <p>Hi {{ $name }},</p>
                <p>Your account has been activated successfully. Please sign in to continue with your onboarding and complete the remaining steps.</p>
                <div class="button-wrap">
                    <a class="button" href="{{ $login_url }}">Sign in to the portal</a>
                </div>
                <p>If you prefer, you can open your dashboard after signing in: <a href="{{ $dashboard_url }}" style="color:#0d6efd;text-decoration:none;">Open dashboard</a></p>
                <hr style="border:none;border-top:1px solid #E5E7EB;margin:40px 0;">
                <p style="margin:0;font-size:16px;font-weight:700;color:#356991;">{{ $organization ?? config('app.name', 'AHHC Portal') }}</p>
                <p style="margin-top:8px;font-size:14px;color:#6b7280;">Compassion you can Trust.<br>Care you deserve.</p>
            </div>
            <div class="footer">
                <p>If you did not expect this message, please contact our support team.</p>
                <p class="footer-brand">© {{ $year ?? now()->year }} {{ $organization ?? config('app.name', 'AHHC Portal') }}. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
