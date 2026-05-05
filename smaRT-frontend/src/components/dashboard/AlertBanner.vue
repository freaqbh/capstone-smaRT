<script setup lang="ts">
</script>

<template>
  <div class="alert-banner">
    <span class="alert-dot"></span>
    <div class="alert-content">
      <strong>Darurat Aktif — Budi Hartono, Blok A-12</strong>
      <span class="alert-detail">Panik ditekan 4 menit lalu · GPS: -7.2845, 112.7431</span>
    </div>
    <span class="badge badge-darurat" style="font-size: 0.75rem; padding: 4px 14px;">Tangani</span>
  </div>
</template>

<style scoped>
.alert-banner {
  display: flex;
  align-items: center;
  gap: 14px;
  padding: 14px 20px;
  background: linear-gradient(90deg, rgba(239,68,68,0.08), rgba(239,68,68,0.03));
  border: 1px solid rgba(239,68,68,0.2);
  border-radius: var(--radius-lg);
}
.alert-dot {
  width: 10px; height: 10px;
  border-radius: 50%;
  background: var(--accent-orange);
  box-shadow: 0 0 10px var(--accent-orange);
  animation: pulse-glow 2s ease-in-out infinite;
  flex-shrink: 0;
}
.alert-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 2px;
}
.alert-content strong {
  font-size: 0.88rem;
  color: var(--text-primary);
}
.alert-detail {
  font-size: 0.75rem;
  color: var(--text-muted);
  font-family: var(--font-mono);
}
</style>
