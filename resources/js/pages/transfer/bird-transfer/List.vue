<script setup lang="ts">
import listInfocard from '@/components/ListinfoCard.vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { useListFilters } from '@/composables/useListFilters';
import { useNotifier } from '@/composables/useNotifier';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { Calendar, FileText } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

// ✅ Props
const props = defineProps<{
    birdTransfers?: {
        data: Array<{
            id: number;
            job_no?: string;
            transaction_no?: string;
            flock_no: number;
            transfer_date: string;
            transfer_female_qty: number;
            transfer_male_qty: number;
            transfer_total_qty: number;
            flock: {
                id: number;
                name: string;
                code: string;
            };
            from_company: {
                id: number;
                name: string;
                short_name: string;
            };
            to_company: {
                id: number;
                name: string;
                short_name: string;
            };
            from_shed: {
                id: number;
                name: string;
            };
            to_shed: {
                id: number;
                name: string;
            };
            from_project?: {
                id: number;
                name: string;
            };
            to_project?: {
                id: number;
                name: string;
            };
            breed_type?: number[];
            country_of_origin?: number;
            batch_assign?: {
                id: number;
                shed?: {
                    id: number;
                    name: string;
                };
                batch?: {
                    id: number;
                    name: string;
                };
            };
            created_at: string;
        }>;
        meta: { current_page: number; last_page: number; per_page: number; total: number };
    };
    filters?: {
        search?: string;
        per_page?: number;
        from_company_id?: number;
        to_company_id?: number;
        flock_id?: number;
        date_from?: string;
        date_to?: string;
    };
    companies?: Array<{ id: number; name: string; short_name?: string; code?: string }>;
    flocks?: Array<{ id: number; name: string; code: string }>;
    sheds?: Array<{ id: number; name: string }>;
    breedTypes?: Array<{ id: number; name: string }>;
    countries?: Array<{ id: number; name: string }>;
}>();

useListFilters({ routeName: '/bird-transfer', filters: props.filters });
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
const showFromCompanyDropdown = ref(false);
const showToCompanyDropdown = ref(false);
const showFlockDropdown = ref(false);

// ✅ Close on outside click
const handleClick = (e: MouseEvent) => {
    if (
        !(e.target as HTMLElement).closest(
            '.action-dropdown, .action-btn, .pdf-dropdown, .pdf-button, .date-picker-container, .company-dropdown, .flock-dropdown',
        )
    ) {
        closeDropdown();
        openExportDropdown.value = false;
        showFromDatePicker.value = false;
        showToDatePicker.value = false;
        showFromCompanyDropdown.value = false;
        showToCompanyDropdown.value = false;
        showFlockDropdown.value = false;
    }
};
onMounted(() => document.addEventListener('click', handleClick));
onBeforeUnmount(() => document.removeEventListener('click', handleClick));

// ✅ Delete action
const deleteTransfer = (id: number) => {
    confirmDelete({
        url: `/bird-transfer/${id}`,
        text: 'This will permanently delete the transfer record.',
        successMessage: 'Transfer record deleted.',
    });
};

// ✅ Export filters
const filters = ref({
    search: props.filters?.search ?? '',
    per_page: props.filters?.per_page ?? 10,
    from_company_id: props.filters?.from_company_id ?? '',
    to_company_id: props.filters?.to_company_id ?? '',
    flock_id: props.filters?.flock_id ?? '',
    date_from: props.filters?.date_from ?? '',
    date_to: props.filters?.date_to ?? '',
});

// ✅ Filter methods
const applyFilters = () => {
    const params = new URLSearchParams();

    if (filters.value.search) params.set('search', filters.value.search);
    if (filters.value.per_page) params.set('per_page', filters.value.per_page.toString());
    if (filters.value.from_company_id) params.set('from_company_id', filters.value.from_company_id.toString());
    if (filters.value.to_company_id) params.set('to_company_id', filters.value.to_company_id.toString());
    if (filters.value.flock_id) params.set('flock_id', filters.value.flock_id.toString());
    if (filters.value.date_from) params.set('date_from', filters.value.date_from);
    if (filters.value.date_to) params.set('date_to', filters.value.date_to);

    window.location.href = `/bird-transfer?${params.toString()}`;
};

const clearFilters = () => {
    filters.value = {
        search: '',
        per_page: 10,
        from_company_id: '',
        to_company_id: '',
        flock_id: '',
        date_from: '',
        date_to: '',
    };
    applyFilters();
};

// ✅ Computed properties for filter summary
const hasActiveFilters = computed(() => {
    return filters.value.from_company_id || filters.value.to_company_id || filters.value.flock_id || filters.value.date_from || filters.value.date_to;
});

