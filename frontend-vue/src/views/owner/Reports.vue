<template>
  <div class="reports-container">
    <SidebarOwner />

    <main class="main-content">
      <!-- Top Bar -->
      <div class="top-bar">
        <div class="page-title">
          <h1>Laporan Keuangan</h1>
          <p>Ringkasan pendapatan, transaksi, dan laba per cabang</p>
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

      <!-- Filter Bar -->
      <div class="filter-bar">
        <div class="filter-group">
          <label>Cabang</label>
          <select v-model="selectedBranch" class="filter-select">
            <option value="all">Semua Cabang</option>
            <option value="1">Cabang Utama</option>
            <option value="2">Cabang Kedua</option>
          </select>
        </div>
        <div class="filter-group">
          <label>Periode</label>
          <select v-model="period" class="filter-select">
            <option value="today">Hari Ini</option>
            <option value="week">Minggu Ini</option>
            <option value="month">Bulan Ini</option>
            <option value="custom">Kustom</option>
          </select>
        </div>
        <div class="filter-group" v-if="period === 'custom'">
          <label>Dari</label>
          <input type="date" v-model="startDate" class="date-input" />
        </div>
        <div class="filter-group" v-if="period === 'custom'">
          <label>Sampai</label>
          <input type="date" v-model="endDate" class="date-input" />
        </div>
        <button class="btn-filter" @click="applyFilter">Terapkan</button>
        <button class="btn-export" @click="exportPDF">
          <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
          >
            <path
              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
          <span>Export PDF</span>
        </button>
      </div>

      <!-- Summary Cards -->
      <div class="summary-grid">
        <div class="summary-card">
          <div class="summary-icon revenue">
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
          <div class="summary-info">
            <p class="summary-label">Total Pendapatan</p>
            <p class="summary-value">Rp {{ formatNumber(totalRevenue) }}</p>
            <p class="summary-sub">Periode terpilih</p>
          </div>
        </div>
        <div class="summary-card">
          <div class="summary-icon transactions">
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
          <div class="summary-info">
            <p class="summary-label">Total Transaksi</p>
            <p class="summary-value">{{ totalTransactions }}</p>
            <p class="summary-sub">Periode terpilih</p>
          </div>
        </div>
        <div class="summary-card">
          <div class="summary-icon profit">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
            </svg>
          </div>
          <div class="summary-info">
            <p class="summary-label">Laba Bersih</p>
            <p class="summary-value">Rp {{ formatNumber(netProfit) }}</p>
            <p class="summary-sub">Estimasi (25% margin)</p>
          </div>
        </div>
        <div class="summary-card">
          <div class="summary-icon average">
            <svg
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
            >
              <path
                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
              />
            </svg>
          </div>
          <div class="summary-info">
            <p class="summary-label">Rata-rata per Transaksi</p>
            <p class="summary-value">
              Rp {{ formatNumber(averageTransaction) }}
            </p>
            <p class="summary-sub">Periode terpilih</p>
          </div>
        </div>
      </div>

      <!-- Chart Section -->
      <div class="card">
        <div class="card-header">
          <h3>Grafik Pendapatan</h3>
          <select v-model="chartType" class="chart-type-select">
            <option value="bar">Bar Chart</option>
            <option value="line">Line Chart</option>
          </select>
        </div>
        <div class="chart-container">
          <canvas ref="revenueChartCanvas"></canvas>
        </div>
      </div>

      <!-- Daily Report Table -->
      <div class="card">
        <div class="card-header">
          <h3>Laporan Harian</h3>
          <button class="btn-refresh" @click="refreshData">🔄 Refresh</button>
        </div>
        <div class="table-responsive">
          <table class="data-table">
            <thead>
              <tr>
                <th>Tanggal</th>
                <th>Cabang</th>
                <th>Total Pendapatan</th>
                <th>Transaksi</th>
                <th>Rata-rata</th>
                <th>Laba (est.)</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(report, index) in paginatedReports" :key="index">
                <td>{{ formatDate(report.date) }}</td>
                <td>{{ report.branch_name }}</td>
                <td class="revenue">Rp {{ formatNumber(report.revenue) }}</td>
                <td>{{ report.transactions }}</td>
                <td>Rp {{ formatNumber(report.average) }}</td>
                <td class="profit-cell">
                  Rp {{ formatNumber(report.profit) }}
                </td>
              </tr>
              <tr v-if="filteredReports.length === 0">
                <td colspan="6" class="empty-row">
                  Tidak ada data laporan untuk periode yang dipilih
                </td>
              </tr>
            </tbody>
            <tfoot v-if="filteredReports.length > 0">
              <tr class="total-row">
                <td colspan="2"><strong>Total</strong></td>
                <td>
                  <strong>Rp {{ formatNumber(totalRevenue) }}</strong>
                </td>
                <td>
                  <strong>{{ totalTransactions }}</strong>
                </td>
                <td>
                  <strong>Rp {{ formatNumber(averageTransaction) }}</strong>
                </td>
                <td>
                  <strong>Rp {{ formatNumber(netProfit) }}</strong>
                </td>
              </tr>
            </tfoot>
          </table>
        </div>
        <!-- Pagination -->
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from "vue";
import { useRouter } from "vue-router";
import SidebarOwner from "../../components/SidebarOwner.vue";

