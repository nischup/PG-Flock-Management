<script setup lang="ts">
import { computed, provide, ref } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  defaultValue?: string
  value?: string
  class?: string
}

const props = defineProps<Props>()
const emit = defineEmits<{
  'update:value': [value: string]
}>()

const activeTab = computed({
  get: () => props.value || props.defaultValue || '',
  set: (value: string) => emit('update:value', value)
})

provide('tabs', {
  activeTab,
  setActiveTab: (value: string) => {
    activeTab.value = value
  }
})
</script>

<template>
  <div :class="cn('w-full', props.class)">
    <slot />
  </div>
</template>
