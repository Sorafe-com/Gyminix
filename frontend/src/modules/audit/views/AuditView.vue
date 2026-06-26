<script setup>
import { ref, onMounted } from 'vue'
import { auditApi } from '@/api/audit'

const logs    = ref([])
const meta    = ref({})
const loading = ref(false)
const page    = ref(1)
const perPage = ref(20)

const filters = ref({
  search: '',
  module: '',
  action: '',
  date_from: '',
  date_to: '',
})

const actionOptions = [
  { title: 'Todas las acciones', value: '' },
  { title: 'Login',              value: 'login' },
  { title: 'Logout',             value: 'logout' },
  { title: 'Crear',              value: 'create' },
  { title: 'Actualizar',         value: 'update' },
  { title: 'Eliminar',           value: 'delete' },
  { title: 'Cambiar estado',     value: 'toggle_status' },
  { title: 'Sync permisos',      value: 'sync_permissions' },
]

const headers = [
  { title: 'Usuario',  key: 'user',        sortable: false },
  { title: 'Módulo',   key: 'module',      width: '120px' },
  { title: 'Acción',   key: 'action',      width: '140px' },
  { title: 'Detalle',  key: 'description', sortable: false },
  { title: 'IP',       key: 'ip_address',  sortable: false, width: '130px' },
  { title: 'Fecha',    key: 'created_at',  width: '150px' },
]

const actionColor = (action) => ({
  login: 'success', logout: 'secondary', create: 'primary',
  update: 'warning', delete: 'error', toggle_status: 'info',
  sync_permissions: 'purple',
}[action] || 'default')

function formatDate(dt) {
  if (!dt) return '—'
  return new Date(dt).toLocaleString('es-PE', { dateStyle: 'short', timeStyle: 'short' })
}

async function load() {
  loading.value = true
  try {
    const { data } = await auditApi.list({
      ...filters.value,
      page:     page.value,
      per_page: perPage.value,
    })
    logs.value = data.data ?? []
    meta.value = data.meta ?? {}
  } catch (e) {
    toast.error(e.response?.data?.message || e.message || 'Error al cargar registros')
  } finally { loading.value = false }
}

function applyFilters() {
  page.value = 1; load()
}

function clearFilters() {
  filters.value = { search: '', module: '', action: '', date_from: '', date_to: '' }
  page.value = 1; load()
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex align-center mb-6">
      <div>
        <h2 class="text-h5 font-weight-bold">Auditoría</h2>
        <p class="text-body-2 text-medium-emphasis mb-0">Registro de acciones del sistema</p>
      </div>
      <v-spacer />
      <v-chip variant="tonal" color="primary">Total: {{ meta.total || 0 }}</v-chip>
    </div>

    <!-- Filters -->
    <v-card class="mb-4 pa-4" elevation="0" border>
      <v-row dense>
        <v-col cols="12" sm="4" md="3">
          <v-text-field
            v-model="filters.search"
            prepend-inner-icon="mdi-magnify"
            placeholder="Buscar..."
            clearable
            @keyup.enter="applyFilters"
          />
        </v-col>
        <v-col cols="6" sm="3" md="2">
          <v-text-field v-model="filters.module" label="Módulo" clearable @keyup.enter="applyFilters" />
        </v-col>
        <v-col cols="6" sm="3" md="2">
          <v-select
            v-model="filters.action"
            :items="actionOptions"
            item-title="title"
            item-value="value"
            label="Acción"
          />
        </v-col>
        <v-col cols="6" sm="3" md="2">
          <v-text-field v-model="filters.date_from" label="Desde" type="date" />
        </v-col>
        <v-col cols="6" sm="3" md="2">
          <v-text-field v-model="filters.date_to" label="Hasta" type="date" />
        </v-col>
        <v-col cols="12" class="d-flex ga-2 justify-end">
          <v-btn variant="text" color="default" size="small" @click="clearFilters">Limpiar</v-btn>
          <v-btn size="small" prepend-icon="mdi-filter" @click="applyFilters">Filtrar</v-btn>
        </v-col>
      </v-row>
    </v-card>

    <!-- Table -->
    <v-card elevation="0" border>
      <v-data-table
        :headers="headers"
        :items="logs"
        :loading="loading"
        :items-per-page="-1"
        hover
      >
        <template #item.user="{ item }">
          <div class="py-1">
            <div class="font-weight-medium text-body-2">{{ item.first_name }} {{ item.last_name }}</div>
            <div v-if="item.email" class="text-caption text-medium-emphasis">{{ item.email }}</div>
          </div>
        </template>
        <template #item.module="{ item }">
          <v-chip size="x-small" variant="tonal">{{ item.module }}</v-chip>
        </template>
        <template #item.action="{ item }">
          <v-chip :color="actionColor(item.action)" size="x-small" variant="tonal">{{ item.action }}</v-chip>
        </template>
        <template #item.ip_address="{ item }">
          <span class="text-caption text-medium-emphasis">{{ item.ip_address || '—' }}</span>
        </template>
        <template #item.created_at="{ item }">
          <span class="text-caption text-medium-emphasis">{{ formatDate(item.created_at) }}</span>
        </template>
        <template #bottom>
          <v-divider />
          <div class="d-flex align-center justify-space-between pa-3">
            <span class="text-caption text-medium-emphasis">Total: {{ meta.total || 0 }} registros</span>
            <v-pagination v-model="page" :length="meta.pages || 1" density="compact" @update:model-value="load" />
          </div>
        </template>
        <template #no-data>
          <div class="text-center py-8 text-medium-emphasis">
            <v-icon icon="mdi-history" size="40" class="mb-2 d-block" />
            Sin registros de auditoría
          </div>
        </template>
      </v-data-table>
    </v-card>
  </div>
</template>
