<template>
  <AppLayout title="Peminjaman">
    <!-- Page Header -->
    <div class="page-hd">
      <div>
        <h2 class="page-hd-title">Manajemen Peminjaman</h2>
        <p class="page-hd-sub">Kelola transaksi check-in dan check-out peralatan</p>
      </div>
      <button id="btn-open-create-transaction" class="btn btn-primary" @click="openCreate">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Buat Transaksi
      </button>
    </div>

    <!-- Toolbar -->
    <div class="toolbar card" style="padding:1rem 1.25rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
      <div class="search-wrap" style="flex:1; min-width:180px; max-width:320px;">
        <svg class="search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input
          id="input-search-transaction"
          v-model="search"
          type="text"
          class="form-control"
          placeholder="Cari peminjam atau peralatan..."
        />
      </div>

      <select id="filter-status" v-model="statusFilter" class="form-control" style="max-width:170px;">
        <option value="">Semua Status</option>
        <option value="checkin">Dipinjam</option>
        <option value="checkout">Dikembalikan</option>
        <option value="pending">Pending</option>
      </select>

      <div class="toolbar-badges" style="margin-left:auto; display:flex; gap:0.5rem; flex-wrap:wrap;">
        <span class="badge badge-primary">{{ filteredTransactions.length }} transaksi</span>
      </div>
    </div>

    <!-- Table -->
    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th style="width:44px">#</th>
              <th>Peminjam</th>
              <th>Peralatan</th>
              <th>Tgl. Pinjam</th>
              <th>Tgl. Kembali</th>
              <th>Status</th>
              <th style="text-align:center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!filteredTransactions.length">
              <td colspan="7">
                <div class="empty-state">
                  <div class="empty-state-icon">🔄</div>
                  <div class="empty-state-text">Tidak ada data transaksi</div>
                </div>
              </td>
            </tr>
            <tr v-for="trx in filteredTransactions" :key="trx.id">
              <td><span class="badge badge-primary">#{{ trx.id }}</span></td>
              <td>
                <div style="font-weight:600; color:var(--text-1)">{{ trx.borrower_name }}</div>
                <div style="font-size:0.72rem; color:var(--text-3); margin-top:0.1rem">{{ trx.borrower_nim }}</div>
              </td>
              <td>
                <div style="font-weight:500; color:var(--text-1)">{{ trx.item_name ?? '-' }}</div>
                <div v-if="trx.item_code" style="font-size:0.72rem; color:var(--text-3); margin-top:0.1rem">
                  <span class="badge badge-secondary">{{ trx.item_code }}</span>
                </div>
              </td>
              <td style="font-size:0.82rem">{{ formatDate(trx.borrow_date) }}</td>
              <td style="font-size:0.82rem">
                <span :class="{ 'overdue': isOverdue(trx) }">
                  {{ formatDate(trx.return_date) }}
                </span>
                <span v-if="isOverdue(trx)" style="display:block; font-size:0.68rem; color:#f87171; margin-top:0.15rem">
                  ⚠️ Terlambat
                </span>
              </td>
              <td>
                <span class="badge" :class="statusBadgeClass(trx.status)">
                  {{ statusLabel(trx.status) }}
                </span>
              </td>
              <td style="text-align:center">
                <button
                  v-if="trx.status === 'checkin'"
                  :id="`btn-checkout-${trx.id}`"
                  class="btn btn-success btn-sm"
                  @click="openUpdate(trx)"
                >
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  Kembalikan
                </button>
                <button
                  v-if="trx.status === 'pending'"
                  :id="`btn-checkin-${trx.id}`"
                  class="btn btn-primary btn-sm"
                  @click="updateToCheckin(trx)"
                >
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                  </svg>
                  Check-in
                </button>
                <span v-if="trx.status === 'checkout'" class="badge badge-success" style="font-size:0.7rem">
                  ✓ Selesai
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Transaction Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal" style="max-width:560px;">
        <div class="modal-header">
          <h3 class="modal-title">Buat Transaksi Peminjaman</h3>
          <button class="modal-close" @click="closeModal" aria-label="Close">✕</button>
        </div>

        <form @submit.prevent="submitCreate" novalidate>
          <div class="form-grid">
            <div class="form-group">
              <label for="trx-borrower" class="form-label">Nama Peminjam</label>
              <input id="trx-borrower" v-model="form.borrower_name" type="text" class="form-control" :class="{ 'is-invalid': form.errors.borrower_name }" placeholder="Nama lengkap" required />
              <p v-if="form.errors.borrower_name" class="form-error">{{ form.errors.borrower_name }}</p>
            </div>
            <div class="form-group">
              <label for="trx-nim" class="form-label">NIM / No. Identitas</label>
              <input id="trx-nim" v-model="form.borrower_nim" type="text" class="form-control" :class="{ 'is-invalid': form.errors.borrower_nim }" placeholder="12345678" required />
              <p v-if="form.errors.borrower_nim" class="form-error">{{ form.errors.borrower_nim }}</p>
            </div>
          </div>

          <div class="form-group">
            <label for="trx-item" class="form-label">Peralatan</label>
            <select id="trx-item" v-model="form.item_id" class="form-control" :class="{ 'is-invalid': form.errors.item_id }" required>
              <option value="">-- Pilih Peralatan --</option>
              <option v-for="item in availableItems" :key="item.id" :value="item.id">
                {{ item.name }} — {{ item.code }} (Stok: {{ item.stock }})
              </option>
            </select>
            <p v-if="form.errors.item_id" class="form-error">{{ form.errors.item_id }}</p>
          </div>

          <div class="form-grid">
            <div class="form-group">
              <label for="trx-borrow-date" class="form-label">Tanggal Pinjam</label>
              <input id="trx-borrow-date" v-model="form.borrow_date" type="date" class="form-control" :class="{ 'is-invalid': form.errors.borrow_date }" required />
              <p v-if="form.errors.borrow_date" class="form-error">{{ form.errors.borrow_date }}</p>
            </div>
            <div class="form-group">
              <label for="trx-return-date" class="form-label">Tanggal Kembali</label>
              <input id="trx-return-date" v-model="form.return_date" type="date" class="form-control" :class="{ 'is-invalid': form.errors.return_date }" required />
              <p v-if="form.errors.return_date" class="form-error">{{ form.errors.return_date }}</p>
            </div>
          </div>

          <!-- Client-side validation alert -->
          <div v-if="validationError" class="alert alert-danger" style="margin-top:0.5rem">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:1px">
              <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            {{ validationError }}
          </div>

          <div class="form-group">
            <label for="trx-notes" class="form-label">Catatan (opsional)</label>
            <textarea id="trx-notes" v-model="form.notes" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
            <button id="btn-submit-transaction" type="submit" class="btn btn-primary" :disabled="form.processing">
              <span v-if="form.processing" class="spinner"></span>
              <span v-else>Simpan Transaksi</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Check-out Confirmation Modal -->
    <div v-if="showUpdateModal" class="modal-overlay" @click.self="closeUpdateModal">
      <div class="modal" style="max-width:460px;">
        <div class="modal-header">
          <h3 class="modal-title">Konfirmasi Pengembalian</h3>
          <button class="modal-close" @click="closeUpdateModal" aria-label="Close">✕</button>
        </div>

        <div style="background:var(--surface-2); border:1px solid var(--border); border-radius:var(--r-md); padding:1rem; margin-bottom:1.25rem;">
          <div style="font-size:0.78rem; color:var(--text-3); margin-bottom:0.5rem">Detail Transaksi</div>
          <div style="display:flex; justify-content:space-between; margin-bottom:0.3rem">
            <span style="color:var(--text-2); font-size:0.82rem">Peminjam</span>
            <span style="color:var(--text-1); font-weight:600; font-size:0.82rem">{{ selectedTrx?.borrower_name }}</span>
          </div>
          <div style="display:flex; justify-content:space-between">
            <span style="color:var(--text-2); font-size:0.82rem">Peralatan</span>
            <span style="color:var(--text-1); font-weight:600; font-size:0.82rem">{{ selectedTrx?.item_name ?? '-' }}</span>
          </div>
        </div>

        <div class="form-group">
          <label for="checkout-notes" class="form-label">Catatan Pengembalian (opsional)</label>
          <textarea id="checkout-notes" v-model="updateForm.checkout_notes" class="form-control" rows="2" placeholder="Kondisi peralatan saat dikembalikan..."></textarea>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" @click="closeUpdateModal">Batal</button>
          <button id="btn-confirm-checkout" class="btn btn-success" @click="submitCheckout" :disabled="updateForm.processing">
            <span v-if="updateForm.processing" class="spinner"></span>
            <span v-else>
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:middle; margin-right:0.3rem">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
              Konfirmasi Pengembalian
            </span>
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
  items:        { type: Array, default: () => [] },
});

