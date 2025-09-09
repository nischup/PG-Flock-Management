<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

import FileUploader from '@/components/FileUploader.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useDropdownOptions } from '@/composables/dropdownOptions';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { ArrowLeft } from 'lucide-vue-next';
import Multiselect from 'vue-multiselect';
import 'vue-multiselect/dist/vue-multiselect.css';

const { transportTypes, shipmentTypes } = useDropdownOptions();
// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Parent Stock', href: '/ps-receive' },
    { title: 'Receive Entry', href: '/ps-receive/create' },
];

const props = defineProps<{
    suppliers: Array<{ id: number; name: string }>;
    breedTypes: Array<{ id: number; name: string }>;
    companies: Array<{ id: number; name: string }>;
    countries: Array<{ id: number; name: string }>;
}>();

// Reactive suppliers list that will be filtered
const filteredSuppliers = ref<Array<{ id: number; name: string }>>(props.suppliers);

// Search functionality for suppliers
const supplierSearchQuery = ref('');
const showSupplierDropdown = ref(false);
const selectedSupplier = ref<{ id: number; name: string } | null>(null);
const highlightedIndex = ref(-1);

// Search functionality for countries
const countrySearchQuery = ref('');
const showCountryDropdown = ref(false);
const selectedCountry = ref<{ id: number; name: string } | null>(null);
const highlightedCountryIndex = ref(-1);

// Form data
const form = useForm({
    shipment_type_id: 1,
    pi_no: '',
    pi_date: null,
    order_no: '',
    order_date: null,
    lc_no: '',
    lc_date: null,
    supplier_id: '',
    breed_type: [],
    country_of_origin: 1,
    transport_type: '',
    vehicle_inside_temp: '',

    remarks: '',
    file: [], // file upload
    labfile: [],
    net_weight: 0,
    gross_weight: 0,
    company_id: 0,
    ps_bonus_qty: 0,

    ps_male_rec_box: 0, // Male Box Receive Qty
    ps_male_qty: 0, // Male Chicks Qty (can be auto-calculated if needed)

    // Female
    ps_female_rec_box: 0, // Female Box Receive Qty
    ps_female_qty: 0, // Female Chicks Qty

    // Totals
    ps_total_qty: 0, // Total Chicks Qty (Male + Female)
    ps_total_re_box_qty: 0, // Total Box Qty (Male + Female)

    // Challan
    ps_challan_box_qty: 0, // Challan Box Qty

    // Weight
    ps_gross_weight: 0,
    ps_net_weight: 0,

    lab_type: 'Gov Lab',
    gov_lab_send_female_qty: 0,
    gov_lab_send_male_qty: 0,
    gov_lab_send_total_qty: 0,

    provita_lab_type: 'Provita Lab',
    provita_lab_send_female_qty: 0,
    provita_lab_send_male_qty: 0,
    provita_lab_send_total_qty: 0,
});

// Function to fetch suppliers by shipment type
const fetchSuppliersByShipmentType = async (shipmentTypeId: number) => {
    try {
        const response = await axios.get('/ps-receive/suppliers-by-shipment-type', {
            params: { shipment_type_id: shipmentTypeId }
        });
        filteredSuppliers.value = response.data;
        // Reset supplier selection when shipment type changes
        form.supplier_id = '';
    } catch (error) {
        console.error('Error fetching suppliers:', error);
        // Fallback to all suppliers on error
        filteredSuppliers.value = props.suppliers;
    }
};

// Computed property for searchable suppliers
const searchableSuppliers = computed(() => {
    if (!supplierSearchQuery.value) {
        return filteredSuppliers.value;
    }
    return filteredSuppliers.value.filter(supplier =>
        supplier.name.toLowerCase().includes(supplierSearchQuery.value.toLowerCase())
    );
});

// Computed property for searchable countries
const searchableCountries = computed(() => {
    if (!countrySearchQuery.value) {
        return props.countries;
    }
    return props.countries.filter(country =>
        country.name.toLowerCase().includes(countrySearchQuery.value.toLowerCase())
    );
});

// Methods for supplier search
const selectSupplier = (supplier: { id: number; name: string }) => {
    selectedSupplier.value = supplier;
    form.supplier_id = supplier.id;
    supplierSearchQuery.value = supplier.name;
    showSupplierDropdown.value = false;
};

const clearSupplierSelection = () => {
    selectedSupplier.value = null;
    form.supplier_id = '';
    supplierSearchQuery.value = '';
    showSupplierDropdown.value = false;
};

