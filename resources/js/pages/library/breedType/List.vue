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
import Swal from 'sweetalert2';
import { onBeforeUnmount, onMounted, ref } from 'vue';

interface BreedType {
    id: number;
    name: string;
    status: number;
    created_at: string;
}

const props = defineProps<{
    breedTypes: BreedType[];
    filters: { search?: string; per_page?: number; page?: number };
}>();

useListFilters({
    routeName: '/breed-type',
    filters: props.filters,
});

useNotifier();
const { can } = usePermissions();
const breedTypes = ref<BreedType[]>([...props.breedTypes]);

// Filters for export
const filters = ref({ ...props.filters, sort: 'name', direction: 'asc' });

// Export dropdown
const openExportDropdown = ref(false);
const exportPdf = (orientation: 'portrait' | 'landscape' = 'portrait') => {
    const url = route('reports.breed-type.pdf', { ...filters.value, orientation });
    window.open(url, '_blank');
};
const exportExcel = () => {
    const url = route('reports.breed-type.excel', { ...filters.value });
    window.open(url, '_blank');
};

// Modal state
const showModal = ref(false);
const editingBreedType = ref<BreedType | null>(null);

// Modal form
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

// Open modal
const openModal = (breedType: BreedType | null = null) => {
    if (breedType) {
        editingBreedType.value = breedType;
        form.name = breedType.name;
        form.status = breedType.status;
    } else {
        editingBreedType.value = null;
        form.reset();
        form.status = 1;
    }
    showModal.value = true;
};

// Reset form
const resetForm = () => {
    form.reset();
    form.status = 1;
    editingBreedType.value = null;
    showModal.value = false;
};

// Submit (Create/Update)
const submit = () => {
    if (!form.name.trim()) return;

    if (editingBreedType.value) {
        form.put(route('breed-type.update', editingBreedType.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                const i = breedTypes.value.findIndex((c) => c.id === editingBreedType.value!.id);
                if (i !== -1) {
                    breedTypes.value[i] = { ...breedTypes.value[i], name: form.name, status: form.status };
                }
                resetForm();
            },
        });
    } else {
        form.post(route('breed-type.store'), {
            preserveScroll: true,
            onSuccess: (page) => {
                if ((page as any).props?.breedTypes) {
                    breedTypes.value = (page as any).props.breedTypes;
                } else {
                    breedTypes.value.unshift({
                        id: Date.now(),
                        name: form.name,
                        status: form.status,
                        created_at: new Date().toISOString(),
                    });
                }
                resetForm();
            },
        });
    }
};

// Dropdown for actions
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
    document.addEventListener('click', handleClickOutside);
});
onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Toggle status
const toggleStatus = (breedType: BreedType) => {
    const newStatus = breedType.status === 1 ? 0 : 1;
    router.put(
        route('breed-type.update', breedType.id),
        { name: breedType.name, status: newStatus },
        {
            preserveScroll: true,
            onSuccess: () => {
                const i = breedTypes.value.findIndex((c) => c.id === breedType.id);
                if (i !== -1) breedTypes.value[i].status = newStatus;
            },
            onError: () => Swal.fire('Error!', 'Could not update status.', 'error'),
            onFinish: () => (openDropdownId.value = null),
        },
    );
};

// Breadcrumbs
const breadcrumbs = [
    { title: 'Master Setup', href: '/master-setup' },
    { title: 'Breed Type', href: '/master-setup/breed-type' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Breed Types" />

        <div class="px-4 py-6">
            <div class="mb-4 flex items-center justify-between">
                <HeadingSmall title="Breed Types List" />

                <div class="relative flex items-center gap-2">
                    <Button v-if="can('breed-type.create')" class="bg-chicken text-white hover:bg-yellow-600" @click="openModal()">
                        + Add New
                    </Button>

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

            <!-- Table -->
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
                        <tr v-for="(breedType, index) in breedTypes" :key="breedType.id">
                            <td class="px-6 py-4">{{ index + 1 }}</td>
                            <td class="px-6 py-4">{{ breedType.name }}</td>
                            <td class="px-6 py-4">
                                <span :class="breedType.status === 1 ? 'font-semibold text-green-600' : 'font-semibold text-red-600'">
                                    {{ breedType.status === 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ breedType.created_at }}</td>
                            <td class="relative px-6 py-4">
                                <Button
                                    size="sm"
                                    class="actions-button bg-gray-500 text-white hover:bg-gray-600"
                                    @click.stop="toggleDropdown(breedType.id)"
                                    >Actions ▼</Button
                                >
                                <div
                                    v-if="openDropdownId === breedType.id"
                                    class="dropdown-menu absolute z-10 mt-1 w-40 rounded border bg-white shadow-md"
                                >
                                    <button class="w-full px-4 py-2 text-left hover:bg-gray-100" @click="openModal(breedType)">✏ Edit</button>
                                    <button class="w-full px-4 py-2 text-left hover:bg-gray-100" @click="toggleStatus(breedType)">
                                        {{ breedType.status === 1 ? 'Inactive' : 'Activate' }}
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="breedTypes.length === 0">
                            <td colspan="5" class="px-6 py-6 text-center text-gray-500">No breed types found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Draggable Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex justify-center bg-black/30 pt-6" @click.self="resetForm">
            <div ref="modalRef" class="w-full max-w-2xl rounded-lg border border-gray-300 bg-white shadow-lg" style="top: 100px; position: absolute">
                <div class="flex cursor-move items-center justify-between border-b border-gray-200 p-4" @mousedown="startDrag">
                    <h3 class="text-xl font-semibold text-gray-900">{{ editingBreedType ? 'Edit Breed Type' : 'Add New Breed Type' }}</h3>
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
                        <Label for="name" class="mb-2">Breed Type Name</Label>
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
                    <Button class="bg-chicken text-white" @click="submit">{{ editingBreedType ? 'Update' : 'Save' }}</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
