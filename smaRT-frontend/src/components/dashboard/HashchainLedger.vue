<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ history: any[]; monitor: any }>()

const recentTx = computed(() => {
  const items = [...(props.history || [])].reverse().slice(0, 4)
  return items
})

function shortHash(h: string): string {
  if (!h) return '---'
  return h.slice(0, 4) + '...' + h.slice(-4)
}

function formatNominal(val: number, jenis: string): string {
  const sign = jenis === 'PEMASUKAN' ? '+' : '-'
  return `${sign}${val.toLocaleString('id-ID')}`
}

function timeAgo(dateStr: string): string {
  const now = new Date()
  const d = new Date(dateStr)
  const diff = Math.floor((now.getTime() - d.getTime()) / 60000)
  if (diff < 60) return `${diff} mnt lalu`
  if (diff < 1440) return `${Math.floor(diff / 60)} jam lalu`
  return `${Math.floor(diff / 1440)} hr lalu`
}
</script>

<template>
  <div class="card ledger-card">
    <div class="ledger-header">
      <h3 class="ledger-title">Hashchain Ledger</h3>
      <span class="badge badge-valid" style="font-size: 0.7rem;">SHA-256 ✓</span>
    </div>

    <div class="ledger-list">
      <div v-if="recentTx.length === 0" class="empty-state">Belum ada transaksi</div>
      <div v-for="tx in recentTx" :key="tx.id" class="ledger-item">
        <div class="ledger-icon">→</div>
        <div class="ledger-info">
          <span class="ledger-hash">{{ shortHash(tx.current_hash) }}</span>
          <span class="ledger-desc">
            {{ tx.jenis_kas === 'PEMASUKAN' ? 'Iuran' : 'Pengeluaran' }} ·
            {{ tx.bendahara?.nama || 'Bendahara' }} · {{ timeAgo(tx.created_at) }}
          </span>
        </div>
        <div class="ledger-amount" :class="tx.jenis_kas === 'PEMASUKAN' ? 'amount-plus' : 'amount-minus'">
          {{ formatNominal(tx.nominal, tx.jenis_kas) }}
          <span class="badge badge-valid" style="margin-top:2px">VALID</span>
        </div>
      </div>
    </div>

    <router-link to="/keuangan" class="ledger-link">
      Lihat semua transaksi ↗
    </router-link>
  </div>
</template>

<style scoped>
.ledger-card { padding: 20px; display: flex; flex-direction: column; gap: 14px; }
.ledger-header { display: flex; justify-content: space-between; align-items: center; }
.ledger-title { font-size: 0.95rem; font-weight: 600; color: var(--text-primary); }
.ledger-list { display: flex; flex-direction: column; gap: 2px; }
.ledger-item {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 10px; border-radius: var(--radius-md);
  transition: background var(--transition-fast);
}
.ledger-item:hover { background: var(--bg-card-hover); }
.ledger-icon {
  width: 32px; height: 32px; border-radius: 50%;
  background: var(--accent-blue-dim); color: var(--accent-blue);
  display: flex; align-items: center; justify-content: center;
  font-weight: 700; font-size: 0.85rem; flex-shrink: 0;
}
.ledger-info { flex: 1; display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.ledger-hash {
  font-family: var(--font-mono); font-size: 0.82rem;
  font-weight: 600; color: var(--text-primary);
}
.ledger-desc { font-size: 0.72rem; color: var(--text-muted); }
.ledger-amount {
  text-align: right; display: flex; flex-direction: column;
  align-items: flex-end; gap: 2px; font-weight: 700;
  font-size: 0.85rem; font-family: var(--font-mono); flex-shrink: 0;
}
.amount-plus { color: var(--accent-green); }
.amount-minus { color: var(--accent-red); }
.ledger-link {
  text-align: center; padding: 8px;
  border: 1px solid var(--border-color); border-radius: var(--radius-md);
  font-size: 0.8rem; color: var(--text-secondary);
  transition: all var(--transition-fast);
}
.ledger-link:hover { border-color: var(--accent-blue); color: var(--accent-blue); }
.empty-state { text-align: center; padding: 20px; color: var(--text-muted); font-size: 0.85rem; }
</style>
