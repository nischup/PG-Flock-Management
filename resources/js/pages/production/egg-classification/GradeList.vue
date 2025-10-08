<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { useListFilters } from '@/composables/useListFilters';
import { useNotifier } from '@/composables/useNotifier';
import { usePermissions } from '@/composables/usePermissions';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { FileText, Pencil, Calendar, Search } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

// ✅ Props
const props = defineProps<{
    grades?: {
        data: Array<{
            id: number;
            quantity: number;
            grade?: {
                id: number;
                name: string;
                type: string;
                min_weight: number;
                max_weight: number;
            };
            classification?: {
                id: number;
                classification_date: string;
                total_eggs: number;
                commercial_eggs: number;
                hatching_eggs: number;
                batch_assign?: {
                    id: number;
                    transaction_no: string;
                    batch?: {
                        id: number;
                        name: string;
                    };
                    flock?: {
                        id: number;
                        name: string;
                        code: string;
                    };
                    shed?: {
                        id: number;
                        name: string;
                    };
                    company?: {
                        id: number;
                        name: string;
                        short_name: string;
                    };
                    project?: {
                        id: number;
                        name: string;
                    };
                    level: number;
                };
            };
        }>;
        meta: { current_page: number; last_page: number; per_page: number; total: number };
    };
    filters?: { 
        search?: string; 
        per_page?: number; 
        grade_type?: string; 
        date_from?: string; 
        date_to?: string; 
    };
}>();

useListFilters({ routeName: '/egg-classification-grades', filters: props.filters });
const { confirmDelete } = useNotifier();
const { can } = usePermissions();

const openDropdownId = ref<number | null>(null);
const toggleDropdown = (id: number) => {
    openDropdownId.value = openDropdownId.value === id ? null : id;
};
const closeDropdown = () => (openDropdownId.value = null);

// Date picker states
const showFromDatePicker = ref(false);
const showToDatePicker = ref(false);

// Dropdown states
const showGradeTypeDropdown = ref(false);

// ✅ Close on outside click
const handleClick = (e: MouseEvent) => {
    if (!(e.target as HTMLElement).closest('.action-dropdown, .action-btn, .pdf-dropdown, .pdf-button, .date-picker-container, .grade-type-dropdown')) {
        closeDropdown();
        openExportDropdown.value = false;
        showFromDatePicker.value = false;
        showToDatePicker.value = false;
        showGradeTypeDropdown.value = false;
    }
};
onMounted(() => document.addEventListener('click', handleClick));
onBeforeUnmount(() => document.removeEventListener('click', handleClick));

// ✅ Delete action
const deleteGrade = (id: number) => {
    confirmDelete({
        url: `/egg-classification-grades/${id}`,
        text: 'This will permanently delete the egg grade record.',
        successMessage: 'Egg Grade deleted successfully.',
    });
};

// ✅ Export filters
const filters = ref({
    search: props.filters?.search ?? '',
    per_page: props.filters?.per_page ?? 10,
    grade_type: props.filters?.grade_type ?? '',
    date_from: props.filters?.date_from ?? '',
    date_to: props.filters?.date_to ?? '',
});

// ✅ Filter methods
const applyFilters = () => {
    const params = new URLSearchParams();
    
    if (filters.value.search) params.set('search', filters.value.search);
    if (filters.value.per_page) params.set('per_page', filters.value.per_page.toString());
    if (filters.value.grade_type) params.set('grade_type', filters.value.grade_type);
    if (filters.value.date_from) params.set('date_from', filters.value.date_from);
    if (filters.value.date_to) params.set('date_to', filters.value.date_to);
    
    window.location.href = `/egg-classification-grades?${params.toString()}`;
};

const clearFilters = () => {
    filters.value = {
        search: '',
        per_page: 10,
        grade_type: '',
        date_from: '',
        date_to: '',
    };
    applyFilters();
};

// ✅ Computed properties for filter summary
const hasActiveFilters = computed(() => {
    return filters.value.grade_type || 
           filters.value.date_from || 
           filters.value.date_to;
});

// Date picker helper functions
const formatDate = (date: string | null) => {
    if (!date) return '';
    return dayjs(date).format('YYYY-MM-DD');
};

const selectFromDate = (date: string) => {
    filters.value.date_from = date;
    showFromDatePicker.value = false;
};

const selectToDate = (date: string) => {
    filters.value.date_to = date;
    showToDatePicker.value = false;
};

const clearFromDate = () => {
    filters.value.date_from = '';
    showFromDatePicker.value = false;
};

const clearToDate = () => {
    filters.value.date_to = '';
    showToDatePicker.value = false;
};

// Generate date options for picker
const generateDateOptions = () => {
    const options = [];
    const today = dayjs();
    
    // Generate last 90 days
    for (let i = 90; i >= 0; i--) {
        const date = today.subtract(i, 'day');
        options.push({
            value: date.format('YYYY-MM-DD'),
            label: date.format('MMM DD, YYYY'),
            isToday: i === 0
        });
    }
    
    return options;
};

