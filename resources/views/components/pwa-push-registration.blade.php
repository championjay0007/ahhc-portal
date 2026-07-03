const PUSH_PUBLIC_KEY_ENDPOINT = "{{ route('portal.push.public_key') }}";
const PUSH_SUBSCRIPTION_SAVE_ENDPOINT = "{{ route('portal.push.subscription.store') }}";
const PUSH_SUBSCRIPTION_DELETE_ENDPOINT = "{{ route('portal.push.subscription.destroy') }}";

window.isAuthenticated = {{ Auth::check() ? 'true' : 'false' }};

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : null;
}

async function sendPushSubscription(subscription) {
    const csrfToken = getCsrfToken();
    if (!csrfToken || !subscription) {
        return;
    }

    await fetch(PUSH_SUBSCRIPTION_SAVE_ENDPOINT, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        credentials: 'same-origin',
        body: JSON.stringify(subscription.toJSON ? subscription.toJSON() : subscription),
    });
}

async function removePushSubscription(subscription) {
    const csrfToken = getCsrfToken();
    if (!csrfToken || !subscription) {
        return;
    }

    await fetch(PUSH_SUBSCRIPTION_DELETE_ENDPOINT, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
        },
        credentials: 'same-origin',
        body: JSON.stringify({ endpoint: subscription.endpoint }),
    });
}

function updateNotificationCta(state) {
    const button = document.getElementById('pwaNotificationCta');
    if (!button) {
        return;
    }

    const permission = ('Notification' in window) ? Notification.permission : 'unsupported';
    const resolvedState = state || permission;

    if (resolvedState === 'granted') {
        button.textContent = 'Notifications enabled';
        button.className = 'btn btn-sm btn-success rounded-pill shadow position-fixed bottom-0 end-0 me-3 mb-3';
        button.disabled = true;
        button.style.zIndex = '1060';
        return;
    }

    if (resolvedState === 'denied') {
        button.textContent = 'Notifications blocked';
        button.className = 'btn btn-sm btn-outline-secondary rounded-pill shadow position-fixed bottom-0 end-0 me-3 mb-3';
        button.disabled = true;
        button.style.zIndex = '1060';
        return;
    }

    if (resolvedState === 'unsupported') {
        button.textContent = 'Notifications unavailable';
        button.className = 'btn btn-sm btn-outline-secondary rounded-pill shadow position-fixed bottom-0 end-0 me-3 mb-3';
        button.disabled = true;
        button.style.zIndex = '1060';
        return;
    }

    button.textContent = 'Enable notifications';
    button.className = 'btn btn-sm btn-primary rounded-pill shadow position-fixed bottom-0 end-0 me-3 mb-3';
    button.disabled = false;
    button.style.zIndex = '1060';
}

function ensureNotificationCta() {
    if (document.getElementById('pwaNotificationCta')) {
        updateNotificationCta();
        return;
    }

    const button = document.createElement('button');
    button.id = 'pwaNotificationCta';
    button.type = 'button';
    button.className = 'btn btn-sm btn-primary rounded-pill shadow position-fixed bottom-0 end-0 me-3 mb-3';
    button.style.zIndex = '1060';
    button.textContent = 'Enable notifications';
    button.addEventListener('click', function() {
        if (typeof window.enablePwaNotifications === 'function') {
            window.enablePwaNotifications();
        }
    });

    document.body.appendChild(button);
    updateNotificationCta();
}

async function initializePushSubscription(registration) {
    if (!PWA_ENABLED || !window.isAuthenticated || !('PushManager' in window) || !('Notification' in window)) {
        updateNotificationCta('unsupported');
        return;
    }

    if (Notification.permission === 'denied') {
        updateNotificationCta('denied');
        return;
    }

    if (Notification.permission === 'default') {
        updateNotificationCta('default');
        return;
    }

    try {
        const response = await fetch(PUSH_PUBLIC_KEY_ENDPOINT, { credentials: 'include' });
        const payload = await response.json();
        const publicKey = payload.publicKey;

        if (!publicKey) {
            updateNotificationCta('unsupported');
            console.warn('VAPID public key is not configured. Push subscription is disabled.');
            return;
        }

        const existingSubscription = await registration.pushManager.getSubscription();
        if (existingSubscription) {
            await sendPushSubscription(existingSubscription);
            updateNotificationCta('granted');
            return;
        }

        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: urlBase64ToUint8Array(publicKey),
        });

        await sendPushSubscription(subscription);
        updateNotificationCta('granted');
    } catch (error) {
        updateNotificationCta('unsupported');
        console.error('Failed to initialize push subscription:', error);
    }
}

