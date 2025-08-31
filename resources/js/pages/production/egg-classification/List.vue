<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import FilterControls from '@/components/FilterControls.vue';
import Pagination from '@/components/Pagination.vue';
import { useListFilters } from '@/composables/useListFilters';
import { useNotifier } from '@/composables/useNotifier';
import type { BreadcrumbItem } from '@/types';
import { usePermissions } from '@/composables/usePermissions';

const props = defineProps<{
  classifications: {
    data: Array<{
      id: number;
      grading_type: 'Commercial' | 'Hatching';
      grade: string;
      classification?: string;
      qty: number;
    }>;
    meta: { current_page: number; last_page: number; per_page: number; total: number };
  };
  filters: { search?: string; per_page?: number; page?: number };
}>();

// Setup reactive filters with Inertia
useListFilters({
  routeName: '/egg-classification',
  filters: props.filters,
});

const { confirmDelete } = useNotifier();
const { can } = usePermissions();

function deleteClassification(id: number) {
  confirmDelete({
    url: `/egg-classification/${id}`,
    text: 'This will permanently delete the classification record.',
    successMessage: 'Classification record has been deleted.',
  });
}

// Extract page safely
const page = props.filters.page ?? 1;

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Egg Classification', href: '/egg-classification' },
];
</script>

<template>
  <Head title="Egg Classification" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 m-5 bg-white dark:bg-gray-900 rounded-xl shadow-md">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <h1 class="text-3xl font-semibold text-gray-800 dark:text-white">Egg Classification</h1>
        <Link
          href="/production/egg-classification/create"
          class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold rounded shadow transition"
        >
          + Add
        </Link>
      </div>

      <!-- Filters -->
      <FilterControls :filters="props.filters" routeName="/egg-classification" />

      <!-- Classification Table -->
      <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 mt-4">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
            <tr>
              <th class="px-6 py-3 text-left font-bold">Grading Type</th>
              <th class="px-6 py-3 text-left font-bold">Grade</th>
              <th class="px-6 py-3 text-left font-bold">Classification</th>
              <th class="px-6 py-3 text-left font-bold">Quantity</th>
              <th class="px-6 py-3 text-left font-bold">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            <tr
              v-for="item in classifications.data"
              :key="item.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800"
            >
              <td class="px-6 py-4">{{ item.grading_type }}</td>
              <td class="px-6 py-4">{{ item.grade }}</td>
              <td class="px-6 py-4">{{ item.classification || '-' }}</td>
              <td class="px-6 py-4">{{ item.qty }}</td>
              <td class="px-6 py-4 flex gap-4 items-center">
                <Link
                  v-if="can('eggclassification.edit')"
                  :href="`/egg-classification/${item.id}/edit`"
                  class="text-indigo-600 hover:underline font-medium"
                >
                  Edit
                </Link>
                <button
                  v-if="can('eggclassification.delete')"
                  @click="deleteClassification(item.id)"
                  class="text-red-600 hover:underline font-medium"
                >
                  Delete
                </button>
              </td>
            </tr>

            <tr v-if="classifications.data.length === 0">
              <td colspan="5" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">
                No classification records found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <Pagination :meta="classifications.meta" :page="page" class="mt-6" />
    </div>
  </AppLayout>
</template>
