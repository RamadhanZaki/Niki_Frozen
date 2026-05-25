<template>
  <div class="products-container">
    <SidebarKasir />

    <main class="main-content">
      <!-- Top Bar -->
      <div class="top-bar">
        <div class="page-title">
          <h1>Daftar Produk</h1>
          <p>Lihat informasi produk, stok, dan tanggal kedaluwarsa</p>
        </div>
        <div class="user-avatar">
          <span>{{ cashierInitial }}</span>
        </div>
      </div>

      <!-- Filters -->
      <div class="filters-bar">
        <div class="search-bar">
          <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input 
            v-model="searchQuery" 
            type="text" 
            placeholder="Cari produk..." 
            class="search-input"
          >
        </div>
        <select v-model="categoryFilter" class="filter-select">
          <option value="all">Semua Kategori</option>
          <option value="Frozen">Frozen Food</option>
          <option value="Snack">Snack</option>
          <option value="Dessert">Dessert</option>
        </select>
        <select v-model="statusFilter" class="filter-select">
          <option value="all">Semua Status</option>
          <option value="normal">Normal</option>
          <option value="low_stock">Stok Menipis</option>
          <option value="expiring">Akan Expired</option>
          <option value="expired">Expired</option>
        </select>
      </div>

      <!-- Products Table -->
      <div class="card">
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>No</th>
                <th>Nama Produk</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Expired Date</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(product, index) in paginatedProducts" :key="product.id">
                <td>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                <td class="product-name">{{ product.name }}</td>
                <td>{{ product.category }}</td>
                <td class="price">Rp {{ formatNumber(product.price) }}</td>
                <td>
                  <span :class="getStockClass(product.stock)">{{ product.stock }}</span>
                </td>
                <td>
                  {{ formatDate(product.expired_date) }}
                  <span class="days-left" :class="getDaysLeftClass(product.daysLeft)">
                    ({{ product.daysLeft }} hari)
                  </span>
                </td>
                <td>
                  <span class="status-badge" :class="getProductStatusClass(product)">
                    {{ getProductStatusText(product) }}
                  </span>
                </td>
              </tr>
              <tr v-if="filteredProducts.length === 0">
                <td colspan="7" class="empty-row">Tidak ada produk yang ditemukan</td>
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
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import SidebarKasir from '../../components/SidebarKasir.vue'

// State
const searchQuery = ref('')
const categoryFilter = ref('all')
const statusFilter = ref('all')
const currentPage = ref(1)
const itemsPerPage = ref(10)
const cashierName = ref('Kasir Cabang Utama')

// Products data (sama dengan data di POS, hanya untuk tampilan)
const products = ref([
  { id: 1, name: 'Nugget Ayam', category: 'Frozen', price: 35000, stock: 45, expired_date: '2025-12-31' },
  { id: 2, name: 'Sosis Solo', category: 'Frozen', price: 28000, stock: 8, expired_date: '2025-11-30' },
  { id: 3, name: 'Roti Bakar', category: 'Snack', price: 15000, stock: 3, expired_date: '2025-10-15' },
  { id: 4, name: 'Kentang Goreng', category: 'Frozen', price: 20000, stock: 28, expired_date: '2025-12-20' },
  { id: 5, name: 'Es Krim', category: 'Dessert', price: 12000, stock: 52, expired_date: '2026-01-15' },
  { id: 6, name: 'Pizza Frozen', category: 'Frozen', price: 55000, stock: 8, expired_date: '2025-12-31' },
  { id: 7, name: 'Dimsum', category: 'Frozen', price: 25000, stock: 15, expired_date: '2025-12-10' },
  { id: 8, name: 'Cireng', category: 'Snack', price: 10000, stock: 40, expired_date: '2026-01-01' }
])

// Helpers
const addDaysLeft = (product) => {
  const today = new Date()
  const expiredDate = new Date(product.expired_date)
  return Math.ceil((expiredDate - today) / (1000 * 60 * 60 * 24))
}

