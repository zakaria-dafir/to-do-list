<template>
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header">
          <h3 class="text-center">Login</h3>
        </div>
        <div class="card-body">
          <form @submit.prevent="handleLogin">
            <div class="mb-3">
              <label for="email" class="form-label">Email</label>
              <input type="email" class="form-control" id="email" v-model="form.email" required>
            </div>
            <div class="mb-3">
              <label for="password" class="form-label">Password</label>
              <input type="password" class="form-control" id="password" v-model="form.password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100" :disabled="loading">
              {{ loading ? 'Logging in...' : 'Login' }}
            </button>
          </form>
          <div class="mt-3 text-center">
            <p>Don't have an account? <router-link to="/register">Register here</router-link></p>
          </div>
          <div v-if="error" class="alert alert-danger mt-3">
            {{ error }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { useNotificationStore } from '../stores/notifications'

export default {
  name: 'Login',
  setup() {
    const form = ref({
      email: '',
      password: ''
    })
    const loading = ref(false)
    const error = ref('')
    const router = useRouter()
    const authStore = useAuthStore()
    const notificationStore = useNotificationStore()

    const handleLogin = async () => {
      loading.value = true
      error.value = ''

      try {
        // Nettoyer complètement les notifications avant le login
        console.log('🧹 Cleaning notifications before login')
        notificationStore.resetForNewUser()

        await authStore.login(form.value)
        router.push('/dashboard')
      } catch (err) {
        error.value = err.error || 'Login failed'
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      loading,
      error,
      handleLogin
    }
  }
}
</script>