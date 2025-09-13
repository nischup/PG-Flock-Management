<script setup lang="ts">
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
import { FileText, Pencil, Calendar, Eye, Trash2 } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

// ✅ Props
const props = defineProps<{
    dailyOperations?: {
        data: Array<{
            id: number;
            operation_date: string;
            flock_name: string;
            shed_name: string;
            company_name: string;
            batch_name: string;
            job_no: string;
            transaction_no: string;
            male_mortality: number;
            female_mortality: number;
            total_mortality: number;
            feed_consumption: string;
            water_consumption: string;
            light_hour: number;
            egg_collection: number;
            created_by_name: string;
            created_at: string;
            status: number;
            flock?: { id: number; name: string } | null;
            shed?: { id: number; name: string } | null;
            company?: { id: number; name: string } | null;
        }>;
        meta: { current_page: number; last_page: number; per_page: number; total: number };
    };
    filters?: { 
        search?: string; 
        per_page?: number; 
        company_id?: number; 
        flock_id?: number; 
        shed_id?: number; 
        date_from?: string; 
        date_to?: string; 
    };
    stage: string;
    companies?: Array<{ id: number; name: string }>;
    flocks?: Array<{ id: number; name: string }>;
    sheds?: Array<{ id: number; name: string }>;
}>();

// Map stage → display titles
const stageTitles: Record<string, string> = {
    brooding: "Brooding",
    growing: "Growing", 
    laying: "Laying / Production",
};

const currentTitle = stageTitles[props.stage] ?? props.stage;

useListFilters({ routeName: `/daily-operation/stage/${props.stage}`, filters: props.filters });
const { confirmDelete } = useNotifier();
const { can } = usePermissions();

// Delete operation
const deleteOperation = (id: number) => {
    confirmDelete({
        url: `/daily-operation/${props.stage}/${id}`,
        text: 'This will permanently delete the daily operation.',
        successMessage: 'Daily operation deleted.',
    });
};

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Farm Operations', href: '/daily-operation' },
    { title: currentTitle, href: `/daily-operation/stage/${props.stage}` },
];

// Dropdown state
const openDropdownId = ref<number | null>(null);
const toggleDropdown = (id: number) => {
    openDropdownId.value = openDropdownId.value === id ? null : id;
};

