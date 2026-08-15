<template>
  <div class="min-h-screen flex bg-white font-dmsans">
    <!-- Left: image panel -->
    <div class="hidden lg:block lg:w-1/2 relative overflow-hidden">
      <img :src="sidebarBg" alt="" class="absolute inset-0 w-full h-full object-cover" />
      <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-white"></div>
      <div class="absolute top-10 left-10 max-w-xs">
        <h1 class="text-3xl font-bold text-gray-900">{{ $t('auth.registerTitle') }}</h1>
        <p class="text-gray-600 mt-1">{{ $t('auth.registerSubtitle') }}</p>
      </div>
    </div>

    <!-- Right: form -->
    <div class="flex-1 flex items-center justify-center px-6 py-12">
      <div class="w-full max-w-md">
        <!-- Tab navigation -->
        <div class="flex border-b border-gray-200 mb-8">
          <button
            @click="$router.push('/login')"
            class="px-1 pb-3 mr-8 text-gray-400 hover:text-gray-600 font-medium transition-colors"
          >
            {{ $t('auth.login') }}
          </button>
          <button class="px-1 pb-3 text-gray-900 font-semibold border-b-2 border-blue-500">
            {{ $t('auth.register') }}
          </button>
        </div>

        <form @submit.prevent="register" class="space-y-4">
          <!-- Full Name -->
          <input
            v-model="name"
            type="text"
            :placeholder="$t('auth.enterNamePlaceholder')"
            class="w-full px-4 py-3 bg-gray-100 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400 transition-shadow"
          />

          <!-- Email -->
          <input
            v-model="email"
            type="email"
            :placeholder="$t('auth.enterEmailPlaceholder')"
            class="w-full px-4 py-3 bg-gray-100 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400 transition-shadow"
          />

          <!-- Password -->
          <div class="relative">
            <input
              v-model="password"
              :type="showPassword ? 'text' : 'password'"
              :placeholder="$t('auth.enterPasswordPlaceholder')"
              class="w-full px-4 py-3 pr-11 bg-gray-100 rounded-lg text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400 transition-shadow"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
            >
              <IconEyeOff v-if="showPassword" :size="18" />
              <IconEye v-else :size="18" />
            </button>
          </div>

          <!-- Terms and conditions -->
          <div class="flex items-start pt-1">
            <input
              type="checkbox"
              v-model="acceptTerms"
              class="w-4 h-4 mt-0.5 text-blue-500 border-gray-300 rounded focus:ring-blue-400 accent-blue-500"
            />
            <span class="ml-2 text-gray-500 text-sm">
              {{ $t('auth.termsText') }}
              <a href="#" class="text-blue-500 hover:underline">{{ $t('auth.terms') }}</a>
              {{ $t('auth.and') }}
              <a href="#" class="text-blue-500 hover:underline">{{ $t('auth.privacy') }}</a>
            </span>
          </div>

          <!-- Submit Button -->
          <button
            type="submit"
            :disabled="loading || !acceptTerms"
            class="w-full bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-lg font-semibold transition-colors disabled:opacity-50 disabled:cursor-not-allowed mt-2"
          >
            {{ loading ? $t('auth.creatingAccount') : $t('auth.createAccount') }}
          </button>
        </form>

        <div class="flex items-center gap-4 my-6">
          <div class="flex-1 h-px bg-gray-200"></div>
          <span class="text-sm text-gray-400">{{ $t('auth.or') }}</span>
          <div class="flex-1 h-px bg-gray-200"></div>
        </div>

        <button
          type="button"
          @click="handleGoogleLogin"
          :disabled="loading"
          class="w-full flex items-center justify-center gap-2 border border-gray-200 rounded-lg py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg class="w-4 h-4" viewBox="0 0 24 24">
            <path fill="#4285F4" d="M23.49 12.27c0-.79-.07-1.54-.19-2.27H12v4.51h6.47a5.53 5.53 0 01-2.4 3.63v3h3.88c2.27-2.09 3.54-5.17 3.54-8.87z" />
            <path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3a7.4 7.4 0 01-4.05 1.14c-3.11 0-5.75-2.1-6.69-4.93H1.3v3.1A12 12 0 0012 24z" />
            <path fill="#FBBC05" d="M5.31 14.3a7.2 7.2 0 010-4.6v-3.1H1.3a12 12 0 000 10.8z" />
            <path fill="#EA4335" d="M12 4.75c1.76 0 3.34.61 4.59 1.8l3.44-3.44C17.94 1.19 15.24 0 12 0A12 12 0 001.3 6.6l4.01 3.1C6.25 6.86 8.89 4.75 12 4.75z" />
          </svg>
          {{ $t('auth.continueWithGoogle') }}
        </button>

        <!-- Login link -->
        <p class="text-center text-gray-500 text-sm mt-8">
          {{ $t('auth.alreadyHaveAccount') }}
          <button @click="$router.push('/login')" class="text-blue-500 hover:underline font-medium ml-1">
            {{ $t('auth.login') }}
          </button>
        </p>

        <!-- Error message -->
        <p v-if="errorMessage" class="text-sm text-center text-red-500 mt-4">
          {{ errorMessage }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'
import { useAuthStore } from '@/stores/airQuality'
import { useI18n } from 'vue-i18n'
import { IconEye, IconEyeOff } from '@tabler/icons-vue'
import sidebarBg from '@/assets/images/video/sidebar-bg.52323289.webp'

const { t } = useI18n()
const router = useRouter()
const auth = useAuthStore()

const name = ref('')
const email = ref('')
const password = ref('')
const acceptTerms = ref(false)
const showPassword = ref(false)
const loading = ref(false)
const errorMessage = ref('')

const register = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    const res = await api.post('/register', {
      name: name.value,
      email: email.value,
      password: password.value,
    })

    const token = res.data.token
    auth.login(token)

    router.push('/')
  } catch (err) {
    errorMessage.value =
      err.response?.data?.message || t('auth.registerFailed') || 'Registration failed. Please try again.'
  } finally {
    loading.value = false
  }
}

const handleGoogleLogin = () => {
  errorMessage.value = ''
  loading.value = true
  window.location.href = api.defaults.baseURL + '/auth/google/redirect'
}
</script>
