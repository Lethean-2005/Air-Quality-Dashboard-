<template>
  <div class="w-full font-nunito" v-click-outside="closeDropdown">
    <div class="w-full space-y-4">
      <!-- Stat Cards -->
      <div v-if="loading" class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div v-for="i in 4" :key="i" class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm">
          <div class="flex items-center justify-between">
            <div class="min-w-0 space-y-2">
              <Skeleton class="h-3 w-16" />
              <Skeleton class="h-5 w-8" />
            </div>
            <Skeleton class="w-9 h-9 rounded-lg flex-shrink-0" />
          </div>
          <div class="mt-3 pt-2.5 border-t border-gray-50">
            <Skeleton class="h-2.5 w-24" />
          </div>
        </div>
      </div>
      <div v-else class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div class="min-w-0">
              <div class="text-xs font-medium text-gray-400 truncate">Total Users</div>
              <div class="text-xl font-bold text-gray-900 mt-0.5">{{ users.length }}</div>
            </div>
            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 flex-shrink-0">
              <IconUsers :size="16" />
            </div>
          </div>
          <div class="mt-3 pt-2.5 border-t border-gray-50">
            <span class="text-[11px] text-gray-400 truncate">All registered accounts</span>
          </div>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div class="min-w-0">
              <div class="text-xs font-medium text-gray-400 truncate">Admins</div>
              <div class="text-xl font-bold text-gray-900 mt-0.5">{{ adminCount }}</div>
            </div>
            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 flex-shrink-0">
              <IconShieldCheck :size="16" />
            </div>
          </div>
          <div class="mt-3 pt-2.5 border-t border-gray-50">
            <span class="text-[11px] text-gray-400 truncate">Full admin access</span>
          </div>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div class="min-w-0">
              <div class="text-xs font-medium text-gray-400 truncate">Verified</div>
              <div class="text-xl font-bold text-gray-900 mt-0.5">{{ verifiedCount }}</div>
            </div>
            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 flex-shrink-0">
              <IconRosetteDiscountCheck :size="16" />
            </div>
          </div>
          <div class="mt-3 pt-2.5 border-t border-gray-50">
            <span class="text-[11px] text-gray-400 truncate">Email confirmed</span>
          </div>
        </div>
        <div class="bg-white border border-gray-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between">
            <div class="min-w-0">
              <div class="text-xs font-medium text-gray-400 truncate">Pending</div>
              <div class="text-xl font-bold text-gray-900 mt-0.5">{{ pendingCount }}</div>
            </div>
            <div class="w-9 h-9 rounded-lg bg-gray-100 flex items-center justify-center text-gray-400 flex-shrink-0">
              <IconClock :size="16" />
            </div>
          </div>
          <div class="mt-3 pt-2.5 border-t border-gray-50">
            <span class="text-[11px] text-gray-400 truncate">Awaiting verification</span>
          </div>
        </div>
      </div>

      <!-- Page action, kept outside the table card -->
      <div class="flex justify-end">
        <Skeleton v-if="loading" class="h-9 w-28 rounded-lg" />
        <button
          v-else
          @click="openAddUser"
          class="flex items-center gap-1.5 px-3 py-2 bg-gray-900 text-white rounded-lg text-sm font-medium hover:bg-gray-800"
        >
          <IconPlus class="w-4 h-4" />
          Add user
        </button>
      </div>

      <!-- Card -->
      <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
        <!-- Toolbar -->
        <div v-if="loading" class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-100">
          <Skeleton class="h-4 w-20" />
          <div class="flex items-center gap-2">
            <Skeleton class="h-10 w-56 rounded-lg" />
            <Skeleton class="h-9 w-24 rounded-lg" />
            <Skeleton class="h-9 w-24 rounded-lg" />
            <Skeleton class="h-9 w-20 rounded-lg" />
          </div>
        </div>
        <div v-else class="flex flex-wrap items-center justify-between gap-3 p-4 border-b border-gray-100">
          <div class="flex items-center gap-2">
            <span class="text-sm font-semibold text-gray-900">All users</span>
            <span class="text-sm text-gray-400">{{ filteredUsers.length }}</span>
          </div>

          <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            <div class="relative flex-1 min-w-[140px] sm:flex-none">
              <IconSearch class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search"
                class="pl-9 pr-3 h-10 rounded-lg text-sm w-full sm:w-56 text-gray-700 placeholder-gray-400 bg-gray-50 focus:outline-none focus:bg-gray-100"
              />
            </div>

            <AppDropdown
              v-model="statusFilter"
              placeholder="Status"
              :options="[
                { value: '', label: 'Status' },
                { value: 'active', label: 'Verified' },
                { value: 'pending', label: 'Pending' },
              ]"
              :lead-icon="IconUserCheck"
              light
            />

            <AppDropdown
              v-model="dateSort"
              :options="[
                { value: 'newest', label: 'Newest' },
                { value: 'oldest', label: 'Oldest' },
              ]"
              :lead-icon="IconCalendar"
              light
            />

            <AppDropdown
              v-model="roleFilter"
              :options="[
                { value: '', label: 'Role' },
                { value: 'admin', label: $t('user.admin') },
                { value: 'user', label: $t('user.user') },
              ]"
              :lead-icon="IconUserCircle"
              light
            />
          </div>
        </div>

        <!-- Mobile: stacked cards instead of a horizontally-scrolling table -->
        <div class="md:hidden space-y-3 p-3">
          <template v-if="loading">
            <div v-for="i in 5" :key="i" class="bg-white border border-gray-100 rounded-xl p-4 space-y-2.5">
              <div class="flex items-center gap-3">
                <Skeleton class="w-9 h-9 rounded-full flex-shrink-0" />
                <div class="flex-1 space-y-1.5">
                  <Skeleton class="h-3.5 w-32" />
                  <Skeleton class="h-3 w-40" />
                </div>
              </div>
              <Skeleton class="h-3 w-full" />
              <Skeleton class="h-3 w-2/3" />
            </div>
          </template>
          <template v-else>
          <div
            v-for="user in paginatedUsers"
            :key="user.id"
            class="bg-white border border-gray-100 rounded-xl p-4"
          >
            <div class="flex items-start justify-between gap-2 mb-3">
              <div class="flex items-center gap-3 min-w-0">
                <img
                  v-if="user.profile_image && !brokenImageIds.has(user.id)"
                  :src="user.profile_image"
                  alt=""
                  class="w-9 h-9 rounded-full object-cover flex-shrink-0"
                  @error="markImageBroken(user.id)"
                />
                <div
                  v-else
                  class="w-9 h-9 rounded-full bg-teal-600 text-white flex items-center justify-center text-sm font-semibold flex-shrink-0"
                >
                  {{ user.name?.charAt(0).toUpperCase() }}
                </div>
                <div class="min-w-0">
                  <div class="text-sm font-semibold text-gray-900 truncate">{{ user.name }}</div>
                  <div class="text-xs text-gray-400 truncate">{{ user.email }}</div>
                </div>
              </div>
              <button
                @click="toggleDropdown(user, $event)"
                class="dropdown-button p-1.5 rounded-lg hover:bg-gray-100 text-gray-400 flex-shrink-0"
                aria-haspopup="true"
                :aria-expanded="dropdownOpenId === user"
              >
                <IconDotsVertical class="w-4 h-4" />
              </button>
            </div>
            <div class="space-y-2 text-xs">
              <div class="flex items-start justify-between gap-2">
                <span class="text-gray-400 flex-shrink-0">Access</span>
                <div class="flex flex-wrap gap-1.5 justify-end min-w-0">
                  <span
                    v-for="tag in accessTags(user)"
                    :key="tag"
                    :class="accessBadgeClass(tag)"
                    class="px-2 py-0.5 rounded-[5px] font-medium whitespace-nowrap"
                  >
                    {{ tag }}
                  </span>
                </div>
              </div>
              <div class="flex items-center justify-between gap-2">
                <span class="text-gray-400 flex-shrink-0">Status</span>
                <span
                  class="px-2 py-0.5 rounded-[5px] font-medium flex-shrink-0"
                  :class="isActiveUser(user) ? 'bg-green-50 text-green-600' : 'bg-gray-100 text-gray-500'"
                >
                  {{ isActiveUser(user) ? "Active" : "Inactive" }}
                </span>
              </div>
              <div class="flex items-center justify-between gap-2">
                <span class="text-gray-400 flex-shrink-0">Last login</span>
                <span class="text-gray-600 text-right truncate">{{ user.last_login_at ? formatShortDate(user.last_login_at) : "Never" }}</span>
              </div>
              <div class="flex items-center justify-between gap-2">
                <span class="text-gray-400 flex-shrink-0">Last active</span>
                <span class="text-gray-600 text-right truncate">{{ formatShortDate(user.updated_at) }}</span>
              </div>
              <div class="flex items-center justify-between gap-2">
                <span class="text-gray-400 flex-shrink-0">Date added</span>
                <span class="text-gray-600 text-right truncate">{{ formatShortDate(user.created_at) }}</span>
              </div>
            </div>
          </div>
          </template>
          <div v-if="!loading && filteredUsers.length === 0" class="p-6 text-center text-gray-500 text-sm">
            {{ $t("user.noUsers") }}
          </div>
        </div>

        <!-- Desktop/tablet: full table -->
        <div class="hidden md:block overflow-x-auto max-h-[600px] overflow-y-auto">
          <table class="min-w-full">
            <thead class="sticky top-0 z-10 bg-white">
              <tr class="border-b border-gray-100">
                <th class="w-10 px-4 py-3">
                  <Skeleton v-if="loading" class="w-4 h-4 rounded" />
                  <input
                    v-else
                    type="checkbox"
                    :checked="allSelected"
                    @change="toggleSelectAll"
                    class="rounded border-gray-300 text-teal-600 focus:ring-teal-400"
                  />
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">
                  User name
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">
                  Access
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">
                  Status
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">
                  Last login
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">
                  Last active
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-400">
                  Date added
                </th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-400">Action</th>
              </tr>
            </thead>
            <tbody v-if="loading">
              <tr v-for="i in 8" :key="i" class="border-b border-gray-50 last:border-b-0">
                <td class="px-4 py-3"><Skeleton class="w-4 h-4 rounded" /></td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    <Skeleton class="w-9 h-9 rounded-full flex-shrink-0" />
                    <div class="space-y-1.5">
                      <Skeleton class="h-3.5 w-32" />
                      <Skeleton class="h-3 w-40" />
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3"><Skeleton class="h-5 w-24 rounded-[5px]" /></td>
                <td class="px-4 py-3"><Skeleton class="h-5 w-16 rounded-[5px]" /></td>
                <td class="px-4 py-3"><Skeleton class="h-3.5 w-20" /></td>
                <td class="px-4 py-3"><Skeleton class="h-3.5 w-20" /></td>
                <td class="px-4 py-3"><Skeleton class="h-3.5 w-20" /></td>
                <td class="px-4 py-3 text-right"><Skeleton class="h-6 w-6 rounded-lg ml-auto" /></td>
              </tr>
            </tbody>
            <tbody v-else>
              <tr v-for="user in paginatedUsers" :key="user.id" class="hover:bg-gray-50/60">
                <td class="px-4 py-3">
                  <input
                    type="checkbox"
                    :value="user.id"
                    v-model="selectedIds"
                    class="rounded border-gray-300 text-teal-600 focus:ring-teal-400"
                  />
                </td>
                <td class="px-4 py-3">
                  <div class="flex items-center gap-3">
                    <img
                      v-if="user.profile_image && !brokenImageIds.has(user.id)"
                      :src="user.profile_image"
                      alt=""
                      class="w-9 h-9 rounded-full object-cover flex-shrink-0"
                      @error="markImageBroken(user.id)"
                    />
                    <div
                      v-else
                      class="w-9 h-9 rounded-full bg-teal-600 text-white flex items-center justify-center text-sm font-semibold flex-shrink-0"
                    >
                      {{ user.name?.charAt(0).toUpperCase() }}
                    </div>
                    <div class="min-w-0">
                      <div class="text-sm font-semibold text-gray-900 truncate">{{ user.name }}</div>
                      <div class="text-xs text-gray-400 truncate">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <div class="flex flex-wrap gap-1.5">
                    <span
                      v-for="tag in accessTags(user)"
                      :key="tag"
                      :class="accessBadgeClass(tag)"
                      class="px-2 py-0.5 rounded-[5px] text-xs font-medium whitespace-nowrap"
                    >
                      {{ tag }}
                    </span>
                  </div>
                </td>
                <td class="px-4 py-3">
                  <span
                    class="px-2 py-0.5 rounded-[5px] text-xs font-medium whitespace-nowrap"
                    :class="isActiveUser(user) ? 'bg-green-50 text-green-600' : 'bg-gray-100 text-gray-500'"
                  >
                    {{ isActiveUser(user) ? "Active" : "Inactive" }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">
                  {{ user.last_login_at ? formatShortDate(user.last_login_at) : "Never" }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">
                  {{ formatShortDate(user.updated_at) }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">
                  {{ formatShortDate(user.created_at) }}
                </td>
                <td class="px-4 py-3 text-right">
                  <button
                    @click="toggleDropdown(user, $event)"
                    class="dropdown-button p-1.5 rounded-lg hover:bg-gray-100 text-gray-400"
                    aria-haspopup="true"
                    :aria-expanded="dropdownOpenId === user"
                  >
                    <IconDotsVertical class="w-4 h-4" />
                  </button>
                </td>
              </tr>
            </tbody>
          </table>

          <div v-if="!loading && filteredUsers.length === 0" class="p-6 text-center text-gray-500 text-sm">
            {{ $t("user.noUsers") }}
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="loading" class="flex flex-col sm:flex-row justify-between items-center gap-3 p-4 border-t border-gray-100">
          <Skeleton class="h-3.5 w-20" />
          <div class="flex items-center space-x-1">
            <Skeleton v-for="i in 4" :key="i" class="h-7 w-7 rounded-full" />
          </div>
        </div>
        <div v-else class="flex flex-col sm:flex-row justify-between items-center gap-3 p-4 border-t border-gray-100">
          <div class="text-sm text-gray-500">Page {{ currentPage }} of {{ totalPages || 1 }}</div>

          <div class="flex flex-wrap items-center justify-center space-x-1">
            <button
              @click="goToPage(currentPage - 1)"
              :disabled="currentPage === 1"
              class="px-3 py-1 rounded-full border border-gray-200 text-gray-600 hover:bg-gray-100 disabled:opacity-50"
            >
              «
            </button>

            <template v-for="page in paginationRange" :key="page">
              <button
                v-if="page !== '...'"
                @click="goToPage(page)"
                :class="[
                  'px-3 py-1 rounded-full border',
                  currentPage === page
                    ? 'bg-gray-900 text-white border-gray-900'
                    : 'border-gray-200 text-gray-600 hover:bg-gray-100',
                ]"
              >
                {{ page }}
              </button>
              <span v-else class="px-3 py-1 text-gray-400">...</span>
            </template>

            <button
              @click="goToPage(currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="px-3 py-1 rounded-full border border-gray-200 text-gray-600 hover:bg-gray-100 disabled:opacity-50"
            >
              »
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Row action menu (teleported so it displays over the table) -->
    <Teleport to="body">
      <div
        v-if="dropdownOpenId"
        class="dropdown-menu fixed w-40 bg-white border border-gray-100 rounded-lg shadow-lg z-[9999] py-1"
        :style="{ left: dropdownPos.left + 'px', top: dropdownPos.top + 'px' }"
      >
        <button
          @click="viewUser(dropdownOpenId); closeDropdown();"
          class="flex items-center w-full text-left px-3 py-2 text-sm text-gray-600 hover:text-gray-900 transition-colors"
        >
          <IconEye class="w-4 h-4 mr-2 text-gray-400" />
          {{ $t("user.view") }}
        </button>
        <button
          @click="openEditUser(dropdownOpenId); closeDropdown();"
          class="flex items-center w-full text-left px-3 py-2 text-sm text-indigo-600 hover:text-indigo-800 transition-colors"
        >
          <IconPencil class="w-4 h-4 mr-2 text-indigo-400" />
          {{ $t("user.edit") }}
        </button>
        <button
          @click="toggleUserActive(dropdownOpenId); closeDropdown();"
          class="flex items-center w-full text-left px-3 py-2 text-sm text-amber-600 hover:text-amber-800 transition-colors"
        >
          <IconPower class="w-4 h-4 mr-2 text-amber-400" />
          {{ isActiveUser(dropdownOpenId) ? "Deactivate" : "Activate" }}
        </button>
        <button
          @click="deleteUser(dropdownOpenId); closeDropdown();"
          class="flex items-center w-full text-left px-3 py-2 text-sm text-red-600 hover:text-red-800 transition-colors"
        >
          <IconTrash class="w-4 h-4 mr-2 text-red-400" />
          {{ $t("user.delete") }}
        </button>
      </div>
    </Teleport>

    <!--Add User Modal -->
    <div
      v-if="showAddUserModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-2xl p-6 w-full max-w-md relative">
        <button
          @click="closeAddUser"
          class="absolute top-4 right-4 text-gray-400 hover:text-gray-700"
        >
          <IconX class="w-5 h-5" />
        </button>
        <h2 class="text-xl font-semibold mb-4 text-gray-900">{{ $t("user.addTitle") }}</h2>
        <form @submit.prevent="submitAddUser" class="space-y-3 text-sm">
          <input
            v-model="addUserForm.name"
            type="text"
            :placeholder="$t('user.name')"
            class="w-full bg-gray-100 border-0 rounded-lg px-3 py-2.5"
            required
          />
          <input
            v-model="addUserForm.email"
            type="email"
            :placeholder="$t('user.email')"
            class="w-full bg-gray-100 border-0 rounded-lg px-3 py-2.5"
            required
          />
          <input
            v-model="addUserForm.password"
            type="password"
            :placeholder="$t('user.password')"
            class="w-full bg-gray-100 border-0 rounded-lg px-3 py-2.5"
            required
          />
          <input
            v-model="addUserForm.password_confirmation"
            type="password"
            :placeholder="$t('user.confirmPassword')"
            class="w-full bg-gray-100 border-0 rounded-lg px-3 py-2.5"
            required
          />
          <AppDropdown
            v-model="addUserForm.role"
            placeholder="Select Role"
            :options="[
              { value: 'admin', label: $t('user.admin') },
              { value: 'user', label: $t('user.user') },
            ]"
            :lead-icon="IconUserCircle"
            light
          />
          <input
            v-model="addUserForm.phone"
            type="tel"
            :placeholder="$t('user.phoneOptional')"
            class="w-full bg-gray-100 border-0 rounded-lg px-3 py-2.5"
          />
          <div>
            <label class="block mb-1 font-medium text-gray-700">{{ $t("user.profileOptions") }}</label>
            <label class="flex flex-col items-center justify-center gap-1.5 w-full py-6 px-3 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:bg-gray-100 hover:border-gray-400 transition-colors">
              <IconCloudUpload :size="20" class="text-gray-400" />
              <span class="text-xs text-gray-500 text-center">
                <span class="text-gray-700 font-medium">Click to upload</span> or drag and drop
              </span>
              <input @change="onAddImageChange" type="file" accept="image/*" class="hidden" />
            </label>
            <img
              v-if="addUserImagePreview"
              :src="addUserImagePreview"
              alt="preview"
              class="mt-2 w-20 h-20 rounded-lg object-cover"
            />
          </div>
          <div class="flex justify-end gap-2 mt-4">
            <button
              type="button"
              @click="closeAddUser"
              class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200 text-gray-700"
            >
              {{ $t("user.cancel") }}
            </button>
            <button
              type="submit"
              :disabled="addUserLoading"
              class="px-4 py-2 bg-gray-900 text-white rounded-lg hover:bg-gray-800"
            >
              {{ addUserLoading ? $t("user.saving") : $t("user.save") }}
            </button>
          </div>
          <div v-if="addUserErrors" class="text-red-600 mt-2 text-xs">
            <div v-for="(msgs, field) in addUserErrors" :key="field">
              <div v-for="msg in msgs" :key="msg">{{ msg }}</div>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Edit User Modal -->
    <div
      v-if="showEditUserModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-2xl p-6 w-full max-w-md relative">
        <button
          @click="closeEditUser"
          class="absolute top-4 right-4 text-gray-400 hover:text-gray-700"
        >
          <IconX class="w-5 h-5" />
        </button>

        <h3 class="text-xl font-semibold mb-4 text-gray-900">{{ $t("user.editTitle") }}</h3>

        <form @submit.prevent="submitEditUser" class="space-y-3 text-sm">
          <div>
            <label class="block mb-1 font-medium text-gray-700">{{ $t("user.name") }}</label>
            <input
              v-model="editUserForm.name"
              type="text"
              class="w-full bg-gray-100 border-0 rounded-lg px-3 py-2.5"
              required
            />
          </div>

          <div>
            <label class="block mb-1 font-medium text-gray-700">{{ $t("user.email") }}</label>
            <input
              v-model="editUserForm.email"
              type="email"
              class="w-full bg-gray-100 border-0 rounded-lg px-3 py-2.5"
              required
            />
          </div>

          <div>
            <label class="block mb-1 font-medium text-gray-700">{{ $t("user.role") }}</label>
            <AppDropdown
              v-model="editUserForm.role"
              :options="[
                { value: 'user', label: $t('user.user') },
                { value: 'admin', label: $t('user.admin') },
              ]"
              :lead-icon="IconUserCircle"
              light
            />
          </div>

          <div>
            <label class="block mb-1 font-medium text-gray-700">{{ $t("user.phone") }}</label>
            <input
              v-model="editUserForm.phone"
              type="text"
              class="w-full bg-gray-100 border-0 rounded-lg px-3 py-2.5"
            />
          </div>

          <div>
            <label class="block mb-1 font-medium text-gray-700">{{ $t("user.profileImage") }}</label>
            <label class="flex flex-col items-center justify-center gap-1.5 w-full py-6 px-3 bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:bg-gray-100 hover:border-gray-400 transition-colors">
              <IconCloudUpload :size="20" class="text-gray-400" />
              <span class="text-xs text-gray-500 text-center">
                <span class="text-gray-700 font-medium">Click to upload</span> or drag and drop
              </span>
              <input type="file" @change="onEditImageChange" class="hidden" />
            </label>
            <div v-if="editUserImagePreview" class="mt-2">
              <img :src="editUserImagePreview" alt="Preview" class="w-16 h-16 rounded-full object-cover" />
            </div>
          </div>

          <button
            type="submit"
            :disabled="editUserLoading"
            class="w-full bg-gray-900 text-white px-4 py-2.5 rounded-lg hover:bg-gray-800 mt-2"
          >
            {{ editUserLoading ? "Saving..." : "Save Changes" }}
          </button>
        </form>

        <div v-if="editUserErrors" class="text-red-600 mt-2 text-xs">
          <div v-for="(msgs, field) in editUserErrors" :key="field">
            <div v-for="msg in msgs" :key="msg">{{ msg }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- View User Modal -->
    <div
      v-if="showViewUserModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
    >
      <div class="bg-white rounded-2xl p-6 w-full max-w-md relative">
        <button
          @click="closeViewUser"
          class="absolute top-4 right-4 text-gray-400 hover:text-gray-700"
        >
          <IconX class="w-5 h-5" />
        </button>

        <div class="flex gap-4 mb-4 items-center">
          <img
            v-if="viewUserData.profile_image"
            :src="viewUserData.profile_image"
            alt="profile"
            class="w-16 h-16 rounded-full object-cover"
          />
          <div
            v-else
            class="w-16 h-16 rounded-full bg-teal-600 text-white flex items-center justify-center font-bold text-2xl"
          >
            {{ viewUserData.name?.charAt(0).toUpperCase() }}
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ viewUserData.name }}</h3>
            <div class="flex flex-wrap gap-1.5 mt-1">
              <span
                v-for="tag in accessTags(viewUserData)"
                :key="tag"
                :class="accessBadgeClass(tag)"
                class="px-2 py-0.5 rounded-[5px] text-xs font-medium"
              >
                {{ tag }}
              </span>
            </div>
          </div>
        </div>

        <div class="space-y-1.5 text-sm">
          <p><span class="text-gray-500">{{ $t("user.email") }}:</span> {{ viewUserData.email }}</p>
          <p v-if="viewUserData.phone"><span class="text-gray-500">{{ $t("user.phone") }}:</span> {{ viewUserData.phone }}</p>
          <p><span class="text-gray-500">{{ $t("user.createdAt") }}:</span> {{ formatDate(viewUserData.created_at) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from "vue";
import api from "@/services/api";
import {
  IconSearch,
  IconPlus,
  IconDotsVertical,
  IconEye,
  IconPencil,
  IconTrash,
  IconPower,
  IconX,
  IconUsers,
  IconShieldCheck,
  IconRosetteDiscountCheck,
  IconClock,
  IconUserCheck,
  IconCalendar,
  IconUserCircle,
  IconCloudUpload,
} from "@tabler/icons-vue";
import AppDropdown from "../components/AppDropdown.vue";
import Skeleton from "../components/Skeleton.vue";

const users = ref([]);
const loading = ref(false);

const searchQuery = ref("");
const roleFilter = ref("");
const statusFilter = ref("");
const dateSort = ref("newest");

const adminCount = computed(() => users.value.filter((u) => u.role === "admin").length);
const verifiedCount = computed(() => users.value.filter((u) => u.email_verified_at !== null).length);
const pendingCount = computed(() => users.value.filter((u) => u.email_verified_at === null).length);

const showAddUserModal = ref(false);
const showEditUserModal = ref(false);
const showViewUserModal = ref(false);
const currentPage = ref(1);
const itemsPerPage = 5;

const selectedIds = ref([]);
const brokenImageIds = ref(new Set());
const markImageBroken = (id) => {
  brokenImageIds.value = new Set(brokenImageIds.value).add(id);
};

const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage;
  return filteredUsers.value.slice(start, start + itemsPerPage);
});

