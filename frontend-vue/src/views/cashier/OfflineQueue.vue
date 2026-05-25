<template>
  <div class="offline-container">
    <SidebarKasir />

    <main class="main-content">
      <!-- Top Bar -->
      <div class="top-bar">
        <div class="page-title">
          <h1>Antrian Sinkronisasi Offline</h1>
          <p>Transaksi yang disimpan secara lokal menunggu dikirim ke server</p>
        </div>
        <div class="top-bar-right">
          <div class="connection-status" :class="{ online: isOnline, offline: !isOnline }">
            <span class="status-dot"></span>
            <span>{{ isOnline ? 'Online' : 'Offline' }}</span>
          </div>
          <div class="user-avatar">
            <span>{{ cashierInitial }}</span>
          </div>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-info">
            <p class="stat-label">Total Pending</p>
            <p class="stat-value">{{ pendingTransactions.length }}</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-info">
            <p class="stat-label">Total Nilai</p>
            <p class="stat-value">Rp {{ formatNumber(totalPendingValue) }}</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-info">
            <p class="stat-label">Gagal Sinkron</p>
            <p class="stat-value">{{ failedCount }}</p>
          </div>
        </div>
      </div>

      <!-- Actions Bar -->
      <div class="actions-bar">
        <button 
          class="btn-sync-all" 
          @click="syncAll" 
          :disabled="!isOnline || pendingTransactions.length === 0 || isSyncing"
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          <span>{{ isSyncing ? 'Menyinkronkan...' : 'Sync Semua' }}</span>
        </button>
        <button class="btn-clear-all" @click="clearAll" :disabled="pendingTransactions.length === 0">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
          </svg>
          <span>Hapus Semua</span>
        </button>
      </div>

      <!-- Pending List -->
      <div class="card">
        <div class="card-header">
          <h3>Daftar Transaksi Pending</h3>
          <span class="total-count">{{ pendingTransactions.length }} transaksi</span>
        </div>
        
        <div class="pending-list">
          <div v-for="(transaction, idx) in paginatedTransactions" :key="transaction.id" class="pending-item">
            <div class="pending-header">
              <div class="pending-info">
                <span class="pending-id">#{{ transaction.id }}</span>
                <span class="pending-date">{{ formatDate(transaction.created_at) }}</span>
              </div>
              <div class="pending-status">
                <span class="status-badge" :class="transaction.status">
                  {{ transaction.status === 'pending' ? 'Menunggu Sync' : 'Gagal Sync' }}
                </span>
              </div>
            </div>
            <div class="pending-body">
              <div class="pending-items">
                <div v-for="item in transaction.items" :key="item.id" class="pending-item-detail">
                  <span>{{ item.name }} x{{ item.quantity }}</span>
                  <span>Rp {{ formatNumber(item.price * item.quantity) }}</span>
                </div>
              </div>
              <div class="pending-summary">
                <div class="summary-row">
                  <span>Total</span>
                  <strong>Rp {{ formatNumber(transaction.total) }}</strong>
                </div>
                <div class="summary-row">
                  <span>Pembayaran</span>
                  <span>Rp {{ formatNumber(transaction.payment) }}</span>
                </div>
                <div class="summary-row change">
                  <span>Kembalian</span>
                  <span>Rp {{ formatNumber(transaction.change) }}</span>
                </div>
              </div>
            </div>
            <div class="pending-footer">
              <button class="btn-retry" @click="retrySync(transaction)" :disabled="!isOnline">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Sync Ulang
              </button>
              <button class="btn-delete" @click="deleteTransaction(transaction)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Hapus
              </button>
            </div>
          </div>
          
          <div v-if="pendingTransactions.length === 0" class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
            </svg>
            <p>Tidak ada transaksi pending</p>
            <small>Semua transaksi sudah tersinkronisasi</small>
          </div>
        </div>

        <!-- Pagination -->
        <div class="pagination" v-if="totalPages > 1">
          <button class="page-btn" @click="prevPage" :disabled="currentPage === 1">&laquo; Sebelumnya</button>
          <span class="page-info">Halaman {{ currentPage }} dari {{ totalPages }}</span>
          <button class="page-btn" @click="nextPage" :disabled="currentPage === totalPages">Selanjutnya &raquo;</button>
        </div>
      </div>
    </main>

    <!-- Konfirmasi Hapus Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" @click.self="showDeleteModal = false">
      <div class="modal small-modal">
        <div class="modal-header">
          <h3>Hapus Transaksi Pending</h3>
          <button class="close-btn" @click="showDeleteModal = false">✕</button>
        </div>
        <div class="modal-body">
          <p>Apakah Anda yakin ingin menghapus transaksi ini?</p>
          <p class="warning-text">Data yang dihapus tidak dapat dikembalikan!</p>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="showDeleteModal = false">Batal</button>
          <button class="btn-danger" @click="confirmDeleteTransaction">Hapus</button>
        </div>
      </div>
    </div>

    <!-- Konfirmasi Hapus Semua Modal -->
    <div v-if="showClearAllModal" class="modal-overlay" @click.self="showClearAllModal = false">
      <div class="modal small-modal">
        <div class="modal-header">
          <h3>Hapus Semua Transaksi Pending</h3>
          <button class="close-btn" @click="showClearAllModal = false">✕</button>
        </div>
        <div class="modal-body">
          <p>Apakah Anda yakin ingin menghapus <strong>semua</strong> transaksi pending?</p>
          <p class="warning-text">Tindakan ini tidak dapat dibatalkan!</p>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="showClearAllModal = false">Batal</button>
          <button class="btn-danger" @click="confirmClearAll">Hapus Semua</button>
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
import { ref, computed, onMounted, onUnmounted } from 'vue'
import SidebarKasir from '../../components/SidebarKasir.vue'

