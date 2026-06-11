<template>
  <div class="products-container">
    <SidebarOwner />

    <main class="main-content">
      <div class="top-bar">
        <div class="page-title">
          <h1>Manajemen Produk</h1>
          <p>Kelola produk, harga, dan monitoring expired date</p>
        </div>
        <div class="top-bar-right">
          <button class="btn-add" @click="openAddModal">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M12 4v16m8-8H4" />
            </svg>
            <span>Tambah Produk</span>
          </button>
          <div class="user-avatar">
            <span>{{ userInitial }}</span>
          </div>
        </div>
      </div>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-info">
            <p class="stat-label">Total Produk</p>
            <p class="stat-value">{{ totalProducts }}</p>
          </div>
        </div>
        <div class="stat-card warning">
          <div class="stat-info">
            <p class="stat-label">Stok Menipis</p>
            <p class="stat-value">{{ lowStockCount }}</p>
          </div>
        </div>
        <div class="stat-card danger">
          <div class="stat-info">
            <p class="stat-label">Expired < 7 Hari</p>
            <p class="stat-value">{{ expiringCount }}</p>
          </div>
        </div>
        <div class="stat-card expired">
          <div class="stat-info">
            <p class="stat-label">Sudah Expired</p>
            <p class="stat-value">{{ expiredCount }}</p>
          </div>
        </div>
      </div>

      <div class="filters-bar">
        <div class="search-bar">
          <svg
            class="search-icon"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari produk..."
            class="search-input"
          />
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
        <select v-model="branchFilter" class="filter-select">
          <option value="all">Semua Cabang</option>
          <option v-for="b in branches" :key="b.id" :value="b.id.toString()">{{ b.name }}</option>
        </select>
      </div>

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
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(product, index) in paginatedProducts"
                :key="product.id"
              >
                <td>{{ (currentPage - 1) * itemsPerPage + index + 1 }}</td>
                <td class="product-name">{{ product.name }}</td>
                <td>{{ product.category }}</td>
                <td class="price">Rp {{ formatNumber(product.price) }}</td>
                <td>
                  <span :class="getStockClass(product.stock)">{{
                    product.stock
                  }}</span>
                </td>
                <td>
                  {{ formatDate(product.expired_date) }}
                  <span
                    class="days-left"
                    :class="getDaysLeftClass(product.daysLeft)"
                    >({{ product.daysLeft }} hari)</span
                  >
                </td>
                <td>
                  <span
                    class="status-badge"
                    :class="getProductStatusClass(product)"
                    >{{ getProductStatusText(product) }}</span
                  >
                </td>
                <td class="actions">
                  <button
                    class="action-btn edit"
                    @click="openEditModal(product)"
                    title="Edit"
                  >
                    <svg
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                    >
                      <path
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                      />
                    </svg>
                  </button>
                  <button
                    class="action-btn delete"
                    @click="confirmDelete(product)"
                    title="Hapus"
                  >
                    <svg
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                    >
                      <path
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                      />
                    </svg>
                  </button>
                </td>
              </tr>
              <tr v-if="filteredProducts.length === 0">
                <td colspan="8" class="empty-row">
                  Tidak ada produk yang ditemukan
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="pagination" v-if="totalPages > 1">
          <button
            class="page-btn"
            @click="prevPage"
            :disabled="currentPage === 1"
          >
            &laquo; Sebelumnya
          </button>
          <span class="page-info"
            >Halaman {{ currentPage }} dari {{ totalPages }}</span
          >
          <button
            class="page-btn"
            @click="nextPage"
            :disabled="currentPage === totalPages"
          >
            Selanjutnya &raquo;
          </button>
        </div>
      </div>
    </main>

    <!-- Modal Add/Edit -->
    <div v-if="showModal" class="modal-overlay" @click.self="closeModal">
      <div class="modal">
        <div class="modal-header">
          <h3>{{ isEditMode ? "Edit Produk" : "Tambah Produk Baru" }}</h3>
          <button class="close-btn" @click="closeModal">✕</button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Nama Produk *</label
            ><input v-model="form.name" type="text" class="modal-input" />
            <p v-if="errors.name" class="error-text">{{ errors.name }}</p>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Kategori *</label
              ><select v-model="form.category" class="modal-input">
                <option value="">Pilih Kategori</option>
                <option value="Frozen">Frozen Food</option>
                <option value="Snack">Snack</option>
                <option value="Dessert">Dessert</option>
              </select>
            </div>
            <div class="form-group">
              <label>Harga *</label>
              <div class="input-wrapper">
                <span class="currency">Rp</span
                ><input
                  v-model.number="form.price"
                  type="number"
                  class="modal-input"
                />
              </div>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Stok</label
              ><input
                v-model.number="form.stock"
                type="number"
                class="modal-input"
              />
            </div>
            <div class="form-group">
              <label>Tanggal Expired</label
              ><input
                v-model="form.expired_date"
                type="date"
                class="modal-input"
              />
            </div>
          </div>
          <div class="form-group">
            <label>Cabang</label
            ><select v-model="form.branch_id" class="modal-input">
              <option value="">Pilih Cabang</option>
              <option v-for="b in branches" :key="b.id" :value="b.id">{{ b.name }}</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="closeModal">Batal</button
          ><button class="btn-primary" @click="saveProduct">Simpan</button>
        </div>
      </div>
    </div>

    <!-- Modal Delete -->
    <div
      v-if="showDeleteModal"
      class="modal-overlay"
      @click.self="showDeleteModal = false"
    >
      <div class="modal small-modal">
        <div class="modal-header">
          <h3>Hapus Produk</h3>
          <button class="close-btn" @click="showDeleteModal = false">✕</button>
        </div>
        <div class="modal-body">
          <p>
            Apakah Anda yakin ingin menghapus produk
            <strong>{{ productToDelete?.name }}</strong
            >?
          </p>
          <p class="warning-text">
            Data yang dihapus tidak dapat dikembalikan!
          </p>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="showDeleteModal = false">
            Batal</button
          ><button class="btn-danger" @click="deleteProduct">Hapus</button>
        </div>
      </div>
    </div>

    <!-- Alert Toast -->
    <div v-if="showAlert" class="alert-toast" :class="alertType">
      <span>{{ alertMessage }}</span
      ><button @click="showAlert = false">✕</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import SidebarOwner from "../../components/SidebarOwner.vue";
