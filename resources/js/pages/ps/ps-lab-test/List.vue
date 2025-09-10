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
        <div class="m-3 rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
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
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="PI No, Order No, Notes..."
                                class="block w-full pl-10 pr-4 py-2.5 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 transition-all duration-200"
                            />
                            <button
                                v-if="filters.search"
                                @click="filters.search = ''"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Lab Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Lab Type</label>
                        <div class="lab-type-dropdown relative">
                            <button
                                @click="showLabTypeDropdown = !showLabTypeDropdown"
                                type="button"
                                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white transition-all duration-200"
                            >
                                <span class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 7.172V5L8 4z"></path>
                                    </svg>
                                    {{ getSelectedLabTypeName() || 'All Lab Types' }}
                                </span>
                                <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showLabTypeDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <!-- Lab Type Dropdown -->
                            <div
                                v-if="showLabTypeDropdown"
                                class="absolute z-20 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700"
                            >
                                <div class="p-2">
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Select Lab Type</span>
                                        <button
                                            @click="clearLabTypeFilter"
                                            class="text-xs text-red-600 hover:text-red-800 dark:text-red-400"
                                        >
                                            Clear
                                        </button>
                                    </div>
                                    <div class="space-y-1">
                                        <button
                                            @click="selectLabType('')"
                                            class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                            :class="{ 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': !filters.lab_type }"
                                        >
                                            <span>All Lab Types</span>
                                        </button>
                                        <button
                                            @click="selectLabType('1')"
                                            class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                            :class="{ 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': filters.lab_type === '1' }"
                                        >
                                            <span class="inline-flex items-center gap-2">
                                                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                                Government Lab
                                            </span>
                                        </button>
                                        <button
                                            @click="selectLabType('2')"
                                            class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                            :class="{ 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': filters.lab_type === '2' }"
                                        >
                                            <span class="inline-flex items-center gap-2">
                                                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                                Provita Lab
                                            </span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Per Page -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Per Page</label>
                        <select
                            v-model="filters.per_page"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white transition-all duration-200"
                        >
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date Range</label>
                        <div class="mt-1 grid grid-cols-2 gap-2">
                            <!-- From Date Picker -->
                            <div class="date-picker-container relative">
                                <button
                                    @click="showFromDatePicker = !showFromDatePicker"
                                    type="button"
                                    class="flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <span class="flex items-center gap-2">
                                        <Calendar class="h-4 w-4 text-gray-400" />
                                        {{ filters.date_from ? formatDate(filters.date_from) : 'From Date' }}
                                    </span>
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                
                                <!-- From Date Dropdown -->
                                <div
                                    v-if="showFromDatePicker"
                                    class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700"
                                >
                                    <div class="p-2">
                                        <div class="mb-2 flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Select From Date</span>
                                            <button
                                                @click="clearFromDate"
                                                class="text-xs text-red-600 hover:text-red-800 dark:text-red-400"
                                            >
                                                Clear
                                            </button>
                                        </div>
                                        <div class="max-h-48 space-y-1 overflow-y-auto">
                                            <button
                                                v-for="option in dateOptions"
                                                :key="option.value"
                                                @click="selectFromDate(option.value)"
                                                class="flex w-full items-center justify-between rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                                :class="{
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': filters.date_from === option.value,
                                                    'font-semibold text-green-600 dark:text-green-400': option.isToday
                                                }"
                                            >
                                                <span>{{ option.label }}</span>
                                                <span v-if="option.isToday" class="text-xs text-green-600 dark:text-green-400">Today</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- To Date Picker -->
                            <div class="date-picker-container relative">
                                <button
                                    @click="showToDatePicker = !showToDatePicker"
                                    type="button"
                                    class="flex w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                >
                                    <span class="flex items-center gap-2">
                                        <Calendar class="h-4 w-4 text-gray-400" />
                                        {{ filters.date_to ? formatDate(filters.date_to) : 'To Date' }}
                                    </span>
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                
                                <!-- To Date Dropdown -->
                                <div
                                    v-if="showToDatePicker"
                                    class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700"
                                >
                                    <div class="p-2">
                                        <div class="mb-2 flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Select To Date</span>
                                            <button
                                                @click="clearToDate"
                                                class="text-xs text-red-600 hover:text-red-800 dark:text-red-400"
                                            >
                                                Clear
                                            </button>
                                        </div>
                                        <div class="max-h-48 space-y-1 overflow-y-auto">
                                            <button
                                                v-for="option in dateOptions"
                                                :key="option.value"
                                                @click="selectToDate(option.value)"
                                                class="flex w-full items-center justify-between rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                                :class="{
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': filters.date_to === option.value,
                                                    'font-semibold text-green-600 dark:text-green-400': option.isToday
                                                }"
                                            >
                                                <span>{{ option.label }}</span>
                                                <span v-if="option.isToday" class="text-xs text-green-600 dark:text-green-400">Today</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Filters Summary -->
                <div v-if="hasActiveFilters" class="mt-4 flex flex-wrap gap-2">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active Filters:</span>
                    <span 
                        v-if="filters.lab_type" 
                        class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                    >
                        Lab Type: {{ filters.lab_type === '1' ? 'Government Lab' : 'Provita Lab' }}
                    </span>
                    <span 
                        v-if="filters.date_from || filters.date_to" 
                        class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-900 dark:text-purple-200"
                    >
                        Date: {{ filters.date_from || 'Start' }} to {{ filters.date_to || 'End' }}
                    </span>
                </div>

                <!-- Filter Actions -->
                <div class="mt-4 flex justify-end gap-2">
                    <button
                        @click="applyFilters"
                        class="rounded-md bg-black px-4 py-2 text-sm font-medium text-white shadow-lg hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200"
                        style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);"
                    >
                        Apply Filters
                    </button>
                </div>
            </div>

            <!-- Responsive Table -->
            <div class="relative">
                <!-- Scroll indicator -->
                <div class="mb-2 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                    <span class="flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                        </svg>
                        Scroll horizontally to see all columns
                    </span>
                    <span class="text-xs">{{ props.labTests?.data?.length ?? 0 }} records</span>
                </div>
                
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700" style="min-width: 1000px;">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr class="text-left text-gray-600 dark:text-gray-300">
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 60px;">S/N</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 120px;">PI No</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 120px;">Order No</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 120px;">Receive Date</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 120px;">Lab Type</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 100px;">Female Qty</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 100px;">Male Qty</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 100px;">Total Qty</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 150px;">Notes</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 80px;">Status</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                            <tr
                                v-for="(lab, index) in props.labTests?.data ?? []"
                                :key="lab.id"
                                class="odd:bg-white even:bg-gray-50 hover:bg-gray-100 dark:odd:bg-gray-900 dark:even:bg-gray-800"
                            >
                                <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400 whitespace-nowrap font-medium">
                                    {{ ((props.labTests?.meta?.current_page || 1) - 1) * (props.labTests?.meta?.per_page || 10) + index + 1 }}
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap">{{ lab.ps_receive?.pi_no ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ lab.ps_receive?.order_no ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    {{ lab.ps_receive?.created_at ? dayjs(lab.ps_receive.created_at).format('YYYY-MM-DD') : '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span 
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                        :class="lab.lab_type === '1' 
                                            ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' 
                                            : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'"
                                    >
                                        {{ lab.lab_type === '1' ? 'Government Lab' : lab.lab_type === '2' ? 'Provita Lab' : 'Unknown' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ lab.lab_send_female_qty }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ lab.lab_send_male_qty }}</td>
                                <td class="px-4 py-3 whitespace-nowrap font-medium">{{ lab.lab_send_total_qty }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ lab.notes ?? '-' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span 
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                        :class="lab.status === 1 
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                                            : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'"
                                    >
                                        {{ lab.status === 1 ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="relative px-4 py-3">
                                    <Button size="sm" class="action-btn bg-gray-500 text-white hover:bg-gray-600" @click.stop="toggleDropdown(lab.id)">
                                        Actions ▼
                                    </Button>

                                    <div
                                        v-if="openDropdownId === lab.id"
                                        class="action-dropdown absolute top-full left-0 z-20 mt-1 flex w-40 flex-col rounded border bg-white shadow-md"
                                        @click.stop
                                    >
                                        <!-- Edit -->
                                        <Link
                                            v-if="can('ps-lab-test.edit')"
                                            :href="`/ps-lab-test/${lab.id}/edit`"
                                            class="flex items-center gap-2 px-4 py-2 text-blue-600 hover:bg-blue-50"
                                        >
                                            <Pencil class="h-4 w-4" />
                                            <span>Edit</span>
                                        </Link>

                                        <!-- Report -->
                                        <button
                                            v-if="can('ps-lab-test.view')"
                                            @click="exportRowPdf(lab.id)"
                                            class="flex w-full items-center gap-2 px-4 py-2 text-green-600 hover:bg-green-50"
                                        >
                                            <FileText class="h-4 w-4" />
                                            <span>Report</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="(props.labTests?.data ?? []).length === 0">
                                <td colspan="11" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">No lab tests found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination 
                v-if="props.labTests?.meta && props.labTests.meta.current_page" 
                :meta="props.labTests.meta" 
                class="mt-6" 
            />
        </div>
    </AppLayout>
</template>

<style scoped>
/* Custom scrollbar for table */
.overflow-x-auto::-webkit-scrollbar {
    height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Dark mode scrollbar */
.dark .overflow-x-auto::-webkit-scrollbar-track {
    background: #374151;
}

.dark .overflow-x-auto::-webkit-scrollbar-thumb {
    background: #6b7280;
}

.dark .overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Smooth scrolling */
.overflow-x-auto {
    scroll-behavior: smooth;
}
</style>
