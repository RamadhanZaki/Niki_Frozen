<template>
  <div class="pos-container">
    <!-- Sidebar Kasir Component -->
    <SidebarKasir />

    <!-- Main POS Area -->
    <main class="pos-main">
      <!-- Top Bar -->
      <div class="top-bar">
        <div class="page-title">
          <h1>Point of Sales</h1>
          <p>Kasir: {{ cashierName }} | Cabang Utama</p>
        </div>
        <div class="top-bar-right">
          <div
            class="pending-badge"
            v-if="pendingCount > 0"
            @click="showPendingModal = true"
          >
            <span class="pending-icon">📤</span>
            <span class="pending-count">{{ pendingCount }}</span>
          </div>
          <div class="user-avatar">
            <span>{{ cashierInitial }}</span>
          </div>
        </div>
      </div>

      <!-- Shift Warning -->
      <div v-if="!shiftActive" class="shift-warning">
        <svg
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <path
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
          />
        </svg>
        <div>
          <h3>Shift Belum Dibuka</h3>
          <p>Silakan buka shift terlebih dahulu untuk memulai transaksi</p>
        </div>
        <button @click="openShiftModal = true" class="btn-primary">
          Buka Shift
        </button>
      </div>

      <!-- POS Content -->
      <div v-else class="pos-content">
        <!-- Left: Product Grid -->
        <div class="product-section">
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

          <div class="product-grid">
            <div
              v-for="product in filteredProducts"
              :key="product.id"
              class="product-card"
              :class="{ 'low-stock': product.stock <= 5 }"
              @click="addToCart(product)"
            >
              <div class="product-info">
                <h4>{{ product.name }}</h4>
                <p class="product-price">
                  Rp {{ formatNumber(product.price) }}
                </p>
                <p class="product-stock" :class="{ low: product.stock <= 5 }">
                  Stok: {{ product.stock }}
                </p>
              </div>
              <div class="product-expiry" v-if="product.expiryWarning">
                <span>⚠️</span>
              </div>
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
            <button
              v-if="cart.length > 0"
              class="clear-cart"
              @click="clearCart"
            >
              Kosongkan
            </button>
          </div>

          <div class="cart-items">
            <div v-for="(item, index) in cart" :key="index" class="cart-item">
              <div class="item-info">
                <h4>{{ item.name }}</h4>
                <p>Rp {{ formatNumber(item.price) }}</p>
              </div>
              <div class="item-actions">
                <button class="qty-btn" @click="updateQuantity(item, -1)">
                  -
                </button>
                <span class="qty">{{ item.quantity }}</span>
                <button class="qty-btn" @click="updateQuantity(item, 1)">
                  +
                </button>
                <button class="remove-btn" @click="removeFromCart(index)">
                  ✕
                </button>
              </div>
              <div class="item-subtotal">
                Rp {{ formatNumber(item.price * item.quantity) }}
              </div>
            </div>

            <div v-if="cart.length === 0" class="empty-cart">
              <svg
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
              >
                <path
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 6M18 13l1.5 6M9 21h6M12 13v8"
                />
              </svg>
              <p>Keranjang kosong</p>
            </div>
          </div>

          <div class="cart-total">
            <div class="total-row">
              <span>Total</span>
              <span class="total-amount"
                >Rp {{ formatNumber(totalAmount) }}</span
              >
            </div>
            <div class="total-row">
              <span>Pembayaran</span>
              <input
                type="number"
                v-model.number="paymentAmount"
                class="payment-input"
                placeholder="0"
              />
            </div>
            <div class="total-row change">
              <span>Kembalian</span>
              <span class="change-amount"
                >Rp {{ formatNumber(changeAmount) }}</span
              >
            </div>
          </div>

          <button
            class="btn-checkout"
            :disabled="cart.length === 0 || paymentAmount < totalAmount"
            @click="processTransaction"
          >
            <span>Proses Transaksi</span>
          </button>
        </div>
      </div>
    </main>

    <!-- Buka Shift Modal -->
    <div
      v-if="openShiftModal"
      class="modal-overlay"
      @click.self="openShiftModal = false"
    >
      <div class="modal">
        <div class="modal-header">
          <h3>Buka Shift</h3>
          <button class="close-btn" @click="openShiftModal = false">✕</button>
        </div>
        <div class="modal-body">
          <label>Saldo Kas Awal</label>
          <input
            type="number"
            v-model="openingCashInput"
            class="modal-input"
            placeholder="Masukkan nominal kas awal"
          />
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="openShiftModal = false">
            Batal
          </button>
          <button
            class="btn-primary"
            @click="openShift"
            :disabled="!openingCashInput"
          >
            Buka Shift
          </button>
        </div>
      </div>
    </div>

    <!-- Struk Modal -->
    <div
      v-if="showReceipt"
      class="modal-overlay"
      @click.self="showReceipt = false"
    >
      <div class="modal receipt-modal">
        <div class="modal-header">
          <h3>Struk Transaksi</h3>
          <button class="close-btn" @click="showReceipt = false">✕</button>
        </div>
        <div class="modal-body receipt-content">
          <div class="receipt-header">
            <h2>Nicky Frozen</h2>
            <p>Jl. Contoh No. 1, Yogyakarta</p>
            <p>Telp: 08123456789</p>
            <hr />
          </div>
          <div class="receipt-items">
            <div
              v-for="item in lastTransaction.items"
              :key="item.id"
              class="receipt-item"
            >
              <span>{{ item.name }} x{{ item.quantity }}</span>
              <span>Rp {{ formatNumber(item.price * item.quantity) }}</span>
            </div>
            <hr />
            <div class="receipt-total">
              <strong>Total</strong>
              <strong>Rp {{ formatNumber(lastTransaction.total) }}</strong>
            </div>
            <div class="receipt-payment">
              <span>Pembayaran</span>
              <span>Rp {{ formatNumber(lastTransaction.payment) }}</span>
            </div>
            <div class="receipt-change">
              <span>Kembalian</span>
              <span>Rp {{ formatNumber(lastTransaction.change) }}</span>
            </div>
            <hr />
            <div class="receipt-footer">
              <p>Terima kasih!</p>
              <p>{{ new Date().toLocaleString() }}</p>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-primary" @click="printReceipt">Cetak Struk</button>
          <button class="btn-secondary" @click="showReceipt = false">
            Tutup
          </button>
        </div>
      </div>
    </div>

    <!-- Pending Sync Modal -->
    <div
      v-if="showPendingModal"
      class="modal-overlay"
      @click.self="showPendingModal = false"
    >
      <div class="modal">
        <div class="modal-header">
          <h3>Transaksi Pending (Offline)</h3>
          <button class="close-btn" @click="showPendingModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div
            v-for="(item, idx) in pendingTransactions"
            :key="idx"
            class="pending-item"
          >
            <div>
              <strong>{{ item.items.length }} produk</strong>
              <p>Total: Rp {{ formatNumber(item.total) }}</p>
              <small>{{ new Date(item.created_at).toLocaleString() }}</small>
            </div>
            <div class="pending-status" :class="item.status">
              {{ item.status === "pending" ? "Menunggu Sync" : "Gagal" }}
            </div>
          </div>
          <div v-if="pendingTransactions.length === 0" class="empty-state">
            Tidak ada transaksi pending
          </div>
        </div>
        <div class="modal-footer">
          <button
            class="btn-primary"
            @click="syncPendingTransactions"
            :disabled="!isOnline"
          >
            Sync Sekarang
          </button>
          <button class="btn-secondary" @click="showPendingModal = false">
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import SidebarKasir from "../../components/SidebarKasir.vue";

