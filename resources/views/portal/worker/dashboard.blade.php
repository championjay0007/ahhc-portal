@extends('layouts.portal')

@section('title', 'Worker Dashboard')

@section('content')
<div class="worker-dashboard">
    <!-- Header Section -->
    <div class="dashboard-header">
        <div class="header-content">
            <h1 class="greeting">Good morning, {{ auth()->user()->name }} <span class="wave-emoji">☀️</span></h1>
            <p class="date-info">
                <i class="bi bi-calendar3"></i> {{ now()->format('l, j F Y') }} 
                <span class="separator">|</span> 
                <i class="bi bi-shield-check"></i> Secure browser access
            </p>
        </div>
    </div>

    <!-- Notification Cards Row -->
    <div class="dashboard-notification-row">
        <a href="{{ route('portal.notifications') }}" class="notification-card card-link">
            <div class="notification-card-icon bg-primary-gradient">
                <i class="bi bi-bell-fill"></i>
            </div>
            <div class="notification-card-content">
                <p class="notification-card-label">Unread alerts</p>
                <h3 class="notification-card-value">{{ $unreadNotificationCount ?? 0 }}</h3>
                <small class="notification-card-hint">Open portal notifications</small>
            </div>
        </a>
        <a href="{{ route('portal.messages.inbox') }}" class="notification-card card-link">
            <div class="notification-card-icon bg-success-gradient">
                <i class="bi bi-envelope-fill"></i>
            </div>
            <div class="notification-card-content">
                <p class="notification-card-label">Unread messages</p>
                <h3 class="notification-card-value">{{ $unreadMessageCount ?? 0 }}</h3>
                <small class="notification-card-hint">Go to your inbox</small>
            </div>
        </a>
        <a href="{{ route('portal.notifications.preferences') }}" class="notification-card card-link">
            <div class="notification-card-icon bg-warning-gradient">
                <i class="bi bi-gear-fill"></i>
            </div>
            <div class="notification-card-content">
                <p class="notification-card-label">Email preferences</p>
                <h3 class="notification-card-value">Manage</h3>
                <small class="notification-card-hint">Update delivery settings</small>
            </div>
        </a>
    </div>

    <!-- Status Cards -->
    <div class="status-grid">
        <div class="status-card status-primary">
            <div class="status-icon-wrapper">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="status-info">
                <span class="status-number">3</span>
                <span class="status-text">Assigned shifts today</span>
            </div>
        </div>
        <div class="status-card status-danger">
            <div class="status-icon-wrapper">
                <i class="bi bi-file-earmark-medical"></i>
            </div>
            <div class="status-info">
                <span class="status-number">1</span>
                <span class="status-text">Care note due</span>
            </div>
        </div>
        <div class="status-card status-secondary">
            <div class="status-icon-wrapper">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div class="status-info">
                <span class="status-number">0</span>
                <span class="status-text">Open incidents</span>
            </div>
        </div>
        <div class="status-card status-warning">
            <div class="status-icon-wrapper">
                <i class="bi bi-clipboard-check"></i>
            </div>
            <div class="status-info">
                <span class="status-number">2</span>
                <span class="status-text">Compliance reminders</span>
            </div>
        </div>
    </div>

    <!-- Chat Panel -->
    <div class="chat-panel">
        <div class="dash-card chat-card">
            <div class="dash-card-header">
                <div class="card-badge">💬</div>
                <div class="card-heading">
                    <h2 class="card-title">Assigned Participant Chat</h2>
                    <span class="card-subtitle">Coordinate care, confirm arrival, or share updates instantly.</span>
                </div>
            </div>
            <div class="chat-card-body">
                @php
                    $primaryAssignment = $assignments->first();
                    $primaryParticipant = optional($primaryAssignment)->participant;
                @endphp

                @if($primaryParticipant)
                    <div class="chat-participant-info">
                        <div class="participant-avatar">
                            {{ substr($primaryParticipant->first_name ?? 'P', 0, 1) }}
                        </div>
                        <div class="participant-details">
                            <strong>{{ $primaryParticipant->first_name }} {{ $primaryParticipant->last_name }}</strong>
                            <span>Primary assigned participant</span>
                        </div>
                    </div>
                    <div class="chat-actions">
                        <a href="{{ route('portal.messages.conversation', $primaryParticipant->user_id) }}" class="btn-modern btn-primary-modern">
                            <i class="bi bi-chat-dots-fill"></i> Chat with {{ $primaryParticipant->first_name }}
                        </a>
                        <a href="{{ route('portal.worker.assigned_participants') }}" class="btn-modern btn-outline-modern">
                            <i class="bi bi-people"></i> View all assigned participants
                        </a>
                    </div>
                @else
                    <div class="empty-state-chat">
                        <i class="bi bi-inbox"></i>
                        <p>No active assigned participants found. Check your assignments page for updates.</p>
                        <a href="{{ route('portal.worker.assigned_participants') }}" class="btn-modern btn-outline-modern">
                            <i class="bi bi-arrow-right"></i> View assignments
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="content-grid">
        <!-- Left Column: Shifts Table -->
        <div class="dash-card shifts-card">
            <div class="dash-card-header">
                <div class="card-badge">📅</div>
                <div class="card-heading">
                    <h2 class="card-title">Assigned Shifts & Participant Snapshot</h2>
                    <span class="card-subtitle">Your schedule for today</span>
                </div>
            </div>
            <div class="table-responsive-custom">
                <table class="shifts-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Participant</th>
                            <th>Service</th>
                            <th>Risk Alert</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($todaysShifts as $shift)
                            @php
                                $participant = $shift->participant;
                                $timeRange = $shift->start_date ? $shift->start_date->format('H:i') . ' - ' . $shift->end_date->format('H:i') : 'TBA';
                                $serviceLabel = $shift->assignment_type ? ucwords(str_replace('_', ' ', $shift->assignment_type)) : 'Support';
                                $riskLabel = $participant?->medical_alerts ? 'Falls Risk' : 'No Alerts';
                                $riskClass = $participant?->medical_alerts ? 'risk-high' : 'risk-low';
                            @endphp
                            <tr>
                                <td class="time-cell">
                                    <i class="bi bi-clock"></i> {{ $timeRange }}
                                </td>
                                <td>
                                    <div class="participant-name-cell">
                                        <div class="mini-avatar-sm">{{ substr($participant->first_name ?? 'N', 0, 1) }}</div>
                                        <span>{{ $participant->first_name ?? 'N/A' }} {{ $participant->last_name ?? '' }}</span>
                                    </div>
                                </td>
                                <td>{{ $serviceLabel }}</td>
                                <td><span class="risk-badge {{ $riskClass }}">{{ $riskLabel }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('portal.worker.shifts') }}" class="btn-icon-sm" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="empty-state">
                                    <i class="bi bi-calendar-x"></i>
                                    <p>No shifts scheduled for today. Enjoy your day off!</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Column: Quick Actions -->
        <div class="actions-column">
            <div class="dash-card quick-actions-card">
                <div class="dash-card-header">
                    <div class="card-badge">⚡</div>
                    <div class="card-heading">
                        <h2 class="card-title">Quick Actions</h2>
                        <span class="card-subtitle">Frequently used tasks</span>
                    </div>
                </div>
                <div class="actions-list">
                    <a href="{{ route('portal.worker.shifts') }}" class="action-btn action-start">
                        <div class="action-icon"><i class="bi bi-play-circle-fill"></i></div>
                        <div class="action-text">
                            <span class="action-title">Start Shift</span>
                            <span class="action-desc">Confirm arrival time</span>
                        </div>
                    </a>
                    <a href="{{ route('portal.worker.care_notes.create') }}" class="action-btn action-submit">
                        <div class="action-icon"><i class="bi bi-file-earmark-text-fill"></i></div>
                        <div class="action-text">
                            <span class="action-title">Submit Care Note</span>
                            <span class="action-desc">Log daily support details</span>
                        </div>
                    </a>
                    <a href="{{ route('portal.worker.incidents.create') }}" class="action-btn action-report">
                        <div class="action-icon"><i class="bi bi-exclamation-octagon-fill"></i></div>
                        <div class="action-text">
                            <span class="action-title">Report Incident</span>
                            <span class="action-desc">Log falls or safety issues</span>
                        </div>
                    </a>
                    <a href="{{ route('portal.worker.documents.upload') }}" class="action-btn action-upload">
                        <div class="action-icon"><i class="bi bi-cloud-upload-fill"></i></div>
                        <div class="action-text">
                            <span class="action-title">Upload Evidence</span>
                            <span class="action-desc">Service photos or docs</span>
                        </div>
                    </a>
                    <a href="{{ route('portal.worker.invoices') }}" class="action-btn action-invoice">
                        <div class="action-icon"><i class="bi bi-receipt"></i></div>
                        <div class="action-text">
                            <span class="action-title">Submit Invoice</span>
                            <span class="action-desc">Bill for delivered care</span>
                        </div>
                    </a>
                    <a href="{{ route('portal.gallery') }}" class="action-btn action-gallery">
                        <div class="action-icon"><i class="bi bi-images"></i></div>
                        <div class="action-text">
                            <span class="action-title">Shared Gallery</span>
                            <span class="action-desc">View uploaded media</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Workflow Section -->
    <div class="workflow-section">
        <div class="dash-card workflow-card">
            <div class="dash-card-header">
                <div class="card-badge">🔄</div>
                <div class="card-heading">
                    <h2 class="card-title">Staff Workflow - Safe & Simple</h2>
                    <span class="card-subtitle">Follow these steps for every shift to ensure compliance and quality care.</span>
                </div>
            </div>
            
            <div class="workflow-steps">
                <div class="workflow-step">
                    <div class="step-number step-1">1</div>
                    <p class="step-label">View assigned shift</p>
                </div>
                <div class="step-connector"></div>
                <div class="workflow-step">
                    <div class="step-number step-2">2</div>
                    <p class="step-label">Check care instructions</p>
                </div>
                <div class="step-connector"></div>
                <div class="workflow-step">
                    <div class="step-number step-3">3</div>
                    <p class="step-label">Deliver service</p>
                </div>
                <div class="step-connector"></div>
                <div class="workflow-step">
                    <div class="step-number step-4">4</div>
                    <p class="step-label">Submit care note</p>
                </div>
                <div class="step-connector"></div>
                <div class="workflow-step">
                    <div class="step-number step-5">5</div>
                    <p class="step-label">Escalate risks/incidents</p>
                </div>
            </div>
            
            <!-- Info Boxes -->
            <div class="workflow-info">
                <div class="info-box info-access">
                    <div class="info-icon"><i class="bi bi-shield-lock"></i></div>
                    <div class="info-content">
                        <strong>Must-have access control</strong>
                        <p>Assigned participants only. No broad participant list. Audit trail for every view.</p>
                    </div>
                </div>
                <div class="info-box info-mobile">
                    <div class="info-icon"><i class="bi bi-phone"></i></div>
                    <div class="info-content">
                        <strong>Mobile-friendly PWA</strong>
                        <p>Works flawlessly via browser and can be saved to your phone's home screen.</p>
                    </div>
                </div>
                <div class="info-box info-compliance">
                    <div class="info-icon"><i class="bi bi-check2-square"></i></div>
                    <div class="info-content">
                        <strong>Compliance prompts</strong>
                        <p>Care note due alerts, incident forms, and missing evidence reminders.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* ===== CSS Variables ===== */
    :root {
        --primary: #0E3863;
        --primary-dark: #092B4A;
        --accent: #1699A1;
        --accent-dark: #0F7C88;
        --success: #10B981;
        --warning: #F59E0B;
        --danger: #EF4444;
        --info: #3B82F6;
        --bg-app: #F4F7FC;
        --bg-surface: #ffffff;
        --text-dark: #0B2B3F;
        --text-primary: #1E293B;
        --text-secondary: #475569;
        --text-muted: #64748B;
        --border-light: rgba(14, 56, 99, 0.08);
        --border-medium: rgba(14, 56, 99, 0.12);
        --shadow-sm: 0 2px 8px rgba(14, 56, 99, 0.06);
        --shadow-md: 0 4px 16px rgba(14, 56, 99, 0.08);
        --shadow-lg: 0 8px 32px rgba(14, 56, 99, 0.12);
        --card-shadow: 0 4px 20px rgba(14, 56, 99, 0.06), 0 2px 8px rgba(0, 0, 0, 0.04);
        --card-hover-shadow: 0 12px 40px rgba(14, 56, 99, 0.12), 0 4px 16px rgba(0, 0, 0, 0.06);
        --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        --radius-sm: 8px;
        --radius-md: 12px;
        --radius-lg: 16px;
        --radius-xl: 20px;
        --radius-2xl: 24px;
        --radius-full: 999px;
    }

    /* ===== Base Styles ===== */
    .worker-dashboard {
        padding: 2rem;
        background: var(--bg-app);
        min-height: 100vh;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        animation: fadeIn 0.5s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ===== Header ===== */
    .dashboard-header {
        margin-bottom: 2rem;
        background: var(--bg-surface);
        border: 1px solid var(--border-light);
        border-radius: var(--radius-2xl);
        padding: 1.75rem 2rem;
        box-shadow: var(--card-shadow);
        transition: var(--transition);
    }

    .dashboard-header:hover {
        box-shadow: var(--card-hover-shadow);
    }

    .greeting {
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-dark);
        margin: 0 0 0.5rem;
        line-height: 1.1;
        letter-spacing: -0.5px;
    }

    .wave-emoji {
        font-size: 1.75rem;
        display: inline-block;
        animation: wave 2s ease-in-out infinite;
    }

    @keyframes wave {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(20deg); }
        75% { transform: rotate(-15deg); }
    }

    .date-info {
        color: var(--text-muted);
        font-size: 0.95rem;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .date-info .separator {
        color: var(--border-medium);
    }

    /* ===== Notification Cards Row ===== */
    .dashboard-notification-row {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .notification-card {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.5rem;
        border-radius: var(--radius-xl);
        background: var(--bg-surface);
        border: 1px solid var(--border-light);
        transition: var(--transition);
        color: var(--text-dark);
        text-decoration: none;
        box-shadow: var(--card-shadow);
        position: relative;
        overflow: hidden;
    }

    .notification-card::before {
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

    .notification-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--card-hover-shadow);
        text-decoration: none;
        color: var(--text-dark);
    }

    .notification-card:hover::before {
        opacity: 1;
    }

    .notification-card-icon {
        min-width: 56px;
        min-height: 56px;
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.5rem;
        flex-shrink: 0;
        box-shadow: var(--shadow-md);
    }

    .bg-primary-gradient { background: linear-gradient(135deg, var(--info), #2563EB); }
    .bg-success-gradient { background: linear-gradient(135deg, var(--success), #059669); }
    .bg-warning-gradient { background: linear-gradient(135deg, var(--warning), #D97706); }

    .notification-card-content {
        flex: 1;
        min-width: 0;
    }

    .notification-card-label {
        margin: 0 0 0.35rem;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: var(--text-muted);
        font-weight: 600;
    }

    .notification-card-value {
        margin: 0 0 0.35rem;
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--text-dark);
        line-height: 1;
    }

    .notification-card-hint {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    /* ===== Status Cards ===== */
    .status-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1.25rem;
        margin-bottom: 2rem;
    }

    .status-card {
        background: var(--bg-surface);
        border-radius: var(--radius-xl);
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        border: 1px solid var(--border-light);
        box-shadow: var(--card-shadow);
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .status-card::after {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        transition: var(--transition);
    }

    .status-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--card-hover-shadow);
    }

    .status-primary::after { background: var(--accent); }
    .status-danger::after { background: var(--danger); }
    .status-secondary::after { background: var(--text-muted); }
    .status-warning::after { background: var(--warning); }

    .status-icon-wrapper {
        width: 52px;
        height: 52px;
        border-radius: var(--radius-lg);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        flex-shrink: 0;
    }

    .status-primary .status-icon-wrapper { background: rgba(22, 153, 161, 0.1); color: var(--accent); }
    .status-danger .status-icon-wrapper { background: rgba(239, 68, 68, 0.1); color: var(--danger); }
    .status-secondary .status-icon-wrapper { background: rgba(100, 116, 139, 0.1); color: var(--text-muted); }
    .status-warning .status-icon-wrapper { background: rgba(245, 158, 11, 0.1); color: var(--warning); }

    .status-info {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .status-number {
        font-size: 1.75rem;
        font-weight: 800;
        line-height: 1;
        color: var(--text-dark);
    }

    .status-text {
        font-size: 0.85rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    /* ===== Chat Panel ===== */
    .chat-panel {
        margin-bottom: 2rem;
    }

    .dash-card {
        background: var(--bg-surface);
        border-radius: var(--radius-2xl);
        border: 1px solid var(--border-light);
        box-shadow: var(--card-shadow);
        transition: var(--transition);
    }

    .dash-card:hover {
        box-shadow: var(--card-hover-shadow);
    }

    .dash-card-header {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1.75rem 1.75rem 0;
        margin-bottom: 1.5rem;
    }

    .card-badge {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-md);
        background: var(--bg-app);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .card-heading {
        flex: 1;
        min-width: 0;
    }

    .card-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: var(--text-dark);
        margin: 0 0 0.35rem;
        letter-spacing: -0.3px;
    }

    .card-subtitle {
        display: inline-block;
        font-size: 0.85rem;
        color: var(--text-muted);
        line-height: 1.5;
    }

    .chat-card-body {
        padding: 0 1.75rem 1.75rem;
    }

    .chat-participant-info {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem;
        background: var(--bg-app);
        border-radius: var(--radius-lg);
        margin-bottom: 1.25rem;
        border: 1px solid var(--border-light);
    }

    .participant-avatar {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--accent), var(--primary));
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .participant-details {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .participant-details strong {
        font-size: 1rem;
        color: var(--text-dark);
    }

    .participant-details span {
        font-size: 0.85rem;
        color: var(--text-muted);
    }

    .chat-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn-modern {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        border-radius: var(--radius-md);
        font-weight: 600;
        font-size: 0.9rem;
        text-decoration: none;
        transition: var(--transition);
        border: none;
        cursor: pointer;
    }

    .btn-primary-modern {
        background: linear-gradient(135deg, var(--accent), var(--accent-dark));
        color: #fff;
        box-shadow: 0 4px 12px rgba(22, 153, 161, 0.25);
    }

    .btn-primary-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(22, 153, 161, 0.35);
        color: #fff;
    }

    .btn-outline-modern {
        background: var(--bg-surface);
        color: var(--text-secondary);
        border: 1px solid var(--border-medium);
    }

    .btn-outline-modern:hover {
        background: var(--bg-app);
        color: var(--accent);
        border-color: var(--accent);
    }

    .empty-state-chat {
        text-align: center;
        padding: 2rem 1rem;
        color: var(--text-muted);
    }

    .empty-state-chat i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        opacity: 0.5;
        display: block;
    }

    .empty-state-chat p {
        margin-bottom: 1.25rem;
    }

    /* ===== Content Grid ===== */
    .content-grid {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    /* ===== Shifts Table ===== */
    .shifts-card {
        min-height: 300px;
    }

    .table-responsive-custom {
        padding: 0 1.75rem 1.75rem;
        overflow-x: auto;
    }

    .table-responsive-custom .shifts-table {
        min-width: 100%;
        width: 100%;
    }

    .shifts-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .shifts-table thead th {
        padding: 1rem 1.25rem;
        text-align: left;
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background: var(--bg-app);
        border-bottom: 2px solid var(--border-light);
    }

    .shifts-table thead th:first-child {
        border-radius: var(--radius-md) 0 0 0;
    }

    .shifts-table thead th:last-child {
        border-radius: 0 var(--radius-md) 0 0;
        text-align: right;
    }

    .shifts-table tbody tr {
        transition: var(--transition);
    }

    .shifts-table tbody tr:hover {
        background: rgba(22, 153, 161, 0.03);
    }

    .shifts-table td {
        padding: 1.25rem;
        border-bottom: 1px solid var(--border-light);
        font-size: 0.9rem;
        color: var(--text-secondary);
        vertical-align: middle;
    }

    .shifts-table td:last-child {
        text-align: right;
    }

    .time-cell {
        font-weight: 700;
        color: var(--text-dark);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .time-cell i {
        color: var(--accent);
    }

    .participant-name-cell {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .mini-avatar-sm {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--accent), var(--primary));
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.8rem;
        flex-shrink: 0;
    }

    .risk-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.85rem;
        border-radius: var(--radius-full);
        font-size: 0.75rem;
        font-weight: 700;
    }

    .risk-high {
        background: rgba(239, 68, 68, 0.1);
        color: var(--danger);
    }

    .risk-low {
        background: rgba(16, 185, 129, 0.1);
        color: var(--success);
    }

    .empty-state {
        text-align: center;
        padding: 3rem 1rem !important;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 2.5rem;
        margin-bottom: 0.75rem;
        opacity: 0.4;
        display: block;
    }

    .btn-icon-sm {
        width: 36px;
        height: 36px;
        border-radius: var(--radius-md);
        background: var(--bg-app);
        color: var(--text-secondary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: var(--transition);
    }

    .btn-icon-sm:hover {
        background: var(--accent);
        color: #fff;
        transform: translateX(2px);
    }

    /* ===== Quick Actions ===== */
    .actions-list {
        padding: 0 1.75rem 1.75rem;
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
    }

    .action-btn {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.25rem;
        border-radius: var(--radius-lg);
        text-decoration: none;
        color: #fff;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }

    .action-btn::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        transform: translateX(-100%);
        transition: transform 0.5s ease;
    }

    .action-btn:hover::before {
        transform: translateX(100%);
    }

    .action-btn:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
        color: #fff;
    }

    .action-start { background: linear-gradient(135deg, #14b8a6, #0d9488); }
    .action-submit { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); }
    .action-report { background: linear-gradient(135deg, var(--danger), #b91c1c); }
    .action-upload { background: linear-gradient(135deg, #7c3aed, #6d28d9); }

    .action-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-md);
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .action-text {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }

    .action-title {
        font-weight: 700;
        font-size: 0.95rem;
    }

    .action-desc {
        font-size: 0.8rem;
        opacity: 0.85;
    }

    /* ===== Workflow Section ===== */
    .workflow-section {
        margin-bottom: 2rem;
    }

    .workflow-card {
        padding-bottom: 1.75rem;
    }

    .workflow-steps {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        padding: 0 1.75rem 2rem;
        position: relative;
    }

    .workflow-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
        flex: 1;
        position: relative;
        z-index: 2;
    }

    .step-number {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.1rem;
        color: #fff;
        box-shadow: var(--shadow-md);
        transition: var(--transition);
    }

    .workflow-step:hover .step-number {
        transform: scale(1.1);
    }

    .step-1 { background: linear-gradient(135deg, #14b8a6, #0d9488); }
    .step-2 { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); }
    .step-3 { background: linear-gradient(135deg, #7c3aed, #6d28d9); }
    .step-4 { background: linear-gradient(135deg, var(--warning), #D97706); }
    .step-5 { background: linear-gradient(135deg, var(--danger), #b91c1c); }

    .step-label {
        font-size: 0.85rem;
        color: var(--text-secondary);
        text-align: center;
        margin: 0;
        font-weight: 600;
        line-height: 1.3;
    }

    .step-connector {
        flex: 1;
        height: 3px;
        background: linear-gradient(90deg, var(--border-light), var(--border-medium), var(--border-light));
        margin-top: 22px;
        margin-bottom: 26px;
        border-radius: 2px;
    }

    .workflow-info {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1.25rem;
        padding: 0 1.75rem;
    }

    .info-box {
        padding: 1.25rem;
        border-radius: var(--radius-lg);
        display: flex;
        gap: 1rem;
        border: 1px solid transparent;
        transition: var(--transition);
    }

    .info-box:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
    }

    .info-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-md);
        background: rgba(255, 255, 255, 0.8);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    .info-content strong {
        display: block;
        font-size: 0.9rem;
        margin-bottom: 0.35rem;
        color: var(--text-dark);
    }

    .info-content p {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin: 0;
        line-height: 1.5;
    }

    .info-access {
        background: rgba(16, 185, 129, 0.08);
        border-color: rgba(16, 185, 129, 0.15);
    }
    .info-access .info-icon { color: var(--success); }

    .info-mobile {
        background: rgba(59, 130, 246, 0.08);
        border-color: rgba(59, 130, 246, 0.15);
    }
    .info-mobile .info-icon { color: var(--info); }

    .info-compliance {
        background: rgba(245, 158, 11, 0.08);
        border-color: rgba(245, 158, 11, 0.15);
    }
    .info-compliance .info-icon { color: var(--warning); }

    /* ===== Responsive Design ===== */
    
    /* Large screens (1200px and up) */
    @media (min-width: 1200px) {
        .worker-dashboard {
            padding: 2.5rem;
        }
    }

    /* Tablets and small desktops (992px - 1199px) */
    @media (max-width: 1199.98px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
        
        .actions-column {
            order: -1;
        }

        .actions-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
        }
    }

    /* Tablets (768px - 991px) */
    @media (max-width: 991.98px) {
        .worker-dashboard {
            padding: 1.5rem;
        }

        .dashboard-notification-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .status-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .workflow-info {
            grid-template-columns: 1fr;
        }
    }

    /* Mobile devices (576px - 767px) */
    @media (max-width: 767.98px) {
        .worker-dashboard {
            padding: 1.25rem;
        }

        .dashboard-header {
            padding: 1.5rem;
        }

        .greeting {
            font-size: 1.5rem;
        }

        .dashboard-notification-row {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .status-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .status-card {
            padding: 1.25rem;
        }

        .content-grid {
            gap: 1.25rem;
        }

        .actions-list {
            grid-template-columns: 1fr;
        }

        .workflow-steps {
            flex-direction: column;
            gap: 0;
            padding-left: 2rem;
        }

        .workflow-step {
            flex-direction: row;
            align-items: center;
            gap: 1.25rem;
            width: 100%;
            padding: 1rem 0;
        }

        .step-number {
            width: 40px;
            height: 40px;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .step-label {
            text-align: left;
            font-size: 0.9rem;
        }

        .step-connector {
            width: 3px;
            height: 20px;
            margin: 0 0 0 18px;
            background: linear-gradient(180deg, var(--border-light), var(--border-medium));
        }

        .workflow-step:last-child + .step-connector {
            display: none;
        }

        .table-responsive-custom {
            padding: 0 1.25rem 1.25rem;
        }

        .shifts-table thead {
            display: none;
        }

        .shifts-table tbody tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid var(--border-light);
            border-radius: var(--radius-lg);
            padding: 1rem;
            background: var(--bg-surface);
        }

        .shifts-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-light);
        }

        .shifts-table td:last-child {
            border-bottom: none;
            justify-content: flex-end;
        }

        .shifts-table td::before {
            content: attr(data-label);
            font-weight: 700;
            color: var(--text-muted);
            font-size: 0.8rem;
            text-transform: uppercase;
        }

        /* Add data-label via CSS for mobile table */
        .shifts-table td:nth-child(1)::before { content: "Time"; }
        .shifts-table td:nth-child(2)::before { content: "Participant"; }
        .shifts-table td:nth-child(3)::before { content: "Service"; }
        .shifts-table td:nth-child(4)::before { content: "Risk Alert"; }
        .shifts-table td:nth-child(5)::before { content: "Action"; }
    }

    /* Small mobile devices (375px - 575px) */
    @media (max-width: 575.98px) {
        .worker-dashboard {
            padding: 1rem;
        }

        .dashboard-header {
            padding: 1.25rem;
            border-radius: var(--radius-xl);
        }

        .greeting {
            font-size: 1.35rem;
        }

        .date-info {
            font-size: 0.85rem;
        }

        .dash-card {
            border-radius: var(--radius-xl);
        }

        .dash-card-header {
            padding: 1.25rem 1.25rem 0;
            margin-bottom: 1.25rem;
        }

        .card-title {
            font-size: 1.05rem;
        }

        .notification-card {
            padding: 1.25rem;
        }

        .notification-card-value {
            font-size: 1.5rem;
        }

        .chat-card-body {
            padding: 0 1.25rem 1.25rem;
        }

        .chat-actions {
            flex-direction: column;
        }

        .btn-modern {
            justify-content: center;
            width: 100%;
        }

        .action-btn {
            padding: 0.85rem 1rem;
        }

        .action-title {
            font-size: 0.9rem;
        }

        .action-desc {
            font-size: 0.75rem;
        }

        .workflow-info {
            padding: 0 1.25rem;
        }

        .info-box {
            padding: 1rem;
        }
    }

    /* Extra small devices (374px and down) */
    @media (max-width: 374.98px) {
        .worker-dashboard {
            padding: 0.875rem;
        }

        .dashboard-header {
            padding: 1rem;
        }

        .greeting {
            font-size: 1.2rem;
        }

        .notification-card-icon {
            min-width: 48px;
            min-height: 48px;
            font-size: 1.25rem;
        }

        .notification-card-value {
            font-size: 1.35rem;
        }

        .status-number {
            font-size: 1.5rem;
        }

        .step-number {
            width: 36px;
            height: 36px;
            font-size: 0.9rem;
        }
    }

    /* Landscape mode */
    @media (max-height: 500px) and (orientation: landscape) {
        .worker-dashboard {
            padding: 1rem;
        }

        .dashboard-header {
            padding: 1rem 1.5rem;
        }

        .workflow-steps {
            flex-direction: row;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .step-connector {
            display: none;
        }

        .workflow-step {
            flex-direction: column;
            width: auto;
            flex: 0 0 calc(20% - 1rem);
        }
    }

    /* Touch devices */
    @media (hover: none) and (pointer: coarse) {
        .notification-card,
        .status-card,
        .action-btn,
        .btn-modern,
        .btn-icon-sm {
            min-height: 44px;
        }

        .dash-card:hover,
        .notification-card:hover,
        .status-card:hover,
        .action-btn:hover {
            transform: none;
        }
    }

    /* Print styles */
    @media print {
        .worker-dashboard {
            background: white;
            padding: 0;
        }

        .dash-card,
        .dashboard-header,
        .notification-card,
        .status-card {
            box-shadow: none;
            border: 1px solid #ddd;
            break-inside: avoid;
        }

        .action-btn,
        .btn-modern,
        .btn-icon-sm,
        .notification-card-arrow {
            display: none;
        }
    }
</style>

@include('components.notification-alert-modal')
@endsection