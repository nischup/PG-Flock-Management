<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted } from 'vue'

import DashboardCard from '@/components/DashboardCard.vue'
import ProgressInfoBar from '@/components/BigProgressbar.vue'
import CircleProgress from '@/components/CircularProgress.vue'
import BirdStage from '@/components/BirdStage.vue'
import SkeletonLoader from '@/components/SkeletonLoader.vue'
import InteractiveChart from '@/components/InteractiveChart.vue'
import InteractiveDataTable from '@/components/InteractiveDataTable.vue'
import DashboardWidget from '@/components/DashboardWidget.vue'
import MobileDashboardControls from '@/components/MobileDashboardControls.vue'
import InteractiveTooltip from '@/components/InteractiveTooltip.vue'
import InteractiveModal from '@/components/InteractiveModal.vue'
import RealtimeNotification from '@/components/RealtimeNotification.vue'
import { useRealtimeData } from '@/composables/useRealtimeData'
import { useSidebar } from '@/components/ui/sidebar'

import Datepicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

// Lucide icons
import { 
  User, Drumstick, ShieldX, Egg, FlaskConical, PackageSearch, Factory, Syringe, Archive,
  TrendingUp, TrendingDown, Activity, BarChart3, PieChart, RefreshCw, Settings
} from "lucide-vue-next"
import { BabyChick } from '@/icons/BabyChick'

// Props
const props = defineProps<{
  filterOptions: Record<string,string[]>,
  cards: any[],
  progressBars: any[],
  circleBars: any[],
  birdStage: any,
  filters: Record<string,string>
}>()

// Breadcrumb
const breadcrumbs = [{ title: 'Dashboard', href: '/dashboard' }]

// Tabs
const alltabs = ['Dashboard','Company','Project','Flock','Shed','Batch']
const activeTab = ref('Dashboard')

// Real-time data composable (temporarily disabled)
// const {
//   data: realtimeData,
//   isLoading: isRealtimeLoading,
//   isConnected,
//   lastUpdate,
//   error: realtimeError,
//   connectionStatus,
//   timeSinceUpdate,
//   refresh: refreshRealtimeData,
//   triggerUpdate: triggerRealtimeUpdate
// } = useRealtimeData({
//   pollingInterval: 30000, // 30 seconds
//   enablePolling: true,
//   autoRefresh: true
// })

// Temporary fallback values
const realtimeData = ref(null)
const isRealtimeLoading = ref(false)
const isConnected = ref(false)
const lastUpdate = ref(0)
const realtimeError = ref(null)
const connectionStatus = ref('disconnected')
const timeSinceUpdate = ref(null)
const refreshRealtimeData = async () => {}
const triggerRealtimeUpdate = async () => {}

// Interactive state
const isRefreshing = ref(false)
const showCharts = ref(true)
const showDataTable = ref(false)
const dashboardLayout = ref('grid') // 'grid' | 'list' | 'compact'
const showModal = ref(false)
const modalData = ref<any>(null)
const isMobile = ref(false)

// Sidebar control (optional - only if sidebar context is available)
let setSidebarOpen: any = null
let sidebarState: any = null
const sidebarWasOpen = ref(false)

try {
  const sidebar = useSidebar()
  setSidebarOpen = sidebar.setOpen
  sidebarState = sidebar.state
} catch (error) {
  // Sidebar context not available, disable sidebar control
  console.warn('Sidebar context not available, sidebar control disabled')
}

// Real-time notifications
const showNotification = ref(false)
const notificationData = ref({
  title: '',
  message: '',
  type: 'info' as 'success' | 'error' | 'info' | 'warning',
  timestamp: Date.now()
})

// Filters reactive with default placeholders
const filters = ref({
  company: props.filters.company || '',
  project: props.filters.project || '',
  flock: props.filters.flock || '',
  shed: props.filters.shed || '',
  batch: props.filters.batch || '',
  date: props.filters.date || '',
  dateRange: props.filters.dateRange || [],
  date_from: props.filters.date_from || '',
  date_to: props.filters.date_to || ''
})

