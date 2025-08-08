<template>
  <div>
    <h2>Articles</h2>
    <button @click="load">Recharger</button>
    <span v-if="store.loading" style="margin-left:8px">Chargement…</span>
    <ul v-if="store.items.length">
      <li v-for="it in store.items" :key="it.id">
        <router-link :to="{ name: 'item-detail', params: { id: it.id } }">{{ it.title }}</router-link>
        — {{ it.lat }}, {{ it.lon }}
      </li>
    </ul>
    <div v-else>Aucun item chargé.</div>
    <div v-if="store.error" style="color:red">Erreur: {{ store.error }}</div>
  </div>
</template>

<script setup>
import { useItemsStore } from '@/stores/items'
const store = useItemsStore()

async function load() {
  await store.load()
}
</script>
