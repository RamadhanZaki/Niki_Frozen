import { createRouter, createWebHistory } from 'vue-router'
import Login from '../views/auth/Login.vue'

const routes = [
  {
    path: '/',
    redirect: '/login'
  },
  {
    path: '/login',
    name: 'Login',
    component: Login
  },
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

// Navigation guard
router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const user = JSON.parse(localStorage.getItem('user') || '{}')

  if (to.meta.requiresAuth) {
    if (!token) {
      next('/login')
    } else if (to.meta.role && user.role !== to.meta.role) {
      if (user.role === 'owner') {
        next('/dashboard')
      } else if (user.role === 'kasir') {
        next('/pos')
      } else {
        next('/login')
      }
    } else {
      next()
    }
  } else {
    if (token && to.path === '/login') {
      if (user.role === 'owner') {
        next('/dashboard')
      } else if (user.role === 'kasir') {
        next('/pos')
      } else {
        next()
      }
    } else {
      next()
    }
  }
})

export default router