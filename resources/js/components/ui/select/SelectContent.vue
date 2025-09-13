<script setup lang="ts">
import { inject, ref } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
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
</script>

<template>
  <Transition
    enter-active-class="transition duration-200 ease-out"
    enter-from-class="transform scale-95 opacity-0"
    enter-to-class="transform scale-100 opacity-100"
    leave-active-class="transition duration-150 ease-in"
    leave-from-class="transform scale-100 opacity-100"
    leave-to-class="transform scale-95 opacity-0"
  >
    <div
      v-if="selectContext.isOpen.value"
      :class="cn('absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md border bg-popover py-1 text-popover-foreground shadow-md', props.class)"
    >
      <slot />
    </div>
  </Transition>
</template>