// Computed
const cashierInitial = computed(() => cashierName.value.charAt(0))

const productsWithStatus = computed(() => {
  return products.value.map(p => ({
    ...p,
    daysLeft: addDaysLeft(p),
    isLowStock: p.stock > 0 && p.stock <= 10,
    isExpiring: addDaysLeft(p) > 0 && addDaysLeft(p) <= 7,
    isExpired: addDaysLeft(p) <= 0 && p.stock > 0
  }))
})

const filteredProducts = computed(() => {
  let result = productsWithStatus.value

  if (searchQuery.value) {
    result = result.filter(p => 
      p.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }
  if (categoryFilter.value !== 'all') {
    result = result.filter(p => p.category === categoryFilter.value)
  }
  if (statusFilter.value !== 'all') {
    switch (statusFilter.value) {
      case 'low_stock':
        result = result.filter(p => p.isLowStock && !p.isExpired)
        break
      case 'expiring':
        result = result.filter(p => p.isExpiring && !p.isExpired)
        break
      case 'expired':
        result = result.filter(p => p.isExpired)
        break
      case 'normal':
        result = result.filter(p => !p.isLowStock && !p.isExpiring && !p.isExpired && p.stock > 0)
        break
    }
  }
  return result
})

const totalPages = computed(() => Math.ceil(filteredProducts.value.length / itemsPerPage.value))
const paginatedProducts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredProducts.value.slice(start, end)
})

// Methods
const formatNumber = (num) => new Intl.NumberFormat('id-ID').format(num)
const formatDate = (date) => date ? new Date(date).toLocaleDateString('id-ID') : '-'

const getStockClass = (stock) => {
  if (stock === 0) return 'stock-zero'
  if (stock <= 5) return 'stock-critical'
  if (stock <= 10) return 'stock-low'
  return 'stock-normal'
}

const getDaysLeftClass = (days) => {
  if (days <= 0) return 'expired'
  if (days <= 3) return 'critical'
  if (days <= 7) return 'warning'
  return 'normal'
}

const getProductStatusClass = (p) => {
  if (p.isExpired) return 'status-expired'
  if (p.isExpiring) return 'status-expiring'
  if (p.stock === 0) return 'status-out'
  if (p.isLowStock) return 'status-low'
  return 'status-normal'
}

const getProductStatusText = (p) => {
  if (p.isExpired) return 'Expired'
  if (p.isExpiring) return 'Akan Expired'
  if (p.stock === 0) return 'Habis'
  if (p.isLowStock) return 'Stok Menipis'
  return 'Normal'
}

const prevPage = () => { if (currentPage.value > 1) currentPage.value-- }
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++ }

watch([searchQuery, categoryFilter, statusFilter], () => {
  currentPage.value = 1
})
</script>

<style scoped>
.products-container {
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

.filters-bar {
  display: flex;
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

.product-name {
  font-weight: 500;
}

.price {
  font-weight: 500;
}

.stock-zero {
  color: #ef4444;
  font-weight: 600;
}

.stock-critical {
  color: #ef4444;
}

.stock-low {
  color: #f59e0b;
}

.stock-normal {
  color: #10b981;
}

.days-left {
  font-size: 0.7rem;
}

.days-left.expired {
  color: #ef4444;
}

.days-left.critical {
  color: #ef4444;
}

.days-left.warning {
  color: #f59e0b;
}

.days-left.normal {
  color: #10b981;
}

.status-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 20px;
  font-size: 0.7rem;
}

.status-expired {
  background: #fee2e2;
  color: #991b1b;
}

.status-expiring {
  background: #fed7aa;
  color: #9a3412;
}

.status-out {
  background: #f3f4f6;
  color: #6b7280;
}

.status-low {
  background: #fef3c7;
  color: #92400e;
}

.status-normal {
  background: #d1fae5;
  color: #065f46;
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

/* Responsive */
@media (max-width: 768px) {
  .main-content {
    margin-left: 70px;
    padding: 1rem;
  }
  .filters-bar {
    flex-direction: column;
  }
}
</style>