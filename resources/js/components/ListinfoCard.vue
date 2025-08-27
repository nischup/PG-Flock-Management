<!-- SmallInfoCards.vue -->
<template>
  <div class="grid gap-4 md:grid-cols-4 sm:grid-cols-2 grid-cols-1 p-4">
    <div
      v-for="(card, index) in cards"
      :key="index"
      class="relative overflow-hidden rounded-xl shadow-lg hover:shadow-2xl transition-all duration-300 p-5 text-gray-900 dark:text-white flex items-center justify-between h-full"
      :style="{
        background: card.colorFrom && card.colorTo
          ? `linear-gradient(to right, ${card.colorFrom}, ${card.colorTo})`
          : getFallbackGradient(index)
      }"
    >
      <!-- Text -->
      <div>
        <p class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-200">
          {{ card.title }}
        </p>
        <p class="text-3xl font-extrabold mt-1">{{ card.value }}</p>
        <p v-if="card.extra" class="text-xs mt-0.5">{{ card.extra }}</p>
      </div>

      <!-- Icon -->
      <div
        v-if="card.icon"
        class="flex-shrink-0 w-16 h-16 rounded-full bg-white dark:bg-gray-800 flex items-center justify-center shadow-inner"
        v-html="card.icon"
      ></div>

      <!-- Optional Background Pattern -->
      <div
        class="absolute top-0 right-0 w-32 h-32 opacity-20 rounded-full -translate-x-1/3 -translate-y-1/3 pointer-events-none"
        :style="{ background: card.colorFrom && card.colorTo ? card.colorTo : getFallbackGradient(index, true) }"
      ></div>
    </div>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  cards: Array<{
    title: string
    value: number | string
    extra?: string
    colorFrom?: string
    colorTo?: string
    icon?: string
  }>
}>()

// Fallback gradient list (light colors from your example)
const gradientList = [
  { from: '#ffffff', to: '#6C6C6C' }, // yellow-300 -> yellow-500
  { from: '#86efac', to: '#22c55e' }, // green-300 -> green-500
  { from: '#fecaca', to: '#f87171' }, // red-200 -> red-400
  { from: '#a78bfa', to: '#7c3aed' }, // purple-400 -> purple-600
  { from: '#f472b6', to: '#ec4899' }, // pink-400 -> pink-500
  { from: '#60a5fa', to: '#3b82f6' }  // blue-400 -> blue-600
]

// Return gradient from list based on index
const getFallbackGradient = (index: number, singleColor = false) => {
  const grad = gradientList[index % gradientList.length]
  return singleColor ? grad.to : `linear-gradient(to right, ${grad.from}, ${grad.to})`
}
</script>
