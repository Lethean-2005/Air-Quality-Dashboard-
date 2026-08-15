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
          {{
            activeTab === "profile" ? "My Account" :
            activeTab === "password" ? "Reset Password" : "Favourite Cities"
          }}
        </h1>
      </div>

      <div class="flex flex-col md:flex-row">
        <!-- Sidebar -->
        <div class="w-full md:w-64 p-4 border-b md:border-b-0 md:border-r border-gray-100">
          <nav class="space-y-1">
            <button
              @click="activeTab = 'profile'"
              class="flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
              :class="activeTab === 'profile' ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50'"
            >
              <span class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                  <IconUser :size="16" />
                </span>
                My Account
              </span>
              <IconChevronRight :size="16" class="text-gray-400" />
            </button>

            <button
              @click="activeTab = 'password'"
              class="flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
              :class="activeTab === 'password' ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50'"
            >
              <span class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                  <IconLock :size="16" />
                </span>
                Reset Password
              </span>
              <IconChevronRight :size="16" class="text-gray-400" />
            </button>

            <button
              v-if="form.role !== 'admin'"
              @click="activeTab = 'favourites'"
              class="flex items-center justify-between w-full px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
              :class="activeTab === 'favourites' ? 'bg-blue-50 text-blue-600' : 'text-gray-700 hover:bg-gray-50'"
            >
              <span class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                  <IconHeart :size="16" />
                </span>
                Favourites
              </span>
              <IconChevronRight :size="16" class="text-gray-400" />
            </button>
          </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6">
          <div v-if="loading" class="max-w-2xl">
            <div class="flex items-start gap-4 mb-6">
              <Skeleton class="w-16 h-16 rounded-full flex-shrink-0" />
              <div class="flex-1 min-w-0 space-y-2 pt-1">
                <Skeleton class="h-4 w-32" />
                <Skeleton class="h-3 w-40" />
                <Skeleton class="h-1.5 w-full max-w-xs rounded-full" />
              </div>
            </div>
            <div class="space-y-3">
              <Skeleton v-for="i in 4" :key="i" class="h-10 w-full rounded-lg" />
            </div>
          </div>
          <div v-else class="max-w-2xl">
            <div
              v-if="error"
              class="bg-red-50 text-red-600 text-sm p-3 rounded mb-4"
            >
              <ul v-if="error.errors">
                <li v-for="(errs, field) in error.errors" :key="field">
                  {{ field }}: {{ errs.join(", ") }}
                </li>
              </ul>
              <p v-else>{{ error.message || error }}</p>
            </div>

            <!-- Avatar + completion header -->
            <div class="flex items-start gap-4 mb-6">
              <div class="relative flex-shrink-0">
                <img
                  :src="form.profile_image_url || '/default-avatar.png'"
                  class="w-16 h-16 rounded-full object-cover border border-gray-200"
                  :alt="t('profile.profileImageAlt')"
                />
                <button
                  type="button"
                  @click="openFileInput"
                  class="absolute -bottom-1 -right-1 bg-blue-600 text-white p-1.5 rounded-full hover:bg-blue-700 border-2 border-white"
                  :title="t('profile.changeProfileImage')"
                >
                  <IconCamera :size="12" />
                </button>
                <input
                  type="file"
                  ref="fileInput"
                  class="hidden"
                  accept="image/*"
                  @change="handleImageUpload"
                />
              </div>
              <div class="flex-1 min-w-0">
                <h2 class="text-base font-bold text-gray-900 truncate">{{ form.name }}</h2>
                <p class="text-sm text-gray-500 truncate">{{ form.email }}</p>
                <div class="mt-2 w-full max-w-xs h-1.5 bg-gray-100 rounded-full overflow-hidden">
                  <div
                    class="h-full bg-orange-400 rounded-full transition-all duration-300"
                    :style="{ width: profileCompletion + '%' }"
                  ></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                  Complete your profile
                  <span class="text-orange-500 font-medium">{{ profileCompletion }}%</span>
                </p>
              </div>
            </div>

            <!-- Profile Information Form -->
            <form
              v-if="activeTab === 'profile'"
              @submit.prevent="updateProfile"
              class="space-y-3"
            >
              <div>
                <label class="block mb-1.5 text-sm font-medium text-gray-700">{{
                  t("profile.name")
                }}</label>
                <input
                  type="text"
                  v-model="form.name"
                  class="w-full px-4 py-2.5 bg-gray-100 border-0 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                  required
                />
              </div>
              <div>
                <label class="block mb-1.5 text-sm font-medium text-gray-700">{{
                  t("profile.email")
                }}</label>
                <input
                  type="email"
                  v-model="form.email"
                  class="w-full px-4 py-2.5 bg-gray-100 border-0 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                  required
                />
              </div>
              <div>
                <label class="block mb-1.5 text-sm font-medium text-gray-700">{{
                  t("profile.phone")
                }}</label>
                <input
                  type="text"
                  v-model="form.phone"
                  placeholder="Mobile Number"
                  class="w-full px-4 py-2.5 bg-gray-100 border-0 rounded-lg text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-400"
                />
              </div>
              <div>
                <label class="block mb-1.5 text-sm font-medium text-gray-700">{{
                  t("profile.bio")
                }}</label>
                <textarea
                  v-model="form.bio"
                  rows="3"
                  class="w-full px-4 py-2.5 bg-gray-100 border-0 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                ></textarea>
              </div>
              <div class="flex justify-end gap-3 pt-2">
                <button
                  type="button"
                  @click="resetForm"
                  class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                  {{ t("profile.cancel") }}
                </button>
                <button
                  type="submit"
                  :disabled="updating"
                  class="px-6 py-2 text-sm bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:bg-blue-300"
                >
                  <span v-if="updating">{{ t("profile.saving") }}</span>
                  <span v-else>{{ t("Save Changes") }}</span>
                </button>
              </div>
            </form>

            <!-- Password Reset Form -->
            <form
              v-if="activeTab === 'password'"
              @submit.prevent="updatePassword"
              class="space-y-3"
            >
              <div>
                <label class="block mb-1.5 text-sm font-medium text-gray-700">{{
                  t("profile.currentPassword")
                }}</label>
                <input
                  type="password"
                  v-model="passwordForm.current_password"
                  class="w-full px-4 py-2.5 bg-gray-100 border-0 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                  required
                />
              </div>
              <div>
                <label class="block mb-1.5 text-sm font-medium text-gray-700">{{
                  t("profile.newPassword")
                }}</label>
                <input
                  type="password"
                  v-model="passwordForm.new_password"
                  class="w-full px-4 py-2.5 bg-gray-100 border-0 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                  required
                />
              </div>
              <div>
                <label class="block mb-1.5 text-sm font-medium text-gray-700">{{
                  t("profile.confirmPassword")
                }}</label>
                <input
                  type="password"
                  v-model="passwordForm.new_password_confirmation"
                  class="w-full px-4 py-2.5 bg-gray-100 border-0 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                  required
                />
              </div>
              <div class="flex justify-end gap-3 pt-2">
                <button
                  type="button"
                  @click="resetPasswordForm"
                  class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                  {{ t("profile.cancel") }}
                </button>
                <button
                  type="submit"
                  :disabled="updatingPassword"
                  class="px-6 py-2 text-sm bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 disabled:bg-blue-300"
                >
                  <span v-if="updatingPassword">{{ t("profile.updating") }}</span>
                  <span v-else>{{ t("Update Password") }}</span>
                </button>
              </div>
            </form>

          <!-- Favourite Cities Tab - Enhanced -->
          <div
            v-if="activeTab === 'favourites'"
            class=""
          >
            <div v-if="favourites.length === 0" class="text-center py-10">
              <div class="text-orange-400 text-5xl mb-4">
                <i class="fas fa-star"></i>
              </div>
              <h3 class="text-lg font-medium text-gray-700 mb-2">No favourite cities yet</h3>
              <p class="text-gray-500 text-sm">Start adding cities to see them here</p>
            </div>
            <div v-else class="space-y-4">
              <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-medium text-gray-800">Your Favourite Cities</h2>
                <span class="bg-orange-100 text-orange-600 text-xs font-medium px-2.5 py-0.5 rounded-full">
                  {{ favourites.length }} {{ favourites.length === 1 ? 'city' : 'cities' }}
                </span>
              </div>
              <div class="grid grid-cols-1 gap-3">
                <div
                  v-for="city in favourites"
                  :key="city.id"
                  class="flex items-center justify-between p-4 bg-gray-50 hover:bg-orange-50 rounded-lg transition-all duration-200 border border-gray-100"
                >
                  <div class="flex items-center gap-3 cursor-pointer" @click="goToCityOnMap(city)">
                    <div class="relative flex-shrink-0">
                      <img
                        :src="city.flag_url"
                        :alt="`${city.name} flag`"
                        class="w-10 h-7 object-cover rounded-sm shadow-sm border"
                      />
                      <div class="absolute -bottom-1 -right-1 bg-white rounded-full p-0.5 shadow">
                        <i class="fas fa-star text-orange-400 text-xs"></i>
                      </div>
                    </div>
                    <div>
                      <span class="font-medium text-gray-800 block">{{ city.name }}</span>
                      <span class="text-xs text-gray-500">Click to view on map</span>
                    </div>
                  </div>
                  <div class="flex items-center gap-2">
                    <button
                      @click.stop="goToCityOnMap(city)"
                      class="text-gray-500 hover:text-orange-500 p-2 transition-colors"
                      title="View on map"
                    >
                      <i class="fas fa-map-marker-alt"></i>
                    </button>
                    <button
                      @click.stop="deleteFavourite(city)"
                      class="text-gray-400 hover:text-red-500 p-2 transition-colors"
                      title="Remove from favourites"
                    >
                      <i class="fas fa-trash-alt"></i>
                    </button>
                  </div>
                </div>
              </div>
              <div class="pt-4 mt-4 border-t border-gray-100 text-center">
                <p class="text-xs text-gray-500">
                  <i class="fas fa-info-circle mr-1"></i>
                  Click on any city to view it on the map
                </p>
              </div>
            </div>
          </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useI18n } from "vue-i18n";
