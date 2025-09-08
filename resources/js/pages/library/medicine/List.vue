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
import { onBeforeUnmount, onMounted, ref } from 'vue';

// Medicine interface
interface Medicine {
    id: number;
    name: string;
    status: number; // 0 or 1
    created_at: string;
}

// Props
const props = defineProps<{
    medicines: Medicine[];
    filters: { search?: string; per_page?: number; page?: number };
}>();

// Filters for export
const filters = ref({
    search: '',
    sort: 'name',
    direction: 'asc',
});

const openDropdown = ref(false);

const exportPdf = (orientation: 'portrait' | 'landscape' = 'portrait') => {
    const url = route('reports.medicine.pdf', { ...filters.value, orientation });
    window.open(url, '_blank');
};

const exportExcel = () => {
    const url = route('reports.medicine.excel', { ...filters.value });
    window.open(url, '_blank');
};

useListFilters({
    routeName: '/medicine',
    filters: props.filters,
});

const { can } = usePermissions();
const medicines = ref<Medicine[]>([...props.medicines]);

// Modal state
const showModal = ref(false);
const editingMedicine = ref<Medicine | null>(null);

// Form
const form = useForm({
    name: '',
    status: 1, // default Active = 1
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

// Dropdown state
const openDropdownId = ref<number | null>(null);
const toggleDropdown = (id: number) => {
    openDropdownId.value = openDropdownId.value === id ? null : id;
};

// Click outside dropdown
const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.dropdown-menu') && !target.closest('.actions-button')) {
        openDropdownId.value = null;
    }
};

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

useNotifier();

// Open modal
const openModal = (medicine: Medicine | null = null) => {
    if (medicine) {
        editingMedicine.value = medicine;
        form.name = medicine.name;
        form.status = medicine.status;
    } else {
        editingMedicine.value = null;
        form.reset();
        form.status = 1; // Active
    }
    showModal.value = true;
};

// Reset form
const resetForm = () => {
    form.reset();
    form.status = 1;
    editingMedicine.value = null;
    showModal.value = false;
};

// Submit
const submit = () => {
    if (!form.name.trim()) return;

    if (editingMedicine.value) {
        form.put(route('medicine.update', editingMedicine.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                const i = medicines.value.findIndex((m) => m.id === editingMedicine.value!.id);
                if (i !== -1) {
                    medicines.value[i] = { ...medicines.value[i], ...form };
                }
                resetForm();
            },
            onError: () => Swal.fire('Error!', 'Could not update medicine.', 'error'),
        });
    } else {
        form.post(route('medicine.store'), {
            preserveScroll: true,
            onSuccess: (page) => {
                if ((page as any).props?.medicines) {
                    medicines.value = (page as any).props.medicines;
                } else {
                    medicines.value.unshift({ id: Date.now(), ...form, created_at: new Date().toISOString() });
                }
                resetForm();
            },
            onError: () => Swal.fire('Error!', 'Could not create medicine.', 'error'),
        });
    }
};

// Toggle status
const toggleStatus = (medicine: Medicine) => {
    const newStatus = medicine.status === 1 ? 0 : 1;
    router.put(
        route('medicine.update', medicine.id),
        { name: medicine.name, status: newStatus },
        {
            preserveScroll: true,
            onSuccess: () => {
                const i = medicines.value.findIndex((m) => m.id === medicine.id);
                if (i !== -1) medicines.value[i].status = newStatus;
            },
            onError: () => Swal.fire('Error!', 'Could not update status.', 'error'),
            onFinish: () => (openDropdownId.value = null),
        },
    );
};

// Breadcrumbs
const breadcrumbs = [
    { title: 'Library', href: '/library' },
    { title: 'Medicine', href: '/library/medicine' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Medicines" />

        <!-- Filters -->
        <FilterControls :filters="props.filters" routeName="/medicine" />

        <div class="px-4 py-6">
            <div class="mb-4 flex items-center justify-between">
                <HeadingSmall title="Medicine List" />
                <div class="relative flex items-center gap-2">
                    <!-- Add New Button -->
                    <Button v-if="can('medicine.create')" class="bg-chicken text-white hover:bg-yellow-600" @click="openModal()"> + Add New </Button>

                    <!-- Export Report Dropdown -->
                    <div class="relative">
                        <Button class="bg-green-600 text-white hover:bg-green-700" @click="openDropdown = !openDropdown"> Export Report ▼ </Button>
                        <div v-if="openDropdown" class="absolute right-0 z-20 mt-2 w-44 rounded border bg-white shadow-lg">
                            <button
                                @click="
                                    exportPdf('portrait');
                                    openDropdown = false;
                                "
                                class="block w-full px-4 py-2 text-left hover:bg-gray-100"
                            >
                                PDF
                            </button>
                            <button
                                @click="
                                    exportExcel();
                                    openDropdown = false;
                                "
                                class="block w-full px-4 py-2 text-left hover:bg-gray-100"
                            >
                                Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medicine Table -->
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold">#</th>
                            <th class="px-6 py-3 text-left font-semibold">Name</th>
                            <th class="px-6 py-3 text-left font-semibold">Status</th>
                            <th class="px-6 py-3 text-left font-semibold">Created At</th>
                            <th class="px-6 py-3 text-left font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr
                            v-for="(medicine, index) in medicines"
                            :key="medicine.id"
                            class="odd:bg-white even:bg-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800"
                        >
                            <td class="px-6 py-4">{{ index + 1 }}</td>
                            <td class="px-6 py-4">{{ medicine.name }}</td>
                            <td class="px-6 py-4">
                                <span :class="medicine.status == 1 ? 'font-semibold text-green-600' : 'font-semibold text-red-600'">
                                    {{ medicine.status == 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ dayjs(medicine.created_at).format('YYYY-MM-DD') }}</td>
                            <td class="relative px-6 py-4">
                                <Button size="sm" class="actions-button bg-gray-500 text-white hover:bg-gray-600" @click="toggleDropdown(medicine.id)"
                                    >Actions ▼</Button
                                >
                                <div
                                    v-if="openDropdownId === medicine.id"
                                    class="dropdown-menu absolute z-10 mt-1 w-40 rounded border bg-white shadow-md"
                                    @click.stop
                                >
                                    <button class="w-full px-4 py-2 text-left hover:bg-gray-100" @click="openModal(medicine)">✏ Edit</button>
                                    <button class="w-full px-4 py-2 text-left hover:bg-gray-100" @click="toggleStatus(medicine)">
                                        {{ medicine.status === 1 ? 'Inactive' : 'Activate' }}
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="medicines.length === 0">
                            <td colspan="5" class="px-6 py-6 text-center text-gray-500">No medicines found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex justify-center bg-black/30 pt-6" @click.self="resetForm">
            <div ref="modalRef" class="w-full max-w-2xl rounded-lg border border-gray-300 bg-white shadow-lg" style="top: 100px; position: absolute">
                <div class="flex cursor-move items-center justify-between border-b border-gray-200 p-4" @mousedown="startDrag">
                    <h3 class="text-xl font-semibold text-gray-900">{{ editingMedicine ? 'Edit Medicine' : 'Add New Medicine' }}</h3>
                    <button
                        type="button"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-gray-200 hover:text-gray-900"
                        @click="resetForm"
                    >
                        ✕
                    </button>
                </div>

                <div class="space-y-4 p-4">
                    <div>
                        <Label for="name" class="mb-2">Medicine Name</Label>
                        <Input v-model="form.name" id="name" />
                        <span v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</span>
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
                    <Button class="bg-chicken text-white" @click="submit">{{ editingMedicine ? 'Update' : 'Save' }}</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
