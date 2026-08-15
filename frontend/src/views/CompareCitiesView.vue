<template>
  <div class="aqi-graph-page">
    <div class="wrap" v-if="!loading">
      <!-- Header -->
      <div class="header-row">
        <div>
          <div class="eyebrow">AQI Graph</div>
          <div class="title">Historical Air Quality Data</div>
          <div class="location">{{ primaryName }}</div>
        </div>

        <div class="controls">
          <div
            class="icon-btn"
            :class="{ active: chartType === 'line' }"
            @click="chartType = 'line'"
            title="Line chart"
          >
            <i class="fa-solid fa-chart-line"></i>
          </div>
          <div
            class="icon-btn"
            :class="{ active: chartType === 'bar' }"
            @click="chartType = 'bar'"
            title="Bar chart"
          >
            <i class="fa-solid fa-chart-bar"></i>
          </div>

          <AppDropdown v-model="hours" :options="hourDropdownOptions" :lead-icon="IconClock" align="right" />

          <AppDropdown v-model="metric" :options="metricDropdownOptions" :lead-icon="IconGauge" align="right" />
        </div>
      </div>

      <!-- Card -->
      <div class="card">
        <div class="card-top">
          <div class="left-controls">
            <!-- Primary city pill -->
            <div class="pill relative" @click.stop="toggleList('city1')">
              <span class="dot" style="background: #c3d92e"></span>
              <span class="max-w-[150px] truncate">{{ city1Label }}</span>
              <i class="fa-solid fa-chevron-down text-[10px] text-[#8992a3] ml-1"></i>
              <div v-if="showList === 'city1'" class="menu top-full left-0">
                <div class="menu-search">
                  <i class="fa-solid fa-magnifying-glass"></i>
                  <input
                    v-model="search"
                    class="search-input"
                    type="text"
                    placeholder="Search city…"
                    @click.stop
                  />
                </div>
                <div class="menu-list">
                  <div
                    v-for="c in filteredCities"
                    :key="c.name"
                    class="opt"
                    @click.stop="selectCity(1, c)"
                  >
                    <img :src="c.flag" class="flag" alt="" />
                    <span>{{ cityDisplay(c) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Secondary city pill -->
            <div
              v-if="city2"
              class="pill relative"
              @click.stop="toggleList('city2')"
            >
              <span class="dot" style="background: #5aa9f7"></span>
              <span class="max-w-[150px] truncate">{{ city2Label }}</span>
              <i class="fa-solid fa-xmark ml-1 text-[#8992a3] hover:text-white" @click.stop="removeCity2"></i>
              <div v-if="showList === 'city2'" class="menu top-full left-0">
                <div class="menu-search">
                  <i class="fa-solid fa-magnifying-glass"></i>
                  <input
                    v-model="search"
                    class="search-input"
                    type="text"
                    placeholder="Search city…"
                    @click.stop
                  />
                </div>
                <div class="menu-list">
                  <div
                    v-for="c in filteredCities"
                    :key="c.name"
                    class="opt"
                    @click.stop="selectCity(2, c)"
                  >
                    <img :src="c.flag" class="flag" alt="" />
                    <span>{{ cityDisplay(c) }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Add to Compare -->
            <div v-else class="pill add relative" @click.stop="toggleAdd">
              <i class="fa-solid fa-plus text-[11px]"></i> Add to Compare
              <div v-if="showAdd" class="menu top-full left-0">
                <div class="menu-search">
                  <i class="fa-solid fa-magnifying-glass"></i>
                  <input
                    ref="addSearchInput"
                    v-model="addSearch"
                    class="search-input"
                    type="text"
                    placeholder="Search a city…"
                    @click.stop
                    @keydown.esc="closeAdd"
                  />
                </div>
                <div class="menu-list">
                  <div
                    v-for="c in addResults"
                    :key="c.name"
                    class="opt"
                    @click.stop="selectCity(2, c)"
                  >
                    <img :src="c.flag" class="flag" alt="" />
                    <span class="truncate">{{ cityDisplay(c) }}</span>
                  </div>
                  <div v-if="!addResults.length" class="no-results">No matching cities found</div>
                </div>
              </div>
            </div>
          </div>

          <div v-if="metricAvailable" class="stats">
            <div class="stat">
              <div class="stat-badge min">{{ minVal }}</div>
              <div>
                <div class="stat-label min">
                  <i class="fa-solid fa-arrow-down text-[11px]"></i> Min. AQI (US)
                </div>
                <div class="stat-time"><i class="fa-solid fa-clock text-[11px]"></i> {{ minTime }}</div>
              </div>
            </div>
            <div class="stat">
              <div class="stat-badge max">{{ maxVal }}</div>
              <div>
                <div class="stat-label max">
                  <i class="fa-solid fa-arrow-up text-[11px]"></i> Max. AQI (US)
                </div>
                <div class="stat-time"><i class="fa-solid fa-clock text-[11px]"></i> {{ maxTime }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Chart -->
        <div class="chart-area">
          <div v-if="historyLoading" class="loading-overlay">
            <Skeleton class="absolute inset-2 rounded-lg bg-white/5" />
          </div>

          <!-- Frozen y-axis: pinned over the scrollable chart's left edge so the
               AQI scale never scrolls out of view, only the bars/x-axis do. -->
          <svg
            v-if="metricAvailable && chartSeries.length"
            viewBox="0 0 1180 420"
            preserveAspectRatio="xMidYMid meet"
            class="chart-yaxis-overlay"
          >
            <rect x="0" y="0" :width="padL + 6" :height="H" class="yaxis-mask" />
            <text
              v-for="g in gridLines"
              :key="'sticky' + g.v"
              :x="padL - 10"
              :y="g.y + 4"
              text-anchor="end"
              class="y-label"
            >{{ g.v }}</text>
            <text
              :x="padL - 32"
              :y="padT + plotH / 2"
              text-anchor="middle"
              :transform="`rotate(-90 ${padL - 32} ${padT + plotH / 2})`"
              class="axis-title"
            >AQI (US)</text>
          </svg>

          <div ref="chartScrollRef" class="chart-scroll-outer no-scrollbar">
          <div class="chart-scroll-inner" :style="{ width: svgPxWidth + 'px' }">
          <svg
            v-if="metricAvailable && chartSeries.length"
            ref="svgRef"
            class="w-full h-full block overflow-visible"
            viewBox="0 0 1180 420"
            preserveAspectRatio="none"
          >
            <!-- Area gradients -->
            <defs>
              <linearGradient
                v-for="s in chartSeries"
                :key="'g' + s.id"
                :id="'areaGrad' + s.id"
                x1="0"
                y1="0"
                x2="0"
                y2="1"
              >
                <stop offset="0%" :stop-color="s.color" stop-opacity="0.22" />
                <stop offset="100%" :stop-color="s.color" stop-opacity="0" />
              </linearGradient>
            </defs>

            <!-- Grid lines only — the y-axis tick numbers and "AQI (US)" title
                 are owned exclusively by the frozen chart-yaxis-overlay above,
                 since this SVG is stretched to svgPxWidth for scrolling and
                 would otherwise render its own copy at a different scale. -->
            <line
              v-for="g in gridLines"
              :key="g.v"
              :x1="padL"
              :y1="g.y"
              :x2="W - padR"
              :y2="g.y"
              class="grid-line"
            />

            <!-- X labels -->
            <text
              v-for="l in xLabels"
              :key="l.i"
              :x="l.x"
              :y="H - padB + 18"
              text-anchor="middle"
              class="x-label"
            >{{ l.label }}</text>

            <!-- Axis title -->
            <text :x="padL + plotW / 2" :y="H - 4" text-anchor="middle" class="axis-title">Time</text>

            <!-- Bars: each bar is colored by its own AQI level (matches the
                 Good/Moderate/etc. bands used across the rest of the app),
                 not a flat per-series color, so the chart reads at a glance. -->
            <template v-if="chartType === 'bar' && primarySeries">
              <rect
                v-for="(p, i) in primarySeries.points"
                :key="i"
                class="aqi-bar"
                :class="{ 'is-hover': hover && nearestIndexFor(primarySeries, hover.svgX) === i }"
                :x="p.x - barWidth / 2"
                :y="p.y"
                :width="barWidth"
                :height="padT + plotH - p.y"
                rx="3"
                :fill="aqiBandColor(p.value)"
              />
            </template>

            <!-- Lines: smooth curve + area fill + dots -->
            <template v-if="chartType === 'line'">
              <template v-for="s in chartSeries" :key="'s' + s.id">
                <path :d="areaPath(s)" :fill="`url(#areaGrad${s.id})`" stroke="none" />
                <path :d="smoothPath(s)" class="aqi-line" :stroke="s.color" />
                <circle
                  v-for="(p, i) in s.points"
                  :key="'d' + i"
                  class="aqi-dot"
                  :cx="p.x"
                  :cy="p.y"
                  r="3"
                  :fill="s.color"
                />
              </template>

              <!-- Hover: highlighted dots (line chart only) -->
              <template v-if="hover">
                <template v-for="s in chartSeries" :key="'h' + s.id">
                  <circle
                    v-if="hoverPointFor(s)"
                    class="hover-dot-outer"
                    :cx="hoverPointFor(s).x"
                    :cy="hoverPointFor(s).y"
                    r="9"
                    :fill="s.color"
                  />
                  <circle
                    v-if="hoverPointFor(s)"
                    class="hover-dot-inner"
                    :cx="hoverPointFor(s).x"
                    :cy="hoverPointFor(s).y"
                    r="4"
                    fill="#0c0f14"
                    :stroke="s.color"
                    stroke-width="2"
                  />
                </template>
              </template>
            </template>

            <!-- Hover guide line: shared by both chart types -->
            <line
              v-if="hover"
              class="hover-line"
              :x1="hover.svgX"
              :x2="hover.svgX"
              :y1="padT"
              :y2="padT + plotH"
            />
          </svg>

          <!-- Hover capture layer + tooltip -->
          <div
            v-if="!historyLoading && metricAvailable && chartSeries.length"
            ref="hoverCapture"
            class="hover-capture"
            @mousemove="onHoverMove"
            @mouseleave="onHoverLeave"
            @touchstart.passive="onHoverTouch"
            @touchmove.passive="onHoverTouch"
          ></div>

          <div
            v-if="hover"
            class="chart-tooltip"
            :style="tooltipStyle"
          >
            <div class="tooltip-time">{{ tooltipTime }}</div>
            <div v-for="s in chartSeries" :key="'t' + s.id" class="tooltip-row">
              <span class="tooltip-dot" :style="{ background: s.color }"></span>
              <span>{{ tooltipLabel(s) }}</span>
            </div>
          </div>
          </div>
          </div>

          <div v-if="!metricAvailable" class="empty-chart">
            No {{ metric }} history available yet — switch back to AQI (US).
          </div>
          <div v-else class="empty-chart">No data yet — select a city to draw its AQI trend.</div>
        </div>

        <div class="date-row">
          <div class="date-label">{{ startDate }}</div>
          <div class="date-label">{{ endDate }}</div>
        </div>
      </div>
  </div>

  <!-- Initial loading -->
  <div v-else class="wrap">
    <div class="header-row">
      <div>
        <Skeleton class="h-3 w-16 bg-white/10" />
        <Skeleton class="h-6 w-48 mt-2 bg-white/10" />
        <Skeleton class="h-3 w-32 mt-2 bg-white/10" />
      </div>
      <div class="flex items-center gap-2">
        <Skeleton class="h-9 w-9 rounded-lg bg-white/10" />
        <Skeleton class="h-9 w-9 rounded-lg bg-white/10" />
        <Skeleton class="h-9 w-28 rounded-lg bg-white/10" />
        <Skeleton class="h-9 w-28 rounded-lg bg-white/10" />
      </div>
    </div>
    <div class="card">
      <div class="card-top">
        <div class="flex items-center gap-2">
          <Skeleton class="h-8 w-28 rounded-full bg-white/10" />
          <Skeleton class="h-8 w-28 rounded-full bg-white/10" />
        </div>
      </div>
      <Skeleton class="w-full h-[280px] rounded-lg bg-white/5" />
    </div>
  </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onUnmounted } from 'vue'
import axios from 'axios'
import { API_ROOT } from '@/services/api.js'
import AppDropdown from '@/components/AppDropdown.vue'
import Skeleton from '@/components/Skeleton.vue'
import { IconClock, IconGauge } from '@tabler/icons-vue'

const API = `${API_ROOT}/api`

const cities = ref([])
const loading = ref(true)

const city1 = ref(null)
const city2 = ref(null)
const chartType = ref('line')
const hours = ref(24)
const hourOptions = [
  { hours: 24, label: '24 Hours' },
  { hours: 168, label: '7 Days' },
  { hours: 720, label: '30 Days' },
]
const hourDropdownOptions = hourOptions.map((o) => ({ value: o.hours, label: o.label }))
const metric = ref('AQI (US)')
const metricDropdownOptions = [
  { value: 'AQI (US)', label: 'AQI (US)' },
  { value: 'PM2.5', label: 'PM2.5' },
  { value: 'PM10', label: 'PM10' },
]
const showList = ref(null)
const search = ref('')

const showAdd = ref(false)
const addSearch = ref('')
const addSearchInput = ref(null)

const history1 = ref([])
const history2 = ref([])
const historyLoading = ref(false)

const CH = { W: 1180, H: 420, padL: 46, padR: 14, padT: 14, padB: 40 }

const padL = CH.padL
const padR = CH.padR
const padT = CH.padT
const padB = CH.padB
const W = CH.W
const H = CH.H
const plotW = W - padL - padR
const plotH = H - padT - padB

/* ---------------------------------------------------------------- helpers */

const splitName = (name) => {
  const parts = (name || '').split(',').map((s) => s.trim()).filter(Boolean)
  return { city: parts[0] || name || '', subtitle: parts.length > 1 ? parts.slice(1).join(', ') : '' }
}

const cityDisplay = (city) => {
  const { city: c, subtitle } = splitName(city?.name)
  return subtitle ? `${c}, ${subtitle}` : c || city?.name || ''
}

const cityName = (city) => {
  if (!city) return '—'
  const { city: c } = splitName(city.name)
  return c || city.name
}

const pad2 = (n) => String(n).padStart(2, '0')

const formatTime = (iso) => {
  if (!iso) return '—'
  const d = new Date(iso)
  if (isNaN(d.getTime())) return '—'
  return d.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }).toLowerCase()
}

const formatDate = (iso) => {
  if (!iso) return '—'
  const d = new Date(iso)
  if (isNaN(d.getTime())) return '—'
  return `${pad2(d.getDate())}-${pad2(d.getMonth() + 1)}-${d.getFullYear()}`
}

/* ---------------------------------------------------------------- computed */

const primaryName = computed(() => cityName(city1.value) || 'Select a city')
const city1Label = computed(() => cityName(city1.value))
const city2Label = computed(() => cityName(city2.value))

const hoursLabel = computed(() => hourOptions.find((o) => o.hours === hours.value)?.label || `${hours.value} Hours`)
const metricAvailable = computed(() => metric.value === 'AQI (US)')

const filteredCities = computed(() => {
  if (!cities.value.length) return []
  const q = search.value.trim().toLowerCase()
  const base = q
    ? cities.value.filter((c) => (c.name || '').toLowerCase().includes(q))
    : cities.value
  return base.slice(0, 300)
})

// Live drill-down results for the "Add to Compare" popup search.
const addResults = computed(() => {
  if (!cities.value.length) return []
  const q = addSearch.value.trim().toLowerCase()
  const base = q
    ? cities.value.filter((c) => (c.name || '').toLowerCase().includes(q))
    : cities.value
  return base
    .filter((c) => c.name !== city1.value?.name)
    .slice(0, 200)
})

const historyFor = computed(() => {
  const all = [...history1.value, ...history2.value].map((p) => Number(p.aqi) || 0)
  return all
})

const maxAqiY = computed(() => {
  if (!historyFor.value.length) return 100
  const mx = Math.max(...historyFor.value)
  return Math.max(100, Math.ceil((mx + 1) / 10) * 10)
})

const gridLines = computed(() => {
  const lines = []
  for (let v = 0; v <= maxAqiY.value + 0.0001; v += 10) {
    lines.push({ v, y: padT + plotH - (v / maxAqiY.value) * plotH })
  }
  return lines
})

// Backend returns one entry per time bucket even when a station has no reading
// for that slot; keeping those null entries in the series produces zero-height
// "gap" bars and pushes the first real bar away from the left edge. Dropping
// them makes the chart start right at the axis and read as one continuous set.
const hasReading = (p) => p && p.aqi != null && !isNaN(Number(p.aqi))
const cleanHistory = (pts) => (pts || []).filter(hasReading)

const xLabels = computed(() => {
  const pts = cleanHistory(history1.value.length ? history1.value : history2.value)
  if (!pts.length) return []
  // Space labels by pixel width instead of a fixed index step, so they stay
  // readable (no overlap) regardless of how many points are in the series.
  const minGapPx = 70
  const step = Math.max(1, Math.ceil((minGapPx * (pts.length - 1)) / plotW))
  const labels = []
  for (let i = 0; i < pts.length; i += step) {
    labels.push({
      i,
      x: padL + (i / (pts.length - 1)) * plotW,
      label: formatTime(pts[i].time),
    })
  }
  return labels
})

const buildSeries = (rawPts, color, id, city) => {
  const pts = cleanHistory(rawPts)
  if (!pts.length) return null
  return {
    id,
    color,
    city,
    values: pts,
    points: pts.map((p, i) => ({
      x: padL + (i / (pts.length - 1)) * plotW,
      y: padT + plotH - (Math.min(Number(p.aqi) || 0, maxAqiY.value) / maxAqiY.value) * plotH,
      value: Number(p.aqi) || 0,
    })),
  }
}

// US AQI category bands — same palette used for the level cards/gauges
// elsewhere in the app, so a bar's color always means the same thing.
const aqiBandColor = (v) => {
  if (v == null || isNaN(v)) return '#5b6472'
  if (v <= 50) return '#4cd964'
  if (v <= 100) return '#ffcc00'
  if (v <= 150) return '#ff9500'
  if (v <= 200) return '#ff2d55'
  if (v <= 300) return '#af52de'
  return '#ff3b30'
}

const primarySeries = computed(() => (city1.value ? buildSeries(history1.value, '#c3d92e', 1, cityName(city1.value)) : null))
const secondarySeries = computed(() => (city2.value ? buildSeries(history2.value, '#5aa9f7', 2, cityName(city2.value)) : null))

const chartSeries = computed(() => {
  const s = []
  if (primarySeries.value) s.push(primarySeries.value)
  if (secondarySeries.value) s.push(secondarySeries.value)
  return s
})

const barWidth = computed(() => {
  const len = primarySeries.value?.points.length || 1
  return Math.max(8, (plotW / len) * 0.82)
})

// The SVG's viewBox/coordinate system (W/plotW above) stays fixed — only its
// rendered CSS width grows with the point count, inside a scrollable wrapper,
// so dense series get more physical room per bar instead of being squeezed.
const chartScrollRef = ref(null)
const svgPxWidth = computed(() => {
  const len = Math.max(primarySeries.value?.points.length || 0, secondarySeries.value?.points.length || 0)
  return Math.max(CH.W, padL + padR + len * 14)
})

// Catmull-Rom → Bezier smooth curve through the points (matches the mockup).
const smoothPath = (series) => {
  const pts = series.points
  if (pts.length < 3) {
    return pts.map((p, i) => `${i === 0 ? 'M' : 'L'}${p.x.toFixed(1)} ${p.y.toFixed(1)}`).join(' ')
  }
  // Catmull-Rom control points can overshoot past a sharp local min/max
  // (e.g. a value that dips close to 0 between two higher readings), which
  // pushes the curve — and the area fill under it — below the plot's own
  // baseline. Clamping each control point back into [padT, padT+plotH]
  // keeps the curve (and its fill) from ever dipping past the 0/100 lines.
  const clampY = (y) => Math.min(Math.max(y, padT), padT + plotH)
  let d = `M ${pts[0].x.toFixed(1)} ${pts[0].y.toFixed(1)}`
  for (let i = 0; i < pts.length - 1; i++) {
    const p0 = pts[i === 0 ? 0 : i - 1]
    const p1 = pts[i]
    const p2 = pts[i + 1]
    const p3 = pts[i + 2 < pts.length ? i + 2 : i + 1]
    const cp1x = p1.x + (p2.x - p0.x) / 6
    const cp1y = clampY(p1.y + (p2.y - p0.y) / 6)
    const cp2x = p2.x - (p3.x - p1.x) / 6
    const cp2y = clampY(p2.y - (p3.y - p1.y) / 6)
    d += ` C ${cp1x.toFixed(1)} ${cp1y.toFixed(1)}, ${cp2x.toFixed(1)} ${cp2y.toFixed(1)}, ${p2.x.toFixed(1)} ${p2.y.toFixed(1)}`
  }
  return d
}

const areaPath = (series) => {
  const pts = series.points
  if (!pts.length) return ''
  return `${smoothPath(series)} L ${pts[pts.length - 1].x.toFixed(1)} ${(padT + plotH).toFixed(1)} L ${pts[0].x.toFixed(1)} ${(padT + plotH).toFixed(1)} Z`
}

/* ---------------------------------------------------------------- hover */

const svgRef = ref(null)
const hoverCapture = ref(null)
const hover = ref(null)

const nearestIndexFor = (series, svgX) => {
  if (!series || !series.points.length) return -1
  let best = 0
  let min = Infinity
  series.points.forEach((p, i) => {
    const d = Math.abs(p.x - svgX)
    if (d < min) {
      min = d
      best = i
    }
  })
  return best
}

const computeHover = (clientX) => {
  if (!hoverCapture.value || !svgRef.value) return
  const rect = hoverCapture.value.getBoundingClientRect()
  const relX = clientX - rect.left
  const svgX = (relX / rect.width) * CH.W

  const svgRect = svgRef.value.getBoundingClientRect()
  const scaleX = svgRect.width / CH.W
  const scaleY = svgRect.height / CH.H

  const primary = primarySeries.value
  let py = padT + plotH
  if (primary) {
    const idx = nearestIndexFor(primary, svgX)
    if (idx >= 0) py = primary.points[idx].y
  }

  hover.value = { svgX, pxX: svgX * scaleX, pxY: py * scaleY }
}

const onHoverMove = (e) => computeHover(e.clientX)
const onHoverTouch = (e) => {
  const t = e.touches[0]
  if (t) computeHover(t.clientX)
}
const onHoverLeave = () => {
  hover.value = null
}

const hoverPointFor = (s) => {
  if (!hover.value) return null
  const idx = nearestIndexFor(s, hover.value.svgX)
  return idx >= 0 ? s.points[idx] : null
}

const tooltipTime = computed(() => {
  const s = primarySeries.value || secondarySeries.value
  if (!s || !hover.value) return ''
  const idx = nearestIndexFor(s, hover.value.svgX)
  return idx >= 0 ? formatTime(s.values[idx]?.time) : ''
})

const tooltipLabel = (s) => {
  const p = hoverPointFor(s)
  if (!p) return ''
  const idx = s.points.indexOf(p)
  const v = s.values[idx]
  return `${s.city}: ${v ? v.aqi : '—'}`
}

const tooltipStyle = computed(() => {
  if (!hover.value) return {}
  return {
    left: hover.value.pxX + 'px',
    top: hover.value.pxY + 'px',
    transform:
      hover.value.pxY < 90
        ? 'translate(-50%, 30px)'
        : 'translate(-50%, calc(-100% - 14px))',
  }
})

const minPoint = computed(() => history1.value.reduce((a, p) => (a && a.aqi <= p.aqi ? a : p), null))
const maxPoint = computed(() => history1.value.reduce((a, p) => (a && a.aqi >= p.aqi ? a : p), null))
const minVal = computed(() => (minPoint.value ? minPoint.value.aqi : '—'))
const maxVal = computed(() => (maxPoint.value ? maxPoint.value.aqi : '—'))
const minTime = computed(() => formatTime(minPoint.value?.time))
const maxTime = computed(() => formatTime(maxPoint.value?.time))

const startDate = computed(() => formatDate(history1.value[0]?.time))
const endDate = computed(() => formatDate(history1.value[history1.value.length - 1]?.time))

/* ---------------------------------------------------------------- actions */

const toggleList = (name) => {
  search.value = ''
  showList.value = showList.value === name ? null : name
}

const toggleAdd = () => {
  if (showAdd.value) {
    showAdd.value = false
    addSearch.value = ''
    return
  }
  showAdd.value = true
  nextTick(() => addSearchInput.value?.focus())
}

const closeAdd = () => {
  showAdd.value = false
  addSearch.value = ''
}

const selectCity = (slot, city) => {
  if (slot === 1) city1.value = city
  else city2.value = city
  showList.value = null
  search.value = ''
  showAdd.value = false
  addSearch.value = ''
}

const removeCity2 = () => {
  city2.value = null
  history2.value = []
  showList.value = null
}

const fetchHistory = async (name) => {
  if (!name) return []
  try {
    const res = await axios.get(`${API}/aqi-history`, { params: { name, hours: hours.value } })
    return res.data?.points || []
  } catch (err) {
    return []
  }
}

const loadHistories = async () => {
  historyLoading.value = true
  const [h1, h2] = await Promise.all([
    city1.value ? fetchHistory(city1.value.name) : Promise.resolve([]),
    city2.value ? fetchHistory(city2.value.name) : Promise.resolve([]),
  ])
  history1.value = h1
  history2.value = h2
  historyLoading.value = false
}

const fetchCities = async () => {
  loading.value = true
  try {
    const [globalRes, phnomRes] = await Promise.all([
      axios.get(`${API}/cities`),
      axios.get(`${API}/air-quality/phnom-penh`),
    ])

    let allCities = []
    if (globalRes.data?.status === 'ok') allCities = [...globalRes.data.data]

    // Phnom Penh endpoint returns an unwrapped payload (no status/data wrapper).
    const phnom = phnomRes.data
    if (phnom && typeof phnom === 'object') {
      allCities.push({
        name: 'Phnom Penh, Cambodia',
        aqi: Number(phnom.AQI) || null,
        timezone: 'Asia/Phnom_Penh',
        flag: 'https://flagcdn.com/kh.svg',
        countryCode: 'KH',
      })
    }

    // De-duplicate by name (OpenAQ has many duplicate location names).
    const seen = new Set()
    cities.value = allCities.filter((c) => {
      if (seen.has(c.name)) return false
      seen.add(c.name)
      return true
    })

    // Default primary city = Phnom Penh (or the first city with an AQI).
    city1.value =
      cities.value.find((c) => (c.name || '').toLowerCase().includes('phnom penh')) ||
      cities.value.find((c) => c.aqi) ||
      cities.value[0] ||
      null
  } catch (err) {
    console.error('Failed to fetch cities:', err)
  } finally {
    loading.value = false
  }
}

/* ---------------------------------------------------------------- lifecycle */

const closeAll = () => {
  showList.value = null
  showAdd.value = false
}

onMounted(() => {
  document.addEventListener('click', closeAll)
  fetchCities()
})

onUnmounted(() => {
  document.removeEventListener('click', closeAll)
})

watch([city1, city2, hours], loadHistories, { deep: false })
</script>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800&display=swap');

.aqi-graph-page {
  --bg: #0c0f14;
  --panel: #141922;
  --panel-2: #1a2029;
  --border: #242b36;
  --text: #e8ecf2;
  --text-dim: #8992a3;
  --text-faint: #5b6472;
  --accent-blue: #5aa9f7;
  --line: #c3d92e;
  --green: #2fae5c;
  --amber: #e8b83d;
  --radius: 5px;

  font-family: 'Nunito', sans-serif;
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
  border-radius: 12px;
}

.wrap {
  max-width: 1180px;
  margin: 0 auto;
}

.header-row {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 16px;
  margin-bottom: 20px;
}

.eyebrow {
  font-size: 13px;
  color: var(--text-dim);
  margin-bottom: 6px;
}

.title {
  font-size: 26px;
  font-weight: 700;
  margin-bottom: 6px;
}

.location {
  font-size: 15px;
  color: var(--accent-blue);
  font-weight: 600;
}

.controls {
  display: flex;
  gap: 8px;
  align-items: center;
}

.icon-btn {
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--panel-2);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  color: var(--text-dim);
  cursor: pointer;
  font-size: 15px;
  transition: all 0.15s ease;
}

