<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { broadcastApi } from '@/services/api'

const agendaList = ref<any[]>([])
const loading = ref(true)
const error = ref('')

const showModal = ref(false)
const submitLoading = ref(false)
const form = ref({
  judul: '',
  kategori: 'INFORMASI' as 'INFORMASI' | 'DARURAT' | 'KEGIATAN',
  isi_pesan: ''
})

async function fetchAgenda() {
  try {
    loading.value = true
    const res = await broadcastApi.list(50) // fetch up to 50
    agendaList.value = res.data.data || []
  } catch (e: any) {
    error.value = 'Gagal mengambil data pengumuman'
  } finally {
    loading.value = false
  }
}

async function handleCreate() {
  submitLoading.value = true
  try {
    await broadcastApi.send(form.value)
    showModal.value = false
    form.value = { judul: '', kategori: 'INFORMASI', isi_pesan: '' }
    fetchAgenda()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Gagal mengirim pengumuman')
  } finally {
    submitLoading.value = false
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

function katClass(k: string) {
  if (k === 'DARURAT') return 'badge-red'
  if (k === 'KEGIATAN') return 'badge-orange'
  return 'badge-blue'
}

onMounted(() => {
  fetchAgenda()
})
</script>

<template>
  <div class="agenda-page">
    <div class="page-header">
      <div>
        <h2 class="page-title">Manajemen Agenda & Pengumuman</h2>
        <p class="page-subtitle">Kirim dan kelola broadcast informasi warga</p>
      </div>
      <button class="btn btn-primary" @click="showModal = true">
        + Buat Pengumuman
      </button>
    </div>

    <!-- Modal Create -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-content card">
        <h3>Buat Pengumuman Baru</h3>
        <p class="text-sm text-muted mb-4">Pengumuman ini akan disiarkan dan dapat dilihat oleh semua warga di RT terkait.</p>
        
        <form @submit.prevent="handleCreate" class="form-grid">
          <div class="form-group" style="grid-column: 1 / -1;">
            <label>Judul Pengumuman</label>
            <input v-model="form.judul" type="text" required class="input" placeholder="Contoh: Rapat Rutin Bulanan" />
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label>Kategori</label>
            <select v-model="form.kategori" class="input" required>
              <option value="INFORMASI">INFORMASI</option>
              <option value="KEGIATAN">KEGIATAN</option>
              <option value="DARURAT">DARURAT</option>
            </select>
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label>Isi Pesan / Deskripsi</label>
            <textarea v-model="form.isi_pesan" required class="input" rows="4" placeholder="Tuliskan isi pesan pengumuman..."></textarea>
          </div>

          <div class="modal-actions" style="grid-column: 1 / -1;">
            <button type="button" class="btn btn-outline" @click="showModal = false">Batal</button>
            <button type="submit" class="btn btn-primary" :disabled="submitLoading">
              {{ submitLoading ? 'Mengirim...' : 'Kirim Sekarang' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="card table-card">
      <div v-if="loading" class="loading">Memuat data...</div>
      <div v-else-if="error" class="error">{{ error }}</div>
      <table v-else class="data-table">
        <thead>
          <tr>
            <th>WAKTU SIARAN</th>
            <th>KATEGORI</th>
            <th>JUDUL PENGUMUMAN</th>
            <th>PENGIRIM</th>
            <th>ISI PESAN</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!agendaList.length">
            <td colspan="5" class="empty-state">Belum ada pengumuman / broadcast.</td>
          </tr>
          <tr v-for="agenda in agendaList" :key="agenda.id">
            <td>
              <div class="font-medium">{{ timeAgo(agenda.created_at) }}</div>
              <div class="text-sm text-muted">{{ formatTanggal(agenda.created_at) }}</div>
            </td>
            <td>
              <span class="badge" :class="katClass(agenda.kategori)">{{ agenda.kategori }}</span>
            </td>
            <td class="font-medium text-primary">{{ agenda.judul }}</td>
            <td class="text-sm">{{ agenda.pengurus?.nama || 'Pengurus' }}</td>
            <td class="text-sm text-muted desc-col">{{ agenda.isi_pesan }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.agenda-page {
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
  vertical-align: top;
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
  max-width: 350px;
  line-height: 1.4;
}

.text-primary { color: var(--text-primary); }
.font-medium { font-weight: 500; }
.text-sm { font-size: 0.8rem; }
.text-muted { color: var(--text-muted); }
.mb-4 { margin-bottom: 16px; }

.badge-red { background: var(--accent-red-dim); color: var(--accent-red); }
.badge-orange { background: var(--accent-orange-dim); color: var(--accent-orange); }
.badge-blue { background: var(--accent-blue-dim); color: var(--accent-blue); }

.loading, .error, .empty-state {
  text-align: center;
  padding: 40px;
  color: var(--text-muted);
}
.error { color: var(--accent-red); }

/* Modal Styles */
.modal-overlay {
  position: fixed; top: 0; left: 0; right: 0; bottom: 0;
  background: rgba(0,0,0,0.5); backdrop-filter: blur(4px);
  display: flex; align-items: center; justify-content: center;
  z-index: 100;
}
.modal-content {
  width: 100%; max-width: 500px;
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
  font-family: inherit;
}
.input:focus {
  outline: none; border-color: var(--accent-blue);
}
textarea.input {
  resize: vertical;
}
.modal-actions {
  display: flex; justify-content: flex-end; gap: 12px; margin-top: 12px;
}
</style>
