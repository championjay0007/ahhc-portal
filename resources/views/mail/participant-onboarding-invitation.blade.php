<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Complete your AHHC onboarding</title>
    @php
        $portalSettings = $portalSettings ?? [];
        $logo = $logo ?? \App\Services\EmailBrandingService::logoUrl();
        $organization = $organization ?? config('app.name', 'AHHC Portal');
        $year = $year ?? now()->year;

        // Prefer a locally uploaded branding logo when available to avoid remote fetch issues.
        $localBrandingPath = storage_path('app/public/branding/logo.jpg');
        $localBrandingUrl = file_exists($localBrandingPath) ? asset('storage/branding/logo.jpg') : null;

        // Inline SVG fallback (base64) used when no local branding and remote image unavailable.
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="120"><rect width="100%" height="100%" fill="#19B0A5" rx="12" ry="12"/><text x="50%" y="50%" font-family="Segoe UI, Roboto, Arial, Helvetica, sans-serif" font-size="36" fill="#ffffff" dominant-baseline="middle" text-anchor="middle">AHHC</text></svg>';
        $inlineLogoData = 'data:image/svg+xml;base64,' . base64_encode($svg);

        $emailLogoSrc = $localBrandingUrl ?? ($logo ?? $inlineLogoData);
    @endphp
    <style>
        body { margin: 0; padding: 0; background: #f6f7f9; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; color: #1f2937; }
        .wrapper { width: 100%; padding: 20px; }
        .card { max-width: 650px; margin: 0 auto; background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 12px 35px rgba(0,0,0,.08); }
        .header { background: linear-gradient(135deg,#356991,#19B0A5,#72CEAC); padding: 45px 30px; text-align: center; color: #ffffff; }
        .header img {
            width: 110px;
            height: 110px;
            margin-bottom: 14px;
            border-radius: 50%;
            display: block;
            margin-left: auto;
            margin-right: auto;
            object-fit: cover;
            background: #ffffff;
            padding: 6px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        }
        .badge { display: inline-block; padding: 6px 16px; background: rgba(255,255,255,.18); border-radius: 50px; color: #ffffff; font-size: 12px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 20px; }
        .title { margin: 20px 0 10px; font-size: 32px; line-height: 1.1; }
        .subtitle { margin: 0; font-size: 16px; line-height: 1.75; color: #eafdfc; }
        .body { padding: 45px 40px; }
        .body p { margin: 0 0 24px; font-size: 16px; line-height: 1.85; color: #4b5563; }
        .body p strong { color: #356991; }
        .button-wrap { text-align: center; margin: 40px 0; }
        .button { display: inline-block; padding: 16px 42px; background: #19B0A5; color: #ffffff; text-decoration: none; border-radius: 10px; font-size: 16px; font-weight: 700; }
        .panel { background: #f8fafc; border-left: 5px solid #356991; border-radius: 12px; margin-bottom: 30px; }
        .panel-inner { padding: 28px; }
        .panel-heading { margin: 0 0 18px; font-size: 18px; font-weight: 700; color: #356991; }
        .panel-table { width: 100%; border-collapse: collapse; color: #374151; }
        .panel-table td { padding: 8px 0; vertical-align: top; }
        .panel-table td.label { width: 35%; font-weight: 700; }
        .note-panel { margin-top: 30px; background: #fffdf7; border-left: 5px solid #f59e0b; border-radius: 12px; }
        .warning-panel { margin-top: 30px; background: #fff8f8; border-left: 5px solid #eb3035; border-radius: 12px; }
        .support-panel { margin-top: 30px; background: #f8fafc; border: 1px solid #e5e7eb; border-radius: 12px; }
        .panel h3 { margin: 0 0 15px; font-size: 18px; font-weight: 700; }
        .panel p { margin: 0; font-size: 15px; line-height: 1.8; color: #4b5563; }
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
                    <img src="{{ $emailLogoSrc }}" alt="{{ $badge ?? ($organization ?? config('app.name', 'AHHC Portal')) }}" style="width:110px;height:110px;border-radius:50%;display:block;margin:0 auto 14px;object-fit:cover;background:#fff;padding:6px;box-shadow:0 6px 18px rgba(0,0,0,0.08);border:0;" />
                </a>
                <div class="badge">Onboarding Invitation</div>
                <h1 class="title">Complete your AHHC onboarding</h1>
                <p class="subtitle">Your secure onboarding link is ready. Please follow the steps to activate your portal access.</p>
            </div>
            <div class="body">
                <p>Hello <strong>{{ $participant->first_name ?? 'Participant' }}</strong>,</p>
                <p>You're invited to begin your onboarding with <strong style="color:#19B0A5;">{{ $organization ?? config('app.name', 'AHHC Portal') }}</strong>. This helps us confirm your details, review your documentation, and prepare your portal access.</p>
                <p>Please use the secure link below to continue. The link remains active until <strong>{{ $expires_at ?? optional($participant->onboarding_expires_at)->format('d M Y H:i') }}</strong>.</p>
                <div class="button-wrap">
                    <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center" style="margin:0 auto;">
                        <tr>
                            <td align="center">
                                <!--[if mso]>
                                <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $onboarding_url ?? route('portal.onboarding.show', ['token' => $participant->onboarding_token]) }}" style="height:44px;v-text-anchor:middle;width:240px;" arcsize="8%" strokecolor="#19B0A5" fillcolor="#19B0A5">
                                    <w:anchorlock/>
                                    <center style="color:#ffffff;font-family:Arial, Helvetica, sans-serif;font-size:15px;font-weight:700;">Continue onboarding</center>
                                </v:roundrect>
                                <![endif]-->
                                <!--[if !mso]><!-- -->
                                <a href="{{ $onboarding_url ?? route('portal.onboarding.show', ['token' => $participant->onboarding_token]) }}" class="button" style="background:#19B0A5;color:#ffffff;padding:12px 26px;border-radius:8px;font-weight:700;display:inline-block;white-space:nowrap;text-decoration:none;">Continue onboarding</a>
                                <!--<![endif]-->
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="panel">
                    <div class="panel-inner">
                        <p class="panel-heading">Your details</p>
                        <table class="panel-table">
                            <tr><td class="label">Name</td><td>{{ trim(($participant->first_name ?? '') . ' ' . ($participant->last_name ?? '')) }}</td></tr>
                            <tr><td class="label">Email</td><td>{{ $participant->email ?? '—' }}</td></tr>
                            <tr><td class="label">Expires</td><td style="color:#EB3035;font-weight:700;">{{ $expires_at ?? optional($participant->onboarding_expires_at)->format('d M Y H:i') }}</td></tr>
                        </table>
                    </div>
                </div>
                <div class="support-panel">
                    <div class="panel-inner">
                        <h3>Need help?</h3>
                        <p>If you have any questions or did not expect this invitation, please contact our support team for assistance.</p>
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