// Sample data for interactive charts
const chartData = ref({
  production: [
    { label: 'Week 1', value: 1200, color: '#3b82f6' },
    { label: 'Week 2', value: 1350, color: '#3b82f6' },
    { label: 'Week 3', value: 1100, color: '#3b82f6' },
    { label: 'Week 4', value: 1450, color: '#3b82f6' },
    { label: 'Week 5', value: 1600, color: '#3b82f6' },
    { label: 'Week 6', value: 1400, color: '#3b82f6' }
  ],
  mortality: [
    { label: 'Male', value: 15, color: '#ef4444' },
    { label: 'Female', value: 12, color: '#f97316' },
    { label: 'Unknown', value: 8, color: '#eab308' }
  ],
  eggTypes: [
    { label: 'Hatchable', value: 45, color: '#10b981' },
    { label: 'Commercial', value: 35, color: '#3b82f6' },
    { label: 'Broken', value: 20, color: '#ef4444' }
  ]
})

// Batch performance table data
const tableData = ref([])
const tableColumns = ref([
  { key: 'company', label: 'Company', type: 'text' },
  { key: 'project', label: 'Project', type: 'text' },
  { key: 'batch', label: 'Batch Name', type: 'text' },
  { key: 'flock', label: 'Flock Code', type: 'text' },
  { key: 'shed', label: 'Shed', type: 'text' },
  { key: 'stage', label: 'Stage', type: 'badge' },
  { key: 'total_birds', label: 'Total Birds', type: 'number' },
  { key: 'eggs', label: 'Today\'s Eggs', type: 'number' },
  { key: 'mortality', label: 'Mortality %', type: 'progress' },
  { key: 'status', label: 'Status', type: 'badge' },
  { key: 'date', label: 'Created', type: 'date' }
])

const tableActions = ref([
  {
    name: 'view',
    icon: 'Eye',
    handler: (row: any) => console.log('View', row)
  },
  {
    name: 'edit',
    icon: 'Edit',
    handler: (row: any) => console.log('Edit', row)
  }
])

// Icon mapping
const iconMap: Record<string, any> = { User, Drumstick, ShieldX, Egg, FlaskConical, PackageSearch, Factory, Syringe, Archive, BabyChick }

// Interactive methods
const refreshData = async () => {
  isRefreshing.value = true
  try {
    await refreshRealtimeData(filters.value)
    await fetchBatchPerformanceData()
    console.log('Real-time data refreshed')
  } finally {
    isRefreshing.value = false
  }
}

const fetchBatchPerformanceData = async () => {
  try {
    const response = await fetch(`/api/dashboard/batch-performance?${new URLSearchParams(filters.value)}`)
    const result = await response.json()
    
    if (result.success) {
      tableData.value = result.data
    } else {
      console.error('Failed to fetch batch performance data:', result.message)
    }
  } catch (error) {
    console.error('Error fetching batch performance data:', error)
  }
}

const toggleCharts = () => {
  showCharts.value = !showCharts.value
}

const toggleDataTable = () => {
  showDataTable.value = !showDataTable.value
}

const changeLayout = (layout: string) => {
  dashboardLayout.value = layout
}

const openModal = (data: any) => {
  // Store current sidebar state and collapse it (if sidebar control is available)
  if (setSidebarOpen && sidebarState) {
    sidebarWasOpen.value = sidebarState.value === 'expanded'
    if (sidebarState.value === 'expanded') {
      setSidebarOpen(false)
    }
  }
  
  modalData.value = data
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  modalData.value = null
  
  // Restore sidebar state if it was open before (if sidebar control is available)
  if (setSidebarOpen && sidebarWasOpen.value) {
    setSidebarOpen(true)
  }
}

const handleCardClick = (card: any) => {
  // Special handling for Total Flock card
  if (card.title === 'Total Flock' || card.title === 'Active Flocks') {
    handleFlockCardClick(card)
  } else {
    openModal({
      title: card.title,
      content: `Detailed information about ${card.title}`,
      data: card
    })
  }
}


