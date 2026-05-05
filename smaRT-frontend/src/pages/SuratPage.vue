<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { suratApi } from '@/services/api'

const suratList = ref<any[]>([])
const loading = ref(true)
const error = ref('')

async function fetchSurat() {
  try {
    loading.value = true
    const res = await suratApi.list()
    suratList.value = res.data.data || []
  } catch (e: any) {
    error.value = 'Gagal mengambil data surat'
  } finally {
    loading.value = false
  }
}

const reviewModal = ref(false)
const selectedSurat = ref<any>(null)
const reviewStatus = ref<'APPROVED'|'REJECTED'>('APPROVED')
const reviewFile = ref<File | null>(null)
const submitLoading = ref(false)

function getFileUrl(path: string) {
  return `http://localhost:8000/storage/${path}`
}

function openReview(s: any, status: 'APPROVED' | 'REJECTED') {
  selectedSurat.value = s
  reviewStatus.value = status
  reviewFile.value = null
  reviewModal.value = true
}

function handleFile(e: Event) {
  const target = e.target as HTMLInputElement
  if (target.files && target.files[0]) {
    reviewFile.value = target.files[0]
  }
}

async function submitReview() {
  submitLoading.value = true
  const formData = new FormData()
  formData.append('id', selectedSurat.value.id)
  formData.append('status', reviewStatus.value)
  if (reviewFile.value) {
    formData.append('file_final', reviewFile.value)
  }

  try {
    await suratApi.review(formData)
    reviewModal.value = false
    fetchSurat()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Gagal mengubah status surat')
  } finally {
    submitLoading.value = false
  }
}

