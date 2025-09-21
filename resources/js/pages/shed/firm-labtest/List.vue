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
import { FlaskConical, Pencil, Calendar, Building2, Package, Users, Download, FileText } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

// ✅ Props
const props = defineProps<{
    firmLabTests?: {
        data: Array<{
            id: number;
            firm_lab_type: number;
            firm_lab_send_female_qty: number;
            firm_lab_send_male_qty: number;
            firm_lab_send_total_qty: number;
            firm_lab_receive_female_qty: number;
            firm_lab_receive_male_qty: number;
            firm_lab_receive_total_qty: number;
            note?: string;
            remarks?: string;
            status: number;
            created_at: string;
            batch_assign?: {
                id: number;
                transaction_no: string;
                batch: { id: number; name: string };
                company: { id: number; name: string };
                project: { id: number; name: string };
                shed: { id: number; name: string };
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
        company?: string;
        project?: string;
        shed?: string;
    };
}>();

useListFilters({ routeName: '/firm-lab-tests', filters: props.filters });
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
const showCompanyDropdown = ref(false);
const showProjectDropdown = ref(false);
const showShedDropdown = ref(false);

// ✅ Close on outside click
const handleClick = (e: MouseEvent) => {
    if (!(e.target as HTMLElement).closest('.action-dropdown, .action-btn, .pdf-dropdown, .pdf-button, .date-picker-container, .lab-type-dropdown, .company-dropdown, .project-dropdown, .shed-dropdown')) {
        closeDropdown();
        openExportDropdown.value = false;
        showFromDatePicker.value = false;
        showToDatePicker.value = false;
        showLabTypeDropdown.value = false;
        showCompanyDropdown.value = false;
        showProjectDropdown.value = false;
        showShedDropdown.value = false;
    }
};
onMounted(() => document.addEventListener('click', handleClick));
onBeforeUnmount(() => document.removeEventListener('click', handleClick));

// ✅ Delete action
const deleteFirmLabTest = (id: number) => {
    confirmDelete({
        url: `/firm-lab-tests/${id}`,
        text: 'This will permanently delete the firm lab test record.',
        successMessage: 'Firm Lab Test deleted successfully.',
    });
};

// ✅ Export filters
const filters = ref({
    search: props.filters?.search ?? '',
    per_page: props.filters?.per_page ?? 10,
    lab_type: props.filters?.lab_type ?? '',
    date_from: props.filters?.date_from ?? '',
    date_to: props.filters?.date_to ?? '',
    company: props.filters?.company ?? '',
    project: props.filters?.project ?? '',
    shed: props.filters?.shed ?? '',
});

// ✅ Filter methods
const applyFilters = () => {
    const params = new URLSearchParams();
    
    if (filters.value.search) params.set('search', filters.value.search);
    if (filters.value.per_page) params.set('per_page', filters.value.per_page.toString());
    if (filters.value.lab_type) params.set('lab_type', filters.value.lab_type);
    if (filters.value.date_from) params.set('date_from', filters.value.date_from);
    if (filters.value.date_to) params.set('date_to', filters.value.date_to);
    if (filters.value.company) params.set('company', filters.value.company);
    if (filters.value.project) params.set('project', filters.value.project);
    if (filters.value.shed) params.set('shed', filters.value.shed);
    
    window.location.href = `/firm-lab-tests?${params.toString()}`;
};

const clearFilters = () => {
    filters.value = {
        search: '',
        per_page: 10,
        lab_type: '',
        date_from: '',
        date_to: '',
        company: '',
        project: '',
        shed: '',
    };
    applyFilters();
};

// ✅ Computed properties for filter summary
const hasActiveFilters = computed(() => {
    return filters.value.lab_type || 
           filters.value.date_from || 
           filters.value.date_to ||
           filters.value.company ||
           filters.value.project ||
           filters.value.shed;
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
    return filters.value.lab_type === '1' ? 'Government Lab' : filters.value.lab_type === '2' ? 'Firm Lab' : '';
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
    const url = route('reports.firm-lab-tests.pdf', { ...filters.value, orientation });
    window.open(url, '_blank');
};

const exportExcel = () => {
    const url = route('reports.firm-lab-tests.excel', { ...filters.value });
    window.open(url, '_blank');
};

const exportRowPdf = (id: number) => {
    const url = `/firm-lab-tests/${id}/pdf`;
    window.open(url, '_blank');
};

// ✅ Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Shed', href: '/firm-lab-tests' },
    { title: 'Firm Lab Tests', href: '/firm-lab-tests' },
];
</script>

<template>
    <Head title="Firm Lab Tests" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 m-3 bg-white dark:bg-gray-900 rounded-xl shadow-md">
            <!-- Header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-xl font-semibold text-gray-800 dark:text-white">Firm Lab Test Information</h1>
                <div class="flex items-center gap-2">
                    <Link
                        v-if="can('firm-lab-tests.create')"
                        href="/firm-lab-tests/create"
                        class="group relative overflow-hidden rounded-md px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-gray-500"
                        style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);"
                    >
                        <span class="relative z-10 flex items-center gap-2">
                            <FlaskConical class="h-4 w-4 transition-transform duration-300 group-hover:rotate-12" />
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
                                <Download class="h-4 w-4 transition-transform duration-300 group-hover:rotate-12" />
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
                            placeholder="Transaction No, Batch, Company..."
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
                            <option value="2">Firm Lab</option>
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
                            <th class="px-6 py-3 text-left font-semibold">Transaction No</th>
                            <th class="px-6 py-3 text-left font-semibold">Batch</th>
                            <th class="px-6 py-3 text-left font-semibold">Company</th>
                            <th class="px-6 py-3 text-left font-semibold">Project</th>
                            <th class="px-6 py-3 text-left font-semibold">Shed</th>
                            <th class="px-6 py-3 text-left font-semibold">Lab Type</th>
                            <th class="px-6 py-3 text-left font-semibold">Send Female</th>
                            <th class="px-6 py-3 text-left font-semibold">Send Male</th>
                            <th class="px-6 py-3 text-left font-semibold">Send Total</th>
                            <th class="px-6 py-3 text-left font-semibold">Receive Female</th>
                            <th class="px-6 py-3 text-left font-semibold">Receive Male</th>
                            <th class="px-6 py-3 text-left font-semibold">Receive Total</th>
                            <th class="px-6 py-3 text-left font-semibold">Created Date</th>
                            <th class="px-6 py-3 text-left font-semibold">Status</th>
                            <th class="px-6 py-3 text-left font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                        <tr
                            v-for="(lab, index) in (props.firmLabTests?.data ?? [])" 
                            :key="lab.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-800 odd:bg-white even:bg-gray-100"
                        >
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">
                                {{ ((props.firmLabTests?.meta?.current_page || 1) - 1) * (props.firmLabTests?.meta?.per_page || 10) + index + 1 }}
                            </td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ lab.batch_assign?.transaction_no ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ lab.batch_assign?.batch?.name ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ lab.batch_assign?.company?.name ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ lab.batch_assign?.project?.name ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ lab.batch_assign?.shed?.name ?? '-' }}</td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">
                                <span 
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                    :class="lab.firm_lab_type === 1 
                                        ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' 
                                        : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'"
                                >
                                    {{ lab.firm_lab_type === 1 ? 'Government Lab' : lab.firm_lab_type === 2 ? 'Firm Lab' : 'Unknown' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ lab.firm_lab_send_female_qty }}</td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ lab.firm_lab_send_male_qty }}</td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100 font-medium">{{ lab.firm_lab_send_total_qty }}</td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ lab.firm_lab_receive_female_qty }}</td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ lab.firm_lab_receive_male_qty }}</td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100 font-medium">{{ lab.firm_lab_receive_total_qty }}</td>
                            <td class="px-6 py-4 text-gray-800 dark:text-gray-100">
                                {{ lab.created_at ? dayjs(lab.created_at).format('YYYY-MM-DD') : '-' }}
                            </td>
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
                                    v-if="can('firm-lab-tests.edit')"
                                    :href="`/firm-lab-tests/${lab.id}/edit`"
                                    class="text-indigo-600 hover:underline font-medium"
                                >
                                    Edit
                                </Link>
                                <button
                                    v-if="can('firm-lab-tests.view')"
                                    @click="exportRowPdf(lab.id)"
                                    class="text-green-600 hover:underline font-medium"
                                >
                                    Report
                                </button>
                                <button
                                    v-if="can('firm-lab-tests.delete')"
                                    @click="deleteFirmLabTest(lab.id)"
                                    class="text-red-600 hover:underline font-medium"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                        <tr v-if="(props.firmLabTests?.data ?? []).length === 0">
                            <td colspan="15" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">
                                No firm lab tests found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <Pagination v-if="props.firmLabTests?.meta" :meta="props.firmLabTests.meta" class="mt-6" />
        </div>
    </AppLayout>
</template>
