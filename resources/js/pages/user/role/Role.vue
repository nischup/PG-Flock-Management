<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import FilterControls from '@/components/FilterControls.vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useListFilters } from '@/composables/useListFilters';
import { useNotifier } from '@/composables/useNotifier';
import type { BreadcrumbItem } from '@/types';
import { usePermissions } from '@/composables/usePermissions';
import { ref, computed } from 'vue';
import { 
  Shield, 
  Plus, 
  Search, 
  Filter, 
  MoreVertical, 
  Edit, 
  Trash2, 
  Eye, 
  Users, 
  Settings,
  ChevronDown,
  FileText,
  Download
} from 'lucide-vue-next';

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

// State for dropdowns and UI
const openDropdownId = ref<number | null>(null);
const showExportDropdown = ref(false);
const searchQuery = ref(props.filters.search || '');

// Computed properties
const totalRoles = computed(() => props.roles.meta.total);
const currentPage = computed(() => props.roles.meta.current_page);
const lastPage = computed(() => props.roles.meta.last_page);

function deleteRole(id: number) {
  confirmDelete({
    url: `/user-role/${id}`,
    text: 'This will permanently delete the role.',
    successMessage: 'Role has been deleted.',
  });
}

function toggleDropdown(id: number) {
  openDropdownId.value = openDropdownId.value === id ? null : id;
}

function closeDropdown() {
  openDropdownId.value = null;
}

function handleClick(event: Event) {
  if (!(event.target as Element).closest('.dropdown-container')) {
    closeDropdown();
  }
}

// Export functions
function exportPdf() {
  window.open('/reports/user-role/pdf', '_blank');
}

function exportExcel() {
  window.open('/reports/user-role/excel', '_blank');
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'User Roles', href: '/user-role' },
  { title: 'Role Management', href: '/user-role' },
];
</script>

