<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useMenuStore } from '@/stores/menu'
import { useGymStore } from '@/stores/gym'
import SidebarMenu from '@/components/common/SidebarMenu.vue'

const router    = useRouter()
const authStore = useAuthStore()
const menuStore = useMenuStore()
const gymStore  = useGymStore()

const showUserDropdown = ref(false)

const userInitials = () => {
  const u = authStore.user
  if (!u) return '?'
  return ((u.first_name?.[0] || '') + (u.last_name?.[0] || '')).toUpperCase()
}

async function doLogout() {
  await authStore.logout()
  menuStore.clear()
  gymStore.clear()
  router.push('/login')
}

onMounted(async () => {
  await menuStore.fetchUserMenus()
  if (authStore.gymId) {
    await gymStore.loadGym(authStore.gymId)
  }
})
</script>

<template>
  <div>
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-brand">
        <i class="fa fa-dumbbell"></i>
        <span>Gyminix</span>
      </div>
      <nav class="sidebar-nav">
        <SidebarMenu :items="menuStore.items" />
      </nav>
    </aside>

    <!-- Topbar -->
    <header class="topbar">
      <span class="topbar-title">{{ gymStore.current?.name || 'Gyminix' }}</span>

      <div class="topbar-user" @click="showUserDropdown = !showUserDropdown">
        <div class="avatar-circle">{{ userInitials() }}</div>
        <span style="font-size:.85rem; font-weight:600">{{ authStore.user?.first_name }}</span>
        <i class="fa fa-chevron-down" style="font-size:.7rem; color:#aaa"></i>

        <div v-if="showUserDropdown" class="dropdown-menu show" style="position:absolute;top:42px;right:0;min-width:160px;z-index:200" @click.stop>
          <router-link class="dropdown-item" to="/dashboard" @click="showUserDropdown=false">
            <i class="fa fa-home me-2"></i> Dashboard
          </router-link>
          <div class="dropdown-divider"></div>
          <button class="dropdown-item text-danger" @click="doLogout">
            <i class="fa fa-sign-out me-2"></i> Cerrar sesión
          </button>
        </div>
      </div>
    </header>

    <!-- Content -->
    <main class="main-content">
      <RouterView />
    </main>
  </div>
</template>
