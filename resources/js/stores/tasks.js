import { defineStore } from 'pinia'
import { api } from '../services/api'
import { useNotificationStore } from './notifications'

export const useTaskStore = defineStore('tasks', {
    state: () => ({
        tasks: [],
        loading: false,
        error: null
    }),

    actions: {
        async fetchTasks() {
            this.loading = true
            this.error = null
            console.log('Fetching tasks...')
            try {
                const response = await api.get('/tasks')
                console.log('Tasks response:', response.data)
                    // Trier les tâches par date de création (plus récentes en premier)
                this.tasks = response.data.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
            } catch (error) {
                console.error('Error fetching tasks:', error)
                this.error = (error.response && error.response.data) || error.message
            } finally {
                this.loading = false
            }
        },

        async createTask(taskData) {
            try {
                const response = await api.post('/tasks', taskData)
                this.tasks.push(response.data)

                // Ajouter la notification localement (pas de Pusher)
                const notificationStore = useNotificationStore()

                // S'assurer que le store est initialisé pour l'utilisateur actuel
                if (!notificationStore.currentUserId) {
                    notificationStore.initializeForUser(response.data.user_id)
                }

                notificationStore.addNotification(
                    'Une nouvelle tâche a été créée avec succès!',
                    response.data,
                    'task_created'
                )

                console.log(`✅ Local notification added for user ${response.data.user_id} only`)

                return response.data
            } catch (error) {
                throw error.response.data
            }
        },

        async updateTask(id, taskData) {
            try {
                const response = await api.put(`/tasks/${id}`, taskData)
                const index = this.tasks.findIndex(task => task.id === id)
                if (index !== -1) {
                    this.tasks[index] = response.data
                }
                return response.data
            } catch (error) {
                throw error.response.data
            }
        },

        async deleteTask(id) {
            try {
                await api.delete(`/tasks/${id}`)
                this.tasks = this.tasks.filter(task => task.id !== id)
            } catch (error) {
                throw error.response.data
            }
        }
    }
})