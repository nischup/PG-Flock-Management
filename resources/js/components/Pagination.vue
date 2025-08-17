<script setup lang="ts">
import { computed } from 'vue';
import { useListFilters } from '@/composables/useListFilters';

const props = defineProps<{
  meta?: {
    current_page: number;
    last_page: number;
  };
}>();

const { page } = useListFilters();

// Ensure meta has safe defaults
const meta = computed(() => ({
  current_page: props.meta?.current_page ?? 1,
  last_page: props.meta?.last_page ?? 1,
}));

function setPage(n: number) {
  if (n >= 1 && n <= meta.value.last_page) {
    page.value = n;
  }
}
</script>

<template>
  <div class="flex gap-2 items-center" v-if="meta.last_page > 1">
    <!-- Previous button -->
    <button
      :disabled="meta.current_page === 1"
      @click="setPage(meta.current_page - 1)"
      class="px-3 py-1 border rounded hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
    >
      Previous
    </button>

    <!-- Page numbers -->
    <button
      v-for="n in meta.last_page"
      :key="n"
      @click="setPage(n)"
      :class="[
        'px-3 py-1 border rounded',
        n === meta.current_page
          ? 'font-bold bg-blue-500 text-white'
          : 'hover:bg-gray-200'
      ]"
    >
      {{ n }}
    </button>

    <!-- Next button -->
    <button
      :disabled="meta.current_page === meta.last_page"
      @click="setPage(meta.current_page + 1)"
      class="px-3 py-1 border rounded hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed"
    >
      Next
    </button>
  </div>
</template>

<style scoped>
button:disabled {
  pointer-events: none;
}
</style>
