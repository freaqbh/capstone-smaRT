<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { dashboardApi } from '@/services/api'
import StatsCards from '@/components/dashboard/StatsCards.vue'
import AlertBanner from '@/components/dashboard/AlertBanner.vue'
import IuranChart from '@/components/dashboard/IuranChart.vue'
import HashchainLedger from '@/components/dashboard/HashchainLedger.vue'
import SuratTerbaru from '@/components/dashboard/SuratTerbaru.vue'
import LogAktivitas from '@/components/dashboard/LogAktivitas.vue'
import LaporanWarga from '@/components/dashboard/LaporanWarga.vue'
import AgendaKegiatan from '@/components/dashboard/AgendaKegiatan.vue'

const dashboardData = ref<any>(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const dashboardRes = await dashboardApi.get()
    dashboardData.value = dashboardRes.data.data
  } catch (e) {
    console.error('Dashboard load error:', e)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="overview">
    <!-- Alert Banner -->
    <AlertBanner class="animate-fade-in-up delay-1" />

    <!-- Section: Ringkasan -->
    <div class="section-header animate-fade-in-up delay-2">
      <h2 class="section-title">ringkasan hari ini</h2>
      <button class="btn btn-outline btn-sm">Ekspor Laporan ↗</button>
    </div>

    <StatsCards :dashboardData="dashboardData" class="animate-fade-in-up delay-2" />

    <!-- Row: Chart + Hashchain -->
    <div class="grid-2 animate-fade-in-up delay-3">
      <IuranChart :history="dashboardData?.blocks || []" />
      <HashchainLedger :history="dashboardData?.blocks || []" :monitor="dashboardData?.kas_summary || null" />
    </div>

    <!-- Row: Surat + Log -->
    <div class="grid-2 animate-fade-in-up delay-4">
      <SuratTerbaru :surat="dashboardData?.recent_surat || []" />
      <LogAktivitas :activities="dashboardData?.recent_activities || []" />
    </div>

    <!-- Row: Laporan + Agenda -->
    <div class="grid-2 animate-fade-in-up delay-5">
      <LaporanWarga :laporan="dashboardData?.laporan_warga || []" />
      <AgendaKegiatan :agenda="dashboardData?.recent_agenda || []" />
    </div>
  </div>
</template>

<style scoped>
.overview {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-top: 4px;
}
.section-title {
  font-size: 0.95rem;
  font-weight: 600;
  color: var(--text-secondary);
}
.grid-2 {
  display: grid;
  grid-template-columns: 1.4fr 1fr;
  gap: 20px;
}
@media (max-width: 1100px) {
  .grid-2 { grid-template-columns: 1fr; }
}
</style>
