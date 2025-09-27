<script setup lang="ts">
import listInfocard from '@/components/ListinfoCard.vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { useListFilters } from '@/composables/useListFilters';
import { useNotifier } from '@/composables/useNotifier';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { ArrowsUpFromLine, Calendar, FileText, Pencil } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

// ✅ Props
const props = defineProps<{
    batchAssigns?: {
        data: Array<{
            id: number;
            job_no?: string;
            flock_id: number;
            flock_name: string;
            shed_id: number;
            shed_name: string;
            company_id: number;
            company_name: string;
            batch_female_qty: number;
            batch_male_qty: number;
            stage: number;
            batch_total_qty: number;
            level: number;
            batch_no: string;
            batch_name: string;
            percentage?: number;
            created_at: string;
            flock?: { id: number; name: string; code: string } | null;
            shed?: { id: number; name: string } | null;
            company?: { id: number; name: string } | null;
            batch?: { id: number; name: string } | null;
        }>;
        meta: { current_page: number; last_page: number; per_page: number; total: number };
    };
    filters?: {
        search?: string;
        per_page?: number;
        company_id?: number;
        flock_id?: number;
        shed_id?: number;
        level?: number;
        date_from?: string;
        date_to?: string;
    };
    companies?: Array<{ id: number; name: string }>;
    flocks?: Array<{ id: number; code: string; name: string }>;
    sheds?: Array<{ id: number; name: string }>;
    levels?: Array<{ id: number; name: string }>;
    batches?: Array<{ id: number; name: string }>;
}>();

useListFilters({ routeName: '/batch-assign', filters: props.filters });
const { confirmDelete, confirmUpdate } = useNotifier();
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
const showCompanyDropdown = ref(false);
const showFlockDropdown = ref(false);
const showShedDropdown = ref(false);
const showLevelDropdown = ref(false);

// ✅ Close on outside click
const handleClick = (e: MouseEvent) => {
    if (
        !(e.target as HTMLElement).closest(
            '.action-dropdown, .action-btn, .pdf-dropdown, .pdf-button, .date-picker-container, .company-dropdown, .flock-dropdown, .shed-dropdown, .level-dropdown',
        )
    ) {
        closeDropdown();
        openExportDropdown.value = false;
        showFromDatePicker.value = false;
        showToDatePicker.value = false;
        showCompanyDropdown.value = false;
        showFlockDropdown.value = false;
        showShedDropdown.value = false;
        showLevelDropdown.value = false;
    }
};
onMounted(() => document.addEventListener('click', handleClick));
onBeforeUnmount(() => document.removeEventListener('click', handleClick));

// ✅ Delete action
const deleteBatchAssign = (id: number) => {
    confirmDelete({
        url: `/batch-assign/${id}`,
        text: 'This will permanently delete the batch assignment.',
        successMessage: 'Batch assignment deleted.',
    });
};

const transferStage = (item) => {
    const stageNames = { 1: 'Growing', 2: 'Production' };
    const nextStage = stageNames[item.stage] || null;

    if (!nextStage) {
        return;
    }

    confirmUpdate({
        url: `/batch-assign/${item.id}/next-stage`,
        title: 'Are you sure?',
        text: `You want to move ${nextStage} stage?`,
        confirmButtonText: `Yes, Move to ${nextStage}`,
        successMessage: `Stage updated to ${nextStage}!`,
    });
};

// ✅ Export filters
const filters = ref({
    search: props.filters?.search ?? '',
    per_page: props.filters?.per_page ?? 10,
    company_id: props.filters?.company_id ?? '',
    flock_id: props.filters?.flock_id ?? '',
    shed_id: props.filters?.shed_id ?? '',
    level: props.filters?.level ?? '',
    date_from: props.filters?.date_from ?? '',
    date_to: props.filters?.date_to ?? '',
});

// ✅ Filter methods
const applyFilters = () => {
    const params = new URLSearchParams();

    if (filters.value.search) params.set('search', filters.value.search);
    if (filters.value.per_page) params.set('per_page', filters.value.per_page.toString());
    if (filters.value.company_id) params.set('company_id', filters.value.company_id.toString());
    if (filters.value.flock_id) params.set('flock_id', filters.value.flock_id.toString());
    if (filters.value.shed_id) params.set('shed_id', filters.value.shed_id.toString());
    if (filters.value.level) params.set('level', filters.value.level.toString());
    if (filters.value.date_from) params.set('date_from', filters.value.date_from);
    if (filters.value.date_to) params.set('date_to', filters.value.date_to);

    window.location.href = `/batch-assign?${params.toString()}`;
};

