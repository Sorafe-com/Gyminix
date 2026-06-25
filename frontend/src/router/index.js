import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const routes = [
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/modules/auth/views/LoginView.vue'),
    meta: { public: true },
  },
  {
    path: '/',
    component: () => import('@/layouts/AppLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      { path: '',           redirect: '/dashboard' },
      { path: 'dashboard',  name: 'Dashboard',   component: () => import('@/modules/dashboard/views/DashboardView.vue') },
      { path: 'gyms',       name: 'Gyms',        component: () => import('@/modules/gyms/views/GymsView.vue') },
      { path: 'branches',   name: 'Branches',    component: () => import('@/modules/branches/views/BranchesView.vue') },
      { path: 'users',      name: 'Users',       component: () => import('@/modules/users/views/UsersView.vue') },
      { path: 'roles',      name: 'Roles',       component: () => import('@/modules/roles/views/RolesView.vue') },
      { path: 'permissions',name: 'Permissions', component: () => import('@/modules/roles/views/PermissionsView.vue') },
      { path: 'menus',      name: 'Menus',       component: () => import('@/modules/menus/views/MenusView.vue') },
      { path: 'audit-logs', name: 'AuditLogs',   component: () => import('@/modules/audit/views/AuditView.vue') },
    ],
  },
  { path: '/:pathMatch(.*)*', redirect: '/dashboard' },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  const auth = useAuthStore()
  if (to.meta.requiresAuth && !auth.isAuthenticated) return '/login'
  if (to.meta.public && auth.isAuthenticated)        return '/dashboard'
})

export default router
