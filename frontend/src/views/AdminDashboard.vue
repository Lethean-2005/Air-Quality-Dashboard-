<template>
  <div class="w-full space-y-8 text-left">
    <!-- Top Metric Cards -->
    <div v-if="dashboardLoading" class="grid grid-cols-1 md:grid-cols-5 gap-3">
      <div v-for="i in 5" :key="i" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
        <div class="flex items-center justify-between">
          <div class="min-w-0 space-y-2">
            <Skeleton class="h-3 w-16" />
            <Skeleton class="h-5 w-10" />
          </div>
          <Skeleton class="h-11 w-11 rounded-full flex-shrink-0" />
        </div>
        <div class="mt-3 pt-2.5 border-t border-gray-50">
          <Skeleton class="h-2.5 w-24" />
        </div>
      </div>
    </div>
    <div v-else class="grid grid-cols-1 md:grid-cols-5 gap-3">
      <!-- Total Locations Card -->
      <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div class="min-w-0">
            <div class="text-xs font-medium text-gray-400 truncate">{{ $t("dashboard.Total") }}</div>
            <div class="text-xl font-bold text-gray-900 mt-0.5">{{ totalLocationsComputed }}</div>
          </div>
          <img :src="aqiModerateImg" alt="" class="h-11 w-auto object-contain flex-shrink-0" />
        </div>
        <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center justify-between">
          <span class="text-[11px] text-gray-400 truncate">All monitored stations</span>
          <button @click="exportAllData" class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
            <IconDownload :size="13" />
          </button>
        </div>
      </div>

      <!-- High Pollution Locations -->
      <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div class="min-w-0">
            <div class="text-xs font-medium text-gray-400 truncate">{{ $t("dashboard.High") }}</div>
            <div class="text-xl font-bold text-gray-900 mt-0.5">{{ highPollutionCountComputed }}</div>
          </div>
          <img :src="aqiUnhealthySensitiveImg" alt="" class="h-11 w-auto object-contain flex-shrink-0" />
        </div>
        <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center justify-between">
          <span class="text-[11px] text-gray-400 truncate">AQI above 100</span>
          <button @click="exportAllHighPollutionData" class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
            <IconDownload :size="13" />
          </button>
        </div>
      </div>

      <!-- Low Pollution Locations -->
      <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div class="min-w-0">
            <div class="text-xs font-medium text-gray-400 truncate">{{ $t("dashboard.Low") }}</div>
            <div class="text-xl font-bold text-gray-900 mt-0.5">{{ lowPollutionCountComputed }}</div>
          </div>
          <img :src="aqiGoodImg" alt="" class="h-11 w-auto object-contain flex-shrink-0" />
        </div>
        <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center justify-between">
          <span class="text-[11px] text-gray-400 truncate">AQI 50 or below</span>
          <button @click="exportAllLowPollutionData" class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
            <IconDownload :size="13" />
          </button>
        </div>
      </div>

      <!-- Top High Pollution -->
      <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div class="min-w-0">
            <div class="text-xs font-medium text-gray-400 truncate">{{ $t("dashboard.Top") }}</div>
            <div class="text-xl font-bold text-gray-900 mt-0.5">{{ topHighPollutionLocation?.aqi || 0 }}</div>
          </div>
          <img :src="aqiHazardousImg" alt="" class="h-11 w-auto object-contain flex-shrink-0" />
        </div>
        <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center justify-between gap-2">
          <span class="text-[11px] text-gray-400 truncate">{{ topHighPollutionLocation?.name || "No data" }}</span>
          <button @click="exportTopHighPollutionData" class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
            <IconDownload :size="13" />
          </button>
        </div>
      </div>

      <!-- Top Low Pollution -->
      <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div class="min-w-0">
            <div class="text-xs font-medium text-gray-400 truncate">{{ $t("dashboard.top") }}</div>
            <div class="text-xl font-bold text-gray-900 mt-0.5">{{ topLowPollutionLocation?.aqi || 0 }}</div>
          </div>
          <img :src="aqiGoodImg" alt="" class="h-11 w-auto object-contain flex-shrink-0" />
        </div>
        <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center justify-between gap-2">
          <span class="text-[11px] text-gray-400 truncate">{{ topLowPollutionLocation?.name || "No data" }}</span>
          <button @click="exportTopLowPollutionData" class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
            <IconDownload :size="13" />
          </button>
        </div>
      </div>
    </div>

    <!-- Middle Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Pollution Trends -->
      <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
        <div v-if="dashboardLoading" class="mb-3 space-y-2">
          <Skeleton class="h-3.5 w-24" />
          <Skeleton class="h-2.5 w-40" />
        </div>
        <div v-else class="mb-3">
          <h3 class="text-sm font-semibold text-gray-900">
            {{ $t("dashboard.Pm10") }}
          </h3>
          <p class="text-xs text-gray-400 mt-0.5">Top 10 highest vs. lowest readings</p>
        </div>
        <div class="relative h-40">
          <Skeleton v-if="dashboardLoading" class="absolute inset-0 rounded-lg" />
          <canvas ref="trendsChartRef" class="w-full h-full"></canvas>
        </div>
        <div v-if="dashboardLoading" class="mt-3 pt-2.5 border-t border-gray-50">
          <Skeleton class="h-2.5 w-40" />
        </div>
        <div v-else class="mt-3 pt-2.5 border-t border-gray-50 flex items-center gap-1.5 text-[11px] text-gray-400">
          <IconClock :size="12" />
          <span>Live readings, updates automatically</span>
        </div>
        <div v-if="dashboardLoading" class="mt-2.5 grid grid-cols-2 gap-2">
          <Skeleton class="h-2.5 w-16" />
          <Skeleton class="h-2.5 w-16" />
        </div>
        <div v-else class="mt-2.5 grid grid-cols-2 gap-2 text-xs">
          <div class="flex items-center space-x-2">
            <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
            <span class="text-slate-500">{{ $t("dashboard.HighPm10") }}</span>
          </div>
          <div class="flex items-center space-x-2">
            <div class="w-2 h-2 bg-green-500 rounded-full"></div>
            <span class="text-slate-500">{{ $t("dashboard.LowPm10") }}</span>
          </div>
        </div>
      </div>

      <!-- Pollution Distribution -->
      <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
        <div v-if="dashboardLoading" class="mb-3 space-y-2">
          <Skeleton class="h-3.5 w-24" />
          <Skeleton class="h-2.5 w-40" />
        </div>
        <div v-else class="mb-3">
          <h3 class="text-sm font-semibold text-gray-900">
            {{ $t("dashboard.Pm25") }}
          </h3>
          <p class="text-xs text-gray-400 mt-0.5">Top 10 highest vs. lowest readings</p>
        </div>
        <div class="relative h-40">
          <Skeleton v-if="dashboardLoading" class="absolute inset-0 rounded-lg" />
          <canvas ref="distributionChartRef" class="w-full h-full"></canvas>
        </div>
        <div v-if="dashboardLoading" class="mt-3 pt-2.5 border-t border-gray-50">
          <Skeleton class="h-2.5 w-40" />
        </div>
        <div v-else class="mt-3 pt-2.5 border-t border-gray-50 flex items-center gap-1.5 text-[11px] text-gray-400">
          <IconClock :size="12" />
          <span>Live readings, updates automatically</span>
        </div>
        <div v-if="dashboardLoading" class="mt-2.5 grid grid-cols-2 gap-2">
          <Skeleton class="h-2.5 w-16" />
          <Skeleton class="h-2.5 w-16" />
        </div>
        <div v-else class="mt-2.5 grid grid-cols-2 gap-2 text-xs">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
              <div class="w-2 h-2 bg-red-500 rounded-full"></div>
              <span class="text-slate-500">{{ $t("dashboard.HighPm25") }}</span>
            </div>
          </div>
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
              <div class="w-2 h-2 bg-green-500 rounded-full"></div>
              <span class="text-slate-500">{{ $t("dashboard.LowPm25") }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Status Distribution -->
      <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
          <div v-if="dashboardLoading" class="space-y-2">
            <Skeleton class="h-3.5 w-28" />
            <Skeleton class="h-2.5 w-32" />
          </div>
          <div v-else>
            <h3 class="text-sm font-semibold text-gray-900">
              {{ $t("dashboard.status") }}
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">Station counts by AQI category</p>
          </div>
          <Skeleton v-if="dashboardLoading" class="h-8 w-20 rounded-[5px] flex-shrink-0" />
          <AppDropdown
            v-else
            v-model="selectedStatusMetric"
            :options="pollutantOptions"
            small
            light
            class="status-dropdown dropdown-spaced"
          />
        </div>
        <div class="relative h-32">
          <Skeleton v-if="dashboardLoading" class="absolute inset-0 rounded-lg" />
          <canvas ref="statusChartRef" class="w-full h-full"></canvas>
        </div>
        <div v-if="dashboardLoading" class="mt-3 pt-2.5 border-t border-gray-50">
          <Skeleton class="h-2.5 w-40" />
        </div>
        <div v-else class="mt-3 pt-2.5 border-t border-gray-50 flex items-center gap-1.5 text-[11px] text-gray-400">
          <IconClock :size="12" />
          <span>Live readings, updates automatically</span>
        </div>
        <div v-if="dashboardLoading" class="mt-2.5 grid grid-cols-3 gap-x-2 gap-y-1.5">
          <Skeleton v-for="i in 6" :key="i" class="h-2.5 w-14" />
        </div>
        <div v-else class="mt-2.5 grid grid-cols-3 gap-x-2 gap-y-1.5 text-xs">
          <div
            v-for="item in statusLegendItems"
            :key="item.label"
            class="flex items-center space-x-1.5 min-w-0"
          >
            <div class="w-2 h-2 rounded-full flex-shrink-0" :style="{ backgroundColor: item.color }"></div>
            <span class="text-slate-500 truncate">{{ item.label }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Enhanced Top Polluted Areas -->
      <div class="bg-white border border-gray-100 rounded-2xl shadow-sm overflow-hidden">
        <!-- Card Header -->
        <div class="px-4 py-3 bg-slate-50/50 border-b border-slate-100">
          <div class="flex items-center justify-between">
            <Skeleton v-if="dashboardLoading" class="h-4 w-32" />
            <h3 v-else class="text-sm font-semibold text-slate-700">
              {{ $t("dashboard.TopPollutedAreas") }}
            </h3>
            <Skeleton v-if="dashboardLoading" class="h-6 w-16 rounded-[3px]" />
            <button
              v-else
              @click="exportData"
              class="px-3 py-1 bg-white text-slate-500 border border-slate-200 rounded-[3px] text-xs font-medium hover:bg-slate-50 transition-colors"
            >
              {{ $t("dashboard.Export") }}
            </button>
          </div>
        </div>

        <!-- Card Content -->
        <div class="p-4">
          <!-- Filter Controls -->
          <div class="mb-4">
            <!-- Pollution Level Filter + Metric Selector, same row, metric pushed right -->
            <div v-if="dashboardLoading" class="flex items-center justify-between gap-2">
              <div class="flex items-center space-x-2">
                <Skeleton class="h-3.5 w-10" />
                <Skeleton class="h-7 w-24 rounded-[3px]" />
              </div>
              <div class="flex items-center space-x-2">
                <Skeleton class="h-3.5 w-12" />
                <Skeleton class="h-7 w-20 rounded-[3px]" />
              </div>
            </div>
            <div v-else class="flex items-center justify-between gap-2">
              <div class="flex items-center space-x-2">
                <span class="text-xs font-medium text-slate-500 min-w-[45px]"
                  >{{ $t("dashboard.Level") }}:</span
                >
                <div class="flex bg-slate-100 rounded-[3px] p-1">
                  <button
                    @click="pollutionFilter = 'high'"
                    :class="[
                      'px-3 py-1 text-xs font-medium rounded-sm transition-colors',
                      pollutionFilter === 'high'
                        ? 'bg-red-500 text-white'
                        : 'text-slate-500 hover:text-slate-700',
                    ]"
                  >
                    {{ $t("analyticsPage.High") }}
                  </button>
                  <button
                    @click="pollutionFilter = 'low'"
                    :class="[
                      'px-3 py-1 text-xs font-medium rounded-sm transition-colors',
                      pollutionFilter === 'low'
                        ? 'bg-green-500 text-white'
                        : 'text-slate-500 hover:text-slate-700',
                    ]"
                  >
                    {{ $t("analyticsPage.Low") }}
                  </button>
                </div>
              </div>

              <div class="flex items-center space-x-2">
                <span class="text-xs font-medium text-slate-500"
                  >{{ $t("dashboard.Metric") }}:</span
                >
                <AppDropdown
                  v-model="selectedMetric"
                  :options="pollutantOptions"
                  small
                  light
                  align="right"
                  class="metric-dropdown dropdown-spaced"
                />
              </div>
            </div>
          </div>

          <!-- Top 5 List -->
          <div v-if="dashboardLoading" class="space-y-2 mb-4">
            <div v-for="i in 5" :key="i" class="flex items-center justify-between p-2 bg-slate-50 rounded-[3px]">
              <div class="flex items-center space-x-2 min-w-0 flex-1">
                <Skeleton class="h-4 w-4" />
                <Skeleton class="w-6 h-4 rounded flex-shrink-0" />
                <div class="min-w-0 flex-1 space-y-1.5">
                  <Skeleton class="h-3 w-24" />
                  <Skeleton class="h-2.5 w-16" />
                </div>
              </div>
              <div class="flex items-center space-x-2 flex-shrink-0">
                <Skeleton class="w-16 h-1.5 rounded-full" />
                <Skeleton class="h-3 w-8" />
              </div>
            </div>
          </div>
          <div v-else class="space-y-2 mb-4">
            <div
              v-for="(location, index) in filteredTop5Locations"
              :key="index"
              class="flex items-center justify-between p-2 bg-slate-50 rounded-[3px] hover:bg-slate-100 transition-colors"
            >
              <div class="flex items-center space-x-2 min-w-0 flex-1">
                <div class="text-xs font-bold text-slate-400 w-5">
                  {{ String(index + 1).padStart(2, "0") }}
                </div>

                <!-- Country Flag -->
                <div
                  class="w-6 h-4 rounded overflow-hidden border border-slate-200 flex-shrink-0"
                >
                  <img
                    :src="getCountryFlag(location.name)"
                    :alt="`${extractCountryName(location.name)} flag`"
                    class="w-full h-full object-cover"
                    @error="handleFlagError"
                  />
                </div>

                <div class="min-w-0 flex-1">
                  <div class="text-xs font-medium text-slate-700 truncate">
                    {{ extractCityName(location.name) }}
                  </div>
                  <div class="text-xs text-slate-400 truncate">
                    {{ extractCountryName(location.name) }}
                  </div>
                </div>
              </div>

              <div class="flex items-center space-x-2 flex-shrink-0">
                <div class="w-16 bg-slate-200 rounded-full h-1.5">
                  <div
                    class="h-1.5 rounded-full transition-all duration-300"
                    :style="{
                      width: `${getProgressWidth(
                        location[selectedMetric],
                        selectedMetric
                      )}%`,
                      backgroundColor: getColor(
                        location[selectedMetric],
                        selectedMetric
                      ),
                    }"
                  ></div>
                </div>
                <div
                  class="text-xs font-bold text-slate-700 min-w-[2.5rem] text-right"
                >
                  {{ formatValue(location[selectedMetric], selectedMetric) }}
                </div>
              </div>
            </div>
          </div>

          <!-- Summary Stats -->
          <div class="pt-3 border-t border-slate-200">
            <div v-if="dashboardLoading" class="grid grid-cols-2 gap-4">
              <div class="text-center space-y-1.5">
                <Skeleton class="h-5 w-10 mx-auto" />
                <Skeleton class="h-3 w-16 mx-auto" />
              </div>
              <div class="text-center space-y-1.5">
                <Skeleton class="h-5 w-10 mx-auto" />
                <Skeleton class="h-3 w-16 mx-auto" />
              </div>
            </div>
            <div v-else class="grid grid-cols-2 gap-4 text-xs">
              <div class="text-center">
                <div
                  class="font-bold text-lg"
                  :style="{
                    color: pollutionFilter === 'high' ? '#ef4444' : '#10b981',
                  }"
                >
                  {{ filteredTop5Locations.length }}
                </div>
                <div class="text-slate-400">
                  {{ pollutionFilter === "high" ? "High" : "Low" }}
                  {{ selectedMetric.toUpperCase() }}
                </div>
              </div>
              <div class="text-center">
                <div class="font-bold text-lg text-blue-600">
                  {{
                    Math.round(
                      filteredTop5Locations.reduce(
                        (sum, loc) =>
                          sum + (parseFloat(loc[selectedMetric]) || 0),
                        0
                      ) / filteredTop5Locations.length
                    ) || 0
                  }}
                </div>
                <div class="text-slate-400">{{ $t("dashboard.Average") }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pollution Map with Selection -->
      <div class="lg:col-span-2 bg-white border border-gray-100 rounded-2xl p-1 shadow-sm">
        <div class="flex items-center justify-between mb-1">
          <Skeleton v-if="dashboardLoading" class="h-4 w-32 ml-1" />
          <h3 v-else class="text-sm font-semibold text-slate-700">
            {{ $t("dashboard.AirQualityMap") }}
          </h3>
          <Skeleton v-if="dashboardLoading" class="h-6 w-16 rounded-[3px]" />
          <button
            v-else
            class="px-2 py-0.5 bg-white text-slate-500 border border-slate-200 rounded-[3px] text-xs font-medium hover:bg-slate-50"
          >
            {{ $t("dashboard.Export") }}
          </button>
        </div>
        <div class="relative">
          <div
            id="map"
            class="h-[600px] w-full overflow-hidden relative rounded-lg bg-slate-100"
          >
            <!-- Map tiles/markers load asynchronously into #map itself (Leaflet mounts by DOM id in
                 onMounted), so this overlay sits on top rather than replacing the div, keeping the
                 mount target intact while masking the blank tile background during initial load. -->
            <Skeleton v-if="dashboardLoading" class="absolute inset-0 rounded-lg z-[1001]" />

            <!-- Dynamic Legend on left -->
            <div
              v-if="dashboardLoading"
              class="absolute bottom-4 left-4 bg-white p-4 shadow-lg rounded-lg z-[1002] max-w-[200px] space-y-2.5"
            >
              <Skeleton class="h-4 w-24" />
              <Skeleton v-for="i in 4" :key="i" class="h-3 w-28" />
            </div>
            <div
              v-else
              class="absolute bottom-4 left-4 bg-white p-4 shadow-lg rounded-lg z-[1000] max-w-[200px]"
            >
              <div class="text-sm font-semibold mb-3 text-slate-700">
                {{ legendTitle }}
              </div>
              <div class="space-y-2 text-xs">
                <div
                  v-for="item in legendItems"
                  :key="item.color"
                  class="flex items-center"
                >
                  <img
                    v-if="legendIcon(item)"
                    :src="legendIcon(item)"
                    alt=""
                    class="w-5 h-5 mr-2.5 object-contain flex-shrink-0"
                  />
                  <div
                    v-else
                    class="w-4 h-4 mr-3 rounded-sm flex-shrink-0"
                    :style="{ backgroundColor: item.color }"
                  ></div>
                  <span class="text-slate-500">{{ item.label }}</span>
                </div>
              </div>
            </div>

            <!-- Search control on top right -->
            <div class="absolute top-4 right-4 z-[1000]">
              <div class="relative">
                <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                <input
                  type="text"
                  placeholder="Search location..."
                  class="pl-9 pr-9 w-64 h-9 text-xs text-gray-200 placeholder-gray-400 bg-[#2b3138] border border-white/5 focus:outline-none focus:ring-1 focus:ring-blue-400 focus:border-blue-400 rounded-lg transition"
                  v-model="searchQuery"
                  @keyup="searchLocation"
                />
                <button
                  v-if="searchQuery"
                  @click="clearSearch"
                  class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 text-sm leading-none hover:text-gray-200 rounded-full w-6 h-6 flex items-center justify-center"
                >
                  ×
                </button>
              </div>
            </div>

            <!-- Pollutant Selector in Map -->
            <div
              class="absolute top-4 left-4 z-[1000]"
            >
              <AppDropdown
                v-model="selectedPollutant"
                :options="pollutantOptions"
                small
                light
                class="map-dropdown dropdown-spaced"
              />
            </div>

            <!-- Search Results - styled to match the navbar search dropdown -->
            <div
              v-if="searchResults.length > 0"
              class="absolute top-20 right-4 bg-[#2b3138] shadow-lg rounded-lg z-[1000] w-72 p-2"
            >
              <p class="px-2 pt-1 pb-2 text-xs font-semibold text-gray-400">States</p>

              <div class="max-h-80 overflow-y-auto">
                <div
                  v-for="(result, index) in showAllResults
                    ? searchResults
                    : searchResults.slice(0, maxVisibleResults)"
                  :key="result.name"
                  class="group flex items-center justify-between gap-3 px-2 py-2 rounded-sm cursor-pointer hover:bg-white/5 transition-colors"
                  @click="goToLocation(result)"
                >
                  <span class="text-sm text-white truncate">
                    {{ result.name }}, {{ result.country || "Unknown" }}
                  </span>
                  <span
                    class="flex-shrink-0 inline-flex items-center justify-center min-w-[2.25rem] px-1.5 py-1 rounded-sm text-xs font-medium text-white transition-transform group-hover:scale-105"
                    :style="{ backgroundColor: getColor(result.aqi, 'aqi') }"
                  >
                    {{ result.aqi || "N/A" }}
                  </span>
                </div>

                <div
                  v-if="searchResults.length > maxVisibleResults"
                  class="p-2"
                >
                  <button
                    @click="showAllResults = !showAllResults"
                    class="w-full text-center text-xs text-gray-400 hover:text-white transition-colors"
                  >
                    {{
                      showAllResults
                        ? "Show Less"
                        : `Show All (${searchResults.length})`
                    }}
                  </button>
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
import { onMounted, ref, watch, computed } from "vue";
import { useRouter } from "vue-router";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
import axios from "axios";
import { API_ROOT } from "@/services/api.js";
import Chart from "chart.js/auto";
// Chart.js labels, tick text, and tooltips all read this default — set once so every
// chart on the dashboard (line + bar) renders in the app's Nunito font, not the canvas default.
Chart.defaults.font.family = "'Nunito Sans', sans-serif";
import AppDropdown from "../components/AppDropdown.vue";
import Skeleton from "../components/Skeleton.vue";
import { getFlagUrl } from "@/utils/countryFlags";
import { IconDownload, IconClock } from "@tabler/icons-vue";
import aqiGoodImg from "@/assets/images/svg/aqi-good-level.webp";
import aqiModerateImg from "@/assets/images/svg/aqi-moderate-level.webp";
import aqiUnhealthySensitiveImg from "@/assets/images/svg/aqi-poor-level.webp";
import aqiUnhealthyImg from "@/assets/images/svg/aqi-unhealthy-level.webp";
import aqiSevereImg from "@/assets/images/svg/aqi-severe-level.webp";
import aqiHazardousImg from "@/assets/images/svg/aqi-hazardous-level.webp";

