import { defineStore, acceptHMRUpdate } from 'pinia'
import { apiFetch } from 'src/lib/api'
import { Notify } from 'quasar'
import { useAuthStore } from './auth'

let eventSource = null

export const useChatsStore = defineStore('chats', {
  state: () => ({
    chats: [],
    fetching: false,
    saving: false,
    sseStatus: 'Disconnected',
    sseStatusColor: 'red-5',
    sseStatusClass: 'status-disconnected',
  }),

  actions: {
    async fetchChats() {
      this.fetching = true
      try {
        const res = await apiFetch('/chats/all')
        this.chats = res.item || res || []
      } catch (e) {
        Notify.create({
          type: 'negative',
          message: e.message || 'Failed to load chats',
          position: 'top-right'
        })
      } finally {
        this.fetching = false
      }
    },

    // SSE Connection (Mercure)
    connectSSE() {
      this.closeSSE()
      
      this.sseStatus = 'Connecting'
      this.sseStatusColor = 'amber-5'
      this.sseStatusClass = 'status-connecting'

      const hubUrl = import.meta.env.VITE_MERCURE_URL || 'http://localhost:8085/.well-known/mercure'
      const topic = encodeURIComponent('chats')
      const url = `${hubUrl}?topic=${topic}`

      eventSource = new EventSource(url)

      eventSource.onopen = () => {
        this.sseStatus = 'Connected'
        this.sseStatusColor = 'green-5'
        this.sseStatusClass = 'status-connected'
      }

      eventSource.onerror = () => {
        this.sseStatus = 'Disconnected'
        this.sseStatusColor = 'red-5'
        this.sseStatusClass = 'status-disconnected'
      }

      eventSource.onmessage = (event) => {
        try {
          const payload = JSON.parse(event.data)
          this.handleRealtimeUpdate(payload)
        } catch (e) {
          console.error('Failed to parse SSE message:', e)
        }
      }
    },

    closeSSE() {
      if (eventSource) {
        eventSource.close()
        eventSource = null
      }
      this.sseStatus = 'Disconnected'
      this.sseStatusColor = 'red-5'
      this.sseStatusClass = 'status-disconnected'
    },

    handleRealtimeUpdate(payload) {
      const { event, data, id } = payload
      
      if (event === 'chat_created') {
        if (!this.chats.some(c => c.id === data.id)) {
          this.chats.push(data)
        }
      } else if (event === 'chat_updated') {
        const idx = this.chats.findIndex(c => c.id === data.id)
        if (idx !== -1) {
          this.chats[idx] = data
        }
      } else if (event === 'chat_deleted') {
        const rawId = id || data
        const deletedId = parseInt(typeof rawId === 'object' && rawId !== null ? (rawId.id || rawId) : rawId)
        const idx = this.chats.findIndex(c => c.id === deletedId)
        if (idx !== -1) {
          this.chats.splice(idx, 1)
        }
      }
    },

    async sendChatMessage(receiverId, message) {
      this.saving = true
      const authStore = useAuthStore()
      try {
        const payload = {
          sender_id: authStore.user?.id,
          receiver_id: receiverId,
          message: message,
          is_read: 0
        }
        const res = await apiFetch('/chats', {
          method: 'POST',
          body: JSON.stringify(payload)
        })
        return res.item || res
      } catch (e) {
        Notify.create({
          type: 'negative',
          message: e.message || 'Failed to send message',
          position: 'top-right'
        })
        return null
      } finally {
        this.saving = false
      }
    }
  }
})

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useChatsStore, import.meta.hot))
}