const clearFilters = () => {
    filters.value = {
        search: '',
        per_page: 10,
        company_id: '',
        flock_id: '',
        shed_id: '',
        level: '',
        date_from: '',
        date_to: '',
    };
    applyFilters();
};

// ✅ Computed properties for filter summary
const hasActiveFilters = computed(() => {
    return (
        filters.value.company_id ||
        filters.value.flock_id ||
        filters.value.shed_id ||
        filters.value.level ||
        filters.value.date_from ||
        filters.value.date_to
    );
});

const getCompanyName = (companyId: string | number) => {
    const company = props.companies?.find((c) => c.id === Number(companyId));
    return company?.name || 'Unknown';
};

const getFlockName = (flockId: string | number) => {
    const flock = props.flocks?.find((f) => f.id === Number(flockId));
    return flock?.code || 'Unknown';
};

const getShedName = (shedId: string | number) => {
    const shed = props.sheds?.find((s) => s.id === Number(shedId));
    return shed?.name || 'Unknown';
};

const getLevelName = (levelId: string | number) => {
    const level = props.levels?.find((l) => l.id === Number(levelId));
    return level?.name || `Level ${levelId}`;
};

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
            isToday: i === 0,
        });
    }

    return options;
};

const dateOptions = computed(() => generateDateOptions());

// Company dropdown helper functions
const getSelectedCompanyName = () => {
    if (!filters.value.company_id) return '';
    const company = props.companies?.find((c) => c.id === Number(filters.value.company_id));
    return company?.name || '';
};

const selectCompany = (companyId: string | number) => {
    filters.value.company_id = companyId ? Number(companyId) : '';
    showCompanyDropdown.value = false;
};

const clearCompanyFilter = () => {
    filters.value.company_id = '';
    showCompanyDropdown.value = false;
};

// Flock dropdown helper functions
const getSelectedFlockName = () => {
    if (!filters.value.flock_id) return '';
    const flock = props.flocks?.find((f) => f.id === Number(filters.value.flock_id));
    return flock?.code || '';
};

const selectFlock = (flockId: string | number) => {
    filters.value.flock_id = flockId ? Number(flockId) : '';
    showFlockDropdown.value = false;
};

const clearFlockFilter = () => {
    filters.value.flock_id = '';
    showFlockDropdown.value = false;
};

// Shed dropdown helper functions
const getSelectedShedName = () => {
    if (!filters.value.shed_id) return '';
    const shed = props.sheds?.find((s) => s.id === Number(filters.value.shed_id));
    return shed?.name || '';
};

const selectShed = (shedId: string | number) => {
    filters.value.shed_id = shedId ? Number(shedId) : '';
    showShedDropdown.value = false;
};

const clearShedFilter = () => {
    filters.value.shed_id = '';
    showShedDropdown.value = false;
};

// Level dropdown helper functions
const getSelectedLevelName = () => {
    if (!filters.value.level) return '';
    const level = props.levels?.find((l) => l.id === Number(filters.value.level));
    return level?.name || '';
};

const selectLevel = (levelId: string | number) => {
    filters.value.level = levelId ? Number(levelId) : '';
    showLevelDropdown.value = false;
};

const clearLevelFilter = () => {
    filters.value.level = '';
    showLevelDropdown.value = false;
};

const openExportDropdown = ref(false);

const exportPdf = (orientation: 'portrait' | 'landscape' = 'portrait') => {
    const url = route('reports.batch-assign.pdf', { ...filters.value, orientation });
    window.open(url, '_blank');
};

const exportExcel = () => {
    const url = route('reports.batch-assign.excel', { ...filters.value });
    window.open(url, '_blank');
};

const exportRowPdf = (id: number) => {
    const url = `/batch-assign/${id}/pdf`;
    window.open(url, '_blank');
};

