<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
// import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { useNotifier } from '@/composables/useNotifier';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Save } from 'lucide-vue-next';

// Props
const props = defineProps<{
    modules: Record<string, string>;
    approvalTypes: Record<string, string>;
}>();

// Props are received correctly

const { can } = usePermissions();
const { notify } = useNotifier();

// Form
const form = useForm({
    name: '',
    module_name: '',
    approval_type: 'sequential',
    description: '',
    is_active: true,
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

    form.post(route('approval-matrix-config.store'), {
        onSuccess: () => {
            notify('Approval matrix configuration created successfully.', 'success');
        },
        onError: (errors) => {
            console.error('Validation errors:', errors);
            notify('Failed to create approval matrix configuration.', 'error');
        },
    });
};

// Cancel
const handleCancel = () => {
    router.visit(route('approval-matrix-config.index'));
};
</script>

<template>
    <Head title="Create Approval Matrix Configuration" />

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
                    <HeadingSmall title="Create Approval Matrix Configuration" />
                </div>
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
                            <select
                                id="module_name"
                                v-model="form.module_name"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                            >
                                <option value="">Select a module</option>
                                <option
                                    v-for="(label, value) in (modules || {})"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>
                            <p v-if="form.errors.module_name" class="mt-1 text-sm text-red-600">
                                {{ form.errors.module_name }}
                            </p>
                        </div>

                        <!-- Approval Type -->
                        <div>
                            <Label for="approval_type">Approval Type *</Label>
                            <select
                                id="approval_type"
                                v-model="form.approval_type"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                            >
                                <option value="">Select approval type</option>
                                <option
                                    v-for="(label, value) in (approvalTypes || {})"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>
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
                            {{ form.processing ? 'Creating...' : 'Create Configuration' }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
