<template>
  <div class="min-h-screen -m-6 bg-gray-50 dark:bg-[#0a0e17]">
    <div class="relative overflow-hidden">
      <!-- Whole-section backdrop for the Weather tab (not confined to a card) — pinned to the
           viewport (background-attachment: fixed) so it stays put on screen while the map
           banner, hero content, and Weather Parameters panel scroll over it like normal cards. -->
      <div
        v-if="activeHeroTab === 'weather'"
        class="absolute inset-0 z-0 pointer-events-none bg-scroll md:bg-fixed"
        :style="{ backgroundImage: `url(${weatherSceneBg})`, backgroundSize: 'cover', backgroundPosition: 'center' }"
      ></div>
      <div
        v-if="activeHeroTab === 'weather'"
        class="absolute inset-0 z-0 pointer-events-none"
        style="background: linear-gradient(180deg, rgba(255,255,255,0.08) 0%, rgba(255,255,255,0.02) 45%, rgba(255,255,255,0.02) 75%, rgba(10,14,23,0.6) 95%, #0a0e17 100%);"
      ></div>

      <!-- Map banner -->
      <div class="relative overflow-hidden" style="height: 400px;">
      <div
        id="hero-map"
        class="absolute inset-0 z-0 transition-opacity duration-300"
        :class="activeHeroTab === 'weather' ? 'opacity-0 pointer-events-none' : ''"
      ></div>
      <!-- Top vignette: keeps the map from touching the very top edge harshly -->
      <div class="absolute inset-x-0 top-0 z-[6] pointer-events-none" style="height: 56px; background: linear-gradient(to bottom, rgba(0,0,0,0.55), transparent);"></div>

      <!-- AQI / Weather tabs -->
      <Skeleton v-if="heroLoading" class="absolute top-4 left-4 z-20 h-8 w-36 rounded-lg bg-white/10" />
      <div v-else class="absolute top-4 left-4 z-20 flex items-center gap-1 rounded-lg p-1 border border-white/20">
        <button
          @click="router.push('/home')"
          class="px-3 py-1.5 rounded-md text-xs font-medium transition-colors"
          :class="activeHeroTab === 'aqi' ? 'bg-white text-slate-900' : (activeHeroTab === 'weather' ? 'text-slate-700 hover:bg-black/10' : 'text-gray-200 hover:bg-white/10')"
        >
          AQI
        </button>
        <button
          @click="router.push('/home/weather')"
          class="px-3 py-1.5 rounded-md text-xs font-medium transition-colors"
          :class="activeHeroTab === 'weather' ? 'bg-white text-slate-900' : 'text-gray-200 hover:bg-white/10'"
        >
          Weather
        </button>
      </div>

      <!-- Expand to full world map -->
      <Skeleton v-if="heroLoading" class="absolute top-4 right-4 z-20 h-8 w-24 rounded-lg bg-white/10" />
      <RouterLink
        v-else
        to="/world-map"
        class="absolute top-4 right-4 z-20 flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border transition-colors"
        :class="activeHeroTab === 'weather' ? 'text-slate-800 border-slate-400/40 hover:bg-black/10' : 'text-white border-white/10 hover:bg-white/10'"
      >
        AQI Map
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
      </RouterLink>
    </div>

    <div class="p-6">
    <div class="max-w-7xl mx-auto space-y-8">
      <!-- Compact AQI card: fitted to its content, background tinted by the live AQI status color.
           Top ~30% is transparent so it blends into the map instead of showing a hard seam,
           becoming fully opaque right around where the overlap ends. -->
      <div
        class="relative z-10 -mt-[150px] p-8 md:p-10 transition-[background] duration-500"
        :class="activeHeroTab === 'weather' ? '' : 'overflow-hidden rounded-2xl shadow-2xl border-x border-b border-slate-200 dark:border-white/10'"
        :style="{ background: heroCardBackground }"
      >
        <!-- Country-specific skyline, served from the backend's cached country SVGs, following the detected location -->
        <div
          v-if="activeHeroTab === 'aqi' && heroCountrySvgUrl"
          class="absolute left-0 bottom-0 z-0 pointer-events-none ml-6"
          :style="{ width: '320px', height: '85px', backgroundRepeat: 'no-repeat', backgroundPosition: 'bottom left', backgroundSize: 'contain', backgroundImage: `url('${heroCountrySvgUrl}')` }"
        ></div>
        <!-- Generic fallback skyline silhouette, used when no country SVG is cached for the detected location -->
        <div
          v-else-if="activeHeroTab === 'aqi'"
          class="absolute inset-x-0 bottom-0 z-0 pointer-events-none"
          style="height: 110px; opacity: 0.06; background-repeat: repeat-x; background-image: url(&quot;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 1200 120' preserveAspectRatio='none'%3E%3Cpath d='M0,120 L1200,120 L1200,90 L1150,90 L1150,40 L1130,40 L1130,90 L1050,90 L1050,20 L1020,20 L1020,90 L950,90 L950,50 L930,50 L930,90 L850,90 L850,10 L780,10 L780,90 L600,90 L600,0 L580,0 L580,90 L400,90 L400,30 L380,30 L380,90 L250,90 L250,60 L230,60 L230,90 L100,90 L100,70 L80,70 L80,90 L0,90 Z' fill='%23ffffff'%3E%3C/path%3E%3C/svg%3E&quot;);"
        ></div>

        <div class="relative z-10 flex items-start justify-between gap-4">
          <div>
            <Skeleton v-if="heroLoading" class="h-7 w-72 bg-white/10" />
            <h1 v-else class="text-base sm:text-2xl font-bold leading-tight" :class="activeHeroTab === 'weather' ? 'text-slate-800' : 'text-slate-900 dark:text-white'">{{ $t('home.title') }}</h1>
            <button
              v-if="nearestStation"
              @click="router.push(`/city/${nearestStation.id}`)"
              class="hover:underline text-sm mt-1"
              :class="activeHeroTab === 'weather' ? 'text-blue-700 hover:text-blue-600' : 'text-blue-600 dark:text-blue-300 hover:text-blue-500 dark:hover:text-blue-200 underline'"
            >
              {{ nearestStation.name }}
            </button>
            <p v-else-if="userLocation" class="text-sm mt-1" :class="activeHeroTab === 'weather' ? 'text-blue-700' : 'text-blue-600 dark:text-blue-300'">
              {{ userLocation.lat.toFixed(4) }}, {{ userLocation.lon.toFixed(4) }}
            </p>
            <Skeleton v-else class="h-4 w-32 mt-1 bg-white/10" />
            <p v-if="heroLastUpdated" class="text-xs font-bold mt-1" :class="activeHeroTab === 'weather' ? 'text-slate-600' : 'text-white'">
              {{ $t('home.lastUpdated') }} {{ heroLastUpdated.toLocaleString() }} {{ $t('home.localTime') }}
            </p>
            <Skeleton v-else class="h-3 w-40 mt-1 bg-white/10" />
          </div>
          <div class="flex items-center gap-2 flex-shrink-0">
            <Skeleton v-if="heroLoading" class="h-8 w-8 sm:w-28 rounded-full sm:rounded-md bg-white/10" />
            <button
              v-else
              @click="detectUserLocation"
              :title="$t('home.locateMe')"
              class="flex items-center justify-center sm:justify-start gap-1.5 h-8 w-8 sm:w-auto text-xs font-medium border px-0 sm:px-4 rounded-full sm:rounded-md transition-colors"
              :class="activeHeroTab === 'weather' ? 'text-blue-700 border-blue-500/50 hover:bg-black/10' : 'text-blue-300 border-blue-400/60 hover:bg-white/10'"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="flex-shrink-0">
                <path d="M9 12a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                <path d="M4 12a8 8 0 1 0 16 0a8 8 0 1 0 -16 0" />
                <path d="M12 2l0 2" />
                <path d="M12 20l0 2" />
                <path d="M20 12l2 0" />
                <path d="M2 12l2 0" />
              </svg>
              <span class="hidden sm:inline">{{ $t('home.locateMe') }}</span>
            </button>
            <Skeleton v-if="heroLoading" class="w-8 h-8 rounded-full bg-white/10" />
            <button
              v-else-if="nearestStation"
              @click="toggleFavorite(nearestStation)"
              class="w-8 h-8 flex items-center justify-center rounded-full border transition-colors"
              :class="isFavorite(nearestStation) ? 'text-red-400 border-red-400' : (activeHeroTab === 'weather' ? 'text-slate-800 border-slate-400/40 hover:bg-black/10' : 'text-white border-blue-400/60 hover:bg-white/10')"
            >
              <svg class="w-4 h-4" :fill="isFavorite(nearestStation) ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/></svg>
            </button>
            <Skeleton v-if="heroLoading" class="w-8 h-8 rounded-full bg-white/10" />
            <button
              v-else
              @click="shareLocation"
              class="w-8 h-8 flex items-center justify-center rounded-full border transition-colors"
              :class="activeHeroTab === 'weather' ? 'text-slate-800 border-slate-400/40 hover:bg-black/10' : 'text-white border-white/20 hover:bg-white/10'"
            >
              <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 12a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                <path d="M15 6a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                <path d="M15 18a3 3 0 1 0 6 0a3 3 0 1 0 -6 0" />
                <path d="M8.7 10.7l6.6 -3.4" />
                <path d="M8.7 13.3l6.6 3.4" />
              </svg>
            </button>
          </div>
        </div>

        <div v-if="activeHeroTab === 'aqi'" class="relative z-10 mt-6 grid grid-cols-1 lg:grid-cols-[1fr_auto_18rem] items-end gap-6">
          <!-- AQI panel -->
          <div class="flex flex-col gap-4">
            <div class="flex flex-wrap items-start gap-8">
              <div>
                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-gray-400 mb-1">
                  <span class="w-2 h-2 rounded-full bg-red-500"></span> {{ $t('home.liveAqi') }}
                </div>
                <div v-if="heroLoading" class="flex items-baseline gap-2">
                  <Skeleton class="h-14 w-24" />
                </div>
                <div v-else class="flex items-baseline gap-2">
                  <span class="text-6xl font-extrabold text-slate-900 dark:text-white">{{ nearestStation?.aqi ?? 'N/A' }}</span>
                  <span class="text-xs text-slate-500 dark:text-gray-400">AQI (US)</span>
                </div>
              </div>
              <div>
                <p class="text-xs text-slate-500 dark:text-gray-400 mb-1">{{ $t('home.airQualityIs') }}</p>
                <Skeleton v-if="heroLoading" class="h-8 w-28 rounded-md" />
                <div v-else class="flex items-center h-8 px-4 rounded-md text-lg font-bold text-white" :style="{ backgroundColor: heroAqiStatus.color }">
                  {{ heroAqiStatus.label }}
                </div>
              </div>
            </div>

            <div v-if="heroLoading" class="flex items-center gap-8">
              <Skeleton class="h-4 w-28" />
              <Skeleton class="h-4 w-28" />
            </div>
            <div v-else class="flex items-center gap-8 text-sm text-slate-800 dark:text-white">
              <div>
                PM2.5 :
                <strong>{{ nearestStation?.pm25 != null && nearestStation?.pm_estimated ? '~' : '' }}{{ nearestStation?.pm25 ?? 'N/A' }}</strong>
                µg/m³
                <span v-if="nearestStation?.pm25 != null && nearestStation?.pm_estimated" class="text-[10px] text-slate-400 dark:text-gray-400" title="Estimated from AQI (no direct sensor reading available for this location)">(est.)</span>
              </div>
              <div>
                PM10 :
                <strong>{{ nearestStation?.pm10 != null && nearestStation?.pm_estimated ? '~' : '' }}{{ nearestStation?.pm10 ?? 'N/A' }}</strong>
                µg/m³
                <span v-if="nearestStation?.pm10 != null && nearestStation?.pm_estimated" class="text-[10px] text-slate-400 dark:text-gray-400" title="Estimated from AQI (no direct sensor reading available for this location)">(est.)</span>
              </div>
            </div>

            <!-- AQI Scale: segmented, one flex block per level -->
            <div v-if="heroLoading" class="max-w-md space-y-1">
              <Skeleton class="h-3 w-full" />
              <Skeleton class="h-1.5 w-full rounded-full" />
              <Skeleton class="h-3 w-full" />
            </div>
            <div v-else class="max-w-md">
              <div class="flex justify-between text-[10px] font-bold text-slate-600 dark:text-gray-300 mb-1">
                <span v-for="lvl in heroAqiLevels" :key="lvl.label">{{ lvl.label }}</span>
              </div>
              <div class="relative">
                <div class="flex h-1.5 rounded-full overflow-hidden">
                  <div v-for="lvl in heroAqiLevels" :key="lvl.label" class="flex-1" :style="{ backgroundColor: lvl.color }"></div>
                </div>
                <div
                  class="absolute -top-1 w-3.5 h-3.5 rounded-full border-2 border-white dark:border-slate-900 shadow"
                  :style="{ left: heroAqiMarkerPercent + '%', transform: 'translateX(-50%)', backgroundColor: heroAqiStatus.color }"
                ></div>
              </div>
              <div class="flex justify-between text-[10px] text-slate-400 dark:text-gray-500 mt-1">
                <span>0</span><span>50</span><span>100</span><span>150</span><span>200</span><span>300</span><span>301+</span>
              </div>
            </div>
          </div>

          <!-- AQI mascot: centered in the gap between the AQI panel and weather chip, reflects the current AQI band -->
          <div class="hidden lg:flex items-end justify-center pointer-events-none">
            <img
              v-if="heroAqiMascot"
              :src="heroAqiMascot"
              :alt="heroAqiStatus.label"
              class="drop-shadow-xl"
              style="height: 180px; width: auto;"
            />
          </div>

          <!-- Weather panel: solid dark chip so it stays readable regardless of the card's underlying AQI-color gradient -->
          <div class="relative w-full lg:w-72 bg-black/40 border border-white/15 rounded-xl p-4 flex-shrink-0">
            <Skeleton v-if="heroLoading" class="absolute top-3 right-3 w-7 h-7 rounded-full bg-white/10" />
            <RouterLink
              v-else
              to="/analytics"
              class="absolute top-3 right-3 w-7 h-7 flex items-center justify-center rounded-full bg-white text-slate-900 hover:bg-gray-100 transition-colors"
              title="View more"
            >
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 17L17 7M17 7H7M17 7V17"/></svg>
            </RouterLink>
            <div v-if="heroLoading" class="flex items-center gap-3">
              <Skeleton class="w-9 h-9 rounded-full bg-white/10" />
              <Skeleton class="h-6 w-24 bg-white/10" />
            </div>
            <div v-else class="flex items-center gap-3">
              <img v-if="heroWeatherIsClear" :src="clearIcon" alt="Clear" class="w-9 h-9 shrink-0" />
              <span v-else class="text-3xl">{{ heroWeatherDisplay?.icon ? weatherIconEmoji(heroWeatherDisplay.icon) : '🌤️' }}</span>
              <div class="flex items-baseline gap-2">
                <span class="text-2xl font-bold text-white">{{ heroWeatherDisplay?.temp != null ? Math.round(heroWeatherDisplay.temp) : '—' }}<span class="text-sm font-normal text-gray-300">°C</span></span>
                <span class="text-sm font-medium text-gray-300 capitalize">{{ heroWeatherDisplay?.description || '—' }}</span>
              </div>
            </div>
            <div v-if="heroLoading" class="grid grid-cols-3 gap-2 mt-4 pt-3 border-t border-white/15">
              <Skeleton v-for="i in 3" :key="i" class="h-8 w-full bg-white/10" />
            </div>
            <div v-else class="grid grid-cols-3 gap-2 mt-4 pt-3 border-t border-white/15">
              <div class="flex items-start gap-1.5 min-w-0">
                <img :src="humidityIcon" alt="Humidity" class="w-3.5 h-3.5 shrink-0 mt-[1px] [filter:brightness(0)_invert(1)] opacity-90" />
                <div class="text-left leading-tight min-w-0">
                  <p class="text-[9px] text-gray-400 whitespace-nowrap">{{ $t('home.humidity') }}</p>
                  <p class="text-xs font-semibold text-white">{{ heroWeatherDisplay?.humidity ?? '—' }}%</p>
                </div>
              </div>
              <div class="flex items-start gap-1.5 min-w-0">
                <img :src="windIcon" alt="Wind Speed" class="w-3.5 h-3.5 shrink-0 mt-[1px] [filter:brightness(0)_invert(1)] opacity-90" />
                <div class="text-left leading-tight min-w-0">
                  <p class="text-[9px] text-gray-400 whitespace-nowrap">{{ $t('home.windSpeed') }}</p>
                  <p class="text-xs font-semibold text-white">{{ heroWeatherDisplay?.wind != null ? Math.round(heroWeatherDisplay.wind * 3.6) : '—' }}<span class="text-[9px] text-gray-300 font-medium">km/h</span></p>
                </div>
              </div>
              <div class="flex items-start gap-1.5 min-w-0">
                <img :src="uvIcon" alt="UV Index" class="w-3.5 h-3.5 shrink-0 mt-[1px] [filter:brightness(0)_invert(1)] opacity-90" />
                <div class="text-left leading-tight min-w-0">
                  <p class="text-[9px] text-gray-400 whitespace-nowrap">{{ $t('home.uvIndex') }}</p>
                  <p class="text-xs font-semibold text-white">{{ heroWeatherDisplay?.uv ?? '—' }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Weather tab: current conditions + forecast, real data from OpenWeather -->
        <div v-else class="relative z-10 mt-6 grid grid-cols-1 lg:grid-cols-[1fr_480px] gap-6">
          <div class="flex flex-col gap-4">
            <div class="flex items-start gap-6 flex-wrap">
              <div class="flex items-center gap-3">
                <span class="text-5xl">{{ weatherIconEmoji(weatherForecast?.current?.icon) }}</span>
                <div>
                  <div class="flex items-baseline gap-1">
                    <span class="text-6xl font-extrabold text-slate-800">{{ weatherForecast?.current?.temp ?? '—' }}</span>
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
              <div class="flex items-center gap-3 bg-black/40 rounded-xl px-4 py-3 flex-1 min-w-[220px]">
                <div class="flex-1 min-w-0">
                  <p class="text-[10px] font-bold uppercase tracking-wide" :style="{ color: heroAqiStatus.color }">{{ heroAqiStatus.label }}</p>
                  <p class="text-xs text-gray-400 mt-0.5">{{ aqiComfortLabel }}</p>
                </div>
                <div class="text-right flex-shrink-0">
                  <span class="text-xl font-bold text-white">{{ nearestStation?.aqi ?? 'N/A' }}</span>
                  <span class="text-[10px] text-gray-400 ml-1">AQI</span>
                </div>
              </div>
              <div class="flex items-center gap-3 bg-black/40 rounded-xl px-4 py-3 flex-1 min-w-[220px]">
                <div class="flex-1 min-w-0">
                  <p class="text-[10px] font-bold uppercase tracking-wide text-blue-300">{{ $t('home.humidity') }}</p>
                  <p class="text-xs text-gray-400 mt-0.5">{{ humidityLabel }}</p>
                </div>
                <div class="text-right flex-shrink-0">
                  <span class="text-xl font-bold text-white">{{ weatherForecast?.current?.humidity ?? '—' }}%</span>
                </div>
              </div>
            </div>

            <p v-if="heroLastUpdated" class="text-xs text-gray-300 italic mt-1 drop-shadow">{{ $t('home.lastUpdated') }} {{ heroLastUpdated.toLocaleString() }} {{ $t('home.localTime') }}</p>
          </div>

          <div class="relative bg-black/35 backdrop-blur-xl border border-white/10 rounded-2xl p-5 shadow-lg">
            <Skeleton v-if="weatherLoading" class="h-7 w-32 rounded-[3px] bg-white/10 mb-3" />
            <div v-else class="flex items-center bg-white/10 rounded-[3px] p-1 w-fit mb-3">
              <button
                @click="forecastRange = 'hourly'"
                class="px-3 py-1 rounded-sm text-xs font-medium transition-colors"
                :class="forecastRange === 'hourly' ? 'bg-blue-500 text-white' : 'text-gray-300 hover:text-white'"
              >{{ $t('home.hourly') }}</button>
              <button
                @click="forecastRange = 'daily'"
                class="px-3 py-1 rounded-sm text-xs font-medium transition-colors"
                :class="forecastRange === 'daily' ? 'bg-blue-500 text-white' : 'text-gray-300 hover:text-white'"
              >{{ $t('home.daily') }}</button>
            </div>

            <div v-if="weatherLoading" class="space-y-3 py-2">
              <div class="flex gap-4">
                <Skeleton v-for="i in 6" :key="i" class="w-14 h-16 flex-shrink-0 bg-white/10" />
              </div>
              <Skeleton class="h-8 w-full bg-white/10" />
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
            <p v-else class="text-xs text-gray-500 text-center py-8">{{ $t('home.noForecastData') }}</p>
          </div>
        </div>
      </div>

      <WeatherParametersPanel
        v-if="activeHeroTab === 'weather'"
        :city-name="nearestStation?.name?.split(',')[0] || 'Your Location'"
        :weather="weatherForecast?.current"
      />
    </div>
    </div>
    </div>

    <div class="px-6 pb-6">
    <div class="max-w-7xl mx-auto space-y-8 mt-8">
      <!-- Major Air Pollutants -->
      <div>
        <div class="flex items-center justify-between mb-4">
          <div v-if="heroLoading" class="space-y-2">
            <Skeleton class="h-6 w-48" />
            <Skeleton class="h-4 w-32" />
          </div>
          <div v-else>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $t('home.majorPollutants') }}</h2>
            <button
              v-if="nearestStation"
              @click="router.push(`/city/${nearestStation.id}`)"
              class="text-blue-600 dark:text-blue-400 text-sm hover:underline"
            >
              {{ nearestStation.name }}
            </button>
          </div>
        </div>
        <div v-if="heroLoading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="i in 6"
            :key="i"
            class="flex items-center gap-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 rounded-xl p-6"
          >
            <Skeleton class="w-14 h-14 rounded-lg flex-shrink-0" />
            <div class="flex-1 min-w-0 space-y-2">
              <Skeleton class="h-4 w-20" />
              <Skeleton class="h-3 w-10" />
            </div>
            <Skeleton class="h-6 w-10 flex-shrink-0" />
          </div>
        </div>
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <div
            v-for="p in pollutantCards"
            :key="p.key"
            class="relative flex items-center gap-5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 rounded-xl p-6 overflow-hidden"
          >
            <div class="absolute left-0 top-0 bottom-0 w-1.5" :style="{ backgroundColor: p.color }"></div>
            <img :src="p.icon" :alt="p.label" class="w-14 h-14 flex-shrink-0" />
            <div class="flex-1 min-w-0">
              <p class="text-base font-medium text-slate-900 dark:text-white">{{ p.label }}</p>
              <p class="text-sm text-slate-500 dark:text-gray-400">({{ p.abbr }})</p>
            </div>
            <div class="text-right flex-shrink-0">
              <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ p.value ?? 'N/A' }}</p>
              <p class="text-sm text-slate-400 dark:text-gray-500">{{ p.unit }}</p>
            </div>
            <svg class="w-5 h-5 text-slate-300 dark:text-gray-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
          </div>
        </div>
      </div>

      <!-- AQI Graph -->
      <div>
        <div class="flex items-center justify-between mb-4 flex-wrap gap-3">
          <div v-if="heroLoading" class="space-y-2">
            <Skeleton class="h-3 w-16" />
            <Skeleton class="h-6 w-56" />
            <Skeleton class="h-4 w-32" />
          </div>
          <div v-else>
            <p class="text-xs font-semibold text-slate-400 dark:text-gray-500 uppercase tracking-wide">{{ $t('home.aqiGraph') }}</p>
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">{{ $t('home.historicalData') }}</h2>
            <button v-if="nearestStation" @click="router.push(`/city/${nearestStation.id}`)" class="text-blue-600 dark:text-blue-400 text-sm hover:underline">
              {{ nearestStation.name }}
            </button>
          </div>

          <div v-if="heroLoading" class="flex items-center gap-2">
            <Skeleton class="w-9 h-9 rounded-lg" />
            <Skeleton class="w-9 h-9 rounded-lg" />
            <Skeleton class="h-9 w-28 rounded-lg" />
            <Skeleton class="h-9 w-28 rounded-lg" />
          </div>
          <div v-else class="flex items-center gap-2" ref="graphControlsRef">
            <button
              type="button"
              title="Line chart"
              class="w-9 h-9 flex items-center justify-center rounded-lg border shadow-sm dark:shadow-none transition-colors"
              :class="chartType === 'line' ? 'bg-slate-800 border-slate-800 text-white dark:bg-white/10 dark:border-white/30' : 'bg-white border-gray-300 text-gray-500 hover:text-gray-800 hover:border-gray-400 dark:bg-white/5 dark:border-white/10 dark:text-gray-400 dark:hover:text-white'"
              @click="chartType = 'line'"
            >
              <i class="fa-solid fa-chart-line text-sm"></i>
            </button>
            <button
              type="button"
              title="Bar chart"
              class="w-9 h-9 flex items-center justify-center rounded-lg border shadow-sm dark:shadow-none transition-colors"
              :class="chartType === 'bar' ? 'bg-slate-800 border-slate-800 text-white dark:bg-white/10 dark:border-white/30' : 'bg-white border-gray-300 text-gray-500 hover:text-gray-800 hover:border-gray-400 dark:bg-white/5 dark:border-white/10 dark:text-gray-400 dark:hover:text-white'"
              @click="chartType = 'bar'"
            >
              <i class="fa-solid fa-chart-bar text-sm"></i>
            </button>

            <div class="relative">
              <div
                class="flex items-center gap-2 bg-white border border-gray-300 shadow-sm rounded-lg px-3 py-2 text-xs font-semibold text-gray-700 cursor-pointer select-none hover:border-gray-400 dark:bg-white/5 dark:border-white/10 dark:shadow-none dark:text-gray-200 dark:hover:border-white/30"
                @click="hoursOpen = !hoursOpen; metricOpen = false"
              >
                <span>{{ hoursLabel }}</span>
                <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 transition-transform" :class="{ 'rotate-180': hoursOpen }"></i>
              </div>
              <div v-if="hoursOpen" class="absolute right-0 top-[calc(100%+8px)] min-w-[160px] bg-white border border-gray-200 shadow-xl dark:bg-[#1c222d] dark:border-white/10 rounded-lg p-1.5 z-20">
                <div
                  v-for="(o, i) in hourOptions"
                  :key="o.hours"
                  class="flex items-center gap-2.5 px-3 py-2.5 text-xs font-semibold rounded-md cursor-pointer whitespace-nowrap"
                  :class="[hours === o.hours ? 'text-gray-900 dark:text-white' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white', i < hourOptions.length - 1 ? 'mb-1' : '']"
                  @click="setHours(o.hours)"
                >
                  <span class="w-[3px] h-4 rounded-sm flex-shrink-0" :class="hours === o.hours ? 'bg-blue-400' : 'bg-transparent'"></span>{{ o.label }}
                </div>
              </div>
            </div>

            <div class="relative">
              <div
                class="flex items-center gap-2 bg-white border border-gray-300 shadow-sm rounded-lg px-3 py-2 text-xs font-semibold text-gray-700 cursor-pointer select-none hover:border-gray-400 dark:bg-white/5 dark:border-white/10 dark:shadow-none dark:text-gray-200 dark:hover:border-white/30"
                @click="metricOpen = !metricOpen; hoursOpen = false"
              >
                <span>{{ metricLabel }}</span>
                <i class="fa-solid fa-chevron-down text-[10px] text-gray-400 transition-transform" :class="{ 'rotate-180': metricOpen }"></i>
              </div>
              <div v-if="metricOpen" class="absolute right-0 top-[calc(100%+8px)] min-w-[160px] bg-white border border-gray-200 shadow-xl dark:bg-[#1c222d] dark:border-white/10 rounded-lg p-1.5 z-20">
                <div
                  v-for="(m, i) in metricOptions"
                  :key="m.key"
                  class="flex items-center gap-2.5 px-3 py-2.5 text-xs font-semibold rounded-md cursor-pointer whitespace-nowrap"
                  :class="[metric === m.key ? 'text-gray-900 dark:text-white' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white', i < metricOptions.length - 1 ? 'mb-1' : '']"
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
            <div v-if="heroLoading" class="flex items-center gap-2 bg-white/5 border border-white/10 rounded-lg px-3 py-1.5">
              <Skeleton class="w-2 h-2 rounded-full bg-white/10" />
              <Skeleton class="h-3 w-24 bg-white/10" />
            </div>
            <div v-else class="flex items-center gap-2 bg-white/5 border border-white/10 rounded-lg px-3 py-1.5">
              <span class="w-2 h-2 rounded-full" :style="{ backgroundColor: heroAqiStatus.color }"></span>
              <span class="text-xs font-medium text-gray-200">{{ nearestStation?.name || 'Unknown location' }}</span>
            </div>
            <div v-if="historyLoading" class="flex items-center gap-2">
              <Skeleton class="h-8 w-32 rounded-lg bg-white/10" />
              <Skeleton class="h-8 w-32 rounded-lg bg-white/10" />
            </div>
            <div v-else-if="aqiHistory.min && aqiHistory.max" class="flex items-center gap-2">
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

          <div v-if="historyLoading" class="relative" style="height: 220px;">
            <Skeleton class="absolute inset-0 rounded-lg bg-white/5" />
          </div>
          <div v-else-if="chartPoints.length" class="relative" style="height: 220px;">
            <svg
              ref="svgRef"
              class="w-full h-full block overflow-visible"
              :viewBox="`0 0 ${CH.W} ${CH.H}`"
              preserveAspectRatio="none"
            >
              <defs>
                <linearGradient id="homeAqiAreaGrad" x1="0" y1="0" x2="0" y2="1">
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
                <path :d="areaPath" fill="url(#homeAqiAreaGrad)" stroke="none" />
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
            {{ $t('home.notEnoughData') }}
          </div>

          <div v-if="chartPoints.length" class="flex items-center justify-between mt-2 text-[11px] font-semibold text-gray-500">
            <span>{{ startDate }}</span>
            <span>{{ endDate }}</span>
          </div>
        </div>
      </div>
    </div>
    </div>
  </div>