const allSelected = computed(
  () =>
    paginatedUsers.value.length > 0 &&
    paginatedUsers.value.every((u) => selectedIds.value.includes(u.id))
);

const toggleSelectAll = (e) => {
  const pageIds = paginatedUsers.value.map((u) => u.id);
  if (e.target.checked) {
    selectedIds.value = Array.from(new Set([...selectedIds.value, ...pageIds]));
  } else {
    selectedIds.value = selectedIds.value.filter((id) => !pageIds.includes(id));
  }
};

const totalPages = computed(() => Math.ceil(filteredUsers.value.length / itemsPerPage));

function goToPage(page) {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page;
  }
}

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

const addUserForm = ref({
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
  role: "",
  phone: "",
  profile_image: null,
});
const addUserImagePreview = ref(null);
const addUserErrors = ref({});
const addUserLoading = ref(false);

const editUserForm = ref({
  id: null,
  name: "",
  email: "",
  password: "",
  password_confirmation: "",
  role: "",
  phone: "",
  profile_image: null,
});
const editUserImagePreview = ref(null);
const editUserErrors = ref({});
const editUserLoading = ref(false);

const viewUserData = ref({});

const dropdownOpenId = ref(null);
const dropdownPos = ref({ left: 0, top: 0 });

// Fetch users from API
const fetchUsers = async () => {
  loading.value = true;
  try {
    const res = await api.get("/users");
    users.value = res.data;
  } catch (e) {
    console.error(e);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchUsers();
  document.addEventListener("click", handleClickOutside);
});

