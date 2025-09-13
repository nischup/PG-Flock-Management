<script setup lang="ts">
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Skeleton } from '@/components/ui/skeleton/index';

interface Props {
    type?: 'cards' | 'table' | 'tabs' | 'filters';
}

withDefaults(defineProps<Props>(), {
    type: 'cards'
});
</script>

<template>
    <!-- Cards Loading State -->
    <div v-if="type === 'cards'" class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <Card v-for="i in 4" :key="i">
            <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                <Skeleton class="h-4 w-24" />
                <Skeleton class="h-4 w-4" />
            </CardHeader>
            <CardContent>
                <Skeleton class="h-8 w-16 mb-2" />
                <Skeleton class="h-3 w-32" />
            </CardContent>
        </Card>
    </div>
    
    <!-- Table Loading State -->
    <div v-else-if="type === 'table'">
        <Card>
            <CardHeader>
                <Skeleton class="h-6 w-32" />
                <Skeleton class="h-4 w-48" />
            </CardHeader>
            <CardContent>
                <div class="space-y-3">
                    <div v-for="i in 5" :key="i" class="flex items-center space-x-4">
                        <Skeleton class="h-4 w-16" />
                        <Skeleton class="h-4 w-24" />
                        <Skeleton class="h-4 w-32" />
                        <Skeleton class="h-4 w-20" />
                        <Skeleton class="h-4 w-28" />
                        <Skeleton class="h-8 w-8" />
                    </div>
                </div>
            </CardContent>
        </Card>
    </div>
    
    <!-- Tabs Loading State -->
    <div v-else-if="type === 'tabs'" class="space-y-4">
        <div class="flex space-x-1 bg-gray-100 rounded-lg p-1">
            <Skeleton v-for="i in 4" :key="i" class="h-8 w-24" />
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <Card v-for="i in 2" :key="i">
                <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                    <Skeleton class="h-4 w-24" />
                    <Skeleton class="h-4 w-4" />
                </CardHeader>
                <CardContent>
                    <Skeleton class="h-8 w-16 mb-2" />
                    <Skeleton class="h-3 w-32" />
                </CardContent>
            </Card>
        </div>
    </div>
    
    <!-- Filters Loading State -->
    <div v-else-if="type === 'filters'">
        <Card>
            <CardHeader>
                <Skeleton class="h-6 w-16" />
            </CardHeader>
            <CardContent>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                    <div v-for="i in 5" :key="i" class="space-y-2">
                        <Skeleton class="h-4 w-20" />
                        <Skeleton class="h-10 w-full" />
                    </div>
                </div>
                <div class="flex justify-end mt-4">
                    <Skeleton class="h-9 w-24" />
                </div>
            </CardContent>
        </Card>
    </div>
</template>
