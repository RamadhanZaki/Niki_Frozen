<template>
  <div class="shifts-container">
    <SidebarOwner />

    <main class="main-content">
      <div class="top-bar">
        <div class="page-title">
          <h1>Data Shift Kasir</h1>
          <p>Rekap seluruh shift beserta selisih kas</p>
        </div>
        <div class="top-bar-right">
          <div class="connection-status" :class="{ online: isOnline, offline: !isOnline }">
            <span class="status-dot"></span>
            <span>{{ isOnline ? "Online" : "Offline" }}</span>
          </div>
          <div class="user-avatar"><span>{{ userInitial }}</span></div>
        </div>
      </div>

      <!-- Summary Cards -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue">📋</div>
          <div class="stat-info">
            <p class="stat-label">Shift Hari Ini</p>
            <p class="stat-value">{{ summary.total_shifts_today }}</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green">✅</div>
          <div class="stat-info">
            <p class="stat-label">Shift Aktif</p>
            <p class="stat-value">{{ summary.active_shifts }}</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon purple">💰</div>
          <div class="stat-info">
            <p class="stat-label">Total Penjualan Hari Ini</p>
            <p class="stat-value">Rp {{ formatNumber(summary.total_sales_today) }}</p>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon" :class="summary.total_difference_today < 0 ? 'red' : 'orange'">⚠️</div>
          <div class="stat-info">
            <p class="stat-label">Selisih Kas Hari Ini</p>
            <p class="stat-value" :class="summary.total_difference_today < 0 ? 'danger' : ''">
              Rp {{ formatNumber(Math.abs(summary.total_difference_today)) }}
              <small v-if="summary.total_difference_today !== 0">
                ({{ summary.total_difference_today < 0 ? 'Kurang' : 'Lebih' }})
              </small>
            </p>
          </div>
        </div>
      </div>

      <!-- Filter -->
      <div class="filter-bar">
        <div class="filter-group">
          <label>Cabang</label>
          <select v-model="filterBranch" class="filter-select">
            <option value="all">Semua Cabang</option>
            <option v-for="b in branches" :key="b.id" :value="b.id.toString()">{{ b.name }}</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Dari Tanggal</label>
          <input type="date" v-model="filterDateFrom" class="date-input" />
        </div>
        <div class="filter-group">
          <label>Sampai Tanggal</label>
          <input type="date" v-model="filterDateTo" class="date-input" />
        </div>
        <div class="filter-group">
          <label>Status</label>
          <select v-model="filterStatus" class="filter-select">
            <option value="all">Semua Status</option>
            <option value="aktif">Aktif</option>
            <option value="selesai">Selesai</option>
          </select>
        </div>
        <button class="btn-filter" @click="fetchShifts">Terapkan</button>
      </div>

      <!-- Loading -->
      <div v-if="isLoading" class="loading-state">⏳ Memuat data shift...</div>

      <!-- Error -->
      <div v-if="errorMsg" class="error-banner">{{ errorMsg }}</div>

      <!-- Table -->
      <div class="card" v-if="!isLoading">
        <div class="card-header">
          <h3>Riwayat Shift</h3>
          <button class="btn-refresh" @click="fetchShifts">🔄 Refresh</button>
        </div>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Kasir</th>
                <th>Cabang</th>
                <th>Buka Shift</th>
                <th>Tutup Shift</th>
                <th>Kas Awal</th>
                <th>Kas Aktual</th>
                <th>Kas Diharapkan</th>
                <th>Selisih</th>
                <th>Total Penjualan</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="shift in shifts" :key="shift.id">
                <td class="cashier-name">{{ shift.cashier_name }}</td>
                <td>{{ shift.branch_name }}</td>
                <td>{{ shift.opened_at ?? '-' }}</td>
                <td>{{ shift.closed_at ?? '-' }}</td>
                <td>Rp {{ formatNumber(shift.opening_cash) }}</td>
                <td>Rp {{ formatNumber(shift.closing_cash) }}</td>
                <td>Rp {{ formatNumber(shift.expected_cash) }}</td>
                <td :class="shift.difference < 0 ? 'text-danger' : shift.difference > 0 ? 'text-warning' : 'text-ok'">
                  <span v-if="shift.difference === 0">-</span>
                  <span v-else>
                    {{ shift.difference < 0 ? '▼' : '▲' }}
                    Rp {{ formatNumber(Math.abs(shift.difference)) }}
                  </span>
                </td>
                <td>Rp {{ formatNumber(shift.total_sales) }}</td>
                <td>
                  <span class="status-badge" :class="shift.status === 'aktif' ? 'active' : 'closed'">
                    {{ shift.status === 'aktif' ? 'Aktif' : 'Selesai' }}
                  </span>
                </td>
              </tr>
              <tr v-if="shifts.length === 0">
                <td colspan="10" class="empty-row">Tidak ada data shift untuk filter yang dipilih.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="pagination" v-if="pagination.last_page > 1">
          <button class="page-btn" @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page <= 1">
            ← Prev
          </button>
          <span class="page-info">
            Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}
            ({{ pagination.total }} data)
          </span>
          <button class="page-btn" @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page">
            Next →
          </button>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import SidebarOwner from "../../components/SidebarOwner.vue";
