<template>
  <div>
    <div class="max-w-7xl mx-auto space-y-8">
      <!-- Top 5 Metric Cards -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
        <div v-for="i in 5" :key="i" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
          <div class="flex items-center justify-between">
            <div class="min-w-0 flex-1 space-y-2">
              <Skeleton class="h-3 w-20" />
              <Skeleton class="h-6 w-12" />
            </div>
            <Skeleton class="h-9 w-9 rounded-full flex-shrink-0" />
          </div>
          <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center justify-between">
            <Skeleton class="h-3 w-24" />
            <Skeleton class="h-3 w-3 rounded-full" />
          </div>
        </div>
      </div>
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
        <!-- Total Locations -->
        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div class="min-w-0">
              <div class="text-xs font-medium text-gray-400 truncate">{{ $t('analyticsPage.total') }}</div>
              <div class="text-xl font-bold text-gray-900 mt-0.5">{{ aqiData.length }}</div>
            </div>
            <img v-if="getAqiLevelImage(averageAQI)" :src="getAqiLevelImage(averageAQI)" alt="" class="h-9 w-9 object-contain flex-shrink-0" />
            <IconWorld v-else :size="28" class="text-blue-500 flex-shrink-0" stroke="1.75" />
          </div>
          <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center justify-between">
            <span class="text-[11px] text-gray-400 truncate">{{ $t('analyticsPage.Active') }}</span>
            <button @click="exportData('total')" class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
              <IconDownload :size="13" />
            </button>
          </div>
        </div>

        <!-- Highest Pollution -->
        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div class="min-w-0">
              <div class="text-xs font-medium text-gray-400 truncate">{{ $t('analyticsPage.Highes') }}</div>
              <div class="text-xl font-bold text-gray-900 mt-0.5">{{ highestPollution?.aqi || 'N/A' }}</div>
            </div>
            <img v-if="getAqiLevelImage(highestPollution?.aqi)" :src="getAqiLevelImage(highestPollution?.aqi)" alt="" class="h-9 w-9 object-contain flex-shrink-0" />
            <IconTrendingUp v-else :size="28" class="text-red-500 flex-shrink-0" stroke="1.75" />
          </div>
          <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center justify-between gap-2">
            <span class="text-[11px] text-gray-400 truncate">{{ highestPollution?.name || 'No data' }}</span>
            <button @click="exportData('highest')" class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
              <IconDownload :size="13" />
            </button>
          </div>
        </div>

        <!-- Lowest Pollution -->
        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div class="min-w-0">
              <div class="text-xs font-medium text-gray-400 truncate">{{ $t('analyticsPage.Lowest') }}</div>
              <div class="text-xl font-bold text-gray-900 mt-0.5">{{ lowestPollution?.aqi || 'N/A' }}</div>
            </div>
            <img v-if="getAqiLevelImage(lowestPollution?.aqi)" :src="getAqiLevelImage(lowestPollution?.aqi)" alt="" class="h-9 w-9 object-contain flex-shrink-0" />
            <IconTrendingDown v-else :size="28" class="text-emerald-500 flex-shrink-0" stroke="1.75" />
          </div>
          <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center justify-between gap-2">
            <span class="text-[11px] text-gray-400 truncate">{{ lowestPollution?.name || 'No data' }}</span>
            <button @click="exportData('lowest')" class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
              <IconDownload :size="13" />
            </button>
          </div>
        </div>

        <!-- Average AQI -->
        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div class="min-w-0">
              <div class="text-xs font-medium text-gray-400 truncate">{{ $t('analyticsPage.Average') }}</div>
              <div class="text-xl font-bold text-gray-900 mt-0.5">{{ averageAQI }}</div>
            </div>
            <img v-if="getAqiLevelImage(averageAQI)" :src="getAqiLevelImage(averageAQI)" alt="" class="h-9 w-9 object-contain flex-shrink-0" />
            <IconGauge v-else :size="28" class="text-indigo-500 flex-shrink-0" stroke="1.75" />
          </div>
          <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center justify-between">
            <span class="text-[11px] text-gray-400 truncate">{{ $t('analyticsPage.Global') }}</span>
            <button @click="exportData('average')" class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
              <IconDownload :size="13" />
            </button>
          </div>
        </div>

        <!-- Your Location -->
        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div class="min-w-0">
              <div class="text-xs font-medium text-gray-400 truncate">{{ $t('analyticsPage.YourLocation') }}</div>
              <div class="text-xl font-bold text-gray-900 mt-0.5">
                {{ nearestLocation?.aqi ?? (locationStatus === 'locating' ? '…' : 'N/A') }}
              </div>
            </div>
            <IconMapPin :size="28" class="text-orange-500 flex-shrink-0" stroke="1.75" />
          </div>
          <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center justify-between gap-2">
            <span class="text-[11px] text-gray-400 truncate">
              <template v-if="locationStatus === 'denied'">{{ $t('analyticsPage.LocationDenied') }}</template>
              <template v-else-if="locationStatus === 'unsupported'">{{ $t('analyticsPage.LocationUnsupported') }}</template>
              <template v-else-if="locationStatus === 'locating'">{{ $t('analyticsPage.Locating') }}</template>
              <template v-else-if="nearestLocation">{{ nearestLocation.name }}</template>
              <template v-else>{{ $t('analyticsPage.WaitingForLocation') }}</template>
            </span>
            <button @click="locateUser" :title="$t('analyticsPage.Relocate')" class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
              <IconCurrentLocation :size="13" />
            </button>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- AQI Trend Chart -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-8">
          <div class="flex items-center justify-between mb-6">
            <div v-if="loading" class="space-y-2">
              <Skeleton class="h-5 w-32" />
              <Skeleton class="h-3.5 w-44" />
            </div>
            <div v-else>
              <h3 class="text-xl font-bold text-slate-900 mb-1"> {{ $t('analyticsPage.Pollution') }}</h3>
              <p class="text-slate-600 text-sm"> {{ $t('analyticsPage.top') }}</p>
            </div>
            <div class="flex items-center space-x-4">
              <Skeleton v-if="loading" class="h-9 w-28 rounded-lg" />
              <AppDropdown
                v-else
                v-model="chartMetric"
                :options="chartMetricOptions"
                :lead-icon="IconChartBar"
                light
                class="dropdown-spaced"
                @update:model-value="updateChart"
              />
              <div v-if="loading" class="flex items-center space-x-3">
                <Skeleton class="h-4 w-16" />
                <Skeleton class="h-4 w-16" />
              </div>
              <div v-else class="flex items-center space-x-3 text-xs">
                <div class="flex items-center space-x-1">
                  <div class="w-2 h-2 bg-red-500 rounded-full"></div>
                  <span class="text-slate-600 font-medium">{{ $t('analyticsPage.High') }}: {{ chartHighValue }}</span>
                </div>
                <div class="flex items-center space-x-1">
                  <div class="w-2 h-2 bg-emerald-500 rounded-full"></div>
                  <span class="text-slate-600 font-medium">{{ $t('analyticsPage.Low') }}: {{ chartLowValue }}</span>
                </div>
              </div>
            </div>
          </div>
          <div class="h-80 relative">
            <canvas ref="chartCanvas" class="w-full h-full"></canvas>
            <Skeleton v-if="loading" class="absolute inset-0 rounded-lg" />
          </div>
        </div>

        <!-- Top 5 Locations by Pollution -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
          <div class="flex items-center justify-between mb-4">
            <div v-if="loading" class="space-y-2">
              <Skeleton class="h-4.5 w-28" />
              <Skeleton class="h-3 w-24" />
            </div>
            <div v-else>
              <h3 class="text-lg font-bold text-slate-900 mb-1"> {{ $t('analyticsPage.TopPollution') }}</h3>
              <p class="text-slate-600 text-xs"> {{ $t('analyticsPage.UpdatesEvery30s') }}</p>
            </div>
            <div v-if="loading" class="flex space-x-3">
              <Skeleton class="h-9 w-24 rounded-lg" />
              <Skeleton class="h-9 w-9 rounded-lg" />
            </div>
            <div v-else class="flex space-x-3">
              <AppDropdown
                v-model="selectedMetric"
                :options="metricOptions"
                :lead-icon="IconWind"
                light
                class="dropdown-spaced"
              />
              <AppDropdown
                v-model="pollutionType"
                :options="highLowOptions"
                :lead-icon="IconArrowsSort"
                small
                light
                class="dropdown-spaced"
                @update:model-value="updatePollutionList"
              />
            </div>
          </div>
          <div class="space-y-3">
            <template v-if="loading">
              <div v-for="i in 5" :key="i" class="bg-slate-50 rounded-lg p-3 border border-slate-100">
                <div class="flex items-start justify-between">
                  <div class="flex items-start space-x-3">
                    <Skeleton class="w-8 h-5 rounded flex-shrink-0" />
                    <div class="flex-1 min-w-0">
                      <Skeleton class="h-4 w-24" />
                    </div>
                  </div>
                  <div class="text-right flex-shrink-0 ml-3 space-y-2">
                    <Skeleton class="h-4 w-10 ml-auto" />
                    <Skeleton class="w-16 h-2 rounded-full" />
                    <Skeleton class="h-3 w-8 ml-auto" />
                  </div>
                </div>
              </div>
            </template>
            <template v-else>
              <div v-for="(location, index) in pollutionLocations" :key="location.id" class="bg-slate-50 rounded-lg p-3 border border-slate-100 hover:shadow-md transition-all duration-200">
                <div class="flex items-start justify-between">
                  <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                      <img v-if="location.flag" :src="location.flag" :alt="location.name" class="w-8 h-5 object-cover rounded shadow-sm">
                      <div class="w-8 h-5 bg-gradient-to-br from-slate-200 to-slate-300 rounded" v-else></div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-sm font-bold text-slate-900 truncate">{{ truncateName(location.name) }}</p>
                    </div>
                  </div>
                  <div class="text-right flex-shrink-0 ml-3">
                    <div class="text-base font-bold text-slate-900">{{ formatMetricValue(getMetricValue(location)) }}</div>
                    <div class="w-16 bg-slate-200 rounded-full h-2 mt-2">
                      <div
                        class="h-2 rounded-full transition-all duration-500"
                        :class="getPollutionColor(getMetricValue(location))"
                        :style="{ width: `${Math.min(100, (getMetricValue(location) / getMaxMetricValue()) * 100)}%` }"
                      ></div>
                    </div>
                    <div class="text-sm text-slate-500 mt-2 font-medium">
                      {{ Math.round((getMetricValue(location) / getMaxMetricValue()) * 100) }}%
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>

      <!-- Additional Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- AQI Distribution -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-slate-900">{{ $t('analyticsPage.AQIDistribution') }}</h3>
            <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
          </div>
          <div class="grid grid-cols-2 gap-6">
            <template v-if="loading">
              <div v-for="i in 6" :key="i" class="bg-slate-50 rounded-lg p-4 border border-slate-100">
                <div class="flex items-center space-x-3">
                  <Skeleton class="w-4 h-4 rounded-full" />
                  <Skeleton class="h-4 w-20" />
                </div>
                <Skeleton class="h-6 w-10 mt-3" />
                <Skeleton class="w-full h-2.5 rounded-full mt-3" />
              </div>
            </template>
            <template v-else>
              <div v-for="category in aqiCategories" :key="category.name" class="bg-slate-50 rounded-lg p-4 border border-slate-100 hover:shadow-md transition-all duration-200">
                <div class="flex items-center space-x-3">
                  <div class="w-4 h-4 rounded-full" :style="{ backgroundColor: category.color }"></div>
                  <span class="text-sm font-medium text-slate-700">{{ category.name }}</span>
                </div>
                <div class="mt-3 text-xl font-bold text-slate-900">{{ category.count }}</div>
                <div class="w-full bg-slate-200 h-2.5 rounded-full mt-3">
                  <div class="h-2.5 rounded-full transition-all duration-500" :style="{ width: `${(category.count / aqiData.length * 100) || 0}%`, backgroundColor: category.color }"></div>
                </div>
              </div>
            </template>
          </div>
        </div>

        <!-- Environmental Metrics -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-slate-900">{{ $t('analyticsPage.EnvironmentalMetrics') }}</h3>
            <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
          </div>
          <div class="grid grid-cols-1 gap-3">
            <template v-if="loading">
              <div v-for="i in 4" :key="i" class="flex items-center justify-between p-2 bg-slate-50 rounded-lg border border-slate-100">
                <div class="flex items-center space-x-2">
                  <Skeleton class="w-3 h-3 rounded-full" />
                  <Skeleton class="h-3 w-16" />
                </div>
                <Skeleton class="h-4 w-12" />
              </div>
            </template>
            <template v-else>
              <div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg border border-slate-100 hover:shadow-md transition-all duration-200">
                <div class="flex items-center space-x-2">
                  <div class="w-3 h-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full"></div>
                  <span class="text-xs font-medium text-slate-700">{{ $t('analyticsPage.Temperature') }}</span>
                </div>
                <span class="text-base font-bold text-slate-900">{{ averageTemperature }} °C</span>
              </div>
              <div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg border border-slate-100 hover:shadow-md transition-all duration-200">
                <div class="flex items-center space-x-2">
                  <div class="w-3 h-3 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-full"></div>
                  <span class="text-xs font-medium text-slate-700">{{ $t('analyticsPage.Humidity') }}</span>
                </div>
                <span class="text-base font-bold text-slate-900">{{ averageHumidity }} %</span>
              </div>
              <div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg border border-slate-100 hover:shadow-md transition-all duration-200">
                <div class="flex items-center space-x-2">
                  <div class="w-3 h-3 bg-gradient-to-r from-orange-500 to-orange-600 rounded-full"></div>
                  <span class="text-xs font-medium text-slate-700">{{ $t('analyticsPage.WindSpeed') }}</span>
                </div>
                <span class="text-base font-bold text-slate-900">{{ averageWindSpeed }} m/s</span>
              </div>
              <div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg border border-slate-100 hover:shadow-md transition-all duration-200">
                <div class="flex items-center space-x-2">
                  <div class="w-3 h-3 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-full"></div>
                  <span class="text-xs font-medium text-slate-700">{{ $t('analyticsPage.Pressure') }}</span>
                </div>
                <span class="text-base font-bold text-slate-900">{{ averagePressure }} hPa</span>
              </div>
            </template>
          </div>
        </div>

        <!-- Average Pollution Levels -->
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-slate-900">{{ $t('analyticsPage.AveragePollutionLevels') }}</h3>
            <div class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse"></div>
          </div>
          <div class="space-y-3 text-sm">
            <template v-if="loading">
              <div v-for="i in 9" :key="i" class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-100">
                <div class="flex items-center space-x-3">
                  <Skeleton class="w-3 h-3 rounded-full" />
                  <Skeleton class="h-3 w-14" />
                </div>
                <Skeleton class="h-4 w-16" />
              </div>
            </template>
            <template v-else>
              <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-100">
                <div class="flex items-center space-x-3">
                  <div class="w-3 h-3 bg-gradient-to-r from-orange-500 to-orange-600 rounded-full"></div>
                  <span class="text-slate-700 font-medium">PM2.5</span>
                </div>
                <span class="font-bold text-slate-900">{{ averagePM25 }} µg/m³</span>
              </div>
              <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-100">
                <div class="flex items-center space-x-3">
                  <div class="w-3 h-3 bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-full"></div>
                  <span class="text-slate-700 font-medium">PM10</span>
                </div>
                <span class="font-bold text-slate-900">{{ averagePM10 }} µg/m³</span>
              </div>
              <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-100">
                <div class="flex items-center space-x-3">
                  <div class="w-3 h-3 bg-gradient-to-r from-purple-500 to-purple-600 rounded-full"></div>
                  <span class="text-slate-700 font-medium">NO2</span>
                </div>
                <span class="font-bold text-slate-900">{{ averageNO2 }} ppb</span>
              </div>
              <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-100">
                <div class="flex items-center space-x-3">
                  <div class="w-3 h-3 bg-gradient-to-r from-gray-500 to-gray-600 rounded-full"></div>
                  <span class="text-slate-700 font-medium">CO</span>
                </div>
                <span class="font-bold text-slate-900">{{ averageCO }} ppm</span>
              </div>
              <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg border border-slate-100">
                <div class="flex items-center space-x-3">
                  <div class="w-3 h-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full"></div>
                  <span class="text-slate-700 font-medium">O3</span>
                </div>
                <span class="font-bold text-slate-900">{{ averageO3 }} ppb</span>
              </div>
              <div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg border border-slate-100">
                <div class="flex items-center space-x-2">
                  <div class="w-2 h-2 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full"></div>
                  <span class="text-slate-700 font-medium">{{ $t('analyticsPage.Temperature') }}</span>
                </div>
                <span class="font-bold text-slate-900">{{ averageTemperature }} °C</span>
              </div>
              <div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg border border-slate-100">
                <div class="flex items-center space-x-2">
                  <div class="w-2 h-2 bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-full"></div>
                  <span class="text-slate-700 font-medium">{{ $t('analyticsPage.Humidity') }}</span>
                </div>
                <span class="font-bold text-slate-900">{{ averageHumidity }} %</span>
              </div>
              <div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg border border-slate-100">
                <div class="flex items-center space-x-2">
                  <div class="w-2 h-2 bg-gradient-to-r from-orange-500 to-orange-600 rounded-full"></div>
                  <span class="text-slate-700 font-medium">{{ $t('analyticsPage.WindSpeed') }}</span>
                </div>
                <span class="font-bold text-slate-900">{{ averageWindSpeed }} m/s</span>
              </div>
              <div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg border border-slate-100">
                <div class="flex items-center space-x-2">
                  <div class="w-2 h-2 bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-full"></div>
                  <span class="text-slate-700 font-medium">{{ $t('analyticsPage.Pressure') }}</span>
                </div>
                <span class="font-bold text-slate-900">{{ averagePressure }} hPa</span>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from 'vue'