const toggleSupplierDropdown = () => {
    showSupplierDropdown.value = !showSupplierDropdown.value;
    if (showSupplierDropdown.value) {
        supplierSearchQuery.value = '';
        highlightedIndex.value = -1;
    }
};

// Methods for country search
const selectCountry = (country: { id: number; name: string }) => {
    selectedCountry.value = country;
    form.country_of_origin = country.id;
    countrySearchQuery.value = country.name;
    showCountryDropdown.value = false;
};

const clearCountrySelection = () => {
    selectedCountry.value = null;
    form.country_of_origin = 1; // Default to first country
    countrySearchQuery.value = '';
    showCountryDropdown.value = false;
};

const toggleCountryDropdown = () => {
    showCountryDropdown.value = !showCountryDropdown.value;
    if (showCountryDropdown.value) {
        countrySearchQuery.value = '';
        highlightedCountryIndex.value = -1;
    }
};

const handleKeydown = (event: KeyboardEvent) => {
    if (!showSupplierDropdown.value) return;

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault();
            highlightedIndex.value = Math.min(highlightedIndex.value + 1, searchableSuppliers.value.length - 1);
            break;
        case 'ArrowUp':
            event.preventDefault();
            highlightedIndex.value = Math.max(highlightedIndex.value - 1, -1);
            break;
        case 'Enter':
            event.preventDefault();
            if (highlightedIndex.value >= 0 && searchableSuppliers.value[highlightedIndex.value]) {
                selectSupplier(searchableSuppliers.value[highlightedIndex.value]);
            }
            break;
        case 'Escape':
            showSupplierDropdown.value = false;
            highlightedIndex.value = -1;
            break;
    }
};

const handleCountryKeydown = (event: KeyboardEvent) => {
    if (!showCountryDropdown.value) return;

    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault();
            highlightedCountryIndex.value = Math.min(highlightedCountryIndex.value + 1, searchableCountries.value.length - 1);
            break;
        case 'ArrowUp':
            event.preventDefault();
            highlightedCountryIndex.value = Math.max(highlightedCountryIndex.value - 1, -1);
            break;
        case 'Enter':
            event.preventDefault();
            if (highlightedCountryIndex.value >= 0 && searchableCountries.value[highlightedCountryIndex.value]) {
                selectCountry(searchableCountries.value[highlightedCountryIndex.value]);
            }
            break;
        case 'Escape':
            showCountryDropdown.value = false;
            highlightedCountryIndex.value = -1;
            break;
    }
};

// Click outside handler
const supplierDropdownRef = ref<HTMLElement | null>(null);
const countryDropdownRef = ref<HTMLElement | null>(null);

