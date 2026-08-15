<template>
  <!-- Mobile backdrop: tap outside to close the drawer -->
  <div
    v-if="sidebar.mobileOpen"
    class="fixed inset-0 bg-black/40 z-40 md:hidden"
    @click="sidebar.closeMobile()"
  ></div>

  <aside
    class="fixed top-4 left-4 bottom-4 bg-white text-gray-900 rounded-2xl shadow-[0_0_24px_rgba(0,0,0,0.06)] z-50 flex-col p-4 transition-all duration-200"
    :class="[
      sidebar.mobileOpen ? 'flex' : 'hidden md:flex',
      sidebar.collapsed ? 'w-[76px]' : 'w-[76px] md:w-64',
    ]"
  >
    <!-- Logo + collapse/close toggle -->
    <div
      class="flex items-center gap-2.5 px-2 pt-2 pb-4"
      :class="sidebar.collapsed ? 'flex-col' : 'flex-col md:flex-row'"
    >
      <div class="flex items-center gap-2.5 min-w-0" :class="sidebar.collapsed ? '' : 'md:flex-1'">
        <div class="w-8 h-8 rounded-lg bg-teal-600 flex items-center justify-center flex-shrink-0">
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" role="img" aria-label="Leaf">
            <path d="M5 21c.5 -4.5 2.5 -8 7 -10" />
            <path d="M7.5 15q -3.5 0 -4.5 -6a8.4 8.4 0 0 1 3.438 .402a12 12 0 0 1 -.052 -.793c0 -3.606 3.204 -5.609 3.204 -5.609s2.003 1.252 2.842 3.557q 2.568 -1.557 6.568 -1.557q .396 3.775 -1.557 6.568c2.305 .839 3.557 2.842 3.557 2.842s-3 2.59 -7 2.59c0 1 0 1 .5 3q -6 0 -7 -5" />
          </svg>
        </div>
        <span class="font-bold text-gray-900 text-base truncate" :class="sidebar.collapsed ? 'hidden' : 'hidden md:inline'">Air Quality</span>
      </div>

      <!-- Desktop: collapse/expand -->
      <button
        @click="sidebar.toggle()"
        :title="sidebar.collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
        class="hidden md:flex items-center justify-center w-6 h-6 rounded-full border border-gray-200 bg-white text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-all flex-shrink-0"
        :class="sidebar.collapsed ? 'mt-2' : ''"
      >
        <IconChevronLeft :size="14" :class="['transition-transform duration-200', sidebar.collapsed ? 'rotate-180' : '']" />
      </button>

      <!-- Mobile: close drawer -->
      <button
        @click="sidebar.closeMobile()"
        title="Close menu"
        class="flex md:hidden items-center justify-center w-7 h-7 rounded-full border border-gray-200 bg-white text-gray-400 hover:text-gray-600 hover:bg-gray-50 transition-all flex-shrink-0 mt-2"
      >
        <IconX :size="15" />
      </button>
    </div>

    <!-- Main Navigation -->
    <nav class="flex-1 overflow-y-auto space-y-1.5 text-[13px] font-medium">
      <RouterLink
        v-for="item in navItems"
        :key="item.to"
        :to="item.to"
        :title="item.label"
        class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors duration-150"
        :class="[isActive(item) ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50', sidebar.collapsed ? 'justify-center' : 'justify-center md:justify-start']"
        @click="sidebar.closeMobile()"
      >
        <component :is="item.icon" class="w-[15px] h-[15px] flex-shrink-0" />
        <span class="truncate" :class="sidebar.collapsed ? 'hidden' : 'hidden md:inline'">{{ item.label }}</span>
      </RouterLink>
    </nav>
  </aside>
</template>

<script setup>
import { computed } from "vue";
import { useRoute } from "vue-router";
import { useI18n } from "vue-i18n";
import { useAuthStore } from "@/stores/airQuality";
import { useSidebarStore } from "@/stores/sidebar";
import {
  IconHome,
  IconBuildingSkyscraper,
  IconTreadmill,
  IconRadio,
  IconBotId,
  IconChevronLeft,
  IconX,
} from "@tabler/icons-vue";

const auth = useAuthStore();
const route = useRoute();
const { t } = useI18n();
const sidebar = useSidebarStore();

const navItems = computed(() => [
  {
    to: auth.userRole === "admin" ? "/admin-dashboard" : "/home",
    label: auth.userRole === "admin" ? t("nav.dashboard") : t("nav.home"),
    icon: IconHome,
    matches: ["/home", "/admin-dashboard"],
  },
  {
    to: auth.userRole === "admin" ? "/cityaqi" : "/city-detail",
    label: auth.userRole === "admin" ? t("nav.cityAQI") : t("nav.cityDetail"),
    icon: IconBuildingSkyscraper,
    matches: ["/city-detail", "/cityaqi"],
  },
  {
    to: "/health-alert",
    label: t("nav.healthAlert"),
    icon: IconTreadmill,
    matches: ["/health-alert"],
  },
  {
    to: "/admin-news",
    label: t("nav.adminNews"),
    icon: IconRadio,
    matches: ["/admin-news"],
  },
  {
    to: "/user-management",
    label: t("nav.userManagement"),
    icon: IconBotId,
    matches: ["/user-management"],
  },
]);

const isActive = (item) => item.matches.includes(route.path);
</script>