<template>
  <Head title="Roles" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 p-6" @click="handleClick">
      <div class="max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="mb-8">
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
              <div class="p-3 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl shadow-lg">
                <Shield class="w-8 h-8 text-white" />
              </div>
              <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Role Management</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1">Manage user roles and permissions across your organization</p>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <!-- Export Dropdown -->
              <div class="relative dropdown-container">
                <Button
                  @click="showExportDropdown = !showExportDropdown"
                  class="group relative overflow-hidden rounded-lg px-4 py-2 text-sm font-semibold text-slate-700 dark:text-slate-300 shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-slate-500 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700"
                >
                  <span class="relative z-10 flex items-center gap-2">
                    <FileText class="w-4 h-4 transition-transform duration-300 group-hover:scale-110" />
                    Export Report
                    <ChevronDown class="w-4 h-4 transition-transform duration-300" :class="{ 'rotate-180': showExportDropdown }" />
                  </span>
                  <div class="absolute inset-0 bg-gradient-to-r from-transparent via-slate-100 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-20 group-hover:translate-x-full"></div>
                </Button>
                
                <!-- Export Dropdown Menu -->
                <div v-if="showExportDropdown" class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 z-50">
                  <div class="py-2">
                    <button
                      @click="exportPdf(); showExportDropdown = false"
                      class="w-full px-4 py-2 text-left text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center gap-2"
                    >
                      <FileText class="w-4 h-4 text-red-500" />
                      Export as PDF
                    </button>
                    <button
                      @click="exportExcel(); showExportDropdown = false"
                      class="w-full px-4 py-2 text-left text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center gap-2"
                    >
                      <Download class="w-4 h-4 text-green-500" />
                      Export as Excel
                    </button>
                  </div>
                </div>
              </div>

              <!-- Add Role Button -->
              <Link
                v-if="can('role.create')"
                href="/user-role/create"
                class="group relative overflow-hidden rounded-lg px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-purple-500"
                style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);"
              >
                <span class="relative z-10 flex items-center gap-2">
                  <Plus class="w-4 h-4 transition-transform duration-300 group-hover:rotate-90" />
                  Add New Role
                </span>
                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-20 group-hover:translate-x-full"></div>
              </Link>
            </div>
          </div>

          <!-- Stats Cards -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
              <div class="flex items-center gap-4">
                <div class="p-3 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg">
                  <Shield class="w-6 h-6 text-white" />
                </div>
                <div>
                  <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Total Roles</p>
                  <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ totalRoles }}</p>
                </div>
              </div>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
              <div class="flex items-center gap-4">
                <div class="p-3 bg-gradient-to-r from-green-500 to-green-600 rounded-lg">
                  <Users class="w-6 h-6 text-white" />
                </div>
                <div>
                  <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Active Roles</p>
                  <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ totalRoles }}</p>
                </div>
              </div>
            </div>
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
              <div class="flex items-center gap-4">
                <div class="p-3 bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg">
                  <Settings class="w-6 h-6 text-white" />
                </div>
                <div>
                  <p class="text-sm font-medium text-slate-600 dark:text-slate-400">Permissions</p>
                  <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ roles.data.reduce((acc, role) => acc + role.permissions.length, 0) }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content -->
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
          <!-- Search and Filters -->
          <div class="px-6 py-4 bg-gradient-to-r from-slate-50/50 to-slate-100/50 dark:from-slate-700/50 dark:to-slate-800/50 border-b border-slate-200 dark:border-slate-700">
            <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
              <div class="flex-1 max-w-md">
                <div class="relative">
                  <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-slate-400" />
                  <Input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search roles..."
                    class="pl-10 w-full"
                  />
                </div>
              </div>
              <div class="flex items-center gap-2">
                <FilterControls :filters="props.filters" routeName="/user-role" />
              </div>
            </div>
          </div>

          <!-- Roles Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
              <thead class="bg-slate-50 dark:bg-slate-800">
                <tr>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                    Role Details
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                    Permissions
                  </th>
                  <th class="px-6 py-4 text-left text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                    Status
                  </th>
                  <th class="px-6 py-4 text-right text-xs font-semibold text-slate-600 dark:text-slate-400 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-slate-900 divide-y divide-slate-200 dark:divide-slate-700">
                <tr
                  v-for="role in roles.data"
                  :key="role.id"
                  class="hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors duration-200"
                >
                  <!-- Role Details -->
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                      <div class="p-2 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg">
                        <Shield class="w-5 h-5 text-white" />
                      </div>
                      <div>
                        <div class="text-sm font-semibold text-slate-900 dark:text-white">{{ role.name }}</div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">ID: {{ role.id }}</div>
                      </div>
                    </div>
                  </td>

                  <!-- Permissions -->
                  <td class="px-6 py-4">
                    <div class="flex flex-wrap gap-1">
                      <span
                        v-for="perm in role.permissions.slice(0, 3)"
                        :key="perm.id"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300"
                      >
                        {{ perm.name }}
                      </span>
                      <span
                        v-if="role.permissions.length > 3"
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300"
                      >
                        +{{ role.permissions.length - 3 }} more
                      </span>
                    </div>
                  </td>

                  <!-- Status -->
                  <td class="px-6 py-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300">
                      <div class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5"></div>
                      Active
                    </span>
                  </td>

                  <!-- Actions -->
                  <td class="px-6 py-4 text-right">
                    <div class="relative dropdown-container">
                      <Button
                        @click="toggleDropdown(role.id)"
                        class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors duration-200"
                      >
                        <MoreVertical class="w-4 h-4" />
                      </Button>

                      <!-- Dropdown Menu -->
                      <div
                        v-if="openDropdownId === role.id"
                        class="absolute right-0 mt-2 w-48 bg-white dark:bg-slate-800 rounded-xl shadow-xl border border-slate-200 dark:border-slate-700 z-50"
                      >
                        <div class="py-2">
                          <Link
                            :href="`/user-role/${role.id}`"
                            class="w-full px-4 py-2 text-left text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center gap-2"
                          >
                            <Eye class="w-4 h-4 text-blue-500" />
                            View Details
                          </Link>
                          <Link
                            v-if="can('role.edit')"
                            :href="`/user-role/${role.id}/edit`"
                            class="w-full px-4 py-2 text-left text-sm text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-700 flex items-center gap-2"
                          >
                            <Edit class="w-4 h-4 text-indigo-500" />
                            Edit Role
                          </Link>
                          <button
                            v-if="can('role.delete')"
                            @click="deleteRole(role.id)"
                            class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-2"
                          >
                            <Trash2 class="w-4 h-4" />
                            Delete Role
                          </button>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>

                <!-- Empty State -->
                <tr v-if="roles.data.length === 0">
                  <td colspan="4" class="px-6 py-12 text-center">
                    <div class="flex flex-col items-center gap-4">
                      <div class="p-4 bg-slate-100 dark:bg-slate-700 rounded-full">
                        <Shield class="w-8 h-8 text-slate-400" />
                      </div>
                      <div>
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-white">No roles found</h3>
                        <p class="text-slate-500 dark:text-slate-400 mt-1">Get started by creating your first role</p>
                      </div>
                      <Link
                        v-if="can('role.create')"
                        href="/user-role/create"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-lg transition-colors duration-200"
                      >
                        <Plus class="w-4 h-4" />
                        Create Role
                      </Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
            <Pagination :meta="roles.meta" :page="currentPage" />
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