const router = useRouter();
const selectedPollutant = ref("aqi");
const searchQuery = ref("");
const aqiData = ref([]);
const dashboardLoading = ref(true);
let map = null;
let markers = [];
const markerMap = ref({});
const searchMarkers = ref([]);
const showAllResults = ref(false);
const maxVisibleResults = ref(5);

// New state for enhanced Top Polluted Areas
const pollutionFilter = ref("high"); // 'high' or 'low'
const selectedMetric = ref("aqi"); // 'aqi', 'pm25', 'pm10', 'no2', 'co', 'o3'

// New for status distribution
const selectedStatusMetric = ref("aqi");

// Chart instances
let trendsChartInstance = null;
let distributionChartInstance = null;
let statusChartInstance = null;

// Chart refs
const trendsChartRef = ref(null);
const distributionChartRef = ref(null);
const statusChartRef = ref(null);

// Computed properties for metrics
const totalLocationsComputed = computed(() => {
  return aqiData.value.length;
});

const highPollutionCountComputed = computed(() => {
  return aqiData.value.filter((location) => location.aqi > 100).length;
});

const lowPollutionCountComputed = computed(() => {
  return aqiData.value.filter((location) => location.aqi <= 50).length;
});

// Only entries with a real numeric AQI qualify — the API returns placeholders
// like null/"-"/"N/A" for stations with no current reading, and comparing those
// against numbers (or as strings) breaks the reduce's max/min tracking.
const validAqiLocations = computed(() =>
  aqiData.value.filter((location) => !isNaN(parseFloat(location.aqi)))
);