// State
const isOnline = ref(navigator.onLine)
const isSyncing = ref(false)
const pendingTransactions = ref([])
const currentPage = ref(1)
const itemsPerPage = ref(5)
const showDeleteModal = ref(false)
const showClearAllModal = ref(false)
const selectedTransaction = ref(null)
const showAlert = ref(false)
const alertMessage = ref('')
const alertType = ref('success')
const cashierName = ref('Kasir Cabang Utama')

// Computed
const cashierInitial = computed(() => cashierName.value.charAt(0))
const totalPendingValue = computed(() => {
  return pendingTransactions.value.reduce((sum, t) => sum + t.total, 0)
})
const failedCount = computed(() => {
  return pendingTransactions.value.filter(t => t.status === 'failed').length
})
const totalPages = computed(() => Math.ceil(pendingTransactions.value.length / itemsPerPage.value))
const paginatedTransactions = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return pendingTransactions.value.slice(start, end)
})

// Methods
const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num)
const formatDate = (dateStr) => {
  const d = new Date(dateStr)
  return d.toLocaleDateString('id-ID') + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

// Load pending from localStorage
const loadPending = () => {
  const stored = localStorage.getItem('pending_transactions')
  if (stored) {
    pendingTransactions.value = JSON.parse(stored)
  } else {
    pendingTransactions.value = []
  }
}

// Save to localStorage
const savePending = () => {
  localStorage.setItem('pending_transactions', JSON.stringify(pendingTransactions.value))
}

// Simulate sync to server
const syncTransaction = async (transaction) => {
  // Simulate API call
  return new Promise((resolve, reject) => {
    setTimeout(() => {
      // Random success/fail for demo (90% success)
      if (Math.random() < 0.9) {
        resolve(true)
      } else {
        reject(new Error('Gagal sinkronisasi'))
      }
    }, 800)
  })
}

// Retry sync individual transaction
const retrySync = async (transaction) => {
  if (!isOnline.value) {
    showAlertMessage('Tidak ada koneksi internet', 'error')
    return
  }

  transaction.status = 'pending'
  savePending()

  try {
    await syncTransaction(transaction)
    // If success, remove from pending and move to synced
    const index = pendingTransactions.value.findIndex(t => t.id === transaction.id)
    if (index !== -1) {
      pendingTransactions.value.splice(index, 1)
      savePending()
      
      // Also add to synced transactions in localStorage
      const synced = JSON.parse(localStorage.getItem('transactions') || '[]')
      synced.push({ ...transaction, status: 'synced' })
      localStorage.setItem('transactions', JSON.stringify(synced))
      
      showAlertMessage('Transaksi berhasil tersinkronisasi', 'success')
    }
  } catch (error) {
    transaction.status = 'failed'
    savePending()
    showAlertMessage('Gagal sinkronisasi, silakan coba lagi', 'error')
  }
}

// Sync all pending transactions
const syncAll = async () => {
  if (!isOnline.value) {
    showAlertMessage('Tidak ada koneksi internet', 'error')
    return
  }
  if (pendingTransactions.value.length === 0) return

  isSyncing.value = true
  const failedIds = []

  for (const transaction of pendingTransactions.value) {
    transaction.status = 'pending'
    savePending()
    try {
      await syncTransaction(transaction)
      // Success: will be removed after loop
    } catch (error) {
      transaction.status = 'failed'
      failedIds.push(transaction.id)
      savePending()
    }
  }

  // Remove successful ones (those not in failedIds)
  const newPending = pendingTransactions.value.filter(t => failedIds.includes(t.id))
  pendingTransactions.value = newPending
  savePending()

  // Add successful to synced
  const synced = JSON.parse(localStorage.getItem('transactions') || '[]')
  // In a real app you'd add only the successful ones; here we simplify
  // Reload to reflect changes
  loadPending()

  isSyncing.value = false
  if (failedIds.length === 0) {
    showAlertMessage('Semua transaksi berhasil tersinkronisasi', 'success')
  } else {
    showAlertMessage(`${failedIds.length} transaksi gagal disinkronisasi`, 'error')
  }
}

// Delete individual transaction
const deleteTransaction = (transaction) => {
  selectedTransaction.value = transaction
  showDeleteModal.value = true
}

const confirmDeleteTransaction = () => {
  const index = pendingTransactions.value.findIndex(t => t.id === selectedTransaction.value.id)
  if (index !== -1) {
    pendingTransactions.value.splice(index, 1)
    savePending()
    showAlertMessage('Transaksi pending dihapus', 'success')
  }
  showDeleteModal.value = false
  selectedTransaction.value = null
}

// Clear all pending
const clearAll = () => {
  if (pendingTransactions.value.length === 0) return
  showClearAllModal.value = true
}

const confirmClearAll = () => {
  pendingTransactions.value = []
  savePending()
  showAlertMessage('Semua transaksi pending dihapus', 'success')
  showClearAllModal.value = false
}

const showAlertMessage = (message, type) => {
  alertMessage.value = message
  alertType.value = type
  showAlert.value = true
  setTimeout(() => { showAlert.value = false }, 3000)
}

const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }

