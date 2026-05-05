<script setup lang="ts">
const props = defineProps<{ activities: any[] }>()

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
</script>

<template>
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Log Aktivitas Sistem</h3>
      <router-link to="/log" class="btn btn-outline btn-sm">Semua Log ↗</router-link>
    </div>
    <div class="log-list">
      <div v-if="!activities?.length" class="empty-state">Belum ada aktivitas</div>
      <div v-for="(item, i) in activities" :key="i" class="log-item">
        <div class="log-icon" :class="`log-icon-${item.color}`">{{ item.icon }}</div>
        <div class="log-content">
          <div class="log-title">
            {{ item.title }}
            <span class="log-tag" :class="`tag-${item.color}`">{{ item.tag }}</span>
          </div>
          <span class="log-desc">{{ item.desc }} · {{ timeAgo(item.created_at) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
.card-title { font-size: 0.95rem; font-weight: 600; color: var(--text-primary); }
.log-list { display: flex; flex-direction: column; gap: 4px; }
.log-item {
  display: flex; align-items: flex-start; gap: 12px;
  padding: 10px 8px; border-radius: var(--radius-md);
  transition: background var(--transition-fast);
}
.log-item:hover { background: var(--bg-card-hover); }
.log-icon {
  width: 34px; height: 34px; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.9rem; flex-shrink: 0;
}
.log-icon-red { background: var(--accent-red-dim); }
.log-icon-purple { background: var(--accent-purple-dim); }
.log-icon-green { background: var(--accent-green-dim); }
.log-icon-blue { background: var(--accent-blue-dim); }
.log-icon-orange { background: var(--accent-orange-dim); }
.log-content { flex: 1; display: flex; flex-direction: column; gap: 2px; }
.log-title { font-size: 0.82rem; color: var(--text-primary); }
.log-tag {
  padding: 1px 8px; border-radius: var(--radius-full);
  font-size: 0.72rem; font-weight: 600; margin-left: 4px;
}
.tag-red { background: var(--accent-red-dim); color: var(--accent-red); }
.tag-purple { background: var(--accent-purple-dim); color: var(--accent-purple); }
.tag-green { background: var(--accent-green-dim); color: var(--accent-green); }
.tag-blue { background: var(--accent-blue-dim); color: var(--accent-blue); }
.tag-orange { background: var(--accent-orange-dim); color: var(--accent-orange); }
.log-desc { font-size: 0.72rem; color: var(--text-muted); }
.empty-state { text-align: center; padding: 20px; color: var(--text-muted); font-size: 0.85rem; }
</style>
