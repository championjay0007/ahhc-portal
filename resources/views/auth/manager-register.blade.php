<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manager registration | AHHC Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { color-scheme: light; }
        body { min-height: 100vh; display: grid; place-items: center; padding: 24px; background: #f2f7f6; color: #193735; }
        main { width: min(100%, 480px); padding: 32px; background: #fff; border: 1px solid #d8e5e2; border-radius: 8px; box-shadow: 0 16px 45px rgba(25, 55, 53, .08); }
        .eyebrow { color: #197568; font-size: .78rem; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .form-control { min-height: 44px; }
        .btn-primary { --bs-btn-bg: #176e63; --bs-btn-border-color: #176e63; --bs-btn-hover-bg: #10594f; --bs-btn-hover-border-color: #10594f; min-height: 44px; }
    </style>
</head>
<body>
<main>
    <p class="eyebrow mb-2">AHHC Portal</p>
    <h1 class="h3 mb-2">Manager account</h1>
    <p class="text-secondary mb-4">{{ $invitation->participant->user->name }} invited you to manage their participant account.</p>

    <div class="mb-4">
        <label class="form-label" for="invited-email">Invited email</label>
        <input id="invited-email" class="form-control" type="email" value="{{ $invitation->invited_email }}" readonly>
    </div>

    @if($existingUser)
        @if($existingUser->role === 'manager')
            <p class="text-secondary">A manager account already exists for this email. Sign in with that account, then open this invitation again to accept it.</p>
            <a class="btn btn-primary w-100" href="{{ route('portal.login') }}">Sign in</a>
        @else
            <div class="alert alert-warning" role="alert">This email already belongs to a {{ $existingUser->role }} account. Ask the participant to send the invitation to a different email for your manager account.</div>
        @endif
    @else
        <form method="POST" action="{{ route('portal.participant.accounts.register', ['token' => request()->route('token')]) }}">
            @csrf
            <div class="mb-3">
                <label class="form-label" for="manager-name">Your name</label>
                <input id="manager-name" name="name" class="form-control" value="{{ old('name') }}" autocomplete="name" required maxlength="255">
                @error('name')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="manager-phone">Phone <span class="text-secondary">(optional)</span></label>
                <input id="manager-phone" name="phone" class="form-control" type="tel" value="{{ old('phone') }}" autocomplete="tel" maxlength="50">
                @error('phone')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="manager-password">Password</label>
                <input id="manager-password" name="password" class="form-control" type="password" autocomplete="new-password" required minlength="8">
                @error('password')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
            </div>
            <div class="mb-4">
                <label class="form-label" for="manager-password-confirmation">Confirm password</label>
                <input id="manager-password-confirmation" name="password_confirmation" class="form-control" type="password" autocomplete="new-password" required minlength="8">
            </div>
            <button class="btn btn-primary w-100" type="submit">Create manager account</button>
        </form>
    @endif
</main>
</body>
</html>
