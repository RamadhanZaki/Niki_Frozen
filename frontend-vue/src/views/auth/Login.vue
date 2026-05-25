<template>
  <div class="login-container">
    <div class="login-card">
      <!-- Header -->
      <div class="login-header">
        <div class="logo-wrapper">
          <div class="logo">
            <svg class="logo-icon" viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
            </svg>
          </div>
        </div>
        <h1 class="login-title">POS & Monitoring Keuangan</h1>
        <p class="login-subtitle">Nicky Frozen</p>
      </div>

      <!-- Form Body -->
      <div class="login-body">
        
        <!-- Connection Status -->
        <div v-if="!isOnline" class="alert alert-warning">
          <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
          </svg>
          <span>Mode Offline - Login akan menggunakan data lokal</span>
        </div>

        <!-- Error Alert -->
        <div v-if="errorMessage" class="alert alert-error">
          <svg class="alert-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span>{{ errorMessage }}</span>
        </div>

        <!-- Form -->
        <form @submit.prevent="handleLogin">
          
          <!-- Email Field -->
          <div class="form-group">
            <label class="form-label">Email</label>
            <div class="input-wrapper">
              <span class="input-icon">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                </svg>
              </span>
              <input
                v-model="form.email"
                type="email"
                class="form-input"
                :class="{ 'input-error': errors.email }"
                placeholder="owner@nicksfrozen.com"
              />
            </div>
            <p v-if="errors.email" class="error-text">{{ errors.email }}</p>
          </div>

          <!-- Password Field -->
          <div class="form-group">
            <label class="form-label">Password</label>
            <div class="input-wrapper">
              <span class="input-icon">
                <svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
              </span>
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                class="form-input"
                :class="{ 'input-error': errors.password }"
                placeholder="••••••••"
              />
              <button type="button" class="password-toggle" @click="showPassword = !showPassword">
                <svg v-if="!showPassword" class="icon-small" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <svg v-else class="icon-small" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                </svg>
              </button>
            </div>
            <p v-if="errors.password" class="error-text">{{ errors.password }}</p>
          </div>

          <!-- Remember Me & Forgot -->
          <div class="flex-between">
            <label class="checkbox-label">
              <input type="checkbox" v-model="rememberMe" class="checkbox">
              <span>Ingat Saya</span>
            </label>
            <button type="button" class="forgot-link">Lupa Password?</button>
          </div>

          <!-- Submit Button -->
          <button type="submit" :disabled="isLoading" class="btn-login">
            <span v-if="isLoading" class="spinner"></span>
            <span>{{ isLoading ? 'Memproses...' : 'Login' }}</span>
          </button>
        </form>

        <!-- Demo Credentials -->
        <div class="demo-section">
          <p class="demo-title">Demo Credentials (klik untuk isi otomatis):</p>
          <div class="demo-grid">
            <div class="demo-card" @click="fillDemoOwner">
              <p class="demo-role">👑 Owner</p>
              <p class="demo-email">owner@nicksfrozen.com</p>
              <p class="demo-pass">password</p>
            </div>
            <div class="demo-card" @click="fillDemoKasir">
              <p class="demo-role">🛒 Kasir</p>
              <p class="demo-email">kasir@nicksfrozen.com</p>
              <p class="demo-pass">password</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="login-footer">
        <p>&copy; 2026 NICE GANK - Sistem POS & Monitoring Keuangan</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

// State
const isLoading = ref(false)
const showPassword = ref(false)
const rememberMe = ref(false)
const isOnline = ref(navigator.onLine)
const errorMessage = ref('')

const form = reactive({
  email: '',
  password: ''
})

const errors = reactive({
  email: '',
  password: ''
})

// Demo fill functions
const fillDemoOwner = () => {
  form.email = 'owner@nicksfrozen.com'
  form.password = 'password'
}

const fillDemoKasir = () => {
  form.email = 'kasir@nicksfrozen.com'
  form.password = 'password'
}

// Validate form
const validateForm = () => {
  let isValid = true
  errors.email = ''
  errors.password = ''

  if (!form.email) {
    errors.email = 'Email wajib diisi'
    isValid = false
  } else if (!/\S+@\S+\.\S+/.test(form.email)) {
    errors.email = 'Format email tidak valid'
    isValid = false
  }

  if (!form.password) {
    errors.password = 'Password wajib diisi'
    isValid = false
  }

  return isValid
}

// Handle login
const handleLogin = async () => {
  if (!validateForm()) return

  isLoading.value = true
  errorMessage.value = ''

  try {
    await new Promise(resolve => setTimeout(resolve, 1000))

    const demoUsers = [
      { email: 'owner@nicksfrozen.com', password: 'password', role: 'owner', name: 'Owner Nicky Frozen' },
      { email: 'kasir@nicksfrozen.com', password: 'password', role: 'kasir', name: 'Kasir Cabang Utama' }
    ]

    const user = demoUsers.find(u => u.email === form.email && u.password === form.password)

    if (user) {
      if (rememberMe.value) {
        localStorage.setItem('remembered_email', form.email)
      } else {
        localStorage.removeItem('remembered_email')
      }

      localStorage.setItem('token', 'demo_token_' + Date.now())
      localStorage.setItem('user', JSON.stringify({
        name: user.name,
        email: user.email,
        role: user.role
      }))

      if (user.role === 'owner') {
        router.push('/dashboard')
      } else {
        router.push('/pos')
      }
    } else {
      errorMessage.value = 'Email atau password salah'
    }
  } catch (error) {
    errorMessage.value = 'Terjadi kesalahan, silakan coba lagi'
  } finally {
    isLoading.value = false
  }
}

