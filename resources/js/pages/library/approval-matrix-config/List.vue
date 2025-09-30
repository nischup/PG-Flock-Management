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
import { CheckCircle, Edit, Plus, Trash2, FileText, Download, Layers } from 'lucide-vue-next';

// Approval Matrix Config interface
interface ApprovalMatrixConfig {
    id: number;
    name: string;
    module_name: string;
    approval_type: string;
    description?: string;
    is_active: number;
    layers_count: number;
    created_at: string;
}

// Props
const props = defineProps<{
    configs: ApprovalMatrixConfig[];
}>();

// for pdf
const filters = ref({
    search: '',
    sort: 'name',
    direction: 'asc',
});

const openDropdown = ref(false);

const exportPdf = (orientation: 'portrait' | 'landscape' = 'portrait') => {
    const url = route('reports.approval-matrix-config.pdf', { ...filters.value, orientation });
    window.open(url, '_blank');
};

//for excel
const exportExcel = () => {
    const url = route('reports.approval-matrix-config.excel', { ...filters.value });
    window.open(url, '_blank');
};

const { can } = usePermissions();
const { notify } = useNotifier();

const { search, perPage, page } = useListFilters();

// Additional reactive refs for sorting and pagination
const sortBy = ref('name');
const sortDirection = ref('asc');
const pagination = ref({
    page: 1,
    per_page: 10,
});

// Filtered and sorted data
const filteredConfigs = computed(() => {
    let filtered = [...props.configs];

    // Search filter
    if (search.value) {
        const searchLower = search.value.toLowerCase();
        filtered = filtered.filter(
            (config) =>
                config.name.toLowerCase().includes(searchLower) ||
                config.module_name.toLowerCase().includes(searchLower) ||
                config.approval_type.toLowerCase().includes(searchLower) ||
                (config.description && config.description.toLowerCase().includes(searchLower))
        );
    }

    // Sort
    filtered.sort((a, b) => {
        const aValue = a[sortBy.value as keyof ApprovalMatrixConfig];
        const bValue = b[sortBy.value as keyof ApprovalMatrixConfig];
        
        if (sortDirection.value === 'asc') {
            return aValue > bValue ? 1 : -1;
        } else {
            return aValue < bValue ? 1 : -1;
        }
    });

    return filtered;
});

// Pagination
const paginatedConfigs = computed(() => {
    const start = (page.value - 1) * perPage.value;
    const end = start + perPage.value;
    return filteredConfigs.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(filteredConfigs.value.length / perPage.value);
});