import api from "@/services/api";
import Skeleton from "@/components/Skeleton.vue";
import { useAuthStore } from "@/stores/airQuality";
import { useRouter } from "vue-router";
import {
  IconChevronLeft,
  IconChevronRight,
  IconUser,
  IconLock,
  IconHeart,
  IconCamera,
} from "@tabler/icons-vue";

const { t } = useI18n();
const auth = useAuthStore();
const router = useRouter();
const loading = ref(true);
const updating = ref(false);
const updatingPassword = ref(false);
const error = ref(null);
const fileInput = ref(null);
const activeTab = ref("profile");
const favourites = ref([]);

const form = ref({
  name: "",
  email: "",
  phone: "",
  role: '', 
  profile_image: null,
  profile_image_url: "",
});

const passwordForm = ref({
  current_password: "",
  new_password: "",
  new_password_confirmation: "",
});

const fetchProfile = async () => {
  loading.value = true;
  error.value = null;
  try {
    const res = await api.get("/profile");
    form.value.name = res.data.name;
    form.value.email = res.data.email;
    form.value.phone = res.data.phone || "";
    form.value.role = res.data.role || '';
    form.value.profile_image_url = res.data.profile_image;
  } catch (err) {
    error.value = err.response?.data || { message: t("profile.fetchFailed") };
  } finally {
    loading.value = false;
  }
};

