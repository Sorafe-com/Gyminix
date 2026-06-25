<script setup>
import { ref, onMounted } from 'vue'
import { rolesApi } from '@/api/roles'

const perms   = ref({})
const loading = ref(true)

onMounted(async () => {
  const { data } = await rolesApi.permissions()
  if (data.success) perms.value = data.data
  loading.value = false
})
</script>

<template>
  <div>
    <div class="page-header">
      <h2><i class="fa fa-key me-2 text-primary"></i> Permisos del Sistema</h2>
    </div>

    <div v-if="loading" class="text-center py-5"><span class="spinner-sm" style="border-top-color:#1a73e8"></span></div>

    <div v-else class="row g-3">
      <div v-for="(list, module) in perms" :key="module" class="col-md-6 col-xl-4">
        <div class="data-table">
          <div class="p-3 border-bottom d-flex align-items-center gap-2">
            <i class="fa fa-cube text-primary"></i>
            <strong class="text-capitalize">{{ module }}</strong>
            <span class="badge bg-primary ms-auto">{{ list.length }}</span>
          </div>
          <table>
            <tbody>
              <tr v-for="p in list" :key="p.id">
                <td><code>{{ p.slug }}</code></td>
                <td class="text-muted-sm">{{ p.description }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
