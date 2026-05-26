<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { laporanApi } from '@/services/api'

const laporanList = ref<any[]>([])
const loading = ref(true)
const error = ref('')

async function fetchLaporan() {
  try {
    loading.value = true
    const res = await laporanApi.list()
    laporanList.value = res.data.data || []
  } catch (e: any) {
    error.value = 'Gagal mengambil data laporan'
  } finally {
    loading.value = false
  }
}

// ─── Status Update ──────────────────────────────────────
const statusModal = ref(false)
const selectedLaporan = ref<any>(null)
const newStatus = ref<'DIPROSES' | 'SELESAI'>('SELESAI')
const submitLoading = ref(false)

function openStatusModal(laporan: any, status: 'DIPROSES' | 'SELESAI') {
  selectedLaporan.value = laporan
  newStatus.value = status
  statusModal.value = true
}

async function submitStatusUpdate() {
  submitLoading.value = true
  try {
    await laporanApi.updateStatus(selectedLaporan.value.id, newStatus.value)
    statusModal.value = false
    fetchLaporan()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Gagal mengubah status laporan')
  } finally {
    submitLoading.value = false
  }
}

// ─── Detail Modal ───────────────────────────────────────
const detailModal = ref(false)
const detailLaporan = ref<any>(null)

function openDetail(laporan: any) {
  detailLaporan.value = laporan
  detailModal.value = true
}

function getFileUrl(path: string) {
  return `http://localhost:8000/storage/${path}`
}

// ─── Helpers ────────────────────────────────────────────
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

function statusClass(s: string) {
  if (s === 'DIPROSES') return 'badge-orange'
  if (s === 'SELESAI') return 'badge-green'
  return 'badge-blue'
}

function kategoriClass(k: string) {
  const lower = k?.toLowerCase() || ''
  if (lower.includes('infrastruktur') || lower.includes('jalan')) return 'badge-red'
  if (lower.includes('kebersihan') || lower.includes('sampah')) return 'badge-orange'
  if (lower.includes('keamanan')) return 'badge-purple'
  if (lower.includes('fasilitas')) return 'badge-cyan'
  return 'badge-blue'
}

onMounted(() => {
  fetchLaporan()
})
</script>

