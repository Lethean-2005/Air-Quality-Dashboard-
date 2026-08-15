<template>
  <div class="relative h-full w-full overflow-hidden bg-[#0b0f19] text-white font-sans">
    <!-- Map layer -->
    <div id="world-map" class="absolute inset-0 z-0"></div>

    <!-- Custom attribution (kept clear of the floating card) -->
    <div class="absolute bottom-1.5 left-[368px] z-[5] text-[10px] text-gray-500 select-none pointer-events-none hidden sm:block">
      © OpenStreetMap contributors · © CARTO
    </div>

    <!-- Loading overlay -->
    <div
      v-if="loading"
      class="absolute inset-0 z-30 flex flex-col items-center justify-center bg-[#0b0f19]/75 backdrop-blur-sm"
    >
      <div class="h-10 w-10 rounded-full border-2 border-gray-700 border-t-cyan-400 animate-spin"></div>
      <p class="mt-3 text-sm text-gray-400">Loading world air quality data…</p>
    </div>

    <!-- Error overlay -->
    <div v-else-if="error" class="absolute inset-0 z-30 flex items-center justify-center bg-[#0b0f19]/75">
      <div class="bg-[#111827]/95 border border-gray-800 rounded-2xl p-6 text-center max-w-sm shadow-2xl">
        <i class="fa-solid fa-triangle-exclamation text-red-400 text-2xl"></i>
        <p class="mt-2 text-sm text-red-400">{{ error }}</p>
        <button
          @click="loadData"
          class="mt-4 text-xs font-bold bg-white/10 hover:bg-white/15 rounded-lg px-4 py-2 transition"
        >
          Retry
        </button>
      </div>
    </div>

    <!-- TOP RIGHT CONTROLS -->
    <div class="absolute top-4 right-4 z-20 flex items-center gap-3">
      <!-- Search bar with autocomplete -->
      <div class="relative" v-click-outside="() => (searchOpen = false)">
        <div
          class="bg-[#111827]/90 backdrop-blur-md border border-gray-800/80 rounded-2xl px-3.5 py-2.5 flex items-center gap-3 text-sm w-72 max-w-[55vw] shadow-xl"
        >
          <i class="fa-solid fa-magnifying-glass text-gray-400 text-xs"></i>
          <input
            v-model="searchQuery"
            @input="onSearchInput"
            @focus="searchOpen = true"
            type="text"
            :placeholder="searchPlaceholder"
            class="flex-1 bg-transparent text-gray-200 placeholder-gray-400 text-sm font-medium focus:outline-none min-w-0"
          />
          <i
            class="fa-solid fa-location-crosshairs text-blue-400 text-xs cursor-pointer hover:text-blue-300 transition flex-shrink-0"
            @click="locateUser"
            title="Locate me"
          ></i>
          <div class="h-4 w-[1px] bg-gray-700/80 flex-shrink-0"></div>
          <div class="flex items-center gap-1 text-xs font-bold text-gray-200 cursor-pointer select-none flex-shrink-0">
            <span>AQI</span>
            <i class="fa-solid fa-chevron-down text-[10px] text-gray-400"></i>
          </div>
        </div>

        <!-- Autocomplete dropdown -->
        <div
          v-if="searchOpen && searchQuery"
          class="absolute right-0 mt-2 w-72 max-w-[55vw] bg-[#111827]/95 backdrop-blur-md border border-gray-800/80 rounded-2xl shadow-2xl overflow-hidden max-h-80 overflow-y-auto"
        >
          <div v-if="searchLoading" class="p-3 text-center text-xs text-gray-400">
            <i class="fa-solid fa-spinner fa-spin mr-1"></i> Searching…
          </div>
          <template v-else>
            <p v-if="searchResults.length === 0" class="p-3 text-center text-xs text-gray-400">
              No locations found
            </p>
            <div
              v-for="r in searchResults"
              :key="r.full_name"
              @click="selectSearchResult(r)"
              class="flex items-center justify-between gap-3 px-3 py-2.5 hover:bg-white/5 cursor-pointer transition-colors border-b border-gray-800/60 last:border-b-0"
            >
              <div class="flex items-center gap-2.5 min-w-0">
                <img
                  :src="flagUrl(r.country)"
                  :alt="r.country"
                  class="w-6 h-4 rounded object-cover border border-white/10 flex-shrink-0"
                />
                <span class="text-xs text-gray-200 truncate">{{ r.full_name }}</span>
              </div>
              <span
                v-if="r.aqi"
                class="flex-shrink-0 px-2 py-0.5 rounded-md text-[10px] font-bold text-white"
                :style="{ backgroundColor: aqiColor(parseFloat(r.aqi)) }"
              >
                {{ r.aqi }}
              </span>
            </div>
          </template>
        </div>
      </div>

      <!-- Track Climate Change card -->
      <RouterLink
        to="/analytics"
        class="bg-[#111827]/90 backdrop-blur-md border border-gray-800/80 rounded-2xl p-2.5 flex items-center gap-3 shadow-xl cursor-pointer hover:border-gray-700 transition group"
      >
        <img :src="climateChangeIcon" alt="Climate change severity" class="w-8 h-8 object-cover flex-shrink-0" />
        <div class="text-xs pr-1">
          <p class="text-gray-400 font-medium">Track the</p>
          <p class="font-bold text-white flex items-center gap-1">
            Climate Change
            <i
              class="fa-solid fa-arrow-up-right-from-square text-[9px] text-gray-400 group-hover:text-white transition"
            ></i>
          </p>
        </div>
      </RouterLink>
    </div>

    <!-- BOTTOM RIGHT FLOATING CONTROLS -->
    <div class="absolute bottom-6 right-4 z-20 flex flex-col gap-2.5">
      <div
        class="bg-[#111827]/90 backdrop-blur-md border border-gray-800/80 rounded-2xl flex flex-col overflow-hidden shadow-xl text-gray-300"
      >
        <button
          class="w-10 h-10 flex items-center justify-center hover:bg-gray-800/60 transition border-b border-gray-800/80"
          @click="zoomIn"
          title="Zoom in"
        >
          <i class="fa-solid fa-plus text-xs"></i>
        </button>
        <button
          class="w-10 h-10 flex items-center justify-center hover:bg-gray-800/60 transition"
          @click="zoomOut"
          title="Zoom out"
        >
          <i class="fa-solid fa-minus text-xs"></i>
        </button>
      </div>
      <button
        class="w-10 h-10 bg-[#111827]/90 backdrop-blur-md border border-gray-800/80 rounded-2xl flex items-center justify-center text-gray-300 hover:bg-gray-800/60 transition shadow-xl"
        @click="resetView"
        title="Reset view"
      >
        <i class="fa-solid fa-earth-asia text-xs"></i>
      </button>
      <RouterLink
        to="/message"
        class="w-10 h-10 bg-[#111827]/90 backdrop-blur-md border border-gray-800/80 rounded-2xl flex items-center justify-center text-blue-400 hover:bg-gray-800/60 transition shadow-xl relative"
        title="Messages"
      >
        <i class="fa-solid fa-envelope text-sm"></i>
        <span
          class="absolute -top-1 -right-1 bg-red-500 text-white text-[9px] w-4 h-4 rounded-full flex items-center justify-center font-bold"
          >1</span
        >
      </RouterLink>
    </div>

    <!-- FLOATING DETAIL CARD (popup on the map) -->
    <aside
      class="absolute left-4 top-4 z-10 w-[340px] max-w-[90vw] max-h-[calc(100%-2rem)] bg-[#111827]/95 backdrop-blur-xl border border-gray-800/80 rounded-2xl flex flex-col shadow-2xl overflow-hidden"
    >
      <!-- Header -->
      <div class="flex items-center justify-between gap-2 p-4 pb-3 border-b border-gray-800/80">
        <div class="flex items-center gap-2.5 min-w-0">
          <RouterLink
            to="/home"
            class="w-6 h-6 rounded-lg bg-blue-500/10 border border-blue-500/20 text-blue-500 flex items-center justify-center text-[10px] font-bold shadow-sm hover:bg-blue-500/20 transition flex-shrink-0"
            title="Back to Home"
          >
            AQ
          </RouterLink>
          <h1 class="font-bold text-sm text-white tracking-wide truncate">{{ t('home.airQualityMap') }}</h1>
        </div>
        <button
          @click="closeCard"
          class="w-6 h-6 flex items-center justify-center text-gray-400 hover:text-gray-200 hover:bg-white/5 rounded-lg transition text-xs flex-shrink-0"
          title="Close"
        >
          <i class="fa-solid fa-xmark"></i>
        </button>
      </div>

      <!-- Body -->
      <div class="flex-1 min-h-0 overflow-y-auto p-4">
        <!-- Empty / hint state -->
        <div v-if="!selectedStation" class="text-center text-xs text-gray-400 py-6">
          <i class="fa-solid fa-circle-nodes text-gray-600 text-xl"></i>
          <p class="mt-2">Click a station on the map to view details.</p>
        </div>

        <template v-else>
          <!-- Location -->
          <div class="flex items-start gap-2 text-xs mb-4">
            <i class="fa-solid fa-location-dot text-blue-500 mt-0.5"></i>
            <div class="min-w-0">
              <p class="font-bold text-white leading-tight truncate">{{ stationCity }}</p>
              <p class="text-[10px] text-gray-400 font-medium truncate">{{ stationSubtitle }}</p>
            </div>
          </div>

          <!-- AQI Score Card -->
          <div
            class="bg-gray-800/70 backdrop-blur-md border border-gray-800/80 rounded-xl p-4 relative overflow-hidden flex items-center justify-between shadow-lg"
          >
            <div class="flex flex-col gap-0.5">
              <div class="flex items-center gap-1.5 text-[10px] text-gray-400 font-medium">
                <i class="fa-solid fa-wind text-[9px]"></i>
                <span>Air Quality Index</span>
              </div>
              <div class="flex items-center gap-2.5 mt-0.5">
                <span class="text-3xl font-black tracking-tight" :style="{ color: aqiInfo.color }">
                  {{ selectedStation.aqi }}
                </span>
                <span
                  class="text-[10px] font-extrabold px-2.5 py-0.5 rounded-full shadow"
                  :style="{ backgroundColor: aqiInfo.color, color: aqiInfo.text }"
                >
                  {{ aqiInfo.label }}
                </span>
              </div>
            </div>
            <img
              v-if="aqiInfo.mascot"
              :src="aqiInfo.mascot"
              :alt="aqiInfo.label"
              :title="aqiInfo.label"
              class="h-16 w-auto flex-shrink-0 object-contain drop-shadow"
            />
            <span v-else class="text-4xl leading-none flex-shrink-0" :title="aqiInfo.label">{{ aqiInfo.icon }}</span>
          </div>

          <!-- Pollutants List Card -->
          <div
            class="mt-3 bg-gray-800/40 backdrop-blur-md border border-gray-800/80 rounded-xl p-4 flex flex-col gap-3 shadow-lg"
          >
            <div v-for="p in pollutantRows" :key="p.key" class="flex items-center justify-between text-xs">
              <div class="flex items-center gap-1.5 text-gray-300 font-medium w-24">
                <span>{{ p.label }}</span>
                <i class="fa-solid fa-arrow-trend-up text-[10px] text-gray-500"></i>
              </div>
              <span class="font-bold text-white w-20 text-right">
                {{ p.display }}
                <span class="text-[10px] text-gray-400 font-normal">{{ p.unit }}</span>
              </span>
              <div class="w-20 h-1.5 bg-gray-800/80 rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full transition-all duration-500"
                  :style="{ width: p.pct + '%', backgroundColor: aqiInfo.color }"
                ></div>
              </div>
            </div>
          </div>

          <!-- AQI Trend Last 24 Hour Card -->
          <div
            class="mt-3 mb-1 bg-gray-800/40 backdrop-blur-md border border-gray-800/80 rounded-xl p-4 flex flex-col gap-2 shadow-lg"
          >
            <span class="text-[11px] font-bold text-gray-300">AQI Trend Last 24 hour</span>

            <div class="relative w-full" style="height: 100px;">
              <!-- History loading placeholder -->
              <div v-if="historyLoading" class="absolute inset-0 flex items-center">
                <div class="h-1 w-full rounded bg-gray-700/40 animate-pulse"></div>
              </div>

              <!-- SVG Line Chart with y-axis grid -->
              <svg
                v-else
                class="w-full h-full overflow-visible"
                preserveAspectRatio="none"
                :viewBox="`0 0 ${TREND_W} ${TREND_H}`"
              >
                <defs>
                  <linearGradient id="trendFill" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" :stop-color="aqiInfo.color" stop-opacity="0.28" />
                    <stop offset="100%" :stop-color="aqiInfo.color" stop-opacity="0" />
                  </linearGradient>
                </defs>

                <template v-for="g in trendGridLines" :key="g.v">
                  <line :x1="TREND_PAD_L" :y1="g.y" :x2="TREND_W" :y2="g.y" stroke="rgba(255,255,255,0.08)" stroke-width="1" />
                  <text :x="TREND_PAD_L - 6" :y="g.y + 3" text-anchor="end" fill="#8b93a1" font-size="8">{{ g.v }}</text>
                </template>

                <path v-if="trendAreaPath" :d="trendAreaPath" fill="url(#trendFill)" />
                <path
                  :d="trendLinePath"
                  fill="none"
                  :stroke="historyPoints.length ? aqiInfo.color : '#334155'"
                  stroke-width="2"
                  stroke-linecap="round"
                />
              </svg>
            </div>

            <!-- Graph X-Axis Labels -->
            <div class="flex justify-between text-[10px] text-gray-400 pt-2 border-t border-gray-800/80">
              <div>
                <p class="font-semibold text-gray-300">{{ trendStart.time }}</p>
                <p>{{ trendStart.date }}</p>
              </div>
              <div class="text-center">
                <p class="font-semibold text-gray-300">{{ historyPoints.length ? trendMid.time : 'No data' }}</p>
              </div>
              <div class="text-right">
                <p class="font-semibold text-gray-300">{{ trendEnd.time }}</p>
                <p>{{ trendEnd.date }}</p>
              </div>
            </div>
          </div>
        </template>
      </div>

      <!-- Bottom AQI Scale Legend Bar -->
      <div class="p-3 bg-[#111827] border-t border-gray-800/80 shrink-0">
        <div class="grid grid-cols-7 h-3 rounded-lg overflow-hidden gap-0.5 shadow-inner">
          <div class="bg-emerald-500 flex items-center justify-center text-[7px] font-extrabold text-emerald-950">0</div>
          <div class="bg-lime-400 flex items-center justify-center text-[7px] font-extrabold text-lime-950">50</div>
          <div class="bg-amber-400 flex items-center justify-center text-[7px] font-extrabold text-amber-950">100</div>
          <div class="bg-orange-500 flex items-center justify-center text-[7px] font-extrabold text-white">150</div>
          <div class="bg-red-600 flex items-center justify-center text-[7px] font-extrabold text-white">200</div>
          <div class="bg-purple-700 flex items-center justify-center text-[7px] font-extrabold text-white">300</div>
          <div class="bg-rose-900 flex items-center justify-center text-[7px] font-extrabold text-white">301+</div>
        </div>
      </div>
    </aside>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import axios from 'axios'
