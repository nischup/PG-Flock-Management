<script setup lang="ts">
import { ref, onMounted } from 'vue'

const city = ref('')
const temp = ref<number | null>(null)
const description = ref('')

// Loading & error
const loading = ref(true)
const error = ref('')

// Fetch weather from backend
async function fetchWeather(lat: number, lon: number) {
  try {
    const url = `${window.location.origin}/weather?lat=${lat}&lon=${lon}`
    const res = await fetch(url)
    const data = await res.json()
    if (data.error) throw new Error(data.error)

    city.value = data.city
    temp.value = data.temperature
    description.value = data.description
  } catch (e: any) {
    error.value = e.message
  } finally {
    loading.value = false
  }
}

// Get browser location
onMounted(() => {
  if (!navigator.geolocation) {
    error.value = 'Geolocation not supported'
    loading.value = false
    return
  }

  navigator.geolocation.getCurrentPosition(
    (position) => {
      fetchWeather(position.coords.latitude, position.coords.longitude)
    },
    (err) => {
      error.value = 'Permission denied or unavailable'
      loading.value = false
    }
  )
})
</script>

<template>
  <div class="flex items-center h-12 px-4  rounded text-sm space-x-3">
    <template v-if="loading">
      <span>Loading weather...</span>
    </template>
    <template v-else-if="error">
      <span class="text-red-500">{{ error }}</span>
    </template>
    <template v-else>
      <span class="font-bold text-lg">{{ temp }}°C</span>
    </template>
  </div>
</template>

<style scoped>
/* height is controlled via h-12 (48px), can adjust to h-12 / h-14 for 50px+ */
</style>
