<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import FilterControls from '@/components/FilterControls.vue';
import Pagination from '@/components/Pagination.vue';
import { useNotifier } from '@/composables/useNotifier';
import { type BreadcrumbItem } from '@/types';
import { usePermissions } from '@/composables/usePermissions';

// Props for Daily Operations (dummy data)
const props = defineProps<{
  dailyOperations: {
    data: Array<{
      id: number;
      operation_date: string;
      flock_code: string;
      male_mortality: number;
      female_mortality: number;
      feed_consumption: string;
      water_consumption: string;
      light_hour: number;
      note: string;
    }>;
    meta: {
      current_page: number;
      last_page: number;
      per_page: number;
      total: number;
    };
  };
  filters: { search?: string; per_page?: number };
}>();

// If no data passed, add dummy records
if (!props.dailyOperations) {
  props.dailyOperations = {
    data: [
      {
        id: 1,
        operation_date: '2025-08-01',
        flock_code: '1-22A',
        male_mortality: 5,
        female_mortality: 3,
        feed_consumption: '200 Kg',
        water_consumption: '150 L',
        light_hour: 8,
        note: 'Normal day',
      },
      {
        id: 2,
        operation_date: '2025-08-02',
        flock_code: '1-22B',
        male_mortality: 2,
        female_mortality: 4,
        feed_consumption: '180 Kg',
        water_consumption: '130 L',
        light_hour: 9,
        note: 'Slight heat stress',
      },
    ],
    meta: { current_page: 1, last_page: 1, per_page: 10, total: 2 },
  };
}

const { confirmDelete } = useNotifier();
const { can } = usePermissions();

// Delete dummy operation
function deleteOperation(id: number) {
  confirmDelete({
    url: `/daily-operations/${id}`,
    text: 'This will permanently delete the daily operation.',
    successMessage: 'Daily operation deleted.',
  });
}

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Daily Operations', href: '/daily-operations' },
];
</script>

<template>
  <Head title="Daily Operations" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow-md">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <h1 class="text-3xl font-semibold text-gray-800 dark:text-white">Daily Operations</h1>
        <Link 
          href="/daily-operation/create"
          class="inline-flex items-center px-4 py-2 bg-chicken hover:bg-chicken text-white text-sm font-semibold rounded shadow transition"
        >
          + Add
        </Link>
      </div>

      <!-- Filters -->
      <FilterControls :filters="props.filters" routeName="/daily-operations" />

      <!-- Table -->
      <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
            <tr>
              <th class="px-6 py-3 text-left font-semibold">Date</th>
              <th class="px-6 py-3 text-left font-semibold">Flock</th>
              <th class="px-6 py-3 text-left font-semibold">Male Mortality</th>
              <th class="px-6 py-3 text-left font-semibold">Female Mortality</th>
              <th class="px-6 py-3 text-left font-semibold">Feed</th>
              <th class="px-6 py-3 text-left font-semibold">Water</th>
              <th class="px-6 py-3 text-left font-semibold">Light</th>
              <th class="px-6 py-3 text-left font-semibold">Note</th>
              <th class="px-6 py-3 text-left font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="op in dailyOperations.data" :key="op.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ op.operation_date }}</td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ op.flock_code }}</td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ op.male_mortality }}</td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ op.female_mortality }}</td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ op.feed_consumption }}</td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ op.water_consumption }}</td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ op.light_hour }}</td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ op.note }}</td>
              <td class="px-6 py-4 flex gap-4">
                <Link  :href="`/daily-operations/${op.id}/edit`" class="text-indigo-600 hover:underline font-medium">
                  Edit
                </Link>
                <button  @click="deleteOperation(op.id)" class="text-red-600 hover:underline font-medium">
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="dailyOperations.data.length === 0">
              <td colspan="9" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">
                No daily operations found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <Pagination :meta="dailyOperations.meta" class="mt-6" />
    </div>
  </AppLayout>
</template>
