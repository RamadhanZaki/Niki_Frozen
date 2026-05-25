<template>
  <div class="transactions-container">
    <SidebarKasir />

    <main class="main-content">
      <!-- Top Bar -->
      <div class="top-bar">
        <div class="page-title">
          <h1>Riwayat Transaksi</h1>
          <p>Daftar transaksi yang telah Anda lakukan</p>
        </div>
        <div class="user-avatar">
          <span>{{ cashierInitial }}</span>
        </div>
      </div>

      <!-- Filter Bar -->
      <div class="filter-bar">
        <div class="search-bar">
          <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Cari berdasarkan nama produk..." 
            class="search-input"
          >
        </div>
        <select v-model="filterStatus" class="filter-select">
          <option value="all">Semua Status</option>
          <option value="synced">Tersinkronisasi</option>
          <option value="pending">Menunggu Sync</option>
          <option value="failed">Gagal</option>
        </select>
        <select v-model="sortBy" class="filter-select">
          <option value="newest">Terbaru</option>
          <option value="oldest">Terlama</option>
          <option value="highest">Nilai Tertinggi</option>
          <option value="lowest">Nilai Terendah</option>
        </select>
      </div>

      <!-- Transactions List -->
      <div class="card">
        <div class="card-header">
          <h3>Transaksi</h3>
          <span class="total-count">{{ filteredTransactions.length }} transaksi</span>
        </div>
        
        <div class="transactions-list">
          <div v-for="(transaction, idx) in paginatedTransactions" :key="transaction.id" class="transaction-item">
            <div class="transaction-header">
              <div class="transaction-info">
                <span class="transaction-id">#{{ transaction.id }}</span>
                <span class="transaction-date">{{ formatDate(transaction.created_at) }}</span>
              </div>
              <div class="transaction-status">
                <span class="status-badge" :class="transaction.status">
                  {{ getStatusText(transaction.status) }}
                </span>
              </div>
            </div>
            <div class="transaction-body">
              <div class="transaction-items">
                <div v-for="item in transaction.items" :key="item.id" class="transaction-item-detail">
                  <span>{{ item.name }} x{{ item.quantity }}</span>
                  <span>Rp {{ formatNumber(item.price * item.quantity) }}</span>
                </div>
              </div>
              <div class="transaction-summary">
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
            <div class="transaction-footer" v-if="transaction.status !== 'synced'">
              <button class="btn-retry" @click="retrySync(transaction)" :disabled="!isOnline">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Sync Ulang
              </button>
            </div>
          </div>
          
          <div v-if="filteredTransactions.length === 0" class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            <p>Belum ada transaksi</p>
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import SidebarKasir from '../../components/SidebarKasir.vue'

// State
const searchQuery = ref('')
const filterStatus = ref('all')
const sortBy = ref('newest')
const currentPage = ref(1)
const itemsPerPage = ref(10)
const isOnline = ref(navigator.onLine)

// Data
const cashierName = ref('Kasir Cabang Utama')
const cashierEmail = ref('kasir@nicksfrozen.com') // akan diambil dari localStorage

// Ambil transaksi dari localStorage
const loadTransactions = () => {
  const allTransactions = JSON.parse(localStorage.getItem('transactions') || '[]')
  const pending = JSON.parse(localStorage.getItem('pending_transactions') || '[]')
  // Gabungkan transaksi online (sudah tersimpan di server) dan pending (offline)
  // Untuk demo, kita asumsikan transaksi yang sudah tersimpan di 'transactions' memiliki status 'synced'
  // Dan yang di 'pending_transactions' memiliki status 'pending' atau 'failed'
  const synced = allTransactions.map(t => ({ ...t, status: 'synced' }))
  const pendingList = pending.map(p => ({ ...p, status: p.status || 'pending' }))
  return [...synced, ...pendingList]
}

const allTransactions = ref([])

// Computed
const cashierInitial = computed(() => cashierName.value.charAt(0))

const filteredTransactions = computed(() => {
  let result = [...allTransactions.value]
  
  // Filter berdasarkan kasir yang login (gunakan email atau nama)
  const currentUser = JSON.parse(localStorage.getItem('user') || '{}')
  const currentEmail = currentUser.email || cashierEmail.value
  result = result.filter(t => t.cashier_email === currentEmail)
  
  // Filter status
  if (filterStatus.value !== 'all') {
    result = result.filter(t => t.status === filterStatus.value)
  }
  
  // Filter search (cari di nama produk)
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(t => 
      t.items.some(item => item.name.toLowerCase().includes(q))
    )
  }
  
  // Sorting
  switch (sortBy.value) {
    case 'newest':
      result.sort((a,b) => new Date(b.created_at) - new Date(a.created_at))
      break
    case 'oldest':
      result.sort((a,b) => new Date(a.created_at) - new Date(b.created_at))
      break
    case 'highest':
      result.sort((a,b) => b.total - a.total)
      break
    case 'lowest':
      result.sort((a,b) => a.total - b.total)
      break
  }
  
  return result
})

const totalPages = computed(() => Math.ceil(filteredTransactions.value.length / itemsPerPage.value))
const paginatedTransactions = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredTransactions.value.slice(start, end)
})

