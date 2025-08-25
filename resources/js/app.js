import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

window.Pusher = Pusher

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    auth: {
        headers: {
            Authorization: () => `Bearer ${localStorage.getItem('token')}`,
        },
    },
    authEndpoint: '/api/broadcasting/auth'
})

const app = createApp(App)
app.use(createPinia())
app.use(router)
app.mount('#app')