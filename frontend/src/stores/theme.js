import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useThemeStore = defineStore('theme', () => {
  const isDark = ref(localStorage.getItem('gyminix-theme') === 'dark')

  function toggle() {
    isDark.value = !isDark.value
    localStorage.setItem('gyminix-theme', isDark.value ? 'dark' : 'light')
  }

  return { isDark, toggle }
})
