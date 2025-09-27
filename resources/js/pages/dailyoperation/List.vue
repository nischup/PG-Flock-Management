<script setup lang="ts">
import listInfocard from '@/components/ListinfoCard.vue';
import Pagination from '@/components/Pagination.vue';
import { useListFilters } from '@/composables/useListFilters';
import { useNotifier } from '@/composables/useNotifier';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { Calendar, Eye, Pencil, Trash2 } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

// ✅ Props
const props = defineProps<{
    dailyOperations?: {
        data: Array<{
            id: number;
            operation_date: string;
            flock_name: string;
            shed_name: string;
            company_name: string;
            batch_name: string;
            job_no: string;
            transaction_no: string;
            male_mortality: number;
            female_mortality: number;
            total_mortality: number;
            feed_consumption: string;
            water_consumption: string;
            light_hour: number;
            egg_collection: number;
            created_by_name: string;
            created_at: string;
            status: number;
            flock?: { id: number; name: string } | null;
            shed?: { id: number; name: string } | null;
            company?: { id: number; name: string } | null;
        }>;
        meta: { current_page: number; last_page: number; per_page: number; total: number };
    };
    filters?: {
        search?: string;
        per_page?: number;
        company_id?: number;
        flock_id?: number;
        shed_id?: number;
        date_from?: string;
        date_to?: string;
    };
    stage: string;
    companies?: Array<{ id: number; name: string }>;
    flocks?: Array<{ id: number; name: string }>;
    sheds?: Array<{ id: number; name: string }>;
}>();

// Map stage → display titles
const stageTitles: Record<string, string> = {
    brooding: 'Brooding',
    growing: 'Growing',
    laying: 'Laying / Production',
};

const currentTitle = stageTitles[props.stage] ?? props.stage;

useListFilters({ routeName: `/daily-operation/stage/${props.stage}`, filters: props.filters });
const { confirmDelete } = useNotifier();
const { can } = usePermissions();

// Date picker states
const showFromDatePicker = ref(false);
const showToDatePicker = ref(false);

// Dropdown states
const showCompanyDropdown = ref(false);
const showFlockDropdown = ref(false);
const showShedDropdown = ref(false);