const router = useRouter();

// State
const isOnline = ref(navigator.onLine);
const shiftActive = ref(false);
const openingCash = ref(0);
const openingCashInput = ref(0);
const cashierName = ref("Kasir Cabang Utama");
const searchQuery = ref("");
const paymentAmount = ref(0);
const openShiftModal = ref(false);
const showReceipt = ref(false);
const showPendingModal = ref(false);
const pendingCount = ref(0);

// Cart
const cart = ref([]);
const lastTransaction = ref({
  items: [],
  total: 0,
  payment: 0,
  change: 0,
});

// Pending transactions (offline)
const pendingTransactions = ref([]);

// Products data
const products = ref([
  {
    id: 1,
    name: "Nugget Ayam",
    price: 35000,
    stock: 45,
    category: "Frozen",
    expired_date: "2025-12-31",
  },
  {
    id: 2,
    name: "Sosis Solo",
    price: 28000,
    stock: 32,
    category: "Frozen",
    expired_date: "2025-11-30",
  },
  {
    id: 3,
    name: "Roti Bakar",
    price: 15000,
    stock: 18,
    category: "Snack",
    expired_date: "2025-10-15",
  },
  {
    id: 4,
    name: "Kentang Goreng",
    price: 20000,
    stock: 28,
    category: "Frozen",
    expired_date: "2025-12-20",
  },
  {
    id: 5,
    name: "Es Krim",
    price: 12000,
    stock: 52,
    category: "Dessert",
    expired_date: "2026-01-15",
  },
  {
    id: 6,
    name: "Pizza Frozen",
    price: 55000,
    stock: 8,
    category: "Frozen",
    expired_date: "2025-12-31",
  },
  {
    id: 7,
    name: "Dimsum",
    price: 25000,
    stock: 15,
    category: "Frozen",
    expired_date: "2025-12-10",
  },
  {
    id: 8,
    name: "Cireng",
    price: 10000,
    stock: 40,
    category: "Snack",
    expired_date: "2026-01-01",
  },
]);