function statusClass(s: string) {
  if (s === 'PENDING') return 'badge-pending'
  if (s === 'APPROVED') return 'badge-approved'
  if (s === 'DIPROSES') return 'badge-orange' /* assuming custom or we can map it */
  if (s === 'SELESAI') return 'badge-green'
  return 'badge-rejected'
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

onMounted(() => {
  fetchSurat()
})
</script>

<template>
  <div class="surat-page">
    <div class="page-header">
      <div>
        <h2 class="page-title">Manajemen Surat</h2>
        <p class="page-subtitle">Tinjau dan setujui pengajuan surat dari warga</p>
      </div>
    </div>

    <div class="card table-card">
      <div v-if="loading" class="loading">Memuat data...</div>
      <div v-else-if="error" class="error">{{ error }}</div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>PEMOHON</th>
            <th>JENIS SURAT</th>
            <th>DESKRIPSI</th>
            <th>LAMPIRAN</th>
            <th>WAKTU PENGUSULAN</th>
            <th>STATUS</th>
            <th class="text-right">AKSI</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!suratList.length">
            <td colspan="7" class="empty-state">Belum ada data pengajuan surat.</td>
          </tr>
          <tr v-for="s in suratList" :key="s.id">
            <td class="font-medium text-primary">{{ s.user?.nama || 'Warga' }}</td>
            <td class="font-medium">{{ s.nama_surat }}</td>
            <td class="text-sm text-muted desc-col">{{ s.deskripsi_surat }}</td>
            <td class="text-sm">
              <a v-if="s.dokumen_pendukung" :href="getFileUrl(s.dokumen_pendukung)" target="_blank" class="text-blue hover-underline">Unduh</a>
              <span v-else class="text-muted">-</span>
            </td>
            <td class="text-sm">{{ timeAgo(s.created_at) }}</td>
            <td>
              <span class="badge" :class="statusClass(s.status)">
                {{ s.status === 'APPROVED' ? 'DISETUJUI' : (s.status === 'REJECTED' ? 'DITOLAK' : s.status) }}
              </span>
            </td>
            <td class="text-right action-btns">
              <template v-if="s.status === 'PENDING' || s.status === 'DIPROSES'">
                <button class="btn btn-sm btn-outline text-green hover-bg-green" @click="openReview(s, 'APPROVED')">
                  Setujui
                </button>
                <button class="btn btn-sm btn-outline text-red hover-bg-red" @click="openReview(s, 'REJECTED')">
                  Tolak
                </button>
              </template>
              <template v-else>
                <a v-if="s.file_final" :href="getFileUrl(s.file_final)" target="_blank" class="btn btn-sm btn-outline text-blue hover-bg-blue">Lihat Surat</a>
                <span v-else class="text-sm text-muted">Selesai</span>
              </template>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Review Surat -->
    <div v-if="reviewModal" class="modal-overlay" @click.self="reviewModal = false">
      <div class="modal-content card">
        <h3>{{ reviewStatus === 'APPROVED' ? 'Setujui Surat' : 'Tolak Surat' }}</h3>
        <p class="text-sm text-muted mb-4">Pemohon: <strong>{{ selectedSurat?.user?.nama }}</strong> ({{ selectedSurat?.nama_surat }})</p>
        
        <form @submit.prevent="submitReview" class="form-grid">
          <div class="form-group" style="grid-column: 1 / -1;">
            <label>Unggah File Balasan / Surat Final (Opsional)</label>
            <input type="file" @change="handleFile" class="input" accept=".pdf,.jpg,.jpeg,.png" />
            <span class="text-sm text-muted mt-1">Hanya jika diperlukan (PDF/JPG/PNG max 5MB).</span>
          </div>

          <div class="modal-actions" style="grid-column: 1 / -1;">
            <button type="button" class="btn btn-outline" @click="reviewModal = false">Batal</button>
            <button type="submit" class="btn btn-primary" :class="reviewStatus === 'APPROVED' ? 'bg-green' : 'bg-red'" :disabled="submitLoading">
              {{ submitLoading ? 'Menyimpan...' : (reviewStatus === 'APPROVED' ? 'Ya, Setujui' : 'Ya, Tolak') }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
.surat-page {
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

.desc-col {
  max-width: 250px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

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

.text-green { color: var(--accent-green); border-color: var(--accent-green-dim); }
.hover-bg-green:hover { background: var(--accent-green); color: white; border-color: var(--accent-green); }

.text-red { color: var(--accent-red); border-color: var(--accent-red-dim); }
.hover-bg-red:hover { background: var(--accent-red); color: white; border-color: var(--accent-red); }

.loading, .error, .empty-state {
  text-align: center;
  padding: 40px;
  color: var(--text-muted);
}
.error { color: var(--accent-red); }

.text-blue { color: var(--accent-blue); }
.hover-underline:hover { text-decoration: underline; }
.hover-bg-blue:hover { background: var(--accent-blue); color: white; border-color: var(--accent-blue); }
.mb-4 { margin-bottom: 16px; }
.mt-1 { margin-top: 4px; }
.bg-green { background: var(--accent-green) !important; color: white !important; border-color: var(--accent-green) !important; }
.bg-red { background: var(--accent-red) !important; color: white !important; border-color: var(--accent-red) !important; }

/* Modal Styles */
.modal-overlay {
  position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center;
  z-index: 100;
}
.modal-content {
  width: 100%; max-width: 450px;
  padding: 24px;
}
.modal-content h3 {
  margin-bottom: 8px; color: var(--text-heading); font-size: 1.2rem;
}
.form-grid {
  display: grid; grid-template-columns: 1fr; gap: 16px;
}
.form-group {
  display: flex; flex-direction: column; gap: 6px;
}
.form-group label {
  font-size: 0.8rem; font-weight: 500; color: var(--text-secondary);
}
.input {
  background: var(--bg-dark);
  border: 1px solid var(--border-color);
  color: var(--text-primary);
  padding: 10px 12px;
  border-radius: var(--radius-md);
  font-size: 0.9rem;
  transition: border-color var(--transition-fast);
}
.input:focus {
  outline: none; border-color: var(--accent-blue);
}
.modal-actions {
  display: flex; justify-content: flex-end; gap: 12px; margin-top: 12px;
}
</style>
