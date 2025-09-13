<script setup lang="ts">
import { computed, inject, ref } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  value: string
  class?: string
}

const props = defineProps<Props>()

const tabsContext = inject('tabs', {
  activeTab: ref(''),
  setActiveTab: () => {}
})

const isActive = computed(() => tabsContext.activeTab.value === props.value)

const handleClick = () => {
  tabsContext.setActiveTab(props.value)
}
</script>

<template>
  <button
    :class="cn(
      'inline-flex items-center justify-center whitespace-nowrap rounded-sm px-3 py-1.5 text-sm font-medium ring-offset-background transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50',
      isActive ? 'bg-background text-foreground shadow-sm' : 'hover:bg-background/50',
      props.class
    )"
    @click="handleClick"
  >
    <slot />
  </button>
</template>