import axios from 'axios'
import { API_ROOT } from '@/services/api.js'
import Chart from 'chart.js/auto'
import { useI18n } from 'vue-i18n'
import AppDropdown from '../components/AppDropdown.vue'
import Skeleton from '../components/Skeleton.vue'
import {
  IconChartBar,
  IconWind,
  IconArrowsSort,
  IconDownload,
  IconWorld,
  IconTrendingUp,
  IconTrendingDown,
  IconGauge,
  IconMapPin,
  IconCurrentLocation,
} from '@tabler/icons-vue'
import aqiGoodImg from '@/assets/images/svg/aqi-good-level.webp'
import aqiModerateImg from '@/assets/images/svg/aqi-moderate-level.webp'
import aqiPoorImg from '@/assets/images/svg/aqi-poor-level.webp'
import aqiUnhealthyImg from '@/assets/images/svg/aqi-unhealthy-level.webp'
import aqiSevereImg from '@/assets/images/svg/aqi-severe-level.webp'
import aqiHazardousImg from '@/assets/images/svg/aqi-hazardous-level.webp'

const { t } = useI18n()

// AQI level -> icon, shared by the Average and Total cards (Total has no AQI
// of its own, so it mirrors the average's level like the admin dashboard does).
const aqiLevelImages = {
  Good: aqiGoodImg,
  Moderate: aqiModerateImg,
  Poor: aqiPoorImg,
  Unhealthy: aqiUnhealthyImg,
  Severe: aqiSevereImg,
  Hazardous: aqiHazardousImg,
}

