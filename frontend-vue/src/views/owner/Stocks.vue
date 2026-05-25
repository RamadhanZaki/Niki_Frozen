<template>
  <div class="stocks-container">
    <SidebarOwner />

    <main class="main-content">
      <div class="top-bar">
        <div class="page-title"><h1>Manajemen Stok</h1><p>Kelola stok produk dan monitoring mutasi</p></div>
        <div class="top-bar-right">
          <button class="btn-add" @click="openAddStockModal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg><span>Tambah Stok</span></button>
          <div class="user-avatar"><span>{{ userInitial }}</span></div>
        </div>
      </div>

      <div class="stats-grid">
        <div class="stat-card"><div class="stat-info"><p class="stat-label">Total Produk</p><p class="stat-value">{{ totalProducts }}</p></div></div>
        <div class="stat-card warning"><div class="stat-info"><p class="stat-label">Stok Menipis (≤10)</p><p class="stat-value">{{ lowStockCount }}</p></div></div>
        <div class="stat-card danger"><div class="stat-info"><p class="stat-label">Stok Kritis (≤5)</p><p class="stat-value">{{ criticalStockCount }}</p></div></div>
        <div class="stat-card info"><div class="stat-info"><p class="stat-label">Total Nilai Stok</p><p class="stat-value">Rp {{ formatNumber(totalStockValue) }}</p></div></div>
      </div>

      <div class="filters-bar">
        <div class="search-bar"><svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg><input v-model="searchQuery" placeholder="Cari produk..." class="search-input"></div>
        <select v-model="branchFilter" class="filter-select"><option value="all">Semua Cabang</option><option value="1">Cabang Utama</option><option value="2">Cabang Kedua</option></select>
        <select v-model="stockFilter" class="filter-select"><option value="all">Semua Stok</option><option value="normal">Stok Normal</option><option value="low">Stok Menipis (≤10)</option><option value="critical">Stok Kritis (≤5)</option><option value="out">Habis (0)</option></select>
      </div>

      <div class="card">
        <div class="card-header"><h3>Daftar Stok Produk</h3><button class="btn-export" @click="exportData"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><span>Export Excel</span></button></div>
        <div class="table-responsive"><table class="data-table"><thead><tr><th>No</th><th>Nama Produk</th><th>Kategori</th><th>Harga</th><th>Stok Saat Ini</th><th>Status</th><th>Last Update</th><th>Aksi</th></tr></thead>
          <tbody><tr v-for="(product, index) in paginatedProducts" :key="product.id"><td>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td><td class="product-name">{{ product.name }}</td><td>{{ product.category }}</td><td class="price">Rp {{ formatNumber(product.price) }}</td><td><span :class="getStockClass(product.stock)">{{ product.stock }}</span> pcs</td>
          <td><span class="status-badge" :class="getStockStatusClass(product.stock)">{{ getStockStatusText(product.stock) }}</span></td><td class="last-update">{{ product.last_update || '-' }}</td>
          <td class="actions"><button class="action-btn add" @click="openAdjustModal(product, 'add')" title="Tambah Stok"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 4v16m8-8H4"/></svg></button>
          <button class="action-btn reduce" @click="openAdjustModal(product, 'reduce')" title="Kurangi Stok"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20 12H4"/></svg></button>
          <button class="action-btn history" @click="viewHistory(product)" title="Riwayat"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></button></td></tr>
          <tr v-if="filteredProducts.length === 0"><td colspan="8" class="empty-row">Tidak ada produk yang ditemukan</td></tr></tbody></table></div>
        <div class="pagination" v-if="totalPages > 1"><button class="page-btn" @click="prevPage" :disabled="currentPage === 1">&laquo; Sebelumnya</button><span class="page-info">Halaman {{ currentPage }} dari {{ totalPages }}</span><button class="page-btn" @click="nextPage" :disabled="currentPage === totalPages">Selanjutnya &raquo;</button></div>
      </div>

      <div class="card" v-if="selectedProduct"><div class="card-header"><h3>Riwayat Mutasi Stok - {{ selectedProduct.name }}</h3><button class="close-history" @click="selectedProduct = null">✕</button></div>
        <div class="table-responsive"><table class="data-table history-table"><thead><tr><th>Tanggal</th><th>Jenis</th><th>Jumlah</th><th>Stok Sebelum</th><th>Stok Sesudah</th><th>Keterangan</th><th>Petugas</th></tr></thead>
          <tbody><tr v-for="(history, idx) in stockHistory" :key="idx"><td>{{ history.date }}</td><td><span class="history-type" :class="history.type">{{ history.type === 'in' ? 'Masuk' : 'Keluar' }}</span></td><td :class="history.type === 'in' ? 'text-success' : 'text-danger'">{{ history.type === 'in' ? '+' : '-' }} {{ history.quantity }}</td>
          <td>{{ history.before_stock }}</td><td>{{ history.after_stock }}</td><td>{{ history.note }}</td><td>{{ history.user }}</td></tr>
          <tr v-if="stockHistory.length === 0"><td colspan="7" class="empty-row">Belum ada riwayat mutasi</td></tr></tbody></table></div>
      </div>
    </main>

    <!-- Modal Add Stock -->
    <div v-if="showAddModal" class="modal-overlay" @click.self="closeModal"><div class="modal"><div class="modal-header"><h3>Tambah Stok Baru</h3><button class="close-btn" @click="closeModal">✕</button></div>
      <div class="modal-body"><div class="form-group"><label>Pilih Produk</label><select v-model="stockForm.product_id" class="modal-input"><option value="">Pilih Produk</option><option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }} (Stok: {{ getProductStock(p.id) }})</option></select></div>
      <div class="form-group"><label>Cabang</label><select v-model="stockForm.branch_id" class="modal-input"><option value="1">Cabang Utama</option><option value="2">Cabang Kedua</option></select></div>
      <div class="form-row"><div class="form-group"><label>Jumlah Tambahan</label><input v-model.number="stockForm.quantity" type="number" class="modal-input" min="1"></div><div class="form-group"><label>Stok Akan Menjadi</label><input :value="newStockValue" type="text" class="modal-input" disabled></div></div>
      <div class="form-group"><label>Keterangan</label><textarea v-model="stockForm.note" class="modal-input" rows="2" placeholder="Contoh: Restock dari supplier"></textarea></div></div>
      <div class="modal-footer"><button class="btn-secondary" @click="closeModal">Batal</button><button class="btn-primary" @click="addStock" :disabled="!stockForm.product_id || !stockForm.quantity">Tambah Stok</button></div></div></div>

    <!-- Modal Adjust Stock -->
    <div v-if="showAdjustModal" class="modal-overlay" @click.self="showAdjustModal = false"><div class="modal"><div class="modal-header"><h3>{{ adjustType === 'add' ? 'Tambah Stok' : 'Kurangi Stok' }}</h3><button class="close-btn" @click="showAdjustModal = false">✕</button></div>
      <div class="modal-body"><div class="product-info-card"><h4>{{ currentProduct?.name }}</h4><p>Stok Saat Ini: <strong>{{ currentProduct?.stock }} pcs</strong></p><p>Harga: Rp {{ formatNumber(currentProduct?.price) }}</p></div>
      <div class="form-group"><label>Jumlah {{ adjustType === 'add' ? 'Ditambahkan' : 'Dikurangi' }}</label><input v-model.number="adjustQuantity" type="number" class="modal-input" min="1"></div>
      <div class="form-group" v-if="adjustType === 'reduce'"><label>Stok Setelah Pengurangan</label><input :value="currentProduct?.stock - adjustQuantity" type="text" class="modal-input" disabled><p v-if="currentProduct?.stock - adjustQuantity < 0" class="error-text">Stok tidak boleh negatif!</p></div>
      <div class="form-group"><label>Keterangan</label><textarea v-model="adjustNote" class="modal-input" rows="2" :placeholder="adjustType === 'add' ? 'Contoh: Restock' : 'Contoh: Kadaluarsa / Rusak'"></textarea></div></div>
      <div class="modal-footer"><button class="btn-secondary" @click="showAdjustModal = false">Batal</button><button class="btn-primary" @click="saveAdjustment" :disabled="!adjustQuantity || (adjustType === 'reduce' && currentProduct?.stock - adjustQuantity < 0)">Simpan</button></div></div></div>

    <div v-if="showAlert" class="alert-toast" :class="alertType"><span>{{ alertMessage }}</span><button @click="showAlert = false">✕</button></div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import SidebarOwner from '../../components/SidebarOwner.vue'

