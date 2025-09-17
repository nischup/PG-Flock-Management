<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-all duration-300"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition-all duration-200"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="isVisible"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        @click="handleBackdropClick"
      >
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm"></div>

        <!-- Modal Content -->
        <div
          ref="modalRef"
          class="relative bg-white rounded-xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-hidden"
          :class="sizeClasses"
          @click.stop
        >
          <!-- Header -->
          <div v-if="title || $slots.header" class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center space-x-3">
              <div v-if="icon" class="p-2 rounded-lg" :class="iconBgClass">
                <component :is="icon" class="h-5 w-5" :class="iconClass" />
              </div>
              <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
            </div>
            <button
              @click="close"
              class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200"
            >
              <X class="h-5 w-5" />
            </button>
          </div>

          <!-- Body -->
          <div class="p-6 overflow-y-auto" :class="bodyClasses">
            <slot :close="close" :confirm="confirm" />
          </div>

          <!-- Footer -->
          <div v-if="showFooter || $slots.footer" class="flex items-center justify-end space-x-3 p-6 border-t border-gray-200 bg-gray-50">
            <slot name="footer" :close="close" :confirm="confirm">
              <button
                v-if="showCancel"
                @click="close"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200"
              >
                {{ cancelText }}
              </button>
              <button
                v-if="showConfirm"
                @click="confirm"
                :disabled="confirmDisabled"
                class="px-4 py-2 text-sm font-medium text-white rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                :class="confirmButtonClass"
              >
                {{ confirmText }}
              </button>
            </slot>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { X, AlertCircle, CheckCircle, Info, AlertTriangle } from 'lucide-vue-next'

interface Props {
  isVisible: boolean
  title?: string
  icon?: any
  size?: 'sm' | 'md' | 'lg' | 'xl' | 'full'
  variant?: 'default' | 'success' | 'warning' | 'danger' | 'info'
  showFooter?: boolean
  showCancel?: boolean
  showConfirm?: boolean
  cancelText?: string
  confirmText?: string
  confirmDisabled?: boolean
  closeOnBackdrop?: boolean
  closeOnEscape?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  size: 'md',
  variant: 'default',
  showFooter: true,
  showCancel: true,
  showConfirm: true,
  cancelText: 'Cancel',
  confirmText: 'Confirm',
  confirmDisabled: false,
  closeOnBackdrop: true,
  closeOnEscape: true
})

const emit = defineEmits<{
  'update:isVisible': [value: boolean]
  'close': []
  'confirm': []
}>()

// Refs
const modalRef = ref<HTMLElement>()

// Computed
const sizeClasses = computed(() => {
  const sizes = {
    sm: 'max-w-sm',
    md: 'max-w-md',
    lg: 'max-w-lg',
    xl: 'max-w-xl',
    full: 'max-w-full mx-4'
  }
  return sizes[props.size]
})

const bodyClasses = computed(() => {
  const variants = {
    default: '',
    success: 'text-green-800',
    warning: 'text-yellow-800',
    danger: 'text-red-800',
    info: 'text-blue-800'
  }
  return variants[props.variant]
})

const iconBgClass = computed(() => {
  const variants = {
    default: 'bg-gray-100',
    success: 'bg-green-100',
    warning: 'bg-yellow-100',
    danger: 'bg-red-100',
    info: 'bg-blue-100'
  }
  return variants[props.variant]
})

const iconClass = computed(() => {
  const variants = {
    default: 'text-gray-600',
    success: 'text-green-600',
    warning: 'text-yellow-600',
    danger: 'text-red-600',
    info: 'text-blue-600'
  }
  return variants[props.variant]
})

const confirmButtonClass = computed(() => {
  const variants = {
    default: 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
    success: 'bg-green-600 hover:bg-green-700 focus:ring-green-500',
    warning: 'bg-yellow-600 hover:bg-yellow-700 focus:ring-yellow-500',
    danger: 'bg-red-600 hover:bg-red-700 focus:ring-red-500',
    info: 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500'
  }
  return variants[props.variant]
})

// Methods
const close = () => {
  emit('update:isVisible', false)
  emit('close')
}

const confirm = () => {
  emit('confirm')
}

const handleBackdropClick = (event: MouseEvent) => {
  if (props.closeOnBackdrop && event.target === event.currentTarget) {
    close()
  }
}

const handleEscape = (event: KeyboardEvent) => {
  if (props.closeOnEscape && event.key === 'Escape') {
    close()
  }
}

const handleFocusTrap = (event: KeyboardEvent) => {
  if (event.key === 'Tab' && modalRef.value) {
    const focusableElements = modalRef.value.querySelectorAll(
      'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    )
    const firstElement = focusableElements[0] as HTMLElement
    const lastElement = focusableElements[focusableElements.length - 1] as HTMLElement

    if (event.shiftKey) {
      if (document.activeElement === firstElement) {
        lastElement?.focus()
        event.preventDefault()
      }
    } else {
      if (document.activeElement === lastElement) {
        firstElement?.focus()
        event.preventDefault()
      }
    }
  }
}

// Lifecycle
onMounted(() => {
  document.addEventListener('keydown', handleEscape)
  document.addEventListener('keydown', handleFocusTrap)
  
  // Focus the modal when it opens
  if (props.isVisible) {
    nextTick(() => {
      modalRef.value?.focus()
    })
  }
})

onUnmounted(() => {
  document.removeEventListener('keydown', handleEscape)
  document.removeEventListener('keydown', handleFocusTrap)
})

// Watch for visibility changes
watch(() => props.isVisible, (isVisible) => {
  if (isVisible) {
    // Prevent body scroll
    document.body.style.overflow = 'hidden'
    nextTick(() => {
      modalRef.value?.focus()
    })
  } else {
    // Restore body scroll
    document.body.style.overflow = ''
  }
})
</script>

<style scoped>
/* Custom scrollbar for modal body */
.overflow-y-auto::-webkit-scrollbar {
  width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>