const dateOptions = computed(() => generateDateOptions());

// Grade type dropdown helper functions
const getSelectedGradeTypeName = () => {
    if (!filters.value.grade_type) return '';
    return filters.value.grade_type === '1' ? 'Hatching' : filters.value.grade_type === '2' ? 'Commercial' : '';
};

const selectGradeType = (typeId: string) => {
    filters.value.grade_type = typeId;
    showGradeTypeDropdown.value = false;
};

const clearGradeTypeFilter = () => {
    filters.value.grade_type = '';
    showGradeTypeDropdown.value = false;
};

const openExportDropdown = ref(false);

const exportPdf = (orientation: 'portrait' | 'landscape' = 'portrait') => {
    const url = route('reports.egg-classification-grades.pdf', { ...filters.value, orientation });
    window.open(url, '_blank');
};

const exportExcel = () => {
    const url = route('reports.egg-classification-grades.excel', { ...filters.value });
    window.open(url, '_blank');
};

const exportRowPdf = (id: number) => {
    const url = `/egg-classification-grades/${id}/pdf`;
    window.open(url, '_blank');
};

// Group grades by classification_id
const grouped = computed(() => {
    if (!props.grades?.data) return {};
    
    return props.grades.data.reduce((acc: any, grade: any) => {
        const id = grade.classification?.id;
        if (!acc[id]) acc[id] = { classification: grade.classification, grades: [] };
        acc[id].grades.push(grade);
        return acc;
    }, {});
});

const groupedArray = computed(() => Object.values(grouped.value));

// ✅ Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Production', href: '/production' },
    { title: 'Egg Gradding', href: '/egg-classification-grades' },
];
</script>

