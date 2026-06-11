<template>
  <!-- Tahan semua tampilan sampai verifikasi token selesai -->
  <div v-if="isChecking" class="auth-loading">
    <div class="spinner"></div>
  </div>
  <router-view v-else />
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from './services/axios'

const router = useRouter()
const isChecking = ref(true)

onMounted(async () => {
  const token = localStorage.getItem('token')

  if (token) {
    try {
      const res = await api.get('/me')
      const user = res.data.user
      // Token valid — perbarui data user
      localStorage.setItem('user', JSON.stringify(user))
    } catch {
      // Server mati atau token expired → hapus sesi, paksa login
      localStorage.removeItem('token')
      localStorage.removeItem('user')
      await router.replace('/login')
    }
  }

  // Selesai verifikasi — tampilkan halaman
  isChecking.value = false
})
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: #f3f4f6;
}

.auth-loading {
  position: fixed;
  inset: 0;
  background: #1f3864;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid rgba(255, 255, 255, 0.3);
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>