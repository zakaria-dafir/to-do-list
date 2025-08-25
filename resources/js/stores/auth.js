import { defineStore } from 'pinia'
import { api } from '../services/api'
import { useNotificationStore } from './notifications'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null,
        isAuthenticated: !!localStorage.getItem('token'),
        savedUserId: localStorage.getItem('user_id') ? parseInt(localStorage.getItem('user_id')) : null
    }),

    actions: {
        async login(credentials) {
            try {
                const response = await api.post('/auth/login', credentials)
                this.setAuth(response.data)
                return response
            } catch (error) {
                throw error.response.data
            }
        },

        async register(userData) {
            try {
                const response = await api.post('/auth/register', userData)
                this.setAuth(response.data)
                return response
            } catch (error) {
                throw error.response.data
            }
        },

        setAuth(authData) {
            this.token = authData.access_token
            this.user = authData.user
            this.isAuthenticated = true

            localStorage.setItem('token', authData.access_token)
            localStorage.setItem('user_id', authData.user.id) // Sauvegarder l'user_id
            api.defaults.headers.common['Authorization'] = `Bearer ${authData.access_token}`

            // Initialize notifications immediately after login
            const notificationStore = useNotificationStore()
            console.log('🔔 Initializing notifications after login for user:', authData.user.id)
            notificationStore.initializeForUser(authData.user.id)
        },

        logout() {
            // Nettoyer les notifications AVANT de nettoyer l'auth
            const notificationStore = useNotificationStore()
            notificationStore.resetForNewUser()

            this.token = null
            this.user = null
            this.isAuthenticated = false

            localStorage.removeItem('token')
            localStorage.removeItem('user_id')
            delete api.defaults.headers.common['Authorization']

            // Nettoyer l'user_id sauvegardé
            this.savedUserId = null

            console.log('🧹 Complete logout cleanup done')
        },

        async fetchUser() {
            if (this.token) {
                try {
                    api.defaults.headers.common['Authorization'] = `Bearer ${this.token}`
                    const response = await api.get('/auth/me')
                    this.user = response.data
                } catch (error) {
                    this.logout()
                }
            }
        },

        // Initialize auth state on app startup
        initializeAuth() {
            const token = localStorage.getItem('token')
            const userId = localStorage.getItem('user_id')

            if (token) {
                this.token = token
                this.isAuthenticated = true
                api.defaults.headers.common['Authorization'] = `Bearer ${token}`

                // Marquer que l'utilisateur est prêt pour l'initialisation des notifications
                this.savedUserId = parseInt(userId)
                console.log('Auth initialized, saved user ID:', this.savedUserId)
            }
        }
    }
})