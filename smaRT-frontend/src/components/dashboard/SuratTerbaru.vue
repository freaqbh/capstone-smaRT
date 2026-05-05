<script setup lang="ts">
const props = defineProps<{ surat: any[] }>()
function statusClass(s: string) {
  if (s === 'PENDING') return 'badge-pending'
  if (s === 'APPROVED') return 'badge-approved'
  return 'badge-rejected'
}
function statusLabel(s: string) {
  if (s === 'APPROVED') return 'DISETUJUI'
  if (s === 'REJECTED') return 'DITOLAK'
  return s
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
</script>

<template>
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Pengajuan Surat Terbaru</h3>
      <router-link to="/surat" class="btn btn-outline btn-sm">Lihat semua ↗</router-link>
    </div>
    <table class="data-table">
      <thead>
        <tr>
          <th>PEMOHON</th><th>JENIS SURAT</th><th>STATUS</th><th>WAKTU</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="!surat?.length">
          <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 20px;">Belum ada pengajuan surat</td>
        </tr>
        <tr v-for="s in surat" :key="s.id">
          <td style="color: var(--text-primary); font-weight: 500;">{{ s.user?.nama || 'Warga' }}</td>
          <td>{{ s.nama_surat }}</td>
          <td><span class="badge" :class="statusClass(s.status)">{{ statusLabel(s.status) }}</span></td>
          <td>{{ timeAgo(s.created_at) }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.card-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 14px;
}
.card-title { font-size: 0.95rem; font-weight: 600; color: var(--text-primary); }
</style>