onBeforeUnmount(() => {
  document.removeEventListener("click", handleClickOutside);
});

// Click outside handler to close dropdown
const handleClickOutside = (e) => {
  if (!e.target.closest(".dropdown-menu") && !e.target.closest(".dropdown-button")) {
    closeDropdown();
  }
};

// Dropdown toggle and close functions
const toggleDropdown = (user, e) => {
  if (dropdownOpenId.value === user) {
    dropdownOpenId.value = null;
    return;
  }
  dropdownOpenId.value = user;
  const rect = e.currentTarget.getBoundingClientRect();
  dropdownPos.value = {
    left: Math.max(8, rect.right - 160),
    top: rect.bottom + 6,
  };
};
const closeDropdown = () => {
  dropdownOpenId.value = null;
};

// Filtering users based on search, role, and status filters
const filteredUsers = computed(() => {
  const filtered = users.value.filter((u) => {
    const matchesSearch =
      u.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      u.email.toLowerCase().includes(searchQuery.value.toLowerCase());
    const matchesRole = !roleFilter.value || u.role === roleFilter.value;
    const matchesStatus =
      !statusFilter.value ||
      (statusFilter.value === "active"
        ? u.email_verified_at !== null
        : u.email_verified_at === null);
    return matchesSearch && matchesRole && matchesStatus;
  });

  return [...filtered].sort((a, b) => {
    const diff = new Date(b.created_at) - new Date(a.created_at);
    return dateSort.value === "newest" ? diff : -diff;
  });
});

