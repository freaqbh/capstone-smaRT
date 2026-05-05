<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { kasApi } from '@/services/api'

const historyList = ref<any[]>([])
const monitorData = ref<any>(null)
const loading = ref(true)
const error = ref('')

const showModal = ref(false)
const submitLoading = ref(false)
const form = ref({
  jenis_kas: 'PEMASUKAN' as 'PEMASUKAN' | 'PENGELUARAN',
  nominal: '',
  keterangan: ''
})

const verifying = ref(false)

const currentUser = ref<any>(null)

const isBendahara = computed(() => {
  return currentUser.value?.role === 'BENDAHARA' || currentUser.value?.role === 'KETUA' || currentUser.value?.role === 'PENGURUS'
  // Adjusted to allow admin to view/try. Strict enforcement is on backend.
})

async function fetchData() {
  try {
    loading.value = true
    const [histRes, monRes] = await Promise.all([
      kasApi.history(),
      kasApi.monitor()
    ])
    historyList.value = histRes.data.data || []
    // Monitor returns data directly, not wrapped in {data: ...}
    monitorData.value = monRes.data
  } catch (e: any) {
    error.value = 'Gagal mengambil data keuangan'
  } finally {
    loading.value = false
  }
}

async function handleCatat() {
  if (!form.value.nominal || !form.value.keterangan) return
  submitLoading.value = true
  try {
    await kasApi.input({
      jenis_kas: form.value.jenis_kas,
      nominal: parseInt(form.value.nominal.toString().replace(/\D/g, ''), 10),
      keterangan: form.value.keterangan
    })
    showModal.value = false
    form.value = { jenis_kas: 'PEMASUKAN', nominal: '', keterangan: '' }
    fetchData()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Gagal mencatat transaksi')
  } finally {
    submitLoading.value = false
  }
}

function formatRupiah(amount: number) {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount || 0)
}

async function handleVerifikasi() {
  verifying.value = true
  try {
    const res = await kasApi.monitor()
    monitorData.value = res.data
    if (res.data.status_integritas) {
      alert('Verifikasi Berhasil: Seluruh rantai blok kas berstatus VALID (Hash sesuai).')
    } else {
      alert('Peringatan: Integritas rantai blok INVALID! Ditemukan perubahan data yang tidak sah.')
    }
  } catch (e: any) {
    alert('Gagal melakukan verifikasi hashchain.')
  } finally {
    verifying.value = false
  }
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
  const userStr = localStorage.getItem('user')
  if (userStr) {
    currentUser.value = JSON.parse(userStr)
  }
  fetchData()
})
</script>

