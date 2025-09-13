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
import { FileText, Pencil, Calendar, User, Building2, Users, Shield, Eye, Trash2 } from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

// ✅ Props
const props = defineProps<{
    users?: {
        data: Array<{
            id: number;
            name: string;
            email: string;
            roles: Array<{ id: number; name: string }>;
            permissions: Array<{ id: number; name: string }>;
            company?: { id: number; name: string } | null;
            shed?: { id: number; name: string } | null;
            created_at: string;
            updated_at: string;
        }>;
        meta: {
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
        };
    };
    filters?: { 
        search?: string; 
        per_page?: number; 
        company_id?: number; 
        shed_id?: number; 
        role_id?: number;
        date_from?: string; 
        date_to?: string; 
    };
    companies?: Array<{ id: number; name: string }>;
    sheds?: Array<{ id: number; name: string }>;
    roles?: Array<{ id: number; name: string }>;
}>();

useListFilters({ routeName: '/user-register', filters: props.filters });
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
const showCompanyDropdown = ref(false);
const showShedDropdown = ref(false);
const showRoleDropdown = ref(false);

// ✅ Close on outside click
const handleClick = (e: MouseEvent) => {
    if (!(e.target as HTMLElement).closest('.action-btn, .pdf-dropdown, .pdf-button, .date-picker-container, .company-dropdown, .shed-dropdown, .role-dropdown')) {
        closeDropdown();
        openExportDropdown.value = false;
        showFromDatePicker.value = false;
        showToDatePicker.value = false;
        showCompanyDropdown.value = false;
        showShedDropdown.value = false;
        showRoleDropdown.value = false;
    }
};
onMounted(() => document.addEventListener('click', handleClick));
onBeforeUnmount(() => document.removeEventListener('click', handleClick));

// ✅ Delete action
const deleteUser = (id: number) => {
    confirmDelete({
        url: `/user-register/${id}`,
        text: 'This will permanently delete the user.',
        successMessage: 'User deleted successfully.',
    });
};

// ✅ Export filters
const filters = ref({
    search: props.filters?.search ?? '',
    per_page: props.filters?.per_page ?? 10,
    company_id: props.filters?.company_id ?? '',
    shed_id: props.filters?.shed_id ?? '',
    role_id: props.filters?.role_id ?? '',
    date_from: props.filters?.date_from ?? '',
    date_to: props.filters?.date_to ?? '',
});

// ✅ Filter methods
const applyFilters = () => {
    const params = new URLSearchParams();
    
    if (filters.value.search) params.set('search', filters.value.search);
    if (filters.value.per_page) params.set('per_page', filters.value.per_page.toString());
    if (filters.value.company_id) params.set('company_id', filters.value.company_id.toString());
    if (filters.value.shed_id) params.set('shed_id', filters.value.shed_id.toString());
    if (filters.value.role_id) params.set('role_id', filters.value.role_id.toString());
    if (filters.value.date_from) params.set('date_from', filters.value.date_from);
    if (filters.value.date_to) params.set('date_to', filters.value.date_to);
    
    window.location.href = `/user-register?${params.toString()}`;
};

const clearFilters = () => {
    filters.value = {
        search: '',
        per_page: 10,
        company_id: '',
        shed_id: '',
        role_id: '',
        date_from: '',
        date_to: '',
    };
    applyFilters();
};

// ✅ Computed properties for filter summary
const hasActiveFilters = computed(() => {
    return filters.value.company_id || 
           filters.value.shed_id || 
           filters.value.role_id ||
           filters.value.date_from || 
           filters.value.date_to;
});

const getCompanyName = (companyId: string | number) => {
    const company = props.companies?.find(c => c.id === Number(companyId));
    return company?.name || 'Unknown';
};

const getShedName = (shedId: string | number) => {
    const shed = props.sheds?.find(s => s.id === Number(shedId));
    return shed?.name || 'Unknown';
};

const getRoleName = (roleId: string | number) => {
    const role = props.roles?.find(r => r.id === Number(roleId));
    return role?.name || 'Unknown';
};

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

// Company dropdown helper functions
const getSelectedCompanyName = () => {
    if (!filters.value.company_id) return '';
    const company = props.companies?.find(c => c.id === Number(filters.value.company_id));
    return company?.name || '';
};

const selectCompany = (companyId: string | number) => {
    filters.value.company_id = companyId ? Number(companyId) : '';
    showCompanyDropdown.value = false;
};

