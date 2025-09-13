<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Activity, User, TrendingUp, BarChart3 } from 'lucide-vue-next';
import { useAuditLog } from '@/composables/useAuditLog';

interface AnalysisData {
    totalLogs: number;
    uniqueUsers: number;
    eventDistribution: Record<string, number>;
    modelDistribution: Record<string, number>;
    dateRange: {
        from: string | null;
        to: string | null;
    };
}

interface Props {
    analysis: AnalysisData;
}

defineProps<Props>();

const { getModelDisplayName } = useAuditLog();
</script>

<template>
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card>
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle class="text-sm font-medium">Total Activities</CardTitle>
                <Activity class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
                <div class="text-2xl font-bold">{{ analysis.totalLogs.toLocaleString() }}</div>
                <p class="text-xs text-muted-foreground">
                    {{ analysis.dateRange.from ? `From ${analysis.dateRange.from}` : 'All time' }}
                </p>
            </CardContent>
        </Card>

        <Card>
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle class="text-sm font-medium">Active Users</CardTitle>
                <User class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
                <div class="text-2xl font-bold">{{ analysis.uniqueUsers }}</div>
                <p class="text-xs text-muted-foreground">
                    Unique users with activity
                </p>
            </CardContent>
        </Card>

        <Card>
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle class="text-sm font-medium">Most Active Event</CardTitle>
                <TrendingUp class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
                <div class="text-2xl font-bold">
                    {{ Object.keys(analysis.eventDistribution)[0] || 'N/A' }}
                </div>
                <p class="text-xs text-muted-foreground">
                    {{ Object.values(analysis.eventDistribution)[0] || 0 }} occurrences
                </p>
            </CardContent>
        </Card>

        <Card>
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle class="text-sm font-medium">Most Active Model</CardTitle>
                <BarChart3 class="h-4 w-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
                <div class="text-2xl font-bold">
                    {{ getModelDisplayName(Object.keys(analysis.modelDistribution)[0] || 'N/A') }}
                </div>
                <p class="text-xs text-muted-foreground">
                    {{ Object.values(analysis.modelDistribution)[0] || 0 }} activities
                </p>
            </CardContent>
        </Card>
    </div>
</template>
