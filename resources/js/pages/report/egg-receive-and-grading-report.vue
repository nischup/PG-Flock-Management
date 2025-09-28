<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, reactive } from 'vue';

type Batch = {
    flock_no: string;
    breed: string;
    age: string;
    received_date: string;
    eggs: number | string;
    commercial_rejects: number | string;
    broken: number | string;
    liquid: number | string;
    damage: number | string;
    total: number | string;
    percent: number | string;
    hatchable_eggs: number | string;
    set_eggs: number | string;
    set_date: string;
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
    window.open(`/bird-transfer-receive-report/pdf${qs ? `?${qs}` : ''}`, '_blank');
};

const exportExcel = () => {
    const qs = buildQuery();
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
                <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Transfer & Receive Report</h1>
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
                    <div class="export-dropdown relative">
                        <button
                            type="button"
                            class="inline-flex items-center gap-2 rounded-md bg-gradient-to-r from-gray-800 to-black px-4 py-2 text-sm font-semibold text-white shadow-lg transition-all duration-200 hover:from-gray-700 hover:to-gray-900 focus:ring-2 focus:ring-gray-500 focus:outline-none"
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
                            class="ring-opacity-5 absolute right-0 z-10 mt-2 max-h-[80vh] w-[600px] origin-top-right overflow-y-auto rounded-md bg-white px-4 py-4 shadow-lg ring-1 ring-black focus:outline-none dark:bg-gray-700"
                        >
                            <!-- Export Buttons -->
                            <div class="mb-4 flex justify-between">
                                <button
                                    type="button"
                                    class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600"
                                    @click="
                                        exportPdf;
                                        state.showExportDropdown = false;
                                    "
                                >
                                    Export PDF
                                </button>
                                <button
                                    type="button"
                                    class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-600"
                                    @click="
                                        exportExcel;
                                        state.showExportDropdown = false;
                                    "
                                >
                                    Export Excel
                                </button>
                            </div>
                            <!-- Egg Grading Report Table (inside dropdown) -->
                            <div class="overflow-x-auto">
                                <table class="min-w-full border border-gray-300 text-sm dark:border-gray-600">
                                    <thead class="bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                                        <tr>
                                            <th class="border px-2 py-1">Sr. No.</th>
                                            <th class="border px-2 py-1">Shed No.</th>
                                            <th class="border px-2 py-1">MC</th>
                                            <th class="border px-2 py-1">Total Eggs Received</th>
                                            <th class="border px-2 py-1">Graded Eggs</th>
                                            <th class="border px-2 py-1">Damage Eggs</th>
                                            <th class="border px-2 py-1">A</th>
                                            <th class="border px-2 py-1">B</th>
                                            <th class="border px-2 py-1">C</th>
                                            <th class="border px-2 py-1">Liquid</th>
                                            <th class="border px-2 py-1">Dirty</th>
                                            <th class="border px-2 py-1">Cracked</th>
                                            <th class="border px-2 py-1">Hatchable</th>
                                            <th class="border px-2 py-1">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(batch, index) in props.batches" :key="index">
                                            <td class="border px-2 py-1">{{ index + 1 }}</td>
                                            <td class="border px-2 py-1">{{ batch.flock_no }}</td>
                                            <td class="border px-2 py-1">{{ batch.breed }}</td>
                                            <td class="border px-2 py-1">{{ batch.eggs }}</td>
                                            <td class="border px-2 py-1">{{ batch.hatchable_eggs }}</td>
                                            <td class="border px-2 py-1">{{ batch.damage }}</td>
                                            <td class="border px-2 py-1">{{ batch.broken }}</td>
                                            <td class="border px-2 py-1">{{ batch.liquid }}</td>
                                            <td class="border px-2 py-1">{{ batch.total }}</td>
                                            <td class="border px-2 py-1">{{ batch.percent }}</td>
                                            <td class="border px-2 py-1">{{ batch.hatchable_eggs }}</td>
                                            <td class="border px-2 py-1">{{ batch.set_eggs }}</td>
                                            <td class="border px-2 py-1">{{ batch.set_date }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Table (Main report) -->
            <div class="report-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Flock No</th>
                            <th>Breed</th>
                            <th>Age (Wks)</th>
                            <th>Received Date</th>
                            <th>Eggs</th>
                            <th>Commercial Rejects</th>
                            <th>Broken</th>
                            <th>Liquid</th>
                            <th>Damage</th>
                            <th>Total</th>
                            <th>%</th>
                            <th>Hatchable Eggs</th>
                            <th>Set Eggs</th>
                            <th>Set Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="batch in props.batches" :key="batch.batch_no">
                            <td>{{ batch.flock_no }}</td>
                            <td>{{ batch.breed }}</td>
                            <td>{{ batch.age }}</td>
                            <td>{{ batch.received_date }}</td>
                            <td>{{ batch.eggs }}</td>
                            <td>{{ batch.commercial_rejects }}</td>
                            <td>{{ batch.broken }}</td>
                            <td>{{ batch.liquid }}</td>
                            <td>{{ batch.damage }}</td>
                            <td>{{ batch.total }}</td>
                            <td>{{ batch.percent }}</td>
                            <td>{{ batch.hatchable_eggs }}</td>
                            <td>{{ batch.set_eggs }}</td>
                            <td>{{ batch.set_date }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Footer -->
                <div class="footer">
                    <p>Prepared by: {{ props.from_company }} | Grading Date: {{ new Date().toLocaleDateString() }}</p>
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
    font-size: 9.5px;
}

.report-wrapper table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 0.5em;
}

.report-wrapper th,
.report-wrapper td {
    border: 1px solid #999;
    padding: 5px;
    text-align: center;
}

.report-wrapper th {
    background-color: #f0f0f0;
    font-weight: bold;
}

.report-wrapper .footer {
    margin-top: 1em;
    font-size: 9.5px;
    text-align: right;
}
</style>