<template>
  <div class="keuangan-page">
    <div class="page-header">
      <div>
        <h2 class="page-title">Transparansi Keuangan & Kas</h2>
        <p class="page-subtitle">Sistem pencatatan mutasi kas RT berbasis Hashchain</p>
      </div>
      <div class="header-actions">
        <button class="btn btn-outline" @click="handleVerifikasi" :disabled="verifying" style="color: var(--accent-blue); border-color: var(--accent-blue-dim);">
          {{ verifying ? 'Memeriksa...' : 'Verifikasi Hashchain' }}
        </button>
        <button v-if="isBendahara" class="btn btn-primary" @click="showModal = true">
          + Catat Transaksi
        </button>
      </div>
    </div>

    <!-- Summary Cards -->
    <div v-if="monitorData" class="grid-4 mb-4">
      <div class="card stat-card">
        <div class="stat-icon bg-blue-dim text-blue">💰</div>
        <div class="stat-info">
          <div class="stat-label">Saldo Saat Ini</div>
          <div class="stat-value">{{ formatRupiah(monitorData.saldo) }}</div>
        </div>
      </div>
      <div class="card stat-card">
        <div class="stat-icon bg-green-dim text-green">📈</div>
        <div class="stat-info">
          <div class="stat-label">Total Pemasukan</div>
          <div class="stat-value text-green">{{ formatRupiah(monitorData.total_pemasukan) }}</div>
        </div>
      </div>
      <div class="card stat-card">
        <div class="stat-icon bg-red-dim text-red">📉</div>
        <div class="stat-info">
          <div class="stat-label">Total Pengeluaran</div>
          <div class="stat-value text-red">{{ formatRupiah(monitorData.total_pengeluaran) }}</div>
        </div>
      </div>
      <div class="card stat-card">
        <div class="stat-icon" :class="monitorData.status_integritas ? 'bg-green-dim text-green' : 'bg-red-dim text-red'">🔗</div>
        <div class="stat-info">
          <div class="stat-label">Integritas Hashchain</div>
          <div class="stat-value" :class="monitorData.status_integritas ? 'text-green' : 'text-red'">
            {{ monitorData.status_integritas ? 'VALID' : 'INVALID/TIDAK SAH' }}
          </div>
          <div class="text-xs text-muted mt-1" v-if="monitorData.last_block_hash">
            Hash: {{ monitorData.last_block_hash.substring(0, 10) }}...
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Catat Transaksi -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-content card">
        <h3>Catat Transaksi Kas Baru</h3>
        <p class="text-sm text-muted mb-4">Transaksi ini akan digenerate menjadi block hash baru dan tidak dapat diubah (Immutable).</p>
        
        <form @submit.prevent="handleCatat" class="form-grid">
          <div class="form-group" style="grid-column: 1 / -1;">
            <label>Jenis Kas</label>
            <select v-model="form.jenis_kas" class="input" required>
              <option value="PEMASUKAN">Pemasukan (Kredit)</option>
              <option value="PENGELUARAN">Pengeluaran (Debit)</option>
            </select>
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label>Nominal (Rp)</label>
            <input v-model="form.nominal" type="number" min="1" required class="input" placeholder="Contoh: 50000" />
          </div>

          <div class="form-group" style="grid-column: 1 / -1;">
            <label>Keterangan / Tujuan</label>
            <textarea v-model="form.keterangan" required class="input" rows="3" placeholder="Tulis rincian catatan transaksi..."></textarea>
          </div>

          <div class="modal-actions" style="grid-column: 1 / -1;">
            <button type="button" class="btn btn-outline" @click="showModal = false">Batal</button>
            <button type="submit" class="btn btn-primary" :disabled="submitLoading">
              {{ submitLoading ? 'Mencatat...' : 'Simpan Transaksi' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <div class="card table-card">
      <div class="card-header border-bottom p-4">
        <h3 class="card-title">Buku Kas Utama (Ledger)</h3>
      </div>
      <div v-if="loading" class="loading">Memuat riwayat transaksi...</div>
      <div v-else-if="error" class="error">{{ error }}</div>
      <div class="table-responsive" v-else>
        <table class="data-table">
          <thead>
            <tr>
              <th>WAKTU</th>
              <th>PENCATAT</th>
              <th>KETERANGAN</th>
              <th>JENIS</th>
              <th class="text-right">NOMINAL</th>
              <th>BLOCK HASH</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!historyList.length">
              <td colspan="6" class="empty-state">Belum ada riwayat transaksi kas.</td>
            </tr>
            <tr v-for="block in historyList" :key="block.id">
              <td class="text-sm">{{ formatTanggal(block.created_at) }}</td>
              <td class="text-sm font-medium">{{ block.bendahara?.nama || 'Bendahara' }}</td>
              <td class="text-sm text-secondary desc-col">{{ block.keterangan }}</td>
              <td>
                <span class="badge" :class="block.jenis_kas === 'PEMASUKAN' ? 'badge-green' : 'badge-red'">
                  {{ block.jenis_kas }}
                </span>
              </td>
              <td class="text-right font-medium" :class="block.jenis_kas === 'PEMASUKAN' ? 'text-green' : 'text-red'">
                {{ block.jenis_kas === 'PEMASUKAN' ? '+' : '-' }} {{ formatRupiah(block.nominal) }}
              </td>
              <td class="font-mono text-xs text-muted max-hash">
                <span :title="block.current_hash">{{ block.current_hash }}</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<style scoped>
.keuangan-page {
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
.header-actions {
  display: flex;
  gap: 12px;
}
.grid-4 {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
}
.stat-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px;
}
.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: var(--radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
}
.stat-info { flex: 1; }
.stat-label {
  font-size: 0.8rem;
  color: var(--text-muted);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
}
.stat-value {
  font-size: 1.4rem;
  font-weight: 700;
  color: var(--text-heading);
}
.bg-blue-dim { background: rgba(59, 130, 246, 0.1); }
.text-blue { color: #3b82f6; }
.bg-green-dim { background: rgba(34, 197, 94, 0.1); }
.text-green { color: #22c55e; }
.bg-red-dim { background: rgba(239, 68, 68, 0.1); }
.text-red { color: #ef4444; }

.badge-green { background: rgba(34, 197, 94, 0.15); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.3); }
.badge-red { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }

.table-card {
  padding: 0;
  overflow: hidden;
}
.table-responsive {
  overflow-x: auto;
}
.data-table {
  width: 100%;
  border-collapse: collapse;
}
.data-table th, .data-table td {
  padding: 14px 20px;
  text-align: left;
  border-bottom: 1px solid var(--border-color);
  vertical-align: middle;
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

.desc-col { max-width: 200px; white-space: normal; }
.max-hash {
  max-width: 150px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  display: inline-block;
  cursor: help;
}

.text-right { text-align: right !important; }
.font-medium { font-weight: 500; }
.text-sm { font-size: 0.85rem; }
.text-xs { font-size: 0.75rem; }
.text-muted { color: var(--text-muted); }
.mt-1 { margin-top: 4px; }
.mb-4 { margin-bottom: 20px; }
.font-mono { font-family: var(--font-mono); }
.border-bottom { border-bottom: 1px solid var(--border-color); }
.p-4 { padding: 20px; }

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
  font-family: inherit;
}
.input:focus {
  outline: none; border-color: var(--accent-blue);
}
textarea.input { resize: vertical; }
.modal-actions {
  display: flex; justify-content: flex-end; gap: 12px; margin-top: 12px;
}
</style>