</template>
<script setup>
import { onMounted, onUnmounted, ref, watch, computed, nextTick } from "vue";
import { useRoute, useRouter } from "vue-router";
import { useI18n } from "vue-i18n";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import axios from "axios";
import { API_ROOT } from "@/services/api.js";
import Swal from "sweetalert2";
import { useAuthStore } from "@/stores/airQuality";
import { useThemeStore } from "@/stores/theme";
import WeatherParametersPanel from "@/components/WeatherParametersPanel.vue";
import Skeleton from "@/components/Skeleton.vue";
import weatherSceneBg from "@/assets/images/svg/15.webp";
import humidityIcon from "@/assets/images/svg/humidity.svg";
import windIcon from "@/assets/images/svg/wind-speed.svg";
import temperatureIcon from "@/assets/images/svg/temperature.svg";
import rainfallIcon from "@/assets/images/svg/rainfall.svg";
import uvIcon from "@/assets/images/svg/uv-index.svg";
import clearIcon from "@/assets/images/svg/clear.svg";
import cloudyIcon from "@/assets/images/svg/cloudy.svg";
import overcastIcon from "@/assets/images/svg/overcast.svg";
import lightRainShowerIcon from "@/assets/images/svg/light-rain-shower.svg";
import moderateOrHeavyRainShowerIcon from "@/assets/images/svg/moderate-or-heavy-rain-shower.svg";
import pm25Icon from "@/assets/images/svg/pm25.svg";
import pm10Icon from "@/assets/images/svg/pm10.svg";
import coIcon from "@/assets/images/svg/co.svg";
import so2Icon from "@/assets/images/svg/so2.svg";
import no2Icon from "@/assets/images/svg/no2.svg";
import o3Icon from "@/assets/images/svg/o3.svg";
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
const theme = useThemeStore();
const router = useRouter();
const { t } = useI18n();
const route = useRoute();
const aqiData = ref([]);
const favorites = ref([]);
const auth = useAuthStore();
const userLocation = ref(null);

