import { defineStore } from 'pinia'
import api from '@/lib/http'

export const useItemsStore = defineStore('items', {
  state: () => ({
    items: [],
    center: { lat: 45.5017, lon: -73.5673 },
    radius: 2,
    loading: false,
    error: '',
    meta: null,
  }),
  actions: {
    setCenter(lat, lon) {
      this.center = { lat, lon }
    },
    setRadius(r) {
      this.radius = r
    },
    async load(perPage = 100) {
      try {
        this.error = ''
        this.loading = true
        const { lat, lon } = this.center
        const { data } = await api.get('/items', { params: { lat, lon, radius: this.radius, per_page: perPage } })
        this.items = Array.isArray(data) ? data : (data.data || [])
        this.meta = data.meta || null
      } catch (e) {
        this.error = e.message
      } finally {
        this.loading = false
      }
    }
  }
})