// ✅ Export filters
const filters = ref({
    search: props.filters?.search ?? '',
    per_page: props.filters?.per_page ?? 15,
    company_id: props.filters?.company_id ?? '',
    flock_id: props.filters?.flock_id ?? '',
    shed_id: props.filters?.shed_id ?? '',
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
    if (filters.value.date_from) params.set('date_from', filters.value.date_from);
    if (filters.value.date_to) params.set('date_to', filters.value.date_to);

    window.location.href = `/daily-operation/stage/${props.stage}?${params.toString()}`;
};

const clearFilters = () => {
    filters.value = {
        search: '',
        per_page: 15,
        company_id: '',
        flock_id: '',
        shed_id: '',
        date_from: '',
        date_to: '',
    };
    applyFilters();
};

// ✅ Computed properties for filter summary
const hasActiveFilters = computed(() => {
    return filters.value.company_id || filters.value.flock_id || filters.value.shed_id || filters.value.date_from || filters.value.date_to;
});

const getCompanyName = (companyId: string | number) => {
    const company = props.companies?.find((c) => c.id === Number(companyId));
    return company?.name || 'Unknown';
};

const getFlockName = (flockId: string | number) => {
    const flock = props.flocks?.find((f) => f.id === Number(flockId));
    return flock?.name || 'Unknown';
};

const getShedName = (shedId: string | number) => {
    const shed = props.sheds?.find((s) => s.id === Number(shedId));
    return shed?.name || 'Unknown';
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
    return flock?.name || '';
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

// Delete operation
const deleteOperation = (id: number) => {
    confirmDelete({
        url: `/daily-operation/${props.stage}/${id}`,
        text: 'This will permanently delete the daily operation.',
        successMessage: 'Daily operation deleted.',
    });
};

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Farm Operations', href: '/daily-operation' },
    { title: currentTitle, href: `/daily-operation/stage/${props.stage}` },
];

// Dropdown state
const openDropdownId = ref<number | null>(null);
const toggleDropdown = (id: number) => {
    openDropdownId.value = openDropdownId.value === id ? null : id;
};

// ✅ Close on outside click
const handleClick = (e: MouseEvent) => {
    if (
        !(e.target as HTMLElement).closest(
            '.action-dropdown, .action-btn, .date-picker-container, .company-dropdown, .flock-dropdown, .shed-dropdown',
        )
    ) {
        openDropdownId.value = null;
        showFromDatePicker.value = false;
        showToDatePicker.value = false;
        showCompanyDropdown.value = false;
        showFlockDropdown.value = false;
        showShedDropdown.value = false;
    }
};
onMounted(() => document.addEventListener('click', handleClick));
onBeforeUnmount(() => document.removeEventListener('click', handleClick));
</script>

<template>
    <Head :title="`Daily Operations - ${currentTitle}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-3 rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
            <!-- Header -->
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">{{ currentTitle }} Operations</h1>
                <Link
                    v-if="can(`${props.stage}.create`)"
                    :href="`/daily-operation/stage/${props.stage}/create`"
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
                        Add Operation
                    </span>
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:translate-x-full group-hover:opacity-20"
                    ></div>
                </Link>
            </div>

            <!-- Info Cards -->
            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                <listInfocard title="Total Operations" :value="props.dailyOperations?.meta?.total || 0" icon="Calendar" color="blue" />
                <listInfocard title="This Month" :value="props.dailyOperations?.data?.length || 0" icon="FileText" color="green" />
                <listInfocard
                    title="Total Mortality"
                    :value="props.dailyOperations?.data?.reduce((sum, op) => sum + (op.total_mortality || 0), 0) || 0"
                    icon="Trash2"
                    color="red"
                />
                <listInfocard
                    title="Total Eggs"
                    :value="props.dailyOperations?.data?.reduce((sum, op) => sum + (op.egg_collection || 0), 0) || 0"
                    icon="Eye"
                    color="purple"
                />
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
                                placeholder="Flock, Shed, Company..."
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
                                            <span>{{ flock.name }}</span>
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

                    <!-- Per Page -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Per Page</label>
                        <select
                            v-model="filters.per_page"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm transition-all duration-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:focus:border-blue-500 dark:focus:ring-blue-500"
                        >
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
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

            <!-- Table -->
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Company</th>
                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Project</th>
                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Flock</th>
                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Shed</th>
                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Batch</th>
                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Stage</th>
                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Age</th>
                            <th class="px-4 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                Male Mortality
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                Female Mortality
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                Total Mortality
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Feed</th>
                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Water</th>
                            <th class="px-4 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                Light (hrs)
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">Eggs</th>
                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                Created By
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                Submitted Date
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium tracking-wider text-gray-500 uppercase dark:text-gray-400">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        <tr
                            v-for="(item, index) in props.dailyOperations?.data ?? []"
                            :key="item.id"
                            class="odd:bg-white even:bg-gray-50 hover:bg-gray-100 dark:odd:bg-gray-900 dark:even:bg-gray-800"
                        >
                            <td class="px-4 py-3 text-center font-medium whitespace-nowrap text-gray-500 dark:text-gray-400">
                                {{
                                    ((props.dailyOperations?.meta?.current_page || 1) - 1) * (props.dailyOperations?.meta?.per_page || 10) + index + 1
                                }}
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap">{{ item.company_name }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ item.project_name }}</td>

                            <td class="px-4 py-3 whitespace-nowrap">{{ item.flock_name }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span
                                    class="inline-flex rounded-full bg-orange-100 px-2 py-1 text-xs font-medium text-orange-800 dark:bg-orange-900 dark:text-orange-200"
                                >
                                    {{ item.shed_name }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ item.batch_name }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ item.stage_name }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ item.age || 'N/A' }}</td>

                            <td class="px-4 py-3 text-center font-medium whitespace-nowrap text-red-600">
                                {{ item.male_mortality || 0 }}
                            </td>
                            <td class="px-4 py-3 text-center font-medium whitespace-nowrap text-red-600">
                                {{ item.female_mortality || 0 }}
                            </td>
                            <td class="px-4 py-3 text-center font-medium whitespace-nowrap text-red-700">
                                {{ item.total_mortality || 0 }}
                            </td>
                            <td class="px-4 py-3 font-medium whitespace-nowrap text-green-600">
                                {{ item.feed_consumption }}
                            </td>
                            <td class="px-4 py-3 font-medium whitespace-nowrap text-blue-600">
                                {{ item.water_consumption }}
                            </td>
                            <td class="px-4 py-3 text-center font-medium whitespace-nowrap text-yellow-600">
                                {{ item.light_hour || 0 }}
                            </td>
                            <td class="px-4 py-3 text-center font-medium whitespace-nowrap text-purple-600">
                                {{ item.egg_collection || 0 }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                {{ item.created_by_name }}
                            </td>
                            <td class="px-4 py-3 font-medium whitespace-nowrap text-gray-900 dark:text-white">
                                {{ dayjs(item.operation_date).format('MMM DD, YYYY') }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-medium whitespace-nowrap">
                                <div class="dropdown-container relative">
                                    <button @click="toggleDropdown(item.id)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                        </svg>
                                    </button>
                                    <div
                                        v-if="openDropdownId === item.id"
                                        class="absolute right-0 z-10 mt-2 w-48 rounded-md border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800"
                                    >
                                        <div class="py-1">
                                            <Link
                                                v-if="can('daily-operation.bording.edit')"
                                                :href="`/daily-operation/stage/${props.stage}/${item.id}/edit`"
                                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                                            >
                                                <Pencil class="mr-2 h-4 w-4" />
                                                Edit
                                            </Link>
                                            <Link
                                                :href="`/daily-operation/stage/${props.stage}/${item.id}`"
                                                class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700"
                                            >
                                                <Eye class="mr-2 h-4 w-4" />
                                                View Details
                                            </Link>
                                            <button
                                                v-if="can('daily-operation.bording.delete')"
                                                @click="deleteOperation(item.id)"
                                                class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700"
                                            >
                                                <Trash2 class="mr-2 h-4 w-4" />
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!props.dailyOperations?.data?.length">
                            <td colspan="13" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">No daily operations found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <Pagination :meta="props.dailyOperations?.meta" class="mt-6" />
        </div>
    </AppLayout>
</template>
