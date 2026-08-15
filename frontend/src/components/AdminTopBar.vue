<template>
  <nav class="w-full flex items-center justify-between px-8 py-3 relative z-50 bg-transparent">
    <!-- Left Section: Mobile menu button + Breadcrumb + Page Title -->
    <div class="min-w-0 flex items-center gap-3">
      <button
        @click="sidebar.toggleMobile()"
        title="Open menu"
        class="flex md:hidden items-center justify-center w-8 h-8 rounded-full text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/10 transition-all duration-200 flex-shrink-0"
      >
        <IconMenu2 :size="18" />
      </button>
      <div class="min-w-0">
      <div class="flex items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500">
        <RouterLink to="/admin-dashboard" class="hover:text-gray-600 dark:hover:text-gray-300 transition-colors">Pages</RouterLink>
        <IconChevronRight :size="12" />
        <template v-if="pageMeta.parent">
          <RouterLink :to="pageMeta.parent.path" class="hover:text-gray-600 dark:hover:text-gray-300 transition-colors">{{ pageMeta.parent.label }}</RouterLink>
          <IconChevronRight :size="12" />
        </template>
        <span class="text-gray-600 dark:text-gray-300 font-medium">{{ pageTitle }}</span>
      </div>
      <h1 class="text-lg font-bold text-gray-900 dark:text-white leading-tight mt-0.5 truncate">
        {{ pageTitle }}
      </h1>
      </div>
    </div>

    <!-- Right Section: Theme, Language, Notifications, Login/Profile -->
    <div class="flex items-center gap-1.5 relative flex-shrink-0">
      <!-- Dark / light mode toggle -->
      <button
        @click="theme.toggle()"
        class="flex items-center justify-center w-8 h-8 rounded-full text-gray-500 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/10 transition-all duration-200"
        :title="theme.mode === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
      >
        <IconSun v-if="theme.mode === 'dark'" :size="15" />
        <IconMoon v-else :size="15" />
      </button>

      <!-- Language Switcher -->
      <div class="relative" ref="languageDropdownRef">
        <button
          @click="toggleLanguageDropdown"
          class="flex items-center justify-center gap-1.5 h-8 px-2.5 bg-white dark:bg-white/5 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-white/10 transition-all duration-200 border border-gray-200 dark:border-white/10 w-[88px]"
          title="Change Language"
        >
          <IconGlobe :size="14" class="text-teal-600 dark:text-teal-300 flex-shrink-0" />
          <span class="text-[11px] font-medium select-none truncate">
            {{ currentLanguage === 'en' ? 'English' : 'ខ្មែរ' }}
          </span>
        </button>

        <div
          v-if="languageDropdownOpen"
          class="absolute right-0 mt-2 w-40 bg-white border border-gray-100 rounded-lg shadow-xl z-50 overflow-hidden"
        >
          <button
            @click="changeLanguage('en')"
            class="flex items-center gap-2.5 w-full px-4 py-2.5 text-left text-sm text-gray-900 hover:text-gray-400 transition-colors duration-200 border-b border-gray-100 whitespace-nowrap"
          >
            <img src="https://flagcdn.com/w40/gb.png" alt="English" class="w-5 h-3.5 rounded-sm object-cover flex-shrink-0" />
            English
          </button>
          <button
            @click="changeLanguage('kh')"
            class="flex items-center gap-2.5 w-full px-4 py-2.5 text-left text-sm text-gray-900 hover:text-gray-400 transition-colors duration-200 whitespace-nowrap"
          >
            <img src="https://flagcdn.com/w40/kh.png" alt="Khmer" class="w-5 h-3.5 rounded-sm object-cover flex-shrink-0" />
            ភាសាខ្មែរ
          </button>
        </div>
      </div>

      <!-- Login / Profile -->
      <div v-if="!isLoggedIn">
        <button
          class="flex items-center justify-center gap-1.5 h-8 px-2.5 bg-white dark:bg-white/5 rounded-lg text-gray-700 dark:text-white hover:bg-gray-50 dark:hover:bg-white/10 transition-all duration-200 border border-gray-200 dark:border-white/10 w-[80px]"
          @click="$router.push('/login')"
        >
          <IconLogin :size="14" class="text-teal-600 dark:text-teal-300 flex-shrink-0" />
          <span class="text-[11px] font-medium select-none truncate">
            {{ $t('auth.login') }}
          </span>
        </button>
      </div>

      <div v-else class="relative" ref="profileDropdownRef">
        <button
          v-if="loadingProfile"
          class="flex items-center gap-1.5 h-8 px-2 rounded-lg border border-gray-200 dark:border-white/10 min-w-[40px]"
          disabled
        >
          <Skeleton class="w-3.5 h-3.5 rounded-full flex-shrink-0" />
          <Skeleton class="h-2.5 w-12 hidden sm:inline-block" />
        </button>
        <button
          v-else
          @click="toggleProfileDropdown"
          class="flex items-center justify-center gap-1.5 h-8 px-2 bg-white dark:bg-white/5 rounded-lg text-gray-700 dark:text-white hover:bg-gray-50 dark:hover:bg-white/10 transition-all duration-200 border border-gray-200 dark:border-white/10 min-w-[40px] max-w-[140px]"
          title="User Profile"
        >
          <img
            v-if="profile.profile_image"
            :src="profile.profile_image"
            alt="Profile Image"
            class="w-3.5 h-3.5 rounded-full object-cover flex-shrink-0"
          />
          <IconUserCircle v-else :size="14" class="text-teal-600 dark:text-teal-300 flex-shrink-0" />

          <span class="text-[11px] font-medium select-none truncate hidden sm:inline">
            {{ displayUserName }}
          </span>
          <IconChevronDown
            :size="11"
            class="text-gray-400 dark:text-gray-300 flex-shrink-0 transition-transform duration-200 hidden sm:inline"
            :class="{ 'rotate-180': profileDropdownOpen }"
          />
        </button>

        <div
          v-if="profileDropdownOpen"
          class="absolute right-0 mt-2 w-56 bg-white border border-gray-100 rounded-lg shadow-xl z-50 overflow-hidden"
        >
          <div class="flex items-center gap-2.5 px-3 py-3 border-b border-gray-100">
            <div v-if="loadingProfile" class="flex items-center gap-2.5 w-full">
              <Skeleton class="w-9 h-9 rounded-full flex-shrink-0" />
              <div class="flex-1 space-y-2">
                <Skeleton class="h-3.5 w-20" />
                <Skeleton class="h-3 w-28" />
              </div>
            </div>
            <template v-else>
              <img
                v-if="profile.profile_image"
                :src="profile.profile_image"
                alt="Profile Image"
                class="w-9 h-9 rounded-full object-cover flex-shrink-0"
              />
              <div
                v-else
                class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0"
              >
                <IconUserCircle :size="20" class="text-gray-400" />
              </div>
              <div class="min-w-0">
                <p class="text-sm font-semibold text-gray-900 truncate">
                  {{ profile.name || 'User' }}
                  <span class="font-normal text-gray-400">({{ profile.role || 'Role not set' }})</span>
                </p>
                <p class="text-xs text-gray-500 truncate">{{ profile.email || 'No email' }}</p>
              </div>
            </template>
          </div>

          <div class="py-1">
            <button
              @click="navigateToProfile"
              class="flex items-center gap-3 w-full px-3 py-2.5 text-left text-xs font-medium text-gray-900 hover:text-gray-400 transition-colors duration-200 border-b border-gray-100"
            >
              <IconUser :size="15" class="flex-shrink-0" />
              Profile
            </button>

            <button
              @click="navigateToMessages"
              class="flex items-center gap-3 w-full px-3 py-2.5 text-left text-xs font-medium text-gray-900 hover:text-gray-400 transition-colors duration-200 border-b border-gray-100"
            >
              <IconBrandHipchat :size="15" class="flex-shrink-0" />
              Messages
            </button>

            <button
              @click="handleLogout"
              class="flex items-center gap-3 w-full px-3 py-2.5 text-left text-xs font-medium text-red-500 hover:text-red-400 transition-colors duration-200"
            >
              <IconLogout :size="15" class="flex-shrink-0" />
              {{ $t('auth.logout') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/airQuality'
import { useThemeStore } from '@/stores/theme'
import { useSidebarStore } from '@/stores/sidebar'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import Skeleton from '@/components/Skeleton.vue'
import {
  IconGlobe,
  IconLogin,
  IconUserCircle,
  IconChevronDown,
  IconChevronRight,
  IconUser,
  IconBrandHipchat,
  IconLogout,
  IconSun,
  IconMoon,
  IconMenu2,
} from '@tabler/icons-vue'

const theme = useThemeStore()
const sidebar = useSidebarStore()
const route = useRoute()

const PAGE_META = {
  '/admin-dashboard': { title: 'Dashboard' },
  '/cityaqi': { title: 'City AQI' },
  '/health-alert': { title: 'Health Alert' },
  '/admin-news': { title: 'News' },
  '/categories': { title: 'Categories', parent: { label: 'News', path: '/admin-news' } },
  '/user-management': { title: 'User management' },
}

const pageMeta = computed(() => PAGE_META[route.path] || { title: 'Dashboard' })
const pageTitle = computed(() => pageMeta.value.title)

const { locale, t } = useI18n()
const currentLanguage = ref(locale.value)
const languageDropdownOpen = ref(false)
const profileDropdownOpen = ref(false)
const router = useRouter()
const auth = useAuthStore()

const profile = ref({})
// Starts true (not false) whenever a token is already present, so the very first paint shows
// the skeleton instead of briefly flashing the real button with empty/fallback profile data
// before onMounted's fetchUserProfile() has a chance to run and flip it.
const loadingProfile = ref(!!auth.token)
const profileError = ref(null)
const languageDropdownRef = ref(null)
const profileDropdownRef = ref(null)

function toggleLanguageDropdown() {
  languageDropdownOpen.value = !languageDropdownOpen.value
  profileDropdownOpen.value = false
}

function toggleProfileDropdown() {
  profileDropdownOpen.value = !profileDropdownOpen.value
  languageDropdownOpen.value = false
}

function changeLanguage(lang) {
  currentLanguage.value = lang
  locale.value = lang
  document.documentElement.lang = lang === 'kh' ? 'km' : 'en'
  languageDropdownOpen.value = false
}

async function fetchUserProfile() {
  if (!auth.token) return
  loadingProfile.value = true
  profileError.value = null
  try {
    const response = await api.get('/profile')
    profile.value = response.data
    auth.userName = profile.value.name || ''
    auth.userRole = profile.value.role || ''
    localStorage.setItem('user_name', auth.userName)
    localStorage.setItem('user_role', auth.userRole)
  } catch (error) {
    profileError.value = error.response?.data?.message || 'Failed to fetch profile.'
    console.error('Failed to fetch profile:', error)
    if (error.response?.status === 401) {
      auth.logout()
      router.push('/login')
    }
  } finally {
    loadingProfile.value = false
  }
}

// Close dropdowns when clicking outside
// Checked against each dropdown's own wrapper (not the generic `.relative`/`button`
// selectors, which matched almost every element on the page and made most clicks
// outside fail to close anything).
function handleClickOutside(event) {
  if (languageDropdownRef.value && !languageDropdownRef.value.contains(event.target)) {
    languageDropdownOpen.value = false
  }
  if (profileDropdownRef.value && !profileDropdownRef.value.contains(event.target)) {
    profileDropdownOpen.value = false
  }
}

function navigateToProfile() {
  profileDropdownOpen.value = false
  router.push('/profile')
}

function navigateToMessages() {
  profileDropdownOpen.value = false
  router.push('/messages')
}

function handleLogout() {
  profileDropdownOpen.value = false
  auth.logout()
  router.push('/login')
}

const isLoggedIn = computed(() => auth.isAuthenticated)

const displayUserName = computed(() => profile.value.name || auth.userName || 'User')

watch(() => auth.isAuthenticated, (newVal) => {
  if (newVal) fetchUserProfile()
  else profile.value = {}
})

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  if (auth.isAuthenticated) fetchUserProfile()
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
})
</script>
