@php
    $link = route('portal.onboarding.show', ['token' => $participant->onboarding_token]);
    $expires = optional($participant->onboarding_expires_at)->format('d M Y H:i');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Begin your AHHC onboarding</title>
</head>
<body style="margin:0;padding:0;background:#f5f7fb;font-family:Arial,sans-serif;color:#172033;">
    <div style="max-width:640px;margin:32px auto;padding:24px;background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;">
        <div style="margin-bottom:20px;">
            <div style="display:inline-block;padding:6px 10px;border-radius:999px;background:#e8f1ff;color:#0d6efd;font-size:12px;font-weight:bold;letter-spacing:0.04em;text-transform:uppercase;">AHHC Portal</div>
        </div>
        <h1 style="margin:0 0 12px;font-size:24px;color:#0E3863;">Welcome, {{ $participant->first_name ?? 'there' }}</h1>
        <p style="margin:0 0 12px;font-size:16px;line-height:1.6;">Hello {{ $participant->first_name ?? 'there' }},</p>
        <p style="margin:0 0 12px;font-size:16px;line-height:1.6;">You’re invited to begin your onboarding with Allegiance Heart &amp; Home Care. This first step helps us confirm your information, review your documents, and prepare your portal access.</p>
        <p style="margin:0 0 18px;font-size:16px;line-height:1.6;">Please use the secure link below to continue. The link remains active until {{ $expires }}.</p>
        <p style="margin:0 0 16px;">
            <a href="{{ $link }}" style="display:inline-block;padding:12px 20px;background:#0d6efd;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:bold;">Continue onboarding</a>
        </p>
        <p style="margin:0 0 8px;font-size:14px;line-height:1.6;">If you have any questions or did not expect this invitation, please contact our support team for assistance.</p>
        <p style="margin:24px 0 0;font-size:13px;color:#6b7280;line-height:1.6;">This is an automated message from AHHC Portal. Please do not reply directly to this email.</p>
    </div>
</body>
</html>
