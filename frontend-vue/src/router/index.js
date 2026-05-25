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
    path: '/pos',
    name: 'POS',
    component: () => import('../views/cashier/Pos.vue'),
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
      } else {
        next('/pos')
      }
    } else {
      next()
    }
  } else {
    if (token && to.path === '/login') {
      if (user.role === 'owner') {
        next('/dashboard')
      } else {
        next('/pos')
      }
    } else {
      next()
    }
  }
})

export default router