<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';

type FlockData = {
    flock_no: string;
    breed: string;
    age_wks: string;
    pro_date: string;
    received_egg: number;
    commercial: number;
    broken: number;
    liquid: number;
    damage: number;
    total_reject: number;
    reject_percent: number;
    hatchable_egg: number;
    total_hatchable: number;
    set_egg_qty1: number;
    set_date1: string;
    set_egg_qty2: number;
    set_date2: string;
    closing: number;
    remarks: string;
};

type SubtotalData = {
    received_egg: number;
    commercial: number;
    broken: number;
    liquid: number;
    damage: number;
    total_reject: number;
    reject_percent: number;
    hatchable_egg: number;
    total_hatchable: number;
    set_egg_qty1: number;
    set_date1: string;
    set_egg_qty2: number;
    set_date2: string;
    closing: number;
};

type ShedData = {
    shed_no: string;
    flocks: FlockData[];
    subtotal: SubtotalData;
};

type GrandTotalData = {
    received_egg: number;
    commercial: number;
    broken: number;
    liquid: number;
    damage: number;
    total_reject: number;
    reject_percent: number;
    hatchable_egg: number;
    total_hatchable: number;
    set_egg_qty1: number;
    set_date1: string;
    set_egg_qty2: number;
    set_date2: string;
    closing: number;
};

type ReportData = {
    report_title: string;
    received_date: string;
    he_pro_date: string;
    report_no: string;
    stock_day_before_set: string;
    unloading_time_start: string;
    unloading_time_finish: string;
    total_time_elapsed_unloading: string;
    driver_name: string;
    vehicle_no: string;
    mobile_no: string;
    unloading_sup_name: string;
    challan_no: string;
    egg_grading_date: string;
    grading_start_time: string;
    grading_finish_time: string;
    total_time_elapsed_grading: string;
};

const props = defineProps<{
    reportData: ReportData;
    sheds: ShedData[];
    grandTotal: GrandTotalData;
}>();

const formatNumber = (num: number): string => {
    return num.toLocaleString();
};

const formatPercent = (num: number): string => {
    return num.toFixed(2);
};

const printReport = () => {
    window.print();
};
</script>