const getCompanyName = (companyId: string | number) => {
    const company = props.companies?.find((c) => c.id === Number(companyId));
    return company?.name || 'Unknown';
};

const getFlockName = (flockId: string | number) => {
    const flock = props.flocks?.find((f) => f.id === Number(flockId));
    return flock?.code || 'Unknown';
};

const getBreedNames = (breedTypeIds: number[] | null) => {
    if (!breedTypeIds || !Array.isArray(breedTypeIds)) return 'N/A';
    const breedNames = breedTypeIds.map(id => {
        const breed = props.breedTypes?.find(b => b.id === id);
        return breed?.name || `Breed-${id}`;
    });
    return breedNames.join(', ');
};

const getCountryName = (countryId: number | null) => {
    if (!countryId) return 'N/A';
    const country = props.countries?.find(c => c.id === countryId);
    return country?.name || `Country-${countryId}`;
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
const getSelectedFromCompanyName = () => {
    if (!filters.value.from_company_id) return '';
    const company = props.companies?.find((c) => c.id === Number(filters.value.from_company_id));
    return company?.name || '';
};

const selectFromCompany = (companyId: string | number) => {
    filters.value.from_company_id = companyId ? Number(companyId) : '';
    showFromCompanyDropdown.value = false;
};

const clearFromCompanyFilter = () => {
    filters.value.from_company_id = '';
    showFromCompanyDropdown.value = false;
};

const getSelectedToCompanyName = () => {
    if (!filters.value.to_company_id) return '';
    const company = props.companies?.find((c) => c.id === Number(filters.value.to_company_id));
    return company?.name || '';
};

const selectToCompany = (companyId: string | number) => {
    filters.value.to_company_id = companyId ? Number(companyId) : '';
    showToCompanyDropdown.value = false;
};

const clearToCompanyFilter = () => {
    filters.value.to_company_id = '';
    showToCompanyDropdown.value = false;
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

// Action functions
const handleViewTransfer = (transferId: number) => {
    // Implement view functionality
    console.log('View transfer:', transferId);
    closeDropdown();
};

const handleReportTransfer = (transferId: number) => {
    // Implement report functionality
    console.log('Report transfer:', transferId);
    closeDropdown();
};

const handleDeleteTransfer = (transferId: number) => {
    // Implement delete functionality
    deleteTransfer(transferId);
    closeDropdown();
};

const openExportDropdown = ref(false);

const exportPdf = (orientation: 'portrait' | 'landscape' = 'portrait') => {
    const url = route('reports.bird-transfer.pdf', { ...filters.value, orientation });
    window.open(url, '_blank');
};

const exportExcel = () => {
    const url = route('reports.bird-transfer.excel', { ...filters.value });
    window.open(url, '_blank');
};

const viewPdf = (id: number) => {
    // open PDF in browser tab (stream)
    const url = `/bird-transfer-row/${id}/pdf`;
    window.open(url, '_blank');
};

const downloadPdf = (id: number) => {
    // open PDF and force download
    const url = `/bird-transfer-row/${id}/pdf?download=1`;
    window.open(url, '_blank');
};

// ✅ Dynamic data for cards based on selected Transfer
const selectedTransfer = ref<number | null>(null);
const cardData = computed(() => {
    if (!selectedTransfer.value || !props.birdTransfers?.data) return [];

    const selectedItem = props.birdTransfers.data.find((item) => item.id === selectedTransfer.value);
    if (!selectedItem) return [];

    return [
        {
            title: 'Total Transfer',
            value: selectedItem.transfer_total_qty,
            title1: 'Male',
            value1: selectedItem.transfer_male_qty,
            title2: 'Female',
            value2: selectedItem.transfer_female_qty,
        },
        {
            title: 'From Company',
            value: selectedItem.from_company?.name || 'N/A',
            title1: '',
            value1: '',
            title2: '',
            value2: '',
        },
        {
            title: 'To Company',
            value: selectedItem.to_company?.name || 'N/A',
            title1: '',
            value1: '',
            title2: '',
            value2: '',
        },
        {
            title: 'Flock',
            value: selectedItem.flock?.code || 'N/A',
            title1: '',
            value1: '',
            title2: '',
            value2: '',
        },
        {
            title: 'From Shed',
            value: selectedItem.from_shed?.name || 'N/A',
            title1: '',
            value1: '',
            title2: '',
            value2: '',
        },
        {
            title: 'To Shed',
            value: selectedItem.to_shed?.name || 'N/A',
            title1: '',
            value1: '',
            title2: '',
            value2: '',
        },
        {
            title: 'Transfer Date',
            value: dayjs(selectedItem.transfer_date).format('MMM DD, YYYY') || 'N/A',
            title1: '',
            value1: '',
            title2: '',
            value2: '',
        },
    ];
});

// ✅ Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Transfer', href: '/bird-transfer' },
    { title: 'Bird Transfer', href: '/bird-transfer' },
];
</script>

<template>
    <Head title="Bird Transfer List" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6">
            <!-- Select Box -->
            <div class="m-5 w-full max-w-sm">
                <select
                    v-model="selectedTransfer"
                    class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                >
                    <option :value="null" disabled>Select Transfer Record</option>
                    <option v-for="item in props.birdTransfers?.data ?? []" :key="item.id" :value="item.id">
                        {{ item.flock?.code || item.flock_no }} - {{ item.from_company?.short_name }} to {{ item.to_company?.short_name }} :
                        {{ dayjs(item.transfer_date).format('YYYY-MM-DD') }}
                    </option>
                </select>
            </div>

            <listInfocard :cards="cardData" />

            <div class="m-3 rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
                <!-- Header -->
                <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <h1 class="text-xl font-semibold text-gray-800 dark:text-white">Bird Transfer Information</h1>
                    <div class="flex items-center gap-2">
                        <!-- <Link
                      href="/bird-transfer/create"
                                    class="group relative overflow-hidden rounded-md px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-gray-500"
                                    style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);"
                                >
                                    <span class="relative z-10 flex items-center gap-2">
                                        <svg class="h-4 w-4 transition-transform duration-300 group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        Add Transfer
                                    </span>
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-20 group-hover:translate-x-full"></div>
                  </Link> -->

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

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
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
                                    placeholder="Flock, Company, Job No..."
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

                        <!-- From Company Filter -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">From Company</label>
                            <div class="company-dropdown relative">
                                <button
                                    @click="showFromCompanyDropdown = !showFromCompanyDropdown"
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
                                        {{ getSelectedFromCompanyName() || 'All From Companies' }}
                                    </span>
                                    <svg
                                        class="h-4 w-4 text-gray-400 transition-transform duration-200"
                                        :class="{ 'rotate-180': showFromCompanyDropdown }"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <!-- From Company Dropdown -->
                                <div
                                    v-if="showFromCompanyDropdown"
                                    class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700"
                                >
                                    <div class="p-2">
                                        <div class="mb-2 flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Select From Company</span>
                                            <button @click="clearFromCompanyFilter" class="text-xs text-red-600 hover:text-red-800 dark:text-red-400">
                                                Clear
                                            </button>
                                        </div>
                                        <div class="max-h-48 space-y-1 overflow-y-auto">
                                            <button
                                                @click="selectFromCompany('')"
                                                class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                                :class="{ 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': !filters.from_company_id }"
                                            >
                                                <span>All From Companies</span>
                                            </button>
                                            <button
                                                v-for="company in props.companies ?? []"
                                                :key="company.id"
                                                @click="selectFromCompany(company.id)"
                                                class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                                :class="{
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200':
                                                        filters.from_company_id == company.id,
                                                }"
                                            >
                                                <span>{{ company.name }}</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- To Company Filter -->
                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">To Company</label>
                            <div class="company-dropdown relative">
                                <button
                                    @click="showToCompanyDropdown = !showToCompanyDropdown"
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
                                        {{ getSelectedToCompanyName() || 'All To Companies' }}
                                    </span>
                                    <svg
                                        class="h-4 w-4 text-gray-400 transition-transform duration-200"
                                        :class="{ 'rotate-180': showToCompanyDropdown }"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <!-- To Company Dropdown -->
                                <div
                                    v-if="showToCompanyDropdown"
                                    class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700"
                                >
                                    <div class="p-2">
                                        <div class="mb-2 flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300"> To Company</span>
                                            <button @click="clearToCompanyFilter" class="text-xs text-red-600 hover:text-red-800 dark:text-red-400">
                                                Clear
                                            </button>
                                        </div>
                                        <div class="max-h-48 space-y-1 overflow-y-auto">
                                            <button
                                                @click="selectToCompany('')"
                                                class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                                :class="{ 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': !filters.to_company_id }"
                                            >
                                                <span>All Companies</span>
                                            </button>
                                            <button
                                                v-for="company in props.companies ?? []"
                                                :key="company.id"
                                                @click="selectToCompany(company.id)"
                                                class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                                :class="{
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200':
                                                        filters.to_company_id == company.id,
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
                                                :class="{
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': filters.flock_id == flock.id,
                                                }"
                                            >
                                                <span>{{ flock.code }}</span>
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
                                                        'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200':
                                                            filters.date_to === option.value,
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

                    <!-- Active Filters Summary -->
                    <div v-if="hasActiveFilters" class="mt-4 flex flex-wrap gap-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Active Filters:</span>
                        <span
                            v-if="filters.from_company_id"
                            class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                        >
                            From Company: {{ getCompanyName(filters.from_company_id) }}
                        </span>
                        <span
                            v-if="filters.to_company_id"
                            class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200"
                        >
                            To Company: {{ getCompanyName(filters.to_company_id) }}
                        </span>
                        <span
                            v-if="filters.flock_id"
                            class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-800 dark:bg-orange-900 dark:text-orange-200"
                        >
                            Flock: {{ getFlockName(filters.flock_id) }}
                        </span>
                        <span
                            v-if="filters.date_from || filters.date_to"
                            class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200"
                        >
                            Date: {{ filters.date_from || 'Start' }} to {{ filters.date_to || 'End' }}
                        </span>
                    </div>
                </div>

                <!-- List Table -->
                <div class="mt-4 overflow-x-auto rounded-xl bg-white shadow dark:bg-gray-800">
                    <table class="w-full border-collapse text-left">
                        <thead class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th class="border-b px-4 py-2">#SL</th>
                                <th class="border-b px-4 py-2">From Company</th>
                                <th class="border-b px-4 py-2">From Project</th>
                                <th class="border-b px-4 py-2">To Company</th>
                                <th class="border-b px-4 py-2">To Project</th>
                                <th class="border-b px-4 py-2">Flock</th>
                                <th class="border-b px-4 py-2">Transfered Batch</th>
                                <th class="border-b px-4 py-2">Breed</th>
                                <th class="border-b px-4 py-2">Origin</th>
                                <th class="border-b px-4 py-2">Total Qty</th>
                                <th class="border-b px-4 py-2">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(transfer, index) in birdTransfers.data" :key="transfer.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="border-b px-4 py-2">{{ index + 1 }}</td>
                                <td class="border-b px-4 py-2">{{ transfer.from_company?.short_name || 'N/A' }}</td>
                                <td class="border-b px-4 py-2">{{ transfer.from_project?.name || 'N/A' }}</td>
                                <td class="border-b px-4 py-2">{{ transfer.to_company?.short_name || 'N/A' }}</td>
                                <td class="border-b px-4 py-2">{{ transfer.to_project?.name || 'N/A' }}</td>
                                <td class="border-b px-4 py-2">{{ transfer.flock?.code || 'Flock-' + transfer.flock_no }}</td>
                                <td class="border-b px-4 py-2">{{ transfer.batch_assign?.batch?.name || 'N/A' }}</td>
                                <td class="border-b px-4 py-2">{{ getBreedNames(transfer.breed_type ?? null) || 'N/A' }}</td>
                                <td class="border-b px-4 py-2">{{ getCountryName(transfer.country_of_origin ?? null) || 'N/A' }}</td>
                                <td class="border-b px-4 py-2">{{ transfer.transfer_total_qty || 0 }}</td>
                                <td class="relative border-b px-4 py-2">
                                    <Button
                                        size="sm"
                                        class="action-btn bg-gray-500 text-white hover:bg-gray-600"
                                        @click.stop="toggleDropdown(transfer.id)"
                                    >
                                        Actions ▼
                                    </Button>

                                    <!-- Action Popup Overlay -->
                                    <div
                                        v-if="openDropdownId === transfer.id"
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
                                                <!-- Approve By -->
                                                <button
                                                    @click="handleViewTransfer(transfer.id)"
                                                    class="flex w-full items-center gap-2 px-4 py-2 text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700"
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
                                                    <span>Approve By</span>
                                                </button>

                                                <!-- View -->
                                                <button
                                                    @click="handleViewTransfer(transfer.id)"
                                                    class="flex w-full items-center gap-2 px-4 py-2 text-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700"
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
                                                </button>

                                                <!-- Report -->
                                                <button
                                                    v-if="can('ps-receive.view')"
                                                    @click="
                                                        viewPdf(transfer.id);
                                                        closeDropdown();
                                                    "
                                                    class="flex w-full items-center gap-2 px-4 py-2 text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20"
                                                >
                                                    <FileText class="h-4 w-4" />
                                                    <span>Report</span>
                                                </button>

                                                <!-- Delete -->
                                                <button
                                                    @click="handleDeleteTransfer(transfer.id)"
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
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <Pagination v-if="props.birdTransfers?.meta && props.birdTransfers.meta.current_page" :meta="props.birdTransfers.meta" class="mt-6" />
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
