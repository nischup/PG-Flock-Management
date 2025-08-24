<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import FilterControls from '@/components/FilterControls.vue';
import Pagination from '@/components/Pagination.vue';
import { useListFilters } from '@/composables/useListFilters';
import { useNotifier } from '@/composables/useNotifier';
import { usePermissions } from '@/composables/usePermissions';
import type { BreadcrumbItem } from '@/types';
import dayjs from 'dayjs';

const props = defineProps<{
  labTests: {
    data: Array<{
      id: number;
      lab_type: string;
      lab_send_female_qty: number;
      lab_send_male_qty: number;
      lab_send_total_qty: number;
      notes?: string;
      status: number;
      ps_receive?: {
        id: number;
        pi_no: string;
        order_no: string;
        created_at: string;
      } | null;
    }>;
    meta: any;
  };
  filters: { search?: string; per_page?: number; page?: number };
}>();

useListFilters({
  routeName: '/ps-lab-test',
  filters: props.filters,
});

const { confirmDelete } = useNotifier();
const { can } = usePermissions();

function deleteLabTest(id: number) {
  confirmDelete({
    url: `/ps-lab-test/${id}`,
    text: 'This will permanently delete the lab test record.',
    successMessage: 'Lab Test deleted successfully.',
  });
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Lab Tests', href: '/ps-lab-test' },
];
</script>

<template>
  <Head title="Lab Tests" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow-md">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <h1 class="text-3xl font-semibold text-gray-800 dark:text-white">Lab Tests</h1>
        <Link v-if="can('ps-lab-test.create')"
          href="/ps-lab-test/create"
          class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold rounded shadow transition"
        >
          + Add
        </Link>
      </div>

      <!-- Filters -->
      <FilterControls :filters="props.filters" routeName="/ps-lab-test" />

      <!-- Table -->
      <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 mt-4">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
            <tr>
              <th class="px-6 py-3 text-left font-semibold">PI No</th>
              <th class="px-6 py-3 text-left font-semibold">Order No</th>
              <th class="px-6 py-3 text-left font-semibold">Receive Date</th>
              <th class="px-6 py-3 text-left font-semibold">Lab</th>
              
              <th class="px-6 py-3 text-left font-semibold">Female Qty</th>
              <th class="px-6 py-3 text-left font-semibold">Male Qty</th>
              <th class="px-6 py-3 text-left font-semibold">Lab</th>
              <th class="px-6 py-3 text-left font-semibold">Female Qty</th>
              <th class="px-6 py-3 text-left font-semibold">Male Qty</th>
              <th class="px-6 py-3 text-left font-semibold">Total Qty</th>
              <th class="px-6 py-3 text-left font-semibold">Notes</th>
              <th class="px-6 py-3 text-left font-semibold">Status</th>
              <th class="px-6 py-3 text-left font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            <tr
              v-for="lab in props.labTests.data"
              :key="lab.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800"
            >
              <td class="px-6 py-4">{{ lab.ps_receive?.pi_no ?? '-' }}</td>
              <td class="px-6 py-4">{{ lab.ps_receive?.order_no ?? '-' }}</td>
              <td class="px-6 py-4">
                {{ lab.ps_receive?.created_at ? dayjs(lab.ps_receive.created_at).format('YYYY-MM-DD') : '-' }}
              </td>
              <td class="px-6 py-4">{{ lab.lab_type }}</td>
              <td class="px-6 py-4">{{ lab.lab_send_female_qty }}</td>
              <td class="px-6 py-4">{{ lab.lab_send_male_qty }}</td>
              <td class="px-6 py-4">Provita Lab</td>
              <td class="px-6 py-4"></td>
              <td class="px-6 py-4"></td>
              <td class="px-6 py-4">{{ lab.lab_send_total_qty }}</td>
              <td class="px-6 py-4">{{ lab.notes ?? '-' }}</td>
              <td class="px-6 py-4">
                <span :class="lab.status === 1 ? 'text-green-600' : 'text-red-600'">
                  {{ lab.status === 1 ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="px-6 py-4 flex gap-4 items-center">
                <Link
                  v-if="can('ps-lab-test.edit')"
                  :href="`/ps-lab-test/${lab.id}/edit`"
                  class="text-indigo-600 hover:underline font-medium"
                >
                  Edit
                </Link>
                <!-- <button
                  v-if="can('ps-lab-test.delete')"
                  @click="deleteLabTest(lab.id)"
                  class="text-red-600 hover:underline font-medium"
                >
                  Delete
                </button> -->
              </td>
            </tr>

            <tr v-if="labTests.data.length === 0">
              <td colspan="10" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">
                No lab tests found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <Pagination :meta="labTests.meta" :page="props.filters.page" class="mt-6" />
    </div>
  </AppLayout>
</template>