<template>
    <Head title="Egg Receive and Grading Report" />

    <AppLayout
        :breadcrumbs="[
            { title: 'Reports', href: '#' },
            { title: 'Egg Receive and Grading Report', href: '/egg-receive-and-grading-report' },
        ]"
    >
        <div class="space-y-4">
            <!-- Page Header -->
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between print:hidden">
                <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Egg Receive and Grading Report</h1>
                <button
                    @click="printReport"
                    class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print Report
                </button>
            </div>

            <!-- Report Content -->
            <div class="min-h-screen bg-white p-6">
                <div class="mx-auto max-w-7xl">
                    <!-- Report Header -->
                    <div class="mb-6">
                        <h1 class="mb-4 text-center text-xl font-bold text-black">{{ reportData.report_title }}</h1>
                        
                        <!-- First Row of Header Info -->
                        <div class="mb-4 grid grid-cols-3 gap-4 text-xs">
                            <div class="space-y-1">
                                <div class="flex justify-between">
                                    <span class="font-medium">Received Date:</span>
                                    <span>{{ reportData.received_date }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">HE Pro. Date:</span>
                                    <span>{{ reportData.he_pro_date }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">Report No:</span>
                                    <span>{{ reportData.report_no }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">Stock Day Before Set:</span>
                                    <span>{{ reportData.stock_day_before_set }}</span>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <div class="flex justify-between">
                                    <span class="font-medium">Unloading Time Start:</span>
                                    <span>{{ reportData.unloading_time_start }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">Unloading Time Finish:</span>
                                    <span>{{ reportData.unloading_time_finish }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">Total Time Elapsed (Hour) for Unloading:</span>
                                    <span>{{ reportData.total_time_elapsed_unloading }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">Unloading Sup. Name:</span>
                                    <span>{{ reportData.unloading_sup_name }}</span>
                                </div>
                            </div>
                            <div class="space-y-1">
                                <div class="flex justify-between">
                                    <span class="font-medium">Driver Name:</span>
                                    <span>{{ reportData.driver_name }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">Vehicle No.:</span>
                                    <span>{{ reportData.vehicle_no }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">Mobile No.:</span>
                                    <span>{{ reportData.mobile_no }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">Challan No.:</span>
                                    <span>{{ reportData.challan_no }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Second Row of Header Info -->
                        <div class="grid grid-cols-3 gap-4 text-xs">
                            <div class="flex justify-between">
                                <span class="font-medium">Egg Grading Date:</span>
                                <span>{{ reportData.egg_grading_date }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Grading Start Time:</span>
                                <span>{{ reportData.grading_start_time }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="font-medium">Grading Finish Time:</span>
                                <span>{{ reportData.grading_finish_time }}</span>
                            </div>
                        </div>

                        <!-- Third Row of Header Info -->
                        <div class="mt-1 grid grid-cols-3 gap-4 text-xs">
                            <div></div>
                            <div class="flex justify-between">
                                <span class="font-medium">Total Time Elapsed (hr) for Grading:</span>
                                <span>{{ reportData.total_time_elapsed_grading }}</span>
                            </div>
                            <div></div>
                        </div>
                    </div>

                    <!-- Main Report Table -->
                    <div class="overflow-x-auto print:overflow-visible">
                        <table class="report-table min-w-full border border-black text-xs print:break-inside-avoid">
                            <thead class="bg-gray-200 text-gray-700 print:bg-gray-200">
                                <tr class="print:break-after-avoid">
                                    <th rowspan="3" class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Shed<br />No.</th>
                                    <th colspan="4" class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Stock Day Report Set</th>
                                    <th rowspan="3" class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Received<br />Egg</th>
                                    <th colspan="6" class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Commercial/Reject Details</th>
                                    <th colspan="2" class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Hatchable</th>
                                    <th colspan="4" class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Set Egg</th>
                                    <th rowspan="3" class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Closing<br />Balance</th>
                                    <th rowspan="3" class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Remarks</th>
                                </tr>
                                <tr class="print:break-after-avoid">
                                    <th class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Flock No</th>
                                    <th class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Breed</th>
                                    <th class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Age<br />(Wks)</th>
                                    <th class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Pro.<br />Date</th>
                                    <th class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Com</th>
                                    <th class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Broken</th>
                                    <th class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Liquid</th>
                                    <th class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Damage</th>
                                    <th class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Total</th>
                                    <th class="border border-black px-2 py-1 text-center print:px-1 print:py-1">%</th>
                                    <th class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Egg</th>
                                    <th class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Total Hatchable<br />Eggs</th>
                                    <th class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Quantity-1</th>
                                    <th class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Set.<br />Date</th>
                                    <th class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Quantity-2</th>
                                    <th class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Shed Data -->
                                <template v-for="shed in sheds" :key="shed.shed_no">
                                    <!-- Flock Rows -->
                                    <template v-for="(flock, index) in shed.flocks" :key="`${shed.shed_no}-${flock.flock_no}`">
                                        <tr :class="index % 2 === 0 ? 'bg-white print:bg-white' : 'bg-gray-50 print:bg-gray-50'">
                                            <td v-if="index === 0" :rowspan="shed.flocks.length" class="border border-black px-2 py-1 text-center font-medium print:px-1 print:py-1">{{ shed.shed_no }}</td>
                                            <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ flock.flock_no }}</td>
                                            <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ flock.breed }}</td>
                                            <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ flock.age_wks }}</td>
                                            <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ flock.pro_date }}</td>
                                            <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ formatNumber(flock.received_egg) }}</td>
                                            <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ flock.commercial }}</td>
                                            <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ flock.broken }}</td>
                                            <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ flock.liquid }}</td>
                                            <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ flock.damage }}</td>
                                            <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ flock.total_reject }}</td>
                                            <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ formatPercent(flock.reject_percent) }}</td>
                                            <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ formatNumber(flock.hatchable_egg) }}</td>
                                            <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ formatNumber(flock.total_hatchable) }}</td>
                                            <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ formatNumber(flock.set_egg_qty1) }}</td>
                                            <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ flock.set_date1 }}</td>
                                            <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ formatNumber(flock.set_egg_qty2) }}</td>
                                            <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ flock.set_date2 }}</td>
                                            <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ flock.closing }}</td>
                                            <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ flock.remarks }}</td>
                                        </tr>
                                    </template>
                                    
                                    <!-- Subtotal Row -->
                                    <tr class="bg-gray-100 font-medium print:bg-gray-100 print:break-after-avoid">
                                        <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Sub Total</td>
                                        <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1"></td>
                                        <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1"></td>
                                        <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1"></td>
                                        <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ formatNumber(shed.subtotal.received_egg) }}</td>
                                        <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ shed.subtotal.commercial }}</td>
                                        <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ shed.subtotal.broken }}</td>
                                        <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ shed.subtotal.liquid }}</td>
                                        <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ shed.subtotal.damage }}</td>
                                        <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ shed.subtotal.total_reject }}</td>
                                        <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ formatPercent(shed.subtotal.reject_percent) }}</td>
                                        <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ formatNumber(shed.subtotal.hatchable_egg) }}</td>
                                        <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ formatNumber(shed.subtotal.total_hatchable) }}</td>
                                        <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ formatNumber(shed.subtotal.set_egg_qty1) }}</td>
                                        <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ shed.subtotal.set_date1 }}</td>
                                        <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ formatNumber(shed.subtotal.set_egg_qty2) }}</td>
                                        <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ shed.subtotal.set_date2 }}</td>
                                        <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ shed.subtotal.closing }}</td>
                                        <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1"></td>
                                    </tr>
                                </template>

                                <!-- Grand Total Row -->
                                <tr class="bg-gray-200 font-bold print:bg-gray-200 print:break-before-avoid">
                                    <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">Total</td>
                                    <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1"></td>
                                    <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1"></td>
                                    <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1"></td>
                                    <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ formatNumber(grandTotal.received_egg) }}</td>
                                    <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ grandTotal.commercial }}</td>
                                    <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ grandTotal.broken }}</td>
                                    <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ grandTotal.liquid }}</td>
                                    <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ grandTotal.damage }}</td>
                                    <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ grandTotal.total_reject }}</td>
                                    <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ formatPercent(grandTotal.reject_percent) }}</td>
                                    <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ formatNumber(grandTotal.hatchable_egg) }}</td>
                                    <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ formatNumber(grandTotal.total_hatchable) }}</td>
                                    <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ formatNumber(grandTotal.set_egg_qty1) }}</td>
                                    <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ grandTotal.set_date1 }}</td>
                                    <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ formatNumber(grandTotal.set_egg_qty2) }}</td>
                                    <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ grandTotal.set_date2 }}</td>
                                    <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1">{{ grandTotal.closing }}</td>
                                    <td class="border border-black px-2 py-1 text-center print:px-1 print:py-1"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Percentages -->
                    <div class="mt-4 text-xs print:mt-2 print:text-xs">
                        <div class="grid grid-cols-6 gap-4 print:grid-cols-6 print:gap-2">
                            <div class="flex justify-between print:justify-between">
                                <span class="font-medium print:font-medium">Com:</span>
                                <span class="print:font-medium">{{ formatPercent(grandTotal.commercial / grandTotal.received_egg * 100) }}%</span>
                            </div>
                            <div class="flex justify-between print:justify-between">
                                <span class="font-medium print:font-medium">Broken:</span>
                                <span class="print:font-medium">{{ formatPercent(grandTotal.broken / grandTotal.received_egg * 100) }}%</span>
                            </div>
                            <div class="flex justify-between print:justify-between">
                                <span class="font-medium print:font-medium">Liquid:</span>
                                <span class="print:font-medium">{{ formatPercent(grandTotal.liquid / grandTotal.received_egg * 100) }}%</span>
                            </div>
                            <div class="flex justify-between print:justify-between">
                                <span class="font-medium print:font-medium">Damage:</span>
                                <span class="print:font-medium">{{ formatPercent(grandTotal.damage / grandTotal.received_egg * 100) }}%</span>
                            </div>
                            <div class="flex justify-between print:justify-between">
                                <span class="font-medium print:font-medium">Total (Reject):</span>
                                <span class="print:font-medium">{{ formatPercent(grandTotal.total_reject / grandTotal.received_egg * 100) }}%</span>
                            </div>
                            <div class="flex justify-between print:justify-between">
                                <span class="font-medium print:font-medium">Hatchable Egg:</span>
                                <span class="print:font-medium">{{ formatPercent(grandTotal.hatchable_egg / grandTotal.received_egg * 100) }}%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Print Styles */