import api from "../../services/axios.js";

const searchQuery = ref("");
const categoryFilter = ref("all");
const statusFilter = ref("all");
const branchFilter = ref("all");
const currentPage = ref(1);
const itemsPerPage = ref(10);
const showModal = ref(false);
const showDeleteModal = ref(false);
const isEditMode = ref(false);
const showAlert = ref(false);
const alertMessage = ref("");
const alertType = ref("success");
const productToDelete = ref(null);
const userName = ref("Owner");
const isLoading = ref(false);

// Stats dari API
const statTotal = ref(0);
const statExpiring = ref(0);
const statExpired = ref(0);
const statLowStock = ref(0);

const branches = ref([]);
const products = ref([]);

const form = ref({
  id: null,
  name: "",
  category: "",
  price: 0,
  stock: 0,
  min_stock: 10,
  expired_date: "",
  branch_id: "",
});
const errors = ref({ name: "", category: "", price: "" });

const addDaysLeft = (product) => {
  // Pakai days_left dari API (sudah dihitung backend)
  // days_left: positif = belum expired, negatif/0 = sudah expired
  if (product.days_left !== undefined && product.days_left !== null) {
    return product.days_left;
  }
  // fallback hitung manual jika tidak ada
  const today = new Date();
  const expiredDate = new Date(product.expired_date);
  return Math.ceil((expiredDate - today) / (1000 * 60 * 60 * 24));
};

