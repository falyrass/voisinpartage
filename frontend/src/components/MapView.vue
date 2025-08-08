<template>
  <div style="height: 400px; width: 100%" ref="mapEl"></div>
  <div style="margin-top:8px; display:flex; align-items:center; gap:8px; flex-wrap:wrap">
    <button @click="centerOnMe">Autour de moi</button>
    <label>
      Rayon:
      <select v-model.number="radius" @change="reloadAroundCenter">
        <option :value="1">1 km</option>
        <option :value="2">2 km</option>
        <option :value="5">5 km</option>
      </select>
    </label>
    <span v-if="loading">Chargement…</span>
    <span v-if="error" style="color:red">{{ error }}</span>
  </div>
  
</template>

<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'
// Fix Leaflet default icon paths under Vite
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png'
import markerIcon from 'leaflet/dist/images/marker-icon.png'
import markerShadow from 'leaflet/dist/images/marker-shadow.png'
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
})
import { useItemsStore } from '@/stores/items'

const mapEl = ref(null)
let map
let markersLayer
const store = useItemsStore()
const error = ref('')
const loading = ref(false)
const radius = ref(store.radius)

function debounce(fn, delay = 400) {
  let t
  return (...args) => {
    clearTimeout(t)
    t = setTimeout(() => fn(...args), delay)
  }
}

function createMap(center=[45.5017, -73.5673], zoom=13) {
  map = L.map(mapEl.value).setView(center, zoom)
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map)
  markersLayer = L.layerGroup().addTo(map)
}

async function loadNearby(lat, lon, radiusKm=2) {
  try {
    error.value = ''
    loading.value = true
  await store.load(100)
  const list = store.items
    markersLayer.clearLayers()
    list.forEach(it => {
      if (typeof it.lat === 'number' && typeof it.lon === 'number') {
        const title = it.title ?? 'Sans titre'
        const desc = it.description ?? ''
  const html = `<div><b><a href="/items/${it.id}">${title}</a></b><div style="margin-top:4px">${desc}</div></div>`
        L.marker([it.lat, it.lon]).addTo(markersLayer).bindPopup(html)
      }
    })
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}

function reloadAroundCenter() {
  if (!map) return
  const c = map.getCenter()
  store.setCenter(c.lat, c.lng)
  store.setRadius(radius.value)
  loadNearby(c.lat, c.lng, radius.value)
}

function tryAutoLocate() {
  if (!navigator.geolocation) {
    // fallback: charger autour du centre par défaut
    const c = map.getCenter()
    loadNearby(c.lat, c.lng, radius.value)
    return
  }
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      const lat = pos.coords.latitude
      const lon = pos.coords.longitude
  map.setView([lat, lon], 14)
  store.setCenter(lat, lon)
  store.setRadius(radius.value)
  loadNearby(lat, lon, radius.value)
    },
    () => {
      // fallback si refus ou erreur
  const c = map.getCenter()
  store.setCenter(c.lat, c.lng)
  store.setRadius(radius.value)
  loadNearby(c.lat, c.lng, radius.value)
    },
    { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 }
  )
}

function centerOnMe() {
  if (!navigator.geolocation) {
    error.value = 'Géolocalisation non supportée'
    return
  }
  navigator.geolocation.getCurrentPosition(
    (pos) => {
      const lat = pos.coords.latitude
      const lon = pos.coords.longitude
  map.setView([lat, lon], 14)
  loadNearby(lat, lon, radius.value)
    },
    (err) => {
      error.value = err.message
    },
    { enableHighAccuracy: true, timeout: 8000 }
  )
}

onMounted(() => {
  createMap()
  // Auto-géolocalisation au chargement avec repli
  tryAutoLocate()
})

onBeforeUnmount(() => {
  map?.remove()
})
</script>

<style>
/* Fix leaflet default icon paths in Vite */
.leaflet-container {
  width: 100%;
  height: 100%;
}
</style>
