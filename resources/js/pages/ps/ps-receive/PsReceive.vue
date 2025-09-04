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
import dayjs from 'dayjs';
import { FileText, Pencil } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

// ✅ Props
const props = defineProps<{
    psReceives?: {
        data: Array<{
            id: number;
            pi_no: string;
            receive_date: string;
            shipment_type_id?: number;
            supplier: { id: number; name: string } | null;
            remarks?: string | null;
            chick_counts?: {
                id: number;
                ps_total_qty: number;
                ps_total_re_box_qty: number;
            } | null;
        }>;
        meta: { current_page: number; last_page: number; per_page: number; total: number };
    };
    filters?: { search?: string; per_page?: number };
}>();

useListFilters({ routeName: '/ps-receive', filters: props.filters });
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
const deleteReceive = (id: number) => {
    confirmDelete({
        url: `/ps-receive/${id}`,
        text: 'This will permanently delete the record.',
        successMessage: 'PS Receive deleted.',
    });
};

// ✅ Export filters
const filters = ref({
    search: props.filters?.search ?? '',
    per_page: props.filters?.per_page ?? 10,
});

const openExportDropdown = ref(false);

const exportPdf = (orientation: 'portrait' | 'landscape' = 'portrait') => {
    const url = route('reports.ps-receive.pdf', { ...filters.value, orientation });
    window.open(url, '_blank');
};

const exportExcel = () => {
    const url = route('reports.ps-receive.excel', { ...filters.value });
    window.open(url, '_blank');
};

// ✅ Demo data for cards
const piCardData: Record<string, any[]> = {
    PI001: [
        { title: 'Challan Box', value: 12, title1: 'F Box', value1: 10, title2: 'M Box', value2: 2 },
        { title: 'Receive Box', value: 12, title1: 'F Box', value1: 9, title2: 'M Box', value2: 3 },
        { title: 'Challan Chicks', value: 10000, title1: 'M', value1: 8000, title2: 'F', value2: 2000 },
        { title: 'Ship To', value: 'PCL' },
    ],
    PI002: [
        { title: 'Challan Box', value: 15, title1: 'F Box', value1: 12, title2: 'M Box', value2: 5 },
        { title: 'Receive Box', value: 15, title1: 'F Box', value1: 13, title2: 'M Box', value2: 2 },
        { title: 'Challan Chicks', value: 10000, title1: 'M', value1: 8000, title2: 'F', value2: 2000 },
        { title: 'Ship To', value: 'PBL' },
    ],
};

const selectedPI = ref('PI001');
const cardData = computed(() => piCardData[selectedPI.value] || []);

// ✅ Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Parent Stock', href: '/ps-receive' },
    { title: 'Receive', href: '/ps-receive' },
];
</script>

<template>
    <Head title="PS Receives" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Select Box -->
        <div class="m-5 w-full max-w-sm">
            <select
                v-model="selectedPI"
                class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
            >
                <option value="" disabled>Select PI No</option>
                <option value="PI001">PI001</option>
                <option value="PI002">PI002</option>
            </select>
        </div>

        <listInfocard :cards="cardData" />

        <div class="m-3 rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
            <!-- Header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-xl font-semibold text-gray-800 dark:text-white">Parent Stock Receive Information</h1>
                <div class="flex items-center gap-2">
                    <Link
                        v-if="can('ps-receive.create')"
                        href="/ps-receive/create"
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

            <FilterControls :filters="props.filters" routeName="/ps-receive" />

            <!-- Responsive Table -->
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr class="text-left text-gray-600 dark:text-gray-300">
                            <th class="px-4 py-3 font-semibold">PI No</th>
                            <th class="px-4 py-3 font-semibold">Shipment Type</th>
                            <th class="px-4 py-3 font-semibold">Receive Date</th>
                            <th class="px-4 py-3 font-semibold">Supplier</th>
                            <th class="px-4 py-3 font-semibold">Total Birds</th>
                            <th class="px-4 py-3 font-semibold">Total Box</th>
                            <th class="hidden px-4 py-3 font-semibold sm:table-cell">Breed</th>
                            <th class="px-4 py-3 font-semibold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        <tr
                            v-for="item in props.psReceives?.data ?? []"
                            :key="item.id"
                            class="odd:bg-white even:bg-gray-50 hover:bg-gray-100 dark:odd:bg-gray-900 dark:even:bg-gray-800"
                        >
                            <td class="px-4 py-3">{{ item.pi_no }}</td>
                            <td class="px-4 py-3">{{ item.shipment_type_id === 1 ? 'Local' : 'Foreign' }}</td>
                            <td class="px-4 py-3">{{ dayjs(item.receive_date).format('YYYY-MM-DD') }}</td>
                            <td class="px-4 py-3">{{ item.supplier?.name ?? 'N/A' }}</td>
                            <td class="px-4 py-3">{{ item.chick_counts?.ps_total_qty ?? '-' }}</td>
                            <td class="px-4 py-3">{{ item.chick_counts?.ps_total_re_box_qty ?? '-' }}</td>
                            <td class="hidden px-4 py-3 sm:table-cell">{{ item.remarks ?? '-' }}</td>
                            <td class="relative px-4 py-3">
                                <Button size="sm" class="action-btn bg-gray-500 text-white hover:bg-gray-600" @click.stop="toggleDropdown(item.id)">
                                    Actions ▼
                                </Button>

                                <div
                                    v-if="openDropdownId === item.id"
                                    class="action-dropdown absolute top-full left-0 z-20 mt-1 flex w-40 flex-col rounded border bg-white shadow-md"
                                    @click.stop
                                >
                                    <!-- Edit -->
                                    <Link
                                        v-if="can('ps-receive.edit')"
                                        :href="`/ps-receive/${item.id}/edit`"
                                        class="flex items-center gap-2 px-4 py-2 text-blue-600 hover:bg-blue-50"
                                    >
                                        <Pencil class="h-4 w-4" />
                                        <span>Edit</span>
                                    </Link>

                                    <!-- Report -->
                                    <button
                                        v-if="can('ps-receive.delete')"
                                        @click="deleteReceive(item.id)"
                                        class="flex w-full items-center gap-2 px-4 py-2 text-red-600 hover:bg-red-50"
                                    >
                                        <FileText class="h-4 w-4" />
                                        <span>Report</span>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="(props.psReceives?.data ?? []).length === 0">
                            <td colspan="8" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">No PS Receives found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <Pagination :meta="props.psReceives?.meta ?? {}" class="mt-6" />
        </div>
    </AppLayout>
</template>
