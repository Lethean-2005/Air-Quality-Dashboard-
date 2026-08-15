<template>
  <div class="admin-contact-page">
    <h1>User Contact Messages</h1>

    <div class="table-scroll">
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
