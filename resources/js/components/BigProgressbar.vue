<!-- ThickProgressBar.vue -->
<template>
  <div class="flex flex-col items-start p-2">
    <!-- Title -->
    <p class="text-sm font-semibold text-black mb-2">{{ title }}</p>

    <!-- Progress Bar Container -->
    <div
        class="relative rounded-full overflow-hidden"
        :style="{ height: height + 'px', width: width + 'px', backgroundColor: 'rgb(198, 164, 33)' }"
        >
      <!-- Filled Gradient -->
      <div
        class="h-full rounded-full transition-all duration-500"
        :style="{
          width: progress + '%',
          background: `linear-gradient(to right, ${colorFrom}, ${colorTo})`
        }"
      ></div>

      <!-- Tooltip at end of filled part -->
      <div
            class="absolute top-1/2 -translate-y-1/2"
            :style="{ left: `calc(${progress}% - 20px)` }"
            >
            <div
                class="px-2 py-1 rounded text-white text-xs shadow-md whitespace-nowrap"
                :style="{ backgroundColor: '#0a080880' }"
            >
                {{ tooltip }}
            </div>
            </div>
    </div>

   

    
  </div>
</template>

<script setup lang="ts">
const props = defineProps({
  title: { type: String, required: true },
  progress: { type: Number, required: true }, // 0-100
  tooltip: { type: String, default: '' },
  colorFrom: { type: String, default: '#fde68a' },
  colorTo: { type: String, default: '#f59e0b' },
  width: { type: Number, default: 200 }, // bar width in px
  height: { type: Number, default: 40 }, // default thick bar
  bgColor: { type: String, default: 'rgba(255,255,255,0.1)' },
  extra: { type: String, default: '' }
})
</script>
