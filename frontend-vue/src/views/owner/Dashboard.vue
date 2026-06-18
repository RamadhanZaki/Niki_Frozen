<template>
  <div class="dashboard-container">
    <SidebarOwner />

    <main class="main-content">
      <div class="top-bar">
        <div class="page-title">
          <h1>Dashboard</h1>
          <p>Selamat datang, {{ userName }}</p>
        </div>
        <div class="top-bar-right">
          <div
            class="connection-status"
            :class="{ online: isOnline, offline: !isOnline }"
          >
            <span class="status-dot"></span>
            <span>{{ isOnline ? "Online" : "Offline" }}</span>
          </div>
          <div class="user-avatar">
            <span>{{ userInitial }}</span>
          </div>
        </div>
      </div>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon revenue">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
          <div class="stat-info">
            <p class="stat-label">Pendapatan Hari Ini</p>
            <p class="stat-value">Rp {{ formatNumber(totalRevenue) }}</p>
            <p class="stat-change" :class="revenueChange >= 0 ? 'positive' : 'negative'">
              {{ revenueChange >= 0 ? '↑' : '↓' }} {{ Math.abs(revenueChange) }}% dari kemarin
            </p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon transactions">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
              />
            </svg>
          </div>
          <div class="stat-info">
            <p class="stat-label">Total Transaksi</p>
            <p class="stat-value">{{ totalTransactions }}</p>
            <p class="stat-label-small">Hari ini</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon expired">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="stat-info">
            <p class="stat-label">Produk Hampir Expired</p>
            <p class="stat-value danger">{{ expiringProducts }}</p>
            <p class="stat-label-small">Kurang dari 7 hari</p>
          </div>
        </div>

        <div class="stat-card">
          <div class="stat-icon cash-diff">
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
          </div>
          <div class="stat-info">
            <p class="stat-label">Selisih Kas</p>
            <p class="stat-value warning">
              Rp {{ formatNumber(totalDifference) }}
            </p>
            <p class="stat-label-small">Hari ini</p>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3>Performa Cabang Hari Ini</h3>
          <select v-model="selectedBranch" class="branch-filter">
            <option value="all">Semua Cabang</option>
            <option v-for="b in branches" :key="b.id" :value="b.id.toString()">{{ b.name }}</option>
          </select>
        </div>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Cabang</th>
                <th>Pendapatan</th>
                <th>Transaksi</th>
                <th>Rata-rata</th>
                <th>Selisih Kas</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="branch in filteredBranches" :key="branch.id">
                <td class="branch-name">{{ branch.name }}</td>
                <td class="revenue">Rp {{ formatNumber(branch.revenue) }}</td>
                <td>{{ branch.transactions }}</td>
                <td>Rp {{ formatNumber(branch.average) }}</td>
                <td
                  :class="
                    branch.difference < 0
                      ? 'negative'
                      : branch.difference > 0
                        ? 'positive'
                        : 'zero'
                  "
                >
                  Rp {{ formatNumber(Math.abs(branch.difference)) }}
                  <span v-if="branch.difference !== 0" class="diff-sign">
                    {{ branch.difference < 0 ? "(Kurang)" : "(Lebih)" }}
                  </span>
                </td>
                <td>
                  <span
                    class="status-badge"
                    :class="branch.status === 'active' ? 'active' : 'closed'"
                  >
                    {{ branch.status === "active" ? "Aktif" : "Tutup" }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="two-columns">
        <div class="card">
          <div class="card-header">
            <h3>⚠️ Produk Mendekati Expired</h3>
            <button class="link-btn" @click="viewAllExpired">
              Lihat semua
            </button>
          </div>
          <div class="product-list">
            <div
              v-for="product in expiredProductsList"
              :key="product.id"
              class="product-item"
            >
              <div class="product-info">
                <p class="product-name">{{ product.name }}</p>
                <p class="product-branch">{{ product.branch_name }}</p>
              </div>
              <div
                class="product-expiry"
                :class="product.days_left <= 3 ? 'urgent' : 'warning'"
              >
                <span>{{ product.days_left }} hari lagi</span>
                <small>Exp: {{ formatDate(product.expired_date) }}</small>
              </div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <h3>💰 Selisih Kas Terbaru</h3>
            <button class="link-btn" @click="viewAllDifferences">
              Lihat semua
            </button>
          </div>
          <div class="difference-list">
            <div
              v-for="diff in cashDifferencesList"
              :key="diff.id"
              class="difference-item"
            >
              <div class="difference-info">
                <p class="branch-name">{{ diff.branch_name }}</p>
                <p class="difference-detail">
                  {{ diff.shift_date }} | Kasir: {{ diff.cashier_name }}
                </p>
              </div>
              <div
                class="difference-amount"
                :class="diff.difference < 0 ? 'negative' : 'positive'"
              >
                {{ diff.difference < 0 ? "Kurang" : "Lebih" }} Rp
                {{ formatNumber(Math.abs(diff.difference)) }}
                <small>{{ diff.time_ago }}</small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRouter } from "vue-router";
import SidebarOwner from "../../components/SidebarOwner.vue";
import api from "../../services/axios.js";

const router = useRouter();

const isOnline        = ref(navigator.onLine);
const selectedBranch  = ref("all");
const userName        = ref("Owner");
const isLoading       = ref(false);

const totalRevenue       = ref(0);
const totalTransactions  = ref(0);
const expiringProducts   = ref(0);
const totalDifference    = ref(0);
const revenueChange      = ref(0);

