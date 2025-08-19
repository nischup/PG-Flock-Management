<script setup lang="ts">
import { ref, watch, defineProps, defineEmits, computed } from 'vue'
import { CircleX } from 'lucide-vue-next'

const props = defineProps<{
  modelValue: (File | string)[]
  label?: string
  maxFiles?: number
  accept?: string
  wrapperClass?: string
}>()

const emit = defineEmits(['update:modelValue'])

// Internal files state
const files = ref<(File | string)[]>([...props.modelValue])

// Sync parent changes to internal files without causing recursion
watch(
  () => props.modelValue,
  (newValue) => {
    // Only update if different reference to prevent recursive updates
    if (newValue !== files.value) {
      files.value = [...newValue]
    }
  }
)

// Helpers
function isFile(f: any): f is File {
  return f instanceof File
}

function getFileUrl(file: File | string) {
  return isFile(file) ? URL.createObjectURL(file) : file
}

function getFileName(file: File | string) {
  if (isFile(file)) return file.name
  if (typeof file === 'string') return file.split('/').pop() || file
  return 'file'
}

// Formatted accept string for display
const formattedAccept = computed(() => {
  if (!props.accept) return ''
  return props.accept.split(',').map(f => f.replace('.', '')).join(', ')
})

// Handle file selection
function handleFilesChange(event: Event) {
  const target = event.target as HTMLInputElement
  if (!target.files) return

  const selectedFiles = Array.from(target.files)
  const remaining = (props.maxFiles || 3) - files.value.length
  if (remaining <= 0) return

  files.value.push(...selectedFiles.slice(0, remaining))
  emit('update:modelValue', files.value) // <-- emit immediately
}

// Remove a file
function removeFile(index: number) {
  files.value.splice(index, 1)
  emit('update:modelValue', files.value) // <-- emit immediately
}
</script>

<template>
  <div :class="props.wrapperClass || ''">
    <label class="font-medium mb-1 block">{{ props.label }}</label>

    <input
      type="file"
      :accept="props.accept"
      multiple
      class="border rounded px-3 py-2 w-1/2"
      @change="handleFilesChange"
    />

    <p class="text-sm text-gray-500 mt-1">
      You can upload up to {{ props.maxFiles || 3 }} files.
      <span v-if="props.accept">Supported types: {{ formattedAccept }}</span>
    </p>

    <div class="flex flex-wrap gap-2 mt-2">
      <div
        v-for="(f, index) in files"
        :key="index"
        class="bg-gray-200 text-gray-800 px-2 py-1 rounded flex items-center space-x-2"
      >
        <!-- Image preview -->
        <template v-if="isFile(f) && f.type.startsWith('image/')">
          <img
            :src="getFileUrl(f)"
            class="w-12 h-12 object-cover rounded cursor-pointer"
            @click="window.open(getFileUrl(f), '_blank')"
          />
        </template>

        <!-- PDF preview -->
        <template v-else-if="isFile(f) && f.type === 'application/pdf'">
          <iframe
            :src="getFileUrl(f)"
            class="w-24 h-24 border cursor-pointer"
            @click="window.open(getFileUrl(f), '_blank')"
          ></iframe>
        </template>

        <!-- Existing file URL -->
        <template v-else-if="typeof f === 'string'">
          <span class="text-sm cursor-pointer" @click="window.open(f, '_blank')">
            {{ getFileName(f) }}
          </span>
        </template>

        <!-- Delete button -->
        <button
          type="button"
          class="text-red-500 hover:text-red-700"
          @click="removeFile(index)"
        >
          <CircleX class="w-5 h-5" />
        </button>
      </div>
    </div>
  </div>
</template>
