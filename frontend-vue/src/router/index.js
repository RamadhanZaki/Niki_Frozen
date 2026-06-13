import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/auth/Login.vue'
import api from '../services/axios'

const routes = [
  { path: '/', redirect: '/login' },
  { path: '/login', name: 'Login', component: Login },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('../views/owner/Dashboard.vue'),
    meta: { requiresAuth: true, role: 'owner' }
  },
  {
    path: '/admin/products',
    name: 'Products',
    component: () => import('../views/owner/Products.vue'),
    meta: { requiresAuth: true, role: 'owner' }
  },
  {
    path: '/admin/stocks',
    name: 'Stocks',
    component: () => import('../views/owner/Stocks.vue'),
    meta: { requiresAuth: true, role: 'owner' }
  },
  {
    path: '/admin/reports',
    name: 'Reports',
    component: () => import('../views/owner/Reports.vue'),
    meta: { requiresAuth: true, role: 'owner' }
  },
  {
    path: '/admin/users',
    name: 'Users',
    component: () => import('../views/owner/Users.vue'),
    meta: { requiresAuth: true, role: 'owner' }
  },
  {
    path: '/admin/branches',
    name: 'Branches',
    component: () => import('../views/owner/Branches.vue'),
    meta: { requiresAuth: true, role: 'owner' }
  },
  {
    path: '/admin/shifts',
    name: 'Shifts',
    component: () => import('../views/owner/Shifts.vue'),
    meta: { requiresAuth: true, role: 'owner' }
  },
  {
    path: '/admin/settings',
    name: 'Settings',
    component: () => import('../views/owner/Settings.vue'),
    meta: { requiresAuth: true, role: 'owner' }
  },
  {
    path: '/pos',
    name: 'POS',
    component: () => import('../views/cashier/Pos.vue'),
    meta: { requiresAuth: true, role: 'kasir' }
  },
  {
    path: '/products',
    name: 'CashierProducts',
    component: () => import('../views/cashier/Products.vue'),
    meta: { requiresAuth: true, role: 'kasir' }
  },
  {
    path: '/shift',
    name: 'Shift',
    component: () => import('../views/cashier/Shift.vue'),
    meta: { requiresAuth: true, role: 'kasir' }
  },
  {
    path: '/transactions',
    name: 'Transactions',
    component: () => import('../views/cashier/Transactions.vue'),
    meta: { requiresAuth: true, role: 'kasir' }
  },
  {
    path: '/offline-queue',
    name: 'OfflineQueue',
    component: () => import('../views/cashier/OfflineQueue.vue'),
    meta: { requiresAuth: true, role: 'kasir' }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

async function verifyToken() {
  try {
    const res = await api.get('/me')
    const user = res.data.user
    localStorage.setItem('user', JSON.stringify(user))
    console.log('[Auth] Token valid, user:', user.role)
    return user
  } catch (err) {
    console.warn('[Auth] Token tidak valid atau server mati:', err.message)
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    return null
  }
}

router.beforeEach(async (to, from, next) => {
  const token = localStorage.getItem('token')
  console.log('[Router] Navigasi ke:', to.path, '| token ada:', !!token)

  if (to.meta.requiresAuth) {
    if (!token) {
      console.log('[Router] Tidak ada token → /login')
      return next('/login')
    }

    const user = await verifyToken()

    if (!user) {
      console.log('[Router] Token tidak valid → /login')
      return next('/login')
    }

    if (to.meta.role && user.role !== to.meta.role) {
      const redirect = user.role === 'owner' ? '/dashboard' : '/pos'
      console.log('[Router] Role salah → redirect ke', redirect)
      return next(redirect)
    }

    return next()
  }

  if (to.path === '/login' && token) {
    const user = await verifyToken()
    if (user) {
      const redirect = user.role === 'owner' ? '/dashboard' : '/pos'
      console.log('[Router] Sudah login → redirect ke', redirect)
      return next(redirect)
    }
  }

  next()
})

export default router