<template>
  <div class="relative w-96">
    <!-- Search Input with Icons -->
    <div class="relative">
      <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
      <input
        v-model="search"
        @input="searchLocations"
        type="text"
        :placeholder="t('search.placeholder')"
        class="text-gray-200 placeholder-gray-400 pl-9 pr-14 py-1.5 rounded-lg w-full bg-[#2b3138] border border-white/5 focus:outline-none focus:ring-1 focus:ring-blue-400 focus:border-blue-400 transition text-xs h-9"
      />
      <button
        v-if="search"
        @click="clearSearch"
        class="absolute right-8 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-200 transition"
        title="Clear"
      >
        <i class="fas fa-times text-xs"></i>
      </button>
      <i
        class="fas fa-crosshairs absolute right-3 top-1/2 -translate-y-1/2 text-[#4ba9ff] text-xs cursor-pointer hover:text-[#7cc4ff] transition"
        :title="t('search.locateMe')"
        @click="locateUser"
      ></i>
    </div>

    <!-- Autocomplete Dropdown: grouped by Cities / Stations -->
    <div v-if="searchResults.length && search" class="absolute z-50 bg-[#2b3138] w-full mt-2 rounded-lg shadow-lg max-h-96 overflow-y-auto p-2">
      <div v-if="loading" class="p-3 text-center text-gray-400 text-sm">
        <i class="fas fa-spinner fa-spin mr-2"></i>
        Searching globally...
      </div>
      <template v-else>
        <div v-if="cityResults.length">
          <p class="px-2 pt-1 pb-2 text-xs font-semibold text-gray-400">Cities</p>
          <div
            v-for="location in cityResults"
            :key="'city-' + location.full_name"
            @click="selectLocation(location)"
            class="group flex items-center justify-between gap-3 px-2 py-2 rounded-sm cursor-pointer hover:bg-white/5 transition-colors"
          >
            <div class="flex items-center gap-3 min-w-0">
              <img :src="flagUrl(location.country)" :alt="location.country" class="w-6 h-4 rounded-sm object-cover flex-shrink-0 border border-white/10" />
              <span class="text-sm text-white truncate">{{ location.full_name }}</span>
            </div>
            <span
              v-if="location.aqi"
              class="flex-shrink-0 inline-flex items-center justify-center min-w-[2.25rem] px-1.5 py-1 rounded-sm text-xs font-medium text-white text-center transition-transform group-hover:scale-105"
              :style="{ backgroundColor: getAQIColor(location.aqi) }"
            >
              {{ location.aqi }}
            </span>
          </div>
        </div>

        <div v-if="stationResults.length" class="mt-2">
          <p class="px-2 pt-1 pb-2 text-xs font-semibold text-gray-400">Stations</p>
          <div
            v-for="location in stationResults"
            :key="'station-' + location.full_name"
            @click="selectLocation(location)"
            class="group flex items-center justify-between gap-3 px-2 py-2 rounded-sm cursor-pointer hover:bg-white/5 transition-colors"
          >
            <div class="flex items-center gap-3 min-w-0">
              <img :src="flagUrl(location.country)" :alt="location.country" class="w-6 h-4 rounded-sm object-cover flex-shrink-0 border border-white/10" />
              <span class="text-sm text-white truncate">{{ location.full_name }}</span>
            </div>
            <span
              v-if="location.aqi"
              class="flex-shrink-0 inline-flex items-center justify-center min-w-[2.25rem] px-1.5 py-1 rounded-sm text-xs font-medium text-white text-center transition-transform group-hover:scale-105"
              :style="{ backgroundColor: getAQIColor(location.aqi) }"
            >
              {{ location.aqi }}
            </span>
          </div>
        </div>
      </template>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import axios from 'axios'
import { API_ROOT } from '@/services/api.js'

const { t } = useI18n()

// Component events
const emit = defineEmits(['location-selected'])

// Reactive state
const search = ref('')
const searchResults = ref([])
const loading = ref(false)
let searchTimeout = null

// The backend doesn't distinguish "city" vs "station" results (every result comes from
// a single WAQI monitoring station's name string) — approximate the reference UI's two
// groups using the match `type` the backend already computes: a query that matched the
// location/country name at its start reads as a "city" match, everything else as a
// broader "station" match.
const cityResults = computed(() =>
  searchResults.value.filter((l) => l.type === 'city' || l.type === 'country')
)
const stationResults = computed(() =>
  searchResults.value.filter((l) => l.type !== 'city' && l.type !== 'country')
)

