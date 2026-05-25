<template>
  <aside class="sidebar">
    <div class="sidebar-header">
      <div class="logo-small">
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path
            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15h-2v-2h2v2zm0-4h-2V7h2v6z"
          />
        </svg>
      </div>
      <h2>Nicky Frozen</h2>
      <span class="role-badge kasir">Kasir</span>
    </div>

    <!-- Shift Info (hanya untuk kasir) -->
    <div class="shift-info" :class="{ active: shiftActive }">
      <div class="shift-status">
        <span class="status-dot"></span>
        <span>{{ shiftActive ? "Shift Aktif" : "Shift Tutup" }}</span>
      </div>
      <p class="shift-cash" v-if="shiftActive">
        Kas Awal: Rp {{ formatNumber(openingCash) }}
      </p>
    </div>

    <nav class="sidebar-nav">
      <a
        href="#"
        class="nav-item"
        :class="{ active: isActive('/pos') }"
        @click.prevent="navigate('/pos')"
      >
        <svg
          class="nav-icon"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <path
            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-1.5 6M18 13l1.5 6M9 21h6M12 13v8"
          />
        </svg>
        <span>POS</span>
      </a>

      <a
        href="#"
        class="nav-item"
        :class="{ active: isActive('/shift') }"
        @click.prevent="navigate('/shift')"
      >
        <svg
          class="nav-icon"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>Shift</span>
      </a>

      <a
        href="#"
        class="nav-item"
        :class="{ active: isActive('/transactions') }"
        @click.prevent="navigate('/transactions')"
      >
        <svg
          class="nav-icon"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <path
            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
          />
        </svg>
        <span>Riwayat</span>
      </a>

      <a
        href="#"
        class="nav-item"
        :class="{ active: isActive('/products') }"
        @click.prevent="navigate('/products')"
      >
        <svg
          class="nav-icon"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <path
            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"
          />
        </svg>
        <span>Produk</span>
      </a>

      <a
        href="#"
        class="nav-item"
        :class="{ active: isActive('/offline-queue') }"
        @click.prevent="navigate('/offline-queue')"
      >
        <svg
          class="nav-icon"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <path
            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
          />
        </svg>
        <span>Pending Sync</span>
        <span v-if="pendingCount > 0" class="pending-badge-sidebar">{{
          pendingCount
        }}</span>
      </a>
    </nav>

    <div class="sidebar-footer">
      <div
        class="connection-status"
        :class="{ online: isOnline, offline: !isOnline }"
      >
        <span class="status-dot"></span>
        <span>{{ isOnline ? "Online" : "Offline" }}</span>
      </div>

      <div class="user-info">
        <div class="user-avatar-small">
          <span>{{ userInitial }}</span>
        </div>
        <div class="user-details">
          <p class="user-name">{{ userName }}</p>
          <p class="user-role">Kasir</p>
        </div>
      </div>

      <button @click="handleLogout" class="logout-btn">
        <svg
          class="nav-icon"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <path
            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
          />
        </svg>
        <span>Logout</span>
      </button>
    </div>
  </aside>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRouter, useRoute } from "vue-router";

const router = useRouter();
const route = useRoute();

// State
const isOnline = ref(navigator.onLine);
const userName = ref("Kasir");
const shiftActive = ref(false);
const openingCash = ref(0);
const pendingCount = ref(0);

// Computed
const userInitial = computed(() => {
  return userName.value.charAt(0);
});

// Methods
const formatNumber = (num) => {
  return new Intl.NumberFormat("id-ID").format(num);
};

const isActive = (path) => {
  return route.path === path;
};

const navigate = (path) => {
  router.push(path);
};

const handleLogout = () => {
  localStorage.clear();
  router.push("/login");
};

const loadShiftState = () => {
  const savedShiftActive = localStorage.getItem("shift_active");
  const savedOpeningCash = localStorage.getItem("opening_cash");

  if (savedShiftActive === "true") {
    shiftActive.value = true;
    openingCash.value = Number(savedOpeningCash) || 0;
  }
};

const loadPendingCount = () => {
  const pending = JSON.parse(
    localStorage.getItem("pending_transactions") || "[]",
  );
  pendingCount.value = pending.length;
};

// Connection status
const updateOnlineStatus = () => {
  isOnline.value = navigator.onLine;
};

