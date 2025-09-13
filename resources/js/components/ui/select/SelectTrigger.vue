<script setup lang="ts">
import { cn } from '@/lib/utils'

interface Props {
  class?: string
}

const props = defineProps<Props>()

interface SlotProps {
  isOpen: { value: boolean }
  open: () => void
  close: () => void
  select: (value: string | number) => void
  modelValue: string | number | undefined
}

const slotProps = defineSlots<{
  default: (props: SlotProps) => any
}>()

const handleClick = () => {
  // This will be handled by the parent Select component
}
</script>

<template>
  <button
    type="button"
    @click="handleClick"
    :class="cn(
      'flex h-9 w-full items-center justify-between rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors',
      'focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
      'disabled:cursor-not-allowed disabled:opacity-50',
      'hover:bg-accent hover:text-accent-foreground',
      selectContext.isOpen.value && 'ring-2 ring-ring ring-offset-2',
      props.class
    )"
  >
    <slot />
  </button>
</template>