const search          = ref('');
const statusFilter    = ref('');
const showModal       = ref(false);
const showUpdateModal = ref(false);
const selectedTrx     = ref(null);
const validationError = ref('');

const availableItems = computed(() => props.items.filter((i) => i.stock > 0));

const filteredTransactions = computed(() =>
  props.transactions.filter((t) => {
    const matchSearch =
      t.borrower_name?.toLowerCase().includes(search.value.toLowerCase()) ||
      (t.item_name ?? '').toLowerCase().includes(search.value.toLowerCase());
    const matchStatus = !statusFilter.value || t.status === statusFilter.value;
    return matchSearch && matchStatus;
  })
);

const today = new Date().toISOString().split('T')[0];

const form = useForm({
  borrower_name: '', borrower_nim: '', item_id: '',
  borrow_date: today, return_date: '', notes: '', status: 'checkin',
});

const updateForm = useForm({ status: 'checkout', checkout_notes: '' });

const openCreate  = () => { form.reset(); form.borrow_date = today; validationError.value = ''; showModal.value = true; };
const closeModal  = () => { showModal.value = false; form.clearErrors(); validationError.value = ''; };

const submitCreate = () => {
  if (form.borrow_date && form.return_date && form.return_date < form.borrow_date) {
    validationError.value = 'Tanggal kembali tidak boleh sebelum tanggal pinjam.';
    return;
  }
  validationError.value = '';
  const selectedItem = props.items.find((i) => i.id == form.item_id);
  if (selectedItem && selectedItem.stock <= 0) {
    validationError.value = 'Stok peralatan yang dipilih sudah habis.';
    return;
  }
  form.post('/transactions', { onSuccess: () => closeModal() });
};

