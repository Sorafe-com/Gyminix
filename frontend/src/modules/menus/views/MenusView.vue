<script setup>
import { ref, computed, onMounted } from 'vue'
import { useToast } from 'vue-toastification'
import { menusApi } from '@/api/menus'

const toast    = useToast()
const tree     = ref([])
const flatList = ref([])
const loading  = ref(false)
const dialog   = ref(false)
const editMode = ref(false)
const saving   = ref(false)
const form     = ref({})

const headers = [
  { title: 'Nombre',    key: 'name',     sortable: false },
  { title: 'Ruta',      key: 'route',    sortable: false },
  { title: 'Ícono',     key: 'icon',     sortable: false, width: '120px' },
  { title: 'Orden',     key: 'order',    width: '80px' },
  { title: 'Permiso',   key: 'permission_slug', sortable: false },
  { title: 'Estado',    key: 'status',   align: 'center', width: '110px' },
  { title: 'Acciones',  key: 'actions',  sortable: false, align: 'center', width: '100px' },
]

const parentOptions = computed(() => [
  { title: '— Sin padre (raíz) —', value: null },
  ...flatList.value.filter(m => !m.parent_id).map(m => ({ title: m.name, value: m.id })),
])

const flatRows = computed(() => {
  const rows = []
  for (const parent of tree.value) {
    rows.push({ ...parent, _depth: 0 })
    for (const child of parent.children || []) {
      rows.push({ ...child, _depth: 1 })
    }
  }
  return rows
})

async function load() {
  loading.value = true
  try {
    const [t, l] = await Promise.all([menusApi.tree(), menusApi.list()])
    tree.value     = t.data.data ?? []
    flatList.value = l.data.data ?? []
  } catch (e) {
    toast.error(e.response?.data?.message || e.message || 'Error al cargar menús')
  } finally { loading.value = false }
}

function openCreate() {
  form.value = { name: '', slug: '', route: '', icon: '', parent_id: null, order: 0, permission_slug: '', status: 1 }
  editMode.value = false; dialog.value = true
}

function openEdit(item) {
  form.value = { ...item }
  editMode.value = true; dialog.value = true
}

async function save() {
  saving.value = true
  try {
    if (editMode.value) {
      await menusApi.update(form.value.id, form.value)
      toast.success('Menú actualizado')
    } else {
      await menusApi.create(form.value)
      toast.success('Menú creado')
    }
    dialog.value = false; load()
  } catch (e) {
    toast.error(e.response?.data?.message || 'Error al guardar')
  } finally { saving.value = false }
}

async function deleteMenu(item) {
  if (!confirm(`¿Eliminar menú "${item.name}"?`)) return
  await menusApi.delete(item.id)
  toast.success('Menú eliminado'); load()
}

onMounted(load)
</script>

<template>
  <div>
    <div class="d-flex align-center mb-6">
      <div>
        <h2 class="text-h5 font-weight-bold">Menús</h2>
        <p class="text-body-2 text-medium-emphasis mb-0">Estructura de navegación del sistema</p>
      </div>
      <v-spacer />
      <v-btn prepend-icon="mdi-plus" @click="openCreate">Nuevo Ítem</v-btn>
    </div>

    <v-card elevation="0" border>
      <v-data-table
        :headers="headers"
        :items="flatRows"
        :loading="loading"
        :items-per-page="-1"
        hover
      >
        <template #item.name="{ item }">
          <div class="d-flex align-center" :style="item._depth ? 'padding-left: 24px' : ''">
            <v-icon v-if="item._depth" icon="mdi-subdirectory-arrow-right" size="16" class="me-1 text-medium-emphasis" />
            <span :class="item._depth ? 'text-body-2' : 'font-weight-medium'">{{ item.name }}</span>
          </div>
        </template>
        <template #item.route="{ item }">
          <span class="text-caption font-weight-medium text-medium-emphasis">{{ item.route || '—' }}</span>
        </template>
        <template #item.icon="{ item }">
          <div v-if="item.icon" class="d-flex align-center ga-1">
            <i :class="item.icon" style="font-size:16px;width:18px;text-align:center" />
            <span class="text-caption text-medium-emphasis">{{ item.icon }}</span>
          </div>
          <span v-else class="text-medium-emphasis">—</span>
        </template>
        <template #item.permission_slug="{ item }">
          <v-chip v-if="item.permission_slug" size="x-small" variant="outlined">{{ item.permission_slug }}</v-chip>
          <span v-else class="text-medium-emphasis text-caption">—</span>
        </template>
        <template #item.status="{ item }">
          <v-chip :color="item.status ? 'success' : 'error'" variant="tonal" size="small">
            {{ item.status ? 'Activo' : 'Inactivo' }}
          </v-chip>
        </template>
        <template #item.actions="{ item }">
          <v-btn icon="mdi-pencil-outline" variant="text" size="small" density="compact" @click="openEdit(item)" />
          <v-btn icon="mdi-delete-outline" color="error" variant="text" size="small" density="compact" @click="deleteMenu(item)" />
        </template>
        <template #bottom><div /></template>
        <template #no-data>
          <div class="text-center py-8 text-medium-emphasis">Sin ítems de menú</div>
        </template>
      </v-data-table>
    </v-card>

    <!-- Dialog -->
    <v-dialog v-model="dialog" max-width="520">
      <v-card>
        <v-card-title class="pa-5 pb-3">{{ editMode ? 'Editar Ítem' : 'Nuevo Ítem de Menú' }}</v-card-title>
        <v-divider />
        <v-card-text class="pa-5">
          <v-row dense>
            <v-col cols="6"><v-text-field v-model="form.name" label="Nombre *" /></v-col>
            <v-col cols="6"><v-text-field v-model="form.slug" label="Slug *" /></v-col>
            <v-col cols="6"><v-text-field v-model="form.route" label="Ruta (ej: /users)" /></v-col>
            <v-col cols="6">
              <v-text-field v-model="form.icon" label="Clase de ícono (FA)" />
              <div v-if="form.icon" class="d-flex align-center ga-2 mt-1">
                <i :class="form.icon" style="font-size:18px" />
                <span class="text-caption text-medium-emphasis">Vista previa</span>
              </div>
            </v-col>
            <v-col cols="12">
              <v-select
                v-model="form.parent_id"
                :items="parentOptions"
                item-title="title"
                item-value="value"
                label="Menú padre"
              />
            </v-col>
            <v-col cols="6"><v-text-field v-model="form.permission_slug" label="Permiso requerido" /></v-col>
            <v-col cols="3">
              <v-text-field v-model.number="form.order" label="Orden" type="number" />
            </v-col>
            <v-col cols="3">
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
