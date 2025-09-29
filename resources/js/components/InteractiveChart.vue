<template>
  <div class="p-6 rounded-xl shadow-lg bg-white/40 backdrop-blur-md border border-white/50 hover:shadow-xl transition-all duration-300 relative">
    <!-- Chart Header -->
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-lg font-semibold text-gray-800">{{ title }}</h3>
    </div>

    <!-- Chart Container -->
    <div class="relative">
      <!-- Line Chart -->
      <div v-if="chartType === 'line'" class="h-64">
        <svg class="w-full h-full" viewBox="0 0 400 200">
          <defs>
            <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
              <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#e5e7eb" stroke-width="0.5"/>
            </pattern>
          </defs>
          <rect width="100%" height="100%" fill="url(#grid)" />
          <polyline
            :points="linePoints"
            fill="none"
            stroke="#3b82f6"
            stroke-width="3"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="transition-all duration-500"
          />
          <circle
            v-for="(point, index) in dataPoints"
            :key="index"
            :cx="point.x"
            :cy="point.y"
            r="4"
            fill="#3b82f6"
            class="cursor-pointer hover:r-6 transition-all duration-200"
            @mouseenter="showTooltip($event, props.data[index])"
            @mouseleave="hideTooltip"
          />
        </svg>
      </div>

      <!-- Bar Chart -->
      <div v-else-if="chartType === 'bar'" class="h-64 flex items-end justify-between space-x-2">
        <div
          v-for="(item, index) in props.data"
          :key="index"
          class="flex-1 rounded-t-lg transition-all duration-300 cursor-pointer group relative"
          :style="{
            height: `${(item.value / maxValue) * 100}%`,
            background: `linear-gradient(to top, ${item.color} 0%, ${lightenColor(item.color, 20)} 100%)`
          }"
          @mouseenter="showTooltip($event, item)"
          @mouseleave="hideTooltip"
        >
          <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
            <div class="bg-gray-800 text-white text-xs px-2 py-1 rounded">
              {{ item.isPercent ? item.value + '%' : item.value }}
            </div>
          </div>
        </div>
      </div>

      <!-- Doughnut Chart -->
      <div v-else-if="chartType === 'doughnut'" class="h-64 flex items-center justify-center">
        <svg class="w-48 h-48 -rotate-90">
          <!-- Base Circle -->
          <circle
            cx="50%"
            cy="50%"
            r="60"
            fill="none"
            stroke="#e5e7eb"
            stroke-width="20"
          />
          <!-- Segments -->
          <circle
            v-for="(segment, index) in doughnutSegments"
            :key="index"
            cx="50%"
            cy="50%"
            r="60"
            fill="none"
            :stroke="segment.color"
            stroke-width="20"
            :stroke-dasharray="segment.length + ' ' + circumference"
            :stroke-dashoffset="segment.offset"
            stroke-linecap="round"
            class="transition-all duration-1000"
          />
        </svg>
        <div class="absolute text-center">
          <div class="text-2xl font-bold text-gray-800">{{ totalValueDisplay }}</div>
          <div class="text-sm text-gray-600">Total</div>
        </div>
      </div>
    </div>

    <!-- Chart Legend -->
    <div v-if="showLegend" class="flex flex-wrap justify-center mt-4 space-x-4">
      <div v-for="(item, index) in props.data" :key="index" class="flex items-center space-x-2">
        <div class="w-3 h-3 rounded-full" :style="{ backgroundColor: item.color }"></div>
        <span class="text-sm text-gray-700">
          {{ item.label }} ({{ item.isPercent ? item.value + '%' : item.value }})
        </span>
      </div>
    </div>

    <!-- Tooltip -->
    <div
      v-if="tooltip.visible"
      class="absolute z-10 bg-gray-800 text-white text-sm px-3 py-2 rounded-lg shadow-lg pointer-events-none"
      :style="{ left: tooltip.x + 'px', top: tooltip.y + 'px' }"
    >
      <div class="font-semibold">{{ tooltip.label }}</div>
      <div>{{ tooltip.isPercent ? tooltip.value + '%' : tooltip.value }}</div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

interface ChartData {
  label: string
  value: number
  color: string
  isPercent?: boolean
}

interface Props {
  title: string
  data: ChartData[]
  chartType: 'line' | 'bar' | 'doughnut'
  showLegend?: boolean
}

const props = withDefaults(defineProps<Props>(), { showLegend: true })

const tooltip = ref({ visible: false, x: 0, y: 0, label: '', value: '', isPercent: false })

// Max for scaling (bars/line)
const maxValue = computed(() => Math.max(...props.data.map(d => d.value)))

// Total for doughnut display
const totalValue = computed(() => props.data.reduce((sum, d) => sum + d.value, 0))

// Display total dynamically (percentage if all items are percent)
const totalValueDisplay = computed(() => {
  if (props.data.every(d => d.isPercent)) return totalValue.value + '%'
  return totalValue.value
})

// Line chart points
const dataPoints = computed(() => {
  if (props.chartType !== 'line') return []
  const width = 360, height = 160, padding = 20
  return props.data.map((d, i) => ({
    x: padding + (i * (width - 2 * padding)) / (props.data.length - 1),
    y: height - padding - ((d.value / maxValue.value) * (height - 2 * padding))
  }))
})
const linePoints = computed(() => dataPoints.value.map(p => `${p.x},${p.y}`).join(' '))

// Doughnut segments
const circumference = computed(() => 2 * Math.PI * 60)
const doughnutSegments = computed(() => {
  if (props.chartType !== 'doughnut') return []
  let currentOffset = 0
  return props.data.map(d => {
    const length = circumference.value * (d.value / totalValue.value)
    const offset = circumference.value - currentOffset - length
    currentOffset += length
    return { color: d.color, length, offset }
  })
})

// Tooltip functions
const showTooltip = (e: MouseEvent, d: ChartData) => {
  tooltip.value = {
    visible: true,
    x: e.pageX + 10,
    y: e.pageY - 10,
    label: d.label,
    value: d.value,
    isPercent: !!d.isPercent
  }
}
const hideTooltip = () => (tooltip.value.visible = false)

// Helper: lighten color
function lightenColor(color: string, percent: number) {
  const num = parseInt(color.replace('#',''),16),
        amt = Math.round(2.55 * percent),
        R = (num >> 16) + amt,
        G = (num >> 8 & 0x00FF) + amt,
        B = (num & 0x0000FF) + amt
  return `#${(0x1000000 + (R<255?R<1?0:R:255)*0x10000 + (G<255?G<1?0:G:255)*0x100 + (B<255?B<1?0:B:255)).toString(16).slice(1)}`
}
</script>

<style scoped>
div[style] { transition: all 0.5s ease; }
circle { transition: stroke-dasharray 1s ease, stroke-dashoffset 1s ease; }
</style>
