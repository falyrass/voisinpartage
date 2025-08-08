<template>
  <div>
    <button @click="testPing">Tester liaison API</button>
    <div v-if="result" class="mt-2">Réponse API: <b>{{ result }}</b></div>
    <div v-if="error" class="mt-2 text-red-500">Erreur: {{ error }}</div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import api from '@/lib/http'

const result = ref('')
const error = ref('')

async function testPing() {
  result.value = ''
  error.value = ''
  try {
    const { data } = await api.get('/ping')
    result.value = data.status
  } catch (e) {
    error.value = e.message
  }
}
</script>
