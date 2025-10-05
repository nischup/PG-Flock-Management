<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import Pagination from '@/components/Pagination.vue';
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

interface Supplier {
    id: number;
    name: string;
    supplier_type: 'Local' | 'Foreign';
    address?: string;
    origin?: string;
    contact_person?: string;
    contact_person_email?: string;
    contact_person_mobile?: string;
    status: string; // 'Active' | 'Inactive'
    created_at: string;
}

// Props
const props = defineProps<{
    suppliers: {
        data: Supplier[];
        meta: { current_page: number; last_page: number; per_page: number; total: number };
    };
    filters: { 
        search?: string; 
        per_page?: number; 
        page?: number;
        status?: string;
        supplier_type?: string;
        date_from?: string;
        date_to?: string;
    };
}>();

const { search, perPage, page, status, supplierType, dateFrom, dateTo } = useListFilters({ routeName: '/supplier', filters: props.filters });
const { can } = usePermissions();

// Export dropdown
const openExportDropdown = ref(false);
const exportPdf = (orientation: 'portrait' | 'landscape' = 'portrait') => {
    const exportFilters = {
        search: search.value,
        per_page: perPage.value,
        status: status.value,
        supplier_type: supplierType.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
        orientation
    };
    const url = route('reports.supplier.pdf', exportFilters);
    window.open(url, '_blank');
};
const exportExcel = () => {
    const exportFilters = {
        search: search.value,
        per_page: perPage.value,
        status: status.value,
        supplier_type: supplierType.value,
        date_from: dateFrom.value,
        date_to: dateTo.value
    };
    const url = route('reports.supplier.excel', exportFilters);
    window.open(url, '_blank');
};

// ✅ Filter methods
const clearFilters = () => {
    search.value = '';
    perPage.value = 10;
    status.value = '';
    supplierType.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    page.value = 1;
};

// ✅ Computed properties for filter summary
const hasActiveFilters = computed(() => {
    return status.value || 
           supplierType.value ||
           dateFrom.value || 
           dateTo.value;
});

useNotifier();

// Dropdown
const openDropdownId = ref<number | null>(null);
const toggleDropdown = (id: number) => {
    openDropdownId.value = openDropdownId.value === id ? null : id;
};

// Click outside
const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.dropdown-menu') && !target.closest('.actions-button') && !target.closest('.export-button')) {
        openDropdownId.value = null;
        openExportDropdown.value = false;
    }
};

// Modal state
const showModal = ref(false);
const editingSupplier = ref<Supplier | null>(null);

// Form
const form = useForm<{
    name: string;
    supplier_type: 'Local' | 'Foreign';
    address: string;
    origin: string;
    contact_person: string;
    contact_person_email: string;
    contact_person_mobile: string;
    status: number;
}>({
    name: '',
    supplier_type: 'Local', // Type now matches union
    address: '',
    origin: '',
    contact_person: '',
    contact_person_email: '',
    contact_person_mobile: '',
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
};

const onDrag = (event: MouseEvent) => {
    if (!isDragging || !modalRef.value) return;
    modalRef.value.style.left = `${event.clientX - offsetX}px`;
    modalRef.value.style.top = `${event.clientY - offsetY}px`;
    modalRef.value.style.position = 'absolute';
    modalRef.value.style.margin = '0';
};

const stopDrag = () => {
    isDragging = false;
};

// Lifecycle
onMounted(() => {
    document.addEventListener('mousemove', onDrag);
    document.addEventListener('mouseup', stopDrag);
    document.addEventListener('click', handleClickOutside);
});
onBeforeUnmount(() => {
    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('mouseup', stopDrag);
    document.removeEventListener('click', handleClickOutside);
});

// Modal
const openModal = (supplier: Supplier | null = null) => {
    if (supplier) {
        editingSupplier.value = supplier;
        Object.assign(form, { 
            ...supplier, 
            status: supplier.status === 'Active' ? 1 : 0 
        });
    } else {
        editingSupplier.value = null;
        form.reset();
        form.supplier_type = 'Local';
        form.status = 1;
    }
    showModal.value = true;
};

const resetForm = () => {
    form.reset();
    form.supplier_type = 'Local';
    form.status = 1;
    editingSupplier.value = null;
    showModal.value = false;
};

// Submit
const submit = () => {
    if (!form.name.trim()) return;

    if (editingSupplier.value) {
        form.put(route('supplier.update', editingSupplier.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                resetForm();
                // Refresh the page to show updated data
                window.location.reload();
            },
            onError: () => Swal.fire('Error!', 'Could not update supplier.', 'error'),
        });
    } else {
        form.post(route('supplier.store'), {
            preserveScroll: true,
            onSuccess: () => {
                resetForm();
                // Refresh the page to show updated data
                window.location.reload();
            },
            onError: () => Swal.fire('Error!', 'Could not create supplier.', 'error'),
        });
    }
};

