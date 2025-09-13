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
import { FileText, Pencil, Calendar } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

// ✅ Props
const props = defineProps<{
    psReceives?: {
        data: Array<{
            id: number;
            pi_no: string;
            pi_date: string;
            order_no?: string;
            order_date?: string;
            lc_no?: string;
            lc_date?: string;
            shipment_type_id: number;
            supplier: { id: number; name: string } | null;
            company: { id: number; name: string } | null;
            country: { id: number; name: string } | null;
            breed_type: number[];
            transport_type: number;
            remarks?: string | null;
            transport_inside_temp?: string | null;
            status: number;
            created_at: string;
            chick_counts?: {
                id: number;
                ps_male_rec_box: number;
                ps_male_qty: number;
                ps_female_rec_box: number;
                ps_female_qty: number;
                ps_total_qty: number;
                ps_total_re_box_qty: number;
                ps_challan_box_qty: number;
                ps_gross_weight: number;
                ps_net_weight: number;
                ps_bonus_qty: number;
            } | null;
        }>;
        meta: { current_page: number; last_page: number; per_page: number; total: number };
    };
    filters?: { 
        search?: string; 
        per_page?: number; 
        company_id?: number; 
        shipment_type_id?: number; 
        date_from?: string; 
        date_to?: string; 
    };
    companies?: Array<{ id: number; name: string }>;
}>();

useListFilters({ routeName: '/ps-receive', filters: props.filters });
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
const showCompanyDropdown = ref(false);
const showShipmentTypeDropdown = ref(false);

// ✅ Close on outside click
const handleClick = (e: MouseEvent) => {
    if (!(e.target as HTMLElement).closest('.action-btn, .pdf-dropdown, .pdf-button, .date-picker-container, .company-dropdown, .shipment-type-dropdown')) {
        closeDropdown();
        openExportDropdown.value = false;
        showFromDatePicker.value = false;
        showToDatePicker.value = false;
        showCompanyDropdown.value = false;
        showShipmentTypeDropdown.value = false;
    }
};
onMounted(() => document.addEventListener('click', handleClick));
onBeforeUnmount(() => document.removeEventListener('click', handleClick));

// ✅ Delete action
const deleteReceive = (id: number) => {
    confirmDelete({
        url: `/ps-receive/${id}`,
        text: 'This will permanently delete the record.',
        successMessage: 'PS Receive deleted.',
    });
};

// ✅ Export filters
const filters = ref({
    search: props.filters?.search ?? '',
    per_page: props.filters?.per_page ?? 10,
    company_id: props.filters?.company_id ?? '',
    shipment_type_id: props.filters?.shipment_type_id ?? '',
    date_from: props.filters?.date_from ?? '',
    date_to: props.filters?.date_to ?? '',
});

// ✅ Filter methods
const applyFilters = () => {
    const params = new URLSearchParams();
    
    if (filters.value.search) params.set('search', filters.value.search);
    if (filters.value.per_page) params.set('per_page', filters.value.per_page.toString());
    if (filters.value.company_id) params.set('company_id', filters.value.company_id.toString());
    if (filters.value.shipment_type_id) params.set('shipment_type_id', filters.value.shipment_type_id.toString());
    if (filters.value.date_from) params.set('date_from', filters.value.date_from);
    if (filters.value.date_to) params.set('date_to', filters.value.date_to);
    
    window.location.href = `/ps-receive?${params.toString()}`;
};

const clearFilters = () => {
    filters.value = {
        search: '',
        per_page: 10,
        company_id: '',
        shipment_type_id: '',
        date_from: '',
        date_to: '',
    };
    applyFilters();
};

// ✅ Computed properties for filter summary
const hasActiveFilters = computed(() => {
    return filters.value.company_id || 
           filters.value.shipment_type_id || 
           filters.value.date_from || 
           filters.value.date_to;
});