const router = useRouter();

// Reactive state
const isOnline = ref(navigator.onLine);
const selectedBranch = ref("all");
const period = ref("week");
const startDate = ref("");
const endDate = ref("");
const chartType = ref("bar");
const currentPage = ref(1);
const itemsPerPage = ref(10);
let revenueChart = null;

// Dummy data for reports
const allReports = ref([
  {
    date: "2026-05-25",
    branch_id: 1,
    branch_name: "Cabang Utama",
    revenue: 3850000,
    transactions: 142,
    average: 27113,
    profit: 962500,
  },
  {
    date: "2026-05-25",
    branch_id: 2,
    branch_name: "Cabang Kedua",
    revenue: 2940000,
    transactions: 98,
    average: 30000,
    profit: 735000,
  },
  {
    date: "2026-05-24",
    branch_id: 1,
    branch_name: "Cabang Utama",
    revenue: 4200000,
    transactions: 156,
    average: 26923,
    profit: 1050000,
  },
  {
    date: "2026-05-24",
    branch_id: 2,
    branch_name: "Cabang Kedua",
    revenue: 3100000,
    transactions: 103,
    average: 30097,
    profit: 775000,
  },
  {
    date: "2026-05-23",
    branch_id: 1,
    branch_name: "Cabang Utama",
    revenue: 3980000,
    transactions: 148,
    average: 26892,
    profit: 995000,
  },
  {
    date: "2026-05-23",
    branch_id: 2,
    branch_name: "Cabang Kedua",
    revenue: 2870000,
    transactions: 95,
    average: 30211,
    profit: 717500,
  },
  {
    date: "2026-05-22",
    branch_id: 1,
    branch_name: "Cabang Utama",
    revenue: 4100000,
    transactions: 152,
    average: 26974,
    profit: 1025000,
  },
  {
    date: "2026-05-22",
    branch_id: 2,
    branch_name: "Cabang Kedua",
    revenue: 3050000,
    transactions: 101,
    average: 30198,
    profit: 762500,
  },
  {
    date: "2026-05-21",
    branch_id: 1,
    branch_name: "Cabang Utama",
    revenue: 3900000,
    transactions: 145,
    average: 26897,
    profit: 975000,
  },
  {
    date: "2026-05-21",
    branch_id: 2,
    branch_name: "Cabang Kedua",
    revenue: 2980000,
    transactions: 99,
    average: 30101,
    profit: 745000,
  },
]);

