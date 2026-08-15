<template>
  <nav
    class="navbar-enter w-full text-white flex items-center justify-between px-3 sm:px-8 py-3 relative z-50 bg-[#22272c]"
  >
    <!-- Left Section: Logo and Search -->
    <div class="flex items-center gap-8">
      <!-- Logo -->
      <div class="flex items-center gap-2 text-teal-300">
        <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-leaf-2" role="img" aria-label="Leaf">
          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
          <path d="M5 21c.5 -4.5 2.5 -8 7 -10" />
          <path d="M7.5 15q -3.5 0 -4.5 -6a8.4 8.4 0 0 1 3.438 .402a12 12 0 0 1 -.052 -.793c0 -3.606 3.204 -5.609 3.204 -5.609s2.003 1.252 2.842 3.557q 2.568 -1.557 6.568 -1.557q .396 3.775 -1.557 6.568c2.305 .839 3.557 2.842 3.557 2.842s-3 2.59 -7 2.59c0 1 0 1 .5 3q -6 0 -7 -5" />
        </svg>
        <span class="text-lg font-bold text-white whitespace-nowrap hidden sm:inline">Camair</span>
      </div>

      <!-- Enhanced Search Bar with Map Integration (hidden on the world map page — it has its own search) -->
      <div v-if="route.path !== '/world-map'" class="relative hidden lg:block">
        <CitySearch @location-selected="handleLocationSelected" />
      </div>
    </div>

    <!-- Center: Menu Links -->
    <div class="hidden md:flex items-center gap-1 rounded-xl border border-white/10 bg-white/5 p-1">
      <RouterLink
        :to="auth.userRole === 'admin' ? '/admin-dashboard' : '/home'"
        class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-sm font-medium transition-colors whitespace-nowrap"
        :class="[$route.path === '/home' || $route.path === '/admin-dashboard' ? 'bg-white/10 text-white' : 'text-gray-300 hover:bg-white/5 hover:text-white']"
      >
        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="flex-shrink-0">
          <rect x="0.5" y="0.5" width="5.42304" height="4.50546" rx="1.33515" stroke="currentColor" />
          <rect x="0.5" y="6.92535" width="5.42304" height="8.17576" rx="1.33515" stroke="currentColor" />
          <rect x="7.83904" y="0.5" width="7.25819" height="8.17576" rx="1.33515" stroke="currentColor" />
          <rect x="7.83904" y="10.59" width="7.25819" height="4.50546" rx="1.33515" stroke="currentColor" />
        </svg>
        {{ homeLabel }}
      </RouterLink>

      <RouterLink
        to="/compare-cities"
        class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-sm font-medium transition-colors whitespace-nowrap"
        :class="$route.path === '/compare-cities' ? 'bg-white/10 text-white' : 'text-gray-300 hover:bg-white/5 hover:text-white'"
      >
        <IconArrowsRightLeft :size="16" class="flex-shrink-0" />
        {{ $t('nav.compareCities') }}
      </RouterLink>

      <RouterLink
        to="/analytics"
        class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-sm font-medium transition-colors whitespace-nowrap"
        :class="$route.path === '/analytics' ? 'bg-white/10 text-white' : 'text-gray-300 hover:bg-white/5 hover:text-white'"
      >
        <IconChartBar :size="16" class="flex-shrink-0" />
        {{ $t('nav.analytics') }}
      </RouterLink>

      <RouterLink
        to="/user-news"
        class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-sm font-medium transition-colors whitespace-nowrap"
        :class="$route.path === '/user-news' ? 'bg-white/10 text-white' : 'text-gray-300 hover:bg-white/5 hover:text-white'"
      >
        <IconNews :size="16" class="flex-shrink-0" />
        {{ $t('nav.userNews') }}
      </RouterLink>
    </div>

    <!-- Right Section: Theme/Language/Login grouped with the mobile menu button so they sit
         together at the true right edge — three separate justify-between flex children would
         instead center this group with space on both sides, leaving it stranded away from the
         hamburger button on narrow screens. -->
    <div class="flex items-center gap-1 sm:gap-2">
    <div class="flex items-center gap-2 relative">

      <!-- Dark / light mode toggle -->
      <button
        @click="theme.toggle()"
        class="flex items-center justify-center w-9 h-9 rounded-full text-gray-200 hover:bg-white/10 transition-all duration-200"
        :title="theme.mode === 'dark' ? 'Switch to light mode' : 'Switch to dark mode'"
      >
        <IconSun v-if="theme.mode === 'dark'" :size="18" />
        <IconMoon v-else :size="18" />
      </button>

      <div class="relative" ref="languageDropdownRef">
        <button
          @click="toggleLanguageDropdown"
          class="flex items-center justify-center gap-2 h-9 px-2 sm:px-3 bg-white/5 rounded-lg text-gray-200 hover:bg-white/10 transition-all duration-200 border border-white/10 w-9 sm:w-[100px]"
          title="Change Language"
        >
          <IconGlobe :size="16" class="text-teal-300 flex-shrink-0" />
          <span class="hidden sm:inline text-xs font-medium select-none truncate">
            {{ currentLanguage === 'en' ? 'English' : 'ខ្មែរ' }}
          </span>
        </button>

        <div
          v-if="languageDropdownOpen"
          class="absolute right-0 mt-2 w-40 bg-[#1c222d] border border-[#242b36] rounded-2xl shadow-xl z-50 overflow-hidden"
        >
          <button
            @click="changeLanguage('en')"
            class="flex items-center gap-2.5 w-full px-4 py-2.5 text-left text-sm text-gray-200 hover:bg-white/5 hover:text-white transition-colors duration-200 border-b border-white/10 whitespace-nowrap"
          >
            <img src="https://flagcdn.com/w40/gb.png" alt="English" class="w-5 h-3.5 rounded-sm object-cover flex-shrink-0" />
            English
          </button>
          <button
            @click="changeLanguage('kh')"
            class="flex items-center gap-2.5 w-full px-4 py-2.5 text-left text-sm text-gray-200 hover:bg-white/5 hover:text-white transition-colors duration-200 whitespace-nowrap"
          >
            <img src="https://flagcdn.com/w40/kh.png" alt="Khmer" class="w-5 h-3.5 rounded-sm object-cover flex-shrink-0" />
            ភាសាខ្មែរ
          </button>
        </div>
      </div>

      <!-- Login / Profile -->
      <div v-if="!isLoggedIn">
        <button
          class="flex items-center justify-center gap-1.5 sm:gap-2 h-9 px-2.5 sm:px-3 bg-white/5 rounded-lg text-white hover:bg-white/10 transition-all duration-200 border border-white/10 w-auto sm:w-[90px]"
          @click="$router.push('/login')"
        >
          <IconLogin :size="16" class="text-teal-300 flex-shrink-0" />
          <span class="text-xs font-medium select-none truncate">
            {{ $t('auth.login') }}
          </span>
        </button>
      </div>

      <div v-else class="relative" ref="profileDropdownRef">
        <button
          v-if="loadingProfile"
          class="flex items-center gap-2 h-9 px-2.5 rounded-lg border border-white/10 min-w-[44px]"
          disabled
        >
          <Skeleton class="w-4 h-4 rounded-full flex-shrink-0 bg-white/10" />
          <Skeleton class="h-2.5 w-14 hidden sm:inline-block bg-white/10" />
        </button>
        <button
          v-else
          @click="toggleProfileDropdown"
          class="flex items-center justify-center gap-2 h-9 px-2.5 bg-white/5 rounded-lg text-white hover:bg-white/10 transition-all duration-200 border border-white/10 min-w-[44px] max-w-[150px]"
          title="$t('profile.title')"
        >
          <img
            v-if="profile.profile_image"
            :src="profile.profile_image"
            alt="Profile Image"
            class="w-4 h-4 rounded-full object-cover flex-shrink-0"
          />
          <IconUserCircle v-else :size="16" class="text-teal-300 flex-shrink-0" />

          <span class="text-xs font-medium select-none truncate hidden sm:inline">
            {{ displayUserName }}
          </span>
          <IconChevronDown
            :size="12"
            class="text-gray-300 flex-shrink-0 transition-transform duration-200 hidden sm:inline"
            :class="{ 'rotate-180': profileDropdownOpen }"
          />
        </button>

        <div
          v-if="profileDropdownOpen"
          class="absolute right-0 mt-2 w-56 bg-[#1c222d] border border-[#242b36] rounded-2xl shadow-xl z-50 overflow-hidden"
        >
          <div class="flex items-center gap-2.5 px-3 py-3 border-b border-white/10">
            <div v-if="loadingProfile" class="flex items-center gap-2.5 w-full">
              <Skeleton class="w-9 h-9 rounded-full flex-shrink-0 bg-white/10" />
              <div class="flex-1 space-y-2">
                <Skeleton class="h-3.5 w-20 bg-white/10" />
                <Skeleton class="h-3 w-28 bg-white/10" />
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
                class="w-9 h-9 rounded-full bg-white/5 flex items-center justify-center flex-shrink-0"
              >
                <IconUserCircle :size="20" class="text-gray-400" />
              </div>
              <div class="min-w-0">
                <p class="text-sm font-semibold text-white truncate">
                  {{ profile.name || $t('profile.user') }}
                  <span class="font-normal text-gray-400">({{ profile.role || $t('profile.roleNotSet') }})</span>
                </p>
                <p class="text-xs text-gray-400 truncate">{{ profile.email || $t('profile.noEmail') }}</p>
              </div>
            </template>
          </div>

          <div class="py-1">
            <button
              @click="navigateToProfile"
              class="flex items-center gap-3 w-full px-3 py-2.5 text-left text-xs font-medium text-gray-200 hover:bg-white/5 hover:text-white transition-colors duration-200 border-b border-white/10"
            >
              <IconUser :size="15" class="flex-shrink-0" />
              {{ $t('profile.profile') }}
            </button>

            <button
              @click="navigateToMessages"
              class="flex items-center gap-3 w-full px-3 py-2.5 text-left text-xs font-medium text-gray-200 hover:bg-white/5 hover:text-white transition-colors duration-200 border-b border-white/10"
            >
              <IconBrandHipchat :size="15" class="flex-shrink-0" />
              {{ $t('profile.messages') }}
            </button>

            <button
              @click="handleLogout"
              class="flex items-center gap-3 w-full px-3 py-2.5 text-left text-xs font-medium text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-colors duration-200"
            >
              <IconLogout :size="15" class="flex-shrink-0" />
              {{ $t('auth.logout') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Menu Button -->
    <button
      class="md:hidden p-2 rounded-lg hover:bg-white/10 transition-colors duration-200"
      @click="toggleMobileMenu"
    >
      <IconMenu2 :size="18" class="text-white" />
    </button>
    </div>

    <!-- Mobile Menu Overlay -->
    <div
      v-if="mobileMenuOpen"
      class="md:hidden absolute top-full left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-40"
    >
      <div class="p-4 space-y-2">
        <RouterLink
          to="/home"
          class="block px-4 py-2 rounded-lg hover:bg-blue-50 hover:text-blue-600"
          @click="mobileMenuOpen = false"
        >
          {{ $t('nav.home') }}
        </RouterLink>
        <RouterLink
          to="/city-detail"
          class="block px-4 py-2 rounded-lg hover:bg-blue-50 hover:text-blue-600"
          @click="mobileMenuOpen = false"
        >
          {{ $t('nav.cityDetail') }}
        </RouterLink>
        <RouterLink
          to="/compare-cities"
          class="block px-4 py-2 rounded-lg hover:bg-blue-50 hover:text-blue-600"
          @click="mobileMenuOpen = false"
        >
          {{ $t('nav.compareCities') }}
        </RouterLink>
        <RouterLink
          to="/analytics"
          class="block px-4 py-2 rounded-lg hover:bg-blue-50 hover:text-blue-600"
          @click="mobileMenuOpen = false"
        >
          {{ $t('nav.analytics') }}
        </RouterLink>

        <!-- Mobile Profile Section -->
        <div v-if="isLoggedIn" class="border-t border-gray-200 pt-2 mt-4">
          <div v-if="loadingProfile" class="px-4 py-2 space-y-1.5">
            <Skeleton class="h-3.5 w-28" />
            <Skeleton class="h-3 w-36" />
          </div>
          <div v-else class="px-4 py-2 text-sm text-gray-600">
            {{ profile.name || auth.userName || 'User' }}
            <span class="text-blue-600">({{ profile.role || auth.userRole || $t('profile.roleNotSet') }})</span>
            <p class="text-xs text-gray-500 truncate">  {{ profile.email || $t('profile.noEmail') }}</p>
          </div>
          <button
            @click="navigateToProfile(); mobileMenuOpen = false"
            class="block w-full text-left px-4 py-2 rounded-lg hover:bg-blue-50 hover:text-blue-600"
          >
              {{ $t('profile.profile') }}
          </button>
          <button
            @click="navigateToMessages(); mobileMenuOpen = false"
            class="block w-full text-left px-4 py-2 rounded-lg hover:bg-blue-50 hover:text-blue-600"
          >
          {{ $t('profile.messages') }}
          </button>
          <button
            @click="handleLogout(); mobileMenuOpen = false"
            class="block w-full text-left px-4 py-2 rounded-lg hover:bg-red-50 hover:text-red-600"
          >
            {{ $t('auth.logout') }}
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import CitySearch from '@/components/CitySearch.vue'
import { useAuthStore } from '@/stores/airQuality'
import { useThemeStore } from '@/stores/theme'
import { useI18n } from 'vue-i18n'
import api from '@/services/api'
import Skeleton from '@/components/Skeleton.vue'
import {
  IconGlobe,
  IconLogin,
  IconUserCircle,
  IconChevronDown,
  IconUser,
  IconBrandHipchat,
  IconLogout,
  IconMenu2,
  IconArrowsRightLeft,
  IconChartBar,
  IconNews,
  IconSun,
  IconMoon,
} from '@tabler/icons-vue'

const theme = useThemeStore()
const { locale, t } = useI18n()
const currentLanguage = ref(locale.value)
const languageDropdownOpen = ref(false)
const profileDropdownOpen = ref(false)
const mobileMenuOpen = ref(false)
const router = useRouter()
const route = useRoute()
const auth = useAuthStore()

const profile = ref({})
// Starts true (not false) whenever a token is already present, so the very first paint shows
// the skeleton instead of briefly flashing the real button with empty/fallback profile data
// before onMounted's fetchUserProfile() has a chance to run and flip it.
const loadingProfile = ref(!!auth.token)
const languageDropdownRef = ref(null)
const profileDropdownRef = ref(null)
const profileError = ref(null)

// Enhanced location selection handler with map integration
function handleLocationSelected(location) {
  // Store selected location for map component to pick up
  localStorage.setItem('selectedSearchLocation', JSON.stringify(location))
  
  // Navigate to home page if not already there
  if (route.path !== '/home' && route.path !== '/admin-dashboard') {
    router.push('/home')
  } else {
    // Trigger map update if already on home page
    window.dispatchEvent(new CustomEvent('location-search-selected', { 
      detail: location 
    }))
  }
}

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

function toggleMobileMenu() {
  mobileMenuOpen.value = !mobileMenuOpen.value
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

// Close dropdowns when clicking outside — checked against each dropdown's own wrapper
// (not the generic `.relative`/`button` selectors, which matched almost every element on
// the page and made most clicks fail to close anything).
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

const homeLabel = computed(() => {
  return auth.userRole === 'admin' ? t('nav.dashboard') : t('nav.home')
})

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

<style>
@import '@fortawesome/fontawesome-free/css/all.min.css';
</style>

<style scoped>
/* Entrance animation, ported from the pill-nav style */
@keyframes navbar-enter {
  from {
    opacity: 0;
    transform: translateY(-16px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.navbar-enter {
  animation: navbar-enter 0.6s ease-out;
}

/* Custom scrollbar for dropdown */
.overflow-hidden::-webkit-scrollbar {
  width: 4px;
}
.overflow-hidden::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 2px;
}

/* Fade transition */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}
.fade-enter,
.fade-leave-to {
  opacity: 0;
}

/* Ensure text truncation works */
.truncate {
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

/* Smooth rotation for chevron */
.transition-transform {
  transition: transform 0.2s ease-in-out;
}
.rotate-180 {
  transform: rotate(180deg);
}

/* Loading animation */
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.fa-spin {
  animation: spin 1s linear infinite;
}

/* Pulse animation for loading states */
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
.animate-pulse {
  animation: pulse 1.5s ease-in-out infinite;
}
</style>