// --- Hero AQI card state (weather panel + cached map location) ---
const heroWeather = ref(null); // from OpenWeather (/api/air-quality/phnom-penh)
const heroLastUpdated = ref(null);
// A real route (not just local state) so the Weather view is its own shareable/bookmarkable
// page — /home stays AQI, /home/weather renders the same component in weather mode.
const activeHeroTab = computed(() => (route.path === '/home/weather' ? 'weather' : 'aqi'));

// Merges OpenWeather data with WAQI's own optional weather sensor readings (temperature,
// humidity, wind — already synced into the `stations` table alongside pollutant data), so
// the panel still shows real numbers for those three fields even when OpenWeather is down
// or unconfigured. Description, icon, and UV index have no WAQI equivalent, so they stay
// OpenWeather-only.
const heroWeatherDisplay = computed(() => {
  const station = nearestStation.value;
  const stationTemp = station?.temperature != null ? Number(station.temperature) : null;
  const stationHumidity = station?.humidity != null ? Number(station.humidity) : null;
  const stationWind = station?.wind_speed != null ? Number(station.wind_speed) : null;

  if (!heroWeather.value && stationTemp == null && stationHumidity == null && stationWind == null) {
    return null;
  }

  return {
    temp: heroWeather.value?.temp ?? stationTemp,
    description: heroWeather.value?.description ?? null,
    icon: heroWeather.value?.icon ?? null,
    humidity: heroWeather.value?.humidity ?? stationHumidity,
    wind: heroWeather.value?.wind ?? stationWind, // both OpenWeather and WAQI report wind in m/s
    uv: heroWeather.value?.uv ?? null,
  };
});

