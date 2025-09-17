<template>
  <div class="animate-pulse">
    <!-- Card Skeleton -->
    <div v-if="type === 'card'" class="p-4 shadow-md rounded-xl bg-white/40 backdrop-blur-md border border-white/50">
      <div class="flex justify-between items-center">
        <div class="flex flex-col space-y-2">
          <div class="h-4 bg-gray-300 rounded w-20"></div>
          <div class="h-8 bg-gray-300 rounded w-16"></div>
        </div>
        <div class="w-10 h-10 bg-gray-300 rounded-full"></div>
      </div>
    </div>

    <!-- Progress Bar Skeleton -->
    <div v-else-if="type === 'progress'" class="flex flex-col items-start p-2">
      <div class="h-4 bg-gray-300 rounded w-24 mb-2"></div>
      <div class="h-10 bg-gray-300 rounded-full w-48"></div>
    </div>

    <!-- Circle Progress Skeleton -->
    <div v-else-if="type === 'circle'" class="p-4 rounded-xl shadow-md bg-white/40 backdrop-blur-md border border-white/50 flex flex-col justify-between items-center w-44 h-44">
      <div class="h-4 bg-gray-300 rounded w-16 self-start"></div>
      <div class="w-28 h-28 bg-gray-300 rounded-full"></div>
    </div>

    <!-- Table Skeleton -->
    <div v-else-if="type === 'table'" class="space-y-3">
      <div v-for="i in rows" :key="i" class="flex space-x-4">
        <div v-for="j in columns" :key="j" class="h-4 bg-gray-300 rounded flex-1"></div>
      </div>
    </div>

    <!-- Chart Skeleton -->
    <div v-else-if="type === 'chart'" class="p-4 rounded-xl shadow-md bg-white/40 backdrop-blur-md border border-white/50">
      <div class="h-4 bg-gray-300 rounded w-32 mb-4"></div>
      <div class="h-48 bg-gray-300 rounded"></div>
    </div>

    <!-- Generic Skeleton -->
    <div v-else class="space-y-2">
      <div class="h-4 bg-gray-300 rounded" :style="{ width: width }"></div>
      <div class="h-4 bg-gray-300 rounded" :style="{ width: width }"></div>
      <div class="h-4 bg-gray-300 rounded" :style="{ width: width }"></div>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  type?: 'card' | 'progress' | 'circle' | 'table' | 'chart' | 'generic'
  width?: string
  rows?: number
  columns?: number
}

withDefaults(defineProps<Props>(), {
  type: 'generic',
  width: '100%',
  rows: 5,
  columns: 4
})
</script>

<style scoped>
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
