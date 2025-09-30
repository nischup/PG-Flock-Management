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
// Company interface
interface Company {
    id: number;
    name: string;
    short_name: string;
    company_type: string;
    contact_person_name?: string;
    contact_person_phone?: string;
    contact_person_email?: string;
    contact_person_designation?: string;
    location?: string;
    status: number;
    created_at: string;
}

// Props
const props = defineProps<{
    companies: Company[];
    filters: { search?: string; per_page?: number; page?: number };
}>();

// for pdf
const filters = ref({
    search: '',
    sort: 'name',
    direction: 'asc',
});

const openDropdown = ref(false);

const exportPdf = (orientation: 'portrait' | 'landscape' = 'portrait') => {
    const url = route('reports.company.pdf', { ...filters.value, orientation });
    window.open(url, '_blank');
};

//for excel
const exportExcel = () => {
    const url = route('reports.company.excel', { ...filters.value });
    window.open(url, '_blank');
};

// Filters
useListFilters({
    routeName: '/company',
    filters: props.filters,
});

const { can } = usePermissions();
const companies = ref<Company[]>([...props.companies]);

// Modal state
const showModal = ref(false);
const editingCompany = ref<Company | null>(null);

// Form
const form = useForm({
    name: '',
    short_name: '',
    company_type: '',
    contact_person_name: '',
    contact_person_phone: '',
    contact_person_email: '',
    contact_person_designation: '',
    location: '',
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

// Open modal
const openModal = (company: Company | null = null) => {
    if (company) {
        editingCompany.value = company;
        form.name = company.name;
        form.short_name = company.short_name;
        form.company_type = company.company_type;
        form.location = company.location || '';
        form.contact_person_name = company.contact_person_name || '';
        form.contact_person_phone = company.contact_person_phone || '';
        form.contact_person_email = company.contact_person_email || '';
        form.contact_person_designation = company.contact_person_designation || '';
        form.status = company.status;
    } else {
        editingCompany.value = null;
        form.reset();
        form.status = 1;
    }
    showModal.value = true;
};

// Reset form
const resetForm = () => {
    form.reset();
    form.status = 1;
    editingCompany.value = null;
    showModal.value = false;
};

useNotifier();

// Submit
const submit = () => {
    if (!form.name.trim()) return;

    if (editingCompany.value) {
        form.put(route('company.update', editingCompany.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                const i = companies.value.findIndex((c) => c.id === editingCompany.value!.id);
                if (i !== -1) {
                    companies.value[i] = { ...companies.value[i], ...form };
                }
                resetForm();
            },
        });
    } else {
        form.post(route('company.store'), {
            preserveScroll: true,
            onSuccess: (page) => {
                if ((page as any).props?.companies) {
                    companies.value = (page as any).props.companies;
                } else {
                    companies.value.unshift({
                        id: Date.now(),
                        name: form.name,
                        short_name: form.short_name,
                        company_type: form.company_type,
                        location: form.location,
                        contact_person_name: form.contact_person_name,
                        contact_person_phone: form.contact_person_phone,
                        contact_person_email: form.contact_person_email,
                        contact_person_designation: form.contact_person_designation,
                        status: form.status,
                        created_at: new Date().toISOString(),
                    });
                }
                resetForm();
            },
        });
    }
};

// Toggle status
const toggleStatus = (company: Company) => {
    const newStatus = company.status === 1 ? 0 : 1;
    router.put(
        route('company.update', company.id),
        { ...company, status: newStatus },
        {
            preserveScroll: true,
            onSuccess: () => {
                const i = companies.value.findIndex((c) => c.id === company.id);
                if (i !== -1) companies.value[i].status = newStatus;
            },
            onError: () => Swal.fire('Error!', 'Could not update status.', 'error'),
            onFinish: () => (openDropdownId.value = null),
        },
    );
};

// Breadcrumbs
const breadcrumbs = [
    { title: 'Master Setup', href: '/master-setup' },
    { title: 'Company', href: '/master-setup/company' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Companies" />

        <FilterControls :filters="props.filters" routeName="/company" />

        <div class="px-4 py-6">
            <div class="mb-4 flex items-center justify-between">
                <HeadingSmall title="Companies List" />
                <div class="relative flex items-center gap-2">
                    <!-- Add New Button -->
                    <Button v-if="can('company.create')" class="bg-chicken text-white hover:bg-yellow-600" @click="openModal()"> + Add New </Button>

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
                            <!-- Uncomment if you want Landscape PDF option -->
                            <!--
            <button
                @click="exportPdf('landscape'); openDropdown = false;"
                class="block w-full px-4 py-2 text-left hover:bg-gray-100"
            >
                Landscape
            </button>
            -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="mt-4 overflow-x-auto rounded-xl bg-white shadow dark:bg-gray-800">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr>
                            <th class="border-b px-4 py-2 bg-blue-500 text-white font-semibold text-sm whitespace-nowrap">#</th>
                            <th class="border-b px-4 py-2 bg-green-500 text-white font-semibold text-sm whitespace-nowrap">Name</th>
                            <th class="border-b px-4 py-2 bg-purple-500 text-white font-semibold text-sm whitespace-nowrap">Short Name</th>
                            <th class="border-b px-4 py-2 bg-orange-500 text-white font-semibold text-sm whitespace-nowrap">Type</th>
                            <th class="border-b px-4 py-2 bg-pink-500 text-white font-semibold text-sm whitespace-nowrap">Location</th>
                            <th class="border-b px-4 py-2 bg-indigo-500 text-white font-semibold text-sm whitespace-nowrap">Contact Name</th>
                            <th class="border-b px-4 py-2 bg-red-500 text-white font-semibold text-sm whitespace-nowrap">Contact Phone</th>
                            <th class="border-b px-4 py-2 bg-yellow-500 text-black font-semibold text-sm whitespace-nowrap">Contact Email</th>
                            <th class="border-b px-4 py-2 bg-teal-500 text-white font-semibold text-sm whitespace-nowrap">Designation</th>
                            <th class="border-b px-4 py-2 bg-cyan-500 text-white font-semibold text-sm whitespace-nowrap">Status</th>
                            <th class="border-b px-4 py-2 bg-emerald-500 text-white font-semibold text-sm whitespace-nowrap">Created At</th>
                            <th class="border-b px-4 py-2 bg-gray-600 text-white font-semibold text-sm whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(company, index) in companies"
                            :key="company.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            <td class="border-b px-4 py-2">{{ index + 1 }}</td>
                            <td class="border-b px-4 py-2">{{ company.name }}</td>
                            <td class="border-b px-4 py-2">{{ company.short_name }}</td>
                            <td class="border-b px-4 py-2">{{ company.company_type }}</td>
                            <td class="border-b px-4 py-2">{{ company.location || '-' }}</td>
                            <td class="border-b px-4 py-2">{{ company.contact_person_name || '-' }}</td>
                            <td class="border-b px-4 py-2">{{ company.contact_person_phone || '-' }}</td>
                            <td class="border-b px-4 py-2">{{ company.contact_person_email || '-' }}</td>
                            <td class="border-b px-4 py-2">{{ company.contact_person_designation || '-' }}</td>
                            <td class="border-b px-4 py-2">
                                <span :class="company.status === 1 ? 'font-semibold text-green-600' : 'font-semibold text-red-600'">
                                    {{ company.status === 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="border-b px-4 py-2">{{ dayjs(company.created_at).format('YYYY-MM-DD') }}</td>
                            <td class="border-b px-4 py-2 relative">
                                <Button size="sm" class="actions-button bg-gray-500 text-white hover:bg-gray-600" @click="toggleDropdown(company.id)">
                                    Actions ▼
                                </Button>
                                <div
                                    v-if="openDropdownId === company.id"
                                    class="dropdown-menu absolute z-10 mt-1 w-40 rounded border bg-white shadow-md"
                                    @click.stop
                                >
                                    <button class="w-full px-4 py-2 text-left hover:bg-gray-100" @click="openModal(company)">✏ Edit</button>
                                    <button class="w-full px-4 py-2 text-left hover:bg-gray-100" @click="toggleStatus(company)">
                                        {{ company.status === 1 ? 'Inactive' : 'Activate' }}
                                    </button>
                                </div>
                            </td>
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
                        {{ editingCompany ? 'Edit Company' : 'Add New Company' }}
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
                        <Label for="name" class="mb-2">Company Name</Label>
                        <Input v-model="form.name" id="name" />
                    </div>
                    <div>
                        <Label for="short_name" class="mb-2">Short Name</Label>
                        <Input v-model="form.short_name" id="short_name" />
                    </div>
                    <div>
                        <Label for="company_type" class="mb-2">Company Type</Label>
                        <select v-model="form.company_type" id="company_type" class="w-full rounded border p-2">
                            <option value="">-- Select Type --</option>
                            <option value="Breeding">Breeding</option>
                            <option value="Hatching">Hatching</option>
                        </select>
                    </div>
                    <div>
                        <Label for="location" class="mb-2">Location</Label>
                        <Input v-model="form.location" id="location" />
                    </div>
                    <div>
                        <Label for="contact_person_name" class="mb-2">Contact Person Name</Label>
                        <Input v-model="form.contact_person_name" id="contact_person_name" />
                    </div>
                    <div>
                        <Label for="contact_person_phone" class="mb-2">Contact Person Phone</Label>
                        <Input v-model="form.contact_person_phone" id="contact_person_phone" />
                    </div>
                    <div>
                        <Label for="contact_person_email" class="mb-2">Contact Person Email</Label>
                        <Input v-model="form.contact_person_email" id="contact_person_email" />
                    </div>
                    <div>
                        <Label for="contact_person_designation" class="mb-2">Contact Person Designation</Label>
                        <Input v-model="form.contact_person_designation" id="contact_person_designation" />
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
                    <Button class="bg-chicken text-white" @click="submit">{{ editingCompany ? 'Update' : 'Save' }}</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
