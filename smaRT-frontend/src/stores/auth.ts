import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authApi } from '@/services/api'
import router from '@/router'

export interface User {
  id: string
  id_rt: string
  nama: string
  NIK: string
  role: 'WARGA' | 'PENGURUS' | 'KETUA' | 'BENDAHARA'
  phone: string | null
  created_at: string
}

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(localStorage.getItem('token'))
  const user = ref<User | null>(
    localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')!) : null,
  )

  const isAuthenticated = computed(() => !!token.value)
  const userRole = computed(() => user.value?.role ?? null)
  const userName = computed(() => user.value?.nama ?? '')

  async function login(NIK: string, password: string) {
    const res = await authApi.login(NIK, password)
    token.value = res.data.token
    user.value = res.data.user
    localStorage.setItem('token', res.data.token)
    localStorage.setItem('user', JSON.stringify(res.data.user))
    router.push('/')
  }

  async function logout() {
    try {
      await authApi.logout()
    } catch {
      // ignore
    }
    token.value = null
    user.value = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    router.push('/login')
  }

  function hasRole(...roles: string[]) {
    return user.value ? roles.includes(user.value.role) : false
  }

  return { token, user, isAuthenticated, userRole, userName, login, logout, hasRole }
})