const clearCompanyFilter = () => {
    filters.value.company_id = '';
    showCompanyDropdown.value = false;
};

// Shed dropdown helper functions
const getSelectedShedName = () => {
    if (!filters.value.shed_id) return '';
    const shed = props.sheds?.find(s => s.id === Number(filters.value.shed_id));
    return shed?.name || '';
};

const selectShed = (shedId: string | number) => {
    filters.value.shed_id = shedId ? Number(shedId) : '';
    showShedDropdown.value = false;
};

const clearShedFilter = () => {
    filters.value.shed_id = '';
    showShedDropdown.value = false;
};

// Role dropdown helper functions
const getSelectedRoleName = () => {
    if (!filters.value.role_id) return '';
    const role = props.roles?.find(r => r.id === Number(filters.value.role_id));
    return role?.name || '';
};

const selectRole = (roleId: string | number) => {
    filters.value.role_id = roleId ? Number(roleId) : '';
    showRoleDropdown.value = false;
};

const clearRoleFilter = () => {
    filters.value.role_id = '';
    showRoleDropdown.value = false;
};

const openExportDropdown = ref(false);

const exportPdf = (orientation: 'portrait' | 'landscape' = 'portrait') => {
    const url = route('reports.users.pdf', { ...filters.value, orientation });
    window.open(url, '_blank');
};

const exportExcel = () => {
    const url = route('reports.users.excel', { ...filters.value });
    window.open(url, '_blank');
};

// ✅ Dynamic data for cards based on selected User
const selectedUser = ref<number | null>(null);
const cardData = computed(() => {
    if (!selectedUser.value || !props.users?.data) return [];
    
    const selectedItem = props.users.data.find(item => item.id === selectedUser.value);
    if (!selectedItem) return [];
    
    return [
        { 
            title: 'User Name', 
            value: selectedItem.name, 
            title1: 'Email', 
            value1: selectedItem.email, 
            title2: 'Company', 
            value2: selectedItem.company?.name || 'N/A'
        },
        { 
            title: 'Shed', 
            value: selectedItem.shed?.name || 'N/A',
            title1: 'Role',
            value1: selectedItem.roles[0]?.name || 'N/A',
            title2: 'Permissions',
            value2: selectedItem.permissions.length.toString()
        },
        { 
            title: 'Created', 
            value: formatDate(selectedItem.created_at) || 'N/A',
            title1: 'Updated',
            value1: formatDate(selectedItem.updated_at) || 'N/A',
            title2: '',
            value2: ''
        },
    ];
});

// ✅ Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Users', href: '/user-register' },
    { title: 'User Management', href: '/user-register' },
];
</script>

