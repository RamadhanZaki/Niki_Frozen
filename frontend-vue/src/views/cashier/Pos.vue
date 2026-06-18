<template>
  <div class="pos-container">
    <SidebarKasir />

    <main class="pos-main">
      <!-- Top Bar -->
      <div class="top-bar">
        <div class="page-title">
          <h1>Point of Sales</h1>
          <p>
            Kasir: {{ cashierName }} | {{ cashierBranch }}
            <span class="online-badge" :class="isOnline ? 'online' : 'offline'">
              {{ isOnline ? '● Online' : '● Offline' }}
            </span>
          </p>
        </div>
        <div class="top-bar-right">
          <div class="pending-badge" v-if="pendingCount > 0" @click="showPendingModal = true">
            <span class="pending-icon">📤</span>
            <span class="pending-count">{{ pendingCount }}</span>
          </div>
          <div class="user-avatar">
            <span>{{ cashierInitial }}</span>
          </div>
        </div>
      </div>

      <!-- Loading state -->
      <div v-if="loadingShift" class="loading-card">
        <span class="spinner"></span>
        <p>Mengecek status shift...</p>
      </div>

      <!-- Shift Warning -->
      <div v-else-if="!activeShift" class="shift-warning">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div>
          <h3>Shift Belum Dibuka</h3>
          <p>Silakan buka shift terlebih dahulu untuk memulai transaksi</p>
        </div>
        <button @click="openShiftModal = true" class="btn-primary">Buka Shift</button>
      </div>

      <!-- POS Content -->
      <div v-else class="pos-content">
        <!-- Shift info bar -->
        <div class="shift-info-bar">
          <span class="shift-status-dot"></span>
          <span class="shift-info-text">
            Shift aktif sejak {{ formatDateTime(activeShift.opened_at) }}
          </span>
          <span class="shift-sales">
            Penjualan: Rp {{ formatNumber(activeShift.total_sales) }}
            ({{ activeShift.total_transactions }} transaksi)
          </span>
          <button class="btn-close-shift-small" @click="closeShiftModal = true">Tutup Shift</button>
        </div>

        <!-- Product + Cart row -->
        <div class="pos-row">
        <!-- Left: Product Grid -->
        <div class="product-section">
          <div class="search-bar">
            <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input v-model="searchQuery" type="text" placeholder="Cari produk..." class="search-input" />
          </div>

          <div v-if="loadingProducts" class="loading-products">
            <span class="spinner"></span>
            <p>Memuat produk...</p>
          </div>

          <div v-else class="product-grid">
            <div
              v-for="product in filteredProducts"
              :key="product.id"
              class="product-card"
              :class="{ 'low-stock': product.stock <= 5, 'out-of-stock': product.stock <= 0 }"
              @click="addToCart(product)"
            >
              <div class="product-info">
                <h4>{{ product.name }}</h4>
                <p class="product-price">Rp {{ formatNumber(product.price) }}</p>
                <p class="product-stock" :class="{ low: product.stock <= 5 }">
                  Stok: {{ product.stock }}
                </p>
              </div>
              <div v-if="product.stock <= 0" class="out-of-stock-overlay">Habis</div>
            </div>

            <div v-if="filteredProducts.length === 0" class="empty-products">
              Produk tidak ditemukan
            </div>
          </div>
        </div>

        <!-- Right: Cart Section -->
        <div class="cart-section">
          <div class="cart-header">
            <h3>Keranjang Belanja</h3>
            <button v-if="cart.length > 0" class="clear-cart" @click="clearCart">Kosongkan</button>
          </div>

          <div class="cart-items">
            <div v-for="(item, index) in cart" :key="index" class="cart-item">
              <div class="item-info">
                <h4>{{ item.name }}</h4>
                <p>Rp {{ formatNumber(item.price) }}</p>
              </div>
              <div class="item-actions">
                <button class="qty-btn" @click="updateQuantity(item, -1)">-</button>
                <span class="qty">{{ item.quantity }}</span>
                <button class="qty-btn" @click="updateQuantity(item, 1)">+</button>
                <button class="remove-btn" @click="removeFromCart(index)">✕</button>
              </div>
              <div class="item-subtotal">Rp {{ formatNumber(item.price * item.quantity) }}</div>
            </div>

            <div v-if="cart.length === 0" class="empty-cart">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 6M18 13l1.5 6M9 21h6M12 13v8" />
              </svg>
              <p>Keranjang kosong</p>
            </div>
          </div>

          <div class="cart-total">
            <div class="total-row">
              <span>Total</span>
              <span class="total-amount">Rp {{ formatNumber(totalAmount) }}</span>
            </div>
            <div class="total-row">
              <span>Pembayaran</span>
              <input type="number" v-model.number="paymentAmount" class="payment-input" placeholder="0" />
            </div>
            <div class="total-row change">
              <span>Kembalian</span>
              <span class="change-amount">Rp {{ formatNumber(changeAmount) }}</span>
            </div>
          </div>

          <button
            class="btn-checkout"
            :disabled="cart.length === 0 || paymentAmount < totalAmount || processingTransaction"
            @click="processTransaction"
          >
            <span v-if="processingTransaction" class="spinner-sm"></span>
            <span>{{ processingTransaction ? 'Memproses...' : 'Proses Transaksi' }}</span>
          </button>
        </div>
        <!-- end cart-section -->
      </div><!-- end pos-row -->
      </div><!-- end pos-content -->
    </main>

    <!-- Modal: Buka Shift -->
    <div v-if="openShiftModal" class="modal-overlay" @click.self="openShiftModal = false">
      <div class="modal">
        <div class="modal-header">
          <h3>Buka Shift</h3>
          <button class="close-btn" @click="openShiftModal = false">✕</button>
        </div>
        <div class="modal-body">
          <label>Saldo Kas Awal</label>
          <div class="input-rp">
            <span>Rp</span>
            <input type="number" v-model.number="openingCashInput" class="modal-input" placeholder="0" min="0" />
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="openShiftModal = false">Batal</button>
          <button class="btn-primary" @click="openShift" :disabled="!openingCashInput || openingCashInput < 0 || openingShiftLoading">
            <span v-if="openingShiftLoading" class="spinner-sm"></span>
            Buka Shift
          </button>
        </div>
      </div>
    </div>

    <!-- Modal: Tutup Shift -->
    <div v-if="closeShiftModal" class="modal-overlay" @click.self="closeShiftModal = false">
      <div class="modal">
        <div class="modal-header">
          <h3>Tutup Shift</h3>
          <button class="close-btn" @click="closeShiftModal = false">✕</button>
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
              <span>Kas Diharapkan</span>
              <span>Rp {{ formatNumber((activeShift?.opening_cash ?? 0) + (activeShift?.total_sales ?? 0)) }}</span>
            </div>
          </div>
          <label>Kas Fisik (hitung uang di kasir)</label>
          <div class="input-rp">
            <span>Rp</span>
            <input type="number" v-model.number="closingCashInput" class="modal-input" placeholder="0" min="0" />
          </div>
          <div v-if="closingCashInput > 0" class="difference-preview" :class="closeShiftDiffClass">
            <span v-if="closeShiftDiff === 0">✓ Kas sesuai</span>
            <span v-else-if="closeShiftDiff < 0">⚠ Kurang Rp {{ formatNumber(Math.abs(closeShiftDiff)) }}</span>
            <span v-else>⚠ Lebih Rp {{ formatNumber(closeShiftDiff) }}</span>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="closeShiftModal = false">Batal</button>
          <button class="btn-danger" @click="closeShift" :disabled="!closingCashInput || closingShiftLoading">
            <span v-if="closingShiftLoading" class="spinner-sm"></span>
            Tutup Shift
          </button>
        </div>
      </div>
    </div>

    <!-- Modal: Struk -->
    <div v-if="showReceipt" class="modal-overlay" @click.self="showReceipt = false">
      <div class="modal receipt-modal">
        <div class="modal-header">
          <h3>Struk Transaksi</h3>
          <button class="close-btn" @click="showReceipt = false">✕</button>
        </div>
        <div class="modal-body receipt-content">
          <div class="receipt-header">
            <h2>Nicky Frozen</h2>
            <p>Yogyakarta</p>
            <p>{{ lastTransaction.invoice_number }}</p>
            <hr />
          </div>
          <div class="receipt-items">
            <div v-for="item in lastTransaction.items" :key="item.product_id" class="receipt-item">
              <span>{{ item.name }} x{{ item.qty }}</span>
              <span>Rp {{ formatNumber(item.subtotal) }}</span>
            </div>
            <hr />
            <div class="receipt-row"><strong>Total</strong><strong>Rp {{ formatNumber(lastTransaction.total) }}</strong></div>
            <div class="receipt-row"><span>Bayar</span><span>Rp {{ formatNumber(lastTransaction.payment) }}</span></div>
            <div class="receipt-row"><span>Kembalian</span><span>Rp {{ formatNumber(lastTransaction.change_amount) }}</span></div>
            <hr />
            <div class="receipt-footer">
              <p>Terima kasih!</p>
              <p>{{ formatDateTime(lastTransaction.created_at) }}</p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-primary" @click="printReceipt">Cetak Struk</button>
          <button class="btn-secondary" @click="showReceipt = false">Tutup</button>
        </div>
      </div>
    </div>

    <!-- Modal: Pending Offline -->
    <div v-if="showPendingModal" class="modal-overlay" @click.self="showPendingModal = false">
      <div class="modal">
        <div class="modal-header">
          <h3>Transaksi Pending (Offline)</h3>
          <button class="close-btn" @click="showPendingModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div v-for="(item, idx) in pendingTransactions" :key="idx" class="pending-item">
            <div>
              <strong>{{ item.items.length }} produk</strong>
              <p>Total: Rp {{ formatNumber(item.total) }}</p>
              <small>{{ formatDateTime(item.created_at) }}</small>
            </div>
            <div class="pending-status" :class="item.status">
              {{ item.status === 'pending' ? 'Menunggu Sync' : 'Gagal' }}
            </div>
          </div>
          <div v-if="pendingTransactions.length === 0" class="empty-state">
            Tidak ada transaksi pending
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-primary" @click="syncPendingTransactions" :disabled="!isOnline || syncingLoading">
            <span v-if="syncingLoading" class="spinner-sm"></span>
            Sync Sekarang
          </button>
          <button class="btn-secondary" @click="showPendingModal = false">Tutup</button>
        </div>
      </div>
    </div>

    <!-- Toast -->
    <div v-if="toast.show" class="toast" :class="toast.type">
      {{ toast.message }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import SidebarKasir from '../../components/SidebarKasir.vue'
import api from '../../services/axios'

const router = useRouter()

// ─── State ──────────────────────────────────────────────────
const isOnline        = ref(navigator.onLine)
const activeShift     = ref(null)
const loadingShift    = ref(true)
const loadingProducts = ref(false)
const processingTransaction = ref(false)
const openingShiftLoading   = ref(false)
const closingShiftLoading   = ref(false)
const syncingLoading        = ref(false)

const cashierName   = ref('')
const cashierBranch = ref('')
const searchQuery   = ref('')
const paymentAmount = ref(0)
const openShiftModal  = ref(false)
const closeShiftModal = ref(false)
const showReceipt     = ref(false)
const showPendingModal = ref(false)

const openingCashInput = ref(0)
const closingCashInput = ref(0)

const products = ref([])
const cart     = ref([])
const lastTransaction  = ref({})
const pendingTransactions = ref([])
const pendingCount        = ref(0)

const toast = ref({ show: false, message: '', type: 'success' })

// ─── Computed ────────────────────────────────────────────────
const cashierInitial = computed(() => cashierName.value.charAt(0).toUpperCase())

const filteredProducts = computed(() => {
  if (!searchQuery.value) return products.value
  return products.value.filter(p =>
    p.name.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

const totalAmount = computed(() =>
  cart.value.reduce((sum, item) => sum + item.price * item.quantity, 0)
)

const changeAmount = computed(() => Math.max(0, paymentAmount.value - totalAmount.value))

const closeShiftDiff = computed(() => {
  if (!activeShift.value) return 0
  const expected = (activeShift.value.opening_cash ?? 0) + (activeShift.value.total_sales ?? 0)
  return closingCashInput.value - expected
})

const closeShiftDiffClass = computed(() => {
  if (closeShiftDiff.value === 0) return 'diff-match'
  if (closeShiftDiff.value < 0) return 'diff-short'
  return 'diff-over'
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

const showToast = (message, type = 'success', duration = 3000) => {
  toast.value = { show: true, message, type }
  setTimeout(() => { toast.value.show = false }, duration)
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

// ─── Shift ────────────────────────────────────────────────────
const fetchActiveShift = async () => {
  loadingShift.value = true
  try {
    const res = await api.get('/cashier/shift/active')
    activeShift.value = res.data.shift ?? null
    // Sync ke localStorage agar SidebarKasir bisa membaca
    if (activeShift.value) {
      localStorage.setItem('shift_active', 'true')
      localStorage.setItem('shift_id', String(activeShift.value.id))
      localStorage.setItem('opening_cash', String(activeShift.value.opening_cash))
      localStorage.setItem('shift_open_time', activeShift.value.opened_at)
    } else {
      clearShiftStorage()
    }
  } catch (err) {
    showToast('Gagal mengecek shift: ' + (err.response?.data?.message ?? err.message), 'error')
  } finally {
    loadingShift.value = false
  }
}

const openShift = async () => {
  if (!openingCashInput.value && openingCashInput.value !== 0) return
  openingShiftLoading.value = true
  try {
    const res = await api.post('/cashier/shift/open', { opening_cash: openingCashInput.value })
    activeShift.value = res.data.shift
    localStorage.setItem('shift_active', 'true')
    localStorage.setItem('shift_id', String(activeShift.value.id))
    localStorage.setItem('opening_cash', String(activeShift.value.opening_cash))
    localStorage.setItem('shift_open_time', activeShift.value.opened_at)
    openShiftModal.value = false
    openingCashInput.value = 0
    showToast('Shift berhasil dibuka!')
    await fetchProducts()
  } catch (err) {
    showToast(err.response?.data?.message ?? 'Gagal membuka shift', 'error')
  } finally {
    openingShiftLoading.value = false
  }
}

const closeShift = async () => {
  if (!closingCashInput.value && closingCashInput.value !== 0) return
  closingShiftLoading.value = true
  try {
    await api.post('/cashier/shift/close', { closing_cash: closingCashInput.value })
    activeShift.value = null
    clearShiftStorage()
    closeShiftModal.value = false
    closingCashInput.value = 0
    showToast('Shift berhasil ditutup!')
  } catch (err) {
    showToast(err.response?.data?.message ?? 'Gagal menutup shift', 'error')
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

// ─── Products ─────────────────────────────────────────────────
const fetchProducts = async () => {
  loadingProducts.value = true
  try {
    // Endpoint khusus kasir — sudah include stok, hanya cabang kasir sendiri
    const res = await api.get('/cashier/products')
    products.value = res.data.products.map(p => ({
      id:    p.id,
      name:  p.name,
      price: parseFloat(p.price),
      stock: p.stock ?? 0,
    }))
  } catch (err) {
    showToast('Gagal memuat produk: ' + (err.response?.data?.message ?? err.message), 'error')
  } finally {
    loadingProducts.value = false
  }
}

// ─── Cart ─────────────────────────────────────────────────────
const addToCart = (product) => {
  if (product.stock <= 0) return
  const existing = cart.value.find(i => i.id === product.id)
  if (existing) {
    if (existing.quantity >= product.stock) {
      showToast('Stok tidak mencukupi', 'error')
      return
    }
    existing.quantity++
  } else {
    cart.value.push({ id: product.id, name: product.name, price: product.price, quantity: 1 })
  }
}

const updateQuantity = (item, delta) => {
  const newQty = item.quantity + delta
  if (newQty <= 0) {
    const idx = cart.value.findIndex(i => i.id === item.id)
    cart.value.splice(idx, 1)
  } else {
    const product = products.value.find(p => p.id === item.id)
    if (product && newQty > product.stock) {
      showToast('Stok tidak mencukupi', 'error')
      return
    }
    item.quantity = newQty
  }
}

const removeFromCart = (index) => cart.value.splice(index, 1)

const clearCart = () => {
  cart.value = []
  paymentAmount.value = 0
}

// ─── Transaction ──────────────────────────────────────────────
const processTransaction = async () => {
  if (cart.value.length === 0 || paymentAmount.value < totalAmount.value) return
  processingTransaction.value = true

  const payload = {
    shift_id: activeShift.value.id,
    payment:  paymentAmount.value,
    items: cart.value.map(i => ({
      product_id: i.id,
      qty:        i.quantity,
      price:      i.price,
    }))
  }

  if (isOnline.value) {
    await saveOnline(payload)
  } else {
    saveOffline(payload)
  }

  processingTransaction.value = false
}

const saveOnline = async (payload) => {
  try {
    const res = await api.post('/cashier/transactions', payload)
    const trx = res.data.transaction

    lastTransaction.value = {
      invoice_number: trx.invoice_number,
      total:         trx.total,
      payment:       trx.payment,
      change_amount: trx.change_amount,
      created_at:    trx.created_at,
      items: trx.items ?? payload.items.map(i => {
        const p = products.value.find(pr => pr.id === i.product_id)
        return { product_id: i.product_id, name: p?.name ?? '', qty: i.qty, subtotal: i.qty * i.price }
      })
    }

    // Update stok lokal & shift stats
    updateLocalStockAndShift(payload.items, trx.total)

    clearCart()
    showReceipt.value = true
    showToast('Transaksi berhasil!')
  } catch (err) {
    // Kalau API gagal, simpan offline
    if (!err.response) {
      showToast('Server tidak terjangkau, disimpan offline', 'warn')
      saveOffline(payload)
    } else {
      showToast(err.response.data?.message ?? 'Transaksi gagal', 'error')
    }
  }
}

const saveOffline = (payload) => {
  const pending = getPendingFromStorage()
  const offlineTrx = {
    local_id:   `offline_${Date.now()}`,
    shift_id:   payload.shift_id,
    items:      payload.items,
    total:      totalAmount.value,
    payment:    payload.payment,
    created_at: new Date().toISOString(),
    status:     'pending'
  }
  pending.push(offlineTrx)
  localStorage.setItem('pending_transactions', JSON.stringify(pending))
  pendingTransactions.value = pending
  pendingCount.value        = pending.length

  lastTransaction.value = {
    invoice_number: offlineTrx.local_id,
    total:          offlineTrx.total,
    payment:        offlineTrx.payment,
    change_amount:  offlineTrx.payment - offlineTrx.total,
    created_at:     offlineTrx.created_at,
    items: payload.items.map(i => {
      const p = products.value.find(pr => pr.id === i.product_id)
      return { product_id: i.product_id, name: p?.name ?? '', qty: i.qty, subtotal: i.qty * i.price }
    })
  }

  updateLocalStockAndShift(payload.items, offlineTrx.total)
  clearCart()
  showReceipt.value = true
  showToast('Disimpan offline, akan disync saat online', 'warn')
}

const updateLocalStockAndShift = (items, total) => {
  // Kurangi stok tampilan lokal
  items.forEach(i => {
    const product = products.value.find(p => p.id === i.product_id)
    if (product) product.stock -= i.qty
  })
  // Update stats shift lokal (tampilan saja, server akan update otomatis)
  if (activeShift.value) {
    activeShift.value.total_sales        = (parseFloat(activeShift.value.total_sales) ?? 0) + total
    activeShift.value.total_transactions = (activeShift.value.total_transactions ?? 0) + 1
  }
}

// ─── Sync Offline ─────────────────────────────────────────────
const syncPendingTransactions = async () => {
  if (!isOnline.value) return
  syncingLoading.value = true
  const pending = getPendingFromStorage()
  if (pending.length === 0) { syncingLoading.value = false; return }

  try {
    const res = await api.post('/cashier/transactions/sync', { transactions: pending })
    const results = res.data.results ?? []

    const stillFailed = pending.filter((p, idx) => results[idx]?.status !== 'ok')
    localStorage.setItem('pending_transactions', JSON.stringify(stillFailed))
    pendingTransactions.value = stillFailed
    pendingCount.value        = stillFailed.length

    const okCount = results.filter(r => r.status === 'ok').length
    showToast(`${okCount} transaksi berhasil disync!`)
    showPendingModal.value = false

    // Refresh shift stats dari server
    await fetchActiveShift()
  } catch (err) {
    showToast('Sync gagal: ' + (err.response?.data?.message ?? err.message), 'error')
  } finally {
    syncingLoading.value = false
  }
}

const getPendingFromStorage = () => {
  try { return JSON.parse(localStorage.getItem('pending_transactions') || '[]') }
  catch { return [] }
}

const loadPendingFromStorage = () => {
  pendingTransactions.value = getPendingFromStorage()
  pendingCount.value        = pendingTransactions.value.length
}

const printReceipt = () => window.print()

// ─── Network listeners ────────────────────────────────────────
const onOnline = async () => {
  isOnline.value = true
  showToast('Kembali online!')
  if (pendingCount.value > 0) await syncPendingTransactions()
}
const onOffline = () => {
  isOnline.value = false
  showToast('Koneksi terputus, mode offline aktif', 'warn')
}

// ─── Lifecycle ────────────────────────────────────────────────
onMounted(async () => {
  loadUserInfo()
  loadPendingFromStorage()
  window.addEventListener('online',  onOnline)
  window.addEventListener('offline', onOffline)

  await fetchActiveShift()
  if (activeShift.value) await fetchProducts()
})

onUnmounted(() => {
  window.removeEventListener('online',  onOnline)
  window.removeEventListener('offline', onOffline)
})
</script>

<style scoped>
.pos-container {
  display: flex;
  min-height: 100vh;
  background: #f3f4f6;
}

.pos-main {
  flex: 1;
  margin-left: 260px;
  padding: 1rem;
}

/* Top Bar */
.top-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  background: white;
  padding: 1rem 1.5rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.page-title h1 { font-size: 1.25rem; color: #1f3864; margin-bottom: 0.25rem; }
.page-title p  { font-size: 0.8rem; color: #6b7280; display: flex; align-items: center; gap: 0.5rem; }

.online-badge { font-size: 0.7rem; font-weight: 600; padding: 2px 8px; border-radius: 20px; }
.online-badge.online  { background: #d1fae5; color: #065f46; }
.online-badge.offline { background: #fee2e2; color: #991b1b; }

.top-bar-right { display: flex; align-items: center; gap: 1rem; }

.pending-badge {
  cursor: pointer;
  padding: 0.5rem;
  background: #fef3c7;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}
.pending-count { background: #d97706; color: white; border-radius: 10px; padding: 0 6px; font-size: 0.7rem; font-weight: bold; }

.user-avatar {
  width: 40px;
  height: 40px;
  background: #2e75b6;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
}

/* Loading */
.loading-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: white;
  padding: 2rem;
  border-radius: 12px;
  color: #6b7280;
}

/* Shift Warning */
.shift-warning {
  background: #fef3c7;
  border: 1px solid #fde68a;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
}
.shift-warning svg { width: 40px; height: 40px; color: #d97706; flex-shrink: 0; }
.shift-warning h3  { font-size: 1rem; margin-bottom: 0.25rem; }
.shift-warning p   { font-size: 0.8rem; color: #92400e; }

/* Shift info bar */
.shift-info-bar {
  display: flex;
  align-items: center;
  gap: 1rem;
  background: #1f3864;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  font-size: 0.8rem;
  flex-wrap: wrap;
}
.shift-status-dot {
  width: 8px; height: 8px;
  background: #4ade80;
  border-radius: 50%;
  flex-shrink: 0;
  animation: pulse 2s infinite;
}
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.5} }
.shift-info-text { flex: 1; }
.shift-sales { color: #93c5fd; font-size: 0.75rem; }
.btn-close-shift-small {
  padding: 0.25rem 0.75rem;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 6px;
  font-size: 0.75rem;
  cursor: pointer;
  margin-left: auto;
}

/* POS Content */
.pos-content {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  height: calc(100vh - 140px);
}

.pos-row {
  display: flex;
  gap: 1rem;
  flex: 1;
  overflow: hidden;
}

.product-section {
  background: white;
  border-radius: 12px;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  overflow: hidden;
  flex: 2;
}

.cart-section {
  background: white;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
  flex: 1.2;
}

/* Search */
.search-bar { position: relative; }
.search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 18px; height: 18px; color: #9ca3af; }
.search-input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.875rem;
}
.search-input:focus { outline: none; border-color: #2e75b6; }

.loading-products { display: flex; align-items: center; gap: 0.5rem; color: #6b7280; padding: 2rem; justify-content: center; }

/* Product Grid */
.product-grid {
  flex: 1;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 0.75rem;
  overflow-y: auto;
  padding-bottom: 1rem;
}

.product-card {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 10px;
  padding: 0.75rem;
  cursor: pointer;
  transition: all 0.2s;
  position: relative;
}
.product-card:hover { border-color: #2e75b6; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.product-card.low-stock { border-left: 3px solid #f59e0b; }
.product-card.out-of-stock { opacity: 0.5; cursor: not-allowed; }
.out-of-stock-overlay {
  position: absolute; inset: 0;
  background: rgba(0,0,0,0.3);
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
  font-size: 0.8rem;
}

.product-info h4    { font-size: 0.875rem; margin-bottom: 0.25rem; }
.product-price      { font-weight: bold; color: #1f3864; font-size: 0.875rem; }
.product-stock      { font-size: 0.7rem; color: #6b7280; }
.product-stock.low  { color: #f59e0b; }
.empty-products     { text-align: center; padding: 2rem; color: #9ca3af; grid-column: 1/-1; }

/* Cart */
.cart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
}
.cart-header h3 { font-size: 1rem; }
.clear-cart { background: none; border: none; color: #ef4444; font-size: 0.75rem; cursor: pointer; }

.cart-items { flex: 1; overflow-y: auto; padding: 0.5rem; }

.cart-item {
  display: flex;
  align-items: center;
  padding: 0.75rem;
  border-bottom: 1px solid #f3f4f6;
  gap: 0.5rem;
}
.item-info { flex: 1; }
.item-info h4 { font-size: 0.8rem; margin-bottom: 0.25rem; }
.item-info p  { font-size: 0.7rem; color: #6b7280; }
.item-actions { display: flex; align-items: center; gap: 0.5rem; }
.qty-btn { width: 28px; height: 28px; border: 1px solid #e5e7eb; background: white; border-radius: 6px; cursor: pointer; font-size: 1rem; }
.qty { min-width: 30px; text-align: center; }
.remove-btn { background: none; border: none; color: #ef4444; cursor: pointer; font-size: 1rem; padding: 0 4px; }
.item-subtotal { font-weight: 500; font-size: 0.8rem; min-width: 80px; text-align: right; }

.empty-cart { text-align: center; padding: 2rem; color: #9ca3af; }
.empty-cart svg { width: 48px; height: 48px; margin-bottom: 0.5rem; }

.cart-total { padding: 1rem; border-top: 1px solid #e5e7eb; background: #f9fafb; }
.total-row { display: flex; justify-content: space-between; margin-bottom: 0.75rem; }
.total-row span:first-child { color: #6b7280; }
.total-amount { font-weight: bold; font-size: 1.1rem; color: #1f3864; }
.payment-input { width: 150px; padding: 0.375rem 0.5rem; border: 1px solid #e5e7eb; border-radius: 6px; text-align: right; }
.change { border-top: 1px solid #e5e7eb; padding-top: 0.75rem; margin-top: 0.25rem; }
.change-amount { font-weight: bold; color: #10b981; }

.btn-checkout {
  width: 100%;
  padding: 0.875rem;
  background: #1f3864;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}
.btn-checkout:hover:not(:disabled) { background: #15284d; }
.btn-checkout:disabled { opacity: 0.5; cursor: not-allowed; }

/* Modal */
.modal-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.5);
  display: flex; align-items: center; justify-content: center;
  z-index: 1000;
}
.modal { background: white; border-radius: 12px; width: 420px; max-width: 90%; overflow: hidden; }
.receipt-modal { width: 350px; }
.modal-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid #e5e7eb; }
.modal-body { padding: 1rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem; border-top: 1px solid #e5e7eb; }
.close-btn { background: none; border: none; font-size: 1.25rem; cursor: pointer; }

.input-rp { position: relative; display: flex; align-items: center; }
.input-rp span { position: absolute; left: 12px; color: #6b7280; font-size: 0.875rem; }
.modal-input {
  width: 100%;
  padding: 0.625rem 0.75rem 0.625rem 2.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.875rem;
  margin-top: 0.5rem;
}
.modal-input:focus { outline: none; border-color: #2e75b6; }

.shift-summary { margin-bottom: 1rem; }
.summary-row { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #f3f4f6; font-size: 0.875rem; }
.summary-row.total { font-weight: bold; border-bottom: 2px solid #e5e7eb; padding-top: 0.75rem; }

.difference-preview { margin-top: 0.75rem; padding: 0.75rem; border-radius: 8px; font-size: 0.875rem; font-weight: 500; }
.diff-match { background: #d1fae5; color: #065f46; }
.diff-short { background: #fee2e2; color: #991b1b; }
.diff-over  { background: #fed7aa; color: #9a3412; }

/* Receipt */
.receipt-content { text-align: center; }
.receipt-header h2 { font-size: 1.1rem; margin-bottom: 0.25rem; }
.receipt-header p  { font-size: 0.7rem; color: #6b7280; }
.receipt-items { text-align: left; margin: 1rem 0; }
.receipt-item { display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 0.25rem; }
.receipt-row  { display: flex; justify-content: space-between; margin: 0.5rem 0; font-size: 0.8rem; }
.receipt-footer { margin-top: 1rem; font-size: 0.7rem; color: #6b7280; }

/* Pending */
.pending-item { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem; border-bottom: 1px solid #e5e7eb; }
.pending-item p  { font-size: 0.75rem; color: #6b7280; }
.pending-item small { font-size: 0.7rem; color: #9ca3af; }
.pending-status { font-size: 0.7rem; padding: 0.25rem 0.5rem; border-radius: 20px; }
.pending-status.pending { background: #fef3c7; color: #d97706; }
.pending-status.failed  { background: #fee2e2; color: #991b1b; }
.empty-state { text-align: center; padding: 2rem; color: #9ca3af; }

/* Buttons */
.btn-primary   { padding: 0.5rem 1rem; background: #1f3864; color: white; border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; }
.btn-secondary { padding: 0.5rem 1rem; background: #e5e7eb; color: #374151; border: none; border-radius: 6px; cursor: pointer; }
.btn-danger    { padding: 0.5rem 1rem; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; }

/* Spinner */
.spinner {
  width: 20px; height: 20px;
  border: 2px solid #e5e7eb;
  border-top-color: #2e75b6;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  display: inline-block;
}
.spinner-sm {
  width: 14px; height: 14px;
  border: 2px solid rgba(255,255,255,0.4);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  display: inline-block;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* Toast */
.toast {
  position: fixed;
  bottom: 24px; right: 24px;
  padding: 0.875rem 1.25rem;
  border-radius: 10px;
  font-size: 0.875rem;
  font-weight: 500;
  z-index: 2000;
  animation: slideIn 0.3s ease;
  max-width: 320px;
}
.toast.success { background: #10b981; color: white; }
.toast.error   { background: #ef4444; color: white; }
.toast.warn    { background: #f59e0b; color: white; }

@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

@media (max-width: 768px) {
  .pos-main { margin-left: 70px; }
  .pos-row  { flex-direction: column; }
}
</style>