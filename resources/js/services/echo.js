import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

export const initializeEcho = () => {
    if (!import.meta.env.VITE_PUSHER_APP_KEY) {
        console.warn('Pusher non configuré');
        return null;
    }

    window.Pusher = Pusher;

    return new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
        forceTLS: true,
        encrypted: true,
        disableStats: true,
        enabledTransports: ['ws', 'wss']
    });
}

export default initializeEcho;