<script setup>
import { ref, computed, onMounted } from 'vue'
import { rolesApi } from '@/api/roles'

const permissions = ref([])
const loading     = ref(true)
const search      = ref('')

const grouped = computed(() => {
  const filtered = search.value
    ? permissions.value.filter(p =>
        p.name.toLowerCase().includes(search.value.toLowerCase()) ||
        p.slug.toLowerCase().includes(search.value.toLowerCase()) ||
        p.module.toLowerCase().includes(search.value.toLowerCase())
      )
    : permissions.value

  const map = {}
  for (const p of filtered) {
    if (!map[p.module]) map[p.module] = []
    map[p.module].push(p)
  }
  return map
})

onMounted(async () => {
  try {
    const { data } = await rolesApi.permissions()
    permissions.value = data.data
  } finally { loading.value = false }
})
</script>

<template>
  <div>
    <div class="d-flex align-center mb-6">
      <div>
        <h2 class="text-h5 font-weight-bold">Permisos</h2>
        <p class="text-body-2 text-medium-emphasis mb-0">Listado de permisos disponibles en el sistema</p>
      </div>
      <v-spacer />
      <v-chip variant="tonal" color="primary">{{ permissions.length }} permisos</v-chip>
    </div>

    <v-card class="mb-5 pa-4" elevation="0" border>
      <v-text-field
        v-model="search"
        prepend-inner-icon="mdi-magnify"
        placeholder="Buscar permiso o módulo..."
        clearable
        style="max-width:340px"
      />
    </v-card>

    <v-skeleton-loader v-if="loading" type="card,card,card" />

    <v-row v-else>
      <v-col v-for="(perms, module) in grouped" :key="module" cols="12" md="6" xl="4">
        <v-card elevation="0" border height="100%">
          <v-card-title class="pa-4 pb-2 d-flex align-center ga-2">
            <v-icon icon="mdi-shield-key-outline" color="primary" size="20" />
            <span class="text-body-1 font-weight-medium">{{ module }}</span>
            <v-spacer />
            <v-chip size="x-small" variant="tonal">{{ perms.length }}</v-chip>
          </v-card-title>
          <v-divider />
          <v-card-text class="pa-3">
            <div class="d-flex flex-wrap ga-2">
              <v-chip
                v-for="perm in perms"
                :key="perm.id"
                size="small"
                variant="outlined"
                color="primary"
                :title="perm.description || perm.slug"
              >
                {{ perm.name }}
              </v-chip>
            </div>
          </v-card-text>
        </v-card>
      </v-col>
    </v-row>

    <div v-if="!loading && Object.keys(grouped).length === 0" class="text-center py-10 text-medium-emphasis">
      Sin resultados para "{{ search }}"
    </div>
  </div>
</template>
