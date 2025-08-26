<template>
  <div class="font-mono text-black-700 text-lg">
    {{ time }}
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'

const time = ref('')

const updateTime = () => {
  const now = new Date()
  // Format HH:mm:ss (24-hour)
  time.value = now.toLocaleTimeString('en-US', { hour12: false })
}

let intervalId = null

onMounted(() => {
  updateTime()
  intervalId = setInterval(updateTime, 1000) // update every second
})

onBeforeUnmount(() => {
  clearInterval(intervalId)
})
</script>
