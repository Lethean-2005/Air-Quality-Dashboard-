<template>
  <div class="w-full bg-gray-50 dark:bg-transparent rounded-2xl">
    <div class="w-full">
      <!-- Status Summary Cards -->
      <div v-if="smartAlertLoading && !smartAlert" class="grid grid-cols-2 md:grid-cols-6 gap-2.5 mb-8">
        <div v-for="i in 6" :key="i" class="bg-white border border-gray-100 rounded-xl p-3 shadow-sm">
          <div class="flex items-center justify-between">
            <div class="min-w-0 space-y-1.5">
              <Skeleton class="h-2.5 w-14" />
              <Skeleton class="h-4 w-6" />
            </div>
            <Skeleton class="h-6 w-6 rounded-full flex-shrink-0" />
          </div>
          <div class="mt-2 pt-1.5 border-t border-gray-50">
            <Skeleton class="h-2 w-12" />
          </div>
        </div>
      </div>
      <div v-else class="grid grid-cols-2 md:grid-cols-6 gap-2.5 mb-8">
        <div
          v-for="(status, key) in alertStatus"
          :key="key"
          class="bg-white border border-gray-100 rounded-xl p-3 shadow-sm hover:shadow-md transition-shadow"
        >
          <div class="flex items-center justify-between">
            <div class="min-w-0">
              <div class="text-[11px] font-medium text-gray-400 truncate">{{ getStatusLabel(key) }}</div>
              <div class="text-base font-bold text-gray-900 mt-0.5">{{ status.count }}</div>
            </div>
            <img :src="getStatusIcon(key)" alt="" class="h-6 w-auto object-contain flex-shrink-0" />
          </div>
          <div class="mt-2 pt-1.5 border-t border-gray-50">
            <span class="text-[10px] text-gray-400 truncate">{{ getStatusRange(key) }}</span>
          </div>
        </div>
      </div>

      <!-- Smart Health Alert: Location -> AQI + Weather -> Decision Engine -> Smart Message -->
      <div class="mb-8">
        <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div v-if="smartAlertLoading && !smartAlert" class="space-y-1.5">
              <Skeleton class="h-5 w-40" />
              <Skeleton class="h-3 w-56" />
            </div>
            <div v-else>
              <h2 class="text-lg font-bold text-gray-900">Smart Health Alert</h2>
              <p class="text-xs text-gray-400 mt-0.5">Live location &rarr; AQI + weather &rarr; recommendation</p>
            </div>
            <button
              @click="refreshSmartAlert"
              :disabled="smartAlertLoading"
              class="flex items-center gap-1.5 px-3 py-2 border border-gray-200 rounded-lg text-sm text-gray-600 hover:bg-gray-50 disabled:opacity-50 transition-colors"
            >
              <IconRefresh :size="15" :class="smartAlertLoading ? 'animate-spin' : ''" />
              Refresh
            </button>
          </div>

          <div class="p-6">
            <div v-if="smartAlertLoading && !smartAlert">
              <div class="flex flex-wrap items-center gap-2 mb-5">
                <Skeleton class="h-6 w-28 rounded-full" />
                <Skeleton class="h-6 w-16 rounded-full" />
                <Skeleton class="h-6 w-32 rounded-full" />
                <Skeleton class="h-6 w-28 rounded-full" />
              </div>
              <div class="grid md:grid-cols-3 gap-3 mb-5">
                <Skeleton class="h-16 rounded-xl" />
                <Skeleton class="h-16 rounded-xl" />
                <Skeleton class="h-16 rounded-xl" />
              </div>
              <Skeleton class="h-14 rounded-xl" />
            </div>
            <div v-else-if="smartAlertError" class="text-center text-sm text-red-500 py-8">
              {{ smartAlertError }}
            </div>
            <template v-else-if="smartAlert">
              <!-- Pipeline row -->
              <div class="flex flex-wrap items-center gap-2 text-xs text-gray-500 mb-5">
                <span class="flex items-center gap-1 px-2.5 py-1 bg-gray-100 rounded-full font-medium text-gray-600">
                  <IconMapPin :size="13" /> {{ smartAlertLocationLabel }}
                </span>
                <IconArrowRight :size="14" class="text-gray-300" />
                <span class="px-2.5 py-1 bg-gray-100 rounded-full font-medium text-gray-600">AQI {{ smartAlert.aqi.value ?? "—" }}</span>
                <span class="px-2.5 py-1 bg-gray-100 rounded-full font-medium text-gray-600">{{ smartAlert.weather.description || "Weather" }}</span>
                <IconArrowRight :size="14" class="text-gray-300" />
                <span class="px-2.5 py-1 bg-teal-50 text-teal-700 rounded-full font-semibold">Decision Engine</span>
                <IconArrowRight :size="14" class="text-gray-300" />
                <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 rounded-full font-semibold">Smart Message</span>
              </div>

              <!-- Decision engine outputs -->
              <div class="grid md:grid-cols-3 gap-3 mb-5">
                <div class="rounded-xl p-4 border" :class="severityCardClass">
                  <div class="text-[11px] font-semibold uppercase tracking-wide opacity-70">AQI Level</div>
                  <div class="text-lg font-bold mt-0.5">{{ smartAlert.decision.aqi_level }}</div>
                </div>
                <div class="rounded-xl p-4 border border-gray-100 bg-gray-50">
                  <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Weather</div>
                  <div class="text-lg font-bold mt-0.5 text-gray-900">{{ smartAlert.decision.weather_summary }}</div>
                </div>
                <div class="rounded-xl p-4 border border-gray-100 bg-gray-50">
                  <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">Activity Recommendation</div>
                  <div class="text-sm font-medium mt-0.5 text-gray-700">{{ smartAlert.decision.activity_recommendation }}</div>
                </div>
              </div>

              <!-- Smart Message -->
              <div class="rounded-xl border border-teal-100 bg-teal-50/60 p-4">
                <div class="text-[11px] font-semibold uppercase tracking-wide text-teal-700 mb-1">Smart Message</div>
                <p class="text-sm text-gray-800 leading-relaxed">{{ smartMessage }}</p>
              </div>

              <!-- Telegram delivery -->
              <div class="mt-4 flex flex-wrap items-center gap-2">
                <input
                  v-model="telegramChatId"
                  type="text"
                  placeholder="Telegram chat ID"
                  class="px-3 py-2 text-sm border border-gray-200 rounded-lg w-44 focus:outline-none focus:ring-2 focus:ring-teal-400"
                />
                <button
                  @click="sendSmartMessageToTelegram"
                  :disabled="!telegramChatId || telegramSending"
                  class="flex items-center gap-1.5 px-3 py-2 text-sm bg-gray-900 text-white rounded-lg hover:bg-gray-800 disabled:opacity-40 transition-colors"
                >
                  <IconSend :size="14" />
                  {{ telegramSending ? "Sending…" : "Send to Telegram" }}
                </button>
                <button
                  @click="lookupTelegramChatId"
                  :disabled="telegramLookupLoading"
                  class="px-3 py-2 text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 disabled:opacity-50 transition-colors"
                >
                  {{ telegramLookupLoading ? "Looking…" : "Find my chat ID" }}
                </button>
                <span v-if="telegramStatus" class="text-xs" :class="telegramStatus.ok ? 'text-green-600' : 'text-red-500'">
                  {{ telegramStatus.message }}
                </span>
              </div>
              <div v-if="telegramChats.length" class="mt-2 text-xs text-gray-500">
                Message your bot once, then pick your chat:
                <span
                  v-for="c in telegramChats"
                  :key="c.id"
                  @click="telegramChatId = String(c.id)"
                  class="inline-block mt-1 mr-2 px-2 py-0.5 bg-gray-100 rounded cursor-pointer hover:bg-gray-200 transition-colors"
                >
                  {{ c.first_name || c.username || c.id }} ({{ c.id }})
                </span>
              </div>

              <div class="mt-4 pt-3 border-t border-gray-50 flex items-center gap-1.5 text-[11px] text-gray-400">
                <IconClock :size="12" />
                <span>Live readings, updates automatically every minute</span>
              </div>
            </template>
          </div>
        </div>
      </div>



      <!-- Alert Configuration Cards -->
      <div v-if="smartAlertLoading && !smartAlert" class="grid gap-6">
        <div v-for="i in 6" :key="i" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
          <div class="flex items-center gap-4 px-6 py-5 border-b border-gray-100">
            <Skeleton class="w-14 h-14 rounded-xl flex-shrink-0" />
            <div class="min-w-0 space-y-2">
              <Skeleton class="h-3 w-24" />
              <Skeleton class="h-5 w-40" />
            </div>
          </div>
          <div class="p-6">
            <div class="grid md:grid-cols-2 gap-6">
              <div class="space-y-3">
                <Skeleton class="h-4 w-32" />
                <Skeleton class="h-20 w-full rounded-xl" />
              </div>
              <div class="space-y-3">
                <Skeleton class="h-4 w-32" />
                <Skeleton class="h-20 w-full rounded-xl" />
              </div>
            </div>
          </div>
        </div>
      </div>
      <div v-else class="grid gap-6">
        <!-- Good Air Quality -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
          <div class="flex items-center gap-4 px-6 py-5 border-b border-gray-100">
            <div class="w-14 h-14 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
              <img :src="getStatusIcon('good')" alt="" class="h-10 w-auto object-contain" />
            </div>
            <div class="min-w-0">
              <div class="text-[11px] font-bold uppercase tracking-widest text-green-600">{{$t('Health.MinimalRisk')}}</div>
              <h3 class="text-xl font-extrabold text-gray-900 leading-tight">{{$t('Health.GoodAir')}}</h3>
            </div>
          </div>
          <div class="p-6">
            <div class="grid md:grid-cols-2 gap-6">
              <div class="space-y-3">
                <label class="block text-sm font-semibold text-gray-700">{{$t('Health.PublicMessage')}}</label>
                <textarea
                  v-model="messages.good.public"
                  class="w-full border-2 border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 resize-none"
                  rows="3"
                  placeholder="Message for general public..."
                ></textarea>
              </div>
              <div class="space-y-3">
                <label class="block text-sm font-semibold text-gray-700">{{$t('Health.SensitiveGroups')}}</label>
                <textarea
                  v-model="messages.good.sensitive"
                  class="w-full border-2 border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 resize-none"
                  rows="3"
                  placeholder="Message for sensitive individuals..."
                ></textarea>
              </div>
            </div>
          </div>
        </div>


        <!-- Moderate Air Quality -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
          <div class="flex items-center gap-4 px-6 py-5 border-b border-gray-100">
            <div class="w-14 h-14 rounded-xl bg-yellow-50 flex items-center justify-center flex-shrink-0">
              <img :src="getStatusIcon('moderate')" alt="" class="h-10 w-auto object-contain" />
            </div>
            <div class="min-w-0">
              <div class="text-[11px] font-bold uppercase tracking-widest text-yellow-600">{{$t('Health.LowRisk')}}</div>
              <h3 class="text-xl font-extrabold text-gray-900 leading-tight">{{$t('Health.ModerateAir')}}</h3>
            </div>
          </div>
          <div class="p-6">
            <div class="grid md:grid-cols-2 gap-6">
              <div class="space-y-3">
                <label class="block text-sm font-semibold text-gray-700">{{$t('Health.PublicMessage')}}</label>
                <textarea
                  v-model="messages.moderate.public"
                  class="w-full border-2 border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all duration-200 resize-none"
                  rows="3"
                  placeholder="Message for general public..."
                ></textarea>
              </div>
              <div class="space-y-3">
                <label class="block text-sm font-semibold text-gray-700">{{$t('Health.SensitiveGroups')}}</label>
                <textarea
                  v-model="messages.moderate.sensitive"
                  class="w-full border-2 border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 transition-all duration-200 resize-none"
                  rows="3"
                  placeholder="Message for sensitive individuals..."
                ></textarea>
              </div>
            </div>
          </div>
        </div>


        <!-- Unhealthy for Sensitive Groups -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
          <div class="flex items-center gap-4 px-6 py-5 border-b border-gray-100">
            <div class="w-14 h-14 rounded-xl bg-orange-50 flex items-center justify-center flex-shrink-0">
              <img :src="getStatusIcon('unhealthySensitive')" alt="" class="h-10 w-auto object-contain" />
            </div>
            <div class="min-w-0">
              <div class="text-[11px] font-bold uppercase tracking-widest text-orange-600">{{$t('Health.ModerateRisk')}}</div>
              <h3 class="text-xl font-extrabold text-gray-900 leading-tight">{{$t('Health.UnhealthyforSensitive')}}</h3>
            </div>
          </div>
          <div class="p-6">
            <div class="grid md:grid-cols-2 gap-6 mb-6">
              <div class="space-y-3">
                <label class="block text-sm font-semibold text-gray-700">{{$t('Health.PublicMessage')}}</label>
                <textarea
                  v-model="messages.unhealthySensitive.public"
                  class="w-full border-2 border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-200 resize-none"
                  rows="3"
                  placeholder="Message for general public..."
                ></textarea>
              </div>
              <div class="space-y-3">
                <label class="block text-sm font-semibold text-gray-700">{{$t('Health.SensitiveGroupsAlert')}}</label>
                <textarea
                  v-model="messages.unhealthySensitive.sensitive"
                  class="w-full border-2 border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-200 resize-none"
                  rows="3"
                  placeholder="Important alert for sensitive individuals..."
                ></textarea>
              </div>
            </div>
            <div class="space-y-3">
              <label class="block text-sm font-semibold text-gray-700">{{$t('Health.RecommendedActions')}}</label>
              <textarea
                v-model="messages.unhealthySensitive.actions"
                class="w-full border-2 border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-200 resize-none"
                rows="2"
                placeholder="Specific actions and precautions..."
              ></textarea>
            </div>
          </div>
        </div>


        <!-- Unhealthy Air Quality -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
          <div class="flex items-center gap-4 px-6 py-5 border-b border-gray-100">
            <div class="w-14 h-14 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
              <img :src="getStatusIcon('unhealthy')" alt="" class="h-10 w-auto object-contain" />
            </div>
            <div class="min-w-0">
              <div class="text-[11px] font-bold uppercase tracking-widest text-red-600">{{$t('Health.HighRisk')}}</div>
              <h3 class="text-xl font-extrabold text-gray-900 leading-tight">{{$t('Health.UnhealthyAir')}}</h3>
            </div>
          </div>
          <div class="p-6">
            <div class="grid md:grid-cols-2 gap-6 mb-6">
              <div class="space-y-3">
                <label class="block text-sm font-semibold text-gray-700">{{$t('Health.PublicHealthWarning')}}</label>
                <textarea
                  v-model="messages.unhealthy.public"
                  class="w-full border-2 border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all duration-200 resize-none"
                  rows="3"
                  placeholder="Health warning for everyone..."
                ></textarea>
              </div>
              <div class="space-y-3">
                <label class="block text-sm font-semibold text-gray-700">{{$t('Health.SensitiveGroupsAlert')}}</label>
                <textarea
                  v-model="messages.unhealthy.sensitive"
                  class="w-full border-2 border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all duration-200 resize-none"
                  rows="3"
                  placeholder="Critical alert for sensitive groups..."
                ></textarea>
              </div>
            </div>
            <div class="grid md:grid-cols-2 gap-6">
              <div class="space-y-3">
                <label class="block text-sm font-semibold text-gray-700">{{$t('Health.EmergencyActions')}}</label>
                <textarea
                  v-model="messages.unhealthy.emergency"
                  class="w-full border-2 border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all duration-200 resize-none"
                  rows="2"
                  placeholder="Emergency precautions and actions..."
                ></textarea>
              </div>
              <div class="space-y-3">
                <label class="block text-sm font-semibold text-gray-700">{{$t('Health.ActivityRestrictions')}}</label>
                <textarea
                  v-model="messages.unhealthy.restrictions"
                  class="w-full border-2 border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-all duration-200 resize-none"
                  rows="2"
                  placeholder="Outdoor activity restrictions..."
                ></textarea>
              </div>
            </div>
          </div>
        </div>


        <!-- Very Unhealthy Air Quality -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
          <div class="flex items-center gap-4 px-6 py-5 border-b border-gray-100">
            <div class="w-14 h-14 rounded-xl bg-purple-50 flex items-center justify-center flex-shrink-0">
              <img :src="getStatusIcon('veryUnhealthy')" alt="" class="h-10 w-auto object-contain" />
            </div>
            <div class="min-w-0">
              <div class="text-[11px] font-bold uppercase tracking-widest text-purple-600">{{$t('Health.VeryHighRisk')}}</div>
              <h3 class="text-xl font-extrabold text-gray-900 leading-tight">{{$t('Health.VeryUnhealthyAir')}}</h3>
            </div>
          </div>
          <div class="p-6">
            <div class="grid md:grid-cols-2 gap-6 mb-6">
              <div class="space-y-3">
                <label class="block text-sm font-semibold text-gray-700">{{$t('Health.PublicHealthWarning')}}</label>
                <textarea
                  v-model="messages.veryUnhealthy.public"
                  class="w-full border-2 border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 resize-none"
                  rows="3"
                  placeholder="Health warning for everyone..."
                ></textarea>
              </div>
              <div class="space-y-3">
                <label class="block text-sm font-semibold text-gray-700">{{$t('Health.SensitiveGroupsAlert')}}</label>
                <textarea
                  v-model="messages.veryUnhealthy.sensitive"
                  class="w-full border-2 border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 resize-none"
                  rows="3"
                  placeholder="Critical alert for sensitive groups..."
                ></textarea>
              </div>
            </div>
            <div class="grid md:grid-cols-2 gap-6">
              <div class="space-y-3">
                <label class="block text-sm font-semibold text-gray-700">{{$t('Health.EmergencyActions')}}</label>
                <textarea
                  v-model="messages.veryUnhealthy.emergency"
                  class="w-full border-2 border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 resize-none"
                  rows="2"
                  placeholder="Emergency precautions and actions..."
                ></textarea>
              </div>
              <div class="space-y-3">
                <label class="block text-sm font-semibold text-gray-700">{{$t('Health.ActivityRestrictions')}}</label>
                <textarea
                  v-model="messages.veryUnhealthy.restrictions"
                  class="w-full border-2 border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all duration-200 resize-none"
                  rows="2"
                  placeholder="Outdoor activity restrictions..."
                ></textarea>
              </div>
            </div>
          </div>
        </div>


        <!-- Hazardous Air Quality -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
          <div class="flex items-center gap-4 px-6 py-5 border-b border-gray-100">
            <div class="w-14 h-14 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
              <img :src="getStatusIcon('hazardous')" alt="" class="h-10 w-auto object-contain" />
            </div>
            <div class="min-w-0">
              <div class="text-[11px] font-bold uppercase tracking-widest text-red-800">{{$t('Health.ExtremeRisk')}}</div>
              <h3 class="text-xl font-extrabold text-gray-900 leading-tight">{{$t('Health.HazardousAir')}}</h3>
            </div>
          </div>
          <div class="p-6">
            <div class="grid md:grid-cols-2 gap-6 mb-6">
              <div class="space-y-3">
                <label class="block text-sm font-semibold text-gray-700">{{$t('Health.PublicHealthEmergency')}}</label>
                <textarea
                  v-model="messages.hazardous.public"
                  class="w-full border-2 border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600 transition-all duration-200 resize-none"
                  rows="3"
                  placeholder="Health emergency for everyone..."
                ></textarea>
              </div>
              <div class="space-y-3">
                <label class="block text-sm font-semibold text-gray-700">{{$t('Health.SensitiveGroupsEmergency')}}</label>
                <textarea
                  v-model="messages.hazardous.sensitive"
                  class="w-full border-2 border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600 transition-all duration-200 resize-none"
                  rows="3"
                  placeholder="Critical emergency for sensitive groups..."
                ></textarea>
              </div>
            </div>
            <div class="grid md:grid-cols-2 gap-6">
              <div class="space-y-3">
                <label class="block text-sm font-semibold text-gray-700">{{$t('Health.EmergencyActions')}}</label>
                <textarea
                  v-model="messages.hazardous.emergency"
                  class="w-full border-2 border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600 transition-all duration-200 resize-none"
                  rows="2"
                  placeholder="Strict emergency precautions and actions..."
                ></textarea>
              </div>
              <div class="space-y-3">
                <label class="block text-sm font-semibold text-gray-700">{{$t('Health.ActivityRestrictions')}}</label>
                <textarea
                  v-model="messages.hazardous.restrictions"
                  class="w-full border-2 border-gray-200 rounded-xl p-4 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 focus:border-red-600 transition-all duration-200 resize-none"
                  rows="2"
                  placeholder="All outdoor activities prohibited..."
                ></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>


      <!-- Action Panel -->
      <div class="mt-8 bg-white rounded-2xl shadow-lg border border-gray-100 p-6">
        <div class="flex flex-col md:flex-row items-center justify-between space-y-4 md:space-y-0">
          <div class="flex flex-wrap items-center gap-4">
            <button
              @click="saveMessages"
              class="px-3 py-2 text-sm bg-gray-900 text-white rounded-lg hover:bg-gray-800 font-medium transition-colors"
            >
              {{$t('Health.Save')}}
            </button>
            <button
              @click="openPreview"
              class="px-3 py-2 text-sm border border-gray-200 text-gray-600 rounded-lg hover:bg-gray-50 font-medium transition-colors"
            >
              {{$t('Health.Preview')}}
            </button>
            <button
              @click="resetDefaults"
              class="px-3 py-2 text-sm border border-red-200 text-red-600 rounded-lg hover:bg-red-50 font-medium transition-colors"
            >
              {{$t('Health.Reset')}}
            </button>
          </div>
          <div class="flex items-center">
            <transition name="fade">
              <div v-if="saved" class="text-green-600 font-semibold flex items-center bg-green-50 px-4 py-2 rounded-xl border border-green-200">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                {{$t('Health.Messages')}}
              </div>
            </transition>
          </div>
        </div>
      </div>
    </div>


    <!-- Enhanced Preview Modal -->
    <transition name="modal">
      <div v-if="showModal" class="fixed inset-0 bg-white/30 backdrop-blur-sm flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl max-h-[90vh] overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
              <h2 class="text-lg font-bold text-gray-900">{{$t('Health.HealthAlert')}}</h2>
              <p class="text-xs text-gray-400 mt-0.5">How each AQI-tier message will read to users</p>
            </div>
            <button
              @click="showModal = false"
              class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <div class="p-6 overflow-y-auto max-h-[calc(90vh-80px)]">
            <div class="grid gap-4">
              <div
                v-for="(msg, key) in messages"
                :key="key"
                class="border border-gray-100 rounded-2xl overflow-hidden hover:shadow-sm transition-shadow"
              >
                <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100" :class="getPreviewCardClass(key)">
                  <div class="w-10 h-10 rounded-lg bg-white/70 flex items-center justify-center flex-shrink-0">
                    <img :src="getStatusIcon(key)" alt="" class="h-7 w-auto object-contain" />
                  </div>
                  <div class="font-bold text-sm" :class="getStatusTextClass(key)">{{ getAQILabel(key) }}</div>
                </div>
                <div class="p-5 space-y-3 text-sm">
                  <div v-if="msg.public">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">{{$t('Health.Public')}}</div>
                    <div class="text-gray-700 mt-0.5">{{ msg.public }}</div>
                  </div>
                  <div v-if="msg.sensitive">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">{{$t('Health.SensitiveGroups')}}</div>
                    <div class="text-gray-700 mt-0.5">{{ msg.sensitive }}</div>
                  </div>
                  <div v-if="msg.actions">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">{{$t('Health.RecommendedActions')}}</div>
                    <div class="text-gray-700 mt-0.5">{{ msg.actions }}</div>
                  </div>
                  <div v-if="msg.emergency">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">{{$t('Health.EmergencyActions')}}</div>
                    <div class="text-gray-700 mt-0.5">{{ msg.emergency }}</div>
                  </div>
                  <div v-if="msg.restrictions">
                    <div class="text-[11px] font-semibold uppercase tracking-wide text-gray-400">{{$t('Health.ActivityRestrictions')}}</div>
                    <div class="text-gray-700 mt-0.5">{{ msg.restrictions }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import api from "@/services/api";
import Skeleton from "@/components/Skeleton.vue";
import { IconRefresh, IconArrowRight, IconMapPin, IconSend, IconClock } from "@tabler/icons-vue";
import aqiGoodImg from "@/assets/images/svg/aqi-good-level.webp";
import aqiModerateImg from "@/assets/images/svg/aqi-moderate-level.webp";
import aqiUnhealthySensitiveImg from "@/assets/images/svg/aqi-poor-level.webp";
import aqiUnhealthyImg from "@/assets/images/svg/aqi-unhealthy-level.webp";
import aqiSevereImg from "@/assets/images/svg/aqi-severe-level.webp";
import aqiHazardousImg from "@/assets/images/svg/aqi-hazardous-level.webp";


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

const messages = ref(JSON.parse(JSON.stringify(defaultMessages)));
const saved = ref(false);
const showModal = ref(false);

// Computed properties for dashboard stats
const alertStatus = computed(() => ({
  good: { count: Math.floor(Math.random() * 15) + 5 },
  moderate: { count: Math.floor(Math.random() * 10) + 3 },
  unhealthySensitive: { count: Math.floor(Math.random() * 8) + 2 },
  unhealthy: { count: Math.floor(Math.random() * 5) + 1 },
  veryUnhealthy: { count: Math.floor(Math.random() * 3) + 1 },
  hazardous: { count: Math.floor(Math.random() * 2) }
}));

// --- Smart Health Alert: Location -> AQI + Weather -> Decision Engine -> Smart Message ---
const smartAlert = ref(null);
const smartAlertLoading = ref(false);
const smartAlertError = ref("");
const smartAlertLocationLabel = ref("Current Location");

const telegramChatId = ref("");
const telegramSending = ref(false);
const telegramStatus = ref(null);
const telegramChats = ref([]);
const telegramLookupLoading = ref(false);

// Fallback matches the backend's own default (Phnom Penh) so the two agree
// when geolocation is denied/unavailable.
const FALLBACK_LOCATION = { lat: 11.562108, lon: 104.888535 };
// Remembered so the auto-refresh interval re-fetches for the same spot
// instead of re-prompting the browser's geolocation permission every tick.
const lastCoords = ref(null);
const SMART_ALERT_REFRESH_MS = 60000;
let smartAlertInterval = null;

async function fetchSmartAlert(lat, lon) {
  lastCoords.value = { lat, lon };
  smartAlertLoading.value = true;
  smartAlertError.value = "";
  try {
    const { data } = await api.get("/health-alert/decision", { params: { lat, lon } });
    smartAlert.value = data;
  } catch (e) {
    smartAlertError.value = "Could not load live conditions. Retry in a moment.";
  } finally {
    smartAlertLoading.value = false;
  }
}

function detectLocationAndFetch() {
  if (!navigator.geolocation) {
    fetchSmartAlert(FALLBACK_LOCATION.lat, FALLBACK_LOCATION.lon);
    return;
  }
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      smartAlertLocationLabel.value = "Your Location";
      fetchSmartAlert(pos.coords.latitude, pos.coords.longitude);
    },
    () => {
      smartAlertLocationLabel.value = "Phnom Penh (default)";
      fetchSmartAlert(FALLBACK_LOCATION.lat, FALLBACK_LOCATION.lon);
    },
    { timeout: 8000 }
  );
}

