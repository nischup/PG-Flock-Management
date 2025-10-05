<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import Pagination from '@/components/Pagination.vue';
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
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

// Feed interface
interface Feed {
    id: number;
    feed_type_id: number;
    feed_type_name: string;
    feed_name: string;
    status: string; // 'Active' | 'Inactive'
    created_at: string;
}

// Props
const props = defineProps<{
    feeds: {
        data: Feed[];
        meta: { current_page: number; last_page: number; per_page: number; total: number };
    };
    feedTypes: Array<{ id: number; name: string }>;
    filters: { 
        search?: string; 
        per_page?: number; 
        page?: number;
        status?: string;
        feed_type?: string;
        date_from?: string;
        date_to?: string;
    };
}>();

const { search, perPage, page, status, feedType, dateFrom, dateTo } = useListFilters({ routeName: '/feed', filters: props.filters });
const { can } = usePermissions();

// Export dropdown state
const openExportDropdown = ref(false);
const exportPdf = (orientation: 'portrait' | 'landscape' = 'portrait') => {
    const exportFilters = {
        search: search.value,
        per_page: perPage.value,
        status: status.value,
        feed_type: feedType.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
        orientation
    };
    const url = route('reports.feed.pdf', exportFilters);
    window.open(url, '_blank');
};
const exportExcel = () => {
    const exportFilters = {
        search: search.value,
        per_page: perPage.value,
        status: status.value,
        feed_type: feedType.value,
        date_from: dateFrom.value,
        date_to: dateTo.value
    };
    const url = route('reports.feed.excel', exportFilters);
    window.open(url, '_blank');
};

// ✅ Filter methods
const clearFilters = () => {
    search.value = '';
    perPage.value = 10;
    status.value = '';
    feedType.value = '';
    dateFrom.value = '';
    dateTo.value = '';
    page.value = 1;
};

// ✅ Computed properties for filter summary
const hasActiveFilters = computed(() => {
    return status.value || 
           feedType.value ||
           dateFrom.value || 
           dateTo.value;
});

// Modal state
const showModal = ref(false);
const editingFeed = ref<Feed | null>(null);

