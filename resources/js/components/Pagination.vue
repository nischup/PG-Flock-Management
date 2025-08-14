<script setup lang="ts">
import { useListFilters } from '@/composables/useListFilters';

const props = defineProps<{
  meta?: {
    current_page: number;
    last_page: number;
  };
}>();

const meta = props.meta ?? { current_page: 1, last_page: 1 };

const { page } = useListFilters();

function setPage(n: number) {
  page.value = n;
}
</script>

<template>
  <div class="flex gap-2" v-if="meta && meta.last_page">
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
  </div>
</template>
