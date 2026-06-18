<template>
  <div class="shift-container">
    <SidebarKasir />

    <main class="main-content">
      <!-- Top Bar -->
      <div class="top-bar">
        <div class="page-title">
          <h1>Manajemen Shift</h1>
          <p>Kasir: {{ cashierName }} | {{ cashierBranch }}</p>
        </div>
        <div class="user-avatar">
          <span>{{ cashierInitial }}</span>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loadingShift" class="loading-card">
        <span class="spinner"></span>
        <p>Mengecek status shift...</p>
      </div>

      <!-- Current Shift Info -->
      <div class="current-shift-card" v-else-if="activeShift">
        <div class="shift-header">
          <div class="shift-status active">
            <span class="status-dot"></span>
            <span>Shift Aktif</span>
          </div>
          <p class="shift-time">Dibuka: {{ formatDateTime(activeShift.opened_at) }}</p>
        </div>

        <div class="shift-stats">
          <div class="stat-item">
            <span class="stat-label">Kas Awal</span>
            <span class="stat-value">Rp {{ formatNumber(activeShift.opening_cash) }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">Total Transaksi</span>
            <span class="stat-value">{{ activeShift.total_transactions }} transaksi</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">Total Penjualan</span>
            <span class="stat-value">Rp {{ formatNumber(activeShift.total_sales) }}</span>
          </div>
        </div>

        <button class="btn-close-shift" @click="openCloseShiftModal">
          Tutup Shift
        </button>
      </div>

      <!-- No Active Shift -->
      <div class="no-shift-card" v-else>
        <div class="no-shift-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h3>Tidak Ada Shift Aktif</h3>
        <p>Silakan buka shift baru untuk memulai transaksi</p>
        <button class="btn-open-shift" @click="openOpenShiftModal">
          Buka Shift Baru
        </button>
      </div>

      <!-- Shift History -->
      <div class="card">
        <div class="card-header">
          <h3>Riwayat Shift</h3>
          <select v-model="historyFilter" class="filter-select" @change="fetchShiftHistory">
            <option value="week">Minggu Ini</option>
            <option value="month">Bulan Ini</option>
            <option value="all">Semua</option>
          </select>
        </div>

        <div v-if="loadingHistory" class="loading-row">
          <span class="spinner"></span> Memuat riwayat...
        </div>

        <div class="table-responsive" v-else>
          <table class="data-table">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Kas Awal</th>
                <th>Total Penjualan</th>
                <th>Kas Akhir</th>
                <th>Kas Diharapkan</th>
                <th>Selisih</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="shift in filteredShiftHistory" :key="shift.id">
                <td>
                  {{ formatDate(shift.opened_at) }}<br />
                  <small>{{ formatTime(shift.opened_at) }}</small>
                </td>
                <td>Rp {{ formatNumber(shift.opening_cash) }}</td>
                <td>Rp {{ formatNumber(shift.total_sales) }}</td>
                <td>Rp {{ formatNumber(shift.closing_cash ?? 0) }}</td>
                <td>Rp {{ formatNumber(shift.expected_cash ?? 0) }}</td>
                <td :class="getDifferenceClass(shift.difference ?? 0)">
                  {{ (shift.difference ?? 0) > 0 ? '+' : '' }}Rp
                  {{ formatNumber(Math.abs(shift.difference ?? 0)) }}
                </td>
                <td>
                  <span class="status-badge" :class="getStatusClass(shift)">
                    {{ getStatusText(shift) }}
                  </span>
                </td>
              </tr>
              <tr v-if="filteredShiftHistory.length === 0">
                <td colspan="7" class="empty-row">Belum ada riwayat shift</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="pagination" v-if="historyMeta && historyMeta.last_page > 1">
          <button class="page-btn" @click="changePage(historyMeta.current_page - 1)"
            :disabled="historyMeta.current_page === 1">
            &laquo; Sebelumnya
          </button>
          <span class="page-info">
            Halaman {{ historyMeta.current_page }} dari {{ historyMeta.last_page }}
          </span>
          <button class="page-btn" @click="changePage(historyMeta.current_page + 1)"
            :disabled="historyMeta.current_page === historyMeta.last_page">
            Selanjutnya &raquo;
          </button>
        </div>
      </div>
    </main>

    <!-- Buka Shift Modal -->
    <div v-if="showOpenModal" class="modal-overlay" @click.self="showOpenModal = false">
      <div class="modal">
        <div class="modal-header">
          <h3>Buka Shift Baru</h3>
          <button class="close-btn" @click="showOpenModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Saldo Kas Awal</label>
            <div class="input-wrapper">
              <span class="currency">Rp</span>
              <input
                type="number"
                v-model.number="openingCashInput"
                class="modal-input"
                placeholder="0"
                min="0"
              />
            </div>
            <small>Masukkan jumlah uang yang ada di kasir saat memulai shift</small>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="showOpenModal = false">Batal</button>
          <button
            class="btn-primary"
            @click="openShift"
            :disabled="openingCashInput < 0 || openingShiftLoading"
          >
            <span v-if="openingShiftLoading" class="spinner-sm"></span>
            Buka Shift
          </button>
        </div>
      </div>
    </div>

    <!-- Tutup Shift Modal -->
    <div v-if="showCloseModal" class="modal-overlay" @click.self="showCloseModal = false">
      <div class="modal large-modal">
        <div class="modal-header">
          <h3>Tutup Shift</h3>
          <button class="close-btn" @click="showCloseModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="shift-summary">
            <div class="summary-row">
              <span>Kas Awal</span>
              <span>Rp {{ formatNumber(activeShift?.opening_cash ?? 0) }}</span>
            </div>
            <div class="summary-row">
              <span>Total Penjualan</span>
              <span>Rp {{ formatNumber(activeShift?.total_sales ?? 0) }}</span>
            </div>
            <div class="summary-row total">
              <span>Kas yang Diharapkan</span>
              <span>Rp {{ formatNumber(expectedCash) }}</span>
            </div>
            <div class="summary-row">
              <span>Kas Fisik (Masukkan nominal)</span>
              <div class="closing-input">
                <span class="currency">Rp</span>
                <input
                  type="number"
                  v-model.number="closingCashInput"
                  class="modal-input inline"
                  placeholder="0"
                  min="0"
                />
              </div>
            </div>

            <div class="difference-result" v-if="closingCashInput > 0">
              <div class="difference-card" :class="differenceClass">
                <div class="difference-icon">
                  <svg v-if="difference === 0" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                  </svg>
                </div>
                <div class="difference-text">
                  <h4 v-if="difference === 0">✓ Kas Sesuai</h4>
                  <h4 v-else-if="difference < 0">Kurang Rp {{ formatNumber(Math.abs(difference)) }}</h4>
                  <h4 v-else>Lebih Rp {{ formatNumber(difference) }}</h4>
                  <p v-if="Math.abs(difference) > 5000" class="warning-text">
                    ⚠️ Selisih melebihi batas toleransi (Rp 5.000)
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="showCloseModal = false">Batal</button>
          <button
            class="btn-primary"
            @click="closeShift"
            :disabled="closingShiftLoading"
          >
            <span v-if="closingShiftLoading" class="spinner-sm"></span>
            Tutup Shift
          </button>
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
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import SidebarKasir from '../../components/SidebarKasir.vue'
import api from '../../services/axios'

const router = useRouter()

// ─── State ───────────────────────────────────────────────────
const loadingShift    = ref(true)
const loadingHistory  = ref(false)
const openingShiftLoading = ref(false)
const closingShiftLoading = ref(false)

const activeShift      = ref(null)
const cashierName      = ref('Kasir')
const cashierBranch    = ref('Cabang')
const openingCashInput = ref(0)
const closingCashInput = ref(0)
const historyFilter    = ref('week')
const currentPage      = ref(1)

const showOpenModal  = ref(false)
const showCloseModal = ref(false)
const showAlert      = ref(false)
const alertMessage   = ref('')
const alertType      = ref('success')

const shiftHistory = ref([])
const historyMeta  = ref(null)

// ─── Computed ────────────────────────────────────────────────
const cashierInitial = computed(() => cashierName.value.charAt(0).toUpperCase())

const expectedCash = computed(() =>
  (activeShift.value?.opening_cash ?? 0) + (activeShift.value?.total_sales ?? 0)
)

const difference = computed(() => closingCashInput.value - expectedCash.value)

const differenceClass = computed(() => {
  if (difference.value === 0) return 'match'
  return difference.value < 0 ? 'short' : 'over'
})

const filteredShiftHistory = computed(() => {
  if (historyFilter.value === 'all') return shiftHistory.value

  const now = new Date()
  return shiftHistory.value.filter(s => {
    const d = new Date(s.opened_at)
    if (historyFilter.value === 'week') {
      const weekAgo = new Date(now)
      weekAgo.setDate(now.getDate() - 7)
      return d >= weekAgo
    }
    if (historyFilter.value === 'month') {
      return d.getMonth() === now.getMonth() && d.getFullYear() === now.getFullYear()
    }
    return true
  })
})

// ─── Helpers ─────────────────────────────────────────────────
const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num ?? 0)

