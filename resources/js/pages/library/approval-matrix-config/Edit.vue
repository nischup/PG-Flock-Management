<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useNotifier } from '@/composables/useNotifier';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save, Layers } from 'lucide-vue-next';

// Props
const props = defineProps<{
    config: {
        id: number;
        name: string;
        module_name: string;
        approval_type: string;
        description?: string;
        is_active: number;
        layers: Array<{
            id: number;
            layer_order: number;
            layer_name: string;
            role_name: string;
            is_required: number;
            can_override: number;
            timeout_hours?: number;
            description?: string;
            is_active: number;
        }>;
    };
    modules: Record<string, string>;
    approvalTypes: Record<string, string>;
}>();

const { can } = usePermissions();
const { notify } = useNotifier();

// Form
const form = useForm({
    name: props.config.name,
    module_name: props.config.module_name,
    approval_type: props.config.approval_type,
    description: props.config.description || '',
    is_active: Boolean(props.config.is_active),
});

// Validation rules
const validateForm = () => {
    if (!form.name.trim()) {
        notify('Name is required.', 'error');
        return false;
    }
    if (!form.module_name) {
        notify('Module is required.', 'error');
        return false;
    }
    if (!form.approval_type) {
        notify('Approval type is required.', 'error');
        return false;
    }
    return true;
};

// Submit form
const handleSubmit = () => {
    if (!validateForm()) return;

    form.put(route('approval-matrix-config.update', props.config.id), {
        onSuccess: () => {
            notify('Approval matrix configuration updated successfully.', 'success');
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
            notify('Failed to update approval matrix configuration.', 'error');
        },
    });
};

// Cancel
const handleCancel = () => {
    router.visit(route('approval-matrix-config.index'));
};

// Manage layers
const handleManageLayers = () => {
    router.visit(route('approval-matrix-layer.index', { config_id: props.config.id }));
};
</script>

<template>
    <Head title="Edit Approval Matrix Configuration" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="handleCancel"
                        class="flex items-center gap-2"
                    >
                        <ArrowLeft class="w-4 h-4" />
                        Back
                    </Button>
                    <HeadingSmall title="Edit Approval Matrix Configuration" />
                </div>
                <Button
                    @click="handleManageLayers"
                    variant="outline"
                    class="flex items-center gap-2"
                >
                    <Layers class="w-4 h-4" />
                    Manage Layers ({{ config.layers.length }})
                </Button>
            </div>

            <!-- Form -->
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Name -->
                        <div class="md:col-span-2">
                            <Label for="name">Name *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                placeholder="Enter configuration name"
                                :class="{ 'border-red-500': form.errors.name }"
                                class="mt-1"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <!-- Module -->
                        <div>
                            <Label for="module_name">Module *</Label>
                            <Select v-model="form.module_name">
                                <SelectTrigger class="mt-1">
                                    <SelectValue placeholder="Select a module" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="(label, value) in modules"
                                        :key="value"
                                        :value="value"
                                    >
                                        {{ label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.module_name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.module_name }}
                            </p>
                        </div>

                        <!-- Approval Type -->
                        <div>
                            <Label for="approval_type">Approval Type *</Label>
                            <Select v-model="form.approval_type">
                                <SelectTrigger class="mt-1">
                                    <SelectValue placeholder="Select approval type" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="(label, value) in approvalTypes"
                                        :key="value"
                                        :value="value"
                                    >
                                        {{ label }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.approval_type" class="mt-1 text-sm text-red-600">
                                {{ form.errors.approval_type }}
                            </p>
                        </div>

                        <!-- Description -->
                        <div class="md:col-span-2">
                            <Label for="description">Description</Label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                placeholder="Enter description (optional)"
                                :class="{ 'border-red-500': form.errors.description }"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                                rows="3"
                            ></textarea>
                            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <!-- Active Status -->
                        <div class="md:col-span-2">
                            <div class="flex items-center space-x-2">
                                <input
                                    id="is_active"
                                    v-model="form.is_active"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-yellow-600 shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                                />
                                <Label for="is_active">Active</Label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                Enable this configuration for use in the system
                            </p>
                        </div>
                    </div>

                    <!-- Layers Summary -->
                    <div v-if="config.layers.length > 0" class="bg-gray-50 p-4 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Current Layers</h4>
                        <div class="space-y-2">
                            <div
                                v-for="layer in config.layers"
                                :key="layer.id"
                                class="flex items-center justify-between text-sm"
                            >
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{ layer.layer_order }}.</span>
                                    <span>{{ layer.layer_name }}</span>
                                    <span class="text-gray-500">({{ layer.role_name }})</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span
                                        :class="layer.is_required ? 'text-green-600' : 'text-gray-400'"
                                        class="text-xs"
                                    >
                                        {{ layer.is_required ? 'Required' : 'Optional' }}
                                    </span>
                                    <span
                                        :class="layer.is_active ? 'text-green-600' : 'text-red-600'"
                                        class="text-xs"
                                    >
                                        {{ layer.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end gap-4 pt-6 border-t">
                        <Button
                            type="button"
                            variant="outline"
                            @click="handleCancel"
                        >
                            Cancel
                        </Button>
                        <Button
                            type="submit"
                            :disabled="form.processing"
                            class="bg-yellow-500 hover:bg-yellow-600"
                        >
                            <Save class="w-4 h-4 mr-2" />
                            {{ form.processing ? 'Updating...' : 'Update Configuration' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
