<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useListFilters } from '@/composables/useListFilters';
import { useNotifier } from '@/composables/useNotifier';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import Swal from 'sweetalert2';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { CheckCircle, Edit, Plus, Trash2, ArrowLeft, Layers } from 'lucide-vue-next';

// Approval Matrix Layer interface
interface ApprovalMatrixLayer {
    id: number;
    layer_order: number;
    layer_name: string;
    role_name: string;
    is_required: number;
    can_override: number;
    timeout_hours?: number;
    description?: string;
    is_active: number;
    created_at: string;
}

// Props
const props = defineProps<{
    layers: ApprovalMatrixLayer[];
    config: {
        id: number;
        name: string;
        module_name: string;
    };
}>();

const { can } = usePermissions();
const { notify } = useNotifier();

const { search, perPage, page } = useListFilters();

// Additional reactive refs for sorting and pagination
const sortBy = ref('layer_order');
const sortDirection = ref('asc');
const pagination = ref({
    page: 1,
    per_page: 10,
});

// Filtered and sorted data
const filteredLayers = computed(() => {
    let filtered = [...props.layers];

    // Search filter
    if (search.value) {
        const searchLower = search.value.toLowerCase();
        filtered = filtered.filter(
            (layer) =>
                layer.layer_name.toLowerCase().includes(searchLower) ||
                layer.role_name.toLowerCase().includes(searchLower) ||
                (layer.description && layer.description.toLowerCase().includes(searchLower))
        );
    }

    // Sort
    filtered.sort((a, b) => {
        const aValue = a[sortBy.value as keyof ApprovalMatrixLayer];
        const bValue = b[sortBy.value as keyof ApprovalMatrixLayer];
        
        if (sortDirection.value === 'asc') {
            return aValue > bValue ? 1 : -1;
        } else {
            return aValue < bValue ? 1 : -1;
        }
    });

    return filtered;
});

// Pagination
const paginatedLayers = computed(() => {
    const start = (page.value - 1) * perPage.value;
    const end = start + perPage.value;
    return filteredLayers.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(filteredLayers.value.length / perPage.value);
});