const branches           = ref([]);
const expiredProductsList= ref([]);
const cashDifferencesList= ref([]);

const userInitial = computed(() => userName.value.charAt(0));

const filteredBranches = computed(() => {
  if (selectedBranch.value === "all") return branches.value;
  return branches.value.filter((b) => b.id.toString() === selectedBranch.value);
});

const formatNumber = (num) => new Intl.NumberFormat("id-ID").format(num);
const formatDate   = (date) => new Date(date).toLocaleDateString("id-ID");

const viewAllExpired     = () => router.push("/admin/products");
const viewAllDifferences = () => router.push("/admin/shifts");

const updateOnlineStatus = () => { isOnline.value = navigator.onLine; };

const fetchDashboard = async () => {
  isLoading.value = true;
  try {
    const res = await api.get("/dashboard");
    totalRevenue.value        = res.data.total_revenue;
    totalTransactions.value   = res.data.total_transactions;
    expiringProducts.value    = res.data.expiring_products;
    totalDifference.value     = res.data.total_difference;
    revenueChange.value       = res.data.revenue_change ?? 0;
    branches.value            = res.data.branches;
    expiredProductsList.value = res.data.expired_products_list;
    cashDifferencesList.value = res.data.cash_differences_list;
  } catch (err) {
    console.error("Gagal memuat dashboard:", err);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  window.addEventListener("online",  updateOnlineStatus);
  window.addEventListener("offline", updateOnlineStatus);
  const user = JSON.parse(localStorage.getItem("user") || "{}");
  if (user.name) userName.value = user.name;
  fetchDashboard();
});

onUnmounted(() => {
  window.removeEventListener("online",  updateOnlineStatus);
  window.removeEventListener("offline", updateOnlineStatus);
});
</script>

<style scoped>
.dashboard-container {
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
  display: flex;
  gap: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-icon svg {
  width: 24px;
  height: 24px;
}

.stat-icon.revenue {
  background: #dbeafe;
  color: #1e40af;
}

.stat-icon.transactions {
  background: #d1fae5;
  color: #065f46;
}

.stat-icon.expired {
  background: #fed7aa;
  color: #9a3412;
}

.stat-icon.cash-diff {
  background: #fee2e2;
  color: #991b1b;
}

.stat-info {
  flex: 1;
}

.stat-label {
  font-size: 0.75rem;
  color: #6b7280;
  margin-bottom: 0.25rem;
}

.stat-value {
  font-size: 1.25rem;
  font-weight: 700;
  color: #1f2937;
}

.stat-value.danger {
  color: #dc2626;
}

.stat-value.warning {
  color: #d97706;
}

.stat-change {
  font-size: 0.7rem;
  margin-top: 0.25rem;
}

.stat-change.positive {
  color: #10b981;
}

.stat-label-small {
  font-size: 0.7rem;
  color: #9ca3af;
  margin-top: 0.25rem;
}

.card {
  background: white;
  border-radius: 12px;
  margin-bottom: 1.5rem;
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
  color: #1f2937;
}

.branch-filter {
  padding: 0.375rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  font-size: 0.8rem;
  background: white;
}

.link-btn {
  background: none;
  border: none;
  color: #2e75b6;
  font-size: 0.75rem;
  cursor: pointer;
}

.link-btn:hover {
  text-decoration: underline;
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
  font-size: 0.75rem;
  font-weight: 600;
  color: #6b7280;
  text-transform: uppercase;
}

.data-table td {
  font-size: 0.875rem;
  color: #374151;
}

.branch-name {
  font-weight: 500;
}

.revenue {
  font-weight: 500;
}

.negative {
  color: #dc2626;
}

.positive {
  color: #d97706;
}

.zero {
  color: #10b981;
}

.diff-sign {
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

.status-badge.closed {
  background: #f3f4f6;
  color: #6b7280;
}

.two-columns {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.product-list,
.difference-list {
  padding: 0.5rem;
}

.product-item,
.difference-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  border-bottom: 1px solid #f3f4f6;
}

.product-item:last-child,
.difference-item:last-child {
  border-bottom: none;
}

.product-name {
  font-weight: 500;
  font-size: 0.875rem;
  color: #1f2937;
}

.product-branch {
  font-size: 0.7rem;
  color: #6b7280;
}

.product-expiry {
  text-align: right;
}

.product-expiry span {
  display: block;
  font-size: 0.8rem;
  font-weight: 500;
}

.product-expiry small {
  font-size: 0.65rem;
}

.product-expiry.urgent span {
  color: #dc2626;
}

.product-expiry.warning span {
  color: #d97706;
}

.difference-info .branch-name {
  font-weight: 500;
  font-size: 0.875rem;
}

.difference-detail {
  font-size: 0.7rem;
  color: #6b7280;
}

.difference-amount {
  text-align: right;
  font-weight: 500;
  font-size: 0.875rem;
}

.difference-amount small {
  display: block;
  font-size: 0.65rem;
  font-weight: normal;
}

.difference-amount.negative {
  color: #dc2626;
}

.difference-amount.positive {
  color: #d97706;
}

@media (max-width: 768px) {
  .main-content {
    margin-left: 70px;
    padding: 1rem;
  }
  .stats-grid {
    grid-template-columns: 1fr;
  }
  .two-columns {
    grid-template-columns: 1fr;
  }
}
</style>