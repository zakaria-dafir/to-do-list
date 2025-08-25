<template>
  <div id="app">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary" v-if="isAuthenticated">
      <div class="container">
        <router-link to="/" class="navbar-brand">Todo App</router-link>
        <div class="navbar-nav ms-auto">
          <router-link to="/" class="nav-link">
            <i class="fas fa-tasks me-1"></i>Mes Tâches
          </router-link>
          <router-link to="/notifications" class="nav-link">
            <i class="fas fa-bell me-1"></i>Notifications
            <span class="badge bg-danger" v-if="unreadCount > 0">{{ unreadCount }}</span>
          </router-link>
          <router-link to="/profile" class="nav-link">
            <i class="fas fa-user me-1"></i>Profil
          </router-link>
          <button @click="logout" class="btn btn-outline-light btn-sm ms-2">
            <i class="fas fa-sign-out-alt me-1"></i>Déconnexion
          </button>
        </div>
      </div>
    </nav>
    
    <div class="container mt-4">
      <router-view></router-view>
    </div>
  </div>
</template>

<script>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'
import { useNotificationStore } from './stores/notifications'

export default {
  name: 'App',
  setup() {
    const authStore = useAuthStore()
    const notificationStore = useNotificationStore()
    const router = useRouter()

    const isAuthenticated = computed(() => authStore.isAuthenticated)
    const unreadCount = computed(() => notificationStore.unreadCount)

    onMounted(async () => {
      // Initialize auth state from localStorage
      authStore.initializeAuth()

      // Initialize cross-tab notification sync
      notificationStore.initializeCrossTabSync()

      // Nettoyer d'abord les notifications en mémoire
      notificationStore.resetForNewUser()

      // Initialiser les notifications avec l'user_id sauvegardé
      if (authStore.savedUserId) {
        console.log('Initializing notifications with saved user ID:', authStore.savedUserId)
        notificationStore.initializeForUser(authStore.savedUserId)
      }

      if (authStore.isAuthenticated) {
        try {
          await authStore.fetchUser()
          console.log('User fetched:', authStore.user)

          // S'assurer que les notifications sont initialisées avec les bonnes données utilisateur
          if (!notificationStore.currentUserId && authStore.user) {
            console.log('Initializing notifications with fetched user:', authStore.user.id)
            notificationStore.initializeForUser(authStore.user.id)
          }

          notificationStore.initializeWebSocket()
        } catch (error) {
          console.error('Error initializing user:', error)
        }
      } else {
        console.log('User not authenticated, skipping initialization')
      }
    })

    const logout = () => {
      authStore.logout()
      router.push('/login')
    }

    return {
      isAuthenticated,
      unreadCount,
      logout
    }
  }
}
</script>

<style>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
</style>