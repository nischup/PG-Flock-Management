<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge/index';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs/index';
import { Clock } from 'lucide-vue-next';
import { useAuditLog } from '@/composables/useAuditLog';

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
}

interface Props {
    analysis: AnalysisData;
}

defineProps<Props>();

const { getEventBadgeColor, getModelDisplayName } = useAuditLog();
</script>

<template>
    <Tabs default-value="overview" class="space-y-4">
        <TabsList class="grid w-full grid-cols-4">
            <TabsTrigger value="overview">Overview</TabsTrigger>
            <TabsTrigger value="distribution">Distribution</TabsTrigger>
            <TabsTrigger value="activity">Activity Timeline</TabsTrigger>
            <TabsTrigger value="recent">Recent Activity</TabsTrigger>
        </TabsList>

        <TabsContent value="overview" class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Event Distribution -->
                <Card>
                    <CardHeader>
                        <CardTitle>Event Distribution</CardTitle>
                        <CardDescription>Breakdown of activities by event type</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <div v-for="[event, count] in Object.entries(analysis.eventDistribution)" :key="event" class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <Badge :class="getEventBadgeColor(event)">{{ event }}</Badge>
                                </div>
                                <span class="font-medium">{{ count.toLocaleString() }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Model Distribution -->
                <Card>
                    <CardHeader>
                        <CardTitle>Model Distribution</CardTitle>
                        <CardDescription>Activities by model type</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <div v-for="[model, count] in Object.entries(analysis.modelDistribution)" :key="model" class="flex items-center justify-between">
                                <span class="text-sm">{{ getModelDisplayName(model) }}</span>
                                <span class="font-medium">{{ count.toLocaleString() }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Top Users -->
            <Card>
                <CardHeader>
                    <CardTitle>Most Active Users</CardTitle>
                    <CardDescription>Users with the highest activity count</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-2">
                        <div v-for="user in analysis.userActivity" :key="user.user" class="flex items-center justify-between">
                            <span class="text-sm">{{ user.user }}</span>
                            <span class="font-medium">{{ user.count.toLocaleString() }}</span>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </TabsContent>

        <TabsContent value="distribution" class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Event Distribution Chart -->
                <Card>
                    <CardHeader>
                        <CardTitle>Event Distribution Chart</CardTitle>
                        <CardDescription>Visual breakdown of event types</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div v-for="[event, count] in Object.entries(analysis.eventDistribution)" :key="event" class="space-y-1">
                                <div class="flex justify-between text-sm">
                                    <span>{{ event }}</span>
                                    <span>{{ count }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div 
                                        class="bg-blue-600 h-2 rounded-full" 
                                        :style="{ width: `${(count / Math.max(...Object.values(analysis.eventDistribution), 1)) * 100}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Model Distribution Chart -->
                <Card>
                    <CardHeader>
                        <CardTitle>Model Distribution Chart</CardTitle>
                        <CardDescription>Visual breakdown of model types</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-3">
                            <div v-for="[model, count] in Object.entries(analysis.modelDistribution)" :key="model" class="space-y-1">
                                <div class="flex justify-between text-sm">
                                    <span>{{ getModelDisplayName(model) }}</span>
                                    <span>{{ count }}</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div 
                                        class="bg-green-600 h-2 rounded-full" 
                                        :style="{ width: `${(count / Math.max(...Object.values(analysis.modelDistribution), 1)) * 100}%` }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </TabsContent>

        <TabsContent value="activity" class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <!-- Daily Activity -->
                <Card>
                    <CardHeader>
                        <CardTitle>Daily Activity (Last 30 Days)</CardTitle>
                        <CardDescription>Activity count per day</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <div v-for="day in analysis.dailyActivity" :key="day.date" class="flex items-center justify-between">
                                <span class="text-sm">{{ day.date }}</span>
                                <div class="flex items-center gap-2">
                                    <div class="w-20 bg-gray-200 rounded-full h-2">
                                        <div 
                                            class="bg-blue-600 h-2 rounded-full" 
                                            :style="{ width: `${(day.count / Math.max(...analysis.dailyActivity.map(d => d.count), 1)) * 100}%` }"
                                        ></div>
                                    </div>
                                    <span class="text-sm font-medium">{{ day.count }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Hourly Activity -->
                <Card>
                    <CardHeader>
                        <CardTitle>Hourly Activity (Last 24 Hours)</CardTitle>
                        <CardDescription>Activity count per hour</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-2">
                            <div v-for="hour in analysis.hourlyActivity" :key="hour.hour" class="flex items-center justify-between">
                                <span class="text-sm">{{ hour.hour.toString().padStart(2, '0') }}:00</span>
                                <div class="flex items-center gap-2">
                                    <div class="w-20 bg-gray-200 rounded-full h-2">
                                        <div 
                                            class="bg-green-600 h-2 rounded-full" 
                                            :style="{ width: `${(hour.count / Math.max(...analysis.hourlyActivity.map(h => h.count), 1)) * 100}%` }"
                                        ></div>
                                    </div>
                                    <span class="text-sm font-medium">{{ hour.count }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </TabsContent>

        <TabsContent value="recent" class="space-y-4">
            <Card>
                <CardHeader>
                    <CardTitle>Recent Activity</CardTitle>
                    <CardDescription>Latest system activities</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-3">
                        <div v-for="activity in analysis.recentActivity" :key="activity.id" class="flex items-center justify-between p-3 border rounded-lg">
                            <div class="flex items-center gap-3">
                                <Badge :class="getEventBadgeColor(activity.event)">{{ activity.event }}</Badge>
                                <span class="text-sm text-muted-foreground">{{ activity.model }}</span>
                                <span class="text-sm">{{ activity.user }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                                <Clock class="h-4 w-4" />
                                {{ activity.created_at }}
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </TabsContent>
    </Tabs>
</template>