const formatDateTime = (dt) => {
  if (!dt) return '-'
  return new Date(dt).toLocaleString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric',
    hour: '2-digit', minute: '2-digit'
  })
}

const formatDate = (dt) => {
  if (!dt) return '-'
  return new Date(dt).toLocaleDateString('id-ID', {
    day: '2-digit', month: 'short', year: 'numeric'
  })
}

const formatTime = (dt) => {
  if (!dt) return '-'
  return new Date(dt).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
}

const getDifferenceClass = (diff) => {
  if (diff === 0) return 'difference-match'
  return diff < 0 ? 'difference-short' : 'difference-over'
}

const getStatusClass = (shift) => {
  if (shift.status === 'aktif') return 'status-active'
  if ((shift.difference ?? 0) === 0) return 'status-match'
  return (shift.difference ?? 0) < 0 ? 'status-short' : 'status-over'
}

const getStatusText = (shift) => {
  if (shift.status === 'aktif') return 'Aktif'
  if ((shift.difference ?? 0) === 0) return 'Match'
  return (shift.difference ?? 0) < 0 ? 'Kurang' : 'Lebih'
}

const showAlertMessage = (message, type = 'success') => {
  alertMessage.value = message
  alertType.value = type
  showAlert.value = true
  setTimeout(() => { showAlert.value = false }, 3500)
}

