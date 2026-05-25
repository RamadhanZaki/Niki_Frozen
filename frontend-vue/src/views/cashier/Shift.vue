<template>
  <div class="shift-container">
    <!-- Sidebar Kasir Component -->
    <SidebarKasir />

    <!-- Main Content -->
    <main class="main-content">
      <!-- Top Bar -->
      <div class="top-bar">
        <div class="page-title">
          <h1>Manajemen Shift</h1>
          <p>Kasir: {{ cashierName }} | Cabang Utama</p>
        </div>
        <div class="user-avatar">
          <span>{{ cashierInitial }}</span>
        </div>
      </div>

      <!-- Current Shift Info -->
      <div class="current-shift-card" v-if="shiftActive">
        <div class="shift-header">
          <div class="shift-status active">
            <span class="status-dot"></span>
            <span>Shift Aktif</span>
          </div>
          <p class="shift-time">Dibuka: {{ shiftOpenTime }}</p>
        </div>

        <div class="shift-stats">
          <div class="stat-item">
            <span class="stat-label">Kas Awal</span>
            <span class="stat-value">Rp {{ formatNumber(openingCash) }}</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">Total Transaksi</span>
            <span class="stat-value">{{ transactionCount }} transaksi</span>
          </div>
          <div class="stat-item">
            <span class="stat-label">Total Penjualan</span>
            <span class="stat-value">Rp {{ formatNumber(totalSales) }}</span>
          </div>
        </div>

        <button class="btn-close-shift" @click="openCloseShiftModal">
          Tutup Shift
        </button>
      </div>

      <!-- No Active Shift -->
      <div class="no-shift-card" v-else>
        <div class="no-shift-icon">
          <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
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
          <select v-model="historyFilter" class="filter-select">
            <option value="week">Minggu Ini</option>
            <option value="month">Bulan Ini</option>
            <option value="all">Semua</option>
          </select>
        </div>

        <div class="table-responsive">
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
                  {{ shift.date }}<br /><small>{{ shift.time }}</small>
                </td>
                <td>Rp {{ formatNumber(shift.opening_cash) }}</td>
                <td>Rp {{ formatNumber(shift.total_sales) }}</td>
                <td>Rp {{ formatNumber(shift.closing_cash) }}</td>
                <td>Rp {{ formatNumber(shift.expected_cash) }}</td>
                <td :class="getDifferenceClass(shift.difference)">
                  {{ shift.difference > 0 ? "+" : "" }}Rp
                  {{ formatNumber(Math.abs(shift.difference)) }}
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
      </div>
    </main>

    <!-- Buka Shift Modal -->
    <div
      v-if="showOpenModal"
      class="modal-overlay"
      @click.self="showOpenModal = false"
    >
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
            <small
              >Masukkan jumlah uang yang ada di kasir saat memulai shift</small
            >
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="showOpenModal = false">
            Batal
          </button>
          <button
            class="btn-primary"
            @click="openShift"
            :disabled="!openingCashInput || openingCashInput <= 0"
          >
            Buka Shift
          </button>
        </div>
      </div>
    </div>

    <!-- Tutup Shift Modal -->
    <div
      v-if="showCloseModal"
      class="modal-overlay"
      @click.self="showCloseModal = false"
    >
      <div class="modal large-modal">
        <div class="modal-header">
          <h3>Tutup Shift</h3>
          <button class="close-btn" @click="showCloseModal = false">✕</button>
        </div>
        <div class="modal-body">
          <div class="shift-summary">
            <div class="summary-row">
              <span>Kas Awal</span>
              <span>Rp {{ formatNumber(openingCash) }}</span>
            </div>
            <div class="summary-row">
              <span>Total Penjualan</span>
              <span>Rp {{ formatNumber(totalSales) }}</span>
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
                  <svg
                    v-if="difference === 0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                  >
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <svg
                    v-else-if="difference < 0"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                  >
                    <path
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                    />
                  </svg>
                  <svg
                    v-else
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                  >
                    <path
                      d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                    />
                  </svg>
                </div>
                <div class="difference-text">
                  <h4 v-if="difference === 0">✓ Match</h4>
                  <h4 v-else-if="difference < 0">
                    Kurang Rp {{ formatNumber(Math.abs(difference)) }}
                  </h4>
                  <h4 v-else>Lebih Rp {{ formatNumber(difference) }}</h4>
                  <p v-if="Math.abs(difference) > 5000" class="warning-text">
                    ⚠️ Selisih melebihi batas toleransi (Rp5.000)
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-secondary" @click="showCloseModal = false">
            Batal
          </button>
          <button
            class="btn-primary"
            @click="closeShift"
            :disabled="!closingCashInput"
          >
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
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import SidebarKasir from "../../components/SidebarKasir.vue";

