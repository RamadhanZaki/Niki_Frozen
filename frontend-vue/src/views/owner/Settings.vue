<template>
  <div class="settings-container">
    <SidebarOwner />

    <main class="main-content">
      <div class="top-bar">
        <div class="page-title">
          <h1>Pengaturan</h1>
          <p>Konfigurasi toko, laporan, dan notifikasi</p>
        </div>
        <div class="top-bar-right">
          <div class="user-avatar"><span>{{ userInitial }}</span></div>
        </div>
      </div>

      <div v-if="isLoading" class="loading-state">⏳ Memuat pengaturan...</div>

      <div v-if="!isLoading" class="settings-grid">

        <!-- Informasi Toko -->
        <div class="card">
          <div class="card-header">
            <h3>🏪 Informasi Toko</h3>
          </div>
          <div class="card-body">
            <div class="form-group">
              <label>Nama Toko</label>
              <input v-model="form.store_name" type="text" class="form-input" placeholder="Niki Frozen" />
            </div>
            <div class="form-group">
              <label>Alamat Toko</label>
              <textarea v-model="form.store_address" class="form-input" rows="2" placeholder="Alamat lengkap toko"></textarea>
            </div>
            <div class="form-group">
              <label>No. Telepon</label>
              <input v-model="form.store_phone" type="text" class="form-input" placeholder="0274-xxxx" />
            </div>
          </div>
        </div>

        <!-- Pengaturan Laporan -->
        <div class="card">
          <div class="card-header">
            <h3>📊 Pengaturan Laporan</h3>
          </div>
          <div class="card-body">
            <div class="form-group">
              <label>Estimasi Margin Laba (%)</label>
              <div class="input-addon">
                <input v-model.number="form.profit_margin" type="number" class="form-input" min="0" max="100" />
                <span class="addon">%</span>
              </div>
              <p class="hint">Digunakan untuk menghitung estimasi laba bersih di laporan keuangan.</p>
            </div>
          </div>
        </div>

        <!-- Pengaturan Stok & Produk -->
        <div class="card">
          <div class="card-header">
            <h3>📦 Pengaturan Stok & Produk</h3>
          </div>
          <div class="card-body">
            <div class="form-group">
              <label>Batas Stok Menipis (pcs)</label>
              <input v-model.number="form.low_stock_threshold" type="number" class="form-input" min="1" />
              <p class="hint">Produk dengan stok di bawah angka ini ditandai sebagai "Stok Menipis".</p>
            </div>
            <div class="form-group">
              <label>Peringatan Expired (hari)</label>
              <input v-model.number="form.expiry_warning_days" type="number" class="form-input" min="1" />
              <p class="hint">Produk akan muncul di peringatan expired N hari sebelum tanggal kadaluarsa.</p>
            </div>
          </div>
        </div>

      </div>

      <!-- Tombol Simpan -->
      <div class="save-bar" v-if="!isLoading">
        <button class="btn-save" @click="saveSettings" :disabled="isSaving">
          {{ isSaving ? '⏳ Menyimpan...' : '💾 Simpan Pengaturan' }}
        </button>
      </div>
    </main>

    <!-- Alert Toast -->
    <div v-if="showAlert" class="alert-toast" :class="alertType">
      <span>{{ alertMessage }}</span>
      <button @click="showAlert = false">✕</button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import SidebarOwner from "../../components/SidebarOwner.vue";
import api from "../../services/axios.js";

const isLoading = ref(false);
const isSaving  = ref(false);
const showAlert = ref(false);
const alertMessage = ref("");
const alertType    = ref("success");

const userName    = ref("Owner");
const userInitial = computed(() => userName.value.charAt(0));

const form = ref({
  store_name:          "Niki Frozen",
  store_address:       "",
  store_phone:         "",
  profit_margin:       25,
  low_stock_threshold: 10,
  expiry_warning_days: 7,
});

const fetchSettings = async () => {
  isLoading.value = true;
  try {
    const res = await api.get("/settings");
    const s   = res.data.settings;
    Object.keys(form.value).forEach((key) => {
      if (s[key] !== undefined) {
        const type = s[key].type;
        const val  = s[key].value;
        form.value[key] = type === "number" ? Number(val) : val;
      }
    });
  } catch (err) {
    showAlertMessage("Gagal memuat pengaturan.", "error");
  } finally {
    isLoading.value = false;
  }
};

