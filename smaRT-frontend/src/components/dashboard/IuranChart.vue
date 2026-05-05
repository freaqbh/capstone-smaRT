<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Tooltip, Legend } from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend)

const props = defineProps<{ history: any[] }>()
const activeTab = ref<'Masuk' | 'Pengeluaran'>('Masuk')

const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul']

const chartData = computed(() => {
  const masukData = months.map(() => Math.floor(Math.random() * 800000 + 200000))
  const keluarData = months.map(() => Math.floor(Math.random() * 400000 + 100000))

  // Use real data if available
  if (props.history.length > 0) {
    const byMonth: Record<string, { masuk: number; keluar: number }> = {}
    props.history.forEach((tx) => {
      const d = new Date(tx.created_at)
      const m = d.getMonth()
      const key = months[m] || 'Jan'
      if (!byMonth[key]) byMonth[key] = { masuk: 0, keluar: 0 }
      if (tx.jenis_kas === 'PEMASUKAN') byMonth[key]!.masuk += tx.nominal
      else byMonth[key]!.keluar += tx.nominal
    })
    months.forEach((m, i) => {
      if (byMonth[m]) {
        masukData[i] = byMonth[m]!.masuk
        keluarData[i] = byMonth[m]!.keluar
      }
    })
  }

  return {
    labels: months,
    datasets: [
      {
        label: 'Masuk',
        data: masukData,
        backgroundColor: activeTab.value === 'Masuk' ? '#3b82f6' : 'rgba(59,130,246,0.25)',
        borderRadius: 6,
        barPercentage: 0.7,
      },
      {
        label: 'Pengeluaran',
        data: keluarData,
        backgroundColor: activeTab.value === 'Pengeluaran' ? '#22c55e' : 'rgba(34,197,94,0.25)',
        borderRadius: 6,
        barPercentage: 0.7,
      },
    ],
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#1e293b',
      titleColor: '#e8ecf4',
      bodyColor: '#8892a4',
      borderColor: '#2a3548',
      borderWidth: 1,
      cornerRadius: 8,
      padding: 10,
    },
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { color: '#5c6578', font: { size: 11 } },
      border: { display: false },
    },
    y: {
      display: false,
      grid: { display: false },
    },
  },
}
</script>

<template>
  <div class="card chart-card">
    <div class="chart-header">
      <h3 class="chart-title">Iuran Bulanan 2026</h3>
      <div class="chart-tabs">
        <button class="tab" :class="{ active: activeTab === 'Masuk' }" @click="activeTab = 'Masuk'">Masuk</button>
        <button class="tab" :class="{ active: activeTab === 'Pengeluaran' }" @click="activeTab = 'Pengeluaran'">Pengeluaran</button>
      </div>
    </div>
    <div class="chart-container">
      <Bar :data="chartData" :options="chartOptions" />
    </div>
  </div>
</template>

<style scoped>
.chart-card { padding: 20px; }
.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
.chart-title { font-size: 0.95rem; font-weight: 600; color: var(--text-primary); }
.chart-tabs { display: flex; gap: 4px; }
.tab {
  padding: 5px 14px;
  border-radius: var(--radius-full);
  font-size: 0.78rem;
  font-weight: 500;
  color: var(--text-muted);
  border: 1px solid var(--border-color);
  transition: all var(--transition-fast);
}
.tab.active {
  background: var(--accent-blue-dim);
  color: var(--accent-blue-light);
  border-color: var(--accent-blue);
}
.tab:hover:not(.active) { border-color: var(--border-light); color: var(--text-secondary); }
.chart-container { height: 220px; }
</style>
