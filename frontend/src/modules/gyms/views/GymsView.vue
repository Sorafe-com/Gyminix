<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { gymsApi } from '@/api/gyms'

const toast = useToast()
const gyms  = ref([])
const meta  = ref({})
const filters = ref({ search: '', status: '', page: 1, per_page: 15 })
const loading = ref(false)
const showModal = ref(false)
const editMode  = ref(false)
const form = ref({})
const saving = ref(false)

async function load() {
  loading.value = true
  try {
    const { data } = await gymsApi.list(filters.value)
    gyms.value = data.data
    meta.value = data.meta
  } finally { loading.value = false }
}

function openCreate() {
  form.value = { name: '', legal_name: '', tax_id: '', address: '', phone: '', email: '', website: '', currency: 'PEN', currency_symbol: 'S/', timezone: 'America/Lima', language: 'es', status: 1 }
  editMode.value = false
  showModal.value = true
}

function openEdit(gym) {
  form.value = { ...gym }
  editMode.value = true
  showModal.value = true
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
    showModal.value = false
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
    <div class="page-header">
      <h2><i class="fa fa-building me-2 text-primary"></i> Gimnasios</h2>
      <button class="btn btn-primary btn-sm" @click="openCreate">
        <i class="fa fa-plus me-1"></i> Nuevo Gimnasio
      </button>
    </div>

    <div class="filter-bar">
      <input v-model="filters.search" @input="filters.page=1;load()" class="form-control form-control-sm" placeholder="Buscar..." />
      <select v-model="filters.status" @change="filters.page=1;load()" class="form-select form-select-sm">
        <option value="">Todos los estados</option>
        <option value="1">Activo</option>
        <option value="0">Inactivo</option>
      </select>
    </div>

    <div class="data-table">
      <table>
        <thead>
          <tr>
            <th>#</th><th>Nombre</th><th>Email</th><th>Moneda</th><th>Estado</th><th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading"><td colspan="6" class="text-center py-4"><span class="spinner-sm" style="border-top-color:#1a73e8"></span></td></tr>
          <tr v-else-if="!gyms.length"><td colspan="6" class="text-center py-4 text-muted-sm">Sin resultados</td></tr>
          <tr v-for="gym in gyms" :key="gym.id" v-else>
            <td>{{ gym.id }}</td>
            <td><strong>{{ gym.name }}</strong><br><small class="text-muted-sm">{{ gym.legal_name }}</small></td>
            <td>{{ gym.email }}</td>
            <td>{{ gym.currency_symbol }} {{ gym.currency }}</td>
            <td><span :class="gym.status ? 'badge-active' : 'badge-inactive'">{{ gym.status ? 'Activo' : 'Inactivo' }}</span></td>
            <td>
              <button class="icon-btn" title="Editar" @click="openEdit(gym)"><i class="fa fa-edit"></i></button>
              <button class="icon-btn" :title="gym.status ? 'Desactivar' : 'Activar'" @click="toggleStatus(gym)">
                <i :class="gym.status ? 'fa fa-toggle-on text-success' : 'fa fa-toggle-off text-muted'"></i>
              </button>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="pagination-bar">
        <span>Total: {{ meta.total || 0 }} gimnasios</span>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-secondary btn-sm" :disabled="filters.page<=1" @click="filters.page--;load()">‹ Anterior</button>
          <span>Página {{ filters.page }} / {{ meta.pages || 1 }}</span>
          <button class="btn btn-outline-secondary btn-sm" :disabled="filters.page>=meta.pages" @click="filters.page++;load()">Siguiente ›</button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal=false">
      <div class="modal-box">
        <div class="modal-header">
          <span>{{ editMode ? 'Editar Gimnasio' : 'Nuevo Gimnasio' }}</span>
          <button class="icon-btn" @click="showModal=false"><i class="fa fa-times"></i></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-6"><label class="form-label">Nombre comercial *</label><input v-model="form.name" class="form-control" /></div>
            <div class="col-6"><label class="form-label">Razón social</label><input v-model="form.legal_name" class="form-control" /></div>
            <div class="col-6"><label class="form-label">RUC / NIT</label><input v-model="form.tax_id" class="form-control" /></div>
            <div class="col-6"><label class="form-label">Teléfono</label><input v-model="form.phone" class="form-control" /></div>
            <div class="col-12"><label class="form-label">Dirección</label><input v-model="form.address" class="form-control" /></div>
            <div class="col-6"><label class="form-label">Email</label><input v-model="form.email" type="email" class="form-control" /></div>
            <div class="col-6"><label class="form-label">Sitio web</label><input v-model="form.website" class="form-control" /></div>
            <div class="col-4"><label class="form-label">Moneda</label><input v-model="form.currency" class="form-control" /></div>
            <div class="col-4"><label class="form-label">Símbolo</label><input v-model="form.currency_symbol" class="form-control" /></div>
            <div class="col-4">
              <label class="form-label">Estado</label>
              <select v-model="form.status" class="form-select">
                <option :value="1">Activo</option><option :value="0">Inactivo</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-light" @click="showModal=false">Cancelar</button>
          <button class="btn btn-primary" :disabled="saving" @click="save">
            <span v-if="saving" class="spinner-sm me-1"></span>
            {{ saving ? 'Guardando...' : 'Guardar' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