// Form
const form = useForm({
    feed_type_id: props.feedTypes.length ? props.feedTypes[0].id : 0,
    feed_name: '',
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

// Open modal
const openModal = (feed: Feed | null = null) => {
    if (feed) {
        editingFeed.value = feed;
        form.feed_type_id = feed.feed_type_id;
        form.feed_name = feed.feed_name;
        form.status = feed.status;
    } else {
        editingFeed.value = null;
        form.reset();
        form.feed_type_id = props.feedTypes.length ? props.feedTypes[0].id : 0;
        form.status = 1;
    }
    showModal.value = true;
};

// Reset modal
const resetForm = () => {
    form.reset();
    form.feed_type_id = props.feedTypes.length ? props.feedTypes[0].id : 0;
    form.status = 1;
    editingFeed.value = null;
    showModal.value = false;
};

// Submit
const submit = () => {
    if (!form.feed_name.trim()) return;

    if (editingFeed.value) {
        form.put(route('feed.update', editingFeed.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                resetForm();
                // Refresh the page to show updated data
                window.location.reload();
            },
            onError: () => Swal.fire('Error!', 'Could not update feed.', 'error'),
        });
    } else {
        form.post(route('feed.store'), {
            preserveScroll: true,
            onSuccess: () => {
                resetForm();
                // Refresh the page to show updated data
                window.location.reload();
            },
            onError: () => Swal.fire('Error!', 'Could not create feed.', 'error'),
        });
    }
};

// Toggle status
const toggleStatus = (feed: Feed) => {
    const newStatus = feed.status === 1 ? 0 : 1;
    router.put(
        route('feed.update', feed.id),
        { ...feed, status: newStatus },
        {
            preserveScroll: true,
            onSuccess: () => {
                // Refresh the page to show updated data
                window.location.reload();
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
    { title: 'Feed', href: '/master-setup/feed' },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Feeds" />

        <div class="px-4 py-6">
            <div class="mb-4 flex items-center justify-between">
                <HeadingSmall title="Feeds List" />
                <div class="relative flex items-center gap-2">
                    <!-- Add New Button -->
                    <Button v-if="can('feed.create')" class="bg-chicken text-white hover:bg-yellow-600" @click="openModal()"> + Add New </Button>

                    <!-- Export Report Dropdown -->
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

            <!-- Filter Section -->
            <div class="mb-6 rounded-lg border border-gray-200 bg-white p-4 shadow-sm">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-800">Filters</h3>
                    <button
                        @click="clearFilters"
                        class="text-sm text-gray-600 hover:text-gray-800"
                    >
                        Clear All
                    </button>
                </div>
                
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                    <!-- Search -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Feed name or type..."
                            class="block w-full px-3 py-2 text-sm text-gray-900 bg-white border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        />
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select
                            v-model="status"
                            class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">All Status</option>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>

                    <!-- Feed Type Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Feed Type</label>
                        <select
                            v-model="feedType"
                            class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="">All Feed Types</option>
                            <option v-for="ft in props.feedTypes" :key="ft.id" :value="ft.id">{{ ft.name }}</option>
                        </select>
                    </div>

                    <!-- Per Page -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Per Page</label>
                        <select
                            v-model="perPage"
                            class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
                        <div class="grid grid-cols-2 gap-2">
                            <input
                                v-model="dateFrom"
                                type="date"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                            <input
                                v-model="dateTo"
                                type="date"
                                class="block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feed Table -->
            <div class="mt-4 overflow-x-auto rounded-xl bg-white shadow dark:bg-gray-800">
                <table class="w-full border-collapse text-left">
                    <thead>
                        <tr>
                            <th class="border-b px-4 py-2 bg-blue-500 text-white font-semibold text-sm whitespace-nowrap">#</th>
                            <th class="border-b px-4 py-2 bg-green-500 text-white font-semibold text-sm whitespace-nowrap">Feed Type</th>
                            <th class="border-b px-4 py-2 bg-purple-500 text-white font-semibold text-sm whitespace-nowrap">Feed Name</th>
                            <th class="border-b px-4 py-2 bg-orange-500 text-white font-semibold text-sm whitespace-nowrap">Status</th>
                            <th class="border-b px-4 py-2 bg-pink-500 text-white font-semibold text-sm whitespace-nowrap">Created At</th>
                            <th class="border-b px-4 py-2 bg-gray-600 text-white font-semibold text-sm whitespace-nowrap">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(feed, index) in (props.feeds?.data ?? [])"
                            :key="feed.id"
                            class="hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            <td class="border-b px-4 py-2">{{ ((props.feeds?.meta?.current_page || 1) - 1) * (props.feeds?.meta?.per_page || 10) + index + 1 }}</td>
                            <td class="border-b px-4 py-2">{{ feed.feed_type_name }}</td>
                            <td class="border-b px-4 py-2">{{ feed.feed_name }}</td>
                            <td class="border-b px-4 py-2">
                                <span :class="feed.status === 'Active' ? 'font-semibold text-green-600' : 'font-semibold text-red-600'">
                                    {{ feed.status }}
                                </span>
                            </td>
                            <td class="border-b px-4 py-2">{{ dayjs(feed.created_at).format('YYYY-MM-DD') }}</td>
                            <td class="border-b px-4 py-2 relative">
                                <Button
                                    size="sm"
                                    class="actions-button bg-gray-500 text-white hover:bg-gray-600"
                                    @click.stop="toggleDropdown(feed.id)"
                                >
                                    Actions ▼
                                </Button>
                                <div
                                    v-if="openDropdownId === feed.id"
                                    class="dropdown-menu absolute z-10 mt-1 w-40 rounded border bg-white shadow-md"
                                    @click.stop
                                >
                                    <button class="w-full px-4 py-2 text-left hover:bg-gray-100" @click="openModal(feed)">✏ Edit</button>
                                    <button class="w-full px-4 py-2 text-left hover:bg-gray-100" @click="toggleStatus(feed)">
                                        {{ feed.status === 'Active' ? 'Inactive' : 'Activate' }}
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="(props.feeds?.data ?? []).length === 0">
                            <td colspan="6" class="border-b px-4 py-6 text-center text-gray-500">No feeds found.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <Pagination v-if="props.feeds?.meta" :meta="props.feeds.meta" class="mt-6" />
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex justify-center bg-black/30 pt-6" @click.self="resetForm">
            <div ref="modalRef" class="w-full max-w-2xl rounded-lg border border-gray-300 bg-white shadow-lg" style="top: 100px; position: absolute">
                <div class="flex cursor-move items-center justify-between border-b border-gray-200 p-4" @mousedown="startDrag">
                    <h3 class="text-xl font-semibold text-gray-900">{{ editingFeed ? 'Edit Feed' : 'Add New Feed' }}</h3>
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
                        <Label for="feed_type" class="mb-2">Feed Type</Label>
                        <select v-model="form.feed_type_id" id="feed_type" class="w-full rounded border p-2">
                            <option v-for="ft in props.feedTypes" :key="ft.id" :value="ft.id">{{ ft.name }}</option>
                        </select>
                    </div>

                    <div>
                        <Label for="feed_name" class="mb-2">Feed Name</Label>
                        <Input v-model="form.feed_name" id="feed_name" />
                        <span v-if="form.errors.feed_name" class="text-sm text-red-600">{{ form.errors.feed_name }}</span>
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
                    <Button class="bg-chicken text-white" @click="submit">{{ editingFeed ? 'Update' : 'Save' }}</Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
