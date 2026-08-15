<template>
  <div class="w-full">
    <div class="w-full space-y-4">
      <!-- Stat Cards -->
      <div v-if="newsLoading" class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div v-for="i in 4" :key="i" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
          <div class="flex items-center justify-between">
            <div class="min-w-0 space-y-2">
              <Skeleton class="h-3 w-16" />
              <Skeleton class="h-5 w-8" />
            </div>
            <Skeleton class="w-9 h-9 rounded-lg flex-shrink-0" />
          </div>
          <div class="mt-3 pt-2.5 border-t border-gray-50">
            <Skeleton class="h-2.5 w-28" />
          </div>
        </div>
      </div>
      <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div class="min-w-0">
              <div class="text-xs font-medium text-gray-400 truncate">Total News</div>
              <div class="text-xl font-bold text-gray-900 mt-0.5">{{ newsList.length }}</div>
            </div>
            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 flex-shrink-0">
              <i class="fas fa-newspaper text-sm"></i>
            </div>
          </div>
          <div class="mt-3 pt-2.5 border-t border-gray-50">
            <span class="text-[11px] text-gray-400 truncate">All published articles</span>
          </div>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div class="min-w-0">
              <div class="text-xs font-medium text-gray-400 truncate">Categories</div>
              <div class="text-xl font-bold text-gray-900 mt-0.5">{{ categories.length }}</div>
            </div>
            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 flex-shrink-0">
              <i class="fas fa-tags text-sm"></i>
            </div>
          </div>
          <div class="mt-3 pt-2.5 border-t border-gray-50">
            <span class="text-[11px] text-gray-400 truncate">Active categories</span>
          </div>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div class="min-w-0">
              <div class="text-xs font-medium text-gray-400 truncate">With Video</div>
              <div class="text-xl font-bold text-gray-900 mt-0.5">{{ withVideoCount }}</div>
            </div>
            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 flex-shrink-0">
              <i class="fas fa-video text-sm"></i>
            </div>
          </div>
          <div class="mt-3 pt-2.5 border-t border-gray-50">
            <span class="text-[11px] text-gray-400 truncate">Have a video link</span>
          </div>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div class="min-w-0">
              <div class="text-xs font-medium text-gray-400 truncate">With Media</div>
              <div class="text-xl font-bold text-gray-900 mt-0.5">{{ withMediaCount }}</div>
            </div>
            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 flex-shrink-0">
              <i class="fas fa-image text-sm"></i>
            </div>
          </div>
          <div class="mt-3 pt-2.5 border-t border-gray-50">
            <span class="text-[11px] text-gray-400 truncate">Have images/video attached</span>
          </div>
        </div>
      </div>

      <!-- Page actions, kept outside the table card -->
      <div v-if="newsLoading" class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="flex flex-wrap items-center gap-2">
          <Skeleton class="h-9 w-32 rounded-lg" />
          <Skeleton class="h-9 w-40 rounded-lg" />
        </div>
        <Skeleton class="h-9 w-28 rounded-lg" />
      </div>
      <div v-else class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div class="flex flex-wrap items-center gap-2">
          <AppDropdown
            v-model="filterCategory"
            :options="filterOptions"
            placeholder="All Categories"
            :lead-icon="IconTags"
            light
          />

          <button
            @click="$router.push('/categories')"
            class="flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50 dark:bg-white/5 dark:border-white/10 dark:text-gray-300 dark:hover:bg-white/10 transition-colors"
          >
            <i class="fas fa-tags text-sm"></i> Manage Categories
          </button>
        </div>

        <button
          @click="showNewsForm = !showNewsForm"
          class="flex items-center gap-1.5 px-3 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800"
        >
          <i class="fas fa-plus text-sm"></i> Create News
        </button>
      </div>

      <!-- Card -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <!-- Toolbar -->
        <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-100">
          <div v-if="newsLoading" class="flex items-center gap-2">
            <Skeleton class="h-4 w-20" />
          </div>
          <div v-else class="flex flex-wrap items-center gap-2">
            <span class="text-sm font-semibold text-gray-900">All news</span>
            <span class="text-sm text-gray-400">{{ newsList.length }}</span>
          </div>

          <Skeleton v-if="newsLoading" class="h-9 w-64 rounded-lg" />
          <div v-else class="flex flex-wrap items-center gap-2">
            <div class="relative">
              <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
              <input
                v-model="searchTerm"
                type="text"
                placeholder="Search by caption or number"
                class="pl-9 pr-9 h-9 rounded-lg text-sm w-64 text-gray-700 placeholder-gray-400 bg-gray-50 focus:outline-none focus:bg-gray-100"
              />
              <svg
                class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M4 17v-10l7 10v-10" />
                <path d="M15 17h5" />
                <path d="M15 10a2.5 3 0 1 0 5 0a2.5 3 0 1 0 -5 0" />
              </svg>
            </div>
          </div>
        </div>

        <!-- Mobile: stacked cards instead of a horizontally-scrolling table -->
        <div class="md:hidden space-y-3 p-3">
          <template v-if="newsLoading">
            <div v-for="i in 5" :key="i" class="bg-white border border-gray-100 rounded-xl p-4 space-y-2.5">
              <div class="flex items-start justify-between gap-2">
                <div class="flex-1 space-y-1.5">
                  <Skeleton class="h-3.5 w-40" />
                  <Skeleton class="h-3 w-24" />
                </div>
                <Skeleton class="w-6 h-6 rounded-lg flex-shrink-0" />
              </div>
              <Skeleton class="w-24 h-24 rounded-md" />
              <Skeleton class="h-3 w-14" />
            </div>
          </template>
          <template v-else-if="paginatedNews.length">
            <div
              v-for="n in paginatedNews"
              :key="n.id"
              class="bg-white border border-gray-100 rounded-xl p-4"
            >
              <div class="flex items-start justify-between gap-2 mb-3">
                <div class="min-w-0">
                  <div class="text-xs text-gray-400">N° {{ n.globalIndex }}</div>
                  <div class="text-sm font-semibold text-gray-900">{{ n.caption }}</div>
                </div>
                <button
                  @click="toggleDropdown(n, $event)"
                  class="dropdown-button p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 flex-shrink-0"
                  aria-haspopup="true"
                  :aria-expanded="dropdownRow === n"
                >
                  <IconDotsVertical class="w-4 h-4" />
                </button>
              </div>
              <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between">
                  <span class="text-gray-400">Category</span>
                  <span class="text-gray-600">{{ n.category?.name ?? "—" }}</span>
                </div>
                <div v-if="n.media && n.media.length" class="flex items-center justify-between gap-2">
                  <span class="text-gray-400">Media</span>
                  <div class="flex flex-wrap gap-2 justify-end">
                    <template v-for="(path, i) in n.media" :key="i">
                      <video
                        v-if="path.match(/\.(mp4|webm)$/)"
                        :src="n.media_urls[i]"
                        controls
                        class="w-16 h-16 object-cover rounded-md"
                      ></video>
                      <img
                        v-else
                        :src="n.media_urls[i]"
                        class="w-16 h-16 object-cover rounded-md"
                      />
                    </template>
                  </div>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-gray-400">Video Link</span>
                  <a v-if="n.video_link" :href="n.video_link" target="_blank" class="text-blue-600 underline">Watch</a>
                  <span v-else class="text-gray-600">—</span>
                </div>
              </div>
            </div>
          </template>
          <div v-else class="p-6 text-center text-gray-500 text-sm">No news items available.</div>
        </div>

        <!-- Table -->
        <div class="hidden md:block overflow-x-auto max-h-[600px] overflow-y-auto">
          <table v-if="newsLoading" class="min-w-full">
            <tbody>
              <tr v-for="i in 5" :key="i" class="border-b border-gray-50 last:border-b-0">
                <td class="px-4 py-3"><Skeleton class="h-4 w-6" /></td>
                <td class="px-4 py-3"><Skeleton class="h-4 w-40" /></td>
                <td class="px-4 py-3"><Skeleton class="h-4 w-24" /></td>
                <td class="px-4 py-3"><Skeleton class="w-24 h-24 rounded-md" /></td>
                <td class="px-4 py-3"><Skeleton class="h-4 w-14" /></td>
                <td class="px-4 py-3 text-right"><Skeleton class="h-6 w-6 rounded-lg ml-auto" /></td>
              </tr>
            </tbody>
          </table>
          <table v-else-if="paginatedNews.length" class="min-w-full">
            <thead class="sticky top-0 z-10 bg-white">
              <tr class="border-b border-gray-100">
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">N°</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">Caption</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">Category</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">Media</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">Video Link</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-400">Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(n, index) in paginatedNews" :key="n.id" class="hover:bg-gray-50/60">
                <td class="px-4 py-3 text-sm text-gray-600">{{ n.globalIndex }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ n.caption }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ n.category?.name ?? "—" }}</td>
                <td class="px-4 py-3">
                  <div class="flex flex-wrap gap-2">
                    <template v-for="(path, i) in n.media" :key="i">
                      <video
                        v-if="path.match(/\.(mp4|webm)$/)"
                        :src="n.media_urls[i]"
                        controls
                        class="w-24 h-24 object-cover rounded-md"
                      ></video>
                      <img
                        v-else
                        :src="n.media_urls[i]"
                        class="w-24 h-24 object-cover rounded-md"
                      />
                    </template>
                  </div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">
                  <a v-if="n.video_link" :href="n.video_link" target="_blank" class="text-blue-600 underline">Watch</a>
                  <span v-else>—</span>
                </td>
                <td class="px-4 py-3 text-right">
                  <button
                    @click="toggleDropdown(n, $event)"
                    class="dropdown-button p-1.5 rounded-lg hover:bg-gray-100 text-gray-400"
                    aria-haspopup="true"
                    :aria-expanded="dropdownRow === n"
                  >
                    <IconDotsVertical class="w-4 h-4" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-else class="p-6 text-center text-gray-500 text-sm">No news items available.</div>
        </div>

        <!-- Pagination -->
        <div v-if="newsLoading" class="flex justify-between items-center p-4 border-t border-gray-100">
          <Skeleton class="h-4 w-24" />
          <div class="flex items-center space-x-1">
            <Skeleton class="h-7 w-7 rounded-full" />
            <Skeleton class="h-7 w-7 rounded-full" />
            <Skeleton class="h-7 w-7 rounded-full" />
            <Skeleton class="h-7 w-7 rounded-full" />
          </div>
        </div>
        <div v-else class="flex justify-between items-center p-4 border-t border-gray-100">
          <div class="text-sm text-gray-500">Page {{ currentPage }} of {{ totalPages || 1 }}</div>
          <div class="flex items-center space-x-1">
            <button
              @click="prevPage"
              :disabled="currentPage === 1"
              class="px-3 py-1 rounded-full border border-gray-200 text-gray-600 hover:bg-gray-100 disabled:opacity-50"
            >
              «
            </button>
            <template v-for="p in paginationRange" :key="p">
              <button
                v-if="p !== '...'"
                @click="currentPage = p"
                :class="['px-3 py-1 rounded-full border', currentPage === p ? 'bg-gray-900 text-white border-gray-900' : 'border-gray-200 text-gray-600 hover:bg-gray-100']"
              >
                {{ p }}
              </button>
              <span v-else class="px-3 py-1 text-gray-400">...</span>
            </template>
            <button
              @click="nextPage"
              :disabled="currentPage === totalPages"
              class="px-3 py-1 rounded-full border border-gray-200 text-gray-600 hover:bg-gray-100 disabled:opacity-50"
            >
              »
            </button>
          </div>
        </div>
      </div>

    <!-- Create News Modal -->
    <div
      v-if="showNewsForm"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-2xl p-6 w-full max-w-md relative">
        <button
          @click="showNewsForm = false"
          class="absolute top-4 right-4 text-gray-400 hover:text-gray-700"
        >
          <i class="fas fa-times"></i>
        </button>
        <h2 class="text-xl font-semibold mb-4 text-gray-900">Create News</h2>

        <form @submit.prevent="createNews" class="space-y-3 text-sm">
          <input
            v-model="caption"
            placeholder="Caption"
            class="w-full bg-gray-100 border-0 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-400"
          />

          <AppDropdown
            v-model="selectedCategory"
            :options="categoryOptions"
            placeholder="-- Choose Category --"
            :lead-icon="IconTags"
            light
          />
          <label class="flex flex-col items-center justify-center gap-1.5 w-full py-6 px-3 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:bg-gray-100 hover:border-gray-400 transition-colors">
            <IconCloudUpload :size="20" class="text-gray-400" />
            <span class="text-xs text-gray-500 text-center">
              <span class="text-gray-700 font-medium">Click to upload</span> or drag and drop
            </span>
            <input
              type="file"
              multiple
              @change="onSelectFiles"
              class="hidden"
              accept="image/*,video/*"
            />
          </label>

          <input
            v-model="videoLink"
            type="text"
            placeholder="Video link (optional)"
            class="w-full bg-gray-100 border-0 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-400"
          />

          <div class="flex justify-end gap-2 mt-4">
            <button
              type="button"
              @click="showNewsForm = false"
              class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200 text-gray-700"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800"
            >
              Post News
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit News Modal -->
    <div
      v-if="showEditForm"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 px-4"
      @click.self="closeEditForm"
    >
      <div class="bg-white rounded-2xl p-6 w-full max-w-md relative max-h-[90vh] overflow-y-auto">
        <button
          @click="closeEditForm"
          class="absolute top-4 right-4 text-gray-400 hover:text-gray-700"
        >
          <i class="fas fa-times"></i>
        </button>
        <h2 class="text-xl font-semibold mb-4 text-gray-900">Edit News</h2>

        <form @submit.prevent="saveEditNews" class="space-y-3 text-sm">
          <input
            v-model="editCaption"
            placeholder="Caption"
            class="w-full bg-gray-100 border-0 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-400"
          />

          <AppDropdown
            v-model="editCategory"
            :options="categoryOptions"
            placeholder="-- Choose Category --"
            :lead-icon="IconTags"
            light
          />

          <div v-if="editingNews?.media_urls?.length" class="space-y-1.5">
            <div class="text-xs font-medium text-gray-500">Current media</div>
            <div class="flex flex-wrap gap-2">
              <div
                v-for="(url, i) in editingNews.media_urls"
                :key="i"
                class="relative w-20 h-20 rounded-lg overflow-hidden border border-gray-200 transition-opacity"
                :class="{ 'opacity-40': !editKeepMedia[i] }"
              >
                <video v-if="url.match(/\.(mp4|webm)$/)" :src="url" class="w-full h-full object-cover"></video>
                <img v-else :src="url" class="w-full h-full object-cover" />
                <button
                  type="button"
                  title="Remove on save"
                  @click="editKeepMedia[i] = !editKeepMedia[i]"
                  class="absolute top-1 right-1 w-5 h-5 rounded-full flex items-center justify-center text-white text-[10px]"
                  :class="editKeepMedia[i] ? 'bg-black/50 hover:bg-black/70' : 'bg-red-500 hover:bg-red-600'"
                >
                  <i :class="editKeepMedia[i] ? 'fas fa-check' : 'fas fa-xmark'"></i>
                </button>
              </div>
            </div>
          </div>

          <label class="flex flex-col items-center justify-center gap-1.5 w-full py-6 px-3 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:bg-gray-100 hover:border-gray-400 transition-colors">
            <IconCloudUpload :size="20" class="text-gray-400" />
            <span class="text-xs text-gray-500 text-center">
              <span class="text-gray-700 font-medium">Click to upload</span> or drag and drop
            </span>
            <input
              type="file"
              multiple
              @change="onSelectEditFiles"
              class="hidden"
              accept="image/*,video/*"
            />
          </label>
          <div v-if="editNewFiles.length" class="text-xs text-gray-500">{{ editNewFiles.length }} new file(s) selected</div>

          <input
            v-model="editVideoLink"
            type="text"
            placeholder="Video link (optional)"
            class="w-full bg-gray-100 border-0 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-gray-400"
          />

          <div class="flex justify-end gap-2 mt-4">
            <button
              type="button"
              @click="closeEditForm"
              class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200 text-gray-700"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800"
            >
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
    </div>

    <!-- Row action menu (teleported so it displays over the table) -->
    <Teleport to="body">
      <div
        v-if="dropdownRow"
        class="dropdown-menu fixed w-40 bg-white border border-gray-100 rounded-lg shadow-lg z-[9999] py-1"
        :style="{ left: dropdownPos.left + 'px', top: dropdownPos.top + 'px' }"
      >
        <button
          @click="editNews(dropdownRow); closeDropdown();"
          class="flex items-center w-full text-left px-3 py-2 text-sm text-indigo-600 hover:text-indigo-800 transition-colors"
        >
          <IconPencil class="w-4 h-4 mr-2 text-indigo-400" /> Edit
        </button>
        <button
          @click="deleteNews(dropdownRow.id); closeDropdown();"
          class="flex items-center w-full text-left px-3 py-2 text-sm text-red-600 hover:text-red-800 transition-colors"
        >
          <IconTrash class="w-4 h-4 mr-2 text-red-400" /> Delete
        </button>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import Swal from "sweetalert2";