window.enablePwaNotifications = async function() {
    if (!('Notification' in window) || !('PushManager' in window)) {
        updateNotificationCta('unsupported');
        return;
    }

    if (Notification.permission === 'denied') {
        updateNotificationCta('denied');
        return;
    }

    if (Notification.permission === 'default') {
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
            updateNotificationCta(permission);
            return;
        }
    }

    if (!('serviceWorker' in navigator)) {
        updateNotificationCta('unsupported');
        return;
    }

    try {
        const registration = await navigator.serviceWorker.ready;
        await initializePushSubscription(registration);
    } catch (error) {
        console.error('Unable to enable push notifications:', error);
        updateNotificationCta('unsupported');
    }
};

function addPushMessageListener() {
    if (!('serviceWorker' in navigator) || !('postMessage' in MessageEvent.prototype)) {
        return;
    }

    navigator.serviceWorker.addEventListener('message', function(event) {
        const message = event.data;
        if (!message || message.type !== 'push-notification') {
            return;
        }

        updateNotificationUi(message.notification);
    });
}

function setupServiceWorkerUpdates(registration) {
    if (!registration || !('serviceWorker' in navigator)) {
        return;
    }

    let reloadedForUpdate = false;

    navigator.serviceWorker.addEventListener('controllerchange', function() {
        if (!reloadedForUpdate) {
            reloadedForUpdate = true;
            window.location.reload();
        }
    });

    registration.addEventListener('updatefound', function() {
        const newWorker = registration.installing;
        if (!newWorker) {
            return;
        }

        newWorker.addEventListener('statechange', function() {
            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                newWorker.postMessage('skipWaiting');
            }
        });
    });

    if (registration.waiting) {
        registration.waiting.postMessage('skipWaiting');
    }

    registration.update().catch(function() {});

    window.addEventListener('focus', function() {
        registration.update().catch(function() {});
    });

    window.setInterval(function() {
        registration.update().catch(function() {});
    }, 60 * 60 * 1000);
}

function updateNotificationUi(notification) {
    if (!notification) {
        return;
    }

    const badgeSelectors = [
        '#portalNotificationToggle .notification-badge',
        '#adminNotificationToggle .notification-badge',
        'a.icon-btn[title="Notifications"] .icon-badge',
    ];

    badgeSelectors.forEach(selector => {
        const badgeParent = document.querySelector(selector.replace(/ \.notification-badge$/, ''));
        if (!badgeParent) {
            return;
        }

        let badge = badgeParent.querySelector('.notification-badge');
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'notification-badge';
            badgeParent.appendChild(badge);
        }

        const currentCount = parseInt(badge.textContent || '0', 10) || 0;
        badge.textContent = currentCount + 1;
    });

    const notificationCards = document.querySelectorAll('.notification-card');
    notificationCards.forEach(card => {
        if (!card.querySelector('.notification-card-icon .bi-bell-fill')) {
            return;
        }

        const countElement = card.querySelector('.notification-card-value');
        if (!countElement) {
            return;
        }

        const currentCount = parseInt(countElement.textContent || '0', 10);
        if (!Number.isNaN(currentCount)) {
            countElement.textContent = currentCount + 1;
        }
    });

    const listSelectors = ['#portalNotificationDropdown .notification-list', '#adminNotificationDropdown .notification-list'];
    listSelectors.forEach(selector => {
        const list = document.querySelector(selector);
        if (!list) {
            return;
        }

        const notificationUrl = notification.data?.url || '#';
        const now = new Date(notification.timestamp || Date.now());
        const timeLabel = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        const item = document.createElement('a');
        item.className = 'notification-item unread';
        item.href = notificationUrl;
        item.innerHTML = `
            <div class="notification-title">${escapeHtml(notification.title || 'New notification')}</div>
            <div class="notification-message">${escapeHtml(notification.body || 'You have a new notification.')}</div>
            <div class="notification-meta">
                <i class="bi bi-clock"></i>
                ${escapeHtml(timeLabel)}
            </div>
        `;

        list.insertBefore(item, list.firstChild);

        if (list.children.length > 5) {
            list.removeChild(list.lastChild);
        }
    });
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

addPushMessageListener();
document.addEventListener('DOMContentLoaded', ensureNotificationCta);
window.addEventListener('load', ensureNotificationCta);
window.addEventListener('appinstalled', function() {
    if (typeof window.enablePwaNotifications === 'function') {
        window.enablePwaNotifications();
    }
});