.icon-btn:hover {
  color: var(--text);
  border-color: #3a4354;
}

.icon-btn.active {
  color: var(--text);
  border-color: #3a4354;
}

.menu {
  position: absolute;
  z-index: 50;
  background: var(--panel-2);
  border: 1px solid var(--border);
  border-radius: 8px;
  margin-top: 8px;
  min-width: 240px;
  max-width: 320px;
  padding: 4px 0;
  box-shadow: 0 12px 36px rgba(0, 0, 0, 0.5);
}

.menu.top-full {
  top: 100%;
}

.menu-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 6px 12px 4px;
}

.menu-title {
  font-size: 11px;
  font-weight: 800;
  letter-spacing: 1.2px;
  text-transform: uppercase;
  color: var(--text-faint);
}

.menu-close {
  background: none;
  border: none;
  color: var(--text-faint);
  font-size: 13px;
  cursor: pointer;
  padding: 2px 4px;
}

.menu-close:hover {
  color: var(--text);
}

.menu-search {
  position: relative;
  padding: 6px 12px 8px;
}

.menu-search > i {
  position: absolute;
  left: 21px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 12px;
  color: var(--text-faint);
  pointer-events: none;
}

.menu-list {
  max-height: 260px;
  overflow-y: auto;
  scrollbar-width: none;
  -ms-overflow-style: none;
}

