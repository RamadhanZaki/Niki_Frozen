<template>
  <div class="branches-container">
    <SidebarOwner />

    <main class="main-content">
      <div class="top-bar">
        <div class="page-title">
          <h1>Manajemen Cabang</h1>
          <p>Kelola semua cabang Niki Frozen</p>
        </div>
        <div class="top-bar-right">
          <div class="connection-status" :class="{ online: isOnline, offline: !isOnline }">
            <span class="status-dot"></span>
            <span>{{ isOnline ? "Online" : "Offline" }}</span>
          </div>
          <div class="user-avatar"><span>{{ userInitial }}</span></div>
        </div>
      </div>

      <!-- Action bar -->
      <div class="action-bar">
        <button class="btn-add" @click="openAddModal">+ Tambah Cabang</button>
      </div>

      <!-- Loading -->
      <div v-if="isLoading" class="loading-state">⏳ Memuat data cabang...</div>

      <!-- Error -->
      <div v-if="errorMsg" class="error-banner">{{ errorMsg }}</div>

      <!-- Table -->
      <div class="card" v-if="!isLoading">
        <div class="card-header">
          <h3>Daftar Cabang ({{ branches.length }})</h3>
          <button class="btn-refresh" @click="fetchBranches">🔄 Refresh</button>
        </div>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>#</th>
                <th>Nama Cabang</th>
                <th>Alamat</th>
                <th>No. Telpon</th>
                <th>Pengguna</th>
                <th>Produk</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(branch, i) in branches" :key="branch.id">
                <td>{{ i + 1 }}</td>
                <td class="branch-name">{{ branch.name }}</td>
                <td>{{ branch.address || '-' }}</td>
                <td>{{ branch.phone || '-' }}</td>
                <td><span class="badge">{{ branch.users_count }}</span></td>
                <td><span class="badge">{{ branch.products_count }}</span></td>
                <td>
                  <div class="action-btns">
                    <button class="btn-edit" @click="openEditModal(branch)">✏️ Edit</button>
                    <button class="btn-delete" @click="confirmDelete(branch)">🗑️ Hapus</button>
                  </div>
                </td>
              </tr>
              <tr v-if="branches.length === 0">
                <td colspan="7" class="empty-row">Belum ada cabang. Tambahkan cabang pertama Anda.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- Modal Add/Edit -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h3>{{ editMode ? 'Edit Cabang' : 'Tambah Cabang Baru' }}</h3>
          <button class="modal-close" @click="closeModal">✕</button>
        </div>
        <div class="modal-body">
          <div v-if="formError" class="form-error">{{ formError }}</div>
          <div class="form-group">
            <label>Nama Cabang <span class="required">*</span></label>
            <input v-model="form.name" type="text" class="form-input" placeholder="cth. Cabang Malioboro" />
          </div>
          <div class="form-group">
            <label>Alamat</label>
            <textarea v-model="form.address" class="form-input" rows="2" placeholder="Alamat lengkap cabang"></textarea>
          </div>
          <div class="form-group">
            <label>No. Telepon</label>
            <input v-model="form.phone" type="text" class="form-input" placeholder="cth. 0274-123456" />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-cancel" @click="closeModal">Batal</button>
          <button class="btn-submit" @click="submitForm" :disabled="isSaving">
            {{ isSaving ? 'Menyimpan...' : (editMode ? 'Simpan Perubahan' : 'Tambah Cabang') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Delete Confirm -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal modal-sm">
        <div class="modal-header">
          <h3>Hapus Cabang</h3>
          <button class="modal-close" @click="showDeleteModal = false">✕</button>
        </div>
        <div class="modal-body">
          <p>Apakah Anda yakin ingin menghapus cabang <strong>{{ selectedBranch?.name }}</strong>?</p>
          <p class="warning-text">⚠️ Cabang yang memiliki pengguna aktif tidak dapat dihapus.</p>
        </div>
        <div class="modal-footer">
          <button class="btn-cancel" @click="showDeleteModal = false">Batal</button>
          <button class="btn-delete-confirm" @click="deleteBranch" :disabled="isSaving">
            {{ isSaving ? 'Menghapus...' : 'Ya, Hapus' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import SidebarOwner from "../../components/SidebarOwner.vue";
import api from "../../services/axios.js";

const isOnline    = ref(navigator.onLine);
const isLoading   = ref(false);
const isSaving    = ref(false);
const errorMsg    = ref("");
const formError   = ref("");
const showModal        = ref(false);
const showDeleteModal  = ref(false);
const editMode         = ref(false);

const branches       = ref([]);
const selectedBranch = ref(null);

const userName    = ref("Owner");
const userInitial = computed(() => userName.value.charAt(0));

const form = ref({ name: "", address: "", phone: "" });

const fetchBranches = async () => {
  isLoading.value = true;
  errorMsg.value  = "";
  try {
    const res   = await api.get("/branches");
    branches.value = res.data;
  } catch (err) {
    errorMsg.value = "Gagal memuat data cabang. Silakan refresh.";
    console.error(err);
  } finally {
    isLoading.value = false;
  }
};

const openAddModal = () => {
  editMode.value = false;
  form.value     = { name: "", address: "", phone: "" };
  formError.value = "";
  showModal.value = true;
};

const openEditModal = (branch) => {
  editMode.value       = true;
  selectedBranch.value = branch;
  form.value           = { name: branch.name, address: branch.address || "", phone: branch.phone || "" };
  formError.value      = "";
  showModal.value      = true;
};

const closeModal = () => { showModal.value = false; };

const submitForm = async () => {
  if (!form.value.name.trim()) {
    formError.value = "Nama cabang wajib diisi.";
    return;
  }
  isSaving.value  = true;
  formError.value = "";
  try {
    if (editMode.value) {
      await api.put(`/branches/${selectedBranch.value.id}`, form.value);
    } else {
      await api.post("/branches", form.value);
    }
    closeModal();
    await fetchBranches();
  } catch (err) {
    const msg = err.response?.data?.errors
      ? Object.values(err.response.data.errors).flat().join(", ")
      : err.response?.data?.message ?? "Terjadi kesalahan.";
    formError.value = msg;
  } finally {
    isSaving.value = false;
  }
};

const confirmDelete = (branch) => {
  selectedBranch.value  = branch;
  showDeleteModal.value = true;
};

const deleteBranch = async () => {
  isSaving.value = true;
  try {
    await api.delete(`/branches/${selectedBranch.value.id}`);
    showDeleteModal.value = false;
    await fetchBranches();
  } catch (err) {
    const msg = err.response?.data?.message ?? "Gagal menghapus cabang.";
    alert(msg);
  } finally {
    isSaving.value = false;
  }
};

const updateOnlineStatus = () => { isOnline.value = navigator.onLine; };

onMounted(() => {
  window.addEventListener("online",  updateOnlineStatus);
  window.addEventListener("offline", updateOnlineStatus);
  const user = JSON.parse(localStorage.getItem("user") || "{}");
  if (user.name) userName.value = user.name;
  fetchBranches();
});

onUnmounted(() => {
  window.removeEventListener("online",  updateOnlineStatus);
  window.removeEventListener("offline", updateOnlineStatus);
});
</script>

<style scoped>
.branches-container { display: flex; min-height: 100vh; background: #f3f4f6; }
.main-content { flex: 1; margin-left: 260px; padding: 1.5rem; }
.top-bar {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 1.5rem; background: white; padding: 1rem 1.5rem;
  border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.page-title h1 { font-size: 1.25rem; color: #1f3864; margin-bottom: 0.25rem; }
.page-title p  { font-size: 0.8rem; color: #6b7280; }
.top-bar-right { display: flex; align-items: center; gap: 1rem; }
.connection-status {
  display: flex; align-items: center; gap: 0.5rem;
  padding: 0.375rem 0.75rem; border-radius: 20px; font-size: 0.75rem;
}
.connection-status.online  { background: #d1fae5; color: #065f46; }
.connection-status.offline { background: #fee2e2; color: #991b1b; }
.status-dot { width: 8px; height: 8px; border-radius: 50%; background: currentColor; }
.user-avatar {
  width: 40px; height: 40px; background: #2e75b6; border-radius: 50%;
  display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;
}
.action-bar { margin-bottom: 1.25rem; }
.btn-add {
  background: #1f3864; color: white; border: none; padding: 0.6rem 1.2rem;
  border-radius: 8px; cursor: pointer; font-size: 0.875rem; font-weight: 500;
}
.btn-add:hover { background: #2e75b6; }
.loading-state { text-align: center; padding: 2rem; color: #6b7280; font-size: 0.875rem; }
.error-banner { background: #fee2e2; color: #991b1b; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.875rem; }
.card { background: white; border-radius: 12px; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
.card-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb;
}
.card-header h3 { font-size: 1rem; font-weight: 600; color: #1f2937; }
.btn-refresh { background: none; border: none; color: #2e75b6; font-size: 0.8rem; cursor: pointer; }
.table-responsive { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th, .data-table td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid #e5e7eb; }
.data-table th { background: #f9fafb; font-size: 0.7rem; font-weight: 600; color: #6b7280; text-transform: uppercase; }
.data-table td { font-size: 0.875rem; color: #374151; }
.branch-name { font-weight: 500; }
.badge {
  background: #e0e7ff; color: #3730a3; padding: 0.2rem 0.6rem;
  border-radius: 12px; font-size: 0.75rem; font-weight: 600;
}
.empty-row { text-align: center; padding: 2rem; color: #9ca3af; }
.action-btns { display: flex; gap: 0.5rem; }
.btn-edit, .btn-delete {
  padding: 0.35rem 0.75rem; border: none; border-radius: 6px; cursor: pointer; font-size: 0.75rem;
}
.btn-edit   { background: #dbeafe; color: #1e40af; }
.btn-delete { background: #fee2e2; color: #991b1b; }

/* Modal */
.modal-overlay {
  position: fixed; inset: 0; background: rgba(0,0,0,0.4);
  display: flex; align-items: center; justify-content: center; z-index: 1000;
}
.modal {
  background: white; border-radius: 12px; width: 100%; max-width: 480px;
  box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}
.modal-sm { max-width: 380px; }
.modal-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb;
}
.modal-header h3 { font-size: 1rem; font-weight: 600; color: #1f2937; }
.modal-close { background: none; border: none; font-size: 1.1rem; cursor: pointer; color: #6b7280; }
.modal-body { padding: 1.25rem 1.5rem; }
.modal-footer {
  display: flex; justify-content: flex-end; gap: 0.75rem;
  padding: 1rem 1.5rem; border-top: 1px solid #e5e7eb;
}
.form-error { background: #fee2e2; color: #991b1b; padding: 0.6rem 0.75rem; border-radius: 6px; font-size: 0.8rem; margin-bottom: 1rem; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; font-size: 0.8rem; font-weight: 500; color: #374151; margin-bottom: 0.35rem; }
.required { color: #dc2626; }
.form-input {
  width: 100%; padding: 0.6rem 0.75rem; border: 1px solid #d1d5db; border-radius: 8px;
  font-size: 0.875rem; color: #1f2937; box-sizing: border-box; resize: vertical;
}
.form-input:focus { outline: none; border-color: #2e75b6; box-shadow: 0 0 0 2px rgba(46,117,182,0.15); }
.btn-cancel  { padding: 0.6rem 1.25rem; border: 1px solid #d1d5db; border-radius: 8px; background: white; cursor: pointer; font-size: 0.875rem; }
.btn-submit  { padding: 0.6rem 1.25rem; border: none; border-radius: 8px; background: #1f3864; color: white; cursor: pointer; font-size: 0.875rem; }
.btn-submit:disabled  { opacity: 0.6; cursor: not-allowed; }
.btn-delete-confirm { padding: 0.6rem 1.25rem; border: none; border-radius: 8px; background: #dc2626; color: white; cursor: pointer; font-size: 0.875rem; }
.warning-text { font-size: 0.8rem; color: #d97706; margin-top: 0.5rem; }

@media (max-width: 768px) {
  .main-content { margin-left: 70px; padding: 1rem; }
}
</style>