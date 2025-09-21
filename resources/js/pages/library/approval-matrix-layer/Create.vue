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
import { ArrowLeft, Save } from 'lucide-vue-next';

// Props
const props = defineProps<{
    config: {
        id: number;
        name: string;
        module_name: string;
    };
    roles: Record<string, string>;
}>();

const { can } = usePermissions();
const { notify } = useNotifier();

// Form
const form = useForm({
    approval_matrix_config_id: props.config.id,
    layer_order: 1,
    layer_name: '',
    role_name: '',
    is_required: true,
    can_override: false,
    timeout_hours: null,
    description: '',
    is_active: true,
});

// Validation rules
const validateForm = () => {
    if (!form.layer_name.trim()) {
        notify('Layer name is required.', 'error');
        return false;
    }
    if (!form.role_name) {
        notify('Role is required.', 'error');
        return false;
    }
    if (!form.layer_order || form.layer_order < 1) {
        notify('Layer order must be at least 1.', 'error');
        return false;
    }
    return true;
};

// Submit form
const handleSubmit = () => {
    if (!validateForm()) return;

    form.post(route('approval-matrix-layer.store'), {
        onSuccess: () => {
            notify('Approval matrix layer created successfully.', 'success');
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
            notify('Failed to create approval matrix layer.', 'error');
        },
    });
};

// Cancel
const handleCancel = () => {
    router.visit(route('approval-matrix-layer.index', { config_id: props.config.id }));
};
</script>

<template>
    <Head title="Create Approval Matrix Layer" />

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
                    <div>
                        <HeadingSmall title="Create Approval Matrix Layer" />
                        <p class="text-sm text-gray-600 mt-1">
                            Configuration: {{ config.name }} ({{ config.module_name }})
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white p-6 rounded-lg shadow-sm border">
                <form @submit.prevent="handleSubmit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Layer Order -->
                        <div>
                            <Label for="layer_order">Layer Order *</Label>
                            <Input
                                id="layer_order"
                                v-model.number="form.layer_order"
                                type="number"
                                min="1"
                                placeholder="Enter layer order"
                                :class="{ 'border-red-500': form.errors.layer_order }"
                                class="mt-1"
                            />
                            <p v-if="form.errors.layer_order" class="mt-1 text-sm text-red-600">
                                {{ form.errors.layer_order }}
                            </p>
                            <p class="mt-1 text-sm text-gray-500">
                                The order in which this layer should be processed
                            </p>
                        </div>

                        <!-- Layer Name -->
                        <div>
                            <Label for="layer_name">Layer Name *</Label>
                            <Input
                                id="layer_name"
                                v-model="form.layer_name"
                                placeholder="Enter layer name"
                                :class="{ 'border-red-500': form.errors.layer_name }"
                                class="mt-1"
                            />
                            <p v-if="form.errors.layer_name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.layer_name }}
                            </p>
                        </div>

                        <!-- Role Name -->
                        <div>
                            <Label for="role_name">Role *</Label>
                            <select
                                id="role_name"
                                v-model="form.role_name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                            >
                                <option value="">Select a role</option>
                                <option
                                    v-for="(label, value) in (roles || {})"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>
                            <p v-if="form.errors.role_name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.role_name }}
                            </p>
                        </div>

                        <!-- Timeout Hours -->
                        <div>
                            <Label for="timeout_hours">Timeout Hours</Label>
                            <Input
                                id="timeout_hours"
                                v-model.number="form.timeout_hours"
                                type="number"
                                min="1"
                                placeholder="Enter timeout hours (optional)"
                                :class="{ 'border-red-500': form.errors.timeout_hours }"
                                class="mt-1"
                            />
                            <p v-if="form.errors.timeout_hours" class="mt-1 text-sm text-red-600">
                                {{ form.errors.timeout_hours }}
                            </p>
                            <p class="mt-1 text-sm text-gray-500">
                                Auto-approve after specified hours (optional)
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

                        <!-- Required -->
                        <div>
                            <div class="flex items-center space-x-2">
                                <input
                                    id="is_required"
                                    v-model="form.is_required"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-yellow-600 shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                                />
                                <Label for="is_required">Required</Label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                This layer must be completed for approval
                            </p>
                        </div>

                        <!-- Can Override -->
                        <div>
                            <div class="flex items-center space-x-2">
                                <input
                                    id="can_override"
                                    v-model="form.can_override"
                                    type="checkbox"
                                    class="rounded border-gray-300 text-yellow-600 shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                                />
                                <Label for="can_override">Can Override</Label>
                            </div>
                            <p class="mt-1 text-sm text-gray-500">
                                This role can override other layers
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
                                Enable this layer for use in the approval process
                            </p>
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
                            {{ form.processing ? 'Creating...' : 'Create Layer' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