import { API_ROOT } from '@/services/api.js'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'                          
import climateChangeIcon from '@/assets/images/svg/climate-change-severity.gif'
import goodLevelImg from '@/assets/images/svg/aqi-good-level.webp'
import moderateLevelImg from '@/assets/images/svg/aqi-moderate-level.webp'
import poorLevelImg from '@/assets/images/svg/aqi-poor-level.webp'
import unhealthyLevelImg from '@/assets/images/svg/aqi-unhealthy-level.webp'
import severeLevelImg from '@/assets/images/svg/aqi-severe-level.webp'
import hazardousLevelImg from '@/assets/images/svg/aqi-hazardous-level.webp'

const { t } = useI18n()

const API_BASE = `${API_ROOT}/api`

const loading = ref(true)
const error = ref('')

const stations = ref([])
const selectedStation = ref(null)
const historyPoints = ref([])
const historyLoading = ref(false)

const searchQuery = ref('')
const searchResults = ref([])
const searchLoading = ref(false)
const searchOpen = ref(false)
let searchTimer = null

let map = null
let geoLayer = null
let markersLayer = null
let selectedLayer = null

/* ---------------------------------------------------------------- helpers */

const normalize = (name) => (name || '').toLowerCase().trim()

const aqiColor = (aqi) => {
  const v = parseFloat(aqi)
  if (isNaN(v)) return '#4b5563'
  if (v <= 50) return '#22c55e'
  if (v <= 100) return '#a3e635'
  if (v <= 150) return '#fbbf24'
  if (v <= 200) return '#f97316'
  if (v <= 300) return '#ef4444'
  return '#a855f7'
}