const handleHeroResize = () => {
  nextTick(() => heroMap?.invalidateSize());
};

const MAP_LOCATION_CACHE_KEY = 'cached_map_location';
const MAP_LOCATION_TTL_MS = 15 * 60 * 1000; // 15 minutes

function readCachedMapLocation() {
  try {
    const raw = localStorage.getItem(MAP_LOCATION_CACHE_KEY);
    if (!raw) return null;
    const cached = JSON.parse(raw);
    if (Date.now() - cached.timestamp > MAP_LOCATION_TTL_MS) return null;
    return cached;
  } catch {
    return null;
  }
}

function writeCachedMapLocation(lat, lon) {
  localStorage.setItem(MAP_LOCATION_CACHE_KEY, JSON.stringify({ lat, lon, timestamp: Date.now() }));
}

const heroAqiLevels = [
  { label: 'Good', max: 50, color: '#4cd964' },      // bg-emerald-500
  { label: 'Moderate', max: 100, color: '#ffcc00' },  // bg-amber-400
  { label: 'Poor', max: 150, color: '#ff9500' },      // bg-orange-500 (Unhealthy for Sensitive Groups)
  { label: 'Unhealthy', max: 200, color: '#ff2d55' }, // bg-red-500
  { label: 'Severe', max: 300, color: '#af52de' },    // bg-purple-600
  { label: 'Hazardous', max: Infinity, color: '#ff3b30' }, // bg-red-700 (Maroon/Dark Red)
];

