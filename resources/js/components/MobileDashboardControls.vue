<template>
  <div class="lg:hidden">
    <!-- Mobile Header -->
    <div class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-gray-200">
      <div class="px-4 py-3">
        <!-- Mobile Title and Menu -->
        <div class="flex items-center justify-between">
          <h1 class="text-lg font-semibold text-gray-800">Dashboard</h1>
          <button
            @click="showMobileMenu = !showMobileMenu"
            class="p-2 rounded-lg bg-gray-100 text-gray-700 hover:bg-gray-200 transition-colors duration-200"
          >
            <Menu class="h-5 w-5" />
          </button>
        </div>

        <!-- Mobile Controls (when menu is open) -->
        <div v-if="showMobileMenu" class="mt-4 space-y-4">
          <!-- Layout Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Layout</label>
            <div class="grid grid-cols-3 gap-2">
              <button
                v-for="layout in ['grid', 'list', 'compact']"
                :key="layout"
                @click="changeLayout(layout); showMobileMenu = false"
                class="px-3 py-2 text-sm font-medium rounded-md transition-all duration-200 capitalize"
                :class="dashboardLayout === layout ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700'"
              >
                {{ layout }}
              </button>
            </div>
          </div>

          <!-- View Toggle -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Views</label>
            <div class="flex space-x-2">
              <button
                @click="toggleCharts"
                class="flex-1 flex items-center justify-center space-x-2 px-3 py-2 text-sm rounded-md transition-all duration-200"
                :class="showCharts ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700'"
              >
                <BarChart3 class="h-4 w-4" />
                <span>Charts</span>
              </button>
              <button
                @click="toggleDataTable"
                class="flex-1 flex items-center justify-center space-x-2 px-3 py-2 text-sm rounded-md transition-all duration-200"
                :class="showDataTable ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-700'"
              >
                <Activity class="h-4 w-4" />
                <span>Table</span>
              </button>
            </div>
          </div>

          <!-- Refresh Button -->
          <button
            @click="refreshData"
            :disabled="isRefreshing"
            class="w-full flex items-center justify-center space-x-2 px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-all duration-200 disabled:opacity-50"
          >
            <RefreshCw :class="{ 'animate-spin': isRefreshing }" class="h-4 w-4" />
            <span>Refresh Data</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Tabs -->
    <div class="px-4 py-2 bg-gray-50/50">
      <div class="flex space-x-1 overflow-x-auto">
        <button
          v-for="tab in alltabs"
          :key="tab"
          @click="activeTab = tab"
          class="flex-shrink-0 px-4 py-2 text-sm font-medium rounded-full transition-all duration-200 whitespace-nowrap"
          :class="activeTab === tab ? 'bg-black text-white shadow-md' : 'text-gray-700 bg-white hover:bg-gray-100'"
        >
          {{ tab }}
        </button>
      </div>
    </div>

    <!-- Mobile Filters -->
    <div v-if="activeContent.filters.length" class="px-4 py-3 bg-white border-b border-gray-200">
      <div class="space-y-3">
        <template v-for="f in activeContent.filters" :key="f">
          <!-- Normal selects -->
          <div v-if="f !== 'date'">
            <label class="block text-sm font-medium text-gray-700 mb-1">{{ f.charAt(0).toUpperCase() + f.slice(1) }}</label>
            <select
              v-model="filters[f]"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option :value="''" disabled>Select {{ f.charAt(0).toUpperCase() + f.slice(1) }}</option>
              <option v-for="(name,id) in props.filterOptions[f]" :key="id" :value="id">{{ name }}</option>
            </select>
          </div>

          <!-- Date -->
          <div v-else>
            <label class="block text-sm font-medium text-gray-700 mb-1">Date Range</label>
            <div class="space-y-2">
              <select
                v-model="filters.date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Select Date Range</option>
                <option v-for="(name,id) in props.filterOptions[f]" :key="id" :value="id">{{ name }}</option>
              </select>
              
              <div v-if="filters.date === 'Custom'" class="grid grid-cols-2 gap-2">
                <Datepicker
                  v-model="filters.dateRange"
                  range
                  :enable-time-picker="false"
                  placeholder="Select dates"
                  class="w-full"
                />
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Menu, BarChart3, Activity, RefreshCw } from 'lucide-vue-next'
import Datepicker from '@vuepic/vue-datepicker'

interface Props {
  filterOptions: Record<string, string[]>
  filters: Record<string, string>
  activeContent: { filters: string[] }
}

const props = defineProps<Props>()

const emit = defineEmits<{
  'update:activeTab': [value: string]
  'update:dashboardLayout': [value: string]
  'update:showCharts': [value: boolean]
  'update:showDataTable': [value: boolean]
  'refresh': []
  'update:filters': [value: Record<string, string>]
}>()

// Local state
const showMobileMenu = ref(false)
const activeTab = ref('Dashboard')
const dashboardLayout = ref('grid')
const showCharts = ref(true)
const showDataTable = ref(false)
const isRefreshing = ref(false)
const filters = ref({ ...props.filters })

// Computed
const alltabs = ['Dashboard', 'Company', 'Project', 'Flock', 'Shed', 'Batch']

// Methods
const changeLayout = (layout: string) => {
  dashboardLayout.value = layout
  emit('update:dashboardLayout', layout)
}

const toggleCharts = () => {
  showCharts.value = !showCharts.value
  emit('update:showCharts', showCharts.value)
}

const toggleDataTable = () => {
  showDataTable.value = !showDataTable.value
  emit('update:showDataTable', showDataTable.value)
}

const refreshData = async () => {
  isRefreshing.value = true
  emit('refresh')
  // Simulate refresh delay
  setTimeout(() => {
    isRefreshing.value = false
  }, 1000)
}
</script>

<style scoped>
/* Custom scrollbar for mobile tabs */
.overflow-x-auto::-webkit-scrollbar {
  height: 4px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 2px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 2px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