const updateOnlineStatus = () => {
  isOnline.value = navigator.onLine
}

onMounted(() => {
  const savedEmail = localStorage.getItem('remembered_email')
  if (savedEmail) {
    form.email = savedEmail
    rememberMe.value = true
  }
  window.addEventListener('online', updateOnlineStatus)
  window.addEventListener('offline', updateOnlineStatus)
})

onUnmounted(() => {
  window.removeEventListener('online', updateOnlineStatus)
  window.removeEventListener('offline', updateOnlineStatus)
})
</script>

<style scoped>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

.login-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #1F3864 0%, #2E75B6 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
}

.login-card {
  max-width: 28rem;
  width: 100%;
  background: white;
  border-radius: 1rem;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

/* Header */
.login-header {
  background: #1F3864;
  padding: 1.5rem;
  text-align: center;
}

.logo-wrapper {
  display: flex;
  justify-content: center;
  margin-bottom: 1rem;
}

.logo {
  width: 70px;
  height: 70px;
  background: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.logo-icon {
  width: 40px;
  height: 40px;
  color: #1F3864;
}

.login-title {
  font-size: 1.25rem;
  font-weight: bold;
  color: white;
}

.login-subtitle {
  font-size: 0.875rem;
  color: #90caf9;
  margin-top: 0.25rem;
}

/* Body */
.login-body {
  padding: 1.5rem;
}

/* Alerts */
.alert {
  margin-bottom: 1rem;
  padding: 0.625rem 0.75rem;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.alert-warning {
  background: #fef3c7;
  border: 1px solid #fde68a;
}

.alert-warning span {
  color: #92400e;
  font-size: 0.75rem;
}

.alert-error {
  background: #fee2e2;
  border: 1px solid #fecaca;
}

.alert-error span {
  color: #dc2626;
  font-size: 0.75rem;
}

.alert-icon {
  width: 18px;
  height: 18px;
  flex-shrink: 0;
}

/* Form */
.form-group {
  margin-bottom: 1rem;
}

.form-label {
  display: block;
  color: #374151;
  font-size: 0.8rem;
  font-weight: 600;
  margin-bottom: 0.375rem;
}

.input-wrapper {
  position: relative;
}

.input-icon {
  position: absolute;
  left: 0;
  top: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  padding-left: 0.75rem;
}

.icon {
  width: 18px;
  height: 18px;
  color: #9ca3af;
}

.icon-small {
  width: 16px;
  height: 16px;
  color: #9ca3af;
}

.form-input {
  width: 100%;
  padding: 0.625rem 0.75rem 0.625rem 2.25rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  transition: all 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: #1F3864;
  box-shadow: 0 0 0 2px rgba(31, 56, 100, 0.1);
}

.input-error {
  border-color: #ef4444;
}

.password-toggle {
  position: absolute;
  right: 0;
  top: 0;
  bottom: 0;
  display: flex;
  align-items: center;
  padding-right: 0.75rem;
  background: none;
  border: none;
  cursor: pointer;
}

.error-text {
  color: #ef4444;
  font-size: 0.7rem;
  margin-top: 0.25rem;
}

/* Remember me */
.flex-between {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.25rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  cursor: pointer;
}

.checkbox {
  width: 14px;
  height: 14px;
  margin-right: 0.5rem;
}

.checkbox-label span {
  font-size: 0.75rem;
  color: #4b5563;
}

.forgot-link {
  background: none;
  border: none;
  color: #1F3864;
  font-size: 0.75rem;
  cursor: pointer;
}

.forgot-link:hover {
  text-decoration: underline;
}

/* Login Button */
.btn-login {
  width: 100%;
  background: #1F3864;
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
  padding: 0.625rem;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: background 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.btn-login:hover {
  background: #15284D;
}

.btn-login:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.spinner {
  display: inline-block;
  width: 14px;
  height: 14px;
  border: 2px solid white;
  border-radius: 50%;
  border-top-color: transparent;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Demo Section */
.demo-section {
  margin-top: 1.25rem;
  padding-top: 1.25rem;
  border-top: 1px solid #e5e7eb;
}

.demo-title {
  font-size: 0.7rem;
  text-align: center;
  color: #6b7280;
  margin-bottom: 0.75rem;
}

.demo-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.5rem;
}

.demo-card {
  padding: 0.5rem;
  background: #f9fafb;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: background 0.2s;
  text-align: center;
}

.demo-card:hover {
  background: #f3f4f6;
}

.demo-role {
  font-weight: 600;
  color: #1F3864;
  font-size: 0.75rem;
}

.demo-email {
  color: #4b5563;
  font-size: 0.65rem;
}

.demo-pass {
  color: #9ca3af;
  font-size: 0.65rem;
}

/* Footer */
.login-footer {
  background: #f9fafb;
  padding: 0.75rem 1.5rem;
  text-align: center;
}

.login-footer p {
  font-size: 0.7rem;
  color: #6b7280;
}
</style>