const heroAqiStatus = computed(() => {
  const aqi = parseFloat(nearestStation.value?.aqi);
  if (isNaN(aqi)) return { label: 'N/A', color: '#999' };
  const level = heroAqiLevels.find((l) => aqi <= l.max);
  return level || heroAqiLevels[heroAqiLevels.length - 1];
});

const heroAqiMascot = computed(() => aqiLevelMascots[heroAqiStatus.value.label] || null);

// Colors each pollutant card's left edge using the same AQI-band thresholds as the main
// scale — WAQI's per-pollutant iaqi values are themselves AQI sub-indices (not raw
// concentrations), so the same 0-500 bands apply consistently across all of them.
const colorForPollutantValue = (value) => {
  const v = parseFloat(value);
  if (isNaN(v)) return '#94a3b8';
  const level = heroAqiLevels.find((l) => v <= l.max);
  return (level || heroAqiLevels[heroAqiLevels.length - 1]).color;
};

// PM2.5/PM10 are genuinely µg/m³ (EPA's AQI breakpoints are defined directly in that unit,
// so WAQI's sub-index for them tracks µg/m³ closely at the low end). CO/SO2/NO2/O3 are only
// available from WAQI as AQI sub-indices, not real ppb concentrations, so labeling them
// "AQI" instead of a physical unit avoids asserting a number we can't actually back up.
const pollutantCards = computed(() => {
  const s = nearestStation.value;
  const defs = [
    { key: 'pm25', label: t('home.particulateMatter'), abbr: 'PM2.5', icon: pm25Icon, unit: 'µg/m³', value: s?.pm25 },
    { key: 'pm10', label: t('home.particulateMatter'), abbr: 'PM10', icon: pm10Icon, unit: 'µg/m³', value: s?.pm10 },
    { key: 'co', label: t('home.carbonMonoxide'), abbr: 'CO', icon: coIcon, unit: 'AQI', value: s?.co },
    { key: 'so2', label: t('home.sulfurDioxide'), abbr: 'SO₂', icon: so2Icon, unit: 'AQI', value: s?.so2 },
    { key: 'no2', label: t('home.nitrogenDioxide'), abbr: 'NO₂', icon: no2Icon, unit: 'AQI', value: s?.no2 },
    { key: 'o3', label: t('home.ozone'), abbr: 'O₃', icon: o3Icon, unit: 'AQI', value: s?.o3 },
  ];
  return defs.map((d) => ({
    ...d,
    value: d.value ?? null,
    color: colorForPollutantValue(d.value),
  }));
});

