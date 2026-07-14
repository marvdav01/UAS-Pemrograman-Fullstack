<template>
  <AppLayout title="Data Peralatan">
    <div class="page-header">
      <div>
        <h2>Data Peralatan</h2>
        <p class="page-sub">Kelola master data peralatan yang tersedia</p>
      </div>
      <button id="btn-open-create-item" class="btn btn-primary" @click="openCreate">
        + Tambah Peralatan
      </button>
    </div>

    <!-- Search -->
    <div class="search-bar card" style="padding: 1rem; margin-bottom: 1.25rem;">
      <input
        id="input-search-item"
        v-model="search"
        type="text"
        class="form-control"
        placeholder="🔍 Cari peralatan..."
        style="max-width: 350px;"
      />
    </div>

    <!-- Table -->
    <div class="card">
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Peralatan</th>
              <th>Kategori</th>
              <th>Stok</th>
              <th>Status</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!filteredItems.length">
              <td colspan="6" class="empty-state">Tidak ada data peralatan.</td>
            </tr>
            <tr v-for="(item, index) in filteredItems" :key="item.id" class="table-row">
              <td>{{ index + 1 }}</td>
              <td>
                <div class="item-name">{{ item.name }}</div>
                <div class="item-code">{{ item.code }}</div>
              </td>
              <td>{{ item.category?.name ?? '-' }}</td>
              <td>
                <span class="badge-count" :class="{ low: item.stock <= 2 }">{{ item.stock }}</span>
              </td>
              <td>
                <span class="status-badge" :class="item.status">{{ item.status }}</span>
              </td>
              <td>
                <button
                  :id="`btn-delete-item-${item.id}`"
                  class="btn btn-danger btn-sm"
                  @click="confirmDelete(item)"
                >
                  🗑️ Hapus
                </button>
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
          <h3>Tambah Peralatan Baru</h3>
          <button class="modal-close" @click="closeModal" aria-label="Close">✕</button>
        </div>

        <form @submit.prevent="submitCreate" novalidate>
          <div class="form-group">
            <label for="item-code" class="form-label">Kode Peralatan</label>
            <input id="item-code" v-model="form.code" type="text" class="form-control" :class="{ 'input-error': form.errors.code }" placeholder="EQ-001" required />
            <span v-if="form.errors.code" class="field-error">{{ form.errors.code }}</span>
          </div>

          <div class="form-group">
            <label for="item-name" class="form-label">Nama Peralatan</label>
            <input id="item-name" v-model="form.name" type="text" class="form-control" :class="{ 'input-error': form.errors.name }" placeholder="Laptop Dell XPS" required />
            <span v-if="form.errors.name" class="field-error">{{ form.errors.name }}</span>
          </div>

          <div class="form-group">
            <label for="item-category" class="form-label">Kategori</label>
            <select id="item-category" v-model="form.category_id" class="form-control" :class="{ 'input-error': form.errors.category_id }" required>
              <option value="">-- Pilih Kategori --</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
            <span v-if="form.errors.category_id" class="field-error">{{ form.errors.category_id }}</span>
          </div>

          <div class="form-group">
            <label for="item-stock" class="form-label">Stok</label>
            <input id="item-stock" v-model="form.stock" type="number" min="0" class="form-control" :class="{ 'input-error': form.errors.stock }" placeholder="1" required />
            <span v-if="form.errors.stock" class="field-error">{{ form.errors.stock }}</span>
          </div>

          <div class="form-group">
            <label for="item-description" class="form-label">Deskripsi (opsional)</label>
            <textarea id="item-description" v-model="form.description" class="form-control" rows="2" placeholder="Deskripsi peralatan..."></textarea>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
            <button id="btn-submit-item" type="submit" class="btn btn-primary" :disabled="form.processing">
              <span v-if="form.processing" class="spinner"></span>
              <span v-else>Simpan</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirm Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal glass confirm-modal">
        <div class="confirm-icon">🗑️</div>
        <h3>Hapus Peralatan</h3>
        <p>Apakah Anda yakin ingin menghapus <strong>{{ selectedItem?.name }}</strong>?</p>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showDeleteModal = false">Batal</button>
          <button id="btn-confirm-delete-item" class="btn btn-danger" @click="deleteItem">
            <span v-if="deleting" class="spinner"></span>
            <span v-else>Hapus</span>
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
  items: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
});

const search = ref('');
const showModal = ref(false);
const showDeleteModal = ref(false);
const selectedItem = ref(null);
const deleting = ref(false);

const filteredItems = computed(() =>
  props.items.filter(
    (i) =>
      i.name?.toLowerCase().includes(search.value.toLowerCase()) ||
      i.code?.toLowerCase().includes(search.value.toLowerCase())
  )
);

const form = useForm({
  code: '',
  name: '',
  category_id: '',
  stock: 1,
  description: '',
});

const openCreate = () => { form.reset(); showModal.value = true; };
const closeModal = () => { showModal.value = false; form.clearErrors(); };

const submitCreate = () => {
  form.post('/items', {
    onSuccess: () => { closeModal(); },
  });
};

const confirmDelete = (item) => { selectedItem.value = item; showDeleteModal.value = true; };

const deleteItem = () => {
  deleting.value = true;
  router.delete(`/items/${selectedItem.value.id}`, {
    onFinish: () => { deleting.value = false; showDeleteModal.value = false; selectedItem.value = null; },
  });
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

.item-name { font-weight: 600; }
.item-code { font-size: 0.8rem; color: var(--text-secondary); }

.badge-count {
  background: rgba(59, 130, 246, 0.15);
  color: var(--primary-color);
  padding: 0.15rem 0.6rem;
  border-radius: 999px;
  font-weight: 600;
  font-size: 0.85rem;
}

.badge-count.low {
  background: rgba(239, 68, 68, 0.15);
  color: #fca5a5;
}

.status-badge {
  padding: 0.2rem 0.65rem;
  border-radius: 999px;
  font-size: 0.8rem;
  font-weight: 600;
  text-transform: capitalize;
}
.status-badge.available { background: rgba(16, 185, 129, 0.15); color: #34d399; }
.status-badge.unavailable { background: rgba(239, 68, 68, 0.15); color: #fca5a5; }

.btn-sm { padding: 0.3rem 0.7rem; font-size: 0.82rem; }
.btn-secondary { background: var(--surface-border); color: var(--text-primary); }

.empty-state { text-align: center; color: var(--text-secondary); padding: 2.5rem; }

.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  z-index: 200;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  animation: fadeIn 0.2s ease;
}

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

.modal {
  width: 100%;
  max-width: 500px;
  border-radius: var(--radius-lg);
  padding: 1.75rem;
  animation: slideUp 0.25s ease;
}

@keyframes slideUp {
  from { opacity: 0; transform: translateY(16px); }
  to { opacity: 1; transform: translateY(0); }
}

.modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}

.modal-header h3 { margin: 0; }
.modal-close { background: none; border: none; color: var(--text-secondary); font-size: 1.1rem; cursor: pointer; transition: var(--transition); }
.modal-close:hover { color: var(--text-primary); }

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
  margin-top: 1.5rem;
}

.confirm-modal { text-align: center; max-width: 380px; }
.confirm-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
.confirm-modal p { color: var(--text-secondary); margin: 0.5rem 0 0; }

.input-error { border-color: var(--danger-color) !important; }
.field-error { display: block; margin-top: 0.3rem; font-size: 0.8rem; color: #fca5a5; }

.spinner {
  width: 16px;
  height: 16px;
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
}
</style>