// ✅ Dynamic data for cards based on selected Batch Assignment
const selectedBatchAssign = ref<number | null>(null);
const cardData = computed(() => {
    if (!selectedBatchAssign.value || !props.batchAssigns?.data) return [];

    const selectedItem = props.batchAssigns.data.find((item) => item.id === selectedBatchAssign.value);
    if (!selectedItem) return [];

    return [
        {
            title: 'Total Chicks',
            value: selectedItem.batch_total_qty,
            title1: 'Male',
            value1: selectedItem.batch_male_qty,
            title2: 'Female',
            value2: selectedItem.batch_female_qty,
        },
        {
            title: 'Company',
            value: selectedItem.company?.name || selectedItem.company_name || 'N/A',
            title1: '',
            value1: '',
            title2: '',
            value2: '',
        },
        {
            title: 'Flock',
            value: selectedItem.flock?.name || selectedItem.flock_name || 'N/A',
            title1: '',
            value1: '',
            title2: '',
            value2: '',
        },
        {
            title: 'Shed',
            value: selectedItem.shed?.name || selectedItem.shed_name || 'N/A',
            title1: '',
            value1: '',
            title2: '',
            value2: '',
        },
        {
            title: 'Level',
            value: getLevelName(selectedItem.level) || 'N/A',
            title1: '',
            value1: '',
            title2: '',
            value2: '',
        },
        {
            title: 'Batch Name',
            value: selectedItem.batch?.name || selectedItem.batch_name || 'N/A',
            title1: '',
            value1: '',
            title2: '',
            value2: '',
        },
    ];
});

// ✅ Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Shed', href: '/batch-assign' },
    { title: 'Batch Assign', href: '/batch-assign' },
];
</script>

