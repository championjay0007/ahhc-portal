@extends('layouts.public')

@section('content')
<style>
    /* ============================================================
       AHHC SELF-MANAGEMENT SUPPORT - PAGE STYLES
       ============================================================ */

    /* ----- HERO SECTION ----- */
    .hero-section {
        position: relative;
        min-height: 100vh;
        display: flex;
        align-items: center;
        padding: 8rem 0 5rem;
        overflow: hidden;
        background: linear-gradient(160deg, #f7fffd 0%, #dff6f1 30%, #c9ede8 65%, #e8fbf9 100%);
        isolation: isolate;
    }

    /* Animated orbs */
    .hero-orb {
        position: absolute;
        border-radius: 50%;
        filter: blur(100px);
        z-index: 0;
        pointer-events: none;
        opacity: 0.5;
    }

    .hero-orb-1 {
        width: 70vw;
        max-width: 800px;
        height: 70vw;
        max-height: 800px;
        top: -20%;
        right: -10%;
        background: radial-gradient(circle, rgba(31, 199, 183, 0.2) 0%, rgba(58, 166, 201, 0.06) 50%, transparent 70%);
        animation: orbFloat1 25s ease-in-out infinite;
    }

    .hero-orb-2 {
        width: 60vw;
        max-width: 700px;
        height: 60vw;
        max-height: 700px;
        bottom: -25%;
        left: -8%;
        background: radial-gradient(circle, rgba(58, 166, 201, 0.15) 0%, rgba(31, 199, 183, 0.08) 45%, transparent 70%);
        animation: orbFloat2 30s ease-in-out infinite reverse;
    }

    .hero-orb-3 {
        width: 40vw;
        max-width: 500px;
        height: 40vw;
        max-height: 500px;
        top: 30%;
        left: 40%;
        background: radial-gradient(circle, rgba(125, 216, 196, 0.1) 0%, rgba(31, 199, 183, 0.05) 50%, transparent 70%);
        animation: orbFloat3 20s ease-in-out infinite;
    }

    @keyframes orbFloat1 {
        0%, 100% { transform: translate(0, 0) scale(1) rotate(0deg); }
        25% { transform: translate(40px, -30px) scale(1.06) rotate(2deg); }
        50% { transform: translate(-20px, 25px) scale(0.95) rotate(-1deg); }
        75% { transform: translate(-35px, -15px) scale(1.03) rotate(1deg); }
    }

    @keyframes orbFloat2 {
        0%, 100% { transform: translate(0, 0) scale(1) rotate(0deg); }
        33% { transform: translate(-30px, 25px) scale(1.05) rotate(-2deg); }
        66% { transform: translate(25px, -20px) scale(0.96) rotate(1deg); }
    }

    @keyframes orbFloat3 {
        0%, 100% { transform: translate(0, 0) scale(1); }
        50% { transform: translate(20px, -35px) scale(1.08); }
    }

    /* Grid pattern */
    .hero-grid-pattern {
        position: absolute;
        inset: 0;
        z-index: 0;
        opacity: 0.03;
        background-image: 
            linear-gradient(rgba(14, 56, 99, 0.5) 1px, transparent 1px),
            linear-gradient(90deg, rgba(14, 56, 99, 0.5) 1px, transparent 1px);
        background-size: 60px 60px;
        mask-image: radial-gradient(ellipse at center, black 30%, transparent 70%);
        -webkit-mask-image: radial-gradient(ellipse at center, black 30%, transparent 70%);
    }

    .hero-container {
        position: relative;
        z-index: 3;
        width: 100%;
        max-width: 1320px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    /* Status badge */
    .hero-status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.5rem 1.25rem;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 999px;
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text);
        box-shadow: var(--shadow-sm);
        margin-bottom: 2rem;
        transition: all 0.3s ease;
    }

    .hero-status-badge:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-1px);
    }

    .status-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: var(--success);
        box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.2);
        animation: statusPulse 2.5s ease-in-out infinite;
        flex-shrink: 0;
    }

    @keyframes statusPulse {
        0%, 100% { box-shadow: 0 0 0 5px rgba(16, 185, 129, 0.2); }
        50% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0.06); }
    }

    /* Hero typography */
    .hero-title {
        font-size: clamp(2.6rem, 5.5vw, 4.2rem);
        font-weight: 800;
        letter-spacing: -0.035em;
        line-height: 1.06;
        margin-bottom: 1.5rem;
        color: #0f172a;
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
    }

    .hero-title .highlight {
        background: linear-gradient(135deg, #0E3863 0%, #1699A1 50%, #10b981 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        display: inline;
    }

    .hero-description {
        font-size: 1.15rem;
        color: var(--text-secondary);
        line-height: 1.7;
        margin-bottom: 2.5rem;
        max-width: 560px;
    }

    /* Hero buttons */
    .hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.9rem;
        margin-bottom: 2.5rem;
    }

    .btn-hero-primary {
        background: var(--gradient-brand);
        color: white;
        border: none;
        padding: 1rem 2rem;
        border-radius: 14px;
        font-weight: 600;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        box-shadow: 0 8px 28px rgba(14, 56, 99, 0.28);
        transition: all 0.25s ease;
        text-decoration: none;
        position: relative;
        overflow: hidden;
        white-space: nowrap;
    }

    .btn-hero-primary::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0.25) 0%, transparent 60%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .btn-hero-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 36px rgba(14, 56, 99, 0.38);
        color: white;
        text-decoration: none;
    }

    .btn-hero-primary:hover::before {
        opacity: 1;
    }

    .btn-hero-secondary {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1.5px solid var(--border);
        color: var(--brand);
        padding: 1rem 2rem;
        border-radius: 14px;
        font-weight: 600;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        transition: all 0.25s ease;
        text-decoration: none;
        box-shadow: var(--shadow-sm);
        white-space: nowrap;
    }

    .btn-hero-secondary:hover {
        background: white;
        border-color: var(--brand);
        box-shadow: var(--shadow-lg);
        transform: translateY(-2px);
        color: var(--brand);
        text-decoration: none;
    }

    .btn-hero-outline {
        background: transparent;
        border: 1.5px solid var(--border);
        color: var(--text-secondary);
        padding: 1rem 2rem;
        border-radius: 14px;
        font-weight: 600;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        transition: all 0.25s ease;
        text-decoration: none;
        white-space: nowrap;
    }

    .btn-hero-outline:hover {
        border-color: var(--accent);
        color: var(--accent);
        background: rgba(22, 153, 161, 0.04);
        text-decoration: none;
    }

    /* Trust strip */
    .hero-trust-strip {
        background: rgba(255, 255, 255, 0.75);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-radius: var(--radius-lg);
        padding: 1.35rem 2rem;
        box-shadow: var(--shadow-md);
        border: 1px solid rgba(255, 255, 255, 0.8);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1.25rem;
        margin-top: 1.5rem;
        max-width: 580px;
    }

    .trust-item {
        display: flex;
        align-items: center;
        gap: 0.7rem;
        font-size: 0.9rem;
        font-weight: 550;
        color: var(--text-secondary);
    }

    .trust-icon {
        width: 36px;
        height: 36px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .trust-icon.shield { background: rgba(16, 185, 129, 0.12); color: var(--success); }
    .trust-icon.check { background: rgba(31, 199, 183, 0.12); color: var(--accent); }
    .trust-icon.lock { background: rgba(58, 166, 201, 0.08); color: var(--brand); }

    /* Hero visual */
    .hero-visual-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1.5rem;
    }

    .hero-card-preview {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border-radius: var(--radius-xl);
        padding: 2rem;
        box-shadow: var(--shadow-xl);
        border: 1px solid rgba(255, 255, 255, 0.8);
        transition: all 0.3s ease;
        max-width: 300px;
        position: relative;
        z-index: 2;
    }

    .hero-card-preview:hover {
        box-shadow: 0 40px 80px rgba(14, 56, 99, 0.18);
        transform: translateY(-5px);
    }

    .preview-label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--accent);
        margin-bottom: 0.75rem;
    }

    .preview-title {
        font-weight: 750;
        font-size: 1.25rem;
        color: var(--text);
        margin-bottom: 0.75rem;
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
    }

    .preview-text {
        color: var(--text-muted);
        font-size: 0.875rem;
        line-height: 1.65;
        margin: 0;
    }

    .hero-image-card {
        flex-shrink: 0;
        position: relative;
        z-index: 1;
    }

    .hero-image-card img {
        border-radius: var(--radius-xl);
        max-height: 340px;
        width: auto;
        object-fit: cover;
        box-shadow: var(--shadow-xl);
        border: 1px solid rgba(255, 255, 255, 0.6);
        transition: all 0.3s ease;
        background: white;
        padding: 0.5rem;
    }

    .hero-image-card img:hover {
        transform: scale(1.03);
        box-shadow: 0 40px 80px rgba(14, 56, 99, 0.18);
    }

    /* ============================================================
       AGED CARE SECTION
       ============================================================ */
    .aged-care-section {
        background: white;
        position: relative;
    }

    .feature-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem;
    }

    /* ============================================================
       ELIGIBILITY SECTION
       ============================================================ */
    .eligibility-section {
        background: var(--surface-alt);
        position: relative;
    }

    .eligibility-section .section-title {
        font-size: clamp(1.75rem, 3vw, 2.3rem);
        margin-bottom: 0.75rem;
        letter-spacing: -0.035em;
        line-height: 1.18;
    }

    .eligibility-section .section-subtitle {
        font-size: 1rem;
        line-height: 1.6;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
        color: #475569;
    }

    .eligibility-grid {
        gap: 1rem;
    }

    .eligibility-section .card-modern {
        padding: 1.4rem 1.5rem;
        min-height: 100%;
    }

    .eligibility-section .card-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        font-size: 1.25rem;
        margin-bottom: 0.9rem;
    }

    .eligibility-notice {
        background: linear-gradient(135deg, #fff8e1 0%, #fff3cd 100%);
        border: 1px solid rgba(245, 158, 11, 0.2);
        border-radius: var(--radius-lg);
        padding: 1.5rem 2rem;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        margin-top: 2rem;
    }

    .eligibility-notice .notice-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(245, 158, 11, 0.15);
        color: var(--warm);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    /* ============================================================
       BENTO GRID
       ============================================================ */
    .bento-grid {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 1.5rem;
    }

    .eligibility-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1.25rem;
    }

    .eligibility-grid .bento-item {
        grid-column: span 1;
    }

    .bento-item { grid-column: span 4; }
    .bento-item.large { grid-column: span 6; }
    .bento-item.wide { grid-column: span 8; }

    @media (max-width: 991px) {
        .eligibility-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767px) {
        .eligibility-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 991px) {
        .eligibility-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 767px) {
        .eligibility-grid {
            grid-template-columns: 1fr;
        }
    }

    /* ============================================================
       HOW IT WORKS - TIMELINE
       ============================================================ */
    .how-it-works-section {
        background: white;
        position: relative;
    }

    .timeline-wrapper {
        position: relative;
        padding: 2rem 0;
    }

    .timeline-line {
        position: absolute;
        left: 50%;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(180deg, var(--accent) 0%, var(--brand) 50%, var(--accent) 100%);
        transform: translateX(-50%);
        z-index: 0;
        opacity: 0.3;
    }

    .timeline-item {
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: 2rem;
        padding: 1.5rem 0;
        z-index: 1;
    }

    .timeline-item:nth-child(odd) {
        flex-direction: row;
        padding-right: calc(50% + 2rem);
    }

    .timeline-item:nth-child(even) {
        flex-direction: row-reverse;
        padding-left: calc(50% + 2rem);
    }

    .timeline-marker {
        position: absolute;
        left: 50%;
        top: 2rem;
        transform: translate(-50%, 0);
        width: 56px;
        height: 56px;
        border-radius: 50%;
        background: white;
        border: 3px solid var(--accent);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        color: var(--accent);
        z-index: 2;
        box-shadow: var(--shadow-md);
        transition: all 0.3s ease;
    }

    .timeline-item:hover .timeline-marker {
        background: var(--gradient-brand);
        color: white;
        border-color: transparent;
        box-shadow: 0 0 40px rgba(22, 153, 161, 0.3);
        transform: translate(-50%, 0) scale(1.1);
    }

    .timeline-content {
        background: white;
        border: 1px solid var(--border);
        border-radius: var(--radius-lg);
        padding: 1.75rem;
        flex: 1;
        transition: all 0.3s ease;
        box-shadow: var(--shadow-sm);
    }

    .timeline-content:hover {
        box-shadow: var(--shadow-lg);
        border-color: var(--accent);
        transform: translateY(-3px);
    }

    .timeline-step {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--accent);
        margin-bottom: 0.5rem;
    }

    .timeline-title {
        font-weight: 700;
        font-size: 1.15rem;
        margin-bottom: 0.5rem;
        color: var(--text);
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
    }

    .timeline-desc {
        color: var(--text-muted);
        font-size: 0.925rem;
        line-height: 1.6;
        margin: 0;
    }

    /* ============================================================
       MANAGEMENT TABLE
       ============================================================ */
    .management-section {
        background: var(--surface-alt);
        position: relative;
    }

    .management-table-wrapper {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border-radius: var(--radius-lg);
        box-shadow: var(--shadow-lg);
    }

    .management-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: white;
    }

    .management-table thead th {
        background: var(--gradient-brand);
        color: white;
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        font-size: 0.95rem;
        text-align: left;
        font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
    }

    .management-table thead th:first-child {
        border-radius: var(--radius-lg) 0 0 0;
    }

    .management-table thead th:last-child {
        border-radius: 0 var(--radius-lg) 0 0;
    }

    .management-table tbody td {
        padding: 1.1rem 1.5rem;
        border-bottom: 1px solid var(--border);
        font-size: 0.925rem;
        color: var(--text-secondary);
        vertical-align: middle;
    }

    .management-table tbody tr {
        transition: all 0.15s ease;
    }

    .management-table tbody tr:hover {
        background: #e8f2fb;
    }

    .management-table tbody tr:last-child td:first-child {
        border-radius: 0 0 0 var(--radius-lg);
    }

    .management-table tbody tr:last-child td:last-child {
        border-radius: 0 0 var(--radius-lg) 0;
    }

    .table-badge-you {
        background: rgba(22, 153, 161, 0.1);
        color: var(--accent);
        padding: 0.25rem 0.75rem;
        border-radius: 999px;
        font-weight: 600;
        font-size: 0.8rem;
        white-space: nowrap;
    }

    .table-badge-ahhc {
        background: rgba(14, 56, 99, 0.08);
        color: var(--brand);
        padding: 0.25rem 0.75rem;
        border-radius: 999px;
        font-weight: 600;
        font-size: 0.8rem;
        white-space: nowrap;
    }

    .table-badge-empty {
        color: var(--text-muted);
        font-size: 1rem;
    }

    /* ============================================================
       PORTAL PREVIEW DASHBOARD
       ============================================================ */
    .portal-preview-section {
        background: white;
        position: relative;
        overflow: hidden;
    }

    .portal-preview-section::before {
        content: '';
        position: absolute;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(22, 153, 161, 0.08) 0%, transparent 70%);
        border-radius: 50%;
        top: -10%;
        right: -10%;
        pointer-events: none;
    }

    .dashboard-mockup {
        background: white;
        border-radius: var(--radius-xl);
        box-shadow: var(--shadow-xl);
        border: 1px solid var(--border);
        overflow: hidden;
    }

    .dashboard-header {
        background: var(--gradient-brand);
        padding: 1.25rem 1.75rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: white;
    }

    .dashboard-nav-tabs {
        display: flex;
        gap: 0.25rem;
        padding: 0.75rem 1.75rem;
        background: var(--surface-alt);
        border-bottom: 1px solid var(--border);
        flex-wrap: wrap;
    }

    .dash-tab {
        padding: 0.5rem 1rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 600;
        cursor: default;
        transition: all 0.15s ease;
        border: 1px solid transparent;
        background: transparent;
        color: var(--text-muted);
    }

    .dash-tab.active {
        background: white;
        color: var(--accent);
        border-color: var(--border);
        box-shadow: var(--shadow-sm);
    }

    .dashboard-body {
        padding: 1.75rem;
    }

    .feature-pill {
        background: var(--accent-soft);
        color: var(--accent);
        padding: 0.6rem 1rem;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 600;
        text-align: center;
    }

    .portal-feature-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.9rem 1rem;
        border-radius: var(--radius-md);
        background: white;
        border: 1px solid var(--border);
        transition: all 0.2s ease;
        cursor: default;
    }

    .portal-feature-item:hover {
        border-color: var(--accent);
        box-shadow: var(--shadow-sm);
    }

    .portal-feature-icon {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: var(--accent-soft);
        color: var(--accent);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    /* ============================================================
       PRIVACY SECTION
       ============================================================ */
    .privacy-section {
        background: var(--surface-alt);
        position: relative;
    }

    .privacy-card {
        background: white;
        border-radius: var(--radius-xl);
        padding: 2rem;
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        height: 100%;
        transition: all 0.3s ease;
    }

    .privacy-card:hover {
        box-shadow: var(--shadow-xl);
        transform: translateY(-4px);
    }

    .privacy-icon-large {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 1.25rem;
        background: var(--accent-soft);
        color: var(--accent);
    }

    .privacy-icon-large.navy { background: var(--brand-soft); color: var(--brand); }
    .privacy-icon-large.success { background: rgba(16, 185, 129, 0.1); color: var(--success); }

    /* ============================================================
       CONTACT SECTION
       ============================================================ */
    .contact-section {
        background: linear-gradient(160deg, #0a2540 0%, #0E3863 40%, #1699A1 100%);
        color: white;
        position: relative;
        overflow: hidden;
    }

    .contact-section::before {
        content: '';
        position: absolute;
        top: -30%;
        right: -15%;
        width: 700px;
        height: 700px;
        background: radial-gradient(circle, rgba(255,255,255,0.06) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .contact-section::after {
        content: '';
        position: absolute;
        bottom: -20%;
        left: -10%;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(22, 153, 161, 0.2) 0%, transparent 70%);
        border-radius: 50%;
        pointer-events: none;
    }

    .contact-info-card {
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: var(--radius-xl);
        padding: 2rem;
        color: white;
    }

    .contact-form-card {
        background: white;
        border-radius: var(--radius-xl);
        padding: 2.5rem;
        box-shadow: var(--shadow-xl);
        color: var(--text);
    }

    .form-label-custom {
        font-weight: 600;
        font-size: 0.9rem;
        color: var(--text);
        margin-bottom: 0.4rem;
        display: block;
    }

    .form-input-custom,
    .form-select-custom,
    .form-textarea-custom {
        width: 100%;
        padding: 0.9rem 1.2rem;
        border: 1.5px solid var(--border);
        border-radius: 14px;
        font-size: 0.95rem;
        font-family: 'Inter', system-ui, sans-serif;
        background: var(--surface-alt);
        transition: all 0.15s ease;
        color: var(--text);
    }

    .form-input-custom:focus,
    .form-select-custom:focus,
    .form-textarea-custom:focus {
        outline: none;
        border-color: var(--accent);
        background: white;
        box-shadow: 0 0 0 4px rgba(22, 153, 161, 0.1);
    }

    .form-textarea-custom {
        resize: vertical;
        min-height: 120px;
    }

    .form-select-custom {
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        padding-right: 2.5rem;
    }

    .form-check-custom {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        cursor: pointer;
    }

    .form-check-custom input[type="checkbox"] {
        margin-top: 0.2rem;
        width: 18px;
        height: 18px;
        accent-color: var(--accent);
        flex-shrink: 0;
    }

    .invalid-feedback-custom {
        color: #ef4444;
        font-size: 0.8rem;
        margin-top: 0.3rem;
    }

    /* ============================================================
       RESPONSIVE
       ============================================================ */
    @media (max-width: 1200px) {
        .hero-visual-wrapper {
            flex-direction: column;
            align-items: center;
        }
        .hero-card-preview {
            max-width: 100%;
        }
    }

    @media (max-width: 991px) {
        .hero-section {
            padding: 6rem 0 4rem;
            min-height: auto;
        }
        .hero-title {
            font-size: 2.4rem;
        }
        .hero-actions {
            flex-direction: column;
        }
        .btn-hero-primary,
        .btn-hero-secondary,
        .btn-hero-outline {
            width: 100%;
            justify-content: center;
        }
        .hero-trust-strip {
            flex-direction: column;
            align-items: flex-start;
            max-width: 100%;
        }
        .hero-image-card img {
            max-height: 250px;
        }
        .timeline-line {
            left: 28px;
        }
        .timeline-item,
        .timeline-item:nth-child(odd),
        .timeline-item:nth-child(even) {
            flex-direction: row;
            padding-left: 5rem;
            padding-right: 0;
        }
        .timeline-marker {
            left: 28px;
        }
        .bento-item,
        .bento-item.large,
        .bento-item.wide {
            grid-column: span 6;
        }
        .feature-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }

    @media (max-width: 767px) {
        .hero-section {
            padding: 5rem 0 3rem;
        }
        .hero-title {
            font-size: 2rem;
        }
        .hero-description {
            font-size: 1rem;
        }
        .hero-orb {
            display: none;
        }
        .bento-item,
        .bento-item.large,
        .bento-item.wide {
            grid-column: span 12;
        }
        .contact-form-card,
        .contact-info-card {
            padding: 1.5rem;
        }
        .privacy-card {
            padding: 1.5rem;
        }
        .dashboard-nav-tabs {
            gap: 0;
        }
        .dash-tab {
            font-size: 0.7rem;
            padding: 0.4rem 0.7rem;
        }
    }
</style>

{{-- 
    ALLEGIANCE HEART & HOME CARE — HERO SECTION
    Design Ethos: Premium, warm, and human-centered. 
    Beautiful modern aesthetic with smooth animations and a professional aged care tone.
--}}

<section id="home" class="ahhc-hero" aria-labelledby="hero-heading">
    {{-- Subtle animated background --}}
    <div class="ahhc-hero-bg">
        <div class="ahhc-hero-gradient"></div>
        <div class="ahhc-hero-grid"></div>
        <div class="ahhc-hero-blur ahhc-hero-blur--1" aria-hidden="true"></div>
        <div class="ahhc-hero-blur ahhc-hero-blur--2" aria-hidden="true"></div>
        <div class="ahhc-hero-blur ahhc-hero-blur--3" aria-hidden="true"></div>
    </div>

    <div class="ahhc-hero-container">
        {{-- Content Side --}}
        <div class="ahhc-hero-content">
            <div class="ahhc-hero-badge">
                <span class="ahhc-hero-badge-dot"></span>
                <span>Now accepting new participants</span>
            </div>
            
            <h1 id="hero-heading" class="ahhc-hero-title">
                Self-Management <span class="ahhc-hero-title-highlight">Support</span>
            </h1>
            
            <p class="ahhc-hero-subtitle">
                More choice and control, with provider oversight.
            </p>
            
            <p class="ahhc-hero-description">
                {{ $portalSettings['website_description'] ?? 'Flexible, secure support for approved Support at Home participants and their authorised representatives. Manage workers, suppliers, invoices, approvals, documents and service evidence in one secure portal, with oversight from Allegiance Heart & Home Care.' }}
            </p>

            <div class="ahhc-hero-actions">
                <a href="#contact" class="ahhc-btn ahhc-btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="12" y1="18" x2="12" y2="12"/>
                        <line x1="9" y1="15" x2="15" y2="15"/>
                    </svg>
                    Apply for Self-Management Support
                </a>
                <a href="/portal" class="ahhc-btn ahhc-btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                        <polyline points="10 17 15 12 10 7"/>
                        <line x1="15" y1="12" x2="3" y2="12"/>
                    </svg>
                    Login to Self-Management Portal
                </a>
                <a href="#contact" class="ahhc-btn ahhc-btn-outline">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                    </svg>
                    Talk to Allegiance Heart &amp; Home Care
                </a>
            </div>

            <div class="ahhc-hero-trust-strip">
                <div class="ahhc-hero-trust-item"><i class="bi bi-shield-check"></i> Secure</div>
                <div class="ahhc-hero-trust-item"><i class="bi bi-patch-check"></i> Compliance-supported</div>
                <div class="ahhc-hero-trust-item"><i class="bi bi-lock-fill"></i> MFA-protected</div>
            </div>
        </div>

        {{-- Illustration Side --}}
        <div class="ahhc-hero-visual">
            <div class="ahhc-hero-visual-card">
                <span class="ahhc-hero-visual-card-pill">Secure Portal Preview</span>
                <h3>Manage your self-management support with confidence</h3>
                <p>Keep approvals, budgets, invoices, documents and service evidence in one secure place designed for self-management support.</p>
            </div>
            <div class="ahhc-illustration-wrapper">
                {{-- Main Illustration SVG --}}
                <svg class="ahhc-illustration" viewBox="0 0 520 440" xmlns="http://www.w3.org/2000/svg" aria-label="Self-management support illustration showing a connected ecosystem of care: budget tracking, worker management, scheduling, and security">
                    
                    {{-- Gradient Definitions --}}
                    <defs>
                        <linearGradient id="blueGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#0a4b6e"/>
                            <stop offset="100%" stop-color="#004d7a"/>
                        </linearGradient>
                        <linearGradient id="greenGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#059669"/>
                            <stop offset="100%" stop-color="#10b981"/>
                        </linearGradient>
                        <linearGradient id="amberGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#d97706"/>
                            <stop offset="100%" stop-color="#f59e0b"/>
                        </linearGradient>
                        <linearGradient id="purpleGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#7c3aed"/>
                            <stop offset="100%" stop-color="#8b5cf6"/>
                        </linearGradient>
                        <linearGradient id="skyGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#0284c7"/>
                            <stop offset="100%" stop-color="#0ea5e9"/>
                        </linearGradient>
                        <filter id="softShadow" x="-20%" y="-20%" width="140%" height="140%">
                            <feDropShadow dx="0" dy="6" stdDeviation="8" flood-color="#0a4b6e" flood-opacity="0.12"/>
                        </filter>
                    </defs>

                    {{-- Central Hub Ring --}}
                    <circle cx="260" cy="210" r="155" fill="none" stroke="url(#blueGrad)" stroke-width="1.5" opacity="0.12" stroke-dasharray="8 6">
                        <animateTransform attributeName="transform" type="rotate" from="0 260 210" to="360 260 210" dur="40s" repeatCount="indefinite"/>
                    </circle>
                    <circle cx="260" cy="210" r="130" fill="none" stroke="url(#blueGrad)" stroke-width="1" opacity="0.08" stroke-dasharray="4 8">
                        <animateTransform attributeName="transform" type="rotate" from="360 260 210" to="0 260 210" dur="30s" repeatCount="indefinite"/>
                    </circle>

                    {{-- Central Person / Care Recipient --}}
                    <g transform="translate(227, 160)" filter="url(#softShadow)">
                        {{-- Body --}}
                        <path d="M10 130 Q43 105 76 130 L76 155 L10 155 Z" fill="url(#blueGrad)" opacity="0.95"/>
                        {{-- Head --}}
                        <circle cx="43" cy="35" r="32" fill="url(#blueGrad)" opacity="0.95"/>
                        {{-- Face detail --}}
                        <path d="M28 42 Q35 52 42 48" stroke="#ffffff" stroke-width="2.5" fill="none" stroke-linecap="round" opacity="0.7"/>
                        <circle cx="31" cy="33" r="3" fill="#ffffff" opacity="0.5"/>
                        <circle cx="55" cy="33" r="3" fill="#ffffff" opacity="0.5"/>
                    </g>

                    {{-- Budget Card --}}
                    <g transform="translate(385, 60)" filter="url(#softShadow)">
                        <rect x="0" y="0" width="80" height="100" rx="14" fill="#ffffff" stroke="#e9eef3" stroke-width="1.5"/>
                        <rect x="18" y="14" width="44" height="44" rx="10" fill="url(#greenGrad)" opacity="0.95"/>
                        <circle cx="40" cy="36" r="10" stroke="#ffffff" stroke-width="2.5" fill="none"/>
                        <path d="M40 28 L40 32 M40 40 L40 44 M32 34 L34 36 M46 36 L48 34 M32 38 L34 36 M46 36 L48 38" stroke="#ffffff" stroke-width="1.8" stroke-linecap="round"/>
                        <text x="40" y="78" text-anchor="middle" font-family="'Inter', system-ui, sans-serif" font-size="11" fill="#1e293b" font-weight="700">Budget</text>
                        <text x="40" y="92" text-anchor="middle" font-family="'Inter', system-ui, sans-serif" font-size="9" fill="#64748b" font-weight="500">Live Budget Tracking</text>
                    </g>

                    {{-- Workers Card --}}
                    <g transform="translate(50, 55)" filter="url(#softShadow)">
                        <rect x="0" y="0" width="80" height="100" rx="14" fill="#ffffff" stroke="#e9eef3" stroke-width="1.5"/>
                        <rect x="18" y="14" width="44" height="44" rx="10" fill="url(#skyGrad)" opacity="0.95"/>
                        {{-- Two people icon --}}
                        <circle cx="32" cy="32" r="7" stroke="#ffffff" stroke-width="2" fill="none"/>
                        <circle cx="48" cy="32" r="7" stroke="#ffffff" stroke-width="2" fill="none"/>
                        <path d="M24 46 Q32 38 40 46" stroke="#ffffff" stroke-width="2" fill="none" stroke-linecap="round"/>
                        <path d="M40 46 Q48 38 56 46" stroke="#ffffff" stroke-width="2" fill="none" stroke-linecap="round"/>
                        <text x="40" y="78" text-anchor="middle" font-family="'Inter', system-ui, sans-serif" font-size="11" fill="#1e293b" font-weight="700">Workers and Suppliers</text>
                        <text x="40" y="92" text-anchor="middle" font-family="'Inter', system-ui, sans-serif" font-size="9" fill="#64748b" font-weight="500">Your Choice, Subject to Approval</text>
                    </g>

                    {{-- Schedule Card --}}
                    <g transform="translate(385, 260)" filter="url(#softShadow)">
                        <rect x="0" y="0" width="80" height="100" rx="14" fill="#ffffff" stroke="#e9eef3" stroke-width="1.5"/>
                        <rect x="18" y="14" width="44" height="44" rx="10" fill="url(#amberGrad)" opacity="0.95"/>
                        <rect x="24" y="24" width="32" height="24" rx="4" stroke="#ffffff" stroke-width="1.8" fill="none"/>
                        <line x1="24" y1="32" x2="56" y2="32" stroke="#ffffff" stroke-width="1.5"/>
                        <line x1="36" y1="24" x2="36" y2="20" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round"/>
                        <line x1="44" y1="24" x2="44" y2="20" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round"/>
                        <circle cx="40" cy="40" r="3" fill="#ffffff"/>
                        <text x="40" y="78" text-anchor="middle" font-family="'Inter', system-ui, sans-serif" font-size="11" fill="#1e293b" font-weight="700">Service Planning</text>
                        <text x="40" y="92" text-anchor="middle" font-family="'Inter', system-ui, sans-serif" font-size="9" fill="#64748b" font-weight="500">Plan services and requests ahead</text>
                    </g>

                    {{-- Security Card --}}
                    <g transform="translate(50, 270)" filter="url(#softShadow)">
                        <rect x="0" y="0" width="80" height="100" rx="14" fill="#ffffff" stroke="#e9eef3" stroke-width="1.5"/>
                        <rect x="18" y="14" width="44" height="44" rx="10" fill="url(#purpleGrad)" opacity="0.95"/>
                        <path d="M40 22 L52 30 L52 42 Q52 54 40 60 Q28 54 28 42 L28 30 Z" stroke="#ffffff" stroke-width="2.5" fill="none"/>
                        <path d="M34 42 L38 46 L46 36" stroke="#ffffff" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
                        <text x="40" y="78" text-anchor="middle" font-family="'Inter', system-ui, sans-serif" font-size="11" fill="#1e293b" font-weight="700">Secure</text>
                        <text x="40" y="92" text-anchor="middle" font-family="'Inter', system-ui, sans-serif" font-size="9" fill="#64748b" font-weight="500">Allegiance Heart &amp; Home Care Oversight</text>
                    </g>

                    {{-- Connection Lines --}}
                    <g opacity="0.25">
                        <path d="M283 195 Q340 130 395 105" stroke="#0a4b6e" stroke-width="1.8" stroke-dasharray="6 4" fill="none"/>
                        <path d="M237 195 Q180 130 125 105" stroke="#0a4b6e" stroke-width="1.8" stroke-dasharray="6 4" fill="none"/>
                        <path d="M283 225 Q340 290 395 305" stroke="#0a4b6e" stroke-width="1.8" stroke-dasharray="6 4" fill="none"/>
                        <path d="M237 225 Q180 290 125 315" stroke="#0a4b6e" stroke-width="1.8" stroke-dasharray="6 4" fill="none"/>
                    </g>

                    {{-- Connection Dots --}}
                    <circle cx="283" cy="195" r="5" fill="#0a4b6e" opacity="0.4">
                        <animate attributeName="r" values="5;7;5" dur="2s" repeatCount="indefinite"/>
                        <animate attributeName="opacity" values="0.4;0.7;0.4" dur="2s" repeatCount="indefinite"/>
                    </circle>
                    <circle cx="237" cy="195" r="5" fill="#0a4b6e" opacity="0.4">
                        <animate attributeName="r" values="5;7;5" dur="2s" begin="0.5s" repeatCount="indefinite"/>
                        <animate attributeName="opacity" values="0.4;0.7;0.4" dur="2s" begin="0.5s" repeatCount="indefinite"/>
                    </circle>
                    <circle cx="283" cy="225" r="5" fill="#0a4b6e" opacity="0.4">
                        <animate attributeName="r" values="5;7;5" dur="2s" begin="1s" repeatCount="indefinite"/>
                        <animate attributeName="opacity" values="0.4;0.7;0.4" dur="2s" begin="1s" repeatCount="indefinite"/>
                    </circle>
                    <circle cx="237" cy="225" r="5" fill="#0a4b6e" opacity="0.4">
                        <animate attributeName="r" values="5;7;5" dur="2s" begin="1.5s" repeatCount="indefinite"/>
                        <animate attributeName="opacity" values="0.4;0.7;0.4" dur="2s" begin="1.5s" repeatCount="indefinite"/>
                    </circle>

                    {{-- Floating Accent Dots --}}
                    <circle cx="350" cy="30" r="4" fill="#10b981" opacity="0.5">
                        <animate attributeName="cy" values="30;22;30" dur="4s" repeatCount="indefinite"/>
                        <animate attributeName="opacity" values="0.5;0.8;0.5" dur="4s" repeatCount="indefinite"/>
                    </circle>
                    <circle cx="30" cy="380" r="3" fill="#8b5cf6" opacity="0.5">
                        <animate attributeName="cy" values="380;372;380" dur="3.5s" repeatCount="indefinite"/>
                        <animate attributeName="opacity" values="0.5;0.8;0.5" dur="3.5s" repeatCount="indefinite"/>
                    </circle>
                    <circle cx="460" cy="180" r="5" fill="#0ea5e9" opacity="0.4">
                        <animate attributeName="cy" values="180;172;180" dur="5s" repeatCount="indefinite"/>
                        <animate attributeName="opacity" values="0.4;0.7;0.4" dur="5s" repeatCount="indefinite"/>
                    </circle>
                </svg>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const heroVisual = document.querySelector('.ahhc-hero-visual');
        const illustration = heroVisual?.querySelector('.ahhc-illustration');
        const visualCard = heroVisual?.querySelector('.ahhc-hero-visual-card');

        if (heroVisual && illustration && visualCard) {
            heroVisual.addEventListener('pointermove', function (event) {
                const rect = heroVisual.getBoundingClientRect();
                const x = (event.clientX - rect.left) / rect.width;
                const y = (event.clientY - rect.top) / rect.height;
                const rotateY = ((x - 0.5) * 8).toFixed(2);
                const rotateX = ((0.5 - y) * 8).toFixed(2);
                const cardX = ((x - 0.5) * 10).toFixed(2);
                const cardY = ((y - 0.5) * 10).toFixed(2);

                heroVisual.classList.add('is-interacting');
                heroVisual.style.setProperty('--hero-rotate-x', `${rotateX}deg`);
                heroVisual.style.setProperty('--hero-rotate-y', `${rotateY}deg`);
                heroVisual.style.setProperty('--hero-card-x', `${cardX}px`);
                heroVisual.style.setProperty('--hero-card-y', `${cardY}px`);
            });

            heroVisual.addEventListener('pointerleave', function () {
                heroVisual.classList.remove('is-interacting');
                heroVisual.style.removeProperty('--hero-rotate-x');
                heroVisual.style.removeProperty('--hero-rotate-y');
                heroVisual.style.removeProperty('--hero-card-x');
                heroVisual.style.removeProperty('--hero-card-y');
            });
        }

        const animatedSections = document.querySelectorAll('.fade-up');
        if (!animatedSections.length) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.15,
            rootMargin: '0px 0px -60px 0px'
        });

        animatedSections.forEach((section) => observer.observe(section));
    });