const searchQuery = ref('')
const branchFilter = ref('all')
const stockFilter = ref('all')
const currentPage = ref(1)
const itemsPerPage = ref(10)
const showAddModal = ref(false)
const showAdjustModal = ref(false)
const adjustType = ref('add')
const currentProduct = ref(null)
const adjustQuantity = ref(0)
const adjustNote = ref('')
const selectedProduct = ref(null)
const showAlert = ref(false)
const alertMessage = ref('')
const alertType = ref('success')
const userName = ref('Owner Nicky Frozen')

const stockForm = ref({ product_id: '', branch_id: 1, quantity: 0, note: '' })

const products = ref([
  { id: 1, name: 'Nugget Ayam', category: 'Frozen', price: 35000, stock: 45, branch_id: 1, last_update: '2026-05-25 10:30' },
  { id: 2, name: 'Sosis Solo', category: 'Frozen', price: 28000, stock: 8, branch_id: 1, last_update: '2026-05-24 14:20' },
  { id: 3, name: 'Roti Bakar', category: 'Snack', price: 15000, stock: 3, branch_id: 1, last_update: '2026-05-23 09:15' },
  { id: 4, name: 'Kentang Goreng', category: 'Frozen', price: 20000, stock: 28, branch_id: 1, last_update: '2026-05-25 08:00' },
  { id: 5, name: 'Es Krim', category: 'Dessert', price: 12000, stock: 52, branch_id: 2, last_update: '2026-05-24 16:45' },
  { id: 6, name: 'Pizza Frozen', category: 'Frozen', price: 55000, stock: 0, branch_id: 2, last_update: '2026-05-20 11:00' },
  { id: 7, name: 'Dimsum', category: 'Frozen', price: 25000, stock: 15, branch_id: 1, last_update: '2026-05-25 13:00' },
  { id: 8, name: 'Cireng', category: 'Snack', price: 10000, stock: 40, branch_id: 2, last_update: '2026-05-24 10:00' }
])

