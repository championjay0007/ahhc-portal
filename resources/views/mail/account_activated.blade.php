<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Your account is now active</title>
    <style>
        .container { font-family: Arial, sans-serif; color: #333; padding: 20px; }
        .btn { display: inline-block; padding: 10px 18px; background:#0d6efd; color:#fff; text-decoration:none; border-radius:6px }
        .footer { margin-top: 24px; font-size: 13px; color: #666 }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your account is now active</h2>
        <p>Hi {{ $name }},</p>
        <p>Your account has been created successfully. Please sign in to continue with your onboarding and complete the remaining steps.</p>
        <p><a class="btn" href="{{ $login_url }}">Sign in to the portal</a></p>
        <p>If you prefer, you can open your dashboard after signing in: <a href="{{ $dashboard_url }}">Open dashboard</a></p>
        <p class="footer">If you did not expect this message, please contact our support team.</p>
    </div>
</body>
</html>
