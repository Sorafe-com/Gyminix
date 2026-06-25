import { defineStore } from 'pinia'
import { ref } from 'vue'
import { menusApi } from '@/api/menus'

export const useMenuStore = defineStore('menu', () => {
  const items    = ref([])
  const loading  = ref(false)

  async function fetchUserMenus() {
    loading.value = true
    try {
      const { data } = await menusApi.userMenus()
      if (data.success) items.value = data.data
    } finally {
      loading.value = false
    }
  }

  function clear() {
    items.value = []
  }

  return { items, loading, fetchUserMenus, clear }
})