const topHighPollutionLocation = computed(() => {
  if (validAqiLocations.value.length === 0) return null;
  return validAqiLocations.value.reduce((highest, current) =>
    parseFloat(current.aqi) > parseFloat(highest.aqi) ? current : highest
  );
});

const topLowPollutionLocation = computed(() => {
  if (validAqiLocations.value.length === 0) return null;
  return validAqiLocations.value.reduce((lowest, current) =>
    parseFloat(current.aqi) < parseFloat(lowest.aqi) ? current : lowest
  );
});

// Country code mapping for flags
// Inline SVG icons
const pollutantOptions = [
  // AQI - green/yellow/red gauge
  {
    value: "aqi",
    label: "AQI",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="#FFCC00"/><path d="M12 12l4-4" stroke="#333" stroke-width="2" stroke-linecap="round"/></svg>`,
  },

  // PM2.5 - small gray particles
  {
    value: "pm25",
    label: "PM2.5",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="6" cy="12" r="1.5" fill="#888"/><circle cx="12" cy="8" r="1.5" fill="#888"/><circle cx="17" cy="14" r="2" fill="#888"/><circle cx="10" cy="16" r="1.5" fill="#888"/></svg>`,
  },

  // PM10 - bigger brownish particles
  {
    value: "pm10",
    label: "PM10",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="5" cy="10" r="2" fill="#A0522D"/><circle cx="12" cy="7" r="3" fill="#A0522D"/><circle cx="18" cy="15" r="2.5" fill="#A0522D"/><circle cx="9" cy="17" r="1.5" fill="#A0522D"/></svg>`,
  },

  // NO2 - dark gray factory
  {
    value: "no2",
    label: "NO₂",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><rect x="4" y="10" width="16" height="8" fill="#555"/><polygon points="4,10 8,4 12,10" fill="#777"/></svg>`,
  },

  // CO - molecule circles in red
  {
    value: "co",
    label: "CO",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="8" cy="12" r="4" fill="#FF4D4D"/><circle cx="17" cy="12" r="3" fill="#FF6666"/></svg>`,
  },

  // O3 - ozone in blue
  {
    value: "o3",
    label: "O₃",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="8" cy="12" r="3" fill="#3399FF"/><circle cx="15" cy="9" r="3" fill="#33CCFF"/><circle cx="15" cy="15" r="3" fill="#3399FF"/></svg>`,
  },

  // Temperature - red thermometer
  {
    value: "temperature",
    label: "Temperature",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><rect x="11" y="4" width="2" height="12" fill="#FF3300"/><circle cx="12" cy="18" r="3" fill="#FF3300"/></svg>`,
  },

  // Humidity - blue water droplet
  {
    value: "humidity",
    label: "Humidity",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2.5l5 7a8 8 0 1 1-10 0z" fill="#3399FF"/></svg>`,
  },

  // Pressure - gauge in green
  {
    value: "pressure",
    label: "Pressure",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" fill="#66CC66"/><path d="M12 12l3-4" stroke="#333" stroke-width="2" stroke-linecap="round"/></svg>`,
  },

  // Wind - light blue flowing lines
  {
    value: "wind",
    label: "Wind",
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3 12h13a3 3 0 1 0 0-6M3 18h9a3 3 0 1 1 0 6" stroke="#33CCFF" stroke-width="2" stroke-linecap="round"/></svg>`,
  },
];

