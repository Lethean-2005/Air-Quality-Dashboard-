<template>
  <div class="min-h-screen bg-gray-100 p-4 md:p-8">
    <div class="max-w-6xl mx-auto bg-white rounded-2xl shadow-sm overflow-hidden">
      <!-- Top bar -->
      <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-100">
        <button
          @click="$router.back()"
          class="text-gray-500 hover:text-gray-800 transition-colors"
          title="Back"
        >
          <IconChevronLeft :size="22" />
        </button>
        <h1 class="text-xl font-bold text-gray-900">
          {{ tabs.find((t) => t.id === activeTab)?.name }}
        </h1>
      </div>

      <div class="flex flex-col md:flex-row">
        <!-- Sidebar -->
        <div class="w-full md:w-64 p-4 border-b md:border-b-0 md:border-r border-gray-100">
          <nav class="space-y-1">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              class="flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
              :class="activeTab === tab.id ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50'"
            >
              <span class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                  <component :is="tab.icon" :size="16" />
                </span>
                {{ tab.name }}
              </span>
              <IconChevronRight :size="16" class="text-gray-400" />
            </button>
          </nav>
        </div>

        <!-- Content Area -->
        <div class="flex-1 p-6">
          <div class="max-w-2xl">
            <!-- General Settings -->
            <form v-if="activeTab === 'general'" @submit.prevent="saveGeneralSettings" class="space-y-3">
              <div>
                <label class="block mb-1.5 text-sm font-medium text-gray-700">Language</label>
                <select v-model="settings.language" class="w-full px-4 py-2.5 bg-gray-100 border-0 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                  <option value="en">English</option>
                  <option value="kh">ភាសាខ្មែរ (Khmer)</option>
                </select>
              </div>
              <div>
                <label class="block mb-1.5 text-sm font-medium text-gray-700">Time Zone</label>
                <select v-model="settings.timezone" class="w-full px-4 py-2.5 bg-gray-100 border-0 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                  <option value="UTC+7">UTC+7 (Cambodia)</option>
                  <option value="UTC+0">UTC+0 (GMT)</option>
                  <option value="UTC-5">UTC-5 (EST)</option>
                </select>
              </div>
              <div>
                <label class="block mb-1.5 text-sm font-medium text-gray-700">Date Format</label>
                <select v-model="settings.dateFormat" class="w-full px-4 py-2.5 bg-gray-100 border-0 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                  <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                  <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                  <option value="YYYY-MM-DD">YYYY-MM-DD</option>
                </select>
              </div>
              <div class="flex justify-end pt-2">
                <button type="submit" class="px-6 py-2 text-sm bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700">
                  Save Changes
                </button>
              </div>
            </form>

            <!-- Security Settings -->
            <div v-if="activeTab === 'security'" class="space-y-6">
              <form @submit.prevent="changePassword" class="space-y-3">
                <h3 class="text-sm font-semibold text-gray-900">Change Password</h3>
                <div>
                  <label class="block mb-1.5 text-sm font-medium text-gray-700">Current Password</label>
                  <input
                    v-model="passwordForm.currentPassword"
                    type="password"
                    class="w-full px-4 py-2.5 bg-gray-100 border-0 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="Enter current password"
                  />
                </div>
                <div>
                  <label class="block mb-1.5 text-sm font-medium text-gray-700">New Password</label>
                  <input
                    v-model="passwordForm.newPassword"
                    type="password"
                    class="w-full px-4 py-2.5 bg-gray-100 border-0 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="Enter new password"
                  />
                </div>
                <div>
                  <label class="block mb-1.5 text-sm font-medium text-gray-700">Confirm New Password</label>
                  <input
                    v-model="passwordForm.confirmPassword"
                    type="password"
                    class="w-full px-4 py-2.5 bg-gray-100 border-0 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                    placeholder="Confirm new password"
                  />
                </div>
                <div class="flex justify-end pt-2">
                  <button type="submit" class="px-6 py-2 text-sm bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700">
                    Update Password
                  </button>
                </div>
              </form>

              <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                <div>
                  <h3 class="text-sm font-semibold text-gray-900">Two-Factor Authentication</h3>
                  <p class="text-gray-500 text-xs mt-0.5">Add an extra layer of security to your account</p>
                </div>
                <button
                  @click="toggle2FA"
                  class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors flex-shrink-0"
                  :class="settings.twoFactorEnabled ? 'bg-blue-600' : 'bg-gray-200'"
                >
                  <span
                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                    :class="settings.twoFactorEnabled ? 'translate-x-6' : 'translate-x-1'"
                  />
                </button>
              </div>
            </div>

            <!-- Notifications -->
            <div v-if="activeTab === 'notifications'" class="space-y-1">
              <div v-for="notification in notificationSettings" :key="notification.id" class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
                <div>
                  <h3 class="text-sm font-medium text-gray-900">{{ notification.title }}</h3>
                  <p class="text-xs text-gray-500 mt-0.5">{{ notification.description }}</p>
                </div>
                <button
                  @click="toggleNotification(notification.id)"
                  class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors flex-shrink-0"
                  :class="notification.enabled ? 'bg-blue-600' : 'bg-gray-200'"
                >
                  <span
                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                    :class="notification.enabled ? 'translate-x-6' : 'translate-x-1'"
                  />
                </button>
              </div>
              <div class="flex justify-end pt-4">
                <button @click="saveNotificationSettings" class="px-6 py-2 text-sm bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700">
                  Save Preferences
                </button>
              </div>
            </div>

            <!-- Privacy Settings -->
            <div v-if="activeTab === 'privacy'" class="space-y-1">
              <div v-for="privacy in privacySettings" :key="privacy.id" class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
                <div>
                  <h3 class="text-sm font-medium text-gray-900">{{ privacy.title }}</h3>
                  <p class="text-xs text-gray-500 mt-0.5">{{ privacy.description }}</p>
                </div>
                <button
                  @click="togglePrivacy(privacy.id)"
                  class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors flex-shrink-0"
                  :class="privacy.enabled ? 'bg-blue-600' : 'bg-gray-200'"
                >
                  <span
                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform"
                    :class="privacy.enabled ? 'translate-x-6' : 'translate-x-1'"
                  />
                </button>
              </div>
              <div class="flex justify-end pt-4">
                <button @click="savePrivacySettings" class="px-6 py-2 text-sm bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700">
                  Save Settings
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import {
  IconChevronLeft,
  IconChevronRight,
  IconSettings,
  IconShieldCheck,
  IconBell,
  IconEyeOff,
} from '@tabler/icons-vue'