</script>

<style>
    /* ============================================
       ALLEGIANCE HEART & HOME CARE — HERO STYLES
       Premium, modern, accessible, and warm.
       ============================================ */
    
    .ahhc-hero {
        position: relative;
        min-height: 100vh;
        display: flex;
        align-items: center;
        background:
            radial-gradient(circle at top left, rgba(31, 199, 183, 0.18), transparent 28%),
            radial-gradient(circle at 82% 18%, rgba(58, 166, 201, 0.18), transparent 24%),
            radial-gradient(circle at 22% 92%, rgba(239, 46, 53, 0.09), transparent 22%),
            linear-gradient(135deg, var(--surface-alt) 0%, rgba(240, 253, 255, 0.95) 48%, var(--surface) 100%);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        color: #1e293b;
        padding: 72px 0 64px;
        overflow: hidden;
        isolation: isolate;
    }

    /* ============ BACKGROUND ============ */
    .ahhc-hero-bg {
        position: absolute;
        inset: 0;
        pointer-events: none;
        z-index: 0;
    }

    .ahhc-hero-gradient {
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse 70% 50% at 20% 35%, rgba(10, 75, 110, 0.08) 0%, transparent 60%),
            radial-gradient(ellipse 60% 55% at 80% 25%, rgba(16, 185, 129, 0.08) 0%, transparent 55%),
            radial-gradient(ellipse 60% 45% at 50% 90%, rgba(14, 165, 233, 0.08) 0%, transparent 55%);
    }

    .ahhc-hero-grid {
        position: absolute;
        inset: 0;
        background-image: 
            linear-gradient(rgba(10, 75, 110, 0.025) 1px, transparent 1px),
            linear-gradient(90deg, rgba(10, 75, 110, 0.025) 1px, transparent 1px);
        background-size: 64px 64px;
        mask-image: radial-gradient(ellipse 70% 60% at 50% 50%, black 25%, transparent 75%);
    }

    .ahhc-hero-blur {
        position: absolute;
        border-radius: 50%;
        filter: blur(100px);
    }

    .ahhc-hero-blur--1 {
        width: 400px;
        height: 400px;
        background: rgba(10, 75, 110, 0.06);
        top: -100px;
        left: -80px;
        animation: blurFloat 8s ease-in-out infinite;
    }

    .ahhc-hero-blur--2 {
        width: 350px;
        height: 350px;
        background: rgba(16, 185, 129, 0.04);
        bottom: -100px;
        right: -60px;
        animation: blurFloat 10s ease-in-out infinite 1s;
    }

    .ahhc-hero-blur--3 {
        width: 300px;
        height: 300px;
        background: rgba(139, 92, 246, 0.04);
        top: 50%;
        left: 50%;
        animation: blurFloat 9s ease-in-out infinite 2s;
    }

    @keyframes blurFloat {
        0%, 100% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(20px, -20px) scale(1.05); }
        66% { transform: translate(-15px, 15px) scale(0.95); }
    }

    /* ============ CONTAINER ============ */
    .ahhc-hero-container {
        position: relative;
        z-index: 1;
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 32px;
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(320px, 0.95fr);
        gap: 40px;
        align-items: center;
        width: 100%;
    }

    /* ============ CONTENT ============ */
    .ahhc-hero-content {
        display: flex;
        flex-direction: column;
        gap: 16px;
        max-width: 620px;
        animation: fadeInUp 0.8s ease both;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Badge */
    .ahhc-hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(31, 199, 183, 0.14);
        color: #245E9E;
        padding: 8px 18px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        letter-spacing: 0.03em;
        width: fit-content;
        border: 1px solid rgba(10, 75, 110, 0.1);
        backdrop-filter: blur(10px);
    }

    .ahhc-hero-badge-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #0a4b6e;
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.4; transform: scale(0.8); }
    }

    /* Title */
    .ahhc-hero-title {
        font-size: clamp(2rem, 3.2vw, 3rem);
        font-weight: 800;
        color: #0f172a;
        line-height: 1.15;
        letter-spacing: -0.03em;
        margin: 0;
        max-width: 620px;
    }

    .ahhc-hero-title-highlight {
        position: relative;
        color: #0a4b6e;
        white-space: nowrap;
    }

    .ahhc-hero-title-highlight::after {
        content: '';
        position: absolute;
        bottom: 2px;
        left: 0;
        width: 100%;
        height: 10px;
        background: rgba(10, 75, 110, 0.1);
        border-radius: 5px;
        z-index: -1;
    }

    /* Subtitle */
    .ahhc-hero-subtitle {
        font-size: 1.2rem;
        font-weight: 600;
        color: #0a4b6e;
        margin: 0;
        line-height: 1.4;
        letter-spacing: -0.01em;
    }

    /* Description */
    .ahhc-hero-description {
        font-size: 1rem;
        color: #475569;
        line-height: 1.7;
        margin: 0;
        max-width: 560px;
    }

    /* Actions */
    .ahhc-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 6px;
    }

    .ahhc-hero-trust-strip {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 6px;
    }

    .ahhc-hero-trust-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(255, 255, 255, 0.82);
        color: #334155;
        padding: 9px 12px;
        border-radius: 999px;
        border: 1px solid rgba(10, 75, 110, 0.08);
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
        backdrop-filter: blur(8px);
        font-size: 0.9rem;
        font-weight: 600;
    }

    .ahhc-hero-trust-item i {
        color: #0a4b6e;
    }

    .ahhc-hero-visual {
        position: relative;
        display: flex;
        flex-direction: column;
        gap: 14px;
        align-items: center;
    }

    .ahhc-hero-visual-card {
        position: static;
        z-index: 2;
        width: min(100%, 340px);
        margin: 0 auto;
        padding: 16px 18px;
        background: rgba(255, 255, 255, 0.94);
        border: 1px solid rgba(10, 75, 110, 0.1);
        border-radius: 18px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.08);
        backdrop-filter: blur(10px);
        animation: fadeInUp 0.9s ease both;
    }

    .ahhc-hero-visual-card-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        font-size: 0.78rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #0a4b6e;
    }

    .ahhc-hero-visual-card h3 {
        margin: 0 0 6px;
        font-size: 1.05rem;
        color: #0f172a;
    }

    .ahhc-hero-visual-card p {
        margin: 0;
        font-size: 0.95rem;
        line-height: 1.6;
        color: #475569;
    }

    /* ============ BUTTONS ============ */
    .ahhc-hero-actions .ahhc-btn {
        padding: 13px 20px;
        min-width: 170px;
        justify-content: center;
        font-size: 0.95rem;
        font-weight: 600;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 10px;
        letter-spacing: 0.01em;
        line-height: 1.4;
        position: relative;
        overflow: hidden;
    }

    .ahhc-hero-actions .ahhc-btn::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .ahhc-hero-actions .ahhc-btn:active::after {
        opacity: 1;
    }

    .ahhc-hero-actions .ahhc-btn-primary {
        background: #0a4b6e;
        color: #ffffff;
        box-shadow: 0 4px 16px rgba(10, 75, 110, 0.3), 0 1px 3px rgba(10, 75, 110, 0.2);
    }

    .ahhc-hero-actions .ahhc-btn-primary:hover {
        background: #083d59;
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(10, 75, 110, 0.35), 0 2px 6px rgba(10, 75, 110, 0.2);
    }

    .ahhc-hero-actions .ahhc-btn-primary:active {
        transform: translateY(-1px);
        box-shadow: 0 3px 10px rgba(10, 75, 110, 0.25);
    }

    .ahhc-hero-actions .ahhc-btn-primary::after {
        background: rgba(255, 255, 255, 0.1);
    }

    .ahhc-hero-actions .ahhc-btn-secondary {
        background: #ffffff;
        color: #0a4b6e;
        border: 2px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    }

    .ahhc-hero-actions .ahhc-btn-secondary:hover {
        border-color: #0a4b6e;
        background: #f8fafc;
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(10, 75, 110, 0.1);
    }

    .ahhc-hero-actions .ahhc-btn-secondary:active {
        transform: translateY(-1px);
    }

    .ahhc-hero-actions .ahhc-btn-secondary::after {
        background: rgba(10, 75, 110, 0.04);
    }

    .ahhc-hero-actions .ahhc-btn-outline {
        background: transparent;
        color: #475569;
        border: 2px solid #e2e8f0;
    }

    .ahhc-hero-actions .ahhc-btn-outline:hover {
        border-color: #0a4b6e;
        color: #0a4b6e;
        background: rgba(10, 75, 110, 0.03);
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(10, 75, 110, 0.06);
    }

    .ahhc-hero-actions .ahhc-btn-outline:active {
        transform: translateY(-1px);
    }

    .ahhc-hero-actions .ahhc-btn-outline::after {
        background: rgba(10, 75, 110, 0.03);
    }

    /* ============ VISUAL / ILLUSTRATION ============ */
    .ahhc-hero-visual {
        animation: fadeInUp 0.8s ease 0.2s both;
    }

    .ahhc-illustration-wrapper {
        position: relative;
        max-width: 480px;
        margin: 0 auto;
        padding: 0;
        width: 100%;
    }

    .ahhc-illustration {
        width: 100%;
        height: auto;
        display: block;
        filter: drop-shadow(0 20px 50px rgba(10, 75, 110, 0.12));
        transition: transform 0.25s ease-out;
        transform: perspective(1000px) rotateX(0deg) rotateY(0deg) scale(1);
    }

    .ahhc-hero-visual.is-interacting .ahhc-illustration {
        transform: perspective(1000px) rotateX(var(--hero-rotate-x, 0deg)) rotateY(var(--hero-rotate-y, 0deg)) scale(1.02);
    }

    .ahhc-hero-visual.is-interacting .ahhc-hero-visual-card {
        transform: translate3d(var(--hero-card-x, 0px), var(--hero-card-y, 0px), 0);
    }

    .fade-up {
        opacity: 0;
        transform: translateY(24px);
        transition: opacity 0.7s ease, transform 0.7s ease;
    }

    .fade-up.is-visible {
        opacity: 1;
        transform: translateY(0);
    }

    /* ============ FOCUS STATES ============ */
    .ahhc-btn:focus-visible {
        outline: 3px solid #0a4b6e;
        outline-offset: 3px;
        box-shadow: 0 0 0 6px rgba(10, 75, 110, 0.15);
    }

    /* ============ RESPONSIVE ============ */
    @media (max-width: 1024px) {
        .ahhc-hero-container {
            gap: 48px;
            padding: 0 24px;
        }

        .ahhc-hero-title {
            font-size: clamp(2.4rem, 5vw, 3.2rem);
        }
    }

    @media (max-width: 860px) {
        .ahhc-hero {
            min-height: auto;
            padding: 72px 0 56px;
        }

        .ahhc-hero-container {
            grid-template-columns: 1fr;
            gap: 32px;
            text-align: center;
        }

        .ahhc-hero-content {
            align-items: center;
            order: 0;
            max-width: 100%;
        }

        .ahhc-hero-description {
            max-width: 100%;
        }

        .ahhc-hero-actions {
            justify-content: center;
            gap: 16px;
            margin-top: 18px;
        }

        .ahhc-hero-trust-strip {
            justify-content: center;
            margin-top: 22px;
        }

        .ahhc-hero-visual {
            order: 1;
            max-width: 440px;
            margin: 0 auto;
            width: 100%;
        }

        .ahhc-hero-title-highlight::after {
            bottom: 0;
            height: 8px;
        }
    }

    @media (max-width: 480px) {
        .ahhc-hero {
            padding: 56px 0 44px;
        }

        .ahhc-hero-container {
            padding: 0 16px;
            gap: 24px;
        }

        .ahhc-hero-title {
            font-size: 1.8rem;
        }

        .ahhc-hero-subtitle {
            font-size: 1.05rem;
        }

        .ahhc-hero-description {
            font-size: 0.95rem;
        }

        .ahhc-hero-actions {
            flex-direction: column;
            width: 100%;
        }

        .ahhc-hero-actions .ahhc-btn {
            width: 100%;
            min-width: auto;
            justify-content: center;
            text-align: center;
            padding: 15px 20px;
            font-size: 0.97rem;
            min-height: 52px;
        }

        .ahhc-hero-visual {
            max-width: 340px;
            width: 100%;
        }

        .ahhc-hero-trust-strip {
            flex-direction: column;
            align-items: center;
        }
    }

    /* ============ REDUCED MOTION ============ */
    @media (prefers-reduced-motion: reduce) {
        .ahhc-hero *,
        .ahhc-hero *::before,
        .ahhc-hero *::after,
        .fade-up {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            transform: none !important;
        }

        .fade-up {
            opacity: 1;
        }
    }