const router = useRouter();

// State
const isOnline = ref(navigator.onLine);
const shiftActive = ref(false);
const openingCash = ref(0);
const openingCashInput = ref(0);
const closingCashInput = ref(0);
const cashierName = ref("Kasir Cabang Utama");
const shiftOpenTime = ref("");
const totalSales = ref(0);
const transactionCount = ref(0);
const historyFilter = ref("week");
const showOpenModal = ref(false);
const showCloseModal = ref(false);
const showAlert = ref(false);
const alertMessage = ref("");
const alertType = ref("success");

// Shift history
const shiftHistory = ref([
  {
    id: 1,
    date: "25/05/2026",
    time: "09:00",
    opening_cash: 500000,
    total_sales: 3850000,
    closing_cash: 4350000,
    expected_cash: 4350000,
    difference: 0,
    status: "match",
  },
  {
    id: 2,
    date: "24/05/2026",
    time: "09:00",
    opening_cash: 500000,
    total_sales: 4200000,
    closing_cash: 4695000,
    expected_cash: 4700000,
    difference: -5000,
    status: "short",
  },
  {
    id: 3,
    date: "23/05/2026",
    time: "09:00",
    opening_cash: 500000,
    total_sales: 3980000,
    closing_cash: 4490000,
    expected_cash: 4480000,
    difference: 10000,
    status: "over",
  },
]);

// Computed
const cashierInitial = computed(() => {
  return cashierName.value.charAt(0);
});

const expectedCash = computed(() => {
  return openingCash.value + totalSales.value;
});

const difference = computed(() => {
  return closingCashInput.value - expectedCash.value;
});

const differenceClass = computed(() => {
  if (difference.value === 0) return "match";
  if (difference.value < 0) return "short";
  return "over";
});

const filteredShiftHistory = computed(() => {
  if (historyFilter.value === "week") {
    return shiftHistory.value.slice(0, 7);
  }
  if (historyFilter.value === "month") {
    return shiftHistory.value.slice(0, 30);
  }
  return shiftHistory.value;
});

// Methods
const formatNumber = (num) => {
  return new Intl.NumberFormat("id-ID").format(num);
};

const openOpenShiftModal = () => {
  openingCashInput.value = 0;
  showOpenModal.value = true;
};

const openCloseShiftModal = () => {
  closingCashInput.value = 0;
  showCloseModal.value = true;
};

const openShift = () => {
  openingCash.value = openingCashInput.value;
  shiftActive.value = true;
  shiftOpenTime.value = new Date().toLocaleString();
  showOpenModal.value = false;
  showAlertMessage("Shift berhasil dibuka!", "success");

  // Save to localStorage for SidebarKasir to read
  localStorage.setItem("shift_active", "true");
  localStorage.setItem("opening_cash", openingCash.value.toString());
  localStorage.setItem("shift_open_time", shiftOpenTime.value);
};

