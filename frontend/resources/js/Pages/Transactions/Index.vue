<template>
  <AppLayout title="Transaksi">
    <div class="page-header">
      <div>
        <h2>Manajemen Transaksi</h2>
        <p class="page-sub">Kelola transaksi check-in dan check-out peralatan</p>
      </div>
      <button id="btn-open-create-transaction" class="btn btn-primary" @click="openCreate">
        + Buat Transaksi
      </button>
    </div>

    <!-- Filters -->
    <div class="filters card" style="padding: 1rem; margin-bottom: 1.25rem; display: flex; gap: 1rem; flex-wrap: wrap;">
      <input
        id="input-search-transaction"
        v-model="search"
        type="text"
        class="form-control"
        placeholder="🔍 Cari transaksi..."
        style="max-width: 280px;"
      />
      <select id="filter-status" v-model="statusFilter" class="form-control" style="max-width: 200px;">
        <option value="">Semua Status</option>
        <option value="checkin">Check-in</option>
        <option value="checkout">Check-out</option>
        <option value="pending">Pending</option>
      </select>
    </div>

    <!-- Table -->
    <div class="card">
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Peminjam</th>
              <th>Peralatan</th>
              <th>Tanggal Pinjam</th>
              <th>Tgl Kembali</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!filteredTransactions.length">
              <td colspan="7" class="empty-state">Tidak ada data transaksi.</td>
            </tr>
            <tr v-for="trx in filteredTransactions" :key="trx.id" class="table-row">
              <td><span class="badge">#{{ trx.id }}</span></td>
              <td>
                <div class="trx-borrower">{{ trx.borrower_name }}</div>
                <div class="trx-sub">{{ trx.borrower_nim }}</div>
              </td>
              <td>{{ trx.item?.name ?? '-' }}</td>
              <td>{{ formatDate(trx.borrow_date) }}</td>
              <td>
                <span :class="{ 'overdue': isOverdue(trx) }">
                  {{ formatDate(trx.return_date) }}
                </span>
              </td>
              <td>
                <span class="status-badge" :class="trx.status">{{ statusLabel(trx.status) }}</span>
              </td>
              <td class="action-cell">
                <button
                  v-if="trx.status === 'checkin'"
                  :id="`btn-checkout-${trx.id}`"
                  class="btn btn-checkout btn-sm"
                  @click="openUpdate(trx)"
                >
                  ✅ Check-out
                </button>
                <button
                  v-if="trx.status === 'pending'"
                  :id="`btn-checkin-${trx.id}`"
                  class="btn btn-checkin btn-sm"
                  @click="updateToCheckin(trx)"
                >
                  📥 Check-in
                </button>
                <span v-if="trx.status === 'checkout'" class="done-badge">Selesai</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal glass">
        <div class="modal-header">
          <h3>Buat Transaksi Baru</h3>
          <button class="modal-close" @click="closeModal">✕</button>
        </div>

        <form @submit.prevent="submitCreate" novalidate>
          <div class="form-row">
            <div class="form-group">
              <label for="trx-borrower" class="form-label">Nama Peminjam</label>
              <input id="trx-borrower" v-model="form.borrower_name" type="text" class="form-control" :class="{ 'input-error': form.errors.borrower_name }" placeholder="Nama lengkap" required />
              <span v-if="form.errors.borrower_name" class="field-error">{{ form.errors.borrower_name }}</span>
            </div>
            <div class="form-group">
              <label for="trx-nim" class="form-label">NIM / No. Identitas</label>
              <input id="trx-nim" v-model="form.borrower_nim" type="text" class="form-control" :class="{ 'input-error': form.errors.borrower_nim }" placeholder="12345678" required />
              <span v-if="form.errors.borrower_nim" class="field-error">{{ form.errors.borrower_nim }}</span>
            </div>
          </div>

          <div class="form-group">
            <label for="trx-item" class="form-label">Peralatan</label>
            <select id="trx-item" v-model="form.item_id" class="form-control" :class="{ 'input-error': form.errors.item_id }" required>
              <option value="">-- Pilih Peralatan --</option>
              <option v-for="item in availableItems" :key="item.id" :value="item.id">
                {{ item.name }} (Stok: {{ item.stock }})
              </option>
            </select>
            <span v-if="form.errors.item_id" class="field-error">{{ form.errors.item_id }}</span>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="trx-borrow-date" class="form-label">Tanggal Pinjam</label>
              <input id="trx-borrow-date" v-model="form.borrow_date" type="date" class="form-control" :class="{ 'input-error': form.errors.borrow_date }" required />
              <span v-if="form.errors.borrow_date" class="field-error">{{ form.errors.borrow_date }}</span>
            </div>
            <div class="form-group">
              <label for="trx-return-date" class="form-label">Tanggal Kembali</label>
              <input id="trx-return-date" v-model="form.return_date" type="date" class="form-control" :class="{ 'input-error': form.errors.return_date }" required />
              <span v-if="form.errors.return_date" class="field-error">{{ form.errors.return_date }}</span>
            </div>
          </div>

          <!-- Validation Alert -->
          <div v-if="validationError" class="alert-error">⚠️ {{ validationError }}</div>

          <div class="form-group">
            <label for="trx-notes" class="form-label">Catatan (opsional)</label>
            <textarea id="trx-notes" v-model="form.notes" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
            <button id="btn-submit-transaction" type="submit" class="btn btn-primary" :disabled="form.processing">
              <span v-if="form.processing" class="spinner"></span>
              <span v-else>Simpan</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Update / Check-out Modal -->
    <div v-if="showUpdateModal" class="modal-overlay" @click.self="closeUpdateModal">
      <div class="modal glass">
        <div class="modal-header">
          <h3>Konfirmasi Check-out</h3>
          <button class="modal-close" @click="closeUpdateModal">✕</button>
        </div>
        <p style="color: var(--text-secondary); margin-bottom: 1.25rem;">
          Konfirmasi pengembalian peralatan <strong>{{ selectedTrx?.item?.name }}</strong> oleh <strong>{{ selectedTrx?.borrower_name }}</strong>.
        </p>
        <div class="form-group">
          <label for="checkout-notes" class="form-label">Catatan Pengembalian</label>
          <textarea id="checkout-notes" v-model="updateForm.checkout_notes" class="form-control" rows="2" placeholder="Kondisi peralatan saat dikembalikan..."></textarea>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeUpdateModal">Batal</button>
          <button id="btn-confirm-checkout" class="btn btn-primary" @click="submitCheckout" :disabled="updateForm.processing">
            <span v-if="updateForm.processing" class="spinner"></span>
            <span v-else>✅ Konfirmasi Check-out</span>
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  transactions: { type: Array, default: () => [] },
  items: { type: Array, default: () => [] },
});

