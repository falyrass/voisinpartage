<template>
  <div>
    <button @click="testPing">Tester liaison API</button>
    <div v-if="result" class="mt-2">Réponse API: <b>{{ result }}</b></div>
    <div v-if="error" class="mt-2 text-red-500">Erreur: {{ error }}</div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const result = ref('')
const error = ref('')

async function testPing() {
  result.value = ''
  error.value = ''
  try {
    const { data } = await axios.get('http://127.0.0.1:8000/api/ping')
    result.value = data.status
  } catch (e) {
    error.value = e.message
  }
}
</script>
