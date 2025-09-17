<template>
  <div
    ref="triggerRef"
    class="inline-block"
    @mouseenter="showTooltip"
    @mouseleave="hideTooltip"
    @focus="showTooltip"
    @blur="hideTooltip"
  >
    <slot />
  </div>

  <!-- Tooltip Portal -->
  <Teleport to="body">
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isVisible"
        ref="tooltipRef"
        class="fixed z-50 px-3 py-2 text-sm text-white bg-gray-900 rounded-lg shadow-lg pointer-events-none"
        :style="tooltipStyle"
      >
        <!-- Tooltip Arrow -->
        <div
          v-if="showArrow"
          class="absolute w-2 h-2 bg-gray-900 transform rotate-45"
          :style="arrowStyle"
        ></div>
        
        <!-- Tooltip Content -->
        <div class="relative z-10">
          <slot name="content">
            {{ content }}
          </slot>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue'

interface Props {
  content?: string
  placement?: 'top' | 'bottom' | 'left' | 'right'
  offset?: number
  showArrow?: boolean
  delay?: number
  disabled?: boolean
}

const props = withDefaults(defineProps<Props>(), {
  placement: 'top',
  offset: 8,
  showArrow: true,
  delay: 0,
  disabled: false
})

// Refs
const triggerRef = ref<HTMLElement>()
const tooltipRef = ref<HTMLElement>()
const isVisible = ref(false)
const position = ref({ x: 0, y: 0 })

// Computed
const tooltipStyle = computed(() => {
  if (!isVisible.value) return {}
  
  const { x, y } = position.value
  return {
    left: `${x}px`,
    top: `${y}px`,
    transform: getTransform()
  }
})

const arrowStyle = computed(() => {
  if (!props.showArrow) return {}
  
  const arrowSize = 8
  const offset = props.offset
  
  switch (props.placement) {
    case 'top':
      return {
        bottom: `-${arrowSize / 2}px`,
        left: '50%',
        transform: 'translateX(-50%)'
      }
    case 'bottom':
      return {
        top: `-${arrowSize / 2}px`,
        left: '50%',
        transform: 'translateX(-50%)'
      }
    case 'left':
      return {
        right: `-${arrowSize / 2}px`,
        top: '50%',
        transform: 'translateY(-50%)'
      }
    case 'right':
      return {
        left: `-${arrowSize / 2}px`,
        top: '50%',
        transform: 'translateY(-50%)'
      }
    default:
      return {}
  }
})

// Methods
const getTransform = () => {
  switch (props.placement) {
    case 'top':
      return 'translateX(-50%) translateY(-100%)'
    case 'bottom':
      return 'translateX(-50%)'
    case 'left':
      return 'translateX(-100%) translateY(-50%)'
    case 'right':
      return 'translateY(-50%)'
    default:
      return 'translateX(-50%) translateY(-100%)'
  }
}

const calculatePosition = () => {
  if (!triggerRef.value || !tooltipRef.value) return

  const triggerRect = triggerRef.value.getBoundingClientRect()
  const tooltipRect = tooltipRef.value.getBoundingClientRect()
  const scrollX = window.pageXOffset || document.documentElement.scrollLeft
  const scrollY = window.pageYOffset || document.documentElement.scrollTop

  let x = 0
  let y = 0

  switch (props.placement) {
    case 'top':
      x = triggerRect.left + triggerRect.width / 2 + scrollX
      y = triggerRect.top - props.offset + scrollY
      break
    case 'bottom':
      x = triggerRect.left + triggerRect.width / 2 + scrollX
      y = triggerRect.bottom + props.offset + scrollY
      break
    case 'left':
      x = triggerRect.left - props.offset + scrollX
      y = triggerRect.top + triggerRect.height / 2 + scrollY
      break
    case 'right':
      x = triggerRect.right + props.offset + scrollX
      y = triggerRect.top + triggerRect.height / 2 + scrollY
      break
  }

  // Keep tooltip within viewport
  const viewportWidth = window.innerWidth
  const viewportHeight = window.innerHeight

  if (x + tooltipRect.width > viewportWidth) {
    x = viewportWidth - tooltipRect.width - 10
  }
  if (x < 10) {
    x = 10
  }
  if (y + tooltipRect.height > viewportHeight) {
    y = viewportHeight - tooltipRect.height - 10
  }
  if (y < 10) {
    y = 10
  }

  position.value = { x, y }
}

const showTooltip = async () => {
  if (props.disabled) return

  if (props.delay > 0) {
    setTimeout(() => {
      if (!isVisible.value) {
        isVisible.value = true
        nextTick(() => {
          calculatePosition()
        })
      }
    }, props.delay)
  } else {
    isVisible.value = true
    await nextTick()
    calculatePosition()
  }
}

const hideTooltip = () => {
  isVisible.value = false
}

const handleScroll = () => {
  if (isVisible.value) {
    calculatePosition()
  }
}

const handleResize = () => {
  if (isVisible.value) {
    calculatePosition()
  }
}

// Lifecycle
onMounted(() => {
  window.addEventListener('scroll', handleScroll, true)
  window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll, true)
  window.removeEventListener('resize', handleResize)
})
</script>