const search = ref('');
const statusFilter = ref('');
const showModal = ref(false);
const showUpdateModal = ref(false);
const selectedTrx = ref(null);
const validationError = ref('');

const availableItems = computed(() => props.items.filter((i) => i.stock > 0));

const filteredTransactions = computed(() =>
  props.transactions.filter((t) => {
    const matchSearch =
      t.borrower_name?.toLowerCase().includes(search.value.toLowerCase()) ||
      t.item?.name?.toLowerCase().includes(search.value.toLowerCase());
    const matchStatus = !statusFilter.value || t.status === statusFilter.value;
    return matchSearch && matchStatus;
  })
);

const today = new Date().toISOString().split('T')[0];

const form = useForm({
  borrower_name: '',
  borrower_nim: '',
  item_id: '',
  borrow_date: today,
  return_date: '',
  notes: '',
  status: 'checkin',
});

const updateForm = useForm({
  status: 'checkout',
  checkout_notes: '',
});

const openCreate = () => {
  form.reset();
  validationError.value = '';
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  form.clearErrors();
  validationError.value = '';
};

const submitCreate = () => {
  // Client-side validation: return date must be after borrow date
  if (form.borrow_date && form.return_date && form.return_date < form.borrow_date) {
    validationError.value = 'Tanggal kembali tidak boleh sebelum tanggal pinjam.';
    return;
  }
  validationError.value = '';

  // Validate item stock
  const selectedItem = props.items.find((i) => i.id == form.item_id);
  if (selectedItem && selectedItem.stock <= 0) {
    validationError.value = 'Stok peralatan yang dipilih sudah habis.';
    return;
  }

  form.post('/transactions', { onSuccess: () => closeModal() });
};

