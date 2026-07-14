<template>
  <AppLayout title="Dashboard">
    <!-- Stats Grid -->
    <div class="stats-grid">
      <div v-for="stat in statCards" :key="stat.label" class="stat-card">
        <div class="stat-icon-wrap" :style="{ background: stat.bg, boxShadow: `0 4px 16px ${stat.glow}` }">
          <span>{{ stat.icon }}</span>
        </div>
        <div class="stat-info">
          <div class="stat-num" :style="{ color: stat.color }">{{ stat.value }}</div>
          <div class="stat-desc">{{ stat.label }}</div>
        </div>
        <div class="stat-trend">
          <span class="badge" :class="stat.badgeClass">{{ stat.trend }}</span>
        </div>
      </div>
    </div>

    <!-- Content Grid -->
    <div class="dash-grid">
      <!-- Recent Transactions -->
      <div class="card col-span-2">
        <div class="card-header">
          <div>
            <h2 class="card-title">Transaksi Terbaru</h2>
            <p style="font-size:0.78rem; color:var(--text-3); margin-top:0.2rem;">5 transaksi terakhir yang masuk</p>
          </div>
          <Link href="/transactions" class="btn btn-primary btn-sm" id="btn-view-all-transactions">
            Lihat Semua →
          </Link>
        </div>

        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Peralatan</th>
                <th>Peminjam</th>
                <th>Tgl. Kembali</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="!recentTransactions.length">
                <td colspan="5">
                  <div class="empty-state">
                    <div class="empty-state-icon">📋</div>
                    <div class="empty-state-text">Belum ada transaksi</div>
                  </div>
                </td>
              </tr>
              <tr v-for="trx in recentTransactions" :key="trx.id">
                <td>
                  <span class="badge badge-primary">#{{ trx.id }}</span>
                </td>
                <td style="font-weight:600; color: var(--text-1)">{{ trx.item_name }}</td>
                <td>
                  <div>{{ trx.borrower_name }}</div>
                  <div style="font-size:0.72rem; color:var(--text-3)">{{ trx.borrower_nim }}</div>
                </td>
                <td style="font-size:0.82rem">{{ formatDate(trx.return_date) }}</td>
                <td>
                  <span class="badge" :class="statusClass(trx.status)">
                    {{ statusLabel(trx.status) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="card">
        <div class="card-header" style="margin-bottom:1rem">
          <h2 class="card-title">Aksi Cepat</h2>
        </div>
        <div class="quick-actions">
          <Link v-for="qa in quickActions" :key="qa.label" :href="qa.href" class="quick-action-item">
            <div class="qa-icon" :style="{ background: qa.bg }">{{ qa.icon }}</div>
            <div>
              <div class="qa-label">{{ qa.label }}</div>
              <div class="qa-desc">{{ qa.desc }}</div>
            </div>
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left:auto; color:var(--text-3); flex-shrink:0">
              <polyline points="9 18 15 12 9 6"/>
            </svg>
          </Link>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  stats: { type: Object, default: () => ({ items: 0, categories: 0, transactions: 0, checkin: 0 }) },
  recentTransactions: { type: Array, default: () => [] },
});

const statCards = [
  {
    label: 'Total Peralatan', value: props.stats.items ?? 0,
    icon: '📦', color: '#818cf8',
    bg: 'rgba(99,102,241,0.15)', glow: 'rgba(99,102,241,0.3)',
    trend: 'Inventaris', badgeClass: 'badge-primary',
  },
  {
    label: 'Kategori', value: props.stats.categories ?? 0,
    icon: '🗂️', color: '#34d399',
    bg: 'rgba(16,185,129,0.15)', glow: 'rgba(16,185,129,0.3)',
    trend: 'Master Data', badgeClass: 'badge-success',
  },
  {
    label: 'Total Transaksi', value: props.stats.transactions ?? 0,
    icon: '🔄', color: '#fbbf24',
    bg: 'rgba(245,158,11,0.15)', glow: 'rgba(245,158,11,0.3)',
    trend: 'Semua waktu', badgeClass: 'badge-warning',
  },
  {
    label: 'Check-in Aktif', value: props.stats.checkin ?? 0,
    icon: '📌', color: '#f87171',
    bg: 'rgba(239,68,68,0.15)', glow: 'rgba(239,68,68,0.3)',
    trend: 'Dipinjam', badgeClass: 'badge-danger',
  },
];

const quickActions = [
  { href: '/items',        icon: '📦', label: 'Tambah Peralatan', desc: 'Input data peralatan baru',      bg: 'rgba(99,102,241,0.15)' },
  { href: '/categories',  icon: '🗂️', label: 'Kelola Kategori',  desc: 'Atur kategori peralatan',        bg: 'rgba(16,185,129,0.15)' },
  { href: '/transactions', icon: '🔄', label: 'Catat Peminjaman', desc: 'Buat transaksi peminjaman baru', bg: 'rgba(245,158,11,0.15)' },
];

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' }) : '-';

const statusClass = (s) => ({
  checkin:  'badge-danger',
  checkout: 'badge-success',
  pending:  'badge-warning',
}[s] ?? 'badge-secondary');

const statusLabel = (s) => ({
  checkin:  'Dipinjam',
  checkout: 'Dikembalikan',
  pending:  'Pending',
}[s] ?? s);
</script>

<style scoped>
.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--r-xl);
  padding: 1.25rem 1.25rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: var(--ease);
  cursor: default;
}
.stat-card:hover {
  transform: translateY(-3px);
  border-color: var(--border-hover);
  box-shadow: var(--shadow-md);
}

.stat-icon-wrap {
  width: 52px; height: 52px;
  border-radius: var(--r-lg);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.4rem;
  flex-shrink: 0;
}

.stat-info { flex: 1; min-width: 0; }

.stat-num {
  font-size: 1.9rem;
  font-weight: 800;
  line-height: 1;
  letter-spacing: -0.04em;
  margin-bottom: 0.25rem;
}

.stat-desc {
  font-size: 0.77rem;
  color: var(--text-3);
  font-weight: 500;
}

.stat-trend { margin-left: auto; flex-shrink: 0; }

/* Dashboard grid layout */
.dash-grid {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 1.25rem;
}

.col-span-2 { grid-column: span 2; }

/* Quick Actions */
.quick-actions {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.quick-action-item {
  display: flex;
  align-items: center;
  gap: 0.875rem;
  padding: 0.875rem;
  border-radius: var(--r-md);
  border: 1px solid var(--border);
  background: var(--surface-2);
  transition: var(--ease);
  cursor: pointer;
  text-decoration: none;
  color: inherit;
}
.quick-action-item:hover {
  border-color: var(--border-hover);
  background: var(--surface-3);
  transform: translateX(3px);
}

.qa-icon {
  width: 38px; height: 38px;
  border-radius: var(--r-md);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  flex-shrink: 0;
}

.qa-label {
  font-size: 0.85rem;
  font-weight: 600;
  color: var(--text-1);
  margin-bottom: 0.1rem;
}

.qa-desc {
  font-size: 0.72rem;
  color: var(--text-3);
}

@media (max-width: 1100px) {
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
  .dash-grid  { grid-template-columns: 1fr; }
  .col-span-2 { grid-column: span 1; }
}

@media (max-width: 600px) {
  .stats-grid { grid-template-columns: 1fr 1fr; }
  .stat-trend { display: none; }
}

@media (max-width: 380px) {
  .stats-grid { grid-template-columns: 1fr; }
}
</style>
