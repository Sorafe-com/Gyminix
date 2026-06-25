<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'

const authStore = useAuthStore()
const stats     = ref({ gyms: 0, branches: 0, users: 0, roles: 0 })
const logs      = ref([])
const loading   = ref(true)

const cards = [
  { key: 'gyms',     label: 'Gimnasios',  icon: 'mdi-domain',      color: 'primary' },
  { key: 'branches', label: 'Sucursales', icon: 'mdi-map-marker',   color: 'success' },
  { key: 'users',    label: 'Usuarios',   icon: 'mdi-account-group', color: 'error' },
  { key: 'roles',    label: 'Roles',      icon: 'mdi-shield-check',  color: 'warning' },
]

const logHeaders = [
  { title: 'Usuario',     key: 'user',        sortable: false },
  { title: 'Módulo',      key: 'module',      sortable: false },
  { title: 'Acción',      key: 'action',      sortable: false },
  { title: 'Descripción', key: 'description', sortable: false },
  { title: 'Hace',        key: 'time',        sortable: false },
]

function timeAgo(dateStr) {
  const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000)
  if (diff < 60)    return 'hace un momento'
  if (diff < 3600)  return `hace ${Math.floor(diff / 60)} min`
  if (diff < 86400) return `hace ${Math.floor(diff / 3600)} h`
  return `hace ${Math.floor(diff / 86400)} días`
}

onMounted(async () => {
  try {
    const { data } = await api.get('/dashboard')
    if (data.success) {
      stats.value = data.data.stats
      logs.value  = data.data.recent_logs
    }
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div>
    <!-- Page header -->
    <div class="mb-6">
      <h2 class="text-h5 font-weight-bold mb-1">Dashboard</h2>
      <p class="text-body-2 text-medium-emphasis mb-0">
        Bienvenido, <strong>{{ authStore.user?.first_name }}</strong>
      </p>
    </div>

    <!-- Stat cards -->
    <v-row class="mb-6">
      <v-col v-for="card in cards" :key="card.key" cols="12" sm="6" xl="3">
        <v-card elevation="0" border>
          <v-card-text class="d-flex align-center ga-4 pa-5">
            <v-avatar :color="card.color" variant="tonal" size="52" rounded="lg">
              <v-icon :icon="card.icon" size="24" />
            </v-avatar>
            <div>
              <div class="text-h4 font-weight-bold">
                <v-skeleton-loader v-if="loading" type="text" width="40" />
                <span v-else>{{ stats[card.key] }}</span>
              </div>
              <div class="text-body-2 text-medium-emphasis">{{ card.label }}</div>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <!-- Recent activity -->
    <v-card elevation="0" border>
      <v-card-title class="pa-5 pb-3 d-flex align-center ga-2">
        <v-icon icon="mdi-history" color="primary" />
        Actividad reciente
      </v-card-title>
      <v-divider />
      <v-data-table
        :headers="logHeaders"
        :items="logs"
        :loading="loading"
        hover
        hide-default-footer
        :items-per-page="-1"
      >
        <template #item.user="{ item }">
          {{ item.first_name }} {{ item.last_name }}
        </template>
        <template #item.module="{ item }">
          <v-chip size="x-small" variant="tonal">{{ item.module }}</v-chip>
        </template>
        <template #item.action="{ item }">
          <v-chip size="x-small" color="primary" variant="tonal">{{ item.action }}</v-chip>
        </template>
        <template #item.time="{ item }">
          <span class="text-caption text-medium-emphasis">{{ timeAgo(item.created_at) }}</span>
        </template>
        <template #no-data>
          <div class="text-center py-8 text-medium-emphasis">
            <v-icon icon="mdi-history" size="40" class="mb-2 d-block" />
            Sin actividad reciente
          </div>
        </template>
      </v-data-table>
    </v-card>
  </div>
</template>
