<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { panicApi } from '@/services/api'

const laporanList = ref<any[]>([])
const loading = ref(true)
const error = ref('')

async function fetchLaporan() {
  try {
    loading.value = true
    const res = await panicApi.list()
    laporanList.value = res.data.data || []
  } catch (e: any) {
    error.value = 'Gagal mengambil data laporan'
  } finally {
    loading.value = false
  }
}

function timeAgo(dateStr: string): string {
  if (!dateStr) return ''
  const now = new Date()
  const d = new Date(dateStr)
  const diff = Math.floor((now.getTime() - d.getTime()) / 60000)
  if (diff < 1) return `Baru saja`
  if (diff < 60) return `${diff} mnt lalu`
  if (diff < 1440) return `${Math.floor(diff / 60)} jam lalu`
  return `${Math.floor(diff / 1440)} hr lalu`
}

function formatTanggal(dateStr: string): string {
  if (!dateStr) return ''
  const d = new Date(dateStr)
  return d.toLocaleString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}

onMounted(() => {
  fetchLaporan()
})
</script>

<template>
  <div class="laporan-page">
    <div class="page-header">
      <div>
        <h2 class="page-title">Laporan Darurat Warga</h2>
        <p class="page-subtitle">Pantau sinyal darurat (SOS) dan laporan dari warga</p>
      </div>
    </div>

    <div class="card table-card">
      <div v-if="loading" class="loading">Memuat data...</div>
      <div v-else-if="error" class="error">{{ error }}</div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>PELAPOR</th>
            <th>KATEGORI</th>
            <th>WAKTU LAPORAN</th>
            <th>KOORDINAT / LOKASI</th>
            <th>STATUS</th>
            <th class="text-right">AKSI</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!laporanList.length">
            <td colspan="6" class="empty-state">Belum ada laporan darurat.</td>
          </tr>
          <tr v-for="log in laporanList" :key="log.id">
            <td>
              <div class="font-medium text-primary">{{ log.user?.nama || 'Warga' }}</div>
              <div class="text-sm text-muted">{{ log.user?.phone || '-' }}</div>
            </td>
            <td>
              <span class="badge badge-red">SOS DARURAT</span>
            </td>
            <td>
              <div class="font-medium">{{ timeAgo(log.created_at) }}</div>
              <div class="text-sm text-muted">{{ formatTanggal(log.created_at) }}</div>
            </td>
            <td class="font-mono text-sm text-muted">
              {{ (log.latitude + '').substring(0, 8) }}, {{ (log.longitude + '').substring(0, 8) }}
            </td>
            <td>
              <span class="badge badge-orange">BUTUH TINDAKAN</span>
            </td>
            <td class="text-right action-btns">
              <a :href="`https://maps.google.com/?q=${log.latitude},${log.longitude}`" target="_blank" class="btn btn-sm btn-outline text-blue hover-bg-blue">
                Buka Peta ↗
              </a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.laporan-page {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.page-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text-heading);
}
.page-subtitle {
  font-size: 0.9rem;
  color: var(--text-muted);
}
.table-card {
  padding: 0;
  overflow: hidden;
}
.data-table {
  width: 100%;
  border-collapse: collapse;
}
.data-table th, .data-table td {
  padding: 14px 20px;
  text-align: left;
  border-bottom: 1px solid var(--border-color);
}
.data-table th {
  font-size: 0.75rem;
  font-weight: 600;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  background: var(--bg-card-hover);
}
.data-table td {
  font-size: 0.88rem;
  color: var(--text-secondary);
}
.data-table tr:last-child td { border-bottom: none; }
.data-table tbody tr:hover { background: var(--bg-card-hover); }

.action-btns {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.text-right { text-align: right !important; }
.font-medium { font-weight: 500; }
.text-primary { color: var(--text-primary); }
.text-sm { font-size: 0.8rem; }
.text-muted { color: var(--text-muted); }
.font-mono { font-family: var(--font-mono); }

.badge-red { background: var(--accent-red-dim); color: var(--accent-red); }
.badge-orange { background: var(--accent-orange-dim); color: var(--accent-orange); }

.text-blue { color: var(--accent-blue); border-color: var(--accent-blue-dim); }
.hover-bg-blue:hover { background: var(--accent-blue); color: white; border-color: var(--accent-blue); }

.loading, .error, .empty-state {
  text-align: center;
  padding: 40px;
  color: var(--text-muted);
}
.error { color: var(--accent-red); }
</style>