// Helper: filter reports by branch and date range
const filteredReports = computed(() => {
  let reports = [...allReports.value];

  // Filter by branch
  if (selectedBranch.value !== "all") {
    reports = reports.filter(
      (r) => r.branch_id.toString() === selectedBranch.value,
    );
  }

  // Filter by date range based on period
  const today = new Date();
  let start = null;
  let end = null;

  if (period.value === "today") {
    start = new Date(today.getFullYear(), today.getMonth(), today.getDate());
    end = new Date(
      today.getFullYear(),
      today.getMonth(),
      today.getDate(),
      23,
      59,
      59,
      999,
    );
  } else if (period.value === "week") {
    // Get Monday of current week
    const day = today.getDay();
    const diffToMonday = day === 0 ? 6 : day - 1;
    start = new Date(today);
    start.setDate(today.getDate() - diffToMonday);
    start.setHours(0, 0, 0, 0);
    end = new Date(today);
    end.setHours(23, 59, 59, 999);
  } else if (period.value === "month") {
    start = new Date(today.getFullYear(), today.getMonth(), 1);
    end = new Date(
      today.getFullYear(),
      today.getMonth() + 1,
      0,
      23,
      59,
      59,
      999,
    );
  } else if (period.value === "custom") {
    if (startDate.value && endDate.value) {
      start = new Date(startDate.value);
      start.setHours(0, 0, 0, 0);
      end = new Date(endDate.value);
      end.setHours(23, 59, 59, 999);
    }
  }

  if (start && end) {
    reports = reports.filter((r) => {
      const rDate = new Date(r.date);
      return rDate >= start && rDate <= end;
    });
  }

  // Sort by date descending
  reports.sort((a, b) => new Date(b.date) - new Date(a.date));
  return reports;
});

// Summary calculations
const totalRevenue = computed(() =>
  filteredReports.value.reduce((sum, r) => sum + r.revenue, 0),
);
const totalTransactions = computed(() =>
  filteredReports.value.reduce((sum, r) => sum + r.transactions, 0),
);
const netProfit = computed(() =>
  filteredReports.value.reduce((sum, r) => sum + r.profit, 0),
);
const averageTransaction = computed(() =>
  totalTransactions.value === 0
    ? 0
    : totalRevenue.value / totalTransactions.value,
);

// Pagination
const totalPages = computed(() =>
  Math.ceil(filteredReports.value.length / itemsPerPage.value),
);
const paginatedReports = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return filteredReports.value.slice(start, end);
});

// User initial
const userName = ref("Owner Nicky Frozen");
const userInitial = computed(() => userName.value.charAt(0));

// Methods
const formatNumber = (num) => new Intl.NumberFormat("id-ID").format(num);
const formatDate = (dateStr) => new Date(dateStr).toLocaleDateString("id-ID");

const applyFilter = () => {
  currentPage.value = 1;
  renderChart();
};

const refreshData = () => renderChart();

const exportPDF = () => {
  alert("Fitur ekspor PDF akan segera tersedia. (Simulasi)");
};

