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
  roles: {
    data: Array<{
      id: number;
      name: string;
      permissions: { id: number; name: string }[];
    }>;
    meta: { current_page: number; last_page: number; per_page: number; total: number };
  };
  filters: { search?: string; per_page?: number; page?: number };
}>();

// Setup reactive filters with Inertia
useListFilters({
  routeName: '/user-role',
  filters: props.filters,
});

const { confirmDelete } = useNotifier();
const { can } = usePermissions();

function deleteRole(id: number) {
  confirmDelete({
    url: `/user-role/${id}`,
    text: 'This will permanently delete the role.',
    successMessage: 'Role has been deleted.',
  });
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'User Role', href: '/user-role' },
];
</script>

<template>
  <Head title="Roles" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow-md">

      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <h1 class="text-3xl font-semibold text-gray-800 dark:text-white">Roles</h1>
        <Link v-if="can('role.create')"
          href="/user-role/create"
          class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold rounded shadow transition"
        >
          + Add Role
        </Link>
      </div>

      <!-- Filters -->
      <FilterControls :filters="props.filters" routeName="/user-role" />

      <!-- Roles Table -->
      <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700 mt-4">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
            <tr class="throw">
              <th class="px-6 py-3 text-left font-bold">Name</th>
              <th class="px-6 py-3 text-left font-bold">Permissions</th>
              <th class="px-6 py-3 text-left font-bold">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            <tr
              v-for="role in roles.data"
              :key="role.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800"
            >
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ role.name }}</td>
              <td class="px-6 py-4">
                <span
                  v-for="perm in role.permissions"
                  :key="perm.id"
                  class="inline-block bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 px-2 py-0.5 rounded-full text-xs mr-1 mb-1"
                >
                  {{ perm.name }}
                </span>
              </td>
              <td class="px-6 py-4 flex gap-4 items-center">
                <Link
                  v-if="can('role.edit')"
                  :href="`/user-role/${role.id}/edit`"
                  class="text-indigo-600 hover:underline font-medium"
                >
                  Edit
                </Link>
                <button
                  v-if="can('role.delete')"
                  @click="deleteRole(role.id)"
                  class="text-red-600 hover:underline font-medium"
                >
                  Delete
                </button>
              </td>
            </tr>

            <tr v-if="roles.data.length === 0">
              <td colspan="3" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">
                No roles found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <Pagination :meta="roles.meta" :page="page" class="mt-6" />
    </div>
  </AppLayout>
</template>
