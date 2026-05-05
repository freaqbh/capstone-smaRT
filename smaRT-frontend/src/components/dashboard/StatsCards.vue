<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ dashboardData: any }>()

function formatRupiah(val: number): string {
  if (!val) return 'Rp 0'
  if (val >= 1000000) return `Rp ${(val / 1000000).toFixed(1).replace('.0', '')}jt`
  if (val >= 1000) return `Rp ${(val / 1000).toFixed(0)}rb`
  return `Rp ${val.toLocaleString('id-ID')}`
}

const cards = computed(() => {
  const d = props.dashboardData || {}
  
  return [
    { key: 'warga', label: 'TOTAL WARGA', icon: '👥', color: 'blue', sub: 'Total pengguna aktif', value: d.users?.total || 0 },
    { key: 'surat', label: 'SURAT PENDING', icon: '✉️', color: 'blue', sub: `${d.surat?.diproses || 0} diproses, ${d.surat?.selesai || 0} selesai`, value: d.surat?.pending || 0 },
    { key: 'saldo', label: 'KAS RT SALDO', icon: '💳', color: 'green', sub: `Pemasukan: ${d.kas_summary?.total_pemasukan ? formatRupiah(d.kas_summary.total_pemasukan) : 'Rp 0'}`, value: d.kas_summary?.saldo ? formatRupiah(d.kas_summary.saldo) : 'Rp 0' },
    { key: 'blocks', label: 'TOTAL TRANSAKSI', icon: '🔗', color: 'orange', sub: 'Hashchain records', value: d.kas_summary?.total_blocks || 0 },
  ]
})
</script>

<template>
  <div class="stats-grid">
    <div v-for="(card, i) in cards" :key="card.key" class="stat-card card">
      <div class="stat-header">
        <span class="stat-label">{{ card.label }}</span>
        <span class="stat-icon">{{ card.icon }}</span>
      </div>
      <div class="stat-value" :class="`text-${card.color}`">
        {{ card.value }}
      </div>
      <span class="stat-sub" :class="`sub-${card.color}`">{{ card.sub }}</span>
      <div class="stat-bars">
        <span v-for="j in 8" :key="j" class="bar" :class="`bar-${card.color}`"
          :style="{ height: `${12 + Math.random() * 16}px`, opacity: j === 8 ? 1 : 0.5 }" />
      </div>
    </div>
  </div>
</template>

<style scoped>
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 16px;
}
@media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 600px) { .stats-grid { grid-template-columns: 1fr; } }

.stat-card {
  padding: 18px 20px;
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.stat-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.stat-label {
  font-size: 0.68rem;
  font-weight: 600;
  letter-spacing: 1px;
  text-transform: uppercase;
  color: var(--text-muted);
}
.stat-icon { font-size: 1.4rem; opacity: 0.7; }
.stat-value {
  font-size: 2rem;
  font-weight: 800;
  margin: 4px 0 2px;
}
.text-blue { color: var(--accent-blue-light); }
.text-green { color: var(--accent-green); }
.text-orange { color: var(--accent-orange); }
.stat-sub {
  font-size: 0.72rem;
  font-weight: 500;
}
.sub-blue { color: var(--accent-blue-light); }
.sub-green { color: var(--accent-green); }
.sub-orange { color: var(--accent-orange); }
.stat-bars {
  display: flex;
  align-items: flex-end;
  gap: 5px;
  margin-top: 10px;
}
.bar {
  flex: 1;
  border-radius: 3px;
  min-height: 10px;
}
.bar-blue { background: var(--accent-blue); }
.bar-green { background: var(--accent-green); }
.bar-orange { background: var(--accent-orange); }
</style>