const getAQIStatus = (aqi) => {
  const val = parseFloat(aqi)
  if (isNaN(val)) return null
  if (val <= 50) return 'Good'
  if (val <= 100) return 'Moderate'
  if (val <= 150) return 'Poor'
  if (val <= 200) return 'Unhealthy'
  if (val <= 300) return 'Severe'
  return 'Hazardous'
}

const getAqiLevelImage = (aqi) => {
  const status = getAQIStatus(aqi)
  return status ? aqiLevelImages[status] : null
}

const metricOptions = computed(() => [
  { value: 'aqi', label: 'AQI' },
  { value: 'pm25', label: 'PM2.5' },
  { value: 'pm10', label: 'PM10' },
  { value: 'no2', label: 'NO2' },
  { value: 'co', label: 'CO' },
  { value: 'o3', label: 'O3' },
  { value: 'temperature', label: t('analyticsPage.Temperature') },
  { value: 'humidity', label: t('analyticsPage.Humidity') },
  { value: 'pressure', label: t('analyticsPage.Pressure') },
  { value: 'wind_speed', label: t('analyticsPage.WindSpeed') },
])

const chartMetricOptions = computed(() => [
  { value: 'aqi', label: 'AQI' },
  { value: 'pm25', label: 'PM2.5' },
  { value: 'pm10', label: 'PM10' },
  { value: 'temperature', label: t('analyticsPage.Temperature') },
  { value: 'no2', label: 'NO2' },
  { value: 'co', label: 'CO' },
  { value: 'o3', label: 'O3' },
  { value: 'humidity', label: t('analyticsPage.Humidity') },
  { value: 'pressure', label: t('analyticsPage.Pressure') },
  { value: 'wind_speed', label: t('analyticsPage.WindSpeed') },
])

