<script setup lang="ts">
import { ref, watch, defineProps, defineEmits } from 'vue'
import {
  DialogRoot,
  DialogTrigger,
  DialogPortal,
  DialogOverlay,
  DialogContent,
  DialogTitle,
  DialogDescription,
  DialogClose
} from 'reka-ui'

type Field = {
  name: string
  label: string
  editable: boolean
  type?: string
  options?: Array<{ label: string; value: any }>
}

const props = defineProps<{
  show: boolean
  title: string
  fields: Field[]
  data: Record<string, any>
}>()

const emit = defineEmits<{
  (e: 'update:show', value: boolean): void
  (e: 'updateForm', value: Record<string, any>): void
}>()

// Local reactive copy of data
const localData = ref({ ...props.data })

// Update localData when parent data changes
watch(
  () => props.data,
  (newData) => {
    localData.value = { ...newData }
  },
  { deep: true, immediate: true }
)

// Emit updates back to parent
watch(
  localData,
  (val) => {
    emit('updateForm', val)
  },
  { deep: true }
)

const closeModal = () => {
  emit('update:show', false)
}
</script>

<template>
  <DialogRoot v-model:open="props.show">
    <DialogTrigger />
    <DialogPortal>
      <DialogOverlay />
      <DialogContent class="p-6 max-w-lg w-full">
        <DialogTitle class="text-lg font-bold">{{ props.title }}</DialogTitle>
        <DialogDescription class="mt-4">
          <div v-for="field in props.fields" :key="field.name" class="mb-4">
            <label :for="field.name" class="block text-sm font-medium text-gray-700">
              {{ field.label }}
            </label>

            <template v-if="field.editable">
              <div v-if="field.type === 'select'">
                <select v-model="localData.value[field.name]" :id="field.name" class="mt-1 block w-full border rounded p-2">
                  <option v-for="option in field.options" :key="option.value" :value="option.value">
                    {{ option.label }}
                  </option>
                </select>
              </div>
              <div v-else>
                <input
                  v-model="localData.value[field.name]"
                  :id="field.name"
                  :type="field.type === 'number' ? 'number' : 'text'"
                  class="mt-1 block w-full border rounded p-2"
                />
              </div>
            </template>

            <template v-else>
              <p class="mt-1 text-sm text-gray-500">{{ localData.value[field.name] ?? '-' }}</p>
            </template>
          </div>
        </DialogDescription>

        <div class="mt-4 flex justify-end gap-2">
          <button class="px-4 py-2 bg-gray-300 rounded" @click="closeModal">Close</button>
        </div>
      </DialogContent>
    </DialogPortal>
  </DialogRoot>
</template>
