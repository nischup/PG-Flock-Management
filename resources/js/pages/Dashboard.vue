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
  filters: Record<string,string>,
  realtimeData: any
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
  { key: 'age', label: 'Age', type: 'text' },
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
  } else if (card.title === 'Current Birds') {
    handleBirdsCardClick(card)
  } else if (card.title === 'Mortality Rate') {
    handleMortalityCardClick(card)
  } else if (card.title === 'Daily Eggs') {
    handleDailyEggsCardClick(card)
  } else if (card.title === 'Hatchable Eggs') {
    handleHatchableEggsCardClick(card)
  } else if (card.title === 'Male Birds') {
    handleMaleBirdsCardClick(card)
  } else if (card.title === 'Female Birds') {
    handleFemaleBirdsCardClick(card)
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

const handleBirdsCardClick = async (card: any) => {
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
      title: 'Loading Birds Details...',
      content: 'Fetching detailed birds information by batch...',
      data: card,
      loading: true
    }
    showModal.value = true
   
    // Fetch detailed birds data
    const response = await fetch(`/api/dashboard/birds-details?${new URLSearchParams(filters.value)}`)
    const result = await response.json()
    
    if (result.success) {
      modalData.value = {
        title: 'Total Birds Details',
        content: 'Comprehensive birds information organized by batch',
        data: card,
        birdsData: result.data,
        loading: false
      }
    } else {
      throw new Error(result.message || 'Failed to fetch birds details')
    }
  } catch (error) {
    console.error('Error fetching birds details:', error)
    modalData.value = {
      title: 'Error',
      content: 'Failed to load birds details. Please try again.',
      data: card,
      error: true,
      loading: false
    }
  }
}

const handleMortalityCardClick = async (card: any) => {
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
      title: 'Loading Mortality Details...',
      content: 'Fetching detailed mortality information...',
      data: card,
      loading: true
    }
    showModal.value = true

    // Fetch detailed mortality data
    const response = await fetch(`/api/dashboard/mortality-details?${new URLSearchParams(filters.value)}`)
    const result = await response.json()
    
    if (result.success) {
      modalData.value = {
        title: 'Mortality Rate Details',
        content: 'Comprehensive mortality analysis and trends',
        data: card,
        mortalityData: result.data,
        loading: false
      }
    } else {
      throw new Error(result.message || 'Failed to fetch mortality details')
    }
  } catch (error) {
    console.error('Error fetching mortality details:', error)
    modalData.value = {
      title: 'Error',
      content: 'Failed to load mortality details. Please try again.',
      data: card,
      error: true,
      loading: false
    }
  }
}

const handleDailyEggsCardClick = async (card: any) => {
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
      title: 'Loading Daily Eggs Details...',
      content: 'Fetching detailed egg production information...',
      data: card,
      loading: true
    }
    showModal.value = true

    // Fetch detailed eggs data
    const response = await fetch(`/api/dashboard/daily-eggs-details?${new URLSearchParams(filters.value)}`)
    const result = await response.json()
    
    if (result.success) {
      modalData.value = {
        title: 'Daily Eggs Details',
        content: 'Comprehensive egg production analysis and trends',
        data: card,
        eggsData: result.data,
        loading: false
      }
    } else {
      throw new Error(result.message || 'Failed to fetch daily eggs details')
    }
  } catch (error) {
    console.error('Error fetching daily eggs details:', error)
    modalData.value = {
      title: 'Error',
      content: 'Failed to load daily eggs details. Please try again.',
      data: card,
      error: true,
      loading: false
    }
  }
}

const handleHatchableEggsCardClick = async (card: any) => {
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
      title: 'Loading Hatchable Eggs Details...',
      content: 'Fetching detailed hatchable eggs information...',
      data: card,
      loading: true
    }
    showModal.value = true

    // Fetch detailed hatchable eggs data
    const response = await fetch(`/api/dashboard/hatchable-eggs-details?${new URLSearchParams(filters.value)}`)
    const result = await response.json()
    
    if (result.success) {
      modalData.value = {
        title: 'Hatchable Eggs Details',
        content: 'Comprehensive hatchable eggs analysis and trends',
        data: card,
        hatchableEggsData: result.data,
        loading: false
      }
    } else {
      throw new Error(result.message || 'Failed to fetch hatchable eggs details')
    }
  } catch (error) {
    console.error('Error fetching hatchable eggs details:', error)
    modalData.value = {
      title: 'Error',
      content: 'Failed to load hatchable eggs details. Please try again.',
      data: card,
      error: true,
      loading: false
    }
  }
}

const handleMaleBirdsCardClick = async (card: any) => {
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
      title: 'Loading Male Birds Details...',
      content: 'Fetching detailed male birds information...',
      data: card,
      loading: true
    }
    showModal.value = true

    // Fetch detailed male birds data
    const response = await fetch(`/api/dashboard/male-birds-details?${new URLSearchParams(filters.value)}`)
    const result = await response.json()
    
    if (result.success) {
      modalData.value = {
        title: 'Male Birds Details',
        content: 'Comprehensive male birds analysis and trends',
        data: card,
        maleBirdsData: result.data,
        loading: false
      }
    } else {
      throw new Error(result.message || 'Failed to fetch male birds details')
    }
  } catch (error) {
    console.error('Error fetching male birds details:', error)
    modalData.value = {
      title: 'Error',
      content: 'Failed to load male birds details. Please try again.',
      data: card,
      error: true,
      loading: false
    }
  }
}

const handleFemaleBirdsCardClick = async (card: any) => {
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
      title: 'Loading Female Birds Details...',
      content: 'Fetching detailed female birds information...',
      data: card,
      loading: true
    }
    showModal.value = true

    // Fetch detailed female birds data
    const response = await fetch(`/api/dashboard/female-birds-details?${new URLSearchParams(filters.value)}`)
    const result = await response.json()
    
    if (result.success) {
      modalData.value = {
        title: 'Female Birds Details',
        content: 'Comprehensive female birds analysis and trends',
        data: card,
        femaleBirdsData: result.data,
        loading: false
      }
    } else {
      throw new Error(result.message || 'Failed to fetch female birds details')
    }
  } catch (error) {
    console.error('Error fetching female birds details:', error)
    modalData.value = {
      title: 'Error',
      content: 'Failed to load female birds details. Please try again.',
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
  return props.realtimeData || {
    cards: props.cards,
    progressBars: props.progressBars,
    circleBars: props.circleBars,
    birdStage: props.birdStage,
    chartData: chartData.value
  }
})

