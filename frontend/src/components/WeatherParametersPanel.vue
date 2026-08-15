<template>
  <div>
    <div class="flex items-center justify-between px-1 pb-4">
      <h2 class="text-base font-bold" :class="filled ? 'text-white drop-shadow' : 'text-slate-800'">
        <span class="font-extrabold">{{ cityName }}</span> Weather Parameters
      </h2>
      <span class="text-[10px]" :class="filled ? 'text-gray-300 drop-shadow' : 'text-slate-500'">Powered by OpenWeather</span>
    </div>

    <div class="flex flex-wrap gap-4">
      <!-- Direction / Wind Speed -->
      <div class="rounded-xl p-4 flex items-center gap-4 flex-[2_1_420px]" style="background-color: #627379;">
        <svg viewBox="0 0 100 100" class="w-20 h-20 flex-shrink-0">
          <circle cx="50" cy="50" r="42" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="1" />
          <circle cx="50" cy="50" r="30" fill="none" stroke="rgba(255,255,255,0.2)" stroke-dasharray="2 3" stroke-width="1" />
          <text x="50" y="12" text-anchor="middle" class="fill-white/70" font-size="7">N</text>
          <text x="50" y="94" text-anchor="middle" class="fill-white/70" font-size="7">S</text>
          <text x="92" y="53" text-anchor="middle" class="fill-white/70" font-size="7">E</text>
          <text x="8" y="53" text-anchor="middle" class="fill-white/70" font-size="7">W</text>
          <circle cx="50" cy="50" r="18" fill="#1f2937" stroke="rgba(255,255,255,0.2)" />
          <text x="50" y="47" text-anchor="middle" class="fill-white font-bold" font-size="10">{{ windDeg != null ? Math.round(windDeg) + '°' : '—' }}</text>
          <text x="50" y="57" text-anchor="middle" class="fill-blue-300 font-bold" font-size="7">{{ windCompass }}</text>
          <g v-if="windDeg != null" :style="{ transform: `rotate(${windDeg}deg)`, transformOrigin: '50px 50px' }">
            <path d="M50 10 L45 24 L50 20 L55 24 Z" fill="#60a5fa" />
          </g>
        </svg>
        <div class="min-w-0">
          <p class="text-[10px] uppercase tracking-wide text-white/80">Direction</p>
          <div class="flex items-center gap-1.5 mt-2">
            <img :src="windIcon" alt="Wind" class="w-4 h-4 [filter:brightness(0)_invert(1)] opacity-80" />
            <span class="text-lg font-bold text-white">{{ windSpeedKmh }}</span>
            <span class="text-xs text-white/80">km/h</span>
          </div>
          <p class="text-[10px] mt-1 text-white/80">Wind Speed</p>
        </div>
      </div>

      <!-- Gust Speed -->
      <div class="rounded-xl p-4 flex flex-col items-center text-center flex-[1_1_220px]" :class="tileClass">
        <img :src="gustIcon" alt="" class="w-full h-20 object-contain" />
        <p class="text-[10px] uppercase tracking-wide mt-2" :class="mutedText">Gust Speed</p>
        <p class="text-xl font-bold" :class="valueText">{{ gustSpeedMs }}<span class="text-xs font-medium ml-1" :class="mutedText">m/s</span></p>
      </div>

      <!-- Cloud Cover / Visibility -->
      <div class="relative rounded-xl p-4 overflow-hidden flex-[2_1_420px]" :class="tileClass">
        <div class="absolute inset-0 opacity-90 pointer-events-none">
          <svg viewBox="0 0 200 60" class="w-full h-full" preserveAspectRatio="xMidYMid slice">
            <!-- Each cloud is a cluster of overlapping circles (classic puffy-cloud technique) -->
            <g class="cloud-drift cloud-drift--1" fill="#ffffff">
              <circle cx="10" cy="26" r="7" /><circle cx="18" cy="21" r="9" /><circle cx="28" cy="24" r="7.5" /><circle cx="20" cy="29" r="7" />
              <rect x="6" y="24" width="26" height="9" rx="4.5" />
            </g>
            <g class="cloud-drift cloud-drift--2" fill="#f3f8ff">
              <circle cx="8" cy="20" r="5.5" /><circle cx="14" cy="16" r="7" /><circle cx="22" cy="19" r="6" /><circle cx="16" cy="23" r="5.5" />
              <rect x="4" y="18" width="20" height="7" rx="3.5" />
            </g>
            <g class="cloud-drift cloud-drift--3" fill="#dce6f5" opacity="0.85">
              <circle cx="9" cy="16" r="4.5" /><circle cx="14" cy="13" r="5.5" /><circle cx="20" cy="15.5" r="5" /><circle cx="15" cy="18.5" r="4.5" />
              <rect x="5" y="14" width="17" height="6" rx="3" />
            </g>
          </svg>
        </div>
        <div class="relative flex items-end justify-between h-full pt-8">
          <div>
            <p class="text-[10px] uppercase tracking-wide" :class="mutedTextStrong">Cloud Cover</p>
            <p class="text-lg font-bold" :class="valueText">{{ clouds != null ? clouds + ' %' : '—' }}</p>
          </div>
          <div class="text-right">
            <p class="text-[10px] uppercase tracking-wide" :class="mutedTextStrong">Visibility</p>
            <p class="text-lg font-bold" :class="valueText">{{ visibilityKm }}</p>
          </div>
        </div>
      </div>

      <!-- Precipitation -->
      <div class="rounded-xl p-4 flex flex-col gap-2 flex-[1.2_1_260px]" style="background-color: #5E6E75;">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-lg bg-blue-500/20 flex items-center justify-center flex-shrink-0">
            <img :src="precipIcon" alt="Precipitation" class="w-4 h-4" />
          </div>
          <div>
            <p class="text-[10px] uppercase tracking-wide text-white/80">Precipitation</p>
            <p class="text-lg font-bold text-white">{{ rain1h }} <span class="text-xs font-medium text-white/80">mm</span></p>
          </div>
        </div>
        <p class="text-[10px] leading-snug text-white/80">Current precipitation over the last hour sits at {{ rain1h }}mm.</p>
      </div>

      <!-- Pressure -->
      <div class="rounded-xl p-4 flex flex-col gap-2 flex-[1.5_1_320px]" :class="tileClass">
        <div class="flex items-center gap-3">
          <svg viewBox="0 0 100 60" class="w-14 h-9 flex-shrink-0">
            <path d="M8 52 A42 42 0 0 1 92 52" fill="none" stroke="url(#pressureGrad)" stroke-width="7" stroke-linecap="round" />
            <defs>
              <linearGradient id="pressureGrad" x1="0" y1="0" x2="1" y2="0">
                <stop offset="0%" stop-color="#22c55e" />
                <stop offset="50%" stop-color="#eab308" />
                <stop offset="100%" stop-color="#ef4444" />
              </linearGradient>
            </defs>
            <g :style="{ transform: `rotate(${pressureAngle}deg)`, transformOrigin: '50px 52px' }">
              <line x1="50" y1="52" x2="50" y2="16" :stroke="needleStroke" stroke-width="2" stroke-linecap="round" />
            </g>
            <circle cx="50" cy="52" r="3.5" :fill="needleStroke" />
          </svg>
          <div>
            <p class="text-[10px] uppercase tracking-wide" :class="mutedText">Pressure</p>
            <p class="text-lg font-bold" :class="valueText">{{ pressure ?? '—' }} <span class="text-xs font-medium" :class="mutedText">mb</span></p>
          </div>
        </div>
        <p class="text-[10px] leading-snug" :class="mutedText">Current pressure level is {{ pressure ?? '—' }}mb.</p>
      </div>

      <!-- UV Index -->
      <div class="rounded-xl p-4 flex flex-col gap-2 flex-[1_1_220px]" style="background-color: rgb(79, 155, 28);">
        <p class="text-[10px] uppercase tracking-wide text-white/80">UV Index</p>
        <p class="text-lg font-bold text-white">Not available</p>
        <div class="h-1.5 rounded-full bg-gradient-to-r from-green-500 via-yellow-400 via-orange-500 to-purple-600 opacity-70"></div>
        <p class="text-[10px] leading-snug text-white/80">Live UV data isn't provided by our weather source yet.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import windIcon from '@/assets/images/svg/wind-speed.svg';
