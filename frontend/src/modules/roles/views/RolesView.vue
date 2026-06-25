<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { rolesApi } from '@/api/roles'

const toast     = useToast()
const roles     = ref([])
const loading   = ref(false)
const showModal = ref(false)
const showPermsModal = ref(false)
const editMode  = ref(false)
const form      = ref({})
const saving    = ref(false)

const allPerms   = ref({})
const rolePerms  = ref([])
const selectedRole = ref(null)
const savingPerms  = ref(false)

async function load() {
  loading.value = true
  try {
    const { data } = await rolesApi.list()
    roles.value = data.data
  } finally { loading.value = false }
}

function openCreate() {
  form.value = { name: '', description: '', status: 1 }
  editMode.value = false; showModal.value = true
}

function openEdit(role) {
  form.value = { ...role }
  editMode.value = true; showModal.value = true
}

async function save() {
  saving.value = true
  try {
    if (editMode.value) { await rolesApi.update(form.value.id, form.value); toast.success('Rol actualizado') }
    else                { await rolesApi.create(form.value); toast.success('Rol creado') }
    showModal.value = false; load()
  } catch (e) { toast.error(e.response?.data?.message || 'Error') }
  finally { saving.value = false }
}

async function deleteRole(role) {
  if (role.is_system) { toast.warning('No se pueden eliminar roles del sistema'); return }
  if (!confirm(`¿Eliminar rol "${role.name}"?`)) return
  await rolesApi.delete(role.id); toast.success('Rol eliminado'); load()
}

async function openPermissions(role) {
  selectedRole.value = role
  const [p, rp] = await Promise.all([rolesApi.permissions(), rolesApi.getRolePerms(role.id)])
  allPerms.value  = p.data.data
  rolePerms.value = rp.data.data
  showPermsModal.value = true
}

async function savePermissions() {
  savingPerms.value = true
  try {
    await rolesApi.syncPermissions(selectedRole.value.id, rolePerms.value)
    toast.success('Permisos actualizados')
    showPermsModal.value = false
  } finally { savingPerms.value = false }
}

onMounted(load)
</script>

<template>
  <div>
    <div class="page-header">
      <h2><i class="fa fa-shield me-2 text-primary"></i> Roles</h2>
      <button class="btn btn-primary btn-sm" @click="openCreate"><i class="fa fa-plus me-1"></i> Nuevo Rol</button>
    </div>

    <div class="data-table">
      <table>
        <thead><tr><th>#</th><th>Nombre</th><th>Slug</th><th>Descripción</th><th>Tipo</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
          <tr v-if="loading"><td colspan="7" class="text-center py-4"><span class="spinner-sm" style="border-top-color:#1a73e8"></span></td></tr>
          <tr v-else-if="!roles.length"><td colspan="7" class="text-center py-4 text-muted-sm">Sin roles</td></tr>
          <tr v-for="r in roles" :key="r.id" v-else>
            <td>{{ r.id }}</td>
            <td><strong>{{ r.name }}</strong></td>
            <td><code>{{ r.slug }}</code></td>
            <td>{{ r.description }}</td>
            <td><span class="badge" :class="r.is_system ? 'bg-warning text-dark' : 'bg-secondary'">{{ r.is_system ? 'Sistema' : 'Personalizado' }}</span></td>
            <td><span :class="r.status ? 'badge-active':'badge-inactive'">{{ r.status ? 'Activo':'Inactivo' }}</span></td>
            <td>
              <button class="icon-btn" title="Editar" @click="openEdit(r)"><i class="fa fa-edit"></i></button>
              <button class="icon-btn" title="Permisos" @click="openPermissions(r)"><i class="fa fa-key text-warning"></i></button>
              <button v-if="!r.is_system" class="icon-btn text-danger" @click="deleteRole(r)"><i class="fa fa-trash"></i></button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Role Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal=false">
      <div class="modal-box">
        <div class="modal-header">
          <span>{{ editMode ? 'Editar Rol' : 'Nuevo Rol' }}</span>
          <button class="icon-btn" @click="showModal=false"><i class="fa fa-times"></i></button>
        </div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Nombre *</label><input v-model="form.name" class="form-control" /></div>
          <div class="mb-3"><label class="form-label">Descripción</label><textarea v-model="form.description" class="form-control" rows="2"></textarea></div>
          <div class="mb-3">
            <label class="form-label">Estado</label>
            <select v-model="form.status" class="form-select"><option :value="1">Activo</option><option :value="0">Inactivo</option></select>
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

    <!-- Permissions Modal -->
    <div v-if="showPermsModal" class="modal-overlay" @click.self="showPermsModal=false">
      <div class="modal-box" style="width:640px">
        <div class="modal-header">
          <span><i class="fa fa-key me-2"></i>Permisos — {{ selectedRole?.name }}</span>
          <button class="icon-btn" @click="showPermsModal=false"><i class="fa fa-times"></i></button>
        </div>
        <div class="modal-body" style="max-height:60vh;overflow-y:auto">
          <div v-for="(perms, module) in allPerms" :key="module" class="mb-3">
            <p class="form-label text-uppercase mb-1" style="color:#1a73e8;letter-spacing:.5px">{{ module }}</p>
            <div class="row g-2">
              <div v-for="p in perms" :key="p.id" class="col-4">
                <div class="form-check">
                  <input type="checkbox" class="form-check-input" :id="`p${p.id}`" :value="p.id" v-model="rolePerms" />
                  <label :for="`p${p.id}`" class="form-check-label" style="font-size:.82rem">{{ p.action }}</label>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-light" @click="showPermsModal=false">Cancelar</button>
          <button class="btn btn-primary" :disabled="savingPerms" @click="savePermissions">
            <span v-if="savingPerms" class="spinner-sm me-1"></span> Guardar permisos
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
