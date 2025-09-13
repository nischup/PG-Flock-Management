<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select } from '@/components/ui/select';
import { Filter } from 'lucide-vue-next';
import { useAuditLog } from '@/composables/useAuditLog';

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
    filters: Filters;
}

const props = defineProps<Props>();

const filters = ref({
    event: props.filters.current.event || '',
    user_id: props.filters.current.user_id || '',
    model_type: props.filters.current.model_type || '',
    date_from: props.filters.current.date_from || '',
    date_to: props.filters.current.date_to || '',
});

const isLoading = ref(false);

const applyFilters = () => {
    isLoading.value = true;
    router.get('/audit-log', filters.value, {
        preserveState: true,
        onFinish: () => {
            isLoading.value = false;
        }
    });
};

const clearFilters = () => {
    filters.value = {
        event: '',
        user_id: '',
        model_type: '',
        date_from: '',
        date_to: '',
    };
    applyFilters();
};

const { getModelDisplayName } = useAuditLog();

// Watch for filter changes and auto-apply
watch(filters, () => {
    const timeoutId = setTimeout(applyFilters, 500);
    return () => clearTimeout(timeoutId);
}, { deep: true });

defineExpose({
    applyFilters,
    clearFilters
});
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle class="flex items-center gap-2">
                <Filter class="h-5 w-5" />
                Filters
            </CardTitle>
        </CardHeader>
        <CardContent>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                <div class="space-y-2">
                    <Label for="event">Event Type</Label>
                    <Select 
                        v-model="filters.event"
                        :options="[
                            { value: '', label: 'All Events' },
                            ...props.filters.events.map(event => ({ value: event, label: event }))
                        ]"
                        placeholder="All Events"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="user">User</Label>
                    <Select 
                        v-model="filters.user_id"
                        :options="[
                            { value: '', label: 'All Users' },
                            ...props.filters.users.map(user => ({ value: user.id.toString(), label: user.name }))
                        ]"
                        placeholder="All Users"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="model">Model Type</Label>
                    <Select 
                        v-model="filters.model_type"
                        :options="[
                            { value: '', label: 'All Models' },
                            ...props.filters.modelTypes.map(model => ({ value: model, label: getModelDisplayName(model) }))
                        ]"
                        placeholder="All Models"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="date_from">From Date</Label>
                    <Input
                        id="date_from"
                        v-model="filters.date_from"
                        type="date"
                        placeholder="Start date"
                    />
                </div>

                <div class="space-y-2">
                    <Label for="date_to">To Date</Label>
                    <Input
                        id="date_to"
                        v-model="filters.date_to"
                        type="date"
                        placeholder="End date"
                    />
                </div>
            </div>

            <div class="flex justify-end mt-4">
                <Button variant="outline" @click="clearFilters">
                    Clear Filters
                </Button>
            </div>
        </CardContent>
    </Card>
</template>