const openAddUser = () => {
  showAddUserModal.value = true;
  addUserErrors.value = {};
  addUserForm.value = {
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    role: "",
    phone: "",
    bio: "",
    profile_image: null,
  };
  addUserImagePreview.value = null;
};

const closeAddUser = () => {
  showAddUserModal.value = false;
};

const onAddImageChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    addUserForm.value.profile_image = file;
    addUserImagePreview.value = URL.createObjectURL(file);
  } else {
    addUserForm.value.profile_image = null;
    addUserImagePreview.value = null;
  }
};

const submitAddUser = async () => {
  addUserLoading.value = true;
  addUserErrors.value = {};
  try {
    const formData = new FormData();
    for (const key in addUserForm.value) {
      if (addUserForm.value[key] !== null) {
        formData.append(key, addUserForm.value[key]);
      }
    }
    await api.post("/users", formData);
    await fetchUsers();
    closeAddUser();
  } catch (e) {
    if (e.response && e.response.data.errors) {
      addUserErrors.value = e.response.data.errors;
    } else {
      alert("Failed to add user");
    }
  } finally {
    addUserLoading.value = false;
  }
};

const openEditUser = (user) => {
  showEditUserModal.value = true;
  editUserErrors.value = {};
  editUserForm.value = {
    id: user.id,
    name: user.name,
    email: user.email,
    password: "",
    password_confirmation: "",
    role: user.role,
    phone: user.phone || "",
    profile_image: null,
  };
  editUserImagePreview.value = null;
};

