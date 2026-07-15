import { defineStore, acceptHMRUpdate } from 'pinia'
import { apiFetch } from 'src/lib/api'
import { Notify } from 'quasar'
import { useAuthStore } from './auth'

let eventSource = null

export const useCommentsStore = defineStore('comments', {
  state: () => ({
    comments: [],
    fetching: false,
    saving: false,
    searchQuery: '',
    sseStatus: 'Disconnected',
    sseStatusColor: 'red-5',
    sseStatusClass: 'status-disconnected',
  }),

  actions: {
    async fetchComments() {
      this.fetching = true
      try {
        const url = this.searchQuery 
          ? `/comments/all?search=${encodeURIComponent(this.searchQuery)}`
          : '/comments/all'
        const res = await apiFetch(url)
        this.comments = res.item || res || []
      } catch (e) {
        Notify.create({
          type: 'negative',
          message: e.message || 'Failed to load comments',
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
      const topic = encodeURIComponent('comments')
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
      
      if (event === 'comment_created') {
        if (!this.comments.some(c => c.id === data.id)) {
          this.comments.unshift(data)
          Notify.create({
            color: 'primary',
            icon: 'notifications',
            message: `New comment added`,
            position: 'bottom-right'
          })
        }
      } else if (event === 'comment_updated') {
        const idx = this.comments.findIndex(c => c.id === data.id)
        if (idx !== -1) {
          this.comments[idx] = data
          Notify.create({
            color: 'amber',
            icon: 'update',
            message: `Comment updated`,
            position: 'bottom-right'
          })
        }
      } else if (event === 'comment_deleted') {
        const rawId = id || data
        const deletedId = parseInt(typeof rawId === 'object' && rawId !== null ? (rawId.id || rawId) : rawId)
        const idx = this.comments.findIndex(c => c.id === deletedId)
        if (idx !== -1) {
          this.comments.splice(idx, 1)
          Notify.create({
            color: 'red',
            icon: 'delete',
            message: `Comment deleted`,
            position: 'bottom-right'
          })
        }
      }
    },

    async createComment(commentForm) {
      this.saving = true
      const authStore = useAuthStore()
      try {
        const payload = {
          ...commentForm,
          user_id: authStore.user?.id
        }
        await apiFetch('/comments', {
          method: 'POST',
          body: JSON.stringify(payload)
        })
        Notify.create({
          type: 'positive',
          message: 'Comment added successfully',
          position: 'top-right'
        })
        return true
      } catch (e) {
        Notify.create({
          type: 'negative',
          message: e.message || 'Failed to add comment',
          position: 'top-right'
        })
        return false
      } finally {
        this.saving = false
      }
    },

    async updateComment(id, commentForm) {
      this.saving = true
      const authStore = useAuthStore()
      try {
        const payload = {
          ...commentForm,
          user_id: authStore.user?.id
        }
        await apiFetch(`/comments/${id}`, {
          method: 'PUT',
          body: JSON.stringify(payload)
        })
        Notify.create({
          type: 'positive',
          message: 'Comment updated successfully',
          position: 'top-right'
        })
        return true
      } catch (e) {
        Notify.create({
          type: 'negative',
          message: e.message || 'Failed to update comment',
          position: 'top-right'
        })
        return false
      } finally {
        this.saving = false
      }
    },

    async deleteComment(id) {
      this.saving = true
      try {
        await apiFetch(`/comments/${id}`, {
          method: 'DELETE'
        })
        Notify.create({
          type: 'positive',
          message: 'Comment deleted successfully',
          position: 'top-right'
        })
        return true
      } catch (e) {
        Notify.create({
          type: 'negative',
          message: e.message || 'Failed to delete comment',
          position: 'top-right'
        })
        return false
      } finally {
        this.saving = false
      }
    }
  }
})

if (import.meta.hot) {
  import.meta.hot.accept(acceptHMRUpdate(useCommentsStore, import.meta.hot))
}
