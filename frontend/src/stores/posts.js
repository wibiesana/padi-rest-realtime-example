import { defineStore, acceptHMRUpdate } from 'pinia'
import { apiFetch } from 'src/lib/api'
import { Notify } from 'quasar'
import { useAuthStore } from './auth'

let eventSource = null

export const usePostsStore = defineStore('posts', {
  state: () => ({
    posts: [],
    fetching: false,
    saving: false,
    searchQuery: '',
    sseStatus: 'Disconnected',
    sseStatusColor: 'red-5',
    sseStatusClass: 'status-disconnected',
  }),

  actions: {
    async fetchPosts() {
      this.fetching = true
      try {
        const url = this.searchQuery 
          ? `/posts/all?search=${encodeURIComponent(this.searchQuery)}`
          : '/posts/all'
        const res = await apiFetch(url)
        this.posts = res.item || []
      } catch (e) {
        Notify.create({
          type: 'negative',
          message: e.message || 'Failed to load posts',
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
      const topic = encodeURIComponent('posts')
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
      
      if (event === 'post_created') {
        if (!this.posts.some(p => p.id === data.id)) {
          this.posts.unshift(data)
          Notify.create({
            color: 'primary',
            icon: 'notifications',
            message: `New post created: "${data.title}"`,
            position: 'bottom-right'
          })
        }
      } else if (event === 'post_updated') {
        const idx = this.posts.findIndex(p => p.id === data.id)
        if (idx !== -1) {
          this.posts[idx] = data
          Notify.create({
            color: 'amber',
            icon: 'update',
            message: `Post updated: "${data.title}"`,
            position: 'bottom-right'
          })
        }
      } else if (event === 'post_deleted') {
        const rawId = id || data
        const deletedId = parseInt(typeof rawId === 'object' && rawId !== null ? (rawId.id || rawId) : rawId)
        const idx = this.posts.findIndex(p => p.id === deletedId)
        if (idx !== -1) {
          const title = this.posts[idx].title
          this.posts.splice(idx, 1)
          Notify.create({
            color: 'red',
            icon: 'delete',
            message: `Post deleted: "${title}"`,
            position: 'bottom-right'
          })
        }
      }
    },

    async createPost(postForm) {
      this.saving = true
      const authStore = useAuthStore()
      try {
        const payload = {
          ...postForm,
          user_id: authStore.user?.id
        }
        const res = await apiFetch('/posts', {
          method: 'POST',
          body: JSON.stringify(payload)
        })
        Notify.create({
          type: 'positive',
          message: 'Post created successfully',
          position: 'top-right'
        })
        return res.item || res
      } catch (e) {
        Notify.create({
          type: 'negative',
          message: e.message || 'Failed to create post',
          position: 'top-right'
        })
        return null
      } finally {
        this.saving = false
      }
    },

    async updatePost(id, postForm) {
      this.saving = true
      const authStore = useAuthStore()
      try {
        const payload = {
          ...postForm,
          user_id: authStore.user?.id
        }
        const res = await apiFetch(`/posts/${id}`, {
          method: 'PUT',
          body: JSON.stringify(payload)
        })
        Notify.create({
          type: 'positive',
          message: 'Post updated successfully',
          position: 'top-right'
        })
        return res.item || res
      } catch (e) {
        Notify.create({
          type: 'negative',
          message: e.message || 'Failed to update post',
          position: 'top-right'
        })
        return null
      } finally {
        this.saving = false
      }
    },

    async deletePost(id) {
      this.saving = true
      try {
        await apiFetch(`/posts/${id}`, {
          method: 'DELETE'
        })
        Notify.create({
          type: 'positive',
          message: 'Post deleted successfully',
          position: 'top-right'
        })
        return true
      } catch (e) {
        Notify.create({
          type: 'negative',
          message: e.message || 'Failed to delete post',
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
  import.meta.hot.accept(acceptHMRUpdate(usePostsStore, import.meta.hot))
}