const closeEditUser = () => {
  showEditUserModal.value = false;
};

const onEditImageChange = (e) => {
  const file = e.target.files[0];
  if (file) {
    editUserForm.value.profile_image = file;
    editUserImagePreview.value = URL.createObjectURL(file);
  } else {
    editUserForm.value.profile_image = null;
    editUserImagePreview.value = null;
  }
};

const submitEditUser = async () => {
  editUserLoading.value = true;
  editUserErrors.value = {};

  try {
    const formData = new FormData();

    if (editUserForm.value.name) formData.append("name", editUserForm.value.name);
    if (editUserForm.value.email) formData.append("email", editUserForm.value.email);
    if (editUserForm.value.role) formData.append("role", editUserForm.value.role);
    if (editUserForm.value.phone) formData.append("phone", editUserForm.value.phone);
    if (editUserForm.value.bio) formData.append("bio", editUserForm.value.bio);

    if (editUserForm.value.password) {
      formData.append("password", editUserForm.value.password);
      formData.append("password_confirmation", editUserForm.value.password_confirmation);
    }

    if (editUserForm.value.profile_image) {
      formData.append("profile_image", editUserForm.value.profile_image);
    }

    // Tell Laravel this is a PUT
    formData.append("_method", "PUT");

    // Send as POST
    await api.post(`/users/${editUserForm.value.id}`, formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });

    await fetchUsers();
    closeEditUser();
  } catch (e) {
    if (e.response && e.response.data.errors) {
      editUserErrors.value = e.response.data.errors;
    } else {
      alert("Failed to update user");
    }
  } finally {
    editUserLoading.value = false;
  }
};

