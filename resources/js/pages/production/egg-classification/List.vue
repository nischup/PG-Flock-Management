<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useNotifier } from '@/composables/useNotifier';
import type { BreadcrumbItem } from '@/types';
import { usePermissions } from '@/composables/usePermissions';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
  Search,
  Filter,
  Calendar,
  Download,
  Eye,
  Edit,
  Trash2,
  Plus,
  ChevronDown,
  X,
  RefreshCw,
  Egg,
  AlertCircle,
  CheckCircle2,
  Settings
} from 'lucide-vue-next';

const props = defineProps<{
  classifications: {
    data: Array<{
      id: number;
      classification_date: string;
      total_eggs: number;
      batchAssign: { 
        name?: string; 
        transaction_no?: string;
        shed?: { name?: string };
        batch?: { name?: string };
      } | null;
      technicalEggs: Array<{ id: number; quantity: number; eggType: { name?: string } | null }>;
      rejectedEggs: Array<{ id: number; quantity: number; eggType: { name?: string } | null }>;
      commercial_eggs: number;
      hatching_eggs: number;
      rejected_eggs: number;
      technical_eggs: number;
    }>;
    meta: { current_page: number; last_page: number; per_page: number; total: number };
  };
  filters: { 
    search?: string; 
    per_page?: number; 
    page?: number;
    date_from?: string;
    date_to?: string;
    batch?: string;
    sort_by?: string;
    sort_order?: string;
  };
}>();



const { confirmDelete, showInfo } = useNotifier();
const { can } = usePermissions();

// Filter states
const showFilters = ref(false);
const searchQuery = ref(props.filters.search || '');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const batchFilter = ref(props.filters.batch || '');
const sortBy = ref(props.filters.sort_by || 'classification_date');
const sortOrder = ref(props.filters.sort_order || 'desc');
const perPage = ref(props.filters.per_page || 10);

// Filter options
const sortOptions = [
  { value: 'classification_date', label: 'Date' },
  { value: 'total_eggs', label: 'Total Eggs' },
  { value: 'hatching_eggs', label: 'Hatching Eggs' },
  { value: 'commercial_eggs', label: 'Commercial Eggs' },
  { value: 'rejected_eggs', label: 'Rejected Eggs' },
  { value: 'technical_eggs', label: 'Technical Eggs' },
];

const perPageOptions = [10, 25, 50, 100];

// Computed properties
const totalRecords = computed(() => props.classifications?.meta?.total || 0);
const currentPage = computed(() => props.classifications?.meta?.current_page || 1);
const totalPages = computed(() => props.classifications?.meta?.last_page || 1);

// Filter functions
function applyFilters() {
  const params = {
    search: searchQuery.value || undefined,
    date_from: dateFrom.value || undefined,
    date_to: dateTo.value || undefined,
    batch: batchFilter.value || undefined,
    sort_by: sortBy.value,
    sort_order: sortOrder.value,
    per_page: perPage.value,
    page: 1, // Reset to first page when filtering
  };

  router.get('/production/egg-classification', params, {
    preserveState: true,
    replace: true,
  });
}

function clearFilters() {
  searchQuery.value = '';
  dateFrom.value = '';
  dateTo.value = '';
  batchFilter.value = '';
  sortBy.value = 'classification_date';
  sortOrder.value = 'desc';
  perPage.value = 10;
  applyFilters();
}

function deleteClassification(id: number) {
  confirmDelete({
    url: `/production/egg-classification/${id}`,
    text: 'This will permanently delete the classification record.',
    successMessage: 'Classification record has been deleted.',
  });
}