const highLowOptions = computed(() => [
  { value: 'high', label: t('analyticsPage.High') },
  { value: 'low', label: t('analyticsPage.Low') },
])

// Reactive data
const loading = ref(true)
const aqiData = ref([])
const selectedMetric = ref('aqi')
const chartMetric = ref('aqi')
const pollutionType = ref('high')
const chartCanvas = ref(null)
const pieCanvas = ref(null)
let chart = null
let pieChart = null
const highSortedRef = ref([])
const lowSortedRef = ref([])

// Your Location
const userCoords = ref(null)
const locationStatus = ref('idle') // idle | locating | granted | denied | unsupported

const haversineDistanceKm = (lat1, lon1, lat2, lon2) => {
  const toRad = (deg) => (deg * Math.PI) / 180
  const R = 6371
  const dLat = toRad(lat2 - lat1)
  const dLon = toRad(lon2 - lon1)
  const a =
    Math.sin(dLat / 2) ** 2 +
    Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) ** 2
  return 2 * R * Math.asin(Math.sqrt(a))
}

const nearestLocation = computed(() => {
  if (!userCoords.value || !aqiData.value.length) return null
  let closest = null
  let closestDistance = Infinity
  for (const item of aqiData.value) {
    const lat = parseFloat(item.lat)
    const lon = parseFloat(item.lon)
    if (isNaN(lat) || isNaN(lon)) continue
    const distance = haversineDistanceKm(userCoords.value.lat, userCoords.value.lon, lat, lon)
    if (distance < closestDistance) {
      closestDistance = distance
      closest = item
    }
  }
  return closest
})

