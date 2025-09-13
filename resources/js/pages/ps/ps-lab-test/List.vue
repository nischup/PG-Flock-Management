<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { useListFilters } from '@/composables/useListFilters';
import { useNotifier } from '@/composables/useNotifier';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { FileText, Pencil, Calendar } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

// ✅ Props
const props = defineProps<{
    labTests?: {
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
        meta: { current_page: number; last_page: number; per_page: number; total: number };
    };
    filters?: { 
        search?: string; 
        per_page?: number; 
        lab_type?: string; 
        date_from?: string; 
        date_to?: string; 
    };
}>();

useListFilters({ routeName: '/ps-lab-test', filters: props.filters });
const { confirmDelete } = useNotifier();
const { can } = usePermissions();

const openDropdownId = ref<number | null>(null);
const toggleDropdown = (id: number) => {
    openDropdownId.value = openDropdownId.value === id ? null : id;
};
const closeDropdown = () => (openDropdownId.value = null);

// Date picker states
const showFromDatePicker = ref(false);
const showToDatePicker = ref(false);

// Dropdown states
const showLabTypeDropdown = ref(false);

// ✅ Close on outside click
const handleClick = (e: MouseEvent) => {
    if (!(e.target as HTMLElement).closest('.action-dropdown, .action-btn, .pdf-dropdown, .pdf-button, .date-picker-container, .lab-type-dropdown')) {
        closeDropdown();
        openExportDropdown.value = false;
        showFromDatePicker.value = false;
        showToDatePicker.value = false;
        showLabTypeDropdown.value = false;
    }
};
onMounted(() => document.addEventListener('click', handleClick));
onBeforeUnmount(() => document.removeEventListener('click', handleClick));

// ✅ Delete action
const deleteLabTest = (id: number) => {
    confirmDelete({
        url: `/ps-lab-test/${id}`,
        text: 'This will permanently delete the lab test record.',
        successMessage: 'Lab Test deleted successfully.',
    });
};

// ✅ Export filters
const filters = ref({
    search: props.filters?.search ?? '',
    per_page: props.filters?.per_page ?? 10,
    lab_type: props.filters?.lab_type ?? '',
    date_from: props.filters?.date_from ?? '',
    date_to: props.filters?.date_to ?? '',
});

// ✅ Filter methods
const applyFilters = () => {
    const params = new URLSearchParams();
    
    if (filters.value.search) params.set('search', filters.value.search);
    if (filters.value.per_page) params.set('per_page', filters.value.per_page.toString());
    if (filters.value.lab_type) params.set('lab_type', filters.value.lab_type);
    if (filters.value.date_from) params.set('date_from', filters.value.date_from);
    if (filters.value.date_to) params.set('date_to', filters.value.date_to);
    
    window.location.href = `/ps-lab-test?${params.toString()}`;
};

const clearFilters = () => {
    filters.value = {
        search: '',
        per_page: 10,
        lab_type: '',
        date_from: '',
        date_to: '',
    };
    applyFilters();
};

// ✅ Computed properties for filter summary
const hasActiveFilters = computed(() => {
    return filters.value.lab_type || 
           filters.value.date_from || 
           filters.value.date_to;
});

// Date picker helper functions
const formatDate = (date: string | null) => {
    if (!date) return '';
    return dayjs(date).format('YYYY-MM-DD');
};

const selectFromDate = (date: string) => {
    filters.value.date_from = date;
    showFromDatePicker.value = false;
};

const selectToDate = (date: string) => {
    filters.value.date_to = date;
    showToDatePicker.value = false;
};

const clearFromDate = () => {
    filters.value.date_from = '';
    showFromDatePicker.value = false;
};

const clearToDate = () => {
    filters.value.date_to = '';
    showToDatePicker.value = false;
};

// Generate date options for picker
const generateDateOptions = () => {
    const options = [];
    const today = dayjs();
    
    // Generate last 90 days
    for (let i = 90; i >= 0; i--) {
        const date = today.subtract(i, 'day');
        options.push({
            value: date.format('YYYY-MM-DD'),
            label: date.format('MMM DD, YYYY'),
            isToday: i === 0
        });
    }
    
    return options;
};

const dateOptions = computed(() => generateDateOptions());

// Lab type dropdown helper functions
const getSelectedLabTypeName = () => {
    if (!filters.value.lab_type) return '';
    return filters.value.lab_type === '1' ? 'Government Lab' : filters.value.lab_type === '2' ? 'Provita Lab' : '';
};

const selectLabType = (typeId: string) => {
    filters.value.lab_type = typeId;
    showLabTypeDropdown.value = false;
};

const clearLabTypeFilter = () => {
    filters.value.lab_type = '';
    showLabTypeDropdown.value = false;
};

const openExportDropdown = ref(false);

const exportPdf = (orientation: 'portrait' | 'landscape' = 'portrait') => {
    const url = route('reports.ps-lab-test.pdf', { ...filters.value, orientation });
    window.open(url, '_blank');
};

const exportExcel = () => {
    const url = route('reports.ps-lab-test.excel', { ...filters.value });
    window.open(url, '_blank');
};

const exportRowPdf = (id: number) => {
    const url = `/ps-lab-test/${id}/pdf`;
    window.open(url, '_blank');
};

// ✅ Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Parent Stock', href: '/ps-lab-test' },
    { title: 'Lab Tests', href: '/ps-lab-test' },
];
</script>

