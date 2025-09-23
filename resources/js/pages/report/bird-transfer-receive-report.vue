<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, onMounted, onUnmounted } from 'vue';

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
    filters?: {
        date_from?: string;
        date_to?: string;
        from_company_id?: string | number;
        to_company_id?: string | number;
        flock_id?: string | number;
    };
}>();

const state = reactive({
    date_from: props.filters?.date_from ?? '',
    date_to: props.filters?.date_to ?? '',
    from_company_id: props.filters?.from_company_id ?? '',
    to_company_id: props.filters?.to_company_id ?? '',
    flock_id: props.filters?.flock_id ?? '',
    showExportDropdown: false,
});

const applyFilters = () => {
    router.get(
        '/bird-transfer-receive-report',
        {
            date_from: state.date_from || undefined,
            date_to: state.date_to || undefined,
            from_company_id: state.from_company_id || undefined,
            to_company_id: state.to_company_id || undefined,
            flock_id: state.flock_id || undefined,
        },
        { preserveState: true, preserveScroll: true, replace: true },
    );
};

const buildQuery = () => {
    const q = new URLSearchParams();
    if (state.date_from) q.set('date_from', state.date_from as string);
    if (state.date_to) q.set('date_to', state.date_to as string);
    if (state.from_company_id) q.set('from_company_id', String(state.from_company_id));
    if (state.to_company_id) q.set('to_company_id', String(state.to_company_id));
    if (state.flock_id) q.set('flock_id', String(state.flock_id));
    return q.toString();
};

const exportPdf = () => {
    const qs = buildQuery();
    // opens a new tab with a streamed PDF
    window.open(`/bird-transfer-receive-report/pdf${qs ? `?${qs}` : ''}`, '_blank');
};

const exportExcel = () => {
    const qs = buildQuery();
    // triggers file download (CSV/Excel)
    window.location.href = `/bird-transfer-receive-report/excel${qs ? `?${qs}` : ''}`;
};

