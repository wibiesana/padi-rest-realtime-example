import { defineBoot } from '#q-app/wrappers'
import { useAuthStore } from 'src/stores/auth'
import { apiFetch } from 'src/lib/api'

export { apiFetch }

export default defineBoot(({ app, store }) => {
  const authStore = useAuthStore(store)
  authStore.init()
  app.config.globalProperties.$api = apiFetch
  app.config.globalProperties.$auth = authStore
})
