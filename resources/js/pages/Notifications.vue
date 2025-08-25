<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2>Notifications</h2>
        <small class="text-muted">
          {{ unreadCount }} non lues sur {{ notifications.length }} total
          <span v-if="notificationStore.currentUserId" class="badge bg-info ms-2">
            User: {{ notificationStore.currentUserId }}
          </span>
        </small>
      </div>
      <div>
        <button
          class="btn btn-outline-secondary me-2"
          @click="markAllAsRead"
          :disabled="unreadCount === 0"
        >
          Marquer tout comme lu
        </button>
        <button
          class="btn btn-outline-danger"
          @click="clearAll"
          :disabled="notifications.length === 0"
        >
          Tout supprimer
        </button>

      </div>
    </div>

    <div v-if="notifications.length === 0" class="alert alert-info text-center">
      <i class="fas fa-bell-slash fa-2x mb-3"></i>
      <p class="mb-0">Vous n'avez aucune notification pour le moment.</p>
    </div>

    <div v-else class="list-group">
      <div
        v-for="notification in notifications"
        :key="notification.id"
        class="list-group-item d-flex justify-content-between align-items-start"
        :class="{
          'list-group-item-light': notification.read,
          'border-start border-primary border-3': !notification.read
        }"
      >
        <div class="d-flex align-items-start">
          <div class="me-3 mt-1">
            <i class="fas fa-check-circle text-success fa-lg"></i>
          </div>
          <div>
            <div class="fw-bold mb-1">{{ notification.message }}</div>
            <div class="text-primary mb-2">
              <i class="fas fa-tasks me-1"></i>
              {{ notification.task.title }}
            </div>
            <small class="text-muted">
              <i class="fas fa-clock me-1"></i>
              {{ formatDateTime(notification.task.created_at) }}
            </small>
          </div>
        </div>
        <div class="d-flex flex-column align-items-end">
          <span
            v-if="!notification.read"
            class="badge bg-primary mb-2"
          >
            Nouveau
          </span>
          <button
            v-if="!notification.read"
            class="btn btn-sm btn-outline-primary"
            @click="markAsRead(notification.id)"
          >
            <i class="fas fa-check me-1"></i>
            Marquer comme lu
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed, onMounted } from 'vue'
import { useNotificationStore } from '../stores/notifications'

export default {
  name: 'Notifications',
  setup() {
    const notificationStore = useNotificationStore()
    const notifications = computed(() => notificationStore.notifications)
    const unreadCount = computed(() => notificationStore.unreadCount)

    onMounted(() => {
      // Request notification permission when user visits notifications page
      notificationStore.requestNotificationPermission()
    })

    const markAsRead = (id) => {
      notificationStore.markAsRead(id)
    }

    const markAllAsRead = () => {
      notificationStore.markAllAsRead()
    }

    const clearAll = () => {
      notificationStore.clearAll()
    }



    const formatDateTime = (dateTime) => {
      if (!dateTime) return ''
      const date = new Date(dateTime)
      return date.toLocaleString('fr-FR', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

    return {
      notificationStore,
      notifications,
      unreadCount,
      markAsRead,
      markAllAsRead,
      clearAll,
      formatDateTime
    }
  }
}
</script>

<style scoped>
.list-group-item {
  transition: all 0.3s ease;
}

.list-group-item:hover {
  background-color: #f8f9fa;
}

.border-start {
  border-left-width: 4px !important;
}

.badge {
  font-size: 0.7rem;
}

.fa-check-circle {
  color: #28a745 !important;
}

.fa-tasks {
  color: #007bff !important;
}

.fa-clock {
  color: #6c757d !important;
}
</style>