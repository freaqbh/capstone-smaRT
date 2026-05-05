<script setup lang="ts">
import { useRoute } from 'vue-router'

const route = useRoute()
const titles: Record<string, string> = {
  warga: 'Pengguna / Warga',
  surat: 'Surat Pengantar',
  laporan: 'Laporan Warga',
  agenda: 'Agenda Kegiatan',
  keuangan: 'Keuangan (Hashchain)',
  log: 'Log Aktivitas / SOS',
  pengaturan: 'Pengaturan',
}
</script>

<template>
  <div class="placeholder">
    <div class="placeholder-icon">🚧</div>
    <h2>{{ titles[route.name as string] || 'Halaman' }}</h2>
    <p>Halaman ini sedang dalam pengembangan.</p>
  </div>
</template>

<style scoped>
.placeholder {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  min-height: 400px; gap: 12px; color: var(--text-muted);
}
.placeholder-icon { font-size: 3rem; }
.placeholder h2 { font-size: 1.2rem; color: var(--text-secondary); }
.placeholder p { font-size: 0.9rem; }
</style>
