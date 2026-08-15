<template>
  <div class="w-full">
    <div class="w-full">
      <!-- Card -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <!-- Toolbar -->
        <div class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-100">
          <div v-if="loading" class="flex items-center gap-2">
            <Skeleton class="h-4 w-24" />
          </div>
          <div v-else class="flex items-center gap-2">
            <span class="text-sm font-semibold text-gray-900">All categories</span>
            <span class="text-sm text-gray-400">{{ categories.length }}</span>
          </div>

          <Skeleton v-if="loading" class="h-9 w-40 rounded-lg" />
          <button
            v-else
            @click="openAddForm"
            class="flex items-center gap-1.5 px-3 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800"
          >
            <IconPlus class="w-4 h-4" /> Add New Category
          </button>
        </div>

        <!-- Mobile: stacked cards instead of a horizontally-scrolling table -->
        <div class="md:hidden space-y-3 p-3">
          <template v-if="loading">
            <div v-for="i in 5" :key="i" class="bg-white border border-gray-100 rounded-xl p-4 space-y-2.5">
              <div class="flex items-start justify-between gap-2">
                <Skeleton class="h-3.5 w-32" />
                <Skeleton class="w-6 h-6 rounded-lg flex-shrink-0" />
              </div>
              <Skeleton class="h-3 w-full" />
            </div>
          </template>
          <template v-else>
            <div
              v-for="(c, index) in categories"
              :key="c.id"
              class="bg-white border border-gray-100 rounded-xl p-4"
            >
              <div class="flex items-start justify-between gap-2 mb-3">
                <div class="min-w-0">
                  <div class="text-xs text-gray-400">N° {{ index + 1 }}</div>
                  <div class="text-sm font-semibold text-gray-900 truncate">{{ c.name }}</div>
                </div>
                <button
                  @click="toggleDropdown(c, $event)"
                  class="dropdown-button p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 flex-shrink-0"
                  aria-haspopup="true"
                  :aria-expanded="dropdownRow === c"
                >
                  <IconDotsVertical class="w-4 h-4" />
                </button>
              </div>
              <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between gap-2">
                  <span class="text-gray-400">Description</span>
                  <span class="text-gray-600 text-right">{{ c.description || "—" }}</span>
                </div>
              </div>
            </div>
          </template>
          <div v-if="!loading && categories.length === 0" class="p-6 text-center text-gray-500 text-sm">No categories</div>
        </div>

        <!-- Category Table -->
        <div class="hidden md:block overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">N°</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">Name</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">Description</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-400">Action</th>
              </tr>
            </thead>
            <tbody v-if="loading">
              <tr v-for="i in 5" :key="i" class="border-b border-gray-50 last:border-b-0">
                <td class="px-4 py-3"><Skeleton class="h-3 w-6" /></td>
                <td class="px-4 py-3"><Skeleton class="h-3 w-28" /></td>
                <td class="px-4 py-3"><Skeleton class="h-3 w-40" /></td>
                <td class="px-4 py-3 text-right"><Skeleton class="h-6 w-6 rounded-lg ml-auto" /></td>
              </tr>
            </tbody>
            <tbody v-else>
              <tr v-for="(c, index) in categories" :key="c.id" class="hover:bg-gray-50/60">
                <td class="px-4 py-3 text-sm text-gray-600">{{ index + 1 }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ c.name }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ c.description || "—" }}</td>
                <td class="px-4 py-3 text-right">
                  <button
                    @click="toggleDropdown(c, $event)"
                    class="dropdown-button p-1.5 rounded-lg hover:bg-gray-100 text-gray-400"
                    aria-haspopup="true"
                    :aria-expanded="dropdownRow === c"
                  >
                    <IconDotsVertical class="w-4 h-4" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Add Category Modal -->
    <div
      v-if="showForm"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-2xl p-6 w-full max-w-md relative">
        <button
          @click="closeAddForm"
          class="absolute top-4 right-4 text-gray-400 hover:text-gray-700"
        >
          <IconX class="w-5 h-5" />
        </button>
        <h2 class="text-xl font-semibold mb-4 text-gray-900">Add New Category</h2>
        <form @submit.prevent="createCategory()" class="space-y-3 text-sm">
          <input
            v-model="catName"
            placeholder="Category Name"
            class="w-full bg-gray-100 border-0 rounded-lg px-3 py-2.5"
            required
          />
          <input
            v-model="catDesc"
            placeholder="Description"
            class="w-full bg-gray-100 border-0 rounded-lg px-3 py-2.5"
          />
          <div class="flex justify-end gap-2 pt-2">
            <button
              type="button"
              @click="closeAddForm"
              class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200 text-gray-700"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800"
            >
              Add Category
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit Category Modal -->
    <div
      v-if="showEditForm"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 px-4"
      @click.self="closeEditForm"
    >
      <div class="bg-white rounded-2xl p-6 w-full max-w-md relative">
        <button
          @click="closeEditForm"
          class="absolute top-4 right-4 text-gray-400 hover:text-gray-700"
        >
          <IconX class="w-5 h-5" />
        </button>
        <h2 class="text-xl font-semibold mb-4 text-gray-900">Edit Category</h2>
        <form @submit.prevent="saveEditCategory()" class="space-y-3 text-sm">
          <input
            v-model="editCatName"
            placeholder="Category Name"
            class="w-full bg-gray-100 border-0 rounded-lg px-3 py-2.5"
            required
          />
          <input
            v-model="editCatDesc"
            placeholder="Description"
            class="w-full bg-gray-100 border-0 rounded-lg px-3 py-2.5"
          />
          <div class="flex justify-end gap-2 pt-2">
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

    <!-- Row action menu (teleported so it displays over the table) -->
    <Teleport to="body">
      <div
        v-if="dropdownRow"
        class="dropdown-menu fixed w-40 bg-white border border-gray-100 rounded-lg shadow-lg z-[9999] py-1"
        :style="{ left: dropdownPos.left + 'px', top: dropdownPos.top + 'px' }"
      >
        <button
          @click="openEditCategory(dropdownRow); closeDropdown();"
          class="flex items-center w-full text-left px-3 py-2 text-sm text-indigo-600 hover:text-indigo-800 transition-colors"
        >
          <IconPencil class="w-4 h-4 mr-2 text-indigo-400" /> Edit
        </button>
        <button
          @click="deleteCategory(dropdownRow.id); closeDropdown();"
          class="flex items-center w-full text-left px-3 py-2 text-sm text-red-600 hover:text-red-800 transition-colors"
        >
          <IconTrash class="w-4 h-4 mr-2 text-red-400" /> Delete
        </button>
      </div>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from "vue";
