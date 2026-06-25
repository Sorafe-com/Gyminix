<script setup>
import { ref, onMounted } from 'vue'
import { auditApi } from '@/api/audit'

const logs    = ref([])
const meta    = ref({})
const loading = ref(false)
const filters = ref({ module: '', action: '', date_from: '', date_to: '', page: 1, per_page: 20 })

const modules = ['auth', 'gyms', 'branches', 'users', 'roles', 'menus', 'audit', 'gym_settings']
const actions = ['login', 'logout', 'create', 'update', 'delete', 'toggle_status', 'sync_permissions']

async function load() {
  loading.value = true
  try {
    const { data } = await auditApi.list(filters.value)
    logs.value = data.data; meta.value = data.meta
  } finally { loading.value = false }
}

function actionColor(action) {
  const map = { login:'success', logout:'secondary', create:'primary', update:'warning', delete:'danger', toggle_status:'info' }
  return `bg-${map[action] || 'light'} ${map[action] ? 'text-white' : 'text-dark'}`
}

onMounted(load)
</script>

<template>
  <div>
    <div class="page-header">
      <h2><i class="fa fa-history me-2 text-primary"></i> Registro de Auditoría</h2>
    </div>

    <div class="filter-bar mb-3">
      <select v-model="filters.module" @change="filters.page=1;load()" class="form-select form-select-sm">
        <option value="">Todos los módulos</option>
        <option v-for="m in modules" :key="m" :value="m">{{ m }}</option>
      </select>
      <select v-model="filters.action" @change="filters.page=1;load()" class="form-select form-select-sm">
        <option value="">Todas las acciones</option>
        <option v-for="a in actions" :key="a" :value="a">{{ a }}</option>
      </select>
      <input v-model="filters.date_from" @change="filters.page=1;load()" type="date" class="form-control form-control-sm" />
      <input v-model="filters.date_to"   @change="filters.page=1;load()" type="date" class="form-control form-control-sm" />
    </div>

    <div class="data-table">
      <table>
        <thead>
          <tr><th>Fecha</th><th>Usuario</th><th>Módulo</th><th>Acción</th><th>Descripción</th><th>IP</th></tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="6" class="text-center py-4"><span class="spinner-sm" style="border-top-color:#1a73e8"></span></td></tr>
          <tr v-else-if="!logs.length"><td colspan="6" class="text-center py-4 text-muted-sm">Sin registros</td></tr>
          <tr v-for="log in logs" :key="log.id" v-else>
            <td class="text-muted-sm" style="white-space:nowrap">{{ new Date(log.created_at).toLocaleString() }}</td>
            <td>{{ log.first_name }} {{ log.last_name }}</td>
            <td><span class="badge bg-light text-dark">{{ log.module }}</span></td>
            <td><span class="badge" :class="actionColor(log.action)">{{ log.action }}</span></td>
            <td>{{ log.description }}</td>
            <td class="text-muted-sm">{{ log.ip_address }}</td>
          </tr>
        </tbody>
      </table>
      <div class="pagination-bar">
        <span>Total: {{ meta.total || 0 }} registros</span>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-secondary btn-sm" :disabled="filters.page<=1" @click="filters.page--;load()">‹</button>
          <span>{{ filters.page }} / {{ meta.pages || 1 }}</span>
          <button class="btn btn-outline-secondary btn-sm" :disabled="filters.page>=meta.pages" @click="filters.page++;load()">›</button>
        </div>
      </div>
    </div>
  </div>
</template>
