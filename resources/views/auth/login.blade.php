<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    @php
        $loginPrimary = $portalSettings['dashboard_primary_color'] ?? $portalSettings['primary_color'] ?? '#3358ff';
        $loginSecondary = $portalSettings['dashboard_secondary_color'] ?? $portalSettings['secondary_color'] ?? '#7d4dff';
        $logoPath = $portalSettings['logo_path'] ?? null;
    @endphp
    <meta name="theme-color" content="{{ $loginPrimary }}">
    <title>{{ $portalSettings['website_name'] ?? 'Allegiance Heart & Home Care Portal' }} | Secure Sign In</title>
    <!-- Google Fonts & Bootstrap Icons + CSS -->
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
            background: #f4f7fe;
            position: relative;
            overflow-x: hidden;
        }

        /* animated gradient mesh background */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 10% 20%, rgba(51, 88, 255, 0.08) 0%, rgba(125, 77, 255, 0.05) 45%, rgba(255, 255, 255, 0) 70%),
                        radial-gradient(circle at 90% 70%, rgba(125, 77, 255, 0.1) 0%, rgba(51, 88, 255, 0.05) 60%);
            z-index: -2;
        }

        /* subtle floating orbs for modern depth */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            z-index: -1;
            opacity: 0.45;
            pointer-events: none;
        }
        .orb-1 {
            width: 60vw;
            height: 60vw;
            background: radial-gradient(circle, {{ $loginPrimary }}20, {{ $loginSecondary }}08);
            top: -20vh;
            right: -10vw;
        }
        .orb-2 {
            width: 50vw;
            height: 50vw;
            background: radial-gradient(circle, {{ $loginSecondary }}20, {{ $loginPrimary }}05);
            bottom: -25vh;
            left: -15vw;
        }

        .auth-wrapper {
            min-height: 100vh;
            padding: 2rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(0px);
        }

        /* main card – ultra modern glassmorphism meets clean white */
        .auth-mastercard {
            border-radius: 2.5rem;
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(0px);
            box-shadow: 0 25px 55px -12px rgba(0, 0, 0, 0.18), 0 1px 2px rgba(0,0,0,0.02);
            transition: transform 0.25s ease, box-shadow 0.3s ease;
            border: 1px solid rgba(255,255,255,0.6);
            overflow: hidden;
        }

        .auth-mastercard:hover {
            box-shadow: 0 35px 65px -18px rgba(51, 88, 255, 0.2);
        }

        /* left side premium gradient */
        .brand-sidebar {
            background: linear-gradient(145deg, {{ $loginPrimary }} 0%, {{ $loginSecondary }} 40%, {{ $loginPrimary }} 100%);
            position: relative;
            z-index: 1;
            overflow: hidden;
        }
        .brand-sidebar::after {
            content: '';
            position: absolute;
            top: -20%;
            left: -20%;
            width: 140%;
            height: 140%;
            background: radial-gradient(circle at 30% 20%, {{ $loginSecondary }}33, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }
        .brand-sidebar > * {
            position: relative;
            z-index: 2;
        }

        .brand-badge {
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(4px);
            padding: 0.4rem 1rem;
            border-radius: 60px;
            font-size: 0.75rem;
            font-weight: 500;
            letter-spacing: 0.3px;
            width: fit-content;
            border: 0.5px solid rgba(255,255,255,0.2);
        }

        .feature-item {
            transition: all 0.2s ease;
        }
        .feature-item:hover {
            transform: translateX(4px);
        }

        /* right panel form styling */
        .form-panel {
            padding: 2.4rem 2rem;
        }

        .form-control, .input-group-text {
            border-radius: 1.1rem;
            border: 1px solid #e2e8f2;
            background-color: #ffffff;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: {{ $loginPrimary }};
            box-shadow: 0 0 0 4px rgba(51, 88, 255, 0.12);
            background-color: #fff;
        }

        .btn-modern {
            background: linear-gradient(105deg, {{ $loginPrimary }} 0%, {{ $loginSecondary }} 100%);
            border: none;
            border-radius: 1.5rem;
            padding: 0.85rem 1.2rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.25s;
            box-shadow: 0 6px 14px rgba(51, 88, 255, 0.25);
        }
        .btn-modern:hover {
            transform: translateY(-2px);
            background: linear-gradient(105deg, {{ $loginSecondary }} 0%, {{ $loginPrimary }} 100%);
            box-shadow: 0 12px 22px rgba(51, 88, 255, 0.32);
        }
        .btn-modern:active {
            transform: translateY(1px);
        }

        .forgot-link, .signup-link {
            color: {{ $loginPrimary }};
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s;
        }
        .forgot-link:hover, .signup-link:hover {
            color: {{ $loginSecondary }};
            text-decoration: underline;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            color: #8a99b4;
            font-size: 0.8rem;
            margin: 1.5rem 0;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid #e9edf2;
        }
        .divider::before {
            margin-right: 1rem;
        }
        .divider::after {
            margin-left: 1rem;
        }

        /* custom checkbox modern */
        .form-check-input {
            width: 1.15rem;
            height: 1.15rem;
            border-radius: 0.3rem;
            border: 1.5px solid #cbd5e1;
            margin-top: 0.1rem;
            transition: all 0.15s;
        }
        .form-check-input:checked {
            background-color: {{ $loginPrimary }};
            border-color: {{ $loginPrimary }};
            box-shadow: 0 0 0 1px rgba(51, 88, 255, 0.2);
        }

        /* alerts */
        .alert-custom {
            border-radius: 1.2rem;
            border: none;
            font-size: 0.85rem;
            padding: 0.9rem 1rem;
        }

        /* avatar logo modern */
        .logo-avatar {
            background: linear-gradient(135deg, {{ $loginPrimary }}, {{ $loginSecondary }});
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 1.8rem;
            margin: 0 auto 0.8rem auto;
            box-shadow: 0 12px 18px -8px rgba(51,88,255,0.3);
            transition: all 0.2s;
        }
        .logo-avatar.logo-avatar-image {
            padding: 0;
            background: transparent;
            box-shadow: none;
        }
        .logo-avatar.logo-avatar-image img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 1.8rem;
        }
        .logo-avatar i {
            font-size: 2.3rem;
            color: white;
        }

        @media (max-width: 992px) {
            .form-panel {
                padding: 2rem 1.6rem;
            }
            .brand-sidebar {
                padding: 2rem 1.8rem !important;
            }
            .auth-wrapper {
                padding: 1.2rem;
            }
        }
        @media (max-width: 576px) {
            .auth-mastercard {
                border-radius: 1.8rem;
            }
            .form-panel {
                padding: 1.8rem 1.2rem;
            }
            .logo-avatar {
                width: 60px;
                height: 60px;
            }
            .logo-avatar i {
                font-size: 1.9rem;
            }
        }

        /* helper classes for animations */
        .fade-in-up {
            animation: fadeUp 0.5s ease-out forwards;
        }
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .card-entrance {
            animation: cardGlow 0.6s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }
        @keyframes cardGlow {
            0% {
                opacity: 0;
                transform: scale(0.98);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }
    </style>
</head>
<body>
<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<div class="auth-wrapper">
    <div class="container p-0">
        <div class="row justify-content-center">
            <div class="col-xxl-10 col-xl-11">
                <div class="auth-mastercard card-entrance">
                    <div class="row g-0">
                        <!-- Left panel: brand and features (modern vivid) -->
                        <div class="col-lg-6 brand-sidebar p-4 p-lg-5 d-flex flex-column justify-content-between">
                            <div>
                                <div class="brand-badge mb-4">
                                    <i class="bi bi-shield-check me-1"></i> SECURE PORTAL · v3
                                </div>
                                <h1 class="display-5 fw-bold text-white mb-3" style="letter-spacing: -0.02em;">Allegiance Heart &amp; Home Care<br>Access.</h1>
                                <p class="text-white-50 mb-4 lh-base">Seamless sign-in experience for care coordinators, participants, and trusted staff.</p>
                                
                                <div class="mt-4 d-flex flex-column gap-3">
                                    <div class="feature-item d-flex gap-3">
                                        <div class="bg-white bg-opacity-10 rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;"><i class="bi bi-fingerprint text-white fs-5"></i></div>
                                        <div><strong class="text-white">Passwordless ready</strong><div class="small text-white-50">Biometric & SSO support</div></div>
                                    </div>
                                    <div class="feature-item d-flex gap-3">
                                        <div class="bg-white bg-opacity-10 rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;"><i class="bi bi-clock-history text-white fs-5"></i></div>
                                        <div><strong class="text-white">Real-time updates</strong><div class="small text-white-50">Live approvals & case tracking</div></div>
                                    </div>
                                    <div class="feature-item d-flex gap-3">
                                        <div class="bg-white bg-opacity-10 rounded-3 p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;"><i class="bi bi-shield-lock-fill text-white fs-5"></i></div>
                                        <div><strong class="text-white">Bank-grade security</strong><div class="small text-white-50">Encrypted & audited sessions</div></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-5 pt-2 border-top border-white border-opacity-10">
                                <div class="d-flex flex-wrap justify-content-between align-items-center">
                                    <div class="text-white-50 small">24/7 dedicated support</div>
                                    <a href="#" class="text-white text-decoration-none small fw-semibold" style="opacity:0.8;"><i class="bi bi-headset me-1"></i> Help center →</a>
                                </div>
                            </div>
                        </div>

                        <!-- Right panel: login form (modern + blade-like structure) -->
                        <div class="col-lg-6 bg-white form-panel d-flex flex-column justify-content-center fade-in-up">
                            <div class="text-center mb-3">
                                @if(!empty($logoPath))
                                    <div class="logo-avatar logo-avatar-image">
                                        <img src="{{ asset('storage/' . $logoPath) }}" alt="{{ $portalSettings['website_name'] ?? 'Logo' }}">
                                    </div>
                                @else
                                    <div class="logo-avatar">
                                        <i class="bi bi-person-badge-fill"></i>
                                    </div>
                                @endif
                                <h3 class="fw-bold mb-1" style="color: #0a1a3a;">Welcome back</h3>
                                <p class="text-muted small">Sign in to access your secure dashboard</p>
                            </div>

                            <!-- Dynamic alert simulation (just like server-driven) – examples for status/errors -->
                            <!-- For better user demonstration, I simulate possibility of status/error messages.
                                 The code fully supports backend integration via Laravel Blade syntax (unchanged). 
                                 Also added custom demo alert structure. -->
                            
                            @if(session('status'))
                                <div class="alert alert-success alert-custom mb-3 d-flex align-items-center gap-2">
                                    <i class="bi bi-check-circle-fill fs-6"></i> {{ session('status') }}
                                </div>
                            @endif

                            @if($errors->any())
                                <div class="alert alert-danger alert-custom mb-3">
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

                            <!-- Actual demo / elegant form  -->
                            <form method="POST" action="{{ route('portal.login.submit') }}" class="mt-2">
                                @csrf

                                <div class="mb-3">
                                    <label for="email" class="form-label fw-semibold small text-secondary">Email address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 rounded-start-3"><i class="bi bi-envelope text-muted"></i></span>
                                        <input type="email" name="email" id="email" class="form-control border-start-0 rounded-end-3" placeholder="name@example.com" value="{{ old('email') }}" required autofocus>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label fw-semibold small text-secondary">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 rounded-start-3"><i class="bi bi-lock text-muted"></i></span>
                                        <input type="password" name="password" id="password" class="form-control border-start-0 rounded-end-3" placeholder="••••••••" required>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                                        <label class="form-check-label small text-secondary" for="remember">Keep me signed in</label>
                                    </div>
                                    <a href="{{ route('portal.password.request') }}" class="forgot-link small fw-medium">Forgot password?</a>
                                </div>

                                <button type="submit" class="btn btn-modern w-100 text-white">
                                    <i class="bi bi-box-arrow-in-right me-2"></i> Sign in
                                </button>

                                <div class="divider">
                                    <span>secure access</span>
                                </div>

                                <div class="text-center">
                                    <p class="mb-0 small text-secondary">New to the portal?
                                        <a href="{{ route('public.home') }}" class="signup-link fw-semibold">Visit the public website →</a>
                                    </p>
                                </div>
                            </form>

                            <!-- extra modern note : demo-only micro interaction -->
                            <div class="mt-4 text-center d-flex justify-content-center gap-3 small text-muted">
                                <span><i class="bi bi-shield-check"></i> Privacy first</span>
                                <span><i class="bi bi-clock"></i> Session timeout</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- subtle footer note -->
                <div class="text-center mt-4 small text-secondary opacity-50">
                    <i class="bi bi-dot"></i> Secure platform · role-based access
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Optional: small script to enhance interactivity (demo smooth input feedback) -->
<script>
    (function() {
        // modern ripple effect for button? just light visual polish
        const btns = document.querySelectorAll('.btn-modern');
        btns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                let ripple = document.createElement('span');
                ripple.classList.add('ripple-effect');
                ripple.style.position = 'absolute';
                ripple.style.borderRadius = '50%';
                ripple.style.backgroundColor = 'rgba(255,255,255,0.4)';
                ripple.style.width = '40px';
                ripple.style.height = '40px';
                ripple.style.transform = 'scale(0)';
                ripple.style.transition = 'transform 0.4s ease-out, opacity 0.5s';
                ripple.style.opacity = '1';
                ripple.style.pointerEvents = 'none';
                const rect = btn.getBoundingClientRect();
                const mouseX = e.clientX - rect.left;
                const mouseY = e.clientY - rect.top;
                ripple.style.left = mouseX - 20 + 'px';
                ripple.style.top = mouseY - 20 + 'px';
                btn.style.position = 'relative';
                btn.style.overflow = 'hidden';
                btn.appendChild(ripple);
                setTimeout(() => {
                    ripple.style.transform = 'scale(8)';
                    ripple.style.opacity = '0';
                }, 10);
                setTimeout(() => {
                    if(ripple && ripple.remove) ripple.remove();
                }, 500);
            });
        });

        // improve floating label effect for inputs?
        const inputs = document.querySelectorAll('.form-control');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement?.classList.add('focused-shadow');
            });
            input.addEventListener('blur', function() {
                this.parentElement?.classList.remove('focused-shadow');
            });
        });
    })();
</script>
<style>
    /* ripple style extra */
    .ripple-effect {
        position: absolute;
        border-radius: 50%;
        background: rgba(255,255,255,0.3);
        transform: scale(0);
        animation: none;
        pointer-events: none;
    }
    .input-group:focus-within .input-group-text {
        border-color: #3358ff;
        transition: border 0.2s;
    }
    .input-group .form-control:focus ~ .input-group-text {
        border-color: #3358ff;
    }
    .input-group-text {
        transition: all 0.2s;
    }
    .form-panel .form-control, .form-panel .input-group-text {
        background-color: #ffffff;
    }
    .btn-modern:focus-visible {
        outline: 2px solid #3358ff;
        outline-offset: 2px;
    }
    ::-ms-reveal {
        filter: invert(0.2);
    }
    /* scrollbar custom */
    ::-webkit-scrollbar {
        width: 6px;
    }
    ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb {
        background: #b9c2db;
        border-radius: 10px;
    }
    body {
        scrollbar-width: thin;
    }
</style>
<!-- Ensure that blade directives remain intact and fully compatible with backend engine -->
</body>
</html>