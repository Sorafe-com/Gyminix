<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { usersApi } from '@/api/users'
import { rolesApi } from '@/api/roles'

const toast     = useToast()
const users     = ref([])
const roles     = ref([])
const meta      = ref({})
const filters   = ref({ search: '', status: '', role_id: '', page: 1, per_page: 15 })
const loading   = ref(false)
const showModal = ref(false)
const editMode  = ref(false)
const form      = ref({})
const saving    = ref(false)

async function load() {
  loading.value = true
  try {
    const [u, r] = await Promise.all([usersApi.list(filters.value), rolesApi.list()])
    users.value = u.data.data; meta.value = u.data.meta
    roles.value = r.data.data
  } finally { loading.value = false }
}

function openCreate() {
  form.value = { first_name: '', last_name: '', email: '', password: '', phone: '', role_id: '', status: 1 }
  editMode.value = false; showModal.value = true
}

function openEdit(user) {
  form.value = { ...user, password: '' }
  editMode.value = true; showModal.value = true
}

async function save() {
  saving.value = true
  try {
    if (editMode.value) {
      await usersApi.update(form.value.id, form.value)
      toast.success('Usuario actualizado')
    } else {
      await usersApi.create(form.value)
      toast.success('Usuario creado')
    }
    showModal.value = false; load()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Error al guardar')
  } finally { saving.value = false }
}

async function toggleStatus(user) {
  await usersApi.toggleStatus(user.id)
  toast.success('Estado actualizado'); load()
}

async function deleteUser(user) {
  if (!confirm(`¿Eliminar usuario "${user.first_name} ${user.last_name}"?`)) return
  await usersApi.delete(user.id)
  toast.success('Usuario eliminado'); load()
}

onMounted(load)
</script>

<template>
  <div>
    <div class="page-header">
      <h2><i class="fa fa-users me-2 text-primary"></i> Usuarios</h2>
      <button class="btn btn-primary btn-sm" @click="openCreate"><i class="fa fa-plus me-1"></i> Nuevo Usuario</button>
    </div>

    <div class="filter-bar">
      <input v-model="filters.search" @input="filters.page=1;load()" class="form-control form-control-sm" placeholder="Buscar..." />
      <select v-model="filters.role_id" @change="filters.page=1;load()" class="form-select form-select-sm">
        <option value="">Todos los roles</option>
        <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
      </select>
      <select v-model="filters.status" @change="filters.page=1;load()" class="form-select form-select-sm">
        <option value="">Todos</option><option value="1">Activo</option><option value="0">Inactivo</option>
      </select>
    </div>

    <div class="data-table">
      <table>
        <thead><tr><th>Nombre</th><th>Email</th><th>Rol</th><th>Último acceso</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
          <tr v-if="loading"><td colspan="6" class="text-center py-4"><span class="spinner-sm" style="border-top-color:#1a73e8"></span></td></tr>
          <tr v-else-if="!users.length"><td colspan="6" class="text-center py-4 text-muted-sm">Sin usuarios</td></tr>
          <tr v-for="u in users" :key="u.id" v-else>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="avatar-circle" style="width:32px;height:32px;font-size:.75rem">{{ (u.first_name[0]||'') + (u.last_name[0]||'') }}</div>
                <div><strong>{{ u.first_name }} {{ u.last_name }}</strong></div>
              </div>
            </td>
            <td>{{ u.email }}</td>
            <td><span class="badge bg-light text-dark">{{ u.role_name || '—' }}</span></td>
            <td class="text-muted-sm">{{ u.last_login ? new Date(u.last_login).toLocaleDateString() : '—' }}</td>
            <td><span :class="u.status ? 'badge-active':'badge-inactive'">{{ u.status ? 'Activo':'Inactivo' }}</span></td>
            <td>
              <button class="icon-btn" @click="openEdit(u)"><i class="fa fa-edit"></i></button>
              <button class="icon-btn" @click="toggleStatus(u)"><i :class="u.status?'fa fa-toggle-on text-success':'fa fa-toggle-off text-muted'"></i></button>
              <button class="icon-btn text-danger" @click="deleteUser(u)"><i class="fa fa-trash"></i></button>
            </td>
          </tr>
        </tbody>
      </table>
      <div class="pagination-bar">
        <span>Total: {{ meta.total || 0 }} usuarios</span>
        <div class="d-flex gap-2">
          <button class="btn btn-outline-secondary btn-sm" :disabled="filters.page<=1" @click="filters.page--;load()">‹</button>
          <span>{{ filters.page }} / {{ meta.pages || 1 }}</span>
          <button class="btn btn-outline-secondary btn-sm" :disabled="filters.page>=meta.pages" @click="filters.page++;load()">›</button>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal=false">
      <div class="modal-box">
        <div class="modal-header">
          <span>{{ editMode ? 'Editar Usuario' : 'Nuevo Usuario' }}</span>
          <button class="icon-btn" @click="showModal=false"><i class="fa fa-times"></i></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-6"><label class="form-label">Nombre *</label><input v-model="form.first_name" class="form-control" /></div>
            <div class="col-6"><label class="form-label">Apellido *</label><input v-model="form.last_name" class="form-control" /></div>
            <div class="col-6"><label class="form-label">Email *</label><input v-model="form.email" type="email" class="form-control" /></div>
            <div class="col-6"><label class="form-label">Teléfono</label><input v-model="form.phone" class="form-control" /></div>
            <div class="col-6">
              <label class="form-label">{{ editMode ? 'Nueva Contraseña' : 'Contraseña *' }}</label>
              <input v-model="form.password" type="password" class="form-control" :placeholder="editMode ? 'Dejar en blanco para no cambiar' : ''" />
            </div>
            <div class="col-6">
              <label class="form-label">Rol *</label>
              <select v-model="form.role_id" class="form-select">
                <option value="">Seleccionar rol...</option>
                <option v-for="r in roles" :key="r.id" :value="r.id">{{ r.name }}</option>
              </select>
            </div>
            <div class="col-6">
              <label class="form-label">Estado</label>
              <select v-model="form.status" class="form-select"><option :value="1">Activo</option><option :value="0">Inactivo</option></select>
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
