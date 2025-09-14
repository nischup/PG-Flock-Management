<script setup lang="ts">
import listInfocard from '@/components/ListinfoCard.vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { useListFilters } from '@/composables/useListFilters';
import { useNotifier } from '@/composables/useNotifier';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { FileText, Pencil, Calendar } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

// ✅ Props
const props = defineProps<{
  shedReceives?: {
    data: Array<{
      id: number;
      job_no?: string;
      transaction_no?: string;
      flock_no: number;
      receive_date: string;
      shed_female_qty: number;
      shed_male_qty: number;
      shed_total_qty: number;
      flock: {
        id: number;
        name: string;
      };
      company: {
        id: number;
        name: string;
        short_name: string;
      };
      shed: {
        id: number;
        name: string;
      };
      batch: {
        id: number;
        name: string;
      };
      created_at: string;
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
  companies?: Array<{ id: number; name: string; short_name?: string; code?: string }>;
  flocks?: Array<{ id: number; name: string }>;
  sheds?: Array<{ id: number; name: string }>;
}>();



useListFilters({ routeName: '/production-shed-receive', filters: props.filters });
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
const showFlockDropdown = ref(false);
const showShedDropdown = ref(false);

// Modal state
const showReceiveModal = ref(false)

// Form
const form = useForm({
  shed_receive_id: null,
  flock_id: 0,
  company_id: null,
  receive_date: '',
  shed_female_qty: 0,
  shed_male_qty: 0,
  shed_total_qty: 0,
  note: '',
})

// Open modal with receive data
const openReceiveModal = (receive: any) => {
  form.shed_receive_id = receive.id
  form.flock_id = receive.flock_id
  form.company_id = receive.company_id
  form.receive_date = receive.receive_date

  form.shed_female_qty = 0
  form.shed_male_qty = 0
  form.shed_total_qty = 0
  form.note = ''

  showReceiveModal.value = true
}

// Save receive
const saveReceive = () => {
  form.shed_total_qty = (form.shed_female_qty || 0) + (form.shed_male_qty || 0)
  
  form.post(route('production-shed-receive.store'), {
    onSuccess: () => {
      showReceiveModal.value = false
    },
  })
}

// ✅ Close on outside click
const handleClick = (e: MouseEvent) => {
    if (!(e.target as HTMLElement).closest('.action-dropdown, .action-btn, .pdf-dropdown, .pdf-button, .date-picker-container, .company-dropdown, .flock-dropdown, .shed-dropdown')) {
        closeDropdown();
        openExportDropdown.value = false;
        showFromDatePicker.value = false;
        showToDatePicker.value = false;
        showCompanyDropdown.value = false;
        showFlockDropdown.value = false;
        showShedDropdown.value = false;
    }
};
onMounted(() => document.addEventListener('click', handleClick));
onBeforeUnmount(() => document.removeEventListener('click', handleClick));

// ✅ Delete action
const deleteReceive = (id: number) => {
  confirmDelete({
        url: `/production-shed-receive/${id}`,
    text: 'This will permanently delete the receive record.',
        successMessage: 'Receive record deleted.',
    });
};


// ✅ Export filters
const filters = ref({
    search: props.filters?.search ?? '',
    per_page: props.filters?.per_page ?? 10,
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
    
    window.location.href = `/production-shed-receive?${params.toString()}`;
};

const clearFilters = () => {
    filters.value = {
        search: '',
        per_page: 10,
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
    return filters.value.company_id || 
           filters.value.flock_id ||
           filters.value.shed_id ||
           filters.value.date_from || 
           filters.value.date_to;
});

const getCompanyName = (companyId: string | number) => {
    const company = props.companies?.find(c => c.id === Number(companyId));
    return company?.name || 'Unknown';
};

const getFlockName = (flockId: string | number) => {
    const flock = props.flocks?.find(f => f.id === Number(flockId));
    return flock?.name || 'Unknown';
};

const getShedName = (shedId: string | number) => {
    const shed = props.sheds?.find(s => s.id === Number(shedId));
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

// Flock dropdown helper functions
const getSelectedFlockName = () => {
    if (!filters.value.flock_id) return '';
    const flock = props.flocks?.find(f => f.id === Number(filters.value.flock_id));
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
    const shed = props.sheds?.find(s => s.id === Number(filters.value.shed_id));
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

// Action functions
const handleViewReceive = (receiveId: number) => {
    // Implement view functionality
    console.log('View receive:', receiveId);
    closeDropdown();
};

const handleReportReceive = (receiveId: number) => {
    // Implement report functionality
    console.log('Report receive:', receiveId);
    closeDropdown();
};

const handleDeleteReceive = (receiveId: number) => {
    // Implement delete functionality
    deleteReceive(receiveId);
    closeDropdown();
};

const openExportDropdown = ref(false);

const exportPdf = (orientation: 'portrait' | 'landscape' = 'portrait') => {
    const url = route('reports.production-shed-receive.pdf', { ...filters.value, orientation });
    window.open(url, '_blank');
};

const exportExcel = () => {
    const url = route('reports.production-shed-receive.excel', { ...filters.value });
    window.open(url, '_blank');
};

const exportRowPdf = (id: number) => {
    const url = `/production-shed-receive/${id}/pdf`;
    window.open(url, '_blank');
};

// ✅ Dynamic data for cards based on selected Receive
const selectedReceive = ref<number | null>(null);
const cardData = computed(() => {
    if (!selectedReceive.value || !props.shedReceives?.data) return [];
    
    const selectedItem = props.shedReceives.data.find(item => item.id === selectedReceive.value);
    if (!selectedItem) return [];
    
    return [
        { 
            title: 'Total Receive', 
            value: selectedItem.shed_total_qty, 
            title1: 'Male', 
            value1: selectedItem.shed_male_qty, 
            title2: 'Female', 
            value2: selectedItem.shed_female_qty 
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
            title: 'Flock', 
            value: selectedItem.flock?.name || 'N/A',
            title1: '',
            value1: '',
            title2: '',
            value2: ''
        },
        { 
            title: 'Shed', 
            value: selectedItem.shed?.name || 'N/A',
            title1: '',
            value1: '',
            title2: '',
            value2: ''
        },
        { 
            title: 'Batch', 
            value: selectedItem.batch?.name || 'N/A',
            title1: '',
            value1: '',
            title2: '',
            value2: ''
        },
        { 
            title: 'Receive Date', 
            value: dayjs(selectedItem.receive_date).format('MMM DD, YYYY') || 'N/A',
            title1: '',
            value1: '',
            title2: '',
            value2: ''
        },
    ];
});

// ✅ Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Production', href: '/production-shed-receive' },
  { title: 'Shed Receive', href: '/production-shed-receive' },
];




</script>

<template>
    <Head title="Shed Receive List" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
        <!-- Select Box -->
        <div class="m-5 w-full max-w-sm">
      <select
                v-model="selectedReceive"
                class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
            >
                <option :value="null" disabled>Select Receive Record</option>
                <option 
                    v-for="item in props.shedReceives?.data ?? []" 
                    :key="item.id" 
                    :value="item.id"
                >
                    {{ item.flock?.name || item.flock_no }} - {{ item.company?.short_name }} : {{ dayjs(item.receive_date).format('YYYY-MM-DD') }}
                </option>
      </select>
    </div>

    <listInfocard :cards="cardData" />

        <div class="m-3 rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
            <!-- Header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-xl font-semibold text-gray-800 dark:text-white">Shed Receive Information</h1>
                <div class="flex items-center gap-2">
                    <!-- Shed Receive Button -->
        <Link
          href="/production-shed-receive/create"
                        class="group relative overflow-hidden rounded-md px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-gray-500"
                        style="background: linear-gradient(135deg, #1f2937 0%, #000000 50%, #374151 100%); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.1);"
                    >
                        <span class="relative z-10 flex items-center gap-2">
                            <svg class="h-4 w-4 transition-transform duration-300 group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Shed Receive
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
                                placeholder="Flock, Company, Job No..."
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

                    <!-- Flock Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Flock</label>
                        <div class="flock-dropdown relative">
                            <button
                                @click="showFlockDropdown = !showFlockDropdown"
                                type="button"
                                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white transition-all duration-200"
                            >
                                <span class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    {{ getSelectedFlockName() || 'All Flocks' }}
                                </span>
                                <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showFlockDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                        <button
                                            @click="clearFlockFilter"
                                            class="text-xs text-red-600 hover:text-red-800 dark:text-red-400"
                                        >
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
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Shed</label>
                        <div class="shed-dropdown relative">
                            <button
                                @click="showShedDropdown = !showShedDropdown"
                                type="button"
                                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white transition-all duration-200"
                            >
                                <span class="flex items-center gap-2">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    {{ getSelectedShedName() || 'All Sheds' }}
                                </span>
                                <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showShedDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                        <button
                                            @click="clearShedFilter"
                                            class="text-xs text-red-600 hover:text-red-800 dark:text-red-400"
                                        >
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
                        class="inline-flex items-center rounded-full bg-orange-100 px-2.5 py-0.5 text-xs font-medium text-orange-800 dark:bg-orange-900 dark:text-orange-200"
                    >
                        Flock: {{ getFlockName(filters.flock_id) }}
                    </span>
                    <span 
                        v-if="filters.shed_id" 
                        class="inline-flex items-center rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-medium text-purple-800 dark:bg-purple-900 dark:text-purple-200"
                    >
                        Shed: {{ getShedName(filters.shed_id) }}
                    </span>
                </div>

      </div>

      <!-- List Table -->
      <div class="overflow-x-auto rounded-xl shadow bg-white dark:bg-gray-800 mt-4">
        <table class="min-w-full text-left border-collapse">
          <thead class="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th class="px-4 py-2 border-b whitespace-nowrap min-w-[60px]">#SL</th>
              <th class="px-4 py-2 border-b whitespace-nowrap min-w-[150px]">Company</th>
              <th class="px-4 py-2 border-b whitespace-nowrap min-w-[120px]">Project</th>
              <th class="px-4 py-2 border-b whitespace-nowrap min-w-[120px]">Flock No</th>
              <th class="px-4 py-2 border-b whitespace-nowrap min-w-[100px]">Shed</th>  
              <th class="px-4 py-2 border-b whitespace-nowrap min-w-[120px]">Batch No</th>    
              <th class="px-4 py-2 border-b whitespace-nowrap min-w-[100px]">Female Qty</th>
              <th class="px-4 py-2 border-b whitespace-nowrap min-w-[100px]">Male Qty</th>
              <th class="px-4 py-2 border-b whitespace-nowrap min-w-[120px]">Total Mortality</th>
              <th class="px-4 py-2 border-b whitespace-nowrap min-w-[120px]">Total Excess</th>
              <th class="px-4 py-2 border-b whitespace-nowrap min-w-[120px]">Total Shortage</th>
              <th class="px-4 py-2 border-b whitespace-nowrap min-w-[140px]">Grand Total Qty</th>
              <th class="px-4 py-2 border-b whitespace-nowrap min-w-[120px]">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(receive, index) in props.shedReceives?.data ?? []"
              :key="receive.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              <td class="px-4 py-2 border-b whitespace-nowrap">{{ (props.shedReceives?.meta?.current_page - 1) * (props.shedReceives?.meta?.per_page || 10) + index + 1 }}</td>
              <td class="px-4 py-2 border-b whitespace-nowrap">{{ receive.company?.short_name || 'N/A' }}</td>
              <td class="px-4 py-2 border-b whitespace-nowrap">{{ receive.project?.name || 'N/A' }}</td>
              <td class="px-4 py-2 border-b whitespace-nowrap">{{ receive.flock?.name || 'Flock-' + receive.flock_no }}</td>
              <td class="px-4 py-2 border-b whitespace-nowrap">{{ receive.shed?.name || 'Shed-' + receive.shed_no }}</td> 
              <td class="px-4 py-2 border-b whitespace-nowrap">{{ receive.batch?.name || 'Batch-' + receive.batch_no }}</td>
              <!-- Using optional chaining and default values --> 
              <td class="px-4 py-2 border-b whitespace-nowrap">{{ receive.shed_female_qty || 0 }}</td>
              <td class="px-4 py-2 border-b whitespace-nowrap">{{ receive.shed_male_qty || 0 }}</td>
              <td class="px-4 py-2 border-b whitespace-nowrap">{{ receive.shed_total_qty || 0 }}</td>
              <td class="px-4 py-2 border-b whitespace-nowrap">{{ receive.shed_total_qty || 0 }}</td>
              <td class="px-4 py-2 border-b whitespace-nowrap">{{ receive.shed_total_qty || 0 }}</td>
              <td class="px-4 py-2 border-b whitespace-nowrap">{{ receive.shed_total_qty || 0 }}</td>
              <td class="relative px-4 py-2 border-b whitespace-nowrap">
                <Button 
                  size="sm" 
                  class="action-btn bg-black text-white hover:bg-gray-800 shadow-lg hover:shadow-xl transition-all duration-200 border border-gray-300 hover:border-gray-400" 
                  @click.stop="toggleDropdown(receive.id)"
                  style="background: linear-gradient(135deg, #1f2937 0%, #000000 50%, #374151 100%); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.1);"
                >
                  Actions ▼
                </Button>

                <!-- Action Popup Overlay -->
                <div
                  v-if="openDropdownId === receive.id"
                    class="fixed inset-0 z-50 flex items-center justify-center"
                    @click.stop="closeDropdown"
                >
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-black/20 backdrop-blur-sm"></div>
                    
                    <!-- Popup Content -->
                    <div
                    class="relative z-10 w-52 rounded-xl border border-gray-200 bg-white shadow-2xl dark:border-gray-700 dark:bg-gray-800"
                        @click.stop
                    >
                        <!-- Header -->
                        <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Actions</h3>
                        </div>
                        
                        <!-- Actions List -->
                        <div class="py-2">

                      <!-- View -->
                      <button
                        @click="handleViewReceive(receive.id); closeDropdown()"
                        class="flex w-full items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                      >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        <span>View Details</span>
                      </button>

                      <!-- Report -->
                      <button
                        @click="handleReportReceive(receive.id); closeDropdown()"
                        class="flex w-full items-center gap-3 px-4 py-3 text-blue-700 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-colors"
                      >
                        <FileText class="h-5 w-5" />
                        <span>Generate Report</span>
                      </button>

                            <!-- Delete -->
                            <button
                        @click="handleDeleteReceive(receive.id); closeDropdown()"
                        class="flex w-full items-center gap-3 px-4 py-3 text-red-700 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                      >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        <span>Delete Record</span>
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

            <Pagination 
                v-if="props.shedReceives?.meta && props.shedReceives.meta.current_page" 
                :meta="props.shedReceives.meta" 
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