<template>
  <div class="laporan-page">
    <div class="page-header">
      <div>
        <h2 class="page-title">Laporan Warga</h2>
        <p class="page-subtitle">Pantau dan kelola laporan permasalahan dari warga</p>
      </div>
      <div class="header-stats">
        <div class="stat-pill">
          <span class="stat-dot dot-orange"></span>
          <span class="stat-label">Diproses</span>
          <span class="stat-count">{{ laporanList.filter(l => l.status === 'DIPROSES').length }}</span>
        </div>
        <div class="stat-pill">
          <span class="stat-dot dot-green"></span>
          <span class="stat-label">Selesai</span>
          <span class="stat-count">{{ laporanList.filter(l => l.status === 'SELESAI').length }}</span>
        </div>
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
            <th>DESKRIPSI</th>
            <th>LOKASI</th>
            <th>FOTO</th>
            <th>WAKTU</th>
            <th>STATUS</th>
            <th class="text-right">AKSI</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!laporanList.length">
            <td colspan="8" class="empty-state">Belum ada laporan dari warga.</td>
          </tr>
          <tr v-for="lap in laporanList" :key="lap.id">
            <td>
              <div class="font-medium text-primary">{{ lap.user?.nama || 'Warga' }}</div>
            </td>
            <td>
              <span class="badge" :class="kategoriClass(lap.kategori_masalah)">
                {{ lap.kategori_masalah }}
              </span>
            </td>
            <td class="text-sm text-muted desc-col">{{ lap.deskripsi }}</td>
            <td class="text-sm">{{ lap.lokasi }}</td>
            <td class="text-sm">
              <a v-if="lap.foto" :href="getFileUrl(lap.foto)" target="_blank" class="foto-link">
                <span class="foto-icon">📷</span> Lihat
              </a>
              <span v-else class="text-muted">—</span>
            </td>
            <td>
              <div class="font-medium text-sm">{{ timeAgo(lap.created_at) }}</div>
              <div class="text-xs text-muted">{{ formatTanggal(lap.created_at) }}</div>
            </td>
            <td>
              <span class="badge" :class="statusClass(lap.status)">
                {{ lap.status }}
              </span>
            </td>
            <td class="text-right action-btns">
              <button class="btn btn-sm btn-outline btn-detail" @click="openDetail(lap)">
                Detail
              </button>
              <template v-if="lap.status === 'DIPROSES'">
                <button class="btn btn-sm btn-outline text-green hover-bg-green" @click="openStatusModal(lap, 'SELESAI')">
                  Selesaikan
                </button>
              </template>
              <template v-else>
                <span class="text-xs text-muted">Selesai ✓</span>
              </template>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Detail Laporan -->
    <div v-if="detailModal" class="modal-overlay" @click.self="detailModal = false">
      <div class="modal-content card">
        <div class="modal-header-row">
          <h3>Detail Laporan</h3>
          <button class="btn-close" @click="detailModal = false">✕</button>
        </div>

        <div class="detail-grid">
          <div class="detail-item">
            <span class="detail-label">Pelapor</span>
            <span class="detail-value text-primary">{{ detailLaporan?.user?.nama || 'Warga' }}</span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Kategori</span>
            <span class="badge" :class="kategoriClass(detailLaporan?.kategori_masalah)">
              {{ detailLaporan?.kategori_masalah }}
            </span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Status</span>
            <span class="badge" :class="statusClass(detailLaporan?.status)">
              {{ detailLaporan?.status }}
            </span>
          </div>
          <div class="detail-item">
            <span class="detail-label">Waktu</span>
            <span class="detail-value">{{ formatTanggal(detailLaporan?.created_at) }}</span>
          </div>
          <div class="detail-item full-width">
            <span class="detail-label">Lokasi</span>
            <span class="detail-value">{{ detailLaporan?.lokasi }}</span>
          </div>
          <div class="detail-item full-width">
            <span class="detail-label">Deskripsi</span>
            <p class="detail-desc">{{ detailLaporan?.deskripsi }}</p>
          </div>
          <div v-if="detailLaporan?.foto" class="detail-item full-width">
            <span class="detail-label">Foto Pendukung</span>
            <a :href="getFileUrl(detailLaporan.foto)" target="_blank" class="foto-preview-link">
              <img :src="getFileUrl(detailLaporan.foto)" alt="Foto Laporan" class="foto-preview" />
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Update Status -->
    <div v-if="statusModal" class="modal-overlay" @click.self="statusModal = false">
      <div class="modal-content card">
        <h3>Selesaikan Laporan</h3>
        <p class="text-sm text-muted mb-4">
          Apakah Anda yakin ingin menandai laporan dari
          <strong>{{ selectedLaporan?.user?.nama }}</strong>
          sebagai <strong class="text-green">SELESAI</strong>?
        </p>
        <div class="detail-item mb-4">
          <span class="detail-label">Kategori</span>
          <span class="badge" :class="kategoriClass(selectedLaporan?.kategori_masalah)">
            {{ selectedLaporan?.kategori_masalah }}
          </span>
        </div>
        <div class="modal-actions">
          <button type="button" class="btn btn-outline" @click="statusModal = false">Batal</button>
          <button type="button" class="btn btn-primary bg-green" :disabled="submitLoading" @click="submitStatusUpdate">
            {{ submitLoading ? 'Menyimpan...' : 'Ya, Selesaikan' }}
          </button>
        </div>
      </div>
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
  flex-wrap: wrap;
  gap: 16px;
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

/* ─── Header Stats ──────────────────────────────────── */
.header-stats {
  display: flex;
  gap: 10px;
}
.stat-pill {
  display: flex;
  align-items: center;
  gap: 8px;
  background: var(--bg-card);
  border: 1px solid var(--border-color);
  border-radius: var(--radius-full, 999px);
  padding: 6px 14px;
  font-size: 0.8rem;
}
.stat-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
}
.dot-orange { background: var(--accent-orange); }
.dot-green { background: var(--accent-green); }
.stat-label {
  color: var(--text-muted);
}
.stat-count {
  font-weight: 700;
  color: var(--text-primary);
}