const aqiInfo = computed(() => {
  const v = parseFloat(selectedStation.value?.aqi)
  if (isNaN(v)) return { color: '#4b5563', label: 'N/A', text: '#fff', icon: '❓', mascot: null }
  if (v <= 50) return { color: '#22c55e', label: 'Good', text: '#052e16', icon: '😊', mascot: goodLevelImg }
  if (v <= 100) return { color: '#a3e635', label: 'Moderate', text: '#1a2e05', icon: '🙂', mascot: moderateLevelImg }
  if (v <= 150) return { color: '#fbbf24', label: 'Unhealthy for Sensitive', text: '#1a1200', icon: '😷', mascot: poorLevelImg }
  if (v <= 200) return { color: '#f97316', label: 'Unhealthy', text: '#fff', icon: '😷', mascot: unhealthyLevelImg }
  if (v <= 300) return { color: '#ef4444', label: 'Very Unhealthy', text: '#fff', icon: '🤢', mascot: severeLevelImg }
  return { color: '#a855f7', label: 'Hazardous', text: '#fff', icon: '☠️', mascot: hazardousLevelImg }
})

const splitName = (name) => {
  const parts = (name || '').split(',').map((s) => s.trim()).filter(Boolean)
  const city = parts[0] || name || 'Unknown'
  const subtitle = parts.length > 1 ? parts.slice(1).join(', ') : ''
  return { city, subtitle }
}