// Computed
const cashierInitial = computed(() => {
  return cashierName.value.charAt(0);
});

const filteredProducts = computed(() => {
  if (!searchQuery.value) return products.value;
  return products.value.filter((p) =>
    p.name.toLowerCase().includes(searchQuery.value.toLowerCase()),
  );
});

const totalAmount = computed(() => {
  return cart.value.reduce((sum, item) => sum + item.price * item.quantity, 0);
});

const changeAmount = computed(() => {
  return paymentAmount.value - totalAmount.value;
});

// Methods
const formatNumber = (num) => {
  return new Intl.NumberFormat("id-ID").format(num);
};

const addToCart = (product) => {
  const existing = cart.value.find((item) => item.id === product.id);
  if (existing) {
    existing.quantity++;
  } else {
    cart.value.push({
      id: product.id,
      name: product.name,
      price: product.price,
      quantity: 1,
    });
  }
};

const updateQuantity = (item, delta) => {
  const newQty = item.quantity + delta;
  if (newQty <= 0) {
    const index = cart.value.findIndex((i) => i.id === item.id);
    cart.value.splice(index, 1);
  } else {
    item.quantity = newQty;
  }
};

const removeFromCart = (index) => {
  cart.value.splice(index, 1);
};

const clearCart = () => {
  cart.value = [];
  paymentAmount.value = 0;
};

const openShift = () => {
  openingCash.value = openingCashInput.value;
  shiftActive.value = true;
  openShiftModal.value = false;
  // Simpan ke localStorage agar SidebarKasir bisa membaca
  localStorage.setItem("shift_active", "true");
  localStorage.setItem("opening_cash", openingCash.value.toString());
  localStorage.setItem("shift_open_time", new Date().toLocaleString());
};

const processTransaction = async () => {
  if (cart.value.length === 0) return;
  if (paymentAmount.value < totalAmount.value) return;

  const transaction = {
    id: Date.now(),
    items: [...cart.value],
    total: totalAmount.value,
    payment: paymentAmount.value,
    change: changeAmount.value,
    created_at: new Date().toISOString(),
  };

  lastTransaction.value = transaction;

  if (isOnline.value) {
    await saveTransactionToServer(transaction);
  } else {
    saveTransactionToLocal(transaction);
  }

  updateLocalStock(transaction.items);
  clearCart();
  showReceipt.value = true;
};

