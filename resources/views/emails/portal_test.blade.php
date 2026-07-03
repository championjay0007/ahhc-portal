<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $settings['website_name'] ?? 'Portal' }} - Test Email</title>
</head>
<body>
    <div style="font-family: Arial, Helvetica, sans-serif; color: #222;">
        <h2>{{ $settings['website_name'] ?? 'Portal' }} — Test Email</h2>
        <p>This is a test email sent from the portal to verify the email configuration.</p>
        <p>If you received this, the mail settings are working.</p>
        <hr>
        <p style="font-size: 0.9rem; color:#666">Sent from {{ $settings['website_name'] ?? 'Portal' }} ({{ $settings['support_email'] ?? '' }})</p>
    </div>
</body>
</html>