onMounted(() => {
  window.addEventListener("online", updateOnlineStatus);
  window.addEventListener("offline", updateOnlineStatus);

  // Load user name from localStorage
  const user = JSON.parse(localStorage.getItem("user") || "{}");
  if (user.name) {
    userName.value = user.name;
  }

  loadShiftState();
  loadPendingCount();

  // Listen for storage changes
  window.addEventListener("storage", () => {
    loadShiftState();
    loadPendingCount();
  });
});

onUnmounted(() => {
  window.removeEventListener("online", updateOnlineStatus);
  window.removeEventListener("offline", updateOnlineStatus);
  window.removeEventListener("storage", () => {});
});
</script>

<style scoped>
.sidebar {
  width: 260px;
  background: #1f3864;
  color: white;
  display: flex;
  flex-direction: column;
  position: fixed;
  height: 100vh;
  left: 0;
  top: 0;
  z-index: 100;
}

.sidebar-header {
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  position: relative;
}

.logo-small {
  width: 32px;
  height: 32px;
  background: white;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-small svg {
  width: 20px;
  height: 20px;
  color: #1f3864;
}

.sidebar-header h2 {
  font-size: 1.1rem;
  font-weight: 600;
}

.role-badge {
  position: absolute;
  right: 12px;
  top: 12px;
  font-size: 0.6rem;
  padding: 2px 6px;
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.2);
}

.role-badge.kasir {
  background: #10b981;
  color: white;
}

/* Shift Info */
.shift-info {
  margin: 1rem;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 8px;
  border-left: 3px solid #6b7280;
}

.shift-info.active {
  border-left-color: #4ade80;
  background: rgba(74, 222, 128, 0.1);
}

.shift-status {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  margin-bottom: 0.5rem;
}

.shift-status .status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: #6b7280;
}

.shift-info.active .shift-status .status-dot {
  background: #4ade80;
}

.shift-cash {
  font-size: 0.7rem;
  color: rgba(255, 255, 255, 0.7);
}

.sidebar-nav {
  flex: 1;
  padding: 0 1rem 1rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
  overflow-y: auto;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  color: rgba(255, 255, 255, 0.7);
  text-decoration: none;
  border-radius: 8px;
  transition: all 0.2s;
  cursor: pointer;
  position: relative;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.1);
  color: white;
}

.nav-item.active {
  background: #2e75b6;
  color: white;
}

.nav-icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
}

.nav-item span {
  font-size: 0.85rem;
}

.pending-badge-sidebar {
  margin-left: auto;
  background: #ef4444;
  color: white;
  font-size: 0.65rem;
  padding: 2px 6px;
  border-radius: 10px;
}

.sidebar-footer {
  padding: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.connection-status {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem;
  border-radius: 8px;
  font-size: 0.75rem;
  margin-bottom: 1rem;
}

.connection-status.online {
  background: rgba(74, 222, 128, 0.2);
  color: #4ade80;
}

.connection-status.offline {
  background: rgba(248, 113, 113, 0.2);
  color: #f87171;
}

.status-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: currentColor;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: rgba(255, 255, 255, 0.05);
  border-radius: 8px;
  margin-bottom: 1rem;
}

.user-avatar-small {
  width: 40px;
  height: 40px;
  background: #2e75b6;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 1rem;
}

.user-details {
  flex: 1;
}

.user-name {
  font-size: 0.8rem;
  font-weight: 500;
  margin-bottom: 2px;
}

.user-role {
  font-size: 0.65rem;
  color: rgba(242, 236, 236, 0.6);
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  width: 100%;
  padding: 0.75rem 1rem;
  background: rgba(239, 68, 68, 0.2);
  border: none;
  border-radius: 8px;
  color: #f87171;
  cursor: pointer;
  transition: all 0.2s;
}

.logout-btn:hover {
  background: rgba(239, 68, 68, 0.3);
  color: #ef4444;
}

/* Scrollbar */
.sidebar-nav::-webkit-scrollbar {
  width: 4px;
}

.sidebar-nav::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 4px;
}

.sidebar-nav::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.3);
  border-radius: 4px;
}

/* Responsive */
@media (max-width: 768px) {
  .sidebar {
    width: 70px;
  }

  .sidebar-header h2,
  .role-badge,
  .shift-info,
  .nav-item span,
  .user-details,
  .logout-btn span,
  .connection-status span:last-child {
    display: none;
  }

  .nav-item {
    justify-content: center;
    padding: 0.75rem;
  }

  .sidebar-header {
    justify-content: center;
    padding: 1rem;
  }

  .connection-status {
    justify-content: center;
  }

  .user-info {
    justify-content: center;
    padding: 0.5rem;
  }

  .logout-btn {
    justify-content: center;
  }
}
</style>