const saveTransactionToServer = async (transaction) => {
  console.log("Saving to server:", transaction);
  await new Promise((resolve) => setTimeout(resolve, 500));
};

const saveTransactionToLocal = (transaction) => {
  const pending = JSON.parse(
    localStorage.getItem("pending_transactions") || "[]",
  );
  pending.push({ ...transaction, status: "pending" });
  localStorage.setItem("pending_transactions", JSON.stringify(pending));
  pendingTransactions.value = pending;
  pendingCount.value = pending.length;
};

const updateLocalStock = (items) => {
  items.forEach((item) => {
    const product = products.value.find((p) => p.id === item.id);
    if (product) {
      product.stock -= item.quantity;
    }
  });
};

const syncPendingTransactions = async () => {
  if (!isOnline.value) return;

  const pending = JSON.parse(
    localStorage.getItem("pending_transactions") || "[]",
  );
  for (const transaction of pending) {
    await saveTransactionToServer(transaction);
  }
  localStorage.removeItem("pending_transactions");
  pendingTransactions.value = [];
  pendingCount.value = 0;
};

const printReceipt = () => {
  window.print();
};

const logout = () => {
  localStorage.clear();
  router.push("/login");
};

// Load state from localStorage
const loadShiftState = () => {
  const savedShiftActive = localStorage.getItem("shift_active");
  const savedOpeningCash = localStorage.getItem("opening_cash");
  if (savedShiftActive === "true") {
    shiftActive.value = true;
    openingCash.value = Number(savedOpeningCash) || 0;
  }
};

const loadPendingTransactions = () => {
  const pending = JSON.parse(
    localStorage.getItem("pending_transactions") || "[]",
  );
  pendingTransactions.value = pending;
  pendingCount.value = pending.length;
};

// Connection status
const updateOnlineStatus = () => {
  isOnline.value = navigator.onLine;
  if (isOnline.value && pendingCount.value > 0) {
    syncPendingTransactions();
  }
};

onMounted(() => {
  loadShiftState();
  loadPendingTransactions();
  window.addEventListener("online", updateOnlineStatus);
  window.addEventListener("offline", updateOnlineStatus);
});

onUnmounted(() => {
  window.removeEventListener("online", updateOnlineStatus);
  window.removeEventListener("offline", updateOnlineStatus);
});
</script>

<style scoped>
/* Gaya untuk container utama */
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