const renderChart = async () => {
  if (revenueChart) revenueChart.destroy();

  // Wait for DOM update
  await nextTick();
  const canvas = revenueChartCanvas.value;
  if (!canvas) return;

  const ctx = canvas.getContext("2d");
  if (!ctx) return;

  // Aggregate revenue per date
  const grouped = {};
  filteredReports.value.forEach((r) => {
    const date = formatDate(r.date);
    grouped[date] = (grouped[date] || 0) + r.revenue;
  });
  const labels = Object.keys(grouped).sort();
  const data = labels.map((l) => grouped[l]);

  revenueChart = new Chart(ctx, {
    type: chartType.value,
    data: {
      labels: labels,
      datasets: [
        {
          label: "Pendapatan (Rp)",
          data: data,
          backgroundColor: "rgba(46, 117, 182, 0.5)",
          borderColor: "#1F3864",
          borderWidth: 2,
          tension: 0.3,
          fill: true,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        tooltip: {
          callbacks: {
            label: (ctx) => `Rp ${formatNumber(ctx.raw)}`,
          },
        },
      },
      scales: {
        y: {
          ticks: {
            callback: (val) => "Rp " + formatNumber(val),
          },
        },
      },
    },
  });
};

const prevPage = () => {
  if (currentPage.value > 1) currentPage.value--;
};
const nextPage = () => {
  if (currentPage.value < totalPages.value) currentPage.value++;
};

// Watch for changes that affect chart
watch([selectedBranch, period, startDate, endDate, chartType], () =>
  applyFilter(),
);

// Online status
const updateOnlineStatus = () => {
  isOnline.value = navigator.onLine;
};

// Chart canvas ref
const revenueChartCanvas = ref(null);

onMounted(() => {
  window.addEventListener("online", updateOnlineStatus);
  window.addEventListener("offline", updateOnlineStatus);

  // Set default custom dates (7 days range)
  const today = new Date();
  const weekAgo = new Date(today);
  weekAgo.setDate(today.getDate() - 7);
  startDate.value = weekAgo.toISOString().split("T")[0];
  endDate.value = today.toISOString().split("T")[0];

  renderChart();
});

onUnmounted(() => {
  window.removeEventListener("online", updateOnlineStatus);
  window.removeEventListener("offline", updateOnlineStatus);
  if (revenueChart) revenueChart.destroy();
});
</script>

<style scoped>
/* (style sama seperti yang Anda berikan, tidak ada perubahan) */
.reports-container {
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
.filter-bar {
  background: white;
  border-radius: 12px;
  padding: 1rem 1.5rem;
  margin-bottom: 1.5rem;
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  align-items: flex-end;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}
.filter-group label {
  font-size: 0.7rem;
  color: #6b7280;
  font-weight: 500;
}
.filter-select,
.date-input {
  padding: 0.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.8rem;
  background: white;
}
.btn-filter,
.btn-export {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 8px;
  border: none;
  cursor: pointer;
  font-size: 0.8rem;
  font-weight: 500;
}
.btn-filter {
  background: #1f3864;
  color: white;
}
.btn-export {
  background: #10b981;
  color: white;
}
.btn-export svg {
  width: 16px;
  height: 16px;
}
.btn-refresh {
  background: none;
  border: none;
  color: #2e75b6;
  cursor: pointer;
  font-size: 0.8rem;
}
.summary-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 1rem;
  margin-bottom: 1.5rem;
}
.summary-card {
  background: white;
  border-radius: 12px;
  padding: 1rem;
  display: flex;
  gap: 1rem;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
.summary-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}
.summary-icon svg {
  width: 24px;
  height: 24px;
}
.summary-icon.revenue {
  background: #dbeafe;
  color: #1e40af;
}
.summary-icon.transactions {
  background: #d1fae5;
  color: #065f46;
}
.summary-icon.profit {
  background: #fed7aa;
  color: #9a3412;
}
.summary-icon.average {
  background: #e0e7ff;
  color: #3730a3;
}
.summary-info {
  flex: 1;
}
.summary-label {
  font-size: 0.7rem;
  color: #6b7280;
  margin-bottom: 0.25rem;
}
.summary-value {
  font-size: 1.2rem;
  font-weight: 700;
  color: #1f2937;
}
.summary-sub {
  font-size: 0.65rem;
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
}
.chart-type-select {
  padding: 0.25rem 0.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  font-size: 0.75rem;
}
.chart-container {
  padding: 1rem;
  height: 350px;
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
.revenue,
.profit-cell {
  font-weight: 500;
}
.total-row {
  background: #f3f4f6;
  font-weight: 600;
}
.empty-row {
  text-align: center;
  padding: 2rem;
  color: #9ca3af;
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
  .summary-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  .filter-bar {
    flex-direction: column;
    align-items: stretch;
  }
  .btn-filter,
  .btn-export {
    justify-content: center;
  }
}
</style>