</style>

<!-- ============================================================
     AGED CARE SECTION
     ============================================================ -->
<section id="support-at-home" class="aged-care-section section-padding">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-5 fade-up">
                <span class="section-eyebrow">
                    <i class="bi bi-heart-pulse"></i> Aged Care
                </span>
                <h2 class="section-title">Self-management support designed for modern aged care.</h2>
                <p class="section-subtitle">
                    Allegiance Heart &amp; Home Care helps approved participants maintain independence and choice while remaining supported by experienced care management, compliance oversight and secure digital tools.
                </p>
                <a href="#eligibility" class="btn-secondary-custom mt-3">
                    Learn More About Self-Management Support <i class="bi bi-arrow-right ms-2"></i>
                </a>
            </div>
            <div class="col-lg-7 fade-up">
                <div class="feature-grid">
                    <div class="card-modern">
                        <div class="card-icon"><i class="bi bi-people-fill"></i></div>
                        <h5 style="font-weight: 700; margin-bottom: 0.5rem;">Participant Choice</h5>
                        <p class="text-muted-custom mb-0">Choose preferred workers, suppliers and services, with review and oversight from Allegiance Heart &amp; Home Care.</p>
                    </div>
                    <div class="card-modern">
                        <div class="card-icon accent"><i class="bi bi-shield-check"></i></div>
                        <h5 style="font-weight: 700; margin-bottom: 0.5rem;">Compliance Support</h5>
                        <p class="text-muted-custom mb-0">Allegiance Heart &amp; Home Care supports required approvals, documentation checks, worker compliance and quality monitoring.</p>
                    </div>
                    <div class="card-modern">
                        <div class="card-icon warm"><i class="bi bi-wallet2"></i></div>
                        <h5 style="font-weight: 700; margin-bottom: 0.5rem;">Service Planning and Approvals</h5>
                        <p class="text-muted-custom mb-0">Track service requests, approvals, documents and updates through the secure portal.</p>
                    </div>
                    <div class="card-modern">
                        <div class="card-icon"><i class="bi bi-calendar-check"></i></div>
                        <h5 style="font-weight: 700; margin-bottom: 0.5rem;">Ongoing Review</h5>
                        <p class="text-muted-custom mb-0">Allegiance Heart &amp; Home Care completes regular reviews to monitor care arrangements, budget use, documentation, quality and safety.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     ELIGIBILITY / WHO IT IS FOR
     ============================================================ -->