<template>
    <Head title="User Management" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Select Box -->
        <div class="m-5 w-full max-w-sm">
            <select
                v-model="selectedUser"
                class="block w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 sm:text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
            >
                <option :value="null" disabled>Select User Record</option>
                <option 
                    v-for="item in props.users?.data ?? []" 
                    :key="item.id" 
                    :value="item.id"
                >
                    {{ item.name }} - {{ item.email }} : {{ dayjs(item.created_at).format('YYYY-MM-DD') }}
                </option>
            </select>
        </div>

        <listInfocard :cards="cardData" />

        <div class="m-3 rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
            <!-- Header -->
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="text-xl font-semibold text-gray-800 dark:text-white">User Management System</h1>
                <div class="flex items-center gap-2">
                    <Link
                        v-if="can('user.create')"
                        href="/user-register/create"
                        class="group relative overflow-hidden rounded-md px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-gray-500"
                        style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);"
                    >
                        <span class="relative z-10 flex items-center gap-2">
                            <User class="h-4 w-4 transition-transform duration-300 group-hover:rotate-12" />
                            Add User
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
                                <FileText class="h-4 w-4 transition-transform duration-300 group-hover:rotate-12" />
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
                    <!-- Search Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input
                                v-model="filters.search"
                                type="text"
                                placeholder="Name, Email, Company..."
                                class="block w-full pl-10 pr-4 py-2.5 text-sm text-gray-900 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 transition-all duration-200"
                            />
                            <button
                                v-if="filters.search"
                                @click="filters.search = ''"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Company Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Company</label>
                        <div class="company-dropdown relative">
                            <button
                                @click="showCompanyDropdown = !showCompanyDropdown"
                                type="button"
                                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white transition-all duration-200"
                            >
                                <span class="flex items-center gap-2">
                                    <Building2 class="h-4 w-4 text-gray-400" />
                                    {{ getSelectedCompanyName() || 'All Companies' }}
                                </span>
                                <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showCompanyDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <!-- Company Dropdown -->
                            <div
                                v-if="showCompanyDropdown"
                                class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700"
                            >
                                <div class="p-2">
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Select Company</span>
                                        <button
                                            @click="clearCompanyFilter"
                                            class="text-xs text-red-600 hover:text-red-800 dark:text-red-400"
                                        >
                                            Clear
                                        </button>
                                    </div>
                                    <div class="max-h-48 space-y-1 overflow-y-auto">
                                        <button
                                            @click="selectCompany('')"
                                            class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                            :class="{ 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': !filters.company_id }"
                                        >
                                            <span>All Companies</span>
                                        </button>
                                        <button
                                            v-for="company in props.companies ?? []"
                                            :key="company.id"
                                            @click="selectCompany(company.id)"
                                            class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                            :class="{ 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': filters.company_id == company.id }"
                                        >
                                            <span>{{ company.name }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Shed Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Shed</label>
                        <div class="shed-dropdown relative">
                            <button
                                @click="showShedDropdown = !showShedDropdown"
                                type="button"
                                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white transition-all duration-200"
                            >
                                <span class="flex items-center gap-2">
                                    <Building2 class="h-4 w-4 text-gray-400" />
                                    {{ getSelectedShedName() || 'All Sheds' }}
                                </span>
                                <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showShedDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <!-- Shed Dropdown -->
                            <div
                                v-if="showShedDropdown"
                                class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700"
                            >
                                <div class="p-2">
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Select Shed</span>
                                        <button
                                            @click="clearShedFilter"
                                            class="text-xs text-red-600 hover:text-red-800 dark:text-red-400"
                                        >
                                            Clear
                                        </button>
                                    </div>
                                    <div class="max-h-48 space-y-1 overflow-y-auto">
                                        <button
                                            @click="selectShed('')"
                                            class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                            :class="{ 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': !filters.shed_id }"
                                        >
                                            <span>All Sheds</span>
                                        </button>
                                        <button
                                            v-for="shed in props.sheds ?? []"
                                            :key="shed.id"
                                            @click="selectShed(shed.id)"
                                            class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                            :class="{ 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': filters.shed_id == shed.id }"
                                        >
                                            <span>{{ shed.name }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Role Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Role</label>
                        <div class="role-dropdown relative">
                            <button
                                @click="showRoleDropdown = !showRoleDropdown"
                                type="button"
                                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white transition-all duration-200"
                            >
                                <span class="flex items-center gap-2">
                                    <Shield class="h-4 w-4 text-gray-400" />
                                    {{ getSelectedRoleName() || 'All Roles' }}
                                </span>
                                <svg class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showRoleDropdown }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            
                            <!-- Role Dropdown -->
                            <div
                                v-if="showRoleDropdown"
                                class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-600 dark:bg-gray-700"
                            >
                                <div class="p-2">
                                    <div class="mb-2 flex items-center justify-between">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Select Role</span>
                                        <button
                                            @click="clearRoleFilter"
                                            class="text-xs text-red-600 hover:text-red-800 dark:text-red-400"
                                        >
                                            Clear
                                        </button>
                                    </div>
                                    <div class="max-h-48 space-y-1 overflow-y-auto">
                                        <button
                                            @click="selectRole('')"
                                            class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                            :class="{ 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': !filters.role_id }"
                                        >
                                            <span>All Roles</span>
                                        </button>
                                        <button
                                            v-for="role in props.roles ?? []"
                                            :key="role.id"
                                            @click="selectRole(role.id)"
                                            class="flex w-full items-center gap-2 rounded px-3 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                            :class="{ 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200': filters.role_id == role.id }"
                                        >
                                            <span>{{ role.name }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Actions -->
                <div class="mt-4 flex justify-end gap-2">
                    <button
                        @click="applyFilters"
                        class="rounded-md bg-black px-4 py-2 text-sm font-medium text-white shadow-lg hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200"
                        style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);"
                    >
                        Apply Filters
                    </button>
                </div>
            </div>

            <!-- Responsive Table -->
            <div class="relative">
                <!-- Scroll indicator -->
                <div class="mb-2 flex items-center justify-between text-sm text-gray-500 dark:text-gray-400">
                    <span class="flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"></path>
                        </svg>
                        Scroll horizontally to see all columns
                    </span>
                    <span class="text-xs">{{ props.users?.data?.length ?? 0 }} records</span>
                </div>

            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700" style="min-width: 1200px;">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr class="text-left text-gray-600 dark:text-gray-300">
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 60px;">S/N</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 150px;">Name</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 200px;">Email</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 150px;">Company</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 120px;">Shed</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 120px;">Role</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 100px;">Permissions</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 120px;">Created</th>
                                <th class="px-4 py-3 font-semibold whitespace-nowrap" style="min-width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        <tr
                                v-for="(user, index) in props.users?.data ?? []"
                            :key="user.id"
                                class="odd:bg-white even:bg-gray-50 hover:bg-gray-100 dark:odd:bg-gray-900 dark:even:bg-gray-800"
                            >
                                <td class="px-4 py-3 text-center text-gray-500 dark:text-gray-400 whitespace-nowrap font-medium">
                                    {{ ((props.users?.meta?.current_page || 1) - 1) * (props.users?.meta?.per_page || 10) + index + 1 }}
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-900 dark:text-white whitespace-nowrap">{{ user.name }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ user.email }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ user.company?.name || 'N/A' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ user.shed?.name || 'N/A' }}</td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        {{ user.roles[0]?.name || 'N/A' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ user.permissions.length }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">{{ dayjs(user.created_at).format('MMM DD, YYYY') }}</td>
                                <td class="relative px-4 py-3">
                                <Button size="sm" class="action-btn bg-gray-500 text-white hover:bg-gray-600" @click.stop="toggleDropdown(user.id)">
                                    Actions ▼
                                </Button>

                                <!-- Action Popup Overlay -->
                                <div
                                    v-if="openDropdownId === user.id"
                                    class="fixed inset-0 z-50 flex items-center justify-center"
                                    @click.stop="closeDropdown"
                                >
                                    <!-- Backdrop -->
                                    <div class="absolute inset-0 bg-black/20 backdrop-blur-sm"></div>
                                    
                                    <!-- Popup Content -->
                                    <div
                                        class="relative z-10 w-48 rounded-lg border border-gray-200 bg-white shadow-xl dark:border-gray-700 dark:bg-gray-800"
                                        @click.stop
                                    >
                                        <!-- Header -->
                                        <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Actions</h3>
                                        </div>
                                        
                                        <!-- Actions List -->
                                        <div class="py-2">
                                            <!-- View -->
                                            <button
                                                v-if="can('user.view')"
                                                class="flex w-full items-center gap-3 px-4 py-3 text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors duration-200"
                                                @click="closeDropdown"
                                            >
                                                <Eye class="h-5 w-5 text-gray-500" />
                                                <span class="text-sm font-medium">View Details</span>
                                            </button>

                                            <!-- Edit -->
                                            <Link
                                                v-if="can('user.edit')"
                                                :href="route('users.edit', user.id)"
                                                class="flex items-center gap-3 px-4 py-3 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/20 transition-colors duration-200"
                                                @click="closeDropdown"
                                            >
                                                <Pencil class="h-5 w-5" />
                                                <span class="text-sm font-medium">Edit User</span>
                                            </Link>

                                            <!-- Delete -->
                                            <button
                                                v-if="can('user.delete')"
                                                @click="deleteUser(user.id); closeDropdown()"
                                                class="flex w-full items-center gap-3 px-4 py-3 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 transition-colors duration-200"
                                            >
                                                <Trash2 class="h-5 w-5" />
                                                <span class="text-sm font-medium">Delete User</span>
                                            </button>
                                        </div>
                                        
                                        <!-- Footer -->
                                        <div class="border-t border-gray-200 px-4 py-2 dark:border-gray-700">
                                            <button
                                                @click="closeDropdown"
                                                class="w-full rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 transition-colors duration-200"
                                            >
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="(props.users?.data ?? []).length === 0">
                                <td colspan="9" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">No users found.</td>
                        </tr>
                    </tbody>
                </table>
                </div>
            </div>

            <Pagination :meta="props.users?.meta" class="mt-6" />
        </div>
    </AppLayout>
</template>

<style scoped>
/* Custom scrollbar for table */
.overflow-x-auto::-webkit-scrollbar {
    height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Dark mode scrollbar */
.dark .overflow-x-auto::-webkit-scrollbar-track {
    background: #374151;
}

.dark .overflow-x-auto::-webkit-scrollbar-thumb {
    background: #6b7280;
}

.dark .overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #9ca3af;
}

/* Smooth scrolling */
.overflow-x-auto {
    scroll-behavior: smooth;
}
</style>