// ─── Load user info ───────────────────────────────────────────
const loadUserInfo = () => {
  try {
    const user = JSON.parse(localStorage.getItem('user') || '{}')
    cashierName.value   = user.name   || 'Kasir'
    cashierBranch.value = user.branch?.name || 'Cabang'
  } catch {
    cashierName.value = 'Kasir'
  }
}

// ─── Fetch active shift dari API ──────────────────────────────
const fetchActiveShift = async () => {
  loadingShift.value = true
  try {
    const res = await api.get('/cashier/shift/active')
    activeShift.value = res.data.shift ?? null

    // Sync localStorage agar SidebarKasir bisa membaca
    if (activeShift.value) {
      localStorage.setItem('shift_active', 'true')
      localStorage.setItem('shift_id', String(activeShift.value.id))
      localStorage.setItem('opening_cash', String(activeShift.value.opening_cash))
      localStorage.setItem('shift_open_time', activeShift.value.opened_at)
    } else {
      clearShiftStorage()
    }
  } catch (err) {
    showAlertMessage('Gagal mengecek shift: ' + (err.response?.data?.message ?? err.message), 'error')
  } finally {
    loadingShift.value = false
  }
}

// ─── Fetch riwayat shift dari API ─────────────────────────────
const fetchShiftHistory = async (page = 1) => {
  loadingHistory.value = true
  try {
    const res = await api.get('/cashier/shift/history', { params: { page } })
    const data = res.data.shifts
    shiftHistory.value = data.data ?? data
    historyMeta.value  = data.meta ?? {
      current_page: data.current_page,
      last_page:    data.last_page,
    }
    currentPage.value = historyMeta.value?.current_page ?? 1
  } catch (err) {
    showAlertMessage('Gagal memuat riwayat shift: ' + (err.response?.data?.message ?? err.message), 'error')
  } finally {
    loadingHistory.value = false
  }
}