@page {
    size: A4 landscape;
    margin: 0.5cm;
}

@media print {
    body {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .print\\:hidden {
        display: none !important;
    }
    
    .print\\:block {
        display: block !important;
    }
    
    .print\\:text-xs {
        font-size: 0.75rem !important;
        line-height: 1rem !important;
    }
    
    .print\\:p-2 {
        padding: 0.5rem !important;
    }
    
    .print\\:py-1 {
        padding-top: 0.25rem !important;
        padding-bottom: 0.25rem !important;
    }
    
    .print\\:px-1 {
        padding-left: 0.25rem !important;
        padding-right: 0.25rem !important;
    }
    
    .print\\:text-center {
        text-align: center !important;
    }
    
    .print\\:font-bold {
        font-weight: 700 !important;
    }
    
    .print\\:font-medium {
        font-weight: 500 !important;
    }
    
    .print\\:bg-gray-100 {
        background-color: #f3f4f6 !important;
    }
    
    .print\\:bg-gray-200 {
        background-color: #e5e7eb !important;
    }
    
    .print\\:border-black {
        border-color: #000000 !important;
    }
    
    .print\\:border {
        border-width: 1px !important;
    }
    
    .print\\:border-collapse {
        border-collapse: collapse !important;
    }
    
    .print\\:w-full {
        width: 100% !important;
    }
    
    .print\\:min-w-full {
        min-width: 100% !important;
    }
    
    .print\\:overflow-visible {
        overflow: visible !important;
    }
    
    .print\\:break-inside-avoid {
        break-inside: avoid !important;
    }
    
    .print\\:break-after-avoid {
        break-after: avoid !important;
    }
    
    .print\\:break-before-avoid {
        break-before: avoid !important;
    }
    
    /* Ensure table fits on page */
    table {
        width: 100% !important;
        max-width: 100% !important;
        font-size: 0.7rem !important;
        line-height: 1.1 !important;
    }
    
    th, td {
        padding: 2px 4px !important;
        border: 1px solid #000 !important;
        font-size: 0.7rem !important;
        line-height: 1.1 !important;
    }
    
    /* Hide elements that shouldn't print */
    .no-print {
        display: none !important;
    }
    
    /* Ensure proper page breaks */
    .page-break-before {
        page-break-before: always !important;
    }
    
    .page-break-after {
        page-break-after: always !important;
    }
    
    .page-break-inside-avoid {
        page-break-inside: avoid !important;
    }
}

/* Screen Styles */
table {
    border-collapse: collapse;
    font-family: Arial, sans-serif;
}

th, td {
    border: 1px solid #000;
    padding: 4px 8px;
    text-align: center;
    vertical-align: middle;
}

th {
    background-color: #f0f0f0;
    font-weight: bold;
}

tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

tbody tr:nth-child(odd) {
    background-color: #ffffff;
}

.font-medium {
    font-weight: 500;
}

.font-bold {
    font-weight: 700;
}

/* Print-specific table styling */
@media print {
    .report-table {
        font-size: 0.7rem !important;
        line-height: 1.1 !important;
    }
    
    .report-table th,
    .report-table td {
        padding: 2px 4px !important;
        font-size: 0.7rem !important;
        line-height: 1.1 !important;
    }
    
    .report-table th {
        background-color: #f0f0f0 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .report-table tbody tr:nth-child(even) {
        background-color: #f9f9f9 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .report-table tbody tr:nth-child(odd) {
        background-color: #ffffff !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>