const route = useRoute()
const activeTab = ref('general')

const tabs = [
  { id: 'general', name: 'General', icon: IconSettings },
  { id: 'security', name: 'Security', icon: IconShieldCheck },
  { id: 'notifications', name: 'Notifications', icon: IconBell },
  { id: 'privacy', name: 'Privacy', icon: IconEyeOff }
]

const settings = reactive({
  language: 'en',
  timezone: 'UTC+7',
  dateFormat: 'MM/DD/YYYY',
  twoFactorEnabled: false
})

const passwordForm = reactive({
  currentPassword: '',
  newPassword: '',
  confirmPassword: ''
})

const notificationSettings = ref([
  {
    id: 'email_alerts',
    title: 'Email Alerts',
    description: 'Receive email notifications for important updates',
    enabled: true
  },
  {
    id: 'push_notifications',
    title: 'Push Notifications',
    description: 'Receive push notifications on your device',
    enabled: false
  },
  {
    id: 'sms_alerts',
    title: 'SMS Alerts',
    description: 'Receive SMS notifications for critical alerts',
    enabled: false
  }
])

const privacySettings = ref([
  {
    id: 'profile_visibility',
    title: 'Profile Visibility',
    description: 'Make your profile visible to other users',
    enabled: true
  },
  {
    id: 'data_sharing',
    title: 'Data Sharing',
    description: 'Allow sharing of anonymized data for research',
    enabled: false
  },
  {
    id: 'activity_tracking',
    title: 'Activity Tracking',
    description: 'Track your activity for personalized experience',
    enabled: true
  }
])

function saveGeneralSettings() {
  console.log('Saving general settings:', settings)
  // API call to save settings
}

function changePassword() {
  if (passwordForm.newPassword !== passwordForm.confirmPassword) {
    alert('Passwords do not match')
    return
  }
  console.log('Changing password')
  // API call to change password
}

function toggle2FA() {
  settings.twoFactorEnabled = !settings.twoFactorEnabled
}

function toggleNotification(id) {
  const notification = notificationSettings.value.find(n => n.id === id)
  if (notification) {
    notification.enabled = !notification.enabled
  }
}

function togglePrivacy(id) {
  const privacy = privacySettings.value.find(p => p.id === id)
  if (privacy) {
    privacy.enabled = !privacy.enabled
  }
}

function saveNotificationSettings() {
  console.log('Saving notification settings:', notificationSettings.value)
}

function savePrivacySettings() {
  console.log('Saving privacy settings:', privacySettings.value)
}

onMounted(() => {
  if (route.query.tab) {
    activeTab.value = route.query.tab
  }
})
</script>