// Helper functions
const extractCityName = (name) => {
  if (!name) return "Unknown";
  const parts = name.split(",");
  return parts[0].trim();
};

const extractCountryName = (name) => {
  if (!name) return "Unknown";
  const parts = name.split(",");
  if (parts.length > 1) {
    return parts[1].split("(")[0].trim();
  }
  return "Unknown";
};

// Get country flag URL
const getCountryFlag = (locationName) => {
  const country = extractCountryName(locationName);
  return (
    getFlagUrl(country) ||
    `data:image/svg+xml,${encodeURIComponent(
      `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 24" fill="#e5e7eb"><rect width="32" height="24" fill="#f3f4f6"/><text x="16" y="14" text-anchor="middle" font-family="'Nunito Sans', sans-serif" font-size="8" fill="#6b7280">?</text></svg>`
    )}`
  );
};

// Handle flag loading errors
const handleFlagError = (event) => {
  event.target.src = `data:image/svg+xml,${encodeURIComponent(
    `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 24" fill="#e5e7eb"><rect width="32" height="24" fill="#f3f4f6"/><text x="16" y="14" text-anchor="middle" font-family="'Nunito Sans', sans-serif" font-size="8" fill="#6b7280">?</text></svg>`
  )}`;
};

// Format value based on metric
const formatValue = (value, metric) => {
  if (value === null || value === undefined || isNaN(parseFloat(value)))
    return "N/A";

  const val = parseFloat(value);

  switch (metric) {
    case "co":
      return val.toFixed(1);
    case "temperature":
      return `${Math.round(val)}°C`;
    case "humidity":
      return `${Math.round(val)}%`;
    case "pressure":
      return `${Math.round(val)}`;
    case "wind":
      return `${val.toFixed(1)}`;
    default:
      return Math.round(val).toString();
  }
};

// Get progress bar width
const getProgressWidth = (value, metric) => {
  if (value === null || value === undefined || isNaN(parseFloat(value)))
    return 0;

  const val = parseFloat(value);

  switch (metric) {
    case "aqi":
    case "pm25":
    case "pm10":
      return Math.min((val / 300) * 100, 100);
    case "no2":
    case "o3":
      return Math.min((val / 200) * 100, 100);
    case "co":
      return Math.min((val / 10) * 100, 100);
    case "temperature":
      return Math.min(((val + 10) / 50) * 100, 100);
    case "humidity":
      return Math.min((val / 100) * 100, 100);
    case "pressure":
      return Math.min(((val - 950) / 100) * 100, 100);
    case "wind":
      return Math.min((val / 20) * 100, 100);
    default:
      return 0;
  }
};

// Color scale
const getColor = (value, pollutant = "aqi") => {
  const val = parseFloat(value);
  if (isNaN(val)) return "#999";

  switch (pollutant) {
    case "pm25":
    case "pm10":
    case "aqi":
      if (val <= 50) return "#00e400";
      if (val <= 100) return "#FFEB3B";
      if (val <= 150) return "#ff7e00";
      if (val <= 200) return "#ff0000";
      if (val <= 300) return "#99004c";
      return "#7e0023";
    case "no2":
    case "o3":
      if (val <= 40) return "#00e400";
      if (val <= 80) return "#FFEB3B";
      if (val <= 180) return "#ff7e00";
      if (val <= 280) return "#ff0000";
      return "#99004c";
    case "co":
      if (val <= 1) return "#00e400";
      if (val <= 2) return "#FFEB3B";
      if (val <= 10) return "#ff7e00";
      return "#ff0000";
    case "temperature":
      if (val < 0) return "#0000ff";
      if (val < 15) return "#00ffff";
      if (val < 25) return "#00e400";
      if (val < 35) return "#FFEB3B";
      return "#ff0000";
    case "humidity":
      if (val < 30) return "#ff7e00";
      if (val < 60) return "#00e400";
      return "#0000ff";
    case "pressure":
      if (val < 1000) return "#99004c";
      if (val < 1010) return "#ff0000";
      if (val < 1020) return "#00e400";
      return "#00ff00";
    case "wind":
      if (val < 5) return "#00e400";
      if (val < 10) return "#FFEB3B";
      if (val < 15) return "#ff7e00";
      return "#ff0000";
    default:
      return "#999";
  }
};

// Status text
const getStatus = (value, pollutant = "aqi") => {
  const val = parseFloat(value);
  if (isNaN(val)) return "0";

  switch (pollutant) {
    case "pm25":
    case "pm10":
    case "aqi":
      if (val <= 50) return "Good";
      if (val <= 100) return "Moderate";
      if (val <= 150) return "Poor";
      if (val <= 200) return "Unhealthy";
      if (val <= 300) return "Severe";
      return "Hazardous";
    case "no2":
    case "o3":
      if (val <= 40) return "Good";
      if (val <= 80) return "Moderate";
      if (val <= 180) return "Poor";
      if (val <= 280) return "Unhealthy";
      return "Severe";
    case "co":
      if (val <= 1) return "Good";
      if (val <= 2) return "Moderate";
      if (val <= 10) return "Unhealthy";
      return "Hazardous";
    case "temperature":
      if (val < 0) return "Freezing";
      if (val < 15) return "Cold";
      if (val < 25) return "Mild";
      if (val < 35) return "Warm";
      return "Hot";
    case "humidity":
      if (val < 30) return "Dry";
      if (val < 60) return "Comfortable";
      return "Humid";
    case "pressure":
      if (val < 1000) return "Very Low";
      if (val < 1010) return "Low";
      if (val < 1020) return "Normal";
      return "High";
    case "wind":
      if (val < 5) return "Calm";
      if (val < 10) return "Light";
      if (val < 15) return "Moderate";
      return "Strong";
    default:
      return "0";
  }
};

// Legend items
const getLegendItems = (pollutant) => {
  switch (pollutant) {
    case "pm25":
    case "pm10":
    case "aqi":
      return [
        { color: "#00e400", label: "Good (0-50)" },
        { color: "#FFEB3B", label: "Moderate (51-100)" },
        { color: "#ff7e00", label: "Poor (101-150)" },
        { color: "#ff0000", label: "Unhealthy (151-200)" },
        { color: "#99004c", label: "Severe (201-300)" },
        { color: "#7e0023", label: "Hazardous (301+)" },
      ];
    case "no2":
    case "o3":
      return [
        { color: "#00e400", label: "Good (0-40)" },
        { color: "#FFEB3B", label: "Moderate (41-80)" },
        { color: "#ff7e00", label: "Poor (81-180)" },
        { color: "#ff0000", label: "Unhealthy (181-280)" },
        { color: "#99004c", label: "Severe (281+)" },
      ];
    case "co":
      return [
        { color: "#00e400", label: "Good (0-1)" },
        { color: "#FFEB3B", label: "Moderate (1.1-2)" },
        { color: "#ff7e00", label: "Unhealthy (2.1-10)" },
        { color: "#ff0000", label: "Hazardous (10.1+)" },
      ];
    case "temperature":
      return [
        { color: "#0000ff", label: "Freezing (<0°C)" },
        { color: "#00ffff", label: "Cold (0-15°C)" },
        { color: "#00e400", label: "Mild (15-25°C)" },
        { color: "#FFEB3B", label: "Warm (25-35°C)" },
        { color: "#ff0000", label: "Hot (>35°C)" },
      ];
    case "humidity":
      return [
        { color: "#ff7e00", label: "Dry (0-30%)" },
        { color: "#00e400", label: "Comfortable (30-60%)" },
        { color: "#0000ff", label: "Humid (>60%)" },
      ];
    case "pressure":
      return [
        { color: "#99004c", label: "Very Low (<1000 hPa)" },
        { color: "#ff0000", label: "Low (1000-1010 hPa)" },
        { color: "#00e400", label: "Normal (1010-1020 hPa)" },
        { color: "#00ff00", label: "High (>1020 hPa)" },
      ];
    case "wind":
      return [
        { color: "#00e400", label: "Calm (0-5 m/s)" },
        { color: "#FFEB3B", label: "Light (5-10 m/s)" },
        { color: "#ff7e00", label: "Moderate (10-15 m/s)" },
        { color: "#ff0000", label: "Strong (>15 m/s)" },
      ];
    default:
      return [];
  }
};

const legendItems = computed(() => getLegendItems(selectedPollutant.value));
const legendTitle = computed(
  () => `${selectedPollutant.value.toUpperCase()} Levels`
);

// AQI-band legends (aqi/pm25/pm10/no2/o3/co) reuse the same illustrated level
// icons as the stat cards; other pollutants (temperature, humidity, pressure,
// wind) have their own category labels with no matching artwork, so those
// keep the plain color swatch.
const legendIconMap = {
  Good: aqiGoodImg,
  Moderate: aqiModerateImg,
  Poor: aqiUnhealthySensitiveImg,
  Unhealthy: aqiUnhealthyImg,
  Severe: aqiSevereImg,
  Hazardous: aqiHazardousImg,
};
const legendIcon = (item) => legendIconMap[item.label.split(" (")[0]] || null;

// Enhanced computed property for filtered top 5 locations
const filteredTop5Locations = computed(() => {
  const validStations = [...aqiData.value].filter((station) => {
    const value = parseFloat(station[selectedMetric.value]);
    return !isNaN(value);
  });

  let filteredStations;

  if (pollutionFilter.value === "high") {
    // Sort by highest values
    filteredStations = validStations.sort((a, b) => {
      const aVal = parseFloat(a[selectedMetric.value]) || 0;
      const bVal = parseFloat(b[selectedMetric.value]) || 0;
      return bVal - aVal;
    });
  } else {
    // Sort by lowest values
    filteredStations = validStations.sort((a, b) => {
      const aVal = parseFloat(a[selectedMetric.value]) || 0;
      const bVal = parseFloat(b[selectedMetric.value]) || 0;
      return aVal - bVal;
    });
  }

  return filteredStations.slice(0, 5);
});

// Search location with results panel
const searchResults = computed(() => {
  const searchTerm = searchQuery.value.trim().toLowerCase();
  if (!searchTerm) return [];

  return aqiData.value
    .filter(
      (station) =>
        station.name && station.name.toLowerCase().includes(searchTerm)
    )
    .slice(0, 12);
});

// Status counts
const statusCounts = computed(() => {
  const legend = getLegendItems(selectedStatusMetric.value);
  const counts = {};
  legend.forEach((item) => {
    const statusName = item.label.split(" (")[0];
    counts[statusName] = 0;
  });

  aqiData.value.forEach((loc) => {
    let value = loc[selectedStatusMetric.value];
    if (selectedStatusMetric.value === "wind") {
      value = loc.wind_speed || loc.wind;
    }
    if (value !== undefined && value !== null && !isNaN(parseFloat(value))) {
      const status = getStatus(value, selectedStatusMetric.value);
      if (counts[status] !== undefined) {
        counts[status]++;
      } else {
        counts[status] = 1;
      }
    }
  });
  return counts;
});

// Get status color
const getStatusColor = (status, pollutant) => {
  const legend = getLegendItems(pollutant);
  const item = legend.find((i) => i.label.split(" (")[0] === status);
  return item ? item.color : "#999999";
};

// Status Distribution's own legend row (color dot + short label), matching the
// Pm10/Pm2.5 cards' legend style instead of drawing icons on the chart's x-axis.
const statusLegendItems = computed(() =>
  Object.keys(statusCounts.value).map((status) => ({
    label: status,
    color: getStatusColor(status, selectedStatusMetric.value),
  }))
);

// Export data function
const exportData = () => {
  const dataToExport = filteredTop5Locations.value.map((location, index) => ({
    rank: index + 1,
    city: extractCityName(location.name),
    country: extractCountryName(location.name),
    metric: selectedMetric.value.toUpperCase(),
    value: formatValue(location[selectedMetric.value], selectedMetric.value),
    level: pollutionFilter.value,
  }));

  const csvContent = [
    ["Rank", "City", "Country", "Metric", "Value", "Level"],
    ...dataToExport.map((row) => [
      row.rank,
      row.city,
      row.country,
      row.metric,
      row.value,
      row.level,
    ]),
  ]
    .map((row) => row.join(","))
    .join("\n");

  const blob = new Blob(["﻿" + csvContent], { type: "text/csv;charset=utf-8;" });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `top-polluted-areas-${selectedMetric.value}-${pollutionFilter.value}.csv`;
  a.click();
  window.URL.revokeObjectURL(url);
};

const getAQIStatus = (aqi) => {
  if (aqi <= 50) return "Good";
  if (aqi <= 100) return "Moderate";
  if (aqi <= 150) return "Poor";
  if (aqi <= 200) return "Unhealthy";
  if (aqi <= 300) return "Severe";
  return "Hazardous";
};

const aqiLevelImages = {
  Good: aqiGoodImg,
  Moderate: aqiModerateImg,
  Poor: aqiUnhealthySensitiveImg,
  Unhealthy: aqiUnhealthyImg,
  Severe: aqiSevereImg,
  Hazardous: aqiHazardousImg,
};

const getAqiLevelImage = (aqi) => aqiLevelImages[getAQIStatus(aqi)];

// Export functions for Top High and Top Low Pollution
const exportTopHighPollutionData = () => {
  const location = topHighPollutionLocation.value;
  if (!location) return;

  const csvContent = [
    [
      "Location",
      "AQI",
      "Status",
      "PM2.5",
      "PM10",
      "NO₂",
      "CO",
      "O₃",
      "Temp",
      "Humidity",
      "Pressure",
      "Wind",
    ].join(","),
    [
      location.name,
      location.aqi,
      getAQIStatus(location.aqi),
      location.pm25 || "N/A",
      location.pm10 || "N/A",
      location.no2 || "N/A",
      location.co || "N/A",
      location.o3 || "N/A",
      location.temperature || "N/A",
      location.humidity || "N/A",
      location.pressure || "N/A",
      location.wind || "N/A",
    ].join(","),
  ].join("\n");

  const blob = new Blob(["﻿" + csvContent], { type: "text/csv;charset=utf-8;" });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `top_high_pollution_${location.name.replace(/\s+/g, "_")}_${
    new Date().toISOString().split("T")[0]
  }.csv`;
  a.click();
  window.URL.revokeObjectURL(url);
};

const exportTopLowPollutionData = () => {
  const location = topLowPollutionLocation.value;
  if (!location) return;

  const csvContent = [
    [
      "Location",
      "AQI",
      "Status",
      "PM2.5",
      "PM10",
      "NO₂",
      "CO",
      "O₃",
      "Temp",
      "Humidity",
      "Pressure",
      "Wind",
    ].join(","),
    [
      location.name,
      location.aqi,
      getAQIStatus(location.aqi),
      location.pm25 || "N/A",
      location.pm10 || "N/A",
      location.no2 || "N/A",
      location.co || "N/A",
      location.o3 || "N/A",
      location.temperature || "N/A",
      location.humidity || "N/A",
      location.pressure || "N/A",
      location.wind || "N/A",
    ].join(","),
  ].join("\n");

  const blob = new Blob(["﻿" + csvContent], { type: "text/csv;charset=utf-8;" });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `top_low_pollution_${location.name.replace(/\s+/g, "_")}_${
    new Date().toISOString().split("T")[0]
  }.csv`;
  a.click();
  window.URL.revokeObjectURL(url);
};

const exportAllHighPollutionData = () => {
  const highPollutionLocations = aqiData.value.filter(
    (location) => parseInt(location.aqi) > 100
  );

  const csvContent = [
    [
      "Location",
      "AQI",
      "Status",
      "PM2.5",
      "PM10",
      "NO₂",
      "CO",
      "O₃",
      "Temp",
      "Humidity",
      "Pressure",
      "Wind",
    ].join(","),
    ...highPollutionLocations.map((location) =>
      [
        location.name,
        location.aqi,
        getAQIStatus(location.aqi),
        location.pm25 || "N/A",
        location.pm10 || "N/A",
        location.no2 || "N/A",
        location.co || "N/A",
        location.o3 || "N/A",
        location.temperature || "N/A",
        location.humidity || "N/A",
        location.pressure || "N/A",
        location.wind || "N/A",
      ].join(",")
    ),
  ].join("\n");

  const blob = new Blob(["﻿" + csvContent], { type: "text/csv;charset=utf-8;" });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `high_pollution_locations_${
    new Date().toISOString().split("T")[0]
  }.csv`;
  a.click();
  window.URL.revokeObjectURL(url);
};

const exportAllLowPollutionData = () => {
  const lowPollutionLocations = aqiData.value.filter(
    (location) => parseInt(location.aqi) <= 50
  );

  const csvContent = [
    [
      "Location",
      "AQI",
      "Status",
      "PM2.5",
      "PM10",
      "NO₂",
      "CO",
      "O₃",
      "Temp",
      "Humidity",
      "Pressure",
      "Wind",
    ].join(","),
    ...lowPollutionLocations.map((location) =>
      [
        location.name,
        location.aqi,
        getAQIStatus(location.aqi),
        location.pm25 || "N/A",
        location.pm10 || "N/A",
        location.no2 || "N/A",
        location.co || "N/A",
        location.o3 || "N/A",
        location.temperature || "N/A",
        location.humidity || "N/A",
        location.pressure || "N/A",
        location.wind || "N/A",
      ].join(",")
    ),
  ].join("\n");

  const blob = new Blob(["﻿" + csvContent], { type: "text/csv;charset=utf-8;" });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `low_pollution_locations_${
    new Date().toISOString().split("T")[0]
  }.csv`;
  a.click();
  window.URL.revokeObjectURL(url);
};

const exportAllData = () => {
  const allData = aqiData.value.map((location) => ({
    location: location.name,
    aqi: location.aqi,
    status: getAQIStatus(location.aqi),
    pm25: location.pm25 || "N/A",
    pm10: location.pm10 || "N/A",
    no2: location.no2 || "N/A",
    co: location.co || "N/A",
    o3: location.o3 || "N/A",
    temp: location.temperature || "N/A",
    humidity: location.humidity || "N/A",
    pressure: location.pressure || "N/A",
    wind: location.wind || "N/A",
  }));

  const csvContent = [
    [
      "Location",
      "AQI",
      "Status",
      "PM2.5",
      "PM10",
      "NO₂",
      "CO",
      "O₃",
      "Temp",
      "Humidity",
      "Pressure",
      "Wind",
    ].join(","),
    ...allData.map((location) =>
      [
        location.location,
        location.aqi,
        location.status,
        location.pm25,
        location.pm10,
        location.no2,
        location.co,
        location.o3,
        location.temp,
        location.humidity,
        location.pressure,
        location.wind,
      ].join(",")
    ),
  ].join("\n");

  const blob = new Blob(["﻿" + csvContent], { type: "text/csv;charset=utf-8;" });
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = `all_pollution_data_${
    new Date().toISOString().split("T")[0]
  }.csv`;
  a.click();
  window.URL.revokeObjectURL(url);
};

const top5HighPM10 = computed(() => {
  return [...aqiData.value]
    .filter((loc) => !isNaN(parseFloat(loc.pm10)))
    .sort((a, b) => parseFloat(b.pm10) - parseFloat(a.pm10))
    .slice(0, 10);
});

const top5LowPM10 = computed(() => {
  return [...aqiData.value]
    .filter((loc) => !isNaN(parseFloat(loc.pm10)))
    .sort((a, b) => parseFloat(a.pm10) - parseFloat(b.pm10))
    .slice(0, 10);
});

const top5HighPM25 = computed(() => {
  return [...aqiData.value]
    .filter((loc) => !isNaN(parseFloat(loc.pm25)))
    .sort((a, b) => parseFloat(b.pm25) - parseFloat(a.pm25))
    .slice(0, 10);
});

const top5LowPM25 = computed(() => {
  return [...aqiData.value]
    .filter((loc) => !isNaN(parseFloat(loc.pm25)))
    .sort((a, b) => parseFloat(a.pm25) - parseFloat(b.pm25))
    .slice(0, 10);
});

// Draws a dashed vertical line through the hovered point on line charts,
// matching the "touch to see a crosshair" interaction from the user-facing pages.
const verticalLinePlugin = {
  id: "verticalLine",
  afterDraw: (chart) => {
    if (!chart.tooltip?._active?.length) return;
    const { ctx, chartArea } = chart;
    const x = chart.tooltip._active[0].element.x;

    ctx.save();
    ctx.beginPath();
    ctx.setLineDash([4, 4]);
    ctx.moveTo(x, chartArea.top);
    ctx.lineTo(x, chartArea.bottom);
    ctx.lineWidth = 1;
    ctx.strokeStyle = "#cbd5e1";
    ctx.stroke();
    ctx.restore();
  },
};

// Chart creation functions
const createTrendsChart = () => {
  if (!trendsChartRef.value) return;
  if (trendsChartInstance) trendsChartInstance.destroy();

  const labels = top5HighPM10.value.map((_, index) => `#${index + 1}`);

  const highData = top5HighPM10.value.map((loc) => parseFloat(loc.pm10));
  const lowData = top5LowPM10.value.map((loc) => parseFloat(loc.pm10));

  trendsChartInstance = new Chart(trendsChartRef.value, {
    type: "line",
    data: {
      labels,
      datasets: [
        {
          label: "High PM10",
          data: highData,
          borderColor: "#8b5cf6",
          backgroundColor: "rgba(139, 92, 246, 0.1)",
          tension: 0.4,
          fill: true,
        },
        {
          label: "Low PM10",
          data: lowData,
          borderColor: "#10b981",
          backgroundColor: "rgba(16, 185, 129, 0.1)",
          tension: 0.4,
          fill: true,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        intersect: false,
        mode: "index",
      },
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          backgroundColor: "rgba(17, 24, 39, 0.9)",
          padding: 10,
          cornerRadius: 8,
          displayColors: false,
          callbacks: {
            title: function (context) {
              const datasetIndex = context[0].datasetIndex;
              const dataIndex = context[0].dataIndex;
              let loc;
              if (datasetIndex === 0) {
                // high
                loc = top5HighPM10.value[dataIndex];
              } else if (datasetIndex === 1) {
                // low
                loc = top5LowPM10.value[dataIndex];
              }
              return loc ? loc.name : "";
            },
            label: function (context) {
              return `${context.dataset.label}: ${context.parsed.y} μg/m³`;
            },
          },
        },
      },
      scales: {
        y: {
          display: false,
        },
        x: {
          display: false,
        },
      },
    },
    plugins: [verticalLinePlugin],
  });
};

