<template>
  <div>
    <h2>Détail de l’article #{{ id }}</h2>
    <div v-if="loading">Chargement…</div>
    <div v-else-if="error" style="color:red">Erreur: {{ error }}</div>
    <div v-else-if="item">
      <h3>{{ item.title }}</h3>
      <p>{{ item.description }}</p>
      <p>Coordonnées: {{ item.lat }}, {{ item.lon }}</p>
    </div>
  </div>
</template>
<script setup>
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import api from '@/lib/http'

const route = useRoute()
const id = route.params.id
const item = ref(null)
const loading = ref(false)
const error = ref('')

onMounted(async () => {
  try {
    loading.value = true
    const { data } = await api.get(`/items/${id}`)
    item.value = data
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
})
</script>
