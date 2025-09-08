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

interface Supplier {
    id: number;
    name: string;
    supplier_type: 'Local' | 'Foreign';
    address?: string;
    origin?: string;
    contact_person?: string;
    contact_person_email?: string;
    contact_person_mobile?: string;
    status: number;
    created_at: string;
}

// Props
const props = defineProps<{
    suppliers: Supplier[];
    filters: { search?: string; per_page?: number; page?: number };
}>();

useNotifier();
const { can } = usePermissions();
const suppliers = ref<Supplier[]>([...props.suppliers]);

// Filters for export
const filters = ref({ ...props.filters, sort: 'name', direction: 'asc' });

// Export dropdown
const openExportDropdown = ref(false);
const exportPdf = (orientation: 'portrait' | 'landscape' = 'portrait') => {
    const url = route('reports.supplier.pdf', { ...filters.value, orientation });
    window.open(url, '_blank');
};
const exportExcel = () => {
    const url = route('reports.supplier.excel', { ...filters.value });
    window.open(url, '_blank');
};

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
        Object.assign(form, { ...supplier });
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
                const i = suppliers.value.findIndex((s) => s.id === editingSupplier.value!.id);
                if (i !== -1) suppliers.value[i] = { ...suppliers.value[i], ...form };
                resetForm();
            },
            onError: () => Swal.fire('Error!', 'Could not update supplier.', 'error'),
        });
    } else {
        form.post(route('supplier.store'), {
            preserveScroll: true,
            onSuccess: (page) => {
                if ((page as any).props?.suppliers) {
                    suppliers.value = (page as any).props.suppliers;
                } else {
                    suppliers.value.unshift({
                        id: Date.now(),
                        name: form.name,
                        supplier_type: form.supplier_type,
                        address: form.address,
                        origin: form.origin,
                        contact_person: form.contact_person,
                        contact_person_email: form.contact_person_email,
                        contact_person_mobile: form.contact_person_mobile,
                        status: form.status,
                        created_at: new Date().toISOString(),
                    });
                }
                resetForm();
            },
            onError: () => Swal.fire('Error!', 'Could not create supplier.', 'error'),
        });
    }
};

// Toggle status
const toggleStatus = (supplier: Supplier) => {
    const newStatus = supplier.status === 1 ? 0 : 1;
    router.put(
        route('supplier.update', supplier.id),
        { ...supplier, status: newStatus },
        {
            preserveScroll: true,
            onSuccess: () => {
                const i = suppliers.value.findIndex((s) => s.id === supplier.id);
                if (i !== -1) suppliers.value[i].status = newStatus;
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

            <!-- Supplier Table -->
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold">#</th>
                            <th class="px-6 py-3 text-left font-semibold">Name</th>
                            <th class="px-6 py-3 text-left font-semibold">Type</th>
                            <th class="px-6 py-3 text-left font-semibold">Address</th>
                            <th class="px-6 py-3 text-left font-semibold">Contact</th>
                            <th class="px-6 py-3 text-left font-semibold">Status</th>
                            <th class="px-6 py-3 text-left font-semibold">Created At</th>
                            <th class="px-6 py-3 text-left font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        <tr
                            v-for="(supplier, index) in suppliers"
                            :key="supplier.id"
                            class="odd:bg-white even:bg-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800"
                        >
                            <td class="px-6 py-4">{{ index + 1 }}</td>
                            <td class="px-6 py-4">{{ supplier.name }}</td>
                            <td class="px-6 py-4">{{ supplier.supplier_type }}</td>
                            <td class="px-6 py-4">{{ supplier.address || '-' }}</td>
                            <td class="px-6 py-4">{{ supplier.contact_person || '-' }}</td>
                            <td class="px-6 py-4">
                                <span :class="supplier.status === 1 ? 'font-semibold text-green-600' : 'font-semibold text-red-600'">
                                    {{ supplier.status === 1 ? 'Active' : 'Inactive' }}
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
                                        {{ supplier.status === 1 ? 'Inactive' : 'Activate' }}
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="suppliers.length === 0">
                            <td colspan="8" class="px-6 py-6 text-center text-gray-500">No suppliers found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
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