<section id="who-its-for" class="eligibility-section section-padding">
    <div class="container">
        <div class="text-center mb-5 fade-up">
            <span class="section-eyebrow">
                <i class="bi bi-patch-check"></i> Eligibility and Requirements
            </span>
            <h2 class="section-title">Who is Self-Management Support for?</h2>
            <p class="section-subtitle mx-auto">
                Self-management support may suit Support at Home participants who want greater choice and control over parts of their care and services, while remaining supported by Allegiance Heart &amp; Home Care’s provider oversight, care management and compliance checks. Suitability review and approval by Allegiance Heart &amp; Home Care are required before portal access is created.
            </p>
        </div>

        <div class="bento-grid eligibility-grid mb-4">
            <div class="bento-item fade-up">
                <div class="card-modern">
                    <div class="card-icon"><i class="bi bi-clipboard-check"></i></div>
                    <h5 style="font-weight: 700;">Suitability Review Required</h5>
                    <p class="text-muted-custom mb-0">Allegiance Heart &amp; Home Care reviews each enquiry before approval. Self-management support may not be suitable for everyone.</p>
                </div>
            </div>
            <div class="bento-item fade-up">
                <div class="card-modern">
                    <div class="card-icon accent"><i class="bi bi-check-circle"></i></div>
                    <h5 style="font-weight: 700;">Approval Required</h5>
                    <p class="text-muted-custom mb-0">Portal access is created only after Allegiance Heart &amp; Home Care confirms that self-management support is suitable and the required agreement, consent and setup steps have been completed.</p>
                </div>
            </div>
            <div class="bento-item fade-up">
                <div class="card-modern">
                    <div class="card-icon warm"><i class="bi bi-person-plus"></i></div>
                    <h5 style="font-weight: 700;">No Automatic Account</h5>
                    <p class="text-muted-custom mb-0">Submitting an enquiry does not create a portal account. Allegiance Heart &amp; Home Care will review your enquiry and contact you about the next steps.</p>
                </div>
            </div>
        </div>

        <div class="eligibility-notice fade-up">
            <div class="notice-icon">
                <i class="bi bi-info-circle-fill"></i>
            </div>
            <div>
                <strong style="color: #92400e;">Important:</strong>
                <span style="color: #92400e; font-size: 0.95rem;">
                    Important: Self-management support may not be suitable for everyone. Allegiance Heart &amp; Home Care reviews each enquiry individually. Only approved participants or authorised representatives receive portal access. The enquiry form on this page does not create an account automatically.
                </span>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     HOW IT WORKS - TIMELINE
     ============================================================ -->
