<template>
  <div class="p-6 rounded-xl shadow-lg bg-white/40 backdrop-blur-md border border-white/50 hover:shadow-xl transition-all duration-300">
    <!-- Chart Header -->
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-lg font-semibold text-gray-800">{{ title }}</h3>
      <div class="flex space-x-2">
        <button
          v-for="period in periods"
          :key="period"
          @click="selectedPeriod = period"
          class="px-3 py-1 text-sm rounded-full transition-all duration-200"
          :class="selectedPeriod === period 
            ? 'bg-blue-500 text-white shadow-md' 
            : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
        >
          {{ period }}
        </button>
      </div>
    </div>

    <!-- Chart Container -->
    <div class="relative">
      <!-- Line Chart -->
      <div v-if="chartType === 'line'" class="h-64">
        <svg class="w-full h-full" viewBox="0 0 400 200">
          <!-- Grid Lines -->
          <defs>
            <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
              <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#e5e7eb" stroke-width="0.5"/>
            </pattern>
          </defs>
          <rect width="100%" height="100%" fill="url(#grid)" />
          
          <!-- Chart Line -->
          <polyline
            :points="linePoints"
            fill="none"
            stroke="#3b82f6"
            stroke-width="3"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="transition-all duration-500"
          />
          
          <!-- Data Points -->
          <circle
            v-for="(point, index) in dataPoints"
            :key="index"
            :cx="point.x"
            :cy="point.y"
            r="4"
            fill="#3b82f6"
            class="cursor-pointer hover:r-6 transition-all duration-200"
            @mouseenter="showTooltip($event, data[index])"
            @mouseleave="hideTooltip"
          />
        </svg>
      </div>

      <!-- Bar Chart -->
      <div v-else-if="chartType === 'bar'" class="h-64 flex items-end justify-between space-x-2">
        <div
          v-for="(item, index) in data"
          :key="index"
          class="flex-1 bg-gradient-to-t from-blue-500 to-blue-400 rounded-t-lg hover:from-blue-600 hover:to-blue-500 transition-all duration-300 cursor-pointer group relative"
          :style="{ height: `${(item.value / maxValue) * 100}%` }"
          @mouseenter="showTooltip($event, item)"
          @mouseleave="hideTooltip"
        >
          <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
            <div class="bg-gray-800 text-white text-xs px-2 py-1 rounded">
              {{ item.value }}
            </div>
          </div>
        </div>
      </div>

      <!-- Doughnut Chart -->
      <div v-else-if="chartType === 'doughnut'" class="h-64 flex items-center justify-center">
        <svg class="w-48 h-48 transform -rotate-90">
          <circle
            cx="50%"
            cy="50%"
            r="60"
            fill="none"
            stroke="#e5e7eb"
            stroke-width="20"
          />
          <circle
            v-for="(segment, index) in doughnutSegments"
            :key="index"
            cx="50%"
            cy="50%"
            r="60"
            fill="none"
            :stroke="segment.color"
            stroke-width="20"
            :stroke-dasharray="circumference"
            :stroke-dashoffset="segment.offset"
            stroke-linecap="round"
            class="transition-all duration-1000"
            :style="{ 'stroke-dashoffset': segment.offset }"
          />
        </svg>
        <div class="absolute text-center">
          <div class="text-2xl font-bold text-gray-800">{{ totalValue }}</div>
          <div class="text-sm text-gray-600">Total</div>
        </div>
      </div>
    </div>

    <!-- Chart Legend -->
    <div v-if="showLegend" class="flex flex-wrap justify-center mt-4 space-x-4">
      <div
        v-for="(item, index) in data"
        :key="index"
        class="flex items-center space-x-2"
      >
        <div
          class="w-3 h-3 rounded-full"
          :style="{ backgroundColor: item.color || '#3b82f6' }"
        ></div>
        <span class="text-sm text-gray-700">{{ item.label }}</span>
      </div>
    </div>

    <!-- Tooltip -->
    <div
      v-if="tooltip.visible"
      class="absolute z-10 bg-gray-800 text-white text-sm px-3 py-2 rounded-lg shadow-lg pointer-events-none"
      :style="{ left: tooltip.x + 'px', top: tooltip.y + 'px' }"
    >
      <div class="font-semibold">{{ tooltip.label }}</div>
      <div>{{ tooltip.value }}</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

interface ChartData {
  label: string
  value: number
  color?: string
}

interface Props {
  title: string
  data: ChartData[]
  chartType: 'line' | 'bar' | 'doughnut'
  periods?: string[]
  showLegend?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  periods: () => ['7D', '30D', '90D', '1Y'],
  showLegend: true
})

const selectedPeriod = ref(props.periods[0])
const tooltip = ref({
  visible: false,
  x: 0,
  y: 0,
  label: '',
  value: ''
})

// Computed properties
const maxValue = computed(() => Math.max(...props.data.map(item => item.value)))
const totalValue = computed(() => props.data.reduce((sum, item) => sum + item.value, 0))

// Line chart points
const dataPoints = computed(() => {
  if (props.chartType !== 'line') return []
  
  const width = 360
  const height = 160
  const padding = 20
  
  return props.data.map((item, index) => ({
    x: padding + (index * (width - 2 * padding)) / (props.data.length - 1),
    y: height - padding - ((item.value / maxValue.value) * (height - 2 * padding))
  }))
})

const linePoints = computed(() => {
  return dataPoints.value.map(point => `${point.x},${point.y}`).join(' ')
})

// Doughnut chart segments
const circumference = computed(() => 2 * Math.PI * 60)

const doughnutSegments = computed(() => {
  if (props.chartType !== 'doughnut') return []
  
  let currentOffset = circumference.value
  
  return props.data.map((item, index) => {
    const percentage = item.value / totalValue.value
    const segmentLength = circumference.value * percentage
    const offset = currentOffset - segmentLength
    
    const segment = {
      color: item.color || `hsl(${index * 60}, 70%, 50%)`,
      offset: offset
    }
    
    currentOffset = offset
    return segment
  })
})

// Tooltip functions
const showTooltip = (event: MouseEvent, data: ChartData) => {
  tooltip.value = {
    visible: true,
    x: event.pageX + 10,
    y: event.pageY - 10,
    label: data.label,
    value: data.value.toString()
  }
}

const hideTooltip = () => {
  tooltip.value.visible = false
}

// Animation on mount
onMounted(() => {
  // Trigger animations
  setTimeout(() => {
    // Animation will be handled by CSS transitions
  }, 100)
})
</script>

<style scoped>
/* Custom animations for chart elements */
@keyframes drawLine {
  from {
    stroke-dasharray: 1000;
    stroke-dashoffset: 1000;
  }
  to {
    stroke-dasharray: 1000;
    stroke-dashoffset: 0;
  }
}

@keyframes growBar {
  from {
    height: 0;
  }
  to {
    height: var(--target-height);
  }
}

.polyline {
  animation: drawLine 2s ease-in-out;
}

.bar-segment {
  animation: growBar 1s ease-out;
}
</style>
