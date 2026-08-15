// src/api.js
import axios from 'axios';

// VITE_API_BASE_URL is set per-environment (Vercel project settings in prod, .env.local in
// dev) so this file never needs editing when the backend URL changes between environments.
export const API_ROOT = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8001';

const api = axios.create({
  baseURL: `${API_ROOT}/api`,
});

// Automatically attach token from localStorage
api.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

export default api;

