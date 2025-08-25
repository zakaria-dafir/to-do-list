<template>
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">
          <h3 class="text-center">Register</h3>
        </div>
        <div class="card-body">
          <form @submit.prevent="handleRegister">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="full_name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="full_name" v-model="form.full_name" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" v-model="form.email" required>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="phone_number" class="form-label">Phone Number</label>
                <input type="tel" class="form-control" id="phone_number" v-model="form.phone_number" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="address" class="form-label">Address</label>
                <input type="text" class="form-control" id="address" v-model="form.address" required>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" v-model="form.password" required>
              </div>
              <div class="col-md-6 mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="password_confirmation" v-model="form.password_confirmation" required>
              </div>
            </div>
            <button type="submit" class="btn btn-primary w-100" :disabled="loading">
              {{ loading ? 'Registering...' : 'Register' }}
            </button>
          </form>
          <div class="mt-3 text-center">
            <p>Already have an account? <router-link to="/login">Login here</router-link></p>
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

export default {
  name: 'Register',
  setup() {
    const form = ref({
      full_name: '',
      email: '',
      phone_number: '',
      address: '',
      password: '',
      password_confirmation: ''
    })
    const loading = ref(false)
    const error = ref('')
    const router = useRouter()
    const authStore = useAuthStore()

    const handleRegister = async () => {
      loading.value = true
      error.value = ''
      
      try {
        await authStore.register(form.value)
        router.push('/dashboard')
      } catch (err) {
        if (err.errors) {
          error.value = Object.values(err.errors).flat().join(', ')
        } else {
          error.value = err.error || 'Registration failed'
        }
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      loading,
      error,
      handleRegister
    }
  }
}
</script>