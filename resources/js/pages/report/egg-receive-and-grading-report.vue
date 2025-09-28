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
                        </div>
                    </div>
                </div>
            </div>

            <!-- Report Table (Main report) -->
            <div class="min-h-screen bg-background p-6">
                <div class="mx-auto max-w-7xl">
                    <div class="mb-6">
                        <h1 class="mb-4 text-center text-xl font-bold text-foreground">Egg Received and Grading Report Of Farm-PHL-1</h1>
                        <div class="mb-6 grid grid-cols-3 gap-4 text-xs">
                            <div class="space-y-1">
                                <div class="flex justify-between"><span class="font-medium">Received Date:</span><span>24.05.2025</span></div>
                                <div class="flex justify-between"><span class="font-medium">HF Fm Date:</span><span>22.25.05.2025</span></div>
                                <div class="flex justify-between"><span class="font-medium">Report No:</span><span>285</span></div>
                                <div class="flex justify-between"><span class="font-medium">Stock Day Report Set:</span><span>6</span></div>
                            </div>
                            <div class="space-y-1">
                                <div class="flex justify-between"><span class="font-medium">Unloading Time Start:</span><span>9:00 Am</span></div>
                                <div class="flex justify-between"><span class="font-medium">Unloading Time Finish:</span><span>9:30 Am</span></div>
                                <div class="flex justify-between"><span class="font-medium">Total Time Elapsed (Hour):</span><span>0.30</span></div>
                                <div class="flex justify-between"><span class="font-medium">Unloading Day Name:</span><span>Shribnoddin</span></div>
                            </div>
                            <div class="space-y-1">
                                <div class="flex justify-between"><span class="font-medium">Driver Name:</span><span>Sujon</span></div>
                                <div class="flex justify-between"><span class="font-medium">Vehicle No:</span><span>J2-H-01</span></div>
                                <div class="flex justify-between"><span class="font-medium">Mobile No:</span><span>1865972468</span></div>
                                <div class="flex justify-between"><span class="font-medium">Challan No:</span><span>3936</span></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 text-xs">
                            <div class="flex justify-between"><span class="font-medium">Egg Grading Date:</span><span>24.05.2025</span></div>
                            <div class="flex justify-between"><span class="font-medium">Grading Start Time:</span><span>3:00 Am</span></div>
                            <div class="flex justify-between"><span class="font-medium">Grading Finish Time:</span><span>6:00 Pm</span></div>
                        </div>

                        <div class="mt-1 grid grid-cols-3 gap-4 text-xs">
                            <div></div>
                            <div class="flex justify-between"><span class="font-medium">Total Time Elapsed:</span><span>3:00</span></div>
                            <div></div>
                        </div>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="report-table min-w-full border border-black text-xs">
                        <thead class="bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-200">
                            <tr>
                                <th rowspan="3" class="border border-black px-2 py-1">Shed<br />No.</th>
                                <th colspan="4" class="border border-black px-2 py-1">Stock Day Report Set</th>
                                <th rowspan="3" class="border border-black px-2 py-1">Received<br />Egg</th>
                                <th colspan="6" class="border border-black px-2 py-1">Commercial/Reject Details</th>
                                <th colspan="2" class="border border-black px-2 py-1">Hatchable</th>
                                <th colspan="4" class="border border-black px-2 py-1">Set Egg</th>
                                <th rowspan="3" class="border border-black px-2 py-1">Closing<br />Balance</th>
                                <th rowspan="3" class="border border-black px-2 py-1">Remarks</th>
                            </tr>
                            <tr>
                                <th class="border border-black px-2 py-1">Flock No</th>
                                <th class="border border-black px-2 py-1">Breed</th>
                                <th class="border border-black px-2 py-1">Age<br />(Wks)</th>
                                <th class="border border-black px-2 py-1">Pro.<br />Date</th>
                                <th class="border border-black px-2 py-1">Com</th>
                                <th class="border border-black px-2 py-1">Broken</th>
                                <th class="border border-black px-2 py-1">Liquid</th>
                                <th class="border border-black px-2 py-1">Damage</th>
                                <th class="border border-black px-2 py-1">Total</th>
                                <th class="border border-black px-2 py-1">%</th>
                                <th class="border border-black px-2 py-1">Egg</th>
                                <th class="border border-black px-2 py-1">Total Hatchable<br />Eggs</th>
                                <th class="border border-black px-2 py-1">Quantity-1</th>
                                <th class="border border-black px-2 py-1">Sec.<br />Date</th>
                                <th class="border border-black px-2 py-1">Quantity-2</th>
                                <th class="border border-black px-2 py-1">Sec<br />Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="row-even">
                                <td class="border border-black px-2 py-1">50.B</td>
                                <td class="border border-black px-2 py-1">4</td>
                                <td class="border border-black px-2 py-1">MC</td>
                                <td class="border border-black px-2 py-1">77.4</td>
                                <td class="border border-black px-2 py-1">23.05</td>
                                <td class="border border-black px-2 py-1">1,800</td>
                                <td class="border border-black px-2 py-1">0</td>
                                <td class="border border-black px-2 py-1">4</td>
                                <td class="border border-black px-2 py-1">2</td>
                                <td class="border border-black px-2 py-1">6</td>
                                <td class="border border-black px-2 py-1">12</td>
                                <td class="border border-black px-2 py-1">0.67</td>
                                <td class="border border-black px-2 py-1">1,788</td>
                                <td class="border border-black px-2 py-1">1,788</td>
                                <td class="border border-black px-2 py-1">1,788</td>
                                <td class="border border-black px-2 py-1">28.05.2025</td>
                                <td class="border border-black px-2 py-1">123</td>
                                <td class="border border-black px-2 py-1">123</td>
                                <td class="border border-black px-2 py-1">0</td>
                                <td class="border border-black px-2 py-1">123</td>
                            </tr>
                            <tr class="row-even">
                                <td class="border border-black px-2 py-1">50.B</td>
                                <td class="border border-black px-2 py-1">4</td>
                                <td class="border border-black px-2 py-1">MC</td>
                                <td class="border border-black px-2 py-1">77.4</td>
                                <td class="border border-black px-2 py-1">23.05</td>
                                <td class="border border-black px-2 py-1">1,800</td>
                                <td class="border border-black px-2 py-1">0</td>
                                <td class="border border-black px-2 py-1">4</td>
                                <td class="border border-black px-2 py-1">2</td>
                                <td class="border border-black px-2 py-1">6</td>
                                <td class="border border-black px-2 py-1">12</td>
                                <td class="border border-black px-2 py-1">0.67</td>
                                <td class="border border-black px-2 py-1">1,788</td>
                                <td class="border border-black px-2 py-1">1,788</td>
                                <td class="border border-black px-2 py-1">1,788</td>
                                <td class="border border-black px-2 py-1">28.05.2025</td>
                                <td class="border border-black px-2 py-1">123</td>
                                <td class="border border-black px-2 py-1">123</td>
                                <td class="border border-black px-2 py-1">0</td>
                                <td class="border border-black px-2 py-1">123</td>
                            </tr>
                            <tr class="row-even">
                                <td class="border border-black px-2 py-1">50.B</td>
                                <td class="border border-black px-2 py-1">4</td>
                                <td class="border border-black px-2 py-1">MC</td>
                                <td class="border border-black px-2 py-1">77.4</td>
                                <td class="border border-black px-2 py-1">23.05</td>
                                <td class="border border-black px-2 py-1">1,800</td>
                                <td class="border border-black px-2 py-1">0</td>
                                <td class="border border-black px-2 py-1">4</td>
                                <td class="border border-black px-2 py-1">2</td>
                                <td class="border border-black px-2 py-1">6</td>
                                <td class="border border-black px-2 py-1">12</td>
                                <td class="border border-black px-2 py-1">0.67</td>
                                <td class="border border-black px-2 py-1">1,788</td>
                                <td class="border border-black px-2 py-1">1,788</td>
                                <td class="border border-black px-2 py-1">1,788</td>
                                <td class="border border-black px-2 py-1">28.05.2025</td>
                                <td class="border border-black px-2 py-1">123</td>
                                <td class="border border-black px-2 py-1">123</td>
                                <td class="border border-black px-2 py-1">0</td>
                                <td class="border border-black px-2 py-1">123</td>
                            </tr>
                            <!-- Repeat for other rows... -->
                        </tbody>
                    </table>
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