// Close dropdown when clicking outside
const handleClickOutside = (event: Event) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.export-dropdown')) {
        state.showExportDropdown = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <Head title="Transfer & Receive Report" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Reports', href: '#' },
            { title: 'Transfer & Receive', href: '/bird-transfer-receive-report' },
        ]"
    >
        <div class="space-y-4">
            <!-- Page Header -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Transfer &amp; Receive Report</h1>
            </div>

            <!-- Filters and Export Options -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                    <!-- Date Filters -->
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                        <!-- Date From -->
                        <div class="flex-1">
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">From</label>
                            <input
                                v-model="state.date_from"
                                type="date"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            />
                        </div>

                        <!-- Date To -->
                        <div class="flex-1">
                            <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">To</label>
                            <input
                                v-model="state.date_to"
                                type="date"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100"
                            />
                        </div>

                        <!-- Filter Button -->
                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-gray-800 focus:ring-2 focus:ring-gray-500 focus:outline-none"
                                @click="applyFilters"
                            >
                                Apply
                            </button>
                        </div>
                    </div>

                    <!-- Export Dropdown -->
                    <div class="relative export-dropdown">
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-md bg-gradient-to-r from-gray-800 to-black px-4 py-2 text-sm font-semibold text-white shadow-lg hover:from-gray-700 hover:to-gray-900 focus:ring-2 focus:ring-gray-500 focus:outline-none transition-all duration-200"
                            @click="state.showExportDropdown = !state.showExportDropdown"
                        >
                            Export
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div
                            v-if="state.showExportDropdown"
                            class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-gray-700"
                        >
                            <button
                                type="button"
                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600"
                                @click="exportPdf(); state.showExportDropdown = false"
                            >
                                Export PDF
                            </button>
                            <button
                                type="button"
                                class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600"
                                @click="exportExcel(); state.showExportDropdown = false"
                            >
                                Export Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report (print friendly) -->
            <div class="report-wrapper">
                <h1>MIS Report</h1>
                <h2>Provita Group – Birds Transfer & Receive Report</h2>

                <table>
                    <thead>
                        <tr>
                            <th rowspan="4">Delivery Date</th>
                            <th rowspan="4">Strain</th>
                            <th rowspan="4">Batch</th>
                            <th colspan="10">Transfer Farm</th>
                            <th colspan="6">Receive Farm</th>
                            <th colspan="2" rowspan="3">Actual Deviation<br />(Register vs Received)</th>
                            <th colspan="2" rowspan="3">Deviation<br />(Challan vs Received)</th>
                        </tr>
                        <tr>
                            <th colspan="10">{{ props.from_company }}</th>
                            <th colspan="6">{{ props.to_company }}</th>
                        </tr>
                        <tr>
                            <th colspan="2">Register</th>
                            <th colspan="2">ERP</th>
                            <th colspan="2">Challan</th>
                            <th colspan="2">Medical</th>
                            <th colspan="2">Deviation</th>

                            <th colspan="2">Received</th>
                            <th colspan="2">Mortality</th>
                            <th colspan="2">Total</th>
                        </tr>
                        <tr>
                            <!-- Transfer Farm -->
                            <th>Female</th>
                            <th>Male</th>
                            <th>Female</th>
                            <th>Male</th>
                            <th>Female</th>
                            <th>Male</th>
                            <th>Female</th>
                            <th>Male</th>
                            <th>Female</th>
                            <th>Male</th>

                            <!-- Receive Farm -->
                            <th>Female</th>
                            <th>Male</th>
                            <th>Female</th>
                            <th>Male</th>
                            <th>Female</th>
                            <th>Male</th>

                            <!-- Deviations -->
                            <th>Female</th>
                            <th>Male</th>
                            <th>Female</th>
                            <th>Male</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr v-if="!props.batches || props.batches.length === 0">
                            <td colspan="22">No data available.</td>
                        </tr>

                        <tr v-for="(batch, idx) in props.batches" :key="idx">
                            <td>{{ batch.delivery_date ?? 'N/A' }}</td>
                            <td>{{ batch.breed_type ?? 'N/A' }}</td>
                            <td>{{ batch.batch_no ?? 'N/A' }}</td>

                            <!-- Transfer Farm -->
                            <td>{{ batch.challan_female ?? '' }}</td>
                            <td>{{ batch.challan_male ?? '' }}</td>
                            <td>{{ batch.erp_female ?? '' }}</td>
                            <td>{{ batch.erp_male ?? '' }}</td>
                            <td>{{ batch.challan_female ?? '' }}</td>
                            <td>{{ batch.challan_male ?? '' }}</td>
                            <td>{{ batch.medical_female ?? '' }}</td>
                            <td>{{ batch.medical_male ?? '' }}</td>
                            <td>{{ batch.deviation_female ?? '' }}</td>
                            <td>{{ batch.deviation_male ?? '' }}</td>

                            <!-- Receive Farm -->
                            <td>{{ batch.received_female ?? '' }}</td>
                            <td>{{ batch.received_male ?? '' }}</td>
                            <td>{{ batch.mortality_female ?? '' }}</td>
                            <td>{{ batch.mortality_male ?? '' }}</td>
                            <td>{{ batch.total_received_female ?? '' }}</td>
                            <td>{{ batch.total_received_male ?? '' }}</td>

                            <!-- Deviations -->
                            <td>{{ batch.actual_deviation_female ?? '' }}</td>
                            <td>{{ batch.actual_deviation_male ?? '' }}</td>
                            <td>{{ batch.challan_deviation_female ?? '' }}</td>
                            <td>{{ batch.challan_deviation_male ?? '' }}</td>
                        </tr>

                        <!-- Totals -->
                        <tr v-if="props.batches && props.batches.length">
                            <td colspan="3"><strong>Total</strong></td>
                            <td>
                                <strong>{{ props.totals['register_female'] ?? '' }}</strong>
                            </td>
                            <td>
                                <strong>{{ props.totals['register_male'] ?? '' }}</strong>
                            </td>
                            <td>
                                <strong>{{ props.totals['erp_female'] ?? '' }}</strong>
                            </td>
                            <td>
                                <strong>{{ props.totals['erp_male'] ?? '' }}</strong>
                            </td>
                            <td>
                                <strong>{{ props.totals['challan_female'] ?? '' }}</strong>
                            </td>
                            <td>
                                <strong>{{ props.totals['challan_male'] ?? '' }}</strong>
                            </td>
                            <td>
                                <strong>{{ props.totals['medical_female'] ?? '' }}</strong>
                            </td>
                            <td>
                                <strong>{{ props.totals['medical_male'] ?? '' }}</strong>
                            </td>
                            <td>
                                <strong>{{ props.totals['deviation_female'] ?? '' }}</strong>
                            </td>
                            <td>
                                <strong>{{ props.totals['deviation_male'] ?? '' }}</strong>
                            </td>
                            <td>
                                <strong>{{ props.totals['received_female'] ?? '' }}</strong>
                            </td>
                            <td>
                                <strong>{{ props.totals['received_male'] ?? '' }}</strong>
                            </td>
                            <td>
                                <strong>{{ props.totals['mortality_female'] ?? '' }}</strong>
                            </td>
                            <td>
                                <strong>{{ props.totals['mortality_male'] ?? '' }}</strong>
                            </td>
                            <td>
                                <strong>{{ props.totals['total_received_female'] ?? '' }}</strong>
                            </td>
                            <td>
                                <strong>{{ props.totals['total_received_male'] ?? '' }}</strong>
                            </td>
                            <td>
                                <strong>{{ props.totals['actual_deviation_female'] ?? '' }}</strong>
                            </td>
                            <td>
                                <strong>{{ props.totals['actual_deviation_male'] ?? '' }}</strong>
                            </td>
                            <td>
                                <strong>{{ props.totals['challan_deviation_female'] ?? '' }}</strong>
                            </td>
                            <td>
                                <strong>{{ props.totals['challan_deviation_male'] ?? '' }}</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div class="footer">Prepared by: MIS Department, Provita Group</div>
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
    font-size: 9.5px;
}

.report-wrapper h1,
.report-wrapper h2 {
    text-align: center;
    margin: 0.3em 0;
}

.report-wrapper table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    margin-top: 0.5em;
    page-break-inside: avoid;
}

.report-wrapper th,
.report-wrapper td {
    border: 1px solid #999;
    padding: 2px;
    text-align: center;
    vertical-align: middle;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.report-wrapper th {
    background-color: #f0f0f0;
    font-weight: bold;
}

.report-wrapper tr {
    page-break-inside: avoid;
}

.report-wrapper .footer {
    margin-top: 1em;
    font-size: 9.5px;
    text-align: right;
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
