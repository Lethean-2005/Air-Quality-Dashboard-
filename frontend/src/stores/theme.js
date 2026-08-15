import { defineStore } from 'pinia'

const STORAGE_KEY = 'theme'

export const useThemeStore = defineStore('theme', {
  state: () => ({
    mode: localStorage.getItem(STORAGE_KEY) || 'dark',
  }),
  actions: {
    apply() {
      document.documentElement.classList.toggle('dark', this.mode === 'dark')
    },
    init() {
      this.apply()
    },
    setMode(mode) {
      this.mode = mode
      localStorage.setItem(STORAGE_KEY, mode)
      this.apply()
    },
    toggle() {
      this.setMode(this.mode === 'dark' ? 'light' : 'dark')
    },
  },
})