.menu-list::-webkit-scrollbar {
  display: none;
}

.opt {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  font-size: 13px;
  cursor: pointer;
  color: var(--text-dim);
  white-space: nowrap;
}

.opt:hover {
  background: rgba(255, 255, 255, 0.05);
  color: var(--text);
}

.no-results {
  padding: 14px 12px;
  font-size: 13px;
  color: var(--text-faint);
  text-align: center;
}

.flag {
  width: 23px;
  height: 16px;
  object-fit: cover;
  border-radius: 3px;
  border: 1px solid rgba(255, 255, 255, 0.18);
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.45);
  flex-shrink: 0;
}

.search-input {
  width: 100%;
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 8px 10px 8px 30px;
  font-size: 13px;
  font-family: 'Nunito', sans-serif;
  color: var(--text);
  outline: none;
  box-sizing: border-box;
}

.search-input:focus {
  border-color: #3a4354;
}

.card {
  background: var(--panel);
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 24px 28px 8px;
}

.card-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}

.left-controls {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.pill {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--panel-2);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 9px 16px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  user-select: none;
}

.dot {
  width: 9px;
  height: 9px;
  border-radius: 50%;
  background: #aab2c0;
  flex-shrink: 0;
}

.pill.add {
  color: var(--accent-blue);
  border-color: #2b4f79;
  background: transparent;
  cursor: pointer;
}

