<template>
  <Transition
    enter-active-class="transition-all duration-300"
    enter-from-class="opacity-0 translate-y-2"
    enter-to-class="opacity-100 translate-y-0"
    leave-active-class="transition-all duration-200"
    leave-from-class="opacity-100 translate-y-0"
    leave-to-class="opacity-0 translate-y-2"
  >
    <div
      v-if="isVisible"
      class="fixed top-4 right-4 z-50 max-w-sm w-full bg-white rounded-lg shadow-lg border border-gray-200 p-4"
    >
      <div class="flex items-start space-x-3">
        <!-- Icon -->
        <div class="flex-shrink-0">
          <div
            class="w-8 h-8 rounded-full flex items-center justify-center"
            :class="iconBgClass"
          >
            <component :is="icon" class="w-4 h-4" :class="iconClass" />
          </div>
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">
          <h4 class="text-sm font-semibold text-gray-900">{{ title }}</h4>
          <p class="text-sm text-gray-600 mt-1">{{ message }}</p>
          <div v-if="showTimestamp" class="text-xs text-gray-500 mt-1">
            {{ formatTime(timestamp) }}
          </div>
        </div>

        <!-- Close Button -->
        <button
          @click="close"
          class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors duration-200"
        >
          <X class="w-4 h-4" />
        </button>
      </div>

      <!-- Progress Bar -->
      <div v-if="autoClose && duration > 0" class="mt-3">
        <div class="w-full bg-gray-200 rounded-full h-1">
          <div
            class="h-1 rounded-full transition-all duration-100 ease-linear"
            :class="progressBarClass"
            :style="{ width: progressWidth + '%' }"
          ></div>
        </div>
      </div>
    </div>
  </Transition>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { X, CheckCircle, AlertCircle, Info, RefreshCw } from 'lucide-vue-next'

interface Props {
  isVisible: boolean
  title: string
  message: string
  type?: 'success' | 'error' | 'info' | 'warning'
  autoClose?: boolean
  duration?: number
  showTimestamp?: boolean
  timestamp?: number
}

const props = withDefaults(defineProps<Props>(), {
  type: 'info',
  autoClose: true,
  duration: 5000,
  showTimestamp: true,
  timestamp: () => Date.now()
})

const emit = defineEmits<{
  'update:isVisible': [value: boolean]
  'close': []
}>()

// Local state
const progress = ref(100)
const progressTimer = ref<NodeJS.Timeout | null>(null)

// Computed properties
const icon = computed(() => {
  const icons = {
    success: CheckCircle,
    error: AlertCircle,
    info: Info,
    warning: AlertCircle
  }
  return icons[props.type]
})

const iconBgClass = computed(() => {
  const classes = {
    success: 'bg-green-100',
    error: 'bg-red-100',
    info: 'bg-blue-100',
    warning: 'bg-yellow-100'
  }
  return classes[props.type]
})

const iconClass = computed(() => {
  const classes = {
    success: 'text-green-600',
    error: 'text-red-600',
    info: 'text-blue-600',
    warning: 'text-yellow-600'
  }
  return classes[props.type]
})

const progressBarClass = computed(() => {
  const classes = {
    success: 'bg-green-500',
    error: 'bg-red-500',
    info: 'bg-blue-500',
    warning: 'bg-yellow-500'
  }
  return classes[props.type]
})

const progressWidth = computed(() => progress.value)

// Methods
const close = () => {
  emit('update:isVisible', false)
  emit('close')
}

const startProgress = () => {
  if (!props.autoClose || props.duration <= 0) return

  const interval = 50 // Update every 50ms
  const totalSteps = props.duration / interval
  const stepSize = 100 / totalSteps

  progressTimer.value = setInterval(() => {
    progress.value -= stepSize
    if (progress.value <= 0) {
      close()
    }
  }, interval)
}

const stopProgress = () => {
  if (progressTimer.value) {
    clearInterval(progressTimer.value)
    progressTimer.value = null
  }
}

const formatTime = (timestamp: number) => {
  const date = new Date(timestamp)
  return date.toLocaleTimeString()
}

// Lifecycle
onMounted(() => {
  if (props.isVisible && props.autoClose) {
    startProgress()
  }
})

onUnmounted(() => {
  stopProgress()
})

// Watch for visibility changes
watch(() => props.isVisible, (isVisible) => {
  if (isVisible && props.autoClose) {
    progress.value = 100
    startProgress()
  } else {
    stopProgress()
  }
})
</script>
