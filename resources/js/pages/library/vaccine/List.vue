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

interface Vaccine {
    id: number;
    vaccine_type_id: number;
    vaccine_type_name: string;
    name: string;
    status: number;
    created_at: string;
    applicator: string;
    dose: string;
    note: string;
}

// Export filters
const filters = ref({
    search: '',
    sort: 'name',
    direction: 'asc',
});

const openExportDropdown = ref(false);
const exportPdf = (orientation: 'portrait' | 'landscape' = 'portrait') => {
    const url = route('reports.vaccines.pdf', { ...filters.value, orientation });
    window.open(url, '_blank');
};
const exportExcel = () => {
    const url = route('reports.vaccines.excel', { ...filters.value });
    window.open(url, '_blank');
};

// Props
const props = defineProps<{
    vaccines: Vaccine[];
    vaccineTypes: Array<{ id: number; name: string }>;
    filters: { search?: string; per_page?: number; page?: number };
}>();

useNotifier();
const { can } = usePermissions();

// Local state
const vaccines = ref<Vaccine[]>([...props.vaccines]);

// Modal state
const showModal = ref(false);
const editingVaccine = ref<Vaccine | null>(null);
const form = useForm({
    vaccine_type_id: props.vaccineTypes.length ? props.vaccineTypes[0].id : 0,
    name: '',
    applicator: '',
    dose: '',
    note: '',
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

// Open modal
const openModal = (vaccine: Vaccine | null = null) => {
    if (vaccine) {
        editingVaccine.value = vaccine;
        form.vaccine_type_id = vaccine.vaccine_type_id;
        form.name = vaccine.name;
        form.applicator = vaccine.applicator;
        form.dose = vaccine.dose;
        form.note = vaccine.note;
        form.status = vaccine.status;
    } else {
        editingVaccine.value = null;
        form.reset();
        form.vaccine_type_id = props.vaccineTypes.length ? props.vaccineTypes[0].id : 0;
        form.status = 1;
    }
    showModal.value = true;
};

// Reset modal
const resetForm = () => {
    form.reset();
    form.vaccine_type_id = props.vaccineTypes.length ? props.vaccineTypes[0].id : 0;
    form.status = 1;
    editingVaccine.value = null;
    showModal.value = false;
};

// Submit (Create/Update)
const submit = () => {
    if (!form.name.trim()) return;
    if (editingVaccine.value) {
        form.put(route('vaccine.update', editingVaccine.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                const i = vaccines.value.findIndex((v) => v.id === editingVaccine.value!.id);
                if (i !== -1) {
                    vaccines.value[i] = {
                        ...vaccines.value[i],
                        vaccine_type_id: form.vaccine_type_id,
                        vaccine_type_name: props.vaccineTypes.find((vt) => vt.id === form.vaccine_type_id)?.name || '',
                        name: form.name,
                        applicator: form.applicator,
                        dose: form.dose,
                        note: form.note,
                        status: form.status,
                    };
                }
                resetForm();
            },
            onError: () => Swal.fire('Error!', 'Could not update vaccine.', 'error'),
        });
    } else {
        form.post(route('vaccine.store'), {
            preserveScroll: true,
            onSuccess: (page) => {
                if ((page as any).props?.vaccines) {
                    vaccines.value = (page as any).props.vaccines;
                } else {
                    vaccines.value.unshift({
                        id: Date.now(),
                        vaccine_type_id: form.vaccine_type_id,
                        vaccine_type_name: props.vaccineTypes.find((vt) => vt.id === form.vaccine_type_id)?.name || '',
                        name: form.name,
                        applicator: form.applicator,
                        dose: form.dose,
                        note: form.note,
                        status: form.status,
                        created_at: new Date().toISOString(),
                    });
                }
                resetForm();
            },
            onError: () => Swal.fire('Error!', 'Could not create vaccine.', 'error'),
        });
    }
};

// Actions dropdown (like vaccineType List.vue)
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

// Toggle status
const toggleStatus = (vaccine: Vaccine) => {
    const newStatus = vaccine.status === 1 ? 0 : 1;
    router.put(
        route('vaccine.update', vaccine.id),
        { ...vaccine, status: newStatus },
        {
            preserveScroll: true,
            onSuccess: () => {
                const i = vaccines.value.findIndex((v) => v.id === vaccine.id);
                if (i !== -1) vaccines.value[i].status = newStatus;
            },
            onError: () => Swal.fire('Error!', 'Could not update status.', 'error'),
            onFinish: () => (openDropdownId.value = null),
        },
    );
};

// Breadcrumbs
const breadcrumbs = [
    { title: 'Master Setup', href: '/master-setup' },
    { title: 'Vaccine', href: '/master-setup/vaccine' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Vaccines" />

        <div class="px-4 py-6">
            <div class="mb-4 flex items-center justify-between">
                <HeadingSmall title="Vaccine List" />
                <div class="relative flex items-center gap-2">
                    <Button v-if="can('vaccine.create')" class="bg-chicken text-white hover:bg-yellow-600" @click="openModal()">+ Add New</Button>

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
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50 text-gray-600">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold">#</th>
                            <th class="px-6 py-3 text-left font-semibold">Vaccine Type</th>
                            <th class="px-6 py-3 text-left font-semibold">Name</th>
                            <th class="px-6 py-3 text-left font-semibold">Applicator</th>
                            <th class="px-6 py-3 text-left font-semibold">Dose</th>
                            <th class="px-6 py-3 text-left font-semibold">Note</th>
                            <th class="px-6 py-3 text-left font-semibold">Status</th>
                            <th class="px-6 py-3 text-left font-semibold">Created At</th>
                            <th class="px-6 py-3 text-left font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <tr v-for="(v, index) in vaccines" :key="v.id" class="odd:bg-white even:bg-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-6 py-4">{{ index + 1 }}</td>
                            <td class="px-6 py-4">{{ v.vaccine_type_name }}</td>
                            <td class="px-6 py-4">{{ v.name }}</td>
                            <td class="px-6 py-4">{{ v.applicator }}</td>
                            <td class="px-6 py-4">{{ v.dose }}</td>
                            <td class="px-6 py-4">{{ v.note }}</td>
                            <td class="px-6 py-4">
                                <span :class="v.status === 1 ? 'font-semibold text-green-600' : 'font-semibold text-red-600'">
                                    {{ v.status === 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ dayjs(v.created_at).format('DD MMM YYYY') }}</td>
                            <td class="relative flex items-center gap-2 px-6 py-4">
                                <Button size="sm" class="actions-button bg-gray-500 text-white hover:bg-gray-600" @click.stop="toggleDropdown(v.id)">
                                    Actions ▼
                                </Button>
                                <div
                                    v-if="openDropdownId === v.id"
                                    class="dropdown-menu absolute right-0 z-10 mt-1 w-40 rounded border bg-white shadow-md"
                                >
                                    <button class="w-full px-4 py-2 text-left hover:bg-gray-100" @click="openModal(v)">✏ Edit</button>
                                    <button class="w-full px-4 py-2 text-left hover:bg-gray-100" @click="toggleStatus(v)">
                                        {{ v.status === 1 ? 'Inactive' : 'Activate' }}
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="vaccines.length === 0">
                            <td colspan="9" class="px-6 py-6 text-center text-gray-500">No vaccines found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Draggable Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex justify-center bg-black/30 pt-6" @click.self="resetForm">
            <div ref="modalRef" class="w-full max-w-2xl rounded-lg border border-gray-300 bg-white shadow-lg" style="top: 100px; position: absolute">
                <div class="flex cursor-move items-center justify-between border-b border-gray-200 p-4" @mousedown="startDrag">
                    <h3 class="text-xl font-semibold text-gray-900">{{ editingVaccine ? 'Edit Vaccine' : 'Add New Vaccine' }}</h3>
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
                        <Label for="vaccine_type_id" class="mb-2">Vaccine Type</Label>
                        <select v-model="form.vaccine_type_id" id="vaccine_type_id" class="w-full rounded border p-2">
                            <option v-for="vt in props.vaccineTypes" :key="vt.id" :value="vt.id">{{ vt.name }}</option>
                        </select>
                    </div>
                    <div>
                        <Label for="name" class="mb-2">Vaccine Name</Label>
                        <Input v-model="form.name" id="name" />
                    </div>
                    <div>
                        <Label for="applicator" class="mb-2">Applicator</Label>
                        <Input v-model="form.applicator" id="applicator" />
                    </div>
                    <div>
                        <Label for="dose" class="mb-2">Dose</Label>
                        <Input v-model="form.dose" id="dose" />
                    </div>
                    <div>
                        <Label for="note" class="mb-2">Note</Label>
                        <textarea v-model="form.note" id="note" class="w-full rounded border p-2"></textarea>
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
                    <Button class="bg-chicken text-white" @click="submit">{{ editingVaccine ? 'Update' : 'Save' }}</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
