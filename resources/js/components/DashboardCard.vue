<template>
  <div
    :class="[cardShapeClass, cardWidthClass]"
    class="relative p-4 shadow-md hover:shadow-lg transition flex flex-col justify-start bg-white/40 backdrop-blur-md border border-white/50"
  >
    <!-- Top row: Title left, Icon right -->
    <div class="flex justify-between items-center">
      <!-- Title + Value stacked -->
      <div class="flex flex-col">
        <p class="text-sm font-semibold tracking-wide text-black">
          {{ title }}
        </p>
        <p class="text-2xl font-thin text-black mt-1 tracking-wider">
          {{ value ?? '-' }}
        </p>
      </div>

      <!-- Icon with smaller rounded white background -->
      <div
        v-if="icon"
        class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm"
      >
        <component :is="icon" class="w-5 h-5 text-[#dc8926]" />
      </div>
    </div>

    <!-- Optional extra below -->
    <div v-if="extra" class="mt-4 text-center">
      <p class="text-sm text-black/80">{{ extra }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: { type: String, required: true },
  value: { type: [String, Number], default: null },
  extra: { type: String, default: '' },
  icon: { type: Object, default: null }, // Lucide Vue icon
  shape: { type: String, default: 'rounded-xl' }, // 'rounded-xl', 'rounded-2xl', 'rounded-none', etc.
  width: { type: String, default: 'w-64' } // Tailwind width classes e.g. w-64, w-48, w-80
})

// Compute card shape class
const cardShapeClass = computed(() => props.shape)
const cardWidthClass = computed(() => props.width)
</script>