function refreshSmartAlert() {
  // Manual click re-detects location (user may have moved); the background
  // interval below just re-polls the last known spot.
  detectLocationAndFetch();
}

// The Smart Message pulls the admin's own configured "public" wording for this
// AQI tier (from the message-template editor below) and appends the live,
// weather-aware activity recommendation from the decision engine.
const smartMessage = computed(() => {
  if (!smartAlert.value) return "";
  const key = smartAlert.value.decision.aqi_template_key;
  const template = messages.value[key]?.public || "";
  return `${template} ${smartAlert.value.decision.activity_recommendation}`.trim();
});

const severityCardClass = computed(() => {
  const severity = smartAlert.value?.decision?.aqi_severity ?? 0;
  const classes = [
    "border-green-200 bg-green-50 text-green-800",
    "border-yellow-200 bg-yellow-50 text-yellow-800",
    "border-orange-200 bg-orange-50 text-orange-800",
    "border-red-200 bg-red-50 text-red-800",
    "border-purple-200 bg-purple-50 text-purple-800",
    "border-rose-300 bg-rose-100 text-rose-900",
  ];
  return classes[severity] ?? classes[0];
});

async function sendSmartMessageToTelegram() {
  if (!telegramChatId.value) return;
  telegramSending.value = true;
  telegramStatus.value = null;
  try {
    await api.post("/health-alert/telegram-send", {
      chat_id: telegramChatId.value,
      message: smartMessage.value,
    });
    telegramStatus.value = { ok: true, message: "Sent!" };
  } catch (e) {
    telegramStatus.value = { ok: false, message: e.response?.data?.message || "Failed to send." };
  } finally {
    telegramSending.value = false;
  }
}