// Watch for changes and apply filters with debounce
let filterTimeout: NodeJS.Timeout;
watch([searchQuery, dateFrom, dateTo, batchFilter, sortBy, sortOrder, perPage], () => {
  clearTimeout(filterTimeout);
  filterTimeout = setTimeout(() => {
    applyFilters();
  }, 500);
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Production', href: '/production' },
  { title: 'Egg Classification', href: '' },
];

// Totals helper functions
function totalRejected(rejecteds: Array<{ quantity: number }>) {
  return rejecteds?.reduce((sum, r) => sum + r.quantity, 0) || 0;
}

function totalTechnical(technicals: Array<{ quantity: number }>) {
  return technicals?.reduce((sum, t) => sum + t.quantity, 0) || 0;
}

// Format date
function formatDate(dateString: string) {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
}

// Get status color based on egg counts
function getStatusColor(item: any) {
  const total = item.total_eggs || 0;
  const hatching = item.hatching_eggs || 0;
  const rejected = item.rejected_eggs || 0;
  const technical = item.technical_eggs || 0;
  
  if (hatching > total * 0.8) return 'text-green-600 bg-green-50';
  if (rejected > total * 0.3) return 'text-red-600 bg-red-50';
  if (technical > total * 0.2) return 'text-yellow-600 bg-yellow-50';
  return 'text-blue-600 bg-blue-50';
}
</script>

<template>
  <Head title="Egg Classification" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200 shadow-sm overflow-hidden mb-6">
      <div class="bg-gradient-to-r from-gray-900 to-black px-6 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-white/20 rounded flex items-center justify-center">
              <Egg class="w-5 h-5 text-white" />
            </div>
            <div>
              <h1 class="text-2xl font-bold text-white">Egg Classification</h1>
              <p class="text-blue-100 text-sm">{{ totalRecords }} total records</p>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <Button
              @click="showFilters = !showFilters"
              variant="outline"
              class="bg-white/10 border-white/20 text-white hover:bg-white/20"
            >
              <Filter class="w-4 h-4 mr-2" />
              Filters
            </Button>
            <Link
              href="/production/egg-classification/create"
              class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 text-sm font-medium"
            >
              <Plus class="w-4 h-4 mr-2" />
              Add Classification
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters Section -->
    <div v-if="showFilters" class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 mb-6">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800 flex items-center">
          <Settings class="w-5 h-5 mr-2 text-blue-600" />
          Filter Options
        </h3>
        <Button @click="clearFilters" variant="outline" size="sm">
          <X class="w-4 h-4 mr-1" />
          Clear All
        </Button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Search -->
        <div class="space-y-2">
          <Label class="text-sm font-medium text-gray-700">Search</Label>
          <div class="relative">
            <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
            <Input
              v-model="searchQuery"
              placeholder="Search by batch, transaction..."
              class="pl-10"
            />
          </div>
        </div>

        <!-- Date From -->
        <div class="space-y-2">
          <Label class="text-sm font-medium text-gray-700">Date From</Label>
          <div class="relative">
            <Calendar class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
            <Input
              v-model="dateFrom"
              type="date"
              class="pl-10"
            />
          </div>
        </div>

        <!-- Date To -->
        <div class="space-y-2">
          <Label class="text-sm font-medium text-gray-700">Date To</Label>
          <div class="relative">
            <Calendar class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
            <Input
              v-model="dateTo"
              type="date"
              class="pl-10"
            />
          </div>
        </div>

        <!-- Sort By -->
        <div class="space-y-2">
          <Label class="text-sm font-medium text-gray-700">Sort By</Label>
          <div class="relative">
            <select
              v-model="sortBy"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                {{ option.label }}
              </option>
            </select>
          </div>
        </div>

        <!-- Sort Order -->
        <div class="space-y-2">
          <Label class="text-sm font-medium text-gray-700">Order</Label>
          <div class="relative">
            <select
              v-model="sortOrder"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option value="desc">Descending</option>
              <option value="asc">Ascending</option>
            </select>
          </div>
        </div>

        <!-- Per Page -->
        <div class="space-y-2">
          <Label class="text-sm font-medium text-gray-700">Per Page</Label>
          <div class="relative">
            <select
              v-model="perPage"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            >
              <option v-for="option in perPageOptions" :key="option" :value="option">
                {{ option }} records
              </option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
      <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-blue-600 text-sm font-medium">Total Records</p>
            <p class="text-2xl font-bold text-blue-900">{{ totalRecords }}</p>
          </div>
          <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
            <Egg class="w-6 h-6 text-white" />
          </div>
        </div>
      </div>

      <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-4 border border-green-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-green-600 text-sm font-medium">Current Page</p>
            <p class="text-2xl font-bold text-green-900">{{ currentPage }} / {{ totalPages }}</p>
          </div>
          <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
            <CheckCircle2 class="w-6 h-6 text-white" />
          </div>
        </div>
      </div>

      <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 rounded-lg p-4 border border-yellow-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-yellow-600 text-sm font-medium">Records Per Page</p>
            <p class="text-2xl font-bold text-yellow-900">{{ perPage }}</p>
          </div>
          <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
            <Settings class="w-6 h-6 text-white" />
          </div>
        </div>
      </div>

      <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-lg p-4 border border-purple-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-purple-600 text-sm font-medium">Showing</p>
            <p class="text-2xl font-bold text-purple-900">{{ (props.classifications?.data || []).length }}</p>
          </div>
          <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
            <Eye class="w-6 h-6 text-white" />
          </div>
        </div>
      </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gradient-to-r from-gray-50 to-gray-100">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                Date & Batch
              </th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                Total Eggs
              </th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                Hatching
              </th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                Commercial
              </th>
              <!-- <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                Rejected
              </th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                Technical
              </th> -->
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                Actions
              </th>
           </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="item in (props.classifications?.data || [])"
              :key="item.id"
              class="hover:bg-gray-50 transition-colors duration-200"
            >
              <!-- Date & Batch -->
             <td class="px-6 py-4">
                <div class="flex flex-col">
                  <div class="text-sm font-medium text-gray-900">{{ formatDate(item.classification_date) }}</div>
                  <div class="text-xs text-gray-500">{{ item.batch_assign?.transaction_no || 'N/A' }}</div>
                  <div class="text-xs text-gray-400">{{ item.batch_assign?.batch?.name || 'No Shed' }}</div>
                </div>
              </td>

              <!-- Total Eggs -->
              <td class="px-6 py-4">
                <div class="text-sm font-semibold text-gray-900">{{ item.total_eggs?.toLocaleString() || 0 }}</div>
              </td>

              <!-- Hatching Eggs -->
              <td class="px-6 py-4">
                <div class="flex items-center">
                  <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                  <span class="text-sm font-medium text-green-700">{{ item.hatching_eggs?.toLocaleString() || 0 }}</span>
                </div>
              </td>

              <!-- Commercial Eggs -->
              <td class="px-6 py-4">
                <div class="flex items-center">
                  <div class="w-2 h-2 bg-red-500 rounded-full mr-2"></div>
                  <span class="text-sm font-medium text-red-700">{{ item.commercial_eggs?.toLocaleString() || 0 }}</span>
                </div>
              </td>

              <!-- Rejected Eggs 
              <td class="px-6 py-4">
                <div class="flex items-center">
                  <div class="w-2 h-2 bg-orange-500 rounded-full mr-2"></div>
                  <span class="text-sm font-medium text-orange-700">{{ item.rejected_eggs?.toLocaleString() || 0 }}</span>
                </div>
              </td>
              -->
              <!-- Technical Eggs 
              <td class="px-6 py-4">
                <div class="flex items-center">
                  <div class="w-2 h-2 bg-blue-500 rounded-full mr-2"></div>
                  <span class="text-sm font-medium text-blue-700">{{ item.technical_eggs?.toLocaleString() || 0 }}</span>
                </div>
              </td>
              -->
              <!-- Status -->
              <td class="px-6 py-4">
                <span 
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="getStatusColor(item)"
                >
                  <div class="w-1.5 h-1.5 rounded-full mr-1.5"
                    :class="getStatusColor(item).includes('green') ? 'bg-green-500' : 
                           getStatusColor(item).includes('red') ? 'bg-red-500' : 
                           getStatusColor(item).includes('yellow') ? 'bg-yellow-500' : 'bg-blue-500'">
                  </div>
                  {{ getStatusColor(item).includes('green') ? 'Excellent' : 
                     getStatusColor(item).includes('red') ? 'High Rejection' : 
                     getStatusColor(item).includes('yellow') ? 'Technical Issues' : 'Normal' }}
                </span>
              </td>

              <!-- Actions -->
              <td class="px-6 py-4">
                <div class="flex items-center space-x-2">
                  <!-- View Details Button -->
                  <Link
                    :href="`/production/egg-classification/${item.id}`"
                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-blue-600 bg-blue-50 hover:bg-blue-100 rounded-md transition-colors duration-200"
                    title="View Details"
                  >
                    <Eye class="w-3 h-3 mr-1" />
                    View
                  </Link>
                  
                  <!-- Edit Button -->
                  <Link
                    v-if="can('egg-classification.edit')"
                    :href="`/production/egg-classification/${item.id}/edit`"
                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-600 bg-green-50 hover:bg-green-100 rounded-md transition-colors duration-200"
                    title="Edit Classification"
                  >
                    <Edit class="w-3 h-3 mr-1" />
                    Edit
                  </Link>
                  
                  <!-- Delete Button -->
                  <button
                    v-if="can('egg-classification.delete')"
                    @click="deleteClassification(item.id)"
                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-600 bg-red-50 hover:bg-red-100 rounded-md transition-colors duration-200"
                    title="Delete Classification"
                  >
                    <Trash2 class="w-3 h-3 mr-1" />
                    Delete
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty State -->
            <tr v-if="(props.classifications?.data || []).length === 0">
              <td colspan="6" class="px-6 py-12 text-center">
                <div class="flex flex-col items-center">
                  <AlertCircle class="w-12 h-12 text-gray-400 mb-4" />
                  <h3 class="text-lg font-medium text-gray-900 mb-2">No classification records found</h3>
                  <p class="text-gray-500 mb-4">Get started by creating your first egg classification record.</p>
                  <Link
                    href="/production/egg-classification/create"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200"
                  >
                    <Plus class="w-4 h-4 mr-2" />
                    Add Classification
                  </Link>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="bg-gray-50 px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Showing {{ ((currentPage - 1) * perPage) + 1 }} to {{ Math.min(currentPage * perPage, totalRecords) }} of {{ totalRecords }} results
          </div>
          <div class="flex items-center space-x-2">
            <Button
              @click="router.get('/production/egg-classification', { ...props.filters, page: currentPage - 1 })"
              :disabled="currentPage <= 1"
              variant="outline"
              size="sm"
            >
              Previous
            </Button>
            <span class="px-3 py-1 text-sm text-gray-700">
              Page {{ currentPage }} of {{ totalPages }}
            </span>
            <Button
              @click="router.get('/production/egg-classification', { ...props.filters, page: currentPage + 1 })"
              :disabled="currentPage >= totalPages"
              variant="outline"
              size="sm"
            >
              Next
            </Button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