<template>
    <Head title="Batch Assignments" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Select Box -->
        <div class="m-5 w-full max-w-sm">
            <select
                v-model="selectedBatchAssign"
                class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
            >
                <option :value="null" disabled>Select Batch Assignment Record</option>
                <option v-for="item in props.batchAssigns?.data ?? []" :key="item.id" :value="item.id">
                    {{ item.flock_name }} - {{ item.shed_name }} - {{ getLevelName(item.level) }} : {{ dayjs(item.created_at).format('YYYY-MM-DD') }}
                </option>
            </select>
        </div>

        <listInfocard :cards="cardData" />

        <div class="m-3 rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
            <!-- Header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-xl font-semibold text-gray-800 dark:text-white">Batch Assignment Information</h1>
                <div class="flex items-center gap-2">
                    <Link
                        v-if="can('batch-assign.create')"
                        href="/batch-assign/create"
                        class="group relative overflow-hidden rounded-md px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl focus:ring-2 focus:ring-gray-500 focus:outline-none"
                        style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3)"
                    >
                        <span class="relative z-10 flex items-center gap-2">
                            <svg
                                class="h-4 w-4 transition-transform duration-300 group-hover:rotate-12"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Batch Assignment
                        </span>
                        <div
                            class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:translate-x-full group-hover:opacity-20"
                        ></div>
                    </Link>

                    <!-- Export Dropdown -->
                    <div class="pdf-dropdown relative">
                        <Button
                            class="group relative overflow-hidden rounded-md px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl focus:ring-2 focus:ring-green-500 focus:outline-none"
                            style="background: linear-gradient(135deg, #059669 0%, #047857 100%); box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3)"
                            @click="openExportDropdown = !openExportDropdown"
                        >
                            <span class="relative z-10 flex items-center gap-2">
                                <svg
                                    class="h-4 w-4 transition-transform duration-300 group-hover:rotate-12"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                    ></path>
                                </svg>
                                Export Report
                                <svg
                                    class="h-3 w-3 transition-transform duration-300"
                                    :class="{ 'rotate-180': openExportDropdown }"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </span>
                            <div
                                class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:translate-x-full group-hover:opacity-20"
                            ></div>
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
                    <button @click="clearFilters" class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200">
                        Clear All
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
                    <!-- Search -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                        <div class="relative">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                    ></path>
                                </svg>
                            </div>
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Flock, Shed, Level..."
                                class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 pr-4 pl-10 text-sm text-gray-900 shadow-sm transition-all duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 dark:focus:border-blue-500 dark:focus:ring-blue-500"
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

                    <!-- Company Filter -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                        <div class="company-dropdown relative">
                            <button
                                @click="showCompanyDropdown = !showCompanyDropdown"
                                type="button"
                                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm transition-all duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <span class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                                        ></path>
                                    </svg>
                                    {{ getSelectedCompanyName() || 'All Companies' }}
                                </span>
                                <svg
                                    class="h-4 w-4 text-gray-400 transition-transform duration-200"
                                    :class="{ 'rotate-180': showCompanyDropdown }"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Company Dropdown -->
                            <div
                                v-if="showCompanyDropdown"
                                class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700"
                            >
                                <div class="p-2">
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Select Company</span>
                                        <button @click="clearCompanyFilter" class="text-xs text-red-600 hover:text-red-800 dark:text-red-400">
                                            Clear
                                        </button>
                                    </div>
                                    <div class="max-h-48 space-y-1 overflow-y-auto">
                                        <button
                                            @click="selectCompany('')"
                                            class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                            :class="{ 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': !filters.company_id }"
                                        >
                                            <span>All Companies</span>
                                        </button>
                                        <button
                                            v-for="company in props.companies ?? []"
                                            :key="company.id"
                                            @click="selectCompany(company.id)"
                                            class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                            :class="{
                                                'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': filters.company_id == company.id,
                                            }"
                                        >
                                            <span>{{ company.name }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Flock Filter -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Flock</label>
                        <div class="flock-dropdown relative">
                            <button
                                @click="showFlockDropdown = !showFlockDropdown"
                                type="button"
                                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm transition-all duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <span class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                                        ></path>
                                    </svg>
                                    {{ getSelectedFlockName() || 'All Flocks' }}
                                </span>
                                <svg
                                    class="h-4 w-4 text-gray-400 transition-transform duration-200"
                                    :class="{ 'rotate-180': showFlockDropdown }"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Flock Dropdown -->
                            <div
                                v-if="showFlockDropdown"
                                class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700"
                            >
                                <div class="p-2">
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Select Flock</span>
                                        <button @click="clearFlockFilter" class="text-xs text-red-600 hover:text-red-800 dark:text-red-400">
                                            Clear
                                        </button>
                                    </div>
                                    <div class="max-h-48 space-y-1 overflow-y-auto">
                                        <button
                                            @click="selectFlock('')"
                                            class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                            :class="{ 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': !filters.flock_id }"
                                        >
                                            <span>All Flocks</span>
                                        </button>
                                        <button
                                            v-for="flock in props.flocks ?? []"
                                            :key="flock.id"
                                            @click="selectFlock(flock.id)"
                                            class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                            :class="{ 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': filters.flock_id == flock.id }"
                                        >
                                            <span>{{ flock.code }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shed Filter -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Shed</label>
                        <div class="shed-dropdown relative">
                            <button
                                @click="showShedDropdown = !showShedDropdown"
                                type="button"
                                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm transition-all duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <span class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2V5a2 2 0 012-2h14a2 2 0 012 2v2"
                                        ></path>
                                    </svg>
                                    {{ getSelectedShedName() || 'All Sheds' }}
                                </span>
                                <svg
                                    class="h-4 w-4 text-gray-400 transition-transform duration-200"
                                    :class="{ 'rotate-180': showShedDropdown }"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Shed Dropdown -->
                            <div
                                v-if="showShedDropdown"
                                class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700"
                            >
                                <div class="p-2">
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Select Shed</span>
                                        <button @click="clearShedFilter" class="text-xs text-red-600 hover:text-red-800 dark:text-red-400">
                                            Clear
                                        </button>
                                    </div>
                                    <div class="max-h-48 space-y-1 overflow-y-auto">
                                        <button
                                            @click="selectShed('')"
                                            class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                            :class="{ 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': !filters.shed_id }"
                                        >
                                            <span>All Sheds</span>
                                        </button>
                                        <button
                                            v-for="shed in props.sheds ?? []"
                                            :key="shed.id"
                                            @click="selectShed(shed.id)"
                                            class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                            :class="{ 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': filters.shed_id == shed.id }"
                                        >
                                            <span>{{ shed.name }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Level Filter -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Level</label>
                        <div class="level-dropdown relative">
                            <button
                                @click="showLevelDropdown = !showLevelDropdown"
                                type="button"
                                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm transition-all duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            >
                                <span class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h4a1 1 0 011 1v2a1 1 0 01-1 1H3a1 1 0 01-1-1V5a1 1 0 011-1h4zM3 8v12a2 2 0 002 2h14a2 2 0 002-2V8"
                                        ></path>
                                    </svg>
                                    {{ getSelectedLevelName() || 'All Levels' }}
                                </span>
                                <svg
                                    class="h-4 w-4 text-gray-400 transition-transform duration-200"
                                    :class="{ 'rotate-180': showLevelDropdown }"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Level Dropdown -->
                            <div
                                v-if="showLevelDropdown"
                                class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700"
                            >
                                <div class="p-2">
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Select Level</span>
                                        <button @click="clearLevelFilter" class="text-xs text-red-600 hover:text-red-800 dark:text-red-400">
                                            Clear
                                        </button>
                                    </div>
                                    <div class="max-h-48 space-y-1 overflow-y-auto">
                                        <button
                                            @click="selectLevel('')"
                                            class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                            :class="{ 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': !filters.level }"
                                        >
                                            <span>All Levels</span>
                                        </button>
                                        <button
                                            v-for="level in props.levels ?? []"
                                            :key="level.id"
                                            @click="selectLevel(level.id)"
                                            class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                            :class="{ 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': filters.level == level.id }"
                                        >
                                            <span>{{ level.name }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                                            <button @click="clearFromDate" class="text-xs text-red-600 hover:text-red-800 dark:text-red-400">
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
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200':
                                                        filters.date_from === option.value,
                                                    'font-semibold text-green-600 dark:text-green-400': option.isToday,
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
                                            <button @click="clearToDate" class="text-xs text-red-600 hover:text-red-800 dark:text-red-400">
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
                                                    'font-semibold text-green-600 dark:text-green-400': option.isToday,
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
                        v-if="filters.company_id"
                        class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                    >
                        Company: {{ getCompanyName(filters.company_id) }}
                    </span>
                    <span
                        v-if="filters.flock_id"
                        class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200"
                    >
                        Flock: {{ getFlockName(filters.flock_id) }}
                    </span>
                    <span
                        v-if="filters.shed_id"
                        class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-800 dark:bg-orange-900 dark:text-orange-200"
                    >
                        Shed: {{ getShedName(filters.shed_id) }}
                    </span>
                    <span
                        v-if="filters.level"
                        class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-900 dark:text-purple-200"
                    >
                        Level: {{ getLevelName(filters.level) }}
                    </span>
                    <span
                        v-if="filters.date_from || filters.date_to"
                        class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200"
                    >
                        Date: {{ filters.date_from || 'Start' }} to {{ filters.date_to || 'End' }}
                    </span>
                </div>

                <!-- Filter Actions -->
                <div class="mt-4 flex justify-end gap-2">
                    <button
                        @click="applyFilters"
                        class="rounded-md bg-black px-4 py-2 text-sm font-medium text-white shadow-lg transition-all duration-200 hover:bg-gray-800 focus:ring-2 focus:ring-gray-500 focus:outline-none"
                        style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3)"
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
                    <span class="text-xs">{{ props.batchAssigns?.data?.length ?? 0 }} records</span>
                </div>

                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700" style="min-width: 1300px">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr class="text-left text-gray-600 dark:text-gray-300">
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 60px">S/N</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 150px">Flock No</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 150px">Shed Name</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 150px">Company</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 100px">Level</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 120px">Batch</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 100px">Male Qty</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 100px">Female Qty</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 120px">Total Qty</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 120px">Period</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 120px">Created Date</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 100px">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                            <tr
                                v-for="(item, index) in props.batchAssigns?.data ?? []"
                                :key="item.id"
                                class="odd:bg-white even:bg-gray-50 hover:bg-gray-100 dark:odd:bg-gray-900 dark:even:bg-gray-800"
                            >
                                <td class="px-4 py-3 text-center font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">
                                    {{ ((props.batchAssigns?.meta?.current_page || 1) - 1) * (props.batchAssigns?.meta?.per_page || 10) + index + 1 }}
                                </td>
                                <td class="px-4 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-white">
                                    {{ item.flock?.name || item.flock_name }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span
                                        class="inline-flex rounded-full bg-orange-100 px-2 py-1 text-xs font-medium text-orange-800 dark:bg-orange-900 dark:text-orange-200"
                                    >
                                        {{ item.shed?.name || item.shed_name }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ item.company?.name || item.company_name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span
                                        class="inline-flex rounded-full bg-purple-100 px-2 py-1 text-xs font-medium text-purple-800 dark:bg-purple-900 dark:text-purple-200"
                                    >
                                        {{ getLevelName(item.level) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ item.batch?.name || item.batch_name }}</td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">{{ item.batch_male_qty }}</td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">{{ item.batch_female_qty }}</td>
                                <td class="px-4 py-3 text-center font-medium whitespace-nowrap">{{ item.batch_total_qty }}</td>
                                <td class="px-4 py-3 text-center font-medium whitespace-nowrap">
                                    {{ item.stage == 1 ? 'Brooding' : item.stage == 2 ? 'Growing' : item.stage == 3 ? 'Production' : '' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ dayjs(item.created_at).format('MMM DD, YYYY') }}</td>
                                <td class="relative px-4 py-3">
                                    <Button
                                        size="sm"
                                        class="action-btn bg-gray-500 text-white hover:bg-gray-600"
                                        @click.stop="toggleDropdown(item.id)"
                                    >
                                        Actions ▼
                                    </Button>

                                    <!-- Action Popup Overlay -->
                                    <div
                                        v-if="openDropdownId === item.id"
                                        class="fixed inset-0 z-50 flex items-center justify-center"
                                        @click.stop="closeDropdown"
                                    >
                                        <!-- Backdrop -->
                                        <div class="absolute inset-0 bg-black/20 backdrop-blur-sm"></div>

                                        <!-- Popup Content -->
                                        <div
                                            class="relative z-10 w-48 rounded-lg border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-800"
                                            @click.stop
                                        >
                                            <!-- Header -->
                                            <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Actions</h3>
                                            </div>

                                            <!-- Actions List -->
                                            <div class="py-2">
                                                <!-- View -->
                                                <Link
                                                    :href="route('batch-assign.show', item.id)"
                                                    class="flex items-center gap-2 px-4 py-2 text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700"
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                                                        ></path>
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                                                        ></path>
                                                    </svg>
                                                    <span>View</span>
                                                </Link>

                                                <!-- Edit -->
                                                <Link
                                                    v-if="can('batch-assign.edit')"
                                                    :href="`/batch-assign/${item.id}/edit`"
                                                    class="flex items-center gap-2 px-4 py-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                                >
                                                    <Pencil class="h-4 w-4" />
                                                    <span>Edit</span>
                                                </Link>

                                                <!-- Edit -->
                                                <button
                                                    v-if="can('batch-assign.edit') && item.stage == 1"
                                                    @click="transferStage(item)"
                                                    class="flex w-full items-center gap-2 px-4 py-2 text-purple-600 hover:bg-purple-50 dark:hover:bg-purple-900/20"
                                                >
                                                    <ArrowsUpFromLine class="h-4 w-4" />
                                                    <span>Move to Growing</span>
                                                </button>
                                                <!-- Report -->
                                                <button
                                                    @click="exportRowPdf(item.id)"
                                                    class="flex w-full items-center gap-2 px-4 py-2 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20"
                                                >
                                                    <FileText class="h-4 w-4" />
                                                    <span>Report</span>
                                                </button>

                                                <!-- Transfer -->
                                                <Link
                                                    v-if="can('batch-assign.edit') && item.stage == 2"
                                                    :href="`/bird-transfer/create/${item.id}`"
                                                    class="flex items-center gap-2 px-4 py-2 text-yellow-600 hover:bg-yellow-50 dark:hover:bg-yellow-900/20"
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"
                                                        ></path>
                                                    </svg>
                                                    <span>Batch Transfer</span>
                                                </Link>

                                                <!-- Delete -->
                                                <!-- <button
                                                    @click="deleteBatchAssign(item.id)"
                                                    class="flex w-full items-center gap-2 px-4 py-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20"
                                                >
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path
                                                            stroke-linecap="round"
                                                            stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                                                        ></path>
                                                    </svg>
                                                    <span>Delete</span>
                                                </button> -->
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="(props.batchAssigns?.data ?? []).length === 0">
                                <td colspan="11" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">No batch assignments found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination v-if="props.batchAssigns?.meta && props.batchAssigns.meta.current_page" :meta="props.batchAssigns.meta" class="mt-6" />
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
