import { defineStore, acceptHMRUpdate } from 'pinia'
import { apiFetch } from 'src/lib/api'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('auth_token') || null,
    user: (() => {
      try {
        const u = localStorage.getItem('auth_user')
        return u ? JSON.parse(u) : null
      } catch {
        return null
      }
    })(),
    realtime: (() => {
      try {
        const r = localStorage.getItem('auth_realtime')
        return r ? JSON.parse(r) : null
      } catch {
        return null
      }
    })()
  }),

  actions: {
    setAuth(token, user, realtime = null) {
      this.token = token
      this.user = user
      if (token) {
        localStorage.setItem('auth_token', token)
        localStorage.setItem('auth_user', JSON.stringify(user))
      } else {
        localStorage.removeItem('auth_token')
        localStorage.removeItem('auth_user')
      }
      if (realtime) {
        this.realtime = realtime
        localStorage.setItem('auth_realtime', JSON.stringify(realtime))
      } else {
        this.realtime = null
        localStorage.removeItem('auth_realtime')
      }
    },

    async login(username, password) {
      const res = await apiFetch('/auth/login', {
        method: 'POST',
        body: JSON.stringify({ username, password })
      })
      
      if (res.success && res.item) {
        const token = res.item.token || res.token || res.item
        const user = res.item.user || res.user || { username }
        const realtime = res.item.realtime || res.realtime || null
        this.setAuth(token, user, realtime)
        return { success: true }
      } else if (res.token) {
        this.setAuth(res.token, res.user || { username }, res.realtime)
        return { success: true }
      }
      return { success: false, message: res.message || 'Login failed' }
    },

    async register(username, email, password, passwordConfirmation) {
      const res = await apiFetch('/auth/register', {
        method: 'POST',
        body: JSON.stringify({
          username,
          email,
          password,
          password_confirmation: passwordConfirmation
        })
      })

      if (res.success && res.item) {
        const token = res.item.token || res.token
        const user = res.item.user || res.user
        const realtime = res.item.realtime || res.realtime
        this.setAuth(token, user, realtime)
        return { success: true }
      } else if (res.token) {
        this.setAuth(res.token, res.user, res.realtime)
        return { success: true }
      }
      return { success: false, message: res.message || 'Registration failed', errors: res.errors }
    },

    logout() {
      apiFetch('/auth/logout', { method: 'POST' }).catch(() => {})
      this.setAuth(null, null, null)
    },

    init() {
      const token = localStorage.getItem('auth_token')
      const userStr = localStorage.getItem('auth_user')
      const rtStr = localStorage.getItem('auth_realtime')
      if (token && userStr) {
        try {
          this.user = JSON.parse(userStr)
          this.token = token
          if (rtStr) {
            this.realtime = JSON.parse(rtStr)
          }
        } catch {
          this.setAuth(null, null, null)
        }
      }
    }
  }
})

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useAuthStore, import.meta.hot))
}
