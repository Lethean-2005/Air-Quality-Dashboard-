<template>
  <div class="w-full space-y-4">
    <!-- Stat Cards -->
    <div v-if="loading" class="grid grid-cols-2 md:grid-cols-5 gap-3">
      <div v-for="i in 5" :key="i" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
        <div class="flex items-center justify-between">
          <div class="min-w-0 space-y-2">
            <Skeleton class="h-3 w-16" />
            <Skeleton class="h-5 w-10" />
          </div>
          <Skeleton class="h-11 w-11 rounded-full flex-shrink-0" />
        </div>
        <div class="mt-3 pt-2.5 border-t border-gray-50">
          <Skeleton class="h-2.5 w-16" />
        </div>
      </div>
    </div>
    <div v-else class="grid grid-cols-2 md:grid-cols-5 gap-3">
      <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div class="min-w-0">
            <div class="text-xs font-medium text-gray-400 truncate">Good</div>
            <div class="text-xl font-bold text-gray-900 mt-0.5">{{ levelCounts.Good }}</div>
          </div>
          <img :src="aqiGoodImg" alt="" class="h-11 w-auto object-contain flex-shrink-0" />
        </div>
        <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center justify-between">
          <span class="text-[11px] text-gray-400 truncate">AQI 0-50</span>
          <button @click="exportLevelCSV('Good')" class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
            <IconDownload :size="13" />
          </button>
        </div>
      </div>
      <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div class="min-w-0">
            <div class="text-xs font-medium text-gray-400 truncate">Moderate</div>
            <div class="text-xl font-bold text-gray-900 mt-0.5">{{ levelCounts.Moderate }}</div>
          </div>
          <img :src="aqiModerateImg" alt="" class="h-11 w-auto object-contain flex-shrink-0" />
        </div>
        <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center justify-between">
          <span class="text-[11px] text-gray-400 truncate">AQI 51-100</span>
          <button @click="exportLevelCSV('Moderate')" class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
            <IconDownload :size="13" />
          </button>
        </div>
      </div>
      <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div class="min-w-0">
            <div class="text-xs font-medium text-gray-400 truncate">Unhealthy for SG</div>
            <div class="text-xl font-bold text-gray-900 mt-0.5">{{ levelCounts["Unhealthy for Sensitive Groups"] }}</div>
          </div>
          <img :src="aqiUnhealthySensitiveImg" alt="" class="h-11 w-auto object-contain flex-shrink-0" />
        </div>
        <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center justify-between">
          <span class="text-[11px] text-gray-400 truncate">AQI 101-150</span>
          <button @click="exportLevelCSV('Unhealthy for Sensitive Groups')" class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
            <IconDownload :size="13" />
          </button>
        </div>
      </div>
      <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div class="min-w-0">
            <div class="text-xs font-medium text-gray-400 truncate">Unhealthy</div>
            <div class="text-xl font-bold text-gray-900 mt-0.5">{{ levelCounts.Unhealthy }}</div>
          </div>
          <img :src="aqiUnhealthyImg" alt="" class="h-11 w-auto object-contain flex-shrink-0" />
        </div>
        <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center justify-between">
          <span class="text-[11px] text-gray-400 truncate">AQI 151-200</span>
          <button @click="exportLevelCSV('Unhealthy')" class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
            <IconDownload :size="13" />
          </button>
        </div>
      </div>
      <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between">
          <div class="min-w-0">
            <div class="text-xs font-medium text-gray-400 truncate">Hazardous</div>
            <div class="text-xl font-bold text-gray-900 mt-0.5">{{ levelCounts.Hazardous }}</div>
          </div>
          <img :src="aqiHazardousImg" alt="" class="h-11 w-auto object-contain flex-shrink-0" />
        </div>
        <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center justify-between">
          <span class="text-[11px] text-gray-400 truncate">AQI 301+</span>
          <button @click="exportLevelCSV('Hazardous')" class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
            <IconDownload :size="13" />
          </button>
        </div>
      </div>
    </div>

    <!-- Toolbar -->
    <div v-if="loading" class="flex flex-wrap items-center justify-between gap-3">
      <div class="flex items-center gap-2 flex-wrap">
        <Skeleton class="h-8 w-16 rounded-lg" />
        <Skeleton class="h-8 w-24 rounded-[5px]" />
        <Skeleton class="h-8 w-24 rounded-[5px]" />
      </div>
      <div class="flex items-center gap-2">
        <Skeleton class="h-9 w-56 rounded-lg" />
        <Skeleton class="h-9 w-9 rounded-lg" />
        <Skeleton class="h-9 w-9 rounded-lg" />
      </div>
    </div>
    <div v-else class="flex flex-wrap items-center justify-between gap-3">
      <div class="flex items-center gap-2 flex-wrap">
        <span class="px-3 py-1.5 rounded-lg text-sm font-semibold bg-gray-900 text-white">
          All {{ cities.length }}
        </span>

        <AppDropdown v-model="levelFilter" :options="levelFilterOptions" :lead-icon="IconGauge" light small class="city-dropdown" />

        <AppDropdown v-model="pollutant" :options="pollutantSelectOptions" :lead-icon="IconWind" light small class="city-dropdown" />
      </div>

      <div class="flex items-center gap-2">
        <div class="relative">
          <IconSearch :size="16" class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
          <input
            v-model="search"
            type="text"
            :placeholder="$t('city.searchPlaceholder')"
            class="pl-9 pr-3 h-9 rounded-lg text-sm w-56 text-gray-700 placeholder-gray-400 bg-white border border-gray-200 focus:outline-none focus:bg-gray-50"
          />
        </div>

        <button
          @click="fetchAQIData"
          :title="$t('city.refresh')"
          class="flex items-center justify-center w-9 h-9 bg-white border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50 transition-colors"
        >
          <IconRefresh :size="16" />
        </button>

        <button
          @click="exportToCSV"
          :title="$t('city.exportCSV')"
          class="flex items-center justify-center w-9 h-9 bg-white border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50 transition-colors"
        >
          <IconFileTypeCsv :size="16" />
        </button>
      </div>
    </div>

    <div class="bg-white shadow-sm border border-gray-100 rounded-2xl overflow-hidden">
      <Skeleton v-if="loading" class="mx-4 mt-3 h-3 w-72" />
      <p v-else class="px-4 pt-3 text-xs text-gray-400">
        {{ $t("city.dataSource") }}
        <a href="https://waqi.info" target="_blank" class="text-teal-600 hover:text-teal-700">World Air Quality Index (WAQI)</a>
        &middot; {{ $t("city.lastUpdated") }}: {{ lastUpdated }}
      </p>

      <!-- Mobile: stacked cards instead of a horizontally-scrolling table -->
      <div v-if="loading" class="md:hidden p-3 space-y-3">
        <div v-for="i in 8" :key="i" class="bg-white border border-gray-100 rounded-xl p-4 space-y-2.5">
          <div class="flex items-center gap-3">
            <Skeleton class="w-8 h-6 rounded-[5px] flex-shrink-0" />
            <Skeleton class="h-4 w-32" />
          </div>
          <Skeleton class="h-3 w-full" />
          <Skeleton class="h-3 w-2/3" />
        </div>
      </div>
      <div v-else-if="!error" class="md:hidden p-3 space-y-3">
        <div v-for="city in paginatedCities" :key="city.name" class="bg-white border border-gray-100 rounded-xl p-4">
          <div class="flex items-center gap-3 mb-3">
            <img
              v-if="city.flag"
              :src="city.flag"
              :alt="$t('city.flagAlt')"
              class="w-8 h-6 object-cover rounded-[5px] flex-shrink-0"
            />
            <div v-else class="w-8 h-6 rounded-[5px] bg-gray-100 flex-shrink-0"></div>
            <span class="text-sm font-semibold text-gray-900 truncate">{{ city.name }}</span>
          </div>
          <div class="space-y-2 text-xs">
            <div class="flex items-center justify-between">
              <span class="text-gray-400">{{ $t("city.table.pollutant") }}</span>
              <span class="text-gray-600 uppercase">{{ city.pollutant }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-gray-400">{{ $t("city.table.value") }}</span>
              <span class="text-gray-900 font-semibold">{{ city.value }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-gray-400">{{ $t("city.table.level") }}</span>
              <span :class="levelBadge(city.level)" class="px-2 py-0.5 rounded-[5px] font-medium">{{ city.level }}</span>
            </div>
            <div class="flex items-center justify-between">
              <span class="text-gray-400">{{ $t("city.table.latitude") }} / {{ $t("city.table.longitude") }}</span>
              <span class="text-gray-500">{{ city.lat }}, {{ city.lon }}</span>
            </div>
          </div>
        </div>
        <div v-if="paginatedCities.length === 0" class="p-6 text-center text-gray-500 text-sm">{{ $t("user.noUsers") }}</div>
      </div>

      <!-- Desktop/tablet: full table -->
      <div v-if="loading" class="hidden md:block p-4">
        <table class="min-w-full">
          <tbody>
            <tr v-for="i in 8" :key="i" class="border-b border-gray-50 last:border-b-0">
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <Skeleton class="w-8 h-6 rounded-[5px] flex-shrink-0" />
                  <Skeleton class="h-4 w-32" />
                </div>
              </td>
              <td class="px-4 py-3"><Skeleton class="h-4 w-10" /></td>
              <td class="px-4 py-3"><Skeleton class="h-4 w-8" /></td>
              <td class="px-4 py-3"><Skeleton class="h-5 w-20 rounded-[5px]" /></td>
              <td class="px-4 py-3"><Skeleton class="h-4 w-28" /></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div v-else-if="error" class="p-6 text-center text-red-500 text-sm">{{ error }}</div>

      <div v-else class="hidden md:block overflow-x-auto max-h-[600px] overflow-y-auto">
        <table class="min-w-full">
          <thead class="sticky top-0 z-10 bg-white">
            <tr class="border-b border-gray-100">
              <th
                class="px-4 py-3 text-left text-xs font-medium text-gray-400 cursor-pointer hover:text-gray-600"
                @click="sortBy('name')"
              >
                {{ $t("city.table.city") }}
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">{{ $t("city.table.pollutant") }}</th>
              <th
                class="px-4 py-3 text-left text-xs font-medium text-gray-400 cursor-pointer hover:text-gray-600"
                @click="sortBy('value')"
              >
                {{ $t("city.table.value") }}
              </th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">{{ $t("city.table.level") }}</th>
              <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">{{ $t("city.table.latitude") }} / {{ $t("city.table.longitude") }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="city in paginatedCities" :key="city.name" class="hover:bg-gray-50/60 transition-colors">
              <td class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <img
                    v-if="city.flag"
                    :src="city.flag"
                    :alt="$t('city.flagAlt')"
                    class="w-8 h-6 object-cover rounded-[5px] flex-shrink-0"
                  />
                  <div v-else class="w-8 h-6 rounded-[5px] bg-gray-100 flex-shrink-0"></div>
                  <span class="text-sm font-medium text-gray-900 truncate">{{ city.name }}</span>
                </div>
              </td>
              <td class="px-4 py-3 text-sm text-gray-500 uppercase">{{ city.pollutant }}</td>
              <td class="px-4 py-3 text-sm font-semibold text-gray-900">{{ city.value }}</td>
              <td class="px-4 py-3">
                <span :class="levelBadge(city.level)" class="px-2.5 py-1 rounded-[5px] text-xs font-medium inline-block">
                  {{ city.level }}
                </span>
              </td>
              <td class="px-4 py-3 text-xs text-gray-400 whitespace-nowrap">{{ city.lat }}, {{ city.lon }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="loading" class="flex flex-col sm:flex-row justify-between items-center gap-3 p-4 border-t border-gray-100">
        <Skeleton class="h-4 w-40" />
        <div class="flex items-center gap-1">
          <Skeleton v-for="i in 5" :key="i" class="h-7 w-8 rounded-[5px]" />
        </div>
      </div>
      <div v-else class="flex flex-col sm:flex-row justify-between items-center gap-3 p-4 border-t border-gray-100">
        <span class="text-sm text-gray-500">
          {{ $t("city.showing") }} {{ paginatedCities.length }}
          {{ $t("city.of") }} {{ filteredCities.length }} {{ $t("city.entries") }}
        </span>
        <div class="flex items-center gap-1">
          <button
            @click="goToPage(currentPage - 1)"
            :disabled="currentPage === 1"
            class="px-2.5 py-1 text-xs rounded-[5px] border border-gray-200 text-gray-600 hover:bg-gray-100 disabled:opacity-40 transition-colors"
          >
            «
          </button>

          <template v-for="page in paginationRange" :key="page">
            <button
              v-if="page !== '...'"
              @click="goToPage(page)"
              :class="[
                'px-2.5 py-1 text-xs rounded-[5px] border',
                currentPage === page
                  ? 'bg-gray-900 text-white border-gray-900'
                  : 'border-gray-200 text-gray-600 hover:bg-gray-100',
              ]"
            >
              {{ page }}
            </button>
            <span v-else class="px-2.5 py-1 text-xs text-gray-400">...</span>
          </template>

          <button
            @click="goToPage(currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="px-2.5 py-1 text-xs rounded-[5px] border border-gray-200 text-gray-600 hover:bg-gray-100 disabled:opacity-40 transition-colors"
          >
            »
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import api from "@/services/api";
import { IconSearch, IconRefresh, IconFileTypeCsv, IconDownload, IconGauge, IconWind } from "@tabler/icons-vue";
import AppDropdown from "@/components/AppDropdown.vue";
import Skeleton from "@/components/Skeleton.vue";
import { useI18n } from 'vue-i18n'
import aqiGoodImg from "@/assets/images/svg/aqi-good-level.webp";
import aqiModerateImg from "@/assets/images/svg/aqi-moderate-level.webp";
import aqiUnhealthySensitiveImg from "@/assets/images/svg/aqi-poor-level.webp";
import aqiUnhealthyImg from "@/assets/images/svg/aqi-unhealthy-level.webp";
import aqiHazardousImg from "@/assets/images/svg/aqi-hazardous-level.webp";

const { t } = useI18n()
const search = ref("");
const pollutant = ref("aqi");
const cities = ref([]);
const loading = ref(false);
const error = ref("");
const updatedAt = ref("");
const sortField = ref("name");
const sortDirection = ref("asc");
const lastUpdated = ref("");
const currentPage = ref(1);
const itemsPerPage = 5;
const levelFilter = ref("");

const levelFilterOptions = computed(() => [
  { value: "", label: t("city.allLevels") },
  { value: "Good", label: t("city.levels.good") },
  { value: "Moderate", label: t("city.levels.moderate") },
  { value: "Unhealthy for Sensitive Groups", label: t("city.levels.unhealthySensitive") },
  { value: "Unhealthy", label: t("city.levels.unhealthy") },
  { value: "Very Unhealthy", label: t("city.levels.veryUnhealthy") },
  { value: "Hazardous", label: t("city.levels.hazardous") },
]);

const pollutantSelectOptions = computed(() => [
  { value: "aqi", label: t("city.pollutants.aqi") },
  { value: "pm25", label: t("city.pollutants.pm25") },
  { value: "pm10", label: t("city.pollutants.pm10") },
  { value: "o3", label: t("city.pollutants.o3") },
  { value: "no2", label: t("city.pollutants.no2") },
  { value: "so2", label: t("city.pollutants.so2") },
  { value: "co", label: t("city.pollutants.co") },
  { value: "temperature", label: t("city.pollutants.temperature") },
  { value: "humidity", label: t("city.pollutants.humidity") },
  { value: "pressure", label: t("city.pollutants.pressure") },
  { value: "wind_speed", label: t("city.pollutants.windSpeed") },
]);

const levelBadge = (level) => {
  switch (level) {
    case "Good":
      return "bg-green-50 text-green-600";
    case "Moderate":
      return "bg-yellow-50 text-yellow-700";
    case "Unhealthy for Sensitive Groups":
      return "bg-orange-50 text-orange-600";
    case "Unhealthy":
      return "bg-red-50 text-red-600";
    case "Very Unhealthy":
      return "bg-purple-50 text-purple-600";
    case "Hazardous":
      return "bg-rose-100 text-rose-700";
    default:
      return "bg-gray-100 text-gray-500";
  }
};

const fetchAQIData = async () => {
  try {
    loading.value = true;
    const response = await api.get("/aqi"); // global cities
    const rawData = response.data.data || [];

    // Fetch Phnom Penh separately
    let phnomPenh = null;
    try {
      const ppRes = await api.get("/air-quality/phnom-penh");
      phnomPenh = {
        id: 9999,
        name: "Phnom Penh",
        lat: 11.562108,
        lon: 104.888535,
        aqi: ppRes.data.AQI ?? "N/A",
        pm25: ppRes.data.PM2_5 ?? "N/A",
        pm10: ppRes.data.PM10 ?? "N/A",
        co: ppRes.data.CO ?? "N/A",
        no2: ppRes.data.NO2 ?? "N/A",
        so2: ppRes.data.SO2 ?? "N/A",
        o3: ppRes.data.O3 ?? "N/A",
        temperature: ppRes.data.Temp ?? "N/A",
        humidity: ppRes.data.Humidity ?? "N/A",
        pressure: ppRes.data.Pressure ?? "N/A",
        wind_speed: ppRes.data.Wind ?? "N/A",
        flag: "https://flagcdn.com/w160/kh.png",
      };
    } catch (err) {
      console.error("Failed to fetch Phnom Penh:", err);
    }

    // Map global cities
    let combinedData = rawData.map((city) => ({
      ...city,
      pollutant: pollutant.value,
      value: city[pollutant.value],
      level: getAQILevel(city[pollutant.value]),
    }));

    // Merge Phnom Penh
    if (phnomPenh) {
      combinedData = combinedData.filter((c) => c.name !== "Phnom Penh");
      combinedData.push({
        ...phnomPenh,
        pollutant: pollutant.value,
        value: phnomPenh[pollutant.value],
        level: getAQILevel(phnomPenh[pollutant.value]),
      });
    }

    cities.value = combinedData;
    lastUpdated.value = new Date().toLocaleString();
  } catch (err) {
    console.error(err);
    error.value = "Failed to load data.";
  } finally {
    loading.value = false;
  }
};


// helper to classify pollutant levels (basic AQI logic, can be customized)
const getAQILevel = (value) => {
  if (value === null || value === undefined) return "Unknown";
  if (value <= 50) return "Good";
  if (value <= 100) return "Moderate";
  if (value <= 150) return "Unhealthy for Sensitive Groups";
  if (value <= 200) return "Unhealthy";
  if (value <= 300) return "Very Unhealthy";
  return "Hazardous";
};
watch(pollutant, () => {
  cities.value = cities.value.map((city) => ({
    ...city,
    pollutant: pollutant.value,
    value: city[pollutant.value],
    level: getAQILevel(city[pollutant.value]),
  }));
});

const exportToCSV = () => {
  if (!cities.value || cities.value.length === 0) return;

  const headers = Object.keys(cities.value[0]);
  const rows = cities.value.map((row) =>
    headers.map((field) => `"${row[field]}"`).join(",")
  );

  const csvContent = [headers.join(","), ...rows].join("\n");

  const blob = new Blob(["﻿" + csvContent], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.setAttribute("download", "city-aqi.csv");
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};

const exportLevelCSV = (level) => {
  const rowsForLevel = cities.value.filter((c) => c.level === level);
  if (rowsForLevel.length === 0) return;

  const headers = Object.keys(rowsForLevel[0]);
  const rows = rowsForLevel.map((row) =>
    headers.map((field) => `"${row[field]}"`).join(",")
  );

  const csvContent = [headers.join(","), ...rows].join("\n");

  const blob = new Blob(["﻿" + csvContent], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);
  const link = document.createElement("a");
  link.href = url;
  link.setAttribute("download", `city-aqi-${level.toLowerCase().replace(/\s+/g, "-")}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
};

const paginatedCities = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return sortedCities.value.slice(start, start + itemsPerPage);
});

const totalPages = computed(() =>
  Math.ceil(sortedCities.value.length / itemsPerPage)
);

const levelCounts = computed(() => {
  const counts = {
    Good: 0,
    Moderate: 0,
    "Unhealthy for Sensitive Groups": 0,
    Unhealthy: 0,
    "Very Unhealthy": 0,
    Hazardous: 0,
  };
  cities.value.forEach((city) => {
    if (counts[city.level] !== undefined) counts[city.level]++;
  });
  return counts;
});

// Page range with ellipsis
const paginationRange = computed(() => {
  const total = totalPages.value;
  const current = currentPage.value;
  const delta = 2;
  const range = [];
  const rangeWithDots = [];
  let l;

  for (let i = 1; i <= total; i++) {
    if (i === 1 || i === total || (i >= current - delta && i <= current + delta)) {
      range.push(i);
    }
  }

  for (let i of range) {
    if (l) {
      if (i - l === 2) {
        rangeWithDots.push(l + 1);
      } else if (i - l > 2) {
        rangeWithDots.push("...");
      }
    }
    rangeWithDots.push(i);
    l = i;
  }

  return rangeWithDots;
});

const goToPage = (page) => {
  if (page > 0 && page <= totalPages.value) {
    currentPage.value = page;
  }
};

const sortBy = (field) => {
  if (sortField.value === field) {
    sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
  } else {
    sortField.value = field;
    sortDirection.value = "asc";
  }
};
const filteredCities = computed(() => {
  return cities.value.filter((city) => {
    const hasValue =
      city.value !== null && city.value !== undefined && city.value !== "";
    return (
      hasValue && // exclude cities with missing data
      (!levelFilter.value || city.level === levelFilter.value) &&
      city.name.toLowerCase().includes(search.value.toLowerCase())
    );
  });
});

const sortedCities = computed(() => {
  return [...filteredCities.value].sort((a, b) => {
    let valA = a[sortField.value];
    let valB = b[sortField.value];
    if (typeof valA === "string") valA = valA.toLowerCase();
    if (typeof valB === "string") valB = valB.toLowerCase();
    if (valA < valB) return sortDirection.value === "asc" ? -1 : 1;
    if (valA > valB) return sortDirection.value === "asc" ? 1 : -1;
    return 0;
  });
});

watch([search, pollutant], fetchAQIData, { immediate: true });
</script>

<style scoped>
/* Match the Level/Pollutant dropdowns' corner radius to the dashboard's
   AQI dropdowns (5px). !important beats AppDropdown's own light+small
   rule, which is more specific (.aq-dd-wrap.light .aq-dd.small). Also
   give both a shared fixed width so the Metric dropdown follows the
   AQI (Level) dropdown's size instead of shrinking/growing with
   whichever option label is currently selected. */
.city-dropdown :deep(.aq-dd) {
  border-radius: 5px !important;
  width: 128px;
}

.city-dropdown :deep(.aq-dd-label) {
  flex: 1;
  overflow: hidden;
  text-overflow: ellipsis;
}
</style>