const createDistributionChart = () => {
  if (!distributionChartRef.value) return;
  if (distributionChartInstance) distributionChartInstance.destroy();

  const labels = top5HighPM25.value.map((loc) => extractCityName(loc.name));
  const highData = top5HighPM25.value.map((loc) => parseFloat(loc.pm25));
  const lowData = top5LowPM25.value.map((loc) => parseFloat(loc.pm25));

  distributionChartInstance = new Chart(distributionChartRef.value, {
    type: "line",
    data: {
      labels,
      datasets: [
        {
          label: "High PM2.5",
          data: highData,
          borderColor: "#ef4444",
          backgroundColor: "rgba(239, 68, 68, 0.1)",
          tension: 0.4,
          fill: true,
        },
        {
          label: "Low PM2.5",
          data: lowData,
          borderColor: "#10b981",
          backgroundColor: "rgba(16, 185, 129, 0.1)",
          tension: 0.4,
          fill: true,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        intersect: false,
        mode: "index",
      },
      plugins: {
        legend: {
          display: false,
        },
        tooltip: {
          backgroundColor: "rgba(17, 24, 39, 0.9)",
          padding: 10,
          cornerRadius: 8,
          displayColors: false,
          callbacks: {
            title: function (context) {
              const datasetIndex = context[0].datasetIndex;
              const dataIndex = context[0].dataIndex;
              return datasetIndex === 0
                ? top5HighPM25.value[dataIndex].name
                : top5LowPM25.value[dataIndex].name;
            },
            label: function (context) {
              return `${context.dataset.label}: ${context.parsed.y} μg/m³`;
            },
          },
        },
      },
      scales: {
        y: {
          display: false,
        },
        x: {
          display: false,
        },
      },
    },
    plugins: [verticalLinePlugin],
  });
};

const createStatusChart = () => {
  if (!statusChartRef.value) return;
  if (statusChartInstance) statusChartInstance.destroy();

  const counts = statusCounts.value;
  const labels = Object.keys(counts);
  const data = Object.values(counts);
  const colors = labels.map((status) =>
    getStatusColor(status, selectedStatusMetric.value)
  );

  statusChartInstance = new Chart(statusChartRef.value, {
    type: "bar",
    data: {
      labels,
      datasets: [
        {
          label: "Locations",
          data,
          backgroundColor: colors,
          borderRadius: 4,
          barPercentage: 0.6,
          categoryPercentage: 0.7,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false,
        },
      },
      scales: {
        y: {
          beginAtZero: true,
          grid: {
            color: "#f3f4f6",
          },
          ticks: {
            color: "#6b7280",
          },
        },
        x: {
          grid: {
            display: false,
          },
          ticks: {
            display: false,
          },
        },
      },
    },
  });
};

const fetchAQIData = async () => {
  try {
    console.log("[v0] Fetching AQI data from API...");
    const { data } = await axios.get(`${API_ROOT}/api/aqi`);
    console.log("[v0] API Response:", data);

    if (data.status === "ok" && Array.isArray(data.data)) {
      aqiData.value = data.data;
      console.log("[v0] Successfully loaded", data.data.length, "locations");
      renderMarkers();
      createTrendsChart();
      createDistributionChart();
      createStatusChart();
    } else {
      console.warn("[v0] API returned unexpected format:", data);
    }
  } catch (error) {
    console.error("[v0] Error fetching AQI data:", error);
    console.log("[v0] Using empty data - check API connection");
    aqiData.value = [];
  } finally {
    dashboardLoading.value = false;
  }
};

const fetchPhnomPenhAQI = async () => {
  try {
    const { data } = await axios.get(
      `${API_ROOT}/api/air-quality/phnom-penh`
    );
    const phnomPenhStation = {
      name: "Phnom Penh",
      lat: 11.562108,
      lon: 104.888535,
      aqi: data.AQI,
      pm25: data.PM2_5,
      pm10: data.PM10,
      no2: data.NO2,
      co: data.CO,
      o3: data.O3,
      temperature: data.Temp_C,
      humidity: data.Humidity_percent,
      pressure: data.Pressure_hPa,
      wind: data.Wind_m_s,
      wind_speed: data.Wind_m_s,
      id: data.id || 9999, // Assign a unique id
    };
    const idx = aqiData.value.findIndex((st) => st.name === "Phnom Penh");
    if (idx !== -1) {
      aqiData.value.splice(idx, 1, phnomPenhStation);
    } else {
      aqiData.value.push(phnomPenhStation);
    }
    renderMarkers();
  } catch (error) {
    console.error("Error fetching Phnom Penh AQI data:", error);
  }
};

// Render markers
const renderMarkers = () => {
  markers.forEach((marker) => marker.remove());
  markers = [];
  markerMap.value = {};

  aqiData.value.forEach((station) => {
    if (!station.lat || !station.lon) return;

    let value;
    if (selectedPollutant.value === "wind") {
      value = station.wind_speed || station.wind;
    } else {
      value = station[selectedPollutant.value];
    }

    if (
      selectedPollutant.value !== "aqi" &&
      (value === null || value === undefined)
    ) {
      return;
    }

    const color = getColor(value, selectedPollutant.value);
    const status = getStatus(value, selectedPollutant.value);
    const windValue = station.wind_speed || station.wind || "N/A";
    const renderRow = (label, val, unit = "") => {
      if (val === null || val === undefined || val === "N/A") return "";
      return `<div><strong>${label}:</strong> ${val}${unit}</div>`;
    };

    const popupContent = `
      <div style="
  font-family: 'Nunito Sans', sans-serif;
  font-weight: 700;
  font-size: 15px;
  color: #000000; /* vibrant blue */
  border-bottom: 1px solid #000000;
  padding-bottom: 2px;
  margin-bottom: 6px;
  text-align: center;
">
  ${station.name}
</div>


        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 6px; font-size: 11px; color: #000;">
          ${renderRow(selectedPollutant.value.toUpperCase(), value)}
          ${renderRow("Status", status)}
          ${renderRow("PM2.5", station.pm25)}
          ${renderRow("PM10", station.pm10)}
          ${renderRow("NO₂", station.no2)}
          ${renderRow("CO", station.co)}
          ${renderRow("O₃", station.o3)}
          ${renderRow("Temp", station.temperature, " °C")}
          ${renderRow("Humidity", station.humidity, " %")}
          ${renderRow("Pressure", station.pressure, " hPa")}
          ${renderRow("Wind", windValue, " m/s")}
        </div>
        <div style="margin-top: 8px; text-align: center;">
</div>

      </div>
    `;

    const marker = L.circleMarker([station.lat, station.lon], {
      radius: 8,
      fillColor: color,
      color: "#fff",
      weight: 2,
      opacity: 1,
      fillOpacity: 0.8,
    }).addTo(map);

    marker.bindPopup(popupContent);

    // Add SPA-friendly navigation
    marker.on("popupopen", () => {
      const btn = document.getElementById(`view-detail-${station.id}`);
      if (btn) {
        btn.addEventListener("click", () => {
          router.push({ name: "city-detail", params: { id: station.id } });
        });
      }
    });

    markers.push(marker);
    markerMap.value[station.name] = marker;
  });
};

const searchLocation = () => {
  searchMarkers.value.forEach((marker) => marker.remove());
  searchMarkers.value = [];

  if (searchResults.value.length > 0) {
    searchResults.value.forEach((result) => {
      const marker = L.marker([result.lat, result.lon], {
        icon: L.divIcon({
          html: `<div style="position: relative; z-index: 1001;">
                   <div style="background: white; border: 2px solid #3b82f6; border-radius: 2px; padding: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.3); width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                     <img src="${getAqiLevelImage(result.aqi)}" style="width: 14px; height: 14px; object-fit: contain;" />
                   </div>
                   <div style="position: absolute; top: -18px; left: 50%; transform: translateX(-50%); background: #1f2937; color: white; padding: 1px 3px; border-radius: 2px; font-size: 9px; white-space: nowrap; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                     ${result.aqi || "N/A"}
                   </div>
                 </div>`,
          className: "custom-search-marker",
          iconSize: [20, 20],
          iconAnchor: [10, 10],
        }),
      }).addTo(map);

      marker.bindPopup(`
        <div style="text-align: center; font-family: 'Nunito Sans', sans-serif;">
          <h4 style="margin: 0 0 3px 0; font-size: 13px; font-weight: 600;">${
            result.name
          }</h4>
          <div style="color: ${getColor(
            result.aqi,
            "aqi"
          )}; font-weight: 600; font-size: 12px;">AQI: ${
        result.aqi || "N/A"
      }</div>
        </div>
      `);
      searchMarkers.value.push(marker);
    });
  }
};

// Clear search
const clearSearch = () => {
  searchQuery.value = "";
  searchMarkers.value.forEach((marker) => marker.remove());
  searchMarkers.value = [];
};

// Click handler to go to location and open popup
const goToLocation = (result) => {
  clearSearch();
  map.setView([result.lat, result.lon], 10);
  const marker = markerMap.value[result.name];
  if (marker) {
    marker.openPopup();
  }
};

// Zoom controls
// Init map
const initMap = () => {
  map = L.map("map", {
    center: [20, 100],
    zoom: 4,
    zoomControl: false,
    scrollWheelZoom: true,
  });

  L.tileLayer(
    "https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png",
    {
      attribution: "&copy; OpenStreetMap & CARTO",
      subdomains: "abcd",
      maxZoom: 19,
    }
  ).addTo(map);
};

const detectUserLocation = () => {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const lat = position.coords.latitude;
        const lon = position.coords.longitude;
        map.setView([lat, lon], 5);
        L.marker([lat, lon]).addTo(map).bindPopup("You are here!").openPopup();
      },
      (error) => {
        console.warn("Geolocation error:", error.message);
      }
    );
  } else {
    console.warn("Geolocation is not supported by this browser.");
  }
};

