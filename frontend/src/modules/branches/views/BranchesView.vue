<script setup>
import { ref, watch, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { branchesApi } from '@/api/branches'
import { useDebounce } from '@/composables/useDebounce'

const toast    = useToast()
const branches = ref([])
const meta     = ref({})
const loading  = ref(false)
const dialog   = ref(false)
const editMode = ref(false)
const saving   = ref(false)
const form     = ref({})

const searchQuery     = ref('')
const statusFilter    = ref(null)
const page            = ref(1)
const perPage         = ref(15)
const debouncedSearch = useDebounce(searchQuery, 500)

const scheduleKeys = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo']
const dayLabels    = {
  lunes: 'Lunes', martes: 'Martes', miercoles: 'Miércoles',
  jueves: 'Jueves', viernes: 'Viernes', sabado: 'Sábado', domingo: 'Domingo',
}

const defaultSchedule = () =>
  Object.fromEntries(scheduleKeys.map(d => [d, { open: '06:00', close: '22:00', active: true }]))

const headers = [
  { title: 'Nombre',    key: 'name' },
  { title: 'Dirección', key: 'address', sortable: false },
  { title: 'Teléfono',  key: 'phone',   sortable: false, width: '130px' },
  { title: 'Estado',    key: 'status',  align: 'center', width: '110px' },
  { title: 'Acciones',  key: 'actions', sortable: false, align: 'center', width: '120px' },
]

watch([debouncedSearch, statusFilter], () => { page.value = 1; load() })

async function load() {
  loading.value = true
  try {
    const { data } = await branchesApi.list({
      search:   debouncedSearch.value || undefined,
      status:   statusFilter.value !== null ? statusFilter.value : undefined,
      page:     page.value,
      per_page: perPage.value,
    })
    branches.value = data.data ?? []
    meta.value     = data.meta ?? {}
  } catch (e) {
    toast.error(e.response?.data?.message || e.message || 'Error al cargar sucursales')
  } finally { loading.value = false }
}

function openCreate() {
  form.value = { name: '', address: '', phone: '', email: '', description: '', status: 1, schedule: defaultSchedule() }
  editMode.value = false; dialog.value = true
}

function openEdit(branch) {
  form.value = { ...branch, schedule: { ...defaultSchedule(), ...(branch.schedule || {}) } }
  editMode.value = true; dialog.value = true
}

async function save() {
  saving.value = true
  try {
    if (editMode.value) {
      await branchesApi.update(form.value.id, form.value)
      toast.success('Sucursal actualizada')
    } else {
      await branchesApi.create(form.value)
      toast.success('Sucursal creada')
    }
    dialog.value = false; load()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Error al guardar')
  } finally { saving.value = false }
}

async function toggleStatus(branch) {
  await branchesApi.toggleStatus(branch.id)
  toast.success('Estado actualizado'); load()
}

async function deleteBranch(branch) {
  if (!confirm(`¿Eliminar sucursal "${branch.name}"?`)) return
  await branchesApi.delete(branch.id)
  toast.success('Sucursal eliminada'); load()
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex align-center mb-6">
      <div>
        <h2 class="text-h5 font-weight-bold">Sucursales</h2>
        <p class="text-body-2 text-medium-emphasis mb-0">Gestión de sucursales del gimnasio</p>
      </div>
      <v-spacer />
      <v-btn prepend-icon="mdi-plus" @click="openCreate">Nueva Sucursal</v-btn>
    </div>

    <v-card class="mb-4 pa-4" elevation="0" border>
      <v-row dense>
        <v-col cols="12" sm="5" md="4">
          <v-text-field v-model="searchQuery" prepend-inner-icon="mdi-magnify" placeholder="Buscar sucursal..." clearable />
        </v-col>
        <v-col cols="12" sm="4" md="3">
          <v-select
            v-model="statusFilter"
            :items="[{ title: 'Todos', value: null }, { title: 'Activo', value: 1 }, { title: 'Inactivo', value: 0 }]"
            item-title="title"
            item-value="value"
            placeholder="Estado"
          />
        </v-col>
      </v-row>
    </v-card>

    <v-card elevation="0" border>
      <v-data-table :headers="headers" :items="branches" :loading="loading" :items-per-page="-1" hover>
        <template #item.name="{ item }">
          <div class="py-1">
            <div class="font-weight-medium">{{ item.name }}</div>
            <div v-if="item.email" class="text-caption text-medium-emphasis">{{ item.email }}</div>
          </div>
        </template>
        <template #item.status="{ item }">
          <v-chip :color="item.status ? 'success' : 'error'" variant="tonal" size="small">
            {{ item.status ? 'Activo' : 'Inactivo' }}
          </v-chip>
        </template>
        <template #item.actions="{ item }">
          <v-btn icon="mdi-pencil-outline" variant="text" size="small" density="compact" @click="openEdit(item)" />
          <v-btn
            :icon="item.status ? 'mdi-toggle-switch' : 'mdi-toggle-switch-off-outline'"
            :color="item.status ? 'success' : undefined"
            variant="text"
            size="small"
            density="compact"
            @click="toggleStatus(item)"
          />
          <v-btn icon="mdi-delete-outline" color="error" variant="text" size="small" density="compact" @click="deleteBranch(item)" />
        </template>
        <template #bottom>
          <v-divider />
          <div class="d-flex align-center justify-space-between pa-3">
            <span class="text-caption text-medium-emphasis">Total: {{ meta.total || 0 }} sucursales</span>
            <v-pagination v-model="page" :length="meta.pages || 1" density="compact" @update:model-value="load" />
          </div>
        </template>
        <template #no-data>
          <div class="text-center py-8 text-medium-emphasis">Sin sucursales</div>
        </template>
      </v-data-table>
    </v-card>

    <!-- Dialog -->
    <v-dialog v-model="dialog" max-width="680" scrollable>
      <v-card>
        <v-card-title class="pa-5 pb-3">{{ editMode ? 'Editar Sucursal' : 'Nueva Sucursal' }}</v-card-title>
        <v-divider />
        <v-card-text class="pa-5" style="max-height: 72vh;">
          <v-row dense>
            <v-col cols="6"><v-text-field v-model="form.name" label="Nombre *" /></v-col>
            <v-col cols="6"><v-text-field v-model="form.phone" label="Teléfono" /></v-col>
            <v-col cols="12"><v-text-field v-model="form.address" label="Dirección" /></v-col>
            <v-col cols="6"><v-text-field v-model="form.email" label="Email" type="email" /></v-col>
            <v-col cols="6">
              <v-select
                v-model="form.status"
                :items="[{ title: 'Activo', value: 1 }, { title: 'Inactivo', value: 0 }]"
                item-title="title"
                item-value="value"
                label="Estado"
              />
            </v-col>
            <v-col cols="12"><v-text-field v-model="form.description" label="Descripción" /></v-col>
          </v-row>

          <div class="text-subtitle-2 font-weight-medium mt-4 mb-2">Horario de atención</div>
          <v-table density="compact" class="rounded border">
            <thead>
              <tr>
                <th style="width:110px">Día</th>
                <th class="text-center" style="width:80px">Abierto</th>
                <th style="width:140px">Apertura</th>
                <th style="width:140px">Cierre</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="day in scheduleKeys" :key="day">
                <td class="text-body-2">{{ dayLabels[day] }}</td>
                <td class="text-center">
                  <v-checkbox v-model="form.schedule[day].active" density="compact" hide-details class="d-inline-flex" />
                </td>
                <td class="py-1 px-2">
                  <v-text-field
                    v-model="form.schedule[day].open"
                    type="time"
                    density="compact"
                    hide-details
                    :disabled="!form.schedule[day].active"
                  />
                </td>
                <td class="py-1 px-2">
                  <v-text-field
                    v-model="form.schedule[day].close"
                    type="time"
                    density="compact"
                    hide-details
                    :disabled="!form.schedule[day].active"
                  />
                </td>
              </tr>
            </tbody>
          </v-table>
        </v-card-text>
        <v-divider />
        <v-card-actions class="pa-4">
          <v-spacer />
          <v-btn variant="text" color="default" @click="dialog = false">Cancelar</v-btn>
          <v-btn :loading="saving" @click="save">{{ editMode ? 'Actualizar' : 'Crear' }}</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