import api from "../../services/axios.js";

const isOnline    = ref(navigator.onLine);
const isLoading   = ref(false);
const errorMsg    = ref("");

const shifts    = ref([]);
const branches  = ref([]);
const summary   = ref({
  total_shifts_today: 0,
  active_shifts: 0,
  total_sales_today: 0,
  total_difference_today: 0,
});
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });

const filterBranch   = ref("all");
const filterDateFrom = ref("");
const filterDateTo   = ref("");
const filterStatus   = ref("all");

const userName    = ref("Owner");
const userInitial = computed(() => userName.value.charAt(0));

const formatNumber = (n) => new Intl.NumberFormat("id-ID").format(Math.round(n ?? 0));

const fetchShifts = async (page = 1) => {
  isLoading.value = true;
  errorMsg.value  = "";
  try {
    const params = { page };
    if (filterBranch.value !== "all") params.branch_id  = filterBranch.value;
    if (filterDateFrom.value)         params.date_from  = filterDateFrom.value;
    if (filterDateTo.value)           params.date_to    = filterDateTo.value;
    if (filterStatus.value !== "all") params.status     = filterStatus.value;

    const res = await api.get("/shifts", { params });
    shifts.value   = res.data.shifts.data ?? [];
    summary.value  = res.data.summary;
    branches.value = res.data.branches;
    pagination.value = {
      current_page: res.data.shifts.current_page,
      last_page:    res.data.shifts.last_page,
      total:        res.data.shifts.total,
    };
  } catch (err) {
    errorMsg.value = "Gagal memuat data shift. Silakan coba lagi.";
    console.error(err);
  } finally {
    isLoading.value = false;
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) fetchShifts(page);
};

const updateOnlineStatus = () => { isOnline.value = navigator.onLine; };

onMounted(() => {
  window.addEventListener("online",  updateOnlineStatus);
  window.addEventListener("offline", updateOnlineStatus);
  const user = JSON.parse(localStorage.getItem("user") || "{}");
  if (user.name) userName.value = user.name;
  // Default: 30 hari terakhir
  const today = new Date();
  const past  = new Date(today);
  past.setDate(today.getDate() - 30);
  filterDateFrom.value = past.toISOString().split("T")[0];
  filterDateTo.value   = today.toISOString().split("T")[0];
  fetchShifts();
});

onUnmounted(() => {
  window.removeEventListener("online",  updateOnlineStatus);
  window.removeEventListener("offline", updateOnlineStatus);
});
</script>

