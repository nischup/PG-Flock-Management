<template>
  <div v-if="value !== null"
     class="p-4 rounded-xl shadow-md hover:shadow-lg transition
            flex flex-col justify-between items-center w-44 h-44
            bg-white/40 backdrop-blur-md border border-white/50">
    
    <!-- Title on top-left -->
    <div class="self-start">
      <span class="text-sm font-semibold text-gray-700">{{ title }}</span>
    </div>

    <!-- Progress Bar Container -->
    <div class="flex-1 flex items-center justify-center w-full">
      
      <!-- Rounded / Circular Progress -->
      <div v-if="type === 'rounded'" class="relative w-28 h-28">
        <svg class="w-28 h-28 transform -rotate-90">
          <circle
            class="text-gray-200"
            stroke-width="12"
            stroke="currentColor"
            fill="transparent"
            r="50"
            cx="50%"
            cy="50%"
          />
          <defs>
            <linearGradient :id="'gradient'+index" x1="0%" y1="0%" x2="100%" y2="0%">
              <stop offset="0%" :stop-color="colorFrom || '#fde68a'" />
              <stop offset="100%" :stop-color="colorTo || '#f59e0b'" />
            </linearGradient>
          </defs>
          <circle
            :stroke="'url(#gradient'+index+')'"
            stroke-width="12"
            stroke-linecap="round"
            stroke-dasharray="314"
            :stroke-dashoffset="314 - (314 * value) / 100"
            fill="transparent"
            r="50"
            cx="50%"
            cy="50%"
          />
        </svg>
        <div class="absolute inset-0 flex items-center justify-center">
          <span class="text-lg font-bold">{{ value }}%</span>
        </div>
      </div>

      <!-- Straight / Horizontal Progress -->
      <div v-else-if="type === 'straight'" class="relative w-full h-6 rounded-full bg-gray-200">
        <div class="h-6 rounded-full"
             :style="{
               width: value + '%',
               background: `linear-gradient(to right, ${colorFrom || '#fde68a'}, ${colorTo || '#f59e0b'})`,
               transition: 'width 0.5s ease'
             }">
        </div>
        <div class="absolute inset-0 flex items-center justify-center text-sm font-bold text-gray-700">
          {{ value }}%
        </div>
      </div>

      <!-- Dashed Progress -->
      <div v-else-if="type === 'dashed'" class="relative w-full h-6 rounded-full bg-gray-200 overflow-hidden">
        <div class="h-6 absolute top-0 left-0 rounded-full"
             :style="{
               width: value + '%',
               backgroundImage: `repeating-linear-gradient(to right, ${colorFrom || '#fde68a'} 0 4px, ${colorTo || '#f59e0b'} 4px 8px)`,
               transition: 'width 0.5s ease'
             }">
        </div>
        <div class="absolute inset-0 flex items-center justify-center text-sm font-bold text-gray-700">
          {{ value }}%
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  title: { type: String, required: true },
  value: { type: Number, default: null },
  type: { type: String, default: 'rounded' }, // 'rounded', 'straight', 'dashed'
  index: { type: Number, default: 0 }, // unique index for SVG gradient id
  colorFrom: { type: String, default: null }, // gradient start from backend
  colorTo: { type: String, default: null }   // gradient end from backend
})
</script>

<style scoped>
circle {
  transition: stroke-dashoffset 0.5s ease;
}
</style>
