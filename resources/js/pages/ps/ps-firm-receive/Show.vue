<template>
    <AppLayout title="PS Firm Receive Details">
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
                <Link :href="route('ps-firm-receive.index')" class="hover:text-gray-900 dark:hover:text-white transition-colors">
                    PS Firm Receive
                </Link>
                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
                <span class="text-gray-900 dark:text-white font-medium">
                    {{ psFirmReceive.job_no }}
                </span>
            </nav>

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        PS Firm Receive Details
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Job No: {{ psFirmReceive.job_no }}
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <Link
                        :href="route('ps-firm-receive.index')"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to List
                    </Link>
                    <Link
                        v-if="can('ps-receive.edit')"
                        :href="route('ps-firm-receive.edit', psFirmReceive.id)"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Edit
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
                                :class="psFirmReceive.status === 1 
                                    ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' 
                                    : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'"
                            >
                                {{ psFirmReceive.status === 1 ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Job No</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psFirmReceive.job_no }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Transaction No</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psFirmReceive.transaction_no }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">PI No</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psFirmReceive.pi_no }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Receive Type</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psFirmReceive.receive_type }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Source Type</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psFirmReceive.source_type }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Receive Date</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ formatDate(psFirmReceive.receive_date) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Flock & Company Information -->
                    <div class="rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
                        <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Flock & Company Information</h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Flock Name</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psFirmReceive.flock_name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Company Name</label>
                                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ psFirmReceive.company_name }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Quantity Information -->
                    <div class="rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
                        <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Quantity Information</h2>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Male Quantity</label>
                                <p class="mt-1 text-lg font-semibold text-blue-600 dark:text-blue-400">{{ psFirmReceive.firm_male_qty }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Female Quantity</label>
                                <p class="mt-1 text-lg font-semibold text-pink-600 dark:text-pink-400">{{ psFirmReceive.firm_female_qty }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total Quantity</label>
                                <p class="mt-1 text-lg font-semibold text-green-600 dark:text-green-400">{{ psFirmReceive.firm_total_qty }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Remarks -->
                    <div class="rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
                        <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Remarks</h2>
                        <p class="text-sm text-gray-900 dark:text-white">{{ psFirmReceive.remarks || 'No remarks provided' }}</p>
                    </div>

                    <!-- Record Information -->
                    <div class="rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
                        <h2 class="mb-4 text-lg font-semibold text-gray-800 dark:text-white">Record Information</h2>
                        <div class="space-y-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Created At</label>
                                <p class="text-sm text-gray-900 dark:text-white">{{ formatDateTime(psFirmReceive.receive_date) }}</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Created By</label>
                                <p class="text-sm text-gray-900 dark:text-white">User ID: {{ psFirmReceive.created_by }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Actions -->
                <div class="space-y-6">
                    <!-- Quick Actions -->
                    <div class="rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Quick Actions</h3>
                        <div class="space-y-3">
                            <button
                                v-if="can('ps-receive.view')"
                                @click="exportRowPdf(psFirmReceive.id)"
                                class="w-full flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                            >
                                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Download Report
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { usePermissions } from '@/composables/usePermissions';
import dayjs from 'dayjs';

interface Props {
    psFirmReceive: {
        id: number;
        job_no: string;
        transaction_no: string;
        flock_name: string;
        company_name: string;
        pi_no: string;
        firm_male_qty: number;
        firm_female_qty: number;
        firm_total_qty: number;
        remarks?: string;
        receive_date: string;
        created_by: number;
        status: number;
        receive_type: string;
        source_type: string;
        source_id: number;
        receiving_company_id: number;
        flock_id: number;
        ps_receive_id: number;
    };
}

const props = defineProps<Props>();
const { can } = usePermissions();

const formatDate = (date: string) => {
    return dayjs(date).format('YYYY-MM-DD');
};

const formatDateTime = (date: string) => {
    return dayjs(date).format('YYYY-MM-DD HH:mm:ss');
};

const exportRowPdf = (id: number) => {
    window.open(route('ps-firm-receive-row.row-pdf', id), '_blank');
};
</script>
