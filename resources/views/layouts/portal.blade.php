<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, maximum-scale=5.0">
    <title>{{ $portalSettings['website_name'] ?? 'Allegiance Heart & Home Care Participant Portal' }} · Care Hub</title>
    <meta name="description" content="{{ $portalSettings['website_description'] ?? 'Participant portal for care, approvals, and documents.' }}">
    <link rel="icon" href="{{ ! empty($portalSettings['favicon_path']) ? asset('storage/' . $portalSettings['favicon_path']) : asset('favicon.ico') }}">
    @php
        $dashboardPrimary = $portalSettings['dashboard_primary_color'] ?? $portalSettings['primary_color'] ?? '#0E3863';
        $dashboardSecondary = $portalSettings['dashboard_secondary_color'] ?? $portalSettings['secondary_color'] ?? '#1699A1';
    @endphp
    <meta name="theme-color" content="{{ $dashboardPrimary }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="current-user-id" content="{{ auth()->id() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="{{ $portalSettings['website_name'] ?? 'Allegiance Heart & Home Care Portal' }}">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="msapplication-TileColor" content="{{ $dashboardPrimary }}">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('icons/apple-touch-icon.png') }}">
    <link rel="mask-icon" href="{{ asset('icons/icon-192.png') }}" color="{{ $dashboardPrimary }}">
    
    <!-- Apple Touch Startup Images -->
    <link rel="apple-touch-startup-image" href="{{ asset('icons/splash-640x1136.png') }}" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)">
    <link rel="apple-touch-startup-image" href="{{ asset('icons/splash-750x1334.png') }}" media="(device-width: 375px) and (device-height: 667px) and (-webkit-device-pixel-ratio: 2)">
    <link rel="apple-touch-startup-image" href="{{ asset('icons/splash-1125x2436.png') }}" media="(device-width: 375px) and (device-height: 812px) and (-webkit-device-pixel-ratio: 3)">
    <link rel="apple-touch-startup-image" href="{{ asset('icons/splash-1242x2688.png') }}" media="(device-width: 414px) and (device-height: 896px) and (-webkit-device-pixel-ratio: 3)">
    
    <!-- External Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    
    <style>
        /* ========================================
           CSS VARIABLES & ROOT CONFIGURATION
           ======================================== */
        :root {
            /* Primary Colors */
            --primary: {{ $dashboardPrimary }};
            --primary-dark: #092B4A;
            --primary-light: #1A4D7F;
            --primary-rgb: 14, 56, 99;
            
            /* Accent Colors */
            --accent: {{ $dashboardSecondary }};
            --accent-dark: #0F7C88;
            --accent-light: #1DB5BE;
            --accent-glow: #2DD4BF;
            --accent-rgb: 22, 153, 161;
            
            /* Background Colors */
            --bg-app: #F4F7FC;
            --bg-surface: #ffffff;
            --bg-elevated: #ffffff;
            --bg-muted: #F8FAFC;
            
            /* Text Colors */
            --text-dark: #0B2B3F;
            --text-primary: #1E293B;
            --text-secondary: #475569;
            --text-muted: #64748B;
            --text-light: #94A3B8;
            
            /* Status Colors */
            --success: #10B981;
            --success-light: #D1FAE5;
            --warning: #F59E0B;
            --warning-light: #FEF3C7;
            --danger: #EF4444;
            --danger-light: #FEE2E2;
            --info: #3B82F6;
            --info-light: #DBEAFE;
            
            /* UI Elements */
            --sidebar-hover: rgba(22, 153, 161, 0.12);
            --sidebar-active: rgba(22, 153, 161, 0.18);
            --border-light: rgba(14, 56, 99, 0.08);
            --border-medium: rgba(14, 56, 99, 0.12);
            --border-strong: rgba(14, 56, 99, 0.18);
            
            /* Shadows */
            --shadow-xs: 0 1px 2px rgba(0, 0, 0, 0.04);
            --shadow-sm: 0 2px 8px rgba(14, 56, 99, 0.06);
            --shadow-md: 0 4px 16px rgba(14, 56, 99, 0.08);
            --shadow-lg: 0 8px 32px rgba(14, 56, 99, 0.12);
            --shadow-xl: 0 16px 48px rgba(14, 56, 99, 0.16);
            --shadow-2xl: 0 24px 64px rgba(14, 56, 99, 0.2);
            --shadow-glow: 0 0 40px rgba(22, 153, 161, 0.15);
            
            /* Card Shadows */
            --card-shadow: 0 4px 20px rgba(14, 56, 99, 0.06), 0 2px 8px rgba(0, 0, 0, 0.04);
            --card-hover-shadow: 0 12px 40px rgba(14, 56, 99, 0.12), 0 4px 16px rgba(0, 0, 0, 0.06);
            
            /* Transitions */
            --transition-fast: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-bounce: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            
            /* Spacing */
            --spacing-xs: 0.25rem;
            --spacing-sm: 0.5rem;
            --spacing-md: 1rem;
            --spacing-lg: 1.5rem;
            --spacing-xl: 2rem;
            --spacing-2xl: 3rem;
            
            /* Border Radius */
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --radius-xl: 20px;
            --radius-2xl: 24px;
            --radius-3xl: 32px;
            --radius-full: 999px;
            
            /* Typography */
            --font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --font-size-xs: 0.75rem;
            --font-size-sm: 0.875rem;
            --font-size-base: 1rem;
            --font-size-lg: 1.125rem;
            --font-size-xl: 1.25rem;
            --font-size-2xl: 1.5rem;
            --font-size-3xl: 1.875rem;
            --font-size-4xl: 2.25rem;
            
            /* Layout */
            --navbar-height: 72px;
            --sidebar-width: 280px;
            --bottom-nav-height: 72px;
        }

        /* ========================================
           RESET & BASE STYLES
           ======================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        *::before,
        *::after {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            -webkit-text-size-adjust: 100%;
        }

        body {
            font-family: var(--font-family);
            background: var(--bg-app);
            min-height: 100vh;
            color: var(--text-dark);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            display: flex;
            flex-direction: column;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Prevent horizontal scroll */
        html, body {
            overflow-x: hidden;
            max-width: 100%;
        }

        /* ========================================
           SCROLLBAR STYLING
           ======================================== */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(14, 56, 99, 0.04);
            border-radius: var(--radius-sm);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(14, 56, 99, 0.2);
            border-radius: var(--radius-sm);
            transition: var(--transition);
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(14, 56, 99, 0.35);
        }

        /* Firefox scrollbar */
        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(14, 56, 99, 0.2) rgba(14, 56, 99, 0.04);
        }

        /* ========================================
           TYPOGRAPHY
           ======================================== */
        h1, h2, h3, h4, h5, h6 {
            font-weight: 700;
            line-height: 1.2;
            color: var(--text-dark);
            margin-bottom: var(--spacing-md);
        }

        h1 { font-size: var(--font-size-4xl); }
        h2 { font-size: var(--font-size-3xl); }
        h3 { font-size: var(--font-size-2xl); }
        h4 { font-size: var(--font-size-xl); }
        h5 { font-size: var(--font-size-lg); }
        h6 { font-size: var(--font-size-base); }

        p {
            margin-bottom: var(--spacing-md);
            color: var(--text-secondary);
        }

        a {
            color: var(--accent);
            text-decoration: none;
            transition: var(--transition);
        }

        a:hover {
            color: var(--accent-dark);
        }

        /* ========================================
           TOP NAVIGATION BAR
           ======================================== */
        .navbar-portal {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 0;
            box-shadow: var(--shadow-lg);
            backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 1030;
            height: var(--navbar-height);
        }

        .navbar-portal .container-fluid {
            height: 100%;
            padding: 0 var(--spacing-lg);
        }

        .navbar-brand {
            font-weight: 800;
            font-size: var(--font-size-xl);
            color: white !important;
            letter-spacing: -0.5px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            transition: var(--transition);
        }

        .navbar-brand:hover {
            transform: translateY(-1px);
            color: white !important;
        }

        .navbar-brand i {
            font-size: var(--font-size-xl);
            background: rgba(255, 255, 255, 0.15);
            padding: 0.5rem;
            border-radius: var(--radius-md);
            backdrop-filter: blur(8px);
        }

        .navbar-brand img {
            height: 32px;
            width: auto;
        }

        /* ========================================
           NAVBAR ACTIONS
           ======================================== */
        .navbar-actions {
            display: flex;
            align-items: center;
            gap: var(--spacing-md);
            flex-wrap: nowrap;
            flex-shrink: 0;
        }

        /* Menu Toggle Button */
        .portal-menu-btn {
            display: none;
            border-radius: var(--radius-md);
            width: 44px;
            height: 44px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            background: rgba(255, 255, 255, 0.1);
            transition: var(--transition);
            align-items: center;
            justify-content: center;
            cursor: pointer;
            backdrop-filter: blur(8px);
        }

        .portal-menu-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: scale(1.05);
        }

        .portal-menu-btn:active {
            transform: scale(0.95);
        }

        /* Notification & Message Toggles */
        .notification-menu {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .notification-toggle {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            padding: 0;
            border-radius: var(--radius-md);
            border: 1px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.1);
            color: white;
            cursor: pointer;
            transition: var(--transition);
            backdrop-filter: blur(8px);
            flex-shrink: 0;
            overflow: visible;
            line-height: 1;
        }

        .notification-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        .notification-toggle:active {
            transform: translateY(0);
        }

        .notification-toggle i {
            font-size: var(--font-size-lg);
            line-height: 1;
        }

        .notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: var(--danger);
            color: white;
            border-radius: var(--radius-full);
            padding: 0.15rem 0.4rem;
            font-size: 0.68rem;
            font-weight: 700;
            min-width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            border: 2px solid rgba(255, 255, 255, 0.95);
            animation: pulse 2s infinite;
            z-index: 2;
            box-shadow: 0 0 0 1px rgba(14, 56, 99, 0.08);
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.8;
                transform: scale(1.1);
            }
        }

        /* User Dropdown */
        .user-dropdown-toggle {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: var(--radius-full);
            color: white;
            text-decoration: none;
            transition: var(--transition);
            backdrop-filter: blur(8px);
        }

        .user-dropdown-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            transform: translateY(-1px);
        }

        .user-dropdown-toggle img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .user-dropdown-toggle span {
            font-weight: 600;
            font-size: var(--font-size-sm);
        }

        /* ========================================
           NOTIFICATION DROPDOWN
           ======================================== */
        .notification-dropdown {
            position: absolute;
            top: calc(100% + 12px);
            right: 0;
            width: min(380px, calc(100vw - 2rem));
            max-height: 480px;
            background: var(--bg-surface);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-2xl);
            border: 1px solid var(--border-light);
            padding: 0;
            display: none;
            z-index: 1060;
            overflow: hidden;
            animation: slideDown 0.25s ease-out;
        }

        .notification-dropdown.show {
            display: block;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .notification-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-light);
            background: var(--bg-muted);
        }

        .notification-header h6 {
            margin: 0;
            font-weight: 700;
            color: var(--text-dark);
            font-size: var(--font-size-base);
        }

        .notification-list {
            max-height: 320px;
            overflow-y: auto;
            padding: 0.75rem;
        }

        .notification-item {
            display: block;
            padding: 1rem 1.25rem;
            border-radius: var(--radius-lg);
            text-decoration: none;
            color: var(--text-dark);
            transition: var(--transition);
            margin-bottom: 0.5rem;
            border: 1px solid transparent;
        }

        .notification-item:hover {
            background: var(--bg-muted);
            border-color: var(--border-light);
            transform: translateX(4px);
        }

        .notification-item.unread {
            background: rgba(22, 153, 161, 0.06);
            border-left: 3px solid var(--accent);
        }

        .notification-title {
            font-weight: 700;
            margin-bottom: 0.35rem;
            font-size: var(--font-size-sm);
            color: var(--text-dark);
        }

        .notification-message {
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
            line-height: 1.5;
            margin-bottom: 0.5rem;
        }

        .notification-meta {
            font-size: var(--font-size-xs);
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .notification-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            background: var(--bg-muted);
        }

        .notification-footer a,
        .notification-footer button {
            border: none;
            background: transparent;
            color: var(--accent);
            text-decoration: none;
            font-weight: 700;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-md);
            transition: var(--transition);
            font-size: var(--font-size-sm);
            cursor: pointer;
        }

        .notification-footer a:hover,
        .notification-footer button:hover {
            background: rgba(22, 153, 161, 0.1);
            color: var(--accent-dark);
        }

        /* ========================================
           SIDEBAR OVERLAY
           ======================================== */
        .portal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
            z-index: 1040;
        }

        .portal-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }

        /* ========================================
           SIDEBAR NAVIGATION
           ======================================== */
        .portal-sidebar {
            background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            min-height: calc(100vh - var(--navbar-height));
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: var(--navbar-height);
            height: calc(100vh - var(--navbar-height));
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 2rem 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.1);
        }

        .sidebar-title {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(255, 255, 255, 0.6);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .sidebar-welcome {
            color: white;
            font-size: var(--font-size-lg);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .portal-menu-close {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            width: 36px;
            height: 36px;
            border-radius: var(--radius-md);
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .portal-menu-close:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(90deg);
        }

        /* Navigation Menu */
        .nav-menu {
            padding: 1.5rem 1rem;
            flex: 1;
            overflow-y: auto;
        }

        .nav-section-title {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: rgba(255, 255, 255, 0.45);
            font-weight: 700;
            padding: 1.5rem 1rem 0.75rem;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-section-title::before {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.875rem 1rem;
            margin-bottom: 0.35rem;
            border-radius: var(--radius-lg);
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            transition: var(--transition);
            font-weight: 500;
            position: relative;
            font-size: var(--font-size-sm);
        }

        .nav-link-custom:hover {
            background: var(--sidebar-hover);
            color: white;
            transform: translateX(6px);
        }

        .nav-link-custom.active {
            background: var(--sidebar-active);
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(22, 153, 161, 0.2);
        }

        .nav-link-custom.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60%;
            background: var(--accent-glow);
            border-radius: 0 var(--radius-full) var(--radius-full) 0;
        }

        .nav-icon {
            font-size: 1.35rem;
            width: 28px;
            text-align: center;
            flex-shrink: 0;
        }

        .nav-link-custom .badge {
            margin-left: auto;
            font-size: 0.7rem;
            padding: 0.25rem 0.6rem;
        }

        /* Sidebar Scrollbar */
        .portal-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .portal-sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .portal-sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .portal-sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.35);
        }

        /* ========================================
           MAIN CONTENT AREA
           ======================================== */
        .main-content {
            background: var(--bg-app);
            height: calc(100vh - var(--navbar-height));
            overflow-y: auto;
        }

        .content-wrapper {
            padding: var(--spacing-xl);
            min-height: auto;
            height: 100%;
            box-sizing: border-box;
        }

        .container-fluid.p-0,
        .row.g-0 {
            min-height: calc(100vh - var(--navbar-height));
            height: calc(100vh - var(--navbar-height));
            overflow: hidden;
        }

        /* ========================================
           ALERTS & NOTIFICATIONS
           ======================================== */
        .alert-custom {
            border-radius: var(--radius-xl);
            border: none;
            box-shadow: var(--shadow-md);
            animation: slideDown 0.3s ease-out;
            padding: 1.25rem 1.5rem;
            margin-bottom: var(--spacing-lg);
        }

        .alert-custom.alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
            color: #065F46;
            border-left: 4px solid var(--success);
        }

        .alert-custom.alert-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.05));
            color: #991B1B;
            border-left: 4px solid var(--danger);
        }

        .alert-custom i {
            font-size: var(--font-size-xl);
        }

        /* ========================================
           PORTAL CARDS
           ======================================== */
        .portal-card {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            background: var(--bg-surface);
            border-radius: var(--radius-2xl);
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-light);
            transition: var(--transition);
            padding: var(--spacing-xl);
        }

        .portal-card:hover {
            box-shadow: var(--card-hover-shadow);
            transform: translateY(-2px);
        }

        /* ========================================
           MOBILE BOTTOM NAVIGATION
           ======================================== */
        .mobile-bottom-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-top: 1px solid var(--border-light);
            padding: 0.5rem 0.75rem;
            z-index: 1050;
            box-shadow: 0 -8px 32px rgba(14, 56, 99, 0.08);
            justify-content: space-around;
            align-items: center;
            height: var(--bottom-nav-height);
        }

        .mobile-bottom-nav a {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
            padding: 0.75rem 0.5rem;
            border-radius: var(--radius-lg);
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.7rem;
            font-weight: 600;
            transition: var(--transition);
            position: relative;
        }

        .mobile-bottom-nav a:hover {
            color: var(--accent);
            background: rgba(22, 153, 161, 0.06);
        }

        .mobile-bottom-nav a.active {
            color: var(--accent);
            background: rgba(22, 153, 161, 0.1);
        }

        .mobile-bottom-nav a.active::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 32px;
            height: 3px;
            background: var(--accent);
            border-radius: 0 0 var(--radius-full) var(--radius-full);
        }

        .mobile-bottom-nav i {
            font-size: 1.4rem;
        }

        .mobile-bottom-nav .badge {
            position: absolute;
            top: 0.5rem;
            right: 1rem;
            font-size: 0.6rem;
            padding: 0.15rem 0.4rem;
        }

        /* ========================================
           PWA INSTALL BANNER
           ======================================== */
        #pwaInstallBanner {
            background: var(--bg-surface);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--border-light);
            max-width: 380px;
            animation: slideUp 0.4s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #pwaInstallBanner .btn {
            border-radius: var(--radius-md);
            font-weight: 600;
            padding: 0.5rem 1.25rem;
        }

        /* ========================================
           RESPONSIVE DESIGN
           ======================================== */
        
        /* Large screens (992px and up) */
        @media (min-width: 992px) {
            .portal-menu-btn {
                display: none !important;
            }

            .portal-sidebar {
                transform: translateX(0) !important;
            }
        }

        /* Tablets and mobile (991px and down) */
        @media (max-width: 991.98px) {
            .portal-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                width: var(--sidebar-width);
                transform: translateX(-100%);
                z-index: 1060;
                will-change: transform;
            }

            .portal-sidebar.show {
                transform: translateX(0);
                box-shadow: 8px 0 40px rgba(0, 0, 0, 0.15);
            }

            .portal-menu-btn {
                display: inline-flex;
            }

            .portal-menu-close {
                display: inline-flex;
            }

            .content-wrapper {
                padding: var(--spacing-lg);
            }
        }

        /* Mobile devices (767px and down) */
        @media (max-width: 767.98px) {
            :root {
                --navbar-height: 64px;
                --spacing-xl: 1.5rem;
                --spacing-lg: 1.25rem;
            }

            .mobile-bottom-nav {
                display: flex;
            }

            body {
                padding-bottom: calc(var(--bottom-nav-height) + 1rem);
            }

            .navbar-brand {
                font-size: var(--font-size-lg);
            }

            .navbar-brand span {
                display: none;
            }

            .user-dropdown-toggle span {
                display: none;
            }

            .user-dropdown-toggle {
                padding: 0.4rem;
            }

            .content-wrapper {
                padding: var(--spacing-md);
            }

            .portal-card {
                padding: var(--spacing-lg);
                border-radius: var(--radius-xl);
            }

            /* Notification dropdown on mobile */
            .notification-dropdown {
                position: fixed;
                top: auto;
                bottom: calc(var(--bottom-nav-height) + 1rem);
                left: 1rem;
                right: 1rem;
                width: auto;
                max-width: none;
            }
        }

        /* Small mobile devices (575px and down) */
        @media (max-width: 575.98px) {
            :root {
                --spacing-xl: 1.25rem;
                --spacing-lg: 1rem;
                --spacing-md: 0.875rem;
            }

            .navbar-portal .container-fluid {
                padding: 0 var(--spacing-md);
            }

            .navbar-actions {
                gap: var(--spacing-sm);
            }

            .notification-menu {
                margin: 0;
            }

            .notification-toggle {
                width: 42px;
                height: 42px;
            }

            .notification-badge {
                top: -1px;
                right: -1px;
                min-width: 18px;
                height: 18px;
                font-size: 0.64rem;
                padding: 0 0.3rem;
            }

            h1 { font-size: var(--font-size-3xl); }
            h2 { font-size: var(--font-size-2xl); }
            h3 { font-size: var(--font-size-xl); }

            .portal-card {
                padding: var(--spacing-md);
            }

            .portal-card:hover {
                transform: none;
                box-shadow: var(--card-shadow);
            }
        }

        /* Extra small devices (374px and down) */
        @media (max-width: 374.98px) {
            :root {
                --spacing-xl: 1rem;
                --spacing-lg: 0.875rem;
                --spacing-md: 0.75rem;
            }

            .navbar-brand {
                font-size: var(--font-size-base);
            }

            .notification-toggle {
                width: 38px;
                height: 38px;
            }

            .portal-card {
                padding: var(--spacing-md);
                border-radius: var(--radius-lg);
            }

            h1 { font-size: var(--font-size-2xl); }
            h2 { font-size: var(--font-size-xl); }
        }

        /* Landscape mode */
        @media (max-height: 500px) and (orientation: landscape) {
            .portal-sidebar {
                padding-top: 1rem;
            }

            .sidebar-header {
                padding: 1rem;
            }

            .nav-menu {
                padding: 1rem 0.75rem;
            }
        }

        /* Touch devices */
        @media (hover: none) and (pointer: coarse) {
            .nav-link-custom,
            .notification-toggle,
            .portal-menu-btn,
            .mobile-bottom-nav a {
                min-height: 44px;
                min-width: 44px;
            }

            .portal-card:hover {
                transform: none;
            }
        }

        /* ========================================
           UTILITY CLASSES
           ======================================== */
        .text-gradient {
            background: linear-gradient(135deg, var(--accent), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        }

        .bg-gradient-accent {
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
        }

        .shadow-glow {
            box-shadow: var(--shadow-glow);
        }

        /* Loading states */
        .skeleton {
            background: linear-gradient(90deg, var(--bg-muted) 25%, var(--bg-app) 50%, var(--bg-muted) 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            border-radius: var(--radius-md);
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        /* Focus states for accessibility */
        *:focus-visible {
            outline: 2px solid var(--accent);
            outline-offset: 2px;
            border-radius: var(--radius-sm);
        }

        /* Smooth transitions for all interactive elements */
        button, a, input, select, textarea {
            transition: var(--transition);
        }

        /* Prevent text selection on interactive elements */
        .nav-link-custom,
        .mobile-bottom-nav a,
        .notification-toggle,
        .portal-menu-btn {
            user-select: none;
            -webkit-user-select: none;
        }

        /* ========================================
           LOGIN NOTIFICATION MODAL STYLES
           ======================================== */
        #loginNotificationModal .modal-content {
            background: linear-gradient(135deg, var(--bg-app) 0%, var(--bg-muted) 100%);
            border-radius: var(--radius-lg);
        }

        #loginNotificationModal .modal-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        #loginNotificationModal .modal-title {
            font-weight: 600;
            font-size: 1.1rem;
        }

        #loginNotificationModal .btn-close {
            filter: brightness(0) invert(1);
        }

        #loginNotificationModal .alert {
            border: none;
            border-radius: var(--radius-md);
            font-size: 0.95rem;
        }

        #loginNotificationModal .alert-success {
            background-color: rgba(25, 135, 84, 0.1);
            color: var(--success);
        }

        #loginNotificationModal .alert-info {
            background-color: rgba(13, 110, 253, 0.1);
            color: var(--info);
        }

        #loginNotificationModal .modal-footer {
            background: var(--bg-muted);
        }

        #notificationContent > div {
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <!-- PWA Install Banner -->
    <div id="pwaInstallBanner" class="alert alert-info p-3 position-fixed bottom-0 end-0 m-3 shadow d-none" style="z-index:1080;">
        <div class="d-flex align-items-start gap-2">
            <i class="bi bi-cloud-arrow-down-fill fs-3 text-primary"></i>
            <div class="flex-grow-1">
                <strong class="d-block mb-1">Install Allegiance Heart &amp; Home Care Portal</strong>
                <div class="small text-muted">Add to home screen for faster access</div>
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button id="pwaInstallButton" class="btn btn-sm btn-primary flex-grow-1">Install</button>
            <button id="pwaInstallDismiss" class="btn btn-sm btn-outline-secondary">Dismiss</button>
        </div>
    </div>

    <!-- Top Navigation Bar -->
    <nav class="navbar-portal">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center w-100 h-100">
                <!-- Left: Menu Toggle & Brand -->
                <div class="d-flex align-items-center gap-3">
                    <button class="portal-menu-btn" id="portalMenuToggle" aria-label="Toggle menu">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    <a class="navbar-brand" href="{{ route('portal.dashboard') }}">
                        @if(!empty($portalSettings['logo_path']))
                            <img src="{{ asset('storage/' . $portalSettings['logo_path']) }}" alt="{{ $portalSettings['website_name'] ?? 'Portal' }} logo">
                        @else
                            <i class="bi bi-heart-pulse-fill"></i>
                        @endif
                        <span class="d-none d-sm-inline">{{ $portalSettings['website_name'] ?? 'Allegiance Heart & Home Care Portal' }}</span>
                    </a>
                </div>

                <!-- Right: Actions -->
                <div class="navbar-actions">
                    <!-- Notifications -->
                    <div class="notification-menu">
                        <button class="notification-toggle" id="portalNotificationToggle" aria-label="Notifications" aria-expanded="false" type="button">
                            <i class="bi bi-bell-fill"></i>
                            @if(isset($unreadNotificationCount) && $unreadNotificationCount > 0)
                                <span class="notification-badge">{{ $unreadNotificationCount }}</span>
                            @endif
                        </button>
                        <div class="notification-dropdown" id="portalNotificationDropdown" aria-hidden="true">
                            <div class="notification-header">
                                <h6>Notifications</h6>
                            </div>
                            <div class="notification-list">
                                @forelse($portalNotifications ?? [] as $notification)
                                    @php
                                        $data = $notification->data ?? [];
                                        $title = $data['title'] ?? ucfirst($notification->type ?? 'Notification');
                                        $message = $data['message'] ?? 'View details for this update.';
                                        
                                        // Determine the action URL based on notification type
                                        if (isset($data['url']) && !empty($data['url'])) {
                                            $url = $data['url'];
                                        } elseif ($notification->type === 'App\\Notifications\\IncidentReported' && isset($data['incident_id'])) {
                                            $url = route('portal.admin.incidents.show', $data['incident_id']);
                                        } elseif ($notification->type === 'App\\Notifications\\CareNoteSubmitted' && isset($data['care_note_id'])) {
                                            $url = route('portal.admin.care_notes.show', $data['care_note_id']);
                                        } else {
                                            $url = route('portal.notifications.show', $notification);
                                        }
                                        
                                        $isUnread = $notification->read_at === null;
                                    @endphp
                                    <a href="{{ $url }}" class="notification-item {{ $isUnread ? 'unread' : '' }}">
                                        <div class="notification-title">{{ $title }}</div>
                                        <div class="notification-message">{{ $message }}</div>
                                        <div class="notification-meta">
                                            <i class="bi bi-clock"></i>
                                            {{ $notification->created_at->diffForHumans() }}
                                        </div>
                                    </a>
                                @empty
                                    <div class="notification-item text-center text-muted py-4">
                                        <i class="bi bi-bell-slash fs-1 d-block mb-2"></i>
                                        <div>No notifications yet</div>
                                    </div>
                                @endforelse
                            </div>
                            <div class="notification-footer">
                                <a href="{{ route('portal.notifications') }}">
                                    <i class="bi bi-list-ul me-1"></i>View all
                                </a>
                                <button type="button" class="btn btn-link p-0 text-danger clear-dropdown-btn" data-clear-url="{{ route('portal.notifications.mark_all_read') }}">
                                    <i class="bi bi-trash me-1"></i>Clear
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div class="notification-menu">
                        <button class="notification-toggle" id="portalMessageToggle" aria-label="Messages" aria-expanded="false" type="button">
                            <i class="bi bi-envelope-fill"></i>
                            @if(isset($unreadMessageCount) && $unreadMessageCount > 0)
                                <span class="notification-badge">{{ $unreadMessageCount }}</span>
                            @endif
                        </button>
                        <div class="notification-dropdown" id="portalMessageDropdown" aria-hidden="true">
                            <div class="notification-header">
                                <h6>Messages</h6>
                            </div>
                            <div class="notification-list">
                                @forelse($portalMessages ?? [] as $message)
                                    <a href="{{ route($messageRoutePrefix.'show', $message) }}" class="notification-item {{ $message->read_at === null ? 'unread' : '' }}">
                                        <div class="notification-title">{{ $message->sender->name ?? 'Unknown' }}</div>
                                        <div class="notification-message">{{ Str::limit($message->body ?? $message->message, 60) }}</div>
                                        <div class="notification-meta">
                                            <i class="bi bi-clock"></i>
                                            {{ $message->created_at->diffForHumans() }}
                                        </div>
                                    </a>
                                @empty
                                    <div class="notification-item text-center text-muted py-4">
                                        <i class="bi bi-envelope fs-1 d-block mb-2"></i>
                                        <div>No messages yet</div>
                                    </div>
                                @endforelse
                            </div>
                            <div class="notification-footer">
                                <a href="{{ route($messageRoutePrefix.'inbox') }}">
                                    <i class="bi bi-inbox me-1"></i>View all messages
                                </a>
                                <button type="button" class="btn btn-link p-0 text-danger clear-dropdown-btn" data-clear-url="{{ route($messageRoutePrefix.'mark_all_read') }}">
                                    <i class="bi bi-trash me-1"></i>Clear
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <a class="user-dropdown-toggle" href="#" id="portalUserDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <img src="{{ auth()->user()->profile_photo_url }}" alt="avatar">
                            <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                            <i class="bi bi-chevron-down d-none d-md-inline small"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" aria-labelledby="portalUserDropdown" style="border-radius: var(--radius-lg); min-width: 200px;">
                            <li>
                                <a class="dropdown-item py-2 px-3" href="{{ route('portal.profile') }}">
                                    <i class="bi bi-person me-2"></i>Profile
                                </a>
                            </li>
                            @if(Route::has('portal.settings'))
                                <li>
                                    <a class="dropdown-item py-2 px-3" href="{{ route('portal.settings') }}">
                                        <i class="bi bi-gear me-2"></i>Settings
                                    </a>
                                </li>
                            @endif
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <form method="POST" action="{{ route('portal.logout') }}" class="m-0">
                                    @csrf
                                    <button type="submit" class="dropdown-item py-2 px-3 text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar Overlay -->
    <div class="portal-overlay" id="portalOverlay"></div>

    <!-- Notifications / Status Modal (shown on login or when there are unread notifications) -->
    <div class="modal fade" id="portalNotificationsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="portalNotificationsModalLabel">Notifications</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="portalNotificationSummaryContent">
                        @if(session('status') || session('success'))
                            <p class="mb-0">{{ session('status') ?? session('success') }}</p>
                        @else
                            <p class="mb-0">You have new updates.</p>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="{{ route('portal.notifications') }}" class="btn btn-primary">View notifications</a>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Layout Container -->
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <aside class="col-lg-3 col-xl-2 portal-sidebar" id="portalSidebar">
                <div class="sidebar-header d-flex justify-content-between align-items-start">
                    <div>
                        <div class="sidebar-title">
                            @if(auth()->user()->role === 'worker')
                                Worker Portal
                            @elseif(auth()->user()->role === 'admin')
                                Admin Portal
                            @else
                                Participant Portal
                            @endif
                        </div>
                        <div class="sidebar-welcome">
                            <i class="bi bi-hand-thumbs-up-fill"></i>
                            Hi, {{ auth()->user()->name }}
                        </div>
                    </div>
                    <button class="portal-menu-close" id="portalSidebarClose" aria-label="Close sidebar">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <div class="nav-menu">
                    @if(auth()->user()->role === 'worker')
                        <!-- Worker Navigation -->
                        <a href="{{ route('portal.worker.dashboard') }}" 
                           class="nav-link-custom {{ request()->routeIs('portal.worker.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-house-fill nav-icon"></i>
                            <span>My Dashboard</span>
                        </a>

                        <div class="nav-section-title">👥 Participants</div>
                        <a href="{{ route('portal.worker.assigned_participants') }}" 
                           class="nav-link-custom {{ request()->routeIs('portal.worker.assigned_participants') ? 'active' : '' }}">
                            <i class="bi bi-people-fill nav-icon"></i>
                            <span>Assigned Participants</span>
                        </a>
                        @php
                            $assignedChatParticipant = optional(optional(auth()->user()->worker)->assignments()->where('status', 'active')->with('participant')->first())->participant;
                        @endphp
                        <a href="{{ $assignedChatParticipant ? route($messageRoutePrefix.'conversation', $assignedChatParticipant->user_id) : route('portal.worker.assigned_participants') }}" 
                           class="nav-link-custom {{ request()->routeIs($messageRoutePrefix.'conversation') ? 'active' : '' }}">
                            <i class="bi bi-chat-right-text nav-icon"></i>
                            <span>Chat Participant</span>
                        </a>
                        <a href="{{ route('portal.worker.shifts') }}" 
                           class="nav-link-custom {{ request()->routeIs('portal.worker.shifts') ? 'active' : '' }}">
                            <i class="bi bi-calendar-check-fill nav-icon"></i>
                            <span>My Shifts</span>
                        </a>

                        <div class="nav-section-title">📝 Care & Risk</div>
                        <a href="{{ route('portal.worker.care_notes.create') }}" 
                           class="nav-link-custom {{ request()->routeIs('portal.worker.care_notes.*') ? 'active' : '' }}">
                            <i class="bi bi-journal-text nav-icon"></i>
                            <span>Submit Care Note</span>
                        </a>
                        <a href="{{ route('portal.worker.incidents.create') }}" 
                           class="nav-link-custom {{ request()->routeIs('portal.worker.incidents.*') ? 'active' : '' }}">
                            <i class="bi bi-exclamation-octagon-fill nav-icon"></i>
                            <span>Incident / Risk Forms</span>
                        </a>

                        <div class="nav-section-title">💬 Messaging</div>
                        <a href="{{ route($messageRoutePrefix.'inbox') }}" 
                           class="nav-link-custom {{ request()->routeIs($messageRoutePrefix.'*') ? 'active' : '' }}">
                            <i class="bi bi-chat-left-dots-fill nav-icon"></i>
                            <span>Inbox</span>
                            @if(isset($unreadMessageCount) && $unreadMessageCount > 0)
                                <span class="badge bg-danger rounded-pill">{{ $unreadMessageCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('portal.support.conversations.index') }}" 
                           onclick="return openPortalSupportChat(event, this.href)"
                           class="nav-link-custom {{ request()->routeIs('portal.support.conversations.*') ? 'active' : '' }}">
                            <i class="bi bi-chat-dots nav-icon"></i>
                            <span>Live Chat</span>
                        </a>

                        <div class="nav-section-title">📁 Documents</div>
                        <a href="{{ route('portal.worker.documents.upload') }}" 
                           class="nav-link-custom {{ request()->routeIs('portal.worker.documents.*') ? 'active' : '' }}">
                            <i class="bi bi-cloud-upload-fill nav-icon"></i>
                            <span>Upload Documents</span>
                        </a>
                        <a href="{{ route('portal.worker.invoices') }}" 
                           class="nav-link-custom {{ request()->routeIs('portal.worker.invoices*') ? 'active' : '' }}">
                            <i class="bi bi-receipt nav-icon"></i>
                            <span>Invoice Submission</span>
                        </a>
                        <a href="{{ route('portal.gallery') }}"
                           class="nav-link-custom {{ request()->routeIs('portal.gallery*') ? 'active' : '' }}">
                            <i class="bi bi-images nav-icon"></i>
                            <span>Shared Gallery</span>
                        </a>
                        <a href="{{ route('portal.worker.forms') }}" 
                           class="nav-link-custom {{ request()->routeIs('portal.worker.forms') ? 'active' : '' }}">
                            <i class="bi bi-pencil-square nav-icon"></i>
                            <span>Forms to Sign</span>
                        </a>

                        <div class="nav-section-title">⚙️ Account</div>
                        <a href="{{ route('portal.worker.profile') }}" 
                           class="nav-link-custom {{ request()->routeIs('portal.worker.profile') ? 'active' : '' }}">
                            <i class="bi bi-person-fill nav-icon"></i>
                            <span>My Profile</span>
                        </a>
                    @else
                        <!-- Participant Navigation -->
                        <a href="{{ route('portal.dashboard') }}" 
                           class="nav-link-custom {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-house-fill nav-icon"></i>
                            <span>Dashboard</span>
                        </a>

                        <div class="nav-section-title">💰 Budget & Finance</div>
                        <a href="{{ route('portal.participant.budget') }}" 
                           class="nav-link-custom {{ request()->routeIs('portal.participant.budget') ? 'active' : '' }}">
                            <i class="bi bi-wallet2 nav-icon"></i>
                            <span>My Budget</span>
                        </a>
                        <a href="{{ route('portal.participant.pre_approvals.index') }}" 
                           class="nav-link-custom {{ request()->routeIs('portal.participant.pre_approvals.*') ? 'active' : '' }}">
                            <i class="bi bi-check2-circle-fill nav-icon"></i>
                            <span>Pre-Approvals</span>
                        </a>
                        <a href="{{ route('portal.participant.invoices.index') }}" 
                           class="nav-link-custom {{ request()->routeIs('portal.participant.invoices.*') ? 'active' : '' }}">
                            <i class="bi bi-receipt-fill nav-icon"></i>
                            <span>Submit Invoice</span>
                        </a>

                        <div class="nav-section-title">📁 Documents & Forms</div>
                        <a href="{{ route('portal.participant.documents.index') }}" 
                           class="nav-link-custom {{ request()->routeIs('portal.participant.documents.*') && !request()->routeIs('portal.participant.documents.pending') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-text-fill nav-icon"></i>
                            <span>Documents</span>
                        </a>
                        <a href="{{ route('portal.gallery') }}"
                           class="nav-link-custom {{ request()->routeIs('portal.gallery*') ? 'active' : '' }}">
                            <i class="bi bi-images nav-icon"></i>
                            <span>Shared Gallery</span>
                        </a>
                        <a href="{{ route('portal.participant.documents.pending') }}" 
                           class="nav-link-custom {{ request()->routeIs('portal.participant.documents.pending') ? 'active' : '' }}">
                            <i class="bi bi-pen-fill nav-icon"></i>
                            <span>Forms to Sign</span>
                        </a>

                        <div class="nav-section-title">👥 People & Services</div>
                        <a href="{{ route('portal.participant.team') }}" 
                           class="nav-link-custom {{ request()->routeIs('portal.participant.team') ? 'active' : '' }}">
                            <i class="bi bi-people-fill nav-icon"></i>
                            <span>Workers / Suppliers</span>
                        </a>
                        <a href="{{ route('portal.participant.services') }}" 
                           class="nav-link-custom {{ request()->routeIs('portal.participant.services') ? 'active' : '' }}">
                            <i class="bi bi-calendar-check-fill nav-icon"></i>
                            <span>My Services / Shifts</span>
                        </a>
                        <a href="{{ route('portal.participant.care_notes.index') }}" 
                           class="nav-link-custom {{ request()->routeIs('portal.participant.care_notes.*') ? 'active' : '' }}">
                            <i class="bi bi-clipboard-check nav-icon"></i>
                            <span>Care Notes</span>
                        </a>

                        <div class="nav-section-title">⚠️ Support & Reporting</div>
                        <a href="{{ route('portal.participant.complaints.create') }}" 
                           class="nav-link-custom {{ request()->routeIs('portal.participant.complaints.*') ? 'active' : '' }}">
                            <i class="bi bi-exclamation-triangle-fill nav-icon"></i>
                            <span>Incidents / Feedback</span>
                        </a>
                        <a href="{{ route('portal.support.index') }}" 
                           onclick="return openPortalSupportChat(event, this.href)"
                           class="nav-link-custom {{ request()->routeIs('portal.support.*') ? 'active' : '' }}">
                            <i class="bi bi-headset nav-icon"></i>
                            <span>Contact Support</span>
                        </a>

                        <div class="nav-section-title">💬 Messaging</div>
                        <a href="{{ route($messageRoutePrefix.'inbox') }}" 
                           class="nav-link-custom {{ request()->routeIs($messageRoutePrefix.'*') ? 'active' : '' }}">
                            <i class="bi bi-chat-left-dots-fill nav-icon"></i>
                            <span>Inbox</span>
                            @if(isset($unreadMessageCount) && $unreadMessageCount > 0)
                                <span class="badge bg-danger rounded-pill">{{ $unreadMessageCount }}</span>
                            @endif
                        </a>

                        <div class="nav-section-title">⚙️ Account</div>
                        <a href="{{ route('portal.profile') }}" 
                           class="nav-link-custom {{ request()->routeIs('portal.profile') ? 'active' : '' }}">
                            <i class="bi bi-person-fill nav-icon"></i>
                            <span>My Profile</span>
                        </a>
                    @endif
                </div>
            </aside>

            <!-- Main Content -->
            <main class="col-lg-9 col-xl-10 main-content">
                <div class="content-wrapper">
                    <!-- Success Alert -->
                    @if(session('status'))
                        <div class="alert alert-success alert-custom d-flex align-items-center" role="alert">
                            <i class="bi bi-check-circle-fill me-3"></i>
                            <div class="flex-grow-1">{{ session('status') }}</div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    <!-- Error Alert -->
                    @if($errors->any())
                        <div class="alert alert-danger alert-custom" role="alert">
                            <div class="d-flex align-items-center mb-2">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>Please fix the following errors:</strong>
                            </div>
                            <ul class="mb-0 ps-4">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Page Content -->
                    <div class="portal-card">
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Mobile Bottom Navigation -->
    <div class="mobile-bottom-nav">
        @if(auth()->user()->role === 'worker')
            <a href="{{ route('portal.worker.dashboard') }}" class="{{ request()->routeIs('portal.worker.dashboard') ? 'active' : '' }}">
                <i class="bi bi-house-door-fill"></i>
                <span>Home</span>
            </a>
            <a href="{{ route('portal.worker.assigned_participants') }}" class="{{ request()->routeIs('portal.worker.assigned_participants') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i>
                <span>Team</span>
            </a>
            <a href="{{ route($messageRoutePrefix.'inbox') }}" class="{{ request()->routeIs($messageRoutePrefix.'*') ? 'active' : '' }}">
                <i class="bi bi-chat-left-dots-fill"></i>
                <span>Messages</span>
                @if(isset($unreadMessageCount) && $unreadMessageCount > 0)
                    <span class="badge bg-danger rounded-pill">{{ $unreadMessageCount }}</span>
                @endif
            </a>
            <a href="{{ route('portal.worker.profile') }}" class="{{ request()->routeIs('portal.worker.profile') ? 'active' : '' }}">
                <i class="bi bi-person-circle"></i>
                <span>Profile</span>
            </a>
            <a href="{{ route('portal.gallery') }}" class="{{ request()->routeIs('portal.gallery*') ? 'active' : '' }}">
                <i class="bi bi-images"></i>
                <span>Gallery</span>
            </a>
        @else
            <a href="{{ route('portal.dashboard') }}" class="{{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
                <i class="bi bi-house-door-fill"></i>
                <span>Home</span>
            </a>
            <a href="{{ route('portal.participant.services') }}" class="{{ request()->routeIs('portal.participant.services') ? 'active' : '' }}">
                <i class="bi bi-calendar-check-fill"></i>
                <span>Services</span>
            </a>
            <a href="{{ route($messageRoutePrefix.'inbox') }}" class="{{ request()->routeIs($messageRoutePrefix.'*') ? 'active' : '' }}">
                <i class="bi bi-chat-left-dots-fill"></i>
                <span>Messages</span>
                @if(isset($unreadMessageCount) && $unreadMessageCount > 0)
                    <span class="badge bg-danger rounded-pill">{{ $unreadMessageCount }}</span>
                @endif
            </a>
            <a href="{{ route('portal.support.index') }}" onclick="return openPortalSupportChat(event, this.href)">
                <i class="bi bi-headset"></i>
                <span>Support</span>
            </a>
            <a href="{{ route('portal.profile') }}" class="{{ request()->routeIs('portal.profile') ? 'active' : '' }}">
                <i class="bi bi-person-circle"></i>
                <span>Profile</span>
            </a>
            <a href="{{ route('portal.gallery') }}" class="{{ request()->routeIs('portal.gallery*') ? 'active' : '' }}">
                <i class="bi bi-images"></i>
                <span>Gallery</span>
            </a>
        @endif
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // ========================================
        // SIDEBAR TOGGLE FUNCTIONALITY
        // ========================================
        document.addEventListener('DOMContentLoaded', function() {
            // ========================================
            // DASHBOARD NOTIFICATION SUMMARY MODAL
            // ========================================
            const isDashboardRoute = {{ request()->routeIs('portal.dashboard') ? 'true' : 'false' }};
            const showDashboardNotifications = {{ session('show_dashboard_notifications_modal') ? 'true' : 'false' }};
            const notificationsData = {!! json_encode([
                'notifications' => $portalNotifications->take(5)->map(function ($notification) {
                    return [
                        'title' => $notification->data['title'] ?? ucfirst($notification->type ?? 'Notification'),
                        'message' => $notification->data['message'] ?? '',
                        'read_at' => $notification->read_at,
                    ];
                })->values(),
                'unreadCount' => isset($unreadNotificationCount) ? $unreadNotificationCount : 0,
                'statusMessage' => session('status') ?? session('success') ?? null,
                'accountActivated' => session('account_activated') ?? false,
            ]) !!};

            if (isDashboardRoute && showDashboardNotifications && (notificationsData.unreadCount > 0 || notificationsData.statusMessage || notificationsData.accountActivated)) {
                let notificationHTML = '';

                if (notificationsData.accountActivated) {
                    notificationHTML += `
                        <div class="alert alert-success d-flex align-items-center mb-3">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <div><strong>Account Activated!</strong></div>
                        </div>
                    `;
                }

                if (notificationsData.statusMessage) {
                    notificationHTML += `
                        <div class="alert alert-info d-flex align-items-center mb-3">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <div>${notificationsData.statusMessage}</div>
                        </div>
                    `;
                }

                if (notificationsData.unreadCount > 0) {
                    const notificationTitle = notificationsData.unreadCount === 1
                        ? 'You have 1 new notification'
                        : `You have ${notificationsData.unreadCount} new notifications`;

                    notificationHTML += `
                        <div class="alert alert-primary d-flex align-items-center mb-3">
                            <i class="bi bi-bell-fill fs-4 text-warning me-3"></i>
                            <div>
                                <p class="mb-0"><strong>${notificationTitle}</strong></p>
                                <small class="text-muted">Here are the latest notifications.</small>
                            </div>
                        </div>
                    `;

                    notificationHTML += `<div class="list-group mb-3">`;
                    (Array.isArray(notificationsData.notifications) ? notificationsData.notifications : []).forEach((note, index) => {
                        if (index >= 3) return;
                        notificationHTML += `
                            <div class="list-group-item p-3 mb-2 rounded border ${note.read_at === null ? 'bg-white' : 'bg-light'}">
                                <div class="fw-semibold">${note.title || 'Notification'}</div>
                                <div class="small text-muted">${note.message ? note.message.substring(0, 80) : 'View the notification for details.'}</div>
                            </div>
                        `;
                    });
                    notificationHTML += `</div>`;

                    if (notificationsData.unreadCount > 3) {
                        notificationHTML += `
                            <div class="small text-muted mb-3">Showing 3 of ${notificationsData.unreadCount} new notifications.</div>
                        `;
                    }
                }

                notificationHTML += `
                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('portal.notifications') }}" class="btn btn-primary">Open notifications</a>
                    </div>
                `;

                const notificationSummary = document.getElementById('portalNotificationSummaryContent');
                const notificationModalElement = document.getElementById('portalNotificationsModal');

                const cleanupModalBackdrops = () => {
                    document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());
                    document.querySelectorAll('.modal.show').forEach(modal => {
                        modal.classList.remove('show');
                        modal.style.display = 'none';
                        modal.setAttribute('aria-hidden', 'true');
                        modal.removeAttribute('aria-modal');
                    });
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                };

                if (notificationSummary) {
                    notificationSummary.innerHTML = notificationHTML;
                }

                if (notificationModalElement && typeof bootstrap !== 'undefined' && typeof bootstrap.Modal === 'function') {
                    cleanupModalBackdrops();
                    const notificationModal = new bootstrap.Modal(notificationModalElement, {
                        keyboard: true,
                        backdrop: false
                    });
                    notificationModal.show();

                    notificationModalElement.addEventListener('hidden.bs.modal', function() {
                        cleanupModalBackdrops();
                    });
                }
            }

            const sidebar = document.getElementById('portalSidebar');
            const overlay = document.getElementById('portalOverlay');
            const menuToggle = document.getElementById('portalMenuToggle');
            const sidebarClose = document.getElementById('portalSidebarClose');

            function openSidebar() {
                sidebar.classList.add('show');
                overlay.classList.add('show');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
                document.body.style.overflow = '';
            }

            if (menuToggle) {
                menuToggle.addEventListener('click', openSidebar);
            }

            if (sidebarClose) {
                sidebarClose.addEventListener('click', closeSidebar);
            }

            if (overlay) {
                overlay.addEventListener('click', closeSidebar);
            }

            // Close sidebar on nav link click (mobile)
            document.querySelectorAll('.nav-link-custom').forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        closeSidebar();
                    }
                });
            });

            // Close sidebar on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && sidebar.classList.contains('show')) {
                    closeSidebar();
                }
            });

            // ========================================
            // NOTIFICATION & MESSAGE DROPDOWNS
            // ========================================
            const notificationToggle = document.getElementById('portalNotificationToggle');
            const notificationDropdown = document.getElementById('portalNotificationDropdown');
            const messageToggle = document.getElementById('portalMessageToggle');
            const messageDropdown = document.getElementById('portalMessageDropdown');
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const notificationMarkAllUrl = @json(route('portal.notifications.mark_all_read'));
            const messageMarkAllUrl = @json(route($messageRoutePrefix.'mark_all_read'));

            function closeAllDropdowns() {
                if (notificationDropdown) {
                    notificationDropdown.classList.remove('show');
                    notificationDropdown.setAttribute('aria-hidden', 'true');
                }
                if (messageDropdown) {
                    messageDropdown.classList.remove('show');
                    messageDropdown.setAttribute('aria-hidden', 'true');
                }
            }

            function openDropdown(dropdown, toggle) {
                closeAllDropdowns();
                if (!dropdown || !toggle) {
                    return;
                }

                dropdown.classList.add('show');
                dropdown.setAttribute('aria-hidden', 'false');
                toggle.setAttribute('aria-expanded', 'true');
            }

            function setBadgeCount(toggle, count) {
                const badge = toggle?.querySelector('.notification-badge');
                if (!toggle) {
                    return;
                }

                if (count > 0) {
                    if (badge) {
                        badge.textContent = count;
                    } else {
                        const badgeEl = document.createElement('span');
                        badgeEl.className = 'notification-badge';
                        badgeEl.textContent = count;
                        toggle.appendChild(badgeEl);
                    }
                } else if (badge) {
                    badge.remove();
                }
            }

            async function markDropdownAsRead(dropdown, toggle, url) {
                if (!dropdown || !toggle || !url) {
                    return;
                }

                const unreadItems = dropdown.querySelectorAll('.notification-item.unread');
                if (unreadItems.length === 0 && !toggle.querySelector('.notification-badge')) {
                    return;
                }

                if (toggle.dataset.marking === 'true') {
                    return;
                }

                toggle.dataset.marking = 'true';

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({}),
                    });

                    if (!response.ok) {
                        return;
                    }

                    const data = await response.json();
                    dropdown.querySelectorAll('.notification-item.unread').forEach(item => item.classList.remove('unread'));
                    setBadgeCount(toggle, data.count ?? 0);
                } finally {
                    toggle.dataset.marking = 'false';
                }
            }

            document.querySelectorAll('.clear-dropdown-btn').forEach(button => {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    const dropdown = button.closest('.notification-dropdown');
                    const toggle = dropdown?.previousElementSibling;
                    if (dropdown && toggle) {
                        markDropdownAsRead(dropdown, toggle, button.dataset.clearUrl);
                    }
                });
            });

            if (notificationToggle && notificationDropdown) {
                notificationToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const isOpen = notificationDropdown.classList.contains('show');
                    if (!isOpen) {
                        openDropdown(notificationDropdown, notificationToggle);
                        markDropdownAsRead(notificationDropdown, notificationToggle, notificationMarkAllUrl);
                    } else {
                        closeAllDropdowns();
                        notificationToggle.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            if (messageToggle && messageDropdown) {
                messageToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const isOpen = messageDropdown.classList.contains('show');
                    if (!isOpen) {
                        openDropdown(messageDropdown, messageToggle);
                        markDropdownAsRead(messageDropdown, messageToggle, messageMarkAllUrl);
                    } else {
                        closeAllDropdowns();
                        messageToggle.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.notification-menu')) {
                    closeAllDropdowns();
                    if (notificationToggle) notificationToggle.setAttribute('aria-expanded', 'false');
                    if (messageToggle) messageToggle.setAttribute('aria-expanded', 'false');
                }
            });

            // Close dropdowns on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeAllDropdowns();
                }
            });

            // ========================================
            // SMOOTH SCROLL & ANIMATIONS
            // ========================================
            
            // Add fade-in animation to cards
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                            entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe all cards and panels
            document.querySelectorAll('.portal-card, .status-card, .dashboard-action-card').forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });

            // ========================================
            // ACTIVE NAV LINK HIGHLIGHTING
            // ========================================
            const currentPath = window.location.pathname;
            document.querySelectorAll('.mobile-bottom-nav a').forEach(link => {
                if (link.getAttribute('href') === currentPath) {
                    link.classList.add('active');
                }
            });
        });

        // ========================================
        // PWA SUPPORT CHAT FUNCTION
        // ========================================
        window.openPortalSupportChat = function(event, fallbackHref) {
            event.preventDefault();

            var openTawk = function() {
                if (window.Tawk_API) {
                    if (typeof window.Tawk_API.maximize === 'function') {
                        window.Tawk_API.maximize();
                        return true;
                    }
                    if (typeof window.Tawk_API.showWidget === 'function') {
                        window.Tawk_API.showWidget();
                        return true;
                    }
                    if (typeof window.Tawk_API.toggle === 'function') {
                        window.Tawk_API.toggle();
                        return true;
                    }
                }
                return false;
            };

            if (openTawk()) {
                return false;
            }

            window.__portalSupportOpenRequested = true;
            var attempts = 0;
            var interval = setInterval(function() {
                attempts += 1;
                if (openTawk()) {
                    clearInterval(interval);
                    window.__portalSupportOpenRequested = false;
                    return;
                }
                if (attempts >= 20) {
                    clearInterval(interval);
                    window.__portalSupportOpenRequested = false;
                    window.location.href = fallbackHref;
                }
            }, 250);

            return false;
        };

        // Attach support chat handler to all support links
        document.addEventListener('DOMContentLoaded', function() {
            var supportIndexPath = new URL('{{ route("portal.support.index") }}', window.location.origin).pathname.replace(/\/+$/, '');
            var supportConversationsPath = new URL('{{ route("portal.support.conversations.index") }}', window.location.origin).pathname.replace(/\/+$/, '');

            document.querySelectorAll('a[href]').forEach(function(link) {
                try {
                    var url = new URL(link.href, window.location.origin);
                    var path = url.pathname.replace(/\/+$/, '');
                    if (path === supportIndexPath || path === supportConversationsPath) {
                        link.addEventListener('click', function(event) {
                            window.openPortalSupportChat(event, this.href);
                        });
                    }
                } catch (e) {
                    // ignore invalid URLs
                }
            });
        });

        // ========================================
        // PWA SERVICE WORKER & INSTALL PROMPT
        // ========================================
        @php
            $pwaSettingValue = \App\Models\PortalSetting::where('key', 'pwa_enabled')->value('value');
            $pwaEnabled = filter_var($pwaSettingValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === true;
        @endphp
        const PWA_ENABLED = {{ $pwaEnabled ? 'true' : 'false' }};

        function updateOfflineState() {
            if (navigator.onLine) {
                document.body.classList.remove('offline-state');
                const offlineBanner = document.getElementById('offlineBanner');
                if (offlineBanner) offlineBanner.style.display = 'none';
            } else {
                document.body.classList.add('offline-state');
                showOfflineBanner();
            }
        }

        function showOfflineBanner() {
            let banner = document.getElementById('offlineBanner');
            if (!banner) {
                banner = document.createElement('div');
                banner.id = 'offlineBanner';
                banner.className = 'alert alert-warning position-fixed top-0 start-50 translate-middle-x mt-3 shadow-lg d-flex align-items-center gap-2';
                banner.style.cssText = 'z-index: 9999; border-radius: 12px; min-width: 300px;';
                banner.innerHTML = '<i class="bi bi-wifi-off fs-5"></i><strong>You are offline</strong><span class="ms-auto small text-muted">Some features may be limited</span>';
                document.body.appendChild(banner);
            }
            banner.style.display = 'flex';
        }

        function unregisterServiceWorkers() {
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.getRegistrations().then(function(registrations) {
                    registrations.forEach(function(registration) {
                        registration.unregister().catch(function(error) {
                            console.log('Failed to unregister service worker:', error);
                        });
                    });
                });
            }
        }

        @if(Auth::check())
            @include('components.pwa-push-registration')
        @endif

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                updateOfflineState();

                if (PWA_ENABLED) {
                    navigator.serviceWorker.register('/service-worker.js', { scope: '/' }).then(function(registration) {
                        console.log('PWA service worker registered:', registration);
                        if (typeof initializePushSubscription === 'function') {
                            initializePushSubscription(registration);
                        }
                        if (typeof setupServiceWorkerUpdates === 'function') {
                            setupServiceWorkerUpdates(registration);
                        }
                    }).catch(function(error) {
                        console.error('PWA service worker registration failed:', error);
                    });
                } else {
                    unregisterServiceWorkers();
                }
            });
        } else {
            window.addEventListener('load', updateOfflineState);
        }

        window.addEventListener('appinstalled', function() {
            if (typeof window.enablePwaNotifications === 'function') {
                window.enablePwaNotifications();
            }
        });

        var deferredPwaPrompt;
        var pwaInstallBanner = document.getElementById('pwaInstallBanner');
        var pwaInstallButton = document.getElementById('pwaInstallButton');
        var pwaInstallDismiss = document.getElementById('pwaInstallDismiss');

        function showPwaInstallBanner() {
            if (!pwaInstallBanner) {
                return;
            }

            pwaInstallBanner.classList.remove('d-none');
            pwaInstallBanner.classList.add('show');
        }

        function hidePwaInstallBanner() {
            if (!pwaInstallBanner) {
                return;
            }

            pwaInstallBanner.classList.add('d-none');
            pwaInstallBanner.classList.remove('show');
        }

        window.addEventListener('beforeinstallprompt', function(event) {
            if (!PWA_ENABLED) {
                event.preventDefault();
                return;
            }

            event.preventDefault();
            deferredPwaPrompt = event;
            if (pwaInstallBanner) {
                setTimeout(showPwaInstallBanner, 1200);
            }
        });

        if (pwaInstallButton) {
            pwaInstallButton.addEventListener('click', function() {
                if (!deferredPwaPrompt) {
                    showPwaInstallBanner();
                    return;
                }

                deferredPwaPrompt.prompt();
                deferredPwaPrompt.userChoice.then(function(choiceResult) {
                    hidePwaInstallBanner();
                    deferredPwaPrompt = null;
                });
            });
        }

        if (pwaInstallDismiss) {
            pwaInstallDismiss.addEventListener('click', function() {
                hidePwaInstallBanner();
            });
        }

        if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true) {
            hidePwaInstallBanner();
        } else if (PWA_ENABLED && pwaInstallBanner) {
            setTimeout(showPwaInstallBanner, 2500);
        }

        function registerPendingSync() {
            if ('serviceWorker' in navigator && PWA_ENABLED && 'SyncManager' in window) {
                navigator.serviceWorker.ready.then(function(registration) {
                    registration.sync.register('sync-pending-requests').catch(function() {});
                });
            }
        }

        window.addEventListener('online', function() {
            registerPendingSync();
            updateOfflineState();
        });

        window.addEventListener('offline', updateOfflineState);

        // ========================================
        // AUTO-DISMISS ALERTS
        // ========================================
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.alert-custom').forEach(function(alert) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(function() { alert.remove(); }, 300);
                }, 5000);
            });
        });
    </script>

    <!-- Tawk.to Live Chat Integration -->
    @if(! empty($portalSettings['tawk_to_property_id']) && ! empty($portalSettings['tawk_to_widget_id']))
        <script type="text/javascript">
            var Tawk_API = Tawk_API || {}, Tawk_LoadStart = new Date();
            Tawk_API.onLoad = function() {
                if (window.__portalSupportOpenRequested) {
                    window.Tawk_API.maximize();
                    window.__portalSupportOpenRequested = false;
                }
            };
            (function(){
                var s = document.createElement('script'), h = document.getElementsByTagName('script')[0];
                s.async = true;
                s.src = 'https://embed.tawk.to/{{ $portalSettings["tawk_to_property_id"] }}/{{ $portalSettings["tawk_to_widget_id"] }}';
                s.charset = 'UTF-8';
                s.setAttribute('crossorigin', '*');
                h.parentNode.insertBefore(s, h);
            })();
        </script>
    @endif

    <!-- Additional Styles for Dynamic Content -->
    <style>
        /* ========================================
           DASHBOARD COMPONENTS
           ======================================== */
        .dashboard-hero {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: minmax(0, 1.3fr) minmax(0, 0.7fr);
            align-items: start;
            margin-bottom: 2rem;
        }

        .dashboard-hero h1 {
            font-size: 2.5rem;
            line-height: 1.1;
            margin-bottom: 0.5rem;
            font-weight: 800;
        }

        .dashboard-hero p {
            color: var(--text-muted);
            font-size: 1rem;
            margin-bottom: 0;
        }

        .dashboard-hero > div {
            background: var(--bg-surface);
            border-radius: var(--radius-2xl);
            padding: 2rem;
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-light);
        }

        /* Status Cards */
        .status-cards {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .status-card {
            background: var(--bg-surface);
            border-radius: var(--radius-xl);
            border: 1px solid var(--border-light);
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
            min-height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .status-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--accent), var(--primary));
            opacity: 0;
            transition: var(--transition);
        }

        .status-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--card-hover-shadow);
        }

        .status-card:hover::before {
            opacity: 1;
        }

        .status-card .status-top {
            display: flex;
            justify-content: space-between;
            gap: 0.75rem;
            align-items: center;
            margin-bottom: 1rem;
        }

        .status-card .status-label {
            font-size: 0.8rem;
            color: var(--text-muted);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }

        .status-card .status-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--text-dark);
            line-height: 1;
        }

        .status-card .status-change {
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.6rem;
            border-radius: var(--radius-full);
        }

        .status-change.positive {
            background: var(--success-light);
            color: #065F46;
        }

        .status-change.negative {
            background: var(--danger-light);
            color: #991B1B;
        }

        /* Icon Circle */
        .icon-circle {
            width: 52px;
            height: 52px;
            border-radius: var(--radius-lg);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.4rem;
            flex-shrink: 0;
        }

        .icon-circle-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        }

        .icon-circle-accent {
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
        }

        .icon-circle-success {
            background: linear-gradient(135deg, var(--success), #059669);
        }

        .icon-circle-warning {
            background: linear-gradient(135deg, var(--warning), #D97706);
        }

        .icon-circle-danger {
            background: linear-gradient(135deg, var(--danger), #DC2626);
        }

        .icon-circle-info {
            background: linear-gradient(135deg, var(--info), #2563EB);
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .quick-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            border-radius: var(--radius-lg);
            border: none;
            padding: 1rem 1.5rem;
            color: white;
            font-weight: 700;
            text-decoration: none;
            transition: var(--transition);
            font-size: 0.95rem;
            box-shadow: var(--shadow-md);
        }

        .quick-action-btn:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            color: white;
        }

        .quick-action-btn:active {
            transform: translateY(-1px);
        }

        .btn-action-start { background: linear-gradient(135deg, #26c0b8, #0e8a97); }
        .btn-action-submit { background: linear-gradient(135deg, #0a6d97, #10456f); }
        .btn-action-report { background: linear-gradient(135deg, #dc3545, #b02a37); }
        .btn-action-upload { background: linear-gradient(135deg, #6f42c1, #4f2a90); }
        .btn-action-chat { background: linear-gradient(135deg, #1699A1, #0E3863); }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.6fr) minmax(0, 1fr);
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        /* Panel Component */
        .panel {
            background: var(--bg-surface);
            border-radius: var(--radius-xl);
            border: 1px solid var(--border-light);
            box-shadow: var(--card-shadow);
            padding: 1.75rem;
            transition: var(--transition);
        }

        .panel:hover {
            box-shadow: var(--card-hover-shadow);
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-light);
        }

        .panel-header h2, .panel-header h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .panel-header p {
            margin: 0.25rem 0 0;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        /* Tables */
        .shift-table {
            min-width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .shift-table tbody tr {
            border-bottom: 1px solid var(--border-light);
            transition: var(--transition);
        }

        .shift-table tbody tr:last-child {
            border-bottom: none;
        }

        .shift-table tbody tr:hover {
            background: var(--bg-muted);
        }

        .shift-table td,
        .shift-table th {
            vertical-align: middle;
            border: 0;
            padding: 1rem 0.85rem;
        }

        .shift-table thead th {
            color: var(--text-muted);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
            border-bottom: 2px solid var(--border-light);
        }

        /* Badges & Chips */
        .shift-label {
            font-size: 0.8rem;
            color: var(--primary);
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .risk-chip {
            display: inline-flex;
            align-items: center;
            font-size: 0.78rem;
            padding: 0.4rem 0.75rem;
            border-radius: var(--radius-full);
            background: rgba(239, 68, 68, 0.1);
            color: #B91C1C;
            font-weight: 700;
        }

        .risk-chip.low {
            background: rgba(16, 185, 129, 0.1);
            color: #065F46;
        }

        .risk-chip.medium {
            background: rgba(245, 158, 11, 0.1);
            color: #92400E;
        }

        .risk-chip.high {
            background: rgba(239, 68, 68, 0.1);
            color: #991B1B;
        }

        .bg-soft-primary {
            background: rgba(22, 153, 161, 0.12);
            color: var(--primary-dark);
            border-radius: var(--radius-full);
            padding: 0.35rem 0.85rem;
            font-size: 0.78rem;
            font-weight: 700;
        }

        /* Workflow Component */
        .workflow {
            background: var(--bg-muted);
            border-radius: var(--radius-xl);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .workflow-header {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .workflow-header h3 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .workflow-header p {
            margin: 0;
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .workflow-list {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 1rem;
        }

        .workflow-step {
            background: var(--bg-surface);
            border-radius: var(--radius-lg);
            padding: 1.25rem 1rem;
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-sm);
            text-align: center;
            transition: var(--transition);
        }

        .workflow-step:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .workflow-step.completed {
            border-color: var(--success);
            background: rgba(16, 185, 129, 0.04);
        }

        .workflow-step.active {
            border-color: var(--accent);
            background: rgba(22, 153, 161, 0.04);
        }

        .step-badge {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--accent), var(--primary));
            color: white;
            font-weight: 700;
            margin-bottom: 0.85rem;
            font-size: 0.9rem;
        }

        .workflow-step.completed .step-badge {
            background: var(--success);
        }

        .workflow-step p {
            margin: 0;
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 500;
        }

        /* Dashboard Action Cards */
        .dashboard-action-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .dashboard-action-card {
            display: inline-flex;
            align-items: center;
            gap: 0.9rem;
            padding: 1.15rem 1.25rem;
            border-radius: var(--radius-lg);
            background: var(--bg-surface);
            border: 1px solid var(--border-light);
            color: var(--text-dark);
            text-decoration: none;
            font-weight: 700;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
            font-size: 0.9rem;
        }

        .dashboard-action-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
            color: var(--text-dark);
        }

        .dashboard-action-card i {
            font-size: 1.35rem;
            flex-shrink: 0;
        }

        .dashboard-action-card span {
            flex: 1;
            text-align: left;
        }

        .dashboard-action-card-danger {
            background: rgba(239, 68, 68, 0.06);
            border-color: rgba(239, 68, 68, 0.12);
            color: #991B1B;
        }

        .dashboard-action-card-warning {
            background: rgba(245, 158, 11, 0.08);
            border-color: rgba(245, 158, 11, 0.15);
            color: #92400E;
        }

        .dashboard-action-card-info {
            background: rgba(59, 130, 246, 0.06);
            border-color: rgba(59, 130, 246, 0.12);
            color: #1D4ED8;
        }

        .dashboard-action-card-success {
            background: rgba(16, 185, 129, 0.06);
            border-color: rgba(16, 185, 129, 0.12);
            color: #065F46;
        }

        /* Progress Bars */
        .progress {
            background-color: rgba(14, 56, 99, 0.08);
            border-radius: var(--radius-full);
            height: 8px;
            overflow: hidden;
        }

        .progress-bar {
            background: linear-gradient(90deg, var(--accent), var(--primary)) !important;
            border-radius: var(--radius-full);
            transition: width 0.6s ease;
        }

        /* Dashboard Summary */
        .dashboard-summary-card {
            background: var(--bg-surface);
            border-radius: var(--radius-xl);
            border: 1px solid var(--border-light);
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
        }

        .dashboard-summary-card h5 {
            margin-bottom: 1rem;
            font-weight: 700;
        }

        .dashboard-summary-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .dashboard-summary-row .summary-item {
            padding: 1rem;
            border-radius: var(--radius-lg);
            background: var(--bg-muted);
            border: 1px solid var(--border-light);
            transition: var(--transition);
        }

        .dashboard-summary-row .summary-item:hover {
            border-color: var(--accent);
            background: rgba(22, 153, 161, 0.04);
        }

        .dashboard-summary-row .summary-item strong {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 0.9rem;
        }

        .dashboard-summary-row .summary-item .text-muted {
            font-size: 0.85rem;
        }

        /* Portal Welcome Card */
        .portal-welcome-card {
            background: linear-gradient(135deg, rgba(22, 153, 161, 0.1), rgba(14, 56, 99, 0.05));
            border: 1px solid rgba(22, 153, 161, 0.15);
            border-radius: var(--radius-xl);
            padding: 2rem;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .portal-welcome-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(22, 153, 161, 0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        /* Buttons */
        .btn-soft-primary {
            background: rgba(22, 153, 161, 0.12);
            color: var(--primary-dark);
            border: 1px solid rgba(22, 153, 161, 0.2);
            transition: var(--transition);
            font-weight: 600;
            border-radius: var(--radius-md);
            padding: 0.6rem 1.25rem;
        }

        .btn-soft-primary:hover {
            background: rgba(22, 153, 161, 0.2);
            transform: translateY(-1px);
            color: var(--primary-dark);
        }

        .btn-accent {
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            color: white;
            border: none;
            box-shadow: 0 8px 24px rgba(22, 153, 161, 0.25);
            transition: var(--transition);
            font-weight: 600;
            border-radius: var(--radius-md);
            padding: 0.6rem 1.5rem;
        }

        .btn-accent:hover {
            background: linear-gradient(135deg, var(--accent-dark), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(22, 153, 161, 0.35);
            color: white;
        }

        /* Empty States */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: var(--text-muted);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .empty-state h5 {
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        /* Offline State */
        body.offline-state .main-content {
            opacity: 0.8;
        }

        /* ========================================
           RESPONSIVE ADJUSTMENTS FOR DASHBOARD
           ======================================== */
        @media (max-width: 1200px) {
            .dashboard-hero {
                grid-template-columns: 1fr;
            }

            .status-cards {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .workflow-list {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .dashboard-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 991.98px) {
            .dashboard-action-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .dashboard-summary-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767.98px) {
            .dashboard-hero h1 {
                font-size: 1.85rem;
            }

            .status-cards {
                grid-template-columns: 1fr;
            }

            .workflow-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }

            .dashboard-action-grid {
                grid-template-columns: 1fr;
            }

            .panel {
                padding: 1.25rem;
            }

            .status-card .status-value {
                font-size: 1.75rem;
            }
        }

        @media (max-width: 575.98px) {
            .dashboard-hero h1 {
                font-size: 1.5rem;
            }

            .workflow-list {
                grid-template-columns: 1fr;
            }

            .shift-table {
                font-size: 0.85rem;
            }

            .shift-table td,
            .shift-table th {
                padding: 0.75rem 0.5rem;
            }
        }

        /* ========================================
           PRINT STYLES
           ======================================== */
        @media print {
            .navbar-portal,
            .portal-sidebar,
            .mobile-bottom-nav,
            .notification-menu,
            #pwaInstallBanner,
            .portal-overlay {
                display: none !important;
            }

            .main-content {
                width: 100% !important;
                max-width: 100% !important;
                flex: 0 0 100% !important;
            }

            .portal-card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }

            body {
                background: white !important;
            }
        }

        /* ========================================
           ANIMATIONS
           ======================================== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.9);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.5s ease-out forwards;
        }

        .animate-fadeIn {
            animation: fadeIn 0.4s ease-out forwards;
        }

        .animate-scaleIn {
            animation: scaleIn 0.3s ease-out forwards;
        }

        /* Staggered animations */
        .stagger-1 { animation-delay: 0.1s; }
        .stagger-2 { animation-delay: 0.2s; }
        .stagger-3 { animation-delay: 0.3s; }
        .stagger-4 { animation-delay: 0.4s; }
        .stagger-5 { animation-delay: 0.5s; }

        /* ========================================
           FORM ENHANCEMENTS
           ======================================== */
        .form-control, .form-select {
            border-radius: var(--radius-md);
            border: 1px solid var(--border-medium);
            padding: 0.75rem 1rem;
            transition: var(--transition);
            font-size: 0.95rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(22, 153, 161, 0.12);
            outline: none;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .input-group {
            border-radius: var(--radius-md);
            overflow: hidden;
        }

        .input-group .form-control {
            border-radius: 0;
        }

        .input-group .btn {
            border-radius: 0;
        }

        /* ========================================
           TABLE ENHANCEMENTS
           ======================================== */
        .table-responsive {
            border-radius: var(--radius-lg);
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border: 1px solid var(--border-light);
        }

        .table-responsive .table {
            min-width: 100%;
        }

        .table-responsive .table th,
        .table-responsive .table td {
            white-space: normal;
        }

        .table {
            margin-bottom: 0;
            width: 100%;
        }

        .table thead th {
            background: var(--bg-muted);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 700;
            color: var(--text-muted);
            border-bottom: 2px solid var(--border-light);
            padding: 1rem;
        }

        .table tbody tr {
            transition: var(--transition);
        }

        .table tbody tr:hover {
            background: var(--bg-muted);
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-color: var(--border-light);
        }

        /* ========================================
           MODAL ENHANCEMENTS
           ======================================== */
        .modal-content {
            border-radius: var(--radius-xl);
            border: none;
            box-shadow: var(--shadow-2xl);
        }

        .modal-header {
            border-bottom: 1px solid var(--border-light);
            padding: 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid var(--border-light);
            padding: 1.25rem 1.5rem;
        }

        .modal-backdrop {
            backdrop-filter: blur(4px);
        }

        #notificationAlertModal,
        #portalNotificationsModal,
        #adminNotificationsModal {
            pointer-events: none;
        }

        #notificationAlertModal .modal-dialog,
        #portalNotificationsModal .modal-dialog,
        #adminNotificationsModal .modal-dialog {
            pointer-events: auto;
        }

        /* ========================================
           TOOLTIP & POPOVER
           ======================================== */
        .tooltip-inner {
            background: var(--text-dark);
            border-radius: var(--radius-md);
            padding: 0.5rem 0.85rem;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .tooltip .tooltip-arrow::before {
            border-top-color: var(--text-dark);
        }

        /* ========================================
           LOADING SPINNER
           ======================================== */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
        }

        .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .loading-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        /* ========================================
           AVATAR & PROFILE
           ======================================== */
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border-light);
        }

        .avatar-sm {
            width: 32px;
            height: 32px;
        }

        .avatar-lg {
            width: 56px;
            height: 56px;
        }

        .avatar-xl {
            width: 80px;
            height: 80px;
        }

        .avatar-group {
            display: flex;
        }

        .avatar-group .avatar {
            margin-left: -10px;
            border: 2px solid white;
        }

        .avatar-group .avatar:first-child {
            margin-left: 0;
        }

        /* ========================================
           TIMELINE
           ======================================== */
        .timeline {
            position: relative;
            padding-left: 2rem;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 0.75rem;
            top: 0;
            bottom: 0;
            width: 2px;
            background: var(--border-light);
        }

        .timeline-item {
            position: relative;
            padding-bottom: 1.5rem;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: -1.65rem;
            top: 0.5rem;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--accent);
            border: 2px solid white;
            box-shadow: 0 0 0 2px var(--accent);
        }

        .timeline-item:last-child {
            padding-bottom: 0;
        }

        /* ========================================
           TAGS & CHIPS
           ======================================== */
        .tag {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.3rem 0.75rem;
            border-radius: var(--radius-full);
            font-size: 0.78rem;
            font-weight: 600;
            background: var(--bg-muted);
            color: var(--text-secondary);
            border: 1px solid var(--border-light);
        }

        .tag-primary {
            background: rgba(14, 56, 99, 0.08);
            color: var(--primary);
            border-color: rgba(14, 56, 99, 0.15);
        }

        .tag-accent {
            background: rgba(22, 153, 161, 0.1);
            color: var(--accent-dark);
            border-color: rgba(22, 153, 161, 0.2);
        }

        .tag-success {
            background: var(--success-light);
            color: #065F46;
            border-color: rgba(16, 185, 129, 0.2);
        }

        .tag-warning {
            background: var(--warning-light);
            color: #92400E;
            border-color: rgba(245, 158, 11, 0.2);
        }

        .tag-danger {
            background: var(--danger-light);
            color: #991B1B;
            border-color: rgba(239, 68, 68, 0.2);
        }

        /* ========================================
           CARD VARIANTS
           ======================================== */
        .card-small {
            border-radius: var(--radius-lg);
            background: var(--bg-surface);
            box-shadow: var(--card-shadow);
            border: 1px solid var(--border-light);
            padding: 1.25rem;
            transition: var(--transition);
        }

        .card-small:hover {
            box-shadow: var(--card-hover-shadow);
            transform: translateY(-2px);
        }

        .border-left-accent {
            border-left: 4px solid var(--accent) !important;
        }

        .border-left-warning {
            border-left: 4px solid var(--warning) !important;
        }

        .border-left-danger {
            border-left: 4px solid var(--danger) !important;
        }

        .border-left-success {
            border-left: 4px solid var(--success) !important;
        }

        /* ========================================
           SEARCH BAR
           ======================================== */
        .search-bar {
            position: relative;
            max-width: 400px;
        }

        .search-bar input {
            padding-left: 2.75rem;
            border-radius: var(--radius-full);
            background: var(--bg-muted);
            border: 1px solid var(--border-light);
        }

        .search-bar input:focus {
            background: var(--bg-surface);
        }

        .search-bar i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        /* ========================================
           DROPDOWN ENHANCEMENTS
           ======================================== */
        .dropdown-menu {
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-light);
            box-shadow: var(--shadow-lg);
            padding: 0.5rem;
            animation: scaleIn 0.2s ease-out;
        }

        .dropdown-item {
            border-radius: var(--radius-md);
            padding: 0.6rem 1rem;
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .dropdown-item:hover {
            background: var(--bg-muted);
            color: var(--text-dark);
        }

        .dropdown-item i {
            width: 20px;
            text-align: center;
            margin-right: 0.5rem;
        }

        /* ========================================
           BREADCRUMB
           ======================================== */
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 1.5rem;
        }

        .breadcrumb-item {
            font-size: 0.875rem;
        }

        .breadcrumb-item a {
            color: var(--text-muted);
            text-decoration: none;
            transition: var(--transition);
        }

        .breadcrumb-item a:hover {
            color: var(--accent);
        }

        .breadcrumb-item.active {
            color: var(--text-dark);
            font-weight: 600;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            color: var(--text-light);
        }

        /* ========================================
           TABS
           ======================================== */
        .nav-tabs-custom {
            border-bottom: 2px solid var(--border-light);
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            margin-bottom: -2px;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            color: var(--text-muted);
            transition: var(--transition);
            border-radius: 0;
        }

        .nav-tabs-custom .nav-link:hover {
            color: var(--accent);
            border-bottom-color: rgba(22, 153, 161, 0.3);
        }

        .nav-tabs-custom .nav-link.active {
            color: var(--accent);
            border-bottom-color: var(--accent);
            background: transparent;
        }

        /* ========================================
           PAGINATION
           ======================================== */
        .pagination {
            gap: 0.35rem;
        }

        .page-link {
            border-radius: var(--radius-md);
            border: 1px solid var(--border-light);
            padding: 0.5rem 0.85rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-secondary);
            transition: var(--transition);
        }

        .page-link:hover {
            background: var(--bg-muted);
            border-color: var(--accent);
            color: var(--accent);
        }

        .page-item.active .page-link {
            background: var(--accent);
            border-color: var(--accent);
            color: white;
        }

        /* ========================================
           STAT MINI CARDS
           ======================================== */
        .stat-mini {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            background: var(--bg-muted);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-light);
        }

        .stat-mini .stat-mini-icon {
            width: 40px;
            height: 40px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .stat-mini .stat-mini-content {
            flex: 1;
        }

        .stat-mini .stat-mini-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-mini .stat-mini-value {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        /* ========================================
           NOTIFICATION DOT
           ======================================== */
        .notification-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--danger);
            display: inline-block;
            animation: pulse 2s infinite;
        }

        /* ========================================
           FILE UPLOAD AREA
           ======================================== */
        .file-upload-area {
            border: 2px dashed var(--border-medium);
            border-radius: var(--radius-xl);
            padding: 2.5rem;
            text-align: center;
            transition: var(--transition);
            cursor: pointer;
            background: var(--bg-muted);
        }

        .file-upload-area:hover {
            border-color: var(--accent);
            background: rgba(22, 153, 161, 0.04);
        }

        .file-upload-area.dragover {
            border-color: var(--accent);
            background: rgba(22, 153, 161, 0.08);
        }

        .file-upload-area i {
            font-size: 2.5rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }

        .file-upload-area p {
            margin: 0;
            color: var(--text-muted);
        }

        .file-upload-area .browse-link {
            color: var(--accent);
            font-weight: 600;
            text-decoration: underline;
        }

        /* ========================================
           TOAST NOTIFICATIONS
           ======================================== */
        .toast-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 9999;
        }

        .toast-custom {
            width: auto;
            max-width: min(100vw - 2rem, 360px);
            background: var(--bg-surface);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            border: 1px solid var(--border-light);
            padding: 1rem 1.25rem;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 0;
            animation: slideDown 0.3s ease-out;
        }

        .toast-custom.success {
            border-left: 4px solid var(--success);
        }

        .toast-custom.error {
            border-left: 4px solid var(--danger);
        }

        .toast-custom.warning {
            border-left: 4px solid var(--warning);
        }

        .toast-custom.info {
            border-left: 4px solid var(--info);
        }

        /* ========================================
           SUPPORT WIDGET STYLES
           ======================================== */
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
            border: 1px solid var(--border-medium);
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
    </style>

    <!-- Support Widget -->
    <div id="supportWidgetCard" class="support-widget-card d-none" aria-live="polite">
        <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
            <div>
                <h6 class="mb-1">Need help?</h6>
                <p class="mb-0 small text-muted">Send a message to the support team.</p>
            </div>
            <button type="button" class="btn btn-sm btn-light rounded-circle" onclick="toggleSupportWidget(false)" aria-label="Close support options">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div id="supportWidgetMessages" class="support-widget-messages mb-2 d-none"></div>
        <form id="supportWidgetForm" class="d-grid gap-2">
            @csrf
            <div id="supportVisitorFields" class="d-none"></div>
            <textarea name="message" rows="3" class="form-control form-control-sm" placeholder="How can we help?" required></textarea>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-send me-2"></i><span id="supportSubmitLabel">Send message</span>
            </button>
            <div id="supportWidgetStatus" class="small text-muted"></div>
        </form>
    </div>

    <div class="support-widget-fab">
        <button type="button" class="btn btn-primary" onclick="toggleSupportWidget()" aria-label="Open support options">
            <i class="bi bi-headset"></i>
        </button>
    </div>
</body>

<script>
    // Support widget functions
    function toggleSupportWidget(show) {
        const card = document.getElementById('supportWidgetCard');
        if (!card) return;

        if (typeof show === 'boolean') {
            card.classList.toggle('d-none', !show);
            return;
        }

        card.classList.toggle('d-none');
    }

    const supportForm = document.getElementById('supportWidgetForm');
    const supportStatus = document.getElementById('supportWidgetStatus');
    const supportMessages = document.getElementById('supportWidgetMessages');
    const supportSubmitLabel = document.getElementById('supportSubmitLabel');

    let widgetConversationId = sessionStorage.getItem('supportConversationId');
    let widgetConversationToken = sessionStorage.getItem('supportConversationToken');
    let widgetPollInterval = null;

    const isChatActive = () => widgetConversationId && widgetConversationToken;

    const renderWidgetMessages = (messages) => {
        if (!supportMessages) return;

        if (!messages || messages.length === 0) {
            supportMessages.innerHTML = '<div class="text-muted small">No messages yet.</div>';
            supportMessages.classList.remove('d-none');
            return;
        }

        supportMessages.innerHTML = messages.map(msg => `
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
        supportMessages.classList.remove('d-none');
        supportMessages.scrollTop = supportMessages.scrollHeight;
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
        if (supportSubmitLabel) {
            supportSubmitLabel.textContent = 'Send message';
        }
        if (supportMessages) {
            supportMessages.classList.remove('d-none');
        }
        if (supportStatus) {
            supportStatus.className = 'small text-muted';
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

    if (supportForm && supportStatus) {
        if (isChatActive()) {
            enableChatMode();
            loadWidgetConversation();
            startWidgetPoll();
        }

        supportForm.addEventListener('submit', async function (event) {
            event.preventDefault();

            const submitButton = supportForm.querySelector('button[type="submit"]');
            const originalText = submitButton ? submitButton.innerHTML : '';

            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Sending...';
            }

            supportStatus.textContent = '';
            supportStatus.className = 'small text-muted';

            const messageField = supportForm.querySelector('textarea[name="message"]');
            const messageValue = messageField ? messageField.value.trim() : '';

            try {
                let payload = null;

                if (isChatActive()) {
                    payload = await sendWidgetMessage(messageValue);
                } else {
                    const formData = new FormData(supportForm);
                    const response = await fetch('{{ route('support.widget.store.authenticated') }}', {
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
                    supportStatus.textContent = payload.message || 'Message sent. A support agent will reply shortly.';
                    supportStatus.className = 'small text-success';
                }

                if (messageField) {
                    messageField.value = '';
                }

                if (isChatActive()) {
                    loadWidgetConversation();
                }
            } catch (error) {
                supportStatus.textContent = error.message || 'Unable to send your message right now.';
                supportStatus.className = 'small text-danger';
            } finally {
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalText;
                }
            }
        });
    }
</script>
</html>