<template>
    <Head title="Egg Gradding" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 m-3 bg-white dark:bg-gray-900 rounded-xl shadow-md">
            <!-- Header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-xl font-semibold text-gray-800 dark:text-white">Egg Gradding</h1>
                <div class="flex items-center gap-2">
                    <Link
                        v-if="can('egg-classification-grades.create')"
                        href="/egg-classification-grades/create"
                        class="group relative overflow-hidden rounded-md px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-gray-500"
                        style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);"
                    >
                        <span class="relative z-10 flex items-center gap-2">
                            <svg class="h-4 w-4 transition-transform duration-300 group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add
                        </span>
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-20 group-hover:translate-x-full"></div>
                    </Link>

                    <!-- Export Dropdown -->
                    <div class="pdf-dropdown relative">
                        <Button 
                            class="group relative overflow-hidden rounded-md px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-green-500" 
                            style="background: linear-gradient(135deg, #059669 0%, #047857 100%); box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3);"
                            @click="openExportDropdown = !openExportDropdown"
                        >
                            <span class="relative z-10 flex items-center gap-2">
                                <svg class="h-4 w-4 transition-transform duration-300 group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Export Report
                                <svg class="h-3 w-3 transition-transform duration-300" :class="{ 'rotate-180': openExportDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-20 group-hover:translate-x-full"></div>
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

            <!-- Custom Filter Section -->
            <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Filters</h3>
                    <button
                        @click="clearFilters"
                        class="text-sm text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200"
                    >
                        Clear All
                    </button>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                        <input
                            v-model="filters.search"
                            type="text"
                            placeholder="Transaction No, Batch Name, Grade Name..."
                            class="block w-full px-3 py-2.5 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500"
                        />
                    </div>

                    <!-- Grade Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Grade Type</label>
                        <select
                            v-model="filters.grade_type"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">All Grade Types</option>
                            <option value="1">Hatching</option>
                            <option value="2">Commercial</option>
                        </select>
                    </div>

                    <!-- Per Page -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Per Page</label>
                        <select
                            v-model="filters.per_page"
                            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        >
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date Range</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input
                                v-model="filters.date_from"
                                type="date"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                            <input
                                v-model="filters.date_to"
                                type="date"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                    </div>
                </div>

                <!-- Filter Actions -->
                <div class="mt-4 flex justify-end gap-2">
                    <button
                        @click="applyFilters"
                        class="rounded-md bg-black px-4 py-2 text-sm font-medium text-white shadow-lg hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200"
                    >
                        Apply Filters
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="mt-4 overflow-x-auto rounded-xl bg-white shadow dark:bg-gray-800">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr>
                            <th class="border-b px-4 py-2 bg-blue-500 text-white font-semibold text-sm whitespace-nowrap">S/N</th>
                            <th class="border-b px-4 py-2 bg-green-500 text-white font-semibold text-sm whitespace-nowrap">Classification ID</th>
                            <th class="border-b px-4 py-2 bg-purple-500 text-white font-semibold text-sm whitespace-nowrap">Batch / Transaction</th>
                            <th class="border-b px-4 py-2 bg-orange-500 text-white font-semibold text-sm whitespace-nowrap">Classification Date</th>
                            <th class="border-b px-4 py-2 bg-pink-500 text-white font-semibold text-sm whitespace-nowrap">Flock / Shed</th>
                            <th class="border-b px-4 py-2 bg-indigo-500 text-white font-semibold text-sm whitespace-nowrap">Grades / Quantity</th>
                            <th class="border-b px-4 py-2 bg-red-500 text-white font-semibold text-sm whitespace-nowrap">Total Eggs</th>
                            <th class="border-b px-4 py-2 bg-gray-600 text-white font-semibold text-sm whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(item, index) in groupedArray"
                            :key="item.classification.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            <td
                            class="border-b px-4 py-2 text-gray-800 dark:text-gray-100 align-top"
                            rowspan="2"
                            >
                            {{ ((props.grades?.meta?.current_page || 1) - 1) * (props.grades?.meta?.per_page || 10) + index + 1 }}
                            </td>
                            <td class="border-b px-4 py-2 text-gray-800 dark:text-gray-100 align-top" rowspan="2">
                            {{ item.classification?.id ?? '-' }}
                            </td>
                            <td class="border-b px-4 py-2 text-gray-800 dark:text-gray-100 align-top" rowspan="2">
                            <div class="flex flex-col">
                                <span class="font-medium">{{ item.classification?.batch_assign?.transaction_no ?? '-' }}</span>
                                <span class="text-xs text-gray-500">{{ item.classification?.batch_assign?.batch?.name ?? '-' }}</span>
                            </div>
                            </td>
                            <td class="border-b px-4 py-2 text-gray-800 dark:text-gray-100 align-top" rowspan="2">
                            {{ item.classification?.classification_date ? dayjs(item.classification.classification_date).format('YYYY-MM-DD') : '-' }}
                            </td>
                            <td class="border-b px-4 py-2 text-gray-800 dark:text-gray-100 align-top" rowspan="2">
                            <div class="flex flex-col">
                                <span class="font-medium">{{ item.classification?.batch_assign?.flock?.name ?? '-' }}</span>
                                <span class="text-xs text-gray-500">{{ item.classification?.batch_assign?.shed?.name ?? '-' }}</span>
                            </div>
                            </td>

                            <!-- First row: Commercial eggs -->
                            <td class="border-b px-4 py-2 text-gray-800 dark:text-gray-100">
                            <div class="space-y-1">
                                <div
                                v-for="g in item.grades.filter(gr => gr.grade?.type === 1)"
                                :key="g.id"
                                class="flex justify-between items-center"
                                >
                                <span class="text-sm font-medium">
                                    {{ g.grade?.name ?? '-' }} <span class="text-xs text-gray-500">(Commercial)</span>
                                </span>
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                    {{ g.quantity }}
                                </span>
                                </div>
                            </div>
                            </td>

                            <td class="border-b px-4 py-2 text-gray-800 dark:text-gray-100 font-medium" rowspan="2">
                            {{ item.classification?.total_eggs ?? 0 }}
                            </td>
                            <td class="border-b px-4 py-2 flex gap-4" rowspan="2">
                            <Link
                                v-if="can('egg-classification-grades.edit')"
                                :href="`/egg-classification-grades/${item.id}/edit`"
                                class="text-indigo-600 hover:underline font-medium"
                            >
                                Edit
                            </Link>
                            <button
                                v-if="can('egg-classification-grades.view')"
                                @click="exportRowPdf(item.classification.id)"
                                class="text-green-600 hover:underline font-medium"
                            >
                                Report
                            </button>
                            </td>
                        </tr>

                        <!-- Second row: Hatching eggs -->
                        <tr
                            v-for="(item, index) in groupedArray"
                            :key="'hatch-' + item.classification.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            <td class="border-b px-4 py-2 text-gray-800 dark:text-gray-100">
                            <div class="space-y-1">
                                <div
                                v-for="g in item.grades.filter(gr => gr.grade?.type === 2)"
                                :key="g.id"
                                class="flex justify-between items-center"
                                >
                                <span class="text-sm font-medium">
                                    {{ g.grade?.name ?? '-' }} <span class="text-xs text-gray-500">(Hatching)</span>
                                </span>
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    {{ g.quantity }}
                                </span>
                                </div>
                            </div>
                            </td>
                        </tr>

                        <tr v-if="groupedArray.length === 0">
                            <td colspan="8" class="border-b px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                            No egg classification grades found.
                            </td>
                        </tr>
                        </tbody>

                </table>
            </div>

            <!-- Pagination -->
            <Pagination v-if="props.grades?.meta" :meta="props.grades.meta" class="mt-6" />
        </div>
    </AppLayout>
</template>