.pill.add:hover {
  background: rgba(90, 169, 247, 0.08);
}

.stats {
  display: flex;
  gap: 12px;
}

.stat {
  display: flex;
  align-items: center;
  gap: 10px;
  background: var(--panel-2);
  border-radius: 14px;
  padding: 8px 14px;
}

.stat-badge {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 15px;
  color: #0c0f14;
}

.stat-badge.min {
  background: var(--green);
}

.stat-badge.max {
  background: var(--amber);
}

.stat-label {
  font-size: 12px;
  font-weight: 600;
}

.stat-label.min {
  color: var(--green);
}

.stat-label.max {
  color: #f0808a;
}

.stat-time {
  font-size: 11px;
  color: var(--text-faint);
  display: flex;
  align-items: center;
  gap: 4px;
  margin-top: 2px;
}

.chart-area {
  position: relative;
  padding-bottom: 14px;
}

.chart-scroll-outer {
  overflow-x: auto;
  /* Height comes from THIS element's own (natural, unscrolled) width, not
     from chart-scroll-inner's widened width — otherwise a wide chart would
     also render taller, drifting out of sync with the fixed y-axis overlay
     below, which sizes itself the same way. */
  aspect-ratio: 1180 / 420;
}

.no-scrollbar {
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* IE/Edge */
}
.no-scrollbar::-webkit-scrollbar {
  display: none; /* Chrome/Safari */
}