import api from "@/services/api";
import AppDropdown from "../components/AppDropdown.vue";
import Skeleton from "../components/Skeleton.vue";
import { IconTags, IconCloudUpload, IconDotsVertical, IconPencil, IconTrash } from "@tabler/icons-vue";
import aqiGoodImg from "@/assets/images/svg/aqi-good-level.webp";
import aqiModerateImg from "@/assets/images/svg/aqi-moderate-level.webp";
import aqiHazardousImg from "@/assets/images/svg/aqi-hazardous-level.webp";

// Shared alert styling so every popup in this page reads as one flat,
// "clear and clean" card instead of SweetAlert's default look — reusing the
// same AQI-level illustrations as the rest of the app instead of generic icons.
const alertPopupClass = {
  popup: "rounded-2xl",
  title: "!text-lg !font-semibold !text-gray-900",
  htmlContainer: "!text-sm !text-gray-500",
  // The AQI illustrations are tall portrait art, not square — only the
  // height is constrained above, and object-fit keeps them undistorted.
  image: "!w-auto !object-contain",
};

function notify(type, title, text) {
  const image = type === "success" ? aqiGoodImg : aqiHazardousImg;
  return Swal.fire({
    title,
    text,
    imageUrl: image,
    imageHeight: 88,
    imageAlt: type,
    confirmButtonText: "OK",
    buttonsStyling: false,
    customClass: {
      ...alertPopupClass,
      confirmButton: "px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800 text-sm font-medium",
    },
  });
}

