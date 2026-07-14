<template>
  <AppLayout title="Data Peralatan">
    <!-- Page Header -->
    <div class="page-hd">
      <div>
        <h2 class="page-hd-title">Data Peralatan</h2>
        <p class="page-hd-sub">Kelola master data inventaris peralatan yang tersedia</p>
      </div>
      <button id="btn-open-create-item" class="btn btn-primary" @click="openCreate">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Tambah Peralatan
      </button>
    </div>

    <!-- Toolbar -->
    <div class="toolbar card" style="padding:1rem 1.25rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:1rem; flex-wrap:wrap;">
      <div class="search-wrap" style="flex:1; min-width:200px; max-width:380px;">
        <svg class="search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input
          id="input-search-item"
          v-model="search"
          type="text"
          class="form-control"
          placeholder="Cari nama atau kode peralatan..."
        />
      </div>
      <div class="toolbar-info">
        <span class="badge badge-primary">{{ filteredItems.length }} item</span>
      </div>
    </div>

    <!-- Table Card -->
    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th style="width:44px">#</th>
              <th>Peralatan</th>
              <th>Kategori</th>
              <th>Stok</th>
              <th>Status</th>
              <th style="width:90px; text-align:center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="!filteredItems.length">
              <td colspan="6">
                <div class="empty-state">
                  <div class="empty-state-icon">📦</div>
                  <div class="empty-state-text">Tidak ada data peralatan</div>
                </div>
              </td>
            </tr>
            <tr v-for="(item, idx) in filteredItems" :key="item.id">
              <td style="color:var(--text-3); font-size:0.8rem">{{ idx + 1 }}</td>
              <td>
                <div style="font-weight:600; color:var(--text-1)">{{ item.name }}</div>
                <div style="font-size:0.72rem; color:var(--text-3); margin-top:0.1rem">
                  <span class="badge badge-secondary">{{ item.code }}</span>
                </div>
              </td>
              <td>
                <span class="badge badge-primary">{{ item.category?.name ?? '-' }}</span>
              </td>
              <td>
                <span class="badge" :class="item.stock <= 2 ? 'badge-danger' : 'badge-success'">
                  {{ item.stock }} unit
                </span>
              </td>
              <td>
                <span class="badge" :class="item.status === 'available' ? 'badge-success' : 'badge-secondary'">
                  {{ item.status === 'available' ? 'Tersedia' : item.status }}
                </span>
              </td>
              <td style="text-align:center">
                <button
                  :id="`btn-delete-item-${item.id}`"
                  class="btn btn-danger btn-sm"
                  @click="confirmDelete(item)"
                >
                  <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                    <path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
                  </svg>
                  Hapus
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h3 class="modal-title">Tambah Peralatan Baru</h3>
          <button class="modal-close" @click="closeModal" aria-label="Close">✕</button>
        </div>

        <form @submit.prevent="submitCreate" novalidate>
          <div class="form-grid">
            <div class="form-group">
              <label for="item-code" class="form-label">Kode Peralatan</label>
              <input id="item-code" v-model="form.code" type="text" class="form-control" :class="{ 'is-invalid': form.errors.code }" placeholder="EQ-001" required />
              <p v-if="form.errors.code" class="form-error">{{ form.errors.code }}</p>
            </div>
            <div class="form-group">
              <label for="item-stock" class="form-label">Stok</label>
              <input id="item-stock" v-model="form.stock" type="number" min="0" class="form-control" :class="{ 'is-invalid': form.errors.stock }" placeholder="1" required />
              <p v-if="form.errors.stock" class="form-error">{{ form.errors.stock }}</p>
            </div>
          </div>
          <div class="form-group">
            <label for="item-name" class="form-label">Nama Peralatan</label>
            <input id="item-name" v-model="form.name" type="text" class="form-control" :class="{ 'is-invalid': form.errors.name }" placeholder="Laptop Dell XPS 15" required />
            <p v-if="form.errors.name" class="form-error">{{ form.errors.name }}</p>
          </div>
          <div class="form-group">
            <label for="item-category" class="form-label">Kategori</label>
            <select id="item-category" v-model="form.category_id" class="form-control" :class="{ 'is-invalid': form.errors.category_id }" required>
              <option value="">-- Pilih Kategori --</option>
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
            <p v-if="form.errors.category_id" class="form-error">{{ form.errors.category_id }}</p>
          </div>
          <div class="form-group">
            <label for="item-description" class="form-label">Deskripsi (opsional)</label>
            <textarea id="item-description" v-model="form.description" class="form-control" rows="2" placeholder="Deskripsi singkat peralatan..."></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
            <button id="btn-submit-item" type="submit" class="btn btn-primary" :disabled="form.processing">
              <span v-if="form.processing" class="spinner"></span>
              <span v-else>Simpan Peralatan</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirm Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal" style="max-width:400px; text-align:center;">
        <div style="width:56px;height:56px;background:rgba(239,68,68,0.12);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;font-size:1.5rem; border:1px solid rgba(239,68,68,0.2);">🗑️</div>
        <h3 class="modal-title" style="text-align:center;margin-bottom:0.5rem">Hapus Peralatan?</h3>
        <p style="color:var(--text-2);font-size:0.875rem;margin-bottom:0.25rem">
          Anda akan menghapus <strong style="color:var(--text-1)">{{ selectedItem?.name }}</strong>.
        </p>
        <p style="color:var(--text-3);font-size:0.8rem">Tindakan ini tidak dapat dibatalkan.</p>
        <div class="modal-footer" style="justify-content:center">
          <button class="btn btn-secondary" @click="showDeleteModal = false">Batal</button>
          <button id="btn-confirm-delete-item" class="btn btn-danger" @click="deleteItem">
            <span v-if="deleting" class="spinner"></span>
            <span v-else>Ya, Hapus</span>
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
  items:      { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
});

const search          = ref('');
const showModal       = ref(false);
const showDeleteModal = ref(false);
const selectedItem    = ref(null);
const deleting        = ref(false);

const filteredItems = computed(() =>
  props.items.filter((i) =>
    i.name?.toLowerCase().includes(search.value.toLowerCase()) ||
    i.code?.toLowerCase().includes(search.value.toLowerCase())
  )
);

const form = useForm({ code: '', name: '', category_id: '', stock: 1, description: '' });

const openCreate  = () => { form.reset(); showModal.value = true; };
const closeModal  = () => { showModal.value = false; form.clearErrors(); };

const submitCreate = () => {
  form.post('/items', { onSuccess: () => closeModal() });
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

.toolbar-info { margin-left: auto; }

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0 1rem;
}

@media (max-width: 600px) {
  .page-hd { flex-direction: column; }
  .page-hd .btn { width: 100%; }
  .form-grid { grid-template-columns: 1fr; }
}
</style>
