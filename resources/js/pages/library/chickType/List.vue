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

interface ChickType {
    id: number;
    name: string;
    status: number;
    created_at: string;
}

const props = defineProps<{
    chickTypes: ChickType[];
    filters: { search?: string; per_page?: number; page?: number };
}>();

useNotifier();
const { can } = usePermissions();
const chickTypes = ref<ChickType[]>([...props.chickTypes]);

// Filters for export
const filters = ref({ ...props.filters, sort: 'name', direction: 'asc' });

// Export dropdown
const openExportDropdown = ref(false);
const exportPdf = (orientation: 'portrait' | 'landscape' = 'portrait') => {
    const url = route('reports.chick-type.pdf', { ...filters.value, orientation });
    window.open(url, '_blank');
};
const exportExcel = () => {
    const url = route('reports.chick-type.excel', { ...filters.value });
    window.open(url, '_blank');
};

// Modal state
const showModal = ref(false);
const editingChickType = ref<ChickType | null>(null);

// Form
const form = useForm({
    name: '',
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
const openModal = (chickType: ChickType | null = null) => {
    if (chickType) {
        editingChickType.value = chickType;
        form.name = chickType.name;
        form.status = chickType.status;
    } else {
        editingChickType.value = null;
        form.reset();
        form.status = 1;
    }
    showModal.value = true;
};

const resetForm = () => {
    form.reset();
    form.status = 1;
    editingChickType.value = null;
    showModal.value = false;
};

// Submit
const submit = () => {
    if (!form.name.trim()) return;

    if (editingChickType.value) {
        form.put(route('chick-type.update', editingChickType.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                const i = chickTypes.value.findIndex((c) => c.id === editingChickType.value!.id);
                if (i !== -1) {
                    chickTypes.value[i] = { ...chickTypes.value[i], name: form.name, status: form.status };
                }
                resetForm();
            },
            onError: () => Swal.fire('Error!', 'Could not update chick type.', 'error'),
        });
    } else {
        form.post(route('chick-type.store'), {
            preserveScroll: true,
            onSuccess: (page) => {
                if ((page as any).props?.chickTypes) {
                    chickTypes.value = (page as any).props.chickTypes;
                } else {
                    chickTypes.value.unshift({
                        id: Date.now(),
                        name: form.name,
                        status: form.status,
                        created_at: new Date().toISOString(),
                    });
                }
                resetForm();
            },
            onError: () => Swal.fire('Error!', 'Could not create chick type.', 'error'),
        });
    }
};

// Toggle status
const toggleStatus = (chickType: ChickType) => {
    const newStatus = chickType.status === 1 ? 0 : 1;
    router.put(
        route('chick-type.update', chickType.id),
        { name: chickType.name, status: newStatus },
        {
            preserveScroll: true,
            onSuccess: () => {
                const i = chickTypes.value.findIndex((c) => c.id === chickType.id);
                if (i !== -1) chickTypes.value[i].status = newStatus;
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
    { title: 'Chick Type', href: '/master-setup/chick-type' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Chick Types" />

        <div class="px-4 py-6">
            <div class="mb-4 flex items-center justify-between">
                <HeadingSmall title="Chick Types List" />

                <div class="relative flex items-center gap-2">
                    <!-- Add New Button -->
                    <Button v-if="can('chick-type.create')" class="bg-chicken text-white hover:bg-yellow-600" @click="openModal()">+ Add New</Button>

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

            <!-- Chick Type Table -->
            <div class="mt-4 overflow-x-auto rounded-xl bg-white shadow dark:bg-gray-800">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr>
                            <th class="border-b px-4 py-2 bg-blue-500 text-white font-semibold text-sm whitespace-nowrap">#</th>
                            <th class="border-b px-4 py-2 bg-green-500 text-white font-semibold text-sm whitespace-nowrap">Name</th>
                            <th class="border-b px-4 py-2 bg-purple-500 text-white font-semibold text-sm whitespace-nowrap">Status</th>
                            <th class="border-b px-4 py-2 bg-orange-500 text-white font-semibold text-sm whitespace-nowrap">Created At</th>
                            <th class="border-b px-4 py-2 bg-gray-600 text-white font-semibold text-sm whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(chickType, index) in chickTypes"
                            :key="chickType.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            <td class="border-b px-4 py-2">{{ index + 1 }}</td>
                            <td class="border-b px-4 py-2">{{ chickType.name }}</td>
                            <td class="border-b px-4 py-2">
                                <span :class="chickType.status === 1 ? 'font-semibold text-green-600' : 'font-semibold text-red-600'">
                                    {{ chickType.status === 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="border-b px-4 py-2">{{ dayjs(chickType.created_at).format('YYYY-MM-DD') }}</td>
                            <td class="border-b px-4 py-2 relative">
                                <Button
                                    size="sm"
                                    class="actions-button bg-gray-500 text-white hover:bg-gray-600"
                                    @click.stop="toggleDropdown(chickType.id)"
                                >
                                    Actions ▼
                                </Button>
                                <div
                                    v-if="openDropdownId === chickType.id"
                                    class="dropdown-menu absolute z-10 mt-1 w-40 rounded border bg-white shadow-md"
                                >
                                    <button class="w-full px-4 py-2 text-left hover:bg-gray-100" @click="openModal(chickType)">✏ Edit</button>
                                    <button class="w-full px-4 py-2 text-left hover:bg-gray-100" @click="toggleStatus(chickType)">
                                        {{ chickType.status === 1 ? 'Inactive' : 'Activate' }}
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="chickTypes.length === 0">
                            <td colspan="5" class="border-b px-4 py-6 text-center text-gray-500">No chick types found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Draggable Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex justify-center bg-black/30 pt-6" @click.self="resetForm">
            <div ref="modalRef" class="w-full max-w-2xl rounded-lg border border-gray-300 bg-white shadow-lg" style="top: 100px; position: absolute">
                <div class="flex cursor-move items-center justify-between border-b border-gray-200 p-4" @mousedown="startDrag">
                    <h3 class="text-xl font-semibold text-gray-900">{{ editingChickType ? 'Edit Chick Type' : 'Add New Chick Type' }}</h3>
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
                        <Label for="name" class="mb-2">Chick Type Name</Label>
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
                    <Button class="bg-chicken text-white" @click="submit">{{ editingChickType ? 'Update' : 'Save' }}</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
