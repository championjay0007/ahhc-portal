@php
    $link = route('portal.onboarding.show', ['token' => $participant->onboarding_token]);
    $expires = optional($participant->onboarding_expires_at)->format('d M Y H:i');
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allegiance Heart & Home Care Portal Invitation</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1c1c1c;">
    <div style="max-width: 600px; margin: 0 auto; padding: 24px; background: #ffffff; border: 1px solid #e5e7eb; border-radius: 12px;">
        <h1 style="font-size: 24px; margin-bottom: 16px; color: #0d6efd;">Welcome to the Allegiance Heart &amp; Home Care Portal</h1>
        <p style="margin-bottom: 16px;">Hello {{ $participant->first_name }},</p>
        <p style="margin-bottom: 16px;">You have been invited to complete your participant onboarding with Allegiance Heart &amp; Home Care. Please use the link below to set your password and confirm your details.</p>
        <p style="margin-bottom: 24px;"><a href="{{ $link }}" style="display: inline-block; padding: 12px 20px; background: #0d6efd; color: #fff; border-radius: 8px; text-decoration: none;">Complete onboarding</a></p>
        <p style="margin-bottom: 16px;">This link is valid until {{ $expires }}.</p>
        <p style="margin-bottom: 16px;">If you did not expect this invitation, please contact the Allegiance Heart &amp; Home Care support team.</p>
        <p style="font-size: 14px; color: #6b7280;">Allegiance Heart &amp; Home Care Care Portal</p>
    </div>
</body>
</html>