const stationCity = computed(() => selectedStation.value?.city || splitName(selectedStation.value?.name).city)
const stationSubtitle = computed(() => selectedStation.value?.subtitle || splitName(selectedStation.value?.name).subtitle)

const pollutantRows = computed(() => {
  const s = selectedStation.value
  if (!s) return []
  const defs = [
    { key: 'pm25', label: 'PM₂.₅', unit: 'µg/m³', max: 100 },
    { key: 'pm10', label: 'PM₁₀', unit: 'µg/m³', max: 150 },
    { key: 'co', label: 'CO', unit: 'ppb', max: 2000 },
    { key: 'so2', label: 'SO₂', unit: 'ppb', max: 100 },
    { key: 'no2', label: 'NO₂', unit: 'ppb', max: 200 },
    { key: 'o3', label: 'O₃', unit: 'ppb', max: 200 },
  ]
  return defs.map((d) => {
    const v = parseFloat(s[d.key])
    const pct = isNaN(v) ? 0 : Math.min(100, Math.round((v / d.max) * 100))
    const display = isNaN(v) ? '--' : Number.isInteger(v) ? v : v.toFixed(1)
    return { ...d, display, pct }
  })
})

/* ---------------------------------------------------------------- trend chart */

const TREND_W = 300
const TREND_H = 100
const TREND_PAD_L = 26
const TREND_PAD_T = 6
const TREND_PAD_B = 6
const TREND_PLOT_H = TREND_H - TREND_PAD_T - TREND_PAD_B