const handleFlockCardClick = async (card: any) => {
  try {
    // Store current sidebar state and collapse it (if sidebar control is available)
    if (setSidebarOpen && sidebarState) {
      sidebarWasOpen.value = sidebarState.value === 'expanded'
      if (sidebarState.value === 'expanded') {
        setSidebarOpen(false)
      }
    }
    
    // Show loading state
    modalData.value = {
      title: 'Loading Flock Details...',
      content: 'Fetching detailed flock information...',
      data: card,
      loading: true
    }
    showModal.value = true

    // Fetch detailed flock data
    const response = await fetch(`/api/dashboard/flock-details?${new URLSearchParams(filters.value)}`)
    const result = await response.json()
    
    if (result.success) {
      modalData.value = {
        title: 'Active Flocks Details',
        content: 'Comprehensive information about all active flocks',
        data: card,
        flockData: result.data,
        loading: false
      }
    } else {
      throw new Error(result.message || 'Failed to fetch flock details')
    }
  } catch (error) {
    console.error('Error fetching flock details:', error)
    modalData.value = {
      title: 'Error',
      content: 'Failed to load flock details. Please try again.',
      data: card,
      error: true,
      loading: false
    }
  }
}

// Mobile detection
const checkMobile = () => {
  isMobile.value = window.innerWidth < 1024
}

// Handle mobile controls updates
const handleMobileUpdate = (updates: any) => {
  if (updates.activeTab) activeTab.value = updates.activeTab
  if (updates.dashboardLayout) dashboardLayout.value = updates.dashboardLayout
  if (updates.showCharts !== undefined) showCharts.value = updates.showCharts
  if (updates.showDataTable !== undefined) showDataTable.value = updates.showDataTable
  if (updates.filters) filters.value = { ...filters.value, ...updates.filters }
}

// Real-time notification methods
const showRealtimeNotification = (title: string, message: string, type: 'success' | 'error' | 'info' | 'warning' = 'info') => {
  notificationData.value = {
    title,
    message,
    type,
    timestamp: Date.now()
  }
  showNotification.value = true
}

const closeNotification = () => {
  showNotification.value = false
}

// Watch for real-time data changes
watch(realtimeData, (newData, oldData) => {
  if (newData && oldData && newData.timestamp !== oldData.timestamp) {
    showRealtimeNotification(
      'Data Updated',
      'Dashboard data has been refreshed with the latest information',
      'success'
    )
  }
}, { deep: true })

// Watch for connection status changes
watch(connectionStatus, (newStatus, oldStatus) => {
  if (newStatus !== oldStatus) {
    if (newStatus === 'connected') {
      showRealtimeNotification(
        'Connected',
        'Real-time data connection established',
        'success'
      )
    } else if (newStatus === 'error' || newStatus === 'disconnected') {
      showRealtimeNotification(
        'Connection Lost',
        'Real-time data connection lost. Using cached data.',
        'warning'
      )
    }
  }
})

// Watch filters & send backend request
watch(filters, (newFilters) => {
  const payload = { ...newFilters }
  if(payload.date === 'Custom' && payload.dateRange?.length === 2){
    payload.date_from = payload.dateRange[0]
    payload.date_to = payload.dateRange[1]
  }
  router.get('/dashboard', payload, { preserveState: true, replace: true })
}, { deep: true })

// Auto-refresh data every 5 minutes
onMounted(async () => {
  // Check mobile on mount
  checkMobile()
  
  // Add resize listener for mobile detection
  window.addEventListener('resize', checkMobile)
  
  // Initial data fetch
  await refreshData()
  await fetchBatchPerformanceData()
  
  const interval = setInterval(() => {
    if (!isRefreshing.value) {
      refreshData()
    }
  }, 300000) // 5 minutes

  // Cleanup on unmount
  return () => {
    clearInterval(interval)
    window.removeEventListener('resize', checkMobile)
  }
})

// Dependent dropdowns reset
watch(() => filters.company, () => {
  filters.project = ''
  filters.flock = ''
  filters.shed = ''
  filters.batch = ''
})
watch(() => filters.project, () => {
  filters.flock = ''
  filters.shed = ''
  filters.batch = ''
})
watch(() => filters.flock, () => {
  filters.shed = ''
  filters.batch = ''
})
watch(() => filters.shed, () => {
  filters.batch = ''
})

// Computed properties for real-time data
const dashboardData = computed(() => {
  return realtimeData.value || {
    cards: props.cards,
    progressBars: props.progressBars,
    circleBars: props.circleBars,
    birdStage: props.birdStage,
    chartData: chartData.value
  }
})

const isLoading = computed(() => isRealtimeLoading.value || isRefreshing.value)

