import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('@/pages/LoginPage.vue'),
      meta: { requiresAuth: false },
    },
    {
      path: '/',
      component: () => import('@/layouts/DashboardLayout.vue'),
      meta: { requiresAuth: true },
      children: [
        {
          path: '',
          name: 'overview',
          component: () => import('@/pages/OverviewPage.vue'),
        },
        {
          path: 'warga',
          name: 'warga',
          component: () => import('@/pages/WargaPage.vue'),
        },
        {
          path: 'surat',
          name: 'surat',
          component: () => import('@/pages/SuratPage.vue'),
        },
        {
          path: 'laporan',
          name: 'laporan',
          component: () => import('@/pages/LaporanPage.vue'),
        },
        {
          path: 'agenda',
          name: 'agenda',
          component: () => import('@/pages/AgendaPage.vue'),
        },
        {
          path: 'keuangan',
          name: 'keuangan',
          component: () => import('@/pages/KeuanganPage.vue'),
        },
        {
          path: 'log',
          name: 'log',
          component: () => import('@/pages/LogPage.vue'),
        },
        {
          path: 'pengaturan',
          name: 'pengaturan',
          component: () => import('@/pages/PlaceholderPage.vue'),
        },
      ],
    },
  ],
})

// Navigation guard
router.beforeEach((to) => {
  const token = localStorage.getItem('token')
  if (to.meta.requiresAuth !== false && !token) {
    return '/login'
  }
  if (to.path === '/login' && token) {
    return '/'
  }
})

export default router
