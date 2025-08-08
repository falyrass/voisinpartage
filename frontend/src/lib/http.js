import axios from 'axios'

function normalizeBase(url) {
  if (!url) return ''
  return url.endsWith('/') ? url.slice(0, -1) : url
}

const base = normalizeBase(import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000')

export const api = axios.create({
  baseURL: `${base}/api`,
  timeout: 10000,
  withCredentials: false,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

api.interceptors.response.use(
  (res) => res,
  (err) => {
    const message = err?.response?.data?.message || err.message || 'Request error'
    return Promise.reject(new Error(message))
  }
)

export default api
