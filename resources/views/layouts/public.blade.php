<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $portalSettings['website_name'] ?? 'Allegiance Heart & Home Care Self-Management Support Portal' }} | {{ $portalSettings['organization_name'] ?? 'Allegiance Heart & Home Care' }}</title>
    <meta name="description" content="{{ $portalSettings['website_description'] ?? 'Discover Allegiance Heart & Home Care Self-Management Support Portal for aged care: greater choice, provider oversight and secure portal tools for invoices, workers and compliance.' }}">
    <meta property="og:title" content="{{ $portalSettings['website_name'] ?? 'Allegiance Heart & Home Care Self-Management Support Portal' }}" />
    <meta property="og:description" content="{{ $portalSettings['website_description'] ?? '' }}" />
    <meta property="og:type" content="business.business" />
    <meta property="og:url" content="{{ url('/') }}" />
    <meta name="theme-color" content="{{ $portalSettings['primary_color'] ?? '#1FC7B7' }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php $favicon = $portalSettings['favicon_path'] ?? null; @endphp
    @if(!empty($favicon))
        <link rel="icon" href="{{ asset('storage/' . $favicon) }}" />
    @else
        <link rel="icon" href="{{ asset('favicon.ico') }}" />
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
                --brand: {{ $portalSettings['primary_color'] ?? '#1FC7B7' }};
                --brand-light: {{ $portalSettings['primary_color'] ?? '#7DD8C4' }};
                --brand-soft: #E0F7F8;
                --accent: {{ $portalSettings['secondary_color'] ?? '#3AA6C9' }};
            --accent-light: #7DD8C4;
            --accent-soft: #E7FBFA;
            --accent-glow: rgba(58, 166, 201, 0.18);
            --warm: #EF2E35;
            --warm-soft: #FDE4E5;
            --success: #10b981;
            --surface: #ffffff;
            --surface-alt: #f8fafc;
            --surface-strong: #f1f5f9;
            --text: #0f172a;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --border-soft: #f1f5f9;
            --shadow-sm: 0 1px 2px rgba(15, 23, 42, 0.04);
            --shadow-md: 0 4px 16px rgba(15, 23, 42, 0.06);
            --shadow-lg: 0 20px 40px rgba(31, 199, 183, 0.12);
            --shadow-xl: 0 30px 60px rgba(31, 199, 183, 0.18);
            --radius-sm: 12px;
            --radius-md: 18px;
            --radius-lg: 24px;
            --radius-xl: 32px;
            --gradient-brand: linear-gradient(135deg, {{ $portalSettings['primary_color'] ?? '#1FC7B7' }} 0%, {{ $portalSettings['secondary_color'] ?? '#3AA6C9' }} 100%);
            --gradient-warm: linear-gradient(135deg, #EF2E35 0%, #FF6B6B 100%);
            --gradient-soft: linear-gradient(135deg, #E0F7F8 0%, #E7FBFA 100%);
        }

        * { box-sizing: border-box; }

        html {
            scroll-behavior: smooth;
            scroll-padding-top: 90px;
        }

        body {
            margin: 0;
            font-family: 'Inter', system-ui, sans-serif;
            background: var(--surface-alt);
            color: var(--text);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
            letter-spacing: -0.02em;
            color: var(--text);
        }

        a { color: var(--brand); text-decoration: none; transition: all 0.2s ease; }
        a:hover { color: var(--accent); }

        .text-brand { color: var(--brand) !important; }
        .text-accent { color: var(--accent) !important; }
        .text-warm { color: var(--warm) !important; }
        .text-muted-custom { color: var(--text-muted) !important; }

        /* ============ NAVBAR ============ */
        .navbar-main {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: saturate(180%) blur(20px);
            -webkit-backdrop-filter: saturate(180%) blur(20px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
            transition: all 0.3s ease;
            z-index: 1030;
        }

        .navbar-main.scrolled {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.06);
        }

        .navbar-brand-custom {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .brand-logo {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: var(--gradient-brand);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.25rem;
            box-shadow: 0 8px 20px rgba(14, 56, 99, 0.25);
        }

        .brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
        }

        .brand-text strong {
            font-size: 1rem;
            font-weight: 800;
            color: var(--text);
            letter-spacing: -0.02em;
        }

        .brand-text small {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .nav-link-custom {
            color: var(--text-secondary) !important;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 0.5rem 1rem !important;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .nav-link-custom:hover {
            color: var(--brand) !important;
            background: var(--brand-soft);
        }

        .btn-nav-primary {
            background: var(--gradient-brand);
            color: white !important;
            border: none;
            padding: 0.6rem 1.25rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            box-shadow: 0 8px 20px rgba(14, 56, 99, 0.25);
            transition: all 0.2s ease;
        }

        .btn-nav-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 24px rgba(14, 56, 99, 0.3);
            color: white !important;
        }

        .btn-nav-outline {
            color: var(--brand) !important;
            border: 1.5px solid var(--border);
            padding: 0.55rem 1.25rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            background: white;
            transition: all 0.2s ease;
        }

        .btn-nav-outline:hover {
            border-color: var(--brand);
            color: var(--brand) !important;
            background: var(--brand-soft);
        }

        /* ============ BUTTONS ============ */
        .btn-primary-custom {
            background: var(--gradient-brand);
            color: white;
            border: none;
            padding: 0.875rem 1.75rem;
            border-radius: 14px;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 10px 24px rgba(14, 56, 99, 0.25);
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px rgba(14, 56, 99, 0.35);
            color: white;
        }

        .btn-secondary-custom {
            background: white;
            color: var(--brand);
            border: 1.5px solid var(--border);
            padding: 0.875rem 1.75rem;
            border-radius: 14px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.25s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-secondary-custom:hover {
            border-color: var(--brand);
            background: var(--brand-soft);
            color: var(--brand);
            transform: translateY(-2px);
        }

        .support-widget-fab {
            position: fixed;
            right: 1.25rem;
            bottom: 1.25rem;
            z-index: 1050;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .support-widget-fab .btn {
            border-radius: 999px;
            box-shadow: 0 12px 30px rgba(14, 56, 99, 0.2);
            min-width: 56px;
            min-height: 56px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .support-widget-card {
            width: min(320px, calc(100vw - 2rem));
            background: white;
            border: 1px solid var(--border);
            border-radius: 20px;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.16);
            padding: 1rem;
            position: fixed;
            right: 1.25rem;
            bottom: 5.5rem;
            z-index: 1049;
        }

        .support-widget-card .btn {
            width: 100%;
        }

        @media (max-width: 576px) {
            .support-widget-card {
                right: 1rem;
                left: 1rem;
                bottom: 5rem;
                width: auto;
            }

            .support-widget-fab {
                right: 1rem;
                bottom: 1rem;
            }
        }

        /* ============ SECTION STYLES ============ */
        .section-padding { padding: 6rem 0; }

        .section-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 0.9rem;
            background: var(--accent-soft);
            color: var(--accent);
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
        }

        .section-title {
            font-size: clamp(2rem, 4vw, 3rem);
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.15;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            color: var(--text-muted);
            font-size: 1.1rem;
            line-height: 1.7;
            max-width: 640px;
        }

        /* ============ CARDS ============ */
        .card-modern {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            background: white;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 2rem;
            transition: all 0.3s ease;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .card-modern:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-xl);
            border-color: transparent;
        }

        .table-responsive {
            border-radius: var(--radius-lg);
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border: 1px solid var(--border-light);
        }

        .table-responsive .table {
            min-width: 100%;
            width: 100%;
        }

        .table-responsive .table th,
        .table-responsive .table td {
            white-space: normal;
        }

        @media (max-width: 991px) {
            .navbar-brand-custom {
                flex-wrap: wrap;
                gap: 0.75rem;
            }

            .brand-text {
                min-width: 0;
            }
        }

        @media (max-width: 767px) {
            .brand-text strong {
                font-size: 1rem;
            }

            .brand-text small {
                display: none;
            }

            .card-modern:hover {
                transform: none;
                box-shadow: var(--shadow-md);
            }
        }

        @media (max-width: 767px) {
            .card-modern:hover {
                transform: none;
                box-shadow: var(--shadow-md);
            }
        }

        .card-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1.25rem;
            background: var(--brand-soft);
            color: var(--brand);
        }

        .card-icon.accent { background: var(--accent-soft); color: var(--accent); }
        .card-icon.warm { background: var(--warm-soft); color: var(--warm); }

        /* ============ FOOTER ============ */
        .footer-modern {
            background: linear-gradient(180deg, #0a2540 0%, #071a30 100%);
            color: #cbd5e1;
            padding: 5rem 0 2rem;
            position: relative;
            overflow: hidden;
        }

        .footer-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        }

        .footer-modern h6 {
            color: white;
            font-weight: 700;
            margin-bottom: 1.25rem;
            font-size: 0.95rem;
            letter-spacing: 0.02em;
        }

        .footer-modern a {
            color: #94a3b8;
            font-size: 0.9rem;
            transition: color 0.2s ease;
        }

        .footer-modern a:hover { color: white; }

        .footer-modern ul { list-style: none; padding: 0; margin: 0; }
        .footer-modern ul li { margin-bottom: 0.75rem; }

        /* ============ ANIMATIONS ============ */
        .fade-up {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.7s ease, transform 0.7s ease;
        }

        .fade-up.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* ============ RESPONSIVE ============ */
        @media (max-width: 991px) {
            .section-padding { padding: 4rem 0; }
        }

        @media (max-width: 767px) {
            .section-padding { padding: 3rem 0; }
            .section-title { font-size: 2rem; }
        }
    </style>
</head>
<body>

<nav class="navbar-main fixed-top">
    <div class="container py-3">
        <div class="d-flex align-items-center justify-content-between gap-3">
            @php $logoPath = $portalSettings['logo_path'] ?? null; @endphp
            <a href="{{ url('/') }}" class="navbar-brand-custom">
                <div class="brand-logo">
                    @if(!empty($logoPath))
                        <img src="{{ asset('storage/' . $logoPath) }}" alt="{{ $portalSettings['website_name'] ?? 'Allegiance Heart & Home Care' }}" style="width:44px;height:44px;border-radius:12px;object-fit:cover;">
                    @else
                        <i class="bi bi-heart-fill"></i>
                    @endif
                </div>
                <div class="brand-text">
                    <strong>{{ $portalSettings['website_name'] ?? 'Allegiance Heart & Home Care' }}</strong>
                    <small>{{ $portalSettings['website_subtitle'] ?? 'Self-Management Support Portal' }}</small>
                </div>
            </a>

            <div class="d-none d-lg-flex align-items-center gap-1">
                <a class="nav-link-custom" href="#home">Home</a>
                <a class="nav-link-custom" href="#support-at-home">Support at Home</a>
                <a class="nav-link-custom" href="#who-its-for">Who It’s For</a>
                <a class="nav-link-custom" href="#how-self-management-works">How Self-Management Works</a>
                <a class="nav-link-custom" href="#contact">Contact Us</a>
            </div>

            <div class="d-flex align-items-center gap-2">
                <a href="#contact" class="btn-nav-outline d-none d-lg-inline-flex">Apply for Self-Management</a>
                <a href="{{ $portalUrl }}" target="_blank" rel="noopener" class="btn-nav-primary">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Portal Login
                </a>
                <button class="btn btn-link d-lg-none text-dark p-0" type="button" data-bs-toggle="collapse" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-expanded="false">
                    <i class="bi bi-list fs-3"></i>
                </button>
            </div>
        </div>

        <div class="collapse mt-3" id="mobileMenu">
            <div class="card border-0 shadow-sm rounded-4 p-3">
                <a class="d-block py-2 px-2 text-secondary text-decoration-none rounded-3" href="#home">Home</a>
                <a class="d-block py-2 px-2 text-secondary text-decoration-none rounded-3" href="#support-at-home">Support at Home</a>
                <a class="d-block py-2 px-2 text-secondary text-decoration-none rounded-3" href="#who-its-for">Who It’s For</a>
                <a class="d-block py-2 px-2 text-secondary text-decoration-none rounded-3" href="#how-self-management-works">How Self-Management Works</a>
                <a class="d-block py-2 px-2 text-secondary text-decoration-none rounded-3" href="#contact">Contact Us</a>
                <hr class="my-2">
                <a class="btn btn-secondary-custom w-100 mb-2" href="#contact">Apply for Self-Management</a>
                <a class="btn btn-primary-custom w-100" href="{{ $portalUrl }}" target="_blank" rel="noopener">Login to Portal</a>
            </div>
        </div>
    </div>
</nav>

<main>
    @yield('content')
</main>

<div id="supportWidgetCard" class="support-widget-card d-none" aria-live="polite">
    <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
        <div>
            <h6 class="mb-1">Need help?</h6>
            <p class="mb-0 small text-muted">Send a message to the Allegiance Heart &amp; Home Care support team.</p>
        </div>
        <button type="button" class="btn btn-sm btn-light rounded-circle" onclick="toggleSupportWidget(false)" aria-label="Close support options">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div id="publicSupportWidgetMessages" class="support-widget-messages mb-2 d-none"></div>
    <form id="publicSupportWidgetForm" class="d-grid gap-2">
        @csrf
        <div id="publicSupportVisitorFields">
            <input type="text" name="name" class="form-control form-control-sm" placeholder="Your name" autocomplete="name">
            <input type="email" name="email" class="form-control form-control-sm" placeholder="Email address" autocomplete="email" required>
        </div>
        <textarea name="message" rows="3" class="form-control form-control-sm" placeholder="How can we help?" required></textarea>
        <button type="submit" class="btn btn-primary-custom btn-sm">
            <i class="bi bi-send me-2"></i><span id="publicSupportSubmitLabel">Send message</span>
        </button>
        <div id="publicSupportWidgetStatus" class="small text-muted"></div>
    </form>
</div>

<div class="support-widget-fab">
    <button type="button" class="btn btn-primary-custom" onclick="toggleSupportWidget()" aria-label="Open support options">
        <i class="bi bi-headset"></i>
    </button>
</div>

<footer class="footer-modern">
    <div class="container">
        <div class="row gy-4">
            <div class="col-lg-4 mb-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="brand-logo">
                        @if(!empty($logoPath))
                            <img src="{{ asset('storage/' . $logoPath) }}" alt="{{ $portalSettings['website_name'] ?? 'Allegiance Heart & Home Care' }}" style="width:44px;height:44px;border-radius:12px;object-fit:cover;">
                        @else
                            <i class="bi bi-heart-fill"></i>
                        @endif
                    </div>
                    <div class="brand-text">
                        <strong style="color: white;">{{ $portalSettings['organization_name'] ?? ($portalSettings['website_name'] ?? 'Allegiance Heart & Home Care') }}</strong>
                        <small style="color: #94a3b8;">{{ $portalSettings['website_subtitle'] ?? 'Self-Management Support' }}</small>
                    </div>
                </div>
                <p style="color: #94a3b8; font-size: 0.95rem; max-width: 360px; line-height: 1.7;">
                    Professional aged care support and secure portal tools for approved participants. Greater choice, provider oversight, and peace of mind.
                </p>
            </div>
            <div class="col-6 col-lg-2">
                <h6>Explore</h6>
                <ul>
                    <li><a href="#home">Home</a></li>
                    <li><a href="#aged-care">Aged Care</a></li>
                    <li><a href="#eligibility">Eligibility</a></li>
                    <li><a href="#how-it-works">How It Works</a></li>
                </ul>
            </div>
            <div class="col-6 col-lg-3">
                <h6>Contact</h6>
                <ul>
                    <li><a href="mailto:{{ $portalSettings['support_email'] ?? 'intake@allegiancehearthomecare.com.au' }}"><i class="bi bi-envelope me-2"></i>{{ $portalSettings['support_email'] ?? 'intake@allegiancehearthomecare.com.au' }}</a></li>
                    <li><a href="tel:+61287309049"><i class="bi bi-telephone me-2"></i>02 8730 9049</a></li>
                    <li><a href="#"><i class="bi bi-geo-alt me-2"></i>Australia</a></li>
                </ul>
            </div>
            <div class="col-lg-3">
                <h6>Access Portal</h6>
                <p style="color: #94a3b8; font-size: 0.9rem;">Secure login for approved participants and workers.</p>
                <a href="{{ $portalUrl }}" target="_blank" rel="noopener" class="btn btn-nav-primary mt-2">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Portal Login
                </a>
            </div>
        </div>
        <hr class="my-4" style="border-color: rgba(255,255,255,0.08);">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
            <p class="mb-0" style="color: #64748b; font-size: 0.85rem;">
                &copy; {{ date('Y') }} Allegiance Heart Home Care. All rights reserved.
            </p>
            <div class="d-flex gap-3">
                @if(! empty($portalSettings['privacy_policy_path']))
                    <a href="{{ asset('storage/' . $portalSettings['privacy_policy_path']) }}" target="_blank" rel="noopener" style="font-size: 0.85rem;">Privacy Policy</a>
                @else
                    <span style="font-size: 0.85rem; color: rgba(255,255,255,0.6);">Privacy Policy</span>
                @endif
                @if(! empty($portalSettings['terms_of_service_path']))
                    <a href="{{ asset('storage/' . $portalSettings['terms_of_service_path']) }}" target="_blank" rel="noopener" style="font-size: 0.85rem;">Terms of Service</a>
                @else
                    <span style="font-size: 0.85rem; color: rgba(255,255,255,0.6);">Terms of Service</span>
                @endif
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script>
    // Navbar scroll effect
    const navbar = document.querySelector('.navbar-main');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 20) navbar.classList.add('scrolled');
        else navbar.classList.remove('scrolled');
    });

    // Smooth scroll with offset
    document.querySelectorAll('a[href^="#"]').forEach(link => {
        link.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                // Close mobile menu
                const menu = document.getElementById('mobileMenu');
                if (menu.classList.contains('show')) {
                    new bootstrap.Collapse(menu).hide();
                }
            }
        });
    });

    // Fade-up animations on scroll
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

    function toggleSupportWidget(show) {
        const card = document.getElementById('supportWidgetCard');
        if (!card) return;

        if (typeof show === 'boolean') {
            card.classList.toggle('d-none', !show);
            return;
        }

        card.classList.toggle('d-none');
    }

    const publicSupportForm = document.getElementById('publicSupportWidgetForm');
    const publicSupportStatus = document.getElementById('publicSupportWidgetStatus');
    const publicSupportMessages = document.getElementById('publicSupportWidgetMessages');
    const publicSupportVisitorFields = document.getElementById('publicSupportVisitorFields');
    const publicSupportSubmitLabel = document.getElementById('publicSupportSubmitLabel');

    let widgetConversationId = sessionStorage.getItem('supportConversationId');
    let widgetConversationToken = sessionStorage.getItem('supportConversationToken');
    let widgetPollInterval = null;

    const isChatActive = () => widgetConversationId && widgetConversationToken;

    const renderWidgetMessages = (messages) => {
        if (!publicSupportMessages) return;

        if (!messages || messages.length === 0) {
            publicSupportMessages.innerHTML = '<div class="text-muted small">No messages yet.</div>';
            publicSupportMessages.classList.remove('d-none');
            return;
        }

        publicSupportMessages.innerHTML = messages.map(msg => `
            <div class="d-flex ${msg.is_admin ? 'justify-content-start' : 'justify-content-end'} mb-2">
                <div class="rounded-3 p-2 ${msg.is_admin ? 'bg-light text-dark' : 'bg-primary text-white'}" style="max-width: 85%;">
                    <div class="small fw-semibold mb-1">${msg.author}</div>
                    <div>${msg.text.replace(/\n/g, '<br>')}</div>
                    <div class="small mt-1 d-flex align-items-center gap-2 ${msg.is_admin ? 'text-muted' : 'text-white-50'}">
                        <span>${msg.created_at}</span>
                        ${!msg.is_admin ? `<span class="fw-semibold">${getWidgetStatusLabel(msg.status)}</span>` : ''}
                    </div>
                </div>
            </div>
        `).join('');
        publicSupportMessages.classList.remove('d-none');
        publicSupportMessages.scrollTop = publicSupportMessages.scrollHeight;
    };

    const getWidgetStatusLabel = (status) => {
        switch (status) {
            case 'seen':
                return 'Seen';
            case 'delivered':
                return 'Delivered';
            default:
                return 'Sent';
        }
    };

    const enableChatMode = () => {
        if (publicSupportVisitorFields) {
            publicSupportVisitorFields.classList.add('d-none');
        }
        if (publicSupportSubmitLabel) {
            publicSupportSubmitLabel.textContent = 'Send message';
        }
        if (publicSupportMessages) {
            publicSupportMessages.classList.remove('d-none');
        }
        if (publicSupportStatus) {
            publicSupportStatus.className = 'small text-muted';
        }
    };

    const startWidgetPoll = () => {
        if (widgetPollInterval) {
            clearInterval(widgetPollInterval);
        }
        widgetPollInterval = setInterval(() => {
            if (!isChatActive()) {
                clearInterval(widgetPollInterval);
                return;
            }
            loadWidgetConversation();
        }, 7000);
    };

    const saveWidgetConversation = (id, token) => {
        widgetConversationId = id;
        widgetConversationToken = token;
        sessionStorage.setItem('supportConversationId', id);
        sessionStorage.setItem('supportConversationToken', token);
        enableChatMode();
        startWidgetPoll();
    };

    const loadWidgetConversation = async () => {
        if (!isChatActive()) {
            return;
        }

        const response = await fetch(`/support/widget/${widgetConversationId}?token=${encodeURIComponent(widgetConversationToken)}`);
        if (!response.ok) {
            return;
        }

        const payload = await response.json().catch(() => null);
        if (!payload || !payload.messages) {
            return;
        }

        renderWidgetMessages(payload.messages);
    };

    const sendWidgetMessage = async (message) => {
        if (!isChatActive()) {
            return null;
        }

        const payload = new FormData();
        payload.append('message', message);

        const response = await fetch(`/support/widget/${widgetConversationId}/message?token=${encodeURIComponent(widgetConversationToken)}`, {
            method: 'POST',
            body: payload,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        });

        return response.ok ? response.json() : null;
    };

    if (publicSupportForm && publicSupportStatus) {
        if (isChatActive()) {
            enableChatMode();
            loadWidgetConversation();
            startWidgetPoll();
        }

        publicSupportForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            const submitButton = publicSupportForm.querySelector('button[type="submit"]');
            const originalText = submitButton ? submitButton.innerHTML : '';

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Sending...';
            }

            publicSupportStatus.textContent = '';
            publicSupportStatus.className = 'small text-muted';

            const messageField = publicSupportForm.querySelector('textarea[name="message"]');
            const messageValue = messageField ? messageField.value.trim() : '';

            try {
                let payload = null;

                if (isChatActive()) {
                    payload = await sendWidgetMessage(messageValue);
                } else {
                    const formData = new FormData(publicSupportForm);
                    const response = await fetch('{{ route('public.support.widget.store') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                    });

                    if (!response.ok) {
                        const errorPayload = await response.json().catch(() => ({}));
                        throw new Error(errorPayload.message || 'Unable to send your message right now.');
                    }

                    payload = await response.json();
                    if (payload?.conversation) {
                        saveWidgetConversation(payload.conversation.id, payload.conversation.token);
                        renderWidgetMessages(payload.conversation.messages);
                    }
                }

                if (!isChatActive()) {
                    throw new Error('Unable to initialize support chat.');
                }

                if (payload && payload?.message) {
                    publicSupportStatus.textContent = payload.message || 'Message sent. A support agent will reply shortly.';
                    publicSupportStatus.className = 'small text-success';
                }

                if (messageField) {
                    messageField.value = '';
                }

                if (isChatActive()) {
                    loadWidgetConversation();
                }
            } catch (error) {
                publicSupportStatus.textContent = error.message || 'Unable to send your message right now.';
                publicSupportStatus.className = 'small text-danger';
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }
            }
        });
    }
</script>
</body>
</html>