<section id="how-self-management-works" class="how-it-works-section section-padding">
    <div class="container">
        <div class="text-center mb-5 fade-up">
            <span class="section-eyebrow">
                <i class="bi bi-diagram-3"></i> Step-by-Step Process
            </span>
            <h2 class="section-title">How Self-Management Support Works</h2>
            <p class="section-subtitle mx-auto">
                A clear, guided process from your initial enquiry through to ongoing monthly reviews with Allegiance Heart &amp; Home Care supporting care management, compliance and provider oversight at every step.
            </p>
        </div>

        <div class="timeline-wrapper">
            <div class="timeline-line"></div>

            @php
                $steps = [
                    ['Submit Self-Management Enquiry', 'Submit a self-management enquiry so Allegiance Heart & Home Care can understand your support needs, goals and preferred self-management arrangements.', 'bi-person-check', 1],
                    ['Suitability Review', 'Allegiance Heart & Home Care reviews your enquiry to confirm whether self-management support is suitable and appropriate.', 'bi-file-earmark-text', 2],
                    ['Service Agreement, Consent and Responsibilities', 'Before portal access is activated, the required service agreement, consent forms and self-management responsibilities are reviewed and signed.', 'bi-laptop', 3],
                    ['Portal Setup', 'Allegiance Heart & Home Care creates your secure portal account after approval, with login security, MFA where required and role-based access.', 'bi-people-fill', 4],
                    ['Worker and Service Approval', 'Submit your chosen workers, suppliers or services for Allegiance Heart & Home Care review and approval before services commence.', 'bi-receipt', 5],
                    ['Documentation and Service Evidence', 'Upload invoices, care notes, service evidence and supporting documents through the portal for Allegiance Heart & Home Care review.', 'bi-calendar-check', 6],
                    ['Ongoing Monthly Review', 'Allegiance Heart & Home Care reviews care arrangements, budget use, compliance, incidents, documentation and support need monthly to help maintain quality and safety.', 'bi-arrow-repeat', 7],
                ];
            @endphp

            @foreach($steps as $step)
                <div class="timeline-item fade-up">
                    <div class="timeline-marker">
                        <i class="bi {{ $step[2] }}"></i>
                    </div>
                    <div class="timeline-content">
                        <div class="timeline-step">Step {{ $step[3] }}</div>
                        <h4 class="timeline-title">{{ $step[0] }}</h4>
                        <p class="timeline-desc">{{ $step[1] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ============================================================
     WHAT YOU MANAGE vs AHHC OVERSEES
     ============================================================ -->
<section id="services" class="management-section section-padding">
    <div class="container">
        <div class="text-center mb-5 fade-up">
            <span class="section-eyebrow">
                <i class="bi bi-arrow-left-right"></i> Shared Responsibilities
            </span>
            <h2 class="section-title">What You Manage &amp; What Allegiance Heart &amp; Home Care Oversees</h2>
            <p class="section-subtitle mx-auto">
                Self-management is a partnership. You have more choice and control over day-to-day arrangements, while Allegiance Heart &amp; Home Care provides provider oversight, care management, budget monitoring, compliance checks and quality support.
            </p>
        </div>

        <div class="management-table-wrapper fade-up">
            <table class="management-table">
                <thead>
                    <tr>
                        <th>Responsibility</th>
                        <th style="text-align: center;">You Manage</th>
                        <th style="text-align: center;">Allegiance Heart &amp; Home Care Oversees</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Workers &amp; Suppliers</strong></td>
                        <td style="text-align: center;"><span class="table-badge-you">You choose</span></td>
                        <td style="text-align: center;"><span class="table-badge-ahhc">Allegiance Heart &amp; Home Care reviews and approves</span></td>
                        <td style="font-size: 0.85rem;">You may choose preferred workers or suppliers. Allegiance Heart &amp; Home Care reviews required credentials, compliance documents and suitability before services commence.</td>
                    </tr>
                    <tr>
                        <td><strong>Documentation</strong></td>
                        <td style="text-align: center;"><span class="table-badge-you">You submit</span></td>
                        <td style="text-align: center;"><span class="table-badge-ahhc">Allegiance Heart &amp; Home Care reviews</span></td>
                        <td style="font-size: 0.85rem;">You upload supporting documents through the portal. Allegiance Heart &amp; Home Care reviews them against your approved care and service arrangements.</td>
                    </tr>
                    <tr>
                        <td><strong>Service Evidence</strong></td>
                        <td style="text-align: center;"><span class="table-badge-you">You upload</span></td>
                        <td style="text-align: center;"><span class="table-badge-ahhc">Allegiance Heart &amp; Home Care monitors</span></td>
                        <td style="font-size: 0.85rem;">Care notes, invoices, receipts or service evidence can be uploaded through the portal for review and monitoring.</td>
                    </tr>
                    <tr>
                        <td><strong>Preferences &amp; Choices</strong></td>
                        <td style="text-align: center;"><span class="table-badge-you">You decide</span></td>
                        <td style="text-align: center;"><span class="table-badge-ahhc">Allegiance Heart &amp; Home Care supports</span></td>
                        <td style="font-size: 0.85rem;">Your preferences guide how services are arranged, provided they align with your approved Support at Home services, care needs and budget.</td>
                    </tr>
                    <tr>
                        <td><strong>Documents &amp; Communication</strong></td>
                        <td style="text-align: center;"><span class="table-badge-you">You manage</span></td>
                        <td style="text-align: center;"><span class="table-badge-ahhc">Allegiance Heart &amp; Home Care stores securely</span></td>
                        <td style="font-size: 0.85rem;">The portal supports secure document sharing and communication. Allegiance Heart &amp; Home Care maintains required records for compliance and service oversight.</td>
                    </tr>
                    <tr>
                        <td><strong>Care Management</strong></td>
                        <td style="text-align: center;"><span class="table-badge-empty">Not applicable</span></td>
                        <td style="text-align: center;"><span class="table-badge-ahhc">Allegiance Heart &amp; Home Care leads</span></td>
                        <td style="font-size: 0.85rem;">Allegiance Heart &amp; Home Care provides care management, including planning, review, monitoring and support for your care and services.</td>
                    </tr>
                    <tr>
                        <td><strong>Service Oversight</strong></td>
                        <td style="text-align: center;"><span class="table-badge-you">You can view and track</span></td>
                        <td style="text-align: center;"><span class="table-badge-ahhc">Allegiance Heart &amp; Home Care monitors</span></td>
                        <td style="font-size: 0.85rem;">You can view relevant service information through the portal. Allegiance Heart &amp; Home Care monitors service delivery, documentation, budget use and compliance requirements.</td>
                    </tr>
                    <tr>
                        <td><strong>Pre-Approvals</strong></td>
                        <td style="text-align: center;"><span class="table-badge-you">You request</span></td>
                        <td style="text-align: center;"><span class="table-badge-ahhc">Allegiance Heart &amp; Home Care reviews and approves</span></td>
                        <td style="font-size: 0.85rem;">Services, workers or suppliers may require review and approval before commencement to ensure they align with your care plan, budget and compliance requirements.</td>
                    </tr>
                    <tr>
                        <td><strong>Incidents &amp; Complaints</strong></td>
                        <td style="text-align: center;"><span class="table-badge-you">You report</span></td>
                        <td style="text-align: center;"><span class="table-badge-ahhc">Allegiance Heart &amp; Home Care reviews and responds</span></td>
                        <td style="font-size: 0.85rem;">You can report incidents, risks, concerns, complaints or feedback through the portal. Allegiance Heart &amp; Home Care reviews, follows up and manages required actions.</td>
                    </tr>
                    <tr>
                        <td><strong>Quality &amp; Safety</strong></td>
                        <td style="text-align: center;"><span class="table-badge-you">You participate</span></td>
                        <td style="text-align: center;"><span class="table-badge-ahhc">Allegiance Heart &amp; Home Care monitors and supports</span></td>
                        <td style="font-size: 0.85rem;">Allegiance Heart &amp; Home Care maintains ongoing oversight, reviews documentation, follows up concerns and supports safe, quality care arrangements.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- ============================================================
     PORTAL FEATURES
     ============================================================ -->
<section id="portal-features" class="portal-preview-section section-padding">
    <div class="container">
        <div class="text-center mb-5 fade-up">
            <span class="section-eyebrow">
                <i class="bi bi-window-stack"></i> Portal Features
            </span>
            <h2 class="section-title">What the Self-Management Portal Supports</h2>
            <p class="section-subtitle mx-auto">
                A secure dashboard for approvals, care notes, invoices, service evidence and document handling with oversight from Allegiance Heart &amp; Home Care.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-lg-8 fade-up">
                <div class="dashboard-mockup">
                    <div class="dashboard-header">
                        <div>
                            <strong>Allegiance Heart &amp; Home Care Portal</strong>
                            <div style="font-size: 0.75rem; opacity: 0.8;">Self-Management Dashboard</div>
                        </div>
                        <div style="font-size: 0.8rem;">
                            <i class="bi bi-circle-fill" style="color: #1FC7B7; font-size: 0.5rem;"></i> Secure Access
                        </div>
                    </div>
                    <div class="dashboard-nav-tabs">
                        <span class="dash-tab active">Participant Dashboard</span>
                        <span class="dash-tab">Worker/Supplier Dashboard</span>
                        <span class="dash-tab">Allegiance Heart &amp; Home Care Admin Dashboard</span>
                    </div>
                    <div class="dashboard-body">
                        <p style="margin-bottom: 1.5rem; max-width: 34rem; color: var(--text-muted);">
                            A secure portal experience for approved participants and authorised representatives, showing how Allegiance Heart &amp; Home Care supports choice, approval workflows, documentation and service oversight.
                        </p>
                        <div style="display: grid; gap: 0.75rem; grid-template-columns: repeat(2, minmax(0, 1fr));">
                            <div class="feature-pill">Secure Approval Requests</div>
                            <div class="feature-pill">Care Note Sharing</div>
                            <div class="feature-pill">Service Planning and Requests</div>
                            <div class="feature-pill">Protected Document Upload</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 fade-up">
                <div class="d-flex flex-column gap-2">
                    @php
                        $portalFeatures = [
                            ['bi-send-check', 'Approval Requests', 'Request service approvals and track review outcomes'],
                            ['bi-person-plus', 'Worker and Supplier Details', 'Submit worker or supplier details securely for Allegiance Heart &amp; Home Care review and approval'],
                            ['bi-journal-check', 'Care Notes and Service Evidence', 'Upload care notes, invoices, receipts and related service evidence for review'],
                            ['bi-exclamation-triangle-fill', 'Incident and Feedback Reporting', 'Report incidents, risks, concerns, complaints or feedback through the secure portal'],
                            ['bi-pencil-square', 'Electronic Signatures', 'Sign required documents digitally and securely'],
                            ['bi-folder-lock', 'Secure Documents', 'Upload and access approved documents through a secure workspace'],
                            ['bi-calendar-check', 'Review Progress', 'Track care review cycles, planning updates and required actions'],
                            ['bi-shield-check', 'Compliance Support', 'View compliance-related updates, document requirements and support from Allegiance Heart &amp; Home Care'],
                        ];
                    @endphp
                    @foreach($portalFeatures as $feature)
                        <div class="portal-feature-item">
                            <div class="portal-feature-icon">
                                <i class="bi {{ $feature[0] }}"></i>
                            </div>
                            <div>
                                <div style="font-weight: 600; font-size: 0.85rem;">{{ $feature[1] }}</div>
                                <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $feature[2] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     PRIVACY ASSURANCE
     ============================================================ -->
<section id="privacy" class="privacy-section section-padding">
    <div class="container">
        <div class="text-center mb-5 fade-up">
            <span class="section-eyebrow">
                <i class="bi bi-shield-lock"></i> Privacy and Security
            </span>
            <h2 class="section-title">Your information is managed securely</h2>
            <p class="section-subtitle mx-auto">
                The Allegiance Heart &amp; Home Care portal is a separate secure platform, protected by login, multi-factor authentication, and role-based access controls.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-md-4 fade-up">
                <div class="privacy-card">
                    <div class="privacy-icon-large">
                        <i class="bi bi-lock-fill"></i>
                    </div>
                    <h5 style="font-weight: 700; margin-bottom: 0.75rem;">Secure Login and MFA Protection</h5>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">
                        Portal access is only available to approved users. Secure login is required, with multi-factor authentication.
                    </p>
                </div>
            </div>
            <div class="col-md-4 fade-up">
                <div class="privacy-card">
                    <div class="privacy-icon-large success">
                        <i class="bi bi-person-check"></i>
                    </div>
                    <h5 style="font-weight: 700; margin-bottom: 0.75rem;">Role-Based Access</h5>
                    <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0;">
                        Access is controlled by user role. Participants, authorised representatives, workers/suppliers and Allegiance Heart &amp; Home Care staff only access information relevant to their role and approved permissions.
                    </p>
                </div>
            </div>
        </div>

        <div class="text-center mt-5 fade-up">
            <p style="color: var(--text-muted); font-size: 0.95rem; max-width: 700px; margin: 0 auto;">
                <i class="bi bi-shield-check text-success me-2"></i>
                The Allegiance Heart &amp; Home Care portal is separate from the public website. Portal login is only available to approved users who have been invited or authorised by Allegiance Heart &amp; Home Care.
            </p>
        </div>
    </div>
</section>

{{-- 
    ALLEGIANCE HEART & HOME CARE — FAQ SECTION
    Design Ethos: Warm, accessible, elegant. Clean typography, gentle interactions, and a calm, trustworthy aesthetic.
--}}

<section class="ahhc-faq-section" aria-labelledby="faq-heading">
    <div class="ahhc-container">
        
        {{-- Header --}}
        <header class="ahhc-faq-header">
            <h2 id="faq-heading">Frequently Asked Questions</h2>
            <p>Answers to common questions about Self-Management Support, the secure portal, provider oversight, and how Allegiance Heart &amp; Home Care supports quality, safety, choice and control.</p>
        </header>

        {{-- FAQ Data --}}
        @php
            $faqs = [
                [
                    'q' => 'Am I eligible for self-management support?',
                    'a' => 'Self management support is available to approved support at home participants who wants more choice and control over their care management. You can register your interest through our website, and we will contact you to discuss next steps.'
                ],
                [
                    'q' => 'How much does self-management cost?',
                    'a' => 'Under self-management support, you may choose your preferred workers or suppliers and agree on their service rates directly with them. These costs must fit within your approved Support at Home budget and any contribution requirements. Allegiance Heart & Home Care will review submitted workers, suppliers, quotes and invoices before approval or payment to support budget monitoring, compliance and service oversight.'
                ],
                [
                    'q' => 'Can I use third-party workers or Mable workers?',
                    'a' => 'Yes! One of the biggest benefits of self-management is choice. You can choose and arrange your own workers or suppliers, including those from marketplaces like Mable. Allegiance Heart & Home Care will approve the worker, link them to your care plan, and monitor their compliance to ensure your safety.'
                ],
                [
                    'q' => 'How do payments and documentation work?',
                    'a' => 'You or your support person can submit invoices and receipts directly through the secure portal. Simply attach the invoice along with evidence (like care notes or timesheets). Allegiance Heart & Home Care reviews the submission to ensure it aligns with your care plan and budget before processing it.'
                ],
                [
                    'q' => 'Is my information secure?',
                    'a' => 'Absolutely. We use strict encryption, role-based access controls (so people only see what they need to), and maintain comprehensive audit logs to protect your personal and health information.'
                ],
                [
                    'q' => 'Is two-factor authentication (2FA) required?',
                    'a' => 'Two-Factor Authentication (2FA/MFA) is mandatory for all Allegiance Heart & Home Care staff, care managers, and registered workers/suppliers to ensure maximum security. For participants and support persons, it is strongly recommended and available to protect your account.'
                ],
                [
                    'q' => 'Can I upload documents to the portal?',
                    'a' => 'Yes. The portal includes a secure document upload feature with malware scanning. You can easily upload and manage documents like care plans, agreements, quotes, and receipts based on your access permissions.'
                ],
                [
                    'q' => 'What happens after I submit an enquiry?',
                    'a' => 'Submitting an enquiry does not automatically create a portal account. It sends your request to Allegiance Heart & Home Care for review. Our team will contact you to discuss your preferred self-management setup, responsibilities, worker or supplier arrangements, and next steps before portal access is created.'
                ],
                [
                    'q' => 'What is the onboarding process?',
                    'a' => 'The process follows 7 steps: 1) submit a self-management enquiry, 2) review your enquiry with Allegiance Heart & Home Care, 3) complete the service agreement, consent and responsibilities, 4) set up your portal access, 5) submit workers or services for approval, 6) upload documentation and service evidence, and 7) continue with ongoing monthly reviews.'
                ],
                [
                    'q' => 'What documents are required for onboarding?',
                    'a' => 'You will need to provide your referral or support plan, authority documents (if a support person is acting for you), the Self-Management Agreement, consent and privacy forms, and a handbook acknowledgement.'
                ],
                [
                    'q' => 'How long does onboarding take?',
                    'a' => 'The timeframe varies depending on how quickly documents are provided and the complexity of your care plan. Our team works efficiently to get you set up, and we will keep you informed of your progress at every step.'
                ],
                [
                    'q' => 'Can I update or replace documents after uploading?',
                    'a' => 'Yes. The system features version control, meaning you can upload updated documents or replace existing ones. The system safely maintains a history of your document uploads for auditing purposes.'
                ],
                [
                    'q' => 'What happens when I complete onboarding?',
                    'a' => 'Once approved, your personal dashboard is activated! You will receive a secure login invitation, set up your security preferences, and gain full access to your live budget, pre-approvals, worker management, and communication tools.'
                ],
                [
                    'q' => 'What file formats and sizes are accepted for uploads?',
                    'a' => 'We accept standard file types including PDF, Word, Excel, JPEG, and PNG. For security purposes, the system enforces file size limits and automatically scans all uploads for malware.'
                ],
                [
                    'q' => 'How do I know which documents are missing?',
                    'a' => 'Your dashboard is designed to keep you informed. If a mandatory document is missing, expired, or needs attention, the dashboard will display a clear, easy-to-read alert so you know exactly what needs to be uploaded.'
                ],
            ];
        @endphp

        {{-- FAQ List --}}
        <div class="ahhc-faq-list" role="list">
            @foreach($faqs as $index => $faq)
                <details class="ahhc-faq-item" @if($index === 0) open @endif role="listitem">
                    <summary class="ahhc-faq-question" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">
                        <span>{{ $faq['q'] }}</span>
                        <svg class="ahhc-faq-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"></polyline>
                        </svg>
                    </summary>
                    <div class="ahhc-faq-answer">
                        <p>{{ $faq['a'] }}</p>
                    </div>
                </details>
            @endforeach
        </div>

        {{-- Call to Action --}}
        <div class="ahhc-faq-cta">
            <h3>Still have questions?</h3>
            <p>Our team is here to help you understand how self-management can work for you or your loved one.</p>
            <div class="ahhc-cta-buttons">
                <a href="/contact" class="ahhc-btn ahhc-btn-primary">Contact Us</a>
                <a href="/apply" class="ahhc-btn ahhc-btn-secondary">Apply for Self-Management</a>
            </div>
        </div>

    </div>
</section>

<style>
    /* 
       AHHC FAQ Styles 
       Refined for warmth, accessibility, and a premium, trustworthy feel.
    */
    .ahhc-faq-section {
        padding: 80px 24px;
        background: linear-gradient(to bottom, #fafbfc 0%, #f1f5f9 100%);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        color: #1e293b;
    }

    .ahhc-container {
        max-width: 860px;
        margin: 0 auto;
    }

    /* Header */
    .ahhc-faq-header {
        text-align: center;
        margin-bottom: 56px;
    }

    .ahhc-faq-header h2 {
        font-size: clamp(2rem, 5vw, 2.5rem);
        color: #0a4b6e;
        margin-bottom: 16px;
        font-weight: 700;
        letter-spacing: -0.02em;
        line-height: 1.2;
    }

    .ahhc-faq-header p {
        font-size: 1.2rem;
        color: #475569;
        max-width: 680px;
        margin: 0 auto;
        line-height: 1.7;
        font-weight: 400;
    }

    /* FAQ List */
    .ahhc-faq-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .ahhc-faq-item {
        background: #ffffff;
        border: 1px solid #e9eef3;
        border-radius: 14px;
        overflow: hidden;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02), 0 1px 2px rgba(0, 0, 0, 0.03);
    }

    .ahhc-faq-item:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.03), 0 2px 4px rgba(0, 0, 0, 0.03);
    }

    .ahhc-faq-item[open] {
        border-color: #0a4b6e;
        box-shadow: 0 10px 25px -5px rgba(10, 75, 110, 0.08), 0 4px 10px -2px rgba(10, 75, 110, 0.04);
        background: #ffffff;
    }

    /* Question */
    .ahhc-faq-question {
        font-size: 1.15rem;
        font-weight: 600;
        padding: 22px 28px;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        list-style: none;
        color: #0f172a;
        transition: all 0.2s ease;
        user-select: none;
        gap: 20px;
    }

    .ahhc-faq-question:hover {
        background-color: #f8fafc;
        color: #0a4b6e;
    }

    .ahhc-faq-item[open] .ahhc-faq-question {
        background-color: #f8fafc;
        color: #0a4b6e;
        padding-bottom: 16px;
    }

    .ahhc-faq-question::-webkit-details-marker {
        display: none;
    }

    .ahhc-faq-question span {
        flex: 1;
        line-height: 1.5;
    }

    /* Icon */
    .ahhc-faq-icon {
        width: 22px;
        height: 22px;
        color: #0a4b6e;
        transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        flex-shrink: 0;
        opacity: 0.7;
    }

    .ahhc-faq-item:hover .ahhc-faq-icon {
        opacity: 1;
    }

    .ahhc-faq-item[open] .ahhc-faq-icon {
        transform: rotate(180deg);
        opacity: 1;
    }

    /* Answer */
    .ahhc-faq-answer {
        padding: 0 28px 26px 28px;
        font-size: 1.05rem;
        line-height: 1.8;
        color: #334155;
        animation: faqReveal 0.3s ease;
    }

    .ahhc-faq-answer p {
        margin: 0;
    }

    @keyframes faqReveal {
        from {
            opacity: 0;
            transform: translateY(-8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* CTA Section */
    .ahhc-faq-cta {
        margin-top: 72px;
        text-align: center;
        background: #ffffff;
        padding: 48px 40px;
        border-radius: 20px;
        border: 1px solid #e9eef3;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        transition: box-shadow 0.3s ease;
    }

    .ahhc-faq-cta:hover {
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.05);
    }

    .ahhc-faq-cta h3 {
        font-size: 1.7rem;
        color: #0a4b6e;
        margin-bottom: 12px;
        font-weight: 700;
        letter-spacing: -0.01em;
    }

    .ahhc-faq-cta p {
        font-size: 1.1rem;
        color: #475569;
        margin-bottom: 32px;
        line-height: 1.6;
    }

    .ahhc-cta-buttons {
        display: flex;
        gap: 16px;
        justify-content: center;
        flex-wrap: wrap;
    }

    /* Buttons */
    .ahhc-btn {
        padding: 15px 32px;
        font-size: 1.05rem;
        font-weight: 600;
        border-radius: 10px;
        text-decoration: none;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        letter-spacing: 0.01em;
        line-height: 1.4;
    }

    .ahhc-btn-primary {
        background-color: #0a4b6e;
        color: #ffffff;
        box-shadow: 0 2px 8px rgba(10, 75, 110, 0.25);
    }

    .ahhc-btn-primary:hover {
        background-color: #083d59;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(10, 75, 110, 0.3);
    }

    .ahhc-btn-primary:active {
        transform: translateY(0);
        box-shadow: 0 2px 4px rgba(10, 75, 110, 0.2);
    }

    .ahhc-btn-secondary {
        background-color: #ffffff;
        color: #0a4b6e;
        border: 2px solid #0a4b6e;
    }

    .ahhc-btn-secondary:hover {
        background-color: #f0f6fa;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(10, 75, 110, 0.1);
    }

    .ahhc-btn-secondary:active {
        transform: translateY(0);
    }

    /* Focus states for accessibility */
    .ahhc-btn:focus-visible,
    .ahhc-faq-question:focus-visible {
        outline: 2px solid #0a4b6e;
        outline-offset: 2px;
        border-radius: 6px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .ahhc-faq-section {
            padding: 56px 18px;
        }

        .ahhc-faq-header {
            margin-bottom: 40px;
        }

        .ahhc-faq-header h2 {
            font-size: 1.8rem;
        }

        .ahhc-faq-header p {
            font-size: 1.05rem;
        }

        .ahhc-faq-question {
            font-size: 1.05rem;
            padding: 18px 22px;
        }

        .ahhc-faq-answer {
            padding: 0 22px 22px 22px;
            font-size: 1rem;
        }

        .ahhc-faq-cta {
            margin-top: 56px;
            padding: 36px 24px;
        }

        .ahhc-faq-cta h3 {
            font-size: 1.5rem;
        }

        .ahhc-cta-buttons {
            gap: 12px;
        }

        .ahhc-btn {
            padding: 14px 24px;
            font-size: 1rem;
        }
    }

    @media (max-width: 480px) {
        .ahhc-faq-section {
            padding: 44px 12px;
        }

        .ahhc-faq-question {
            padding: 16px 18px;
            font-size: 1rem;
        }

        .ahhc-faq-answer {
            padding: 0 18px 18px 18px;
        }

        .ahhc-cta-buttons {
            flex-direction: column;
            align-items: stretch;
        }

        .ahhc-btn {
            width: 100%;
            text-align: center;
        }
    }
</style>

<!-- ============================================================
     CONTACT / ENQUIRY FORM
     ============================================================ -->
<section id="contact" class="contact-section section-padding">
    <div class="container position-relative" style="z-index: 2;">
        <div class="row g-5 align-items-stretch">
            <div class="col-lg-5 fade-up">
                <div style="margin-bottom: 2rem;">
                    <span class="section-eyebrow" style="background: rgba(255,255,255,0.15); color: white;">
                        <i class="bi bi-envelope"></i> Get in Touch
                    </span>
                    <h2 style="font-size: 2rem; font-weight: 800; color: white; letter-spacing: -0.03em; margin-bottom: 1rem; font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;">
                        Ready to take control of your care?
                    </h2>
                    <p style="color: rgba(255,255,255,0.85); font-size: 1.05rem; line-height: 1.7;">
                        Submit an enquiry for self-management support. Our team will review your enquiry and discuss your suitability with you.
                    </p>
                </div>

                <div class="contact-info-card">
                    <h5 style="font-weight: 700; margin-bottom: 1.5rem; color: white; font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;">
                        <i class="bi bi-building me-2"></i> Contact Information
                    </h5>
                    
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-building"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.78rem; opacity: 0.7;">Office</div>
                            <div style="font-weight: 600;">Allegiance Heart & Home Care</div>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.78rem; opacity: 0.7;">Email</div>
                            <div style="font-weight: 600;"><a href="mailto:intake@allegiancehearthomecare.com.au" style="color: white; text-decoration: underline;">intake@allegiancehearthomecare.com.au</a></div>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: rgba(255,255,255,0.15); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="bi bi-telephone"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.78rem; opacity: 0.7;">Phone</div>
                            <div style="font-weight: 600;">02 8730 9049</div>
                        </div>
                    </div>

                    <div class="mt-4 p-3 rounded-3" style="background: rgba(255,255,255,0.06);">
                        <div style="font-size: 0.85rem; color: rgba(255,255,255,0.8);">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Portal login:</strong> 
                            <a href="/portal" style="color: white; text-decoration: underline;">
                                Portal Login
                            </a>
                            <br><small style="opacity: 0.7;">Submitting this form does not create portal access. A team member from Allegiance Heart Home Care will contact you about your request.</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 fade-up">
                <div class="contact-form-card">
                    @if(session('status'))
                        <div class="alert alert-success rounded-3 mb-4" data-enquiry-success="true" style="background: #d1fae5; border: 1px solid #a7f3d0; color: #065f46;">
                            <i class="bi bi-check-circle-fill me-2"></i>{{ session('status') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger rounded-3 mb-4" style="background: #fee2e2; border: 1px solid #fecaca; color: #991b1b;">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Please correct the errors below and try again.
                        </div>
                    @endif

                    <h4 style="font-weight: 700; margin-bottom: 0.35rem; font-size: 1.4rem; font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;">
                        Submit an Enquiry
                    </h4>
                    <p style="color: var(--text-muted); margin-bottom: 1.5rem; font-size: 0.9rem;">
                        This form creates an enquiry for Allegiance Heart & Home Care review. It does <strong>not</strong> create a portal account automatically.
                    </p>

                    <form id="enquiryForm" method="POST" action="{{ route('public.enquiries.store') }}" novalidate>
                        @csrf
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label-custom" for="name">Full Name <span style="color: #ef4444;">*</span></label>
                                <input type="text" 
                                       name="name" 
                                       id="name" 
                                       class="form-input-custom @error('name') is-invalid @enderror" 
                                       value="{{ old('name') }}" 
                                       placeholder="Your full name" 
                                       required
                                       autocomplete="name">
                                @error('name')
                                    <div class="invalid-feedback-custom">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom" for="email">Email Address <span style="color: #ef4444;">*</span></label>
                                <input type="email" 
                                       name="email" 
                                       id="email" 
                                       class="form-input-custom @error('email') is-invalid @enderror" 
                                       value="{{ old('email') }}" 
                                       placeholder="you@example.com" 
                                       required
                                       autocomplete="email">
                                @error('email')
                                    <div class="invalid-feedback-custom">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom" for="phone">Phone Number</label>
                                <input type="tel" 
                                       name="phone" 
                                       id="phone" 
                                       class="form-input-custom @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone') }}" 
                                       placeholder="Your phone number"
                                       autocomplete="tel">
                                @error('phone')
                                    <div class="invalid-feedback-custom">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label-custom" for="role">Your Role <span style="color: #ef4444;">*</span></label>
                                <select name="role" 
                                        id="role" 
                                        class="form-select-custom @error('role') is-invalid @enderror" 
                                        required>
                                    <option value="">Select your role...</option>
                                    <option value="participant" {{ old('role') === 'participant' ? 'selected' : '' }}>Participant</option>
                                    <option value="family_member" {{ old('role') === 'family_member' ? 'selected' : '' }}>Family Member / Carer</option>
                                    <option value="representative" {{ old('role') === 'representative' ? 'selected' : '' }}>Legal Representative</option>
                                    <option value="support_coordinator" {{ old('role') === 'support_coordinator' ? 'selected' : '' }}>Support Coordinator</option>
                                    <option value="worker" {{ old('role') === 'worker' ? 'selected' : '' }}>Worker / Provider</option>
                                    <option value="other" {{ old('role') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('role')
                                    <div class="invalid-feedback-custom">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label-custom" for="support_status">Support at Home Status</label>
                                <select name="support_status" 
                                        id="support_status" 
                                        class="form-select-custom @error('support_status') is-invalid @enderror">
                                    <option value="">Select your current status...</option>
                                    <option value="have_approval" {{ old('support_status') === 'have_approval' ? 'selected' : '' }}>I have Support at Home approval</option>
                                    <option value="awaiting_approval" {{ old('support_status') === 'awaiting_approval' ? 'selected' : '' }}>Awaiting approval</option>
                                    <option value="exploring" {{ old('support_status') === 'exploring' ? 'selected' : '' }}>Exploring options / Not yet applied</option>
                                    <option value="other_program" {{ old('support_status') === 'other_program' ? 'selected' : '' }}>On a different program</option>
                                </select>
                                @error('support_status')
                                    <div class="invalid-feedback-custom">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label-custom" for="message">Your Message <span style="color: #ef4444;">*</span></label>
                                <textarea name="message" 
                                          id="message" 
                                          rows="5" 
                                          class="form-textarea-custom @error('message') is-invalid @enderror" 
                                          placeholder="Tell us about your situation, needs and any questions you have about self-management support..."
                                          required>{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback-custom">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-check-custom">
                                    <input type="checkbox" 
                                           name="consent" 
                                           value="1" 
                                           id="consent" 
                                           {{ old('consent') ? 'checked' : '' }} 
                                           required>
                                    <span style="font-size: 0.88rem; color: var(--text-secondary); line-height: 1.5;">
                                        I consent to Allegiance Heart Home Care contacting me about my enquiry and understand that submitting this form does <strong>not</strong> create a portal account.
                                        <span style="color: #ef4444;">*</span>
                                    </span>
                                </label>
                                @error('consent')
                                    <div class="invalid-feedback-custom">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <button id="enquirySubmitBtn" type="submit" class="btn-primary-custom w-100" style="justify-content: center;">
                                    <span class="submit-label"><i class="bi bi-send-fill"></i> Submit Enquiry</span>
                                    <span class="submit-spinner d-none" role="status" aria-hidden="true">
                                        <span class="spinner-border spinner-border-sm me-2"></span>
                                        Submitting your enquiry...
                                    </span>
                                </button>
                                <p style="text-align: center; font-size: 0.8rem; color: var(--text-muted); margin-top: 0.75rem;">
                                    <i class="bi bi-shield-lock me-1"></i> Your information is secure and encrypted
                                </p>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="enquirySuccessModal" tabindex="-1" aria-labelledby="enquirySuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0" style="background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%); color: white;">
                <h5 class="modal-title fw-bold" id="enquirySuccessModalLabel">
                    <i class="bi bi-check-circle-fill me-2"></i> Enquiry Submitted
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <div class="mb-3">
                    <i class="bi bi-envelope-check-fill" style="font-size: 2.5rem; color: #0f766e;"></i>
                </div>
                <h6 class="fw-bold mb-2">Thank you for getting in touch</h6>
                <p id="enquirySuccessMessage" class="mb-0" style="color: var(--text-secondary);">
                    Thank you for your enquiry. A team member from Allegiance Heart Home care will contact you to discuss your self-management support request and next steps.
                </p>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn btn-primary-custom" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const items = document.querySelectorAll('.faq-accordion-item');

        function setPanelState(item, isOpen) {
            const button = item.querySelector('.faq-accordion-button');
            const panel = item.querySelector('.faq-accordion-panel');

            if (!button || !panel) return;

            button.setAttribute('aria-expanded', String(isOpen));
            panel.classList.toggle('open', isOpen);
            if (isOpen) {
                panel.removeAttribute('hidden');
            } else {
                panel.setAttribute('hidden', 'true');
            }
        }

        items.forEach((item) => {
            const button = item.querySelector('.faq-accordion-button');
            if (!button) return;

            button.addEventListener('click', function(event) {
                event.preventDefault();

                const shouldOpen = button.getAttribute('aria-expanded') !== 'true';

                items.forEach((otherItem) => {
                    setPanelState(otherItem, false);
                });

                if (shouldOpen) {
                    setPanelState(item, true);
                    const faqSection = document.getElementById('faq');
                    if (faqSection) {
                        faqSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
        });

        if (items.length > 0) {
            setPanelState(items[0], true);
        }

        const enquiryForm = document.getElementById('enquiryForm');
        const enquirySubmitBtn = document.getElementById('enquirySubmitBtn');
        const enquirySuccessModal = document.getElementById('enquirySuccessModal');
        const enquirySuccessAlert = document.querySelector('[data-enquiry-success="true"]');

        if (enquirySuccessAlert && enquirySuccessModal && typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(enquirySuccessModal, { backdrop: 'static', keyboard: false });
            modal.show();
        }

        if (enquiryForm && enquirySubmitBtn) {
            enquiryForm.addEventListener('submit', function(event) {
                if (!enquiryForm.checkValidity()) {
                    enquiryForm.reportValidity();
                    event.preventDefault();
                    return;
                }

                const submitLabel = enquirySubmitBtn.querySelector('.submit-label');
                const submitSpinner = enquirySubmitBtn.querySelector('.submit-spinner');

                if (submitLabel && submitSpinner) {
                    submitLabel.classList.add('d-none');
                    submitSpinner.classList.remove('d-none');
                }

                enquirySubmitBtn.disabled = true;
                enquirySubmitBtn.classList.add('is-loading');
            });
        }

        // Form validation visual feedback
        if (enquiryForm) {
            const inputs = enquiryForm.querySelectorAll('input[required], select[required], textarea[required]');
            
            inputs.forEach(input => {
                input.addEventListener('invalid', function() {
                    this.style.borderColor = '#ef4444';
                    this.style.backgroundColor = '#fef2f2';
                });
                
                input.addEventListener('input', function() {
                    if (this.validity.valid) {
                        this.style.borderColor = '';
                        this.style.backgroundColor = '';
                    }
                });
                
                input.addEventListener('change', function() {
                    if (this.validity.valid) {
                        this.style.borderColor = '';
                        this.style.backgroundColor = '';
                    }
                });
            });
        }
    });
</script>
@endpush