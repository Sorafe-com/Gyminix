<script setup>
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/api/axios'

const authStore = useAuthStore()
const stats     = ref({ gyms: 0, branches: 0, users: 0, roles: 0 })
const logs      = ref([])
const loading   = ref(true)

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

const cards = [
  { key: 'gyms',     label: 'Gimnasios',  icon: 'fa fa-building',  color: '#1a73e8', bg: '#e8f0fe' },
  { key: 'branches', label: 'Sucursales', icon: 'fa fa-map-marker', color: '#34a853', bg: '#e6f4ea' },
  { key: 'users',    label: 'Usuarios',   icon: 'fa fa-users',      color: '#ea4335', bg: '#fce8e6' },
  { key: 'roles',    label: 'Roles',      icon: 'fa fa-shield',     color: '#fbbc04', bg: '#fef7e0' },
]

function timeAgo(dateStr) {
  const diff = Math.floor((Date.now() - new Date(dateStr)) / 1000)
  if (diff < 60)   return 'hace un momento'
  if (diff < 3600) return `hace ${Math.floor(diff/60)} min`
  if (diff < 86400)return `hace ${Math.floor(diff/3600)} h`
  return `hace ${Math.floor(diff/86400)} días`
}
</script>

<template>
  <div>
    <div class="page-header">
      <h2><i class="fa fa-home me-2 text-primary"></i> Dashboard</h2>
      <span class="text-muted-sm">Bienvenido, {{ authStore.user?.first_name }}</span>
    </div>

    <!-- Stat cards -->
    <div class="row g-3 mb-4">
      <div v-for="card in cards" :key="card.key" class="col-sm-6 col-xl-3">
        <div class="card-stat">
          <div class="card-stat-icon" :style="`background:${card.bg};color:${card.color}`">
            <i :class="card.icon"></i>
          </div>
          <div>
            <h3>{{ loading ? '—' : stats[card.key] }}</h3>
            <p>{{ card.label }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent activity -->
    <div class="data-table">
      <div class="p-3 border-bottom d-flex align-items-center">
        <i class="fa fa-history me-2 text-primary"></i>
        <strong>Actividad reciente</strong>
      </div>
      <table>
        <thead>
          <tr>
            <th>Usuario</th>
            <th>Módulo</th>
            <th>Acción</th>
            <th>Descripción</th>
            <th>Hace</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="5" class="text-center py-4"><span class="spinner-sm" style="border-top-color:#1a73e8"></span></td></tr>
          <tr v-else-if="!logs.length"><td colspan="5" class="text-center py-4 text-muted-sm">Sin actividad reciente</td></tr>
          <tr v-for="log in logs" :key="log.id" v-else>
            <td>{{ log.first_name }} {{ log.last_name }}</td>
            <td><span class="badge bg-light text-dark">{{ log.module }}</span></td>
            <td><span class="badge bg-primary">{{ log.action }}</span></td>
            <td>{{ log.description }}</td>
            <td class="text-muted-sm">{{ timeAgo(log.created_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
