<!-- StackedBirdBar.vue -->
<template>
  <div class="space-y-2 w-full max-w-lg">
    <!-- Title -->
    <p class="text-sm font-semibold text-black mb-2">{{ title }}</p>

    <!-- Stage Names (outside the bar) -->
    <div class="flex justify-between mb-1 text-xs font-semibold text-black">
      <span>Brooding</span>
      <span>Growing</span>
      <span>Laying</span>
    </div>

    <!-- Stacked Bar -->
    <div class="flex h-8 w-full overflow-hidden bg-gray-200">
      <!-- Bording -->
      <div
        class="h-full flex items-center justify-center text-xs font-semibold text-white transition-all duration-500"
        :style="{ width: bordingPercent + '%', backgroundColor: bordingColor }"
      >
        {{ bordingPercent }}%
      </div>

      <!-- Growing -->
      <div
        class="h-full flex items-center justify-center text-xs font-semibold text-white transition-all duration-500"
        :style="{ width: growingPercent + '%', backgroundColor: growingColor }"
      >
        {{ growingPercent }}%
      </div>

      <!-- Production -->
      <div
        class="h-full flex items-center justify-center text-xs font-semibold text-white transition-all duration-500"
        :style="{ width: productionPercent + '%', backgroundColor: productionColor }"
      >
        {{ productionPercent }}%
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

// Compute total birds
const totalBirds = props.bordingTotal + props.growingTotal + props.productionTotal

// Compute % for each stage
const bordingPercent = ((props.bordingTotal / totalBirds) * 100).toFixed(0)
const growingPercent = ((props.growingTotal / totalBirds) * 100).toFixed(0)
const productionPercent = ((props.productionTotal / totalBirds) * 100).toFixed(0)
</script>

<style scoped>
div[style] {
  transition: width 0.5s ease;
}
</style>