onMounted(() => {
  initMap();
  fetchAQIData();
  fetchPhnomPenhAQI();
  detectUserLocation();
  setInterval(() => {
    fetchAQIData();
    fetchPhnomPenhAQI();
  }, 30000);
});

watch(selectedPollutant, () => {
  renderMarkers();
});

watch(selectedStatusMetric, () => {
  createStatusChart();
});
</script>

<style scoped>
#map {
  z-index: 0;
}

/* Leaflet's own base CSS sets .leaflet-container to "Helvetica Neue"/Arial —
   that governs the attribution text, default tooltips, and any popup markup
   that doesn't set its own font explicitly. Scoped selectors alone can't
   reach it since Leaflet injects this DOM at runtime, not through Vue's
   render, so :deep() is required to pierce past the scope attribute. */
:deep(.leaflet-container),
:deep(.leaflet-popup-content),
:deep(.leaflet-control-attribution) {
  font-family: 'Nunito Sans', sans-serif;
}

/* Match the Metric dropdown's size to the Level filter buttons exactly
   (24px tall, 12px text, same 12px horizontal padding). !important
   beats AppDropdown's own light+small rule, which is more specific
   (.aq-dd-wrap.light .aq-dd.small). */
.metric-dropdown :deep(.aq-dd) {
  border-radius: 5px !important;
  height: 24px !important;
  padding: 0 12px !important;
  font-size: 12px !important;
  gap: 6px !important;
}

