<script setup lang="ts">
import FilterControls from '@/components/FilterControls.vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { useListFilters } from '@/composables/useListFilters';
import { useNotifier } from '@/composables/useNotifier';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { FileText, Pencil } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

// ✅ Props
const props = defineProps<{
    labTests?: {
        data: Array<{
            id: number;
            lab_type: string;
            lab_send_female_qty: number;
            lab_send_male_qty: number;
            lab_send_total_qty: number;
            notes?: string;
            status: number;
            ps_receive?: {
                id: number;
                pi_no: string;
                order_no: string;
                created_at: string;
            } | null;
        }>;
        meta: { current_page: number; last_page: number; per_page: number; total: number };
    };
    filters?: { search?: string; per_page?: number };
}>();

useListFilters({ routeName: '/ps-lab-test', filters: props.filters });
const { confirmDelete } = useNotifier();
const { can } = usePermissions();

const openDropdownId = ref<number | null>(null);
const toggleDropdown = (id: number) => {
    openDropdownId.value = openDropdownId.value === id ? null : id;
};
const closeDropdown = () => (openDropdownId.value = null);

// ✅ Close on outside click
const handleClick = (e: MouseEvent) => {
    if (!(e.target as HTMLElement).closest('.action-dropdown, .action-btn, .pdf-dropdown, .pdf-button')) {
        closeDropdown();
        openExportDropdown.value = false;
    }
};
onMounted(() => document.addEventListener('click', handleClick));
onBeforeUnmount(() => document.removeEventListener('click', handleClick));

// ✅ Delete action
const deleteLabTest = (id: number) => {
    confirmDelete({
        url: `/ps-lab-test/${id}`,
        text: 'This will permanently delete the lab test record.',
        successMessage: 'Lab Test deleted successfully.',
    });
};

// ✅ Export filters
const filters = ref({
    search: props.filters?.search ?? '',
    per_page: props.filters?.per_page ?? 10,
});

const openExportDropdown = ref(false);

const exportPdf = (orientation: 'portrait' | 'landscape' = 'portrait') => {
    const url = route('reports.ps-lab-test.pdf', { ...filters.value, orientation });
    window.open(url, '_blank');
};

const exportExcel = () => {
    const url = route('reports.ps-lab-test.excel', { ...filters.value });
    window.open(url, '_blank');
};

const exportRowPdf = (id: number) => {
    const url = `/ps-lab-test/${id}/pdf`; // route to new controller method
    window.open(url, '_blank');
};

// ✅ Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Parent Stock', href: '/ps-lab-test' },
    { title: 'Lab Tests', href: '/ps-lab-test' },
];
</script>

<template>
    <Head title="PS Lab Tests" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="m-3 rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
            <!-- Header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-xl font-semibold text-gray-800 dark:text-white">Parent Stock Lab Test Information</h1>
                <div class="flex items-center gap-2">
                    <Link
                        v-if="can('ps-lab-test.create')"
                        href="/ps-lab-test/create"
                        class="rounded bg-chicken px-4 py-2 text-sm font-semibold text-white shadow hover:bg-chicken/90"
                    >
                        + Add
                    </Link>

                    <!-- Export Dropdown -->
                    <div class="pdf-dropdown relative">
                        <Button class="pdf-button bg-green-600 text-white hover:bg-green-700" @click="openExportDropdown = !openExportDropdown">
                            Export Report ▼
                        </Button>
                        <div v-if="openExportDropdown" class="absolute right-0 z-20 mt-2 w-40 rounded border bg-white shadow-lg">
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

            <FilterControls :filters="props.filters" routeName="/ps-lab-test" />

            <!-- Responsive Table -->
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr class="text-left text-gray-600 dark:text-gray-300">
                            <th class="px-4 py-3 font-semibold">PI No</th>
                            <th class="px-4 py-3 font-semibold">Order No</th>
                            <th class="px-4 py-3 font-semibold">Receive Date</th>
                            <th class="px-4 py-3 font-semibold">Lab Type</th>
                            <th class="px-4 py-3 font-semibold">Female Qty</th>
                            <th class="px-4 py-3 font-semibold">Male Qty</th>
                            <th class="px-4 py-3 font-semibold">Total Qty</th>
                            <th class="hidden px-4 py-3 font-semibold sm:table-cell">Notes</th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        <tr
                            v-for="lab in props.labTests?.data ?? []"
                            :key="lab.id"
                            class="odd:bg-white even:bg-gray-50 hover:bg-gray-100 dark:odd:bg-gray-900 dark:even:bg-gray-800"
                        >
                            <td class="px-4 py-3">{{ lab.ps_receive?.pi_no ?? '-' }}</td>
                            <td class="px-4 py-3">{{ lab.ps_receive?.order_no ?? '-' }}</td>
                            <td class="px-4 py-3">
                                {{ lab.ps_receive?.created_at ? dayjs(lab.ps_receive.created_at).format('YYYY-MM-DD') : '-' }}
                            </td>
                            <td class="px-4 py-3">{{ lab.lab_type == "1" ? 'Gov Lab' : lab.lab_type == "2" ? 'Provita Lab' : 'Unknown' }}</td>
                            <td class="px-4 py-3">{{ lab.lab_send_female_qty }}</td>
                            <td class="px-4 py-3">{{ lab.lab_send_male_qty }}</td>
                            <td class="px-4 py-3">{{ lab.lab_send_total_qty }}</td>
                            <td class="hidden px-4 py-3 sm:table-cell">{{ lab.notes ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span :class="lab.status === 1 ? 'text-green-600' : 'text-red-600'">
                                    {{ lab.status === 1 ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="relative px-4 py-3">
                                <Button size="sm" class="action-btn bg-gray-500 text-white hover:bg-gray-600" @click.stop="toggleDropdown(lab.id)">
                                    Actions ▼
                                </Button>

                                <div
                                    v-if="openDropdownId === lab.id"
                                    class="action-dropdown absolute top-full left-0 z-20 mt-1 flex w-40 flex-col rounded border bg-white shadow-md"
                                    @click.stop
                                >
                                    <!-- Edit -->
                                    <Link
                                        v-if="can('ps-lab-test.edit')"
                                        :href="`/ps-lab-test/${lab.id}/edit`"
                                        class="flex items-center gap-2 px-4 py-2 text-blue-600 hover:bg-blue-50"
                                    >
                                        <Pencil class="h-4 w-4" />
                                        <span>Edit</span>
                                    </Link>

                                    <!-- Report -->
                                    <button
                                        v-if="can('ps-lab-test.view')"
                                        @click="exportRowPdf(lab.id)"
                                        class="flex w-full items-center gap-2 px-4 py-2 text-green-600 hover:bg-green-50"
                                    >
                                        <FileText class="h-4 w-4" />
                                        <span>Report</span>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="(props.labTests?.data ?? []).length === 0">
                            <td colspan="10" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">No lab tests found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :meta="props.labTests?.meta ?? {}" class="mt-6" />
        </div>
    </AppLayout>
</template>