// AQI Color mapping for visual indicators
const getAQIColor = (aqi) => {
  const val = parseFloat(aqi)
  if (isNaN(val)) return '#999'
  if (val <= 50) return '#00e400'
  if (val <= 100) return '#FFEB3B'
  if (val <= 150) return '#ff7e00'
  if (val <= 200) return '#ff0000'
  if (val <= 300) return '#99004c'
  return '#7e0023'
}

// Country name -> ISO 3166-1 alpha-2 code, for flagcdn.com flag images.
const countryCodeMap = {
  afghanistan: 'af', albania: 'al', algeria: 'dz', andorra: 'ad', angola: 'ao',
  argentina: 'ar', armenia: 'am', australia: 'au', austria: 'at', azerbaijan: 'az',
  bahamas: 'bs', bahrain: 'bh', bangladesh: 'bd', belarus: 'by', belgium: 'be',
  belize: 'bz', benin: 'bj', bhutan: 'bt', bolivia: 'bo', bosnia: 'ba',
  botswana: 'bw', brazil: 'br', brunei: 'bn', bulgaria: 'bg', cambodia: 'kh',
  cameroon: 'cm', canada: 'ca', chad: 'td', chile: 'cl', china: 'cn',
  colombia: 'co', congo: 'cg', 'costa rica': 'cr', croatia: 'hr', cuba: 'cu',
  cyprus: 'cy', czechia: 'cz', 'czech republic': 'cz', denmark: 'dk', djibouti: 'dj',
  dominica: 'dm', 'dominican republic': 'do', ecuador: 'ec', egypt: 'eg', 'el salvador': 'sv',
  estonia: 'ee', ethiopia: 'et', fiji: 'fj', finland: 'fi', france: 'fr',
  gabon: 'ga', gambia: 'gm', georgia: 'ge', germany: 'de', ghana: 'gh',
  greece: 'gr', greenland: 'gl', guatemala: 'gt', guinea: 'gn', guyana: 'gy',
  haiti: 'ht', honduras: 'hn', 'hong kong': 'hk', hungary: 'hu', iceland: 'is',
  india: 'in', indonesia: 'id', iran: 'ir', iraq: 'iq', ireland: 'ie',
  israel: 'il', italy: 'it', jamaica: 'jm', japan: 'jp', jordan: 'jo',
  kazakhstan: 'kz', kenya: 'ke', kuwait: 'kw', kyrgyzstan: 'kg', laos: 'la',
  latvia: 'lv', lebanon: 'lb', lesotho: 'ls', liberia: 'lr', libya: 'ly',
  liechtenstein: 'li', lithuania: 'lt', luxembourg: 'lu', macau: 'mo', madagascar: 'mg',
  malawi: 'mw', malaysia: 'my', maldives: 'mv', mali: 'ml', malta: 'mt',
  mauritania: 'mr', mauritius: 'mu', mexico: 'mx', moldova: 'md', monaco: 'mc',
  mongolia: 'mn', montenegro: 'me', morocco: 'ma', mozambique: 'mz', myanmar: 'mm',
  namibia: 'na', nepal: 'np', netherlands: 'nl', 'new zealand': 'nz', nicaragua: 'ni',
  niger: 'ne', nigeria: 'ng', 'north korea': 'kp', 'north macedonia': 'mk', norway: 'no',
  oman: 'om', pakistan: 'pk', palestine: 'ps', panama: 'pa', 'papua new guinea': 'pg',
  paraguay: 'py', peru: 'pe', philippines: 'ph', poland: 'pl', portugal: 'pt',
  qatar: 'qa', romania: 'ro', russia: 'ru', rwanda: 'rw', 'saudi arabia': 'sa',
  senegal: 'sn', serbia: 'rs', 'sierra leone': 'sl', singapore: 'sg', slovakia: 'sk',
  slovenia: 'si', somalia: 'so', 'south africa': 'za', 'south korea': 'kr', 'south sudan': 'ss',
  spain: 'es', 'sri lanka': 'lk', sudan: 'sd', suriname: 'sr', sweden: 'se',
  switzerland: 'ch', syria: 'sy', taiwan: 'tw', tajikistan: 'tj', tanzania: 'tz',
  thailand: 'th', togo: 'tg', 'trinidad and tobago': 'tt', tunisia: 'tn', turkey: 'tr',
  turkmenistan: 'tm', uganda: 'ug', ukraine: 'ua', 'united arab emirates': 'ae', uae: 'ae',
  'united kingdom': 'gb', uk: 'gb', 'united states': 'us', 'united states of america': 'us', usa: 'us',
  uruguay: 'uy', uzbekistan: 'uz', vanuatu: 'vu', 'vatican city': 'va', venezuela: 've',
  vietnam: 'vn', yemen: 'ye', zambia: 'zm', zimbabwe: 'zw',
}

