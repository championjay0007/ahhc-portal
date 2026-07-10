@php
    $organization = $organization ?? 'AHHC Portal';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Onboarding update' }}</title>
</head>
<body style="margin:0;padding:0;background:#f5f7fb;font-family:Arial,sans-serif;color:#172033;">
    <div style="max-width:640px;margin:32px auto;padding:24px;background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;">
        <div style="margin-bottom:20px;">
            <div style="display:inline-block;padding:6px 10px;border-radius:999px;background:#e8f1ff;color:#0d6efd;font-size:12px;font-weight:bold;letter-spacing:0.04em;text-transform:uppercase;">{{ $organization }}</div>
        </div>
        <h1 style="margin:0 0 12px;font-size:24px;color:#0E3863;">{{ $title ?? 'Onboarding update' }}</h1>
        <p style="margin:0 0 12px;font-size:16px;line-height:1.6;">{{ $greeting ?? 'Hello,' }}</p>
        <p style="margin:0 0 12px;font-size:16px;line-height:1.6;">{{ $intro ?? 'We have an update for your onboarding journey.' }}</p>
        <p style="margin:0 0 18px;font-size:16px;line-height:1.6;">{{ $body ?? 'Please take a moment to review the latest status.' }}</p>
        @if(!empty($ctaLabel) && !empty($ctaUrl))
            <p style="margin:0 0 16px;">
                <a href="{{ $ctaUrl }}" style="display:inline-block;padding:12px 20px;background:#0d6efd;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:bold;">{{ $ctaLabel }}</a>
            </p>
        @endif
        @if(!empty($secondaryLabel) && !empty($secondaryUrl))
            <p style="margin:0 0 8px;font-size:14px;line-height:1.6;">
                <a href="{{ $secondaryUrl }}" style="color:#0d6efd;text-decoration:none;">{{ $secondaryLabel }}</a>
            </p>
        @endif
        <p style="margin:24px 0 0;font-size:13px;color:#6b7280;line-height:1.6;">This is an automated message from {{ $organization }}. Please do not reply directly to this email.</p>
    </div>
</body>
</html>
