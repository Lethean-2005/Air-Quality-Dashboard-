<template>
  <div class="min-h-screen -m-6 bg-[#0a0e17] pb-6" style="font-family: 'Nunito Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;">
    <div class="relative overflow-hidden">
      <!-- Whole-section backdrop for the Weather tab (not confined to a card) — pinned to the
           viewport (background-attachment: fixed) so it stays put on screen while the
           breadcrumb, tabs, title and hero content scroll over it like normal cards. -->
      <div
        v-if="cityData && activeHeroTab === 'weather'"
        class="absolute inset-0 z-0 pointer-events-none"
        :style="{ backgroundImage: `url(${weatherSceneBg})`, backgroundSize: 'cover', backgroundPosition: 'center', backgroundAttachment: 'fixed' }"
      ></div>
      <div
        v-if="cityData && activeHeroTab === 'weather'"
        class="absolute inset-0 z-0 pointer-events-none"
        style="background: linear-gradient(180deg, rgba(255,255,255,0.12) 0%, rgba(255,255,255,0.04) 35%, rgba(255,255,255,0.02) 70%, rgba(10,14,23,0.6) 94%, #0a0e17 100%);"
      ></div>

    <!-- Loading skeleton: breadcrumb, tabs, title, hero/weather card -->
    <div v-if="loading" class="wide-container relative z-10" style="margin-top: 2rem;">
      <div class="flex items-center gap-2 mb-4">
        <Skeleton class="h-3 w-3 rounded-full bg-white/10" />
        <Skeleton class="h-3 w-3 rounded-full bg-white/10" />
        <Skeleton class="h-3 w-24 bg-white/10" />
      </div>
      <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
        <Skeleton class="h-9 w-40 rounded-lg bg-white/10" />
        <div class="flex items-center gap-1.5">
          <Skeleton v-for="i in 5" :key="i" class="h-8 w-16 rounded-full bg-white/10" />
        </div>
      </div>
      <div class="mb-6 space-y-2">
        <Skeleton class="h-5 w-20 rounded-md bg-white/10" />
        <Skeleton class="h-7 w-full max-w-xl bg-white/10" />
        <Skeleton class="h-3 w-56 bg-white/10" />
      </div>
      <div v-if="activeHeroTab === 'aqi'" class="relative overflow-hidden rounded-2xl border border-white/10 p-8 md:p-10 bg-white/5">
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_auto_18rem] items-end gap-6">
          <div class="flex flex-col gap-4">
            <div class="flex flex-wrap items-start gap-8">
              <div class="space-y-2">
                <Skeleton class="h-3 w-16 bg-white/10" />
                <Skeleton class="h-14 w-28 bg-white/10" />
              </div>
              <div class="space-y-2">
                <Skeleton class="h-3 w-16 bg-white/10" />
                <Skeleton class="h-8 w-24 rounded-md bg-white/10" />
              </div>
            </div>
            <div class="flex items-center gap-8">
              <Skeleton class="h-4 w-28 bg-white/10" />
              <Skeleton class="h-4 w-28 bg-white/10" />
            </div>
            <Skeleton class="h-3 w-full max-w-md bg-white/10" />
          </div>
          <div class="hidden lg:block"><Skeleton class="h-44 w-32 rounded-xl bg-white/10" /></div>
          <Skeleton class="h-40 w-full lg:w-72 rounded-xl bg-white/10" />
        </div>
      </div>
      <div v-else class="relative p-2 md:p-4">
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_480px] gap-6">
          <div class="flex flex-col gap-4">
            <div class="flex items-center gap-3">
              <Skeleton class="h-14 w-14 rounded-full bg-white/10" />
              <div class="space-y-2">
                <Skeleton class="h-10 w-24 bg-white/10" />
                <Skeleton class="h-3 w-32 bg-white/10" />
              </div>
            </div>
            <Skeleton class="h-6 w-28 rounded-full bg-white/10" />
            <div class="flex flex-wrap gap-3">
              <Skeleton class="h-14 flex-1 min-w-[220px] rounded-xl bg-white/10" />
              <Skeleton class="h-14 flex-1 min-w-[220px] rounded-xl bg-white/10" />
            </div>
          </div>
          <Skeleton class="h-64 w-full rounded-2xl bg-white/10" />
        </div>
      </div>
    </div>

    <div v-if="cityData" class="wide-container relative z-10" style="margin-top: 2rem;">
      <!-- Breadcrumb -->
      <div class="flex items-center gap-2 text-xs flex-wrap mb-4" :class="activeHeroTab === 'weather' ? 'text-slate-600' : 'text-gray-400'">
        <RouterLink to="/home" class="transition-colors" :class="activeHeroTab === 'weather' ? 'text-slate-600 hover:text-slate-900' : 'text-gray-400 hover:text-white'">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l9-9 9 9M5 10v10a1 1 0 001 1h4a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1h4a1 1 0 001-1V10"/></svg>
        </RouterLink>
        <template v-for="(seg, i) in displayBreadcrumb" :key="i">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" :class="activeHeroTab === 'weather' ? 'text-slate-400' : 'text-gray-600'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
          <span :class="i === displayBreadcrumb.length - 1 ? 'text-blue-500 font-semibold' : (activeHeroTab === 'weather' ? 'text-slate-600' : 'text-gray-400')">{{ seg }}</span>
        </template>
      </div>

      <!-- Tabs + metric pills -->
      <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
        <div class="flex items-center gap-1 border border-white/20 rounded-lg p-1">
          <button
            @click="router.push(`/aqi/city/${cityIdParam}`)"
            class="px-3 py-1.5 rounded-md text-xs font-semibold transition-colors flex items-center gap-1.5"
            :class="activeHeroTab === 'aqi' ? 'bg-blue-500 text-white' : 'text-slate-700 hover:bg-black/10'"
          >AQI</button>
          <button
            @click="router.push(`/weather/city/${cityIdParam}`); if (!weatherForecast) fetchWeatherForecast();"
            class="px-3 py-1.5 rounded-md text-xs font-semibold transition-colors flex items-center gap-1.5"
            :class="activeHeroTab === 'weather' ? 'bg-blue-500 text-white' : 'text-gray-300 hover:bg-white/5'"
          >Weather</button>
        </div>

        <template v-if="activeHeroTab === 'aqi'">
          <div v-if="loading" class="flex items-center gap-1.5 flex-wrap">
            <Skeleton v-for="i in 5" :key="i" class="h-8 w-16 rounded-full bg-white/10" />
          </div>
          <div v-else class="flex items-center gap-1.5 flex-wrap">
            <button
              @click="setMetric('aqi')"
              class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors"
              :class="metric === 'aqi' ? 'bg-blue-500 border-blue-500 text-white' : 'bg-transparent border-white/15 text-gray-300 hover:border-white/30'"
            >AQI</button>
            <template v-for="m in metricOptions.filter(o => o.key !== 'aqi')" :key="m.key">
              <button
                @click="setMetric(m.key)"
                class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-colors"
                :class="metric === m.key ? 'bg-blue-500 border-blue-500 text-white' : 'bg-transparent border-white/15 text-gray-300 hover:border-white/30'"
              >{{ m.label.replace(' (US)', '') }}</button>
            </template>
          </div>
        </template>
      </div>

      <div class="flex items-start justify-between gap-4 flex-wrap mb-4">
        <div v-if="activeHeroTab === 'aqi'">
          <span class="inline-flex items-center gap-1.5 bg-red-500/15 text-red-400 text-[10px] font-bold px-2 py-1 rounded-md mb-2">
            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span> LIVE
          </span>
          <h1 class="text-2xl font-bold text-white">{{ breadcrumbSegments[breadcrumbSegments.length - 1] }} Air Quality Index (AQI) &amp; Air Pollution</h1>
          <p class="text-gray-400 text-sm mt-1">Real-time PM2.5, PM10 air pollution level in {{ heroSubtitleRegion }}</p>
          <p v-if="lastUpdated" class="text-xs text-gray-500 italic mt-1">Last Updated: {{ lastUpdated }} (Local Time)</p>
        </div>
        <div v-else>
          <div class="flex items-center gap-1.5 text-slate-800 text-lg font-bold">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 12.414a4 4 0 10-5.657 5.657l4.243 4.243a8 8 0 1011.314-11.314l-4.243 4.243a4 4 0 00-5.657 5.657z" /></svg>
            {{ breadcrumbSegments[breadcrumbSegments.length - 1] }} Weather Conditions
          </div>
          <p class="text-slate-500 text-sm mt-1">Current Temperature Level</p>
        </div>
        <div v-if="loading" class="flex items-center gap-2 flex-shrink-0">
          <Skeleton class="h-8 w-24 rounded-md bg-white/10" />
          <Skeleton class="h-8 w-28 rounded-md bg-white/10" />
          <Skeleton class="w-8 h-8 rounded-full bg-white/10" />
          <Skeleton class="w-8 h-8 rounded-full bg-white/10" />
        </div>
        <div v-else class="flex items-center gap-2 flex-shrink-0">
          <RouterLink
            to="/world-map"
            class="flex items-center gap-1.5 h-8 text-xs font-medium border px-3 rounded-md transition-colors"
            :class="activeHeroTab === 'weather' ? 'text-slate-800 border-slate-400/40 hover:bg-black/10' : 'text-white border-white/15 hover:bg-white/10'"
          >
            <span class="font-bold text-blue-500">AQI</span> Map
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
          </RouterLink>
          <button
            @click="locateMe"
            class="flex items-center gap-1.5 h-8 text-xs font-medium border px-4 rounded-md transition-colors"
            :class="activeHeroTab === 'weather' ? 'text-blue-700 border-blue-500/50 hover:bg-black/10' : 'text-blue-300 border-blue-400/60 hover:bg-white/10'"
          >
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 12a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M4 12a8 8 0 1 0 16 0a8 8 0 1 0 -16 0" /><path d="M12 2l0 2" /><path d="M12 20l0 2" /><path d="M20 12l2 0" /><path d="M2 12l2 0" />
            </svg>
            Locate me
          </button>
          <button
            @click="toggleFavourite"
            class="w-8 h-8 flex items-center justify-center rounded-full border transition-colors"
            :class="isFavourite ? 'text-red-400 border-red-400' : (activeHeroTab === 'weather' ? 'text-slate-800 border-slate-400/40 hover:bg-black/10' : 'text-white border-blue-400/60 hover:bg-white/10')"
          >
            <svg class="w-4 h-4" :fill="isFavourite ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
          </button>
          <button
            @click="shareCity"
            class="w-8 h-8 flex items-center justify-center rounded-full border transition-colors"
            :class="activeHeroTab === 'weather' ? 'text-slate-800 border-slate-400/40 hover:bg-black/10' : 'text-white border-white/20 hover:bg-white/10'"
          >
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 12a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M15 6a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M15 18a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" /><path d="M8.7 10.7l6.6 -3.4" /><path d="M8.7 13.3l6.6 3.4" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Hero AQI card -->
      <div v-if="activeHeroTab === 'aqi'" class="relative overflow-hidden rounded-2xl shadow-2xl border border-white/10 p-8 md:p-10 transition-[background] duration-500" :style="{ background: heroCardBackground }">
        <div class="relative z-10 grid grid-cols-1 lg:grid-cols-[1fr_auto_18rem] items-end gap-6">
          <div class="flex flex-col gap-4">
            <div class="flex flex-wrap items-start gap-8">
              <div>
                <div class="flex items-center gap-2 text-xs text-gray-300 mb-1">
                  <span class="w-2 h-2 rounded-full bg-red-500"></span> Live AQI
                </div>
                <div class="flex items-baseline gap-2">
                  <span class="text-6xl font-extrabold text-white">{{ cityData?.aqi ?? 'N/A' }}</span>
                  <span class="text-xs text-gray-300">AQI (US)</span>
                </div>
              </div>
              <div>
                <p class="text-xs text-gray-300 mb-1">Air Quality is</p>
                <div class="flex items-center h-8 px-4 rounded-md text-lg font-bold text-white" :style="{ backgroundColor: heroAqiStatus.color }">
                  {{ heroAqiStatus.label }}
                </div>
              </div>
            </div>

            <div class="flex items-center gap-8 text-sm text-white">
              <div>PM2.5 : <strong>{{ cityData?.pm25 ?? 'N/A' }}</strong> µg/m³</div>
              <div>PM10 : <strong>{{ cityData?.pm10 ?? 'N/A' }}</strong> µg/m³</div>
            </div>

            <div class="max-w-md">
              <div class="flex justify-between text-[10px] font-bold text-gray-300 mb-1">
                <span v-for="lvl in aqiBandLevels" :key="lvl.label">{{ lvl.label }}</span>
              </div>
              <div class="relative">
                <div class="flex h-1.5 rounded-full overflow-hidden">
                  <div v-for="lvl in aqiBandLevels" :key="lvl.label" class="flex-1" :style="{ backgroundColor: lvl.color }"></div>
                </div>
                <div
                  class="absolute -top-1 w-3.5 h-3.5 rounded-full border-2 border-slate-900 shadow"
                  :style="{ left: heroAqiMarkerPercent + '%', transform: 'translateX(-50%)', backgroundColor: heroAqiStatus.color }"
                ></div>
              </div>
              <div class="flex justify-between text-[10px] text-gray-400 mt-1">
                <span>0</span><span>50</span><span>100</span><span>150</span><span>200</span><span>300</span><span>301+</span>
              </div>
            </div>
          </div>

          <!-- AQI mascot -->
          <div class="hidden lg:flex items-end justify-center pointer-events-none">
            <img v-if="heroAqiMascot" :src="heroAqiMascot" :alt="heroAqiStatus.label" class="drop-shadow-xl" style="height: 180px; width: auto;" />
          </div>

          <!-- Weather chip -->
          <div class="relative w-full lg:w-72 bg-black/40 border border-white/15 rounded-xl p-4 flex-shrink-0">
            <div class="flex items-center gap-3">
              <span class="text-3xl">🌤️</span>
              <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold text-white">{{ cityData?.temperature ?? '—' }}<span class="text-sm font-normal text-gray-300">°C</span></span>
              </div>
            </div>
            <div class="grid grid-cols-3 gap-2 mt-4 pt-3 border-t border-white/15">
              <div class="text-left leading-tight min-w-0">
                <p class="text-[9px] text-gray-400 whitespace-nowrap">Humidity</p>
                <p class="text-xs font-semibold text-white">{{ cityData?.humidity ?? '—' }}%</p>
              </div>
              <div class="text-left leading-tight min-w-0">
                <p class="text-[9px] text-gray-400 whitespace-nowrap">Wind Speed</p>
                <p class="text-xs font-semibold text-white">{{ cityData?.wind_speed ?? cityData?.wind ?? '—' }}</p>
              </div>
              <div class="text-left leading-tight min-w-0">
                <p class="text-[9px] text-gray-400 whitespace-nowrap">Pressure</p>
                <p class="text-xs font-semibold text-white">{{ cityData?.pressure ?? '—' }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Weather content: floats directly on the whole-section backdrop above, no card box -->
      <div v-else class="relative p-2 md:p-4">
        <div class="grid grid-cols-1 lg:grid-cols-[1fr_480px] gap-6">
          <!-- Left: current conditions -->
          <div class="flex flex-col gap-4">
            <div class="flex items-start gap-6 flex-wrap">
              <div class="flex items-center gap-3">
                <span class="text-5xl">{{ weatherIconEmoji(weatherForecast?.current?.icon) }}</span>
                <div>
                  <div class="flex items-baseline gap-1">
                    <span class="text-5xl font-extrabold text-slate-800">{{ weatherForecast?.current?.temp ?? '—' }}</span>
                    <span class="text-2xl text-slate-500">°C</span>
                  </div>
                  <p class="text-xs text-slate-500 mt-1 flex items-center gap-3">
                    <span>&uarr; {{ weatherForecast?.current?.temp_max_today ?? '—' }}°C</span>
                    <span>&darr; {{ weatherForecast?.current?.temp_min_today ?? '—' }}°C</span>
                  </p>
                </div>
              </div>
              <div class="text-sm text-slate-700 pt-1">
                <p class="capitalize font-semibold">{{ weatherForecast?.current?.description ?? (weatherLoading ? 'Loading…' : '—') }}</p>
                <p class="text-xs text-slate-500 mt-1 flex items-center gap-1"><img :src="temperatureIcon" alt="" class="w-4 h-4 opacity-70" /> Feels Like <strong class="text-slate-700">{{ weatherForecast?.current?.feels_like ?? '—' }}°C</strong></p>
                <p class="text-xs text-slate-500 flex items-center gap-1"><img :src="rainfallIcon" alt="" class="w-4 h-4 opacity-70" /> Chances of Rain <strong class="text-slate-700">{{ weatherForecast?.current?.pop_next ?? 0 }}%</strong></p>
              </div>
            </div>

            <span
              v-if="weatherForecast"
              class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold text-white w-fit"
              :style="{ backgroundColor: tempBadge.color }"
            >{{ tempBadge.label }}</span>

            <div class="flex flex-wrap gap-3 mt-1">
              <div class="relative flex items-center gap-2 border border-black/10 rounded-xl px-3 py-2 flex-none w-[220px]">
                <Skeleton v-if="weatherLoading" class="absolute top-2 right-2 w-5 h-5 rounded-full" />
                <RouterLink
                  v-else
                  to="/analytics"
                  class="absolute top-2 right-2 w-5 h-5 flex items-center justify-center rounded-full bg-slate-800 text-white hover:bg-slate-700 transition-colors"
                  title="View more"
                >
                  <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M17 7H7M17 7V17"/></svg>
                </RouterLink>
                <svg viewBox="0 0 100 60" class="w-11 h-8 flex-shrink-0">
                  <path d="M8 52 A42 42 0 0 1 92 52" fill="none" stroke="url(#cityAqiGaugeGrad)" stroke-width="9" stroke-linecap="round" />
                  <defs>
                    <!-- 6 equal-width bands matching aqiBandLevels (Good/Moderate/Poor/Unhealthy/Severe/Hazardous),
                         same split the needle angle is computed against, so the needle always lands in its own band. -->
                    <linearGradient id="cityAqiGaugeGrad" x1="0" y1="0" x2="1" y2="0">
                      <stop offset="0%" stop-color="#4cd964" /><stop offset="16.667%" stop-color="#4cd964" />
                      <stop offset="16.667%" stop-color="#ffcc00" /><stop offset="33.333%" stop-color="#ffcc00" />
                      <stop offset="33.333%" stop-color="#ff9500" /><stop offset="50%" stop-color="#ff9500" />
                      <stop offset="50%" stop-color="#ff2d55" /><stop offset="66.667%" stop-color="#ff2d55" />
                      <stop offset="66.667%" stop-color="#af52de" /><stop offset="83.333%" stop-color="#af52de" />
                      <stop offset="83.333%" stop-color="#ff3b30" /><stop offset="100%" stop-color="#ff3b30" />
                    </linearGradient>
                  </defs>
                  <g :style="{ transform: `rotate(${heroAqiGaugeAngle}deg)`, transformOrigin: '50px 52px' }">
                    <line x1="50" y1="52" x2="50" y2="16" stroke="#1f2937" stroke-width="2" stroke-linecap="round" />
                  </g>
                  <circle cx="50" cy="52" r="3.5" fill="#1f2937" />
                </svg>
                <div class="min-w-0">
                  <p class="text-base font-bold text-slate-800 leading-tight">{{ cityData?.aqi ?? 'N/A' }} <span class="text-[10px] font-medium text-slate-500">AQI</span></p>
                  <p class="text-[9px] font-bold uppercase tracking-wide" :style="{ color: heroAqiStatus.color }">{{ heroAqiStatus.label }}</p>
                </div>
              </div>
              <div class="flex items-center gap-2 border border-black/10 rounded-xl px-3 py-2 flex-none w-[220px]">
                <div class="w-8 h-8 rounded-full bg-sky-100 flex items-center justify-center flex-shrink-0">
                  <i class="fa-solid fa-droplet text-sky-500 text-sm"></i>
                </div>
                <div class="min-w-0">
                  <p class="text-base font-bold text-slate-800 leading-tight">{{ weatherForecast?.current?.humidity ?? '—' }}%</p>
                  <p class="text-[9px] font-bold uppercase tracking-wide text-blue-700">Humidity</p>
                </div>
              </div>
            </div>

            <p v-if="lastUpdated" class="text-xs text-slate-500 italic mt-1">Last Updated: {{ lastUpdated }} (Local Time)</p>
          </div>

          <!-- Right: hourly/daily forecast -->
          <div class="relative bg-black/35 backdrop-blur-xl border border-white/10 rounded-2xl p-5 shadow-lg">
            <div class="flex items-center bg-white/10 rounded-[3px] p-1 w-fit mb-3">
              <button
                @click="forecastRange = 'hourly'"
                class="px-3 py-1 rounded-sm text-xs font-medium transition-colors"
                :class="forecastRange === 'hourly' ? 'bg-blue-500 text-white' : 'text-gray-300 hover:text-white'"
              >Hourly</button>
              <button
                @click="forecastRange = 'daily'"
                class="px-3 py-1 rounded-sm text-xs font-medium transition-colors"
                :class="forecastRange === 'daily' ? 'bg-blue-500 text-white' : 'text-gray-300 hover:text-white'"
              >Daily</button>
            </div>

            <div v-if="weatherLoading" class="flex gap-4 pr-8 overflow-hidden">
              <div v-for="i in 8" :key="i" class="flex flex-col items-center gap-1.5 w-14 flex-shrink-0">
                <Skeleton class="h-2.5 w-8 bg-white/10" />
                <Skeleton class="h-6 w-6 rounded-full bg-white/10" />
                <Skeleton class="h-3 w-6 bg-white/10" />
              </div>
            </div>
            <template v-else-if="(forecastRange === 'hourly' ? weatherForecast?.hourly : weatherForecast?.daily)?.length">
              <button
                type="button"
                title="Scroll"
                @click="scrollForecastRight"
                class="absolute right-2 top-1/2 -translate-y-1/2 w-6 h-6 flex items-center justify-center rounded-md bg-white text-slate-700 shadow-md hover:bg-gray-100 transition-colors z-10"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                  <path stroke-linecap="round" stroke-linejoin="round" :d="forecastAtEnd ? 'M15 5l-6 7 6 7' : 'M9 5l7 7-7 7'"/>
                </svg>
              </button>
              <div class="pr-8">
                <div ref="forecastScrollRef" class="overflow-x-auto no-scrollbar" @scroll="checkForecastEnd">
                  <div class="flex gap-4 min-w-max">
                    <div
                      v-for="(row, i) in forecastAllRows"
                      :key="i"
                      class="flex flex-col items-center gap-1 w-14 flex-shrink-0"
                    >
                      <span class="text-[10px] text-white/90 whitespace-nowrap">{{ forecastRange === 'hourly' ? formatForecastTime(row.time, i === 0) : formatForecastDay(row.date, i === 0) }}</span>
                      <img v-if="weatherConditionIcon(row.icon)" :src="weatherConditionIcon(row.icon)" alt="" class="w-6 h-6" />
                      <span v-else class="text-lg">{{ weatherIconEmoji(row.icon) }}</span>
                      <span class="text-xs font-bold text-white">{{ (forecastRange === 'hourly' ? row.temp : row.max) }}&deg;</span>
                    </div>
                  </div>
                  <svg viewBox="0 0 100 30" preserveAspectRatio="none" class="h-8 mt-1" :style="{ width: forecastStripWidth + 'px' }">
                    <path :d="forecastSparkline" fill="none" stroke="#f87171" stroke-width="1.5" vector-effect="non-scaling-stroke" />
                    <circle
                      v-for="(pt, i) in forecastSparklinePoints"
                      :key="i"
                      :cx="pt.x"
                      :cy="pt.y"
                      r="1.6"
                      fill="#f87171"
                      vector-effect="non-scaling-stroke"
                    />
                  </svg>
                  <div class="flex gap-4 min-w-max mt-1">
                    <div
                      v-for="(row, i) in forecastAllRows"
                      :key="'p' + i"
                      class="flex items-center justify-center gap-1 w-14 flex-shrink-0 text-[10px] text-white/90"
                    >
                      <i class="fa-solid fa-cloud-rain text-[9px]"></i>{{ row.pop }}%
                    </div>
                  </div>
                </div>
              </div>
              <p class="text-[10px] text-white/80 mt-3 capitalize">{{ weatherForecast.hourly[0]?.description }} may develop in some areas.</p>
            </template>
            <p v-else class="text-xs text-gray-500 text-center py-8">No forecast data available.</p>
          </div>
        </div>
      </div>

      <WeatherParametersPanel
        v-if="activeHeroTab === 'weather'"
        class="mt-6"
        :city-name="breadcrumbSegments[breadcrumbSegments.length - 1]"
        :weather="weatherForecast?.current"
      />
    </div>

    <!-- Loading skeleton: pollutants -->
    <div v-if="loading" class="wide-container relative z-10">
      <Skeleton class="h-5 w-56 mb-4 bg-white/10" />
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div v-for="i in 6" :key="i" class="flex items-center gap-4 bg-white border border-gray-100 rounded-xl p-5">
          <Skeleton class="w-12 h-12 rounded-md flex-shrink-0" />
          <div class="flex-1 space-y-2">
            <Skeleton class="h-4 w-24" />
            <Skeleton class="h-3 w-12" />
          </div>
          <Skeleton class="h-6 w-10 flex-shrink-0" />
        </div>
      </div>
    </div>

    <!-- Major Air Pollutants -->
    <div v-if="cityData" class="wide-container relative z-10">
      <h2 class="text-xl font-bold text-white mb-4">Major Air Pollutants</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="p in pollutantCards"
          :key="p.key"
          class="relative flex items-center gap-4 bg-white border border-gray-100 rounded-xl p-5 overflow-hidden shadow-sm"
        >
          <div class="absolute left-0 top-0 bottom-0 w-1.5" :style="{ backgroundColor: p.color }"></div>
          <img :src="p.icon" :alt="p.label" class="w-12 h-12 flex-shrink-0" />
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-slate-900">{{ p.label }}</p>
            <p class="text-xs text-slate-500">({{ p.abbr }})</p>
          </div>
          <div class="text-right flex-shrink-0">
            <p class="text-xl font-bold text-slate-900">{{ p.value ?? 'N/A' }}</p>
            <p class="text-xs text-slate-400">{{ p.unit }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading skeleton: chart -->
    <div v-if="loading" class="wide-container relative z-10">
      <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <div class="space-y-2">
          <Skeleton class="h-3 w-20 bg-white/10" />
          <Skeleton class="h-5 w-48 bg-white/10" />
        </div>
        <div class="flex items-center gap-2">
          <Skeleton class="h-9 w-9 rounded-lg bg-white/10" />
          <Skeleton class="h-9 w-9 rounded-lg bg-white/10" />
          <Skeleton class="h-9 w-28 rounded-lg bg-white/10" />
          <Skeleton class="h-9 w-28 rounded-lg bg-white/10" />
        </div>
      </div>
      <div class="bg-[#131722] border border-white/10 rounded-2xl p-5">
        <Skeleton class="w-full h-[220px] rounded-lg bg-white/5" />
      </div>
    </div>

    <!-- AQI Graph -->
    <div v-if="cityData" class="wide-container relative z-10">
      <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <div>
          <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">AQI Graph</p>
          <h2 class="text-xl font-bold text-white">Historical Air Quality Data</h2>
        </div>

        <div v-if="loading" class="flex items-center gap-2">
          <Skeleton class="h-9 w-9 rounded-lg bg-white/10" />
          <Skeleton class="h-9 w-9 rounded-lg bg-white/10" />
          <Skeleton class="h-9 w-28 rounded-lg bg-white/10" />
          <Skeleton class="h-9 w-28 rounded-lg bg-white/10" />
        </div>
        <div v-else class="flex items-center gap-2" ref="graphControlsRef">
          <button
            type="button"
            title="Line chart"
            class="w-9 h-9 flex items-center justify-center rounded-lg border shadow-sm transition-colors"
            :class="chartType === 'line' ? 'bg-slate-800 border-slate-800 text-white' : 'bg-white border-gray-300 text-gray-500 hover:text-gray-800 hover:border-gray-400'"
            @click="chartType = 'line'"
          >
            <i class="fa-solid fa-chart-line text-sm"></i>
          </button>
          <button
            type="button"
            title="Bar chart"
            class="w-9 h-9 flex items-center justify-center rounded-lg border shadow-sm transition-colors"
            :class="chartType === 'bar' ? 'bg-slate-800 border-slate-800 text-white' : 'bg-white border-gray-300 text-gray-500 hover:text-gray-800 hover:border-gray-400'"
            @click="chartType = 'bar'"
          >
            <i class="fa-solid fa-chart-bar text-sm"></i>
          </button>

          <div class="relative">
            <div
              class="flex items-center gap-2 bg-white border border-gray-300 shadow-sm rounded-lg px-3 py-2 text-xs font-semibold text-gray-700 cursor-pointer select-none hover:border-gray-400"
              @click="hoursOpen = !hoursOpen; metricOpen = false"
            >
              <span>{{ hoursLabel }}</span>
              <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 transition-transform" :class="{ 'rotate-180': hoursOpen }"></i>
            </div>
            <div v-if="hoursOpen" class="absolute right-0 top-[calc(100%+8px)] min-w-[160px] bg-[#1c222d] border border-white/10 rounded-lg shadow-xl p-1.5 z-20">
              <div
                v-for="(o, i) in hourOptions"
                :key="o.hours"
                class="flex items-center gap-2.5 px-3 py-2.5 text-xs font-semibold rounded-md cursor-pointer whitespace-nowrap"
                :class="[hours === o.hours ? 'text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white', i < hourOptions.length - 1 ? 'mb-1' : '']"
                @click="setHours(o.hours)"
              >
                <span class="w-[3px] h-4 rounded-sm flex-shrink-0" :class="hours === o.hours ? 'bg-blue-400' : 'bg-transparent'"></span>{{ o.label }}
              </div>
            </div>
          </div>

          <div class="relative">
            <div
              class="flex items-center gap-2 bg-white border border-gray-300 shadow-sm rounded-lg px-3 py-2 text-xs font-semibold text-gray-700 cursor-pointer select-none hover:border-gray-400"
              @click="metricOpen = !metricOpen; hoursOpen = false"
            >
              <span>{{ metricLabel }}</span>
              <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 transition-transform" :class="{ 'rotate-180': metricOpen }"></i>
            </div>
            <div v-if="metricOpen" class="absolute right-0 top-[calc(100%+8px)] min-w-[160px] bg-[#1c222d] border border-white/10 rounded-lg shadow-xl p-1.5 z-20">
              <div
                v-for="(m, i) in metricOptions"
                :key="m.key"
                class="flex items-center gap-2.5 px-3 py-2.5 text-xs font-semibold rounded-md cursor-pointer whitespace-nowrap"
                :class="[metric === m.key ? 'text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white', i < metricOptions.length - 1 ? 'mb-1' : '']"
                @click="setMetric(m.key)"
              >
                <span class="w-[3px] h-4 rounded-sm flex-shrink-0" :class="metric === m.key ? 'bg-blue-400' : 'bg-transparent'"></span>{{ m.label }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-[#131722] border border-white/10 rounded-2xl p-5">
        <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
          <div class="flex items-center gap-2 bg-white/5 border border-white/10 rounded-lg px-3 py-1.5">
            <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: colorForPollutantValue(cityData?.aqi) }"></span>
            <span class="text-xs font-medium text-gray-200">{{ cityData?.name || 'Unknown location' }}</span>
          </div>
          <div v-if="aqiHistory.min && aqiHistory.max" class="flex items-center gap-2">
            <div class="flex items-center gap-2 rounded-lg px-3 py-1.5" :style="{ backgroundColor: colorForPollutantValue(aqiHistory.min.value) + '26', border: `1px solid ${colorForPollutantValue(aqiHistory.min.value)}` }">
              <span class="text-sm font-bold" :style="{ color: colorForPollutantValue(aqiHistory.min.value) }">{{ formatMetricValue(aqiHistory.min.value) }}</span>
              <div class="leading-tight">
                <p class="text-[10px] text-gray-300">&darr; Min. {{ metricLabel }}</p>
                <p class="text-[10px] text-gray-400">{{ formatHistTime(aqiHistory.min.time) }}</p>
              </div>
            </div>
            <div class="flex items-center gap-2 rounded-lg px-3 py-1.5" :style="{ backgroundColor: colorForPollutantValue(aqiHistory.max.value) + '26', border: `1px solid ${colorForPollutantValue(aqiHistory.max.value)}` }">
              <span class="text-sm font-bold" :style="{ color: colorForPollutantValue(aqiHistory.max.value) }">{{ formatMetricValue(aqiHistory.max.value) }}</span>
              <div class="leading-tight">
                <p class="text-[10px] text-gray-300">&uarr; Max. {{ metricLabel }}</p>
                <p class="text-[10px] text-gray-400">{{ formatHistTime(aqiHistory.max.time) }}</p>
              </div>
            </div>
          </div>
        </div>

        <div v-if="chartPoints.length" class="relative" style="height: 220px;">
          <svg
            ref="svgRef"
            class="w-full h-full block overflow-visible"
            :viewBox="`0 0 ${CH.W} ${CH.H}`"
            preserveAspectRatio="none"
          >
            <defs>
              <linearGradient id="cityAqiAreaGrad" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="#c3d92e" stop-opacity="0.22" />
                <stop offset="100%" stop-color="#c3d92e" stop-opacity="0" />
              </linearGradient>
            </defs>

            <template v-for="g in gridLines" :key="g.v">
              <line :x1="CH.padL" :y1="g.y" :x2="CH.W - CH.padR" :y2="g.y" stroke="rgba(255,255,255,0.06)" stroke-width="1" />
              <text :x="CH.padL - 8" :y="g.y + 3" text-anchor="end" fill="#8b93a1" font-size="9">{{ g.v }}</text>
            </template>

            <text
              v-for="l in xLabels"
              :key="l.i"
              :x="l.x"
              :y="CH.H - CH.padB + 16"
              text-anchor="middle"
              fill="#8b93a1"
              font-size="9"
            >{{ l.label }}</text>

            <template v-if="chartType === 'bar'">
              <rect
                v-for="(p, i) in chartPoints"
                :key="i"
                :x="Math.max(p.x - barWidth / 2, CH.padL)"
                :y="p.y"
                :width="Math.min(barWidth, p.x + barWidth / 2 - CH.padL)"
                :height="CH.padT + plotH - p.y"
                rx="2"
                fill="#c3d92e"
              />
            </template>

            <template v-else>
              <path :d="areaPath" fill="url(#cityAqiAreaGrad)" stroke="none" />
              <path :d="smoothPath" fill="none" stroke="#c3d92e" stroke-width="2" />
              <circle v-for="(p, i) in chartPoints" :key="'d' + i" :cx="p.x" :cy="p.y" r="2.5" fill="#c3d92e" />

              <template v-if="hover">
                <line class="pointer-events-none" :x1="hover.svgX" :x2="hover.svgX" :y1="CH.padT" :y2="CH.padT + plotH" stroke="rgba(255,255,255,0.18)" stroke-width="1" />
                <circle :cx="hover.point.x" :cy="hover.point.y" r="8" fill="#c3d92e" fill-opacity="0.25" />
                <circle :cx="hover.point.x" :cy="hover.point.y" r="3.5" fill="#131722" stroke="#c3d92e" stroke-width="2" />
              </template>
            </template>
          </svg>

          <div
            ref="hoverCapture"
            class="absolute inset-0"
            @mousemove="onHoverMove"
            @mouseleave="onHoverLeave"
            @touchstart.passive="onHoverTouch"
            @touchmove.passive="onHoverTouch"
          ></div>

          <div v-if="hover" class="absolute pointer-events-none bg-[#1e2530] border border-white/10 rounded-lg px-3 py-2 text-xs shadow-xl" :style="tooltipStyle">
            <p class="text-gray-400 mb-1">{{ formatHistTime(hover.point.time) }}</p>
            <p class="flex items-center gap-1.5 font-semibold text-white">
              <span class="w-2 h-2 rounded-full inline-block" style="background:#c3d92e"></span>
              {{ metricLabel }}: {{ formatMetricValue(hover.point.value) }}
            </p>
          </div>
        </div>
        <div v-else class="h-[220px] flex items-center justify-center text-center text-sm text-gray-500 px-8">
          Not enough historical data yet for this location &mdash; the graph fills in automatically as readings are recorded over time.
        </div>

        <div v-if="chartPoints.length" class="flex items-center justify-between mt-2 text-[11px] font-semibold text-gray-500">
          <span>{{ startDate }}</span>
          <span>{{ endDate }}</span>
        </div>
      </div>
    </div>

    <!-- Loading skeleton: calendar -->
    <div v-if="loading" class="wide-container relative z-10">
      <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <div class="space-y-2">
          <Skeleton class="h-5 w-56 bg-white/10" />
          <Skeleton class="h-3 w-24 bg-white/10" />
        </div>
        <div class="flex items-center gap-2">
          <Skeleton class="h-9 w-20 rounded-lg bg-white/10" />
          <Skeleton class="h-9 w-28 rounded-lg bg-white/10" />
        </div>
      </div>
      <div class="bg-[#131722] border border-white/10 rounded-2xl p-5">
        <div class="flex gap-8 overflow-hidden pb-2">
          <div v-for="i in 3" :key="i" class="flex-shrink-0" style="width: 280px;">
            <Skeleton class="h-4 w-16 mb-2 bg-white/10" />
            <div class="grid grid-cols-7 gap-1">
              <Skeleton v-for="c in 35" :key="c" class="h-10 w-full bg-white/5" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Air Quality Calendar -->
    <div v-if="cityData" class="wide-container relative z-10">
      <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
        <div>
          <h2 class="text-xl font-bold text-white">Air Quality Calendar {{ calYear }}</h2>
          <p class="text-blue-600 text-sm mt-0.5">{{ cityData?.name }}</p>
        </div>

        <div v-if="loading" class="flex items-center gap-2">
          <Skeleton class="h-9 w-20 rounded-lg bg-white/10" />
          <Skeleton class="h-9 w-28 rounded-lg bg-white/10" />
        </div>
        <div v-else class="flex items-center gap-2" ref="calControlsRef">
          <div class="relative">
            <div
              class="flex items-center gap-2 bg-white border border-gray-300 shadow-sm rounded-lg px-3 py-2 text-xs font-semibold text-gray-700 cursor-pointer select-none hover:border-gray-400"
              @click="calYearOpen = !calYearOpen; calMetricOpen = false"
            >
              <span>{{ calYear }}</span>
              <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 transition-transform" :class="{ 'rotate-180': calYearOpen }"></i>
            </div>
            <div v-if="calYearOpen" class="absolute right-0 top-[calc(100%+8px)] min-w-[120px] bg-[#1c222d] border border-white/10 rounded-lg shadow-xl p-1.5 z-20 max-h-60 overflow-y-auto">
              <div
                v-for="y in [...new Set([...calAvailableYears, new Date().getFullYear()])].sort((a,b) => b - a)"
                :key="y"
                class="flex items-center gap-2.5 px-3 py-2.5 text-xs font-semibold rounded-md cursor-pointer whitespace-nowrap"
                :class="calYear === y ? 'text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white'"
                @click="setCalYear(y)"
              >
                <span class="w-[3px] h-4 rounded-sm flex-shrink-0" :class="calYear === y ? 'bg-blue-400' : 'bg-transparent'"></span>{{ y }}
              </div>
            </div>
          </div>

          <div class="relative">
            <div
              class="flex items-center gap-2 bg-white border border-gray-300 shadow-sm rounded-lg px-3 py-2 text-xs font-semibold text-gray-700 cursor-pointer select-none hover:border-gray-400"
              @click="calMetricOpen = !calMetricOpen; calYearOpen = false"
            >
              <span>{{ calMetricLabel }}</span>
              <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 transition-transform" :class="{ 'rotate-180': calMetricOpen }"></i>
            </div>
            <div v-if="calMetricOpen" class="absolute right-0 top-[calc(100%+8px)] min-w-[160px] bg-[#1c222d] border border-white/10 rounded-lg shadow-xl p-1.5 z-20">
              <div
                v-for="(m, i) in metricOptions"
                :key="m.key"
                class="flex items-center gap-2.5 px-3 py-2.5 text-xs font-semibold rounded-md cursor-pointer whitespace-nowrap"
                :class="[calMetric === m.key ? 'text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white', i < metricOptions.length - 1 ? 'mb-1' : '']"
                @click="setCalMetric(m.key)"
              >
                <span class="w-[3px] h-4 rounded-sm flex-shrink-0" :class="calMetric === m.key ? 'bg-blue-400' : 'bg-transparent'"></span>{{ m.label }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-[#131722] border border-white/10 rounded-2xl p-5">
        <div v-if="calLoading" class="flex gap-8 overflow-hidden pb-2">
          <div v-for="i in 3" :key="i" class="flex-shrink-0" style="width: 280px;">
            <Skeleton class="h-4 w-16 mb-2 bg-white/10" />
            <div class="grid grid-cols-7 gap-1">
              <Skeleton v-for="c in 35" :key="c" class="h-10 w-full bg-white/5" />
            </div>
          </div>
        </div>
        <div v-else ref="calScrollRef" class="flex gap-8 overflow-x-auto pb-2 scroll-smooth no-scrollbar">
          <div v-for="month in calMonths" :key="month.index" class="flex-shrink-0" style="width: 280px;">
            <p class="text-sm font-bold text-white mb-2">{{ month.name }}</p>
            <div class="grid grid-cols-7 gap-1 text-center">
              <span v-for="wd in weekdayShort" :key="wd" class="text-[9px] text-gray-500 font-semibold pb-1">{{ wd }}</span>
              <template v-for="(week, wi) in month.weeks" :key="wi">
                <div v-for="(cell, ci) in week" :key="ci">
                  <div v-if="!cell" class="h-10"></div>
                  <div
                    v-else
                    class="h-10 rounded-md flex flex-col items-center justify-center leading-none"
                    :style="cell.value != null
                      ? { backgroundColor: colorForPollutantValue(cell.value) + 'cc' }
                      : { backgroundColor: 'rgba(255,255,255,0.05)', border: '1px solid rgba(255,255,255,0.08)' }"
                  >
                    <span class="text-[8px]" :class="cell.value != null ? 'text-black/70' : 'text-gray-500'">{{ cell.label }}</span>
                    <span class="text-[11px] font-bold" :class="cell.value != null ? 'text-black' : 'text-gray-500'">{{ cell.value != null ? formatMetricValue(cell.value) : '--' }}</span>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>

        <!-- Legend -->
        <div class="mt-4 bg-black/20 border border-white/10 rounded-xl p-3">
          <div class="grid grid-cols-6 text-[11px] font-semibold text-gray-300 mb-1.5">
            <span>Good</span><span>Moderate</span><span>Poor</span><span>Unhealthy</span><span>Severe</span><span>Hazardous</span>
          </div>
          <div class="flex h-2 rounded-full overflow-hidden">
            <div class="flex-1" style="background:#4cd964"></div>
            <div class="flex-1" style="background:#ffcc00"></div>
            <div class="flex-1" style="background:#ff9500"></div>
            <div class="flex-1" style="background:#ff2d55"></div>
            <div class="flex-1" style="background:#af52de"></div>
            <div class="flex-1" style="background:#ff3b30"></div>
          </div>
          <div class="grid grid-cols-6 text-[10px] text-gray-500 mt-1">
            <span>0</span><span>50</span><span>100</span><span>150</span><span>200</span><span>300</span>
          </div>
        </div>

        <!-- Prev / Next -->
        <div v-if="calLoading" class="flex items-center justify-end gap-2 mt-4">
          <Skeleton class="w-9 h-9 rounded-full bg-white/10" />
          <Skeleton class="w-9 h-9 rounded-full bg-white/10" />
        </div>
        <div v-else class="flex items-center justify-end gap-2 mt-4">
          <button
            class="w-9 h-9 rounded-full bg-blue-500 hover:bg-blue-600 text-white flex items-center justify-center transition-colors"
            @click="scrollCalendar(-1)"
            title="Earlier"
          >
            <i class="fa-solid fa-arrow-left text-xs"></i>
          </button>
          <button
            class="w-9 h-9 rounded-full bg-blue-500 hover:bg-blue-600 text-white flex items-center justify-center transition-colors"
            @click="scrollCalendar(1)"
            title="Later"
          >
            <i class="fa-solid fa-arrow-right text-xs"></i>
          </button>
        </div>
      </div>
    </div>
    </div>

    <!-- Error -->
    <div v-if="!loading && !cityData" class="error-message">
      {{$t('CityDetail.Citydatanotfound')}}.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch, nextTick } from "vue";
import { useRoute, useRouter } from "vue-router";
import axios from "axios";
import { API_ROOT } from "@/services/api.js";
import Swal from "sweetalert2";
import { useAuthStore } from "@/stores/airQuality";
import WeatherParametersPanel from "@/components/WeatherParametersPanel.vue";
import Skeleton from "@/components/Skeleton.vue";
import pm25Icon from "@/assets/images/svg/pm25.svg";
import pm10Icon from "@/assets/images/svg/pm10.svg";
import coIcon from "@/assets/images/svg/co.svg";
import so2Icon from "@/assets/images/svg/so2.svg";
import no2Icon from "@/assets/images/svg/no2.svg";
import o3Icon from "@/assets/images/svg/o3.svg";
import weatherSceneBg from "@/assets/images/svg/15.webp";
import temperatureIcon from "@/assets/images/svg/temperature.svg";
import rainfallIcon from "@/assets/images/svg/rainfall.svg";
import clearIcon from "@/assets/images/svg/clear.svg";
import cloudyIcon from "@/assets/images/svg/cloudy.svg";
import overcastIcon from "@/assets/images/svg/overcast.svg";
import lightRainShowerIcon from "@/assets/images/svg/light-rain-shower.svg";
import moderateOrHeavyRainShowerIcon from "@/assets/images/svg/moderate-or-heavy-rain-shower.svg";
import goodLevelImg from "@/assets/images/svg/aqi-good-level.webp";
import moderateLevelImg from "@/assets/images/svg/aqi-moderate-level.webp";
import poorLevelImg from "@/assets/images/svg/aqi-poor-level.webp";
import unhealthyLevelImg from "@/assets/images/svg/aqi-unhealthy-level.webp";
import severeLevelImg from "@/assets/images/svg/aqi-severe-level.webp";
import hazardousLevelImg from "@/assets/images/svg/aqi-hazardous-level.webp";
const aqiLevelMascots = {
  Good: goodLevelImg,
  Moderate: moderateLevelImg,
  Poor: poorLevelImg,
  Unhealthy: unhealthyLevelImg,
  Severe: severeLevelImg,
  Hazardous: hazardousLevelImg,
};

// --------------------------
// ROUTE & STATE
// --------------------------
const route = useRoute();
const router = useRouter();
const cityIdParam = route.params.id;
const cityData = ref(null);
const loading = ref(false);
const lastUpdated = ref("");
const favourites = ref([]);
const auth = useAuthStore();

// --------------------------
// HELPER FUNCTIONS
// --------------------------
const assignIDs = (data) =>
  data.map((station, index) => ({
    ...station,
    id: station.id || index + 1,
  }));

// --------------------------
// AQI BAND COLOR (shared by pollutant cards, AQI graph and calendar cells)
// --------------------------
const aqiBandLevels = [
  { label: 'Good', max: 50, color: '#4cd964' },
  { label: 'Moderate', max: 100, color: '#ffcc00' },
  { label: 'Poor', max: 150, color: '#ff9500' },
  { label: 'Unhealthy', max: 200, color: '#ff2d55' },
  { label: 'Severe', max: 300, color: '#af52de' },
  { label: 'Hazardous', max: Infinity, color: '#ff3b30' },
];
const colorForPollutantValue = (value) => {
  const v = parseFloat(value);
  if (isNaN(v)) return '#94a3b8';
  const level = aqiBandLevels.find((l) => v <= l.max);
  return (level || aqiBandLevels[aqiBandLevels.length - 1]).color;
};

// --------------------------
// HERO CARD (same treatment as the Home page's hero AQI card)
// --------------------------
// A real route (not just local state) so AQI/Weather are shareable/bookmarkable pages —
// /city/:id and /aqi/city/:id both mean AQI mode, /weather/city/:id means weather mode.
const activeHeroTab = computed(() => (route.path.startsWith('/weather/city/') ? 'weather' : 'aqi'));

const heroAqiStatus = computed(() => {
  const aqi = parseFloat(cityData.value?.aqi);
  if (isNaN(aqi)) return { label: 'N/A', color: '#999' };
  const level = aqiBandLevels.find((l) => aqi <= l.max);
  return level || aqiBandLevels[aqiBandLevels.length - 1];
});

const heroAqiMascot = computed(() => aqiLevelMascots[heroAqiStatus.value.label] || null);

const heroAqiMarkerPercent = computed(() => {
  const aqi = parseFloat(cityData.value?.aqi);
  if (isNaN(aqi)) return 0;
  const bounds = [0, 50, 100, 150, 200, 300, 301];
  let idx = aqiBandLevels.findIndex((l) => aqi <= l.max);
  if (idx === -1) idx = aqiBandLevels.length - 1;
  const lower = bounds[idx];
  const upper = bounds[idx + 1] ?? lower + 100;
  const fraction = Math.min(Math.max((aqi - lower) / (upper - lower), 0), 1);
  return ((idx + fraction) / aqiBandLevels.length) * 100;
});

// Position along the AQI card's rainbow arc gauge (same `M8 52 A42 42 0 0 1 92 52`
// semicircle used by the pressure gauge), sweeping 180deg (left) to 0deg (right).
// Needle rotation, same convention as the Pressure gauge's needle (-90deg at the
// left end of the arc, +90deg at the right end).
const heroAqiGaugeAngle = computed(() => -90 + (heroAqiMarkerPercent.value / 100) * 180);

// Unlike Home's hero card, this one doesn't overlap a map banner above it, so it uses a
// plain top-to-bottom tint into the AQI color instead of Home's blend-into-the-map stops.
const heroCardBackground = computed(() => {
  const color = heroAqiStatus.value.color;
  return `linear-gradient(180deg, ${color}26 0%, ${color}99 55%, ${color} 100%)`;
});

// "Mean Chey, Phnom Penh, Cambodia" -> ['Cambodia', 'Phnom Penh', 'Mean Chey'] (country first)
const breadcrumbSegments = computed(() => {
  const parts = (cityData.value?.name || '').split(',').map((s) => s.trim()).filter(Boolean);
  return parts.reverse();
});

const heroSubtitleRegion = computed(() => breadcrumbSegments.value[breadcrumbSegments.value.length - 2] || breadcrumbSegments.value[0] || '');

const locateMe = () => {
  router.push('/home');
};

const shareCity = async () => {
  const name = cityData.value?.name || 'this location';
  const shareData = {
    title: 'Air Quality Index',
    text: `Check the live AQI for ${name}`,
    url: window.location.href,
  };
  if (navigator.share) {
    try {
      await navigator.share(shareData);
    } catch {
      // user cancelled — ignore
    }
  } else {
    await navigator.clipboard.writeText(shareData.url);
    alert('Link copied to clipboard');
  }
};

// Breadcrumb shows a plain "Weather" segment right after Home when that tab is active,
// matching the reference — the AQI tab's breadcrumb has no such prefix.
const displayBreadcrumb = computed(() =>
  activeHeroTab.value === 'weather' ? ['Weather', ...breadcrumbSegments.value] : breadcrumbSegments.value
);

// --------------------------
// WEATHER TAB — real current + forecast data from OpenWeather (5-day/3-hour forecast is
// the finest granularity the free tier offers, so "Hourly" below is real 3-hour steps,
// not fabricated hourly interpolation).
// --------------------------
const weatherForecast = ref(null);
const weatherLoading = ref(false);
const forecastRange = ref('hourly'); // 'hourly' | 'daily'

const fetchWeatherForecast = async () => {
  const lat = cityData.value?.lat;
  const lon = cityData.value?.lon;
  if (lat == null || lon == null) return;
  weatherLoading.value = true;
  try {
    const { data } = await axios.get(`${API_ROOT}/api/weather-forecast`, {
      params: { lat, lon },
    });
    if (data.status === "ok") {
      weatherForecast.value = data;
    }
  } catch (error) {
    console.error("Error fetching weather forecast:", error);
  } finally {
    weatherLoading.value = false;
  }
};

const weatherIconEmoji = (code) => {
  if (!code) return '🌤️';
  const group = code.slice(0, 2);
  const map = {
    '01': '☀️', '02': '⛅', '03': '☁️', '04': '☁️',
    '09': '🌧️', '10': '🌦️', '11': '⛈️', '13': '❄️', '50': '🌫️',
  };
  return map[group] || '🌤️';
};

// Maps OpenWeather icon groups to the illustrated condition set (cloudy, overcast,
// light/heavy rain shower…) instead of emoji, for groups that set covers; codes
// outside that set (snow, mist, thunderstorm) keep falling back to the emoji.
const weatherConditionIcon = (code) => {
  if (!code) return null;
  const map = {
    '01': clearIcon,
    '02': cloudyIcon,
    '03': cloudyIcon,
    '04': overcastIcon,
    '09': moderateOrHeavyRainShowerIcon,
    '10': lightRainShowerIcon,
  };
  return map[code.slice(0, 2)] || null;
};

const humidityLabel = computed(() => {
  const h = weatherForecast.value?.current?.humidity;
  if (h == null) return '';
  if (h >= 70) return 'High humidity may cause discomfort.';
  if (h >= 40) return 'Comfortable humidity expected.';
  return 'Dry air expected.';
});

const aqiComfortLabel = computed(() => {
  const aqi = parseFloat(cityData.value?.aqi);
  if (isNaN(aqi)) return '';
  if (aqi <= 50) return 'Clean air.';
  if (aqi <= 100) return 'Sensitive groups take care.';
  return 'Caution advised.';
});

const tempBadge = computed(() => {
  const t = weatherForecast.value?.current?.temp;
  if (t == null) return { label: '—', color: '#64748b' };
  if (t >= 38) return { label: 'Very Hot', color: '#dc2626' };
  if (t >= 32) return { label: 'Hot', color: '#f97316' };
  if (t >= 24) return { label: 'Warm', color: '#eab308' };
  if (t >= 15) return { label: 'Mild', color: '#22c55e' };
  return { label: 'Cold', color: '#3b82f6' };
});

const formatForecastTime = (dtText, isFirst) => {
  if (isFirst) return 'Now';
  const d = new Date(dtText.replace(' ', 'T'));
  if (isNaN(d.getTime())) return '';
  return d.toLocaleTimeString([], { hour: 'numeric' }).replace(' ', '');
};

const formatForecastDay = (dateStr, isFirst) => {
  if (isFirst) return 'Today';
  const d = new Date(dateStr + 'T00:00:00');
  if (isNaN(d.getTime())) return '';
  return d.toLocaleDateString([], { weekday: 'short' });
};

// Forecast strip scrolls natively (touch/trackpad/wheel) through the full
// dataset; the left/right buttons sit outside the scroll container so they
// stay put (sticky, like the navbar) while the strip underneath scrolls.
const forecastScrollRef = ref(null);
const FORECAST_ITEM_WIDTH = 56 + 16; // w-14 (56px) + gap-4 (16px)

const forecastAllRows = computed(() =>
  forecastRange.value === 'hourly' ? (weatherForecast.value?.hourly || []) : (weatherForecast.value?.daily || [])
);
const forecastStripWidth = computed(() => forecastAllRows.value.length * FORECAST_ITEM_WIDTH);

// Single button: shows ">" to scroll forward, flips to "<" once the strip
// is scrolled to its end so clicking again jumps back to the start.
const forecastAtEnd = ref(false);
const checkForecastEnd = () => {
  const el = forecastScrollRef.value;
  if (!el) return;
  forecastAtEnd.value = el.scrollLeft + el.clientWidth >= el.scrollWidth - 2;
};

watch(forecastRange, () => {
  forecastScrollRef.value?.scrollTo({ left: 0 });
  nextTick(checkForecastEnd);
});
watch(forecastAllRows, () => {
  nextTick(checkForecastEnd);
});

const scrollForecastRight = () => {
  if (forecastAtEnd.value) {
    forecastScrollRef.value?.scrollTo({ left: 0, behavior: 'smooth' });
  } else {
    forecastScrollRef.value?.scrollBy({ left: FORECAST_ITEM_WIDTH * 3, behavior: 'smooth' });
  }
};

// Simple sparkline through the visible forecast temps, matching the app's other mini-chart style.
const forecastSparklinePoints = computed(() => {
  const temps = forecastAllRows.value.map((r) => forecastRange.value === 'hourly' ? r.temp : r.max);
  if (temps.length < 2) return [];
  const min = Math.min(...temps);
  const max = Math.max(...temps);
  const span = max - min || 1;
  const w = 100 / (temps.length - 1);
  return temps.map((t, i) => ({
    x: i * w,
    y: 30 - ((t - min) / span) * 26 - 2,
  }));
});

const forecastSparkline = computed(() =>
  forecastSparklinePoints.value
    .map((p, i) => `${i === 0 ? 'M' : 'L'} ${p.x.toFixed(1)} ${p.y.toFixed(1)}`)
    .join(' ')
);

// --------------------------
// MAJOR AIR POLLUTANTS (same layout/data as the Home page)
// --------------------------
const pollutantCards = computed(() => {
  const s = cityData.value;
  const defs = [
    { key: 'pm25', label: 'Particulate Matter', abbr: 'PM2.5', icon: pm25Icon, unit: 'µg/m³', value: s?.pm25 },
    { key: 'pm10', label: 'Particulate Matter', abbr: 'PM10', icon: pm10Icon, unit: 'µg/m³', value: s?.pm10 },
    { key: 'co', label: 'Carbon Monoxide', abbr: 'CO', icon: coIcon, unit: 'AQI', value: s?.co },
    { key: 'so2', label: 'Sulfur Dioxide', abbr: 'SO₂', icon: so2Icon, unit: 'AQI', value: s?.so2 },
    { key: 'no2', label: 'Nitrogen Dioxide', abbr: 'NO₂', icon: no2Icon, unit: 'AQI', value: s?.no2 },
    { key: 'o3', label: 'Ozone', abbr: 'O₃', icon: o3Icon, unit: 'AQI', value: s?.o3 },
  ];
  return defs.map((d) => ({
    ...d,
    value: d.value === 'N/A' ? null : d.value ?? null,
    color: colorForPollutantValue(d.value === 'N/A' ? null : d.value),
  }));
});

// --------------------------
// AQI GRAPH (same widget as the Home page — real recorded points from aqi_history)
// --------------------------
const aqiHistory = ref({ points: [], min: null, max: null, hours: 24, metric: 'aqi', unit: '' });
const chartType = ref('line');
const hours = ref(24);
const hourOptions = [
  { hours: 24, label: '24 Hours' },
  { hours: 168, label: '7 Days' },
  { hours: 720, label: '30 Days' },
];
const hoursOpen = ref(false);
const metric = ref('aqi');
const metricOptions = [
  { key: 'aqi', label: 'AQI (US)' },
  { key: 'pm25', label: 'PM2.5' },
  { key: 'pm10', label: 'PM10' },
  { key: 'co', label: 'CO' },
  { key: 'so2', label: 'SO₂' },
  { key: 'no2', label: 'NO₂' },
  { key: 'o3', label: 'O₃' },
];
const metricOpen = ref(false);
const graphControlsRef = ref(null);

const hoursLabel = computed(() => hourOptions.find((o) => o.hours === hours.value)?.label || `${hours.value} Hours`);
const metricLabel = computed(() => metricOptions.find((m) => m.key === metric.value)?.label || 'AQI (US)');

const fetchAqiHistory = async () => {
  const name = cityData.value?.name;
  if (!name) return;
  try {
    const { data } = await axios.get(`${API_ROOT}/api/aqi-history`, {
      params: { name, hours: hours.value, metric: metric.value },
    });
    if (data.status === "ok") {
      aqiHistory.value = { points: data.points, min: data.min, max: data.max, hours: data.hours, metric: data.metric, unit: data.unit };
    }
  } catch (error) {
    console.error("Error fetching AQI history:", error);
  }
};

const setHours = (h) => {
  hours.value = h;
  hoursOpen.value = false;
  fetchAqiHistory();
};

const setMetric = (key) => {
  metric.value = key;
  metricOpen.value = false;
  fetchAqiHistory();
};

const formatHistTime = (iso) => {
  if (!iso) return "";
  return new Date(iso).toLocaleTimeString([], { hour: "numeric", minute: "2-digit" }).toLowerCase();
};

const pad2 = (n) => String(n).padStart(2, "0");
const formatHistDate = (iso) => {
  if (!iso) return "";
  const d = new Date(iso);
  if (isNaN(d.getTime())) return "";
  return `${pad2(d.getDate())}-${pad2(d.getMonth() + 1)}-${d.getFullYear()}`;
};

const formatMetricValue = (v) => {
  if (v === null || v === undefined) return "—";
  const n = Number(v);
  if (isNaN(n)) return "—";
  return Number.isInteger(n) ? String(n) : (Math.round(n * 10) / 10).toString();
};

const CH = { W: 1040, H: 220, padL: 34, padR: 8, padT: 10, padB: 26 };
const plotW = CH.W - CH.padL - CH.padR;
const plotH = CH.H - CH.padT - CH.padB;

const niceMax = (mx) => {
  if (!isFinite(mx) || mx <= 0) return 10;
  const magnitude = Math.pow(10, Math.floor(Math.log10(mx)));
  const residual = mx / magnitude;
  let niceResidual;
  if (residual <= 1) niceResidual = 1;
  else if (residual <= 2) niceResidual = 2;
  else if (residual <= 5) niceResidual = 5;
  else niceResidual = 10;
  return niceResidual * magnitude;
};

const maxY = computed(() => {
  const vals = aqiHistory.value.points.map((p) => Number(p.value) || 0);
  if (!vals.length) return 100;
  return niceMax(Math.max(...vals) * 1.05);
});

const gridLines = computed(() => {
  const max = maxY.value;
  const step = max / 5;
  const lines = [];
  for (let i = 0; i <= 5; i++) {
    const v = Math.round(step * i * 100) / 100;
    lines.push({ v, y: CH.padT + plotH - (v / max) * plotH });
  }
  return lines;
});

const chartPoints = computed(() => {
  const pts = aqiHistory.value.points;
  if (!pts.length) return [];
  const max = maxY.value;
  // Positioned by actual elapsed time within the selected window (not evenly spread by
  // index) — a station with only a few recent readings draws a short line near the right
  // edge instead of being stretched to fill the whole card.
  const windowEnd = Date.now();
  const windowStart = windowEnd - hours.value * 3600 * 1000;
  const span = windowEnd - windowStart || 1;
  return pts.map((p) => {
    const t = new Date(p.time).getTime();
    const frac = Math.min(Math.max((t - windowStart) / span, 0), 1);
    return {
      x: CH.padL + frac * plotW,
      y: CH.padT + plotH - (Math.min(Number(p.value) || 0, max) / max) * plotH,
      value: p.value,
      time: p.time,
    };
  });
});

const xLabels = computed(() => {
  const pts = chartPoints.value;
  if (!pts.length) return [];
  const labels = [];
  pts.forEach((p, i) => {
    if (i % 5 === 0) {
      labels.push({ i, x: p.x, label: formatHistTime(p.time) });
    }
  });
  return labels;
});

const barWidth = computed(() => {
  const len = chartPoints.value.length || 1;
  return Math.min(Math.max(4, (plotW / len) * 0.7), 28);
});

const smoothPath = computed(() => {
  const pts = chartPoints.value;
  if (pts.length < 3) {
    return pts.map((p, i) => `${i === 0 ? "M" : "L"}${p.x.toFixed(1)} ${p.y.toFixed(1)}`).join(" ");
  }
  let d = `M ${pts[0].x.toFixed(1)} ${pts[0].y.toFixed(1)}`;
  for (let i = 0; i < pts.length - 1; i++) {
    const p0 = pts[i === 0 ? 0 : i - 1];
    const p1 = pts[i];
    const p2 = pts[i + 1];
    const p3 = pts[i + 2 < pts.length ? i + 2 : i + 1];
    const cp1x = p1.x + (p2.x - p0.x) / 6;
    const cp1y = p1.y + (p2.y - p0.y) / 6;
    const cp2x = p2.x - (p3.x - p1.x) / 6;
    const cp2y = p2.y - (p3.y - p1.y) / 6;
    d += ` C ${cp1x.toFixed(1)} ${cp1y.toFixed(1)}, ${cp2x.toFixed(1)} ${cp2y.toFixed(1)}, ${p2.x.toFixed(1)} ${p2.y.toFixed(1)}`;
  }
  return d;
});

const areaPath = computed(() => {
  const pts = chartPoints.value;
  if (!pts.length) return "";
  return `${smoothPath.value} L ${pts[pts.length - 1].x.toFixed(1)} ${(CH.padT + plotH).toFixed(1)} L ${pts[0].x.toFixed(1)} ${(CH.padT + plotH).toFixed(1)} Z`;
});

const startDate = computed(() => formatHistDate(chartPoints.value[0]?.time));
const endDate = computed(() => formatHistDate(chartPoints.value[chartPoints.value.length - 1]?.time));

const svgRef = ref(null);
const hoverCapture = ref(null);
const hover = ref(null);

const nearestPointIndex = (svgX) => {
  const pts = chartPoints.value;
  if (!pts.length) return -1;
  let best = 0;
  let min = Infinity;
  pts.forEach((p, i) => {
    const d = Math.abs(p.x - svgX);
    if (d < min) {
      min = d;
      best = i;
    }
  });
  return best;
};

const computeHover = (clientX) => {
  if (!hoverCapture.value) return;
  const rect = hoverCapture.value.getBoundingClientRect();
  const relX = clientX - rect.left;
  const svgX = (relX / rect.width) * CH.W;
  const idx = nearestPointIndex(svgX);
  if (idx < 0) return;
  const point = chartPoints.value[idx];
  hover.value = { svgX, point, pxX: (point.x / CH.W) * rect.width, pxY: (point.y / CH.H) * rect.height };
};

const onHoverMove = (e) => computeHover(e.clientX);
const onHoverTouch = (e) => {
  const t = e.touches[0];
  if (t) computeHover(t.clientX);
};
const onHoverLeave = () => {
  hover.value = null;
};

const tooltipStyle = computed(() => {
  if (!hover.value) return {};
  return {
    left: hover.value.pxX + "px",
    top: hover.value.pxY + "px",
    transform: hover.value.pxY < 60 ? "translate(-50%, 14px)" : "translate(-50%, calc(-100% - 14px))",
  };
});

const closeGraphDropdowns = (e) => {
  if (graphControlsRef.value && !graphControlsRef.value.contains(e.target)) {
    hoursOpen.value = false;
    metricOpen.value = false;
  }
};

// --------------------------
// AIR QUALITY CALENDAR
// --------------------------
const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
const weekdayShort = ['Sun.', 'Mon.', 'Tue.', 'Wed.', 'Thu.', 'Fri.', 'Sat.'];

const calYear = ref(new Date().getFullYear());
const calAvailableYears = ref([]);
const calYearOpen = ref(false);
const calMetric = ref('aqi');
const calMetricOpen = ref(false);
const calDays = ref({});
const calLoading = ref(false);
const calControlsRef = ref(null);
const calScrollRef = ref(null);

const calMetricLabel = computed(() => metricOptions.find((m) => m.key === calMetric.value)?.label || 'AQI (US)');

const fetchCalendar = async () => {
  const name = cityData.value?.name;
  if (!name) return;
  calLoading.value = true;
  try {
    const { data } = await axios.get(`${API_ROOT}/api/aqi-calendar`, {
      params: { name, year: calYear.value, metric: calMetric.value },
    });
    if (data.status === "ok") {
      calDays.value = data.days || {};
      calAvailableYears.value = data.available_years || [];
    }
  } catch (error) {
    console.error("Error fetching AQI calendar:", error);
    calDays.value = {};
  } finally {
    calLoading.value = false;
  }
};

const setCalYear = (y) => {
  calYear.value = y;
  calYearOpen.value = false;
  fetchCalendar();
};

const setCalMetric = (key) => {
  calMetric.value = key;
  calMetricOpen.value = false;
  fetchCalendar();
};

const calMonths = computed(() => {
  const year = calYear.value;
  const months = [];
  for (let m = 0; m < 12; m++) {
    const daysInMonth = new Date(year, m + 1, 0).getDate();
    const startWeekday = new Date(year, m, 1).getDay();
    const cells = [];
    for (let i = 0; i < startWeekday; i++) cells.push(null);
    for (let d = 1; d <= daysInMonth; d++) {
      const dateStr = `${year}-${pad2(m + 1)}-${pad2(d)}`;
      const value = calDays.value[dateStr];
      cells.push({
        date: dateStr,
        label: `${d} ${monthNames[m].slice(0, 3)}.`,
        value: value ?? null,
      });
    }
    while (cells.length % 7 !== 0) cells.push(null);
    const weeks = [];
    for (let i = 0; i < cells.length; i += 7) weeks.push(cells.slice(i, i + 7));
    months.push({ index: m, name: monthNames[m], weeks });
  }
  return months;
});

const scrollCalendar = (dir) => {
  if (!calScrollRef.value) return;
  calScrollRef.value.scrollBy({ left: dir * 300, behavior: 'smooth' });
};

const closeCalDropdowns = (e) => {
  if (calControlsRef.value && !calControlsRef.value.contains(e.target)) {
    calYearOpen.value = false;
    calMetricOpen.value = false;
  }
};

// --------------------------
// FAVOURITE FUNCTIONS
// --------------------------
const isFavourite = computed(() => {
  return favourites.value.some(city => city.name === cityData.value?.name);
});

// LOAD FAVOURITES FROM BACKEND
// --------------------------
const loadFavourites = async () => {
  try {
    const { data } = await axios.get(`${API_ROOT}/api/favourites`, {
      headers: { Authorization: `Bearer ${auth.token}` },
    });

    // Store favourites using city_name, id, and flag
    favourites.value = data.map(f => ({
      id: f.id, // Include the favourite ID from backend
      name: f.city_name,
      flag: `https://flagcdn.com/w160/${f.country_code.toLowerCase()}.png`,
    }));
  } catch (err) {
    console.error("Failed to load favourites:", err);
  }
};

const toggleFavourite = async () => {
  if (!cityData.value) return;

  try {
    if (isFavourite.value) {
      // Find the favourite entry to get its ID
      const favourite = favourites.value.find(c => c.name === cityData.value.name);
      if (!favourite || !favourite.id) {
        throw new Error("Favourite ID not found");
      }

      // Remove from backend using the favourite ID
      await axios.delete(
        `${API_ROOT}/api/favourites/${favourite.id}`,
        { headers: { Authorization: `Bearer ${auth.token}` } }
      );

      // Remove from local state
      favourites.value = favourites.value.filter(c => c.name !== cityData.value.name);

      Swal.fire({
        icon: "success",
        title: "Removed from favourites",
        text: "",
        customClass: {
          popup: "swal-custom",
          title: "swal-title",
          confirmButton: "swal-button",
        },
        showConfirmButton: false,
        timer: 1200,
      });
    } else {
      // Add to backend
      await axios.post(
        `${API_ROOT}/api/favourites`,
        {
          city_name: cityData.value.name,
          country_code: cityData.value.flag
            ? cityData.value.flag.split("/").pop().split(".")[0]
            : "kh",
        },
        { headers: { Authorization: `Bearer ${auth.token}` } }
      );

      // Reload favourites to get the new favourite's ID
      await loadFavourites();

      Swal.fire({
        icon: "success",
        title: "Added to favourites",
        text: "",
        customClass: {
          popup: "swal-custom",
          title: "swal-title",
          confirmButton: "swal-button",
        },
        showConfirmButton: false,
        timer: 1200,
      });
    }
  } catch (err) {
    console.error("Error updating favourite:", err);
    Swal.fire({
      icon: "error",
      title: "Failed to update favourite",
      text: err.response?.data?.message || err.message,
      customClass: {
        popup: "swal-custom",
        title: "swal-title",
        confirmButton: "swal-button",
      },
      confirmButtonText: "OK",
    });
  }
};

// --------------------------
// FETCH CITY DATA
// --------------------------
const fetchCityData = async () => {
  loading.value = true;
  try {
    const response = await axios.get(`${API_ROOT}/api/aqi`);
    let allCities = [];
    if (response.data.status === "ok" && Array.isArray(response.data.data)) {
      allCities = assignIDs(response.data.data);
    }

    try {
      const { data } = await axios.get(
        `${API_ROOT}/api/air-quality/phnom-penh`
      );
      const phnomPenhStation = {
        id: 9999,
        name: "Phnom Penh",
        lat: 11.562108,
        lon: 104.888535,
        aqi: data.AQI ?? "N/A",
        pm25: data.PM2_5 ?? "N/A",
        pm10: data.PM10 ?? "N/A",
        no2: data.NO2 ?? "N/A",
        co: data.CO ?? "N/A",
        o3: data.O3 ?? "N/A",
        so2: data.SO2 ?? "N/A",
        temperature: data.Temp_C ?? "N/A",
        humidity: data.Humidity_percent ?? "N/A",
        pressure: data.Pressure_hPa ?? "N/A",
        wind_speed: data.Wind_m_s ?? "N/A",
        flag: "https://flagcdn.com/w160/kh.png",
      };
      allCities = allCities.filter((c) => c.name !== "Phnom Penh");
      allCities.push(phnomPenhStation);
    } catch (err) {
      console.error("Failed to fetch Phnom Penh AQI:", err);
    }

    cityData.value = allCities.find((c) => c.id == cityIdParam) || null;
    lastUpdated.value = new Date().toLocaleString();
  } catch (error) {
    console.error("Error fetching city data:", error);
    cityData.value = null;
  } finally {
    loading.value = false;
  }
};

function getAQILevelKey(aqi) {
  const val = Number(aqi);
  if (isNaN(val)) return "good";
  if (val <= 50) return "good";
  if (val <= 100) return "moderate";
  if (val <= 150) return "usg";
  if (val <= 200) return "unhealthy";
  if (val <= 300) return "very_unhealthy";
  return "hazardous";
}

function getHealthMessage(aqi) {
  const stored = localStorage.getItem("aqiHealthMessages");
  const defaultMessages = {
    good: {
      public: "Air quality is good. Enjoy your outdoor activities!",
      sensitive: "No special precautions needed for sensitive groups.",
    },
    moderate: {
      public: "Air quality is moderate. Sensitive groups should take care.",
      sensitive: "Sensitive individuals should consider limiting prolonged outdoor exertion.",
    },
    unhealthySensitive: {
      public: "Unhealthy for sensitive groups. Limit prolonged outdoor exertion.",
      sensitive: "Sensitive groups should avoid outdoor activities.",
      actions: "Consider wearing masks and staying indoors.",
    },
    unhealthy: {
      public: "Unhealthy air quality. Everyone should reduce outdoor activities.",
      sensitive: "Critical alert for sensitive groups.",
      emergency: "Follow emergency precautions.",
      restrictions: "Outdoor activity restrictions in effect.",
    },
    veryUnhealthy: {
      public: "Very unhealthy air quality. Health alert for everyone.",
      sensitive: "Serious risk for sensitive groups.",
      emergency: "Avoid all outdoor activities. Follow emergency instructions.",
      restrictions: "Strict outdoor activity restrictions in effect.",
    },
    hazardous: {
      public: "Hazardous air quality. Health emergency for everyone.",
      sensitive: "Severe risk for sensitive groups.",
      emergency: "Remain indoors. Follow all emergency instructions.",
      restrictions: "All outdoor activities prohibited.",
    },
  };
  const messages = stored ? JSON.parse(stored) : defaultMessages;
  const keyMap = {
    good: "good",
    moderate: "moderate",
    usg: "unhealthySensitive",
    unhealthy: "unhealthy",
    very_unhealthy: "veryUnhealthy",
    hazardous: "hazardous",
  };
  let key = getAQILevelKey(aqi);
  key = keyMap[key] || key;

  const msg = messages[key] || {};
  let html = "<ul style='padding-left:1.2em'>";
  if (msg.public) html += `<li><strong>Public:</strong> ${msg.public}</li>`;
  if (msg.sensitive) html += `<li><strong>Sensitive Groups:</strong> ${msg.sensitive}</li>`;
  if (msg.actions) html += `<li><strong>Recommended Actions:</strong> ${msg.actions}</li>`;
  if (msg.emergency) html += `<li><strong>Emergency Actions:</strong> ${msg.emergency}</li>`;
  if (msg.restrictions) html += `<li><strong>Activity Restrictions:</strong> ${msg.restrictions}</li>`;
  html += "</ul>";
  return html;
}

// Illustration per AQI band, matching the level actually shown — not a
// generic warning triangle — same set used across the rest of the app.
const healthAlertIcons = {
  good: goodLevelImg,
  moderate: moderateLevelImg,
  usg: poorLevelImg,
  unhealthy: unhealthyLevelImg,
  very_unhealthy: severeLevelImg,
  hazardous: hazardousLevelImg,
};

function showHealthAlert(aqi, cityName) {
  const levelKey = getAQILevelKey(aqi);
  Swal.fire({
    title: `Health Alert for ${cityName}`,
    html: getHealthMessage(aqi),
    imageUrl: healthAlertIcons[levelKey] || goodLevelImg,
    imageHeight: 88,
    imageAlt: levelKey,
    confirmButtonText: "OK",
    buttonsStyling: false,
    customClass: {
      popup: "rounded-2xl",
      title: "!text-lg !font-semibold !text-gray-900",
      htmlContainer: "!text-sm !text-gray-600 !text-left",
      image: "!w-auto !object-contain",
      confirmButton: "px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 text-sm font-medium",
    },
  });
}

// --------------------------
// ON MOUNT
// --------------------------
const closeAllDropdowns = (e) => {
  closeGraphDropdowns(e);
  closeCalDropdowns(e);
};

onMounted(() => {
  document.addEventListener('click', closeAllDropdowns);
  fetchCityData().then(() => {
    if (cityData.value) {
      showHealthAlert(cityData.value.aqi, cityData.value.name);
      fetchAqiHistory();
      fetchCalendar();
      fetchWeatherForecast();
    }
    loadFavourites();
  });
});

onUnmounted(() => {
  document.removeEventListener('click', closeAllDropdowns);
});
</script>

<style scoped>
/* General Page Styling */
.bg-gradient-page {
  background: linear-gradient(135deg, #f0f4ff 0%, #e6f0fa 100%);
  min-height: 100vh;
  padding: 1.5rem 0.75rem;
  font-family: 'Nunito Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Animations */
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(15px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Wide container for the dashboard-style widgets (pollutants grid, AQI graph, calendar) —
   these are dense dark cards designed for a full-width layout, unlike the narrow legacy
   cards below them. */
.wide-container {
  max-width: 1280px;
  margin: 0 auto 1rem;
  padding: 0 0.75rem;
}

.no-scrollbar {
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* IE/Edge */
}
.no-scrollbar::-webkit-scrollbar {
  display: none; /* Chrome/Safari */
}

/* Top Bar */
.top-bar {
  animation: fadeInUp 0.5s ease-out;
  display: flex;
  justify-content: space-between;
  align-items: center;
  max-width: 1000px;
  margin: 0 auto 1rem;
  padding: 0 0.75rem;
}

.back-button {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.375rem 0.75rem;
  background: #ffffff;
  color: #374151;
  font-weight: 500;
  border-radius: 0.375rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease;
  font-size: 0.75rem;
}

.back-button:hover {
  background: #f0f4ff;
  transform: translateY(-1px);
  box-shadow: 0 3px 5px rgba(0, 0, 0, 0.08);
}

.last-updated {
  background: #e5efff;
  color: #4b5563;
  padding: 0.375rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.75rem;
  font-weight: 500;
}

/* Main Card */
.main-card {
  animation: fadeInUp 0.6s ease-out 0.1s both;
  background: #ffffff;
  border-radius: 0.75rem;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
  padding: 1.5rem;
}

.city-flag {
  width: 3.5rem;
  height: 2.25rem;
  object-fit: cover;
  border-radius: 0.25rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.city-name {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.125rem;
}

.city-coordinates {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  color: #6b7280;
  font-size: 0.75rem;
}

.aqi-badge {
  width: 3.5rem;
  height: 3.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  font-weight: 700;
  border-radius: 50%;
  transition: all 0.2s ease;
}

.aqi-badge-text {
  color: #000000;
  font-weight: 700;
}

.aqi-text-badge {
  padding: 0.375rem 0.75rem;
  border-radius: 0.75rem;
  font-weight: 500;
  font-size: 0.75rem;
  border-width: 1px;
}

.favourite-button {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.375rem 0.75rem;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  font-weight: 500;
  color: white;
  margin-top: 0.75rem;
  transition: all 0.2s ease;
}

.favourite-button:hover {
  transform: scale(1.03);
}

.favourite-button svg {
  width: 1rem;
  height: 1rem;
}

/* Weather Conditions */
.weather-card {
  animation: fadeInUp 0.7s ease-out 0.2s both;
  background: #ffffff;
  border-radius: 0.75rem;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
  padding: 1.5rem;
}

.weather-title {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  font-size: 1.25rem;
  font-weight: 600;
  color: #1e40af;
  margin-bottom: 1rem;
}

.weather-metric {
  background: #f9faff;
  border-radius: 0.5rem;
  padding: 0.75rem;
  text-align: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
  transition: transform 0.2s ease;
}

.weather-metric:hover {
  transform: translateY(-2px);
}

.weather-value {
  font-size: 1.125rem;
  font-weight: 600;
  color: #1f2937;
  margin: 0.375rem 0;
}

.weather-label {
  font-size: 0.75rem;
  color: #6b7280;
  text-transform: uppercase;
}

/* Loading & Error */
.loading-message, .error-message {
  animation: fadeInUp 0.5s ease-out;
  text-align: center;
  margin: 2rem auto;
  font-size: 0.875rem;
  font-weight: 500;
  max-width: 1000px;
}

.loading-message {
  color: #4b5563;
}

.error-message {
  color: #dc2626;
}

/* SweetAlert2 Custom Styles (Applied via customClass) */
.swal-custom {
  border-radius: 0.75rem;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
  background: #ffffff;
  padding: 1rem;
  width: 320px;
}

.swal-title {
  font-size: 1rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.75rem;
}

.swal-html {
  font-size: 0.75rem;
  color: #4b5563;
  line-height: 1.4;
}

.swal-html ul {
  padding-left: 1.25em;
  margin: 0;
}

.swal-html ul li {
  margin-bottom: 0.375rem;
}

.swal-html ul li strong {
  color: #1e40af;
}

.swal-button {
  padding: 0.375rem 1rem;
  background: #3b82f6;
  color: #ffffff;
  border-radius: 0.375rem;
  font-weight: 500;
  font-size: 0.75rem;
  transition: all 0.2s ease;
}

.swal-button:hover {
  background: #2563eb;
  transform: translateY(-1px);
}
</style>