const closeShift = () => {
  const newShift = {
    id: shiftHistory.value.length + 1,
    date: new Date().toLocaleDateString("id-ID"),
    time: new Date().toLocaleTimeString("id-ID", {
      hour: "2-digit",
      minute: "2-digit",
    }),
    opening_cash: openingCash.value,
    total_sales: totalSales.value,
    closing_cash: closingCashInput.value,
    expected_cash: expectedCash.value,
    difference: difference.value,
    status:
      difference.value === 0
        ? "match"
        : difference.value < 0
          ? "short"
          : "over",
  };

  shiftHistory.value.unshift(newShift);

  if (Math.abs(difference.value) > 5000) {
    showAlertMessage(
      `⚠️ Selisih kas melebihi batas! ${difference.value < 0 ? "Kurang" : "Lebih"} Rp ${formatNumber(Math.abs(difference.value))}`,
      "error",
    );
    if (isOnline.value) {
      console.log("Notifying owner about cash difference...");
    }
  } else {
    showAlertMessage("Shift berhasil ditutup!", "success");
  }

  shiftActive.value = false;
  openingCash.value = 0;
  totalSales.value = 0;
  transactionCount.value = 0;
  showCloseModal.value = false;

  localStorage.removeItem("shift_active");
  localStorage.removeItem("opening_cash");
  localStorage.removeItem("shift_open_time");
};

const showAlertMessage = (message, type) => {
  alertMessage.value = message;
  alertType.value = type;
  showAlert.value = true;
  setTimeout(() => {
    showAlert.value = false;
  }, 3000);
};

const getDifferenceClass = (difference) => {
  if (difference === 0) return "difference-match";
  if (difference < 0) return "difference-short";
  return "difference-over";
};

const getStatusClass = (shift) => {
  if (shift.difference === 0) return "status-match";
  if (shift.difference < 0) return "status-short";
  return "status-over";
};

const getStatusText = (shift) => {
  if (shift.difference === 0) return "Match";
  if (shift.difference < 0) return "Kurang";
  return "Lebih";
};

// Navigation
const goToPos = () => {
  router.push("/pos");
};

const goToTransactions = () => {
  router.push("/transactions");
};

const logout = () => {
  localStorage.clear();
  router.push("/login");
};

// Load saved shift state
const loadShiftState = () => {
  const savedShiftActive = localStorage.getItem("shift_active");
  const savedOpeningCash = localStorage.getItem("opening_cash");
  const savedShiftOpenTime = localStorage.getItem("shift_open_time");

  if (savedShiftActive === "true") {
    shiftActive.value = true;
    openingCash.value = Number(savedOpeningCash) || 0;
    shiftOpenTime.value = savedShiftOpenTime || "";
  }
};

// Load transaction data from POS (simulasi)
const loadTransactionData = () => {
  const savedTransactions = JSON.parse(
    localStorage.getItem("transactions") || "[]",
  );
  const currentShiftTransactions = savedTransactions.filter(
    (t) => t.shift_active === true,
  );
  totalSales.value = currentShiftTransactions.reduce(
    (sum, t) => sum + t.total,
    0,
  );
  transactionCount.value = currentShiftTransactions.length;
};

onMounted(() => {
  loadShiftState();
  loadTransactionData();
});
</script>

<style scoped>
.shift-container {
  display: flex;
  min-height: 100vh;
  background: #f3f4f6;
}

/* Main Content */
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

