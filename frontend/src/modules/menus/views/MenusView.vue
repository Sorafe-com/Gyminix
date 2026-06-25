<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { menusApi } from '@/api/menus'

const toast     = useToast()
const menus     = ref([])
const loading   = ref(false)
const showModal = ref(false)
const editMode  = ref(false)
const form      = ref({})
const saving    = ref(false)

async function load() {
  loading.value = true
  const { data } = await menusApi.tree()
  menus.value = data.data || []
  loading.value = false
}

const flatMenus = ref([])
async function loadFlat() {
  const { data } = await menusApi.list()
  flatMenus.value = data.data || []
}

function openCreate() {
  form.value = { name: '', slug: '', route: '', icon: 'fa fa-circle-o', permission_slug: '', parent_id: null, order: 0, status: 1 }
  editMode.value = false; showModal.value = true
}

function openEdit(menu) {
  form.value = { ...menu }
  editMode.value = true; showModal.value = true
}

async function save() {
  saving.value = true
  try {
    if (editMode.value) { await menusApi.update(form.value.id, form.value); toast.success('Menú actualizado') }
    else                { await menusApi.create(form.value); toast.success('Menú creado') }
    showModal.value = false; load(); loadFlat()
  } catch (e) { toast.error('Error al guardar') }
  finally { saving.value = false }
}

async function deleteMenu(menu) {
  if (!confirm(`¿Eliminar menú "${menu.name}"?`)) return
  await menusApi.delete(menu.id); toast.success('Menú eliminado'); load(); loadFlat()
}

onMounted(() => { load(); loadFlat() })
</script>

<template>
  <div>
    <div class="page-header">
      <h2><i class="fa fa-bars me-2 text-primary"></i> Menús Dinámicos</h2>
      <button class="btn btn-primary btn-sm" @click="openCreate"><i class="fa fa-plus me-1"></i> Nuevo Menú</button>
    </div>

    <div class="data-table">
      <div v-if="loading" class="text-center py-4"><span class="spinner-sm" style="border-top-color:#1a73e8"></span></div>
      <table v-else>
        <thead><tr><th>Orden</th><th>Nombre</th><th>Ruta</th><th>Icono</th><th>Permiso</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
          <template v-for="m in menus" :key="m.id">
            <tr>
              <td>{{ m.order }}</td>
              <td><strong><i :class="m.icon" class="me-1"></i> {{ m.name }}</strong></td>
              <td><code>{{ m.route || '—' }}</code></td>
              <td><code>{{ m.icon }}</code></td>
              <td><span class="badge bg-light text-dark">{{ m.permission_slug || '—' }}</span></td>
              <td><span :class="m.status ? 'badge-active':'badge-inactive'">{{ m.status ? 'Activo':'Inactivo' }}</span></td>
              <td>
                <button class="icon-btn" @click="openEdit(m)"><i class="fa fa-edit"></i></button>
                <button class="icon-btn text-danger" @click="deleteMenu(m)"><i class="fa fa-trash"></i></button>
              </td>
            </tr>
            <tr v-for="child in m.children" :key="child.id" style="background:#fafbfc">
              <td style="padding-left:32px">{{ child.order }}</td>
              <td style="padding-left:32px"><i :class="child.icon" class="me-1 text-muted"></i> {{ child.name }}</td>
              <td><code>{{ child.route || '—' }}</code></td>
              <td><code>{{ child.icon }}</code></td>
              <td><span class="badge bg-light text-dark">{{ child.permission_slug || '—' }}</span></td>
              <td><span :class="child.status ? 'badge-active':'badge-inactive'">{{ child.status ? 'Activo':'Inactivo' }}</span></td>
              <td>
                <button class="icon-btn" @click="openEdit(child)"><i class="fa fa-edit"></i></button>
                <button class="icon-btn text-danger" @click="deleteMenu(child)"><i class="fa fa-trash"></i></button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal=false">
      <div class="modal-box">
        <div class="modal-header">
          <span>{{ editMode ? 'Editar Menú' : 'Nuevo Menú' }}</span>
          <button class="icon-btn" @click="showModal=false"><i class="fa fa-times"></i></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-6"><label class="form-label">Nombre *</label><input v-model="form.name" class="form-control" /></div>
            <div class="col-6"><label class="form-label">Slug *</label><input v-model="form.slug" class="form-control" /></div>
            <div class="col-6"><label class="form-label">Ruta</label><input v-model="form.route" class="form-control" placeholder="/dashboard" /></div>
            <div class="col-6"><label class="form-label">Icono (FA)</label><input v-model="form.icon" class="form-control" placeholder="fa fa-home" /></div>
            <div class="col-6">
              <label class="form-label">Menú padre</label>
              <select v-model="form.parent_id" class="form-select">
                <option :value="null">— Raíz —</option>
                <option v-for="m in flatMenus" :key="m.id" :value="m.id">{{ m.name }}</option>
              </select>
            </div>
            <div class="col-6"><label class="form-label">Permiso requerido</label><input v-model="form.permission_slug" class="form-control" placeholder="module.action" /></div>
            <div class="col-4"><label class="form-label">Orden</label><input v-model.number="form.order" type="number" class="form-control" /></div>
            <div class="col-4">
              <label class="form-label">Estado</label>
              <select v-model="form.status" class="form-select"><option :value="1">Activo</option><option :value="0">Inactivo</option></select>
            </div>
            <div class="col-4 d-flex align-items-end">
              <div class="p-2 rounded" style="background:#f0f0f0">
                <i :class="form.icon" class="me-1"></i> Preview
              </div>
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