const trendNiceMax = (mx) => {
  if (!isFinite(mx) || mx <= 0) return 100
  const magnitude = Math.pow(10, Math.floor(Math.log10(mx)))
  const residual = mx / magnitude
  let niceResidual
  if (residual <= 1) niceResidual = 1
  else if (residual <= 2) niceResidual = 2
  else if (residual <= 5) niceResidual = 5
  else niceResidual = 10
  return niceResidual * magnitude
}

const trendMaxY = computed(() => {
  const values = historyPoints.value.map((p) => Number(p.aqi) || 0)
  if (!values.length) return 100
  return trendNiceMax(Math.max(...values) * 1.05)
})

const trendGridLines = computed(() => {
  const max = trendMaxY.value
  const step = max / 5
  const lines = []
  for (let i = 0; i <= 5; i++) {
    const v = Math.round(step * i)
    lines.push({ v, y: TREND_PAD_T + TREND_PLOT_H - (v / max) * TREND_PLOT_H })
  }
  return lines
})

const trendLinePath = computed(() => {
  const pts = historyPoints.value
  const baseline = TREND_PAD_T + TREND_PLOT_H
  if (pts.length < 2) return `M ${TREND_PAD_L},${baseline} L ${TREND_W},${baseline}`
  const max = trendMaxY.value
  const step = (TREND_W - TREND_PAD_L) / (pts.length - 1)
  return pts
    .map((p, i) => {
      const x = TREND_PAD_L + i * step
      const y = TREND_PAD_T + TREND_PLOT_H - (Math.min(Number(p.aqi) || 0, max) / max) * TREND_PLOT_H
      return `${i === 0 ? 'M' : 'L'} ${x.toFixed(2)},${y.toFixed(2)}`
    })
    .join(' ')
})

