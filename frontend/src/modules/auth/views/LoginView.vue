<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'vue-toastification'

const router    = useRouter()
const authStore = useAuthStore()
const toast     = useToast()

const form    = ref({ email: '', password: '', remember: false })
const loading = ref(false)
const error   = ref('')

async function handleLogin() {
  error.value   = ''
  loading.value = true
  try {
    const result = await authStore.login(form.value)
    if (result.success) {
      toast.success('¡Bienvenido!')
      router.push('/dashboard')
    } else {
      error.value = result.message || 'Error al iniciar sesión'
    }
  } catch (e) {
    error.value = e.response?.data?.message || 'Error de conexión'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-page">
    <div class="login-card">
      <div class="login-logo">
        <div style="font-size:2.5rem;color:#1a73e8"><i class="fa fa-dumbbell"></i></div>
        <h1>Gyminix</h1>
        <p>Sistema de Gestión de Gimnasios</p>
      </div>

      <form @submit.prevent="handleLogin">
        <div class="mb-3">
          <label class="form-label">Correo electrónico</label>
          <input v-model="form.email" type="email" class="form-control" placeholder="usuario@email.com" required autofocus />
        </div>

        <div class="mb-3">
          <label class="form-label">Contraseña</label>
          <input v-model="form.password" type="password" class="form-control" placeholder="••••••••" required />
        </div>

        <div class="mb-4 d-flex align-items-center justify-content-between">
          <div class="form-check">
            <input v-model="form.remember" type="checkbox" class="form-check-input" id="remember" />
            <label class="form-check-label text-muted-sm" for="remember">Recordarme</label>
          </div>
        </div>

        <div v-if="error" class="alert alert-danger py-2 text-sm mb-3" style="font-size:.85rem">
          <i class="fa fa-exclamation-circle me-1"></i> {{ error }}
        </div>

        <button type="submit" class="btn btn-primary w-100" :disabled="loading">
          <span v-if="loading" class="spinner-sm me-2"></span>
          {{ loading ? 'Ingresando...' : 'Iniciar Sesión' }}
        </button>
      </form>

      <p class="text-center text-muted-sm mt-4" style="margin-bottom:0">
        Gyminix &copy; {{ new Date().getFullYear() }}
      </p>
    </div>
  </div>
</template>
