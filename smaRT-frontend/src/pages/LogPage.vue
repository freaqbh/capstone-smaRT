<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { logApi } from '@/services/api'

const logs = ref<any[]>([])
const loading = ref(true)
const error = ref('')

async function fetchLogs() {
  try {
    loading.value = true
    const res = await logApi.list()
    logs.value = res.data.data || []
  } catch (e: any) {
    error.value = 'Gagal mengambil log aktivitas sistem.'
  } finally {
    loading.value = false
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

onMounted(() => {
  fetchLogs()
})
</script>

<template>
  <div class="log-page">
    <div class="page-header">
      <div>
        <h2 class="page-title">Log Aktivitas Sistem</h2>
        <p class="page-subtitle">Pantau seluruh pergerakan dan aktivitas user dalam aplikasi</p>
      </div>
      <button class="btn btn-outline" @click="fetchLogs" :disabled="loading">
        {{ loading ? 'Memuat...' : 'Muat Ulang' }}
      </button>
    </div>

    <div class="card p-0">
      <div v-if="loading" class="loading">Memuat log aktivitas...</div>
      <div v-else-if="error" class="error">{{ error }}</div>
      
      <div class="logs-container" v-else>
        <div v-if="!logs.length" class="empty-state">Belum ada aktivitas.</div>
        <div v-for="log in logs" :key="log.id" class="log-item">
          <div class="log-icon" :class="`icon-${log.color}`">{{ log.icon }}</div>
          <div class="log-content">
            <div class="log-header">
              <span class="log-title">{{ log.title }}</span>
              <span class="log-tag">{{ log.tag }}</span>
            </div>
            <div class="log-desc">{{ log.desc }}</div>
          </div>
          <div class="log-time">
            <div class="time-ago">{{ timeAgo(log.created_at) }}</div>
            <div class="time-exact">{{ formatTanggal(log.created_at) }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.log-page {
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
.card.p-0 { padding: 0; overflow: hidden; }

.logs-container {
  display: flex;
  flex-direction: column;
}
.log-item {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px 20px;
  border-bottom: 1px solid var(--border-color);
  transition: background var(--transition-fast);
}
.log-item:last-child {
  border-bottom: none;
}
.log-item:hover {
  background: var(--bg-card-hover);
}

.log-icon {
  width: 44px;
  height: 44px;
  border-radius: var(--radius-full);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.3rem;
  flex-shrink: 0;
}
.icon-red { background: var(--accent-red-dim); color: var(--accent-red); }
.icon-purple { background: rgba(168, 85, 247, 0.15); color: #a855f7; }
.icon-blue { background: var(--accent-blue-dim); color: var(--accent-blue); }
.icon-green { background: var(--accent-green-dim); color: var(--accent-green); }
.icon-orange { background: var(--accent-orange-dim); color: var(--accent-orange); }

.log-content {
  flex: 1;
}
.log-header {
  font-size: 0.95rem;
  margin-bottom: 4px;
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.log-title {
  color: var(--text-primary);
  font-weight: 500;
}
.log-tag {
  color: var(--text-heading);
  font-weight: 600;
}
.log-desc {
  font-size: 0.85rem;
  color: var(--text-secondary);
}

.log-time {
  text-align: right;
  flex-shrink: 0;
}
.time-ago {
  font-size: 0.85rem;
  font-weight: 500;
  color: var(--text-primary);
  margin-bottom: 2px;
}
.time-exact {
  font-size: 0.75rem;
  color: var(--text-muted);
}

.loading, .error, .empty-state {
  text-align: center;
  padding: 40px;
  color: var(--text-muted);
}
.error { color: var(--accent-red); }
</style>
