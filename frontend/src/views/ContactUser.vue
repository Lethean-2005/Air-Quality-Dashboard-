<template>
  <div class="admin-contact-page">
    <h1>User Contact Messages</h1>

    <!-- Mobile: stacked cards instead of a horizontally-scrolling table -->
    <div class="md:hidden space-y-3">
      <template v-if="loading">
        <div v-for="i in 6" :key="i" class="bg-white border border-gray-100 rounded-xl p-4 space-y-2.5">
          <div class="flex items-start justify-between gap-2">
            <div class="flex-1 space-y-1.5">
              <Skeleton class="h-3.5 w-32" />
              <Skeleton class="h-3 w-40" />
            </div>
            <Skeleton class="h-3 w-16 flex-shrink-0" />
          </div>
          <Skeleton class="h-3 w-full" />
          <Skeleton class="h-3 w-2/3" />
        </div>
      </template>
      <template v-else>
        <div
          v-for="contact in contacts.data"
          :key="contact.id"
          class="bg-white border border-gray-100 rounded-xl p-4"
        >
          <div class="flex items-start justify-between gap-2 mb-3">
            <div class="min-w-0">
              <div class="text-sm font-semibold text-gray-900 truncate">{{ contact.full_name }}</div>
              <div class="text-xs text-gray-400 truncate">{{ contact.email }}</div>
            </div>
            <span class="text-xs text-gray-400 flex-shrink-0 whitespace-nowrap">{{ new Date(contact.created_at).toLocaleString() }}</span>
          </div>
          <div class="space-y-2 text-xs">
            <div class="flex items-center justify-between gap-2">
              <span class="text-gray-400 flex-shrink-0">Phone</span>
              <span class="text-gray-600 text-right truncate">{{ contact.phone_number }}</span>
            </div>
            <div class="flex items-center justify-between gap-2">
              <span class="text-gray-400 flex-shrink-0">Purpose</span>
              <span class="text-gray-600 text-right truncate">{{ contact.purpose_of_contact }}</span>
            </div>
            <div class="flex items-center justify-between gap-2">
              <span class="text-gray-400 flex-shrink-0">Organisation</span>
              <span class="text-gray-600 text-right truncate">{{ contact.organisation }}</span>
            </div>
            <div class="flex flex-col gap-1">
              <span class="text-gray-400">Message</span>
              <span class="text-gray-600">{{ contact.message }}</span>
            </div>
          </div>
        </div>
      </template>
      <div v-if="!loading && contacts.data.length === 0" class="p-6 text-center text-gray-500 text-sm">No contact messages</div>
    </div>

    <div class="hidden md:block table-scroll">
    <table class="contacts-table">
      <thead>
        <tr>
          <th>Full Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Purpose</th>
          <th>Organisation</th>
          <th>Message</th>
          <th>Received At</th>
        </tr>
      </thead>
      <tbody v-if="loading">
        <tr v-for="i in 6" :key="i">
          <td><Skeleton class="h-3 w-24" /></td>
          <td><Skeleton class="h-3 w-32" /></td>
          <td><Skeleton class="h-3 w-20" /></td>
          <td><Skeleton class="h-3 w-20" /></td>
          <td><Skeleton class="h-3 w-24" /></td>
          <td><Skeleton class="h-3 w-40" /></td>
          <td><Skeleton class="h-3 w-24" /></td>
        </tr>
      </tbody>
      <tbody v-else>
        <tr v-for="contact in contacts.data" :key="contact.id">
          <td>{{ contact.full_name }}</td>
          <td>{{ contact.email }}</td>
          <td>{{ contact.phone_number }}</td>
          <td>{{ contact.purpose_of_contact }}</td>
          <td>{{ contact.organisation }}</td>
          <td>{{ contact.message }}</td>
          <td>{{ new Date(contact.created_at).toLocaleString() }}</td>
        </tr>
      </tbody>
    </table>
    </div>

    <div class="pagination-controls" v-if="loading">
      <Skeleton class="h-8 w-20 inline-block" />
      <Skeleton class="h-4 w-16 inline-block mx-2" />
      <Skeleton class="h-8 w-20 inline-block" />
    </div>
    <div class="pagination-controls" v-else-if="contacts.last_page > 1">
      <button @click="fetchContacts(contacts.current_page - 1)" :disabled="contacts.current_page === 1">
        Previous
      </button>
      <span>Page {{ contacts.current_page }} / {{ contacts.last_page }}</span>
      <button @click="fetchContacts(contacts.current_page + 1)" :disabled="contacts.current_page === contacts.last_page">
        Next
      </button>
    </div>
  </div>
</template>

<script>
import axios from 'axios';
import { API_ROOT } from '@/services/api.js';
import Skeleton from '@/components/Skeleton.vue';

export default {
  name: 'AdminContactPage',
  components: { Skeleton },
  data() {
    return {
      contacts: {
        data: [],
        current_page: 1,
        last_page: 1,
      },
      loading: true,
    };
  },
  created() {
    this.fetchContacts();
  },
  methods: {
    async fetchContacts(page = 1) {
      this.loading = true;
      try {
        const res = await axios.get(`${API_ROOT}/api/admin/contacts?page=${page}`);
        this.contacts = res.data;
      } catch (error) {
        alert('Failed to load contacts');
        console.error(error);
      } finally {
        this.loading = false;
      }
    },
  },
};
</script>

<style scoped>
.admin-contact-page {
  padding: 20px;
  max-width: 1000px;
  margin: auto;
  font-family: Arial, sans-serif;
}

.table-scroll {
  overflow-x: auto;
}

.contacts-table {
  width: 100%;
  min-width: 700px;
  border-collapse: collapse;
}

.contacts-table th, .contacts-table td {
  border: 1px solid #ddd;
  padding: 8px;
}

.contacts-table th {
  background-color: #4f46e5;
  color: white;
  text-align: left;
}

.pagination-controls {
  margin-top: 15px;
  text-align: center;
}

.pagination-controls button {
  padding: 6px 12px;
  margin: 0 5px;
  cursor: pointer;
}
</style>
