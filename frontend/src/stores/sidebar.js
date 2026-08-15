import { defineStore } from 'pinia'

const STORAGE_KEY = 'sidebar_collapsed'

export const useSidebarStore = defineStore('sidebar', {
  state: () => ({
    collapsed: localStorage.getItem(STORAGE_KEY) === '1',
    mobileOpen: false,
  }),
  actions: {
    toggle() {
      this.collapsed = !this.collapsed
      localStorage.setItem(STORAGE_KEY, this.collapsed ? '1' : '0')
    },
    toggleMobile() {
      this.mobileOpen = !this.mobileOpen
    },
    closeMobile() {
      this.mobileOpen = false
    },
  },
})