<style scoped>
.shifts-container { display: flex; min-height: 100vh; background: #f3f4f6; }
.main-content { flex: 1; margin-left: 260px; padding: 1.5rem; }
.top-bar {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 1.5rem; background: white; padding: 1rem 1.5rem;
  border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.page-title h1 { font-size: 1.25rem; color: #1f3864; margin-bottom: 0.25rem; }
.page-title p  { font-size: 0.8rem; color: #6b7280; }
.top-bar-right { display: flex; align-items: center; gap: 1rem; }
.connection-status {
  display: flex; align-items: center; gap: 0.5rem;
  padding: 0.375rem 0.75rem; border-radius: 20px; font-size: 0.75rem;
}
.connection-status.online  { background: #d1fae5; color: #065f46; }
.connection-status.offline { background: #fee2e2; color: #991b1b; }
.status-dot { width: 8px; height: 8px; border-radius: 50%; background: currentColor; }
.user-avatar {
  width: 40px; height: 40px; background: #2e75b6; border-radius: 50%;
  display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;
}

.stats-grid {
  display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin-bottom: 1.5rem;
}
.stat-card {
  background: white; border-radius: 12px; padding: 1rem;
  display: flex; gap: 1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.stat-icon { font-size: 1.5rem; display: flex; align-items: center; justify-content: center; width: 48px; height: 48px; border-radius: 12px; }
.stat-icon.blue   { background: #dbeafe; }
.stat-icon.green  { background: #d1fae5; }
.stat-icon.purple { background: #ede9fe; }
.stat-icon.orange { background: #fed7aa; }
.stat-icon.red    { background: #fee2e2; }
.stat-info { flex: 1; }
.stat-label { font-size: 0.75rem; color: #6b7280; margin-bottom: 0.25rem; }
.stat-value { font-size: 1.1rem; font-weight: 700; color: #1f2937; }
.stat-value.danger { color: #dc2626; }
.stat-value small  { font-size: 0.65rem; font-weight: 400; color: #9ca3af; }

.filter-bar {
  background: white; border-radius: 12px; padding: 1rem 1.5rem; margin-bottom: 1.5rem;
  display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.filter-group { display: flex; flex-direction: column; gap: 0.25rem; }
.filter-group label { font-size: 0.7rem; color: #6b7280; font-weight: 500; }
.filter-select, .date-input {
  padding: 0.5rem; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 0.8rem; background: white;
}
.btn-filter {
  padding: 0.5rem 1rem; background: #1f3864; color: white;
  border: none; border-radius: 8px; cursor: pointer; font-size: 0.8rem;
}

.loading-state { text-align: center; padding: 2rem; color: #6b7280; font-size: 0.875rem; }
.error-banner  { background: #fee2e2; color: #991b1b; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.875rem; }

.card { background: white; border-radius: 12px; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
.card-header {
  display: flex; justify-content: space-between; align-items: center;
  padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb;
}
.card-header h3 { font-size: 1rem; font-weight: 600; color: #1f2937; }
.btn-refresh { background: none; border: none; color: #2e75b6; font-size: 0.8rem; cursor: pointer; }

.table-responsive { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; }
.data-table th, .data-table td { padding: 0.7rem 1rem; text-align: left; border-bottom: 1px solid #e5e7eb; }
.data-table th { background: #f9fafb; font-size: 0.68rem; font-weight: 600; color: #6b7280; text-transform: uppercase; }
.data-table td { font-size: 0.82rem; color: #374151; }
.cashier-name { font-weight: 500; }
.text-danger  { color: #dc2626; font-weight: 500; }
.text-warning { color: #d97706; font-weight: 500; }
.text-ok      { color: #10b981; }
.empty-row    { text-align: center; padding: 2rem; color: #9ca3af; }

.status-badge { padding: 0.25rem 0.6rem; border-radius: 20px; font-size: 0.7rem; }
.status-badge.active { background: #d1fae5; color: #065f46; }
.status-badge.closed { background: #f3f4f6; color: #6b7280; }

.pagination {
  display: flex; justify-content: center; align-items: center;
  gap: 1rem; padding: 1rem; border-top: 1px solid #e5e7eb;
}
.page-btn {
  padding: 0.5rem 1rem; background: #f3f4f6; border: 1px solid #e5e7eb;
  border-radius: 6px; cursor: pointer; font-size: 0.8rem;
}
.page-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.page-info { font-size: 0.8rem; color: #6b7280; }

@media (max-width: 768px) {
  .main-content { margin-left: 70px; padding: 1rem; }
  .stats-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>