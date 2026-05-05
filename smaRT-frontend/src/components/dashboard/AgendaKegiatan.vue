<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ agenda: any[] }>()

const events = computed(() => {
  return (props.agenda || []).map(a => {
    const d = new Date(a.created_at)
    const months = ['JAN','FEB','MAR','APR','MEI','JUN','JUL','AGU','SEP','OKT','NOV','DES']
    const day = ('0' + d.getDate()).slice(-2)
    return {
      id: a.id,
      month: months[d.getMonth()],
      day: day,
      title: a.judul,
      time: d.getHours().toString().padStart(2, '0') + ':' + d.getMinutes().toString().padStart(2, '0') + ' · ' + (a.pengurus?.nama || 'Pengurus'),
      badge: 'KEGIATAN',
      badgeClass: 'badge-wajib'
    }
  })
})
</script>

<template>
  <div class="card">
    <div class="card-header">
      <h3 class="card-title">Agenda Kegiatan Mendatang</h3>
      <button class="btn btn-outline btn-sm">Tambah ↗</button>
    </div>
    <div class="events-list">
      <div v-if="!events?.length" class="empty-state">Belum ada agenda kegiatan</div>
      <div v-for="e in events" :key="e.id" class="event-item">
        <div class="event-date">
          <span class="event-month">{{ e.month }}</span>
          <span class="event-day">{{ e.day }}</span>
        </div>
        <div class="event-info">
          <span class="event-title">{{ e.title }}</span>
          <span class="event-time">{{ e.time }}</span>
        </div>
        <span class="badge" :class="e.badgeClass">{{ e.badge }}</span>
      </div>
    </div>
  </div>
</template>

<style scoped>
.card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; }
.card-title { font-size: 0.95rem; font-weight: 600; color: var(--text-primary); }
.events-list { display: flex; flex-direction: column; gap: 8px; }
.event-item {
  display: flex; align-items: center; gap: 14px;
  padding: 12px 10px; border-radius: var(--radius-md);
  transition: background var(--transition-fast);
}
.event-item:hover { background: var(--bg-card-hover); }
.event-date {
  width: 48px; height: 52px; border-radius: var(--radius-md);
  background: var(--accent-blue-dim); display: flex; flex-direction: column;
  align-items: center; justify-content: center; flex-shrink: 0;
}
.event-month { font-size: 0.6rem; font-weight: 700; color: var(--accent-blue-light); text-transform: uppercase; }
.event-day { font-size: 1.3rem; font-weight: 800; color: var(--text-heading); line-height: 1; }
.event-info { flex: 1; display: flex; flex-direction: column; gap: 2px; }
.event-title { font-size: 0.88rem; font-weight: 600; color: var(--text-primary); }
.event-time { font-size: 0.72rem; color: var(--text-muted); }
.empty-state { text-align: center; padding: 20px; color: var(--text-muted); font-size: 0.85rem; }
</style>