/* Current Shift Card */
.current-shift-card {
  background: linear-gradient(135deg, #1f3864 0%, #2e75b6 100%);
  border-radius: 16px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  color: white;
}

.shift-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.shift-status {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: rgba(255, 255, 255, 0.2);
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.8rem;
}

.shift-status .status-dot {
  background: #4ade80;
}

.shift-time {
  font-size: 0.8rem;
  opacity: 0.8;
}

.shift-stats {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 12px;
}

.stat-item {
  text-align: center;
}

.stat-label {
  display: block;
  font-size: 0.7rem;
  opacity: 0.8;
  margin-bottom: 0.25rem;
}

.stat-value {
  font-size: 1.1rem;
  font-weight: 600;
}

.btn-close-shift {
  width: 100%;
  padding: 0.75rem;
  background: #ef4444;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-close-shift:hover {
  background: #dc2626;
}

/* No Shift Card */
.no-shift-card {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  text-align: center;
  margin-bottom: 1.5rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.no-shift-icon svg {
  width: 64px;
  height: 64px;
  color: #9ca3af;
  margin-bottom: 1rem;
}

.no-shift-card h3 {
  font-size: 1.1rem;
  margin-bottom: 0.5rem;
}

.no-shift-card p {
  font-size: 0.8rem;
  color: #6b7280;
  margin-bottom: 1rem;
}

.btn-open-shift {
  padding: 0.75rem 1.5rem;
  background: #10b981;
  color: white;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
}

/* Card */
.card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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

.filter-select {
  padding: 0.375rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.8rem;
  background: white;
}

/* Table */
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

.empty-row {
  text-align: center;
  color: #9ca3af;
  padding: 2rem;
}

.difference-match {
  color: #10b981;
  font-weight: 500;
}

.difference-short {
  color: #ef4444;
  font-weight: 500;
}

.difference-over {
  color: #f59e0b;
  font-weight: 500;
}

.status-badge {
  padding: 0.25rem 0.5rem;
  border-radius: 20px;
  font-size: 0.7rem;
}

.status-match {
  background: #d1fae5;
  color: #065f46;
}

.status-short {
  background: #fee2e2;
  color: #991b1b;
}

.status-over {
  background: #fed7aa;
  color: #9a3412;
}

/* Modal */
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
  width: 450px;
  max-width: 90%;
  overflow: hidden;
}

.large-modal {
  width: 500px;
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

.form-group {
  margin-bottom: 1rem;
}

.form-group label {
  display: block;
  font-size: 0.8rem;
  font-weight: 500;
  margin-bottom: 0.5rem;
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
  padding: 0.625rem 0.75rem 0.625rem 2rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.875rem;
}

.modal-input.inline {
  width: 200px;
  margin-left: 8px;
}

.modal-input:focus {
  outline: none;
  border-color: #2e75b6;
}

.form-group small {
  display: block;
  font-size: 0.7rem;
  color: #6b7280;
  margin-top: 0.25rem;
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

/* Shift Summary */
.shift-summary {
  margin-bottom: 1rem;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #f3f4f6;
}

.summary-row.total {
  font-weight: bold;
  font-size: 1rem;
  border-bottom: 2px solid #e5e7eb;
  padding-top: 0.75rem;
  margin-top: 0.25rem;
}

.closing-input {
  display: flex;
  align-items: center;
}

.difference-result {
  margin-top: 1rem;
}

.difference-card {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border-radius: 12px;
}

.difference-card.match {
  background: #d1fae5;
}

.difference-card.short {
  background: #fee2e2;
}

.difference-card.over {
  background: #fed7aa;
}

.difference-icon svg {
  width: 32px;
  height: 32px;
}

.difference-card.match .difference-icon svg {
  color: #10b981;
}

.difference-card.short .difference-icon svg {
  color: #ef4444;
}

.difference-card.over .difference-icon svg {
  color: #f59e0b;
}

.difference-text h4 {
  font-size: 0.9rem;
  margin-bottom: 0.25rem;
}

.warning-text {
  font-size: 0.7rem;
  color: #d97706;
}

/* Alert Toast */
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

/* Responsive */
@media (max-width: 768px) {
  .main-content {
    margin-left: 70px;
    padding: 1rem;
  }

  .shift-stats {
    grid-template-columns: 1fr;
    gap: 0.5rem;
  }

  .stat-item {
    text-align: left;
    display: flex;
    justify-content: space-between;
  }
}
</style>