// Actions
const handleDelete = (layer: ApprovalMatrixLayer) => {
    Swal.fire({
        title: 'Are you sure?',
        text: `You are about to delete layer "${layer.layer_name}". This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('approval-matrix-layer.destroy', layer.id), {
                onSuccess: () => {
                    notify('Approval matrix layer deleted successfully.', 'success');
                },
                onError: () => {
                    notify('Failed to delete approval matrix layer.', 'error');
                },
            });
        }
    });
};

const handleEdit = (layer: ApprovalMatrixLayer) => {
    router.visit(route('approval-matrix-layer.edit', layer.id));
};

const handleCreate = () => {
    router.visit(route('approval-matrix-layer.create', { config_id: props.config.id }));
};

const handleBack = () => {
    router.visit(route('approval-matrix-config.index'));
};

// Status badge
const getStatusBadge = (isActive: number) => {
    return isActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
};

const getStatusText = (isActive: number) => {
    return isActive ? 'Active' : 'Inactive';
};

// Required badge
const getRequiredBadge = (isRequired: number) => {
    return isRequired ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800';
};

const getRequiredText = (isRequired: number) => {
    return isRequired ? 'Required' : 'Optional';
};

onMounted(() => {
    // Any initialization logic
});

onBeforeUnmount(() => {
    // Cleanup logic
});
</script>

<template>
    <Head title="Approval Matrix Layers" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="handleBack"
                        class="flex items-center gap-2"
                    >
                        <ArrowLeft class="w-4 h-4" />
                        Back to Configurations
                    </Button>
                    <div>
                        <HeadingSmall title="Approval Matrix Layers" />
                        <p class="text-sm text-gray-600 mt-1">
                            Configuration: {{ config.name }} ({{ config.module_name }})
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        v-if="can('approval-matrix-layer.create')"
                        @click="handleCreate"
                        class="bg-yellow-500 hover:bg-yellow-600"
                    >
                        <Plus class="w-4 h-4 mr-2" />
                        Add Layer
                    </Button>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white p-4 rounded-lg shadow-sm border">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <Label for="search">Search</Label>
                        <Input
                            id="search"
                            v-model="search"
                            placeholder="Search by name or role..."
                            class="mt-1"
                        />
                    </div>
                    <div>
                        <Label for="per_page">Per Page</Label>
                        <select
                            id="per_page"
                            v-model="perPage"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                        >
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div>
                        <Label for="sort_by">Sort By</Label>
                        <select
                            id="sort_by"
                            v-model="sortBy"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                        >
                            <option value="layer_order">Order</option>
                            <option value="layer_name">Name</option>
                            <option value="role_name">Role</option>
                            <option value="created_at">Created Date</option>
                        </select>
                    </div>
                    <div>
                        <Label for="sort_direction">Direction</Label>
                        <select
                            id="sort_direction"
                            v-model="sortDirection"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-yellow-500 focus:ring-yellow-500"
                        >
                            <option value="asc">Ascending</option>
                            <option value="desc">Descending</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="mt-4 overflow-x-auto rounded-xl bg-white shadow dark:bg-gray-800">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr>
                            <th class="border-b px-4 py-2 bg-blue-500 text-white font-semibold text-sm whitespace-nowrap">
                                Order
                            </th>
                            <th class="border-b px-4 py-2 bg-green-500 text-white font-semibold text-sm whitespace-nowrap">
                                Layer Name
                            </th>
                            <th class="border-b px-4 py-2 bg-purple-500 text-white font-semibold text-sm whitespace-nowrap">
                                Role
                            </th>
                            <th class="border-b px-4 py-2 bg-orange-500 text-white font-semibold text-sm whitespace-nowrap">
                                Required
                            </th>
                            <th class="border-b px-4 py-2 bg-pink-500 text-white font-semibold text-sm whitespace-nowrap">
                                Timeout
                            </th>
                            <th class="border-b px-4 py-2 bg-indigo-500 text-white font-semibold text-sm whitespace-nowrap">
                                Status
                            </th>
                            <th class="border-b px-4 py-2 bg-red-500 text-white font-semibold text-sm whitespace-nowrap">
                                Created
                            </th>
                            <th class="border-b px-4 py-2 bg-gray-600 text-white font-semibold text-sm whitespace-nowrap text-right">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="layer in paginatedLayers" :key="layer.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="border-b px-4 py-2 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <Layers class="w-4 h-4 mr-2 text-gray-400" />
                                        <span class="text-sm font-medium text-gray-900">{{ layer.layer_order }}</span>
                                    </div>
                                </td>
                                <td class="border-b px-4 py-2 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ layer.layer_name }}</div>
                                    <div v-if="layer.description" class="text-sm text-gray-500 truncate max-w-xs">
                                        {{ layer.description }}
                                    </div>
                                </td>
                                <td class="border-b px-4 py-2 whitespace-nowrap">
                                    <span class="text-sm text-gray-900 capitalize">
                                        {{ layer.role_name.replace('-', ' ') }}
                                    </span>
                                </td>
                                <td class="border-b px-4 py-2 whitespace-nowrap">
                                    <span
                                        :class="getRequiredBadge(layer.is_required)"
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    >
                                        {{ getRequiredText(layer.is_required) }}
                                    </span>
                                </td>
                                <td class="border-b px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                    {{ layer.timeout_hours ? `${layer.timeout_hours}h` : '-' }}
                                </td>
                                <td class="border-b px-4 py-2 whitespace-nowrap">
                                    <span
                                        :class="getStatusBadge(layer.is_active)"
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    >
                                        {{ getStatusText(layer.is_active) }}
                                    </span>
                                </td>
                                <td class="border-b px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                    {{ dayjs(layer.created_at).format('MMM DD, YYYY') }}
                                </td>
                                <td class="border-b px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button
                                            v-if="can('approval-matrix-layer.edit')"
                                            variant="ghost"
                                            size="sm"
                                            @click="handleEdit(layer)"
                                            class="text-blue-600 hover:text-blue-900"
                                        >
                                            <Edit class="w-4 h-4" />
                                        </Button>
                                        <Button
                                            v-if="can('approval-matrix-layer.delete')"
                                            variant="ghost"
                                            size="sm"
                                            @click="handleDelete(layer)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="paginatedLayers.length === 0" class="text-center py-12">
                    <CheckCircle class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No layers found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        Get started by creating a new approval layer.
                    </p>
                    <div class="mt-6">
                        <Button
                            v-if="can('approval-matrix-layer.create')"
                            @click="handleCreate"
                            class="bg-yellow-500 hover:bg-yellow-600"
                        >
                            <Plus class="w-4 h-4 mr-2" />
                            Add Layer
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1" class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Showing page {{ page }} of {{ totalPages }}
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="page <= 1"
                        @click="page--"
                    >
                        Previous
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="page >= totalPages"
                        @click="page++"
                    >
                        Next
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
