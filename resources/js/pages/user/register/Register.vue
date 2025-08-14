<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import FilterControls from '@/components/FilterControls.vue';
import Pagination from '@/components/Pagination.vue';
import { useListFilters } from '@/composables/useListFilters';
import { useNotifier } from '@/composables/useNotifier';
import { type BreadcrumbItem } from '@/types';

const props = defineProps<{
  users: {
    data: Array<{
      id: number;
      name: string;
      email: string;
      roles: Array<{ id: number; name: string }>;
      permissions: Array<{ id: number; name: string }>;
      company?: { id: number; name: string } | null;
      shed?: { id: number; name: string } | null;
    }>;
    meta: { current_page: number; last_page: number };
  };
  filters: { search?: string; per_page?: number };
}>();

useListFilters({
  routeName: '/users',
  filters: props.filters,
});


const { confirmDelete } = useNotifier();

const deleteUser = (id: number) => {
  confirmDelete({
    url: `/user-register/${id}`,
    text: 'This will permanently delete the user.',
    successMessage: 'User has been deleted.',
  });
};

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Users', href: '/users' },
];
</script>

<template>
  <Head title="Users" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow-md">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <h1 class="text-3xl font-semibold text-gray-800 dark:text-white">Users</h1>
        <Link
          href="/user-register/create"
          class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded shadow transition"
        >
          + Add User
        </Link>
      </div>

      <!-- Filters -->
      <FilterControls routeName="/users                                                                                                                         " :filters="props.filters" />

      <!-- Table -->
      <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
            <tr>
              <th class="px-6 py-3 text-left font-semibold">Name</th>
              <th class="px-6 py-3 text-left font-semibold">Email</th>
              <th class="px-6 py-3 text-left font-semibold">Company</th>
              <th class="px-6 py-3 text-left font-semibold">Shed</th>
              <th class="px-6 py-3 text-left font-semibold">Roles</th>
              <th class="px-6 py-3 text-left font-semibold">Permissions</th>
              <th class="px-6 py-3 text-left font-semibold">Actions</th>
              
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            <tr
              v-for="user in users.data"
              :key="user.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800"
            >
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ user.name }}</td>
              <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ user.email }}</td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ user.company?.name || 'None' }}</td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ user.shed?.name || 'None' }}</td>
              <td class="px-6 py-4">
                <span
                  v-for="role in user.roles"
                  :key="role.id"
                  class="inline-block bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200 px-2 py-0.5 rounded-full text-xs mr-1 mb-1"
                >
                  {{ role.name }}
                </span>
              </td>
              <td class="px-6 py-4">
                <span
                  v-for="perm in user.permissions"
                  :key="perm.id"
                  class="inline-block bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 px-2 py-0.5 rounded-full text-xs mr-1 mb-1"
                >
                  {{ perm.name }}
                </span>
              </td>
              <td class="px-6 py-4 flex gap-4 items-center">
                <Link
                  :href="`/user-register/${user.id}/edit`"
                  class="text-indigo-600 hover:underline font-medium"
                >
                  Edit
                </Link>
                <button
                  @click="deleteUser(user.id)"
                  class="text-red-600 hover:underline font-medium"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="users.data.length === 0">
              <td colspan="5" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">
                No users found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <Pagination :meta="users.meta" class="mt-6" />
    </div>
  </AppLayout>
</template>
