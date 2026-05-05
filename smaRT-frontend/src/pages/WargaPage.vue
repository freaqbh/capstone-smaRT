<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { wargaApi, authApi } from '@/services/api'

const wargaList = ref<any[]>([])
const loading = ref(true)
const error = ref('')
const showModal = ref(false)

const form = ref({
  nama: '',
  NIK: '',
  role: 'WARGA',
  phone: '',
  password: ''
})

const submitLoading = ref(false)

async function fetchWarga() {
  try {
    loading.value = true
    const res = await wargaApi.list()
    wargaList.value = res.data.data || []
  } catch (e: any) {
    error.value = 'Gagal mengambil data warga'
  } finally {
    loading.value = false
  }
}

async function handleDelete(id: string) {
  if (!confirm('Apakah Anda yakin ingin menghapus warga ini?')) return
  try {
    await wargaApi.delete(id)
    fetchWarga()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Gagal menghapus warga')
  }
}

async function handleRegister() {
  submitLoading.value = true
  try {
    const user = JSON.parse(localStorage.getItem('user') || '{}')
    const id_rt = user.id_rt
    
    if (!id_rt) {
      alert('Gagal: id_rt pengurus tidak ditemukan.')
      return
    }

    await authApi.register({
      ...form.value,
      id_rt
    })
    
    showModal.value = false
    form.value = { nama: '', NIK: '', role: 'WARGA', phone: '', password: '' }
    fetchWarga()
  } catch (e: any) {
    alert(e.response?.data?.message || 'Gagal menambahkan warga')
  } finally {
    submitLoading.value = false
  }
}

onMounted(() => {
  fetchWarga()
})
</script>

<template>
  <div class="warga-page">
    <div class="page-header">
      <div>
        <h2 class="page-title">Manajemen Warga</h2>
        <p class="page-subtitle">Kelola data warga, pengurus, dan akses sistem</p>
      </div>
      <button class="btn btn-primary" @click="showModal = true">
        + Tambah Warga
      </button>
    </div>

    <!-- Modal Tambah Warga -->
    <div v-if="showModal" class="modal-overlay" @click.self="showModal = false">
      <div class="modal-content card">
        <h3>Tambah Warga Baru</h3>
        <form @submit.prevent="handleRegister" class="form-grid">
          <div class="form-group">
            <label>Nama Lengkap</label>
            <input v-model="form.nama" type="text" required class="input" />
          </div>
          <div class="form-group">
            <label>NIK</label>
            <input v-model="form.NIK" type="text" required class="input" />
          </div>
          <div class="form-group">
            <label>No. HP</label>
            <input v-model="form.phone" type="text" class="input" />
          </div>
          <div class="form-group">
            <label>Peran (Role)</label>
            <select v-model="form.role" class="input" required>
              <option value="WARGA">Warga</option>
              <option value="PENGURUS">Pengurus</option>
              <option value="BENDAHARA">Bendahara</option>
            </select>
          </div>
          <div class="form-group" style="grid-column: 1 / -1;">
            <label>Password Akun</label>
            <input v-model="form.password" type="password" required minlength="6" class="input" />
          </div>
          <div class="modal-actions" style="grid-column: 1 / -1;">
            <button type="button" class="btn btn-outline" @click="showModal = false">Batal</button>
            <button type="submit" class="btn btn-primary" :disabled="submitLoading">
              {{ submitLoading ? 'Menyimpan...' : 'Simpan' }}
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
            <th>NAMA LENGKAP</th>
            <th>NIK</th>
            <th>ROLE</th>
            <th>NO HP</th>
            <th class="text-right">AKSI</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!wargaList.length">
            <td colspan="5" class="empty-state">Belum ada data warga.</td>
          </tr>
          <tr v-for="w in wargaList" :key="w.id">
            <td class="font-medium text-primary">{{ w.nama }}</td>
            <td class="font-mono text-sm text-muted">{{ w.NIK }}</td>
            <td>
              <span class="badge" :class="w.role === 'WARGA' ? 'badge-pending' : 'badge-approved'">
                {{ w.role }}
              </span>
            </td>
            <td>{{ w.phone || '-' }}</td>
            <td class="text-right">
              <button class="btn btn-sm btn-outline text-red hover-bg-red" @click="handleDelete(w.id)">
                Hapus
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.warga-page {
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

.text-right { text-align: right !important; }
.font-medium { font-weight: 500; }
.text-primary { color: var(--text-primary); }
.font-mono { font-family: var(--font-mono); }
.text-sm { font-size: 0.8rem; }
.text-muted { color: var(--text-muted); }
.text-red { color: var(--accent-red); border-color: var(--accent-red-dim); }
.hover-bg-red:hover { background: var(--accent-red); color: white; border-color: var(--accent-red); }

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
  margin-bottom: 20px; color: var(--text-heading); font-size: 1.2rem;
}
.form-grid {
  display: grid; grid-template-columns: 1fr 1fr; gap: 16px;
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
  display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px;
}
</style>