// Tab configuration
const tabConfig = {
  Dashboard: { filters: [], cards: dashboardData.value.cards },
  Company: { filters: ["company", "date"], cards: dashboardData.value.cards },
  Project: { filters: ["company","project","date"], cards: dashboardData.value.cards },
  Flock: { filters: ["company","project","flock","shed"], cards: dashboardData.value.cards },
  Shed: { filters: ["company","project","flock","shed","date"], cards: dashboardData.value.cards },
  Batch: { filters: ["company","project","flock","shed","batch","date"], cards: dashboardData.value.cards },
}

// Active tab content
const activeContent = computed(() => tabConfig[activeTab.value] || { filters: [], cards: [] })
</script>

<template>
  <Head title="Dashboard" />
  <AppLayout :breadcrumbs="breadcrumbs">

    <!-- Mobile Controls -->
    <MobileDashboardControls
      :filter-options="props.filterOptions"
      :filters="filters"
      :active-content="activeContent"
      @update:active-tab="activeTab = $event"
      @update:dashboard-layout="changeLayout($event)"
      @update:show-charts="showCharts = $event"
      @update:show-data-table="showDataTable = $event"
      @refresh="refreshData"
      @update:filters="filters = { ...filters, ...$event }"
    />

    <!-- Desktop Enhanced Header with Controls -->
    <div class="hidden lg:block">
    <div class="flex justify-between items-center p-4 bg-gradient-to-r from-gray-50/50 to-white/30 border-b border-gray-200/50">
      <!-- Tabs -->
      <div class="flex bg-white dark:bg-gray-800 rounded-full shadow overflow-hidden">
        <button
          v-for="tab in alltabs"
          :key="tab"
          @click="activeTab = tab"
          class="flex-1 text-center px-4 py-2 text-sm font-medium transition-all duration-200"
          :class="activeTab === tab ? 'bg-black text-white shadow-md' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'">
          {{ tab }}
        </button>
      </div>

      <!-- Dashboard Controls -->
      <div class="flex items-center space-x-4">
        <!-- Layout Toggle -->
        <div class="flex bg-white rounded-lg shadow overflow-hidden">
          <button
            v-for="layout in ['grid', 'list', 'compact']"
            :key="layout"
            @click="changeLayout(layout)"
            class="px-3 py-2 text-sm font-medium transition-all duration-200 capitalize"
            :class="dashboardLayout === layout ? 'bg-blue-500 text-white' : 'text-gray-700 hover:bg-gray-100'"
          >
            {{ layout }}
          </button>
        </div>

        <!-- View Toggle -->
        <div class="flex space-x-2">
          <button
            @click="toggleCharts"
            class="p-2 rounded-lg transition-all duration-200"
            :class="showCharts ? 'bg-blue-500 text-white shadow-md' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
          >
            <BarChart3 class="h-4 w-4" />
          </button>
          <button
            @click="toggleDataTable"
            class="p-2 rounded-lg transition-all duration-200"
            :class="showDataTable ? 'bg-blue-500 text-white shadow-md' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'"
          >
            <Activity class="h-4 w-4" />
          </button>
        </div>

        <!-- Real-time Status -->
        <div class="flex items-center space-x-2">
          <!-- Connection Status -->
          <div class="flex items-center space-x-1">
            <div 
              class="w-2 h-2 rounded-full"
              :class="{
                'bg-green-500': isConnected,
                'bg-yellow-500': connectionStatus === 'connecting',
                'bg-red-500': connectionStatus === 'error' || connectionStatus === 'disconnected'
              }"
            ></div>
            <span class="text-xs text-gray-600">
              {{ isConnected ? 'Live' : 'Offline' }}
            </span>
          </div>
          
          <!-- Last Update Time -->
          <div v-if="timeSinceUpdate !== null" class="text-xs text-gray-500">
            {{ timeSinceUpdate < 60 ? `${timeSinceUpdate}s ago` : `${Math.floor(timeSinceUpdate / 60)}m ago` }}
          </div>
          
          <!-- Refresh Button -->
          <button
            @click="refreshData"
            :disabled="isRefreshing"
            class="p-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition-all duration-200 disabled:opacity-50"
          >
            <RefreshCw :class="{ 'animate-spin': isRefreshing }" class="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>
    </div>

    <!-- Filters -->
    <div v-if="activeContent.filters.length" class="flex gap-4 p-4 flex-wrap items-center">
      <template v-for="f in activeContent.filters" :key="f">
        <!-- Normal selects -->
        <select
          v-if="f !== 'date'"
          v-model="filters[f]"
          class="border rounded-md shadow-md px-4 py-2 bg-white text-gray-800 hover:bg-black hover:text-white transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-black focus:ring-opacity-50 cursor-pointer"
        >
          <option :value="''" disabled>Select {{ f.charAt(0).toUpperCase() + f.slice(1) }}</option>
          <option v-for="(name,id) in props.filterOptions[f]" :key="id" :value="id">{{ name }}</option>
        </select>

        <!-- Date -->
        <div v-else class="flex items-center gap-2">
          <select
            v-model="filters.date"
            class="border rounded-md shadow-md px-4 py-2 bg-white text-gray-800 hover:bg-black hover:text-white transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-black focus:ring-opacity-50 cursor-pointer"
          >
            <option :value="''" disabled>Select Date Range</option>
            <option v-for="opt in props.filterOptions.date" :key="opt" :value="opt">{{ opt }}</option>
          </select>

          <Datepicker
            v-if="filters.date === 'Custom'"
            v-model="filters.dateRange"
            :range="true"
            format="yyyy-MM-dd"
            :input-class="'border rounded-md shadow-md px-4 py-2 w-64 bg-white text-gray-800 hover:bg-black hover:text-white transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-black focus:ring-opacity-50 cursor-pointer'"
            placeholder="Select Date Range"
            :auto-apply="true"
            :calendar-position="'right-start'"
          />
        </div>
      </template>
    </div>

    <!-- Progress Bars -->
    <div 
      class="p-6 grid gap-4"
      :class="{
        'grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4': dashboardLayout === 'grid',
        'grid-cols-1': dashboardLayout === 'list',
        'grid-cols-4': dashboardLayout === 'compact'
      }"
    >
      <ProgressInfoBar
        v-for="(pb,i) in props.progressBars"
        :key="i"
        :title="pb.title"
        :progress="pb.progress"
        colorFrom="#34d399"
        colorTo="#10b981"
        :extra="pb.extra"
        :tooltip="pb.progress + '%'"
      />
      <BirdStage
        title="Birds Stage"
        :bordingTotal="props.birdStage.bordingTotal"
        :growingTotal="props.birdStage.growingTotal"
        :productionTotal="props.birdStage.productionTotal"
        bordingColor="#fbbf24"
        growingColor="#22c55e"
        productionColor="#3b82f6"
      />
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="p-6">
      <div 
        class="grid gap-4"
        :class="{
          'grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4': dashboardLayout === 'grid',
          'grid-cols-1': dashboardLayout === 'list',
          'grid-cols-4': dashboardLayout === 'compact'
        }"
      >
        <SkeletonLoader v-for="i in 8" :key="i" type="card" />
      </div>
    </div>

    <!-- Main Dashboard Content -->
    <div v-else class="p-6 space-y-6">
      <!-- Dashboard Cards with Enhanced Layout -->
      <div 
        class="grid gap-4"
        :class="{
          'grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4': dashboardLayout === 'grid',
          'grid-cols-1': dashboardLayout === 'list',
          'grid-cols-4': dashboardLayout === 'compact'
        }"
      >
        <InteractiveTooltip
          v-for="(card,i) in dashboardData.cards"
          :key="i"
          :content="`Click to view detailed information about ${card.title}`"
          placement="top"
        >
          <DashboardWidget
            :title="card.title"
            :subtitle="card.extra"
            :icon="iconMap[card.icon]"
            :data="card"
            :size="dashboardLayout === 'compact' ? 'sm' : 'md'"
            :refreshable="true"
            :configurable="true"
            :trend="card.trend ? { icon: iconMap[card.trend.icon], text: card.trend.percentage > 0 ? `+${card.trend.percentage.toFixed(1)}%` : `${card.trend.percentage.toFixed(1)}%`, color: card.trend.direction === 'up' ? 'text-green-600' : card.trend.direction === 'down' ? 'text-red-600' : 'text-gray-600' } : null"
            :last-updated="timeSinceUpdate ? `${timeSinceUpdate}s ago` : 'Just now'"
            class="cursor-pointer hover:scale-105 transition-transform duration-200"
            @click="handleCardClick(card)"
          >
            <template #default="{ data }">
              <div class="text-center">
                <div class="text-3xl font-bold text-gray-800 mb-2">{{ data.value ?? '-' }}</div>
                <div class="text-sm text-gray-600">{{ data.title }}</div>
              </div>
            </template>
          </DashboardWidget>
        </InteractiveTooltip>
      </div>

      <!-- Interactive Charts Section -->
      <div v-if="showCharts" class="space-y-6">
        <div class="flex items-center justify-between">
          <h2 class="text-xl font-semibold text-gray-800">Analytics & Insights</h2>
          <div class="flex space-x-2">
            <button class="px-3 py-1 text-sm bg-blue-100 text-blue-700 rounded-md">Export</button>
            <button class="px-3 py-1 text-sm bg-gray-100 text-gray-700 rounded-md">Settings</button>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Production Chart -->
          <InteractiveChart
            title="Egg Production Trend"
            :data="dashboardData.chartData?.production || chartData.production"
            chart-type="line"
            :show-legend="true"
          />

          <!-- Mortality Distribution -->
          <InteractiveChart
            title="Mortality Distribution"
            :data="dashboardData.chartData?.mortality || chartData.mortality"
            chart-type="doughnut"
            :show-legend="true"
          />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Egg Types Chart -->
          <InteractiveChart
            title="Egg Classification"
            :data="dashboardData.chartData?.eggTypes || chartData.eggTypes"
            chart-type="bar"
            :show-legend="true"
          />

          <!-- Circle Progress Widgets -->
          <div class="lg:col-span-2">
            <div 
              class="grid gap-4"
              :class="{
                'grid-cols-3': dashboardLayout === 'grid',
                'grid-cols-1': dashboardLayout === 'list',
                'grid-cols-4': dashboardLayout === 'compact'
              }"
            >
              <CircleProgress
                v-for="(c,i) in dashboardData.circleBars"
                :key="i"
                v-bind="c"
                colorFrom="#34D399"
                colorTo="#10B981"
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Interactive Data Table -->
      <div v-if="showDataTable">
        <InteractiveDataTable
          title="Batch Performance Overview"
          :data="tableData"
          :columns="tableColumns"
          :actions="tableActions"
          filter-key="status"
          :filter-options="[
            { value: 'Active', label: 'Active' },
            { value: 'Pending', label: 'Pending' }
          ]"
        />
      </div>

      <!-- Progress Bars Section -->
      <div class="space-y-4">
        <h3 class="text-lg font-semibold text-gray-800">Performance Metrics</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <ProgressInfoBar
            v-for="(bar,i) in dashboardData.progressBars"
            :key="i"
            v-bind="bar"
            colorFrom="#fde68a"
            colorTo="#f59e0b"
            :width="300"
            :height="40"
          />
        </div>
      </div>

      <!-- Bird Stage Visualization -->
      <div class="bg-white/40 backdrop-blur-md border border-white/50 rounded-xl shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Bird Lifecycle Distribution</h3>
        <BirdStage
          :bording-total="dashboardData.birdStage.bordingTotal"
          :growing-total="dashboardData.birdStage.growingTotal"
          :production-total="dashboardData.birdStage.productionTotal"
        />
      </div>
    </div>

    <!-- Interactive Modal -->
    <InteractiveModal
      :is-visible="showModal"
      :title="modalData?.title"
      :icon="modalData?.icon"
      :size="modalData?.flockData ? '5xl' : 'lg'"
      @update:is-visible="showModal = $event"
      @close="closeModal"
    >
      <!-- Loading State -->
      <div v-if="modalData?.loading" class="flex items-center justify-center py-12">
        <div class="text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
          <p class="text-gray-600">{{ modalData.content }}</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="modalData?.error" class="text-center py-12">
        <div class="text-red-500 text-6xl mb-4">⚠️</div>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Error Loading Data</h3>
        <p class="text-gray-600 mb-4">{{ modalData.content }}</p>
        <button 
          @click="handleFlockCardClick(modalData.data)"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          Try Again
        </button>
      </div>

      <!-- Flock Details Content -->
      <div v-else-if="modalData?.flockData" class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-sm text-blue-600 font-medium">Total Flocks</div>
            <div class="text-2xl font-bold text-blue-800">{{ modalData.flockData.total_flocks }}</div>
          </div>
          <div class="bg-green-50 p-4 rounded-lg">
            <div class="text-sm text-green-600 font-medium">Total Birds</div>
            <div class="text-2xl font-bold text-green-800">{{ modalData.flockData.summary.total_birds.toLocaleString() }}</div>
          </div>
          <div class="bg-orange-50 p-4 rounded-lg">
            <div class="text-sm text-orange-600 font-medium">Male Birds</div>
            <div class="text-2xl font-bold text-orange-800">{{ modalData.flockData.summary.total_male.toLocaleString() }}</div>
          </div>
          <div class="bg-pink-50 p-4 rounded-lg">
            <div class="text-sm text-pink-600 font-medium">Female Birds</div>
            <div class="text-2xl font-bold text-pink-800">{{ modalData.flockData.summary.total_female.toLocaleString() }}</div>
          </div>
        </div>

        <!-- Flock Details Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Flock Details</h3>
            <p class="text-sm text-gray-600">Detailed information for each active flock</p>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Flock</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Birds</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mortality</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batches</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Companies</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="flock in modalData.flockData.flocks" :key="flock.id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ flock.name }}</div>
                      <div class="text-sm text-gray-500">{{ flock.code }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ flock.total_birds.toLocaleString() }}</div>
                    <div class="text-xs text-gray-500">
                      M: {{ flock.male_birds.toLocaleString() }} | F: {{ flock.female_birds.toLocaleString() }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ flock.total_mortality.toLocaleString() }}</div>
                    <div class="text-xs text-gray-500">{{ flock.mortality_percentage }}%</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ flock.batch_assignments.length }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ flock.companies.length }}</div>
                    <div class="text-xs text-gray-500">
                      {{ flock.companies.map(c => c.name).join(', ') }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                      Active
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Recent Activity -->
        <div v-if="modalData.flockData.flocks.some(f => f.recent_operations.length > 0)" class="bg-white rounded-lg border border-gray-200">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Recent Activity</h3>
            <p class="text-sm text-gray-600">Latest operations across all flocks</p>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <div v-for="flock in modalData.flockData.flocks.filter(f => f.recent_operations.length > 0)" :key="flock.id" class="border-l-4 border-blue-500 pl-4">
                <h4 class="font-medium text-gray-900">{{ flock.name }} ({{ flock.code }})</h4>
                <div class="mt-2 space-y-2">
                  <div v-for="operation in flock.recent_operations.slice(0, 3)" :key="operation.id" class="text-sm text-gray-600">
                    <span class="font-medium">{{ operation.operation_date }}</span> - 
                    <span>{{ operation.stage }}</span> - 
                    <span class="text-gray-500">by {{ operation.created_by }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Last Updated -->
        <div class="text-center text-sm text-gray-500">
          Last updated: {{ modalData.flockData.last_updated }}
        </div>
      </div>

      <!-- Default Modal Content -->
      <div v-else-if="modalData" class="space-y-4">
        <div class="text-center">
          <div class="text-4xl font-bold text-gray-800 mb-2">{{ modalData.data?.value ?? '-' }}</div>
          <div class="text-lg text-gray-600 mb-4">{{ modalData.data?.title }}</div>
          <div class="text-sm text-gray-500">{{ modalData.content }}</div>
        </div>
        
        <div class="grid grid-cols-2 gap-4 mt-6">
          <div class="bg-gray-50 p-4 rounded-lg">
            <div class="text-sm text-gray-600">Current Value</div>
            <div class="text-xl font-semibold text-gray-800">{{ modalData.data?.value ?? '-' }}</div>
          </div>
          <div class="bg-gray-50 p-4 rounded-lg">
            <div class="text-sm text-gray-600">Trend</div>
            <div class="text-xl font-semibold text-green-600">+12%</div>
          </div>
        </div>
        
        <div class="mt-6">
          <h4 class="text-sm font-semibold text-gray-700 mb-2">Additional Information</h4>
          <p class="text-sm text-gray-600">
            This metric represents the current status of {{ modalData.data?.title?.toLowerCase() }} 
            in your flock management system. Click refresh to get the latest data.
          </p>
        </div>
      </div>
    </InteractiveModal>

    <!-- Real-time Notification (temporarily disabled) -->
    <!-- <RealtimeNotification
      :is-visible="showNotification"
      :title="notificationData.title"
      :message="notificationData.message"
      :type="notificationData.type"
      :timestamp="notificationData.timestamp"
      @update:is-visible="showNotification = $event"
      @close="closeNotification"
    /> -->

  </AppLayout>
</template>
