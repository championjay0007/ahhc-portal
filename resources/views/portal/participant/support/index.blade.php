@extends('layouts.portal')

@section('title', 'Support Tickets')

@push('styles')
    <style>
        /* ============================================
           CSS CUSTOM PROPERTIES
           ============================================ */
        :root {
            --primary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            --primary-color: #4f46e5;
            --primary-light: rgba(79, 70, 229, 0.08);
            --primary-lighter: rgba(79, 70, 229, 0.04);
            --success-color: #059669;
            --warning-color: #d97706;
            --danger-color: #dc2626;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
            --border-color: #e5e7eb;
            --bg-white: #ffffff;
            --bg-gray-50: #f9fafb;
            --bg-gray-100: #f3f4f6;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            --radius-sm: 0.5rem;
            --radius-md: 0.75rem;
            --radius-lg: 1rem;
            --radius-xl: 1.25rem;
            --transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ============================================
           PAGE HEADER
           ============================================ */
        .page-header {
            background: var(--primary-gradient);
            border-radius: var(--radius-xl);
            padding: 2.5rem 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -5%;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
        }

        .page-header::after {
            content: '';
            position: absolute;
            bottom: -40%;
            left: 10%;
            width: 250px;
            height: 250px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .page-header-content {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .page-header-title h1 {
            color: #ffffff;
            font-size: 1.875rem;
            font-weight: 700;
            margin: 0 0 0.5rem 0;
            letter-spacing: -0.025em;
        }

        .page-header-title p {
            color: rgba(255, 255, 255, 0.9);
            margin: 0;
            font-size: 0.95rem;
        }

        .page-header-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .btn-primary-gradient {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: #ffffff;
            padding: 0.625rem 1.25rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
            white-space: nowrap;
        }

        .btn-primary-gradient:hover {
            background: rgba(255, 255, 255, 0.3);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
        }

        .btn-primary-solid {
            background: #ffffff;
            color: var(--primary-color);
            padding: 0.625rem 1.25rem;
            border-radius: var(--radius-md);
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
            white-space: nowrap;
            border: none;
        }

        .btn-primary-solid:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow-lg);
            color: var(--primary-color);
        }

        /* ============================================
           STATISTICS CARDS
           ============================================ */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--bg-white);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1.25rem;
            transition: var(--transition);
        }

        .stat-card:hover {
            box-shadow: var(--shadow-md);
            border-color: #d1d5db;
        }

        .stat-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.75rem;
        }

        .stat-card-icon {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .stat-card-icon.total { background: rgba(79, 70, 229, 0.1); color: #4f46e5; }
        .stat-card-icon.open { background: rgba(5, 150, 105, 0.1); color: #059669; }
        .stat-card-icon.progress { background: rgba(217, 119, 6, 0.1); color: #d97706; }
        .stat-card-icon.resolved { background: rgba(37, 99, 235, 0.1); color: #2563eb; }

        .stat-card-value {
            font-size: 1.875rem;
            font-weight: 700;
            color: var(--text-primary);
            line-height: 1;
            margin-bottom: 0.25rem;
        }

        .stat-card-label {
            font-size: 0.813rem;
            color: var(--text-secondary);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* ============================================
           FILTER BAR
           ============================================ */
        .filter-bar {
            background: var(--bg-white);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }

        .filter-label {
            font-size: 0.813rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            white-space: nowrap;
        }

        .filter-select {
            padding: 0.5rem 2rem 0.5rem 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            font-size: 0.875rem;
            color: var(--text-primary);
            background-color: var(--bg-white);
            cursor: pointer;
            transition: var(--transition);
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            min-width: 140px;
        }

        .filter-select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }

        .filter-results {
            margin-left: auto;
            font-size: 0.875rem;
            color: var(--text-secondary);
            white-space: nowrap;
        }

        .btn-filter-clear {
            padding: 0.5rem 0.875rem;
            font-size: 0.813rem;
            color: var(--text-secondary);
            background: var(--bg-gray-50);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: var(--transition);
            white-space: nowrap;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
        }

        .btn-filter-clear:hover {
            background: var(--bg-gray-100);
            color: var(--text-primary);
        }

        /* ============================================
           TICKET LIST
           ============================================ */
        .ticket-list {
            display: flex;
            flex-direction: column;
            gap: 0.875rem;
        }

        .ticket-item {
            background: var(--bg-white);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            transition: var(--transition);
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }

        .ticket-item:hover {
            box-shadow: var(--shadow-md);
            border-color: #d1d5db;
            transform: translateY(-1px);
        }

        .ticket-item-row {
            display: flex;
            align-items: flex-start;
            gap: 1.25rem;
        }

        .ticket-priority-indicator {
            width: 4px;
            min-height: 60px;
            border-radius: 2px;
            flex-shrink: 0;
            align-self: stretch;
        }

        .priority-low { background: #6b7280; }
        .priority-normal { background: #f59e0b; }
        .priority-high { background: #ef4444; }
        .priority-urgent { background: #dc2626; }

        .ticket-content {
            flex: 1;
            min-width: 0;
        }

        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 0.5rem;
        }

        .ticket-subject {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
            line-height: 1.4;
        }

        .ticket-id {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Fira Mono', monospace;
            background: var(--bg-gray-50);
            padding: 0.25rem 0.625rem;
            border-radius: var(--radius-sm);
            white-space: nowrap;
            font-weight: 500;
        }

        .ticket-description {
            font-size: 0.875rem;
            color: var(--text-secondary);
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .ticket-meta {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .ticket-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.813rem;
            color: var(--text-secondary);
        }

        .ticket-meta-icon {
            color: var(--text-muted);
            font-size: 0.875rem;
        }

        .ticket-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        /* Status Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1.5;
            white-space: nowrap;
        }

        .badge-open { background: #ecfdf5; color: #065f46; }
        .badge-in-progress { background: #fffbeb; color: #92400e; }
        .badge-waiting { background: #faf5ff; color: #6b21a8; }
        .badge-resolved { background: #eff6ff; color: #1e40af; }
        .badge-closed { background: #f3f4f6; color: #374151; }

        .badge-priority-low { background: #f3f4f6; color: #374151; }
        .badge-priority-normal { background: #fffbeb; color: #92400e; }
        .badge-priority-high { background: #fef2f2; color: #991b1b; }
        .badge-priority-urgent { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        .badge-category {
            background: rgba(79, 70, 229, 0.06);
            color: #4f46e5;
            border: 1px solid rgba(79, 70, 229, 0.15);
        }

        .badge-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        /* Response indicator */
        .response-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.813rem;
            color: var(--text-secondary);
        }

        .response-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #4f46e5;
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0%, 100% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.4); }
            50% { box-shadow: 0 0 0 6px rgba(79, 70, 229, 0); }
        }

        .btn-view {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.5rem 1rem;
            font-size: 0.813rem;
            font-weight: 600;
            color: var(--primary-color);
            background: var(--primary-light);
            border: 1px solid transparent;
            border-radius: var(--radius-sm);
            transition: var(--transition);
            text-decoration: none;
            white-space: nowrap;
        }

        .btn-view:hover {
            background: var(--primary-color);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: var(--shadow-md);
        }

        /* ============================================
           EMPTY STATE
           ============================================ */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--bg-white);
            border: 2px dashed var(--border-color);
            border-radius: var(--radius-xl);
        }

        .empty-state-icon {
            width: 4rem;
            height: 4rem;
            background: var(--primary-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 1.75rem;
            color: var(--primary-color);
        }

        .empty-state h3 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
            max-width: 480px;
            margin-left: auto;
            margin-right: auto;
            font-size: 0.938rem;
            line-height: 1.6;
        }

        .empty-state-actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* ============================================
           ALERT
           ============================================ */
        .alert-success-custom {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            padding: 1rem 1.25rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.938rem;
        }

        .alert-success-custom .alert-icon {
            width: 2.25rem;
            height: 2.25rem;
            background: #d1fae5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.125rem;
            flex-shrink: 0;
        }

        /* ============================================
           PAGINATION
           ============================================ */
        .pagination-wrapper {
            display: flex;
            justify-content: center;
            margin-top: 2rem;
        }

        /* ============================================
           RESPONSIVE
           ============================================ */
        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .page-header {
                padding: 1.75rem 1.25rem;
                border-radius: var(--radius-lg);
            }

            .page-header-title h1 {
                font-size: 1.5rem;
            }

            .page-header-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .page-header-actions {
                width: 100%;
            }

            .btn-primary-gradient,
            .btn-primary-solid {
                flex: 1;
                justify-content: center;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .ticket-item {
                padding: 1.25rem;
            }

            .ticket-header {
                flex-direction: column;
                gap: 0.5rem;
            }

            .ticket-meta {
                gap: 1rem;
            }

            .filter-bar {
                flex-direction: column;
                align-items: flex-start;
            }

            .filter-results {
                margin-left: 0;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <!-- ============================================
         PAGE HEADER
         ============================================ -->
    <div class="page-header">
        <div class="page-header-content">
            <div class="page-header-title">
                <h1>Support Tickets</h1>
                <p>Manage and track all your support requests in one place</p>
            </div>
            <div class="page-header-actions">
                <a href="{{ route('portal.support.conversations.index') }}" class="btn-primary-gradient">
                    <i class="bi bi-chat-dots"></i>
                    Live Chat
                </a>
                <a href="{{ route('portal.support.create') }}" class="btn-primary-solid">
                    <i class="bi bi-plus-lg"></i>
                    New Ticket
                </a>
            </div>
        </div>
    </div>

    <!-- ============================================
         SUCCESS MESSAGE
         ============================================ -->
    @if(session('status'))
        <div class="alert-success-custom">
            <div class="alert-icon">
                <i class="bi bi-check-lg"></i>
            </div>
            <div>
                <strong>Success!</strong> {{ session('status') }}
            </div>
        </div>
    @endif

    <!-- ============================================
         STATISTICS
         ============================================ -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon total">
                    <i class="bi bi-ticket-detailed"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ $tickets->total() }}</div>
            <div class="stat-card-label">Total Tickets</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon open">
                    <i class="bi bi-envelope-open"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ $tickets->where('status', 'open')->count() }}</div>
            <div class="stat-card-label">Open</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon progress">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ $tickets->whereIn('status', ['in-progress', 'waiting'])->count() }}</div>
            <div class="stat-card-label">In Progress</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-header">
                <div class="stat-card-icon resolved">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
            <div class="stat-card-value">{{ $tickets->whereIn('status', ['resolved', 'closed'])->count() }}</div>
            <div class="stat-card-label">Resolved</div>
        </div>
    </div>

    <!-- ============================================
         FILTER BAR
         ============================================ -->
    @if($tickets->count() > 0 || request()->hasAny(['status', 'priority']))
        <div class="filter-bar">
            <div class="filter-group">
                <span class="filter-label">Status</span>
                <select class="filter-select" onchange="applyFilter('status', this.value)">
                    <option value="">All Statuses</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="in-progress" {{ request('status') == 'in-progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="waiting" {{ request('status') == 'waiting' ? 'selected' : '' }}>Waiting</option>
                    <option value="resolved" {{ request('status') == 'resolved' ? 'selected' : '' }}>Resolved</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                </select>
            </div>
            <div class="filter-group">
                <span class="filter-label">Priority</span>
                <select class="filter-select" onchange="applyFilter('priority', this.value)">
                    <option value="">All Priorities</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="normal" {{ request('priority') == 'normal' ? 'selected' : '' }}>Normal</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                    <option value="urgent" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                </select>
            </div>
            <span class="filter-results">
                {{ $tickets->total() }} ticket{{ $tickets->total() !== 1 ? 's' : '' }} found
            </span>
            @if(request()->hasAny(['status', 'priority']))
                <a href="{{ route('portal.support.index') }}" class="btn-filter-clear">
                    <i class="bi bi-x-lg"></i>
                    Clear Filters
                </a>
            @endif
        </div>
    @endif

    <!-- ============================================
         TICKETS LIST
         ============================================ -->
    @if($tickets->count() > 0)
        <div class="ticket-list">
            @foreach($tickets as $ticket)
                <a href="{{ route('portal.support.show', $ticket) }}" class="ticket-item">
                    <div class="ticket-item-row">
                        <!-- Priority Indicator -->
                        <div class="ticket-priority-indicator priority-{{ $ticket->priority }}"></div>
                        
                        <!-- Content -->
                        <div class="ticket-content">
                            <!-- Header -->
                            <div class="ticket-header">
                                <h3 class="ticket-subject">{{ $ticket->subject }}</h3>
                                <span class="ticket-id">#{{ str_pad($ticket->id, 6, '0', STR_PAD_LEFT) }}</span>
                            </div>

                            <!-- Description -->
                            <p class="ticket-description">
                                {{ Str::limit($ticket->description, 160) }}
                            </p>

                            <!-- Meta Information -->
                            <div class="ticket-meta">
                                <div class="ticket-meta-item">
                                    <i class="bi bi-calendar3 ticket-meta-icon"></i>
                                    <span>{{ $ticket->created_at->format('M d, Y') }}</span>
                                </div>
                                <div class="ticket-meta-item">
                                    <i class="bi bi-clock-history ticket-meta-icon"></i>
                                    <span>{{ $ticket->updated_at->diffForHumans() }}</span>
                                </div>
                                @if($ticket->category)
                                    <div class="ticket-meta-item">
                                        <i class="bi bi-folder ticket-meta-icon"></i>
                                        <span>{{ ucfirst(str_replace('_', ' ', $ticket->category)) }}</span>
                                    </div>
                                @endif
                                <div class="ticket-meta-item">
                                    <i class="bi bi-chat-dots ticket-meta-icon"></i>
                                    <span>{{ $ticket->responses()->count() }} response{{ $ticket->responses()->count() !== 1 ? 's' : '' }}</span>
                                </div>
                            </div>

                            <!-- Footer -->
                            <div class="ticket-footer">
                                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <span class="badge badge-{{ $ticket->status }}">
                                        <span class="badge-dot"></span>
                                        {{ ucfirst($ticket->status) }}
                                    </span>
                                    <span class="badge badge-priority-{{ $ticket->priority }}">
                                        <i class="bi bi-flag-fill" style="font-size: 0.625rem;"></i>
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                    @if($ticket->category)
                                        <span class="badge badge-category">
                                            {{ ucfirst(str_replace('_', ' ', $ticket->category)) }}
                                        </span>
                                    @endif
                                </div>
                                <span class="btn-view">
                                    View Details
                                    <i class="bi bi-arrow-right"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <!-- Pagination -->
        @if($tickets->hasPages())
            <div class="pagination-wrapper">
                {{ $tickets->links() }}
            </div>
        @endif
    @else
        <!-- Empty State -->
        <div class="empty-state">
            <div class="empty-state-icon">
                <i class="bi bi-inbox"></i>
            </div>
            <h3>No support tickets found</h3>
            <p>You haven't created any support tickets yet. When you need assistance, our support team is ready to help you resolve any issues.</p>
            <div class="empty-state-actions">
                <a href="{{ route('portal.support.create') }}" class="btn-primary-solid" style="background: var(--primary-gradient); color: #fff; padding: 0.75rem 1.5rem;">
                    <i class="bi bi-plus-lg"></i>
                    Create Your First Ticket
                </a>
                <a href="{{ route('portal.support.conversations.index') }}" class="btn-primary-gradient" style="color: var(--primary-color); border-color: var(--border-color); background: var(--bg-white);">
                    <i class="bi bi-chat-dots"></i>
                    Start a Live Chat
                </a>
            </div>
        </div>
    @endif
</div>

<script>
    /**
     * Apply filter and maintain other query parameters
     */
    function applyFilter(key, value) {
        const url = new URL(window.location.href);
        if (value) {
            url.searchParams.set(key, value);
        } else {
            url.searchParams.delete(key);
        }
        url.searchParams.delete('page'); // Reset pagination
        window.location.href = url.toString();
    }
</script>
@endsection