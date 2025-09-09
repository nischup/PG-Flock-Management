<template>
    <AppLayout title="PS Receive Details">
        <div class="space-y-6">
            <!-- Breadcrumb -->
            <nav class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400 mb-4">
                <Link :href="route('dashboard')" class="hover:text-gray-900 dark:hover:text-white transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                    </svg>
                </Link>
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <Link :href="route('ps-receive.index')" class="hover:text-gray-900 dark:hover:text-white transition-colors">
                    PS Receive
                </Link>
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-gray-900 dark:text-white font-medium">
                    PI-{{ psReceive.pi_no }}
                </span>
            </nav>

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">PS Receive Details</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-400">View detailed information about this PS Receive entry</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link
                        :href="route('ps-receive.index')"
                        class="group relative overflow-hidden rounded-md px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-gray-500"
                        style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);"
                    >
                        <span class="relative z-10 flex items-center gap-2">
                            <svg class="h-4 w-4 transition-transform duration-300 group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to List
                        </span>
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-20 group-hover:translate-x-full"></div>
                    </Link>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <!-- Left Column - Main Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Basic Information Card -->
                    <div class="rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Basic Information</h2>
                            <span 
                                class="inline-flex rounded-full px-3 py-1 text-xs font-medium"
                                :class="psReceive.shipment_type_id === 1 
                                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                                    : 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200'"
                            >
                                {{ psReceive.shipment_type_id === 1 ? 'Local' : 'Foreign' }}
                            </span>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">PI Number</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psReceive.pi_no }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">PI Date</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(psReceive.pi_date) }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Order Number</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psReceive.order_no || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Order Date</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psReceive.order_date ? formatDate(psReceive.order_date) : '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">LC Number</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psReceive.lc_no || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">LC Date</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psReceive.lc_date ? formatDate(psReceive.lc_date) : '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Supplier & Company Information -->
                    <div class="rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
                        <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Supplier & Company Information</h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psReceive.supplier?.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psReceive.company?.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Country of Origin</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psReceive.country?.name || 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Transport Type</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psReceive.transport_type || '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Breed Type Information -->
                    <div class="rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
                        <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Breed Type Information</h2>
                        <div class="flex flex-wrap gap-2">
                            <span 
                                v-for="breedName in breedTypeNames" 
                                :key="breedName"
                                class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200"
                            >
                                {{ breedName }}
                            </span>
                        </div>
                    </div>

                    <!-- Chick Counts Information -->
                    <div v-if="psReceive.chick_counts" class="rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
                        <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Chick Counts & Weights</h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Male Quantity</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psReceive.chick_counts.ps_male_qty || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Female Quantity</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psReceive.chick_counts.ps_female_qty || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total Quantity</label>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ psReceive.chick_counts.ps_total_qty?.toLocaleString() || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Male Boxes</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psReceive.chick_counts.ps_male_rec_box || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Female Boxes</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psReceive.chick_counts.ps_female_rec_box || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total Boxes</label>
                                <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white">{{ psReceive.chick_counts.ps_total_re_box_qty || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Gross Weight (kg)</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psReceive.chick_counts.ps_gross_weight || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Net Weight (kg)</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psReceive.chick_counts.ps_net_weight || '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Challan Box Qty</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psReceive.chick_counts.ps_challan_box_qty || '-' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Lab Tests Information -->
                    <div v-if="psReceive.lab_transfers && psReceive.lab_transfers.length > 0" class="rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
                        <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Lab Tests Information</h2>
                        <div class="space-y-4">
                            <div 
                                v-for="lab in psReceive.lab_transfers" 
                                :key="lab.id"
                                class="rounded-lg border border-gray-200 p-4 dark:border-gray-700"
                            >
                                <div class="mb-2 flex items-center justify-between">
                                    <h3 class="font-medium text-gray-900 dark:text-white">
                                        {{ lab.lab_type === '1' ? 'Government Lab' : 'Provita Lab' }}
                                    </h3>
                                    <span 
                                        class="inline-flex rounded-full px-2 py-1 text-xs font-medium"
                                        :class="lab.status === 1 
                                            ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                                            : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'"
                                    >
                                        {{ lab.status === 1 ? 'Completed' : 'Pending' }}
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Male Qty</label>
                                        <p class="text-sm text-gray-900 dark:text-white">{{ lab.lab_send_male_qty || '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Female Qty</label>
                                        <p class="text-sm text-gray-900 dark:text-white">{{ lab.lab_send_female_qty || '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Total Sent</label>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ lab.lab_send_total_qty || '-' }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Total Received</label>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ lab.lab_receive_total_qty || '-' }}</p>
                                    </div>
                                </div>
                                <div v-if="lab.notes" class="mt-2">
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Notes</label>
                                    <p class="text-sm text-gray-900 dark:text-white">{{ lab.notes }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Summary & Actions -->
                <div class="space-y-6">
                    <!-- Summary Card -->
                    <div class="rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
                        <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Summary</h2>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Total Birds:</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ psReceive.chick_counts?.ps_total_qty?.toLocaleString() || '-' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Total Boxes:</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ psReceive.chick_counts?.ps_total_re_box_qty || '-' }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Gross Weight:</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ psReceive.chick_counts?.ps_gross_weight || '-' }} kg
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600 dark:text-gray-400">Net Weight:</span>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                    {{ psReceive.chick_counts?.ps_net_weight || '-' }} kg
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Transport Information -->
                    <div v-if="psReceive.transport_inside_temp" class="rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
                        <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Transport Information</h2>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Inside Temperature</label>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psReceive.transport_inside_temp }}°C</p>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div v-if="psReceive.remarks" class="rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
                        <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Remarks</h2>
                        <p class="text-sm text-gray-900 dark:text-white">{{ psReceive.remarks }}</p>
                    </div>

                    <!-- Created Information -->
                    <div class="rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
                        <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Record Information</h2>
                        <div class="space-y-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Created At</label>
                                <p class="text-sm text-gray-900 dark:text-white">{{ formatDateTime(psReceive.created_at) }}</p>
                            </div>
                            <div v-if="psReceive.updated_at !== psReceive.created_at">
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Last Updated</label>
                                <p class="text-sm text-gray-900 dark:text-white">{{ formatDateTime(psReceive.updated_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import dayjs from 'dayjs';

interface Props {
    psReceive: {
        id: number;
        shipment_type_id: number;
        pi_no: string;
        pi_date: string;
        order_no?: string;
        order_date?: string;
        lc_no?: string;
        lc_date?: string;
        supplier_id: number;
        breed_type: string[];
        country_of_origin: number;
        transport_type?: string;
        transport_inside_temp?: number;
        company_id: number;
        remarks?: string;
        created_at: string;
        updated_at: string;
        supplier?: {
            id: number;
            name: string;
        };
        company?: {
            id: number;
            name: string;
        };
        country?: {
            id: number;
            name: string;
        };
        chick_counts?: {
            ps_male_qty: number;
            ps_female_qty: number;
            ps_total_qty: number;
            ps_male_rec_box: number;
            ps_female_rec_box: number;
            ps_total_re_box_qty: number;
            ps_challan_box_qty: number;
            ps_gross_weight: number;
            ps_net_weight: number;
        };
        lab_transfers?: Array<{
            id: number;
            lab_type: string;
            lab_send_male_qty: number;
            lab_send_female_qty: number;
            lab_send_total_qty: number;
            lab_receive_female_qty: number;
            lab_receive_male_qty: number;
            lab_receive_total_qty: number;
            notes?: string;
            status: number;
        }>;
    };
    breedTypeNames: string[];
}

const props = defineProps<Props>();

const formatDate = (date: string) => {
    return dayjs(date).format('YYYY-MM-DD');
};

const formatDateTime = (date: string) => {
    return dayjs(date).format('YYYY-MM-DD HH:mm:ss');
};
</script>
