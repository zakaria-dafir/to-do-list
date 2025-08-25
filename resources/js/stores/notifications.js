import { defineStore } from 'pinia'

export const useNotificationStore = defineStore('notifications', {
    state: () => ({
        notifications: [],
        connected: false,
        connectionError: null,
        currentUserId: null
    }),

    getters: {
        unreadCount: (state) => state.notifications.filter(n => !n.read).length,
        unreadNotifications: (state) => state.notifications.filter(n => !n.read),
        readNotifications: (state) => state.notifications.filter(n => n.read)
    },

    actions: {
        initializeForUser(userId) {
            if (!userId) {
                console.warn('Cannot initialize notifications - no user ID provided')
                return
            }

            // TOUJOURS nettoyer les notifications en mémoire avant d'initialiser
            console.log(`🧹 Force clearing notifications before initializing user ${userId}`)
            this.notifications = []
            this.connected = false
            this.connectionError = null

            // Si on change d'utilisateur
            if (this.currentUserId && this.currentUserId !== userId) {
                console.log(`🔄 Switching from user ${this.currentUserId} to user ${userId}`)
            }

            this.currentUserId = userId
            this.loadNotificationsForUser(userId)
            console.log(`✅ Notifications initialized for user ${userId} - ${this.notifications.length} loaded`)
        },

        getStorageKey(userId) {
            return `notifications_user_${userId}`
        },

        loadNotificationsForUser(userId) {
            const storageKey = this.getStorageKey(userId)
            const savedNotifications = localStorage.getItem(storageKey)
            console.log(`🔍 Loading notifications for user ${userId} with key ${storageKey}`)
            console.log(`📦 Raw data from localStorage:`, savedNotifications)

            if (savedNotifications) {
                try {
                    this.notifications = JSON.parse(savedNotifications)
                    console.log(`✅ Loaded ${this.notifications.length} notifications for user ${userId}`)
                } catch (error) {
                    console.error('❌ Error parsing notifications:', error)
                    this.notifications = []
                }
            } else {
                console.log(`📭 No notifications found for user ${userId}`)
                this.notifications = []
            }
        },

        saveToLocalStorage() {
            if (!this.currentUserId) {
                console.warn('❌ Cannot save notifications - no user ID set')
                return
            }
            const storageKey = this.getStorageKey(this.currentUserId)
            console.log(`💾 Saving ${this.notifications.length} notifications for user ${this.currentUserId} with key ${storageKey}`)
            localStorage.setItem(storageKey, JSON.stringify(this.notifications))
            console.log('✅ Saved to localStorage successfully')

            // Vérifier que la sauvegarde a fonctionné
            const verification = localStorage.getItem(storageKey)
            console.log('🔍 Verification - data in localStorage:', verification ? 'EXISTS' : 'NOT FOUND')
        },
        initializeWebSocket() {
            if (!this.currentUserId) {
                console.warn('Cannot initialize WebSocket - no user ID')
                return
            }

            console.log('🔌 Initializing WebSocket for user:', this.currentUserId)
            console.log('🔌 Echo object:', window.Echo)
            console.log('🔌 Channel name: global-tasks')

            try {
                // Listen to global channel for all users (for demo)
                const channel = window.Echo.channel('global-tasks')

                console.log('🔌 Channel created:', channel)

                channel.listen('TaskCreated', (e) => {
                        console.log('🔔 TaskCreated event received:', e)
                        this.addNotification(
                            `Nouvelle tâche créée: ${e.task.title}`,
                            e.task,
                            'success'
                        )
                    })
                    .listen('TaskUpdated', (e) => {
                        console.log('🔔 TaskUpdated event received:', e)
                        this.addNotification(
                            `Tâche mise à jour: ${e.task.title}`,
                            e.task,
                            'info'
                        )
                    })
                    .error((error) => {
                        console.error('🔌 Channel error:', error)
                    })

                this.connected = true
                console.log('✅ WebSocket connected successfully to channel: global-tasks')
            } catch (error) {
                console.error('❌ WebSocket connection failed:', error)
                this.connectionError = error.message
            }
        },

        markAsRead(id) {
            const notification = this.notifications.find(n => n.id === id)
            if (notification) {
                notification.read = true
                this.saveToLocalStorage()
            }
        },

        markAllAsRead() {
            this.notifications.forEach(notification => {
                notification.read = true
            })
            this.saveToLocalStorage()
        },

        clearAll() {
            this.notifications = []
            this.saveToLocalStorage()
        },

        clearForUser() {
            console.log(`🧹 Clearing notifications for user ${this.currentUserId}`)
            this.notifications = []
            this.currentUserId = null
            console.log('✅ Notifications cleared for user logout')
        },

        // Méthode pour nettoyer complètement lors du changement d'utilisateur
        resetForNewUser() {
            console.log('🔄 Resetting notifications for new user')
            this.notifications = []
            this.currentUserId = null
            this.connected = false
            this.connectionError = null
        },

        showBrowserNotification(notification) {
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification('Nouvelle Tâche Créée', {
                    body: `${notification.task.title} - ${notification.message}`,
                    icon: '/favicon.ico',
                    tag: 'task-created'
                })
            }
        },

        requestNotificationPermission() {
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission()
            }
        },

        addNotification(message, task, type = 'info') {
            const notification = {
                id: Date.now(),
                message,
                task,
                read: false,
                timestamp: new Date(),
                type
            }
            this.notifications.unshift(notification)
            this.saveToLocalStorage()

            // Broadcast to other tabs/windows
            this.broadcastToOtherTabs(notification)

            return notification
        },

        broadcastToOtherTabs(notification) {
            // Utiliser localStorage pour communiquer entre les onglets
            const event = {
                type: 'NEW_NOTIFICATION',
                notification: notification,
                timestamp: Date.now()
            }
            localStorage.setItem('notification_broadcast', JSON.stringify(event))
                // Supprimer immédiatement pour déclencher l'événement
            localStorage.removeItem('notification_broadcast')
        },

        initializeCrossTabSync() {
            // Écouter les changements de localStorage pour synchroniser entre onglets
            window.addEventListener('storage', (e) => {
                if (e.key === 'notification_broadcast' && e.newValue) {
                    const event = JSON.parse(e.newValue)
                    if (event.type === 'NEW_NOTIFICATION') {
                        // Ajouter la notification sans la broadcaster à nouveau
                        this.notifications.unshift(event.notification)
                        this.saveToLocalStorage()
                        console.log('Notification reçue d\'un autre onglet:', event.notification.task.title)
                    }
                }
            })
        }
    }
})