const saveSettings = async () => {
  isSaving.value = true;
  try {
    const settings = Object.entries(form.value).map(([key, value]) => ({ key, value: String(value) }));
    await api.put("/settings", { settings });
    showAlertMessage("Pengaturan berhasil disimpan!", "success");
  } catch (err) {
    showAlertMessage(err.response?.data?.message || "Gagal menyimpan pengaturan.", "error");
  } finally {
    isSaving.value = false;
  }
};

const showAlertMessage = (message, type) => {
  alertMessage.value = message;
  alertType.value    = type;
  showAlert.value    = true;
  setTimeout(() => { showAlert.value = false; }, 3500);
};

onMounted(() => {
  const user = JSON.parse(localStorage.getItem("user") || "{}");
  if (user.name) userName.value = user.name;
  fetchSettings();
});
</script>

<style scoped>
.settings-container { display: flex; min-height: 100vh; background: #f3f4f6; }
.main-content { flex: 1; margin-left: 260px; padding: 1.5rem; }

.top-bar {
  display: flex; justify-content: space-between; align-items: center;
  margin-bottom: 1.5rem; background: white; padding: 1rem 1.5rem;
  border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.page-title h1 { font-size: 1.25rem; color: #1f3864; margin-bottom: 0.25rem; }
.page-title p  { font-size: 0.8rem; color: #6b7280; }
.top-bar-right { display: flex; align-items: center; }
.user-avatar {
  width: 40px; height: 40px; background: #2e75b6; border-radius: 50%;
  display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;
}

.loading-state { text-align: center; padding: 3rem; color: #6b7280; font-size: 0.875rem; }

.settings-grid {
  display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
  gap: 1.5rem; margin-bottom: 1.5rem;
}

.card {
  background: white; border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;
}
.card-header {
  padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb;
}
.card-header h3 { font-size: 0.95rem; font-weight: 600; color: #1f2937; }
.card-body { padding: 1.25rem; }

.form-group { margin-bottom: 1.25rem; }
.form-group:last-child { margin-bottom: 0; }
.form-group label {
  display: block; font-size: 0.8rem; font-weight: 500;
  color: #374151; margin-bottom: 0.4rem;
}
.form-input {
  width: 100%; padding: 0.6rem 0.75rem; border: 1px solid #d1d5db;
  border-radius: 8px; font-size: 0.875rem; color: #1f2937; box-sizing: border-box;
  resize: vertical;
}
.form-input:focus { outline: none; border-color: #2e75b6; box-shadow: 0 0 0 2px rgba(46,117,182,0.15); }

.input-addon { position: relative; display: flex; align-items: center; }
.input-addon .form-input { padding-right: 2.5rem; }
.addon {
  position: absolute; right: 0.75rem; color: #6b7280; font-size: 0.875rem; pointer-events: none;
}

.hint { font-size: 0.72rem; color: #9ca3af; margin-top: 0.35rem; }

.save-bar {
  display: flex; justify-content: flex-end; padding: 1rem 0;
}
.btn-save {
  padding: 0.65rem 1.75rem; background: #1f3864; color: white; border: none;
  border-radius: 8px; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.2s;
}
.btn-save:hover    { background: #2e75b6; }
.btn-save:disabled { opacity: 0.6; cursor: not-allowed; }

.alert-toast {
  position: fixed; bottom: 20px; right: 20px; padding: 1rem 1.5rem;
  border-radius: 8px; display: flex; align-items: center;
  gap: 1rem; z-index: 1100; animation: slideIn 0.3s ease;
}
.alert-toast.success { background: #10b981; color: white; }
.alert-toast.error   { background: #ef4444; color: white; }
.alert-toast button  { background: none; border: none; color: white; cursor: pointer; font-size: 1rem; }
@keyframes slideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

@media (max-width: 768px) {
  .main-content { margin-left: 70px; padding: 1rem; }
  .settings-grid { grid-template-columns: 1fr; }
}
</style>