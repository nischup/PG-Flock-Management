<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge/index';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table/index';
import { Eye } from 'lucide-vue-next';
import { useAuditLog } from '@/composables/useAuditLog';

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

interface Props {
    auditLogs: {
        data: AuditLog[];
        links: any[];
        meta: any;
    };
}

defineProps<Props>();

const { getEventBadgeColor, formatDate, getModelDisplayName } = useAuditLog();
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Detailed Audit Log</CardTitle>
            <CardDescription>Complete list of all audit log entries</CardDescription>
        </CardHeader>
        <CardContent>
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Event</TableHead>
                            <TableHead>Model</TableHead>
                            <TableHead>User</TableHead>
                            <TableHead>IP Address</TableHead>
                            <TableHead>Date</TableHead>
                            <TableHead>Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="log in auditLogs.data" :key="log.id">
                            <TableCell>
                                <Badge :class="getEventBadgeColor(log.event)">{{ log.event }}</Badge>
                            </TableCell>
                            <TableCell>
                                <div class="text-sm">
                                    <div class="font-medium">{{ getModelDisplayName(log.auditable_type) }}</div>
                                    <div class="text-muted-foreground">ID: {{ log.auditable_id }}</div>
                                </div>
                            </TableCell>
                            <TableCell>
                                <div class="text-sm">
                                    <div>{{ log.user?.name || 'System' }}</div>
                                    <div class="text-muted-foreground">{{ log.user?.email || 'N/A' }}</div>
                                </div>
                            </TableCell>
                            <TableCell class="text-sm">{{ log.ip_address || 'N/A' }}</TableCell>
                            <TableCell class="text-sm">{{ formatDate(log.created_at) }}</TableCell>
                            <TableCell>
                                <Button variant="ghost" size="sm">
                                    <Eye class="h-4 w-4" />
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <div v-if="auditLogs.links" class="flex items-center justify-between mt-4">
                <div class="text-sm text-muted-foreground">
                    <!-- Pagination info can be added here -->
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        v-for="link in auditLogs.links"
                        :key="link.label"
                        variant="outline"
                        size="sm"
                        :disabled="!link.url"
                        @click="link.url && router.get(link.url)"
                        v-html="link.label"
                    />
                </div>
            </div>
        </CardContent>
    </Card>
</template>
