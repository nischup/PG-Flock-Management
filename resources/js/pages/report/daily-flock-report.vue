<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, computed, watch, onMounted, onUnmounted } from 'vue';

type Batch = {
    delivery_date?: string;
    breed_type?: string;
    batch_no?: string;

    // Transfer farm
    register_female?: number | string;
    register_male?: number | string;
    erp_female?: number | string;
    erp_male?: number | string;
    challan_female?: number | string;
    challan_male?: number | string;
    medical_female?: number | string;
    medical_male?: number | string;
    deviation_female?: number | string;
    deviation_male?: number | string;

    // Receive farm
    received_female?: number | string;
    received_male?: number | string;
    mortality_female?: number | string;
    mortality_male?: number | string;
    total_received_female?: number | string;
    total_received_male?: number | string;

    // Deviations
    actual_deviation_female?: number | string;
    actual_deviation_male?: number | string;
    challan_deviation_female?: number | string;
    challan_deviation_male?: number | string;
};

const props = defineProps<{
    from_company: string;
    to_company: string;
    batches: Batch[];
    totals: Record<string, number | string>;
    companies: Array<{ id: number; name: string }>;
    projects: Array<{ id: number; name: string; company_id: number }>;
    flocks: Array<{ id: number; name: string; code: string }>;
    filters?: {
        date_from?: string;
        date_to?: string;
        company_id?: string | number;
        project_id?: string | number;
        flock_id?: string | number;
    };
}>();

const state = reactive({
    date_from: props.filters?.date_from ?? '',
    date_to: props.filters?.date_to ?? '',
    company_id: props.filters?.company_id ?? '',
    project_id: props.filters?.project_id ?? '',
    flock_id: props.filters?.flock_id ?? '',
    flock_search: '',
    show_flock_dropdown: false,
    show_date_from_picker: false,
    show_date_to_picker: false,
});

// Computed property to filter projects based on selected company
const filteredProjects = computed(() => {
    if (!state.company_id) {
        return [];
    }
    const filtered = props.projects.filter(project => project.company_id === Number(state.company_id));
    console.log('Filtering projects for company:', state.company_id, 'Found:', filtered.length);
    return filtered;
});

// Computed property to filter flocks based on search
const filteredFlocks = computed(() => {
    if (!state.flock_search) {
        return props.flocks;
    }
    return props.flocks.filter(flock => 
        flock.code.toLowerCase().includes(state.flock_search.toLowerCase()) ||
        flock.name.toLowerCase().includes(state.flock_search.toLowerCase())
    );
});

// Computed property to get selected flock
const selectedFlock = computed(() => {
    return props.flocks.find(flock => flock.id === Number(state.flock_id));
});

// Watch for company changes to reset project
watch(() => state.company_id, (newCompanyId, oldCompanyId) => {
    console.log('Company changed from:', oldCompanyId, 'to:', newCompanyId);
    state.project_id = '';
    if (!newCompanyId) {
        console.log('No company selected, clearing project');
    }
});

// Methods for flock dropdown
const selectFlock = (flock: any) => {
    state.flock_id = flock.id;
    state.flock_search = flock.code;
    state.show_flock_dropdown = false;
};

const toggleFlockDropdown = () => {
    state.show_flock_dropdown = !state.show_flock_dropdown;
    if (state.show_flock_dropdown) {
        state.flock_search = '';
    }
};

const clearFlockSearch = () => {
    state.flock_search = '';
    state.flock_id = '';
};

// Date picker methods
const toggleDateFromPicker = () => {
    state.show_date_from_picker = !state.show_date_from_picker;
    state.show_date_to_picker = false;
};

const toggleDateToPicker = () => {
    state.show_date_to_picker = !state.show_date_to_picker;
    state.show_date_from_picker = false;
};

const selectDateFrom = (date: string) => {
    state.date_from = date;
    state.show_date_from_picker = false;
};

const selectDateTo = (date: string) => {
    state.date_to = date;
    state.show_date_to_picker = false;
};

const formatDate = (dateString: string) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const getTodayDate = () => {
    return new Date().toISOString().split('T')[0];
};

const getDateOptions = () => {
    const options = [];
    const today = new Date();
    
    // Generate last 30 days
    for (let i = 0; i < 30; i++) {
        const date = new Date(today);
        date.setDate(today.getDate() - i);
        options.push({
            value: date.toISOString().split('T')[0],
            label: formatDate(date.toISOString().split('T')[0]),
            isToday: i === 0
        });
    }
    
    return options;
};