const userInitial = computed(() => userName.value.charAt(0));

const productsWithStatus = computed(() =>
  products.value.map((p) => {
    const d = addDaysLeft(p);
    return {
      ...p,
      daysLeft: d,
      isLowStock: p.stock > 0 && p.stock <= 10,
      isExpiring: d > 0 && d <= 7,
      isExpired: d <= 0,
    };
  }),
);

const filteredProducts = computed(() => {
  let result = productsWithStatus.value;
  if (searchQuery.value)
    result = result.filter((p) =>
      p.name.toLowerCase().includes(searchQuery.value.toLowerCase()),
    );
  if (categoryFilter.value !== "all")
    result = result.filter((p) => p.category === categoryFilter.value);
  if (statusFilter.value !== "all") {
    switch (statusFilter.value) {
      case "low_stock":
        result = result.filter((p) => p.isLowStock && !p.isExpired); break;
      case "expiring":
        result = result.filter((p) => p.isExpiring && !p.isExpired); break;
      case "expired":
        result = result.filter((p) => p.isExpired); break;
      case "normal":
        result = result.filter((p) => !p.isLowStock && !p.isExpiring && !p.isExpired && p.stock > 0); break;
    }
  }
  if (branchFilter.value !== "all")
    result = result.filter((p) => p.branch_id.toString() === branchFilter.value);
  return result;
});

const totalProducts = computed(() => statTotal.value);
const lowStockCount = computed(() => statLowStock.value);
const expiringCount = computed(() => statExpiring.value);
const expiredCount  = computed(() => statExpired.value);
const totalPages    = computed(() => Math.ceil(filteredProducts.value.length / itemsPerPage.value));
const paginatedProducts = computed(() =>
  filteredProducts.value.slice(
    (currentPage.value - 1) * itemsPerPage.value,
    currentPage.value * itemsPerPage.value,
  ),
);

const formatNumber = (num) => new Intl.NumberFormat("id-ID").format(num);
const formatDate = (date) =>
  date ? new Date(date).toLocaleDateString("id-ID") : "-";
const getStockClass = (stock) =>
  stock === 0
    ? "stock-zero"
    : stock <= 5
      ? "stock-critical"
      : stock <= 10
        ? "stock-low"
        : "stock-normal";
const getDaysLeftClass = (days) =>
  days <= 0
    ? "expired"
    : days <= 3
      ? "critical"
      : days <= 7
        ? "warning"
        : "normal";
const getProductStatusClass = (p) =>
  p.isExpired
    ? "status-expired"
    : p.isExpiring
      ? "status-expiring"
      : p.stock === 0
        ? "status-out"
        : p.isLowStock
          ? "status-low"
          : "status-normal";
const getProductStatusText = (p) =>
  p.isExpired
    ? "Expired"
    : p.isExpiring
      ? "Akan Expired"
      : p.stock === 0
        ? "Habis"
        : p.isLowStock
          ? "Stok Menipis"
          : "Normal";

const prevPage = () => { if (currentPage.value > 1) currentPage.value--; };
const nextPage = () => { if (currentPage.value < totalPages.value) currentPage.value++; };

const openAddModal = () => {
  isEditMode.value = false;
  form.value = { id: null, name: "", category: "", price: 0, stock: 0, min_stock: 10, expired_date: "", branch_id: branches.value[0]?.id ?? "" };
  errors.value = { name: "", category: "", price: "" };
  showModal.value = true;
};

const openEditModal = (product) => {
  isEditMode.value = true;
  form.value = { ...product, stock: product.stock ?? 0, min_stock: product.min_stock ?? 10 };
  errors.value = { name: "", category: "", price: "" };
  showModal.value = true;
};

const closeModal = () => { showModal.value = false; };