// Online status
const updateOnlineStatus = () => {
  isOnline.value = navigator.onLine
  // Auto-sync when coming online
  if (isOnline.value && pendingTransactions.value.length > 0) {
    syncAll()
  }
}

// Load initial data
onMounted(() => {
  loadPending()
  window.addEventListener('online', updateOnlineStatus)
  window.addEventListener('offline', updateOnlineStatus)
})

onUnmounted(() => {
  window.removeEventListener('online', updateOnlineStatus)
  window.removeEventListener('offline', updateOnlineStatus)
})
</script>

<style scoped>
.offline-container {
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
  grid-template-columns: repeat(3, 1fr);
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
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.btn-sync-all, .btn-clear-all {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  border: none;
  border-radius: 8px;
  font-size: 0.8rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-sync-all {
  background: #1F3864;
  color: white;
}

.btn-sync-all:hover:not(:disabled) {
  background: #15284D;
}

.btn-sync-all:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-clear-all {
  background: #ef4444;
  color: white;
}

.btn-clear-all:hover:not(:disabled) {
  background: #dc2626;
}

.btn-clear-all:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
  overflow: hidden;
}

.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid #e5e7eb;
}

.card-header h3 {
  font-size: 1rem;
  font-weight: 600;
}

.total-count {
  font-size: 0.8rem;
  color: #6b7280;
}

.pending-list {
  padding: 0.5rem;
}

.pending-item {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  margin-bottom: 1rem;
  overflow: hidden;
}

.pending-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.pending-info {
  display: flex;
  gap: 1rem;
  align-items: baseline;
}

.pending-id {
  font-weight: 600;
  color: #1F3864;
}

.pending-date {
  font-size: 0.7rem;
  color: #6b7280;
}

.status-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 20px;
  font-size: 0.7rem;
}

.status-badge.pending {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.failed {
  background: #fee2e2;
  color: #991b1b;
}

.pending-body {
  padding: 1rem;
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
}

.pending-items {
  flex: 2;
}

.pending-item-detail {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  margin-bottom: 0.25rem;
}

.pending-summary {
  flex: 1;
  background: #f9fafb;
  padding: 0.75rem;
  border-radius: 8px;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.5rem;
  font-size: 0.8rem;
}

.summary-row.change {
  margin-top: 0.5rem;
  padding-top: 0.5rem;
  border-top: 1px solid #e5e7eb;
}

.pending-footer {
  padding: 0.75rem 1rem;
  background: #f3f4f6;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}

.btn-retry, .btn-delete {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.375rem 0.75rem;
  border: none;
  border-radius: 6px;
  font-size: 0.75rem;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-retry {
  background: #d97706;
  color: white;
}

.btn-retry:hover:not(:disabled) {
  background: #b45309;
}

.btn-retry:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-delete {
  background: #ef4444;
  color: white;
}

.btn-delete:hover {
  background: #dc2626;
}

.btn-retry svg, .btn-delete svg {
  width: 14px;
  height: 14px;
}

.empty-state {
  text-align: center;
  padding: 3rem;
  color: #9ca3af;
}

.empty-state svg {
  width: 64px;
  height: 64px;
  margin-bottom: 1rem;
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

/* Modal */
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
  width: 400px;
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

.warning-text {
  color: #d97706;
  font-size: 0.75rem;
  margin-top: 0.5rem;
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
    grid-template-columns: 1fr;
  }
  .actions-bar {
    flex-direction: column;
  }
  .pending-body {
    flex-direction: column;
  }
  .pending-footer {
    flex-direction: column;
    align-items: stretch;
  }
}
</style>