const locateUser = () => {
  if (!navigator.geolocation) {
    locationStatus.value = 'unsupported'
    return
  }
  locationStatus.value = 'locating'
  navigator.geolocation.getCurrentPosition(
    (position) => {
      userCoords.value = {
        lat: position.coords.latitude,
        lon: position.coords.longitude,
      }
      locationStatus.value = 'granted'
    },
    () => {
      locationStatus.value = 'denied'
    },
    { enableHighAccuracy: true, timeout: 10000 }
  )
}

// Computed properties for metrics
const highestPollution = computed(() => {
  if (!aqiData.value.length) return null
  return aqiData.value.reduce((max, current) => {
    const currentAqi = parseInt(current.aqi) || 0
    const maxAqi = parseInt(max.aqi) || 0
    return currentAqi > maxAqi ? current : max
  })
})

const lowestPollution = computed(() => {
  if (!aqiData.value.length) return null
  const validData = aqiData.value.filter(item => parseInt(item.aqi) > 0)
  if (!validData.length) return null
  return validData.reduce((min, current) => {
    const currentAqi = parseInt(current.aqi) || Infinity
    const minAqi = parseInt(min.aqi) || Infinity
    return currentAqi < minAqi ? current : min
  })
})

const averageAQI = computed(() => {
  if (!aqiData.value.length) return 'N/A'
  const validData = aqiData.value.filter(item => parseInt(item.aqi) > 0)
  if (!validData.length) return 'N/A'
  const sum = validData.reduce((acc, item) => acc + (parseInt(item.aqi) || 0), 0)
  return Math.round(sum / validData.length)
})

const pollutionLocations = computed(() => {
  if (!aqiData.value.length) return []
  const filteredData = aqiData.value.filter(item => parseInt(item.aqi) > 0)
  return pollutionType.value === 'high'
    ? [...filteredData].sort((a, b) => (parseInt(b[selectedMetric.value]) || 0) - (parseInt(a[selectedMetric.value]) || 0)).slice(0, 5)
    : [...filteredData].sort((a, b) => (parseInt(a[selectedMetric.value]) || 0) - (parseInt(b[selectedMetric.value]) || 0)).slice(0, 5)
})

const aqiCategories = computed(() => {
  const categories = [
    { name: 'Good', range: [0, 50], color: '#10b981', count: 0 },
    { name: 'Moderate', range: [51, 100], color: '#f59e0b', count: 0 },
    { name: 'Unhealthy SG', range: [101, 150], color: '#f97316', count: 0 },
    { name: 'Unhealthy', range: [151, 200], color: '#ef4444', count: 0 },
    { name: 'Very Unhealthy', range: [201, 300], color: '#dc2626', count: 0 },
    { name: 'Hazardous', range: [301, Infinity], color: '#991b1b', count: 0 }
  ]
  
  aqiData.value.forEach(item => {
    const aqi = parseInt(item.aqi) || 0
    const category = categories.find(cat => aqi >= cat.range[0] && aqi <= cat.range[1])
    if (category) category.count++
  })
  
  return categories
})

const averageTemperature = computed(() => {
  if (!aqiData.value.length) return 'N/A'
  const validData = aqiData.value.filter(item => item.temperature && item.temperature !== 'N/A')
  if (!validData.length) return 'N/A'
  const sum = validData.reduce((acc, item) => acc + (parseFloat(item.temperature) || 0), 0)
  return Math.round(sum / validData.length)
})

