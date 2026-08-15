<template>
  <div class="min-h-screen overflow-x-hidden dark:bg-[#0a0e17]" :class="showLayout ? 'flex bg-gray-50' : 'bg-gray-50'">
    <!-- Layout only shown if NOT login/register -->
    <template v-if="showLayout">
      <!-- Sidebar for admin (hidden on the world map page); Sidebar.vue is itself fixed + styled -->
      <Sidebar v-if="auth.userRole === 'admin' && route.path !== '/world-map'" />

      <!-- Main content area including navbar and content -->
      <div
        class="flex-1 flex flex-col transition-all duration-200"
        :class="[
          auth.userRole === 'admin' && route.path !== '/world-map'
            ? (sidebar.collapsed ? 'md:ml-[108px]' : 'md:ml-72')
            : '',
          route.path === '/world-map' ? 'h-screen overflow-hidden' : 'min-h-screen',
        ]"
      >
        <!-- Navbar for non-admin users -->
        <div v-if="auth.userRole !== 'admin'" class="sticky top-0 z-30">
          <Navbar />
        </div>

        <!-- Admin topbar for admin -->
        <div v-if="auth.userRole === 'admin'" class="sticky top-0 z-30">
          <AdminTopBar />
        </div>

        <!-- Main content area -->
        <main class="flex-1 overflow-y-auto overflow-x-hidden" :class="route.path === '/world-map' ? 'p-0' : 'p-6'">
          <RouterView />
        </main>
      </div>
    </template>

    <!-- Login/Register pages -->
    <template v-else>
      <RouterView />
    </template>

    <!-- Always include contact form modal (hidden on the standalone world map page) -->
    <ContactView v-if="route.path !== '/world-map'" />
  </div>
</template>


<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/airQuality'
import { useThemeStore } from '@/stores/theme'
import { useSidebarStore } from '@/stores/sidebar'

import Sidebar from './components/Sidebar.vue'
import Navbar from './components/Navbar.vue'
import AdminTopBar from './components/AdminTopBar.vue'
import ContactView from './views/ContactView.vue'

const route = useRoute()
const auth = useAuthStore()
const sidebar = useSidebarStore()
useThemeStore().init()

const showLayout = computed(() => !['/login', '/register'].includes(route.path))
</script>