const trendAreaPath = computed(() => {
  if (historyPoints.value.length < 2) return ''
  const baseline = TREND_PAD_T + TREND_PLOT_H
  return `${trendLinePath.value} L ${TREND_W.toFixed(2)},${baseline} L ${TREND_PAD_L.toFixed(2)},${baseline} Z`
})

const formatTrendTime = (iso) => {
  if (!iso) return { time: '—', date: '' }
  const d = new Date(iso)
  if (isNaN(d.getTime())) return { time: '—', date: '' }
  return {
    time: d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }),
    date: d.toLocaleDateString([], { day: '2-digit', month: '2-digit', year: 'numeric' }),
  }
}

const trendStart = computed(() => formatTrendTime(historyPoints.value[0]?.time))
const trendEnd = computed(() => formatTrendTime(historyPoints.value[historyPoints.value.length - 1]?.time))
const trendMid = computed(() => {
  const pts = historyPoints.value
  if (!pts.length) return { time: '—', date: '' }
  return formatTrendTime(pts[Math.floor((pts.length - 1) / 2)]?.time)
})

/* ---------------------------------------------------------------- map */

const initMap = () => {
  map = L.map('world-map', {
    center: [15, 20],
    zoom: 2,
    minZoom: 1,
    maxZoom: 19,
    scrollWheelZoom: true,
    zoomControl: false,
    attributionControl: false,
  })

  L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
    maxZoom: 19,
    subdomains: 'abcd',
  }).addTo(map)
}

const highlightMarker = (s) => {
  if (selectedLayer) {
    selectedLayer.remove()
    selectedLayer = null
  }
  if (!s || s.lat == null || s.lon == null) return
  selectedLayer = L.circleMarker([s.lat, s.lon], {
    radius: 11,
    color: '#ffffff',
    weight: 2.5,
    fillColor: aqiColor(s.aqi),
    fillOpacity: 0.9,
  })
    .bindTooltip(`<strong>${s.name || 'Station'}</strong><br/>AQI: ${s.aqi ?? 'N/A'}`, { sticky: true })
    .addTo(map)
}

const closeCard = () => {
  selectedStation.value = null
  historyPoints.value = []
  if (selectedLayer) {
    selectedLayer.remove()
    selectedLayer = null
  }
}

const selectStation = async (s, { fly = true } = {}) => {
  selectedStation.value = s
  if (map && s.lat != null && s.lon != null && fly) {
    map.flyTo([s.lat, s.lon], Math.max(map.getZoom(), 5))
  }
  highlightMarker(s)
  historyLoading.value = true
  historyPoints.value = []
  try {
    const res = await axios.get(`${API_BASE}/aqi-history`, { params: { name: s.name, hours: 24 } })
    historyPoints.value = res.data.points || []
  } catch (err) {
    historyPoints.value = []
  } finally {
    historyLoading.value = false
  }
}

const addMarker = (s) => {
  if (s.lat == null || s.lon == null) return
  const color = aqiColor(s.aqi)
  L.circleMarker([s.lat, s.lon], {
    radius: 5,
    fillColor: color,
    color: '#fff',
    weight: 1,
    opacity: 1,
    fillOpacity: 0.85,
  })
    .bindTooltip(`<strong>${s.name || 'Station'}</strong><br/>AQI: ${s.aqi ?? 'N/A'}`, { sticky: true })
    .on('click', () => selectStation(s))
    .addTo(markersLayer)
}

