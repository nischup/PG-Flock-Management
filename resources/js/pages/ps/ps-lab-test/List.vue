<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import FilterControls from '@/components/FilterControls.vue';
import Pagination from '@/components/Pagination.vue';
import { useListFilters } from '@/composables/useListFilters';
import { useNotifier } from '@/composables/useNotifier';
import { type BreadcrumbItem } from '@/types';
import { usePermissions } from '@/composables/usePermissions';

const props = defineProps<{
  psReceives: {
    data: Array<{
      id: number;
      pi_no: string;
      receive_date: string;
      supplier: { id: number; name: string } | null;
      quantity: number;
      remarks?: string | null;
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

useListFilters({
  routeName: '/ps-receive',
  filters: props.filters,
});

const { confirmDelete } = useNotifier();
const { can } = usePermissions();

const deleteReceive = (id: number) => {
  confirmDelete({
    url: `/ps-receive/${id}`,
    text: 'This will permanently delete the record.',
    successMessage: 'PS Receive deleted.',
  });
};

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'PS', href: '/ps-receive' },
];
</script>

<template>
  <Head title="PS Lab Test" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow-md">
      <!-- Header -->
      <div
        class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4"
      >
        <h1 class="text-3xl font-semibold text-gray-800 dark:text-white">
          PS Lab Test
        </h1>
        <!-- <Link
          v-if="can('ps.receive.create')"
          href="/ps-receive/create"
          class="inline-flex items-center px-4 py-2 bg-chicken hover:bg-chicken text-white text-sm font-semibold rounded shadow transition"
        >
          + Add PS Receive
        </Link> -->
      </div>

      <!-- Filters -->
      <FilterControls :filters="props.filters" routeName="/ps-receive" />

      <!-- Table -->
      <div
        class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700"
      >
        <table
          class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm"
        >
          <thead
            class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300"
          >
            <tr>
              <th class="px-6 py-3 text-left font-semibold">PI No</th>
              <th class="px-6 py-3 text-left font-semibold">LC NO</th>
              <th class="px-6 py-3 text-left font-semibold">Supplier</th>
              <th class="px-6 py-3 text-left font-semibold">Test Quantity</th>
              <th class="px-6 py-3 text-left font-semibold">Notes</th>
              <th class="px-6 py-3 text-left font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody
            class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700"
          >
            <tr
              v-for="item in psReceives.data"
              :key="item.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800"
            >
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">
                {{ item.pi_no }}
              </td>
              <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                {{ item.receive_date }}
              </td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">
                {{ item.supplier?.name || 'N/A' }}
              </td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">
                {{ item.quantity }}
              </td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">
                {{ item.remarks || '-' }}
              </td>
              <td class="px-6 py-4 flex gap-4 items-center">
                <Link
                  v-if="can('ps.receive.edit')"
                  :href="`/ps-receive/${item.id}/edit`"
                  class="text-indigo-600 hover:underline font-medium"
                >
                  Edit
                </Link>
                <button
                  @click="deleteReceive(item.id)"
                  class="text-red-600 hover:underline font-medium"
                  v-if="can('ps.receive.delete')"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="psReceives.data.length === 0">
              <td
                colspan="6"
                class="px-6 py-6 text-center text-gray-500 dark:text-gray-400"
              >
                No PS Receives found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <Pagination :meta="psReceives.meta" class="mt-6" />
    </div>
  </AppLayout>
</template>
