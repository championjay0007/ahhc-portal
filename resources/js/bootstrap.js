import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
// Laravel Echo + Pusher (optional): initialize when environment variables are present
import Pusher from 'pusher-js';
import Echo from 'laravel-echo';

try {
	if (import.meta.env.VITE_PUSHER_APP_KEY) {
		window.Pusher = Pusher;

		window.Echo = new Echo({
			broadcaster: 'pusher',
			key: import.meta.env.VITE_PUSHER_APP_KEY,
			cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? undefined,
			wsHost: import.meta.env.VITE_PUSHER_HOST ?? undefined,
			wsPort: import.meta.env.VITE_PUSHER_PORT ?? undefined,
			forceTLS: import.meta.env.VITE_PUSHER_SCHEME === 'https',
			enabledTransports: ['ws', 'wss'],
		});
	}
} catch (e) {
	// ignore if Echo/Pusher are not configured
}