// Toggle status
const toggleStatus = (supplier: Supplier) => {
    const newStatus = supplier.status === 'Active' ? 0 : 1;
    router.put(
        route('supplier.update', supplier.id),
        { ...supplier, status: newStatus },
        {
            preserveScroll: true,
            onSuccess: () => {
                // Refresh the page to show updated data
                window.location.reload();
            },
            onError: () => Swal.fire('Error!', 'Could not update status.', 'error'),
            onFinish: () => {
                openDropdownId.value = null;
            },
        },
    );
};

// Breadcrumbs
const breadcrumbs = [
    { title: 'Master Setup', href: '/master-setup' },
    { title: 'Supplier', href: '/master-setup/supplier' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Suppliers" />

        <div class="px-4 py-6">
            <div class="mb-4 flex items-center justify-between">
                <HeadingSmall title="Suppliers List" />

                <div class="relative flex items-center gap-2">
                    <Button v-if="can('supplier.create')" class="bg-chicken text-white hover:bg-yellow-600" @click="openModal()">+ Add New</Button>

                    <!-- Export Dropdown -->
                    <div class="relative">
                        <Button class="export-button bg-green-600 text-white hover:bg-green-700" @click="openExportDropdown = !openExportDropdown">
                            Export Report ▼
                        </Button>
                        <div v-if="openExportDropdown" class="absolute right-0 z-20 mt-2 w-44 rounded border bg-white shadow-lg">
                            <button
                                @click="
                                    exportPdf('portrait');
                                    openExportDropdown = false;
                                "
                                class="block w-full px-4 py-2 text-left hover:bg-gray-100"
                            >
                                PDF
                            </button>
                            <button
                                @click="
                                    exportExcel();
                                    openExportDropdown = false;
                                "
                                class="block w-full px-4 py-2 text-left hover:bg-gray-100"
                            >
                                Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Filters</h3>
                    <button
                        @click="clearFilters"
                        class="text-sm text-gray-600 hover:text-gray-800"
                    >
                        Clear All
                    </button>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Supplier name, contact..."
                            class="block w-full px-3 py-2 text-sm text-gray-900 bg-white border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select
                            v-model="status"
                            class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">All Status</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>

                    <!-- Supplier Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Supplier Type</label>
                        <select
                            v-model="supplierType"
                            class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">All Types</option>
                            <option value="Local">Local</option>
                            <option value="Foreign">Foreign</option>
                        </select>
                    </div>

                    <!-- Per Page -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Per Page</label>
                        <select
                            v-model="perPage"
                            class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input
                                v-model="dateFrom"
                                type="date"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                            <input
                                v-model="dateTo"
                                type="date"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Supplier Table -->
            <div class="mt-4 overflow-x-auto rounded-xl bg-white shadow dark:bg-gray-800">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr>
                            <th class="border-b px-4 py-2 bg-blue-500 text-white font-semibold text-sm whitespace-nowrap">#</th>
                            <th class="border-b px-4 py-2 bg-green-500 text-white font-semibold text-sm whitespace-nowrap">Name</th>
                            <th class="border-b px-4 py-2 bg-purple-500 text-white font-semibold text-sm whitespace-nowrap">Type</th>
                            <th class="border-b px-4 py-2 bg-orange-500 text-white font-semibold text-sm whitespace-nowrap">Address</th>
                            <th class="border-b px-4 py-2 bg-pink-500 text-white font-semibold text-sm whitespace-nowrap">Contact</th>
                            <th class="border-b px-4 py-2 bg-indigo-500 text-white font-semibold text-sm whitespace-nowrap">Status</th>
                            <th class="border-b px-4 py-2 bg-red-500 text-white font-semibold text-sm whitespace-nowrap">Created At</th>
                            <th class="border-b px-4 py-2 bg-gray-600 text-white font-semibold text-sm whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        <tr
                            v-for="(supplier, index) in (props.suppliers?.data ?? [])"
                            :key="supplier.id"
                            class="odd:bg-white even:bg-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800"
                        >
                            <td class="px-6 py-4">{{ ((props.suppliers?.meta?.current_page || 1) - 1) * (props.suppliers?.meta?.per_page || 10) + index + 1 }}</td>
                            <td class="px-6 py-4">{{ supplier.name }}</td>
                            <td class="px-6 py-4">{{ supplier.supplier_type }}</td>
                            <td class="px-6 py-4">{{ supplier.address || '-' }}</td>
                            <td class="px-6 py-4">{{ supplier.contact_person || '-' }}</td>
                            <td class="px-6 py-4">
                                <span :class="supplier.status === 'Active' ? 'font-semibold text-green-600' : 'font-semibold text-red-600'">
                                    {{ supplier.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ dayjs(supplier.created_at).format('YYYY-MM-DD') }}</td>
                            <td class="relative px-6 py-4">
                                <Button
                                    size="sm"
                                    class="actions-button bg-gray-500 text-white hover:bg-gray-600"
                                    @click.stop="toggleDropdown(supplier.id)"
                                >
                                    Actions ▼
                                </Button>
                                <div
                                    v-if="openDropdownId === supplier.id"
                                    class="dropdown-menu absolute z-10 mt-1 w-40 rounded border bg-white shadow-md"
                                >
                                    <button class="w-full px-4 py-2 text-left hover:bg-gray-100" @click="openModal(supplier)">✏ Edit</button>
                                    <button class="w-full px-4 py-2 text-left hover:bg-gray-100" @click="toggleStatus(supplier)">
                                        {{ supplier.status === 'Active' ? 'Inactive' : 'Activate' }}
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="(props.suppliers?.data ?? []).length === 0">
                            <td colspan="8" class="px-6 py-6 text-center text-gray-500">No suppliers found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <Pagination v-if="props.suppliers?.meta" :meta="props.suppliers.meta" class="mt-6" />
        </div>

        <!-- Draggable Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex justify-center bg-black/30 pt-6" @click.self="resetForm">
            <div ref="modalRef" class="w-full max-w-4xl rounded-lg border border-gray-300 bg-white shadow-lg" style="top: 100px; position: absolute">
                <div class="flex cursor-move items-center justify-between border-b border-gray-200 p-4" @mousedown="startDrag">
                    <h3 class="text-xl font-semibold text-gray-900">{{ editingSupplier ? 'Edit Supplier' : 'Add New Supplier' }}</h3>
                    <button
                        type="button"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-200 hover:text-gray-900"
                        @click="resetForm"
                    >
                        ✕
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-4 p-4 md:grid-cols-2">
                    <div>
                        <Label for="name" class="mb-2">Supplier Name</Label>
                        <Input v-model="form.name" id="name" />
                    </div>
                    <div>
                        <Label for="supplier_type" class="mb-2">Supplier Type</Label>
                        <select v-model="form.supplier_type" id="supplier_type" class="w-full rounded border p-2">
                            <option value="Local">Local</option>
                            <option value="Foreign">Foreign</option>
                        </select>
                    </div>
                    <div>
                        <Label for="address" class="mb-2">Address</Label>
                        <Input v-model="form.address" id="address" />
                    </div>
                    <div>
                        <Label for="origin" class="mb-2">Origin</Label>
                        <Input v-model="form.origin" id="origin" />
                    </div>
                    <div>
                        <Label for="contact_person" class="mb-2">Contact Person</Label>
                        <Input v-model="form.contact_person" id="contact_person" />
                    </div>
                    <div>
                        <Label for="contact_person_email" class="mb-2">Email</Label>
                        <Input v-model="form.contact_person_email" id="contact_person_email" />
                    </div>
                    <div>
                        <Label for="contact_person_mobile" class="mb-2">Mobile</Label>
                        <Input v-model="form.contact_person_mobile" id="contact_person_mobile" />
                    </div>
                    <div>
                        <Label for="status" class="mb-2">Status</Label>
                        <select v-model="form.status" id="status" class="w-full rounded border p-2">
                            <option :value="1">Active</option>
                            <option :value="0">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end border-t border-gray-200 p-4">
                    <Button class="mr-2 bg-gray-300 text-black" @click="resetForm">Cancel</Button>
                    <Button class="bg-chicken text-white" @click="submit">{{ editingSupplier ? 'Update' : 'Save' }}</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