// Actions
const handleDelete = (config: ApprovalMatrixConfig) => {
    Swal.fire({
        title: 'Are you sure?',
        text: `You are about to delete "${config.name}". This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('approval-matrix-config.destroy', config.id), {
                onSuccess: () => {
                    notify('Approval matrix configuration deleted successfully.', 'success');
                },
                onError: () => {
                    notify('Failed to delete approval matrix configuration.', 'error');
                },
            });
        }
    });
};

const handleEdit = (config: ApprovalMatrixConfig) => {
    router.visit(route('approval-matrix-config.edit', config.id));
};

const handleCreate = () => {
    router.visit(route('approval-matrix-config.create'));
};

const handleManageLayers = (config: ApprovalMatrixConfig) => {
    router.visit(route('approval-matrix-layer.index', { config_id: config.id }));
};

// Status badge
const getStatusBadge = (isActive: number) => {
    return isActive ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
};

const getStatusText = (isActive: number) => {
    return isActive ? 'Active' : 'Inactive';
};

// Approval type badge
const getApprovalTypeBadge = (type: string) => {
    const badges = {
        sequential: 'bg-blue-100 text-blue-800',
        parallel: 'bg-purple-100 text-purple-800',
        conditional: 'bg-orange-100 text-orange-800',
    };
    return badges[type as keyof typeof badges] || 'bg-gray-100 text-gray-800';
};

const getApprovalTypeText = (type: string) => {
    const texts = {
        sequential: 'Sequential',
        parallel: 'Parallel',
        conditional: 'Conditional',
    };
    return texts[type as keyof typeof texts] || type;
};

onMounted(() => {
    // Any initialization logic
});

onBeforeUnmount(() => {
    // Cleanup logic
});
</script>

<template>
    <Head title="Approval Matrix Configurations" />

    <AppLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <HeadingSmall title="Approval Matrix Configurations" />
                <div class="flex items-center gap-2">
                    <Button
                        v-if="can('approval-matrix-config.create')"
                        @click="handleCreate"
                        class="bg-yellow-500 hover:bg-yellow-600"
                    >
                        <Plus class="w-4 h-4 mr-2" />
                        Add Configuration
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
                            placeholder="Search by name, module, or type..."
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
                            <option value="name">Name</option>
                            <option value="module_name">Module</option>
                            <option value="approval_type">Type</option>
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

            <!-- Export Actions -->
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-600">
                    Showing {{ paginatedConfigs.length }} of {{ filteredConfigs.length }} configurations
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="exportPdf('portrait')"
                        class="flex items-center gap-2"
                    >
                        <FileText class="w-4 h-4" />
                        PDF
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        @click="exportExcel"
                        class="flex items-center gap-2"
                    >
                        <Download class="w-4 h-4" />
                        Excel
                    </Button>
                </div>
            </div>

            <!-- Table -->
            <div class="mt-4 overflow-x-auto rounded-xl bg-white shadow dark:bg-gray-800">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr>
                            <th class="border-b px-4 py-2 bg-blue-500 text-white font-semibold text-sm whitespace-nowrap">
                                Name
                            </th>
                            <th class="border-b px-4 py-2 bg-green-500 text-white font-semibold text-sm whitespace-nowrap">
                                Module
                            </th>
                            <th class="border-b px-4 py-2 bg-purple-500 text-white font-semibold text-sm whitespace-nowrap">
                                Type
                            </th>
                            <th class="border-b px-4 py-2 bg-orange-500 text-white font-semibold text-sm whitespace-nowrap">
                                Layers
                            </th>
                            <th class="border-b px-4 py-2 bg-pink-500 text-white font-semibold text-sm whitespace-nowrap">
                                Status
                            </th>
                            <th class="border-b px-4 py-2 bg-indigo-500 text-white font-semibold text-sm whitespace-nowrap">
                                Created
                            </th>
                            <th class="border-b px-4 py-2 bg-gray-600 text-white font-semibold text-sm whitespace-nowrap text-right">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="config in paginatedConfigs" :key="config.id" class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="border-b px-4 py-2 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ config.name }}</div>
                                    <div v-if="config.description" class="text-sm text-gray-500 truncate max-w-xs">
                                        {{ config.description }}
                                    </div>
                                </td>
                                <td class="border-b px-4 py-2 whitespace-nowrap">
                                    <span class="text-sm text-gray-900 capitalize">
                                        {{ config.module_name.replace('-', ' ') }}
                                    </span>
                                </td>
                                <td class="border-b px-4 py-2 whitespace-nowrap">
                                    <span
                                        :class="getApprovalTypeBadge(config.approval_type)"
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    >
                                        {{ getApprovalTypeText(config.approval_type) }}
                                    </span>
                                </td>
                                <td class="border-b px-4 py-2 whitespace-nowrap">
                                    <div class="flex items-center text-sm text-gray-900">
                                        <Layers class="w-4 h-4 mr-1" />
                                        {{ config.layers_count }}
                                    </div>
                                </td>
                                <td class="border-b px-4 py-2 whitespace-nowrap">
                                    <span
                                        :class="getStatusBadge(config.is_active)"
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                    >
                                        {{ getStatusText(config.is_active) }}
                                    </span>
                                </td>
                                <td class="border-b px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                    {{ dayjs(config.created_at).format('MMM DD, YYYY') }}
                                </td>
                                <td class="border-b px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button
                                            v-if="can('approval-matrix-config.edit')"
                                            variant="ghost"
                                            size="sm"
                                            @click="handleEdit(config)"
                                            class="text-blue-600 hover:text-blue-900"
                                        >
                                            <Edit class="w-4 h-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="handleManageLayers(config)"
                                            class="text-green-600 hover:text-green-900"
                                            title="Manage Layers"
                                        >
                                            <Layers class="w-4 h-4" />
                                        </Button>
                                        <Button
                                            v-if="can('approval-matrix-config.delete')"
                                            variant="ghost"
                                            size="sm"
                                            @click="handleDelete(config)"
                                            class="text-red-600 hover:text-red-900"
                                        >
                                            <Trash2 class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Empty State -->
                    <div v-if="paginatedConfigs.length === 0" class="text-center py-12">
                        <CheckCircle class="mx-auto h-12 w-12 text-gray-400" />
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No configurations found</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Get started by creating a new approval matrix configuration.
                        </p>
                        <div class="mt-6">
                            <Button
                                v-if="can('approval-matrix-config.create')"
                                @click="handleCreate"
                                class="bg-yellow-500 hover:bg-yellow-600"
                            >
                                <Plus class="w-4 h-4 mr-2" />
                                Add Configuration
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
