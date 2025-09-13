<script setup lang="ts">
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { cn } from '@/lib/utils'
import { ChevronDownIcon, MagnifyingGlassIcon } from '@heroicons/vue/20/solid'

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
  searchPlaceholder?: string
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string | number): void
}>()

const isOpen = ref(false)
const searchQuery = ref('')
const dropdownRef = ref<HTMLElement>()
const searchInputRef = ref<HTMLInputElement>()

const selectedOption = computed(() => {
  if (!props.options) return null
  return props.options.find(option => option.value === props.modelValue)
})

const filteredOptions = computed(() => {
  if (!props.options) return []
  if (!searchQuery.value) return props.options
  
  return props.options.filter(option =>
    option.label.toLowerCase().includes(searchQuery.value.toLowerCase())
  )
})

function selectOption(option: Option) {
  if (option.disabled) return
  emit('update:modelValue', option.value)
  isOpen.value = false
  searchQuery.value = ''
}

function toggleDropdown() {
  if (props.disabled) return
  isOpen.value = !isOpen.value
  if (isOpen.value) {
    // Focus search input when dropdown opens
    setTimeout(() => {
      searchInputRef.value?.focus()
    }, 100)
  } else {
    searchQuery.value = ''
  }
}

function handleClickOutside(event: Event) {
  const target = event.target as HTMLElement
  if (dropdownRef.value && !dropdownRef.value.contains(target)) {
    isOpen.value = false
    searchQuery.value = ''
  }
}

function handleKeydown(event: KeyboardEvent) {
  if (!isOpen.value) return
  
  if (event.key === 'Escape') {
    isOpen.value = false
    searchQuery.value = ''
  }
}

// Close dropdown when clicking outside
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('keydown', handleKeydown)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('keydown', handleKeydown)
})

// Close dropdown when value changes externally
watch(() => props.modelValue, () => {
  if (isOpen.value) {
    isOpen.value = false
    searchQuery.value = ''
  }
})
</script>

<template>
  <div class="relative" :class="props.class" ref="dropdownRef">
    <button
      type="button"
      @click="toggleDropdown"
      :disabled="disabled"
      :class="cn(
        'flex h-9 w-full items-center justify-between rounded-md border border-gray-300 dark:border-gray-600 bg-transparent px-3 py-1 text-sm shadow-sm transition-colors',
        'focus:outline-none focus:ring-2 focus:ring-chicken focus:border-chicken',
        'disabled:cursor-not-allowed disabled:opacity-50',
        'hover:bg-gray-50 dark:hover:bg-gray-700',
        'dark:bg-gray-700 dark:text-white',
        isOpen && 'ring-2 ring-chicken ring-offset-2'
      )"
    >
      <span :class="selectedOption ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400'">
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
        class="absolute z-50 mt-1 max-h-60 w-full overflow-hidden rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 shadow-lg"
      >
        <!-- Search Input -->
        <div class="p-2 border-b border-gray-200 dark:border-gray-600">
          <div class="relative">
            <MagnifyingGlassIcon class="absolute left-2 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
            <input
              ref="searchInputRef"
              v-model="searchQuery"
              type="text"
              :placeholder="searchPlaceholder || 'Search...'"
              class="w-full pl-8 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-chicken focus:border-chicken dark:bg-gray-600 dark:text-white"
            />
          </div>
        </div>
        
        <!-- Options List -->
        <div class="max-h-48 overflow-auto">
          <div
            v-for="option in filteredOptions"
            :key="option.value"
            @click="selectOption(option)"
            :class="cn(
              'relative flex cursor-pointer select-none items-center rounded-sm px-3 py-2 text-sm outline-none',
              'hover:bg-gray-100 dark:hover:bg-gray-600',
              'focus:bg-gray-100 focus:text-gray-900 dark:focus:bg-gray-600 dark:focus:text-white',
              option.disabled && 'pointer-events-none opacity-50',
              option.value === modelValue && 'bg-chicken/10 text-chicken-900 dark:bg-chicken/20 dark:text-chicken-100'
            )"
          >
            {{ option.label }}
          </div>
          
          <!-- No results message -->
          <div
            v-if="filteredOptions.length === 0"
            class="px-3 py-2 text-sm text-gray-500 dark:text-gray-400 text-center"
          >
            No options found
          </div>
        </div>
      </div>
    </Transition>
  </div>
</template>
