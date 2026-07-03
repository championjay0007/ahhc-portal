<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    @php
        $siteName = $portalSettings['website_name'] ?? 'AHHC Portal';
        $faviconPath = $portalSettings['favicon_path'] ?? null;
    @endphp
    <title>{{ $siteName }} | Registration</title>
    <link rel="icon" href="{{ ! empty($faviconPath) ? asset('storage/' . $faviconPath) : asset('favicon.ico') }}">
    <!-- Google Fonts + Bootstrap Icons + Bootstrap CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            min-height: 100vh;
            background: #f5f9ff;
            position: relative;
            overflow-x: hidden;
        }

        /* Modern animated gradient background */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 0% 30%, rgba(63, 109, 255, 0.1) 0%, rgba(157, 94, 255, 0.06) 45%, rgba(255,255,255,0) 75%),
                        radial-gradient(circle at 100% 70%, rgba(157, 94, 255, 0.12) 0%, rgba(63, 109, 255, 0.05) 60%);
            z-index: -2;
        }

        /* floating glass orbs */
        .orb-glow {
            position: fixed;
            border-radius: 50%;
            filter: blur(90px);
            z-index: -1;
            pointer-events: none;
            opacity: 0.5;
        }
        .orb-left {
            width: 55vw;
            height: 55vw;
            background: radial-gradient(circle, #3f6dff25, #9d5eff08);
            bottom: -20vh;
            left: -25vw;
        }
        .orb-right {
            width: 50vw;
            height: 50vw;
            background: radial-gradient(circle, #9d5eff20, #3f6dff05);
            top: -15vh;
            right: -20vw;
        }

        .register-wrapper {
            min-height: 100vh;
            padding: 2rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Main card - premium frosted glass + clean modern */
        .register-mastercard {
            border-radius: 2.5rem;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(0px);
            box-shadow: 0 30px 60px -18px rgba(0, 0, 0, 0.2), 0 2px 6px rgba(0,0,0,0.02);
            transition: transform 0.25s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(255,255,255,0.7);
            overflow: hidden;
        }
        .register-mastercard:hover {
            box-shadow: 0 40px 70px -20px rgba(63, 109, 255, 0.25);
        }

        /* Left side: deep modern gradient with pattern */
        .brand-gradient {
            background: linear-gradient(145deg, #182a5e 0%, #0f1a42 40%, #080c26 100%);
            position: relative;
            z-index: 1;
        }
        .brand-gradient::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 30% 20%, rgba(157,94,255,0.2), transparent 70%);
            pointer-events: none;
            z-index: 0;
        }
        .brand-gradient > * {
            position: relative;
            z-index: 2;
        }

        .feature-badge {
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(6px);
            padding: 0.4rem 1rem;
            border-radius: 60px;
            font-size: 0.7rem;
            font-weight: 500;
            letter-spacing: 0.4px;
            width: fit-content;
            border: 0.5px solid rgba(255,255,255,0.2);
        }

        .feature-row {
            transition: all 0.2s ease;
        }
        .feature-row:hover {
            transform: translateX(5px);
        }

        /* Right panel form modern */
        .form-panel-modern {
            padding: 2.2rem 2rem;
        }

        .form-control, .form-select {
            border-radius: 1.1rem;
            border: 1px solid #e4e9f2;
            background: #ffffff;
            padding: 0.7rem 1rem;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #3f6dff;
            box-shadow: 0 0 0 4px rgba(63, 109, 255, 0.12);
            background-color: #fff;
        }
        .input-group-text-custom {
            background: white;
            border-right: none;
            border-radius: 1.1rem 0 0 1.1rem;
            border: 1px solid #e4e9f2;
            border-right: none;
        }
        .input-group .form-control {
            border-left: none;
        }

        .btn-premium {
            background: linear-gradient(105deg, #3f6dff 0%, #7a4eff 100%);
            border: none;
            border-radius: 1.5rem;
            padding: 0.85rem 1.2rem;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.25s;
            box-shadow: 0 8px 18px rgba(63, 109, 255, 0.3);
        }
        .btn-premium:hover {
            transform: translateY(-2px);
            background: linear-gradient(105deg, #2f5af0 0%, #683de0 100%);
            box-shadow: 0 14px 24px rgba(63, 109, 255, 0.35);
        }
        .btn-premium:active {
            transform: translateY(1px);
        }

        .signin-link {
            color: #3f6dff;
            font-weight: 600;
            text-decoration: none;
        }
        .signin-link:hover {
            color: #1d44c9;
            text-decoration: underline;
        }

        .divider-custom {
            display: flex;
            align-items: center;
            text-align: center;
            color: #8a99b4;
            font-size: 0.75rem;
            margin: 1.2rem 0;
        }
        .divider-custom::before, .divider-custom::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #eef2f8;
        }
        .divider-custom::before {
            margin-right: 1rem;
        }
        .divider-custom::after {
            margin-left: 1rem;
        }

        /* modern checkbox */
        .form-check-input {
            width: 1.1rem;
            height: 1.1rem;
            border-radius: 0.25rem;
            border: 1.5px solid #cfddee;
            transition: all 0.15s;
        }
        .form-check-input:checked {
            background-color: #3f6dff;
            border-color: #3f6dff;
            box-shadow: 0 0 0 1px rgba(63,109,255,0.2);
        }

        /* alert modern */
        .alert-soft {
            border-radius: 1.2rem;
            border: none;
            font-size: 0.85rem;
            padding: 0.85rem 1rem;
        }

        /* Logo avatar icon */
        .logo-ring {
            background: linear-gradient(135deg, #3f6dff, #9d5eff);
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 1.6rem;
            margin: 0 auto 1rem auto;
            box-shadow: 0 12px 20px -8px rgba(63,109,255,0.4);
            transition: all 0.2s;
        }
        .logo-ring i {
            font-size: 2.2rem;
            color: white;
        }

        @media (max-width: 992px) {
            .form-panel-modern {
                padding: 1.8rem 1.4rem;
            }
            .brand-gradient {
                padding: 2rem 1.6rem !important;
            }
        }
        @media (max-width: 576px) {
            .register-mastercard {
                border-radius: 1.8rem;
            }
            .logo-ring {
                width: 56px;
                height: 56px;
            }
            .logo-ring i {
                font-size: 1.8rem;
            }
        }

        /* animations */
        .fade-up {
            animation: slideUpFade 0.5s ease-out forwards;
        }
        @keyframes slideUpFade {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .card-scale {
            animation: cardEnter 0.5s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }
        @keyframes cardEnter {
            0% {
                opacity: 0;
                transform: scale(0.97);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
</head>
<body>
<div class="orb-glow orb-left"></div>
<div class="orb-glow orb-right"></div>

<div class="register-wrapper">
    <div class="container p-0">
        <div class="row justify-content-center">
            <div class="col-xxl-10 col-xl-11">
                <div class="register-mastercard card-scale">
                    <div class="row g-0">
                        <!-- Left modern side: value proposition -->
                        <div class="col-lg-6 brand-gradient p-4 p-lg-5 d-flex flex-column justify-content-between">
                            <div>
                                <div class="feature-badge mb-4">
                                    <i class="bi bi-shield-plus me-1"></i> JOIN THE NETWORK
                                </div>
                                <h1 class="display-5 fw-bold text-white mb-3" style="letter-spacing: -0.02em;">Get started<br>with Allegiance Heart &amp; Home Care.</h1>
                                <p class="text-white-50 mb-4 lh-base">Secure portal for participants, caregivers, and administrators — unified experience.</p>
                                
                                <div class="d-flex flex-column gap-3 mt-4">
                                    <div class="feature-row d-flex gap-3">
                                        <div class="bg-white bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;"><i class="bi bi-person-check-fill text-white fs-6"></i></div>
                                        <div><strong class="text-white">Easy onboarding</strong><div class="small text-white-50">Register in under 2 minutes</div></div>
                                    </div>
                                    <div class="feature-row d-flex gap-3">
                                        <div class="bg-white bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;"><i class="bi bi-card-list text-white fs-6"></i></div>
                                        <div><strong class="text-white">Full dashboard access</strong><div class="small text-white-50">View documents, approvals, calendar</div></div>
                                    </div>
                                    <div class="feature-row d-flex gap-3">
                                        <div class="bg-white bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;"><i class="bi bi-lock-fill text-white fs-6"></i></div>
                                        <div><strong class="text-white">HIPAA ready</strong><div class="small text-white-50">End-to-end encrypted environment</div></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-5 pt-3 border-top border-white border-opacity-10">
                                <div class="d-flex flex-wrap align-items-center justify-content-between">
                                    <span class="text-white-50 small"><i class="bi bi-headset"></i> Priority support 24/7</span>
                                    <a href="#" class="text-white text-decoration-none small fw-semibold opacity-75">Contact security →</a>
                                </div>
                            </div>
                        </div>

                        <!-- Right form panel: registration modernized -->
                        <div class="col-lg-6 bg-white form-panel-modern fade-up">
                            <div class="text-center mb-3">
                                <div class="logo-ring">
                                    <i class="bi bi-person-plus-fill"></i>
                                </div>
                                <h3 class="fw-bold mb-1" style="color: #0b1e42;">Create account</h3>
                                <p class="text-muted small">Join the secure portal today</p>
                            </div>

                            <!-- Blade error block (fully preserved) -->
                            @if($errors->any())
                                <div class="alert alert-danger alert-soft mb-3">
                                    <div class="d-flex gap-2 align-items-start">
                                        <i class="bi bi-exclamation-triangle-fill mt-0.5"></i>
                                        <ul class="mb-0 ps-3 small">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('portal.register.submit') }}">
                                @csrf
                                @if(isset($prefill_nomination_id))
                                    <input type="hidden" name="nomination_id" value="{{ $prefill_nomination_id }}">
                                @endif
                                <!-- hidden full name field from first + last (sync via js) -->
                                <input type="hidden" name="name" id="full_name_hidden" value="{{ trim(old('first_name') . ' ' . old('last_name')) }}">

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="first_name" class="form-label fw-semibold small text-secondary">First name</label>
                                            <input type="text" name="first_name" id="first_name" class="form-control" placeholder="John" value="{{ old('first_name', $prefill_first_name ?? '') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="last_name" class="form-label fw-semibold small text-secondary">Last name</label>
                                        <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Doe" value="{{ old('last_name', $prefill_last_name ?? '') }}" required>
                                    </div>
                                </div>

                                <div class="mb-3 mt-3">
                                    <label for="email" class="form-label fw-semibold small text-secondary">Email address</label>
                                    <div class="input-group">
                                        <span class="input-group-text input-group-text-custom bg-white"><i class="bi bi-envelope text-muted"></i></span>
                                        <input type="email" name="email" id="email" class="form-control" placeholder="hello@example.com" value="{{ old('email', $prefill_email ?? '') }}" required {{ isset($prefill_email) ? 'readonly' : '' }}>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="role" class="form-label fw-semibold small text-secondary">Account type</label>
                                    @if(isset($prefill_role) && $prefill_role === 'worker')
                                        <input type="hidden" name="role" value="worker">
                                        <select name="role_disabled" id="role" class="form-select" disabled>
                                            <option selected>🛡️ Worker / Staff</option>
                                        </select>
                                    @else
                                        <select name="role" id="role" class="form-select" required>
                                            <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select your role</option>
                                            <option value="participant" {{ old('role') === 'participant' ? 'selected' : '' }}>👤 Participant</option>
                                            <option value="worker" {{ old('role') === 'worker' ? 'selected' : '' }}>🛡️ Worker / Staff</option>
                                        </select>
                                    @endif
                                    <div class="form-text text-muted small">Role defines your dashboard permissions.</div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="password" class="form-label fw-semibold small text-secondary">Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text input-group-text-custom bg-white"><i class="bi bi-key"></i></span>
                                            <input type="password" name="password" id="password" class="form-control" placeholder="········" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="password_confirmation" class="form-label fw-semibold small text-secondary">Confirm password</label>
                                        <div class="input-group">
                                            <span class="input-group-text input-group-text-custom bg-white"><i class="bi bi-shield-lock"></i></span>
                                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="········" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-check my-4">
                                    <input class="form-check-input" type="checkbox" id="terms" required>
                                    <label class="form-check-label small text-secondary" for="terms">I agree to the <a href="#" class="signin-link fw-medium">Terms of Service</a> and <a href="#" class="signin-link fw-medium">Privacy Policy</a>.</label>
                                </div>

                                <button type="submit" class="btn btn-premium w-100 text-white">
                                    <i class="bi bi-person-plus-fill me-2"></i> Register account
                                </button>

                                <div class="divider-custom">
                                    <span>secure registration</span>
                                </div>

                                <div class="text-center">
                                    <p class="mb-0 small text-secondary">Already have an account? 
                                        <a href="{{ route('portal.login') }}" class="signin-link fw-semibold">Sign in →</a>
                                    </p>
                                </div>
                            </form>

                            <!-- extra micro trust signals -->
                            <div class="mt-4 d-flex justify-content-center gap-3 small text-muted">
                                <span><i class="bi bi-database-check"></i> Encrypted data</span>
                                <span><i class="bi bi-person-bounding-box"></i> Role specific</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-3 small text-secondary opacity-50">
                    <i class="bi bi-dot"></i> Secure portal · trusted by care organizations
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for realtime full name sync + minor modern enhancements -->
<script>
    (function() {
        const firstName = document.getElementById('first_name');
        const lastName = document.getElementById('last_name');
        const fullNameHidden = document.getElementById('full_name_hidden');

        function updateFullName() {
            const firstVal = firstName.value.trim();
            const lastVal = lastName.value.trim();
            const combined = [firstVal, lastVal].filter(Boolean).join(' ').trim();
            if (fullNameHidden) fullNameHidden.value = combined;
        }

        if (firstName && lastName) {
            firstName.addEventListener('input', updateFullName);
            lastName.addEventListener('input', updateFullName);
            updateFullName(); // initial sync
        }

        // Add ripple effect to premium button (micro-interaction)
        const btn = document.querySelector('.btn-premium');
        if (btn) {
            btn.addEventListener('click', function(e) {
                let rippleSpan = document.createElement('span');
                rippleSpan.classList.add('ripple-micro');
                const rect = btn.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                rippleSpan.style.width = rippleSpan.style.height = size + 'px';
                rippleSpan.style.left = x + 'px';
                rippleSpan.style.top = y + 'px';
                rippleSpan.style.position = 'absolute';
                rippleSpan.style.borderRadius = '50%';
                rippleSpan.style.backgroundColor = 'rgba(255,255,255,0.4)';
                rippleSpan.style.transform = 'scale(0)';
                rippleSpan.style.transition = 'transform 0.4s, opacity 0.5s';
                rippleSpan.style.pointerEvents = 'none';
                btn.style.position = 'relative';
                btn.style.overflow = 'hidden';
                btn.appendChild(rippleSpan);
                setTimeout(() => rippleSpan.style.transform = 'scale(2)', 10);
                setTimeout(() => rippleSpan.style.opacity = '0', 150);
                setTimeout(() => rippleSpan.remove(), 500);
            });
        }
        
        // Focus animation for inputs
        const inputs = document.querySelectorAll('.form-control, .form-select');
        inputs.forEach(input => {
            input.addEventListener('focus', () => {
                input.parentElement?.classList.add('focused-group');
            });
            input.addEventListener('blur', () => {
                input.parentElement?.classList.remove('focused-group');
            });
        });
    })();
</script>
<style>
    .ripple-micro {
        position: absolute;
        transform: scale(0);
        border-radius: 50%;
        background: rgba(255,255,255,0.3);
        pointer-events: none;
    }
    .input-group:focus-within .input-group-text-custom {
        border-color: #3f6dff;
        transition: all 0.2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: #3f6dff;
    }
    ::-webkit-scrollbar {
        width: 5px;
    }
    ::-webkit-scrollbar-track {
        background: #eef2fa;
        border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb {
        background: #b7c4e0;
        border-radius: 10px;
    }
    body {
        scrollbar-width: thin;
    }
    .btn-premium:focus-visible {
        outline: 2px solid #3f6dff;
        outline-offset: 2px;
    }
</style>
</body>
</html>