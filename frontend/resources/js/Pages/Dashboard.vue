<template>
  <AppLayout title="Dashboard">
    <div class="dashboard-grid">
      <!-- Stats Cards -->
      <div class="stats-row">
        <div v-for="stat in stats" :key="stat.label" class="stat-card card">
          <div class="stat-icon" :style="{ background: stat.gradient }">{{ stat.icon }}</div>
          <div class="stat-info">
            <div class="stat-value">{{ stat.value }}</div>
            <div class="stat-label">{{ stat.label }}</div>
          </div>
        </div>
      </div>

      <!-- Recent Transactions -->
      <div class="card">
        <div class="card-header">
          <h2>Transaksi Terbaru</h2>
          <Link href="/transactions" class="btn btn-primary" id="btn-view-all-transactions">Lihat Semua</Link>
        </div>
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Peralatan</th>
                <th>Status</th>
                <th>Tanggal</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!recentTransactions.length">
                <td colspan="4" class="empty-state">Belum ada transaksi.</td>
              </tr>
              <tr v-for="trx in recentTransactions" :key="trx.id">
                <td><span class="badge">#{{ trx.id }}</span></td>
                <td>{{ trx.item_name }}</td>
                <td><span class="status-badge" :class="trx.status">{{ trx.status }}</span></td>
                <td>{{ formatDate(trx.created_at) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  stats: {
    type: Object,
    default: () => ({ items: 0, categories: 0, transactions: 0, checkin: 0 }),
  },
  recentTransactions: {
    type: Array,
    default: () => [],
  },
});

const stats = [
  { label: 'Total Peralatan', value: props.stats.items ?? 0, icon: '📦', gradient: 'linear-gradient(135deg, #3b82f6, #1d4ed8)' },
  { label: 'Kategori', value: props.stats.categories ?? 0, icon: '🗂️', gradient: 'linear-gradient(135deg, #8b5cf6, #6d28d9)' },
  { label: 'Transaksi', value: props.stats.transactions ?? 0, icon: '🔄', gradient: 'linear-gradient(135deg, #10b981, #059669)' },
  { label: 'Check-in Aktif', value: props.stats.checkin ?? 0, icon: '✅', gradient: 'linear-gradient(135deg, #f59e0b, #d97706)' },
];

const formatDate = (date) => new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
</script>

<style scoped>
.dashboard-grid {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.stats-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
}

.stat-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem;
  transition: var(--transition);
}

.stat-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
}

.stat-icon {
  width: 50px;
  height: 50px;
  border-radius: var(--radius-md);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.5rem;
  flex-shrink: 0;
}

.stat-value {
  font-size: 1.75rem;
  font-weight: 700;
}

.stat-label {
  font-size: 0.8rem;
  color: var(--text-secondary);
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.25rem;
}

.card-header h2 {
  margin: 0;
  font-size: 1.1rem;
}

.badge {
  background: rgba(59, 130, 246, 0.15);
  color: var(--primary-color);
  padding: 0.2rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.85rem;
  font-weight: 600;
}

.status-badge {
  padding: 0.2rem 0.65rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: capitalize;
}

.status-badge.checkin, .status-badge.active {
  background: rgba(16, 185, 129, 0.15);
  color: #34d399;
}

.status-badge.checkout, .status-badge.completed {
  background: rgba(59, 130, 246, 0.15);
  color: #60a5fa;
}

.status-badge.pending {
  background: rgba(245, 158, 11, 0.15);
  color: #fbbf24;
}

.empty-state {
  text-align: center;
  color: var(--text-secondary);
  padding: 2rem;
}

@media (max-width: 900px) {
  .stats-row {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .stats-row {
    grid-template-columns: 1fr;
  }
}
</style>
