<template>
  <div>
    <div class="d-flex justify-content-between align-items-center mb-4">
      <h2>My Tasks</h2>
      <button class="btn btn-primary" @click="showTaskForm = true">
        Add New Task
      </button>
    </div>

    <div v-if="taskStore.loading" class="text-center">
      <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
      </div>
    </div>

    <div v-else-if="taskStore.error" class="alert alert-danger">
      {{ taskStore.error }}
    </div>

    <div v-else-if="taskStore.tasks.length === 0" class="alert alert-info">
      You don't have any tasks yet. Create your first task!
    </div>

    <div v-else class="row">
      <div class="col-md-6 mb-3" v-for="task in taskStore.tasks" :key="task.id">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title" :class="{ 'text-decoration-line-through': task.completed }">
              {{ task.title }}
            </h5>
            <p class="card-text">{{ task.description }}</p>
            <div class="d-flex justify-content-between align-items-center">
              <div class="form-check">
                <input 
                  class="form-check-input" 
                  type="checkbox" 
                  :checked="task.completed" 
                  @change="toggleTask(task)"
                >
                <label class="form-check-label">
                  {{ task.completed ? 'Completed' : 'Mark as complete' }}
                </label>
              </div>
              <div>
                <button class="btn btn-sm btn-outline-primary me-2" @click="editTask(task)">
                  Edit
                </button>
                <button class="btn btn-sm btn-outline-danger" @click="deleteTask(task.id)">
                  Delete
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <TaskForm
      v-if="showTaskForm"
      :task="editingTask"
      @close="closeTaskForm"
      @saved="handleTaskSaved"
    />
  </div>
</template>

<script>
import { ref, onMounted } from 'vue'
import { useTaskStore } from '../stores/tasks'
import { useAuthStore } from '../stores/auth'
import TaskForm from '../components/TaskForm.vue'

export default {
  name: 'Dashboard',
  components: {
    TaskForm
  },
  setup() {
    const taskStore = useTaskStore()
    const authStore = useAuthStore()
    const showTaskForm = ref(false)
    const editingTask = ref(null)

    onMounted(async () => {
      console.log('Dashboard mounted, fetching tasks...')
      console.log('Auth state:', {
        isAuthenticated: authStore.isAuthenticated,
        token: localStorage.getItem('token')
      })

      try {
        await taskStore.fetchTasks()
        console.log('Tasks fetched:', taskStore.tasks.length)
      } catch (error) {
        console.error('Error fetching tasks:', error)
      }
    })

    const toggleTask = async (task) => {
      try {
        await taskStore.updateTask(task.id, {
          completed: !task.completed
        })
      } catch (error) {
        console.error('Error updating task:', error)
      }
    }

    const deleteTask = async (id) => {
      if (confirm('Are you sure you want to delete this task?')) {
        try {
          await taskStore.deleteTask(id)
        } catch (error) {
          console.error('Error deleting task:', error)
        }
      }
    }

    const editTask = (task) => {
      editingTask.value = task
      showTaskForm.value = true
    }

    const closeTaskForm = () => {
      showTaskForm.value = false
      editingTask.value = null
    }



    const handleTaskSaved = () => {
      closeTaskForm()
      taskStore.fetchTasks()
    }

    return {
      taskStore,
      showTaskForm,
      editingTask,
      toggleTask,
      deleteTask,
      editTask,
      closeTaskForm,
      handleTaskSaved
    }
  }
}
</script>