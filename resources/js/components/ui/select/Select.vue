<script setup lang="ts">
import { ref, computed } from 'vue'
import { cn } from '@/lib/utils'
import { ChevronDownIcon } from '@heroicons/vue/20/solid'

interface Option {
  value: string | number
  label: string
  disabled?: boolean
}

const props = defineProps<{
  modelValue?: string | number
  options?: Option[]
  placeholder?: string
  disabled?: boolean
  class?: string
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string | number): void
}>()

const isOpen = ref(false)

const selectedOption = computed(() => {
  if (!props.options) return null
  return props.options.find(option => option.value === props.modelValue)
})

function selectOption(option: Option) {
  if (option.disabled) return
  emit('update:modelValue', option.value)
  isOpen.value = false
}

function toggleDropdown() {
  if (props.disabled) return
  isOpen.value = !isOpen.value
}
</script>

<template>
  <div class="relative" :class="props.class">
    <button
      type="button"
      @click="toggleDropdown"
      :disabled="disabled"
      :class="cn(
        'flex h-9 w-full items-center justify-between rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs transition-colors',
        'focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
        'disabled:cursor-not-allowed disabled:opacity-50',
        'hover:bg-accent hover:text-accent-foreground',
        isOpen && 'ring-2 ring-ring ring-offset-2'
      )"
    >
      <span :class="selectedOption ? 'text-foreground' : 'text-muted-foreground'">
        {{ selectedOption?.label || placeholder }}
      </span>
      <ChevronDownIcon 
        :class="cn(
          'h-4 w-4 transition-transform duration-200',
          isOpen && 'rotate-180'
        )" 
      />
    </button>
    
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="transform scale-95 opacity-0"
      enter-to-class="transform scale-100 opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="transform scale-100 opacity-100"
      leave-to-class="transform scale-95 opacity-0"
    >
      <div
        v-if="isOpen"
        class="absolute z-50 mt-1 max-h-60 w-full overflow-auto rounded-md border bg-popover py-1 text-popover-foreground shadow-md"
      >
        <div
          v-for="option in options"
          :key="option.value"
          @click="selectOption(option)"
          :class="cn(
            'relative flex cursor-pointer select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none',
            'hover:bg-accent hover:text-accent-foreground',
            'focus:bg-accent focus:text-accent-foreground',
            option.disabled && 'pointer-events-none opacity-50',
            option.value === modelValue && 'bg-accent text-accent-foreground'
          )"
        >
          {{ option.label }}
        </div>
      </div>
    </Transition>
  </div>
</template>