// Real recorded pollutant points for the nearest station, from the backend's aqi_history
// table (populated by the 30-min stations:sync job and live hero-card fetches — no
// fake/dummy data). Sparse until enough sync cycles have run; the template shows an
// empty-state message instead of a misleading near-blank chart in that case.
const aqiHistory = ref({ points: [], min: null, max: null, hours: 24, metric: 'aqi', unit: '' });
// True until the first fetchAqiHistory() call resolves, so the graph shows a
// skeleton instead of the misleading "not enough data" empty-state while
// the request (which only starts once nearestStation resolves) is in flight.
const historyLoading = ref(true);

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
  const name = nearestStation.value?.name;
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
  } finally {
    historyLoading.value = false;
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

// --- Compact SVG chart (line/bar) matching the Compare Cities graph's visual style ---
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

// Catmull-Rom → Bezier smooth curve through the points.
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

// --- Hover / tooltip ---
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

// Card background: translucent gray at the very top (so the card still reads as a distinct
// card over the map, instead of disappearing completely), fading to the theme's base color
// over exactly 150px (a fixed pixel distance, matching the card's -mt-[150px] overlap) so
// opacity reaches 100% right at the map's true bottom edge, then washing into the AQI color.
const heroCardBackground = computed(() => {
  if (activeHeroTab.value === 'weather') {
    // No card box in weather mode — the illustrated scene now bleeds behind the whole
    // section (see the absolute backdrop layers wrapping the map banner), so this element
    // itself stays fully transparent rather than painting its own background.
    return 'transparent';
  }
  const color = heroAqiStatus.value.color;
  const base = theme.mode === 'dark' ? '27,27,27' : '255,255,255';
  return `linear-gradient(180deg, rgba(107,114,128,0.45) 0px, rgba(${base},1) 150px, ${color}55 60%, ${color} 100%)`;
});

// Marker position along the 6 equal-width category bins (matches how the scale is drawn)
const heroAqiMarkerPercent = computed(() => {
  const aqi = parseFloat(nearestStation.value?.aqi);
  if (isNaN(aqi)) return 0;
  const bounds = [0, 50, 100, 150, 200, 300, 301];
  let idx = heroAqiLevels.findIndex((l) => aqi <= l.max);
  if (idx === -1) idx = heroAqiLevels.length - 1;
  const lower = bounds[idx];
  const upper = bounds[idx + 1] ?? lower + 100;
  const fraction = Math.min(Math.max((aqi - lower) / (upper - lower), 0), 1);
  return ((idx + fraction) / heroAqiLevels.length) * 100;
});

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

// OpenWeather icon group "01" = clear sky — show the clear.svg illustration instead of the emoji
const heroWeatherIsClear = computed(() =>
  !!heroWeatherDisplay.value?.icon && heroWeatherDisplay.value.icon.slice(0, 2) === '01'
);

const fetchHeroWeather = async (lat, lon) => {
  try {
    const { data } = await axios.get(
      `${API_ROOT}/api/air-quality/phnom-penh?lat=${lat}&lon=${lon}`
    );
    heroWeather.value = {
      temp: data.Temp_C,
      description: data.Weather_Description,
      icon: data.Weather_Icon,
      humidity: data.Humidity_percent,
      wind: data.Wind_m_s,
      uv: data.UV_Index,
    };
    heroLastUpdated.value = new Date();
  } catch (err) {
    console.error('Failed to fetch hero weather:', err);
  }
};

// --------------------------
// WEATHER TAB — real current + forecast data from OpenWeather (5-day/3-hour forecast is
// the finest granularity the free tier offers, so "Hourly" below is real 3-hour steps,
// not fabricated hourly interpolation). Mirrors CityDetailView's weather tab.
// --------------------------
const weatherForecast = ref(null);
const weatherLoading = ref(false);
const forecastRange = ref('hourly'); // 'hourly' | 'daily'

const fetchWeatherForecast = async () => {
  const lat = userLocation.value?.lat ?? nearestStation.value?.lat;
  const lon = userLocation.value?.lon ?? nearestStation.value?.lon;
  if (lat == null || lon == null) return; // retried by the watcher below once the location resolves
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

const humidityLabel = computed(() => {
  const h = weatherForecast.value?.current?.humidity;
  if (h == null) return '';
  if (h >= 70) return 'Humid conditions expected.';
  if (h >= 40) return 'Comfortable humidity expected.';
  return 'Dry air expected.';
});

const aqiComfortLabel = computed(() => {
  const aqi = parseFloat(nearestStation.value?.aqi);
  if (isNaN(aqi)) return '';
  if (aqi <= 50) return 'Acceptable air.';
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
    y: 28 - ((t - min) / span) * 26,
  }));
});

