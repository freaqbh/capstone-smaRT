<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

defineProps<{
  title: string
  breadcrumb?: string
}>()

const auth = useAuthStore()
const searchQuery = ref('')

function formatDate(): string {
  const now = new Date()
  const options: Intl.DateTimeFormatOptions = { day: 'numeric', month: 'short', year: 'numeric' }
  return now.toLocaleDateString('id-ID', options)
}
</script>

<template>
  <header class="topbar">
    <div class="topbar-left">
      <div>
        <h1 class="topbar-title">{{ title }}</h1>
        <p v-if="breadcrumb" class="topbar-breadcrumb">{{ breadcrumb }}</p>
      </div>
    </div>

    <div class="topbar-right">
      <!-- Search -->
      <div class="search-box">
        <svg class="search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8" /><line x1="21" y1="21" x2="16.65" y2="16.65" />
        </svg>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari warga, transaksi..."
          class="search-input"
        />
      </div>

      <!-- Notification bell -->
      <button class="topbar-btn" title="Notifikasi">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
          <path d="M13.73 21a2 2 0 0 1-3.46 0" />
        </svg>
        <span class="notification-dot"></span>
      </button>

      <!-- Clock -->
      <button class="topbar-btn" title="Waktu">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" />
        </svg>
      </button>

      <!-- Date -->
      <div class="topbar-date">{{ formatDate() }}</div>

      <!-- Logout -->
      <button class="topbar-btn topbar-logout" title="Logout" @click="auth.logout()">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
          <polyline points="16 17 21 12 16 7" />
          <line x1="21" y1="12" x2="9" y2="12" />
        </svg>
      </button>
    </div>
  </header>
</template>

<style scoped>
.topbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 32px;
  height: var(--topbar-height);
  background: var(--bg-secondary);
  border-bottom: 1px solid var(--border-color);
  position: sticky;
  top: 0;
  z-index: 50;
}

.topbar-title {
  font-size: 1.2rem;
  font-weight: 700;
  color: var(--text-heading);
  letter-spacing: -0.3px;
}

.topbar-breadcrumb {
  font-size: 0.75rem;
  color: var(--text-muted);
  margin-top: 2px;
}

.topbar-right {
  display: flex;
  align-items: center;
  gap: 10px;
}

/* Search */
.search-box {
  position: relative;
  display: flex;
  align-items: center;
}

.search-icon {
  position: absolute;
  left: 12px;
  color: var(--text-muted);
  pointer-events: none;
}

.search-input {
  padding: 8px 14px 8px 36px;
  width: 240px;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-full);
  font-size: 0.82rem;
  color: var(--text-primary);
}

.search-input::placeholder {
  color: var(--text-muted);
}

.search-input:focus {
  border-color: var(--accent-blue);
  box-shadow: 0 0 0 3px var(--accent-blue-dim);
}

/* Buttons */
.topbar-btn {
  position: relative;
  width: 38px;
  height: 38px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: var(--radius-md);
  color: var(--text-secondary);
  transition: all var(--transition-fast);
}

.topbar-btn:hover {
  background: var(--bg-card);
  color: var(--text-primary);
}

.notification-dot {
  position: absolute;
  top: 8px;
  right: 8px;
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--accent-red);
  border: 2px solid var(--bg-secondary);
}

.topbar-date {
  padding: 7px 14px;
  border-radius: var(--radius-md);
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  font-size: 0.82rem;
  color: var(--text-secondary);
  font-weight: 500;
  white-space: nowrap;
}

.topbar-logout {
  margin-left: 4px;
}

.topbar-logout:hover {
  color: var(--accent-red);
  background: var(--accent-red-dim);
}
</style>
