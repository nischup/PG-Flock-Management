<template>
  <div class="mb-4">
    <input
      type="text"
      :value="modelValue"
      @input="onInput"
      @keyup.enter="emit('submit')"
      placeholder="Search..."
      class="w-full max-w-sm px-4 py-2 border border-gray-300 rounded-md shadow-sm
             focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
    />
  </div>
</template>

<script setup lang="ts">
const emit = defineEmits<{
  (e: 'update:modelValue', value: string): void;
  (e: 'submit'): void;
}>();

defineProps<{
  modelValue: string;
}>();

function onInput(event: Event) {
  const target = event.target as HTMLInputElement | null;
  if (target) {
    emit('update:modelValue', target.value);
  }
}
</script>