/* Same 5px corner radius on the other dashboard AQI dropdowns (Status
   Distribution's metric picker, the map's pollutant picker), for a
   consistent look across all three cards. */
.status-dropdown :deep(.aq-dd),
.map-dropdown :deep(.aq-dd) {
  border-radius: 5px !important;
}

/* Open-menu corners match the trigger button's radius on all three
   dropdowns, instead of AppDropdown's default 8px. */
.metric-dropdown :deep(.aq-dd-menu),
.status-dropdown :deep(.aq-dd-menu),
.map-dropdown :deep(.aq-dd-menu) {
  border-radius: 5px;
}

/* Same open-menu spacing and hover-row breathing room as the Analytics
   page's dropdowns (.dropdown-spaced there), so the dropdown feel is
   consistent across admin pages. */
.dropdown-spaced :deep(.aq-dd-menu) {
  top: calc(100% + 20px);
}

.dropdown-spaced :deep(.aq-dd-item) {
  padding: 9px 10px !important;
  margin-bottom: 4px;
}

.dropdown-spaced :deep(.aq-dd-item:last-child) {
  margin-bottom: 0;
}

.custom-search-marker {
  z-index: 1001 !important;
}

button {
  transition: all 0.2s ease-in-out;
}

button:hover {
  transform: translateY(-1px);
}

/* Custom scrollbar for search results */
.max-h-96::-webkit-scrollbar {
  width: 6px;
}

.max-h-96::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.max-h-96::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.max-h-96::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Custom scrollbar for search results - dark theme */
.max-h-80::-webkit-scrollbar {
  width: 4px;
}

.max-h-80::-webkit-scrollbar-track {
  background: #374151;
  border-radius: 2px;
}

.max-h-80::-webkit-scrollbar-thumb {
  background: #6b7280;
  border-radius: 2px;
}

.max-h-80::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>