console.log(dashboardData);

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
      :size="modalData?.flockData || modalData?.birdsData || modalData?.mortalityData || modalData?.eggsData || modalData?.hatchableEggsData || modalData?.maleBirdsData || modalData?.femaleBirdsData ? '5xl' : 'lg'"
      :show-confirm="false"
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
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assign Birds</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mortality</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Other Rejections</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Birds</th>
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
                    <div class="text-sm text-gray-900">{{ flock.total_assign_bird.toLocaleString() }}</div>
                    <div class="text-xs text-gray-500">
                      M: {{ flock.assign_male_birds.toLocaleString() }} | F: {{ flock.assign_female_birds.toLocaleString() }}
                    </div>
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ flock.total_mortality.toLocaleString() }}</div>
                    <div class="text-xs text-gray-500">{{ flock.mortality_percentage }}%</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ flock.others_rejection.toLocaleString() }}</div>
                    <div class="text-xs text-gray-500">{{ flock.rejection_precentage }}%</div>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ flock.total_birds.toLocaleString() }}</div>
                    <div class="text-xs text-gray-500">
                      M: {{ flock.male_birds.toLocaleString() }} | F: {{ flock.female_birds.toLocaleString() }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ flock.batch_assignments.map(d => d.batch_name).join(', ') }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
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

      <!-- Birds Details Content -->
      <div v-else-if="modalData?.birdsData" class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-sm text-blue-600 font-medium">Assign Total Birds</div>
            <div class="text-2xl font-bold text-blue-800">{{ modalData.birdsData.summary.total_birds?.toLocaleString() }}</div>
          </div>
          <div class="bg-orange-50 p-4 rounded-lg">
            <div class="text-sm text-orange-600 font-medium">Male Birds</div>
            <div class="text-2xl font-bold text-orange-800">{{ modalData.birdsData.summary.total_male_birds?.toLocaleString() }}</div>
          </div>
          <div class="bg-pink-50 p-4 rounded-lg">
            <div class="text-sm text-pink-600 font-medium">Female Birds</div>
            <div class="text-2xl font-bold text-pink-800">{{ modalData.birdsData.summary.total_female_birds?.toLocaleString() }} </div>
          </div>
          <div class="bg-red-50 p-4 rounded-lg">
            <div class="text-sm text-red-600 font-medium">Assign F/M Percentage</div>
            <div class="text-xl font-bold text-red-800"> F-{{ modalData.birdsData.summary?.assign_female_percentage }}%, M-{{ modalData.birdsData.summary?.assign_male_percentage }} %</div>
          </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-sm text-blue-600 font-medium">Current Total Birds</div>
            <div class="text-2xl font-bold text-blue-800">{{ modalData.birdsData.summary.current_total_birds?.toLocaleString() }}</div>
          </div>
          <div class="bg-orange-50 p-4 rounded-lg">
            <div class="text-sm text-orange-600 font-medium">Current Male Birds</div>
            <div class="text-2xl font-bold text-orange-800">{{ modalData.birdsData.summary.current_male_birds?.toLocaleString() }}</div>
          </div>
          <div class="bg-pink-50 p-4 rounded-lg">
            <div class="text-sm text-pink-600 font-medium">Current Female Birds</div>
            <div class="text-2xl font-bold text-pink-800">{{ modalData.birdsData.summary.current_female_birds?.toLocaleString() }}</div>
          </div>
          <div class="bg-red-50 p-4 rounded-lg">
            <div class="text-sm text-red-600 font-medium">Current F/M Percentage</div>
            <div class="text-xl font-bold text-red-800">F-{{ modalData.birdsData.summary?.current_female_percentage }}%, M-{{ modalData.birdsData.summary?.current_male_percentage }} %</div>
          </div>
        </div>

        <!-- Additional Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
          <div class="bg-green-50 p-4 rounded-lg">
            <div class="text-sm text-green-600 font-medium">Total Batches</div>
            <div class="text-xl font-bold text-green-800">{{ modalData.birdsData.summary?.total_batches }}</div>
          </div>
          <div class="bg-purple-50 p-4 rounded-lg">
            <div class="text-sm text-purple-600 font-medium">Active Batches</div>
            <div class="text-xl font-bold text-purple-800">{{ modalData.birdsData.summary?.active_batches }}</div>
          </div>
          <div class="bg-indigo-50 p-4 rounded-lg">
            <div class="text-sm text-indigo-600 font-medium">Total Assignments</div>
            <div class="text-xl font-bold text-indigo-800">{{ modalData.birdsData.summary?.total_assignments }}</div>
          </div>
        </div>

        <!-- Batch Details Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Birds by Batch</h3>
            <p class="text-sm text-gray-600">Detailed breakdown of birds organized by batch</p>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Flock</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CurrentTotal Birds</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Male/Female</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mortality</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Other Rejectios</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shed</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="batch in modalData.birdsData.batch_details" :key="batch.batch_id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ batch.batch_name }}</div>
                    <div class="text-xs text-gray-500">{{ batch.assignments_count }} assignment(s)</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ batch.age }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.flock_name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ batch.current_total_birds?.toLocaleString() }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">
                      <div>M: {{ batch.current_male_birds?.toLocaleString() }}</div>
                      <div>F: {{ batch.current_female_birds?.toLocaleString() }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.total_mortality?.toLocaleString() }}</div>
                    <div class="text-xs text-gray-500">{{ batch.total_mortality_percentage }}%</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.total_other_rejection?.toLocaleString() }}</div>
                    <div class="text-xs text-gray-500">{{ batch.total_other_rejection_percentage }}%</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.company_name }}</div>
                    <div class="text-xs text-gray-500">{{ batch.project_name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.shed_name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="batch.status === 'Active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" 
                          class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ batch.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Recent Operations -->
        <div v-if="modalData.birdsData?.recent_operations?.length > 0" class="bg-white rounded-lg border border-gray-200">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Recent Operations</h3>
            <p class="text-sm text-gray-600">Latest operations across all batches</p>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <div v-for="operation in modalData.birdsData.recent_operations" :key="operation.id" 
                   class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg">
                <div class="flex-shrink-0">
                  <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900">{{ operation.operation_type }}</p>
                    <p class="text-xs text-gray-500">{{ operation.operation_date }}</p>
                  </div>
                  <p class="text-sm text-gray-600 mt-1">{{ operation.description }}</p>
                  <div class="mt-2 text-xs text-gray-500">
                    <span class="font-medium">{{ operation.batch_name }}</span> in 
                    <span class="font-medium">{{ operation.shed_name }}</span> - 
                    <span class="font-medium">{{ operation.flock_name }}</span>
                    <span v-if="operation.birds_affected > 0"> ({{ operation.birds_affected }} birds affected)</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Last Updated -->
        <div class="text-center text-sm text-gray-500">
          Last updated: {{ new Date(modalData.birdsData.timestamp * 1000).toLocaleString() }}
        </div>
      </div>

      <!-- Mortality Details Content -->
      <div v-else-if="modalData?.mortalityData" class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-red-50 p-4 rounded-lg">
            <div class="text-sm text-red-600 font-medium">Total Mortality</div>
            <div class="text-2xl font-bold text-red-800">{{ modalData.mortalityData.summary.total_mortality.toLocaleString() }}</div>
          </div>
          <div class="bg-orange-50 p-4 rounded-lg">
            <div class="text-sm text-orange-600 font-medium">Male Mortality</div>
            <div class="text-2xl font-bold text-orange-800">{{ modalData.mortalityData.summary.male_mortality.toLocaleString() }}</div>
          </div>
          <div class="bg-pink-50 p-4 rounded-lg">
            <div class="text-sm text-pink-600 font-medium">Female Mortality</div>
            <div class="text-2xl font-bold text-pink-800">{{ modalData.mortalityData.summary.female_mortality.toLocaleString() }}</div>
          </div>
          <div class="bg-purple-50 p-4 rounded-lg">
            <div class="text-sm text-purple-600 font-medium">Overall Rate</div>
            <div class="text-2xl font-bold text-purple-800">{{ modalData.mortalityData.summary.overall_mortality_rate }}%</div>
          </div>
        </div>

        <!-- Risk Assessment Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-green-50 p-4 rounded-lg">
            <div class="text-sm text-green-600 font-medium">Excellent Flocks</div>
            <div class="text-xl font-bold text-green-800">{{ modalData.mortalityData.summary.excellent_flocks }}</div>
            <div class="text-xs text-green-600">≤ 2% mortality</div>
          </div>
          <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-sm text-blue-600 font-medium">Good Flocks</div>
            <div class="text-xl font-bold text-blue-800">{{ modalData.mortalityData.summary.good_flocks }}</div>
            <div class="text-xs text-blue-600">2-5% mortality</div>
          </div>
          <div class="bg-yellow-50 p-4 rounded-lg">
            <div class="text-sm text-yellow-600 font-medium">Moderate Risk</div>
            <div class="text-xl font-bold text-yellow-800">{{ modalData.mortalityData.summary.moderate_flocks }}</div>
            <div class="text-xs text-yellow-600">5-10% mortality</div>
          </div>
          <div class="bg-red-50 p-4 rounded-lg">
            <div class="text-sm text-red-600 font-medium">High Risk</div>
            <div class="text-xl font-bold text-red-800">{{ modalData.mortalityData.summary.high_risk_flocks }}</div>
            <div class="text-xs text-red-600">> 10% mortality</div>
          </div>
        </div>

        <!-- Flock Mortality Details Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Mortality by Flock</h3>
            <p class="text-sm text-gray-600">Detailed mortality breakdown for each flock</p>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Flock</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Birds</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mortality</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Male/Female</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batches</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risk Level</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="flock in modalData.mortalityData.flock_details" :key="flock.flock_id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ flock.flock_name }}</div>
                      <div class="text-sm text-gray-500">{{ flock.flock_code }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ flock.total_birds.toLocaleString() }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ flock.total_mortality.toLocaleString() }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ flock.mortality_rate }}%</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">
                      <div>M: {{ flock.male_mortality.toLocaleString() }}</div>
                      <div>F: {{ flock.female_mortality.toLocaleString() }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ flock.batches_count }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="{
                      'bg-green-100 text-green-800': flock.mortality_rate <= 2,
                      'bg-blue-100 text-blue-800': flock.mortality_rate > 2 && flock.mortality_rate <= 5,
                      'bg-yellow-100 text-yellow-800': flock.mortality_rate > 5 && flock.mortality_rate <= 10,
                      'bg-red-100 text-red-800': flock.mortality_rate > 10
                    }" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ flock.mortality_rate <= 2 ? 'Excellent' : 
                         flock.mortality_rate <= 5 ? 'Good' : 
                         flock.mortality_rate <= 10 ? 'Moderate' : 'High Risk' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Batch Mortality Details Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Mortality by Batch</h3>
            <p class="text-sm text-gray-600">Detailed mortality breakdown for each batch</p>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Flock</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Birds</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mortality</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shed</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="batch in modalData.mortalityData.batch_details" :key="batch.batch_no" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ batch.batch_name }}</div>
                    <div class="text-xs text-gray-500">{{ batch.assignments_count }} assignment(s)</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.flock_name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.total_birds.toLocaleString() }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ batch.total_mortality.toLocaleString() }}</div>
                    <div class="text-xs text-gray-500">
                      M: {{ batch.male_mortality.toLocaleString() }} | F: {{ batch.female_mortality.toLocaleString() }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ batch.mortality_rate }}%</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.company_name }}</div>
                    <div class="text-xs text-gray-500">{{ batch.project_name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.shed_name }}</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Recent Mortality Operations -->
        <div v-if="modalData.mortalityData?.recent_operations?.length > 0" class="bg-white rounded-lg border border-gray-200">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Recent Mortality Operations</h3>
            <p class="text-sm text-gray-600">Latest mortality-related operations and incidents</p>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <div v-for="operation in modalData.mortalityData.recent_operations" :key="operation.id" 
                   class="flex items-start space-x-4 p-4 bg-red-50 rounded-lg border-l-4 border-red-400">
                <div class="flex-shrink-0">
                  <div class="w-2 h-2 bg-red-500 rounded-full mt-2"></div>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900">{{ operation.operation_type }}</p>
                    <p class="text-xs text-gray-500">{{ operation.operation_date }}</p>
                  </div>
                  <p class="text-sm text-gray-600 mt-1">{{ operation.description }}</p>
                  <div class="mt-2 text-xs text-gray-500">
                    <span class="font-medium">{{ operation.batch_name }}</span> in 
                    <span class="font-medium">{{ operation.shed_name }}</span> - 
                    <span class="font-medium">{{ operation.flock_name }}</span>
                    <span class="text-gray-400"> • by {{ operation.created_by }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Last Updated -->
        <div class="text-center text-sm text-gray-500">
          Last updated: {{ new Date(modalData.mortalityData.timestamp * 1000).toLocaleString() }}
        </div>
      </div>

      <!-- Daily Eggs Details Content -->
      <div v-else-if="modalData?.eggsData" class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-sm text-blue-600 font-medium">Total Eggs</div>
            <div class="text-2xl font-bold text-blue-800">{{ modalData.eggsData.summary.total_eggs.toLocaleString() }}</div>
          </div>
          <div class="bg-green-50 p-4 rounded-lg">
            <div class="text-sm text-green-600 font-medium">Commercial Eggs</div>
            <div class="text-2xl font-bold text-green-800">{{ modalData.eggsData.summary.commercial_eggs.toLocaleString() }}</div>
            <div class="text-xs text-green-600">{{ modalData.eggsData.summary.commercial_percentage }}% of total</div>
          </div>
          <div class="bg-orange-50 p-4 rounded-lg">
            <div class="text-sm text-orange-600 font-medium">Technical Eggs</div>
            <div class="text-2xl font-bold text-orange-800">{{ modalData.eggsData.summary.technical_eggs?.toLocaleString() }}</div>
          </div>
          <div class="bg-purple-50 p-4 rounded-lg">
            <div class="text-sm text-purple-600 font-medium">Hatching Eggs</div>
            <div class="text-2xl font-bold text-purple-800">{{ modalData.eggsData.summary.hatching_eggs.toLocaleString() }}</div>
          </div>
        </div>

        <!-- Additional Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-red-50 p-4 rounded-lg">
            <div class="text-sm text-red-600 font-medium">Rejected Eggs</div>
            <div class="text-xl font-bold text-red-800">{{ modalData.eggsData.summary.rejected_eggs.toLocaleString() }}</div>
            <div class="text-xs text-red-600">{{ modalData.eggsData.summary.rejection_rate }}% rejection rate</div>
          </div>
          <div class="bg-indigo-50 p-4 rounded-lg">
            <div class="text-sm text-indigo-600 font-medium">Total Flocks</div>
            <div class="text-xl font-bold text-indigo-800">{{ modalData.eggsData.summary.total_flocks }}</div>
          </div>
          <div class="bg-teal-50 p-4 rounded-lg">
            <div class="text-sm text-teal-600 font-medium">Total Batches</div>
            <div class="text-xl font-bold text-teal-800">{{ modalData.eggsData.summary.total_batches }}</div>
          </div>
          <div class="bg-cyan-50 p-4 rounded-lg">
            <div class="text-sm text-cyan-600 font-medium">Classifications</div>
            <div class="text-xl font-bold text-cyan-800">{{ modalData.eggsData.summary.total_classifications }}</div>
          </div>
        </div>

        <!-- Production Performance Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-green-50 p-4 rounded-lg">
            <div class="text-sm text-green-600 font-medium">Excellent Flocks</div>
            <div class="text-xl font-bold text-green-800">{{ modalData.eggsData.summary.excellent_flocks }}</div>
            <div class="text-xs text-green-600">≥ 80% commercial</div>
          </div>
          <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-sm text-blue-600 font-medium">Good Flocks</div>
            <div class="text-xl font-bold text-blue-800">{{ modalData.eggsData.summary.good_flocks }}</div>
            <div class="text-xs text-blue-600">70-79% commercial</div>
          </div>
          <div class="bg-yellow-50 p-4 rounded-lg">
            <div class="text-sm text-yellow-600 font-medium">Moderate Flocks</div>
            <div class="text-xl font-bold text-yellow-800">{{ modalData.eggsData.summary.moderate_flocks }}</div>
            <div class="text-xs text-yellow-600">60-69% commercial</div>
          </div>
          <div class="bg-red-50 p-4 rounded-lg">
            <div class="text-sm text-red-600 font-medium">Poor Flocks</div>
            <div class="text-xl font-bold text-red-800">{{ modalData.eggsData.summary.poor_flocks }}</div>
            <div class="text-xs text-red-600">< 60% commercial</div>
          </div>
        </div>

        <!-- Flock Egg Details Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Egg Production by Flock</h3>
            <p class="text-sm text-gray-600">Detailed egg production breakdown for each flock</p>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Flock</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Eggs</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commercial</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Technical</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hatching</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rejected</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="flock in modalData.eggsData.flock_details" :key="flock.flock_id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ flock.flock_name }}</div>
                      <div class="text-sm text-gray-500">{{ flock.flock_code }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ flock.total_eggs.toLocaleString() }}</div>
                    <div class="text-xs text-gray-500">{{ flock.classifications_count }} classifications</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ flock.commercial_eggs.toLocaleString() }}</div>
                    <div class="text-xs text-gray-500">{{ flock.commercial_percentage }}%</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ flock.technical_eggs?.toLocaleString() }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ flock.hatching_eggs.toLocaleString() }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ flock.rejected_eggs.toLocaleString() }}</div>
                    <div class="text-xs text-gray-500">{{ flock.rejection_rate }}%</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="{
                      'bg-green-100 text-green-800': flock.commercial_percentage >= 80,
                      'bg-blue-100 text-blue-800': flock.commercial_percentage >= 70 && flock.commercial_percentage < 80,
                      'bg-yellow-100 text-yellow-800': flock.commercial_percentage >= 60 && flock.commercial_percentage < 70,
                      'bg-red-100 text-red-800': flock.commercial_percentage < 60
                    }" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ flock.commercial_percentage >= 80 ? 'Excellent' : 
                         flock.commercial_percentage >= 70 ? 'Good' : 
                         flock.commercial_percentage >= 60 ? 'Moderate' : 'Poor' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Batch Egg Details Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Egg Production by Batch</h3>
            <p class="text-sm text-gray-600">Detailed egg production breakdown for each batch</p>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Flock</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Eggs</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Commercial</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Technical</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hatching</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rejected</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="batch in modalData.eggsData.batch_details" :key="batch.batch_no" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ batch.batch_name }}</div>
                    <div class="text-xs text-gray-500">{{ batch.classifications_count }} classifications</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.flock_name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ batch.total_eggs.toLocaleString() }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.commercial_eggs.toLocaleString() }}</div>
                    <div class="text-xs text-gray-500">{{ batch.commercial_percentage }}%</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.technical_eggs?.toLocaleString() }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.hatching_eggs.toLocaleString() }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.rejected_eggs.toLocaleString() }}</div>
                    <div class="text-xs text-gray-500">{{ batch.rejection_rate }}%</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.company_name }}</div>
                    <div class="text-xs text-gray-500">{{ batch.project_name }}</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Recent Egg Classifications -->
        <div v-if="modalData.eggsData?.recent_classifications?.length > 0" class="bg-white rounded-lg border border-gray-200">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Recent Egg Classifications</h3>
            <p class="text-sm text-gray-600">Latest egg classification records</p>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <div v-for="classification in modalData.eggsData.recent_classifications" :key="classification.id" 
                   class="flex items-start space-x-4 p-4 bg-blue-50 rounded-lg border-l-4 border-blue-400">
                <div class="flex-shrink-0">
                  <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900">Egg Classification</p>
                    <p class="text-xs text-gray-500">{{ classification.classification_date }}</p>
                  </div>
                  <div class="mt-2 grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                      <span class="text-gray-600">Total:</span>
                      <span class="font-medium">{{ classification.total_eggs.toLocaleString() }}</span>
                    </div>
                    <div>
                      <span class="text-gray-600">Commercial:</span>
                      <span class="font-medium text-green-600">{{ classification.commercial_eggs.toLocaleString() }}</span>
                    </div>
                    <div>
                      <span class="text-gray-600">Technical:</span>
                      <span class="font-medium text-orange-600">{{ classification.technical_eggs?.toLocaleString() }}</span>
                    </div>
                    <div>
                      <span class="text-gray-600">Hatching:</span>
                      <span class="font-medium text-purple-600">{{ classification.hatching_eggs.toLocaleString() }}</span>
                    </div>
                  </div>
                  <div class="mt-2 text-xs text-gray-500">
                    <span class="font-medium">{{ classification.batch_name }}</span> in 
                    <span class="font-medium">{{ classification.shed_name }}</span> - 
                    <span class="font-medium">{{ classification.flock_name }}</span>
                    <span class="text-gray-400"> • by {{ classification.created_by }}</span>
                  </div>
                  <div v-if="classification.remarks" class="mt-2 text-xs text-gray-600 italic">
                    "{{ classification.remarks }}"
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Last Updated -->
        <div class="text-center text-sm text-gray-500">
          Last updated: {{ new Date(modalData.eggsData.timestamp * 1000).toLocaleString() }}
        </div>
      </div>

      <!-- Hatchable Eggs Details Content -->
      <div v-else-if="modalData?.hatchableEggsData" class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-sm text-blue-600 font-medium">Total Eggs</div>
            <div class="text-2xl font-bold text-blue-800">{{ modalData.hatchableEggsData.summary.total_eggs.toLocaleString() }}</div>
          </div>
          <div class="bg-purple-50 p-4 rounded-lg">
            <div class="text-sm text-purple-600 font-medium">Total Hatchable Eggs</div>
            <div class="text-2xl font-bold text-purple-800">{{ modalData.hatchableEggsData.summary.total_hatching_eggs.toLocaleString() }}</div>
          </div>
          <div class="bg-green-50 p-4 rounded-lg">
            <div class="text-sm text-green-600 font-medium">Hatching Rate</div>
            <div class="text-2xl font-bold text-green-800">{{ modalData.hatchableEggsData.summary.hatching_percentage }}%</div>
          </div>
          <div class="bg-cyan-50 p-4 rounded-lg">
            <div class="text-sm text-cyan-600 font-medium">Classifications</div>
            <div class="text-xl font-bold text-cyan-800">{{ modalData.hatchableEggsData.summary.total_classifications }}</div>
          </div>
        </div>

        <!-- Priority System Info -->
        <div class="bg-gradient-to-r from-purple-50 to-blue-50 p-4 rounded-lg border border-purple-200">
          <div class="flex items-center space-x-3">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                <span class="text-purple-600 font-bold">1</span>
              </div>
            </div>
            <div class="flex-1">
              <h3 class="text-sm font-semibold text-gray-800">Priority System</h3>
              <p class="text-xs text-gray-600">
                <span class="font-medium text-purple-600">Hatchable Eggs</span> are the first priority (95%+ = Excellent, 80-95% = Good, 70-79% = Moderate, <70% = Poor), 
                <span class="font-medium text-gray-600">Commercial Eggs</span> are the second priority (lower percentages = better for breeding)
              </p>
            </div>
          </div>
        </div>

        <!-- Hatching Performance Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-green-50 p-4 rounded-lg">
            <div class="text-sm text-green-600 font-medium">Excellent Flocks</div>
            <div class="text-xl font-bold text-green-800">{{ modalData.hatchableEggsData.summary.excellent_flocks }}</div>
            <div class="text-xs text-green-600">≥ 95% hatching rate</div>
            <div class="text-xs text-gray-500">Priority: Hatchable</div>
          </div>
          <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-sm text-blue-600 font-medium">Good Flocks</div>
            <div class="text-xl font-bold text-blue-800">{{ modalData.hatchableEggsData.summary.good_flocks }}</div>
            <div class="text-xs text-blue-600">80-95% hatching rate</div>
            <div class="text-xs text-gray-500">Priority: Hatchable</div>
          </div>
          <div class="bg-yellow-50 p-4 rounded-lg">
            <div class="text-sm text-yellow-600 font-medium">Moderate Flocks</div>
            <div class="text-xl font-bold text-yellow-800">{{ modalData.hatchableEggsData.summary.moderate_flocks }}</div>
            <div class="text-xs text-yellow-600">70-79% hatching rate</div>
            <div class="text-xs text-gray-500">Priority: Hatchable</div>
          </div>
          <div class="bg-red-50 p-4 rounded-lg">
            <div class="text-sm text-red-600 font-medium">Poor Flocks</div>
            <div class="text-xl font-bold text-red-800">{{ modalData.hatchableEggsData.summary.poor_flocks }}</div>
            <div class="text-xs text-red-600">< 70% hatching rate</div>
            <div class="text-xs text-gray-500">Priority: Commercial</div>
          </div>
        </div>

        <!-- Flock Hatching Details Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Hatchable Eggs by Flock</h3>
            <p class="text-sm text-gray-600">Detailed hatchable eggs breakdown for each flock</p>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Flock</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hatchable Eggs</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Eggs</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hatching Rate</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Classifications</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="flock in modalData.hatchableEggsData.flock_details" :key="flock.flock_id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ flock.flock_name }}</div>
                      <div class="text-sm text-gray-500">{{ flock.flock_code }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-purple-900">{{ flock.total_hatching_eggs.toLocaleString() }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ flock.total_eggs.toLocaleString() }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ flock.hatching_percentage }}%</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ flock.classifications_count }}</div>
                    <div class="text-xs text-gray-500">Last: {{ flock.last_classification_date }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="{
                      'bg-green-100 text-green-800': flock.hatching_percentage >= 95,
                      'bg-blue-100 text-blue-800': flock.hatching_percentage >= 80 && flock.hatching_percentage < 95,
                      'bg-yellow-100 text-yellow-800': flock.hatching_percentage >= 70 && flock.hatching_percentage < 80,
                      'bg-red-100 text-red-800': flock.hatching_percentage < 70
                    }" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ flock.hatching_percentage >= 95 ? 'Excellent' : 
                         flock.hatching_percentage >= 80 ? 'Good' : 
                         flock.hatching_percentage >= 70 ? 'Moderate' : 'Poor' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Batch Hatching Details Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Hatchable Eggs by Batch</h3>
            <p class="text-sm text-gray-600">Detailed hatchable eggs breakdown for each batch</p>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Flock</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hatchable Eggs</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Eggs</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hatching Rate</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="batch in modalData.hatchableEggsData.batch_details" :key="batch.batch_no" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ batch.batch_name }}</div>
                    <div class="text-xs text-gray-500">{{ batch.classifications_count }} classifications</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.flock_name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-purple-900">{{ batch.total_hatching_eggs.toLocaleString() }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.total_eggs.toLocaleString() }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ batch.hatching_percentage }}%</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.company_name }}</div>
                    <div class="text-xs text-gray-500">{{ batch.project_name }}</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Recent Hatching Classifications -->
        <div v-if="modalData.hatchableEggsData?.recent_classifications?.length > 0" class="bg-white rounded-lg border border-gray-200">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Recent Hatching Egg Classifications</h3>
            <p class="text-sm text-gray-600">Latest hatchable egg classification records</p>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <div v-for="classification in modalData.hatchableEggsData.recent_classifications" :key="classification.id" 
                   class="flex items-start space-x-4 p-4 bg-purple-50 rounded-lg border-l-4 border-purple-400">
                <div class="flex-shrink-0">
                  <div class="w-2 h-2 bg-purple-500 rounded-full mt-2"></div>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900">Hatching Egg Classification</p>
                    <p class="text-xs text-gray-500">{{ classification.classification_date }}</p>
                  </div>
                  <div class="mt-2 grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                    <div>
                      <span class="text-gray-600">Hatchable:</span>
                      <span class="font-medium text-purple-600">{{ classification.total_hatching_eggs.toLocaleString() }}</span>
                    </div>
                    <div>
                      <span class="text-gray-600">Total:</span>
                      <span class="font-medium">{{ classification.total_eggs.toLocaleString() }}</span>
                    </div>
                    <div>
                      <span class="text-gray-600">Rate:</span>
                      <span class="font-medium text-green-600">{{ classification.hatching_percentage }}%</span>
                    </div>
                  </div>
                  <div class="mt-2 text-xs text-gray-500">
                    <span class="font-medium">{{ classification.batch_name }}</span> in 
                    <span class="font-medium">{{ classification.shed_name }}</span> - 
                    <span class="font-medium">{{ classification.flock_name }}</span>
                    <span class="text-gray-400"> • by {{ classification.created_by }}</span>
                  </div>
                  <div class="mt-1 text-xs text-purple-600 font-medium">
                    Hatching Rate: {{ classification.hatching_percentage }}%
                  </div>
                  <div v-if="classification.remarks" class="mt-2 text-xs text-gray-600 italic">
                    "{{ classification.remarks }}"
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Last Updated -->
        <div class="text-center text-sm text-gray-500">
          Last updated: {{ new Date(modalData.hatchableEggsData.timestamp * 1000).toLocaleString() }}
        </div>
      </div>

      <!-- Male Birds Details Content -->
      <div v-else-if="modalData?.maleBirdsData" class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

          <div class="bg-gray-50 p-4 rounded-lg">
            <div class="flex items-center space-x-2 mb-2">
              <div class="text-gray-600">🐣</div>
              <div class="text-sm text-gray-600 font-medium">Assign Total Birds</div>
            </div>
            <div class="text-2xl font-bold text-gray-800">{{ modalData.maleBirdsData.summary.total_birds.toLocaleString() }}</div>
          </div>
          
          <div class="bg-gray-50 p-4 rounded-lg">
            <div class="flex items-center space-x-2 mb-2">
              <div class="text-gray-600">🐣</div>
              <div class="text-sm text-gray-600 font-medium">Assign Male Birds</div>
            </div>
            <div class="text-2xl font-bold text-gray-800">{{ modalData.maleBirdsData.summary.total_male_birds.toLocaleString() }}</div>
          </div>
          
          
          <div class="bg-green-50 p-4 rounded-lg">
            <div class="flex items-center space-x-2 mb-2">
              <div class="text-green-600">📊</div>
              <div class="text-sm text-green-600 font-medium">Assign Male Percentage</div>
            </div>
            <div class="text-2xl font-bold text-green-800">{{ modalData.maleBirdsData.summary.assign_male_percentage }}%</div>
          </div>
          
          
          <div class="bg-pink-50 p-4 rounded-lg">
            <div class="flex items-center space-x-2 mb-2">
              <div class="text-pink-600">🐣</div>
              <div class="text-sm text-pink-600 font-medium">Current Total Birds</div>
            </div>
            <div class="text-2xl font-bold text-pink-800">{{ modalData.maleBirdsData.summary.current_total_birds.toLocaleString() }}</div>
          </div>

          <div class="bg-blue-50 p-4 rounded-lg">
            <div class="flex items-center space-x-2 mb-2">
              <div class="text-blue-600">🐣</div>
              <div class="text-sm text-blue-600 font-medium">Current Male Birds</div>
            </div>
            <div class="text-2xl font-bold text-blue-800">{{ modalData.maleBirdsData.summary.current_male_birds.toLocaleString() }}</div>
          </div>
          <div class="bg-blue-50 p-4 rounded-lg">
            <div class="flex items-center space-x-2 mb-2">
              <div class="text-blue-600">🐣</div>
              <div class="text-sm text-blue-600 font-medium">Current Male Percentage</div>
            </div>
            <div class="text-2xl font-bold text-blue-800">{{ modalData.maleBirdsData.summary.current_male_percentage.toLocaleString() }} %</div>
          </div>
        </div>
        

        <!-- Additional Stats -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
          <div class="bg-red-50 p-4 rounded-lg">
            <div class="text-sm text-red-600 font-medium">Male Mortality</div>
            <div class="text-xl font-bold text-red-800">{{ modalData.maleBirdsData.summary.total_male_mortality?.toLocaleString() }}</div>
            <div class="text-xs text-red-600">{{ modalData.maleBirdsData.summary.male_mortality_percentage }}% mortality rate</div>
          </div>
          <div class="bg-purple-50 p-4 rounded-lg">
            <div class="text-sm text-purple-600 font-medium">Active Flocks</div>
            <div class="text-xl font-bold text-purple-800">{{ modalData.maleBirdsData.summary.total_flocks }}</div>
            <div class="text-xs text-purple-600">{{ modalData.maleBirdsData.summary.total_batches }} batches</div>
          </div>
          <div class="bg-cyan-50 p-4 rounded-lg">
            <div class="text-sm text-cyan-600 font-medium">Assignments</div>
            <div class="text-xl font-bold text-cyan-800">{{ modalData.maleBirdsData.summary.total_assignments }}</div>
            <div class="text-xs text-cyan-600">Total assignments</div>
          </div>
        </div>

        <!-- Male Bird Performance Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-green-50 p-4 rounded-lg">
            <div class="text-sm text-green-600 font-medium">Excellent Flocks</div>
            <div class="text-xl font-bold text-green-800">{{ modalData.maleBirdsData.summary.excellent_flocks }}</div>
            <div class="text-xs text-green-600">≥ 50% male ratio</div>
          </div>
          <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-sm text-blue-600 font-medium">Good Flocks</div>
            <div class="text-xl font-bold text-blue-800">{{ modalData.maleBirdsData.summary.good_flocks }}</div>
            <div class="text-xs text-blue-600">45-50% male ratio</div>
          </div>
          <div class="bg-yellow-50 p-4 rounded-lg">
            <div class="text-sm text-yellow-600 font-medium">Moderate Flocks</div>
            <div class="text-xl font-bold text-yellow-800">{{ modalData.maleBirdsData.summary.moderate_flocks }}</div>
            <div class="text-xs text-yellow-600">40-45% male ratio</div>
          </div>
          <div class="bg-red-50 p-4 rounded-lg">
            <div class="text-sm text-red-600 font-medium">Poor Flocks</div>
            <div class="text-xl font-bold text-red-800">{{ modalData.maleBirdsData.summary.poor_flocks }}</div>
            <div class="text-xs text-red-600">< 40% male ratio</div>
          </div>
        </div>

        <!-- Flock Male Birds Details Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Male Birds by Flock</h3>
            <p class="text-sm text-gray-600">Detailed male birds breakdown for each flock</p>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Flock</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">🐣 Assign Male Birds</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">🐣 Current Male Birds</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Male %</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mortality</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="flock in modalData.maleBirdsData.flock_details" :key="flock.flock_id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ flock.flock_name }}</div>
                      <div class="text-sm text-gray-500">{{ flock.flock_code }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-blue-900">{{ flock.total_male_birds.toLocaleString() }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-pink-900">{{ flock.current_male_birds.toLocaleString() }}</div>
                  </td>
                  <!-- <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ flock.total_birds.toLocaleString() }}</div>
                  </td> -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ flock.current_male_percentage }}%</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ flock.male_mortality?.toLocaleString() }}</div>
                    <div class="text-xs text-red-600">{{ flock.male_mortality_percentage }}% rate</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="{
                      'bg-green-100 text-green-800': flock.current_male_percentage >= 50,
                      'bg-blue-100 text-blue-800': flock.current_male_percentage >= 45 && flock.current_male_percentage < 50,
                      'bg-yellow-100 text-yellow-800': flock.current_male_percentage >= 40 && flock.current_male_percentage < 45,
                      'bg-red-100 text-red-800': flock.current_male_percentage < 40
                    }" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ flock.current_male_percentage >= 50 ? 'Excellent' : 
                         flock.current_male_percentage >= 45 ? 'Good' : 
                         flock.current_male_percentage >= 40 ? 'Moderate' : 'Poor' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Batch Male Birds Details Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Male Birds by Batch</h3>
            <p class="text-sm text-gray-600">Detailed male birds breakdown for each batch</p>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Flock</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">🐣 Male Birds</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Male %</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">🐣Male Mortality</th>
                  <!-- <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">🐣 Total Birds</th> -->
                  
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="batch in modalData.maleBirdsData.batch_details" :key="batch.batch_no" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ batch.batch_name }}</div>
                    <div class="text-xs text-gray-500">{{ batch.assignments_count }} assignments</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.flock_name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-blue-900">{{ batch.current_male_birds?.toLocaleString() }}</div>
                  </td>
                  <!-- <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-pink-900">{{ batch.total_female_birds.toLocaleString() }}</div>
                  </td> -->
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ batch.current_male_percentage }}%</div>
                    
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.mortality_male.toLocaleString() }}</div>
                    <div class="text-xs text-red-600">{{ batch.male_mortality_percentage }}% rate</div>
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.company_name }}</div>
                    <div class="text-xs text-gray-500">{{ batch.project_name }}</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Recent Operations -->
        <div v-if="modalData.maleBirdsData?.recent_operations?.length > 0" class="bg-white rounded-lg border border-gray-200">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Recent Operations</h3>
            <p class="text-sm text-gray-600">Latest operations related to male birds</p>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <div v-for="operation in modalData.maleBirdsData.recent_operations" :key="operation.id" 
                   class="flex items-start space-x-4 p-4 bg-blue-50 rounded-lg border-l-4 border-blue-400">
                <div class="flex-shrink-0">
                  <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900">{{ operation.operation_type }}</p>
                    <p class="text-xs text-gray-500">{{ operation.operation_date }}</p>
                  </div>
                  <div class="mt-2 text-xs text-gray-500">
                    <span class="font-medium">{{ operation.batch_name }}</span> in 
                    <span class="font-medium">{{ operation.shed_name }}</span> - 
                    <span class="font-medium">{{ operation.flock_name }}</span>
                    <span class="text-gray-400"> • by {{ operation.created_by }}</span>
                  </div>
                  <div class="mt-1 text-xs text-gray-600">
                    {{ operation.description }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Last Updated -->
        <div class="text-center text-sm text-gray-500">
          Last updated: {{ new Date(modalData.maleBirdsData.timestamp * 1000).toLocaleString() }}
        </div>
      </div>

      <!-- Female Birds Details Content -->
      <div v-else-if="modalData?.femaleBirdsData" class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-gray-50 p-4 rounded-lg">
            <div class="flex items-center space-x-2 mb-2">
              <div class="text-gray-600">🐣</div>
              <div class="text-sm text-gray-600 font-medium">Total Birds</div>
            </div>
            <div class="text-2xl font-bold text-gray-800">{{ modalData.femaleBirdsData.summary.total_birds.toLocaleString() }}</div>
          </div>
          
          <div class="bg-pink-50 p-4 rounded-lg">
            <div class="flex items-center space-x-2 mb-2">
              <div class="text-pink-600">🐣</div>
              <div class="text-sm text-pink-600 font-medium">Assign Female Birds</div>
            </div>
            <div class="text-2xl font-bold text-pink-800">{{ modalData.femaleBirdsData.summary.total_female_birds.toLocaleString() }}</div>
          </div>
          <div class="bg-green-50 p-4 rounded-lg">
            <div class="flex items-center space-x-2 mb-2">
              <div class="text-green-600">📊</div>
              <div class="text-sm text-green-600 font-medium">Female Percentage</div>
            </div>
            <div class="text-2xl font-bold text-green-800">{{ modalData.femaleBirdsData.summary.female_percentage }}%</div>
          </div>
          <div class="bg-blue-50 p-4 rounded-lg">
            <div class="flex items-center space-x-2 mb-2">
              <div class="text-blue-600">🐣</div>
              <div class="text-sm text-blue-600 font-medium">Current Female Birds</div>
            </div>
            <div class="text-2xl font-bold text-blue-800">{{ modalData.femaleBirdsData.summary.current_female_birds.toLocaleString() }}</div>
          </div>

          <div class="bg-blue-50 p-4 rounded-lg">
            <div class="flex items-center space-x-2 mb-2">
              <div class="text-blue-600">🐣</div>
              <div class="text-sm text-blue-600 font-medium">Current Female Percentage</div>
            </div>
            <div class="text-2xl font-bold text-green-800">{{ modalData.femaleBirdsData.summary.current_female_percentage }}%</div>
          </div>
          
        </div>

        <!-- Additional Stats -->
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
          <div class="bg-red-50 p-4 rounded-lg">
            <div class="text-sm text-red-600 font-medium">Female Mortality</div>
            <div class="text-xl font-bold text-red-800">{{ modalData.femaleBirdsData.summary.female_mortality.toLocaleString() }}</div>
            <div class="text-xs text-red-600">{{ modalData.femaleBirdsData.summary.female_mortality_rate }}% mortality rate</div>
          </div>
          <div class="bg-purple-50 p-4 rounded-lg">
            <div class="text-sm text-purple-600 font-medium">Active Flocks</div>
            <div class="text-xl font-bold text-purple-800">{{ modalData.femaleBirdsData.summary.total_flocks }}</div>
            <div class="text-xs text-purple-600">{{ modalData.femaleBirdsData.summary.total_batches }} batches</div>
          </div>
          <div class="bg-cyan-50 p-4 rounded-lg">
            <div class="text-sm text-cyan-600 font-medium">Assignments</div>
            <div class="text-xl font-bold text-cyan-800">{{ modalData.femaleBirdsData.summary.total_assignments }}</div>
            <div class="text-xs text-cyan-600">Total assignments</div>
          </div>
        </div>

        <!-- Female Bird Performance Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div class="bg-green-50 p-4 rounded-lg">
            <div class="text-sm text-green-600 font-medium">Excellent Flocks</div>
            <div class="text-xl font-bold text-green-800">{{ modalData.femaleBirdsData.summary.excellent_flocks }}</div>
            <div class="text-xs text-green-600">≥ 50% female ratio</div>
          </div>
          <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-sm text-blue-600 font-medium">Good Flocks</div>
            <div class="text-xl font-bold text-blue-800">{{ modalData.femaleBirdsData.summary.good_flocks }}</div>
            <div class="text-xs text-blue-600">45-50% female ratio</div>
          </div>
          <div class="bg-yellow-50 p-4 rounded-lg">
            <div class="text-sm text-yellow-600 font-medium">Moderate Flocks</div>
            <div class="text-xl font-bold text-yellow-800">{{ modalData.femaleBirdsData.summary.moderate_flocks }}</div>
            <div class="text-xs text-yellow-600">40-45% female ratio</div>
          </div>
          <div class="bg-red-50 p-4 rounded-lg">
            <div class="text-sm text-red-600 font-medium">Poor Flocks</div>
            <div class="text-xl font-bold text-red-800">{{ modalData.femaleBirdsData.summary.poor_flocks }}</div>
            <div class="text-xs text-red-600">< 40% female ratio</div>
          </div>
        </div>

        <!-- Flock Female Birds Details Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Female Birds by Flock</h3>
            <p class="text-sm text-gray-600">Detailed female birds breakdown for each flock</p>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Flock</th>
                  
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">🐣 Total Birds</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">🐣 Assign Female Birds</th>
                  
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assign Female %</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">🐣 Current Female Birds</th>
                   <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">🐣 Current Female %</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mortality</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Performance</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="flock in modalData.femaleBirdsData.flock_details" :key="flock.flock_id" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div>
                      <div class="text-sm font-medium text-gray-900">{{ flock.flock_name }}</div>
                      <div class="text-sm text-gray-500">{{ flock.flock_code }}</div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ flock.total_birds.toLocaleString() }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-pink-900">{{ flock.total_female_birds.toLocaleString() }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-blue-900">{{ flock.female_percentage.toLocaleString() }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-blue-900">{{ flock.current_female_birds.toLocaleString() }}</div>
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ flock.current_female_percentage }}%</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ flock.female_mortality.toLocaleString() }}</div>
                    <div class="text-xs text-red-600">{{ flock.female_mortality_percentage }}% rate</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="{
                      'bg-green-100 text-green-800': flock.female_percentage >= 50,
                      'bg-blue-100 text-blue-800': flock.female_percentage >= 45 && flock.female_percentage < 50,
                      'bg-yellow-100 text-yellow-800': flock.female_percentage >= 40 && flock.female_percentage < 45,
                      'bg-red-100 text-red-800': flock.female_percentage < 40
                    }" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                      {{ flock.female_percentage >= 50 ? 'Excellent' : 
                         flock.female_percentage >= 45 ? 'Good' : 
                         flock.female_percentage >= 40 ? 'Moderate' : 'Poor' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Batch Female Birds Details Table -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Female Birds by Batch</h3>
            <p class="text-sm text-gray-600">Detailed female birds breakdown for each batch</p>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Batch</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Flock</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">🐣 Total Birds</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">🐣 Assign Female</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">🐣 Assign Female %</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">🐣 Current Female</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Female %</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">🐣 Mortality</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Company</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="batch in modalData.femaleBirdsData.batch_details" :key="batch.batch_no" class="hover:bg-gray-50">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ batch.batch_name }}</div>
                    <div class="text-xs text-gray-500">{{ batch.assignments_count }} assignments</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.flock_name }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.total_birds.toLocaleString() }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-pink-900">{{ batch.total_female_birds.toLocaleString() }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ batch.female_percentage }} %</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-blue-900">{{ batch.current_female_birds.toLocaleString() }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-blue-900">{{ batch.current_female_percentage.toLocaleString() }} %</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    
                    <div class="text-sm text-gray-900">{{ batch.female_mortality.toLocaleString() }}</div>
                    <div class="text-xs text-red-600">{{ batch.female_mortality_rate }}% rate</div>
                  
                  </td>
                  
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-900">{{ batch.company_name }}</div>
                    <div class="text-xs text-gray-500">{{ batch.project_name }}</div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Recent Operations -->
        <div v-if="modalData.femaleBirdsData?.recent_operations?.length > 0" class="bg-white rounded-lg border border-gray-200">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Recent Operations</h3>
            <p class="text-sm text-gray-600">Latest operations related to female birds</p>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <div v-for="operation in modalData.femaleBirdsData.recent_operations" :key="operation.id" 
                   class="flex items-start space-x-4 p-4 bg-pink-50 rounded-lg border-l-4 border-pink-400">
                <div class="flex-shrink-0">
                  <div class="w-2 h-2 bg-pink-500 rounded-full mt-2"></div>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-900">{{ operation.operation_type }}</p>
                    <p class="text-xs text-gray-500">{{ operation.operation_date }}</p>
                  </div>
                  <div class="mt-2 text-xs text-gray-500">
                    <span class="font-medium">{{ operation.batch_name }}</span> in 
                    <span class="font-medium">{{ operation.shed_name }}</span> - 
                    <span class="font-medium">{{ operation.flock_name }}</span>
                    <span class="text-gray-400"> • by {{ operation.created_by }}</span>
                  </div>
                  <div class="mt-1 text-xs text-gray-600">
                    {{ operation.description }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Last Updated -->
        <div class="text-center text-sm text-gray-500">
          Last updated: {{ new Date(modalData.femaleBirdsData.timestamp * 1000).toLocaleString() }}
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