const validateForm = () => {
  let isValid = true;
  errors.value = { name: "", category: "", price: "" };
  if (!form.value.name)            { errors.value.name     = "Nama produk wajib diisi"; isValid = false; }
  if (!form.value.category)        { errors.value.category = "Kategori wajib dipilih";  isValid = false; }
  if (!form.value.price || form.value.price <= 0) { errors.value.price = "Harga harus lebih dari 0"; isValid = false; }
  return isValid;
};

const saveProduct = async () => {
  if (!validateForm()) return;
  try {
    if (isEditMode.value) {
      const res = await api.put(`/products/${form.value.id}`, form.value);
      const idx = products.value.findIndex((p) => p.id === form.value.id);
      if (idx !== -1) products.value[idx] = res.data.product;
      showAlertMessage("Produk berhasil diperbarui!", "success");
    } else {
      const res = await api.post("/products", form.value);
      products.value.unshift(res.data.product);
      statTotal.value++;
      showAlertMessage("Produk berhasil ditambahkan!", "success");
    }
  } catch (err) {
    showAlertMessage(err.response?.data?.message || "Gagal menyimpan produk.", "error");
  }
  closeModal();
};

const confirmDelete = (product) => {
  productToDelete.value = product;
  showDeleteModal.value = true;
};

const deleteProduct = async () => {
  try {
    await api.delete(`/products/${productToDelete.value.id}`);
    products.value = products.value.filter((p) => p.id !== productToDelete.value.id);
    statTotal.value--;
    showAlertMessage("Produk berhasil dihapus!", "success");
  } catch (err) {
    showAlertMessage(err.response?.data?.message || "Gagal menghapus produk.", "error");
  }
  showDeleteModal.value = false;
  productToDelete.value = null;
};

const fetchProducts = async () => {
  isLoading.value = true;
  try {
    const params = {};
    if (searchQuery.value)          params.search    = searchQuery.value;
    if (categoryFilter.value !== "all") params.category = categoryFilter.value;
    if (branchFilter.value !== "all")   params.branch_id = branchFilter.value;

    const res = await api.get("/products", { params });
    products.value   = res.data.products;
    branches.value   = res.data.branches;
    statTotal.value    = res.data.total_products;
    statExpiring.value = res.data.expiring_soon;
    statExpired.value  = res.data.expired_count;
    statLowStock.value = res.data.low_stock;
  } catch (err) {
    showAlertMessage("Gagal memuat data produk.", "error");
  } finally {
    isLoading.value = false;
  }
};

const showAlertMessage = (message, type) => {
  alertMessage.value = message;
  alertType.value    = type;
  showAlert.value    = true;
  setTimeout(() => { showAlert.value = false; }, 3000);
};

const resetPage = () => { currentPage.value = 1; };

watch([categoryFilter, statusFilter, branchFilter], () => { resetPage(); fetchProducts(); });
watch(searchQuery, () => {
  resetPage();
  clearTimeout(window._prodSearchTimeout);
  window._prodSearchTimeout = setTimeout(fetchProducts, 400);
});

onMounted(() => {
  const user = JSON.parse(localStorage.getItem("user") || "{}");
  if (user.name) userName.value = user.name;
  fetchProducts();
});
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
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
.page-title h1 {
  font-size: 1.25rem;
  color: #1f3864;
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
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
.stat-card.warning {
  border-left: 4px solid #f59e0b;
}
.stat-card.danger {
  border-left: 4px solid #ef4444;
}
.stat-card.expired {
  border-left: 4px solid #7f1d1d;
  background: #fef2f2;
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
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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
  color: #2e75b6;
}
.action-btn.edit:hover {
  background: #dbeafe;
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
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}
.modal {
  background: white;
  border-radius: 12px;
  width: 550px;
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
.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}
.input-wrapper {
  position: relative;
}
.currency {
  position: absolute;
  left: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #6b7280;
  font-size: 0.8rem;
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
  border-color: #2e75b6;
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
  background: #1f3864;
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
  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>