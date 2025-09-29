<!-- VerticalBirdBars.vue -->
<template>
  <div class="w-full max-w-lg">
    <p class="text-sm font-semibold text-black mb-4">{{ title }}</p>

    <!-- Container -->
    <div class="flex justify-around items-end h-64 space-x-8">
      <!-- Brooding -->
      <div class="flex flex-col items-center w-16">
        <div class="relative w-full h-56 bg-gradient-to-b from-gray-900/40 to-gray-700/20 rounded-full overflow-hidden shadow-inner">
          <!-- Fill -->
          <div
            class="absolute bottom-0 left-0 w-full transition-all duration-700 rounded-full shadow-lg"
            :style="{
              height: bordingPercent + '%',
              background: `linear-gradient(to top, ${bordingColor}, ${bordingColor}cc)`
            }"
          >
            <!-- Shine -->
            <div class="absolute top-0 left-0 h-full w-1/3 bg-white/20 rounded-full blur-sm"></div>
          </div>
        </div>
        <span class="mt-2 text-xs font-bold text-gray-700">Brooding</span>
        <span class="text-sm font-semibold text-gray-900">{{ bordingPercent }}%</span>
      </div>

      <!-- Growing -->
      <div class="flex flex-col items-center w-16">
        <div class="relative w-full h-56 bg-gradient-to-b from-gray-900/40 to-gray-700/20 rounded-full overflow-hidden shadow-inner">
          <div
            class="absolute bottom-0 left-0 w-full transition-all duration-700 rounded-full shadow-lg"
            :style="{
              height: growingPercent + '%',
              background: `linear-gradient(to top, ${growingColor}, ${growingColor}cc)`
            }"
          >
            <div class="absolute top-0 left-0 h-full w-1/3 bg-white/20 rounded-full blur-sm"></div>
          </div>
        </div>
        <span class="mt-2 text-xs font-bold text-gray-700">Growing</span>
        <span class="text-sm font-semibold text-gray-900">{{ growingPercent }}%</span>
      </div>

      <!-- Production -->
      <div class="flex flex-col items-center w-16">
        <div class="relative w-full h-56 bg-gradient-to-b from-gray-900/40 to-gray-700/20 rounded-full overflow-hidden shadow-inner">
          <div
            class="absolute bottom-0 left-0 w-full transition-all duration-700 rounded-full shadow-lg"
            :style="{
              height: productionPercent + '%',
              background: `linear-gradient(to top, ${productionColor}, ${productionColor}cc)`
            }"
          >
            <div class="absolute top-0 left-0 h-full w-1/3 bg-white/20 rounded-full blur-sm"></div>
          </div>
        </div>
        <span class="mt-2 text-xs font-bold text-gray-700">Laying</span>
        <span class="text-sm font-semibold text-gray-900">{{ productionPercent }}%</span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  title: { type: String, default: 'Bird Stages' },
  bordingTotal: { type: Number, required: true },
  growingTotal: { type: Number, required: true },
  productionTotal: { type: Number, required: true },
  bordingColor: { type: String, default: '#fbbf24' },   // yellow
  growingColor: { type: String, default: '#22c55e' },   // green
  productionColor: { type: String, default: '#3b82f6' } // blue
})

const totalBirds = props.bordingTotal + props.growingTotal + props.productionTotal

const bordingPercent = totalBirds > 0 ? ((props.bordingTotal / totalBirds) * 100).toFixed(0) : 0
const growingPercent = totalBirds > 0 ? ((props.growingTotal / totalBirds) * 100).toFixed(0) : 0
const productionPercent = totalBirds > 0 ? ((props.productionTotal / totalBirds) * 100).toFixed(0) : 0
</script>

<style scoped>
.shadow-inner {
  box-shadow: inset -4px -4px 8px rgba(0, 0, 0, 0.3),
              inset 4px 4px 8px rgba(255, 255, 255, 0.05);
}
</style>