const stockHistory = ref([
  { date: '2026-05-25 10:30', type: 'in', quantity: 20, before_stock: 25, after_stock: 45, note: 'Restock dari supplier', user: 'Owner' }
])

const userInitial = computed(() => userName.value.charAt(0))
const getProductStock = (id) => products.value.find(p => p.id === id)?.stock || 0
const newStockValue = computed(() => getProductStock(stockForm.value.product_id) + stockForm.value.quantity)

const filteredProducts = computed(() => {
  let result = [...products.value]
  if (searchQuery.value) result = result.filter(p => p.name.toLowerCase().includes(searchQuery.value.toLowerCase()))
  if (branchFilter.value !== 'all') result = result.filter(p => p.branch_id.toString() === branchFilter.value)
  if (stockFilter.value !== 'all') {
    switch (stockFilter.value) {
      case 'low': result = result.filter(p => p.stock > 0 && p.stock <= 10); break
      case 'critical': result = result.filter(p => p.stock > 0 && p.stock <= 5); break
      case 'out': result = result.filter(p => p.stock === 0); break
      case 'normal': result = result.filter(p => p.stock > 10); break
    }
  }
  return result
})

const totalProducts = computed(() => filteredProducts.value.length)
const lowStockCount = computed(() => filteredProducts.value.filter(p => p.stock > 0 && p.stock <= 10).length)
const criticalStockCount = computed(() => filteredProducts.value.filter(p => p.stock > 0 && p.stock <= 5).length)
const totalStockValue = computed(() => filteredProducts.value.reduce((s, p) => s + (p.price * p.stock), 0))
const totalPages = computed(() => Math.ceil(filteredProducts.value.length / itemsPerPage.value))
const paginatedProducts = computed(() => filteredProducts.value.slice((currentPage.value - 1) * itemsPerPage.value, currentPage.value * itemsPerPage.value))

