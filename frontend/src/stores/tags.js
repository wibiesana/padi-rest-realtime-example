import { defineStore, acceptHMRUpdate } from 'pinia'
import { apiFetch } from 'src/lib/api'
import { Notify } from 'quasar'

let eventSource = null

export const useTagsStore = defineStore('tags', {
  state: () => ({
    tags: [],
    fetching: false,
    saving: false,
    searchQuery: '',
    sseStatus: 'Disconnected',
    sseStatusColor: 'red-5',
    sseStatusClass: 'status-disconnected',
  }),

  actions: {
    async fetchTags() {
      this.fetching = true
      try {
        const url = this.searchQuery 
          ? `/tags/all?search=${encodeURIComponent(this.searchQuery)}`
          : '/tags/all'
        const res = await apiFetch(url)
        this.tags = res.item || res || []
      } catch (e) {
        Notify.create({
          type: 'negative',
          message: e.message || 'Failed to load tags',
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
      const topic = encodeURIComponent('tags')
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
      
      if (event === 'tag_created') {
        if (!this.tags.some(t => t.id === data.id)) {
          this.tags.unshift(data)
          Notify.create({
            color: 'primary',
            icon: 'notifications',
            message: `New tag created: "${data.name}"`,
            position: 'bottom-right'
          })
        }
      } else if (event === 'tag_updated') {
        const idx = this.tags.findIndex(t => t.id === data.id)
        if (idx !== -1) {
          this.tags[idx] = data
          Notify.create({
            color: 'amber',
            icon: 'update',
            message: `Tag updated: "${data.name}"`,
            position: 'bottom-right'
          })
        }
      } else if (event === 'tag_deleted') {
        const rawId = id || data
        const deletedId = parseInt(typeof rawId === 'object' && rawId !== null ? (rawId.id || rawId) : rawId)
        const idx = this.tags.findIndex(t => t.id === deletedId)
        if (idx !== -1) {
          const name = this.tags[idx].name
          this.tags.splice(idx, 1)
          Notify.create({
            color: 'red',
            icon: 'delete',
            message: `Tag deleted: "${name}"`,
            position: 'bottom-right'
          })
        }
      }
    },

    async createTag(tagForm) {
      this.saving = true
      try {
        await apiFetch('/tags', {
          method: 'POST',
          body: JSON.stringify(tagForm)
        })
        Notify.create({
          type: 'positive',
          message: 'Tag created successfully',
          position: 'top-right'
        })
        return true
      } catch (e) {
        Notify.create({
          type: 'negative',
          message: e.message || 'Failed to create tag',
          position: 'top-right'
        })
        return false
      } finally {
        this.saving = false
      }
    },

    async updateTag(id, tagForm) {
      this.saving = true
      try {
        await apiFetch(`/tags/${id}`, {
          method: 'PUT',
          body: JSON.stringify(tagForm)
        })
        Notify.create({
          type: 'positive',
          message: 'Tag updated successfully',
          position: 'top-right'
        })
        return true
      } catch (e) {
        Notify.create({
          type: 'negative',
          message: e.message || 'Failed to update tag',
          position: 'top-right'
        })
        return false
      } finally {
        this.saving = false
      }
    },

    async deleteTag(id) {
      this.saving = true
      try {
        await apiFetch(`/tags/${id}`, {
          method: 'DELETE'
        })
        Notify.create({
          type: 'positive',
          message: 'Tag deleted successfully',
          position: 'top-right'
        })
        return true
      } catch (e) {
        Notify.create({
          type: 'negative',
          message: e.message || 'Failed to delete tag',
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
  import.meta.hot.accept(acceptHMRUpdate(useTagsStore, import.meta.hot))
}