// Click outside handler to close dropdowns
const handleClickOutside = (event: Event) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.flock-dropdown-container')) {
        state.show_flock_dropdown = false;
    }
    if (!target.closest('.date-picker-container')) {
        state.show_date_from_picker = false;
        state.show_date_to_picker = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    // Initialize flock search with selected flock code
    if (state.flock_id && selectedFlock.value) {
        state.flock_search = selectedFlock.value.code;
    }
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const applyFilters = () => {
    router.get(
        '/daily-flock-report',
        {
            date_from: state.date_from || undefined,
            date_to: state.date_to || undefined,
            company_id: state.company_id || undefined,
            project_id: state.project_id || undefined,
            flock_id: state.flock_id || undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const buildQuery = () => {
    const q = new URLSearchParams();
    if (state.date_from) q.set('date_from', state.date_from as string);
    if (state.date_to) q.set('date_to', state.date_to as string);
    if (state.company_id) q.set('company_id', String(state.company_id));
    if (state.project_id) q.set('project_id', String(state.project_id));
    if (state.flock_id) q.set('flock_id', String(state.flock_id));
    return q.toString();
};

const exportPdf = () => {
    const qs = buildQuery();
    // opens a new tab with a streamed PDF
    window.open(`/daily-flock-report/pdf${qs ? `?${qs}` : ''}`, '_blank');
};

const exportExcel = () => {
    const qs = buildQuery();
    // triggers file download (CSV/Excel)
    window.location.href = `/daily-flock-report/excel${qs ? `?${qs}` : ''}`;
};

const handleExportChange = (event: Event) => {
    const target = event.target as HTMLSelectElement;
    const value = target.value;
    
    if (value === 'pdf') {
        exportPdf();
    } else if (value === 'excel') {
        exportExcel();
    }
    
    // Reset dropdown to default option
    target.value = '';
};
</script>

<template>
    <Head title="Daily Flock Report" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Reports', href: '#' },
            { title: 'Daily Flock Report', href: '/daily-flock-report' },
        ]"
    >
        <div class="space-y-4">
            <!-- Page Header -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Daily Flock Report</h1>
            </div>

            <!-- Filters -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <!-- Filters - In One Row -->
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-end">
                        <!-- Company Dropdown -->
                        <div class="min-w-[180px]">
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                            <select
                                v-model="state.company_id"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option value="">Select Company</option>
                                <option v-for="company in props.companies" :key="company.id" :value="company.id">
                                    {{ company.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Project Dropdown -->
                        <div class="min-w-[180px]">
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Project</label>
                            <select
                                v-model="state.project_id"
                                :disabled="!state.company_id"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 disabled:bg-gray-100 disabled:cursor-not-allowed disabled:text-gray-500"
                            >
                                <option value="">{{ state.company_id ? 'Select Project' : 'Select Company First' }}</option>
                                <option v-for="project in filteredProjects" :key="project.id" :value="project.id">
                                    {{ project.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Flock Searchable Dropdown -->
                        <div class="min-w-[180px] relative flock-dropdown-container">
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Flock</label>
                            <div class="relative">
                                <input
                                    v-model="state.flock_search"
                                    @focus="toggleFlockDropdown"
                                    @input="state.show_flock_dropdown = true"
                                    type="text"
                                    placeholder="Search flock..."
                                    class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 pr-8 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                                />
                    <button
                        type="button"
                                    @click="toggleFlockDropdown"
                                    class="absolute inset-y-0 right-0 flex items-center pr-2"
                    >
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                    </button>
                </div>
                            
                            <!-- Dropdown Options -->
                            <div
                                v-if="state.show_flock_dropdown"
                                class="absolute z-10 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
                            >
                                <div class="py-1">
                                    <div
                                        v-for="flock in filteredFlocks"
                                        :key="flock.id"
                                        @click="selectFlock(flock)"
                                        class="px-3 py-2 text-sm cursor-pointer hover:bg-gray-100 flex justify-between items-center"
                                    >
                                        <span class="font-medium">{{ flock.code }}</span>
                                        <span class="text-gray-500 text-xs">{{ flock.name }}</span>
                                    </div>
                                    <div
                                        v-if="filteredFlocks.length === 0"
                                        class="px-3 py-2 text-sm text-gray-500"
                                    >
                                        No flocks found
                                    </div>
                                </div>
                </div>
            </div>

                    <!-- Date From -->
                        <div class="min-w-[150px] relative date-picker-container">
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">From</label>
                            <div class="relative">
                        <input
                                    :value="formatDate(state.date_from)"
                                    @click="toggleDateFromPicker"
                                    readonly
                                    type="text"
                                    placeholder="Select date"
                                    class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 pr-8 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 cursor-pointer"
                                />
                                <button
                                    type="button"
                                    @click="toggleDateFromPicker"
                                    class="absolute inset-y-0 right-0 flex items-center pr-2"
                                >
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                    </div>

                            <!-- Date From Picker -->
                            <div
                                v-if="state.show_date_from_picker"
                                class="absolute z-10 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
                            >
                                <div class="p-2">
                                    <div class="text-xs text-gray-500 mb-2 font-medium">Recent Dates</div>
                                    <div
                                        v-for="option in getDateOptions()"
                                        :key="option.value"
                                        @click="selectDateFrom(option.value)"
                                        class="px-3 py-2 text-sm cursor-pointer hover:bg-gray-100 rounded flex justify-between items-center"
                                        :class="{ 'bg-blue-50 text-blue-700': option.isToday }"
                                    >
                                        <span>{{ option.label }}</span>
                                        <span v-if="option.isToday" class="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded">Today</span>
                                    </div>
                                </div>
                            </div>
                    </div>

                        <!-- Date To -->
                        <div class="min-w-[150px] relative date-picker-container">
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">To</label>
                            <div class="relative">
                        <input
                                    :value="formatDate(state.date_to)"
                                    @click="toggleDateToPicker"
                                    readonly
                            type="text"
                                    placeholder="Select date"
                                    class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 pr-8 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 cursor-pointer"
                                />
                                <button
                                    type="button"
                                    @click="toggleDateToPicker"
                                    class="absolute inset-y-0 right-0 flex items-center pr-2"
                                >
                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </button>
                    </div>

                            <!-- Date To Picker -->
                            <div
                                v-if="state.show_date_to_picker"
                                class="absolute z-10 mt-1 w-full bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto"
                            >
                                <div class="p-2">
                                    <div class="text-xs text-gray-500 mb-2 font-medium">Recent Dates</div>
                                    <div
                                        v-for="option in getDateOptions()"
                                        :key="option.value"
                                        @click="selectDateTo(option.value)"
                                        class="px-3 py-2 text-sm cursor-pointer hover:bg-gray-100 rounded flex justify-between items-center"
                                        :class="{ 'bg-blue-50 text-blue-700': option.isToday }"
                                    >
                                        <span>{{ option.label }}</span>
                                        <span v-if="option.isToday" class="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded">Today</span>
                                    </div>
                                </div>
                            </div>
                </div>

                        <!-- Action Button -->
                        <div class="flex gap-2">
                    <button
                        type="button"
                                class="rounded-md bg-black px-4 py-2 text-sm font-semibold text-white shadow-lg hover:bg-gray-800 focus:ring-2 focus:ring-gray-500 focus:outline-none transform hover:scale-105 transition-all duration-200"
                        @click="applyFilters"
                    >
                        Apply
                    </button>
                        </div>
                    </div>

                    <!-- Export Actions -->
                    <div class="flex gap-2">
                        <!-- Export Dropdown -->
                        <div class="relative">
                            <select
                                @change="handleExportChange"
                                class="appearance-none rounded-md border border-gray-300 bg-black px-4 py-2 pr-8 text-sm text-white shadow-lg focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:outline-none transform hover:scale-105 transition-all duration-200"
                            >
                                <option value="" class="bg-black text-white">Export Options</option>
                                <option value="pdf" class="bg-black text-white">Export PDF</option>
                                <option value="excel" class="bg-black text-white">Export Excel</option>
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report (print friendly) -->
            <div class="report-wrapper">
                <!-- Header Section -->
                <div class="report-header">
                    <div class="company-info">
                        <h1>Provita Chicks Limited-01</h1>
                        <p>Jahazmara, Noakhali.</p>
                        <h2>Daily Flock Report</h2>
                    </div>
                    
                    <!-- Environmental Conditions -->
                    <div class="environmental-conditions">
                        <div class="env-table">
                            <div class="env-header">Outside Tem (°C)</div>
                            <div class="env-data">
                                <div>Max: 0.0</div>
                                <div>Min: 0.0</div>
                            </div>
                        </div>
                        <div class="env-table">
                            <div class="env-header">Inside Tem (°C)</div>
                            <div class="env-data">
                                <div>Max: 31.0</div>
                                <div>Min: 29.0</div>
                            </div>
                        </div>
                        <div class="env-table">
                            <div class="env-header">Humidity %</div>
                            <div class="env-data">
                                <div>Max: 0</div>
                                <div>Min: 0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Body Weight Table -->
                <div class="body-weight-section">
                    <div class="body-weight-table">
                        <div class="bw-header">Body Weight (gm)</div>
                        <table class="bw-table">
                    <thead>
                        <tr>
                                    <th>Batch No</th>
                                    <th>Age (wks)</th>
                                    <th colspan="3">Female</th>
                                    <th colspan="3">Male</th>
                        </tr>
                        <tr>
                                    <th></th>
                                    <th></th>
                                    <th>Act</th>
                                    <th>Std</th>
                                    <th>Uni (%)</th>
                                    <th>Act</th>
                                    <th>Std</th>
                                    <th>Uni (%)</th>
                        </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>A</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>0</td>
                                </tr>
                                <tr>
                                    <td>B</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>0</td>
                                </tr>
                                <tr>
                                    <td>C</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>0</td>
                        </tr>
                        <tr>
                                    <td>D</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>0</td>
                                    <td>0</td>
                        </tr>
                        <tr>
                                    <td>E</td>
                                    <td>30+7</td>
                                    <td>1185</td>
                                    <td>1200</td>
                                    <td>66</td>
                                    <td>1847</td>
                                    <td>1780</td>
                                    <td>0.0</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Main Central Table -->
                <div class="main-table-section">
                    <table class="main-table">
                        <thead>
                            <tr>
                                <th rowspan="2">Breed</th>
                                <th rowspan="2">IR</th>
                                <th rowspan="2">Mail Flock No</th>
                                <th rowspan="2">Shed No:01</th>
                                <th rowspan="2">Date 22-Aug-2025</th>
                                <th colspan="5">Mortality</th>
                                <th colspan="5">Sold/Cull</th>
                                <th colspan="2">Closing Birds No.</th>
                                <th colspan="3">Production Egg</th>
                                <th colspan="3">Hatching Egg</th>
                                <th colspan="2">Egg Wt. (gm)</th>
                                <th colspan="4">Feed Consumption</th>
                                <th rowspan="2">Light (hrs)</th>
                                <th rowspan="2">Water Intake (Lit)</th>
                                <th colspan="2">FFT</th>
                                <th rowspan="2">Type of Feed</th>
                        </tr>
                        <tr>
                                <th>Opening Birds No.</th>
                                <th>Daily (F)</th>
                                <th>Cum (F)</th>
                                <th>Daily (M)</th>
                                <th>Cum (M)</th>
                                <th>Daily (F)</th>
                                <th>Cum (F)</th>
                                <th>Daily (M)</th>
                                <th>Cum (M)</th>
                            <th>Female</th>
                            <th>Male</th>
                                <th>Qty</th>
                                <th>Act %</th>
                                <th>Std %</th>
                                <th>Qty</th>
                                <th>Act %</th>
                                <th>Std %</th>
                                <th>Act</th>
                                <th>Std</th>
                                <th>F. gm (F)</th>
                                <th>F. gm (M)</th>
                                <th>F. kg (F)</th>
                                <th>F. kg (M)</th>
                                <th>F</th>
                                <th>M</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Batch</td>
                                <td>Age (Wk)</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>A</td>
                                <td>36+2</td>
                                <td>10,200</td>
                                <td>5</td>
                                <td>80</td>
                                <td>2</td>
                                <td>35</td>
                                <td>3</td>
                                <td>40</td>
                                <td>1</td>
                                <td>10,195</td>
                                <td>3,560</td>
                                <td>2,500</td>
                                <td>85.0</td>
                                <td>86.0</td>
                                <td>2,300</td>
                                <td>78.0</td>
                                <td>80.0</td>
                                <td>65</td>
                                <td>70</td>
                                <td>720</td>
                                <td>115</td>
                                <td>16</td>
                                <td>1800</td>
                                <td>27</td>
                                <td>B</td>
                                <td>29</td>
                                <td>28</td>
                                <td>16</td>
                                <td>IR Developer +IR Grower</td>
                            </tr>
                            <tr>
                                <td>B</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                            </tr>
                            <tr>
                                <td>C</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                            </tr>
                            <tr>
                                <td>D</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                            </tr>
                            <tr>
                                <td>E</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                            </tr>
                            <tr class="total-row">
                                <td>AVG/Total</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Bottom Section Tables -->
                <div class="bottom-tables">
                    <!-- Medicine & Vaccine and Egg Quality Tables -->
                    <div class="medicine-egg-section">
                        <div class="medicine-table">
                            <div class="table-title">Medicine & Vaccine</div>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Batch</th>
                                        <th>Name</th>
                                        <th>Qty</th>
                                        <th>Name</th>
                                        <th>Qty</th>
                        </tr>
                    </thead>
                                <tbody>
                                    <tr>
                                        <td>A</td>
                                        <td>Tablet-1</td>
                                        <td>1000 ml</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>B</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>C</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>D</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>E</td>
                                        <td>Liquid Medication</td>
                                        <td>50 gm</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td>Total/Avg</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="egg-quality-table">
                            <div class="table-title">Egg Quality/Defect</div>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Double Yolk</th>
                                        <th>Double Yolk Broken</th>
                                        <th>Commercial A (wt Above 54)</th>
                                        <th>Commercial B (wt 45-54gm)</th>
                                        <th>Commercial C (wt 33-44gm)</th>
                                        <th>Commercial Broken</th>
                                        <th>Liquid</th>
                                        <th>Damage</th>
                                        <th>Total</th>
                                    </tr>
                                    <tr>
                                        <th>No</th>
                                        <th>%</th>
                                        <th>No</th>
                                        <th>%</th>
                                        <th>No</th>
                                        <th>%</th>
                                        <th>No</th>
                                        <th>%</th>
                                        <th>No</th>
                                        <th>%</th>
                                        <th>No</th>
                                        <th>%</th>
                                        <th>No</th>
                                        <th>%</th>
                                        <th>No</th>
                                        <th>%</th>
                                        <th>No</th>
                                        <th>%</th>
                        </tr>
                    </thead>
                    <tbody>
                                    <tr>
                                        <td>0</td>
                                        <td>0.0</td>
                                        <td>0</td>
                                        <td>0.0</td>
                                        <td>0</td>
                                        <td>0.0</td>
                                        <td>0</td>
                                        <td>0.0</td>
                                        <td>0</td>
                                        <td>0.0</td>
                                        <td>0</td>
                                        <td>0.0</td>
                                        <td>0</td>
                                        <td>0.0</td>
                                        <td>0</td>
                                        <td>0.0</td>
                                        <td>0</td>
                                        <td>0.0</td>
                        </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Mortality Details and Technical Information -->
                    <div class="mortality-tech-section">
                        <div class="mortality-details">
                            <div class="table-title">Mortality Details</div>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Batch</th>
                                        <th colspan="2">Destroy</th>
                                        <th colspan="2">Cull (Slaughter)</th>
                                        <th colspan="2">Non Laying</th>
                                        <th colspan="2">Medical</th>
                                        <th colspan="2">Rejects Birds</th>
                                        <th colspan="2">Sexing Error</th>
                                        <th colspan="2">Final Sold</th>
                                        <th colspan="2">Shortage</th>
                                        <th colspan="2">Excess</th>
                        </tr>
                                    <tr>
                                        <th></th>
                                        <th>F</th>
                                        <th>M</th>
                                        <th>F</th>
                                        <th>M</th>
                                        <th>F</th>
                                        <th>M</th>
                                        <th>F</th>
                                        <th>M</th>
                                        <th>F</th>
                                        <th>M</th>
                                        <th>F</th>
                                        <th>M</th>
                                        <th>F</th>
                                        <th>M</th>
                                        <th>F</th>
                                        <th>M</th>
                                        <th>F</th>
                                        <th>M</th>
                                    </tr>
                                </thead>
                    <tbody>
                                    <tr v-for="i in 5" :key="i">
                                        <td>A</td>
                                        <td>1</td>
                                        <td>2</td>
                                        <td>1</td>
                                        <td>2</td>
                                        <td>1</td>
                                        <td>2</td>
                                        <td>1</td>
                                        <td>2</td>
                                        <td>1</td>
                                        <td>2</td>
                                        <td>1</td>
                                        <td>2</td>
                                        <td>1</td>
                                        <td>2</td>
                                        <td>1</td>
                                        <td>2</td>
                                        <td>1</td>
                                        <td>2</td>
                                    </tr>
                                    <tr>
                                        <td>Total</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>10</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                        </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="technical-info">
                            <div class="table-title">Technical Information About Reject Egg</div>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Floor Egg</th>
                                        <th>Thin</th>
                                        <th>Misshape</th>
                                        <th>White Egg</th>
                                        <th>Dirty</th>
                                    </tr>
                                    <tr>
                                        <th>No</th>
                                        <th>%</th>
                                        <th>No</th>
                                        <th>%</th>
                                        <th>No</th>
                                        <th>%</th>
                                        <th>No</th>
                                        <th>%</th>
                                        <th>No</th>
                                        <th>%</th>
                        </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>0</td>
                                        <td>0.0</td>
                                        <td>0</td>
                                        <td>0.0</td>
                                        <td>0</td>
                                        <td>0.0</td>
                                        <td>0</td>
                                        <td>0.0</td>
                                        <td>0</td>
                                        <td>0.0</td>
                        </tr>
                    </tbody>
                </table>
                        </div>
                    </div>

                    <!-- Total Feed Summary -->
                    <div class="feed-summary">
                        <div class="table-title">Total Feed</div>
                        <div class="feed-content">
                            <div>IR Grower: 3073 kg</div>
                            <div>IR Developer: 1078 kg</div>
                            <div class="note-section">
                                <div>Note:</div>
                                <div class="note-box"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="report-footer">
                    <div class="signature-row">
                        <span>Prepared By</span>
                        <span>Security Officer</span>
                        <span>Store Incharge</span>
                        <span>Admin</span>
                        <span>Shed Incharge</span>
                        <span>Project Incharge</span>
                        <span>General Manager</span>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@page {
    size: A4 landscape;
    margin: 1cm;
}

.report-wrapper {
    background: #fff;
    color: #333;
    padding: 1cm;
    font-family: Arial, sans-serif;
    font-size: 8px;
    line-height: 1.2;
}

/* Header Section */
.report-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 10px;
}

.company-info {
    text-align: center;
    flex: 1;
}

.company-info h1 {
    font-size: 14px;
    font-weight: bold;
    margin: 0;
}

.company-info p {
    font-size: 10px;
    margin: 2px 0;
}

.company-info h2 {
    font-size: 12px;
    font-weight: bold;
    margin: 5px 0 0 0;
}

.environmental-conditions {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.env-table {
    border: 1px solid #000;
    width: 80px;
}

.env-header {
    background-color: #f0f0f0;
    font-weight: bold;
    text-align: center;
    padding: 2px;
    font-size: 7px;
}

.env-data {
    display: flex;
    justify-content: space-between;
    padding: 1px 3px;
    font-size: 7px;
}

/* Body Weight Section */
.body-weight-section {
    margin-bottom: 10px;
}

.body-weight-table {
    display: flex;
    align-items: flex-start;
    overflow: auto;
    max-height: 30vh;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.bw-header {
    writing-mode: vertical-rl;
    text-orientation: mixed;
    background-color: #f0f0f0;
    border: 1px solid #000;
    padding: 5px 2px;
    font-weight: bold;
    font-size: 9px;
    min-height: 100px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bw-table {
    border-collapse: collapse;
    border: 1px solid #000;
    margin-left: 2px;
    min-width: 400px; /* Ensure minimum width for horizontal scroll */
}

.bw-table th,
.bw-table td {
    border: 1px solid #000;
    padding: 4px 6px;
    text-align: center;
    font-size: 9px;
    white-space: nowrap; /* Prevent text wrapping */
    min-width: 50px; /* Minimum column width */
}

.bw-table th {
    background-color: #f0f0f0;
    font-weight: bold;
}

/* Main Table Section */
.main-table-section {
    margin-bottom: 10px;
    overflow: auto;
    max-height: 60vh;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.main-table {
    width: 100%;
    min-width: 1400px; /* Ensure minimum width for horizontal scroll */
    border-collapse: collapse;
    border: 1px solid #000;
    font-size: 10px;
}

.main-table th,
.main-table td {
    border: 1px solid #000;
    padding: 6px 8px;
    text-align: center;
    vertical-align: middle;
    white-space: nowrap; /* Prevent text wrapping */
    min-width: 80px; /* Minimum column width */
}

.main-table th {
    background-color: #f0f0f0;
    font-weight: bold;
    position: sticky;
    top: 0;
    z-index: 10;
}

.total-row {
    font-weight: bold;
    background-color: #f8f8f8;
}

/* Bottom Tables */
.bottom-tables {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.medicine-egg-section {
    display: flex;
    gap: 10px;
}

.medicine-table,
.egg-quality-table {
    flex: 1;
}

.mortality-tech-section {
    display: flex;
    gap: 10px;
}

.mortality-details,
.technical-info {
    flex: 1;
}

.feed-summary {
    width: 200px;
    margin-left: auto;
}

.table-title {
    writing-mode: vertical-rl;
    text-orientation: mixed;
    background-color: #f0f0f0;
    border: 1px solid #000;
    padding: 5px 2px;
    font-weight: bold;
    font-size: 8px;
    min-height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 2px;
}

.medicine-table,
.egg-quality-table,
.mortality-details,
.technical-info {
    display: flex;
    align-items: flex-start;
    overflow: auto;
    max-height: 40vh;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.medicine-table table,
.egg-quality-table table,
.mortality-details table,
.technical-info table {
    border-collapse: collapse;
    border: 1px solid #000;
    margin-left: 2px;
    font-size: 9px;
    min-width: 300px; /* Ensure minimum width for horizontal scroll */
}

.medicine-table table th,
.medicine-table table td,
.egg-quality-table table th,
.egg-quality-table table td,
.mortality-details table th,
.mortality-details table td,
.technical-info table th,
.technical-info table td {
    border: 1px solid #000;
    padding: 4px 6px;
    text-align: center;
    vertical-align: middle;
    white-space: nowrap; /* Prevent text wrapping */
    min-width: 60px; /* Minimum column width */
}

.medicine-table table th,
.egg-quality-table table th,
.mortality-details table th,
.technical-info table th {
    background-color: #f0f0f0;
    font-weight: bold;
}

.feed-content {
    border: 1px solid #000;
    padding: 5px;
    font-size: 8px;
}

.feed-content > div {
    margin-bottom: 3px;
}

.note-section {
    margin-top: 10px;
}

.note-box {
    border: 1px solid #000;
    height: 30px;
    margin-top: 2px;
}

/* Footer */
.report-footer {
    margin-top: 15px;
}

.signature-row {
    display: flex;
    justify-content: space-between;
    font-size: 8px;
    font-weight: bold;
}

/* General table styles */
table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto; /* Changed from fixed to auto for better responsiveness */
    page-break-inside: avoid;
}

th,
td {
    border: 1px solid #000;
    padding: 4px 6px; /* Increased padding for better readability */
    text-align: center;
    vertical-align: middle;
    word-wrap: break-word;
    overflow-wrap: break-word;
    font-size: 9px; /* Increased base font size */
}

th {
    background-color: #f0f0f0;
    font-weight: bold;
}

tr {
    page-break-inside: avoid;
}

/* Custom scrollbar styling */
.main-table-section::-webkit-scrollbar,
.body-weight-table::-webkit-scrollbar,
.medicine-table::-webkit-scrollbar,
.egg-quality-table::-webkit-scrollbar,
.mortality-details::-webkit-scrollbar,
.technical-info::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.main-table-section::-webkit-scrollbar-track,
.body-weight-table::-webkit-scrollbar-track,
.medicine-table::-webkit-scrollbar-track,
.egg-quality-table::-webkit-scrollbar-track,
.mortality-details::-webkit-scrollbar-track,
.technical-info::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.main-table-section::-webkit-scrollbar-thumb,
.body-weight-table::-webkit-scrollbar-thumb,
.medicine-table::-webkit-scrollbar-thumb,
.egg-quality-table::-webkit-scrollbar-thumb,
.mortality-details::-webkit-scrollbar-thumb,
.technical-info::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.main-table-section::-webkit-scrollbar-thumb:hover,
.body-weight-table::-webkit-scrollbar-thumb:hover,
.medicine-table::-webkit-scrollbar-thumb:hover,
.egg-quality-table::-webkit-scrollbar-thumb:hover,
.mortality-details::-webkit-scrollbar-thumb:hover,
.technical-info::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

@media screen {
    .report-wrapper {
        margin: 12px auto;
        box-shadow: 0 0 8px rgba(0, 0, 0, 0.12);
        max-width: 100%;
    }
}

@media print {
    body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>
