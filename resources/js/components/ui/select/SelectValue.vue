<script setup lang="ts">
import { inject, ref, computed } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  placeholder?: string
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

const displayValue = computed(() => {
  const value = selectContext.modelValue()
  return value ? value.toString() : props.placeholder || 'Select an option'
})
</script>

<template>
  <span :class="cn('text-foreground', props.class)">
    {{ displayValue }}
  </span>
</template>