import gustIcon from '@/assets/images/svg/gust-speed.svg';
import precipIcon from '@/assets/images/svg/icon-precipitation.svg';

const props = defineProps({
  cityName: { type: String, default: '' },
  weather: { type: Object, default: null },
  // Dark glass chip behind each tile. Off by default (just a hairline border) for pages
  // where the tiles sit directly on a photo backdrop; on for pages that want the extra
  // contrast/definition of a filled card.
  filled: { type: Boolean, default: false },
});

const tileClass = computed(() => (props.filled ? 'bg-black/75' : 'border border-black/10'));
const mutedText = computed(() => (props.filled ? 'text-gray-200' : 'text-slate-500'));
const mutedTextStrong = computed(() => (props.filled ? 'text-gray-100' : 'text-slate-600'));
const valueText = computed(() => (props.filled ? 'text-white' : 'text-slate-800'));
const ringStroke = computed(() => (props.filled ? 'rgba(255,255,255,0.15)' : 'rgba(0,0,0,0.15)'));
const labelFill = computed(() => (props.filled ? 'fill-gray-400' : 'fill-slate-500'));
const needleStroke = computed(() => (props.filled ? '#f1f5f9' : '#1f2937'));

const windDeg = computed(() => props.weather?.wind_deg ?? null);
const windSpeedKmh = computed(() => {
  const v = props.weather?.wind_speed;
  return v != null ? Math.round(v * 3.6) : '—';
});
const gustSpeedMs = computed(() => {
  const v = props.weather?.wind_gust;
  return v != null ? v.toFixed(1) : '—';
});
const clouds = computed(() => props.weather?.clouds ?? null);
const visibilityKm = computed(() => {
  const v = props.weather?.visibility;
  return v != null ? (v / 1000).toFixed(1) + ' km' : '—';
});
const rain1h = computed(() => {
  const v = props.weather?.rain_1h;
  return v != null ? Number(v).toFixed(2) : '0.00';
});
const pressure = computed(() => props.weather?.pressure ?? null);
// Semicircle gauge: typical sea-level range ~950-1050 hPa mapped across -90deg..+90deg.
const pressureAngle = computed(() => {
  const p = pressure.value;
  if (p == null) return -90;
  const clamped = Math.min(Math.max(p, 950), 1050);
  return -90 + ((clamped - 950) / 100) * 180;
});

const COMPASS_POINTS = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
const windCompass = computed(() => {
  if (windDeg.value == null) return '';
  const idx = Math.round(windDeg.value / 22.5) % 16;
  return COMPASS_POINTS[idx];
});
</script>

<style scoped>
.cloud-drift {
  animation: cloud-drift-x linear infinite;
}
.cloud-drift--1 { animation-duration: 22s; transform: translateY(-6px) scale(0.9); }
.cloud-drift--2 { animation-duration: 16s; animation-delay: -6s; transform: translateY(6px) scale(1.15); }
.cloud-drift--3 { animation-duration: 28s; animation-delay: -12s; transform: translateY(0px) scale(0.75); }

@keyframes cloud-drift-x {
  from { transform: translateX(-40px); }
  to { transform: translateX(240px); }
}
</style>
