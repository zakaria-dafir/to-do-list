<template>
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            <h3><i class="fas fa-user me-2"></i>Mon Profil</h3>
          </div>
          <div class="card-body">
            <!-- Loader pendant le chargement -->
            <div v-if="isLoading" class="text-center py-5">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Chargement...</span>
              </div>
              <p class="mt-3 text-muted">Chargement du profil...</p>
            </div>

            <!-- Contenu du profil -->
            <div v-else>
              <!-- Photo de profil -->
              <div class="text-center mb-4">
                <div class="position-relative d-inline-block">
                  <img
                    :src="profileImageUrl"
                    alt="Photo de profil"
                    class="rounded-circle profile-image"
                    @error="handleImageError"
                  >
                  <button
                    class="btn btn-warning btn-sm position-absolute camera-btn"
                    @click="triggerFileInput"
                    title="Changer la photo"
                  >
                    <i class="fas fa-camera"></i>
                  </button>
                </div>
                <input 
                  ref="fileInput"
                  type="file" 
                  accept="image/*" 
                  @change="handleImageUpload"
                  class="d-none"
                >
                <div class="mt-2" :key="user?.updated_at">
                  <h5>{{ user?.full_name || user?.name || 'Utilisateur' }}</h5>
                  <p class="text-muted">{{ user?.email || '' }}</p>
                </div>
              </div>

              <!-- Formulaire de modification -->
              <form @submit.prevent="updateProfile">
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="name" class="form-label">Nom complet</label>
                      <input 
                        type="text" 
                        class="form-control" 
                        id="name" 
                        v-model="form.name"
                        required
                      >
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="email" class="form-label">Email</label>
                      <input 
                        type="email" 
                        class="form-control" 
                        id="email" 
                        v-model="form.email"
                        required
                      >
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="phone" class="form-label">Téléphone</label>
                      <input 
                        type="tel" 
                        class="form-control" 
                        id="phone" 
                        v-model="form.phone"
                        placeholder="Optionnel"
                      >
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label for="address" class="form-label">Adresse</label>
                      <textarea 
                        class="form-control" 
                        id="address" 
                        v-model="form.bio"
                        rows="3"
                        placeholder="Votre adresse..."
                      ></textarea>
                    </div>
                  </div>
                </div>

                <div class="d-flex justify-content-between">
                  <button 
                    type="submit" 
                    class="btn btn-primary"
                    :disabled="loading"
                  >
                    <i class="fas fa-save me-2"></i>
                    {{ loading ? 'Sauvegarde...' : 'Sauvegarder' }}
                  </button>
                  

                </div>
              </form>

              <!-- Messages -->
              <div v-if="successMessage" class="alert alert-success mt-3">
                <i class="fas fa-check-circle me-2"></i>{{ successMessage }}
              </div>
              <div v-if="errorMessage" class="alert alert-danger mt-3">
                <i class="fas fa-exclamation-circle me-2"></i>{{ errorMessage }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>


  </div>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue'
import { useAuthStore } from '../stores/auth'
import { api } from '../services/api'

export default {
  name: 'Profile',
  setup() {
    const authStore = useAuthStore()
    const user = computed(() => authStore.user)
    
    const form = ref({
      full_name: '',
      email: '',
      phone_number: '',
      address: ''
    })
    
    const loading = ref(false)
    const successMessage = ref('')
    const errorMessage = ref('')
    const fileInput = ref(null)
    const isLoading = ref(true)
    const profileImageUrl = ref('/images/default-avatar.svg')

    // Function to update profile image URL
    const updateProfileImageUrl = () => {
      const img = user.value?.image
      if (img) {
        const bust = Date.now()
        profileImageUrl.value = `/storage/${img}?v=${bust}`
        console.log('🖼️ Image URL updated to:', profileImageUrl.value)
      } else {
        profileImageUrl.value = '/images/default-avatar.svg'
        console.log('🖼️ Using default image')
      }
    }

    const loadUserData = () => {
      console.log('🔄 Chargement des données utilisateur...', user.value)
      console.log('🔥 USER FULL_NAME:', user.value?.full_name)
      console.log('🔥 FORM BEFORE:', form.value)

      if (user.value) {
        // Pré-remplir le formulaire avec les bonnes clés DB -> Form
        form.value.name = user.value.full_name || ''  // DB: full_name -> Form: name
        form.value.email = user.value.email || ''
        form.value.phone = user.value.phone_number || ''  // DB: phone_number -> Form: phone
        form.value.bio = user.value.address || ''  // DB: address -> Form: bio

        console.log('✅ Formulaire rempli:', form.value)
        console.log('🔥 FORM AFTER:', form.value)
        console.log('📸 Image depuis la DB:', user.value.image)

        // Update the profile image URL
        updateProfileImageUrl()

        isLoading.value = false
      } else {
        console.log('❌ Aucune donnée utilisateur disponible')
        isLoading.value = false
      }
    }

    const triggerFileInput = () => {
      fileInput.value.click()
    }

    const handleImageUpload = async (event) => {
      const file = event.target.files[0]
      if (!file) return

      // Vérifications du fichier
      if (!file.type.startsWith('image/')) {
        errorMessage.value = 'Veuillez sélectionner un fichier image valide'
        return
      }

      if (file.size > 5 * 1024 * 1024) {
        errorMessage.value = 'L\'image ne doit pas dépasser 5MB'
        return
      }

      const formData = new FormData()
      formData.append('profile_image', file)

      try {
        loading.value = true
        errorMessage.value = ''
        
        const response = await api.post('/profile/image', formData, {
          headers: {
            'Content-Type': 'multipart/form-data'
          }
        })
        
        console.log('✅ Image upload response:', response.data)

        // FORCE update the image URL immediately
        if (response.data.image) {
          profileImageUrl.value = `/storage/${response.data.image}?v=${Date.now()}`
          console.log('🖼️ FORCED image URL to:', profileImageUrl.value)
        }

        // Update user in store
        await authStore.fetchUser()

        successMessage.value = 'Photo de profil mise à jour avec succès!'
        setTimeout(() => successMessage.value = '', 3000)
        
      } catch (error) {
        console.error('❌ Erreur upload image:', error)
        errorMessage.value = error.response?.data?.message || 'Erreur lors du téléchargement de l\'image'
      } finally {
        loading.value = false
        // Réinitialiser l'input file
        event.target.value = ''
      }
    }

    const handleImageError = () => {
      console.log('❌ Erreur de chargement de l\'image')
      profileImageUrl.value = '/images/default-avatar.svg'
    }

    const updateProfile = async () => {


      try {
        loading.value = true
        errorMessage.value = ''

        await api.put('/profile', form.value)
        
        // Mettre à jour l'utilisateur dans le store
        await authStore.fetchUser()



        successMessage.value = 'Profil mis à jour avec succès!'
        setTimeout(() => successMessage.value = '', 3000)
        
      } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Erreur lors de la mise à jour du profil'
      } finally {
        loading.value = false
      }
    }



    // Watcher pour surveiller les changements de l'utilisateur
    watch(user, (newUser) => {
      if (newUser) {
        loadUserData()
        // Also update image URL when user changes
        updateProfileImageUrl()
      }
    }, { immediate: true })

    onMounted(async () => {
      console.log('🚀 Profile mounted')
      isLoading.value = true

      // Récupérer les données utilisateur
      if (authStore.isAuthenticated) {
        try {
          await authStore.fetchUser()
          console.log('✅ Utilisateur chargé depuis le store:', authStore.user)
          console.log('📸 Image field:', authStore.user?.image)
          console.log('🔥 ALL USER FIELDS:', Object.keys(authStore.user || {}))

          // Force update image URL if user has an image
          if (authStore.user?.image) {
            profileImageUrl.value = `/storage/${authStore.user.image}?v=${Date.now()}`
            console.log('🖼️ Set existing image URL:', profileImageUrl.value)
          }

          // Force load user data into form
          loadUserData()
        } catch (error) {
          console.error('❌ Erreur:', error)
        }
      }

      loadUserData()
    })

    return {
      user,
      form,
      loading,
      successMessage,
      errorMessage,
      fileInput,
      profileImageUrl,
      isLoading,
      triggerFileInput,
      handleImageUpload,
      handleImageError,
      updateProfile,
    }
  }
}
</script>

<style scoped>
.profile-image {
  width: 150px;
  height: 150px;
  object-fit: cover;
  border: 4px solid #fff;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.camera-btn {
  bottom: 5px;
  right: 5px;
  width: 35px;
  height: 35px;
  padding: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  font-size: 12px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.2);
  border: 2px solid #fff;
  transition: all 0.3s ease;
}

.camera-btn:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.modal.show {
  background-color: rgba(0,0,0,0.5);
}
</style>ù