// Builds a flagcdn.com URL from a free-text country name; falls back to a blank/unknown flag.
const flagUrl = (country) => {
  const key = (country || '').toLowerCase().trim()
  const code = countryCodeMap[key] || 'xx'
  return `https://flagcdn.com/w40/${code}.png`
}

// Search function with debouncing
function searchLocations() {
  clearTimeout(searchTimeout)

  if (search.value.length < 2) {
    searchResults.value = []
    return
  }

  loading.value = true

  // Debounce search by 300ms for better UX
  searchTimeout = setTimeout(async () => {
    try {
      const response = await axios.get(`${API_ROOT}/api/search-locations?q=${encodeURIComponent(search.value)}`)
      if (response.data.status === 'ok') {
        searchResults.value = response.data.data || []
      }
    } catch (error) {
      console.error('Search failed:', error)
      searchResults.value = []
    } finally {
      loading.value = false
    }
  }, 300)
}

function clearSearch() {
  search.value = ''
  searchResults.value = []
}

// Handle location selection
function selectLocation(location) {
  search.value = location.name
  searchResults.value = []
  emit('location-selected', location)
}

// Geolocation functionality — caches the last known position so repeat
// clicks don't re-prompt/re-fetch every time within CACHE_TTL_MS.
const LOCATION_CACHE_KEY = 'cached_user_location'
const CACHE_TTL_MS = 15 * 60 * 1000 // 15 minutes

function readCachedLocation() {
  try {
    const raw = localStorage.getItem(LOCATION_CACHE_KEY)
    if (!raw) return null
    const cached = JSON.parse(raw)
    if (Date.now() - cached.timestamp > CACHE_TTL_MS) return null
    return cached
  } catch {
    return null
  }
}

function writeCachedLocation(lat, lon) {
  localStorage.setItem(
    LOCATION_CACHE_KEY,
    JSON.stringify({ lat, lon, timestamp: Date.now() })
  )
}

function useLocation(lat, lon) {
  selectLocation({
    name: 'Your Location',
    full_name: 'Your Current Location',
    country: 'Current Position',
    lat,
    lon,
    aqi: null,
    type: 'geolocation',
  })
}

function locateUser() {
  const cached = readCachedLocation()
  if (cached) {
    useLocation(cached.lat, cached.lon)
    return
  }

  if (!navigator.geolocation) {
    alert(t('search.locatingNotSupported'))
    return
  }

  navigator.geolocation.getCurrentPosition(
    (position) => {
      const { latitude, longitude } = position.coords
      writeCachedLocation(latitude, longitude)
      useLocation(latitude, longitude)
    },
    (error) => {
      if (error.code === error.PERMISSION_DENIED) {
        alert(t('search.locatingDenied'))
      } else if (error.code === error.POSITION_UNAVAILABLE) {
        alert(t('search.locatingUnavailable'))
      } else if (error.code === error.TIMEOUT) {
        alert(t('search.locatingTimeout'))
      } else {
        alert(t('search.locatingError'))
      }
    }
  )
}

// Close dropdown when clicking outside
const handleClickOutside = (e) => {
  if (!e.target.closest('.relative')) {
    searchResults.value = []
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside)
  clearTimeout(searchTimeout)
})
</script>

<style scoped>
/* Smooth scrollbar for results */
::-webkit-scrollbar {
  width: 4px;
}

::-webkit-scrollbar-thumb {
  background: #475569;
  border-radius: 2px;
}
</style>
