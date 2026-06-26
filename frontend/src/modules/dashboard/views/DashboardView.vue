<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useGymStore } from '@/stores/gym'
import api from '@/api/axios'

const router    = useRouter()
const authStore = useAuthStore()
const gymStore  = useGymStore()

const stats   = ref({ gyms: 0, branches: 0, users: 0, roles: 0 })
const logs    = ref([])
const loading = ref(true)

const statCards = [
  { key: 'gyms',     label: 'Gimnasios',  icon: 'mdi-domain',       color: 'primary' },
  { key: 'branches', label: 'Sucursales', icon: 'mdi-map-marker',    color: 'success' },
  { key: 'users',    label: 'Usuarios',   icon: 'mdi-account-group', color: 'error'   },
  { key: 'roles',    label: 'Roles',      icon: 'mdi-shield-check',  color: 'warning' },
]

const weeklyActivity = computed(() => {
  const counts = Array(7).fill(0)
  const now = new Date()
  for (const log of logs.value) {
    const diff = Math.floor((now - new Date(log.created_at)) / 86400000)
    if (diff >= 0 && diff < 7) counts[6 - diff]++
  }
  return counts.length ? counts : [0, 0, 0, 0, 0, 0, 0]
})

const actionColor = (a) => ({
  login: 'success', logout: 'secondary', create: 'primary',
  update: 'warning', delete: 'error', toggle_status: 'info',
  sync_permissions: 'deep-purple',
}[a] || 'default')

const actionIcon = (a) => ({
  login: 'mdi-login', logout: 'mdi-logout', create: 'mdi-plus-circle-outline',
  update: 'mdi-pencil-outline', delete: 'mdi-delete-outline',
  toggle_status: 'mdi-toggle-switch-outline', sync_permissions: 'mdi-key-outline',
}[a] || 'mdi-circle-small')

function timeAgo(dt) {
  const diff = Math.floor((Date.now() - new Date(dt)) / 1000)
  if (diff < 60)    return 'hace un momento'
  if (diff < 3600)  return `hace ${Math.floor(diff / 60)} min`
  if (diff < 86400) return `hace ${Math.floor(diff / 3600)} h`
  return `hace ${Math.floor(diff / 86400)} d`
}

onMounted(async () => {
  try {
    const { data } = await api.get('/dashboard')
    if (data.success) {
      stats.value = data.data.stats
      logs.value  = data.data.recent_logs
    }
  } finally { loading.value = false }
})
</script>

