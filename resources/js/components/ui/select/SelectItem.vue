<script setup lang="ts">
import { inject, ref, computed } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  value: string | number
  class?: string
}

const props = defineProps<Props>()

const selectContext = inject('select', {
  isOpen: ref(false),
  open: () => {},
  close: () => {},
  select: () => {},
  modelValue: () => ''
})

const isSelected = computed(() => selectContext.modelValue() === props.value)

const handleClick = () => {
  selectContext.select(props.value)
}
</script>

<template>
  <div 
    @click="handleClick"
    :class="cn(
      'relative flex cursor-pointer select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none',
      'hover:bg-accent hover:text-accent-foreground',
      'focus:bg-accent focus:text-accent-foreground',
      isSelected && 'bg-accent text-accent-foreground',
      props.class
    )"
  >
    <slot />
  </div>
</template>