async function lookupTelegramChatId() {
  telegramLookupLoading.value = true;
  telegramStatus.value = null;
  try {
    const { data } = await api.get("/health-alert/telegram-updates");
    telegramChats.value = data.chats || [];
    if (telegramChats.value.length === 0) {
      telegramStatus.value = { ok: false, message: "No messages yet — message your bot on Telegram first." };
    }
  } catch (e) {
    telegramStatus.value = { ok: false, message: "Could not reach Telegram." };
  } finally {
    telegramLookupLoading.value = false;
  }
}

onMounted(() => {
  detectLocationAndFetch();

  // Keep the card "live" the same way the dashboard's other AQI cards are —
  // re-poll on an interval rather than only ever fetching once on load.
  smartAlertInterval = setInterval(() => {
    if (lastCoords.value) {
      fetchSmartAlert(lastCoords.value.lat, lastCoords.value.lon);
    }
  }, SMART_ALERT_REFRESH_MS);

  const stored = localStorage.getItem("aqiHealthMessages");
  if (stored) {
    const loaded = JSON.parse(stored);
    messages.value = {
      good: { ...defaultMessages.good, ...(loaded.good || {}) },
      moderate: { ...defaultMessages.moderate, ...(loaded.moderate || {}) },
      unhealthySensitive: { ...defaultMessages.unhealthySensitive, ...(loaded.unhealthySensitive || {}) },
      unhealthy: { ...defaultMessages.unhealthy, ...(loaded.unhealthy || {}) },
      veryUnhealthy: { ...defaultMessages.veryUnhealthy, ...(loaded.veryUnhealthy || {}) },
      hazardous: { ...defaultMessages.hazardous, ...(loaded.hazardous || {}) },
    };
  }
});

