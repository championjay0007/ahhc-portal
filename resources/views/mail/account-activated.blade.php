<div style="font-family: Arial, sans-serif; color: #333;">
    <h2 style="color:#0E3863;">{{ $organization }} — Account activated</h2>

    <p>Hi {{ $name }},</p>

    <p>Your account has been activated by an AHHC administrator. You can now sign in and access your dashboard.</p>

    <p style="margin:1em 0;">
        <a href="{{ $login_url }}" style="display:inline-block;padding:10px 16px;background:#1699A1;color:#fff;border-radius:6px;text-decoration:none;">Sign in to the portal</a>
    </p>

    <p>If you prefer, you can go directly to your dashboard after signing in: <a href="{{ $dashboard_url }}">Open dashboard</a>.</p>

    <p>If you did not expect this, please contact AHHC support immediately.</p>

    <p>Thanks,<br>{{ $organization }} team</p>
</div>