<template>
    <Head title="PS Lab Tests" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 m-3 bg-white dark:bg-gray-900 rounded-xl shadow-md">
            <!-- Header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-xl font-semibold text-gray-800 dark:text-white">Parent Stock Lab Test Information</h1>
                <div class="flex items-center gap-2">
                    <Link
                        v-if="can('ps-lab-test.create')"
                        href="/ps-lab-test/create"
                        class="group relative overflow-hidden rounded-md px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-gray-500"
                        style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);"
                    >
                        <span class="relative z-10 flex items-center gap-2">
                            <svg class="h-4 w-4 transition-transform duration-300 group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add
                        </span>
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-20 group-hover:translate-x-full"></div>
                    </Link>

                    <!-- Export Dropdown -->
                    <div class="pdf-dropdown relative">
                        <Button 
                            class="group relative overflow-hidden rounded-md px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-green-500" 
                            style="background: linear-gradient(135deg, #059669 0%, #047857 100%); box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);"
                            @click="openExportDropdown = !openExportDropdown"
                        >
                            <span class="relative z-10 flex items-center gap-2">
                                <svg class="h-4 w-4 transition-transform duration-300 group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export Report
                                <svg class="h-3 w-3 transition-transform duration-300" :class="{ 'rotate-180': openExportDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-20 group-hover:translate-x-full"></div>
                        </Button>
                        <div v-if="openExportDropdown" class="absolute right-0 z-20 mt-2 w-40 rounded border bg-white shadow-lg">
                            <button
                                @click="
                                    exportPdf('portrait');
                                    openExportDropdown = false;
                                "
                                class="block w-full px-4 py-2 text-left hover:bg-gray-100"
                            >
                                PDF
                            </button>
                            <button
                                @click="
                                    exportExcel();
                                    openExportDropdown = false;
                                "
                                class="block w-full px-4 py-2 text-left hover:bg-gray-100"
                            >
                                Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Custom Filter Section -->
            <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Filters</h3>
                    <button
                        @click="clearFilters"
                        class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                    >
                        Clear All
                    </button>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="PI No, Order No, Notes..."
                            class="block w-full px-3 py-2.5 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        />
                    </div>

                    <!-- Lab Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Lab Type</label>
                        <select
                            v-model="filters.lab_type"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">All Lab Types</option>
                            <option value="1">Government Lab</option>
                            <option value="2">Provita Lab</option>
                        </select>
                    </div>

                    <!-- Per Page -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Per Page</label>
                        <select
                            v-model="filters.per_page"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Range</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input
                                v-model="filters.date_from"
                                type="date"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                            <input
                                v-model="filters.date_to"
                                type="date"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                    </div>
                </div>

                <!-- Filter Actions -->
                <div class="mt-4 flex justify-end gap-2">
                    <button
                        @click="applyFilters"
                        class="rounded-md bg-black px-4 py-2 text-sm font-medium text-white shadow-lg hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200"
                    >
                        Apply Filters
                    </button>
                </div>
            </div>

            <!-- Table -->
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold">S/N</th>
                            <th class="px-6 py-3 text-left font-semibold">PI No</th>
                            <th class="px-6 py-3 text-left font-semibold">Order No</th>
                            <th class="px-6 py-3 text-left font-semibold">Receive Date</th>
                            <th class="px-6 py-3 text-left font-semibold">Lab Type</th>
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
                            v-for="(lab, index) in (props.labTests?.data ?? [])" 
                                :key="lab.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800 odd:bg-white even:bg-gray-100"
                            >
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">
                                {{ ((props.labTests?.meta?.current_page || 1) - 1) * (props.labTests?.meta?.per_page || 10) + index + 1 }}
                                </td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ lab.ps_receive?.pi_no ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ lab.ps_receive?.order_no ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">
                                    {{ lab.ps_receive?.created_at ? dayjs(lab.ps_receive.created_at).format('YYYY-MM-DD') : '-' }}
                                </td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">
                                    <span 
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                        :class="lab.lab_type === '1' 
                                            ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' 
                                            : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'"
                                    >
                                        {{ lab.lab_type === '1' ? 'Government Lab' : lab.lab_type === '2' ? 'Provita Lab' : 'Unknown' }}
                                    </span>
                                </td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ lab.lab_send_female_qty }}</td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ lab.lab_send_male_qty }}</td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100 font-medium">{{ lab.lab_send_total_qty }}</td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ lab.notes ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">
                                    <span 
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                        :class="lab.status === 1 
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                                            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'"
                                    >
                                        {{ lab.status === 1 ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            <td class="px-6 py-4 flex gap-4">
                                        <Link
                                            v-if="can('ps-lab-test.edit')"
                                            :href="`/ps-lab-test/${lab.id}/edit`"
                                    class="text-indigo-600 hover:underline font-medium"
                                        >
                                    Edit
                                        </Link>
                                        <button
                                            v-if="can('ps-lab-test.view')"
                                            @click="exportRowPdf(lab.id)"
                                    class="text-green-600 hover:underline font-medium"
                                        >
                                    Report
                                        </button>
                                </td>
                            </tr>
                        <tr v-if="(props.labTests?.data ?? []).length === 0">
                            <td colspan="11" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">
                                No lab tests found.
                            </td>
                            </tr>
                        </tbody>
                    </table>
            </div>

            <!-- Pagination -->
            <Pagination v-if="props.labTests?.meta" :meta="props.labTests.meta" class="mt-6" />
        </div>
    </AppLayout>
</template>

