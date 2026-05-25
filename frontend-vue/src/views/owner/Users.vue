<template>
  <div class="users-container">
    <SidebarOwner />

    <main class="main-content">
      <!-- Top Bar -->
      <div class="top-bar">
        <div class="page-title">
          <h1>Manajemen Pengguna</h1>
          <p>Kelola akun kasir, reset password, dan atur akses cabang</p>
        </div>
        <div class="top-bar-right">
          <div class="connection-status" :class="{ online: isOnline, offline: !isOnline }">
            <span class="status-dot"></span>
            <span>{{ isOnline ? 'Online' : 'Offline' }}</span>
          </div>
          <div class="user-avatar">
            <span>{{ userInitial }}</span>
          </div>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-info">
            <p class="stat-label">Total Pengguna</p>
            <p class="stat-value">{{ totalUsers }}</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-info">
            <p class="stat-label">Kasir Aktif</p>
            <p class="stat-value">{{ activeCashiers }}</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-info">
            <p class="stat-label">Cabang Utama</p>
            <p class="stat-value">{{ branch1Count }}</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-info">
            <p class="stat-label">Cabang Kedua</p>
            <p class="stat-value">{{ branch2Count }}</p>
          </div>
        </div>
      </div>

      <!-- Actions Bar -->
      <div class="actions-bar">
        <div class="search-bar">
          <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input v-model="searchQuery" type="text" placeholder="Cari nama atau email..." class="search-input">
        </div>
        <button class="btn-add" @click="openAddModal">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 4v16m8-8H4" />
          </svg>
          <span>Tambah Kasir</span>
        </button>
      </div>

      <!-- Users Table -->
      <div class="card">
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Cabang</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(user, index) in paginatedUsers" :key="user.id">
                <td>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                <td class="user-name">{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td><span class="role-badge kasir">Kasir</span></td>
                <td>{{ user.branch_name }}</td>
                <td>
                  <span class="status-badge" :class="user.is_active ? 'active' : 'inactive'">
                    {{ user.is_active ? 'Aktif' : 'Nonaktif' }}
                  </span>
                </td>
                <td class="actions">
                  <button class="action-btn edit" @click="openEditModal(user)" title="Edit">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  <button class="action-btn reset" @click="openResetPassword(user)" title="Reset Password">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v-2l2.257-2.257A6 6 0 0121 9z" />
                      <path d="M3 3l18 18" />
                    </svg>
                  </button>
                  <button class="action-btn delete" @click="confirmDelete(user)" title="Hapus">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                 </td>
               </tr>
              <tr v-if="filteredUsers.length === 0">
                <td colspan="7" class="empty-row">Tidak ada pengguna yang ditemukan</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="pagination" v-if="totalPages > 1">
          <button class="page-btn" @click="prevPage" :disabled="currentPage === 1">&laquo; Sebelumnya</button>
          <span class="page-info">Halaman {{ currentPage }} dari {{ totalPages }}</span>
          <button class="page-btn" @click="nextPage" :disabled="currentPage === totalPages">Selanjutnya &raquo;</button>
        </div>
      </div>
    </main>

    <!-- Add/Edit User Modal -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h3>{{ isEditMode ? 'Edit Kasir' : 'Tambah Kasir Baru' }}</h3>
          <button class="close-btn" @click="closeModal">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nama Lengkap *</label>
            <input v-model="form.name" type="text" class="modal-input" placeholder="Nama kasir">
            <p v-if="errors.name" class="error-text">{{ errors.name }}</p>
          </div>
          <div class="form-group">
            <label>Email *</label>
            <input v-model="form.email" type="email" class="modal-input" placeholder="email@example.com">
            <p v-if="errors.email" class="error-text">{{ errors.email }}</p>
          </div>
          <div class="form-group" v-if="!isEditMode">
            <label>Password *</label>
            <input v-model="form.password" type="password" class="modal-input" placeholder="Minimal 6 karakter">
            <p v-if="errors.password" class="error-text">{{ errors.password }}</p>
          </div>
          <div class="form-group">
            <label>Cabang</label>
            <select v-model="form.branch_id" class="modal-input">
              <option value="1">Cabang Utama</option>
              <option value="2">Cabang Kedua</option>
            </select>
          </div>
          <div class="form-group">
            <label class="checkbox-label">
              <input type="checkbox" v-model="form.is_active">
              <span>Aktifkan akun</span>
            </label>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="closeModal">Batal</button>
          <button class="btn-primary" @click="saveUser">Simpan</button>
        </div>
      </div>
    </div>

    <!-- Reset Password Modal -->
    <div v-if="showResetModal" class="modal-overlay" @click.self="showResetModal = false">
      <div class="modal small-modal">
        <div class="modal-header">
          <h3>Reset Password</h3>
          <button class="close-btn" @click="showResetModal = false">✕</button>
        </div>
        <div class="modal-body">
          <p>Reset password untuk <strong>{{ selectedUser?.name }}</strong></p>
          <div class="form-group">
            <label>Password Baru</label>
            <input v-model="newPassword" type="password" class="modal-input" placeholder="Minimal 6 karakter">
          </div>
          <div class="form-group">
            <label>Konfirmasi Password</label>
            <input v-model="confirmPassword" type="password" class="modal-input" placeholder="Ulangi password">
            <p v-if="passwordError" class="error-text">{{ passwordError }}</p>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="showResetModal = false">Batal</button>
          <button class="btn-primary" @click="resetPassword" :disabled="!newPassword || newPassword !== confirmPassword">Reset</button>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal small-modal">
        <div class="modal-header">
          <h3>Hapus Kasir</h3>
          <button class="close-btn" @click="showDeleteModal = false">✕</button>
        </div>
        <div class="modal-body">
          <p>Apakah Anda yakin ingin menghapus akun <strong>{{ selectedUser?.name }}</strong>?</p>
          <p class="warning-text">Data yang dihapus tidak dapat dikembalikan!</p>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="showDeleteModal = false">Batal</button>
          <button class="btn-danger" @click="deleteUser">Hapus</button>
        </div>
      </div>
    </div>

    <!-- Alert Toast -->
    <div v-if="showAlert" class="alert-toast" :class="alertType">
      <span>{{ alertMessage }}</span>
      <button @click="showAlert = false">✕</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import SidebarOwner from '../../components/SidebarOwner.vue'

// Reactive state
const isOnline = ref(navigator.onLine)
const searchQuery = ref('')
const currentPage = ref(1)
const itemsPerPage = ref(10)
const showModal = ref(false)
const showResetModal = ref(false)
const showDeleteModal = ref(false)
const isEditMode = ref(false)
const showAlert = ref(false)
const alertMessage = ref('')
const alertType = ref('success')
const selectedUser = ref(null)
const newPassword = ref('')
const confirmPassword = ref('')
const passwordError = ref('')

const userName = ref('Owner Nicky Frozen')

// Form data
const form = ref({
  id: null,
  name: '',
  email: '',
  password: '',
  branch_id: 1,
  is_active: true
})

const errors = ref({
  name: '',
  email: '',
  password: ''
})

// Dummy users data
const users = ref([
  { id: 1, name: 'Siti Aisyah', email: 'siti@nicksfrozen.com', role: 'kasir', branch_id: 1, branch_name: 'Cabang Utama', is_active: true },
  { id: 2, name: 'Budi Santoso', email: 'budi@nicksfrozen.com', role: 'kasir', branch_id: 2, branch_name: 'Cabang Kedua', is_active: true },
  { id: 3, name: 'Dewi Lestari', email: 'dewi@nicksfrozen.com', role: 'kasir', branch_id: 1, branch_name: 'Cabang Utama', is_active: false }
])

// Computed
const userInitial = computed(() => userName.value.charAt(0))

const filteredUsers = computed(() => {
  let result = [...users.value]
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(u => u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q))
  }
  return result
})

