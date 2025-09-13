<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Download, RefreshCw } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import SummaryCards from '@/components/audit/SummaryCards.vue';
import AuditFilters from '@/components/audit/AuditFilters.vue';
import AnalysisTabs from '@/components/audit/AnalysisTabs.vue';
import AuditLogTable from '@/components/audit/AuditLogTable.vue';
import LoadingState from '@/components/audit/LoadingState.vue';
import ErrorState from '@/components/audit/ErrorState.vue';

interface AuditLog {
    id: number;
    event: string;
    auditable_type: string;
    auditable_id: number;
    old_values: Record<string, unknown> | null;
    new_values: Record<string, unknown> | null;
    url: string | null;
    ip_address: string | null;
    user_agent: string | null;
    tags: string | null;
    user_id: number | null;
    created_at: string;
    user?: {
        id: number;
        name: string;
        email: string;
    };
    auditable?: {
        id: number;
        [key: string]: unknown;
    };
}

interface AnalysisData {
    eventDistribution: Record<string, number>;
    modelDistribution: Record<string, number>;
    userActivity: Array<{ user: string; count: number }>;
    dailyActivity: Array<{ date: string; count: number }>;
    hourlyActivity: Array<{ hour: number; count: number }>;
    recentActivity: Array<{
        id: number;
        event: string;
        model: string;
        user: string;
        created_at: string;
        url: string | null;
    }>;
    totalLogs: number;
    uniqueUsers: number;
    dateRange: {
        from: string | null;
        to: string | null;
    };
}

interface Filters {
    events: string[];
    modelTypes: string[];
    users: Array<{ id: number; name: string }>;
    current: {
        event?: string;
        user_id?: string;
        model_type?: string;
        date_from?: string;
        date_to?: string;
    };
}

interface Props {
    auditLogs: {
        data: AuditLog[];
        links: any[];
        meta: any;
    };
    analysis: AnalysisData;
    filters: Filters;
}

const props = defineProps<Props>();

// Ensure analysis always has a valid structure
const analysis = computed(() => {
    if (!props.analysis) {
        return {
            totalLogs: 0,
            uniqueUsers: 0,
            eventDistribution: {},
            modelDistribution: {},
            userActivity: [],
            dailyActivity: [],
            hourlyActivity: [],
            recentActivity: [],
            dateRange: {
                from: null,
                to: null
            }
        };
    }
    return props.analysis;
});

const isLoading = ref(false);
const hasError = ref(false);

const refreshData = () => {
    isLoading.value = true;
    hasError.value = false;
    router.get('/audit-log', {}, {
        preserveState: true,
        onFinish: () => {
            isLoading.value = false;
        },
        onError: () => {
            hasError.value = true;
            isLoading.value = false;
        }
    });
};
</script>

<template>
    <Head title="Audit Log Analysis" />

    <AppLayout>
        <div class="min-h-screen bg-gray-50/50">
            <div class="space-y-8 p-6">
                <!-- Header Section -->
                <div class="flex flex-col space-y-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                    <div class="space-y-1">
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900">Audit Log Analysis</h1>
                        <p class="text-gray-600">
                        Comprehensive analysis of system activities and user actions
                    </p>
                </div>
                    <div class="flex items-center gap-3">
                        <Button variant="outline" @click="refreshData" :disabled="isLoading" class="gap-2">
                            <RefreshCw :class="['h-4 w-4', { 'animate-spin': isLoading }]" />
                        Refresh
                    </Button>
                        <Button variant="outline" class="gap-2">
                            <Download class="h-4 w-4" />
                        Export
                    </Button>
                </div>
            </div>

                <!-- Error State -->
                <ErrorState 
                    v-if="hasError"
                    title="Failed to load audit logs"
                    message="There was an error loading the audit log data. Please try refreshing the page."
                    :on-retry="refreshData"
                />

                <!-- Loading State -->
                <template v-else-if="isLoading">
                    <section class="space-y-4">
                        <h2 class="text-lg font-semibold text-gray-900">Overview</h2>
                        <LoadingState type="cards" />
                    </section>
                    
                    <section class="space-y-4">
                        <h2 class="text-lg font-semibold text-gray-900">Filters & Search</h2>
                        <LoadingState type="filters" />
                    </section>
                    
                    <section class="space-y-4">
                        <h2 class="text-lg font-semibold text-gray-900">Data Analysis</h2>
                        <LoadingState type="tabs" />
                    </section>
                    
                    <section class="space-y-4">
                        <h2 class="text-lg font-semibold text-gray-900">Detailed Logs</h2>
                        <LoadingState type="table" />
                    </section>
                </template>

                <!-- Main Content -->
                <template v-else>
                    <!-- Summary Cards Section -->
                    <section class="space-y-4">
                        <h2 class="text-lg font-semibold text-gray-900">Overview</h2>
                        <SummaryCards :analysis="analysis" />
                    </section>

                    <!-- Filters Section -->
                    <section class="space-y-4">
                        <h2 class="text-lg font-semibold text-gray-900">Filters & Search</h2>
                        <AuditFilters :filters="filters" />
                    </section>

                    <!-- Analysis Section -->
                    <section class="space-y-4">
                        <h2 class="text-lg font-semibold text-gray-900">Data Analysis</h2>
                        <AnalysisTabs :analysis="analysis" />
                    </section>

                    <!-- Detailed Logs Section -->
                    <section class="space-y-4">
                        <h2 class="text-lg font-semibold text-gray-900">Detailed Logs</h2>
                        <AuditLogTable :audit-logs="auditLogs" />
                    </section>
                </template>
            </div>
        </div>
    </AppLayout>
</template>
