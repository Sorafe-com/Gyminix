import { defineStore } from 'pinia'
import { ref } from 'vue'
import { gymsApi } from '@/api/gyms'

export const useGymStore = defineStore('gym', () => {
  const current  = ref(JSON.parse(localStorage.getItem('current_gym') || 'null'))
  const settings = ref({})

  async function loadGym(id) {
    const { data } = await gymsApi.get(id)
    if (data.success) {
      current.value = data.data
      localStorage.setItem('current_gym', JSON.stringify(data.data))
    }
  }

  async function loadSettings(id) {
    const { data } = await gymsApi.getSettings(id)
    if (data.success) settings.value = data.data
  }

  function clear() {
    current.value  = null
    settings.value = {}
    localStorage.removeItem('current_gym')
  }

  return { current, settings, loadGym, loadSettings, clear }
})
