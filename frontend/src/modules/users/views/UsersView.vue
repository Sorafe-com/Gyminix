<script setup>
import { ref, watch, computed, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { usersApi } from '@/api/users'
import { rolesApi } from '@/api/roles'
import { useDebounce } from '@/composables/useDebounce'

const toast    = useToast()
const users    = ref([])
const roles    = ref([])
const meta     = ref({})
const loading  = ref(false)
const dialog   = ref(false)
const editMode = ref(false)
const saving   = ref(false)
const form     = ref({})
const showPass = ref(false)

const searchQuery     = ref('')
const roleFilter      = ref(null)
const statusFilter    = ref(null)
const page            = ref(1)
const perPage         = ref(15)
const debouncedSearch = useDebounce(searchQuery, 500)

const headers = [
  { title: 'Nombre',        key: 'name',       sortable: false },
  { title: 'Email',         key: 'email' },
  { title: 'Rol',           key: 'role',       sortable: false },
  { title: 'Último acceso', key: 'last_login', sortable: false },
  { title: 'Estado',        key: 'status',     align: 'center', width: '110px' },
  { title: 'Acciones',      key: 'actions',    sortable: false, align: 'center', width: '120px' },
]

const roleItems = computed(() => [
  { title: 'Todos los roles', value: null },
  ...roles.value.map(r => ({ title: r.name, value: r.id })),
])

watch([debouncedSearch, roleFilter, statusFilter], () => { page.value = 1; load() })

async function load() {
  loading.value = true
  try {
    const u = await usersApi.list({ search: debouncedSearch.value, role_id: roleFilter.value ?? '', status: statusFilter.value ?? '', page: page.value, per_page: perPage.value })
    users.value = u.data.data ?? []
    meta.value  = u.data.meta ?? {}
    try {
      const r = await rolesApi.list()
      roles.value = r.data.data ?? []
    } catch {}
  } catch (e) {
    toast.error(e.response?.data?.message || e.message || 'Error al cargar usuarios')
  } finally { loading.value = false }
}

function initials(u) {
  return ((u.first_name?.[0] || '') + (u.last_name?.[0] || '')).toUpperCase()
}

function openCreate() {
  form.value = { first_name: '', last_name: '', email: '', password: '', phone: '', role_id: '', status: 1 }
  editMode.value = false; dialog.value = true; showPass.value = false
}

function openEdit(user) {
  form.value = { ...user, password: '' }
  editMode.value = true; dialog.value = true; showPass.value = false
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
    dialog.value = false; load()
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
    <div class="d-flex align-center mb-6">
      <div>
        <h2 class="text-h5 font-weight-bold">Usuarios</h2>
        <p class="text-body-2 text-medium-emphasis mb-0">Gestión de usuarios del sistema</p>
      </div>
      <v-spacer />
      <v-btn prepend-icon="mdi-account-plus" @click="openCreate">Nuevo Usuario</v-btn>
    </div>

    <v-card class="mb-4 pa-4" elevation="0" border>
      <v-row dense>
        <v-col cols="12" sm="5" md="4">
          <v-text-field v-model="searchQuery" prepend-inner-icon="mdi-magnify" placeholder="Buscar usuario..." clearable />
        </v-col>
        <v-col cols="12" sm="4" md="3">
          <v-select v-model="roleFilter" :items="roleItems" item-title="title" item-value="value" placeholder="Rol" />
        </v-col>
        <v-col cols="12" sm="3" md="2">
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
      <v-data-table :headers="headers" :items="users" :loading="loading" :items-per-page="-1" hover>
        <template #item.name="{ item }">
          <div class="d-flex align-center ga-3 py-1">
            <v-avatar size="34" color="primary" class="text-caption font-weight-bold flex-shrink-0">
              {{ initials(item) }}
            </v-avatar>
            <div>
              <div class="font-weight-medium">{{ item.first_name }} {{ item.last_name }}</div>
            </div>
          </div>
        </template>
        <template #item.role="{ item }">
          <v-chip v-if="item.role_name" variant="tonal" size="small">{{ item.role_name }}</v-chip>
          <span v-else class="text-medium-emphasis">—</span>
        </template>
        <template #item.last_login="{ item }">
          <span class="text-caption text-medium-emphasis">
            {{ item.last_login ? new Date(item.last_login).toLocaleDateString() : '—' }}
          </span>
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
          <v-btn icon="mdi-delete-outline" color="error" variant="text" size="small" density="compact" @click="deleteUser(item)" />
        </template>
        <template #bottom>
          <v-divider />
          <div class="d-flex align-center justify-space-between pa-3">
            <span class="text-caption text-medium-emphasis">Total: {{ meta.total || 0 }} usuarios</span>
            <v-pagination v-model="page" :length="meta.pages || 1" density="compact" @update:model-value="load" />
          </div>
        </template>
        <template #no-data>
          <div class="text-center py-8 text-medium-emphasis">Sin usuarios</div>
        </template>
      </v-data-table>
    </v-card>

    <!-- Dialog -->
    <v-dialog v-model="dialog" max-width="540">
      <v-card>
        <v-card-title class="pa-5 pb-3">{{ editMode ? 'Editar Usuario' : 'Nuevo Usuario' }}</v-card-title>
        <v-divider />
        <v-card-text class="pa-5">
          <v-row dense>
            <v-col cols="6"><v-text-field v-model="form.first_name" label="Nombre *" /></v-col>
            <v-col cols="6"><v-text-field v-model="form.last_name" label="Apellido *" /></v-col>
            <v-col cols="6"><v-text-field v-model="form.email" label="Email *" type="email" /></v-col>
            <v-col cols="6"><v-text-field v-model="form.phone" label="Teléfono" /></v-col>
            <v-col cols="6">
              <v-text-field
                v-model="form.password"
                :label="editMode ? 'Nueva Contraseña' : 'Contraseña *'"
                :type="showPass ? 'text' : 'password'"
                :placeholder="editMode ? 'Dejar en blanco para no cambiar' : ''"
                :append-inner-icon="showPass ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
                @click:append-inner="showPass = !showPass"
              />
            </v-col>
            <v-col cols="6">
              <v-select
                v-model="form.role_id"
                :items="roles.map(r => ({ title: r.name, value: r.id }))"
                item-title="title"
                item-value="value"
                label="Rol *"
                placeholder="Seleccionar rol..."
              />
            </v-col>
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
  </div>
</template>
