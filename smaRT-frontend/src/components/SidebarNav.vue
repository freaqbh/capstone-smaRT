<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const auth = useAuthStore()

const currentPath = computed(() => route.path)

interface NavItem {
  label: string
  icon: string
  path: string
  badge?: number | null
  badgeColor?: string
}

interface NavSection {
  title: string
  items: NavItem[]
}

const navigation = computed<NavSection[]>(() => {
  const sections: NavSection[] = [
    {
      title: 'UTAMA',
      items: [
        { label: 'Overview & Statistik', icon: '📊', path: '/', badge: null, badgeColor: '' },
      ],
    },
    {
      title: 'MANAJEMEN',
      items: [
        { label: 'Pengguna / Warga', icon: '👥', path: '/warga', badge: 12, badgeColor: 'blue' },
        { label: 'Surat Pengantar', icon: '📄', path: '/surat', badge: 7, badgeColor: 'orange' },
        { label: 'Laporan Warga', icon: '📋', path: '/laporan', badge: 3, badgeColor: 'red' },
        { label: 'Agenda Kegiatan', icon: '📅', path: '/agenda', badge: null, badgeColor: '' },
      ],
    },
    {
      title: 'KEUANGAN & KEAMANAN',
      items: [
        { label: 'Keuangan (Hashchain)', icon: '💰', path: '/keuangan', badge: null, badgeColor: '' },
        { label: 'Log Aktivitas / SOS', icon: '🔔', path: '/log', badge: 1, badgeColor: 'red' },
      ],
    },
    {
      title: 'SISTEM',
      items: [
        { label: 'Pengaturan', icon: '⚙️', path: '/pengaturan', badge: null, badgeColor: '' },
      ],
    },
  ]
  return sections
})
</script>

<template>
  <aside class="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
      <div class="logo-icon">
        <svg width="28" height="28" viewBox="0 0 28 28" fill="none">
          <rect width="28" height="28" rx="8" fill="#3b82f6" />
          <text x="14" y="19" text-anchor="middle" fill="white" font-size="13" font-weight="700" font-family="Inter">sR</text>
        </svg>
      </div>
      <div class="logo-text">
        <span class="logo-title">smaRT</span>
        <span class="logo-subtitle">ADMIN DASHBOARD</span>
      </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
      <div v-for="section in navigation" :key="section.title" class="nav-section">
        <span class="nav-section-title">{{ section.title }}</span>
        <router-link
          v-for="item in section.items"
          :key="item.path"
          :to="item.path"
          class="nav-item"
          :class="{ active: currentPath === item.path }"
        >
          <span class="nav-icon">{{ item.icon }}</span>
          <span class="nav-label">{{ item.label }}</span>
          <span
            v-if="item.badge"
            class="nav-badge"
            :class="`nav-badge-${item.badgeColor}`"
          >
            {{ item.badge }}
          </span>
          <span v-if="currentPath === item.path" class="active-dot"></span>
        </router-link>
      </div>
    </nav>

    <!-- User profile at bottom -->
    <div class="sidebar-user">
      <div class="user-avatar">
        {{ auth.userName.slice(0, 2).toUpperCase() }}
      </div>
      <div class="user-info">
        <span class="user-name">{{ auth.userName }}</span>
        <span class="user-role">{{ auth.userRole }} RT</span>
      </div>
      <span class="user-status"></span>
    </div>
  </aside>
</template>

<style scoped>
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  width: var(--sidebar-width);
  height: 100vh;
  background: var(--bg-sidebar);
  border-right: 1px solid var(--border-color);
  display: flex;
  flex-direction: column;
  z-index: 100;
  overflow-y: auto;
  overflow-x: hidden;
}

/* Logo */
.sidebar-logo {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 20px 22px;
  border-bottom: 1px solid var(--border-color);
}

.logo-text {
  display: flex;
  flex-direction: column;
}

.logo-title {
  font-size: 1.25rem;
  font-weight: 800;
  color: var(--text-heading);
  letter-spacing: -0.5px;
}

.logo-subtitle {
  font-size: 0.6rem;
  font-weight: 500;
  color: var(--text-muted);
  letter-spacing: 1.5px;
  text-transform: uppercase;
}

/* Navigation */
.sidebar-nav {
  flex: 1;
  padding: 16px 12px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.nav-section {
  margin-bottom: 8px;
}

.nav-section-title {
  display: block;
  padding: 8px 12px 6px;
  font-size: 0.65rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 1.2px;
  color: var(--text-muted);
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  border-radius: var(--radius-md);
  color: var(--text-secondary);
  font-size: 0.85rem;
  font-weight: 400;
  transition: all var(--transition-fast);
  text-decoration: none;
  position: relative;
}

.nav-item:hover {
  background: var(--bg-card);
  color: var(--text-primary);
}

.nav-item.active {
  background: var(--accent-blue-dim);
  color: var(--accent-blue-light);
  font-weight: 500;
}

.nav-icon {
  font-size: 1rem;
  width: 22px;
  text-align: center;
  flex-shrink: 0;
}

.nav-label {
  flex: 1;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.nav-badge {
  font-size: 0.65rem;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: var(--radius-full);
  min-width: 20px;
  text-align: center;
}

.nav-badge-blue {
  background: var(--accent-blue);
  color: white;
}

.nav-badge-orange {
  background: var(--accent-orange);
  color: #1a1a1a;
}

.nav-badge-red {
  background: var(--accent-red);
  color: white;
}

.active-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--accent-green);
  flex-shrink: 0;
  box-shadow: 0 0 8px var(--accent-green);
}

/* User at bottom */
.sidebar-user {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 16px 18px;
  border-top: 1px solid var(--border-color);
  margin-top: auto;
}

.user-avatar {
  width: 36px;
  height: 36px;
  border-radius: var(--radius-md);
  background: linear-gradient(135deg, var(--accent-blue), var(--accent-purple));
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  font-weight: 700;
  color: white;
  flex-shrink: 0;
}

.user-info {
  display: flex;
  flex-direction: column;
  min-width: 0;
}

.user-name {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-primary);
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-role {
  font-size: 0.7rem;
  color: var(--text-muted);
}

.user-status {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: var(--accent-green);
  margin-left: auto;
  flex-shrink: 0;
  box-shadow: 0 0 8px var(--accent-green);
}
</style>
