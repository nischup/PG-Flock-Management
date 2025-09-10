<script setup lang="ts">
import FilterControls from '@/components/FilterControls.vue';
import listInfocard from '@/components/ListinfoCard.vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { useListFilters } from '@/composables/useListFilters';
import { useNotifier } from '@/composables/useNotifier';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { FileText, Pencil, Trash2 } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps<{
    psFirmReceives?: {
        data: Array<{
            id: number;
            ps_receive_id: number;
            job_no: string;
            receipt_type: string;
            source_type: string;
            source_id: number;
            flock_id: number;
            flock_name: string;
            receiving_company_id: number;
            company_name: string;
            firm_female_qty: number;
            firm_male_qty: number;
            firm_total_qty: number;
            remarks?: string | null;
            created_by: number;
            status: number;
            receive_date: string;
        }>;
        meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
        };
    };
    filters?: { search?: string; per_page?: number };
}>();

useListFilters({ routeName: '/ps-receive', filters: props.filters });
const { confirmDelete } = useNotifier();
const { can } = usePermissions();

// Action dropdown
const openDropdownId = ref<number | null>(null);
const toggleDropdown = (id: number) => {
    openDropdownId.value = openDropdownId.value === id ? null : id;
};
const closeDropdown = () => (openDropdownId.value = null);

// Close dropdown on outside click
const handleClick = (e: MouseEvent) => {
    if (!(e.target as HTMLElement).closest('.action-dropdown, .action-btn, .pdf-dropdown, .pdf-button')) {
        closeDropdown();
        openExportDropdown.value = false;
    }
};
onMounted(() => document.addEventListener('click', handleClick));
onBeforeUnmount(() => document.removeEventListener('click', handleClick));

// Delete action
const deleteReceive = (id: number) => {
    confirmDelete({
        url: `/ps-firm-receive/${id}`,
        text: 'This will permanently delete the record.',
        successMessage: 'PS Firm Receive deleted.',
    });
};

// Export Report button
const filters = ref({
    search: props.filters?.search ?? '',
    per_page: props.filters?.per_page ?? 10,
});
const openExportDropdown = ref(false);

const exportPdf = (orientation: 'portrait' | 'landscape' = 'portrait') => {
    const url = route('reports.ps-firm-receive.pdf', { ...filters.value, orientation });
    window.open(url, '_blank');
};

const exportExcel = () => {
    const url = route('reports.ps-firm-receive.excel', { ...filters.value });
    window.open(url, '_blank');
};

const exportRowPdf = (id: number) => {
    const url = `/ps-firm-receive/${id}/pdf`;
    window.open(url, '_blank');
};

// Demo card data grouped by PI No
const piCardData: Record<string, any[]> = {
    PI001: [
        { title: 'Receive Box', value: 12 },
        { title: 'Male Box', value: 2 },
        { title: 'Female Box', value: 10 },
        { title: 'Sortage Box', value: 12 },
        { title: 'Excess Box', value: 12 },
    ],
    PI002: [
        { title: 'Receive Box', value: 18 },
        { title: 'Male Box', value: 5 },
        { title: 'Female Box', value: 13 },
        { title: 'Sortage Box', value: 1 },
        { title: 'Excess Box', value: 0 },
    ],
    PI003: [
        { title: 'Receive Box', value: 18 },
        { title: 'Male Box', value: 5 },
        { title: 'Female Box', value: 13 },
        { title: 'Sortage Box', value: 0 },
        { title: 'Excess Box', value: 1 },
    ],
};

// Selected PI
const selectedPI = ref('PI001');
const cardData = computed(() => piCardData[selectedPI.value] || []);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Parent Stock', href: '/ps-receive' },
    { title: 'Farm Receive', href: '/ps-receive' },
];
</script>

<template>
    <Head title="PS Receives" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- PI Select -->
        <div class="m-5 w-full max-w-sm">
            <select
                v-model="selectedPI"
                class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
            >
                <option value="" disabled>Select PI No</option>
                <option value="PI001">PI001</option>
                <option value="PI002">PI002</option>
                <option value="PI003">PI003</option>
            </select>
        </div>

        <listInfocard :cards="cardData" />

        <div class="m-3 rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
            <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">Parent Stock Farm Receive Info.</h1>
                <div class="flex items-center gap-2">
                    <Link
                        v-if="can('ps-receive.create')"
                        href="/ps-firm-receive/create"
                        class="inline-flex items-center rounded bg-chicken px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-chicken"
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

            <FilterControls :filters="props.filters" routeName="/ps-receive" />

            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                        <tr>
                            <th class="px-6 py-3 text-left font-bold">Flock Name</th>
                            <th class="px-6 py-3 text-left font-bold">Company</th>
                            <th class="px-6 py-3 text-left font-bold">Male Qty</th>
                            <th class="px-6 py-3 text-left font-bold">Female Qty</th>
                            <th class="px-6 py-3 text-left font-bold">Total Qty</th>
                            <th class="px-6 py-3 text-left font-bold">Remarks</th>
                            <th class="px-6 py-3 text-left font-bold">Receive Date</th>
                            <th class="px-6 py-3 text-left font-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        <tr
                            v-for="item in props.psFirmReceives?.data ?? []"
                            :key="item.id"
                            class="odd:bg-white even:bg-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800"
                        >
                            <td class="px-6 py-4">{{ item.flock_name }}</td>
                            <td class="px-6 py-4">{{ item.company_name }}</td>
                            <td class="px-6 py-4">{{ item.firm_male_qty }}</td>
                            <td class="px-6 py-4">{{ item.firm_female_qty }}</td>
                            <td class="px-6 py-4">{{ item.firm_total_qty }}</td>
                            <td class="px-6 py-4">{{ item.remarks ?? '-' }}</td>
                            <td class="px-6 py-4">{{ item.receive_date }}</td>
                            <td class="relative px-6 py-4">
                                <Button size="sm" class="action-btn bg-gray-500 text-white hover:bg-gray-600" @click.stop="toggleDropdown(item.id)">
                                    Actions ▼
                                </Button>

                                <div
                                    v-if="openDropdownId === item.id"
                                    class="action-dropdown absolute top-full left-0 z-20 mt-1 flex w-40 flex-col rounded border bg-white shadow-md"
                                    @click.stop
                                >
                                    <Link
                                        v-if="can('ps-receive.edit')"
                                        :href="`/ps-firm-receive/${item.id}/edit`"
                                        class="flex items-center gap-2 px-4 py-2 text-blue-600 hover:bg-blue-50"
                                    >
                                        <Pencil class="h-4 w-4" />
                                        <span>Edit</span>
                                    </Link>

                                    <button
                                        v-if="can('ps-receive.delete')"
                                        @click="deleteReceive(item.id)"
                                        class="flex w-full items-center gap-2 px-4 py-2 text-red-600 hover:bg-red-50"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                        <span>Delete</span>
                                    </button>

                                    <button
                                        @click="exportRowPdf(item.id)"
                                        class="flex w-full items-center gap-2 px-4 py-2 text-green-600 hover:bg-green-50"
                                    >
                                        <FileText class="h-4 w-4" />
                                        <span>Report</span>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="(props.psFirmReceives?.data ?? []).length === 0">
                            <td colspan="9" class="text-center text-gray-500 dark:text-gray-400">No PS Firm Receives found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :meta="props.psFirmReceives?.meta ?? {}" class="mt-6" />
        </div>
    </AppLayout>
</template>