.pending-badge {
  cursor: pointer;
  padding: 0.5rem;
  background: #fef3c7;
  border-radius: 8px;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.pending-count {
  background: #d97706;
  color: white;
  border-radius: 10px;
  padding: 0 6px;
  font-size: 0.7rem;
  font-weight: bold;
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

.shift-warning svg {
  width: 40px;
  height: 40px;
  color: #d97706;
}

.shift-warning h3 {
  font-size: 1rem;
  margin-bottom: 0.25rem;
}

.shift-warning p {
  font-size: 0.8rem;
  color: #92400e;
}

/* POS Content */
.pos-content {
  display: flex;
  gap: 1rem;
  height: calc(100vh - 80px);
}

/* Product Section */
.product-section {
  flex: 2;
  background: white;
  border-radius: 12px;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  overflow: hidden;
}

.search-bar {
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
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.875rem;
}

.search-input:focus {
  outline: none;
  border-color: #2e75b6;
}

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

.product-card:hover {
  border-color: #2e75b6;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.product-card.low-stock {
  border-left: 3px solid #f59e0b;
}

.product-info h4 {
  font-size: 0.875rem;
  margin-bottom: 0.25rem;
}

.product-price {
  font-weight: bold;
  color: #1f3864;
  font-size: 0.875rem;
}

.product-stock {
  font-size: 0.7rem;
  color: #6b7280;
}

.product-stock.low {
  color: #f59e0b;
}

.product-expiry {
  position: absolute;
  top: 5px;
  right: 5px;
  font-size: 0.8rem;
}

.empty-products {
  text-align: center;
  padding: 2rem;
  color: #9ca3af;
}

/* Cart Section */
.cart-section {
  flex: 1.2;
  background: white;
  border-radius: 12px;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.cart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.cart-header h3 {
  font-size: 1rem;
}

.clear-cart {
  background: none;
  border: none;
  color: #ef4444;
  font-size: 0.75rem;
  cursor: pointer;
}

.cart-items {
  flex: 1;
  overflow-y: auto;
  padding: 0.5rem;
}

.cart-item {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem;
  border-bottom: 1px solid #f3f4f6;
  gap: 0.5rem;
}

.item-info {
  flex: 1;
}

.item-info h4 {
  font-size: 0.8rem;
  margin-bottom: 0.25rem;
}

.item-info p {
  font-size: 0.7rem;
  color: #6b7280;
}

.item-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.qty-btn {
  width: 28px;
  height: 28px;
  border: 1px solid #e5e7eb;
  background: white;
  border-radius: 6px;
  cursor: pointer;
  font-size: 1rem;
}

.qty {
  min-width: 30px;
  text-align: center;
}

.remove-btn {
  background: none;
  border: none;
  color: #ef4444;
  cursor: pointer;
  font-size: 1rem;
  padding: 0 4px;
}

.item-subtotal {
  font-weight: 500;
  font-size: 0.8rem;
  min-width: 80px;
  text-align: right;
}

.empty-cart {
  text-align: center;
  padding: 2rem;
  color: #9ca3af;
}

.empty-cart svg {
  width: 48px;
  height: 48px;
  margin-bottom: 0.5rem;
}

.cart-total {
  padding: 1rem;
  border-top: 1px solid #e5e7eb;
  background: #f9fafb;
}

.total-row {
  display: flex;
  justify-content: space-between;
  margin-bottom: 0.75rem;
}

.total-row span:first-child {
  color: #6b7280;
}

.total-amount {
  font-weight: bold;
  font-size: 1.1rem;
  color: #1f3864;
}

.payment-input {
  width: 150px;
  padding: 0.375rem 0.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  text-align: right;
}

.change {
  border-top: 1px solid #e5e7eb;
  padding-top: 0.75rem;
  margin-top: 0.25rem;
}

.change-amount {
  font-weight: bold;
  color: #10b981;
}

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
  margin-top: 0.5rem;
}

.btn-checkout:hover:not(:disabled) {
  background: #15284d;
}

.btn-checkout:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Modal Styles */
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
  width: 400px;
  max-width: 90%;
  overflow: hidden;
}

.receipt-modal {
  width: 350px;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-body {
  padding: 1rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 0.5rem;
  padding: 1rem;
  border-top: 1px solid #e5e7eb;
}

.modal-input {
  width: 100%;
  padding: 0.625rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
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

.close-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  cursor: pointer;
}

/* Receipt */
.receipt-content {
  text-align: center;
}

.receipt-header h2 {
  font-size: 1.1rem;
  margin-bottom: 0.25rem;
}

.receipt-header p {
  font-size: 0.7rem;
  color: #6b7280;
}

.receipt-items {
  text-align: left;
  margin: 1rem 0;
}

.receipt-item {
  display: flex;
  justify-content: space-between;
  font-size: 0.75rem;
  margin-bottom: 0.25rem;
}

.receipt-total,
.receipt-payment,
.receipt-change {
  display: flex;
  justify-content: space-between;
  margin: 0.5rem 0;
}

.receipt-footer {
  margin-top: 1rem;
  font-size: 0.7rem;
  color: #6b7280;
}

/* Pending Item */
.pending-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  border-bottom: 1px solid #e5e7eb;
}

.pending-status {
  font-size: 0.7rem;
  padding: 0.25rem 0.5rem;
  border-radius: 20px;
}

.pending-status.pending {
  background: #fef3c7;
  color: #d97706;
}

.empty-state {
  text-align: center;
  padding: 2rem;
  color: #9ca3af;
}

/* Responsive */
@media (max-width: 768px) {
  .pos-main {
    margin-left: 70px;
  }
  .pos-content {
    flex-direction: column;
  }
  .product-grid {
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  }
}
</style>