const forecastSparkline = computed(() =>
  forecastSparklinePoints.value
    .map((p, i) => `${i === 0 ? 'M' : 'L'}${p.x.toFixed(1)},${p.y.toFixed(1)}`)
    .join(' ')
);

// Clicking the Weather tab can race the async geolocation lookup — retry once a location
// becomes available if that first attempt bailed out with nothing to fetch yet.
watch(userLocation, () => {
  if (activeHeroTab.value === 'weather' && !weatherForecast.value) fetchWeatherForecast();
});

// Landing on /home/weather directly (typed URL, refresh, bookmark) needs the same fetch
// that the tab-button click handler triggers — this covers that entry path too. Not
// `immediate` because it would run before nearestStation (declared later) initializes.
watch(
  () => route.path,
  (path) => {
    if (path === '/home/weather' && !weatherForecast.value) fetchWeatherForecast();
  }
);

const shareLocation = async () => {
  const name = nearestStation.value?.name || 'this location';
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

let heroMap = null;
let heroMapMarker = null;
let heroTileLayer = null;

// dark_all for dark mode; voyager (colorful, with parks/water) instead of the plain
// grayscale light_all style for light mode.
const heroTileUrl = () =>
  theme.mode === 'dark'
    ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
    : 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png';

const initHeroMap = () => {
  heroMap = L.map('hero-map', {
    zoomControl: false,
    attributionControl: false,
    dragging: true,
    scrollWheelZoom: true,
    doubleClickZoom: true,
    boxZoom: true,
    keyboard: true,
    touchZoom: true,
    center: [11.5564, 104.9282],
    zoom: 13,
  });

  heroTileLayer = L.tileLayer(heroTileUrl(), { maxZoom: 19 }).addTo(heroMap);
};

// Swap the basemap tiles between dark_all/light_all when the theme toggles
watch(() => theme.mode, () => {
  if (!heroMap || !heroTileLayer) return;
  heroMap.removeLayer(heroTileLayer);
  heroTileLayer = L.tileLayer(heroTileUrl(), { maxZoom: 19 }).addTo(heroMap);
});

const updateHeroMap = () => {
  if (!heroMap || !nearestStation.value?.lat) return;
  const { lat, lon, aqi } = nearestStation.value;
  heroMap.setView([lat, lon], 13);

  if (heroMapMarker) heroMap.removeLayer(heroMapMarker);
  heroMapMarker = L.marker([lat, lon], {
    icon: L.divIcon({
      html: `<div style="background:${heroAqiStatus.value.color};color:#fff;font-weight:700;font-size:12px;width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;border:2px solid #fff;box-shadow:0 2px 6px rgba(0,0,0,0.4);">${aqi ?? '—'}</div>`,
      className: '',
      iconSize: [34, 34],
      iconAnchor: [17, 17],
    }),
  }).addTo(heroMap);
};
// Computed property for nearest station
const nearestStation = computed(() => {
  if (!userLocation.value || aqiData.value.length === 0) return null;
  let minDist = Infinity;
  let nearest = null;
  aqiData.value.forEach(station => {
    if (!station.lat || !station.lon) return;
    const dist = calculateDistance(userLocation.value.lat, userLocation.value.lon, station.lat, station.lon);
    if (dist < minDist) {
      minDist = dist;
      nearest = station;
    }
  });
  return nearest;
});

// True until geolocation + the station list have both resolved and produced
// a nearest station — gates the hero AQI/weather/pollutant skeletons.
const heroLoading = computed(() => !nearestStation.value);

// Derives the backend's country-svg slug (lowercase, hyphenated, e.g. "united-states")
// from either an explicit `country` field or the trailing comma segment of the WAQI
// station name (e.g. "Some St, Phnom Penh, Cambodia" -> "cambodia").
const heroCountrySlug = computed(() => {
  const station = nearestStation.value;
  if (!station) return null;
  const raw = station.country || station.name?.split(',').pop();
  if (!raw) return null;
  const slug = raw.trim().toLowerCase().replace(/[^a-z0-9\s-]/g, '').replace(/\s+/g, '-');
  return slug || null;
});

// Country background SVG served from the backend's local cache (php artisan svg:cache).
// Falls back to nothing (generic skyline shown instead) if the country isn't cached.
const heroCountrySvgUrl = computed(() =>
  heroCountrySlug.value ? `${API_ROOT}/api/svg/${heroCountrySlug.value}` : null
);

// Fetch data
const fetchAQIData = async () => {
  try {
    const { data } = await axios.get(`${API_ROOT}/api/aqi`);
    if (data.status === "ok" && Array.isArray(data.data)) {
      aqiData.value = assignIDs(data.data);
      updateHeroMap();
    }
  } catch (error) {
    console.error("Error fetching AQI data:", error);
  }
};
// Keeps Phnom Penh's iqair_readings row fresh (so it's always available, even for
// viewers physically elsewhere) — but doesn't touch aqiData directly anymore. The
// backend's /api/aqi already merges in iqair_readings entries (including this one),
// so pushing it here too would create a duplicate "Phnom Penh" station.
const fetchPhnomPenhAQI = async () => {
  try {
    await axios.get(`${API_ROOT}/api/air-quality/phnom-penh`);
  } catch (error) {
    console.error("Error refreshing Phnom Penh AQI data:", error);
  }
};
const assignIDs = (data) => {
  return data.map((station, index) => ({
    ...station,
    id: station.id || index + 1,
    flag: station.flag || `https://flagcdn.com/w160/${getCountryCode(station.country || 'kh')}.png`,
  }));
};
// Helper to extract country code from flag URL or country name
const getCountryCode = (country) => {
  const countryCodeMap = {
    'united states': 'us',
    'united kingdom': 'gb',
    'france': 'fr',
    'germany': 'de',
    'italy': 'it',
    'spain': 'es',
    'china': 'cn',
    'india': 'in',
    'japan': 'jp',
    'brazil': 'br',
    'russia': 'ru',
    'canada': 'ca',
    'australia': 'au',
    'cambodia': 'kh',
    'thailand': 'th',
    'singapore': 'sg',
    'malaysia': 'my',
    'indonesia': 'id',
    'philippines': 'ph',
    'vietnam': 'vn',
    'south korea': 'kr',
    'myanmar': 'mm',
    'laos': 'la',
    'bangladesh': 'bd',
    'nepal': 'np',
  };
  if (!country) return 'kh';
  const normalizedCountry = country.toLowerCase();
  return countryCodeMap[normalizedCountry] || 'kh';
};
// Fetch favorites
const fetchFavorites = async () => {
  if (!auth.token) return; // not logged in — nothing to fetch, avoid a guaranteed 401
  try {
    const { data } = await axios.get(`${API_ROOT}/api/favourites`, {
      headers: { Authorization: `Bearer ${auth.token}` },
    });
    favorites.value = data.map(f => ({
      id: f.id,
      name: f.city_name,
      country_code: f.country_code,
      flag: `https://flagcdn.com/w160/${f.country_code.toLowerCase()}.png`,
    }));
  } catch (err) {
    console.error("Failed to fetch favorites:", err);
  }
};
// Check if city is favorite
const isFavorite = (station) => {
  return favorites.value.some(fav => fav.name.toLowerCase() === station.name.toLowerCase());
};
// Toggle favorite
const toggleFavorite = async (station) => {
  try {
    const isFav = isFavorite(station);
    if (isFav) {
      const fav = favorites.value.find(f => f.name.toLowerCase() === station.name.toLowerCase());
      if (!fav || !fav.id) {
        throw new Error("Favorite ID not found");
      }
      await axios.delete(
        `${API_ROOT}/api/favourites/${fav.id}`,
        { headers: { Authorization: `Bearer ${auth.token}` } }
      );
      favorites.value = favorites.value.filter(f => f.id !== fav.id);
      Swal.fire({
        icon: "success",
        title: "Removed from Favorites",
        timer: 1200,
        showConfirmButton: false,
        toast: true,
        position: 'top-end',
        background: '#f6fafd',
        iconColor: '#10b981',
        customClass: {
          popup: 'rounded-xl shadow-lg',
          title: 'font-bold text-gray-800',
        },
      });
    } else {
      const countryCode = station.flag
        ? station.flag.split("/").pop().split(".")[0]
        : getCountryCode(station.country || 'kh');
      await axios.post(
        `${API_ROOT}/api/favourites`,
        {
          city_name: station.name,
          country_code: countryCode,
        },
        { headers: { Authorization: `Bearer ${auth.token}` } }
      );
      await fetchFavorites();
      Swal.fire({
        icon: "success",
        title: "Added to Favorites",
        timer: 1200,
        showConfirmButton: false,
        toast: true,
        position: 'top-end',
        background: '#f6fafd',
        iconColor: '#10b981',
        customClass: {
          popup: 'rounded-xl shadow-lg',
          title: 'font-bold text-gray-800',
        },
      });
    }
  } catch (err) {
    console.error("Error updating favorite:", err);
    Swal.fire({
      icon: "error",
      title: "Failed to update favorite",
      text: err.response?.data?.message || err.message,
      toast: true,
      position: 'top-end',
      background: '#fef2f2',
      iconColor: '#ef4444',
      customClass: {
        popup: 'rounded-xl shadow-lg',
        title: 'font-bold text-gray-800',
      },
    });
  }
};
// Calculate distance between two coordinates
const calculateDistance = (lat1, lon1, lat2, lon2) => {
  const R = 6371; // Radius of the earth in km
  const dLat = deg2rad(lat2 - lat1);
  const dLon = deg2rad(lon2 - lon1);
  const a = 
    Math.sin(dLat/2) * Math.sin(dLat/2) +
    Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
    Math.sin(dLon/2) * Math.sin(dLon/2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
  const d = R * c; // Distance in km
  return d;
};
const deg2rad = (deg) => {
  return deg * (Math.PI/180);
};
// Detect user location
const detectUserLocation = () => {
  // Reuse the cached map location if we have one — avoids re-prompting for
  // geolocation permission and re-fetching weather on every page visit.
  const cached = readCachedMapLocation();
  if (cached) {
    userLocation.value = { lat: cached.lat, lon: cached.lon };
    updateHeroMap();
    fetchHeroWeather(cached.lat, cached.lon);
    return;
  }

  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const lat = position.coords.latitude;
        const lon = position.coords.longitude;
        userLocation.value = { lat, lon };
        writeCachedMapLocation(lat, lon);
        updateHeroMap();
        fetchHeroWeather(lat, lon);
      },
      (error) => {
        console.warn("Geolocation error:", error.message);
        // Set a default location if geolocation fails
        userLocation.value = { lat: 11.5564, lon: 104.9282 }; // Default to Phnom Penh
        updateHeroMap();
        fetchHeroWeather(11.5564, 104.9282);
      }
    );
  } else {
    console.warn("Geolocation is not supported by this browser.");
    // Set a default location if geolocation is not supported
    userLocation.value = { lat: 11.5564, lon: 104.9282 }; // Default to Phnom Penh
    updateHeroMap();
    fetchHeroWeather(11.5564, 104.9282);
  }
};
// Enhanced search location handler for navbar integration
const handleSearchLocationSelected = (event) => {
  const location = event.detail
  if (location && location.lat && location.lon) {
    // Drive the hero AQI card and small map off the selected location.
    userLocation.value = { lat: location.lat, lon: location.lon }
    writeCachedMapLocation(location.lat, location.lon)
    fetchHeroWeather(location.lat, location.lon)
    updateHeroMap();
  }
}
// Lifecycle hooks with enhanced search integration
onMounted(() => {
  window.addEventListener('resize', handleHeroResize);
  document.addEventListener('click', closeGraphDropdowns);

  initHeroMap();
  fetchAQIData();
  fetchPhnomPenhAQI();
  fetchFavorites();
  detectUserLocation();
  if (route.path === '/home/weather') fetchWeatherForecast();

  // Listen for navbar search selections
  window.addEventListener('location-search-selected', handleSearchLocationSelected)

  // Check for stored search location on mount (for page navigation)
  const storedLocation = localStorage.getItem('selectedSearchLocation');
  if (storedLocation) {
    try {
      const location = JSON.parse(storedLocation);
      handleSearchLocationSelected({ detail: location });
      localStorage.removeItem('selectedSearchLocation');
    } catch (e) {
      console.error('Error parsing stored search location:', e);
    }
  }

  // Periodic data refresh
  setInterval(() => {
    fetchAQIData();
    fetchPhnomPenhAQI();
    fetchFavorites();
    fetchAqiHistory();
  }, 30000);
});
watch(() => nearestStation.value?.name, (name) => {
  if (name) fetchAqiHistory();
});
watch(nearestStation, () => {
  updateHeroMap();
  heroLastUpdated.value = new Date();
}, { deep: true });
// Cleanup event listeners
onUnmounted(() => {
  window.removeEventListener('location-search-selected', handleSearchLocationSelected);
  window.removeEventListener('resize', handleHeroResize);
  document.removeEventListener('click', closeGraphDropdowns);
  if (heroMap) {
    heroMap.remove();
  }
});
</script>
<style scoped>
#hero-map {
  z-index: 0;
}
.no-scrollbar {
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* IE/Edge */
}
.no-scrollbar::-webkit-scrollbar {
  display: none; /* Chrome/Safari */
}
button {
  transition: all 0.2s ease-in-out;
}
button:hover {
  transform: translateY(-1px);
}
* {
  font-family: 'Nunito Sans', sans-serif;
}
/* The blanket reset above wins a cascade-order tie against Font Awesome's own
   .fa-solid font-family rule, blanking every icon glyph in this component. */
.fa-solid, .fas {
  font-family: 'Font Awesome 6 Free' !important;
}
</style>