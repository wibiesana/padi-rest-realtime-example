const API_BASE = import.meta.env.VITE_API_URL || 'http://localhost:8085'

export async function apiFetch(path, options = {}) {
  const url = `${API_BASE}${path}`
  const headers = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    ...(options.headers || {})
  }

  const token = localStorage.getItem('auth_token')
  if (token) {
    headers['Authorization'] = `Bearer ${token}`
  }

  const response = await fetch(url, {
    ...options,
    headers
  })

  if (response.status === 204) {
    return { success: true }
  }

  const text = await response.text()
  let data
  try {
    data = JSON.parse(text)
  } catch {
    throw new Error(text || `HTTP error ${response.status}`)
  }

  if (!response.ok) {
    if (response.status === 401) {
      localStorage.removeItem('auth_token')
      localStorage.removeItem('auth_user')
      localStorage.removeItem('auth_realtime')
      window.location.hash = '/login'
    }
    const err = new Error(data.message || `HTTP error ${response.status}`)
    err.status = response.status
    err.errors = data.errors
    throw err
  }

  return data
}
