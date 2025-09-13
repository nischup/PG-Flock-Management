<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { AlertTriangle, RefreshCw } from 'lucide-vue-next';

interface Props {
    title?: string;
    message?: string;
    onRetry?: () => void;
}

const props = withDefaults(defineProps<Props>(), {
    title: 'Something went wrong',
    message: 'An error occurred while loading the data. Please try again.'
});
</script>

<template>
    <Card class="border-red-200 bg-red-50">
        <CardHeader class="text-center">
            <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                <AlertTriangle class="h-6 w-6 text-red-600" />
            </div>
            <CardTitle class="text-red-900">{{ title }}</CardTitle>
            <CardDescription class="text-red-700">
                {{ message }}
            </CardDescription>
        </CardHeader>
        <CardContent class="text-center">
            <Button 
                v-if="onRetry" 
                @click="onRetry" 
                variant="outline" 
                class="border-red-300 text-red-700 hover:bg-red-100"
            >
                <RefreshCw class="mr-2 h-4 w-4" />
                Try Again
            </Button>
        </CardContent>
    </Card>
</template>
