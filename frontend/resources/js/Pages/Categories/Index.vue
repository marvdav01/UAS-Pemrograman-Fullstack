<template>
  <AppLayout title="Kategori">
    <!-- Page Header -->
    <div class="page-hd">
      <div>
        <h2 class="page-hd-title">Kategori Peralatan</h2>
        <p class="page-hd-sub">Kelola kategori untuk pengelompokan peralatan</p>
      </div>
      <button id="btn-open-create-category" class="btn btn-primary" @click="openCreate">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Tambah Kategori
      </button>
    </div>

    <!-- Stats Bar -->
    <div class="cat-stats card" style="padding:0.875rem 1.25rem; margin-bottom:1.25rem; display:flex; align-items:center; gap:1rem;">
      <span style="color:var(--text-3); font-size:0.82rem; font-weight:500">Total Kategori:</span>
      <span class="badge badge-primary">{{ categories.length }}</span>
      <span style="color:var(--text-3); font-size:0.82rem; font-weight:500; margin-left:0.5rem">Total Peralatan:</span>
      <span class="badge badge-success">{{ totalItems }}</span>
    </div>

    <!-- Categories Grid -->
    <div class="cat-grid" v-if="categories.length">
      <div v-for="cat in categories" :key="cat.id" class="cat-card card">
        <div class="cat-header">
          <div class="cat-icon">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2z"/>
            </svg>
          </div>
          <button
            :id="`btn-delete-category-${cat.id}`"
            class="btn btn-danger btn-sm"
            @click="confirmDelete(cat)"
            title="Hapus kategori"
          >
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
            </svg>
          </button>
        </div>
        <div class="cat-name">{{ cat.name }}</div>
        <div class="cat-desc" v-if="cat.description">{{ cat.description }}</div>
        <div class="cat-footer">
          <span class="badge badge-primary">{{ cat.items_count ?? 0 }} peralatan</span>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="card">
      <div class="empty-state">
        <div class="empty-state-icon">🗂️</div>
        <div class="empty-state-text">Belum ada kategori. Tambahkan kategori pertama!</div>
      </div>
    </div>

    <!-- Create Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h3 class="modal-title">Tambah Kategori Baru</h3>
          <button class="modal-close" @click="closeModal" aria-label="Close">✕</button>
        </div>
        <form @submit.prevent="submitCreate" novalidate>
          <div class="form-group">
            <label for="cat-name" class="form-label">Nama Kategori</label>
            <input id="cat-name" v-model="form.name" type="text" class="form-control" :class="{ 'is-invalid': form.errors.name }" placeholder="Elektronik" required />
            <p v-if="form.errors.name" class="form-error">{{ form.errors.name }}</p>
          </div>
          <div class="form-group">
            <label for="cat-desc" class="form-label">Deskripsi (opsional)</label>
            <textarea id="cat-desc" v-model="form.description" class="form-control" rows="3" placeholder="Deskripsi singkat kategori ini..."></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
            <button id="btn-submit-category" type="submit" class="btn btn-primary" :disabled="form.processing">
              <span v-if="form.processing" class="spinner"></span>
              <span v-else>Simpan Kategori</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Confirm Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal" style="max-width:400px; text-align:center;">
        <div style="width:56px;height:56px;background:rgba(239,68,68,0.12);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;font-size:1.5rem; border:1px solid rgba(239,68,68,0.2);">🗑️</div>
        <h3 class="modal-title" style="margin-bottom:0.5rem">Hapus Kategori?</h3>
        <p style="color:var(--text-2);font-size:0.875rem;margin-bottom:0.25rem">
          Anda akan menghapus <strong style="color:var(--text-1)">{{ selectedCat?.name }}</strong>.
        </p>
        <p style="color:var(--text-3);font-size:0.8rem">Semua peralatan dalam kategori ini akan ikut terhapus.</p>
        <div class="modal-footer" style="justify-content:center">
          <button class="btn btn-secondary" @click="showDeleteModal = false">Batal</button>
          <button id="btn-confirm-delete-category" class="btn btn-danger" @click="deleteCategory">
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
  categories: { type: Array, default: () => [] },
});

const totalItems = computed(() => props.categories.reduce((sum, c) => sum + (c.items_count ?? 0), 0));

const showModal       = ref(false);
const showDeleteModal = ref(false);
const selectedCat     = ref(null);
const deleting        = ref(false);

const form = useForm({ name: '', description: '' });

const openCreate  = () => { form.reset(); showModal.value = true; };
const closeModal  = () => { showModal.value = false; form.clearErrors(); };

const submitCreate = () => {
  form.post('/categories', { onSuccess: () => closeModal() });
};

const confirmDelete  = (cat) => { selectedCat.value = cat; showDeleteModal.value = true; };

const deleteCategory = () => {
  deleting.value = true;
  router.delete(`/categories/${selectedCat.value.id}`, {
    onFinish: () => { deleting.value = false; showDeleteModal.value = false; selectedCat.value = null; },
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

.cat-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1rem;
}

.cat-card {
  padding: 1.25rem;
  transition: var(--ease);
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.cat-card:hover {
  transform: translateY(-3px);
  border-color: var(--border-hover);
  box-shadow: var(--shadow-md);
}

.cat-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.cat-icon {
  width: 42px; height: 42px;
  background: rgba(99,102,241,0.12);
  border: 1px solid rgba(99,102,241,0.2);
  border-radius: var(--r-lg);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--primary-light);
}

.cat-name {
  font-size: 1rem;
  font-weight: 700;
  color: var(--text-1);
  line-height: 1.3;
}

.cat-desc {
  font-size: 0.78rem;
  color: var(--text-3);
  line-height: 1.5;
  flex: 1;
}

.cat-footer { margin-top: auto; }

@media (max-width: 600px) {
  .page-hd { flex-direction: column; }
  .page-hd .btn { width: 100%; }
  .cat-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 380px) {
  .cat-grid { grid-template-columns: 1fr; }
}
</style>
