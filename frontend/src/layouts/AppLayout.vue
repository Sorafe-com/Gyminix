<script setup>
import { ref, watch, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useDisplay, useTheme } from 'vuetify'
import { useAuthStore } from '@/stores/auth'
import { useMenuStore } from '@/stores/menu'
import { useGymStore } from '@/stores/gym'
import { useThemeStore } from '@/stores/theme'
import SidebarMenu from '@/components/common/SidebarMenu.vue'
import { barActive, barWidth } from '@/utils/loadingBar'

const router       = useRouter()
const authStore    = useAuthStore()
const menuStore    = useMenuStore()
const gymStore     = useGymStore()
const themeStore   = useThemeStore()
const vuetifyTheme = useTheme()

const { mdAndDown } = useDisplay()
const drawer = ref(true)
const rail   = ref(false)

const userInitials = computed(() => {
  const u = authStore.user
  if (!u) return '?'
  return ((u.first_name?.[0] || '') + (u.last_name?.[0] || '')).toUpperCase()
})

watch(() => themeStore.isDark, (dark) => {
  vuetifyTheme.global.name.value = dark ? 'dark' : 'light'
}, { immediate: true })

watch(mdAndDown, (val) => {
  drawer.value = !val
}, { immediate: true })

async function doLogout() {
  await authStore.logout()
  menuStore.clear()
  gymStore.clear()
  router.push('/login')
}

onMounted(async () => {
  await menuStore.fetchUserMenus()
  if (authStore.gymId) await gymStore.loadGym(authStore.gymId)
})
</script>

<template>
  <!-- Global progress bar -->
  <div
    class="progress-bar-top"
    :class="{ active: barActive }"
    :style="{ width: barWidth + '%' }"
  />

  <!-- Navigation Drawer (Sidebar) -->
  <v-navigation-drawer
    v-model="drawer"
    :rail="rail && !mdAndDown"
    color="surface"
    elevation="2"
    width="260"
  >
    <!-- Brand -->
    <div class="d-flex align-center px-4 sidebar-brand-row">
      <v-icon icon="mdi-dumbbell" color="primary" size="26" />
      <span v-show="!(rail && !mdAndDown)" class="ms-3 text-subtitle-1 font-weight-bold text-primary">
        Gyminix
      </span>
      <v-spacer />
      <v-btn
        v-if="!mdAndDown"
        :icon="rail ? 'mdi-chevron-right' : 'mdi-chevron-left'"
        variant="text"
        size="small"
        density="compact"
        @click="rail = !rail"
      />
    </div>

    <!-- Active gym chip -->
    <div v-if="gymStore.current && !(rail && !mdAndDown)" class="px-3 pt-3 pb-1">
      <v-chip color="primary" variant="tonal" size="small" class="w-100 justify-center">
        <v-icon start icon="mdi-domain" size="14" />
        <span class="text-caption">{{ gymStore.current.name }}</span>
      </v-chip>
    </div>

    <v-divider class="mb-1" />

    <SidebarMenu :items="menuStore.items" :collapsed="rail && !mdAndDown" />
  </v-navigation-drawer>

  <!-- App Bar -->
  <v-app-bar elevation="0" border="b" height="60">
    <v-app-bar-nav-icon v-if="mdAndDown" @click="drawer = !drawer" />

    <v-app-bar-title class="text-subtitle-1 font-weight-medium">
      {{ gymStore.current?.name || 'Gyminix' }}
    </v-app-bar-title>

    <template #append>
      <v-btn
        :icon="themeStore.isDark ? 'mdi-weather-sunny' : 'mdi-weather-night'"
        variant="text"
        size="small"
        @click="themeStore.toggle"
      />

      <v-menu offset="10" min-width="180">
        <template #activator="{ props }">
          <v-btn v-bind="props" variant="text" class="ms-1 px-2">
            <v-avatar size="32" color="primary" class="text-caption font-weight-bold">
              {{ userInitials }}
            </v-avatar>
            <span class="ms-2 d-none d-sm-inline text-body-2 font-weight-medium">
              {{ authStore.user?.first_name }}
            </span>
            <v-icon icon="mdi-chevron-down" size="16" class="ms-1 text-medium-emphasis" />
          </v-btn>
        </template>

        <v-list elevation="3" density="compact" rounded="lg">
          <v-list-item prepend-icon="mdi-view-dashboard-outline" title="Dashboard" to="/dashboard" />
          <v-divider class="my-1" />
          <v-list-item prepend-icon="mdi-logout" title="Cerrar sesión" base-color="error" @click="doLogout" />
        </v-list>
      </v-menu>
    </template>
  </v-app-bar>

  <!-- Main Content -->
  <v-main>
    <v-container fluid class="pa-5 pa-md-6">
      <RouterView />
    </v-container>
  </v-main>
</template>