const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num)
const getStockClass = (stock) => stock === 0 ? 'stock-zero' : stock <= 5 ? 'stock-critical' : stock <= 10 ? 'stock-low' : 'stock-normal'
const getStockStatusClass = (stock) => stock === 0 ? 'status-out' : stock <= 5 ? 'status-critical' : stock <= 10 ? 'status-low' : 'status-normal'
const getStockStatusText = (stock) => stock === 0 ? 'Habis' : stock <= 5 ? 'Kritis' : stock <= 10 ? 'Menipis' : 'Normal'

const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }

const openAddStockModal = () => { stockForm.value = { product_id: '', branch_id: 1, quantity: 0, note: '' }; showAddModal.value = true }
const openAdjustModal = (product, type) => { currentProduct.value = product; adjustType.value = type; adjustQuantity.value = 0; adjustNote.value = ''; showAdjustModal.value = true }
const viewHistory = (product) => { selectedProduct.value = product }
const closeModal = () => { showAddModal.value = false }
const saveAdjustment = () => {
  if (!currentProduct.value) return
  const idx = products.value.findIndex(p => p.id === currentProduct.value.id)
  if (idx !== -1) {
    const oldStock = products.value[idx].stock
    const newStock = adjustType.value === 'add' ? oldStock + adjustQuantity.value : oldStock - adjustQuantity.value
    products.value[idx].stock = newStock
    products.value[idx].last_update = new Date().toLocaleString()
    stockHistory.value.unshift({ date: new Date().toLocaleString(), type: adjustType.value === 'add' ? 'in' : 'out', quantity: adjustQuantity.value, before_stock: oldStock, after_stock: newStock, note: adjustNote.value || (adjustType.value === 'add' ? 'Penambahan stok' : 'Pengurangan stok'), user: 'Owner' })
    showAlertMessage(`Stok ${adjustType.value === 'add' ? 'ditambahkan' : 'dikurangi'} ${adjustQuantity.value} pcs`, 'success')
  }
  showAdjustModal.value = false
  currentProduct.value = null
  adjustQuantity.value = 0
}

const addStock = () => {
  const product = products.value.find(p => p.id === stockForm.value.product_id)
  if (product) {
    const oldStock = product.stock
    product.stock += stockForm.value.quantity
    product.last_update = new Date().toLocaleString()
    stockHistory.value.unshift({ date: new Date().toLocaleString(), type: 'in', quantity: stockForm.value.quantity, before_stock: oldStock, after_stock: product.stock, note: stockForm.value.note || 'Restock', user: 'Owner' })
    showAlertMessage(`Berhasil tambah stok ${product.name} ${stockForm.value.quantity} pcs`, 'success')
  }
  closeModal()
}

const exportData = () => showAlertMessage('Data berhasil diekspor!', 'success')
const showAlertMessage = (msg, type) => { alertMessage.value = msg; alertType.value = type; showAlert.value = true; setTimeout(() => { showAlert.value = false }, 3000) }
const resetPage = () => { currentPage.value = 1 }
watch([searchQuery, branchFilter, stockFilter], () => resetPage())

onMounted(() => { const saved = localStorage.getItem('stocks'); if (saved) products.value = JSON.parse(saved) })
</script>