onUnmounted(() => {
  if (smartAlertInterval) clearInterval(smartAlertInterval);
});

function saveMessages() {
  localStorage.setItem("aqiHealthMessages", JSON.stringify(messages.value));
  saved.value = true;
  setTimeout(() => (saved.value = false), 3000);
}

function openPreview() {
  showModal.value = true;
}

function resetDefaults() {
  messages.value = JSON.parse(JSON.stringify(defaultMessages));
  localStorage.setItem("aqiHealthMessages", JSON.stringify(defaultMessages));
}

function getAQILabel(key) {
  const labels = {
    good: "Good (0-50 AQI)",
    moderate: "Moderate (51-100 AQI)",
    unhealthySensitive: "Unhealthy for Sensitive Groups (101-150 AQI)",
    unhealthy: "Unhealthy (151-200 AQI)",
    veryUnhealthy: "Very Unhealthy (201-300 AQI)",
    hazardous: "Hazardous (301+ AQI)"
  };
  return labels[key] || key;
}

function getStatusLabel(key) {
  const labels = {
    good: "Good",
    moderate: "Moderate",
    unhealthySensitive: "USG",
    unhealthy: "Unhealthy",
    veryUnhealthy: "Very Unhealthy",
    hazardous: "Hazardous"
  };
  return labels[key] || key;
}