const totalUsers = computed(() => users.value.length)
const activeCashiers = computed(() => users.value.filter(u => u.is_active).length)
const branch1Count = computed(() => users.value.filter(u => u.branch_id === 1).length)
const branch2Count = computed(() => users.value.filter(u => u.branch_id === 2).length)

const totalPages = computed(() => Math.ceil(filteredUsers.value.length / itemsPerPage.value))
const paginatedUsers = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredUsers.value.slice(start, end)
})

// Methods
const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num)

const openAddModal = () => {
  isEditMode.value = false
  form.value = { id: null, name: '', email: '', password: '', branch_id: 1, is_active: true }
  errors.value = { name: '', email: '', password: '' }
  showModal.value = true
}

const openEditModal = (user) => {
  isEditMode.value = true
  form.value = { ...user, password: '' }
  errors.value = { name: '', email: '', password: '' }
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const validateForm = () => {
  let isValid = true
  errors.value = { name: '', email: '', password: '' }

  if (!form.value.name) {
    errors.value.name = 'Nama wajib diisi'
    isValid = false
  }
  if (!form.value.email) {
    errors.value.email = 'Email wajib diisi'
    isValid = false
  } else if (!/\S+@\S+\.\S+/.test(form.value.email)) {
    errors.value.email = 'Format email tidak valid'
    isValid = false
  }
  if (!isEditMode.value && !form.value.password) {
    errors.value.password = 'Password wajib diisi'
    isValid = false
  } else if (!isEditMode.value && form.value.password.length < 6) {
    errors.value.password = 'Password minimal 6 karakter'
    isValid = false
  }
  return isValid
}

const saveUser = () => {
  if (!validateForm()) return

  if (isEditMode.value) {
    const index = users.value.findIndex(u => u.id === form.value.id)
    if (index !== -1) {
      users.value[index] = {
        ...users.value[index],
        name: form.value.name,
        email: form.value.email,
        branch_id: form.value.branch_id,
        branch_name: form.value.branch_id === 1 ? 'Cabang Utama' : 'Cabang Kedua',
        is_active: form.value.is_active
      }
      showAlertMessage('Pengguna berhasil diperbarui!', 'success')
    }
  } else {
    const newId = Math.max(...users.value.map(u => u.id), 0) + 1
    const newUser = {
      id: newId,
      name: form.value.name,
      email: form.value.email,
      role: 'kasir',
      branch_id: form.value.branch_id,
      branch_name: form.value.branch_id === 1 ? 'Cabang Utama' : 'Cabang Kedua',
      is_active: form.value.is_active
    }
    users.value.push(newUser)
    showAlertMessage('Kasir berhasil ditambahkan!', 'success')
  }
  closeModal()
}

const openResetPassword = (user) => {
  selectedUser.value = user
  newPassword.value = ''
  confirmPassword.value = ''
  passwordError.value = ''
  showResetModal.value = true
}

const resetPassword = () => {
  if (newPassword.value !== confirmPassword.value) {
    passwordError.value = 'Password tidak cocok'
    return
  }
  if (newPassword.value.length < 6) {
    passwordError.value = 'Password minimal 6 karakter'
    return
  }
  // Simulate password reset
  showAlertMessage(`Password untuk ${selectedUser.value.name} telah direset`, 'success')
  showResetModal.value = false
}

const confirmDelete = (user) => {
  selectedUser.value = user
  showDeleteModal.value = true
}

const deleteUser = () => {
  const index = users.value.findIndex(u => u.id === selectedUser.value.id)
  if (index !== -1) {
    users.value.splice(index, 1)
    showAlertMessage('Pengguna berhasil dihapus!', 'success')
  }
  showDeleteModal.value = false
  selectedUser.value = null
}

const showAlertMessage = (message, type) => {
  alertMessage.value = message
  alertType.value = type
  showAlert.value = true
  setTimeout(() => { showAlert.value = false }, 3000)
}

const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }

