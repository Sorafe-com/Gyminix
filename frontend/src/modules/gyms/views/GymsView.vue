<script setup>
import { ref, watch, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { gymsApi } from '@/api/gyms'
import { useDebounce } from '@/composables/useDebounce'

const toast = useToast()
const gyms    = ref([])
const meta    = ref({})
const loading = ref(false)
const dialog  = ref(false)
const editMode = ref(false)
const saving   = ref(false)
const form     = ref({})

const searchQuery     = ref('')
const statusFilter    = ref(null)
const page            = ref(1)
const perPage         = ref(15)
const debouncedSearch = useDebounce(searchQuery, 500)

const headers = [
  { title: '#',       key: 'id',       width: '60px' },
  { title: 'Nombre',  key: 'name'  },
  { title: 'Email',   key: 'email' },
  { title: 'Moneda',  key: 'currency', width: '100px' },
  { title: 'Estado',  key: 'status',   align: 'center', width: '110px' },
  { title: 'Acciones', key: 'actions', sortable: false, align: 'center', width: '100px' },
]

watch([debouncedSearch, statusFilter], () => { page.value = 1; load() })

async function load() {
  loading.value = true
  try {
    const { data } = await gymsApi.list({
      search:   debouncedSearch.value,
      status:   statusFilter.value ?? '',
      page:     page.value,
      per_page: perPage.value,
    })
    gyms.value = data.data ?? []
    meta.value = data.meta ?? {}
  } catch (e) {
    toast.error(e.response?.data?.message || e.message || 'Error al cargar gimnasios')
  } finally { loading.value = false }
}

function openCreate() {
  form.value = { name: '', legal_name: '', tax_id: '', address: '', phone: '', email: '', website: '', currency: 'PEN', currency_symbol: 'S/', timezone: 'America/Lima', language: 'es', status: 1 }
  editMode.value = false
  dialog.value   = true
}

function openEdit(gym) {
  form.value = { ...gym }
  editMode.value = true
  dialog.value   = true
}

async function save() {
  saving.value = true
  try {
    if (editMode.value) {
      await gymsApi.update(form.value.id, form.value)
      toast.success('Gimnasio actualizado')
    } else {
      await gymsApi.create(form.value)
      toast.success('Gimnasio creado')
    }
    dialog.value = false
    load()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Error al guardar')
  } finally { saving.value = false }
}

async function toggleStatus(gym) {
  await gymsApi.toggleStatus(gym.id)
  toast.success('Estado actualizado')
  load()
}

onMounted(load)
</script>

<template>
  <div>
    <!-- Header -->
    <div class="d-flex align-center mb-6">
      <div>
        <h2 class="text-h5 font-weight-bold">Gimnasios</h2>
        <p class="text-body-2 text-medium-emphasis mb-0">Gestión de gimnasios registrados</p>
      </div>
      <v-spacer />
      <v-btn prepend-icon="mdi-plus" @click="openCreate">Nuevo Gimnasio</v-btn>
    </div>

    <!-- Filters -->
    <v-card class="mb-4 pa-4" elevation="0" border>
      <v-row dense>
        <v-col cols="12" sm="5" md="4">
          <v-text-field
            v-model="searchQuery"
            prepend-inner-icon="mdi-magnify"
            placeholder="Buscar gimnasio..."
            clearable
          />
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

    <!-- Table -->
    <v-card elevation="0" border>
      <v-data-table
        :headers="headers"
        :items="gyms"
        :loading="loading"
        :items-per-page="-1"
        hover
      >
        <template #item.name="{ item }">
          <div class="py-1">
            <div class="font-weight-medium">{{ item.name }}</div>
            <div class="text-caption text-medium-emphasis">{{ item.legal_name }}</div>
          </div>
        </template>
        <template #item.currency="{ item }">
          {{ item.currency_symbol }} {{ item.currency }}
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
        </template>
        <template #bottom>
          <v-divider />
          <div class="d-flex align-center justify-space-between pa-3">
            <span class="text-caption text-medium-emphasis">Total: {{ meta.total || 0 }} gimnasios</span>
            <v-pagination
              v-model="page"
              :length="meta.pages || 1"
              density="compact"
              @update:model-value="load"
            />
          </div>
        </template>
        <template #no-data>
          <div class="text-center py-8 text-medium-emphasis">Sin resultados</div>
        </template>
      </v-data-table>
    </v-card>

    <!-- Dialog -->
    <v-dialog v-model="dialog" max-width="560">
      <v-card>
        <v-card-title class="pa-5 pb-3">{{ editMode ? 'Editar Gimnasio' : 'Nuevo Gimnasio' }}</v-card-title>
        <v-divider />
        <v-card-text class="pa-5">
          <v-row dense>
            <v-col cols="6"><v-text-field v-model="form.name" label="Nombre comercial *" /></v-col>
            <v-col cols="6"><v-text-field v-model="form.legal_name" label="Razón social" /></v-col>
            <v-col cols="6"><v-text-field v-model="form.tax_id" label="RUC / NIT" /></v-col>
            <v-col cols="6"><v-text-field v-model="form.phone" label="Teléfono" /></v-col>
            <v-col cols="12"><v-text-field v-model="form.address" label="Dirección" /></v-col>
            <v-col cols="6"><v-text-field v-model="form.email" label="Email" type="email" /></v-col>
            <v-col cols="6"><v-text-field v-model="form.website" label="Sitio web" /></v-col>
            <v-col cols="4"><v-text-field v-model="form.currency" label="Moneda" /></v-col>
            <v-col cols="4"><v-text-field v-model="form.currency_symbol" label="Símbolo" /></v-col>
            <v-col cols="4">
              <v-select
                v-model="form.status"
                :items="[{ title: 'Activo', value: 1 }, { title: 'Inactivo', value: 0 }]"
                item-title="title"
                item-value="value"
                label="Estado"
              />
            </v-col>
          </v-row>
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