const changePage = (page) => {
  if (page < 1) return
  fetchShiftHistory(page)
}

// ─── Buka Shift ───────────────────────────────────────────────
const openOpenShiftModal = () => {
  openingCashInput.value = 0
  showOpenModal.value = true
}

const openShift = async () => {
  if (openingCashInput.value < 0) return
  openingShiftLoading.value = true
  try {
    const res = await api.post('/cashier/shift/open', {
      opening_cash: openingCashInput.value
    })
    activeShift.value = res.data.shift

    localStorage.setItem('shift_active', 'true')
    localStorage.setItem('shift_id', String(activeShift.value.id))
    localStorage.setItem('opening_cash', String(activeShift.value.opening_cash))
    localStorage.setItem('shift_open_time', activeShift.value.opened_at)

    showOpenModal.value = false
    openingCashInput.value = 0
    showAlertMessage('Shift berhasil dibuka!')
    fetchShiftHistory()
  } catch (err) {
    showAlertMessage(err.response?.data?.message ?? 'Gagal membuka shift', 'error')
  } finally {
    openingShiftLoading.value = false
  }
}

// ─── Tutup Shift ──────────────────────────────────────────────
const openCloseShiftModal = () => {
  closingCashInput.value = 0
  showCloseModal.value = true
}

const closeShift = async () => {
  closingShiftLoading.value = true
  try {
    await api.post('/cashier/shift/close', {
      closing_cash: closingCashInput.value
    })

    if (Math.abs(difference.value) > 5000) {
      showAlertMessage(
        `⚠️ Shift ditutup dengan selisih ${difference.value < 0 ? 'kurang' : 'lebih'} Rp ${formatNumber(Math.abs(difference.value))}`,
        'error'
      )
    } else {
      showAlertMessage('Shift berhasil ditutup!')
    }

    activeShift.value = null
    clearShiftStorage()
    showCloseModal.value = false
    closingCashInput.value = 0
    fetchShiftHistory()
  } catch (err) {
    showAlertMessage(err.response?.data?.message ?? 'Gagal menutup shift', 'error')
  } finally {
    closingShiftLoading.value = false
  }
}

const clearShiftStorage = () => {
  localStorage.removeItem('shift_active')
  localStorage.removeItem('shift_id')
  localStorage.removeItem('opening_cash')
  localStorage.removeItem('shift_open_time')
}

// ─── Lifecycle ────────────────────────────────────────────────
onMounted(async () => {
  loadUserInfo()
  await fetchActiveShift()
  fetchShiftHistory()
})
</script>

<style scoped>
.shift-container {
  display: flex;
  min-height: 100vh;
  background: #f3f4f6;
}

.main-content {
  flex: 1;
  margin-left: 260px;
  padding: 1.5rem;
}