const profileCompletion = computed(() => {
  const fields = [form.value.name, form.value.email, form.value.phone, form.value.profile_image_url];
  const filled = fields.filter((f) => f && String(f).trim().length > 0).length;
  return Math.round((filled / fields.length) * 100);
});

const openFileInput = () => fileInput.value.click();

const handleImageUpload = (e) => {
  const file = e.target.files[0];
  if (file) {
    if (!file.type.startsWith('image/')) {
      error.value = { message: "Please select an image file" };
      return;
    }
    if (file.size > 5 * 1024 * 1024) {
      error.value = { message: "Image size should be less than 5MB" };
      return;
    }
    form.value.profile_image = file;
    form.value.profile_image_url = URL.createObjectURL(file);
  }
};

const updateProfile = async () => {
  updating.value = true;
  error.value = null;
  const formData = new FormData();
  formData.append("name", form.value.name);
  formData.append("email", form.value.email);
  if (form.value.phone) formData.append("phone", form.value.phone);
  if (form.value.bio) formData.append("bio", form.value.bio);
  if (form.value.profile_image) {
    formData.append("profile_image", form.value.profile_image);
  }
  try {
    const res = await api.post("/profile/update", formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    auth.userName = res.data.user.name;
    auth.userEmail = res.data.user.email;
    auth.userProfileImage = res.data.user.profile_image || "";
    alert(t("profile.updateSuccess"));
  } catch (err) {
    error.value = err.response?.data?.errors
      ? err.response.data
      : { message: err.response?.data?.message || t("profile.updateFailed") };
  } finally {
    updating.value = false;
  }
};

const updatePassword = async () => {
  updatingPassword.value = true;
  error.value = null;
  if (passwordForm.value.new_password !== passwordForm.value.new_password_confirmation) {
    error.value = { message: "New passwords do not match" };
    updatingPassword.value = false;
    return;
  }
  try {
    await api.post("/profile/update-password", passwordForm.value);
    alert(t("profile.passwordUpdateSuccess"));
    resetPasswordForm();
  } catch (err) {
    error.value = err.response?.data || { message: t("profile.updateFailed") };
  } finally {
    updatingPassword.value = false;
  }
};

const resetForm = () => { fetchProfile(); };

const resetPasswordForm = () => {
  passwordForm.value = {
    current_password: "",
    new_password: "",
    new_password_confirmation: "",
  };
};

const getCityCoordinates = (cityName, countryCode) => {
  const cityCoords = {
    'phnom penh': { lat: 11.5564, lon: 104.9282 },
    'new york': { lat: 40.7128, lon: -74.0060 },
    'london': { lat: 51.5074, lon: -0.1278 },
    'paris': { lat: 48.8566, lon: 2.3522 },
    'tokyo': { lat: 35.6762, lon: 139.6503 },
    'beijing': { lat: 39.9042, lon: 116.4074 },
    'moscow': { lat: 55.7558, lon: 37.6173 },
    'cairo': { lat: 30.0444, lon: 31.2357 },
    'sydney': { lat: -33.8688, lon: 151.2093 },
    'rio de janeiro': { lat: -22.9068, lon: -43.1729 },
    'bangkok': { lat: 13.7563, lon: 100.5018 },
    'singapore': { lat: 1.3521, lon: 103.8198 },
    'kuala lumpur': { lat: 3.1390, lon: 101.6869 },
    'jakarta': { lat: -6.2088, lon: 106.8456 },
    'manila': { lat: 14.5995, lon: 120.9842 },
    'hanoi': { lat: 21.0278, lon: 105.8342 },
    'ho chi minh city': { lat: 10.8231, lon: 106.6297 },
    'seoul': { lat: 37.5665, lon: 126.9780 },
    'delhi': { lat: 28.6139, lon: 77.2090 },
    'mumbai': { lat: 19.0760, lon: 72.8777 },
  };
  const normalizedName = cityName.toLowerCase();
  return cityCoords[normalizedName] || {
    lat: getApproximateLat(countryCode),
    lon: getApproximateLon(countryCode)
  };
};

const getApproximateLat = (countryCode) => {
  const countryCoords = {
    'US': 39.8283, 'GB': 54.7023, 'FR': 46.6033, 'DE': 51.1657, 'IT': 41.8719,
    'ES': 40.4637, 'CN': 35.8617, 'IN': 20.5937, 'JP': 36.2048, 'BR': -14.2350,
    'RU': 61.5240, 'CA': 56.1304, 'AU': -25.2744, 'KH': 12.5657, 'TH': 15.8700,
    'SG': 1.3521, 'MY': 4.2105, 'ID': -0.7893, 'PH': 12.8797, 'VN': 14.0583,
    'KR': 35.9078, 'MM': 21.9162, 'LA': 19.8563, 'BD': 23.6850, 'NP': 28.3949,
  };
  return countryCoords[countryCode] || 0;
};

const getApproximateLon = (countryCode) => {
  const countryCoords = {
    'US': -98.5795, 'GB': -3.2766, 'FR': 1.8883, 'DE': 10.4515, 'IT': 12.5674,
    'ES': -3.7492, 'CN': 104.1954, 'IN': 78.9629, 'JP': 138.2529, 'BR': -51.9253,
    'RU': 105.3188, 'CA': -106.3468, 'AU': 133.7751, 'KH': 104.9910, 'TH': 100.9925,
    'SG': 103.8198, 'MY': 101.9758, 'ID': 113.9213, 'PH': 121.7740, 'VN': 108.2772,
    'KR': 127.7669, 'MM': 95.9560, 'LA': 102.4955, 'BD': 90.3563, 'NP': 84.1240,
  };
  return countryCoords[countryCode] || 0;
};

const fetchFavourites = async () => {
  try {
    const res = await api.get("/favourites");
    favourites.value = res.data.map((city) => {
      const coords = getCityCoordinates(city.city_name, city.country_code);
      return {
        id: city.id,
        name: city.city_name,
        country_code: city.country_code,
        lat: coords.lat,
        lon: coords.lon,
        flag_url: `https://flagcdn.com/w40/${city.country_code.toLowerCase()}.png`,
      };
    });
  } catch (err) {
    console.error("Failed to fetch favourites", err);
    error.value = { message: "Failed to load favourites" };
  }
};

const goToCityOnMap = (city) => {
  // Store the city data to be used on the home page
  localStorage.setItem('selectedFavoriteCity', JSON.stringify({
    id: city.id,
    name: city.name,
    country_code: city.country_code,
    lat: city.lat,
    lon: city.lon
  }));
  // Navigate to home page
  router.push({ name: 'home' });
};

const deleteFavourite = async (city) => {
  if (!confirm(`Remove ${city.name} from favourites?`)) return;
  try {
    await api.delete(`/favourites/${city.id}`);
    favourites.value = favourites.value.filter(f => f.id !== city.id);
  } catch (err) {
    console.error("Failed to delete favourite:", err);
    error.value = { message: "Failed to remove favourite" };
  }
};

onMounted(() => {
  fetchProfile();
  fetchFavourites();
});
</script>

<style scoped>
input[type="file"] {
  display: none;
}
</style>