const viewUser = (user) => {
  viewUserData.value = user;
  showViewUserModal.value = true;
};

const closeViewUser = () => {
  showViewUserModal.value = false;
};

const deleteUser = async (user) => {
  if (confirm(`Are you sure you want to delete user "${user.name}"?`)) {
    try {
      await api.delete(`/users/${user.id}`);
      await fetchUsers();
    } catch {
      alert("Failed to delete user");
    }
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return "-";
  const d = new Date(dateStr);
  return d.toLocaleDateString() + " " + d.toLocaleTimeString();
};

const formatShortDate = (dateStr) => {
  if (!dateStr) return "-";
  const d = new Date(dateStr);
  return d.toLocaleDateString("en-US", { month: "short", day: "numeric", year: "numeric" });
};

// Registration never sets email_verified_at (only Google sign-in does), so it
// can't stand in for "active" — real accounts stay permanently "unverified".
// is_active is a dedicated column instead, defaulting true, toggled by admins,
// and enforced at login (deactivated accounts are rejected server-side).
const isActiveUser = (user) => user.is_active !== false;

const toggleUserActive = async (user) => {
  const next = !isActiveUser(user);
  try {
    const formData = new FormData();
    formData.append("is_active", next ? "1" : "0");
    formData.append("_method", "PUT");
    await api.post(`/users/${user.id}`, formData, {
      headers: { "Content-Type": "multipart/form-data" },
    });
    await fetchUsers();
  } catch {
    alert("Failed to update user status");
  }
};

// Access badges derived from role, styled like the reference dashboard
const accessTags = (user) => {
  if (user.role === "admin") return ["Admin", "Data Export", "Data Import"];
  if (user.role === "viewer") return ["View only"];
  return ["Data Export", "Data Import"];
};

const accessBadgeClass = (tag) => {
  switch (tag) {
    case "Admin":
      return "bg-green-50 text-green-600";
    case "Data Export":
      return "bg-blue-50 text-blue-600";
    case "Data Import":
      return "bg-purple-50 text-purple-600";
    default:
      return "bg-gray-100 text-gray-600";
  }
};
</script>

<style scoped>
.dropdown-button {
  cursor: pointer;
}
</style>