.chart-scroll-inner {
  position: relative;
  min-width: 100%;
  height: 100%;
}

.chart-yaxis-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  aspect-ratio: 1180 / 420;
  pointer-events: none;
  z-index: 15;
}

.yaxis-mask {
  fill: var(--panel);
}

.grid-line {
  stroke: #1f2530;
  stroke-width: 1;
}

.y-label {
  fill: var(--text-faint);
  font-size: 11px;
  font-family: 'Nunito', sans-serif;
}

.x-label {
  fill: var(--text-faint);
  font-size: 10.5px;
  font-family: 'Nunito', sans-serif;
}

.axis-title {
  fill: var(--text-dim);
  font-size: 12px;
  font-weight: 600;
  font-family: 'Nunito', sans-serif;
}

.aqi-line {
  fill: none;
  stroke-width: 2;
  stroke-linejoin: round;
  stroke-linecap: round;
}

.aqi-bar {
  transition: opacity 0.1s ease;
}

.aqi-bar.is-hover {
  opacity: 0.75;
}

.hover-line {
  stroke: #5b6472;
  stroke-width: 1.5;
  stroke-dasharray: 4 4;
  pointer-events: none;
}

.hover-dot-outer {
  opacity: 0.25;
  pointer-events: none;
}

.hover-dot-inner {
  pointer-events: none;
}