// Methods
const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num)
const formatDate = (dateStr) => {
  const d = new Date(dateStr)
  return d.toLocaleDateString('id-ID') + ' ' + d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

const getStatusText = (status) => {
  switch (status) {
    case 'synced': return 'Tersinkronisasi'
    case 'pending': return 'Menunggu Sync'
    case 'failed': return 'Gagal Sync'
    default: return 'Unknown'
  }
}

const retrySync = async (transaction) => {
  if (!isOnline.value) {
    alert('Tidak ada koneksi internet. Silakan coba lagi nanti.')
    return
  }
  
  // Update status menjadi syncing
  transaction.status = 'pending'
  // Simulasi sync ke server
  try {
    await new Promise(resolve => setTimeout(resolve, 1000))
    // Hapus dari pending list, tambahkan ke synced
    const pendingList = JSON.parse(localStorage.getItem('pending_transactions') || '[]')
    const updatedList = pendingList.filter(p => p.id !== transaction.id)
    localStorage.setItem('pending_transactions', JSON.stringify(updatedList))
    
    // Tambahkan ke transaksi utama (synced)
    const syncedList = JSON.parse(localStorage.getItem('transactions') || '[]')
    syncedList.push({ ...transaction, status: 'synced' })
    localStorage.setItem('transactions', JSON.stringify(syncedList))
    
    // Refresh data
    allTransactions.value = loadTransactions()
  } catch (error) {
    transaction.status = 'failed'
    // Update di localStorage
    const pendingList = JSON.parse(localStorage.getItem('pending_transactions') || '[]')
    const idx = pendingList.findIndex(p => p.id === transaction.id)
    if (idx !== -1) pendingList[idx].status = 'failed'
    localStorage.setItem('pending_transactions', JSON.stringify(pendingList))
    allTransactions.value = loadTransactions()
  }
}

const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }

// Reset page saat filter berubah
watch([searchQuery, filterStatus, sortBy], () => {
  currentPage.value = 1
})

// Ambil data saat komponen dimuat
onMounted(() => {
  allTransactions.value = loadTransactions()
  
  // Simulasi data dummy jika belum ada (untuk demo)
  if (allTransactions.value.length === 0) {
    const dummyTransactions = [
      {
        id: 1001,
        cashier_email: 'kasir@nicksfrozen.com',
        items: [{ id: 1, name: 'Nugget Ayam', price: 35000, quantity: 2 }],
        total: 70000,
        payment: 100000,
        change: 30000,
        created_at: new Date().toISOString(),
        status: 'synced'
      },
      {
        id: 1002,
        cashier_email: 'kasir@nicksfrozen.com',
        items: [{ id: 2, name: 'Sosis Solo', price: 28000, quantity: 1 }],
        total: 28000,
        payment: 50000,
        change: 22000,
        created_at: new Date(Date.now() - 86400000).toISOString(),
        status: 'synced'
      }
    ]
    localStorage.setItem('transactions', JSON.stringify(dummyTransactions))
    allTransactions.value = loadTransactions()
  }
})

// Update online status
const updateOnlineStatus = () => { isOnline.value = navigator.onLine }
window.addEventListener('online', updateOnlineStatus)
window.addEventListener('offline', updateOnlineStatus)
</script>

<style scoped>
.transactions-container {
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

.filter-bar {
  background: white;
  border-radius: 12px;
  padding: 1rem;
  margin-bottom: 1.5rem;
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
  align-items: center;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
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

.filter-select {
  padding: 0.625rem 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.8rem;
  background: white;
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

.transactions-list {
  padding: 0.5rem;
}

.transaction-item {
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  margin-bottom: 1rem;
  overflow: hidden;
  transition: box-shadow 0.2s;
}

.transaction-item:hover {
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.transaction-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 1rem;
  background: #f9fafb;
  border-bottom: 1px solid #e5e7eb;
}

.transaction-info {
  display: flex;
  gap: 1rem;
  align-items: baseline;
}

.transaction-id {
  font-weight: 600;
  color: #1F3864;
}

.transaction-date {
  font-size: 0.7rem;
  color: #6b7280;
}

.status-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 20px;
  font-size: 0.7rem;
}

.status-badge.synced {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.pending {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.failed {
  background: #fee2e2;
  color: #991b1b;
}

.transaction-body {
  padding: 1rem;
  display: flex;
  flex-wrap: wrap;
  gap: 1.5rem;
}

.transaction-items {
  flex: 2;
}

.transaction-item-detail {
  display: flex;
  justify-content: space-between;
  font-size: 0.8rem;
  margin-bottom: 0.25rem;
}

.transaction-summary {
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

.transaction-footer {
  padding: 0.75rem 1rem;
  background: #fef3c7;
  border-top: 1px solid #fde68a;
  text-align: right;
}

.btn-retry {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.375rem 0.75rem;
  background: #d97706;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 0.75rem;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-retry:hover:not(:disabled) {
  background: #b45309;
}

.btn-retry:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-retry svg {
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

@media (max-width: 768px) {
  .main-content {
    margin-left: 70px;
    padding: 1rem;
  }
  .filter-bar {
    flex-direction: column;
    align-items: stretch;
  }
  .transaction-body {
    flex-direction: column;
  }
}
</style>