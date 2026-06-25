<script setup>
import { ref, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { rolesApi } from '@/api/roles'

const toast    = useToast()
const roles    = ref([])
const allPerms = ref([])
const modules  = ref([])
const loading  = ref(false)
const dialog   = ref(false)
const permDialog    = ref(false)
const editMode      = ref(false)
const saving        = ref(false)
const savingPerms   = ref(false)
const form          = ref({})
const rolePerms     = ref([])
const selectedRole  = ref(null)

const headers = [
  { title: '#',        key: 'id',          width: '60px' },
  { title: 'Nombre',   key: 'name' },
  { title: 'Slug',     key: 'slug' },
  { title: 'Permisos', key: 'perms_count', sortable: false, align: 'center', width: '110px' },
  { title: 'Estado',   key: 'status',      align: 'center', width: '110px' },
  { title: 'Acciones', key: 'actions',     sortable: false, align: 'center', width: '140px' },
]

async function load() {
  loading.value = true
  try {
    const [r, p] = await Promise.all([rolesApi.list(), rolesApi.listPermissions()])
    roles.value    = r.data.data
    allPerms.value = p.data.data
    const mods = [...new Set(allPerms.value.map(p => p.module))]
    modules.value = mods.map(m => ({
      name: m,
      permissions: allPerms.value.filter(p => p.module === m),
    }))
  } finally { loading.value = false }
}

function openCreate() {
  form.value = { name: '', slug: '', description: '', status: 1 }
  editMode.value = false; dialog.value = true
}

function openEdit(role) {
  form.value = { ...role }
  editMode.value = true; dialog.value = true
}

function openPermissions(role) {
  selectedRole.value = role
  rolePerms.value    = role.permissions?.map(p => p.id) || []
  permDialog.value   = true
}

async function save() {
  saving.value = true
  try {
    if (editMode.value) {
      await rolesApi.update(form.value.id, form.value)
      toast.success('Rol actualizado')
    } else {
      await rolesApi.create(form.value)
      toast.success('Rol creado')
    }
    dialog.value = false; load()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Error al guardar')
  } finally { saving.value = false }
}

async function savePermissions() {
  savingPerms.value = true
  try {
    await rolesApi.syncPermissions(selectedRole.value.id, { permissions: rolePerms.value })
    toast.success('Permisos sincronizados')
    permDialog.value = false; load()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Error al guardar permisos')
  } finally { savingPerms.value = false }
}

async function toggleStatus(role) {
  await rolesApi.toggleStatus(role.id)
  toast.success('Estado actualizado'); load()
}

function toggleModule(mod) {
  const ids = mod.permissions.map(p => p.id)
  const allChecked = ids.every(id => rolePerms.value.includes(id))
  if (allChecked) {
    rolePerms.value = rolePerms.value.filter(id => !ids.includes(id))
  } else {
    const missing = ids.filter(id => !rolePerms.value.includes(id))
    rolePerms.value = [...rolePerms.value, ...missing]
  }
}

function moduleChecked(mod) {
  return mod.permissions.every(p => rolePerms.value.includes(p.id))
}

function moduleIndeterminate(mod) {
  const some = mod.permissions.some(p => rolePerms.value.includes(p.id))
  return some && !moduleChecked(mod)
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex align-center mb-6">
      <div>
        <h2 class="text-h5 font-weight-bold">Roles</h2>
        <p class="text-body-2 text-medium-emphasis mb-0">Gestión de roles del sistema</p>
      </div>
      <v-spacer />
      <v-btn prepend-icon="mdi-plus" @click="openCreate">Nuevo Rol</v-btn>
    </div>

    <v-card elevation="0" border>
      <v-data-table :headers="headers" :items="roles" :loading="loading" :items-per-page="-1" hover>
        <template #item.perms_count="{ item }">
          <v-chip size="small" variant="tonal">{{ item.permissions?.length || 0 }}</v-chip>
        </template>
        <template #item.status="{ item }">
          <v-chip :color="item.status ? 'success' : 'error'" variant="tonal" size="small">
            {{ item.status ? 'Activo' : 'Inactivo' }}
          </v-chip>
        </template>
        <template #item.actions="{ item }">
          <v-btn icon="mdi-key-variant" color="warning" variant="text" size="small" density="compact" title="Permisos" @click="openPermissions(item)" />
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
        <template #bottom><div /></template>
        <template #no-data>
          <div class="text-center py-8 text-medium-emphasis">Sin roles</div>
        </template>
      </v-data-table>
    </v-card>

    <!-- CRUD Dialog -->
    <v-dialog v-model="dialog" max-width="480">
      <v-card>
        <v-card-title class="pa-5 pb-3">{{ editMode ? 'Editar Rol' : 'Nuevo Rol' }}</v-card-title>
        <v-divider />
        <v-card-text class="pa-5">
          <v-row dense>
            <v-col cols="6"><v-text-field v-model="form.name" label="Nombre *" /></v-col>
            <v-col cols="6"><v-text-field v-model="form.slug" label="Slug *" /></v-col>
            <v-col cols="12"><v-text-field v-model="form.description" label="Descripción" /></v-col>
            <v-col cols="6">
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

    <!-- Permissions Dialog -->
    <v-dialog v-model="permDialog" max-width="640" scrollable>
      <v-card>
        <v-card-title class="pa-5 pb-3 d-flex align-center ga-2">
          <v-icon icon="mdi-key-variant" color="warning" />
          Permisos: {{ selectedRole?.name }}
        </v-card-title>
        <v-divider />
        <v-card-text class="pa-0" style="max-height: 60vh;">
          <v-expansion-panels variant="accordion" flat>
            <v-expansion-panel v-for="mod in modules" :key="mod.name">
              <v-expansion-panel-title class="text-body-2 font-weight-medium">
                <v-checkbox
                  :model-value="moduleChecked(mod)"
                  :indeterminate="moduleIndeterminate(mod)"
                  density="compact"
                  hide-details
                  class="me-2 flex-grow-0"
                  @click.stop="toggleModule(mod)"
                />
                {{ mod.name }}
                <v-chip class="ms-2" size="x-small" variant="tonal">
                  {{ mod.permissions.filter(p => rolePerms.includes(p.id)).length }}/{{ mod.permissions.length }}
                </v-chip>
              </v-expansion-panel-title>
              <v-expansion-panel-text>
                <div class="d-flex flex-wrap ga-2 pt-1 pb-2">
                  <v-checkbox
                    v-for="perm in mod.permissions"
                    :key="perm.id"
                    v-model="rolePerms"
                    :value="perm.id"
                    :label="perm.name"
                    density="compact"
                    hide-details
                    class="flex-grow-0"
                  />
                </div>
              </v-expansion-panel-text>
            </v-expansion-panel>
          </v-expansion-panels>
        </v-card-text>
        <v-divider />
        <v-card-actions class="pa-4">
          <span class="text-caption text-medium-emphasis">{{ rolePerms.length }} permisos seleccionados</span>
          <v-spacer />
          <v-btn variant="text" color="default" @click="permDialog = false">Cancelar</v-btn>
          <v-btn :loading="savingPerms" @click="savePermissions">Guardar</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>
