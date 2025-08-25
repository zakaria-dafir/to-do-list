<template>
  <div class="modal show d-block" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">{{ isEditing ? 'Edit Task' : 'Add New Task' }}</h5>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>
        <div class="modal-body">
          <form @submit.prevent="handleSubmit">
            <div class="mb-3">
              <label for="title" class="form-label">Title</label>
              <input type="text" class="form-control" id="title" v-model="form.title" required>
            </div>
            <div class="mb-3">
              <label for="description" class="form-label">Description</label>
              <textarea class="form-control" id="description" v-model="form.description" rows="3"></textarea>
            </div>
            <div class="d-flex justify-content-end">
              <button type="button" class="btn btn-secondary me-2" @click="$emit('close')">
                Cancel
              </button>
              <button type="submit" class="btn btn-primary" :disabled="loading">
                {{ loading ? 'Saving...' : (isEditing ? 'Update Task' : 'Save Task') }}
              </button>
            </div>
          </form>
          <div v-if="error" class="alert alert-danger mt-3">
            {{ error }}
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, watch } from 'vue'
import { useTaskStore } from '../stores/tasks'

export default {
  name: 'TaskForm',
  props: {
    task: {
      type: Object,
      default: null
    }
  },
  emits: ['close', 'saved'],
  setup(props, { emit }) {
    const form = ref({
      title: props.task?.title || '',
      description: props.task?.description || ''
    })
    const loading = ref(false)
    const error = ref('')
    const taskStore = useTaskStore()
    const isEditing = ref(!!props.task)

    // Watch for changes in the task prop and update form accordingly
    watch(() => props.task, (newTask) => {
      if (newTask) {
        form.value.title = newTask.title || ''
        form.value.description = newTask.description || ''
        isEditing.value = true
      } else {
        form.value.title = ''
        form.value.description = ''
        isEditing.value = false
      }
    }, { immediate: true })

    const handleSubmit = async () => {
      loading.value = true
      error.value = ''

      try {
        if (isEditing.value) {
          await taskStore.updateTask(props.task.id, form.value)
        } else {
          await taskStore.createTask(form.value)
        }
        emit('saved')
        emit('close')
      } catch (err) {
        if (err.errors) {
          error.value = Object.values(err.errors).flat().join(', ')
        } else {
          error.value = err.error || (isEditing.value ? 'Failed to update task' : 'Failed to create task')
        }
      } finally {
        loading.value = false
      }
    }

    return {
      form,
      loading,
      error,
      isEditing,
      handleSubmit
    }
  }
}
</script>

<style scoped>
.modal {
  background-color: rgba(0, 0, 0, 0.5);
}
</style>