const averageHumidity = computed(() => {
  if (!aqiData.value.length) return 'N/A'
  const validData = aqiData.value.filter(item => item.humidity && item.humidity !== 'N/A')
  if (!validData.length) return 'N/A'
  const sum = validData.reduce((acc, item) => acc + (parseFloat(item.humidity) || 0), 0)
  return Math.round(sum / validData.length)
})

const averageWindSpeed = computed(() => {
  if (!aqiData.value.length) return 'N/A'
  const validData = aqiData.value.filter(item => item.wind_speed && item.wind_speed !== 'N/A')
  if (!validData.length) return 'N/A'
  const sum = validData.reduce((acc, item) => acc + (parseFloat(item.wind_speed) || 0), 0)
  return Math.round(sum / validData.length * 10) / 10
})

const averagePressure = computed(() => {
  if (!aqiData.value.length) return 'N/A'
  const validData = aqiData.value.filter(item => item.pressure && item.pressure !== 'N/A')
  if (!validData.length) return 'N/A'
  const sum = validData.reduce((acc, item) => acc + (parseFloat(item.pressure) || 0), 0)
  return Math.round(sum / validData.length)
})

const averagePM25 = computed(() => {
  if (!aqiData.value.length) return 'N/A'
  const validData = aqiData.value.filter(item => !isNaN(parseFloat(item.pm25)))
  if (!validData.length) return 'N/A'
  const sum = validData.reduce((acc, item) => acc + parseFloat(item.pm25), 0)
  return (sum / validData.length).toFixed(1)
})

const averagePM10 = computed(() => {
  if (!aqiData.value.length) return 'N/A'
  const validData = aqiData.value.filter(item => !isNaN(parseFloat(item.pm10)))
  if (!validData.length) return 'N/A'
  const sum = validData.reduce((acc, item) => acc + parseFloat(item.pm10), 0)
  return (sum / validData.length).toFixed(1)
})

const averageNO2 = computed(() => {
  if (!aqiData.value.length) return 'N/A'
  const validData = aqiData.value.filter(item => !isNaN(parseFloat(item.no2)))
  if (!validData.length) return 'N/A'
  const sum = validData.reduce((acc, item) => acc + parseFloat(item.no2), 0)
  return (sum / validData.length).toFixed(1)
})

const averageCO = computed(() => {
  if (!aqiData.value.length) return 'N/A'
  const validData = aqiData.value.filter(item => !isNaN(parseFloat(item.co)))
  if (!validData.length) return 'N/A'
  const sum = validData.reduce((acc, item) => acc + parseFloat(item.co), 0)
  return (sum / validData.length).toFixed(1)
})

const averageO3 = computed(() => {
  if (!aqiData.value.length) return 'N/A'
  const validData = aqiData.value.filter(item => !isNaN(parseFloat(item.o3)))
  if (!validData.length) return 'N/A'
  const sum = validData.reduce((acc, item) => acc + parseFloat(item.o3), 0)
  return (sum / validData.length).toFixed(1)
})

const dataQualityPercentage = computed(() => {
  if (!aqiData.value.length) return 0
  const completeData = aqiData.value.filter(item => 
    item.aqi && item.aqi !== 'N/A' && 
    item.temperature && item.temperature !== 'N/A' &&
    item.humidity && item.humidity !== 'N/A'
  )
  return Math.round((completeData.length / aqiData.value.length) * 100)
})

const lastUpdated = computed(() => {
  return new Date().toLocaleString()
})

const chartHighValue = computed(() => {
  if (!aqiData.value.length) return 'N/A'
  const values = aqiData.value
    .map(item => parseFloat(item[chartMetric.value]) || 0)
    .filter(val => val > 0)
  return values.length ? Math.max(...values).toFixed(3).replace(/\.?0+$/, '') : 'N/A'
})

const chartLowValue = computed(() => {
  if (!aqiData.value.length) return 'N/A'
  const values = aqiData.value
    .map(item => parseFloat(item[chartMetric.value]) || 0)
    .filter(val => val > 0)
  return values.length ? Math.min(...values).toFixed(3).replace(/\.?0+$/, '') : 'N/A'
})

const goodAqiCount = computed(() => {
  return aqiData.value.filter(item => parseInt(item.aqi) <= 50).length
})

const badAqiCount = computed(() => {
  return aqiData.value.filter(item => parseInt(item.aqi) > 50).length
})

const goodAqiPercentage = computed(() => {
  if (!aqiData.value.length) return 0
  return Math.round((goodAqiCount.value / aqiData.value.length) * 100)
})

const temperaturePercentage = computed(() => 35)
const humidityPercentage = computed(() => 40)
const windPercentage = computed(() => 25)

const dataQualityMetrics = computed(() => ({
  completeness: '95%',
  accuracy: '88%',
  freshness: '92%',
  coverage: '85%'
}))

const getMetricValue = (location) => {
  const value = location[selectedMetric.value]
  return value && value !== 'N/A' ? parseFloat(value) || 0 : 0
}

const formatMetricValue = (value) => {
  return Number.isFinite(value) ? value.toFixed(3).replace(/\.?0+$/, '') : value
}

const truncateName = (name) => {
  if (name.length > 20) {
    return name.length > 23 ? `${name.slice(0, 20)}...` : name
  }
  return name
}

