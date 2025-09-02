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

interface Unit {
    id: number;
    name: string;
    status: string; // 'Active' | 'Inactive'
    created_at: string;
}

// Filters for export
const filters = ref({
    search: '',
    sort: 'name',
    direction: 'asc',
});

const openDropdown = ref(false);

const exportPdf = (orientation: 'portrait' | 'landscape' = 'portrait') => {
    const url = route('reports.unit.pdf', { ...filters.value, orientation });
    window.open(url, '_blank');
};

const exportExcel = () => {
    const url = route('reports.unit.excel', { ...filters.value });
    window.open(url, '_blank');
};

const props = defineProps<{
    units: Unit[];
    filters: { search?: string; per_page?: number; page?: number };
}>();

useNotifier();
const { can } = usePermissions();

// State
const units = ref<Unit[]>([...props.units]);

// Modal state
const showModal = ref(false);
const editingUnit = ref<Unit | null>(null);
const form = useForm({
    name: '',
    status: 'Active',
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
    document.addEventListener('mousemove', onDrag);
    document.addEventListener('mouseup', stopDrag);
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
    document.removeEventListener('mousemove', onDrag);
    document.removeEventListener('mouseup', stopDrag);
};

// Modal handlers
const openModal = (unit: Unit | null = null) => {
    if (unit) {
        editingUnit.value = unit;
        form.name = unit.name;
        form.status = unit.status;
    } else {
        editingUnit.value = null;
        form.reset();
        form.status = 'Active';
    }
    showModal.value = true;
};

const resetForm = () => {
    form.reset();
    form.status = 'Active';
    editingUnit.value = null;
    showModal.value = false;
};

// Submit
const submit = () => {
    if (!form.name.trim()) return;

    if (editingUnit.value) {
        form.put(route('unit.update', editingUnit.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                const i = units.value.findIndex((u) => u.id === editingUnit.value!.id);
                if (i !== -1) {
                    units.value[i] = { ...units.value[i], name: form.name, status: form.status };
                }
                resetForm();
            },
            onError: () => Swal.fire('Error!', 'Could not update unit.', 'error'),
        });
    } else {
        form.post(route('unit.store'), {
            preserveScroll: true,
            onSuccess: (page) => {
                if ((page as any).props?.units) {
                    units.value = (page as any).props.units;
                } else {
                    units.value.unshift({
                        id: Date.now(),
                        name: form.name,
                        status: form.status,
                        created_at: new Date().toISOString(),
                    });
                }
                resetForm();
            },
            onError: () => Swal.fire('Error!', 'Could not create unit.', 'error'),
        });
    }
};

// Toggle status
const toggleStatus = (unit: Unit) => {
    const newStatus = unit.status === 'Active' ? 'Inactive' : 'Active';
    router.put(
        route('unit.update', unit.id),
        { name: unit.name, status: newStatus },
        {
            preserveScroll: true,
            onSuccess: () => {
                const i = units.value.findIndex((u) => u.id === unit.id);
                if (i !== -1) units.value[i].status = newStatus;
            },
            onError: () => Swal.fire('Error!', 'Could not update status.', 'error'),
        },
    );
};

// Close dropdowns on outside click
const handleClickOutside = (event: MouseEvent) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.pdf-dropdown') && !target.closest('.pdf-button')) {
        openDropdown.value = false;
    }
};
onMounted(() => document.addEventListener('click', handleClickOutside));
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside));

// Breadcrumbs
const breadcrumbs = [
    { title: 'Master Setup', href: '/master-setup' },
    { title: 'Unit', href: '/master-setup/unit' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Units" />

        <div class="px-4 py-6">
            <div class="mb-4 flex items-center justify-between">
                <HeadingSmall title="Unit List" />
                <div class="relative flex items-center gap-2">
                    <Button v-if="can('unit.create')" class="bg-chicken text-white hover:bg-yellow-600" @click="openModal()"> + Add New </Button>

                    <!-- Export Dropdown -->
                    <div class="pdf-dropdown relative">
                        <Button class="pdf-button bg-green-600 text-white hover:bg-green-700" @click="openDropdown = !openDropdown">
                            Export Report ▼
                        </Button>
                        <div v-if="openDropdown" class="absolute right-0 z-20 mt-2 w-40 rounded border bg-white shadow-lg">
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

            <!-- Unit Table -->
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold">#</th>
                            <th class="px-6 py-3 text-left font-semibold">Name</th>
                            <th class="px-6 py-3 text-left font-semibold">Status</th>
                            <th class="px-6 py-3 text-left font-semibold">Created At</th>
                            <th class="px-6 py-3 text-left font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        <tr
                            v-for="(unit, index) in units"
                            :key="unit.id"
                            class="odd:bg-white even:bg-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800"
                        >
                            <td class="px-6 py-4">{{ index + 1 }}</td>
                            <td class="px-6 py-4">{{ unit.name }}</td>
                            <td class="px-6 py-4">
                                <span :class="unit.status === 'Active' ? 'font-semibold text-green-600' : 'font-semibold text-red-600'">
                                    {{ unit.status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ dayjs(unit.created_at).format('DD MMM YYYY,') }}</td>
                            <td class="flex gap-4 px-6 py-4">
                                <button class="font-medium text-indigo-600 hover:underline" @click="openModal(unit)">Edit</button>
                                <button class="font-medium text-red-600 hover:underline" @click="toggleStatus(unit)">
                                    {{ unit.status === 'Active' ? 'Inactive' : 'Activate' }}
                                </button>
                            </td>
                        </tr>

                        <tr v-if="units.length === 0">
                            <td colspan="5" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">No units found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex justify-center bg-black/30 pt-6" @click.self="resetForm">
            <div ref="modalRef" class="w-full max-w-2xl rounded-lg border border-gray-300 bg-white shadow-lg" style="top: 100px; position: absolute">
                <div class="flex cursor-move items-center justify-between border-b border-gray-200 p-4" @mousedown="startDrag">
                    <h3 class="text-xl font-semibold text-gray-900">
                        {{ editingUnit ? 'Edit Unit' : 'Add New Unit' }}
                    </h3>
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
                        <Label for="name" class="mb-2">Unit Name</Label>
                        <Input v-model="form.name" id="name" />
                        <span v-if="form.errors.name" class="text-sm text-red-600">
                            {{ form.errors.name }}
                        </span>
                    </div>

                    <div>
                        <Label for="status" class="mb-2">Status</Label>
                        <select v-model="form.status" id="status" class="w-full rounded border p-2">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end border-t border-gray-200 p-4">
                    <Button class="mr-2 bg-gray-300 text-black" @click="resetForm">Cancel</Button>
                    <Button class="bg-chicken text-white" @click="submit">
                        {{ editingUnit ? 'Update' : 'Save' }}
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
