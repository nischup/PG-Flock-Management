<script setup lang="ts">
import SearchInput from '@/components/SearchInput.vue';
import { useListFilters } from '@/composables/useListFilters';

const props = defineProps<{
  routeName: string;
  filters?: { search?: string; per_page?: number; page?: number };
  extraParams?: Record<string, any>;
}>();

const { search, perPage } = useListFilters({
  routeName: props.routeName,
  filters: props.filters,
  extraParams: props.extraParams,
});
</script>

<template>
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
    <div class="w-full sm:w-1/2">
      <SearchInput v-model="search" />
    </div>

    <div class="flex items-center gap-2">
      <label for="perPage" class="text-sm">Per page:</label>
      <select
        id="perPage"
        v-model="perPage"
        class="border rounded-md px-3 py-2 text-sm"
      >
        <option :value="10">10</option>
        <option :value="20">20</option>
        <option :value="30">30</option>
      </select>
    </div>
  </div>
</template>
