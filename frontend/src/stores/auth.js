import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/api/auth'

export const useAuthStore = defineStore('auth', () => {
  const user         = ref(JSON.parse(localStorage.getItem('user') || 'null'))
  const accessToken  = ref(localStorage.getItem('access_token') || '')
  const refreshToken = ref(localStorage.getItem('refresh_token') || '')

  const isAuthenticated = computed(() => !!accessToken.value && !!user.value)
  const isSuperAdmin    = computed(() => !!user.value?.is_super_admin)
  const gymId           = computed(() => user.value?.gym_id)

  async function login(credentials) {
    const { data } = await authApi.login(credentials)
    if (data.success) {
      setSession(data.data)
    }
    return data
  }

  async function fetchMe() {
    const { data } = await authApi.me()
    if (data.success) {
      user.value = data.data
      localStorage.setItem('user', JSON.stringify(data.data))
    }
  }

  async function logout() {
    try {
      await authApi.logout(refreshToken.value)
    } catch {}
    clearSession()
  }

  function setSession(payload) {
    accessToken.value  = payload.access_token
    refreshToken.value = payload.refresh_token
    user.value         = payload.user
    localStorage.setItem('access_token',  payload.access_token)
    localStorage.setItem('refresh_token', payload.refresh_token)
    localStorage.setItem('user',          JSON.stringify(payload.user))
  }

  function clearSession() {
    user.value         = null
    accessToken.value  = ''
    refreshToken.value = ''
    localStorage.removeItem('access_token')
    localStorage.removeItem('refresh_token')
    localStorage.removeItem('user')
  }

  return { user, accessToken, refreshToken, isAuthenticated, isSuperAdmin, gymId, login, fetchMe, logout, setSession, clearSession }
})
