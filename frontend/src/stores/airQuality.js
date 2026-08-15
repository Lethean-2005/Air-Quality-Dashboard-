// stores/auth.ts
import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

const STORAGE_URL_BASE = `${import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8001'}/storage/`

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('auth_token') || '')
  const userRole = ref(localStorage.getItem('user_role') || '')
  const userName = ref(localStorage.getItem('user_name') || '')
  const userImage = ref(localStorage.getItem('user_image') || '')

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => userRole.value === 'admin')

  function login(newToken, role, name, profileImagePath = '') {
    token.value = newToken
    userRole.value = role
    userName.value = name

    userImage.value = profileImagePath
      ? profileImagePath.startsWith('http')
        ? profileImagePath
        : STORAGE_URL_BASE + profileImagePath
      : ''

    localStorage.setItem('auth_token', newToken)
    localStorage.setItem('user_role', role)
    localStorage.setItem('user_name', name)
    localStorage.setItem('user_image', userImage.value)

    axios.defaults.headers.common['Authorization'] = `Bearer ${newToken}`
  }

  function logout() {
    token.value = ''
    userRole.value = ''
    userName.value = ''
    userImage.value = ''

    localStorage.removeItem('auth_token')
    localStorage.removeItem('user_role')
    localStorage.removeItem('user_name')
    localStorage.removeItem('user_image')

    delete axios.defaults.headers.common['Authorization']
  }

  return {
    token,
    userRole,
    userName,
    userImage,
    isAuthenticated,
    isAdmin,
    login,
    logout,
  }
})
