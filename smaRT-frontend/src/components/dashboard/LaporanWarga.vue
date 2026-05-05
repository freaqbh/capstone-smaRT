<script setup lang="ts">
const props = defineProps<{ laporan: any[] }>()
function katClass(k: string) { return `badge-${k.toLowerCase()}` }
function statusClass(s: string) { return `badge-${s.toLowerCase()}` }
</script>

<template>
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Laporan Darurat</h3>
      <router-link to="/laporan" class="btn btn-outline btn-sm">Lihat semua ↗</router-link>
    </div>
    <table class="data-table">
      <thead><tr><th>PELAPOR</th><th>KATEGORI</th><th>STATUS</th><th>LOKASI</th></tr></thead>
      <tbody>
        <tr v-if="!laporan?.length">
          <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 20px;">Belum ada laporan</td>
        </tr>
        <tr v-for="r in laporan" :key="r.id">
          <td style="color:var(--text-primary);font-weight:500;">{{ r.user?.nama || 'Warga' }}</td>
          <td><span class="badge" :class="katClass('KEAMANAN')">DARURAT</span></td>
          <td><span class="badge" :class="statusClass('BARU')">BARU</span></td>
          <td>{{ (r.latitude + '').substring(0,7) }}, {{ (r.longitude + '').substring(0,7) }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
.card-title { font-size: 0.95rem; font-weight: 600; color: var(--text-primary); }
</style>
