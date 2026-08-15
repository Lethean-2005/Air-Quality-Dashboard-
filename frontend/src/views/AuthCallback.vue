<template>
  <div class="min-h-screen flex items-center justify-center bg-white">
    <div class="text-center px-6">
      <svg class="animate-spin mx-auto mb-4 w-10 h-10 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
      </svg>
      <p v-if="!errorMessage" class="text-gray-500 text-sm">Signing you in...</p>
      <p v-if="errorMessage" class="text-red-500 text-sm mt-2">{{ errorMessage }}</p>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/airQuality'

const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const errorMessage = ref('')

onMounted(async () => {
  const token = route.query.token
  const error = route.query.error

  if (error) {
    errorMessage.value = error
    setTimeout(() => router.replace('/login'), 2500)
    return
  }

  if (!token) {
    errorMessage.value = 'Google sign-in failed. Please try again.'
    setTimeout(() => router.replace('/login'), 2500)
    return
  }

  const role = route.query.role || 'user'
  const name = route.query.name || 'User'
  const image = route.query.image || ''

  auth.login(token, role, name, image)
  router.replace(role === 'admin' ? '/admin-dashboard' : '/home')
})
</script>
