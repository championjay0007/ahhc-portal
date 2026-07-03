@if(isset($portalUnreadNotifications) && count($portalUnreadNotifications) > 0)
<div class="modal fade" id="notificationAlertModal" tabindex="-1" aria-labelledby="notificationAlertModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0 pb-3">
                <div>
                    <h5 class="modal-title" id="notificationAlertModalLabel">
                        <i class="bi bi-bell-fill me-2"></i>New Notifications
                    </h5>
                    <small class="text-white-50">You have {{ count($portalUnreadNotifications) }} new alert{{ count($portalUnreadNotifications) === 1 ? '' : 's' }}</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                @forelse($portalUnreadNotifications as $notification)
                    <div class="notification-item border-bottom p-3 hover-light transition" style="cursor: pointer;" data-notification-url="{{ route('portal.notifications.show', $notification) }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <!-- Notification Icon/Badge -->
                                <div class="d-flex align-items-center mb-2">
                                    <span class="badge bg-{{ $notification->type === 'urgent' ? 'danger' : ($notification->type === 'warning' ? 'warning' : 'info') }} me-2">
                                        {{ ucfirst($notification->type ?? 'info') }}
                                    </span>
                                    @if(!$notification->read_at)
                                        <span class="badge bg-primary ms-1">NEW</span>
                                    @endif
                                </div>

                                <!-- Title and Message -->
                                <h6 class="mb-1 fw-semibold" style="color: #2c3e50;">
                                    {{ $notification->title }}
                                </h6>
                                <p class="mb-2 text-muted small" style="line-height: 1.4;">
                                    {{ $notification->message }}
                                </p>

                                <!-- Additional Data if Available -->
                                @if(isset($notification->data) && is_array($notification->data))
                                    @if(!empty($notification->data['detail']))
                                        <small class="text-muted d-block mb-2">
                                            <i class="bi bi-info-circle me-1"></i>
                                            {{ $notification->data['detail'] }}
                                        </small>
                                    @endif
                                @endif

                                <!-- Timestamp -->
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>
                                    {{ $notification->created_at->diffForHumans() }}
                                </small>
                            </div>

                            <!-- Action Buttons -->
                            <div class="ms-3">
                                @if(!$notification->read_at)
                                    <form method="POST" action="{{ route('portal.notifications.mark_read', $notification->id) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Mark as read">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center p-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="text-muted mt-3">No new notifications</p>
                    </div>
                @endforelse
            </div>
            <div class="modal-footer bg-light border-top">
                <form method="POST" action="{{ route('portal.notifications.mark_all_read') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary me-2" @if(count($portalNotifications) == 0 || $portalNotifications->every(fn($n) => $n->read_at)) disabled @endif>
                        <i class="bi bi-check-all me-1"></i>Mark all as read
                    </button>
                </form>
                <a href="{{ route('portal.notifications') }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-arrow-right me-1"></i>View all notifications
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-light {
        border-left: 3px solid transparent;
        transition: all 0.2s ease;
    }

    .hover-light:hover {
        background-color: #f8f9fa;
        border-left-color: #0d6efd;
    }

    #notificationAlertModal {
        pointer-events: none;
    }

    #notificationAlertModal .modal-dialog {
        pointer-events: auto;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modalElement = document.getElementById('notificationAlertModal');
        const canShowModal = modalElement && typeof bootstrap !== 'undefined' && typeof bootstrap.Modal === 'function';

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

        cleanupModalBackdrops();

        // Check if there are unread notifications
        @if(isset($unreadNotificationCount) && $unreadNotificationCount > 0)
            if (canShowModal) {
                const previousBodyOverflow = document.body.style.overflow || '';
                const previousBodyPaddingRight = document.body.style.paddingRight || '';
                const modal = new bootstrap.Modal(modalElement, {
                    keyboard: true,
                    backdrop: false
                });
                modal.show();

                modalElement.addEventListener('hidden.bs.modal', function() {
                    document.body.style.overflow = previousBodyOverflow;
                    document.body.style.paddingRight = previousBodyPaddingRight;
                    cleanupModalBackdrops();
                });
            }
        @endif

        // Handle notification item clicks
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function(e) {
                if (e.target.closest('form') || e.target.closest('button')) {
                    return;
                }

                const url = this.getAttribute('data-notification-url');
                if (url) {
                    window.location.href = url;
                }
            });
        });
    });
</script>
@endif