const openUpdate      = (trx) => { selectedTrx.value = trx; updateForm.reset(); showUpdateModal.value = true; };
const closeUpdateModal = () => { showUpdateModal.value = false; updateForm.clearErrors(); };

const submitCheckout = () => {
  updateForm.put(`/transactions/${selectedTrx.value.id}`, { onSuccess: () => closeUpdateModal() });
};

const updateToCheckin = (trx) => {
  router.put(`/transactions/${trx.id}`, { status: 'checkin' });
};

const formatDate = (d) => d ? new Date(d).toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' }) : '-';

const isOverdue = (trx) => {
  if (trx.status === 'checkout') return false;
  return trx.return_date && new Date(trx.return_date) < new Date();
};

const statusLabel = (s) => ({ checkin: 'Dipinjam', checkout: 'Dikembalikan', pending: 'Pending' }[s] ?? s);

const statusBadgeClass = (s) => ({
  checkin:  'badge-danger',
  checkout: 'badge-success',
  pending:  'badge-warning',
}[s] ?? 'badge-secondary');
</script>

<style scoped>
.page-hd {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 1.5rem;
  gap: 1rem;
  flex-wrap: wrap;
}
.page-hd-title { font-size: 1.3rem; font-weight: 800; margin: 0; letter-spacing: -0.02em; }
.page-hd-sub   { color: var(--text-3); font-size: 0.82rem; margin-top: 0.2rem; }

.overdue { color: #f87171; font-weight: 600; }

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0 1rem;
}

@media (max-width: 600px) {
  .page-hd { flex-direction: column; }
  .page-hd .btn { width: 100%; }
  .form-grid { grid-template-columns: 1fr; }
  .toolbar { flex-direction: column; align-items: stretch !important; }
  .toolbar select { max-width: 100% !important; }
}
</style>