const openUpdate = (trx) => { selectedTrx.value = trx; updateForm.reset(); showUpdateModal.value = true; };
const closeUpdateModal = () => { showUpdateModal.value = false; updateForm.clearErrors(); };

const submitCheckout = () => {
  updateForm.put(`/transactions/${selectedTrx.value.id}`, {
    onSuccess: () => closeUpdateModal(),
  });
};

const updateToCheckin = (trx) => {
  router.put(`/transactions/${trx.id}`, { status: 'checkin' });
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
};

const isOverdue = (trx) => {
  if (trx.status === 'checkout') return false;
  return trx.return_date && new Date(trx.return_date) < new Date();
};

const statusLabel = (status) => {
  const labels = { checkin: 'Check-in', checkout: 'Check-out', pending: 'Pending' };
  return labels[status] ?? status;
};
</script>

<style scoped>
.page-header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 1.5rem;
  gap: 1rem;
}
.page-header h2 { margin: 0; font-size: 1.25rem; }
.page-sub { color: var(--text-secondary); font-size: 0.85rem; margin-top: 0.25rem; }
.filters { display: flex; align-items: center; }

.trx-borrower { font-weight: 600; }
.trx-sub { font-size: 0.8rem; color: var(--text-secondary); }

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
}
.status-badge.checkin { background: rgba(16, 185, 129, 0.15); color: #34d399; }
.status-badge.checkout { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
.status-badge.pending { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }

.overdue { color: #fca5a5; font-weight: 600; }

.action-cell { display: flex; gap: 0.5rem; align-items: center; }
.btn-sm { padding: 0.3rem 0.7rem; font-size: 0.82rem; }
.btn-checkout { background: rgba(59, 130, 246, 0.15); color: var(--primary-color); border: 1px solid rgba(59,130,246,0.3); }
.btn-checkout:hover { background: rgba(59, 130, 246, 0.25); }
.btn-checkin { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.3); }
.btn-checkin:hover { background: rgba(16, 185, 129, 0.25); }
.btn-secondary { background: var(--surface-border); color: var(--text-primary); }
.done-badge { font-size: 0.8rem; color: var(--text-secondary); }

.empty-state { text-align: center; color: var(--text-secondary); padding: 2.5rem; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }

.alert-error {
  background: rgba(239, 68, 68, 0.1);
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #fca5a5;
  padding: 0.75rem 1rem;
  border-radius: var(--radius-md);
  margin-bottom: 1rem;
  font-size: 0.9rem;
}

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  z-index: 200;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}
.modal {
  width: 100%;
  max-width: 560px;
  border-radius: var(--radius-lg);
  padding: 1.75rem;
  animation: slideUp 0.25s ease;
  max-height: 90vh;
  overflow-y: auto;
}
@keyframes slideUp {
  from { opacity: 0; transform: translateY(16px); }
  to { opacity: 1; transform: translateY(0); }
}
.modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.modal-header h3 { margin: 0; }
.modal-close { background: none; border: none; color: var(--text-secondary); font-size: 1.1rem; cursor: pointer; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; }
.input-error { border-color: var(--danger-color) !important; }
.field-error { display: block; margin-top: 0.3rem; font-size: 0.8rem; color: #fca5a5; }

.spinner {
  width: 16px; height: 16px;
  border: 2px solid rgba(255,255,255,0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
  display: inline-block;
}
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 600px) {
  .page-header { flex-direction: column; }
  .page-header button { width: 100%; }
  .form-row { grid-template-columns: 1fr; }
  .filters { flex-direction: column; align-items: stretch; }
}
</style>