/* ─── Table ─────────────────────────────────────────── */
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

.desc-col {
  max-width: 220px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.action-btns {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 8px;
}

/* ─── Foto Link ─────────────────────────────────────── */
.foto-link {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  color: var(--accent-blue);
  text-decoration: none;
  font-size: 0.82rem;
  transition: color var(--transition-fast);
}
.foto-link:hover {
  color: var(--accent-blue-light);
  text-decoration: underline;
}
.foto-icon { font-size: 0.9rem; }

/* ─── Detail Button ─────────────────────────────────── */
.btn-detail {
  color: var(--accent-cyan);
  border-color: var(--accent-cyan-dim);
}
.btn-detail:hover {
  background: var(--accent-cyan);
  color: white;
  border-color: var(--accent-cyan);
}

/* ─── Utility ───────────────────────────────────────── */
.text-right { text-align: right !important; }
.font-medium { font-weight: 500; }
.text-primary { color: var(--text-primary); }
.text-sm { font-size: 0.8rem; }
.text-xs { font-size: 0.72rem; }
.text-muted { color: var(--text-muted); }
.mb-4 { margin-bottom: 16px; }

.text-green { color: var(--accent-green); border-color: var(--accent-green-dim); }
.hover-bg-green:hover { background: var(--accent-green); color: white; border-color: var(--accent-green); }
.bg-green { background: var(--accent-green) !important; color: white !important; border-color: var(--accent-green) !important; }

.badge-red { background: var(--accent-red-dim); color: var(--accent-red); }
.badge-orange { background: var(--accent-orange-dim); color: var(--accent-orange); }
.badge-green { background: var(--accent-green-dim); color: var(--accent-green); }
.badge-blue { background: var(--accent-blue-dim); color: var(--accent-blue); }
.badge-purple { background: var(--accent-purple-dim); color: var(--accent-purple); }
.badge-cyan { background: var(--accent-cyan-dim); color: var(--accent-cyan); }

/* ─── Modal ─────────────────────────────────────────── */
.modal-overlay {
  position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0, 0, 0, 0.55);
  backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center;
  z-index: 100;
}
.modal-content {
  width: 100%; max-width: 520px;
  padding: 24px;
  animation: modal-in 0.2s ease-out;
}
@keyframes modal-in {
  from { opacity: 0; transform: translateY(12px) scale(0.97); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}
.modal-content h3 {
  margin-bottom: 8px;
  color: var(--text-heading);
  font-size: 1.2rem;
}
.modal-header-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}
.btn-close {
  background: none;
  border: 1px solid var(--border-color);
  color: var(--text-muted);
  width: 32px;
  height: 32px;
  border-radius: var(--radius-md);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9rem;
  transition: all var(--transition-fast);
}
.btn-close:hover {
  background: var(--accent-red-dim);
  color: var(--accent-red);
  border-color: var(--accent-red-dim);
}
.modal-actions {
  display: flex; justify-content: flex-end; gap: 12px; margin-top: 16px;
}

/* ─── Detail Grid ───────────────────────────────────── */
.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
}
.detail-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}
.detail-item.full-width {
  grid-column: 1 / -1;
}
.detail-label {
  font-size: 0.72rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: var(--text-muted);
}
.detail-value {
  font-size: 0.9rem;
  color: var(--text-secondary);
}
.detail-desc {
  font-size: 0.88rem;
  color: var(--text-secondary);
  line-height: 1.6;
  margin: 0;
  white-space: pre-wrap;
}

/* ─── Foto Preview ──────────────────────────────────── */
.foto-preview-link {
  display: inline-block;
  border-radius: var(--radius-md);
  overflow: hidden;
  border: 1px solid var(--border-color);
  transition: border-color var(--transition-fast);
}
.foto-preview-link:hover {
  border-color: var(--accent-blue);
}
.foto-preview {
  display: block;
  max-width: 100%;
  max-height: 240px;
  object-fit: cover;
  border-radius: var(--radius-md);
}

/* ─── Loading & Empty ───────────────────────────────── */
.loading, .error, .empty-state {
  text-align: center;
  padding: 40px;
  color: var(--text-muted);
}
.error { color: var(--accent-red); }
</style>