const handleClickOutside = (event: Event) => {
    if (supplierDropdownRef.value && !supplierDropdownRef.value.contains(event.target as Node)) {
        showSupplierDropdown.value = false;
    }
    if (countryDropdownRef.value && !countryDropdownRef.value.contains(event.target as Node)) {
        showCountryDropdown.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    // Initialize selected country on component mount
    if (props.countries.length > 0) {
        selectedCountry.value = props.countries[0];
        form.country_of_origin = props.countries[0].id;
    }
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Watch for shipment type changes
watch(() => form.shipment_type_id, (newShipmentTypeId) => {
    if (newShipmentTypeId) {
        fetchSuppliersByShipmentType(newShipmentTypeId);
        // Reset supplier selection when shipment type changes
        clearSupplierSelection();
    }
}, { immediate: true });

function submit() {
    const formData = new FormData();

    for (const key in form) {
        const value = form[key as keyof typeof form];

        if (key === 'file') {
            // Append each File to FormData
            (value as File[]).forEach((f) => {
                formData.append('file[]', f);
            });
        } else {
            // Convert numbers to string before appending
            formData.append(key, value != null ? String(value) : '');
        }
    }

    // Use Inertia to post
    form.post(route('ps-receive.store'), {
        data: formData,
        headers: { 'Content-Type': 'multipart/form-data' },
        onSuccess: () => {
            form.reset();
            // Optional: show a success notification
        },
        onError: () => {
            // Optional: handle validation errors
        },
    });
}

watch(
    () => [form.ps_male_qty, form.ps_female_qty, form.ps_male_rec_box, form.ps_female_rec_box],
    () => {
        // Total chicks quantity
        form.ps_total_qty = Number(form.ps_male_qty || 0) + Number(form.ps_female_qty || 0);

        // Total box quantity
        form.ps_total_re_box_qty = Number(form.ps_male_rec_box || 0) + Number(form.ps_female_rec_box || 0);
    },
    { deep: true, immediate: true },
);

function updateTotalQty() {
    // Subtotals
    form.provita_lab_send_total_qty = Number(form.provita_lab_send_male_qty || 0) + Number(form.provita_lab_send_female_qty || 0);

    form.gov_lab_send_total_qty = Number(form.gov_lab_send_male_qty || 0) + Number(form.gov_lab_send_female_qty || 0);
}

const tabs = [
    { key: 'master', label: 'Shipment Info' },
    { key: 'receive', label: 'Receive Quantity' },
    { key: 'lab', label: 'Lab Transfer' },
];

const activeTab = ref(0);
const tabErrors = ref<{ [key: string]: string }>({});
const completedTabs = ref<number[]>([]);
function goNext() {
    if (!validateTab(activeTab.value)) return;

    if (!completedTabs.value.includes(activeTab.value)) {
        completedTabs.value.push(activeTab.value);
    }

    if (activeTab.value < tabs.length - 1) {
        activeTab.value++;
    }
}

function goPrevious() {
    if (activeTab.value > 0) activeTab.value--;
}

function validateTab(index: number) {
    tabErrors.value = {};

    if (tabs[index].key === 'master') {
        if (!form.pi_no) tabErrors.value.pi_no = 'PI No required';
        if (!form.pi_date) tabErrors.value.pi_date = 'PI Date required';
        if (!form.order_no) tabErrors.value.order_no = 'Order No required';
        if (!form.order_date) tabErrors.value.order_date = 'Order Date required';
        return Object.keys(tabErrors.value).length === 0;
    }

    if (tabs[index].key === 'receive') {
        if (form.ps_total_qty <= 0) tabErrors.value.ps_total_qty = 'Total Qty required';
        return Object.keys(tabErrors.value).length === 0;
    }

    if (tabs[index].key === 'lab') {
        if (form.gov_lab_send_total_qty <= 0 && form.provita_lab_send_female_qty + form.provita_lab_send_male_qty <= 0) {
            tabErrors.value.lab_send_total_qty = 'At least one lab qty required';
            return false;
        }
        return true;
    }

    return true;
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Parent Stock Receive" />

        <div class="space-y-8 px-4 py-6">
            <form @submit.prevent="submit" class="space-y-8" enctype="multipart/form-data">
                <!-- Tabs Navigation -->
                <div class="mb-6 flex items-center justify-between">
                    <!-- Tabs -->
                    <div class="mb-6 flex flex-wrap gap-4">
                        <div
                            v-for="(tab, index) in tabs"
                            :key="tab.key"
                            @click="activeTab = index"
                            class="min-w-[150px] flex-1 cursor-pointer border p-6 text-center font-semibold shadow transition-transform hover:scale-105"
                            :class="[
                                activeTab === index
                                    ? 'bg-chicken text-white'
                                    : completedTabs.includes(index)
                                      ? 'bg-green-500 text-white'
                                      : 'bg-white text-gray-700 hover:bg-gray-100',
                            ]"
                        >
                            {{ tab.label }}
                        </div>
                    </div>

                    <!-- List Link aligned to right -->
                    <Link href="/ps-receive" class="flex items-center gap-1 rounded-md bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200">
                        <ArrowLeft class="h-4 w-4" /> List
                    </Link>
                </div>

                <!-- Master Info Tab -->
                <div v-if="activeTab === 0" class="space-y-6 rounded-lg border-b bg-white p-4 shadow-sm">
                    <div class="mb-6 flex items-center justify-between pb-3">
                        <h2 class="text-xl font-semibold">Parent Stock Receiving Info.</h2>
                    </div>

                    <div class="grid grid-cols-3 gap-6">
                        <div class="flex flex-col">
                            <Label>Shipment Type</Label>
                            <select v-model="form.shipment_type_id" class="mt-2 rounded border px-3 py-2">
                                <option v-for="shipmenttype in shipmentTypes" :key="shipmenttype.id" :value="shipmenttype.id">
                                    {{ shipmenttype.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.shipment_type_id" class="mt-1" />
                        </div>

                        <div class="flex flex-col">
                            <Label>PI No</Label>
                            <Input
                                v-model="form.pi_no"
                                type="text"
                                placeholder="Enter PI No"
                                class="mt-2"
                                :class="tabErrors.pi_no ? 'border-red-500' : form.errors.pi_no ? 'border-red-500' : 'border-gray-300'"
                            />
                            <InputError :message="tabErrors.pi_no || form.errors.pi_no" class="mt-1" />
                        </div>

                        <div class="flex flex-col">
                            <Label>PI Date</Label>
                            <Datepicker
                                v-model="form.pi_date"
                                format="yyyy-MM-dd"
                                :input-class="'mt-2 border rounded px-3 py-2 w-full'"
                                placeholder="Select PI Date"
                                :auto-apply="true"
                            />
                            <InputError :message="form.errors.pi_date" class="mt-1" />
                        </div>

                        <div v-if="form.shipment_type_id != 1" class="flex flex-col">
                            <Label>LC No</Label>
                            <Input v-model="form.lc_no" type="text" placeholder="Enter LC No" class="mt-2" />
                            <InputError :message="form.errors.lc_no" class="mt-1" />
                        </div>

                        <div v-if="form.shipment_type_id != 1" class="flex flex-col">
                            <Label>LC Date</Label>
                            <Datepicker
                                v-model="form.lc_date"
                                format="yyyy-MM-dd"
                                :input-class="'mt-2 border rounded px-3 py-2 w-full'"
                                placeholder="Select LC Date"
                                :auto-apply="true"
                            />
                            <InputError :message="form.errors.lc_date" class="mt-1" />
                        </div>

                        <div class="flex flex-col">
                            <Label>Order No</Label>
                            <Input v-model="form.order_no" type="text" placeholder="Enter Order No" class="mt-2" />
                            <InputError :message="form.errors.order_no" class="mt-1" />
                        </div>

                        <div class="flex flex-col">
                            <Label>Order Date</Label>
                            <Datepicker
                                v-model="form.order_date"
                                format="yyyy-MM-dd"
                                :input-class="'mt-2 border rounded px-3 py-2 w-full'"
                                placeholder="Select Order Date"
                                :auto-apply="true"
                            />
                            <InputError :message="form.errors.order_date" class="mt-1" />
                        </div>

                        <div class="flex flex-col">
                            <Label>Supplier Name</Label>
                            <div ref="supplierDropdownRef" class="relative mt-2">
                                <!-- Search Input -->
                                <div class="relative">
                                    <input
                                        v-model="supplierSearchQuery"
                                        @focus="showSupplierDropdown = true"
                                        @input="showSupplierDropdown = true; highlightedIndex = -1"
                                        @keydown="handleKeydown"
                                        type="text"
                                        placeholder="Search suppliers..."
                                        class="w-full rounded border px-3 py-2 pr-8 focus:border-blue-500 focus:outline-none"
                                        :class="form.errors.supplier_id ? 'border-red-500' : 'border-gray-300'"
                                    />
                                    <button
                                        type="button"
                                        @click="toggleSupplierDropdown"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Dropdown -->
                                <div
                                    v-if="showSupplierDropdown"
                                    class="absolute z-10 mt-1 w-full rounded border bg-white shadow-lg"
                                >
                                    <div class="max-h-60 overflow-y-auto">
                                        <div
                                            v-if="searchableSuppliers.length === 0"
                                            class="px-3 py-2 text-gray-500"
                                        >
                                            No suppliers found
                                        </div>
                                        <button
                                            v-for="(supplier, index) in searchableSuppliers"
                                            :key="supplier.id"
                                            type="button"
                                            @click="selectSupplier(supplier)"
                                            :class="[
                                                'w-full px-3 py-2 text-left focus:outline-none',
                                                index === highlightedIndex 
                                                    ? 'bg-blue-100 text-blue-900' 
                                                    : 'hover:bg-gray-100'
                                            ]"
                                        >
                                            {{ supplier.name }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Clear button -->
                                <button
                                    v-if="selectedSupplier"
                                    type="button"
                                    @click="clearSupplierSelection"
                                    class="absolute right-8 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <InputError :message="form.errors.supplier_id" class="mt-1" />
                        </div>

                        <div class="flex flex-col">
                            <Label>Breed Type</Label>
                            <Multiselect
                                v-model="form.breed_type"
                                :options="props.breedTypes"
                                :multiple="true"
                                :close-on-select="false"
                                :clear-on-select="false"
                                :preserve-search="true"
                                placeholder="Select breed types"
                                label="name"
                                track-by="id"
                            />

                            <InputError :message="form.errors.breed_type" class="mt-1" />
                        </div>

                        <div v-if="form.shipment_type_id == 2" class="flex flex-col">
                            <Label>Country of Origin</Label>
                            <div ref="countryDropdownRef" class="relative mt-2">
                                <!-- Search Input -->
                                <div class="relative">
                                    <input
                                        v-model="countrySearchQuery"
                                        @focus="showCountryDropdown = true"
                                        @input="showCountryDropdown = true; highlightedCountryIndex = -1"
                                        @keydown="handleCountryKeydown"
                                        type="text"
                                        placeholder="Search countries..."
                                        class="w-full rounded border px-3 py-2 pr-8 focus:border-blue-500 focus:outline-none"
                                        :class="form.errors.country_of_origin ? 'border-red-500' : 'border-gray-300'"
                                    />
                                    <button
                                        type="button"
                                        @click="toggleCountryDropdown"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </button>
                                </div>

                                <!-- Dropdown -->
                                <div
                                    v-if="showCountryDropdown"
                                    class="absolute z-10 mt-1 w-full rounded border bg-white shadow-lg"
                                >
                                    <div class="max-h-60 overflow-y-auto">
                                        <div
                                            v-if="searchableCountries.length === 0"
                                            class="px-3 py-2 text-gray-500"
                                        >
                                            No countries found
                                        </div>
                                        <button
                                            v-for="(country, index) in searchableCountries"
                                            :key="country.id"
                                            type="button"
                                            @click="selectCountry(country)"
                                            :class="[
                                                'w-full px-3 py-2 text-left focus:outline-none',
                                                index === highlightedCountryIndex 
                                                    ? 'bg-blue-100 text-blue-900' 
                                                    : 'hover:bg-gray-100'
                                            ]"
                                        >
                                            {{ country.name }}
                                        </button>
                                    </div>
                                </div>

                                <!-- Clear button -->
                                <button
                                    v-if="selectedCountry"
                                    type="button"
                                    @click="clearCountrySelection"
                                    class="absolute right-8 top-1/2 -translate-y-1/2 text-gray-400 hover:text-red-500"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <InputError :message="form.errors.country_of_origin" class="mt-1" />
                        </div>

                        <div class="flex flex-col">
                            <Label>Transport Type</Label>
                            <select v-model="form.transport_type" class="mt-2 rounded border px-3 py-2">
                                <option value="">Select One</option>
                                <option v-for="transporttype in transportTypes" :key="transporttype.id" :value="transporttype.id">
                                    {{ transporttype.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.transport_type" class="mt-1" />
                        </div>

                        <div class="flex flex-col">
                            <Label>Temperature</Label>
                            <Input v-model="form.vehicle_inside_temp" type="text" placeholder="Enter Inside Temperature" class="mt-2" />
                            <InputError :message="form.errors.vehicle_inside_temp" class="mt-1" />
                        </div>

                        <div class="flex flex-col">
                            <Label>Ship To</Label>
                            <select v-model="form.company_id" class="mt-2 rounded border px-3 py-2">
                                <option value="0">Select One</option>
                                <option v-for="company in props.companies" :key="company.id" :value="company.id">
                                    {{ company.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.company_id" class="mt-1" />
                        </div>

                        <div class="col-span-3 flex flex-col">
                            <Label>Note</Label>
                            <textarea v-model="form.remarks" class="mt-2 rounded border px-3 py-2" placeholder="Enter any Note"></textarea>
                            <InputError :message="form.errors.remarks" class="mt-1" />
                        </div>

                        <!-- File upload -->
                        <div class="col-span-3 flex flex-col">
                            <FileUploader
                                v-model="form.file"
                                label="Upload Files"
                                :max-files="3"
                                accept=".jpg,.jpeg,.png,.pdf"
                                wrapper-class="flex flex-col mt-5"
                            />
                            <InputError :message="form.errors.file" class="mt-1" />
                        </div>
                    </div>
                </div>

                <!-- Receive Quantity -->
                <div v-if="activeTab === 1" class="space-y-4 rounded-lg border bg-white p-4 shadow-sm">
                    <h2 class="text-xl font-semibold">Receive Quantity</h2>
                    <div class="grid grid-cols-3 items-center gap-4">
                        <div class="flex flex-col">
                            <Label>Challan Box Qty</Label>
                            <Input v-model.number="form.ps_challan_box_qty" type="number" class="mt-2" />
                        </div>
                        <div class="flex flex-col">
                            <Label>Gross Weight</Label>
                            <Input v-model.number="form.ps_gross_weight" type="number" step="0.01" class="mt-2" />
                        </div>
                        <div class="flex flex-col">
                            <Label>Net Weight</Label>
                            <Input v-model.number="form.ps_net_weight" type="number" step="0.01" class="mt-2" />
                        </div>
                    </div>

                    <div class="mb-6 grid grid-cols-3 gap-4">
                        <div class="flex flex-col">
                            <Label>Female Chicks Qty</Label>
                            <Input v-model.number="form.ps_female_qty" type="number" class="mt-2" />
                        </div>
                        <div class="flex flex-col">
                            <Label>Male Chicks Qty</Label>
                            <Input v-model.number="form.ps_male_qty" type="number" class="mt-2" />
                        </div>
                        <div class="flex flex-col">
                            <Label>Total Chicks Qty</Label>
                            <Input v-model.number="form.ps_total_qty" type="number" class="mt-2" readonly />
                        </div>
                    </div>

                    <div class="mb-6 grid grid-cols-3 gap-4">
                        <div class="flex flex-col">
                            <Label>Female Box Receive Qty</Label>
                            <Input v-model.number="form.ps_female_rec_box" type="number" class="mt-2" />
                        </div>
                        <div class="flex flex-col">
                            <Label>Male Box Receive Qty</Label>
                            <Input v-model.number="form.ps_male_rec_box" type="number" class="mt-2" />
                        </div>
                        <div class="flex flex-col">
                            <Label>Total Box Qty</Label>
                            <Input v-model.number="form.ps_total_re_box_qty" type="number" class="mt-2" readonly />
                        </div>
                        <div class="flex flex-col">
                            <Label>Bonus Qty %</Label>
                            <Input v-model.number="form.ps_bonus_qty" type="number" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- LAB TAB (Create Page, same as Edit Page) -->
                <div v-if="activeTab === 2" class="space-y-4 rounded-lg border bg-white p-4 shadow-sm">
                    <h2 class="text-xl font-semibold">Lab Transfer</h2>

                    <div class="grid grid-cols-3 items-center gap-4">
                        <h3 class="col-span-3 font-semibold text-gray-700">Gov Lab</h3>
                        <div class="flex flex-col">
                            <Label>Male Qty</Label>
                            <Input v-model.number="form.gov_lab_send_male_qty" type="number" class="mt-2" @input="updateTotalQty" />
                        </div>
                        <div class="flex flex-col">
                            <Label>Female Qty</Label>
                            <Input v-model.number="form.gov_lab_send_female_qty" type="number" class="mt-2" @input="updateTotalQty" />
                        </div>
                        <div class="flex flex-col">
                            <Label>Total Qty</Label>
                            <Input v-model.number="form.gov_lab_send_total_qty" type="number" class="mt-2" readonly />
                        </div>

                        <h3 class="col-span-3 mt-4 font-semibold text-gray-700">Provita Lab</h3>
                        <div class="flex flex-col">
                            <Label>Male Qty</Label>
                            <Input v-model.number="form.provita_lab_send_male_qty" type="number" class="mt-2" @input="updateTotalQty" />
                        </div>
                        <div class="flex flex-col">
                            <Label>Female Qty</Label>
                            <Input v-model.number="form.provita_lab_send_female_qty" type="number" class="mt-2" @input="updateTotalQty" />
                        </div>
                        <div class="flex flex-col">
                            <Label>Total Qty</Label>
                            <Input v-model.number="form.provita_lab_send_total_qty" type="number" class="mt-2" readonly />
                        </div>

                        <div class="col-span-3 flex flex-col">
                            <Label>Remarks</Label>
                            <textarea v-model="form.remarks" class="mt-2 rounded border px-3"></textarea>
                        </div>

                        <div class="col-span-3 flex flex-col">
                            <FileUploader
                                v-model="form.labfile"
                                label="Upload Lab Files"
                                :max-files="3"
                                accept=".jpg,.jpeg,.png,.pdf"
                                wrapper-class="flex flex-col mt-5"
                            />
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="mt-4 flex justify-between">
                    <Button type="button" class="bg-black text-white" @click="goPrevious" :disabled="activeTab === 0"> Previous </Button>

                    <Button type="button" class="bg-black text-white" @click="activeTab === tabs.length - 1 ? submit() : goNext()">
                        {{ activeTab === tabs.length - 1 ? 'Submit' : 'Next' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
