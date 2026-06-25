<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useToast } from 'vue-toastification'

const router    = useRouter()
const authStore = useAuthStore()
const toast     = useToast()

const form     = ref({ email: '', password: '', remember: false })
const loading  = ref(false)
const error    = ref('')
const showPass = ref(false)

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
  <div class="auth-wrapper">
    <v-card width="420" rounded="xl" elevation="12">
      <!-- Logo -->
      <v-card-text class="text-center pt-10 pb-2">
        <div class="d-flex align-center justify-center ga-2 mb-3">
          <v-icon icon="mdi-dumbbell" color="primary" size="40" />
          <span class="text-h4 font-weight-bold text-primary">Gyminix</span>
        </div>
        <p class="text-body-2 text-medium-emphasis mb-0">Sistema de Gestión de Gimnasios</p>
      </v-card-text>

      <v-divider class="mx-6 mt-2 mb-1" />

      <v-card-text class="px-8 py-6">
        <h2 class="text-h6 font-weight-semibold mb-1">Bienvenido de vuelta 👋</h2>
        <p class="text-body-2 text-medium-emphasis mb-6">Inicia sesión para continuar</p>

        <!-- Error alert -->
        <v-alert
          v-if="error"
          type="error"
          variant="tonal"
          density="compact"
          class="mb-4 slide-down"
          :text="error"
          closable
          @click:close="error = ''"
        />

        <v-form @submit.prevent="handleLogin">
          <v-text-field
            v-model="form.email"
            label="Correo electrónico"
            type="email"
            prepend-inner-icon="mdi-email-outline"
            class="mb-3"
            autofocus
            required
          />

          <v-text-field
            v-model="form.password"
            label="Contraseña"
            :type="showPass ? 'text' : 'password'"
            prepend-inner-icon="mdi-lock-outline"
            :append-inner-icon="showPass ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
            class="mb-3"
            required
            @click:append-inner="showPass = !showPass"
          />

          <v-checkbox
            v-model="form.remember"
            label="Recordarme"
            class="mb-4"
          />

          <v-btn
            type="submit"
            block
            size="large"
            :loading="loading"
          >
            Iniciar Sesión
          </v-btn>
        </v-form>
      </v-card-text>

      <v-card-text class="text-center py-4">
        <span class="text-caption text-medium-emphasis">
          Gyminix &copy; {{ new Date().getFullYear() }}
        </span>
      </v-card-text>
    </v-card>
  </div>
</template>