const getCompanyName = (companyId: string | number) => {
    const company = props.companies?.find(c => c.id === Number(companyId));
    return company?.name || 'Unknown';
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
    
    // Generate last 30 days
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

// Company dropdown helper functions
const getSelectedCompanyName = () => {
    if (!filters.value.company_id) return '';
    const company = props.companies?.find(c => c.id === Number(filters.value.company_id));
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

// Shipment type dropdown helper functions
const getSelectedShipmentTypeName = () => {
    if (!filters.value.shipment_type_id) return '';
    return filters.value.shipment_type_id === 1 ? 'Local' : 'Foreign';
};

const selectShipmentType = (typeId: string | number) => {
    filters.value.shipment_type_id = typeId ? Number(typeId) : '';
    showShipmentTypeDropdown.value = false;
};

const clearShipmentTypeFilter = () => {
    filters.value.shipment_type_id = '';
    showShipmentTypeDropdown.value = false;
};

// Lab data helper functions
const getGovLabMaleQty = (item: any) => {
    const govLab = item.lab_transfers?.find((lab: any) => lab.lab_type === '1');
    return govLab?.lab_send_male_qty ?? '-';
};

const getGovLabFemaleQty = (item: any) => {
    const govLab = item.lab_transfers?.find((lab: any) => lab.lab_type === '1');
    return govLab?.lab_send_female_qty ?? '-';
};

const getGovLabTotalQty = (item: any) => {
    const govLab = item.lab_transfers?.find((lab: any) => lab.lab_type === '1');
    return govLab?.lab_send_total_qty ?? '-';
};

const getProvitaLabMaleQty = (item: any) => {
    const provitaLab = item.lab_transfers?.find((lab: any) => lab.lab_type === '2');
    return provitaLab?.lab_send_male_qty ?? '-';
};

const getProvitaLabFemaleQty = (item: any) => {
    const provitaLab = item.lab_transfers?.find((lab: any) => lab.lab_type === '2');
    return provitaLab?.lab_send_female_qty ?? '-';
};

const getProvitaLabTotalQty = (item: any) => {
    const provitaLab = item.lab_transfers?.find((lab: any) => lab.lab_type === '2');
    return provitaLab?.lab_send_total_qty ?? '-';
};

const openExportDropdown = ref(false);

const exportPdf = (orientation: 'portrait' | 'landscape' = 'portrait') => {
    const url = route('reports.ps-receive.pdf', { ...filters.value, orientation });
    window.open(url, '_blank');
};

const exportExcel = () => {
    const url = route('reports.ps-receive.excel', { ...filters.value });
    window.open(url, '_blank');
};

const exportRowPdf = (id: number) => {
    const url = `/ps-receive/${id}/pdf`; // route to new controller method
    window.open(url, '_blank');
};

// ✅ Dynamic data for cards based on selected PS Receive
const selectedPI = ref<number | null>(null);
const cardData = computed(() => {
    if (!selectedPI.value || !props.psReceives?.data) return [];
    
    const selectedItem = props.psReceives.data.find(item => item.id === selectedPI.value);
    if (!selectedItem || !selectedItem.chick_counts) return [];
    
    const chick = selectedItem.chick_counts;
    return [
        { 
            title: 'Challan Box', 
            value: chick.ps_challan_box_qty, 
            title1: 'F Box', 
            value1: chick.ps_female_rec_box, 
            title2: 'M Box', 
            value2: chick.ps_male_rec_box 
        },
        { 
            title: 'Receive Box', 
            value: chick.ps_total_re_box_qty, 
            title1: 'F Box', 
            value1: chick.ps_female_rec_box, 
            title2: 'M Box', 
            value2: chick.ps_male_rec_box 
        },
        { 
            title: 'Total Chicks', 
            value: chick.ps_total_qty, 
            title1: 'M', 
            value1: chick.ps_male_qty, 
            title2: 'F', 
            value2: chick.ps_female_qty 
        },
        { 
            title: 'Company', 
            value: selectedItem.company?.name || 'N/A',
            title1: '',
            value1: '',
            title2: '',
            value2: ''
        },
        { 
            title: 'Gross Weight', 
            value: `${chick.ps_gross_weight} kg`,
            title1: '',
            value1: '',
            title2: '',
            value2: ''
        },
        { 
            title: 'Net Weight', 
            value: `${chick.ps_net_weight} kg`,
            title1: '',
            value1: '',
            title2: '',
            value2: ''
        },
    ];
});

// ✅ Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Parent Stock', href: '/ps-receive' },
    { title: 'Receive', href: '/ps-receive' },
];
</script>

<template>
    <Head title="PS Receives" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Select Box -->
        <div class="m-5 w-full max-w-sm">
            <select
                v-model="selectedPI"
                class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
            >
                <option :value="null" disabled>Select PI No</option>
                <option 
                    v-for="item in props.psReceives?.data ?? []" 
                    :key="item.id" 
                    :value="item.id"
                >
                    PI-No {{ item.pi_no }} : {{ dayjs(item.pi_date).format('YYYY-MM-DD') }}
                </option>
            </select>
        </div>

        <listInfocard :cards="cardData" />

        <div class="m-3 rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
            <!-- Header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-xl font-semibold text-gray-800 dark:text-white">Parent Stock Receive Information</h1>
                <div class="flex items-center gap-2">
                    <Link
                        v-if="can('ps-receive.create')"
                        href="/ps-receive/create"
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
                                placeholder="PI No, Order No, Supplier..."
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

                    <!-- Company Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Company</label>
                        <div class="company-dropdown relative">
                            <button
                                @click="showCompanyDropdown = !showCompanyDropdown"
                                type="button"
                                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white transition-all duration-200"
                            >
                                <span class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    {{ getSelectedCompanyName() || 'All Companies' }}
                                </span>
                                <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showCompanyDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                        <button
                                            @click="clearCompanyFilter"
                                            class="text-xs text-red-600 hover:text-red-800 dark:text-red-400"
                                        >
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
                                            :class="{ 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': filters.company_id == company.id }"
                                        >
                                            <span>{{ company.name }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shipment Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Shipment Type</label>
                        <div class="shipment-type-dropdown relative">
                            <button
                                @click="showShipmentTypeDropdown = !showShipmentTypeDropdown"
                                type="button"
                                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white transition-all duration-200"
                            >
                                <span class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                    </svg>
                                    {{ getSelectedShipmentTypeName() || 'All Types' }}
                                </span>
                                <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showShipmentTypeDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <!-- Shipment Type Dropdown -->
                            <div
                                v-if="showShipmentTypeDropdown"
                                class="absolute z-20 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700"
                            >
                                <div class="p-2">
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Select Type</span>
                                        <button
                                            @click="clearShipmentTypeFilter"
                                            class="text-xs text-red-600 hover:text-red-800 dark:text-red-400"
                                        >
                                            Clear
                                        </button>
                                    </div>
                                    <div class="space-y-1">
                                        <button
                                            @click="selectShipmentType('')"
                                            class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                            :class="{ 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': !filters.shipment_type_id }"
                                        >
                                            <span>All Types</span>
                                        </button>
                                        <button
                                            @click="selectShipmentType(1)"
                                            class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                            :class="{ 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': filters.shipment_type_id == 1 }"
                                        >
                                            <span class="inline-flex items-center gap-2">
                                                <span class="h-2 w-2 rounded-full bg-green-500"></span>
                                                Local
                                            </span>
                                        </button>
                                        <button
                                            @click="selectShipmentType(2)"
                                            class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                            :class="{ 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': filters.shipment_type_id == 2 }"
                                        >
                                            <span class="inline-flex items-center gap-2">
                                                <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                                Foreign
                                            </span>
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
                        v-if="filters.company_id" 
                        class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                    >
                        Company: {{ getCompanyName(filters.company_id) }}
                    </span>
                    <span 
                        v-if="filters.shipment_type_id" 
                        class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-200"
                    >
                        Type: {{ filters.shipment_type_id === 1 ? 'Local' : 'Foreign' }}
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
                    <span class="text-xs">{{ props.psReceives?.data?.length ?? 0 }} records</span>
                </div>
                
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700" style="min-width: 1200px;">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr class="text-left text-gray-600 dark:text-gray-300">
                            <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 60px;">S/N</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 150px;">Company</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 120px;">Shipment Type</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 120px;">PI No</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 120px;">LC No</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 120px;">Order No</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 150px;">Supplier</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 100px;">Total Box</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 120px;">Weight (kg)</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 150px;">Gov Lab Total</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 150px;">Provita Lab Total</th>
                            <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        <tr
                            v-for="(item, index) in props.psReceives?.data ?? []"
                            :key="item.id"
                            class="odd:bg-white even:bg-gray-50 hover:bg-gray-100 dark:odd:bg-gray-900 dark:even:bg-gray-800"
                        >
                            <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400 whitespace-nowrap font-medium">
                                {{ ((props.psReceives?.meta?.current_page || 1) - 1) * (props.psReceives?.meta?.per_page || 10) + index + 1 }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ item.company?.name ?? 'N/A' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span 
                                    class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                    :class="item.shipment_type_id === 1 
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                                        : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'"
                                >
                                    {{ item.shipment_type_id === 1 ? 'Local' : 'Foreign' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap">{{ item.pi_no }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ item.lc_no ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ item.order_no ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ item.supplier?.name ?? 'N/A' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ item.chick_counts?.ps_total_re_box_qty ?? '-' }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm">
                                    <div>Gross: {{ item.chick_counts?.ps_gross_weight ?? '-' }} kg</div>
                                    <div>Net: {{ item.chick_counts?.ps_net_weight ?? '-' }} kg</div>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm">
                                    <div>Male: {{ getGovLabMaleQty(item) }}</div>
                                    <div>Female: {{ getGovLabFemaleQty(item) }}</div>
                                    <div class="font-medium">Total: {{ getGovLabTotalQty(item) }}</div>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm">
                                    <div>Male: {{ getProvitaLabMaleQty(item) }}</div>
                                    <div>Female: {{ getProvitaLabFemaleQty(item) }}</div>
                                    <div class="font-medium">Total: {{ getProvitaLabTotalQty(item) }}</div>
                                </div>
                            </td>
                            <td class="relative px-4 py-3">
                                <Button size="sm" class="action-btn bg-gray-500 text-white hover:bg-gray-600" @click.stop="toggleDropdown(item.id)">
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
                                                v-if="can('ps-receive.view')"
                                                :href="route('ps-receive.show', item.id)"
                                                class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors duration-200"
                                                @click="closeDropdown"
                                            >
                                                <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                                <span class="text-sm font-medium">View Details</span>
                                            </Link>

                                            <!-- Edit -->
                                            <Link
                                                v-if="can('ps-receive.edit')"
                                                :href="`/ps-receive/${item.id}/edit`"
                                                class="flex items-center gap-3 px-4 py-3 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20 transition-colors duration-200"
                                                @click="closeDropdown"
                                            >
                                                <Pencil class="h-5 w-5" />
                                                <span class="text-sm font-medium">Edit Record</span>
                                            </Link>

                                            <!-- Report -->
                                            <button
                                                v-if="can('ps-receive.view')"
                                                @click="exportRowPdf(item.id); closeDropdown()"
                                                class="flex w-full items-center gap-3 px-4 py-3 text-green-600 hover:bg-green-50 dark:text-green-400 dark:hover:bg-green-900/20 transition-colors duration-200"
                                            >
                                                <FileText class="h-5 w-5" />
                                                <span class="text-sm font-medium">Generate Report</span>
                                            </button>

                                            <!-- Delete -->
                                            <button
                                                v-if="can('ps-receive.delete')"
                                                @click="deleteReceive(item.id); closeDropdown()"
                                                class="flex w-full items-center gap-3 px-4 py-3 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 transition-colors duration-200"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                <span class="text-sm font-medium">Delete Record</span>
                                            </button>
                                        </div>
                                        
                                        <!-- Footer -->
                                        <div class="border-t border-gray-200 px-4 py-2 dark:border-gray-700">
                                            <button
                                                @click="closeDropdown"
                                                class="w-full rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors duration-200"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="(props.psReceives?.data ?? []).length === 0">
                            <td colspan="9" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">No PS Receives found.</td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>

            <Pagination 
                v-if="props.psReceives?.meta && props.psReceives.meta.current_page" 
                :meta="props.psReceives.meta" 
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
