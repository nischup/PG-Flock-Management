<template>
  <div
    class="relative group bg-white/40 backdrop-blur-md border border-white/50 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden"
    :class="widgetClass"
  >
    <!-- Widget Header -->
    <div class="p-4 border-b border-gray-200/50 bg-gradient-to-r from-gray-50/50 to-white/30">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div
            class="p-2 rounded-lg"
            :class="iconBgClass"
          >
            <component :is="icon" class="h-5 w-5" :class="iconClass" />
          </div>
          <div>
            <h3 class="text-sm font-semibold text-gray-800">{{ title }}</h3>
            <p v-if="subtitle" class="text-xs text-gray-600">{{ subtitle }}</p>
          </div>
        </div>
        
        <div class="flex items-center space-x-2">
          <!-- Refresh Button -->
          <button
            v-if="refreshable"
            @click="refresh"
            :disabled="isRefreshing"
            class="p-1.5 text-gray-400 hover:text-gray-600 transition-colors duration-200 disabled:opacity-50"
          >
            <RefreshCw :class="{ 'animate-spin': isRefreshing }" class="h-4 w-4" />
          </button>
          
          <!-- Settings Button -->
          <button
            v-if="configurable"
            @click="showSettings = true"
            class="p-1.5 text-gray-400 hover:text-gray-600 transition-colors duration-200"
          >
            <Settings class="h-4 w-4" />
          </button>
          
          <!-- Expand/Collapse Button -->
          <button
            v-if="expandable"
            @click="isExpanded = !isExpanded"
            class="p-1.5 text-gray-400 hover:text-gray-600 transition-colors duration-200"
          >
            <ChevronDown :class="{ 'rotate-180': isExpanded }" class="h-4 w-4 transition-transform duration-200" />
          </button>
        </div>
      </div>
    </div>

    <!-- Widget Content -->
    <div class="p-4" :class="{ 'hidden': !isExpanded && expandable }">
      <div v-if="isLoading" class="flex items-center justify-center h-32">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
      </div>
      
      <div v-else-if="error" class="flex flex-col items-center justify-center h-32 text-center">
        <AlertCircle class="h-8 w-8 text-red-500 mb-2" />
        <p class="text-sm text-red-600">{{ error }}</p>
        <button
          @click="refresh"
          class="mt-2 px-3 py-1 text-xs bg-red-100 text-red-700 rounded-md hover:bg-red-200 transition-colors duration-200"
        >
          Retry
        </button>
      </div>
      
      <div v-else>
        <slot :data="data" :isExpanded="isExpanded" />
      </div>
    </div>

    <!-- Widget Footer -->
    <div v-if="showFooter" class="px-4 py-3 border-t border-gray-200/50 bg-gray-50/30">
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div v-if="trend" class="flex items-center space-x-1">
            <component :is="trend.icon" class="h-4 w-4" :class="trend.color" />
            <span class="text-xs font-medium" :class="trend.color">{{ trend.text }}</span>
          </div>
          <div v-if="lastUpdated" class="text-xs text-gray-500">
            Updated {{ lastUpdated }}
          </div>
        </div>
        
        <div v-if="actionText" class="text-xs">
          <button
            @click="$emit('action')"
            class="text-blue-600 hover:text-blue-800 font-medium transition-colors duration-200"
          >
            {{ actionText }}
          </button>
        </div>
      </div>
    </div>

    <!-- Settings Modal -->
    <div
      v-if="showSettings"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click="showSettings = false"
    >
      <div
        class="bg-white rounded-lg p-6 max-w-md w-full mx-4"
        @click.stop
      >
        <h3 class="text-lg font-semibold mb-4">Widget Settings</h3>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
            <input
              v-model="localTitle"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Refresh Interval</label>
            <select
              v-model="refreshInterval"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="0">Manual</option>
              <option value="30">30 seconds</option>
              <option value="60">1 minute</option>
              <option value="300">5 minutes</option>
              <option value="900">15 minutes</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end space-x-3 mt-6">
          <button
            @click="showSettings = false"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 transition-colors duration-200"
          >
            Cancel
          </button>
          <button
            @click="saveSettings"
            class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600 transition-colors duration-200"
          >
            Save
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { 
  RefreshCw, 
  Settings, 
  ChevronDown, 
  AlertCircle,
  TrendingUp,
  TrendingDown,
  Minus
} from 'lucide-vue-next'

interface Trend {
  icon: any
  text: string
  color: string
}

interface Props {
  title: string
  subtitle?: string
  icon: any
  data?: any
  isLoading?: boolean
  error?: string
  refreshable?: boolean
  configurable?: boolean
  expandable?: boolean
  showFooter?: boolean
  actionText?: string
  trend?: Trend
  lastUpdated?: string
  size?: 'sm' | 'md' | 'lg' | 'xl'
  variant?: 'default' | 'success' | 'warning' | 'danger' | 'info'
}

const props = withDefaults(defineProps<Props>(), {
  isLoading: false,
  refreshable: true,
  configurable: false,
  expandable: false,
  showFooter: true,
  size: 'md',
  variant: 'default'
})

const emit = defineEmits<{
  action: []
  refresh: []
  settingsChange: [settings: any]
}>()

// Local state
const isExpanded = ref(true)
const isRefreshing = ref(false)
const showSettings = ref(false)
const localTitle = ref(props.title)
const refreshInterval = ref(0)

let refreshTimer: NodeJS.Timeout | null = null

// Computed properties
const widgetClass = computed(() => {
  const sizeClasses = {
    sm: 'col-span-1',
    md: 'col-span-2',
    lg: 'col-span-3',
    xl: 'col-span-4'
  }
  
  const variantClasses = {
    default: '',
    success: 'border-green-200 bg-green-50/20',
    warning: 'border-yellow-200 bg-yellow-50/20',
    danger: 'border-red-200 bg-red-50/20',
    info: 'border-blue-200 bg-blue-50/20'
  }
  
  return `${sizeClasses[props.size]} ${variantClasses[props.variant]}`
})

const iconBgClass = computed(() => {
  const variantClasses = {
    default: 'bg-gray-100',
    success: 'bg-green-100',
    warning: 'bg-yellow-100',
    danger: 'bg-red-100',
    info: 'bg-blue-100'
  }
  return variantClasses[props.variant]
})

const iconClass = computed(() => {
  const variantClasses = {
    default: 'text-gray-600',
    success: 'text-green-600',
    warning: 'text-yellow-600',
    danger: 'text-red-600',
    info: 'text-blue-600'
  }
  return variantClasses[props.variant]
})

// Methods
const refresh = async () => {
  if (isRefreshing.value) return
  
  isRefreshing.value = true
  emit('refresh')
  
  // Simulate refresh delay
  setTimeout(() => {
    isRefreshing.value = false
  }, 1000)
}

const saveSettings = () => {
  emit('settingsChange', {
    title: localTitle.value,
    refreshInterval: refreshInterval.value
  })
  showSettings.value = false
}

// Auto-refresh setup
const setupAutoRefresh = () => {
  if (refreshTimer) {
    clearInterval(refreshTimer)
  }
  
  if (refreshInterval.value > 0) {
    refreshTimer = setInterval(() => {
      refresh()
    }, refreshInterval.value * 1000)
  }
}

// Lifecycle
onMounted(() => {
  setupAutoRefresh()
})

onUnmounted(() => {
  if (refreshTimer) {
    clearInterval(refreshTimer)
  }
})
</script>