const statusIcons = {
  good: aqiGoodImg,
  moderate: aqiModerateImg,
  unhealthySensitive: aqiUnhealthySensitiveImg,
  unhealthy: aqiUnhealthyImg,
  veryUnhealthy: aqiSevereImg,
  hazardous: aqiHazardousImg,
};

function getStatusIcon(key) {
  return statusIcons[key] || aqiGoodImg;
}

function getStatusRange(key) {
  const ranges = {
    good: "AQI 0-50",
    moderate: "AQI 51-100",
    unhealthySensitive: "AQI 101-150",
    unhealthy: "AQI 151-200",
    veryUnhealthy: "AQI 201-300",
    hazardous: "AQI 301+",
  };
  return ranges[key] || "";
}


function getStatusTextClass(key) {
  const classes = {
    good: "text-green-600",
    moderate: "text-yellow-600",
    unhealthySensitive: "text-orange-600",
    unhealthy: "text-red-600",
    veryUnhealthy: "text-purple-600",
    hazardous: "text-gray-800"
  };
  return classes[key] || "";
}

function getStatusDotClass(key) {
  const classes = {
    good: "bg-green-500",
    moderate: "bg-yellow-500",
    unhealthySensitive: "bg-orange-500",
    unhealthy: "bg-red-500",
    veryUnhealthy: "bg-purple-500",
    hazardous: "bg-gray-800"
  };
  return classes[key] || "";
}

function getPreviewCardClass(key) {
  const classes = {
    good: "bg-green-50",
    moderate: "bg-yellow-50",
    unhealthySensitive: "bg-orange-50",
    unhealthy: "bg-red-50",
    veryUnhealthy: "bg-purple-50",
    hazardous: "bg-gray-100"
  };
  return classes[key] || "";
}
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

.modal-enter-active, .modal-leave-active {
  transition: all 0.3s ease;
}
.modal-enter-from, .modal-leave-to {
  opacity: 0;
  transform: scale(0.9);
}
</style>