const loadData = async () => {
  loading.value = true
  error.value = ''
  try {
    // Fetch each source independently so one failing source (e.g. a tile/geo file)
    // never blanks the whole map — stations are the essential part.
    const [geoRes, aqiRes, stationsRes] = await Promise.allSettled([
      axios.get(`${API_BASE}/world-geojson`),
      axios.get(`${API_BASE}/aqi-by-country`),
      axios.get(`${API_BASE}/aqi`),
    ])

    stations.value = stationsRes.status === 'fulfilled' ? stationsRes.value.data.data || [] : []
    if (!stations.value.length) {
      throw new Error('No station data available.')
    }

    const aqiByCountry = new Map()
    if (aqiRes.status === 'fulfilled') {
      for (const row of aqiRes.value.data.data) aqiByCountry.set(normalize(row.country), row.aqi)
    }

    initMap()

    // Choropleth fill first (bottom layer) — skipped gracefully if the geojson is missing.
    if (geoRes.status === 'fulfilled') {
      geoLayer = L.geoJSON(geoRes.value.data, {
        style: (feature) => {
          const props = feature.properties || {}
          const aqi =
            aqiByCountry.get(normalize(props.name)) ??
            aqiByCountry.get(normalize(props.name_long)) ??
            aqiByCountry.get(normalize(props.admin)) ??
            null
          return {
            fillColor: aqiColor(aqi),
            fillOpacity: aqi == null ? 0.15 : 0.75,
            color: '#ffffff',
            weight: 0.8,
          }
        },
        onEachFeature: (feature, layer) => {
          const props = feature.properties || {}
          const aqi =
            aqiByCountry.get(normalize(props.name)) ??
            aqiByCountry.get(normalize(props.name_long)) ??
            aqiByCountry.get(normalize(props.admin)) ??
            null
          layer.bindTooltip(
            `<strong>${props.name || 'Unknown'}</strong><br/>AQI: ${aqi ?? 'No data'}`,
            { sticky: true }
          )
        },
      }).addTo(map)
    }

    // ...individual location markers on top.
    markersLayer = L.layerGroup().addTo(map)
    stations.value.forEach(addMarker)

    // Default selection: prefer Phnom Penh, otherwise the first station with data.
    const defaultStation =
      stations.value.find((s) => normalize(s.name).includes('phnom penh')) ||
      stations.value.find((s) => s.aqi != null && s.aqi !== 'N/A')
    if (defaultStation) selectStation(defaultStation, { fly: true })
  } catch (err) {
    console.error('Failed to load world AQI map:', err)
    error.value = 'Failed to load world air quality data.'
  } finally {
    loading.value = false
  }
}

const zoomIn = () => map?.zoomIn()
const zoomOut = () => map?.zoomOut()
const resetView = () => map?.flyTo([15, 20], 2)

/* ---------------------------------------------------------------- search */

const searchPlaceholder = computed(() => t('search.placeholder'))

const onSearchInput = () => {
  searchOpen.value = true
  clearTimeout(searchTimer)
  const q = searchQuery.value.trim()
  if (q.length < 2) {
    searchResults.value = []
    searchLoading.value = false
    return
  }
  searchLoading.value = true
  searchTimer = setTimeout(async () => {
    try {
      const res = await axios.get(`${API_BASE}/search-locations`, { params: { q } })
      searchResults.value = res.data.data || []
    } catch (err) {
      searchResults.value = []
    } finally {
      searchLoading.value = false
    }
  }, 300)
}

const selectSearchResult = (r) => {
  searchQuery.value = r.name || r.full_name
  searchOpen.value = false
  const s =
    stations.value.find(
      (st) =>
        st.lat != null &&
        r.lat != null &&
        Math.abs(st.lat - r.lat) < 0.01 &&
        Math.abs(st.lon - r.lon) < 0.01
    ) || stations.value.find((st) => st.name && normalize(st.name).includes(normalize(r.name)))
  if (s) {
    selectStation(s)
  } else if (r.lat != null && r.lon != null) {
    selectStation({
      name: r.full_name,
      city: r.name,
      subtitle: r.country,
      lat: r.lat,
      lon: r.lon,
      aqi: r.aqi,
      pm25: null,
      pm10: null,
      no2: null,
      co: null,
      o3: null,
      so2: null,
    })
  }
}

const locateUser = () => {
  if (!navigator.geolocation) return
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      const { latitude, longitude } = pos.coords
      if (map) map.flyTo([latitude, longitude], 8)
      let nearest = null
      let best = Infinity
      for (const st of stations.value) {
        if (st.lat == null || st.lon == null) continue
        const d = (st.lat - latitude) ** 2 + (st.lon - longitude) ** 2
        if (d < best) {
          best = d
          nearest = st
        }
      }
      if (nearest) selectStation(nearest)
    },
    () => {}
  )
}

/* ---------------------------------------------------------------- flags */

