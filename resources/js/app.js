import './bootstrap';

// Real-time message icon update: subscribe to private user channel and update unread badge
(function () {
	try {
		const meta = document.querySelector('meta[name="current-user-id"]');
		const userId = meta ? meta.content : null;

		if (userId && window.Echo) {
			window.Echo.private(`user.${userId}`)
				.listen('NewMessage', (e) => {
					// increment message badge(s)
					const badges = document.querySelectorAll('.icon-badge, .notification-badge');
					badges.forEach((el) => {
						const n = parseInt(el.textContent || '0', 10) || 0;
						el.textContent = n + 1;
					});

					// Optionally add a visual highlight
					const messageIcon = document.querySelector('[title="Messages"]') || document.querySelector('.icon-link[title="Messages"]');
					if (messageIcon) {
						messageIcon.classList.add('flash-notification');
						setTimeout(() => messageIcon.classList.remove('flash-notification'), 3000);
					}
				});
		}
	} catch (err) {
		// silently ignore
	}
})();
