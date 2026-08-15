<template>
  <div>
    <div class="max-w-7xl mx-auto space-y-8">
      <!-- Top 4 Metric Boxes -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
        <div v-for="i in 4" :key="i" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
          <div class="flex items-center justify-between">
            <div class="min-w-0 space-y-2">
              <Skeleton class="h-3 w-16" />
              <Skeleton class="h-5 w-10" />
            </div>
            <Skeleton class="w-9 h-9 rounded-lg flex-shrink-0" />
          </div>
          <div class="mt-3 pt-2.5 border-t border-gray-50">
            <Skeleton class="h-2.5 w-24" />
          </div>
        </div>
      </div>
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div class="min-w-0">
              <div class="text-xs font-medium text-gray-400 truncate">Total News</div>
              <div class="text-xl font-bold text-gray-900 mt-0.5">{{ totalNews }}</div>
            </div>
            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 flex-shrink-0">
              <i class="fas fa-newspaper text-sm"></i>
            </div>
          </div>
          <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center justify-between">
            <span class="text-[11px] text-gray-400 truncate">Active articles</span>
            <button @click="exportData('total')" class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </button>
          </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div class="min-w-0">
              <div class="text-xs font-medium text-gray-400 truncate">Categories</div>
              <div class="text-xl font-bold text-gray-900 mt-0.5">{{ totalCategories }}</div>
            </div>
            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 flex-shrink-0">
              <i class="fas fa-tags text-sm"></i>
            </div>
          </div>
          <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center justify-between">
            <span class="text-[11px] text-gray-400 truncate">Available topics</span>
            <button @click="exportData('categories')" class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </button>
          </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between gap-2">
            <div class="min-w-0">
              <div class="text-xs font-medium text-gray-400 truncate">Latest Post</div>
              <div class="text-base font-bold text-gray-900 mt-0.5 truncate">{{ latestPost?.caption || "No posts" }}</div>
            </div>
            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 flex-shrink-0">
              <i class="fas fa-clock text-sm"></i>
            </div>
          </div>
          <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center justify-between gap-2">
            <span class="text-[11px] text-gray-400 truncate">{{ formatDate(latestPost?.created_at) }}</span>
            <button @click="exportData('latest')" class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </button>
          </div>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div class="min-w-0">
              <div class="text-xs font-medium text-gray-400 truncate">Total Media</div>
              <div class="text-xl font-bold text-gray-900 mt-0.5">{{ totalMedia }}</div>
            </div>
            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 flex-shrink-0">
              <i class="fas fa-photo-film text-sm"></i>
            </div>
          </div>
          <div class="mt-3 pt-2.5 border-t border-gray-50 flex items-center justify-between">
            <span class="text-[11px] text-gray-400 truncate">Images & videos</span>
            <button @click="exportData('media')" class="text-gray-300 hover:text-gray-500 transition-colors flex-shrink-0">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </button>
          </div>
        </div>
      </div>

      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="i in 6" :key="i" class="bg-slate-600/90 rounded-2xl shadow-sm p-3">
          <Skeleton class="aspect-video rounded-xl bg-slate-500/60" />
          <div class="pt-4 px-1 pb-1 space-y-2">
            <Skeleton class="h-4 w-full bg-slate-500/60" />
            <Skeleton class="h-4 w-2/3 bg-slate-500/60" />
            <div class="flex items-center justify-between mt-3">
              <Skeleton class="h-3 w-16 bg-slate-500/60" />
              <Skeleton class="h-3 w-12 bg-slate-500/60" />
            </div>
          </div>
        </div>
      </div>

      <div v-else-if="!news.length" class="text-center py-20">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="text-gray-500">No news articles available</p>
      </div>

      <div v-else>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="article in pagedNews"
            :key="article.id"
            class="bg-slate-600/90 rounded-2xl shadow-sm hover:shadow-md transition-shadow cursor-pointer p-3"
            @click="viewArticle(article)"
          >
            <div class="relative aspect-video rounded-xl overflow-hidden bg-slate-700">
              <img
                v-if="articleThumb(article)"
                :src="articleThumb(article)"
                :alt="article.caption"
                class="w-full h-full object-cover"
                loading="lazy"
                @error="handleImageError(article)"
              />
              <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
              <!-- Video badge overlay: shown over whatever thumbnail loaded
                   (YouTube-cached image or an uploaded still) so it always
                   reads as playable, not just when no image is available. -->
              <div v-if="article.video_link" class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="w-11 h-11 rounded-full bg-black/50 flex items-center justify-center backdrop-blur-sm">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-white ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M8 5v14l11-7z" />
                  </svg>
                </div>
              </div>
              <span class="absolute top-2 left-2 px-2 py-1 text-[10px] font-semibold rounded-full bg-black/50 text-white backdrop-blur-sm">
                {{ article.category?.name || 'Uncategorized' }}
              </span>
            </div>
            <div class="pt-4 px-1 pb-1">
              <h3 class="font-bold text-white text-base leading-snug line-clamp-2 min-h-[2.75rem]">{{ article.caption }}</h3>
              <div class="flex items-center justify-between mt-3 text-xs text-slate-300">
                <span class="truncate">{{ article.category?.name || 'AQI Team' }}</span>
                <span class="flex-shrink-0 ml-2">{{ formatShortDate(article.created_at) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div v-if="totalPages > 1" class="flex items-center justify-center gap-3 mt-8">
          <button
            type="button"
            title="Previous"
            :disabled="newsPage === 0"
            @click="newsPage--"
            class="w-9 h-9 flex items-center justify-center rounded-full bg-slate-200 text-slate-500 disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-300 transition-colors"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <button
            type="button"
            title="Next"
            :disabled="newsPage >= totalPages - 1"
            @click="newsPage++"
            class="w-9 h-9 flex items-center justify-center rounded-full bg-slate-800 text-white disabled:opacity-40 disabled:cursor-not-allowed hover:bg-slate-700 transition-colors"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
          </button>
        </div>
      </div>
    </div>

    <!-- View Article -->
    <transition name="fade">
      <div v-if="selectedArticle" class="fixed inset-0 bg-black/70 flex items-center justify-center z-50 px-4" @click.self="closeArticle">
        <div class="bg-white rounded-2xl max-w-2xl w-full relative shadow-lg overflow-hidden max-h-[90vh] flex flex-col">
          <!-- Sits in the non-scrolling outer wrapper so it stays put and
               clickable even when the description below is long enough to scroll. -->
          <button
            class="absolute top-3 right-3 bg-white border border-gray-100 hover:bg-gray-50 text-gray-500 rounded-full p-1.5 shadow-sm z-10"
            @click="closeArticle"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>

          <div class="overflow-y-auto">
            <div class="aspect-video bg-slate-100">
              <iframe
                v-if="youtubeEmbedUrl(selectedArticle.video_link)"
                :src="youtubeEmbedUrl(selectedArticle.video_link)"
                class="w-full h-full"
                frameborder="0"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen
              ></iframe>
              <img
                v-else-if="articleThumb(selectedArticle)"
                :src="articleThumb(selectedArticle)"
                :alt="selectedArticle.caption"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center text-slate-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
            </div>

            <div class="p-6">
              <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 mb-2">
                {{ selectedArticle.category?.name || 'Uncategorized' }}
              </span>
              <h3 class="text-xl font-bold text-slate-900">{{ selectedArticle.caption }}</h3>
              <p class="text-xs text-gray-500 mt-1">{{ formatDate(selectedArticle.created_at) }}</p>
              <p class="text-sm text-gray-600 mt-4 whitespace-pre-line">
                {{ selectedArticle.description || 'No description available' }}
              </p>
              <a
                v-if="selectedArticle.video_link && !youtubeEmbedUrl(selectedArticle.video_link)"
                :href="selectedArticle.video_link"
                target="_blank"
                rel="noopener"
                class="inline-block mt-3 text-sm text-blue-600 underline"
              >Watch video</a>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import api from "@/services/api";
import Skeleton from "@/components/Skeleton.vue";

const PAGE_SIZE = 3;

const news = ref([]);
const categories = ref([]);
const loading = ref(true);
const newsPage = ref(0);
const brokenImages = ref(new Set());
const selectedArticle = ref(null);

const totalPages = computed(() => Math.ceil(news.value.length / PAGE_SIZE));
const pagedNews = computed(() => {
  const start = newsPage.value * PAGE_SIZE;
  return news.value.slice(start, start + PAGE_SIZE);
});

const totalNews = computed(() => news.value.length);
const totalCategories = computed(() => categories.value.length);
const totalMedia = computed(() => news.value.reduce((total, article) => total + (article.media_urls?.length || 0), 0));
const latestPost = computed(() => {
  if (!news.value.length) return null;
  return news.value.reduce((latest, current) =>
    new Date(current.created_at) > new Date(latest.created_at) ? current : latest
  );
});

function viewArticle(article) {
  selectedArticle.value = article;
}
function closeArticle() {
  selectedArticle.value = null;
}

// Clamp the page back into range if the list shrinks (e.g. after a refresh).
watch(totalPages, (pages) => {
  if (newsPage.value > 0 && newsPage.value >= pages) {
    newsPage.value = Math.max(0, pages - 1);
  }
});

function isImage(url) {
  if (!url) return false;
  const lowerUrl = url.toLowerCase();
  if (lowerUrl.startsWith("data:image/")) return true;
  const imageExtensions = [".jpg", ".jpeg", ".png", ".gif", ".bmp", ".tiff", ".tif", ".webp", ".svg", ".ico", ".heic", ".heif", ".avif", ".jxl"];
  if (imageExtensions.some((ext) => lowerUrl.includes(ext))) return true;
  if (lowerUrl.includes("image/") || /format=(jpg|jpeg|png|gif|bmp|tiff|webp|svg|ico)/.test(lowerUrl)) return true;
  if (/\/img\/|\/image\/|\/photo\/|\/media\/|\/pictures\/|\/photos\//.test(lowerUrl)) return true;
  if (/type=image|contenttype=image|mimetype=image|responsecontenttype=image/.test(lowerUrl)) return true;
  if (lowerUrl.includes("cdn") && (lowerUrl.includes("img") || lowerUrl.includes("image") || lowerUrl.includes("photo"))) return true;
  return false;
}

function youtubeVideoId(url) {
  if (!url) return null;
  const patterns = [
    /youtu\.be\/([a-zA-Z0-9_-]{6,})/,
    /youtube\.com\/watch\?v=([a-zA-Z0-9_-]{6,})/,
    /youtube\.com\/embed\/([a-zA-Z0-9_-]{6,})/,
    /youtube\.com\/shorts\/([a-zA-Z0-9_-]{6,})/,
  ];
  for (const pattern of patterns) {
    const match = url.match(pattern);
    if (match) return match[1];
  }
  return null;
}

function youtubeEmbedUrl(url) {
  const id = youtubeVideoId(url);
  return id ? `https://www.youtube.com/embed/${id}` : null;
}

// YouTube serves a thumbnail per video id from its own CDN (i.e. it's
// already cached at the source) — reuse it as the card image for
// video articles that have no uploaded media of their own.
function youtubeThumbnail(url) {
  const id = youtubeVideoId(url);
  return id ? `https://img.youtube.com/vi/${id}/hqdefault.jpg` : null;
}

function articleThumb(article) {
  if (brokenImages.value.has(article.id)) return null;
  const url = article.media_urls?.[0];
  if (url && isImage(url)) return url;
  return youtubeThumbnail(article.video_link);
}

function handleImageError(article) {
  brokenImages.value = new Set(brokenImages.value).add(article.id);
}

function formatShortDate(dateString) {
  if (!dateString) return "Unknown";
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return "Unknown";
  return date.toLocaleDateString("en-GB", { day: "numeric", month: "short", year: "numeric" });
}

function formatDate(dateString) {
  if (!dateString) return "Unknown";
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return "Unknown";
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

function exportData(type) {
  const headers = ["id", "caption", "category_id", "category_name", "link", "created_at", "updated_at"];
  const escapeCsvValue = (value) => {
    if (value == null) return "";
    const str = String(value);
    return /[,"\n]/.test(str) ? `"${str.replace(/"/g, '""')}"` : str;
  };
  const articleRow = (item) =>
    [
      item.id,
      item.caption,
      item.category_id,
      item.category?.name ?? "",
      item.video_link ?? "",
      item.created_at,
      item.updated_at,
    ]
      .map(escapeCsvValue)
      .join(",");

  let rows = [];
  let filename = "";

  if (type === "total") {
    rows = news.value.map(articleRow);
    rows.unshift(headers.join(","));
    filename = "total-news.csv";
  } else if (type === "categories") {
    rows = categories.value.map((c) => [escapeCsvValue(c.id), escapeCsvValue(c.name), escapeCsvValue(c.description)].join(","));
    rows.unshift("id,name,description");
    filename = "categories.csv";
  } else if (type === "latest" && latestPost.value) {
    rows = [articleRow(latestPost.value)];
    rows.unshift(headers.join(","));
    filename = "latest-post.csv";
  } else if (type === "media") {
    news.value.forEach((article) => {
      (article.media_urls || []).forEach((url) => {
        rows.push([escapeCsvValue(article.id), escapeCsvValue(article.caption), escapeCsvValue(url), escapeCsvValue(isImage(url) ? "image" : "video")].join(","));
      });
    });
    rows.unshift("id,caption,url,type");
    filename = "media-files.csv";
  }

  const blob = new Blob(["﻿" + rows.join("\n")], { type: "text/csv;charset=utf-8;" });
  const url = URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url;
  a.download = filename;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
  URL.revokeObjectURL(url);
}

async function fetchNews() {
  try {
    loading.value = true;
    const { data } = await api.get("/news");
    news.value = data;
  } catch (error) {
    console.error("Error fetching news:", error);
  } finally {
    loading.value = false;
  }
}

async function fetchCategories() {
  try {
    const { data } = await api.get("/categories");
    categories.value = data;
  } catch (error) {
    console.error("Error fetching categories:", error);
  }
}

onMounted(async () => {
  await fetchNews();
  await fetchCategories();
  setInterval(fetchNews, 30000);
});
</script>

<style scoped>
.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
img {
  transition: opacity 0.3s ease;
}
</style>
