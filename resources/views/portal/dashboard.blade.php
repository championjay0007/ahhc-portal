@extends('layouts.portal')

@section('content')
<div class="dashboard-page">
    <!-- Welcome Header -->
    <div class="dashboard-header">
        <div class="header-greeting">
            <h1 class="greeting-title">Welcome back, {{ $participantName }} <span class="wave-emoji">👋</span></h1>
            <p class="greeting-subtitle">Here's what's happening with your supports today.</p>
        </div>
        <div class="header-actions">
            <a href="{{ route('portal.notifications') }}" class="icon-btn icon-link" title="Notifications">
                <i class="bi bi-bell"></i>
                @if(isset($unreadNotificationCount) && $unreadNotificationCount > 0)
                    <span class="icon-badge">{{ $unreadNotificationCount }}</span>
                @endif
            </a>
            <a href="{{ route($messageRoutePrefix.'inbox') }}" class="icon-btn icon-link" title="Messages">
                <i class="bi bi-envelope"></i>
                @if(isset($unreadMessageCount) && $unreadMessageCount > 0)
                    <span class="icon-badge">{{ $unreadMessageCount }}</span>
                @endif
            </a>
            <a href="{{ route('portal.gallery') }}" class="icon-btn icon-link" title="Gallery">
                <i class="bi bi-images"></i>
            </a>
            <div class="user-dropdown">
                <div class="user-avatar">
                    {{ substr($participantName, 0, 1) }}{{ substr(explode(' ', $participantName)[1] ?? '', 0, 1) }}
                </div>
                <div class="user-meta">
                    <span class="user-name">{{ $participantName }}</span>
                    <span class="user-role">Participant</span>
                </div>
                <i class="bi bi-chevron-down"></i>
            </div>
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
                <small class="notification-card-hint">View portal notifications</small>
            </div>
        </a>
        <a href="{{ route($messageRoutePrefix.'inbox') }}" class="notification-card card-link">
            <div class="notification-card-icon bg-success-gradient">
                <i class="bi bi-envelope-fill"></i>
            </div>
            <div class="notification-card-content">
                <p class="notification-card-label">Unread messages</p>
                <h3 class="notification-card-value">{{ $unreadMessageCount ?? 0 }}</h3>
                <small class="notification-card-hint">Open your inbox</small>
            </div>
        </a>
        <a href="{{ route('portal.gallery') }}" class="notification-card card-link">
            <div class="notification-card-icon bg-secondary-gradient">
                <i class="bi bi-images-fill"></i>
            </div>
            <div class="notification-card-content">
                <p class="notification-card-label">Shared Gallery</p>
                <h3 class="notification-card-value">Open</h3>
                <small class="notification-card-hint">Browse uploaded media</small>
            </div>
        </a>
        <a href="{{ route('portal.notifications.preferences') }}" class="notification-card card-link">
            <div class="notification-card-icon bg-warning-gradient">
                <i class="bi bi-gear-fill"></i>
            </div>
            <div class="notification-card-content">
                <p class="notification-card-label">Email preferences</p>
                <h3 class="notification-card-value">Manage</h3>
                <small class="notification-card-hint">Update notification delivery</small>
            </div>
        </a>
    </div>

    @if(isset($onboardingInProgress) && $onboardingInProgress)
        <div class="dashboard-onboarding-row mb-4">
            <div class="dash-card onboarding-card">
                <div class="dash-card-header">
                    <div class="card-badge">A</div>
                    <div class="card-heading">
                        <h2 class="card-title">Onboarding progress</h2>
                        <span class="card-subtitle">Complete your Allegiance Heart & Home Care participant setup</span>
                    </div>
                </div>

                <div class="onboarding-body">
                    <div class="onboarding-overview">
                        <div>
                            <span class="small text-uppercase text-muted">Current step</span>
                            <h3 class="mb-1">Step {{ $onboardingCurrentStep }} of 8</h3>
                            <p class="mb-0 text-muted">{{ $onboardingRemainingSteps }} step{{ $onboardingRemainingSteps === 1 ? '' : 's' }} remaining</p>
                        </div>
                        <div class="onboarding-status-badge badge bg-{{ $onboardingStatus === 'complete' ? 'success' : ($onboardingStatus === 'draft' ? 'warning' : 'info') }} text-white">{{ ucfirst(str_replace('_', ' ', $onboardingStatus)) }}</div>
                    </div>

                    <div class="progress onboarding-progress mb-3" style="height: 14px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $onboardingCompletionPercent }}%;" aria-valuenow="{{ $onboardingCompletionPercent }}" aria-valuemin="0" aria-valuemax="100">{{ $onboardingCompletionPercent }}%</div>
                    </div>

                    <div class="onboarding-stats mb-3">
                        <div>
                            <span class="text-muted">Signed agreements</span>
                            <h3 class="mb-0">{{ $onboardingSignedAgreementCount }}</h3>
                        </div>
                        <div>
                            <span class="text-muted">Completion</span>
                            <h3 class="mb-0">{{ $onboardingCompletionPercent }}%</h3>
                        </div>
                        <div>
                            <span class="text-muted">Documents</span>
                            <h3 class="mb-0">{{ $documentsCount }}</h3>
                        </div>
                    </div>

                    <div class="onboarding-documents-summary mb-3">
                        @if(isset($missingDocumentCategories) && $missingDocumentCategories->isNotEmpty())
                            <div class="alert alert-warning py-3">
                                <strong>{{ $missingDocumentCategories->count() }} required document{{ $missingDocumentCategories->count() === 1 ? '' : 's' }} missing:</strong>
                                {{ $missingDocumentCategories->implode(', ') }}.
                            </div>
                        @else
                            <div class="alert alert-success py-3 mb-0">
                                <strong>All required onboarding documents are uploaded.</strong>
                            </div>
                        @endif
                    </div>

                    <div class="onboarding-steps">
                        @foreach($onboardingSteps as $step)
                            <div class="onboarding-step-item {{ $step['state'] }}">
                                <span class="step-pill">{{ $step['step'] }}</span>
                                <div>
                                    <strong>{{ $step['label'] }}</strong>
                                    <div class="text-muted small">
                                        @if($step['state'] === 'completed') Completed @elseif($step['state'] === 'current') In progress @else Pending @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex flex-wrap gap-2 mt-3">
                        @if($onboardingResumeUrl)
                            <a href="{{ $onboardingResumeUrl }}" class="btn btn-primary">Resume onboarding</a>
                        @endif
                        <a href="{{ route('portal.participant.documents.index') }}" class="btn btn-outline-secondary">View documents</a>
                        <a href="{{ route('portal.participant.documents.index') }}" class="btn btn-outline-primary">View signed agreements</a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Top Row: Budget + Services -->
    <div class="dashboard-top-row">
        <!-- Card 1: Quarterly Budget -->
        <div class="dash-card budget-card">
            <div class="dash-card-header">
                <div class="card-badge">1</div>
                <div class="card-heading">
                    <h2 class="card-title">Quarterly Budget</h2>
                    <span class="card-subtitle">{{ $currentQuarterLabel ?? 'Current quarter' }}</span>
                </div>
            </div>

            <div class="budget-body">
                <div class="budget-stats">
                    <div class="budget-stat">
                        <span class="budget-stat-label">Total Available</span>
                        <span class="budget-stat-value">${{ number_format($budgetLimitCents / 100, 2) }}</span>
                    </div>
                    <div class="budget-stat">
                        <span class="budget-stat-label">Used</span>
                        <span class="budget-stat-value stat-used">${{ number_format($usedBudgetCents / 100, 2) }}</span>
                    </div>
                    <div class="budget-stat">
                        <span class="budget-stat-label">Committed</span>
                        <span class="budget-stat-value stat-committed">${{ number_format(($committedBudgetCents ?? 0) / 100, 2) }}</span>
                    </div>
                    <div class="budget-stat">
                        <span class="budget-stat-label">Remaining</span>
                        <span class="budget-stat-value stat-remaining">${{ number_format($remainingBudgetCents / 100, 2) }}</span>
                    </div>
                </div>

                <div class="budget-chart-area">
                    <div class="donut-chart" data-percent="{{ $budgetPercent }}">
                        <svg viewBox="0 0 120 120" class="donut-svg">
                            <circle class="donut-bg" cx="60" cy="60" r="50"></circle>
                            <circle class="donut-fill" cx="60" cy="60" r="50"
                                stroke-dasharray="{{ $budgetPercent * 3.1416 }} 314.16"
                                transform="rotate(-90 60 60)"></circle>
                        </svg>
                        <div class="donut-center">
                            <span class="donut-percent">{{ $budgetPercent }}%</span>
                            <span class="donut-label">Used</span>
                        </div>
                    </div>
                    <div class="chart-tags">
                        <span class="live-tag"><i class="bi bi-circle-fill"></i> Live Budget Update</span>
                        <span class="updated-tag">Updated: {{ $budgetUpdatedAtLabel ?? now()->format('j M Y') }} <i class="bi bi-info-circle"></i></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Card 2: Upcoming Services -->
        <div class="dash-card services-card">
            <div class="dash-card-header">
                <div class="card-badge">2</div>
                <div class="card-heading">
                    <h2 class="card-title">Upcoming Services</h2>
                    <span class="card-subtitle">Your participant-managed / planned services</span>
                </div>
            </div>

            <div class="services-list">
                @if(! isset($upcomingServices) || $upcomingServices->isEmpty())
                    <div class="empty-state">
                        <i class="bi bi-calendar-x"></i>
                        <p>No upcoming services from your pre-approvals.</p>
                    </div>
                @else
                    @foreach($upcomingServices->take(3) as $service)
                        <div class="service-row">
                            <div class="service-icon-box">
                                <i class="bi bi-person-badge"></i>
                            </div>
                            <div class="service-info">
                                <strong class="service-name">{{ $service->service_type ?? $service->service_category ?? 'Support Service' }}</strong>
                                <span class="service-provider">{{ optional($service->worker)->first_name }} {{ optional($service->worker)->last_name }}</span>
                                <span class="service-datetime">
                                    <i class="bi bi-calendar3"></i>
                                    {{ optional($service->start_date)->format('D, d M Y') ?? 'Date pending' }} • {{ $service->start_time ?? 'Time TBA' }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            <a href="{{ route('portal.participant.pre_approvals.index') }}" class="view-all">
                View all services <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Middle Row: Workers, Pre-Approvals, Invoices -->
    <div class="dashboard-summary-row">
        <!-- Card 3 -->
        <div class="dash-card summary-card">
            <div class="dash-card-header">
                <div class="card-badge">3</div>
                <div class="card-heading">
                    <h2 class="card-title">Approved Workers / Suppliers</h2>
                </div>
            </div>
            <div class="summary-body">
                <p class="summary-lead">You have <strong>{{ $assignments->count() }}</strong> approved</p>
                <div class="avatar-stack">
                    @foreach($assignments->take(4) as $assignment)
                        <div class="mini-avatar" title="{{ optional($assignment->worker)->first_name }}" style="background-image: url('{{ optional(optional($assignment->worker)->user)->profile_photo_url }}'); background-size: cover; background-position: center; color: transparent;">
                            {{ substr(optional($assignment->worker)->first_name ?? 'W', 0, 1) }}
                        </div>
                    @endforeach
                    @if($assignments->count() > 4)
                        <div class="mini-avatar mini-avatar-more">+{{ $assignments->count() - 4 }}</div>
                    @endif
                </div>
                <a href="{{ route('portal.participant.team') }}" class="summary-link">
                    Manage workers / suppliers <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="dash-card summary-card">
            <div class="dash-card-header">
                <div class="card-badge">4</div>
                <div class="card-heading">
                    <h2 class="card-title">Pre-Approvals</h2>
                </div>
            </div>
            <div class="summary-body">
                <div class="stat-pair">
                    <div class="stat-pill stat-pill-success">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>{{ $preApprovalsApprovedCount }} Approved</span>
                    </div>
                    <div class="stat-pill stat-pill-warning">
                        <i class="bi bi-clock"></i>
                        <span>{{ $preApprovalsPendingCount }} Pending</span>
                    </div>
                </div>
                <a href="{{ route('portal.participant.pre_approvals.index') }}" class="summary-link">
                    View all pre-approvals <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Card 5 -->
        <div class="dash-card summary-card">
            <div class="dash-card-header">
                <div class="card-badge">5</div>
                <div class="card-heading">
                    <h2 class="card-title">Invoice Submissions</h2>
                </div>
            </div>
            <div class="summary-body">
                <div class="stat-pair">
                    <div class="stat-pill stat-pill-info">
                        <i class="bi bi-file-earmark-text"></i>
                        <span>{{ $submittedInvoicesCount }} Submitted</span>
                    </div>
                    <div class="stat-pill stat-pill-success">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>{{ $paidInvoicesCount }} Paid</span>
                    </div>
                </div>
                <a href="{{ route('portal.participant.invoices.index') }}" class="summary-link">
                    View all invoices <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Chat Row -->
    <div class="dashboard-chat-row">
        <div class="dash-card">
            <div class="dash-card-header">
                <div class="card-badge">6</div>
                <div class="card-heading">
                    <h2 class="card-title">Chat with Assigned Worker</h2>
                    <span class="card-subtitle">Message your primary support contact instantly.</span>
                </div>
            </div>
            @php
                $primaryAssignment = $assignments->first();
                $primaryWorker = optional($primaryAssignment)->worker;
            @endphp
            <div class="summary-body">
                @if($primaryWorker)
                    <p class="summary-lead">Your assigned worker is <strong>{{ $primaryWorker->first_name }} {{ $primaryWorker->last_name }}</strong>.</p>
                    <a href="{{ route($messageRoutePrefix.'conversation', $primaryWorker->user_id) }}" class="summary-link">
                        Chat with {{ $primaryWorker->first_name }} <i class="bi bi-chat-dots"></i>
                    </a>
                    <a href="{{ route('portal.participant.team') }}" class="summary-link">
                        View all assigned workers <i class="bi bi-arrow-right"></i>
                    </a>
                @else
                    <p class="summary-lead">No active assigned workers were found.</p>
                    <a href="{{ route('portal.participant.team') }}" class="summary-link">
                        View your care team <i class="bi bi-arrow-right"></i>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Bottom Row: Signatures, Documents, Help -->
    <div class="dashboard-summary-row">
        <!-- Card 7 -->
        <div class="dash-card summary-card">
            <div class="dash-card-header">
                <div class="card-badge">7</div>
                <div class="card-heading">
                    <h2 class="card-title">Pending Signatures</h2>
                </div>
            </div>
            <div class="summary-body">
                <div class="action-box">
                    <i class="bi bi-pencil-square action-icon"></i>
                    <div class="action-text">
                        <span class="action-count">{{ $pendingDocumentsCount }}</span>
                        <span class="action-label">Forms waiting for your signature</span>
                    </div>
                </div>
                <a href="{{ route('portal.participant.documents.pending') }}" class="summary-link">
                    Review & sign now <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Card 8 -->
        <div class="dash-card summary-card">
            <div class="dash-card-header">
                <div class="card-badge">8</div>
                <div class="card-heading">
                    <h2 class="card-title">Documents</h2>
                </div>
            </div>
            <div class="summary-body">
                <div class="doc-pair">
                    <div class="doc-stat">
                        <span class="doc-number">{{ $documentsCount }}</span>
                        <span class="doc-label">Recent uploads</span>
                    </div>
                    <div class="doc-stat">
                        <span class="doc-number">{{ $pendingDocumentsCount }}</span>
                        <span class="doc-label">Need your review</span>
                    </div>
                </div>
                <a href="{{ route('portal.participant.documents.index') }}" class="summary-link">
                    Upload / review documents <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Card 9 -->
        <div class="dash-card summary-card gallery-card">
            <div class="dash-card-header">
                <div class="card-badge">9</div>
                <div class="card-heading">
                    <h2 class="card-title">Shared Gallery</h2>
                </div>
            </div>
            <div class="summary-body">
                <div class="action-box">
                    <i class="bi bi-images action-icon action-icon-secondary"></i>
                    <div class="action-text">
                        <span class="action-label">Browse uploaded photos, videos, and documents.</span>
                    </div>
                </div>
                <a href="{{ route('portal.gallery') }}" class="summary-link">
                    Open gallery <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>

        <!-- Card 10 -->
        <div class="dash-card summary-card help-card">
            <div class="dash-card-header">
                <div class="card-badge">10</div>
                <div class="card-heading">
                    <h2 class="card-title">Help / Feedback</h2>
                </div>
            </div>
            <div class="summary-body">
                <div class="action-box">
                    <i class="bi bi-chat-square-heart action-icon action-icon-success"></i>
                    <div class="action-text">
                        <span class="action-label">We're here to support you</span>
                    </div>
                </div>
                <a href="{{ route('portal.participant.complaints.create') }}" class="summary-link">
                    Get help / Give feedback <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Notification Alert Modal -->
@include('components.notification-alert-modal')

<style>
    /* ===== CSS Variables ===== */
    :root {
        --primary: #0E3863;
        --primary-dark: #092B4A;
        --primary-light: #1A4D7F;
        --accent: #1699A1;
        --accent-dark: #0F7C88;
        --accent-light: #1DB5BE;
        --success: #10B981;
        --success-light: #D1FAE5;
        --warning: #F59E0B;
        --warning-light: #FEF3C7;
        --danger: #EF4444;
        --danger-light: #FEE2E2;
        --info: #3B82F6;
        --info-light: #DBEAFE;
        --bg-app: #F4F7FC;
        --bg-surface: #ffffff;
        --text-dark: #0B2B3F;
        --text-primary: #1E293B;
        --text-secondary: #475569;
        --text-muted: #64748B;
        --text-light: #94A3B8;
        --border-light: rgba(14, 56, 99, 0.08);
        --border-medium: rgba(14, 56, 99, 0.12);
        --shadow-sm: 0 2px 8px rgba(14, 56, 99, 0.06);
        --shadow-md: 0 4px 16px rgba(14, 56, 99, 0.08);
        --shadow-lg: 0 8px 32px rgba(14, 56, 99, 0.12);
        --shadow-xl: 0 16px 48px rgba(14, 56, 99, 0.16);
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

    /* ===== Dashboard Page ===== */
    .dashboard-page {
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
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1.5rem;
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

    .greeting-title {
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

    .greeting-subtitle {
        color: var(--text-muted);
        font-size: 0.95rem;
        margin: 0;
    }

    .header-actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: nowrap;
        flex-shrink: 0;
    }

    .icon-btn {
        width: 44px;
        height: 44px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-medium);
        background: var(--bg-surface);
        color: var(--text-secondary);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        font-size: 1.2rem;
        transition: var(--transition);
        position: relative;
        flex-shrink: 0;
        overflow: visible;
    }

    .icon-btn:hover {
        transform: translateY(-2px);
        background: var(--bg-app);
        color: var(--accent);
        border-color: var(--accent);
        box-shadow: var(--shadow-md);
    }

    .icon-link {
        position: relative;
        text-decoration: none;
    }

    .icon-badge {
        position: absolute;
        top: -3px;
        right: -3px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        border-radius: var(--radius-full);
        background: var(--danger);
        color: #fff;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0 0.35rem;
        border: 2px solid var(--bg-surface);
        animation: pulse 2s infinite;
        z-index: 2;
        box-shadow: 0 0 0 1px rgba(14, 56, 99, 0.08);
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    .user-dropdown {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: var(--bg-app);
        padding: 0.5rem 1rem;
        border-radius: var(--radius-full);
        border: 1px solid var(--border-light);
        cursor: pointer;
        transition: var(--transition);
    }

    .user-dropdown:hover {
        background: var(--bg-surface);
        border-color: var(--accent);
        box-shadow: var(--shadow-sm);
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--accent), var(--primary));
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.85rem;
        flex-shrink: 0;
    }

    .user-meta {
        display: flex;
        flex-direction: column;
        line-height: 1.2;
    }

    .user-name {
        font-weight: 700;
        font-size: 0.9rem;
        color: var(--text-dark);
    }

    .user-role {
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .user-dropdown .bi-chevron-down {
        color: var(--text-light);
        font-size: 0.85rem;
        transition: var(--transition);
    }

    .user-dropdown:hover .bi-chevron-down {
        color: var(--accent);
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

    .bg-primary-gradient {
        background: linear-gradient(135deg, var(--info), #2563EB);
    }

    .bg-success-gradient {
        background: linear-gradient(135deg, var(--success), #059669);
    }

    .bg-warning-gradient {
        background: linear-gradient(135deg, var(--warning), #D97706);
    }

    .notification-card-content {
        flex: 1;
        min-width: 0;
    }

    .dashboard-header,
    .notification-card {
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

    /* ===== Cards ===== */
    .dash-card {
        background: var(--bg-surface);
        border-radius: var(--radius-2xl);
        padding: 1.75rem;
        border: 1px solid var(--border-light);
        box-shadow: var(--card-shadow);
        transition: var(--transition);
    }

    .dash-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--card-hover-shadow);
    }

    .dash-card-header {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .card-badge {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(22, 153, 161, 0.15), rgba(14, 56, 99, 0.1));
        color: var(--accent-dark);
        font-weight: 800;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 2px solid rgba(22, 153, 161, 0.2);
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

    .card-arrows {
        display: flex;
        gap: 0.5rem;
    }

    .arrow-btn {
        width: 36px;
        height: 36px;
        border-radius: var(--radius-md);
        border: 1px solid var(--border-medium);
        background: var(--bg-surface);
        color: var(--text-secondary);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.9rem;
        transition: var(--transition);
    }

    .arrow-btn:hover {
        background: var(--bg-app);
        color: var(--accent);
        border-color: var(--accent);
    }

    /* ===== Top Row ===== */
    .dashboard-top-row {
        display: grid;
        grid-template-columns: 1.4fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.75rem;
    }

    /* ===== Budget Card ===== */
    .budget-card {
        position: relative;
        overflow: hidden;
        background: linear-gradient(180deg, rgba(22, 153, 161, 0.08) 0%, var(--bg-surface) 100%);
    }

    .budget-card::after {
        content: '';
        position: absolute;
        top: -40px;
        right: -40px;
        width: 160px;
        height: 160px;
        background: radial-gradient(circle, rgba(22, 153, 161, 0.12) 0%, transparent 70%);
        border-radius: 50%;
    }

    .budget-body {
        display: flex;
        gap: 2rem;
        align-items: center;
        position: relative;
        z-index: 1;
    }

    .budget-stats {
        flex: 1;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .budget-stat {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        padding: 1rem 1.25rem;
        background: rgba(255, 255, 255, 0.98);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-light);
        transition: var(--transition);
    }

    .budget-stat:hover {
        border-color: var(--accent);
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
    }

    .budget-stat-label {
        font-size: 0.8rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .budget-stat-value {
        font-size: 1.1rem;
        font-weight: 800;
        color: var(--text-dark);
    }

    .stat-used { color: var(--danger); }
    .stat-committed { color: var(--warning); }
    .stat-remaining { color: var(--success); }

    .budget-chart-area {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
        min-width: 180px;
    }

    .donut-chart {
        position: relative;
        width: 160px;
        height: 160px;
    }

    .donut-svg {
        width: 100%;
        height: 100%;
        transform: rotate(-90deg);
    }

    .donut-bg {
        fill: none;
        stroke: rgba(14, 56, 99, 0.08);
        stroke-width: 12;
    }

    .donut-fill {
        fill: none;
        stroke: url(#gradient);
        stroke-width: 12;
        stroke-linecap: round;
        transition: stroke-dasharray 1s ease;
    }

    .donut-center {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        text-align: center;
    }

    .donut-percent {
        display: block;
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-dark);
        line-height: 1;
    }

    .donut-label {
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-top: 0.25rem;
    }

    .chart-tags {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
    }

    .live-tag,
    .updated-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.75rem;
        color: var(--text-muted);
    }

    .live-tag {
        font-weight: 700;
        background: rgba(22, 153, 161, 0.1);
        color: var(--accent-dark);
        padding: 0.4rem 0.85rem;
        border-radius: var(--radius-full);
        border: 1px solid rgba(22, 153, 161, 0.2);
    }

    .live-tag i {
        font-size: 0.5rem;
        color: var(--success);
        animation: blink 2s infinite;
    }

    @keyframes blink {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.3; }
    }

    .updated-tag i {
        font-size: 0.85rem;
        color: var(--accent);
    }

    /* ===== Services Card ===== */
    .services-card {
        display: flex;
        flex-direction: column;
    }

    .services-list {
        display: flex;
        flex-direction: column;
        gap: 0.85rem;
        margin-bottom: 1.25rem;
        flex: 1;
    }

    .service-row {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.25rem;
        background: var(--bg-app);
        border-radius: var(--radius-lg);
        transition: var(--transition);
        border: 1px solid transparent;
    }

    .service-row:hover {
        transform: translateX(4px);
        background: rgba(22, 153, 161, 0.05);
        border-color: rgba(22, 153, 161, 0.15);
    }

    .service-icon-box {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-md);
        background: var(--bg-surface);
        color: var(--accent);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        border: 1px solid var(--border-light);
        flex-shrink: 0;
        box-shadow: var(--shadow-sm);
    }

    .service-info {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .service-name {
        font-size: 0.9rem;
        color: var(--text-dark);
        font-weight: 700;
    }

    .service-provider {
        font-size: 0.8rem;
        color: var(--text-secondary);
    }

    .service-datetime {
        font-size: 0.75rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .service-datetime i {
        font-size: 0.85rem;
    }

    .empty-state {
        color: var(--text-muted);
        font-size: 0.9rem;
        padding: 2rem 1rem;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.75rem;
    }

    .empty-state i {
        font-size: 2.5rem;
        opacity: 0.5;
    }

    .view-all {
        color: var(--accent);
        font-size: 0.85rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: var(--transition);
        margin-top: auto;
    }

    .view-all:hover {
        color: var(--accent-dark);
        gap: 0.75rem;
    }

    /* ===== Summary Rows ===== */
    .dashboard-summary-row {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.75rem;
    }

    .dashboard-chat-row {
        margin-bottom: 1.75rem;
    }

    .summary-body {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .summary-lead {
        font-size: 0.9rem;
        color: var(--text-secondary);
        margin: 0;
    }

    .summary-lead strong {
        color: var(--text-dark);
        font-weight: 800;
    }

    .avatar-stack {
        display: flex;
        align-items: center;
    }

    .mini-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--accent), var(--primary));
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.85rem;
        border: 3px solid var(--bg-surface);
        margin-left: -12px;
        box-shadow: var(--shadow-sm);
        transition: var(--transition);
    }

    .mini-avatar:first-child {
        margin-left: 0;
    }

    .mini-avatar:hover {
        transform: translateY(-3px) scale(1.1);
        z-index: 10;
    }

    .mini-avatar-more {
        background: var(--bg-app);
        color: var(--text-secondary);
        border-color: var(--border-light);
    }

    .summary-link {
        color: var(--accent);
        font-size: 0.85rem;
        font-weight: 700;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: auto;
        transition: var(--transition);
    }

    .summary-link:hover {
        color: var(--accent-dark);
        gap: 0.75rem;
    }

    .stat-pair,
    .doc-pair {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.85rem;
    }

    .stat-pill {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.85rem 1rem;
        background: var(--bg-app);
        border-radius: var(--radius-md);
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-secondary);
        border: 1px solid var(--border-light);
        transition: var(--transition);
    }

    .stat-pill:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
    }

    .stat-pill i {
        font-size: 1.1rem;
    }

    .stat-pill-success { background: rgba(16, 185, 129, 0.08); border-color: rgba(16, 185, 129, 0.15); }
    .stat-pill-success i { color: var(--success); }
    
    .stat-pill-warning { background: rgba(245, 158, 11, 0.08); border-color: rgba(245, 158, 11, 0.15); }
    .stat-pill-warning i { color: var(--warning); }
    
    .stat-pill-info { background: rgba(59, 130, 246, 0.08); border-color: rgba(59, 130, 246, 0.15); }
    .stat-pill-info i { color: var(--info); }

    .action-box {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem;
        background: rgba(59, 130, 246, 0.06);
        border-radius: var(--radius-lg);
        border: 1px solid rgba(59, 130, 246, 0.12);
        transition: var(--transition);
    }

    .action-box:hover {
        background: rgba(59, 130, 246, 0.1);
        transform: translateY(-2px);
    }

    .action-icon {
        font-size: 2.25rem;
        color: var(--info);
        flex-shrink: 0;
    }

    .action-icon-success {
        color: var(--success);
    }

    .action-text {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }

    .action-count {
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text-dark);
        line-height: 1;
    }

    .action-label {
        font-size: 0.85rem;
        color: var(--text-secondary);
    }

    .doc-stat {
        padding: 1.25rem;
        background: var(--bg-app);
        border-radius: var(--radius-md);
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        border: 1px solid var(--border-light);
        transition: var(--transition);
    }

    .doc-stat:hover {
        border-color: var(--accent);
        transform: translateY(-2px);
    }

    .doc-number {
        font-size: 1.75rem;
        font-weight: 800;
        color: var(--text-dark);
        line-height: 1;
    }

    .doc-label {
        font-size: 0.8rem;
        color: var(--text-muted);
    }

    .help-card {
        background: linear-gradient(180deg, rgba(16, 185, 129, 0.05) 0%, var(--bg-surface) 100%);
    }

    .dashboard-onboarding-row {
        margin-bottom: 1.75rem;
    }

    .onboarding-card {
        background: linear-gradient(180deg, rgba(14, 56, 99, 0.05) 0%, var(--bg-surface) 100%);
    }

    .onboarding-body {
        display: grid;
        gap: 1.25rem;
    }

    .onboarding-overview {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .onboarding-status-badge {
        font-size: 0.8rem;
        padding: 0.6rem 0.85rem;
        border-radius: 999px;
        letter-spacing: 0.02em;
    }

    .onboarding-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    .onboarding-stats div {
        padding: 1rem 1.25rem;
        background: var(--bg-app);
        border-radius: var(--radius-lg);
        border: 1px solid var(--border-light);
    }

    .onboarding-stats span {
        display: block;
        color: var(--text-muted);
        font-size: 0.8rem;
        margin-bottom: 0.35rem;
    }

    .onboarding-stats h3 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 800;
        color: var(--text-dark);
    }

    .onboarding-steps {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.8rem;
    }

    .onboarding-step-item {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 0.75rem;
        align-items: center;
        padding: 1rem 1.15rem;
        border-radius: var(--radius-lg);
        background: var(--bg-app);
        border: 1px solid var(--border-light);
        transition: var(--transition);
    }

    .onboarding-step-item.completed {
        border-color: rgba(16, 185, 129, 0.2);
    }

    .onboarding-step-item.current {
        border-color: rgba(59, 130, 246, 0.25);
        background: rgba(59, 130, 246, 0.04);
    }

    .onboarding-step-item.pending {
        opacity: 0.92;
    }

    .step-pill {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: var(--bg-surface);
        color: var(--text-dark);
        font-weight: 700;
        border: 1px solid var(--border-light);
        flex-shrink: 0;
    }

    .onboarding-step-item strong {
        display: block;
        color: var(--text-dark);
        font-size: 0.95rem;
        margin-bottom: 0.25rem;
    }

    .onboarding-step-item .text-muted {
        font-size: 0.78rem;
    }

    @media (max-width: 991.98px) {
        .onboarding-steps {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .onboarding-overview {
            flex-direction: column;
            align-items: flex-start;
        }

        .onboarding-stats {
            grid-template-columns: 1fr;
        }
    }

    /* ===== Responsive Design ===== */
    
    /* Large screens (1200px and up) */
    @media (min-width: 1200px) {
        .dashboard-page {
            padding: 2.5rem;
        }
    }

    /* Tablets and small desktops (992px - 1199px) */
    @media (max-width: 1199.98px) {
        .dashboard-top-row {
            grid-template-columns: 1fr;
        }

        .budget-body {
            flex-direction: column;
            gap: 1.5rem;
        }

        .budget-chart-area {
            min-width: 100%;
        }
    }

    /* Tablets (768px - 991px) */
    @media (max-width: 991.98px) {
        .dashboard-page {
            padding: 1.5rem;
        }

        .dashboard-notification-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dashboard-summary-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .greeting-title {
            font-size: 1.75rem;
        }

        .budget-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    /* Mobile devices (576px - 767px) */
    @media (max-width: 767.98px) {
        .dashboard-page {
            padding: 1.25rem;
        }

        .dashboard-header {
            flex-direction: column;
            align-items: flex-start;
            padding: 1.5rem;
        }

        .header-actions {
            width: 100%;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .icon-btn {
            width: 40px;
            height: 40px;
        }

        .icon-badge {
            top: -2px;
            right: -2px;
            min-width: 18px;
            height: 18px;
            font-size: 0.64rem;
            padding: 0 0.28rem;
        }

        .user-dropdown {
            order: -1;
            width: 100%;
            justify-content: flex-start;
        }

        .dashboard-notification-row {
            grid-template-columns: 1fr;
            gap: 1rem;
        }

        .notification-card {
            padding: 1.25rem;
        }

        .notification-card-value {
            font-size: 1.5rem;
        }

        .dashboard-summary-row {
            grid-template-columns: 1fr;
            gap: 1.25rem;
        }

        .dash-card {
            padding: 1.5rem;
        }

        .greeting-title {
            font-size: 1.5rem;
        }

        .budget-stats {
            grid-template-columns: 1fr;
        }

        .budget-stat {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
        }

        .donut-chart {
            width: 140px;
            height: 140px;
        }

        .donut-percent {
            font-size: 1.75rem;
        }

        .stat-pair,
        .doc-pair {
            grid-template-columns: 1fr;
        }

        .card-arrows {
            display: none;
        }
    }

    /* Small mobile devices (375px - 575px) */
    @media (max-width: 575.98px) {
        .dashboard-page {
            padding: 1rem;
        }

        .dashboard-header {
            padding: 1.25rem;
            border-radius: var(--radius-xl);
        }

        .greeting-title {
            font-size: 1.35rem;
        }

        .greeting-subtitle {
            font-size: 0.85rem;
        }

        .dash-card {
            padding: 1.25rem;
            border-radius: var(--radius-xl);
        }

        .dash-card-header {
            gap: 0.75rem;
            margin-bottom: 1.25rem;
        }

        .card-badge {
            width: 32px;
            height: 32px;
            font-size: 0.8rem;
        }

        .card-title {
            font-size: 1.05rem;
        }

        .notification-card {
            padding: 1rem;
            gap: 0.85rem;
        }

        .notification-card-icon {
            min-width: 48px;
            min-height: 48px;
            font-size: 1.25rem;
        }

        .notification-card-value {
            font-size: 1.35rem;
        }

        .notification-card-label {
            font-size: 0.7rem;
        }

        .notification-card-hint {
            font-size: 0.75rem;
        }

        .icon-btn {
            width: 40px;
            height: 40px;
            font-size: 1.1rem;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            font-size: 0.8rem;
        }

        .user-name {
            font-size: 0.85rem;
        }

        .user-role {
            font-size: 0.7rem;
        }

        .budget-stat {
            padding: 0.85rem 1rem;
        }

        .budget-stat-label {
            font-size: 0.75rem;
        }

        .budget-stat-value {
            font-size: 1rem;
        }

        .donut-chart {
            width: 120px;
            height: 120px;
        }

        .donut-percent {
            font-size: 1.5rem;
        }

        .service-row {
            padding: 0.85rem 1rem;
        }

        .service-icon-box {
            width: 42px;
            height: 42px;
            font-size: 1.15rem;
        }

        .service-name {
            font-size: 0.85rem;
        }

        .service-provider {
            font-size: 0.75rem;
        }

        .service-datetime {
            font-size: 0.7rem;
        }

        .stat-pill {
            padding: 0.75rem 0.85rem;
            font-size: 0.8rem;
        }

        .action-box {
            padding: 1rem;
        }

        .action-icon {
            font-size: 2rem;
        }

        .action-count {
            font-size: 1.35rem;
        }

        .action-label {
            font-size: 0.8rem;
        }

        .doc-stat {
            padding: 1rem;
        }

        .doc-number {
            font-size: 1.5rem;
        }

        .doc-label {
            font-size: 0.75rem;
        }
    }

    /* Extra small devices (374px and down) */
    @media (max-width: 374.98px) {
        .dashboard-page {
            padding: 0.875rem;
        }

        .dashboard-header {
            padding: 1rem;
        }

        .greeting-title {
            font-size: 1.2rem;
        }

        .wave-emoji {
            font-size: 1.35rem;
        }

        .dash-card {
            padding: 1rem;
        }

        .notification-card {
            padding: 0.875rem;
        }

        .notification-card-icon {
            min-width: 44px;
            min-height: 44px;
            font-size: 1.15rem;
        }

        .notification-card-value {
            font-size: 1.25rem;
        }

        .icon-btn {
            width: 38px;
            height: 38px;
        }

        .icon-badge {
            top: -2px;
            right: -2px;
            min-width: 17px;
            height: 17px;
            font-size: 0.62rem;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
        }

        .budget-stat {
            padding: 0.75rem 0.875rem;
        }

        .donut-chart {
            width: 110px;
            height: 110px;
        }

        .donut-percent {
            font-size: 1.35rem;
        }
    }

    /* Landscape mode */
    @media (max-height: 500px) and (orientation: landscape) {
        .dashboard-page {
            padding: 1rem;
        }

        .dashboard-header {
            padding: 1rem 1.5rem;
        }

        .dash-card {
            padding: 1.25rem;
        }

        .donut-chart {
            width: 120px;
            height: 120px;
        }
    }

    /* Touch devices */
    @media (hover: none) and (pointer: coarse) {
        .icon-btn,
        .arrow-btn,
        .notification-card,
        .service-row,
        .summary-link,
        .view-all {
            min-height: 44px;
        }

        .dash-card:hover,
        .notification-card:hover,
        .service-row:hover {
            transform: none;
        }
    }

    /* Print styles */
    @media print {
        .dashboard-page {
            background: white;
            padding: 0;
        }

        .dash-card,
        .dashboard-header,
        .notification-card {
            box-shadow: none;
            border: 1px solid #ddd;
            break-inside: avoid;
        }

        .icon-btn,
        .card-arrows,
        .notification-card-arrow {
            display: none;
        }
    }
</style>

@endsection