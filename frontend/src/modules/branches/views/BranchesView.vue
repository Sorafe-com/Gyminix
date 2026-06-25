<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { branchesApi } from '@/api/branches'

const toast = useToast()
const branches  = ref([])
const meta      = ref({})
const filters   = ref({ search: '', status: '', page: 1, per_page: 15 })
const loading   = ref(false)
const showModal = ref(false)
const editMode  = ref(false)
const form      = ref({})
const saving    = ref(false)

const defaultSchedule = {
  lunes: { open: '06:00', close: '22:00', active: true },
  martes: { open: '06:00', close: '22:00', active: true },
  miercoles: { open: '06:00', close: '22:00', active: true },
  jueves: { open: '06:00', close: '22:00', active: true },
  viernes: { open: '06:00', close: '22:00', active: true },
  sabado: { open: '08:00', close: '20:00', active: true },
  domingo: { open: '08:00', close: '14:00', active: false },
}

async function load() {
  loading.value = true
  try {
    const { data } = await branchesApi.list(filters.value)
    branches.value = data.data
    meta.value     = data.meta
  } finally { loading.value = false }
}

function openCreate() {
  form.value = { name: '', address: '', phone: '', email: '', schedule: { ...defaultSchedule }, status: 1 }
  editMode.value = false
  showModal.value = true
}

function openEdit(branch) {
  form.value = { ...branch, schedule: branch.schedule ? (typeof branch.schedule === 'string' ? JSON.parse(branch.schedule) : branch.schedule) : { ...defaultSchedule } }
  editMode.value = true
  showModal.value = true
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
    showModal.value = false
    load()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Error al guardar')
  } finally { saving.value = false }
}

async function toggleStatus(branch) {
  await branchesApi.toggleStatus(branch.id)
  toast.success('Estado actualizado')
  load()
}

async function deleteBranch(branch) {
  if (!confirm(`¿Eliminar sucursal "${branch.name}"?`)) return
  await branchesApi.delete(branch.id)
  toast.success('Sucursal eliminada')
  load()
}

onMounted(load)
</script>

<template>
  <div>
    <div class="page-header">
      <h2><i class="fa fa-map-marker me-2 text-primary"></i> Sucursales</h2>
      <button class="btn btn-primary btn-sm" @click="openCreate"><i class="fa fa-plus me-1"></i> Nueva Sucursal</button>
    </div>

    <div class="filter-bar">
      <input v-model="filters.search" @input="filters.page=1;load()" class="form-control form-control-sm" placeholder="Buscar..." />
      <select v-model="filters.status" @change="filters.page=1;load()" class="form-select form-select-sm">
        <option value="">Todos</option><option value="1">Activo</option><option value="0">Inactivo</option>
      </select>
    </div>

    <div class="data-table">
      <table>
        <thead><tr><th>#</th><th>Nombre</th><th>Dirección</th><th>Teléfono</th><th>Email</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
          <tr v-if="loading"><td colspan="7" class="text-center py-4"><span class="spinner-sm" style="border-top-color:#1a73e8"></span></td></tr>
          <tr v-else-if="!branches.length"><td colspan="7" class="text-center py-4 text-muted-sm">Sin sucursales</td></tr>
          <tr v-for="b in branches" :key="b.id" v-else>
            <td>{{ b.id }}</td>
            <td><strong>{{ b.name }}</strong></td>
            <td>{{ b.address }}</td>
            <td>{{ b.phone }}</td>
            <td>{{ b.email }}</td>
            <td><span :class="b.status ? 'badge-active':'badge-inactive'">{{ b.status ? 'Activo':'Inactivo' }}</span></td>
            <td>
              <button class="icon-btn" @click="openEdit(b)"><i class="fa fa-edit"></i></button>
              <button class="icon-btn" @click="toggleStatus(b)"><i :class="b.status?'fa fa-toggle-on text-success':'fa fa-toggle-off text-muted'"></i></button>
              <button class="icon-btn text-danger" @click="deleteBranch(b)"><i class="fa fa-trash"></i></button>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="pagination-bar">
        <span>Total: {{ meta.total || 0 }}</span>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-secondary btn-sm" :disabled="filters.page<=1" @click="filters.page--;load()">‹</button>
          <span>{{ filters.page }} / {{ meta.pages || 1 }}</span>
          <button class="btn btn-outline-secondary btn-sm" :disabled="filters.page>=meta.pages" @click="filters.page++;load()">›</button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal=false">
      <div class="modal-box" style="width:600px">
        <div class="modal-header">
          <span>{{ editMode ? 'Editar Sucursal' : 'Nueva Sucursal' }}</span>
          <button class="icon-btn" @click="showModal=false"><i class="fa fa-times"></i></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-12"><label class="form-label">Nombre *</label><input v-model="form.name" class="form-control" /></div>
            <div class="col-12"><label class="form-label">Dirección</label><input v-model="form.address" class="form-control" /></div>
            <div class="col-6"><label class="form-label">Teléfono</label><input v-model="form.phone" class="form-control" /></div>
            <div class="col-6"><label class="form-label">Email</label><input v-model="form.email" type="email" class="form-control" /></div>
            <div class="col-6">
              <label class="form-label">Estado</label>
              <select v-model="form.status" class="form-select"><option :value="1">Activo</option><option :value="0">Inactivo</option></select>
            </div>
            <div class="col-12">
              <label class="form-label">Horarios</label>
              <table class="table table-sm table-bordered" v-if="form.schedule">
                <thead><tr><th>Día</th><th>Apertura</th><th>Cierre</th><th>Activo</th></tr></thead>
                <tbody>
                  <tr v-for="(sch, day) in form.schedule" :key="day">
                    <td class="text-capitalize">{{ day }}</td>
                    <td><input v-model="sch.open" type="time" class="form-control form-control-sm" /></td>
                    <td><input v-model="sch.close" type="time" class="form-control form-control-sm" /></td>
                    <td class="text-center"><input type="checkbox" v-model="sch.active" /></td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-light" @click="showModal=false">Cancelar</button>
          <button class="btn btn-primary" :disabled="saving" @click="save">
            <span v-if="saving" class="spinner-sm me-1"></span> {{ saving ? 'Guardando...' : 'Guardar' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
