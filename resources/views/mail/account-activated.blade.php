<div style="font-family: Arial, sans-serif; color: #172033; background:#f5f7fb; padding:24px;">
    <div style="max-width:640px;margin:0 auto;padding:24px;background:#ffffff;border:1px solid #e5e7eb;border-radius:16px;">
        <div style="margin-bottom:20px;">
            <div style="display:inline-block;padding:6px 10px;border-radius:999px;background:#e8f1ff;color:#0d6efd;font-size:12px;font-weight:bold;letter-spacing:0.04em;text-transform:uppercase;">{{ $organization }}</div>
        </div>
        <h2 style="margin:0 0 12px;color:#0E3863;font-size:24px;">Your account is now active</h2>
        <p style="margin:0 0 12px;font-size:16px;line-height:1.6;">Hi {{ $name }},</p>
        <p style="margin:0 0 12px;font-size:16px;line-height:1.6;">Your account has been activated successfully. You can now sign in and access your portal dashboard whenever you’re ready.</p>
        <p style="margin:0 0 16px;font-size:16px;line-height:1.6;">Use the button below to sign in and begin using the portal.</p>
        <p style="margin:0 0 16px;">
            <a href="{{ $login_url }}" style="display:inline-block;padding:12px 20px;background:#0d6efd;color:#ffffff;text-decoration:none;border-radius:8px;font-weight:bold;">Sign in to the portal</a>
        </p>
        <p style="margin:0 0 8px;font-size:14px;line-height:1.6;">If you prefer, you can also go directly to your dashboard after signing in: <a href="{{ $dashboard_url }}" style="color:#0d6efd;text-decoration:none;">Open dashboard</a>.</p>
        <p style="margin:24px 0 0;font-size:13px;color:#6b7280;line-height:1.6;">If you did not expect this message, please contact our support team right away.</p>
    </div>
</div>