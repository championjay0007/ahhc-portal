<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover, maximum-scale=5.0">
    <title>{{ $portalSettings['website_name'] ?? 'Allegiance Heart & Home Care Admin Dashboard' }} · Control Center</title>
    <meta name="description" content="{{ $portalSettings['website_description'] ?? 'Admin dashboard for portal management.' }}">
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
    <meta name="apple-mobile-web-app-title" content="{{ $portalSettings['website_name'] ?? 'Allegiance Heart & Home Care Admin' }}">
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
            
            /* Sidebar Colors */
            --sidebar-bg: #1E3A5F;
            --sidebar-dark: #152A45;
            --sidebar-hover: rgba(255, 255, 255, 0.08);
            --sidebar-active: rgba(32, 201, 151, 0.95);
            
            /* Background Colors */
            --bg-app: #F5F7FB;
            --bg-surface: #ffffff;
            --bg-elevated: #ffffff;
            --bg-muted: #F8FAFC;
            
            /* Text Colors */
            --text-dark: #1E293B;
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
            
            /* Card Shadows */
            --card-shadow: 0 4px 20px rgba(14, 56, 99, 0.06), 0 2px 8px rgba(0, 0, 0, 0.04);
            --card-hover-shadow: 0 12px 40px rgba(14, 56, 99, 0.12), 0 4px 16px rgba(0, 0, 0, 0.06);
            
            /* Transitions */
            --transition-fast: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            --transition-slow: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            
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
            --sidebar-width: 280px;
            --topbar-height: 72px;
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
           SIDEBAR STYLES
           ======================================== */
        .sidebar {
            background: linear-gradient(180deg, var(--sidebar-bg) 0%, var(--sidebar-dark) 100%);
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            overflow-y: auto;
            padding: 0;
            z-index: 1030;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.12);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            padding: 2rem 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .sidebar-header h5 {
            font-weight: 800;
            font-size: var(--font-size-lg);
            margin-bottom: 0.35rem;
            color: white;
            letter-spacing: -0.3px;
        }

        .sidebar-header small {
            opacity: 0.7;
            font-size: var(--font-size-xs);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .sidebar-header img {
            display: block;
            max-height: 60px;
            width: auto;
            max-width: 100%;
            margin: 1rem auto 0;
            object-fit: contain;
            filter: none;
            opacity: 1;
        }

        /* Navigation Sections */
        .nav-section {
            margin-bottom: 0.5rem;
            padding: 0 0.75rem;
        }

        .nav-section-title {
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            opacity: 0.5;
            margin-bottom: 0.75rem;
            padding: 1rem 0.75rem 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sidebar .nav-group {
            margin-bottom: 0.75rem;
            padding: 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.04);
        }

        .sidebar .nav-group-title {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.35rem 0.5rem 0.7rem;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: rgba(255, 255, 255, 0.72);
        }

        .sidebar .nav-group-title i {
            color: var(--accent-glow);
            font-size: 0.95rem;
        }

        .nav-section-title::before {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar a {
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.75rem 1rem;
            border-radius: var(--radius-lg);
            margin-bottom: 0.25rem;
            transition: var(--transition);
            font-size: var(--font-size-sm);
            font-weight: 500;
            position: relative;
        }

        .sidebar a:hover {
            background: var(--sidebar-hover);
            color: white;
            transform: translateX(4px);
        }

        .sidebar a.active {
            background: var(--sidebar-active);
            color: white;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(32, 201, 151, 0.3);
        }

        .sidebar a.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60%;
            background: white;
            border-radius: 0 var(--radius-full) var(--radius-full) 0;
        }

        .sidebar a i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
            flex-shrink: 0;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            margin-top: auto;
            padding: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.15);
            font-size: var(--font-size-xs);
        }

        .sidebar-footer h6 {
            font-weight: 700;
            margin-bottom: 0.75rem;
            font-size: var(--font-size-sm);
            color: white;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sidebar-footer ul {
            list-style: none;
            margin-bottom: 0;
            padding: 0;
        }

        .sidebar-footer li {
            margin-bottom: 0.5rem;
            opacity: 0.7;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
        }

        .sidebar-footer li::before {
            content: '•';
            color: var(--accent);
            font-weight: bold;
        }

        /* Sidebar Scrollbar */
        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.35);
        }

        /* ========================================
           SIDEBAR OVERLAY
           ======================================== */
        .admin-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
            z-index: 1025;
        }

        .admin-overlay.show {
            opacity: 1;
            pointer-events: auto;
        }

        /* ========================================
           MAIN CONTAINER
           ======================================== */
        .main-container {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
        }

        /* ========================================
           TOPBAR STYLES
           ======================================== */
        .topbar {
            background: var(--bg-surface);
            padding: 0 var(--spacing-xl);
            border-bottom: 1px solid var(--border-light);
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-sm);
            height: var(--topbar-height);
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .topbar-left {
            flex: 1;
            display: flex;
            align-items: center;
            gap: var(--spacing-lg);
        }

        .topbar-search {
            max-width: 400px;
            flex: 1;
            position: relative;
        }

        .topbar-search input {
            width: 100%;
            border-radius: var(--radius-full);
            border: 1px solid var(--border-medium);
            padding: 0.75rem 1rem 0.75rem 3rem;
            font-size: var(--font-size-sm);
            background: var(--bg-muted);
            transition: var(--transition);
        }

        .topbar-search input:focus {
            outline: none;
            border-color: var(--accent);
            background: var(--bg-surface);
            box-shadow: 0 0 0 3px rgba(22, 153, 161, 0.1);
        }

        .topbar-search i {
            position: absolute;
            left: 1.1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.1rem;
        }

        .topbar-right {
            display: flex;
            gap: var(--spacing-md);
            align-items: center;
        }

        .topbar-icon {
            cursor: pointer;
            opacity: 0.7;
            transition: var(--transition);
            font-size: 1.3rem;
            color: var(--text-secondary);
            padding: 0.5rem;
            border-radius: var(--radius-md);
        }

        .topbar-icon:hover {
            opacity: 1;
            background: var(--bg-muted);
            color: var(--accent);
        }

        /* ========================================
           NOTIFICATION & MESSAGE TOGGLES
           ======================================== */
        .notification-menu {
            position: relative;
        }

        .notification-toggle {
            position: relative;
            width: 44px;
            height: 44px;
            border: 1px solid var(--border-medium);
            border-radius: var(--radius-md);
            background: var(--bg-muted);
            color: var(--text-secondary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            font-size: 1.2rem;
        }

        .notification-toggle:hover {
            background: var(--bg-surface);
            border-color: var(--accent);
            color: var(--accent);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .notification-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            min-width: 18px;
            height: 18px;
            border-radius: var(--radius-full);
            background: var(--danger);
            color: white;
            font-size: 0.65rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            border: 2px solid white;
            animation: pulse 2s infinite;
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

        /* ========================================
           NOTIFICATION DROPDOWN
           ======================================== */
        .notification-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 12px);
            width: min(380px, calc(100vw - 2rem));
            max-height: 480px;
            background: var(--bg-surface);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-2xl);
            border: 1px solid var(--border-light);
            opacity: 0;
            visibility: hidden;
            transform: translateY(12px);
            transition: opacity 0.25s ease, transform 0.25s ease, visibility 0.25s;
            z-index: 1100;
            overflow: hidden;
        }

        .notification-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .notification-dropdown .notification-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-light);
            font-weight: 700;
            background: var(--bg-muted);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .notification-dropdown .notification-header h6 {
            margin: 0;
            font-size: var(--font-size-base);
        }

        .notification-dropdown .notification-list {
            max-height: 320px;
            overflow-y: auto;
            padding: 0.75rem;
        }

        .notification-dropdown .notification-item {
            display: block;
            padding: 1rem 1.25rem;
            text-decoration: none;
            color: var(--text-dark);
            border-radius: var(--radius-lg);
            margin-bottom: 0.5rem;
            transition: var(--transition);
            border: 1px solid transparent;
        }

        .notification-dropdown .notification-item:hover {
            background: var(--bg-muted);
            border-color: var(--border-light);
            transform: translateX(4px);
        }

        .notification-dropdown .notification-item.unread {
            background: rgba(22, 153, 161, 0.06);
            border-left: 3px solid var(--accent);
        }

        .notification-dropdown .notification-title {
            font-weight: 700;
            font-size: var(--font-size-sm);
            margin-bottom: 0.35rem;
        }

        .notification-dropdown .notification-message {
            font-size: var(--font-size-sm);
            color: var(--text-secondary);
            line-height: 1.5;
            margin-bottom: 0.5rem;
        }

        .notification-dropdown .notification-meta {
            font-size: var(--font-size-xs);
            color: var(--text-light);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .notification-dropdown .notification-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--border-light);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            background: var(--bg-muted);
        }

        .notification-dropdown .notification-footer a,
        .notification-dropdown .notification-footer button {
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

        .notification-dropdown .notification-footer a:hover,
        .notification-dropdown .notification-footer button:hover {
            background: rgba(22, 153, 161, 0.1);
            color: var(--accent-dark);
        }

        /* ========================================
           USER AVATAR & DROPDOWN
           ======================================== */
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border-light);
            cursor: pointer;
            transition: var(--transition);
        }

        .user-avatar:hover {
            border-color: var(--accent);
            transform: scale(1.05);
        }

        .user-dropdown {
            position: relative;
        }

        .user-dropdown-toggle {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.5rem 1rem;
            background: var(--bg-muted);
            border: 1px solid var(--border-light);
            border-radius: var(--radius-full);
            cursor: pointer;
            transition: var(--transition);
        }

        .user-dropdown-toggle:hover {
            background: var(--bg-surface);
            border-color: var(--accent);
        }

        .user-dropdown-toggle img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
        }

        .user-dropdown-toggle span {
            font-weight: 600;
            font-size: var(--font-size-sm);
            color: var(--text-dark);
        }

        .user-dropdown-toggle i {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        /* ========================================
           MAIN CONTENT AREA
           ======================================== */
        .main-content {
            flex: 1;
            padding: var(--spacing-xl);
            overflow-y: auto;
        }

        .content-header {
            margin-bottom: var(--spacing-2xl);
        }

        .content-header h2 {
            font-weight: 800;
            margin-bottom: 0.35rem;
            font-size: var(--font-size-3xl);
            letter-spacing: -0.5px;
        }

        .content-header p {
            font-size: var(--font-size-base);
            color: var(--text-muted);
            margin-bottom: 0;
        }

        /* ========================================
           CARDS & PANELS
           ======================================== */
        .card {
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            border: none;
            border-radius: var(--radius-xl);
            box-shadow: var(--card-shadow);
            transition: var(--transition);
            background: var(--bg-surface);
            border: 1px solid var(--border-light);
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: var(--card-hover-shadow);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border-light);
            padding: 1.5rem;
            font-weight: 700;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* ========================================
           STAT CARDS
           ======================================== */
        .stat-card {
            background: var(--bg-surface);
            border-radius: var(--radius-xl);
            border: 1px solid var(--border-light);
            box-shadow: var(--card-shadow);
            padding: 1.75rem;
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
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

        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--card-hover-shadow);
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.25rem;
        }

        .stat-card-icon {
            width: 56px;
            height: 56px;
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.6rem;
            color: white;
            flex-shrink: 0;
        }

        .stat-card-icon.primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
        }

        .stat-card-icon.accent {
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
        }

        .stat-card-icon.success {
            background: linear-gradient(135deg, var(--success), #059669);
        }

        .stat-card-icon.warning {
            background: linear-gradient(135deg, var(--warning), #D97706);
        }

        .stat-card-icon.danger {
            background: linear-gradient(135deg, var(--danger), #DC2626);
        }

        .stat-card-icon.info {
            background: linear-gradient(135deg, var(--info), #2563EB);
        }

        .stat-card-label {
            font-size: var(--font-size-sm);
            color: var(--text-muted);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }

        .stat-card-value {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--text-dark);
            line-height: 1;
            margin-bottom: 0.75rem;
        }

        .stat-card-change {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            font-size: var(--font-size-sm);
            font-weight: 600;
            padding: 0.35rem 0.75rem;
            border-radius: var(--radius-full);
        }

        .stat-card-change.positive {
            background: var(--success-light);
            color: #065F46;
        }

        .stat-card-change.negative {
            background: var(--danger-light);
            color: #991B1B;
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
            gap: 0.5rem;
            z-index: 1050;
            box-shadow: 0 -8px 32px rgba(14, 56, 99, 0.08);
            justify-content: space-around;
            align-items: center;
            height: var(--bottom-nav-height);
        }

        .mobile-bottom-nav a,
        .mobile-bottom-nav form button {
            flex: 1;
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
            padding: 0.75rem 0.5rem;
            color: var(--text-muted);
            background: transparent;
            border-radius: var(--radius-lg);
            border: none;
            font-size: 0.7rem;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            position: relative;
            cursor: pointer;
        }

        .mobile-bottom-nav a:hover,
        .mobile-bottom-nav form button:hover {
            background: rgba(22, 153, 161, 0.08);
            color: var(--accent);
        }

        .mobile-bottom-nav a.active,
        .mobile-bottom-nav form button.active {
            color: var(--accent);
            background: rgba(22, 153, 161, 0.12);
        }

        .mobile-bottom-nav a.active::before,
        .mobile-bottom-nav form button.active::before {
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
           MOBILE MENU TOGGLE
           ======================================== */
        .mobile-menu-toggle {
            display: none;
            background: var(--bg-surface);
            border-bottom: 1px solid var(--border-light);
            padding: 0.75rem 1rem;
        }

        .mobile-menu-toggle button {
            background: var(--bg-muted);
            border: 1px solid var(--border-medium);
            border-radius: var(--radius-md);
            padding: 0.6rem 1rem;
            font-size: var(--font-size-sm);
            font-weight: 600;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: var(--transition);
        }

        .mobile-menu-toggle button:hover {
            background: var(--bg-surface);
            border-color: var(--accent);
        }

        /* ========================================
           RESPONSIVE DESIGN
           ======================================== */
        
        /* Large screens (992px and up) */
        @media (min-width: 992px) {
            .mobile-menu-toggle {
                display: none !important;
            }

            .sidebar {
                transform: translateX(0) !important;
            }
        }

        /* Tablets and mobile (991px and down) */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                z-index: 1060;
            }

            .sidebar.show {
                transform: translateX(0);
                box-shadow: 8px 0 40px rgba(0, 0, 0, 0.15);
            }

            .mobile-menu-toggle {
                display: block;
            }

            .main-container {
                margin-left: 0;
            }

            .main-content {
                padding: var(--spacing-lg);
            }
        }

        /* Mobile devices (767px and down) */
        @media (max-width: 767.98px) {
            :root {
                --topbar-height: 64px;
                --spacing-xl: 1.5rem;
                --spacing-lg: 1.25rem;
            }

            .mobile-bottom-nav {
                display: flex;
            }

            body {
                padding-bottom: calc(var(--bottom-nav-height) + 1rem);
            }

            .topbar {
                padding: 0 var(--spacing-md);
                flex-direction: column;
                height: auto;
                padding: var(--spacing-md);
                gap: var(--spacing-md);
            }

            .topbar-left {
                width: 100%;
                flex-direction: column;
                gap: var(--spacing-md);
            }

            .topbar-search {
                max-width: 100%;
                width: 100%;
            }

            .topbar-right {
                width: 100%;
                justify-content: space-between;
                gap: var(--spacing-sm);
            }

            .main-content {
                padding: var(--spacing-md);
            }

            .content-header h2 {
                font-size: var(--font-size-2xl);
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

            .topbar {
                padding: var(--spacing-md);
            }

            .notification-toggle {
                width: 40px;
                height: 40px;
            }

            h1 { font-size: var(--font-size-3xl); }
            h2 { font-size: var(--font-size-2xl); }
            h3 { font-size: var(--font-size-xl); }

            .card:hover,
            .stat-card:hover {
                transform: none;
                box-shadow: var(--card-shadow);
            }

            .stat-card-value {
                font-size: 2rem;
            }

            .stat-card {
                padding: 1.25rem;
            }
        }

        /* Extra small devices (374px and down) */
        @media (max-width: 374.98px) {
            :root {
                --spacing-xl: 1rem;
                --spacing-lg: 0.875rem;
                --spacing-md: 0.75rem;
            }

            .notification-toggle {
                width: 38px;
                height: 38px;
            }

            .stat-card-value {
                font-size: 1.75rem;
            }

            h1 { font-size: var(--font-size-2xl); }
            h2 { font-size: var(--font-size-xl); }
        }

        /* Landscape mode */
        @media (max-height: 500px) and (orientation: landscape) {
            .sidebar {
                padding-top: 1rem;
            }

            .sidebar-header {
                padding: 1rem;
            }

            .main-content {
                padding: 1rem 0.5rem;
            }
        }

        /* Touch devices */
        @media (hover: none) and (pointer: coarse) {
            .sidebar a,
            .notification-toggle,
            .mobile-bottom-nav a,
            .mobile-bottom-nav form button {
                min-height: 44px;
                min-width: 44px;
            }

            .card:hover {
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
            box-shadow: 0 0 40px rgba(22, 153, 161, 0.15);
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
        .sidebar a,
        .mobile-bottom-nav a,
        .notification-toggle {
            user-select: none;
            -webkit-user-select: none;
        }

        /* ========================================
           BADGES & CHIPS
           ======================================== */
        .badge-pill {
            border-radius: var(--radius-full);
            padding: 0.4rem 0.85rem;
            font-size: var(--font-size-xs);
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .badge-success {
            background: var(--success-light);
            color: #065F46;
        }

        .badge-warning {
            background: var(--warning-light);
            color: #92400E;
        }

        .badge-danger {
            background: var(--danger-light);
            color: #991B1B;
        }

        .badge-info {
            background: var(--info-light);
            color: #1D4ED8;
        }

        .badge-primary {
            background: rgba(14, 56, 99, 0.1);
            color: var(--primary);
        }

        /* ========================================
           HOVER EFFECTS
           ======================================== */
        .hover-lift {
            transition: var(--transition);
        }

        .hover-lift:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-xl);
        }

        .text-muted-light {
            opacity: 0.7;
        }

        /* ========================================
           PRINT STYLES
           ======================================== */
        @media print {
            .sidebar,
            .topbar,
            .mobile-bottom-nav,
            .notification-menu,
            #pwaInstallBanner,
            .admin-overlay {
                display: none !important;
            }

            .main-container {
                margin-left: 0 !important;
            }

            .main-content {
                padding: 0 !important;
            }

            .card {
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
            font-size: var(--font-size-sm);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(22, 153, 161, 0.12);
            outline: none;
        }

        .form-label {
            font-weight: 600;
            font-size: var(--font-size-sm);
            color: var(--text-dark);
            margin-bottom: 0.5rem;
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

        /* ========================================
           ALERTS
           ======================================== */
        .alert {
            border-radius: var(--radius-xl);
            border: none;
            box-shadow: var(--shadow-md);
            padding: 1.25rem 1.5rem;
            margin-bottom: var(--spacing-lg);
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(16, 185, 129, 0.05));
            color: #065F46;
            border-left: 4px solid var(--success);
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(239, 68, 68, 0.05));
            color: #991B1B;
            border-left: 4px solid var(--danger);
        }

        .alert-warning {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(245, 158, 11, 0.05));
            color: #92400E;
            border-left: 4px solid var(--warning);
        }

        .alert-info {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.05));
            color: #1D4ED8;
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
    @stack('styles')
</head>
<body>
    <!-- PWA Install Banner -->
    <div id="pwaInstallBanner" class="alert alert-info p-3 position-fixed bottom-0 end-0 m-3 shadow d-none" style="z-index:1080;">
        <div class="d-flex align-items-start gap-2">
            <i class="bi bi-cloud-arrow-down-fill fs-3 text-primary"></i>
            <div class="flex-grow-1">
                <strong class="d-block mb-1">Install Allegiance Heart &amp; Home Care Admin</strong>
                <div class="small text-muted">Add to home screen for faster access</div>
            </div>
        </div>
        <div class="mt-3 d-flex gap-2">
            <button id="pwaInstallButton" class="btn btn-sm btn-primary flex-grow-1">Install</button>
            <button id="pwaInstallDismiss" class="btn btn-sm btn-outline-secondary">Dismiss</button>
        </div>
    </div>

    <!-- Mobile Menu Toggle -->
    <div class="mobile-menu-toggle d-md-none">
        <button type="button" id="adminMenuToggle">
            <i class="bi bi-list"></i>
            <span>Menu</span>
        </button>
    </div>

    <!-- Sidebar Overlay -->
    <div class="admin-overlay" id="adminOverlay"></div>

    <!-- Sidebar -->
    <div class="sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <h5>{{ $portalSettings['website_name'] ?? 'Allegiance Heart & Home Care Admin' }}</h5>
            <small>{{ $portalSettings['website_subtitle'] ?? 'Control Center' }}</small>
            @if(! empty($portalSettings['logo_path']))
                <div class="mt-3 text-center">
                    <img src="{{ asset('storage/' . $portalSettings['logo_path']) }}" alt="{{ $portalSettings['website_name'] ?? 'Logo' }}">
                </div>
            @endif
        </div>

        <div class="flex-grow-1 overflow-auto">
            <div class="nav-section">
                <div class="nav-group">
                    <div class="nav-group-title"><i class="bi bi-speedometer2"></i><span>Overview</span></div>
                    <a href="{{ route('portal.admin.dashboard') }}" class="@if(request()->routeIs('portal.admin.dashboard')) active @endif">
                        <i class="bi bi-grid"></i>
                        <span>Dashboard</span>
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title"><i class="bi bi-people"></i><span>Core</span></div>
                    <a href="{{ route('portal.admin.participants') }}" class="@if(request()->routeIs('portal.admin.participants*')) active @endif">
                        <i class="bi bi-people"></i>
                        <span>Participants</span>
                    </a>
                    <a href="{{ route('portal.admin.workers') }}" class="@if(request()->routeIs('portal.admin.workers*')) active @endif">
                        <i class="bi bi-person-badge"></i>
                        <span>Workers</span>
                    </a>
                    <a href="{{ route('admin.worker_onboarding.index') }}" class="@if(request()->routeIs('admin.worker_onboarding*')) active @endif">
                        <i class="bi bi-person-plus"></i>
                        <span>Worker Onboarding</span>
                    </a>
                    <a href="{{ route('portal.admin.assignments') }}" class="@if(request()->routeIs('portal.admin.assignments*')) active @endif">
                        <i class="bi bi-link"></i>
                        <span>Assignments</span>
                    </a>
                    <a href="{{ route('portal.admin.enquiries.index') }}" class="@if(request()->routeIs('portal.admin.enquiries*')) active @endif">
                        <i class="bi bi-chat-left-text"></i>
                        <span>New Enquiries</span>
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title"><i class="bi bi-cash-stack"></i><span>Finance</span></div>
                    <a href="{{ route('portal.admin.budgets') }}" class="@if(request()->routeIs('portal.admin.budgets')) active @endif">
                        <i class="bi bi-cash-stack"></i>
                        <span>Budgets</span>
                    </a>
                    <a href="{{ route('portal.admin.pre_approvals') }}" class="@if(request()->routeIs('portal.admin.pre_approvals')) active @endif">
                        <i class="bi bi-check2-square"></i>
                        <span>Pre-Approvals</span>
                    </a>
                    <a href="{{ route('portal.admin.invoices') }}" class="@if(request()->routeIs('portal.admin.invoices')) active @endif">
                        <i class="bi bi-receipt"></i>
                        <span>Invoices</span>
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title"><i class="bi bi-tools"></i><span>Operations</span></div>
                    <a href="{{ route('portal.admin.care_notes') }}" class="@if(request()->routeIs('portal.admin.care_notes*')) active @endif">
                        <i class="bi bi-journal-text"></i>
                        <span>Care Notes</span>
                    </a>
                    <a href="{{ route('portal.admin.incidents') }}" class="@if(request()->routeIs('portal.admin.incidents*')) active @endif">
                        <i class="bi bi-exclamation-triangle"></i>
                        <span>Incidents</span>
                    </a>
                    <a href="{{ route('portal.admin.documents') }}" class="@if(request()->routeIs('portal.admin.documents*')) active @endif">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Forms & E-sign</span>
                    </a>
                    <a href="{{ route('portal.gallery') }}" class="@if(request()->routeIs('portal.gallery*')) active @endif">
                        <i class="bi bi-images"></i>
                        <span>Shared Gallery</span>
                    </a>
                    <a href="{{ route('portal.admin.compliance.dashboard') }}" class="@if(request()->routeIs('portal.admin.compliance*')) active @endif">
                        <i class="bi bi-shield-check"></i>
                        <span>Compliance</span>
                    </a>
                    <a href="{{ route('portal.admin.documents.create') }}" class="@if(request()->routeIs('portal.admin.documents.create')) active @endif">
                        <i class="bi bi-file-plus"></i>
                        <span>Assign New Form</span>
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title"><i class="bi bi-chat-dots"></i><span>Communications</span></div>
                    <a href="{{ route('portal.admin.messages.templates.index') }}" class="@if(request()->routeIs('portal.admin.messages.templates*')) active @endif">
                        <i class="bi bi-file-text"></i>
                        <span>Message Templates</span>
                    </a>
                    <a href="{{ route('portal.admin.messages.email_templates.index') }}" class="@if(request()->routeIs('portal.admin.messages.email_templates*')) active @endif">
                        <i class="bi bi-envelope-paper"></i>
                        <span>Email Templates</span>
                    </a>
                    <a href="{{ route('portal.admin.messages.send.index') }}" class="@if(request()->routeIs('portal.admin.messages.send*')) active @endif">
                        <i class="bi bi-envelope"></i>
                        <span>Send Message</span>
                    </a>
                    <a href="{{ route('portal.admin.messages.broadcast.index') }}" class="@if(request()->routeIs('portal.admin.messages.broadcast*')) active @endif">
                        <i class="bi bi-megaphone"></i>
                        <span>Broadcast</span>
                    </a>
                    <a href="{{ route('portal.admin.messages.sent') }}" class="@if(request()->routeIs('portal.admin.messages.sent')) active @endif">
                        <i class="bi bi-send"></i>
                        <span>Sent Messages</span>
                    </a>
                    <a href="{{ route('portal.admin.enquiries.index') }}" class="@if(request()->routeIs('portal.admin.enquiries*')) active @endif">
                        <i class="bi bi-chat-left-text"></i>
                        <span>New Enquiries</span>
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title"><i class="bi bi-headset"></i><span>Support</span></div>
                    <a href="{{ route('portal.admin.support.dashboard') }}" class="@if(request()->routeIs('portal.admin.support.dashboard')) active @endif">
                        <i class="bi bi-headset"></i>
                        <span>Support Center</span>
                    </a>
                    <a href="{{ route('portal.admin.support.tickets') }}" class="@if(request()->routeIs('portal.admin.support.ticket*')) active @endif">
                        <i class="bi bi-ticket"></i>
                        <span>Support Tickets</span>
                    </a>
                    <a href="{{ route('portal.admin.support.conversations') }}" class="@if(request()->routeIs('portal.admin.support.conversation*')) active @endif">
                        <i class="bi bi-chat-dots"></i>
                        <span>Live Chat</span>
                    </a>
                </div>

                <div class="nav-group">
                    <div class="nav-group-title"><i class="bi bi-shield-shaded"></i><span>System</span></div>
                    <a href="{{ route('portal.admin.reports') }}" class="@if(request()->routeIs('portal.admin.reports')) active @endif">
                        <i class="bi bi-bar-chart"></i>
                        <span>Reports</span>
                    </a>
                    <a href="{{ route('portal.admin.users') }}" class="@if(request()->routeIs('portal.admin.users*')) active @endif">
                        <i class="bi bi-shield-exclamation"></i>
                        <span>User Management</span>
                    </a>
                    <a href="{{ route('portal.admin.system.users') }}" class="@if(request()->routeIs('portal.admin.system.users')) active @endif">
                        <i class="bi bi-people-fill"></i>
                        <span>System Users</span>
                    </a>
                    <a href="{{ route('portal.admin.activity') }}" class="@if(request()->routeIs('portal.admin.activity')) active @endif">
                        <i class="bi bi-shield-lock"></i>
                        <span>Audit Logs</span>
                    </a>
                    <a href="{{ route('portal.admin.settings') }}" class="@if(request()->routeIs('portal.admin.settings')) active @endif">
                        <i class="bi bi-sliders"></i>
                        <span>Settings</span>
                    </a>
                    <a href="{{ route('portal.admin.legal') }}" class="@if(request()->routeIs('portal.admin.legal')) active @endif">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>Legal Documents</span>
                    </a>
                    <a href="{{ route('portal.admin.system.dashboard') }}" class="@if(request()->routeIs('portal.admin.system.*')) active @endif">
                        <i class="bi bi-gear-fill"></i>
                        <span>System Admin</span>
                    </a>
                    <a href="{{ route('portal.admin.system.mfa') }}" class="@if(request()->routeIs('portal.admin.system.mfa')) active @endif">
                        <i class="bi bi-shield-lock-fill"></i>
                        <span>MFA Management</span>
                    </a>
                    <a href="{{ route('portal.admin.system.permission_groups') }}" class="@if(request()->routeIs('portal.admin.system.permission_groups*')) active @endif">
                        <i class="bi bi-list-check"></i>
                        <span>Permission Groups</span>
                    </a>
                    <a href="{{ route('portal.admin.system.notification_rules') }}" class="@if(request()->routeIs('portal.admin.system.notification_rules')) active @endif">
                        <i class="bi bi-bell-fill"></i>
                        <span>Notification Rules</span>
                    </a>
                    <a href="{{ route('portal.admin.system.data_retention') }}" class="@if(request()->routeIs('portal.admin.system.data_retention')) active @endif">
                        <i class="bi bi-clock-history"></i>
                        <span>Data Retention</span>
                    </a>
                    <a href="{{ route('portal.admin.system.health') }}" class="@if(request()->routeIs('portal.admin.system.health')) active @endif">
                        <i class="bi bi-heart-pulse-fill"></i>
                        <span>System Health</span>
                    </a>
                </div>
            </div>
    </div>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <h6>Access Control</h6>
        <ul>
            <li>Workers see assigned only</li>
            <li>Participants see own only</li>
            <li>Finance sees invoices</li>
            <li>Admin controls all</li>
        </ul>
    </div>
</div>

<!-- Main Container -->
<div class="main-container">
    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-left">
            <div class="topbar-search">
                <i class="bi bi-search"></i>
                <input type="text" class="form-control" placeholder="Search participants, workers, invoices...">
            </div>
        </div>
        <div class="topbar-right">
            <!-- Notifications -->
            <div class="notification-menu">
                <button class="notification-toggle" type="button" id="adminNotificationToggle" aria-expanded="false" aria-label="Notifications">
                    <i class="bi bi-bell-fill"></i>
                    @if(isset($unreadNotificationCount) && $unreadNotificationCount > 0)
                        <span class="notification-badge">{{ $unreadNotificationCount }}</span>
                    @endif
                </button>
                <div class="notification-dropdown" id="adminNotificationDropdown">
                    <div class="notification-header">
                        <h6>Notifications</h6>
                    </div>
                    <div class="notification-list">
                        @forelse($portalNotifications ?? [] as $notification)
                            @php
                                $data = $notification->data ?? [];
                                $title = $data['title'] ?? ucfirst($notification->type ?? 'Notification');
                                $message = $data['message'] ?? 'View details for this update.';
                                $url = $data['url'] ?? route('portal.notifications');
                                $isUnread = $notification->read_at === null;
                            @endphp
                            <a href="{{ route('portal.notifications.show', $notification) }}" class="notification-item {{ $isUnread ? 'unread' : '' }}">
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
                        @if(isset($unreadNotificationCount) && $unreadNotificationCount > 0)
                            <form method="POST" action="{{ route('portal.notifications.mark_all_read') }}" class="m-0">
                                @csrf
                                <button type="submit">
                                    <i class="bi bi-check2-all me-1"></i>Mark all read
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <div class="notification-menu">
                <button class="notification-toggle" type="button" id="adminMessageToggle" aria-expanded="false" aria-label="Messages">
                    <i class="bi bi-envelope-fill"></i>
                    @if(isset($unreadSupportConversationCount) && $unreadSupportConversationCount > 0)
                        <span class="notification-badge">{{ $unreadSupportConversationCount }}</span>
                    @elseif(isset($unreadMessageCount) && $unreadMessageCount > 0)
                        <span class="notification-badge">{{ $unreadMessageCount }}</span>
                    @endif
                </button>
                <div class="notification-dropdown" id="adminMessageDropdown">
                    <div class="notification-header">
                        <h6>Live chat</h6>
                    </div>
                    <div class="notification-list">
                        @forelse($supportConversations ?? [] as $conversation)
                            @php
                                $latestMessage = $conversation->messages->last();
                                $hasUnread = $conversation->messages->contains(fn ($message) => ! $message->is_admin && $message->read_at === null);
                            @endphp
                            <a href="{{ route('portal.admin.support.conversation.show', $conversation) }}" class="notification-item {{ $hasUnread ? 'unread' : '' }}">
                                <div class="notification-title">{{ $conversation->subject }}</div>
                                <div class="notification-message">{{ Str::limit($latestMessage?->message ?? 'No messages yet', 60) }}</div>
                                <div class="notification-meta">
                                    <i class="bi bi-chat-dots"></i>
                                    {{ $conversation->user->name ?? 'Visitor' }} • {{ $conversation->last_message_at?->diffForHumans() ?? 'New' }}
                                </div>
                            </a>
                        @empty
                            <div class="notification-item text-center text-muted py-4">
                                <i class="bi bi-envelope fs-1 d-block mb-2"></i>
                                <div>No live chats yet</div>
                            </div>
                        @endforelse
                    </div>
                    <div class="notification-footer">
                        <a href="{{ route('portal.admin.support.conversations') }}">
                            <i class="bi bi-chat-left-text me-1"></i>Open chat list
                        </a>
                    </div>
                </div>
            </div>

            <!-- Settings Icon -->
            <a href="{{ route('portal.admin.settings') }}" class="topbar-icon" title="Settings">
                <i class="bi bi-gear-fill"></i>
            </a>

            <!-- User Dropdown -->
            <div class="user-dropdown dropdown">
                <div class="user-dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="{{ auth()->user()->profile_photo_url ?? 'https://via.placeholder.com/40' }}" alt="avatar">
                    <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                    <i class="bi bi-chevron-down d-none d-md-inline"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0" style="border-radius: var(--radius-lg); min-width: 200px;">
                    <li>
                        <a class="dropdown-item py-2 px-3" href="{{ route('portal.profile') }}">
                            <i class="bi bi-person me-2"></i>Profile
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item py-2 px-3" href="{{ route('portal.admin.settings') }}">
                            <i class="bi bi-gear me-2"></i>Settings
                        </a>
                    </li>
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

    <!-- Main Content -->
    <div class="main-content">
        @if(session('status'))
            <div class="alert alert-success d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill me-3 fs-5"></i>
                <div class="flex-grow-1">{{ session('status') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger" role="alert">
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

        @yield('content')
    </div>
</div>

<!-- Mobile Bottom Navigation -->
<div class="mobile-bottom-nav d-md-none">
    <a href="{{ route('portal.admin.dashboard') }}" class="@if(request()->routeIs('portal.admin.dashboard')) active @endif">
        <i class="bi bi-speedometer2"></i>
        <span>Home</span>
    </a>
    <a href="{{ route('portal.admin.participants') }}" class="@if(request()->routeIs('portal.admin.participants*')) active @endif">
        <i class="bi bi-people"></i>
        <span>People</span>
    </a>
    <a href="{{ route('portal.admin.messages.send.index') }}" class="@if(request()->routeIs('portal.admin.messages.*')) active @endif">
        <i class="bi bi-envelope"></i>
        <span>Messages</span>
    </a>
    <a href="{{ route('portal.admin.support.dashboard') }}" class="@if(request()->routeIs('portal.admin.support.*')) active @endif">
        <i class="bi bi-headset"></i>
        <span>Support</span>
    </a>
    <a href="{{ route('portal.admin.settings') }}" class="@if(request()->routeIs('portal.admin.settings')) active @endif">
        <i class="bi bi-sliders"></i>
        <span>More</span>
    </a>
</div>

<!-- Dashboard Notification Summary Modal -->
<div class="modal fade" id="adminNotificationsModal" tabindex="-1" aria-labelledby="adminNotificationsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adminNotificationsModalLabel">Dashboard notifications</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="adminNotificationSummaryContent">
                <!-- Filled dynamically after login if dashboard notifications are available -->
            </div>
            <div class="modal-footer">
                <a href="{{ route('portal.notifications') }}" class="btn btn-primary">Open notifications</a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const originalFetch = window.fetch.bind(window);

    window.fetch = function(input, init = {}) {
        const method = (init.method || (input instanceof Request ? input.method : 'GET')).toUpperCase();
        const targetUrl = typeof input === 'string' ? input : input instanceof Request ? input.url : input.url;

        const isStateChanging = ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method);
        const isSameOrigin = (() => {
            try {
                return !targetUrl.startsWith('http://') && !targetUrl.startsWith('https://') || new URL(targetUrl, window.location.origin).origin === window.location.origin;
            } catch (error) {
                return true;
            }
        })();

        if (csrfToken && isStateChanging && isSameOrigin) {
            const headers = new Headers(init.headers || {});
            if (!headers.has('X-CSRF-TOKEN')) {
                headers.set('X-CSRF-TOKEN', csrfToken);
            }
            if (!headers.has('X-Requested-With')) {
                headers.set('X-Requested-With', 'XMLHttpRequest');
            }
            return originalFetch(input, { ...init, headers });
        }

        return originalFetch(input, init);
    };
</script>

<script>
    // ========================================
    // SIDEBAR TOGGLE FUNCTIONALITY
    // ========================================
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('adminOverlay');
        const menuToggle = document.getElementById('adminMenuToggle');

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

        if (overlay) {
            overlay.addEventListener('click', closeSidebar);
        }

        // Close sidebar on nav link click (mobile)
        document.querySelectorAll('.sidebar a').forEach(link => {
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
        const notificationToggle = document.getElementById('adminNotificationToggle');
        const notificationDropdown = document.getElementById('adminNotificationDropdown');
        const messageToggle = document.getElementById('adminMessageToggle');
        const messageDropdown = document.getElementById('adminMessageDropdown');

        function closeAllDropdowns() {
            if (notificationDropdown) notificationDropdown.classList.remove('show');
            if (messageDropdown) messageDropdown.classList.remove('show');
        }

        if (notificationToggle && notificationDropdown) {
            notificationToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                const isOpen = notificationDropdown.classList.contains('show');
                closeAllDropdowns();
                if (!isOpen) {
                    notificationDropdown.classList.add('show');
                    notificationToggle.setAttribute('aria-expanded', 'true');
                } else {
                    notificationToggle.setAttribute('aria-expanded', 'false');
                }
            });
        }

        if (messageToggle && messageDropdown) {
            messageToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                const isOpen = messageDropdown.classList.contains('show');
                closeAllDropdowns();
                if (!isOpen) {
                    messageDropdown.classList.add('show');
                    messageToggle.setAttribute('aria-expanded', 'true');
                } else {
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
        // DASHBOARD NOTIFICATION SUMMARY MODAL
        // ========================================
        const isDashboardRoute = {{ request()->routeIs('portal.admin.dashboard') ? 'true' : 'false' }};
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
                notificationsData.notifications.forEach((note, index) => {
                    if (index >= 3) return;
                    notificationHTML += `
                        <div class="list-group-item p-3 mb-2 rounded border ${note.read_at === null ? 'bg-white' : 'bg-light'}">
                            <div class="fw-semibold">${note.title}</div>
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

            const notificationSummary = document.getElementById('adminNotificationSummaryContent');
            const notificationModalElement = document.getElementById('adminNotificationsModal');

            if (notificationSummary) {
                notificationSummary.innerHTML = notificationHTML;
            }

            if (notificationModalElement && typeof bootstrap !== 'undefined' && typeof bootstrap.Modal === 'function') {
                const notificationModal = new bootstrap.Modal(notificationModalElement, {
                    keyboard: true,
                    backdrop: false
                });
                notificationModal.show();
            }
        }

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
        document.querySelectorAll('.card, .stat-card').forEach(el => {
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
    // PWA SERVICE WORKER & INSTALL PROMPT
    // ========================================
    @php
        $pwaSettingValue = \App\Models\PortalSetting::where('key', 'pwa_enabled')->value('value');
        $pwaEnabled = $pwaSettingValue === null || $pwaSettingValue === '' || filter_var($pwaSettingValue, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== false;
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

    @if(Auth::check())
        @include('components.pwa-push-registration')
    @endif

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

    var deferredPwaPrompt;
    var pwaInstallBanner = document.getElementById('pwaInstallBanner');
    var pwaInstallButton = document.getElementById('pwaInstallButton');
    var pwaInstallDismiss = document.getElementById('pwaInstallDismiss');

    window.addEventListener('beforeinstallprompt', function(event) {
        if (!PWA_ENABLED) {
            event.preventDefault();
            return;
        }

        event.preventDefault();
        deferredPwaPrompt = event;
        if (pwaInstallBanner) {
            setTimeout(function() {
                pwaInstallBanner.classList.remove('d-none');
            }, 3000);
        }
    });

    if (pwaInstallButton) {
        pwaInstallButton.addEventListener('click', function() {
            if (!deferredPwaPrompt) return;
            deferredPwaPrompt.prompt();
            deferredPwaPrompt.userChoice.then(function(choiceResult) {
                if (pwaInstallBanner) pwaInstallBanner.classList.add('d-none');
                deferredPwaPrompt = null;
            });
        });
    }

    if (pwaInstallDismiss) {
        pwaInstallDismiss.addEventListener('click', function() {
            if (pwaInstallBanner) pwaInstallBanner.classList.add('d-none');
        });
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
        document.querySelectorAll('.alert').forEach(function(alert) {
            if (!alert.classList.contains('alert-danger')) {
                setTimeout(function() {
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-10px)';
                    setTimeout(function() { alert.remove(); }, 300);
                }, 5000);
            }
        });
    });
</script>

    <!-- Support Widget (authenticated users) -->
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

    <script>
        // Support widget functions (authenticated)
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

@stack('scripts')