<script setup lang="ts">
import SidebarNav from '@/components/SidebarNav.vue'
import TopBar from '@/components/TopBar.vue'
import { useRoute } from 'vue-router'
import { computed } from 'vue'

const route = useRoute()

const pageTitle = computed(() => {
  const titles: Record<string, string> = {
    '/': 'Overview & Statistik',
    '/warga': 'Pengguna / Warga',
    '/surat': 'Surat Pengantar',
    '/laporan': 'Laporan Warga',
    '/agenda': 'Agenda Kegiatan',
    '/keuangan': 'Keuangan (Hashchain)',
    '/log': 'Log Aktivitas / SOS',
    '/pengaturan': 'Pengaturan',
  }
  return titles[route.path] || 'Dashboard'
})

const breadcrumb = computed(() => {
  return `smaRT / dashboard / ${pageTitle.value.toLowerCase()}`
})
</script>

<template>
  <div class="layout">
    <SidebarNav />
    <div class="layout-main">
      <TopBar :title="pageTitle" :breadcrumb="breadcrumb" />
      <main class="layout-content">
        <router-view />
      </main>
    </div>
  </div>
</template>

<style scoped>
.layout {
  display: flex;
  min-height: 100vh;
}

.layout-main {
  flex: 1;
  margin-left: var(--sidebar-width);
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

.layout-content {
  flex: 1;
  padding: 24px 32px;
  overflow-y: auto;
}
</style>