/* Top Bar */
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
.page-title h1 { font-size: 1.25rem; color: #1f3864; margin-bottom: 0.25rem; }
.page-title p  { font-size: 0.8rem; color: #6b7280; }
.user-avatar {
  width: 40px; height: 40px;
  background: #2e75b6;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  color: white; font-weight: 600;
}

/* Loading */
.loading-card {
  display: flex; align-items: center; gap: 1rem;
  background: white; padding: 2rem; border-radius: 12px; color: #6b7280;
  margin-bottom: 1.5rem;
}
.loading-row {
  display: flex; align-items: center; gap: .75rem;
  padding: 1.5rem; color: #6b7280; font-size: .875rem;
}

/* Current Shift Card */
.current-shift-card {
  background: linear-gradient(135deg, #1f3864 0%, #2e75b6 100%);
  border-radius: 16px; padding: 1.5rem;
  margin-bottom: 1.5rem; color: white;
}
.shift-header {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 1rem;
}
.shift-status {
  display: flex; align-items: center; gap: .5rem;
  background: rgba(255,255,255,0.2);
  padding: .25rem .75rem; border-radius: 20px; font-size: .8rem;
}
.shift-status .status-dot {
  width: 8px; height: 8px;
  background: #4ade80; border-radius: 50%;
  animation: pulse 2s infinite;
}
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
.shift-time { font-size: .8rem; opacity: .8; }
.shift-stats {
  display: grid; grid-template-columns: repeat(3,1fr);
  gap: 1rem; margin-bottom: 1.5rem;
  padding: 1rem;
  background: rgba(255,255,255,0.1); border-radius: 12px;
}
.stat-item { text-align: center; }
.stat-label { display: block; font-size: .7rem; opacity: .8; margin-bottom: .25rem; }
.stat-value { font-size: 1.1rem; font-weight: 600; }
.btn-close-shift {
  width: 100%; padding: .75rem;
  background: #ef4444; color: white;
  border: none; border-radius: 8px; font-weight: 600; cursor: pointer;
  transition: background .2s;
}
.btn-close-shift:hover { background: #dc2626; }

/* No Shift */
.no-shift-card {
  background: white; border-radius: 16px; padding: 2rem;
  text-align: center; margin-bottom: 1.5rem;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.no-shift-icon svg { width: 64px; height: 64px; color: #9ca3af; margin-bottom: 1rem; }
.no-shift-card h3  { font-size: 1.1rem; margin-bottom: .5rem; }
.no-shift-card p   { font-size: .8rem; color: #6b7280; margin-bottom: 1rem; }
.btn-open-shift {
  padding: .75rem 1.5rem;
  background: #10b981; color: white;
  border: none; border-radius: 8px; font-weight: 600; cursor: pointer;
}

/* Card */
.card {
  background: white; border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;
}
.card-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb;
}
.card-header h3 { font-size: 1rem; font-weight: 600; }
.filter-select {
  padding: .375rem .75rem; border: 1px solid #d1d5db;
  border-radius: 8px; font-size: .8rem; background: white;
}

/* Table */
.table-responsive { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th, .data-table td { padding: .75rem 1rem; text-align: left; border-bottom: 1px solid #e5e7eb; }
.data-table th { background: #f9fafb; font-size: .7rem; font-weight: 600; color: #6b7280; text-transform: uppercase; }
.data-table td { font-size: .8rem; }
.empty-row { text-align: center; color: #9ca3af; padding: 2rem; }

.difference-match { color: #10b981; font-weight: 500; }
.difference-short  { color: #ef4444; font-weight: 500; }
.difference-over   { color: #f59e0b; font-weight: 500; }

.status-badge { padding: .25rem .5rem; border-radius: 20px; font-size: .7rem; }
.status-active { background: #dbeafe; color: #1e40af; }
.status-match  { background: #d1fae5; color: #065f46; }
.status-short  { background: #fee2e2; color: #991b1b; }
.status-over   { background: #fed7aa; color: #9a3412; }

/* Pagination */
.pagination {
  display: flex; justify-content: center; align-items: center;
  gap: 1rem; padding: 1rem; border-top: 1px solid #e5e7eb;
}
.page-btn {
  padding: .5rem 1rem; background: #f3f4f6;
  border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; font-size: .8rem;
}
.page-btn:disabled { opacity: .5; cursor: not-allowed; }
.page-info { font-size: .8rem; color: #6b7280; }

/* Modal */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex; align-items: center; justify-content: center;
  z-index: 1000;
}
.modal { background: white; border-radius: 12px; width: 450px; max-width: 90%; overflow: hidden; }
.large-modal { width: 500px; }
.modal-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 1rem; border-bottom: 1px solid #e5e7eb;
}
.modal-body   { padding: 1.5rem; }
.modal-footer {
  display: flex; justify-content: flex-end; gap: .5rem;
  padding: 1rem; border-top: 1px solid #e5e7eb;
}
.close-btn { background: none; border: none; font-size: 1.25rem; cursor: pointer; }

.form-group { margin-bottom: 1rem; }
.form-group label { display: block; font-size: .8rem; font-weight: 500; margin-bottom: .5rem; }
.input-wrapper { position: relative; }
.currency { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #6b7280; font-size: .8rem; }
.modal-input {
  width: 100%; padding: .625rem .75rem .625rem 2rem;
  border: 1px solid #e5e7eb; border-radius: 8px; font-size: .875rem;
}
.modal-input.inline { width: 200px; margin-left: 8px; }
.modal-input:focus  { outline: none; border-color: #2e75b6; }
.form-group small   { display: block; font-size: .7rem; color: #6b7280; margin-top: .25rem; }

.btn-primary   { padding: .5rem 1rem; background: #1f3864; color: white; border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: .5rem; }
.btn-secondary { padding: .5rem 1rem; background: #e5e7eb; color: #374151; border: none; border-radius: 6px; cursor: pointer; }
.btn-primary:disabled { opacity: .5; cursor: not-allowed; }

/* Shift Summary */
.shift-summary  { margin-bottom: 1rem; }
.summary-row {
  display: flex; justify-content: space-between;
  padding: .5rem 0; border-bottom: 1px solid #f3f4f6;
}
.summary-row.total {
  font-weight: bold; font-size: 1rem;
  border-bottom: 2px solid #e5e7eb;
  padding-top: .75rem; margin-top: .25rem;
}
.closing-input { display: flex; align-items: center; }

.difference-result { margin-top: 1rem; }
.difference-card {
  display: flex; align-items: center; gap: 1rem;
  padding: 1rem; border-radius: 12px;
}
.difference-card.match { background: #d1fae5; }
.difference-card.short { background: #fee2e2; }
.difference-card.over  { background: #fed7aa; }
.difference-icon svg { width: 32px; height: 32px; }
.difference-card.match .difference-icon svg { color: #10b981; }
.difference-card.short .difference-icon svg { color: #ef4444; }
.difference-card.over  .difference-icon svg { color: #f59e0b; }
.difference-text h4 { font-size: .9rem; margin-bottom: .25rem; }
.warning-text { font-size: .7rem; color: #d97706; }

/* Spinner */
.spinner {
  width: 20px; height: 20px;
  border: 2px solid #e5e7eb; border-top-color: #2e75b6;
  border-radius: 50%; animation: spin .7s linear infinite; display: inline-block;
}
.spinner-sm {
  width: 14px; height: 14px;
  border: 2px solid rgba(255,255,255,0.4); border-top-color: white;
  border-radius: 50%; animation: spin .7s linear infinite; display: inline-block;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Alert Toast */
.alert-toast {
  position: fixed; bottom: 20px; right: 20px;
  padding: 1rem 1.5rem; border-radius: 8px;
  display: flex; align-items: center; gap: 1rem;
  z-index: 1100; animation: slideIn .3s ease;
}
.alert-toast.success { background: #10b981; color: white; }
.alert-toast.error   { background: #ef4444; color: white; }
.alert-toast button  { background: none; border: none; color: white; cursor: pointer; font-size: 1rem; }

@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

@media (max-width: 768px) {
  .main-content { margin-left: 70px; padding: 1rem; }
  .shift-stats  { grid-template-columns: 1fr; gap: .5rem; }
  .stat-item    { text-align: left; display: flex; justify-content: space-between; }
}
</style>