function confirmDelete(title, text) {
  return Swal.fire({
    title,
    text,
    imageUrl: aqiModerateImg,
    imageHeight: 88,
    imageAlt: "warning",
    showCancelButton: true,
    confirmButtonText: "Yes, delete it!",
    cancelButtonText: "Cancel",
    buttonsStyling: false,
    customClass: {
      ...alertPopupClass,
      actions: "!gap-2",
      confirmButton: "px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 text-sm font-medium",
      cancelButton: "px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium",
    },
  });
}

const caption = ref("");
const selectedCategory = ref("");
const files = ref([]);
const videoLink = ref("");
const showNewsForm = ref(false);

const categories = ref([]);
const newsList = ref([]);
const newsLoading = ref(true);

const filterCategory = ref("");
const searchTerm = ref("");

const categoryOptions = computed(() =>
  categories.value.map((c) => ({ value: c.id, label: c.name }))
);

const filterOptions = computed(() => [
  { value: "", label: "All Categories" },
  ...categoryOptions.value,
]);

const withVideoCount = computed(() => newsList.value.filter((n) => n.video_link).length);
const withMediaCount = computed(() => newsList.value.filter((n) => n.media && n.media.length).length);

const currentPage = ref(1);
const perPage = ref(5);

async function fetchCategories() {
  try {
    const { data } = await api.get("/categories");
    categories.value = data;
  } catch (err) {
    console.error(err);
  }
}