const countryCodeMap = {
  afghanistan: 'af', albania: 'al', algeria: 'dz', andorra: 'ad', angola: 'ao', argentina: 'ar',
  armenia: 'am', australia: 'au', austria: 'at', azerbaijan: 'az', bahamas: 'bs', bahrain: 'bh',
  bangladesh: 'bd', belarus: 'by', belgium: 'be', belize: 'bz', benin: 'bj', bhutan: 'bt',
  bolivia: 'bo', bosnia: 'ba', botswana: 'bw', brazil: 'br', brunei: 'bn', bulgaria: 'bg',
  cambodia: 'kh', cameroon: 'cm', canada: 'ca', chad: 'td', chile: 'cl', china: 'cn', colombia: 'co',
  congo: 'cg', 'costa rica': 'cr', croatia: 'hr', cuba: 'cu', cyprus: 'cy', czechia: 'cz',
  'czech republic': 'cz', denmark: 'dk', djibouti: 'dj', dominica: 'dm', 'dominican republic': 'do',
  ecuador: 'ec', egypt: 'eg', 'el salvador': 'sv', estonia: 'ee', ethiopia: 'et', fiji: 'fj',
  finland: 'fi', france: 'fr', gabon: 'ga', gambia: 'gm', georgia: 'ge', germany: 'de', ghana: 'gh',
  greece: 'gr', greenland: 'gl', guatemala: 'gt', guinea: 'gn', guyana: 'gy', haiti: 'ht',
  honduras: 'hn', 'hong kong': 'hk', hungary: 'hu', iceland: 'is', india: 'in', indonesia: 'id',
  iran: 'ir', iraq: 'iq', ireland: 'ie', israel: 'il', italy: 'it', jamaica: 'jm', japan: 'jp',
  jordan: 'jo', kazakhstan: 'kz', kenya: 'ke', kuwait: 'kw', kyrgyzstan: 'kg', laos: 'la',
  latvia: 'lv', lebanon: 'lb', lesotho: 'ls', liberia: 'lr', libya: 'ly', liechtenstein: 'li',
  lithuania: 'lt', luxembourg: 'lu', macau: 'mo', madagascar: 'mg', malawi: 'mw', malaysia: 'my',
  maldives: 'mv', mali: 'ml', malta: 'mt', mauritania: 'mr', mauritius: 'mu', mexico: 'mx',
  moldova: 'md', monaco: 'mc', mongolia: 'mn', montenegro: 'me', morocco: 'ma', mozambique: 'mz',
  myanmar: 'mm', namibia: 'na', nepal: 'np', netherlands: 'nl', 'new zealand': 'nz', nicaragua: 'ni',
  niger: 'ne', nigeria: 'ng', 'north korea': 'kp', 'north macedonia': 'mk', norway: 'no', oman: 'om',
  pakistan: 'pk', palestine: 'ps', panama: 'pa', 'papua new guinea': 'pg', paraguay: 'py', peru: 'pe',
  philippines: 'ph', poland: 'pl', portugal: 'pt', qatar: 'qa', romania: 'ro', russia: 'ru',
  rwanda: 'rw', 'saudi arabia': 'sa', senegal: 'sn', serbia: 'rs', 'sierra leone': 'sl',
  singapore: 'sg', slovakia: 'sk', slovenia: 'si', somalia: 'so', 'south africa': 'za',
  'south korea': 'kr', 'south sudan': 'ss', spain: 'es', 'sri lanka': 'lk', sudan: 'sd',
  suriname: 'sr', sweden: 'se', switzerland: 'ch', syria: 'sy', taiwan: 'tw', tajikistan: 'tj',
  tanzania: 'tz', thailand: 'th', togo: 'tg', 'trinidad and tobago': 'tt', tunisia: 'tn',
  turkey: 'tr', turkmenistan: 'tm', uganda: 'ug', ukraine: 'ua', 'united arab emirates': 'ae',
  uae: 'ae', 'united kingdom': 'gb', uk: 'gb', 'united states': 'us',
  'united states of america': 'us', usa: 'us', uruguay: 'uy', uzbekistan: 'uz', vanuatu: 'vu',
  'vatican city': 'va', venezuela: 've', vietnam: 'vn', yemen: 'ye', zambia: 'zm', zimbabwe: 'zw',
}

const flagUrl = (country) => {
  const key = (country || '').toLowerCase().trim()
  const code = countryCodeMap[key] || 'xx'
  return `https://flagcdn.com/w40/${code}.png`
}

/* ---------------------------------------------------------------- lifecycle */

onMounted(() => {
  loadData()
})

onUnmounted(() => {
  clearTimeout(searchTimer)
  if (map) map.remove()
})
</script>

<style>
/* Slim, clean scrollbar for the sidebar */
::-webkit-scrollbar {
  width: 4px;
}
::-webkit-scrollbar-track {
  background: transparent;
}
::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.2);
}
</style>