import Swal from "sweetalert2";
import api from "@/services/api";
import Skeleton from "@/components/Skeleton.vue";
import { IconDotsVertical, IconPencil, IconTrash, IconPlus, IconX } from "@tabler/icons-vue";
import aqiGoodImg from "@/assets/images/svg/aqi-good-level.webp";
import aqiModerateImg from "@/assets/images/svg/aqi-moderate-level.webp";
import aqiHazardousImg from "@/assets/images/svg/aqi-hazardous-level.webp";

// Shared alert styling so every popup on this page reads as one flat,
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

const categories = ref([]);
const loading = ref(true);
const catName = ref("");
const catDesc = ref("");
const showForm = ref(false);

const openAddForm = () => {
  catName.value = "";
  catDesc.value = "";
  showForm.value = true;
};

const closeAddForm = () => {
  showForm.value = false;
};

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

// ------------------
// Fetch Categories
// ------------------
async function fetchCategories() {
  loading.value = true;
  try {
    const { data } = await api.get("/categories");
    categories.value = data;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
}

// ------------------
// Create Category
// ------------------
async function createCategory() {
  if (!catName.value.trim())
    return notify("error", "Error", "Category name required");

  try {
    await api.post("/categories/create", {
      name: catName.value.trim(),
      description: catDesc.value.trim(),
    });
    catName.value = "";
    catDesc.value = "";
    showForm.value = false;
    await fetchCategories();
    notify("success", "Success", "Category added!");
  } catch (err) {
    console.error(err);
    notify("error", "Error", "Failed to add category");
  }
}

// ------------------
// Edit Category
// ------------------
const showEditForm = ref(false);
const editingCategory = ref(null);
const editCatName = ref("");
const editCatDesc = ref("");

function openEditCategory(category) {
  editingCategory.value = category;
  editCatName.value = category.name;
  editCatDesc.value = category.description ?? "";
  showEditForm.value = true;
}

function closeEditForm() {
  showEditForm.value = false;
  editingCategory.value = null;
}

async function saveEditCategory() {
  if (!editCatName.value.trim())
    return notify("error", "Error", "Category name required");

  try {
    await api.put(`/categories/${editingCategory.value.id}/update`, {
      name: editCatName.value.trim(),
      description: editCatDesc.value.trim(),
    });
    closeEditForm();
    await fetchCategories();
    notify("success", "Success", "Category updated!");
  } catch (err) {
    console.error(err);
    notify("error", "Error", "Failed to update category");
  }
}

// ------------------
// Delete Category
// ------------------
async function deleteCategory(id) {
  const confirm = await confirmDelete("Are you sure?", "This action cannot be undone!");

  if (confirm.isConfirmed) {
    try {
      await api.delete(`/categories/${id}/delete`);
      await fetchCategories();
      notify("success", "Deleted!", "Category has been deleted.");
    } catch (err) {
      console.error(err);
      notify("error", "Error", "Failed to delete category");
    }
  }
}

onMounted(() => {
  fetchCategories();
  document.addEventListener("click", handleDocClick);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleDocClick);
});
</script>
