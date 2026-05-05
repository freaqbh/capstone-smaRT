import axios from 'axios'

const api = axios.create({
  baseURL: 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

// Request interceptor — attach JWT token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Response interceptor — handle 401
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      // Try to refresh
      const token = localStorage.getItem('token')
      if (token && !error.config._retry) {
        error.config._retry = true
        try {
          const res = await api.post('/auth/refresh')
          const newToken = res.data.token
          localStorage.setItem('token', newToken)
          error.config.headers.Authorization = `Bearer ${newToken}`
          return api(error.config)
        } catch {
          localStorage.removeItem('token')
          localStorage.removeItem('user')
          window.location.href = '/login'
        }
      } else {
        localStorage.removeItem('token')
        localStorage.removeItem('user')
        window.location.href = '/login'
      }
    }
    return Promise.reject(error)
  },
)

export default api

// ─── Auth ────────────────────────────────────────────────
export const authApi = {
  login(NIK: string, password: string) {
    return api.post('/auth/login', { NIK, password })
  },
  register(data: {
    id_rt: string
    nama: string
    NIK: string
    role: string
    phone?: string
    password: string
  }) {
    return api.post('/auth/register', data)
  },
  logout() {
    return api.post('/auth/logout')
  },
  refresh() {
    return api.post('/auth/refresh')
  },
}

// ─── Surat ───────────────────────────────────────────────
export const suratApi = {
  list() {
    return api.get('/surat')
  },
  ajukan(data: FormData) {
    return api.post('/surat/ajukan', data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  },
  review(data: FormData) {
    data.append('_method', 'PATCH')
    return api.post('/surat/ajukan', data, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
  },
}

// ─── Panic ───────────────────────────────────────────────
export const panicApi = {
  list() {
    return api.get('/panic')
  },
  trigger(latitude: string, longitude: string) {
    return api.post('/panic/trigger', { latitude, longitude })
  },
}

// ─── Kas / Blockchain ────────────────────────────────────
export const kasApi = {
  input(data: { jenis_kas: 'PEMASUKAN' | 'PENGELUARAN'; nominal: number; keterangan: string }) {
    return api.post('/kas/input', data)
  },
  history() {
    return api.get('/kas/history')
  },
  monitor() {
    return api.get('/kas/monitor')
  },
}

// ─── Broadcast ───────────────────────────────────────────
export const broadcastApi = {
  list(limit = 10) {
    return api.get('/broadcast', { params: { limit } })
  },
  send(data: { judul: string; isi_pesan: string; kategori: 'INFORMASI' | 'DARURAT' | 'KEGIATAN' }) {
    return api.post('/broadcast', data)
  },
}

// ─── Dashboard ───────────────────────────────────────────
export const dashboardApi = {
  get() {
    return api.get('/dashboard')
  },
}

// ─── Warga ───────────────────────────────────────────────
export const wargaApi = {
  list() {
    return api.get('/warga')
  },
  delete(id: string) {
    return api.delete(`/warga/${id}`)
  },
}

// ─── Logs ────────────────────────────────────────────────
export const logApi = {
  list() {
    return api.get('/log')
  },
}
