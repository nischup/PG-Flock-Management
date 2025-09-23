<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useNotifier } from '@/composables/useNotifier';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import Swal from 'sweetalert2';
import { onBeforeUnmount, onMounted, ref } from 'vue';

interface VaccineRouting {
    id: number;
    name: string;
    status: number;
    description: string;
    created_at: string;
    updated_at: string;
}

// Export filters
const filters = ref({
    search: '',
    sort: 'name',
    direction: 'asc',
});

const openExportDropdown = ref(false);
const exportPdf = (orientation: 'portrait' | 'landscape' = 'portrait') => {
    const url = route('reports.vaccine-routings.pdf', { ...filters.value, orientation });
    window.open(url, '_blank');
};
const exportExcel = () => {
    const url = route('reports.vaccine-routings.excel', { ...filters.value });
    window.open(url, '_blank');
};

// Props
const props = defineProps<{
    routings: VaccineRouting[];
    filters?: { search?: string; per_page?: number; page?: number };
}>();

useNotifier();
const { can } = usePermissions();

// Local state
const routings = ref<VaccineRouting[]>([...props.routings]);

// Modal state
const showModal = ref(false);
const editingRouting = ref<VaccineRouting | null>(null);
const form = useForm({
    name: '',
    description: '',
    status: 1,
});

// Draggable modal
const modalRef = ref<HTMLElement | null>(null);
let offsetX = 0,
    offsetY = 0,
    isDragging = false;
const startDrag = (event: MouseEvent) => {
    if (!modalRef.value) return;
    isDragging = true;
    const rect = modalRef.value.getBoundingClientRect();
    offsetX = event.clientX - rect.left;
    offsetY = event.clientY - rect.top;
    document.addEventListener('mousemove', drag);
    document.addEventListener('mouseup', stopDrag);
};

const drag = (event: MouseEvent) => {
    if (!isDragging || !modalRef.value) return;
    const x = event.clientX - offsetX;
    const y = event.clientY - offsetY;
    modalRef.value.style.left = `${x}px`;
    modalRef.value.style.top = `${y}px`;
};

const stopDrag = () => {
    isDragging = false;
    document.removeEventListener('mousemove', drag);
    document.removeEventListener('mouseup', stopDrag);
};

// Search functionality
const search = ref(props.filters?.search || '');
const searchTimeout = ref<NodeJS.Timeout | null>(null);

const performSearch = () => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }
    searchTimeout.value = setTimeout(() => {
        router.get(route('vaccine-routing.index'), { search: search.value }, {
            preserveState: true,
            replace: true,
        });
    }, 300);
};

// CRUD operations
const openCreateModal = () => {
    editingRouting.value = null;
    form.reset();
    form.status = 1;
    showModal.value = true;
};

const openEditModal = (routing: VaccineRouting) => {
    editingRouting.value = routing;
    form.name = routing.name;
    form.description = routing.description;
    form.status = routing.status;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingRouting.value = null;
    form.reset();
};

const submitForm = () => {
    if (editingRouting.value) {
        form.put(route('vaccine-routing.update', editingRouting.value.id), {
            onSuccess: () => {
                closeModal();
                // Update local data
                const index = routings.value.findIndex(r => r.id === editingRouting.value!.id);
                if (index !== -1) {
                    routings.value[index] = { ...routings.value[index], ...form.data() };
                }
            },
        });
    } else {
        form.post(route('vaccine-routing.store'), {
            onSuccess: () => {
                closeModal();
                // Refresh the page to get updated data
                router.reload();
            },
        });
    }
};

