<template>
  <AppLayout title="Kategori">
    <div class="page-header">
      <div>
        <h2>Kategori Peralatan</h2>
        <p class="page-sub">Kelola kategori untuk pengelompokan peralatan</p>
      </div>
      <button id="btn-open-create-category" class="btn btn-primary" @click="openCreate">
        + Tambah Kategori
      </button>
    </div>

    <div class="categories-grid">
      <div v-if="!categories.length" class="card empty-card">
        <p>🗂️ Belum ada kategori. Tambahkan kategori pertama.</p>
      </div>
      <div v-for="cat in categories" :key="cat.id" class="category-card card">
        <div class="cat-icon">🗂️</div>
        <div class="cat-info">
          <div class="cat-name">{{ cat.name }}</div>
          <div class="cat-count">{{ cat.items_count ?? 0 }} peralatan</div>
        </div>
        <button
          :id="`btn-delete-category-${cat.id}`"
          class="btn btn-danger btn-sm cat-del"
          @click="confirmDelete(cat)"
        >🗑️</button>
      </div>
    </div>

    <!-- Create Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal glass">
        <div class="modal-header">
          <h3>Tambah Kategori</h3>
          <button class="modal-close" @click="closeModal">✕</button>
        </div>
        <form @submit.prevent="submitCreate" novalidate>
          <div class="form-group">
            <label for="cat-name" class="form-label">Nama Kategori</label>
            <input id="cat-name" v-model="form.name" type="text" class="form-control" :class="{ 'input-error': form.errors.name }" placeholder="Elektronik" required />
            <span v-if="form.errors.name" class="field-error">{{ form.errors.name }}</span>
          </div>
          <div class="form-group">
            <label for="cat-desc" class="form-label">Deskripsi (opsional)</label>
            <textarea id="cat-desc" v-model="form.description" class="form-control" rows="2" placeholder="Deskripsi kategori..."></textarea>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closeModal">Batal</button>
            <button id="btn-submit-category" type="submit" class="btn btn-primary" :disabled="form.processing">
              <span v-if="form.processing" class="spinner"></span>
              <span v-else>Simpan</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal glass confirm-modal">
        <div class="confirm-icon">🗑️</div>
        <h3>Hapus Kategori</h3>
        <p>Apakah Anda yakin ingin menghapus <strong>{{ selectedCat?.name }}</strong>?</p>
        <div class="modal-footer">
          <button class="btn btn-secondary" @click="showDeleteModal = false">Batal</button>
          <button id="btn-confirm-delete-category" class="btn btn-danger" @click="deleteCategory">
            <span v-if="deleting" class="spinner"></span>
            <span v-else>Hapus</span>
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
  categories: { type: Array, default: () => [] },
});

const showModal = ref(false);
const showDeleteModal = ref(false);
const selectedCat = ref(null);
const deleting = ref(false);

const form = useForm({ name: '', description: '' });

const openCreate = () => { form.reset(); showModal.value = true; };
const closeModal = () => { showModal.value = false; form.clearErrors(); };

const submitCreate = () => {
  form.post('/categories', { onSuccess: () => closeModal() });
};

const confirmDelete = (cat) => { selectedCat.value = cat; showDeleteModal.value = true; };

const deleteCategory = () => {
  deleting.value = true;
  router.delete(`/categories/${selectedCat.value.id}`, {
    onFinish: () => { deleting.value = false; showDeleteModal.value = false; selectedCat.value = null; },
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

.categories-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
  gap: 1rem;
}

.empty-card { text-align: center; padding: 2rem; color: var(--text-secondary); }

.category-card {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.25rem;
  transition: var(--transition);
}
.category-card:hover { transform: translateY(-2px); }

.cat-icon { font-size: 1.75rem; }
.cat-info { flex: 1; }
.cat-name { font-weight: 600; }
.cat-count { font-size: 0.8rem; color: var(--text-secondary); }
.cat-del { padding: 0.3rem 0.65rem; font-size: 0.9rem; }

.btn-sm { padding: 0.3rem 0.7rem; font-size: 0.82rem; }
.btn-secondary { background: var(--surface-border); color: var(--text-primary); }

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
  max-width: 440px;
  border-radius: var(--radius-lg);
  padding: 1.75rem;
  animation: slideUp 0.25s ease;
}
@keyframes slideUp {
  from { opacity: 0; transform: translateY(16px); }
  to { opacity: 1; transform: translateY(0); }
}
.modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
.modal-header h3 { margin: 0; }
.modal-close { background: none; border: none; color: var(--text-secondary); font-size: 1.1rem; cursor: pointer; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; }
.confirm-modal { text-align: center; max-width: 360px; }
.confirm-icon { font-size: 2.5rem; margin-bottom: 0.75rem; }
.confirm-modal p { color: var(--text-secondary); margin: 0.5rem 0 0; }
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
}
</style>