.hover-capture {
  position: absolute;
  inset: 0;
  cursor: crosshair;
}

.chart-tooltip {
  position: absolute;
  background: #1c222c;
  border: 1px solid #2e3644;
  border-radius: 8px;
  padding: 10px 14px;
  font-size: 13px;
  box-shadow: 0 10px 24px rgba(0, 0, 0, 0.5);
  pointer-events: none;
  z-index: 10;
  white-space: nowrap;
}

.tooltip-time {
  color: var(--text-dim);
  font-weight: 600;
  font-size: 12.5px;
  margin-bottom: 6px;
}

.tooltip-row {
  display: flex;
  align-items: center;
  gap: 7px;
  font-weight: 700;
  color: var(--text);
}

.tooltip-dot {
  width: 9px;
  height: 9px;
  border-radius: 50%;
  flex-shrink: 0;
}

.date-label {
  font-size: 12px;
  font-weight: 700;
  color: var(--text);
  margin-top: 4px;
}

.date-row {
  display: flex;
  justify-content: space-between;
  padding: 0 4px;
  margin-top: 4px;
}

.empty-chart {
  height: 380px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--text-faint);
  font-size: 14px;
}

.loading-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10;
  background: rgba(12, 15, 20, 0.35);
  border-radius: 8px;
}

.spinner {
  width: 34px;
  height: 34px;
  border-radius: 50%;
  border: 2px solid var(--border);
  border-top-color: var(--accent-blue);
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}
</style>
