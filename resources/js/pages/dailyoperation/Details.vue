<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { type BreadcrumbItem } from '@/types';
import Pagination from '@/components/Pagination.vue';

// Props from route
const props = defineProps<{
  flockId: number;
  startDate: string;
  endDate: string;
  mortalityData?: Array<{ date: string; male: number; female: number; note: string }>;
}>();

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Daily Operation', href: '/daily-operations' },
  { title: 'Details', href: '' },
];

// Dummy mortality data if not provided
const mortalityData = ref(props.mortalityData ?? [
  { date: '2025-08-01', male: 5, female: 3, note: 'Normal' },
  { date: '2025-08-02', male: 2, female: 4, note: 'Heat stress' },
  { date: '2025-08-03', male: 3, female: 3, note: 'Normal' },
]);

// Compute total male/female
const totalMortality = computed(() => {
  return mortalityData.value.reduce(
    (acc, cur) => ({
      male: acc.male + cur.male,
      female: acc.female + cur.female,
    }),
    { male: 0, female: 0 }
  );
});

// Watch props if you want to fetch from API in future
watch(
  () => [props.flockId, props.startDate, props.endDate],
  ([flockId, start, end]) => {
    console.log('Fetch data for', flockId, start, end);
    // Example: fetch via Inertia.visit or axios
    // mortalityData.value = fetchedData
  },
  { immediate: true }
);
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Daily Operation Details" />

    <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow-md space-y-6">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-4">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">
          Mortality Details
        </h1>
        <p class="text-gray-600 dark:text-gray-300 text-sm">
          Flock ID: {{ props.flockId }}, From {{ props.startDate }} To {{ props.endDate }}
        </p>
      </div>

      <!-- Small Cards for Male/Female Mortality -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="p-4 bg-green-100 dark:bg-green-900 rounded shadow text-center">
          <h3 class="font-medium text-gray-700 dark:text-green-200 mb-1">Total Male Mortality</h3>
          <p class="text-xl font-bold text-gray-900 dark:text-white">{{ totalMortality.male }}</p>
        </div>
        <div class="p-4 bg-pink-100 dark:bg-pink-900 rounded shadow text-center">
          <h3 class="font-medium text-gray-700 dark:text-pink-200 mb-1">Total Female Mortality</h3>
          <p class="text-xl font-bold text-gray-900 dark:text-white">{{ totalMortality.female }}</p>
        </div>
      </div>

      <!-- Mortality Table -->
      <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
            <tr>
              <th class="px-6 py-3 text-left font-semibold">Date</th>
              <th class="px-6 py-3 text-left font-semibold">Male Mortality</th>
              <th class="px-6 py-3 text-left font-semibold">Female Mortality</th>
              <th class="px-6 py-3 text-left font-semibold">Note</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            <tr
              v-for="item in mortalityData"
              :key="item.date"
              class="hover:bg-gray-50 dark:hover:bg-gray-800"
            >
              <td class="px-6 py-4">{{ item.date }}</td>
              <td class="px-6 py-4">{{ item.male }}</td>
              <td class="px-6 py-4">{{ item.female }}</td>
              <td class="px-6 py-4">{{ item.note }}</td>
            </tr>
            <tr v-if="mortalityData.length === 0">
              <td colspan="4" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">
                No mortality records found in this date range.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Optional Pagination (reuse if needed) -->
      <!-- <Pagination :meta="metaData" class="mt-4" /> -->
    </div>
  </AppLayout>
</template>