<template>
  <div>
    <!-- ── ROW 1: Welcome + Stats ─────────────────────────────── -->
    <v-row class="mb-4" align="stretch">

      <!-- Welcome card -->
      <v-col cols="12" md="5">
        <v-card elevation="0" border class="overflow-hidden" height="100%">
          <v-card-text class="pa-6 d-flex flex-column" style="min-height:190px; height:100%">
            <div class="d-flex align-start justify-space-between">
              <div>
                <p class="text-caption text-medium-emphasis mb-1 text-uppercase font-weight-medium">
                  {{ authStore.isSuperAdmin ? 'Super Administrador' : 'Administrador' }}
                </p>
                <h2 class="text-h5 font-weight-bold mb-2">
                  ¡Bienvenido, {{ authStore.user?.first_name }}! 👋
                </h2>
                <p class="text-body-2 text-medium-emphasis mb-3">
                  Aquí está el resumen de la actividad del sistema.
                </p>
                <v-chip
                  v-if="gymStore.current"
                  color="primary"
                  variant="tonal"
                  size="small"
                  prepend-icon="mdi-domain"
                >
                  {{ gymStore.current.name }}
                </v-chip>
              </div>
              <v-avatar
                color="primary"
                variant="tonal"
                size="80"
                rounded="xl"
                class="ms-3 flex-shrink-0"
              >
                <v-icon icon="mdi-dumbbell" size="44" />
              </v-avatar>
            </div>
            <v-spacer />
            <v-btn
              color="primary"
              variant="tonal"
              prepend-icon="mdi-history"
              class="mt-5 align-self-start"
              @click="router.push('/audit-logs')"
            >
              Ver actividad
            </v-btn>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Stats 2×2 -->
      <v-col cols="12" md="7">
        <v-card elevation="0" border height="100%">
          <v-card-title class="pa-5 pb-3 text-body-1 font-weight-medium d-flex align-center justify-space-between">
            Resumen del sistema
            <v-chip size="x-small" variant="tonal" color="success" prepend-icon="mdi-circle-small">
              En línea
            </v-chip>
          </v-card-title>
          <v-card-text class="px-4 pb-4 pt-0">
            <v-row dense>
              <v-col v-for="card in statCards" :key="card.key" cols="6">
                <v-sheet border rounded="lg" class="pa-4 d-flex align-center ga-3">
                  <v-avatar :color="card.color" variant="tonal" size="46" rounded="lg">
                    <v-icon :icon="card.icon" size="22" />
                  </v-avatar>
                  <div>
                    <div class="text-h5 font-weight-bold" style="line-height:1.1">
                      <v-skeleton-loader v-if="loading" type="text" width="30" class="mt-1" />
                      <span v-else>{{ stats[card.key] }}</span>
                    </div>
                    <div class="text-caption text-medium-emphasis">{{ card.label }}</div>
                  </div>
                </v-sheet>
              </v-col>
            </v-row>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- ── ROW 2: Sparkline + Activity list + Mini metrics ────── -->
    <v-row align="stretch">

      <!-- Weekly sparkline -->
      <v-col cols="12" md="4">
        <v-card elevation="0" border height="100%">
          <v-card-text class="pa-5">
            <div class="d-flex align-center justify-space-between mb-1">
              <span class="text-body-2 font-weight-medium">Actividad semanal</span>
              <v-chip size="x-small" variant="tonal" color="primary">últimos 7 días</v-chip>
            </div>
            <div class="text-h4 font-weight-bold mt-3">
              <v-skeleton-loader v-if="loading" type="text" width="50" />
              <span v-else>{{ logs.length }}</span>
            </div>
            <div class="text-caption text-medium-emphasis mb-1">acciones registradas</div>

            <v-skeleton-loader v-if="loading" type="image" height="80" class="mt-3 rounded-lg" />
            <v-sparkline
              v-else
              :model-value="weeklyActivity"
              color="primary"
              :line-width="2"
              padding="8"
              smooth
              auto-draw
              class="mt-1"
            />

            <v-divider class="my-3" />
            <div class="d-flex ga-4">
              <div>
                <div class="text-caption text-medium-emphasis">Hoy</div>
                <div class="text-body-2 font-weight-bold">{{ weeklyActivity[6] }}</div>
              </div>
              <div>
                <div class="text-caption text-medium-emphasis">Ayer</div>
                <div class="text-body-2 font-weight-bold">{{ weeklyActivity[5] }}</div>
              </div>
              <div>
                <div class="text-caption text-medium-emphasis">Esta semana</div>
                <div class="text-body-2 font-weight-bold">{{ weeklyActivity.reduce((a, b) => a + b, 0) }}</div>
              </div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>

      <!-- Recent activity list -->
      <v-col cols="12" md="5">
        <v-card elevation="0" border height="100%">
          <v-card-title class="pa-5 pb-3 d-flex align-center justify-space-between">
            <span class="text-body-1 font-weight-medium d-flex align-center ga-2">
              <v-icon icon="mdi-clock-outline" color="primary" size="20" />
              Actividad reciente
            </span>
            <v-btn
              variant="text"
              size="x-small"
              color="primary"
              append-icon="mdi-arrow-right"
              @click="router.push('/audit-logs')"
            >
              Ver todo
            </v-btn>
          </v-card-title>
          <v-divider />

          <v-skeleton-loader
            v-if="loading"
            type="list-item-avatar-two-line,list-item-avatar-two-line,list-item-avatar-two-line"
          />

          <v-list v-else density="compact" class="py-0">
            <template v-if="!logs.length">
              <div class="text-center py-10 text-medium-emphasis">
                <v-icon icon="mdi-history" size="36" class="d-block mb-2" />
                Sin actividad reciente
              </div>
            </template>
            <template v-for="(log, i) in logs.slice(0, 7)" :key="log.id">
              <v-list-item class="py-2 px-4">
                <template #prepend>
                  <v-avatar :color="actionColor(log.action)" variant="tonal" size="36" rounded="lg" class="me-2">
                    <v-icon :icon="actionIcon(log.action)" size="18" />
                  </v-avatar>
                </template>
                <v-list-item-title class="text-body-2">
                  <span class="font-weight-medium">{{ log.first_name }} {{ log.last_name }}</span>
                  <v-chip :color="actionColor(log.action)" size="x-small" variant="tonal" class="ms-2">
                    {{ log.action }}
                  </v-chip>
                </v-list-item-title>
                <v-list-item-subtitle class="text-caption mt-0">
                  {{ log.module }}
                  <span v-if="log.description" class="text-truncate"> · {{ log.description }}</span>
                </v-list-item-subtitle>
                <template #append>
                  <span class="text-caption text-medium-emphasis">{{ timeAgo(log.created_at) }}</span>
                </template>
              </v-list-item>
              <v-divider v-if="i < Math.min(logs.length, 7) - 1" />
            </template>
          </v-list>
        </v-card>
      </v-col>

      <!-- Mini metric cards -->
      <v-col cols="12" md="3" class="d-flex flex-column ga-4">
        <!-- Usuarios -->
        <v-card elevation="0" border class="flex-grow-1">
          <v-card-text class="pa-5">
            <div class="d-flex align-center justify-space-between mb-3">
              <span class="text-caption text-medium-emphasis font-weight-medium text-uppercase">Usuarios</span>
              <v-avatar color="error" variant="tonal" size="34" rounded="lg">
                <v-icon icon="mdi-account-group" size="18" />
              </v-avatar>
            </div>
            <div class="text-h4 font-weight-bold">
              <v-skeleton-loader v-if="loading" type="text" width="40" />
              <span v-else>{{ stats.users }}</span>
            </div>
            <div class="text-caption text-medium-emphasis">en el sistema</div>
            <v-sparkline
              :model-value="[0, 1, stats.users > 2 ? Math.floor(stats.users/2) : 1, stats.users]"
              color="error"
              :line-width="2"
              padding="4"
              smooth
              class="mt-3"
              height="40"
            />
          </v-card-text>
        </v-card>

        <!-- Sucursales -->
        <v-card elevation="0" border class="flex-grow-1">
          <v-card-text class="pa-5">
            <div class="d-flex align-center justify-space-between mb-3">
              <span class="text-caption text-medium-emphasis font-weight-medium text-uppercase">Sucursales</span>
              <v-avatar color="success" variant="tonal" size="34" rounded="lg">
                <v-icon icon="mdi-map-marker" size="18" />
              </v-avatar>
            </div>
            <div class="text-h4 font-weight-bold">
              <v-skeleton-loader v-if="loading" type="text" width="40" />
              <span v-else>{{ stats.branches }}</span>
            </div>
            <div class="text-caption text-medium-emphasis">activas</div>
            <v-sparkline
              :model-value="[0, 1, stats.branches > 2 ? Math.floor(stats.branches/2) : 1, stats.branches || 1]"
              color="success"
              :line-width="2"
              padding="4"
              smooth
              class="mt-3"
              height="40"
            />
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>
  </div>
</template>