// Reset page on search
watch(searchQuery, () => { currentPage.value = 1 })

// Online status
const updateOnlineStatus = () => { isOnline.value = navigator.onLine }

onMounted(() => {
  window.addEventListener('online', updateOnlineStatus)
  window.addEventListener('offline', updateOnlineStatus)
})

// Cleanup
onUnmounted(() => {
  window.removeEventListener('online', updateOnlineStatus)
  window.removeEventListener('offline', updateOnlineStatus)
})
</script>

<style scoped>
.users-container {
  display: flex;
  min-height: 100vh;
  background: #f3f4f6;
}

.main-content {
  flex: 1;
  margin-left: 260px;
  padding: 1.5rem;
}

.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
  background: white;
  padding: 1rem 1.5rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.page-title h1 {
  font-size: 1.25rem;
  color: #1F3864;
  margin-bottom: 0.25rem;
}

.page-title p {
  font-size: 0.8rem;
  color: #6b7280;
}

.top-bar-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.connection-status {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.375rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
}

.connection-status.online {
  background: #d1fae5;
  color: #065f46;
}

.connection-status.offline {
  background: #fee2e2;
  color: #991b1b;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: currentColor;
}

.user-avatar {
  width: 40px;
  height: 40px;
  background: #2E75B6;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.stat-card {
  background: white;
  border-radius: 12px;
  padding: 1rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.stat-label {
  font-size: 0.7rem;
  color: #6b7280;
  margin-bottom: 0.25rem;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
}

.actions-bar {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.search-bar {
  flex: 2;
  position: relative;
}

.search-icon {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  width: 18px;
  height: 18px;
  color: #9ca3af;
}

.search-input {
  width: 100%;
  padding: 0.625rem 1rem 0.625rem 2.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.8rem;
}

.btn-add {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 0.8rem;
}

.btn-add svg {
  width: 16px;
  height: 16px;
}

.card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  overflow: hidden;
}

.table-responsive {
  overflow-x: auto;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.data-table th,
.data-table td {
  padding: 0.75rem 1rem;
  text-align: left;
  border-bottom: 1px solid #e5e7eb;
}

.data-table th {
  background: #f9fafb;
  font-size: 0.7rem;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
}

.data-table td {
  font-size: 0.8rem;
}

.user-name {
  font-weight: 500;
}

.role-badge {
  background: #e0e7ff;
  color: #3730a3;
  padding: 0.25rem 0.5rem;
  border-radius: 20px;
  font-size: 0.7rem;
}

.status-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 20px;
  font-size: 0.7rem;
}

.status-badge.active {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.inactive {
  background: #fee2e2;
  color: #991b1b;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  padding: 0.25rem;
  background: none;
  border: none;
  cursor: pointer;
  border-radius: 4px;
}

.action-btn svg {
  width: 18px;
  height: 18px;
}

.action-btn.edit {
  color: #2E75B6;
}

.action-btn.edit:hover {
  background: #dbeafe;
}

.action-btn.reset {
  color: #f59e0b;
}

.action-btn.reset:hover {
  background: #fef3c7;
}

.action-btn.delete {
  color: #ef4444;
}

.action-btn.delete:hover {
  background: #fee2e2;
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-top: 1px solid #e5e7eb;
}

.page-btn {
  padding: 0.5rem 1rem;
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  cursor: pointer;
  font-size: 0.8rem;
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  font-size: 0.8rem;
  color: #6b7280;
}

.empty-row {
  text-align: center;
  color: #9ca3af;
  padding: 2rem;
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal {
  background: white;
  border-radius: 12px;
  width: 500px;
  max-width: 90%;
  overflow: hidden;
}

.small-modal {
  width: 400px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-body {
  padding: 1.5rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  padding: 1rem;
  border-top: 1px solid #e5e7eb;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
}

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  font-size: 0.8rem;
  font-weight: 500;
  margin-bottom: 0.5rem;
}

.modal-input {
  width: 100%;
  padding: 0.625rem 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.8rem;
}

.modal-input:focus {
  outline: none;
  border-color: #2E75B6;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.checkbox-label span {
  font-size: 0.8rem;
}

.error-text {
  color: #ef4444;
  font-size: 0.7rem;
  margin-top: 0.25rem;
}

.warning-text {
  color: #d97706;
  font-size: 0.75rem;
  margin-top: 0.5rem;
}

.btn-primary {
  padding: 0.5rem 1rem;
  background: #1F3864;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.btn-secondary {
  padding: 0.5rem 1rem;
  background: #e5e7eb;
  color: #374151;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.btn-danger {
  padding: 0.5rem 1rem;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
}

.alert-toast {
  position: fixed;
  bottom: 20px;
  right: 20px;
  padding: 1rem 1.5rem;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 1rem;
  z-index: 1100;
  animation: slideIn 0.3s ease;
}

.alert-toast.success {
  background: #10b981;
  color: white;
}

.alert-toast.error {
  background: #ef4444;
  color: white;
}

.alert-toast button {
  background: none;
  border: none;
  color: white;
  cursor: pointer;
  font-size: 1rem;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 70px;
    padding: 1rem;
  }

  .stats-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .actions-bar {
    flex-direction: column;
  }

  .btn-add {
    justify-content: center;
  }
}
</style>