async function fetchNews() {
  newsLoading.value = true;
  try {
    const { data } = await api.get("/news");
    newsList.value = data;
  } catch (err) {
    console.error(err);
  } finally {
    newsLoading.value = false;
  }
}

function onSelectFiles(e) {
  files.value = Array.from(e.target.files);
}

// ------------------
// Create News
// ------------------
async function createNews() {
  if (!selectedCategory.value) return notify("error", "Error", "Please choose a category");
  if (!caption.value.trim()) return notify("error", "Error", "Caption is required");

  const fd = new FormData();
  fd.append("caption", caption.value.trim());
  fd.append("category_id", selectedCategory.value);
  files.value.forEach(f => fd.append("media[]", f));
  if (videoLink.value?.trim()) fd.append("video_link", videoLink.value.trim());

  try {
    await api.post("/news", fd);
    caption.value = "";
    selectedCategory.value = "";
    files.value = [];
    videoLink.value = "";
    showNewsForm.value = false;
    await fetchNews();
    notify("success", "Success", "News created!");
  } catch (err) {
    notify("error", "Error", "Failed to create news");
  }
}

// ------------------
// Edit News
// ------------------
const showEditForm = ref(false);
const editingNews = ref(null);
const editCaption = ref("");
const editCategory = ref("");
const editVideoLink = ref("");
const editNewFiles = ref([]);
const editKeepMedia = ref([]); // one boolean per editingNews.media[i] — false = drop on save

