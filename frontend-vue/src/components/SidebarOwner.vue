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
      <span class="role-badge owner">Owner</span>
    </div>

    <nav class="sidebar-nav">
      <a
        href="#"
        class="nav-item"
        :class="{ active: isActive('/dashboard') }"
        @click.prevent="navigate('/dashboard')"
      >
        <svg
          class="nav-icon"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <path
            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
          />
        </svg>
        <span>Dashboard</span>
      </a>
      <a
        href="#"
        class="nav-item"
        :class="{ active: isActive('/admin/products') }"
        @click.prevent="navigate('/admin/products')"
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
        :class="{ active: isActive('/admin/stocks') }"
        @click.prevent="navigate('/admin/stocks')"
      >
        <svg
          class="nav-icon"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
        </svg>
        <span>Stok</span>
      </a>
      <a
        href="#"
        class="nav-item"
        :class="{ active: isActive('/admin/reports') }"
        @click.prevent="navigate('/admin/reports')"
      >
        <svg
          class="nav-icon"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <path
            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
          />
        </svg>
        <span>Laporan</span>
      </a>
      <a
        href="#"
        class="nav-item"
        :class="{ active: isActive('/admin/users') }"
        @click.prevent="navigate('/admin/users')"
      >
        <svg
          class="nav-icon"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <path
            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"
          />
        </svg>
        <span>Pengguna</span>
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
          <p class="user-role">Owner</p>
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

const isOnline = ref(navigator.onLine);
const userName = ref("Owner Nicky Frozen");

const userInitial = computed(() => {
  return userName.value.charAt(0);
});

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

const updateOnlineStatus = () => {
  isOnline.value = navigator.onLine;
};

onMounted(() => {
  window.addEventListener("online", updateOnlineStatus);
  window.addEventListener("offline", updateOnlineStatus);
  const user = JSON.parse(localStorage.getItem("user") || "{}");
  if (user.name) {
    userName.value = user.name;
  }
});

onUnmounted(() => {
  window.removeEventListener("online", updateOnlineStatus);
  window.removeEventListener("offline", updateOnlineStatus);
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
}

.role-badge.owner {
  background: #f59e0b;
  color: #1a1a2e;
}

.sidebar-nav {
  flex: 1;
  padding: 1rem;
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
  color: rgba(255, 255, 255, 0.6);
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

@media (max-width: 768px) {
  .sidebar {
    width: 70px;
  }

  .sidebar-header h2,
  .role-badge,
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