const getMaxMetricValue = () => {
  if (!aqiData.value.length) return 100
  const values = aqiData.value.map(item => getMetricValue(item)).filter(val => val > 0)
  return Math.max(...values, 100)
}

const getPollutionColor = (value) => {
  if (selectedMetric.value === 'aqi') {
    if (value <= 50) return 'bg-green-500'
    if (value <= 100) return 'bg-yellow-500'
    if (value <= 150) return 'bg-orange-500'
    if (value <= 200) return 'bg-red-500'
    return 'bg-red-700'
  }
  return 'bg-blue-500'
}

const updatePollutionList = () => {
  // Trigger re-computation of pollutionLocations when pollutionType changes
}

const fetchAQIData = async () => {
  try {
    const { data } = await axios.get(`${API_ROOT}/api/aqi`)
    let cities = Array.isArray(data.data) ? data.data : []

    try {
      const ppRes = await axios.get(`${API_ROOT}/api/air-quality/phnom-penh`)
      const pp = ppRes.data || {}
      const phnomPenh = {
        id: 9999,
        name: 'Phnom Penh',
        lat: 11.562108,
        lon: 104.888535,
        aqi: pp.AQI ?? 'N/A',
        pm25: pp.PM2_5 ?? 'N/A',
        pm10: pp.PM10 ?? 'N/A',
        no2: pp.NO2 ?? 'N/A',
        co: pp.CO ?? 'N/A',
        o3: pp.O3 ?? 'N/A',
        temperature: pp.Temp_C ?? 'N/A',
        humidity: pp.Humidity_percent ?? 'N/A',
        pressure: pp.Pressure_hPa ?? 'N/A',
        wind_speed: pp.Wind_m_s ?? 'N/A',
        flag: 'https://flagcdn.com/w160/kh.png',
      }
      cities = cities.filter(c => c.name !== 'Phnom Penh')
      cities.push(phnomPenh)
    } catch (err) {
      console.error('Failed to fetch Phnom Penh AQI:', err)
    }

    aqiData.value = cities
    updateChart()
  } catch (err) {
    console.error('Error fetching AQI data:', err)
  } finally {
    loading.value = false
  }
}

const initChart = () => {
  if (!chartCanvas.value) return
  
  const ctx = chartCanvas.value.getContext('2d')
  
  chart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: [],
      datasets: [
        {
          label: 'Top 10 High Pollution',
          data: [],
          borderColor: '#ef4444',
          backgroundColor: 'rgba(239, 68, 68, 0.1)',
          fill: true,
          tension: 0.4,
          pointRadius: 5,
          pointHoverRadius: 7,
          pointBackgroundColor: '#ef4444',
          pointBorderColor: '#ffffff',
          pointBorderWidth: 2,
          borderWidth: 3
        },
        {
          label: 'Bottom 10 Low Pollution',
          data: [],
          borderColor: '#10b981',
          backgroundColor: 'rgba(16, 185, 129, 0.1)',
          fill: false,
          tension: 0.4,
          pointRadius: 5,
          pointHoverRadius: 7,
          pointBackgroundColor: '#10b981',
          pointBorderColor: '#ffffff',
          pointBorderWidth: 2,
          borderWidth: 3,
          borderDash: [6, 3]
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: true,
          position: 'top',
          labels: {
            usePointStyle: true,
            padding: 20,
            font: {
              size: 12,
              weight: '600'
            },
            color: '#475569'
          }
        },
        tooltip: {
          backgroundColor: 'rgba(255, 255, 255, 0.95)',
          titleColor: '#1e293b',
          bodyColor: '#475569',
          borderColor: '#e2e8f0',
          borderWidth: 1,
          cornerRadius: 8,
          padding: 12,
          displayColors: true,
          titleFont: {
            size: 14,
            weight: '600'
          },
          bodyFont: {
            size: 12
          },
          callbacks: {
            title: function(tooltipItems) {
              return `Rank ${tooltipItems[0].dataIndex + 1}`
            },
            label: function(context) {
              const sorted = context.datasetIndex === 0 ? highSortedRef.value : lowSortedRef.value
              const item = sorted[context.dataIndex] || {}
              return `${context.dataset.label}: ${context.raw} (${item.name || 'Unknown'})`
            }
          }
        }
      },
      scales: {
        x: {
          grid: {
            display: false
          },
          ticks: {
            color: '#64748b',
            font: {
              size: 12,
              weight: '500'
            },
            maxRotation: 45
          }
        },
        y: {
          beginAtZero: true,
          grid: {
            color: 'rgba(148, 163, 184, 0.1)',
            drawBorder: false
          },
          ticks: {
            color: '#64748b',
            font: {
              size: 12,
              weight: '500'
            },
            padding: 8
          }
        }
      },
      interaction: {
        intersect: false,
        mode: 'index'
      }
    }
  })
}