const deleteRouting = (routing: VaccineRouting) => {
    Swal.fire({
        title: 'Are you sure?',
        text: `You are about to delete "${routing.name}". This action cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(route('vaccine-routing.destroy', routing.id), {
                onSuccess: () => {
                    // Remove from local data
                    const index = routings.value.findIndex(r => r.id === routing.id);
                    if (index !== -1) {
                        routings.value.splice(index, 1);
                    }
                },
            });
        }
    });
};

// Cleanup
onBeforeUnmount(() => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }
    document.removeEventListener('mousemove', drag);
    document.removeEventListener('mouseup', stopDrag);
});
</script>

<template>
    <Head title="Vaccine Routing" />

    <AppLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <HeadingSmall title="Vaccine Routing Management" />
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Manage vaccine routing configurations and their details
                    </p>
                </div>

                <!-- Action Bar -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
                    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                        <!-- Search -->
                        <div class="flex-1 max-w-md">
                            <div class="relative">
                                <Input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search vaccine routings..."
                                    class="pl-10"
                                    @input="performSearch"
                                />
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2">
                            <!-- Export Dropdown -->
                            <div class="relative">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="openExportDropdown = !openExportDropdown"
                                >
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Export
                                </Button>
                                <div
                                    v-if="openExportDropdown"
                                    class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg z-10 border border-gray-200 dark:border-gray-700"
                                >
                                    <div class="py-1">
                                        <button
                                            @click="exportPdf('portrait'); openExportDropdown = false"
                                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        >
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            Export as PDF (Portrait)
                                        </button>
                                        <button
                                            @click="exportPdf('landscape'); openExportDropdown = false"
                                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        >
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                            </svg>
                                            Export as PDF (Landscape)
                                        </button>
                                        <button
                                            @click="exportExcel(); openExportDropdown = false"
                                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                                        >
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                            Export as Excel
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Create Button -->
                            <Button
                                v-if="can('vaccine-routing.create')"
                                @click="openCreateModal"
                                size="sm"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Vaccine Routing
                            </Button>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-gray-600">
                            <tr>
                                <th class="px-6 py-3 text-left font-semibold">#</th>
                                <th class="px-6 py-3 text-left font-semibold">Name</th>
                                <th class="px-6 py-3 text-left font-semibold">Description</th>
                                <th class="px-6 py-3 text-left font-semibold">Status</th>
                                <th class="px-6 py-3 text-left font-semibold">Created At</th>
                                <th class="px-6 py-3 text-left font-semibold">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="(routing, index) in routings" :key="routing.id" class="odd:bg-white even:bg-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800">
                                <td class="px-6 py-4">{{ index + 1 }}</td>
                                <td class="px-6 py-4 font-medium">{{ routing.name }}</td>
                                <td class="px-6 py-4">{{ routing.description || '-' }}</td>
                                <td class="px-6 py-4">
                                    <span :class="routing.status === 1 ? 'font-semibold text-green-600' : 'font-semibold text-red-600'">
                                        {{ routing.status === 1 ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">{{ dayjs(routing.created_at).format('YYYY-MM-DD') }}</td>
                                <td class="relative flex items-center gap-2 px-6 py-4">
                                    <Button
                                        v-if="can('vaccine-routing.edit')"
                                        variant="outline"
                                        size="sm"
                                        @click="openEditModal(routing)"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </Button>
                                    <Button
                                        v-if="can('vaccine-routing.delete')"
                                        variant="outline"
                                        size="sm"
                                        @click="deleteRouting(routing)"
                                        class="text-red-600 hover:text-red-700 hover:bg-red-50"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </Button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="routings.length === 0" class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No vaccine routings</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new vaccine routing.</p>
                    <div class="mt-6">
                        <Button
                            v-if="can('vaccine-routing.create')"
                            @click="openCreateModal"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Vaccine Routing
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div
            v-if="showModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            @click.self="closeModal"
        >
            <div
                ref="modalRef"
                class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md mx-4 relative"
                @mousedown="startDrag"
            >
                <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        {{ editingRouting ? 'Edit Vaccine Routing' : 'Create Vaccine Routing' }}
                    </h3>
                    <button
                        @click="closeModal"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form @submit.prevent="submitForm" class="p-6">
                    <div class="space-y-4">
                        <!-- Name -->
                        <div>
                            <Label for="name">Name *</Label>
                            <Input
                                id="name"
                                v-model="form.name"
                                type="text"
                                :class="{ 'border-red-500': form.errors.name }"
                                placeholder="Enter vaccine routing name"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <!-- Description -->
                        <div>
                            <Label for="description">Description</Label>
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 dark:bg-gray-700 dark:text-gray-100"
                                placeholder="Enter description (optional)"
                            ></textarea>
                            <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
                        </div>

                        <!-- Status -->
                        <div>
                            <Label for="status">Status *</Label>
                            <select
                                id="status"
                                v-model="form.status"
                                class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 dark:bg-gray-700 dark:text-gray-100"
                            >
                                <option :value="1">Active</option>
                                <option :value="0">Inactive</option>
                            </select>
                            <p v-if="form.errors.status" class="mt-1 text-sm text-red-600">{{ form.errors.status }}</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <Button type="button" variant="outline" @click="closeModal">
                            Cancel
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Saving...' : (editingRouting ? 'Update' : 'Create') }}
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