function editNews(news) {
  editingNews.value = news;
  editCaption.value = news.caption;
  editCategory.value = news.category_id;
  editVideoLink.value = news.video_link ?? "";
  editNewFiles.value = [];
  editKeepMedia.value = (news.media ?? []).map(() => true);
  showEditForm.value = true;
}

function closeEditForm() {
  showEditForm.value = false;
  editingNews.value = null;
}

function onSelectEditFiles(e) {
  editNewFiles.value = Array.from(e.target.files);
}

async function saveEditNews() {
  if (!editCaption.value.trim()) return notify("error", "Error", "Caption is required");
  if (!editCategory.value) return notify("error", "Error", "Please choose a category");

  const fd = new FormData();
  fd.append("_method", "PATCH");
  fd.append("caption", editCaption.value.trim());
  fd.append("category_id", editCategory.value);
  fd.append("video_link", editVideoLink.value?.trim() ?? "");
  (editingNews.value.media ?? []).forEach((path, i) => {
    if (editKeepMedia.value[i]) fd.append("keep[]", path);
  });
  editNewFiles.value.forEach(f => fd.append("media[]", f));

  try {
    await api.post(`/news/${editingNews.value.id}`, fd, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    closeEditForm();
    await fetchNews();
    notify("success", "Success", "News updated!");
  } catch (err) {
    console.error(err);
    notify("error", "Error", "Failed to update news");
  }
}


// ------------------
// Delete News
// ------------------
async function deleteNews(id) {
  const confirm = await confirmDelete("Are you sure?", "This action cannot be undone!");
  if (confirm.isConfirmed) {
    try {
      await api.delete(`/news/${id}`);
      await fetchNews();
      notify("success", "Deleted!", "News has been deleted.");
    } catch (err) {
      notify("error", "Error", "Failed to delete news");
    }
  }
}

const filteredNews = computed(() => {
  let list = [...newsList.value];
  if (filterCategory.value) list = list.filter(n => n.category_id === filterCategory.value);
  list = list.map((n, idx) => ({ ...n, globalIndex: idx + 1 }));
  if (searchTerm.value.trim()) {
    const term = searchTerm.value.toLowerCase();
    list = list.filter(n => n.caption.toLowerCase().includes(term) || String(n.globalIndex).includes(term));
  }
  return list;
});

const totalPages = computed(() => Math.ceil(filteredNews.value.length / perPage.value));
const paginatedNews = computed(() =>
  filteredNews.value.slice((currentPage.value - 1) * perPage.value, currentPage.value * perPage.value)
);

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

function prevPage() { if (currentPage.value > 1) currentPage.value--; }
function nextPage() { if (currentPage.value < totalPages.value) currentPage.value++; }

// Row action menu (three-dot)
const dropdownRow = ref(null);
const dropdownPos = ref({ left: 0, top: 0 });

const toggleDropdown = (row, e) => {
  if (dropdownRow.value === row) {
    dropdownRow.value = null;
    return;
  }
  dropdownRow.value = row;
  const rect = e.currentTarget.getBoundingClientRect();
  dropdownPos.value = {
    left: Math.max(8, rect.right - 160),
    top: rect.bottom + 6,
  };
};

const closeDropdown = () => {
  dropdownRow.value = null;
};

const handleDocClick = (e) => {
  if (!e.target.closest(".dropdown-menu") && !e.target.closest(".dropdown-button")) {
    closeDropdown();
  }
};

onMounted(() => {
  fetchCategories();
  fetchNews();
  document.addEventListener("click", handleDocClick);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleDocClick);
});
</script>