const updateChart = () => {
  if (!chart || !aqiData.value.length) return
  
  highSortedRef.value = [...aqiData.value]
    .filter(item => !isNaN(parseFloat(item[chartMetric.value])))
    .sort((a, b) => parseFloat(b[chartMetric.value]) - parseFloat(a[chartMetric.value]))
    .slice(0, 10)
  
  lowSortedRef.value = [...aqiData.value]
    .filter(item => !isNaN(parseFloat(item[chartMetric.value])))
    .sort((a, b) => parseFloat(a[chartMetric.value]) - parseFloat(b[chartMetric.value]))
    .slice(0, 10)
  
  const labels = Array(10).fill(0).map((_, i) => `Rank ${i + 1}`)
  const highData = highSortedRef.value.map(item => parseFloat(item[chartMetric.value]))
  const lowData = lowSortedRef.value.map(item => parseFloat(item[chartMetric.value]))
  
  chart.data.labels = labels
  chart.data.datasets[0].data = highData
  chart.data.datasets[0].label = `Highest 10 ${chartMetric.value.toUpperCase()}`
  chart.data.datasets[1].data = lowData
  chart.data.datasets[1].label = `Lowest 10 ${chartMetric.value.toUpperCase()}`
  chart.update('active')
}

const initPieChart = () => {
  if (!pieCanvas.value) return
  
  const ctx = pieCanvas.value.getContext('2d')
  
  pieChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Temperature', 'Humidity', 'Wind Speed', 'Pressure'],
      datasets: [{
        data: [35, 40, 25, 20],
        backgroundColor: [
          'rgba(59, 130, 246, 0.8)',
          'rgba(16, 185, 129, 0.8)',
          'rgba(249, 115, 22, 0.8)',
          'rgba(99, 102, 241, 0.8)'
        ],
        borderColor: [
          '#3b82f6',
          '#10b981',
          '#f97316',
          '#6366f1'
        ],
        borderWidth: 2,
        hoverBackgroundColor: [
          'rgba(59, 130, 246, 1)',
          'rgba(16, 185, 129, 1)',
          'rgba(249, 115, 22, 1)',
          'rgba(99, 102, 241, 1)'
        ],
        hoverBorderWidth: 3
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          backgroundColor: 'rgba(255, 255, 255, 0.95)',
          titleColor: '#1e293b',
          bodyColor: '#475569',
          borderColor: '#e2e8f0',
          borderWidth: 1,
          cornerRadius: 8,
          padding: 10
        }
      },
      cutout: '65%',
      animation: {
        animateRotate: true,
        duration: 800
      }
    }
  })
}

const exportData = (type) => {
  let data = []
  let filename = ''
  let headers = ['name', 'aqi', 'pm25', 'pm10', 'no2', 'co', 'o3', 'temperature', 'humidity', 'pressure', 'wind_speed']
  
  const escapeCsvValue = (value) => {
    if (value == null) return ''
    const str = String(value)
    if (str.includes(',') || str.includes('"') || str.includes('\n')) {
      return `"${str.replace(/"/g, '""')}"`
    }
    return str
  }
  
  switch(type) {
    case 'total':
      data = aqiData.value.map(item => 
        headers.map(header => escapeCsvValue(item[header])).join(',')
      )
      data.unshift(headers.join(','))
      filename = 'total-locations.csv'
      break
    case 'highest':
      if (highestPollution.value) {
        data = [headers.map(header => escapeCsvValue(highestPollution.value[header])).join(',')]
        data.unshift(headers.join(','))
      }
      filename = 'highest-pollution.csv'
      break
    case 'lowest':
      if (lowestPollution.value) {
        data = [headers.map(header => escapeCsvValue(lowestPollution.value[header])).join(',')]
        data.unshift(headers.join(','))
      }
      filename = 'lowest-pollution.csv'
      break
    case 'average':
      data = [
        `average_aqi,${escapeCsvValue(averageAQI.value)},timestamp,${escapeCsvValue(new Date().toISOString())}`,
        `average_temperature,${escapeCsvValue(averageTemperature.value)},timestamp,${escapeCsvValue(new Date().toISOString())}`,
        `average_humidity,${escapeCsvValue(averageHumidity.value)},timestamp,${escapeCsvValue(new Date().toISOString())}`,
        `average_wind_speed,${escapeCsvValue(averageWindSpeed.value)},timestamp,${escapeCsvValue(new Date().toISOString())}`,
        `average_pressure,${escapeCsvValue(averagePressure.value)},timestamp,${escapeCsvValue(new Date().toISOString())}`
      ]
      data.unshift('metric,value,timestamp')
      filename = 'average-metrics.csv'
      break
  }
  
  const blob = new Blob(['﻿' + data.join('\n')], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}

onMounted(async () => {
  await nextTick()
  initChart()
  initPieChart()
  fetchAQIData()
  locateUser()

  setInterval(fetchAQIData, 30000)
})
</script>

<style scoped>

* {
  font-family: 'Nunito Sans', sans-serif;
}

.bg-gradient-to-br {
  background-image: linear-gradient(to bottom right, var(--tw-gradient-stops));
}

/* Extra breathing room below the dropdown triggers on this page so the open
   menu doesn't sit flush against the button. */
.dropdown-spaced :deep(.aq-dd-menu) {
  top: calc(100% + 20px);
}

/* Extra breathing room between individual options so the hover highlight
   doesn't look like it's overlapping the row above/below it. */
.dropdown-spaced :deep(.aq-dd-item) {
  padding: 9px 10px !important;
  margin-bottom: 4px;
}

.dropdown-spaced :deep(.aq-dd-item:last-child) {
  margin-bottom: 0;
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}

.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

.hover\:shadow-lg:hover {
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.hover\:shadow-xl:hover {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.hover\:shadow-md:hover {
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}
</style>