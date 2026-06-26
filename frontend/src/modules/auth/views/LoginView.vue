<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router    = useRouter()
const authStore = useAuthStore()

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
      router.push('/dashboard')
    } else {
      error.value = result.message || 'Las credenciales no coinciden con nuestros registros.'
    }
  } catch (e) {
    error.value = e.response?.data?.message
      || 'Las credenciales no coinciden con nuestros registros. Verifica mayúsculas o contacta a soporte.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-root">
    <v-card class="login-card" :elevation="6" rounded="lg" width="440">

      <!-- ── Cabecera de marca ────────────────── -->
      <div class="lb-brand">
        <div class="lb-brand-mark">
          <v-icon icon="mdi-dumbbell" size="22" color="primary" />
        </div>
        <div>
          <div class="lb-brand-name">Gyminix</div>
          <div class="lb-brand-sub">Sistema de Gestión de Gimnasios</div>
        </div>
      </div>

      <v-divider />

      <!-- ── Cuerpo del formulario ───────────── -->
      <div class="lb-body">
        <div class="lb-heading">
          <h1 class="lb-title">Acceso al Portal</h1>
          <p class="lb-subtitle">Ingrese sus credenciales institucionales para continuar</p>
        </div>

        <!-- Feedback de error — tono informativo, no acusatorio -->
        <Transition name="err-slide">
          <div v-if="error" class="lb-error">
            <v-icon icon="mdi-alert-outline" size="15" class="lb-error-icon" />
            <span>{{ error }}</span>
          </div>
        </Transition>

        <v-form @submit.prevent="handleLogin">

          <!-- Campo: Identificador -->
          <div class="lb-field">
            <label class="lb-label">Identificador de acceso</label>
            <v-text-field
              v-model="form.email"
              placeholder="Email corporativo o usuario"
              variant="outlined"
              density="comfortable"
              prepend-inner-icon="mdi-account-circle-outline"
              hide-details
              autofocus
              required
            />
          </div>

          <!-- Campo: Contraseña -->
          <div class="lb-field">
            <label class="lb-label">Contraseña</label>
            <v-text-field
              v-model="form.password"
              :type="showPass ? 'text' : 'password'"
              placeholder="••••••••"
              variant="outlined"
              density="comfortable"
              prepend-inner-icon="mdi-lock-outline"
              :append-inner-icon="showPass ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
              hide-details
              required
              @click:append-inner="showPass = !showPass"
            />
            <p class="lb-policy">
              <v-icon icon="mdi-shield-check-outline" size="12" class="me-1" style="opacity:.7" />
              Mínimo 8 caracteres, 1 mayúscula y 1 carácter especial
            </p>
          </div>

          <!-- Recordar dispositivo -->
          <div class="lb-remember">
            <v-checkbox
              v-model="form.remember"
              density="compact"
              color="primary"
              hide-details
            >
              <template #label>
                <span class="lb-remember-label">Recordar este dispositivo</span>
              </template>
            </v-checkbox>
          </div>

          <!-- Acción primaria -->
          <v-btn
            type="submit"
            block
            size="large"
            color="primary"
            :loading="loading"
            class="lb-submit"
          >
            Iniciar Sesión
          </v-btn>

        </v-form>

        <!-- Acciones secundaria y terciaria -->
        <div class="lb-links">
          <a class="lb-link" href="#" @click.prevent>¿Olvidaste tu contraseña?</a>
          <span class="lb-link-sep">·</span>
          <a class="lb-link" href="#" @click.prevent>Soporte de TI</a>
        </div>
      </div>

      <!-- ── Pie de seguridad ────────────────── -->
      <div class="lb-footer">
        <span class="lb-ssl">
          <v-icon icon="mdi-lock" size="12" class="me-1" />
          Conexión segura SSL 256-bit
        </span>
        <span class="lb-footer-sep">·</span>
        <span>&copy; {{ new Date().getFullYear() }} Gyminix</span>
      </div>

    </v-card>
  </div>
</template>

<style scoped>
/* ── Fondo institucional ──────────────────────────────────── */
.login-root {
  min-height: 100vh;
  background: #eef0f4;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 24px;
  font-family: 'Inter', 'Roboto', system-ui, -apple-system, sans-serif;
}

/* Adapt background in dark mode */
:global(.v-theme--dark) .login-root {
  background: #0f1117;
}

/* ── Tarjeta principal ────────────────────────────────────── */
.login-card {
  border: 1px solid rgba(0, 0, 0, 0.07) !important;
}

:global(.v-theme--dark) .login-card {
  border-color: rgba(255, 255, 255, 0.08) !important;
}

/* ── Cabecera de marca ────────────────────────────────────── */
.lb-brand {
  display: flex;
  align-items: center;
  gap: 13px;
  padding: 26px 30px 22px;
}

.lb-brand-mark {
  width: 40px;
  height: 40px;
  border-radius: 9px;
  background: rgba(26, 115, 232, 0.09);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.lb-brand-name {
  font-size: 1.1rem;
  font-weight: 700;
  letter-spacing: -0.4px;
  line-height: 1.15;
}

.lb-brand-sub {
  font-size: 0.7rem;
  color: #8c95a6;
  letter-spacing: 0.15px;
  margin-top: 2px;
}

/* ── Cuerpo ───────────────────────────────────────────────── */
.lb-body {
  padding: 26px 30px 24px;
}

.lb-heading {
  margin-bottom: 22px;
}

.lb-title {
  font-size: 1.05rem;
  font-weight: 600;
  letter-spacing: -0.2px;
  margin: 0 0 4px;
  line-height: 1.3;
}

.lb-subtitle {
  font-size: 0.8rem;
  color: #8c95a6;
  margin: 0;
  line-height: 1.5;
}

/* ── Error — informativo, no acusatorio ──────────────────── */
.lb-error {
  display: flex;
  align-items: flex-start;
  gap: 8px;
  background: #fffbf5;
  border: 1px solid #e8d5b0;
  border-left: 3px solid #f0a500;
  border-radius: 6px;
  padding: 10px 14px;
  margin-bottom: 18px;
  font-size: 0.8rem;
  color: #4a4f5e;
  line-height: 1.55;
}

:global(.v-theme--dark) .lb-error {
  background: #1e1a12;
  border-color: #6b500a;
  border-left-color: #f0a500;
  color: #c8b89a;
}

.lb-error-icon {
  color: #f0a500;
  margin-top: 1px;
  flex-shrink: 0;
}

.err-slide-enter-active { transition: all 0.18s ease; }
.err-slide-leave-active { transition: all 0.14s ease; }
.err-slide-enter-from   { opacity: 0; transform: translateY(-6px); }
.err-slide-leave-to     { opacity: 0; transform: translateY(-4px); }

/* ── Campos ───────────────────────────────────────────────── */
.lb-field {
  margin-bottom: 16px;
}

.lb-label {
  display: block;
  font-size: 0.75rem;
  font-weight: 600;
  color: #3c4257;
  letter-spacing: 0.25px;
  margin-bottom: 6px;
  text-transform: uppercase;
}

:global(.v-theme--dark) .lb-label {
  color: #9aa5b4;
}

.lb-policy {
  display: flex;
  align-items: center;
  margin: 6px 0 0;
  font-size: 0.72rem;
  color: #9aa5b4;
  line-height: 1.4;
}

/* ── Recordar dispositivo ────────────────────────────────── */
.lb-remember {
  margin: 2px 0 20px;
}

.lb-remember-label {
  font-size: 0.8rem;
  color: #5f6b7a;
}

:global(.v-theme--dark) .lb-remember-label {
  color: #8c95a6;
}

/* ── Botón principal ──────────────────────────────────────── */
.lb-submit {
  font-weight: 600 !important;
  font-size: 0.88rem !important;
  letter-spacing: 0.4px !important;
  text-transform: none !important;
  border-radius: 8px !important;
  margin-bottom: 20px;
}

/* ── Links secundarios ────────────────────────────────────── */
.lb-links {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  font-size: 0.78rem;
}

.lb-link {
  color: #5f6b7a;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.15s;
}

.lb-link:hover {
  color: #1a73e8;
}

.lb-link-sep {
  color: #c5cdd8;
  user-select: none;
}

/* ── Pie de seguridad ─────────────────────────────────────── */
.lb-footer {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 13px 30px;
  border-top: 1px solid rgba(0, 0, 0, 0.06);
  background: #f8f9fb;
  border-radius: 0 0 8px 8px;
  font-size: 0.7rem;
  color: #9aa5b4;
  letter-spacing: 0.1px;
}

:global(.v-theme--dark) .lb-footer {
  background: rgba(255, 255, 255, 0.03);
  border-top-color: rgba(255, 255, 255, 0.07);
}

.lb-ssl {
  display: flex;
  align-items: center;
  color: #6b7a8d;
}

.lb-footer-sep {
  color: #c5cdd8;
  user-select: none;
}
</style>