// Close dropdown when clicking outside
const handleClickOutside = (event: Event) => {
    const target = event.target as HTMLElement;
    if (!target.closest('.dropdown-container')) {
        openDropdownId.value = null;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <Head :title="`Daily Operations - ${currentTitle}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 m-3 bg-white dark:bg-gray-900 rounded-xl shadow-md">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
                <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">
                    {{ currentTitle }} Operations
                </h1>
                <Link 
                    :href="`/daily-operation/stage/${props.stage}/create`"
                    class="inline-flex items-center px-4 py-2 bg-chicken hover:bg-chicken text-white text-sm font-semibold rounded shadow transition"
                >
                    + Add Operation
                </Link>
            </div>

            <!-- Info Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <listInfocard
                    title="Total Operations"
                    :value="props.dailyOperations?.meta?.total || 0"
                    icon="Calendar"
                    color="blue"
                />
                <listInfocard
                    title="This Month"
                    :value="props.dailyOperations?.data?.length || 0"
                    icon="FileText"
                    color="green"
                />
                <listInfocard
                    title="Total Mortality"
                    :value="props.dailyOperations?.data?.reduce((sum, op) => sum + (op.total_mortality || 0), 0) || 0"
                    icon="Trash2"
                    color="red"
                />
                <listInfocard
                    title="Total Eggs"
                    :value="props.dailyOperations?.data?.reduce((sum, op) => sum + (op.egg_collection || 0), 0) || 0"
                    icon="Eye"
                    color="purple"
                />
            </div>

            <!-- Filters -->
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                        <input
                            type="text"
                            :value="props.filters?.search || ''"
                            @input="(e) => $inertia.get(`/daily-operation/stage/${props.stage}`, { ...props.filters, search: e.target.value }, { preserveState: true })"
                            placeholder="Search by flock, shed, company..."
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-chicken focus:border-chicken dark:bg-gray-700 dark:text-white"
                        />
                    </div>

                    <!-- Flock Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Flock</label>
                        <select
                            :value="props.filters?.flock_id || ''"
                            @change="(e) => $inertia.get(`/daily-operation/stage/${props.stage}`, { ...props.filters, flock_id: e.target.value }, { preserveState: true })"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-chicken focus:border-chicken dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">All Flocks</option>
                            <option v-for="flock in props.flocks" :key="flock.id" :value="flock.id">
                                {{ flock.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Shed Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Shed</label>
                        <select
                            :value="props.filters?.shed_id || ''"
                            @change="(e) => $inertia.get(`/daily-operation/stage/${props.stage}`, { ...props.filters, shed_id: e.target.value }, { preserveState: true })"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-chicken focus:border-chicken dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">All Sheds</option>
                            <option v-for="shed in props.sheds" :key="shed.id" :value="shed.id">
                                {{ shed.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Company Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Company</label>
                        <select
                            :value="props.filters?.company_id || ''"
                            @change="(e) => $inertia.get(`/daily-operation/stage/${props.stage}`, { ...props.filters, company_id: e.target.value }, { preserveState: true })"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-chicken focus:border-chicken dark:bg-gray-700 dark:text-white"
                        >
                            <option value="">All Companies</option>
                            <option v-for="company in props.companies" :key="company.id" :value="company.id">
                                {{ company.name }}
                            </option>
                        </select>
                    </div>

                    <!-- Date From -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date From</label>
                        <input
                            type="date"
                            :value="props.filters?.date_from || ''"
                            @change="(e) => $inertia.get(`/daily-operation/stage/${props.stage}`, { ...props.filters, date_from: e.target.value }, { preserveState: true })"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-chicken focus:border-chicken dark:bg-gray-700 dark:text-white"
                        />
                    </div>

                    <!-- Date To -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Date To</label>
                        <input
                            type="date"
                            :value="props.filters?.date_to || ''"
                            @change="(e) => $inertia.get(`/daily-operation/stage/${props.stage}`, { ...props.filters, date_to: e.target.value }, { preserveState: true })"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-chicken focus:border-chicken dark:bg-gray-700 dark:text-white"
                        />
                    </div>

                    <!-- Per Page -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Per Page</label>
                        <select
                            :value="props.filters?.per_page || 15"
                            @change="(e) => $inertia.get(`/daily-operation/stage/${props.stage}`, { ...props.filters, per_page: e.target.value }, { preserveState: true })"
                            class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-chicken focus:border-chicken dark:bg-gray-700 dark:text-white"
                        >
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>

                    <!-- Clear Filters -->
                    <div class="flex items-end">
                        <Button
                            @click="$inertia.get(`/daily-operation/stage/${props.stage}`)"
                            variant="outline"
                            class="w-full"
                        >
                            Clear Filters
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                #
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Date
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Flock
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Shed
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Company
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Male Mortality
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Female Mortality
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Total Mortality
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Feed
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Water
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Light (hrs)
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Eggs
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Created By
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        <tr
                            v-for="(item, index) in props.dailyOperations?.data ?? []"
                            :key="item.id"
                            class="odd:bg-white even:bg-gray-50 hover:bg-gray-100 dark:odd:bg-gray-900 dark:even:bg-gray-800"
                        >
                            <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400 whitespace-nowrap font-medium">
                                {{ ((props.dailyOperations?.meta?.current_page || 1) - 1) * (props.dailyOperations?.meta?.per_page || 10) + index + 1 }}
                            </td>
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap">
                                {{ dayjs(item.operation_date).format('MMM DD, YYYY') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ item.flock_name }}</td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200">
                                    {{ item.shed_name }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">{{ item.company_name }}</td>
                            <td class="px-4 py-3 text-center whitespace-nowrap text-red-600 font-medium">
                                {{ item.male_mortality || 0 }}
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap text-red-600 font-medium">
                                {{ item.female_mortality || 0 }}
                            </td>
                            <td class="px-4 py-3 text-center font-medium whitespace-nowrap text-red-700">
                                {{ item.total_mortality || 0 }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-green-600 font-medium">
                                {{ item.feed_consumption }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-blue-600 font-medium">
                                {{ item.water_consumption }}
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap text-yellow-600 font-medium">
                                {{ item.light_hour || 0 }}
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap text-purple-600 font-medium">
                                {{ item.egg_collection || 0 }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-500 dark:text-gray-400">
                                {{ item.created_by_name }}
                            </td>
                            <td class="px-4 py-3 text-center whitespace-nowrap text-sm font-medium">
                                <div class="relative dropdown-container">
                                    <button
                                        @click="toggleDropdown(item.id)"
                                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                    >
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                        </svg>
                                    </button>
                                    <div
                                        v-if="openDropdownId === item.id"
                                        class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg z-10 border border-gray-200 dark:border-gray-700"
                                    >
                                        <div class="py-1">
                                            <Link
                                                :href="`/daily-operation/stage/${props.stage}/${item.id}/edit`"
                                                class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                                            >
                                                <Pencil class="w-4 h-4 mr-2" />
                                                Edit
                                            </Link>
                                            <Link
                                                :href="`/daily-operation/stage/${props.stage}/${item.id}`"
                                                class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700"
                                            >
                                                <Eye class="w-4 h-4 mr-2" />
                                                View Details
                                            </Link>
                                            <button
                                                @click="deleteOperation(item.id)"
                                                class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-gray-700"
                                            >
                                                <Trash2 class="w-4 h-4 mr-2" />
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!props.dailyOperations?.data?.length">
                            <td colspan="13" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                No daily operations found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <Pagination :meta="props.dailyOperations?.meta" class="mt-6" />
        </div>
    </AppLayout>
</template>