<style scoped>
.stocks-container { display: flex; min-height: 100vh; background: #f3f4f6; }
.main-content { flex: 1; margin-left: 260px; padding: 1.5rem; }
.top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; background: white; padding: 1rem 1.5rem; border-radius: 12px; }
.page-title h1 { font-size: 1.25rem; color: #1F3864; }
.page-title p { font-size: 0.8rem; color: #6b7280; }
.top-bar-right { display: flex; align-items: center; gap: 1rem; }
.btn-add { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #10b981; color: white; border: none; border-radius: 8px; cursor: pointer; }
.user-avatar { width: 40px; height: 40px; background: #2E75B6; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; }
.stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
.stat-card { background: white; border-radius: 12px; padding: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
.stat-card.warning { border-left: 4px solid #f59e0b; }
.stat-card.danger { border-left: 4px solid #ef4444; }
.stat-card.info { border-left: 4px solid #3b82f6; }
.stat-label { font-size: 0.7rem; color: #6b7280; }
.stat-value { font-size: 1.5rem; font-weight: 700; }
.filters-bar { display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
.search-bar { flex: 2; position: relative; }
.search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 18px; }
.search-input { width: 100%; padding: 0.625rem 1rem 0.625rem 2.5rem; border: 1px solid #e5e7eb; border-radius: 8px; }
.filter-select { padding: 0.625rem 1rem; border: 1px solid #e5e7eb; border-radius: 8px; background: white; }
.card { background: white; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 1.5rem; }
.card-header { display: flex; justify-content: space-between; align-items: center; padding: 1rem; border-bottom: 1px solid #e5e7eb; }
.table-responsive { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th, .data-table td { padding: 0.75rem 1rem; text-align: left; border-bottom: 1px solid #e5e7eb; }
.data-table th { background: #f9fafb; font-size: 0.7rem; font-weight: 600; color: #6b7280; }
.data-table td { font-size: 0.8rem; }
.product-name { font-weight: 500; }
.stock-zero { color: #ef4444; font-weight: 600; }
.stock-critical { color: #ef4444; }
.stock-low { color: #f59e0b; }
.stock-normal { color: #10b981; }
.status-badge { padding: 0.25rem 0.5rem; border-radius: 20px; font-size: 0.7rem; }
.status-out { background: #fee2e2; color: #991b1b; }
.status-critical { background: #fee2e2; color: #dc2626; }
.status-low { background: #fef3c7; color: #92400e; }
.status-normal { background: #d1fae5; color: #065f46; }
.actions { display: flex; gap: 0.5rem; }
.action-btn { padding: 0.25rem; background: none; border: none; cursor: pointer; border-radius: 4px; }
.action-btn svg { width: 18px; height: 18px; }
.action-btn.add { color: #10b981; }
.action-btn.reduce { color: #f59e0b; }
.action-btn.history { color: #2E75B6; }
.pagination { display: flex; justify-content: center; gap: 1rem; padding: 1rem; border-top: 1px solid #e5e7eb; }
.page-btn { padding: 0.5rem 1rem; background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 6px; cursor: pointer; }
.history-type { padding: 0.25rem 0.5rem; border-radius: 20px; font-size: 0.7rem; }
.history-type.in { background: #d1fae5; color: #065f46; }
.history-type.out { background: #fee2e2; color: #991b1b; }
.text-success { color: #10b981; }
.text-danger { color: #ef4444; }
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal { background: white; border-radius: 12px; width: 500px; max-width: 90%; overflow: hidden; }
.modal-header { display: flex; justify-content: space-between; padding: 1rem; border-bottom: 1px solid #e5e7eb; }
.modal-body { padding: 1.5rem; }
.modal-footer { display: flex; justify-content: flex-end; gap: 0.5rem; padding: 1rem; border-top: 1px solid #e5e7eb; }
.close-btn { background: none; border: none; font-size: 1.25rem; cursor: pointer; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; font-size: 0.8rem; font-weight: 500; margin-bottom: 0.5rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.modal-input { width: 100%; padding: 0.625rem; border: 1px solid #e5e7eb; border-radius: 8px; }
.product-info-card { background: #f3f4f6; padding: 1rem; border-radius: 8px; margin-bottom: 1rem; }
.btn-primary { padding: 0.5rem 1rem; background: #1F3864; color: white; border: none; border-radius: 6px; cursor: pointer; }
.btn-secondary { padding: 0.5rem 1rem; background: #e5e7eb; color: #374151; border: none; border-radius: 6px; cursor: pointer; }
.alert-toast { position: fixed; bottom: 20px; right: 20px; padding: 1rem 1.5rem; border-radius: 8px; display: flex; gap: 1rem; z-index: 1100; animation: slideIn 0.3s ease; }
.alert-toast.success { background: #10b981; color: white; }
@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
@media (max-width: 768px) { .main-content { margin-left: 70px; } .stats-grid { grid-template-columns: repeat(2, 1fr); } }
</style>