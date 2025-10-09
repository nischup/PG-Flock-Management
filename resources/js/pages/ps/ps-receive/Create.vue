<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

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
    lab_remarks: '', // separate field for lab remarks
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
            params: { shipment_type_id: shipmentTypeId },
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
    return filteredSuppliers.value.filter((supplier) => supplier.name.toLowerCase().includes(supplierSearchQuery.value.toLowerCase()));
});

// Computed property for searchable countries
const searchableCountries = computed(() => {
    if (!countrySearchQuery.value) {
        return props.countries;
    }
    return props.countries.filter((country) => country.name.toLowerCase().includes(countrySearchQuery.value.toLowerCase()));
});

// Methods for supplier search
const selectSupplier = (supplier: { id: number; name: string }) => {
    selectedSupplier.value = supplier;
    form.supplier_id = String(supplier.id);
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
watch(
    () => form.shipment_type_id,
    (newShipmentTypeId) => {
        if (newShipmentTypeId) {
            fetchSuppliersByShipmentType(newShipmentTypeId);
            // Reset supplier selection when shipment type changes
            clearSupplierSelection();
        }
    },
    { immediate: true },
);

function submit() {
    console.log('Form submission started', form.data());
    console.log('Breed type before conversion:', form.breed_type);

    // Update form data with proper type conversion
    form.ps_male_rec_box = Number(form.ps_male_rec_box) || 0;
    form.ps_male_qty = Number(form.ps_male_qty) || 0;
    form.ps_female_rec_box = Number(form.ps_female_rec_box) || 0;
    form.ps_female_qty = Number(form.ps_female_qty) || 0;
    form.ps_total_qty = Number(form.ps_total_qty) || 0;
    form.ps_total_re_box_qty = Number(form.ps_total_re_box_qty) || 0;
    form.ps_challan_box_qty = Number(form.ps_challan_box_qty) || 0;
    form.ps_gross_weight = Number(form.ps_gross_weight) || 0;
    form.ps_net_weight = Number(form.ps_net_weight) || 0;
    form.ps_bonus_qty = Number(form.ps_bonus_qty) || 0;
    form.gov_lab_send_female_qty = Number(form.gov_lab_send_female_qty) || 0;
    form.gov_lab_send_male_qty = Number(form.gov_lab_send_male_qty) || 0;
    form.gov_lab_send_total_qty = Number(form.gov_lab_send_total_qty) || 0;
    form.provita_lab_send_female_qty = Number(form.provita_lab_send_female_qty) || 0;
    form.provita_lab_send_male_qty = Number(form.provita_lab_send_male_qty) || 0;
    form.provita_lab_send_total_qty = Number(form.provita_lab_send_total_qty) || 0;
    form.vehicle_inside_temp = form.vehicle_inside_temp ? String(Number(form.vehicle_inside_temp)) : '';

    console.log('Form data after conversion', form.data());
    console.log('Breed type after conversion:', form.breed_type);

    // Use Inertia to post
    form.post(route('ps-receive.store'), {
        onSuccess: (page) => {
            console.log('Form submitted successfully', page);
            form.reset();
            // Optional: show a success notification
        },
        onError: (errors) => {
            console.error('Form submission failed', errors);
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
        // Required fields validation
        if (!form.pi_no?.trim()) tabErrors.value.pi_no = 'PI Number is required';
        if (!form.pi_date) tabErrors.value.pi_date = 'PI Date is required';
        if (!form.order_no?.trim()) tabErrors.value.order_no = 'Order Number is required';
        if (!form.order_date) tabErrors.value.order_date = 'Order Date is required';
        if (!form.supplier_id) tabErrors.value.supplier_id = 'Supplier selection is required';
        if (!form.breed_type?.length) tabErrors.value.breed_type = 'At least one breed type must be selected';
        if (!form.country_of_origin) tabErrors.value.country_of_origin = 'Country of origin is required';
        if (!form.transport_type) tabErrors.value.transport_type = 'Transport type is required';
        if (!form.company_id) tabErrors.value.company_id = 'Company selection is required';

        // String length validation
        if (form.pi_no && form.pi_no.length > 50) tabErrors.value.pi_no = 'PI Number cannot exceed 50 characters';
        if (form.order_no && form.order_no.length > 50) tabErrors.value.order_no = 'Order Number cannot exceed 50 characters';
        if (form.lc_no && form.lc_no.length > 50) tabErrors.value.lc_no = 'LC Number cannot exceed 50 characters';
        if (form.remarks && form.remarks.length > 500) tabErrors.value.remarks = 'Remarks cannot exceed 500 characters';

        // Date logic validation
        if (form.pi_date && form.order_date && new Date(form.order_date) < new Date(form.pi_date)) {
            tabErrors.value.order_date = 'Order date must be after or equal to PI date';
        }
        if (form.pi_date && form.lc_date && new Date(form.lc_date) < new Date(form.pi_date)) {
            tabErrors.value.lc_date = 'LC date must be after or equal to PI date';
        }

        // Future date validation
        if (form.pi_date && new Date(form.pi_date) > new Date()) {
            tabErrors.value.pi_date = 'PI date cannot be in the future';
        }

        // Temperature validation
        if (form.vehicle_inside_temp && (Number(form.vehicle_inside_temp) < -50 || Number(form.vehicle_inside_temp) > 50)) {
            tabErrors.value.vehicle_inside_temp = 'Vehicle temperature must be between -50°C and 50°C';
        }

        return Object.keys(tabErrors.value).length === 0;
    }

    if (tabs[index].key === 'receive') {
        // Quantity validations
        if (form.ps_total_qty <= 0) tabErrors.value.ps_total_qty = 'Total quantity must be greater than 0';
        if (form.ps_male_qty < 0) tabErrors.value.ps_male_qty = 'Male quantity cannot be negative';
        if (form.ps_female_qty < 0) tabErrors.value.ps_female_qty = 'Female quantity cannot be negative';
        if (form.ps_male_rec_box < 0) tabErrors.value.ps_male_rec_box = 'Male box quantity cannot be negative';
        if (form.ps_female_rec_box < 0) tabErrors.value.ps_female_rec_box = 'Female box quantity cannot be negative';
        if (form.ps_total_re_box_qty < 0) tabErrors.value.ps_total_re_box_qty = 'Total box quantity cannot be negative';
        if (form.ps_challan_box_qty < 0) tabErrors.value.ps_challan_box_qty = 'Challan box quantity cannot be negative';

        // Weight validations
        if (form.ps_gross_weight < 0) tabErrors.value.ps_gross_weight = 'Gross weight cannot be negative';
        if (form.ps_net_weight < 0) tabErrors.value.ps_net_weight = 'Net weight cannot be negative';
        if (form.ps_net_weight > form.ps_gross_weight) {
            tabErrors.value.ps_net_weight = 'Net weight cannot exceed gross weight';
        }

        // Quantity consistency validation
        const calculatedTotal = form.ps_male_qty + form.ps_female_qty;
        if (Math.abs(form.ps_total_qty - calculatedTotal) > 0.01) {
            // Allow for small floating point differences
            tabErrors.value.ps_total_qty = 'Total quantity must equal male + female quantity';
        }

        return Object.keys(tabErrors.value).length === 0;
    }

    if (tabs[index].key === 'lab') {
        const govTotal = form.gov_lab_send_female_qty + form.gov_lab_send_male_qty;
        const provitaTotal = form.provita_lab_send_female_qty + form.provita_lab_send_male_qty;

        // Lab quantity validations
        if (form.gov_lab_send_female_qty < 0) tabErrors.value.gov_lab_send_female_qty = 'Government lab female quantity cannot be negative';
        if (form.gov_lab_send_male_qty < 0) tabErrors.value.gov_lab_send_male_qty = 'Government lab male quantity cannot be negative';
        if (form.provita_lab_send_female_qty < 0) tabErrors.value.provita_lab_send_female_qty = 'Provita lab female quantity cannot be negative';
        if (form.provita_lab_send_male_qty < 0) tabErrors.value.provita_lab_send_male_qty = 'Provita lab male quantity cannot be negative';

        // At least one lab quantity required
        if (govTotal <= 0 && provitaTotal <= 0) {
            tabErrors.value.lab_send_total_qty = 'At least one lab quantity is required';
            return false;
        }

        return Object.keys(tabErrors.value).length === 0;
    }

    return true;
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Parent Stock Receive" />

        <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 px-4 py-8">
            <div class="mx-auto max-w-7xl">
                <!-- Header Section -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900">Parent Stock Receive</h1>
                            <p class="mt-2 text-gray-600">Create a new parent stock receive entry</p>
                        </div>
                        <Link
                            href="/ps-receive"
                            class="group flex items-center gap-2 rounded-lg bg-gradient-to-r from-gray-800 to-gray-900 px-4 py-3 text-sm font-medium text-white shadow-md transition-all hover:from-gray-700 hover:to-gray-800 hover:shadow-lg"
                        >
                            <ArrowLeft class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                            Back to List
                        </Link>
                    </div>
                </div>

                <form @submit.prevent="submit" class="space-y-8" enctype="multipart/form-data">
                    <!-- Modern Tabs Navigation -->
                    <div class="rounded-xl bg-white p-2 shadow-sm ring-1 ring-gray-200">
                        <div class="flex gap-2">
                            <div
                                v-for="(tab, index) in tabs"
                                :key="tab.key"
                                @click="activeTab = index"
                                class="relative flex-1 cursor-pointer rounded-lg px-6 py-4 text-center font-medium transition-all duration-200"
                                :class="[
                                    activeTab === index
                                        ? 'glossy-purple-tab text-white shadow-2xl ring-2 ring-purple-200/60 backdrop-blur-sm'
                                        : completedTabs.includes(index)
                                          ? 'bg-gradient-to-r from-green-500 to-green-600 text-white shadow-md'
                                          : 'bg-gradient-to-r from-gray-800 to-gray-900 text-white shadow-md hover:from-gray-700 hover:to-gray-800',
                                ]"
                            >
                                <div class="flex items-center justify-center gap-2">
                                    <div
                                        class="flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold"
                                        :class="activeTab === index ? 'bg-white/30' : completedTabs.includes(index) ? 'bg-white/20' : 'bg-white/20'"
                                    >
                                        {{ index + 1 }}
                                    </div>
                                    {{ tab.label }}
                                </div>
                                <!-- Progress indicator -->
                                <div
                                    v-if="activeTab === index"
                                    class="absolute bottom-0 left-1/2 h-1 w-8 -translate-x-1/2 rounded-full bg-white/60"
                                ></div>
                            </div>
                        </div>
                    </div>

                    <!-- Master Info Tab -->
                    <div v-if="activeTab === 0" class="overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-gray-200">
                        <!-- Tab Header -->
                        <div class="glossy-purple-tab px-8 py-6 shadow-2xl ring-2 ring-purple-200/60 backdrop-blur-sm">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20">
                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                        ></path>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold text-white">Shipment Information</h2>
                                    <p class="text-purple-100">Enter the basic shipment details and supplier information</p>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Content -->
                        <div class="p-8">
                            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2 xl:grid-cols-3">
                                <!-- Shipment Type -->
                                <div class="space-y-2">
                                    <Label class="text-sm font-semibold text-gray-700">Shipment Type</Label>
                                    <select
                                        v-model="form.shipment_type_id"
                                        class="w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-blue-500"
                                    >
                                        <option v-for="shipmenttype in shipmentTypes" :key="shipmenttype.id" :value="shipmenttype.id">
                                            {{ shipmenttype.name }}
                                        </option>
                                    </select>
                                    <InputError :message="form.errors.shipment_type_id" class="text-sm text-red-600" />
                                </div>

                                <!-- PI No -->
                                <div class="space-y-2">
                                    <Label class="text-sm font-semibold text-gray-700">PI No</Label>
                                    <Input
                                        v-model="form.pi_no"
                                        type="text"
                                        placeholder="Enter PI No"
                                        class="w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-blue-500"
                                        :class="tabErrors.pi_no ? 'ring-red-500' : form.errors.pi_no ? 'ring-red-500' : ''"
                                    />
                                    <InputError :message="tabErrors.pi_no || form.errors.pi_no" class="text-sm text-red-600" />
                                </div>

                                <!-- PI Date -->
                                <div class="space-y-2">
                                    <Label class="text-sm font-semibold text-gray-700">PI Date</Label>
                                    <Datepicker
                                        v-model="form.pi_date"
                                        format="yyyy-MM-dd"
                                        :input-class="'w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-blue-500'"
                                        placeholder="Select PI Date"
                                        :auto-apply="true"
                                    />
                                    <InputError :message="tabErrors.pi_date || form.errors.pi_date" class="text-sm text-red-600" />
                                </div>

                                <!-- LC No (Foreign only) -->
                                <div v-if="form.shipment_type_id != 1" class="space-y-2">
                                    <Label class="text-sm font-semibold text-gray-700">LC No</Label>
                                    <Input
                                        v-model="form.lc_no"
                                        type="text"
                                        placeholder="Enter LC No"
                                        class="w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-blue-500"
                                    />
                                    <InputError :message="form.errors.lc_no" class="text-sm text-red-600" />
                                </div>

                                <!-- LC Date (Foreign only) -->
                                <div v-if="form.shipment_type_id != 1" class="space-y-2">
                                    <Label class="text-sm font-semibold text-gray-700">LC Date</Label>
                                    <Datepicker
                                        v-model="form.lc_date"
                                        format="yyyy-MM-dd"
                                        :input-class="'w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-blue-500'"
                                        placeholder="Select LC Date"
                                        :auto-apply="true"
                                    />
                                    <InputError :message="form.errors.lc_date" class="text-sm text-red-600" />
                                </div>

                                <!-- Order No -->
                                <div class="space-y-2">
                                    <Label class="text-sm font-semibold text-gray-700">Order No</Label>
                                    <Input
                                        v-model="form.order_no"
                                        type="text"
                                        placeholder="Enter Order No"
                                        class="w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-blue-500"
                                    />
                                    <InputError :message="tabErrors.order_no || form.errors.order_no" class="text-sm text-red-600" />
                                </div>

                                <!-- Order Date -->
                                <div class="space-y-2">
                                    <Label class="text-sm font-semibold text-gray-700">Order Date</Label>
                                    <Datepicker
                                        v-model="form.order_date"
                                        format="yyyy-MM-dd"
                                        :input-class="'w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-blue-500'"
                                        placeholder="Select Order Date"
                                        :auto-apply="true"
                                    />
                                    <InputError :message="tabErrors.order_date || form.errors.order_date" class="text-sm text-red-600" />
                                </div>

                                <!-- Supplier Name -->
                                <div class="space-y-2">
                                    <Label class="text-sm font-semibold text-gray-700">Supplier Name</Label>
                                    <div ref="supplierDropdownRef" class="relative">
                                        <!-- Search Input -->
                                        <div class="relative">
                                            <input
                                                v-model="supplierSearchQuery"
                                                @focus="showSupplierDropdown = true"
                                                @input="
                                                    showSupplierDropdown = true;
                                                    highlightedIndex = -1;
                                                "
                                                @keydown="handleKeydown"
                                                type="text"
                                                placeholder="Search suppliers..."
                                                class="w-full rounded-lg border-0 bg-gray-50 px-4 py-3 pr-12 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-blue-500"
                                                :class="form.errors.supplier_id ? 'ring-red-500' : ''"
                                            />
                                            <button
                                                type="button"
                                                @click="toggleSupplierDropdown"
                                                class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 transition-colors hover:text-gray-600"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Dropdown -->
                                        <div
                                            v-if="showSupplierDropdown"
                                            class="absolute z-20 mt-2 w-full overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-gray-200"
                                        >
                                            <div class="max-h-60 overflow-y-auto">
                                                <div v-if="searchableSuppliers.length === 0" class="px-4 py-3 text-sm text-gray-500">
                                                    No suppliers found
                                                </div>
                                                <button
                                                    v-for="(supplier, index) in searchableSuppliers"
                                                    :key="supplier.id"
                                                    type="button"
                                                    @click="selectSupplier(supplier)"
                                                    :class="[
                                                        'w-full px-4 py-3 text-left text-sm transition-colors focus:outline-none',
                                                        index === highlightedIndex ? 'bg-blue-50 text-blue-900' : 'hover:bg-gray-50',
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
                                            class="absolute top-1/2 right-10 -translate-y-1/2 text-gray-400 transition-colors hover:text-red-500"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <InputError :message="tabErrors.supplier_id || form.errors.supplier_id" class="text-sm text-red-600" />
                                </div>

                                <!-- Breed Type -->
                                <div class="space-y-2">
                                    <Label class="text-sm font-semibold text-gray-700">Breed Type</Label>
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
                                        class="multiselect-modern"
                                    />
                                    <InputError :message="tabErrors.breed_type || form.errors.breed_type" class="text-sm text-red-600" />
                                </div>

                                <!-- Country of Origin (Foreign only) -->
                                <div v-if="form.shipment_type_id == 2" class="space-y-2">
                                    <Label class="text-sm font-semibold text-gray-700">Country of Origin</Label>
                                    <div ref="countryDropdownRef" class="relative">
                                        <!-- Search Input -->
                                        <div class="relative">
                                            <input
                                                v-model="countrySearchQuery"
                                                @focus="showCountryDropdown = true"
                                                @input="
                                                    showCountryDropdown = true;
                                                    highlightedCountryIndex = -1;
                                                "
                                                @keydown="handleCountryKeydown"
                                                type="text"
                                                placeholder="Search countries..."
                                                class="w-full rounded-lg border-0 bg-gray-50 px-4 py-3 pr-12 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-blue-500"
                                                :class="form.errors.country_of_origin ? 'ring-red-500' : ''"
                                            />
                                            <button
                                                type="button"
                                                @click="toggleCountryDropdown"
                                                class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 transition-colors hover:text-gray-600"
                                            >
                                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Dropdown -->
                                        <div
                                            v-if="showCountryDropdown"
                                            class="absolute z-20 mt-2 w-full overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-gray-200"
                                        >
                                            <div class="max-h-60 overflow-y-auto">
                                                <div v-if="searchableCountries.length === 0" class="px-4 py-3 text-sm text-gray-500">
                                                    No countries found
                                                </div>
                                                <button
                                                    v-for="(country, index) in searchableCountries"
                                                    :key="country.id"
                                                    type="button"
                                                    @click="selectCountry(country)"
                                                    :class="[
                                                        'w-full px-4 py-3 text-left text-sm transition-colors focus:outline-none',
                                                        index === highlightedCountryIndex ? 'bg-blue-50 text-blue-900' : 'hover:bg-gray-50',
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
                                            class="absolute top-1/2 right-10 -translate-y-1/2 text-gray-400 transition-colors hover:text-red-500"
                                        >
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <InputError
                                        :message="tabErrors.country_of_origin || form.errors.country_of_origin"
                                        class="text-sm text-red-600"
                                    />
                                </div>

                                <!-- Transport Type -->
                                <div class="space-y-2">
                                    <Label class="text-sm font-semibold text-gray-700">Transport Type</Label>
                                    <select
                                        v-model="form.transport_type"
                                        class="w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-blue-500"
                                    >
                                        <option value="">Select One</option>
                                        <option v-for="transporttype in transportTypes" :key="transporttype.id" :value="transporttype.id">
                                            {{ transporttype.name }}
                                        </option>
                                    </select>
                                    <InputError :message="tabErrors.transport_type || form.errors.transport_type" class="text-sm text-red-600" />
                                </div>

                                <!-- Temperature -->
                                <div class="space-y-2">
                                    <Label class="text-sm font-semibold text-gray-700">Temperature</Label>
                                    <Input
                                        v-model="form.vehicle_inside_temp"
                                        type="text"
                                        placeholder="Enter Inside Temperature"
                                        class="w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-blue-500"
                                    />
                                    <InputError
                                        :message="tabErrors.vehicle_inside_temp || form.errors.vehicle_inside_temp"
                                        class="text-sm text-red-600"
                                    />
                                </div>

                                <!-- Ship To -->
                                <div class="space-y-2">
                                    <Label class="text-sm font-semibold text-gray-700">Ship To</Label>
                                    <select
                                        v-model="form.company_id"
                                        class="w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-blue-500"
                                    >
                                        <option value="0">Select One</option>
                                        <option v-for="company in props.companies" :key="company.id" :value="company.id">
                                            {{ company.name }}
                                        </option>
                                    </select>
                                    <InputError :message="tabErrors.company_id || form.errors.company_id" class="text-sm text-red-600" />
                                </div>

                                <!-- Note -->
                                <div class="col-span-full space-y-2">
                                    <Label class="text-sm font-semibold text-gray-700">Note</Label>
                                    <textarea
                                        v-model="form.remarks"
                                        class="w-full rounded-lg border-0 bg-gray-50 px-4 py-3 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-blue-500"
                                        placeholder="Enter any Note"
                                        rows="3"
                                    ></textarea>
                                    <InputError :message="tabErrors.remarks || form.errors.remarks" class="text-sm text-red-600" />
                                </div>

                                <!-- File upload -->
                                <div class="col-span-full space-y-2">
                                    <FileUploader
                                        v-model="form.file"
                                        label="Upload Files"
                                        :max-files="3"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        wrapper-class="flex flex-col"
                                    />
                                    <InputError :message="form.errors.file" class="text-sm text-red-600" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Receive Quantity Tab -->
                    <div v-if="activeTab === 1" class="overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-gray-200">
                        <!-- Tab Header -->
                        <div class="bg-gradient-to-r from-green-500 to-green-600 px-8 py-6">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white/20">
                                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"
                                        ></path>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold text-white">Receive Quantity</h2>
                                    <p class="text-green-100">Enter the quantity details for the received stock</p>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Content -->
                        <div class="p-8">
                            <!-- Weight and Box Information -->
                            <div class="mb-8">
                                <!-- <h3 class="mb-4 text-lg font-semibold text-gray-900">Weight & Box Information</h3> -->
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                    <div class="space-y-2">
                                        <Label class="text-sm font-semibold text-gray-700">Challan Box Qty</Label>
                                        <Input
                                            v-model.number="form.ps_challan_box_qty"
                                            type="number"
                                            class="w-full rounded-lg border-0 bg-gray-50 px-3 py-2 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-green-500"
                                        />
                                        <InputError
                                            :message="tabErrors.ps_challan_box_qty || form.errors.ps_challan_box_qty"
                                            class="text-sm text-red-600"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-sm font-semibold text-gray-700">Gross Weight</Label>
                                        <Input
                                            v-model.number="form.ps_gross_weight"
                                            type="number"
                                            step="0.01"
                                            class="w-full rounded-lg border-0 bg-gray-50 px-3 py-2 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-green-500"
                                        />
                                        <InputError
                                            :message="tabErrors.ps_gross_weight || form.errors.ps_gross_weight"
                                            class="text-sm text-red-600"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-sm font-semibold text-gray-700">Net Weight</Label>
                                        <Input
                                            v-model.number="form.ps_net_weight"
                                            type="number"
                                            step="0.01"
                                            class="w-full rounded-lg border-0 bg-gray-50 px-3 py-2 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-green-500"
                                        />
                                        <InputError :message="tabErrors.ps_net_weight || form.errors.ps_net_weight" class="text-sm text-red-600" />
                                    </div>
                                </div>
                            </div>

                            <!-- Box Quantities -->
                            <div class="mb-8">
                                <!-- <h3 class="mb-4 text-lg font-semibold text-gray-900">Box Quantities</h3> -->
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                    <div class="space-y-2">
                                        <Label class="text-sm font-semibold text-gray-700">Female Box Receive Qty</Label>
                                        <Input
                                            v-model.number="form.ps_female_rec_box"
                                            type="number"
                                            class="w-full rounded-lg border-0 bg-gray-50 px-3 py-2 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-green-500"
                                        />
                                        <InputError
                                            :message="tabErrors.ps_female_rec_box || form.errors.ps_female_rec_box"
                                            class="text-sm text-red-600"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-sm font-semibold text-gray-700">Male Box Receive Qty</Label>
                                        <Input
                                            v-model.number="form.ps_male_rec_box"
                                            type="number"
                                            class="w-full rounded-lg border-0 bg-gray-50 px-3 py-2 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-green-500"
                                        />
                                        <InputError
                                            :message="tabErrors.ps_male_rec_box || form.errors.ps_male_rec_box"
                                            class="text-sm text-red-600"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-sm font-semibold text-gray-700">Total Box Qty</Label>
                                        <Input
                                            v-model.number="form.ps_total_re_box_qty"
                                            type="number"
                                            class="w-full rounded-lg border-0 bg-gray-100 px-3 py-2 text-gray-900 ring-1 ring-gray-200"
                                            readonly
                                        />
                                        <InputError
                                            :message="tabErrors.ps_total_re_box_qty || form.errors.ps_total_re_box_qty"
                                            class="text-sm text-red-600"
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Chick Quantities -->
                            <div class="mb-8">
                                <!-- <h3 class="mb-4 text-lg font-semibold text-gray-900">Chick Quantities</h3> -->
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                                    <div class="space-y-2">
                                        <Label class="text-sm font-semibold text-gray-700">Female Chicks Qty</Label>
                                        <Input
                                            v-model.number="form.ps_female_qty"
                                            type="number"
                                            class="w-full rounded-lg border-0 bg-gray-50 px-3 py-2 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-green-500"
                                        />
                                        <InputError :message="tabErrors.ps_female_qty || form.errors.ps_female_qty" class="text-sm text-red-600" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-sm font-semibold text-gray-700">Male Chicks Qty</Label>
                                        <Input
                                            v-model.number="form.ps_male_qty"
                                            type="number"
                                            class="w-full rounded-lg border-0 bg-gray-50 px-3 py-2 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-green-500"
                                        />
                                        <InputError :message="tabErrors.ps_male_qty || form.errors.ps_male_qty" class="text-sm text-red-600" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-sm font-semibold text-gray-700">Total Chicks Qty</Label>
                                        <Input
                                            v-model.number="form.ps_total_qty"
                                            type="number"
                                            class="w-full rounded-lg border-0 bg-gray-100 px-3 py-2 text-gray-900 ring-1 ring-gray-200"
                                            readonly
                                        />
                                        <InputError :message="tabErrors.ps_total_qty || form.errors.ps_total_qty" class="text-sm text-red-600" />
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-sm font-semibold text-gray-700">Bonus Qty %</Label>
                                        <Input
                                            v-model.number="form.ps_bonus_qty"
                                            type="number"
                                            class="w-full rounded-lg border-0 bg-gray-50 px-3 py-2 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-green-500"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lab Transfer Tab -->
                    <div v-if="activeTab === 2" class="overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-gray-200">
                        <!-- Tab Header -->
                        <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-4">
                            <div class="flex items-center gap-2">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-white/20">
                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"
                                        ></path>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-white">Lab Transfer</h2>
                                    <p class="text-sm text-gray-300">Configure laboratory testing and file uploads</p>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Content -->
                        <div class="p-6">
                            <!-- Government Lab Section -->
                            <div class="mb-6">
                                <div class="mb-4 flex items-center gap-2">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100">
                                        <svg class="h-3 w-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                            ></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900">Customs</h3>
                                </div>
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                    <div class="space-y-2">
                                        <Label class="text-sm font-semibold text-gray-700">Male Qty</Label>
                                        <Input
                                            v-model.number="form.gov_lab_send_male_qty"
                                            type="number"
                                            class="w-full rounded-lg border-0 bg-gray-50 px-3 py-2 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-blue-500"
                                            @input="updateTotalQty"
                                        />
                                        <InputError
                                            :message="tabErrors.gov_lab_send_male_qty || form.errors.gov_lab_send_male_qty"
                                            class="text-sm text-red-600"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-sm font-semibold text-gray-700">Female Qty</Label>
                                        <Input
                                            v-model.number="form.gov_lab_send_female_qty"
                                            type="number"
                                            class="w-full rounded-lg border-0 bg-gray-50 px-3 py-2 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-blue-500"
                                            @input="updateTotalQty"
                                        />
                                        <InputError
                                            :message="tabErrors.gov_lab_send_female_qty || form.errors.gov_lab_send_female_qty"
                                            class="text-sm text-red-600"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-sm font-semibold text-gray-700">Total Qty</Label>
                                        <Input
                                            v-model.number="form.gov_lab_send_total_qty"
                                            type="number"
                                            class="w-full rounded-lg border-0 bg-gray-100 px-3 py-2 text-gray-900 ring-1 ring-gray-200"
                                            readonly
                                        />
                                    </div>
                                </div>
                            </div>

                            <!-- Provita Lab Section -->
                            <div class="mb-6">
                                <div class="mb-4 flex items-center gap-2">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-green-100">
                                        <svg class="h-3 w-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                            ></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900">Bio Research Lab</h3>
                                </div>
                                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                                    <div class="space-y-2">
                                        <Label class="text-sm font-semibold text-gray-700">Male Qty</Label>
                                        <Input
                                            v-model.number="form.provita_lab_send_male_qty"
                                            type="number"
                                            class="w-full rounded-lg border-0 bg-gray-50 px-3 py-2 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-green-500"
                                            @input="updateTotalQty"
                                        />
                                        <InputError
                                            :message="tabErrors.provita_lab_send_male_qty || form.errors.provita_lab_send_male_qty"
                                            class="text-sm text-red-600"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-sm font-semibold text-gray-700">Female Qty</Label>
                                        <Input
                                            v-model.number="form.provita_lab_send_female_qty"
                                            type="number"
                                            class="w-full rounded-lg border-0 bg-gray-50 px-3 py-2 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-green-500"
                                            @input="updateTotalQty"
                                        />
                                        <InputError
                                            :message="tabErrors.provita_lab_send_female_qty || form.errors.provita_lab_send_female_qty"
                                            class="text-sm text-red-600"
                                        />
                                    </div>
                                    <div class="space-y-2">
                                        <Label class="text-sm font-semibold text-gray-700">Total Qty</Label>
                                        <Input
                                            v-model.number="form.provita_lab_send_total_qty"
                                            type="number"
                                            class="w-full rounded-lg border-0 bg-gray-100 px-3 py-2 text-gray-900 ring-1 ring-gray-200"
                                            readonly
                                        />
                                    </div>
                                </div>
                                <!-- Lab validation error -->
                                <div v-if="tabErrors.lab_send_total_qty" class="mt-4">
                                    <InputError :message="tabErrors.lab_send_total_qty" class="text-sm text-red-600" />
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="space-y-4">
                                <div class="space-y-2">
                                    <Label class="text-sm font-semibold text-gray-700">Lab Remarks</Label>
                                    <textarea
                                        v-model="form.lab_remarks"
                                        class="w-full rounded-lg border-0 bg-gray-50 px-3 py-2 text-gray-900 ring-1 ring-gray-200 transition-all focus:bg-white focus:ring-2 focus:ring-purple-500"
                                        placeholder="Enter any additional notes for lab testing"
                                        rows="2"
                                    ></textarea>
                                </div>

                                <div class="space-y-2">
                                    <FileUploader
                                        v-model="form.labfile"
                                        label="Upload Lab Files"
                                        :max-files="3"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        wrapper-class="flex flex-col"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Navigation Buttons -->
                    <div class="mt-8 flex items-center justify-between rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                        <Button
                            type="button"
                            class="flex items-center gap-2 rounded-lg bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-3 text-sm font-medium text-white shadow-md transition-all hover:from-gray-700 hover:to-gray-800 hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50"
                            @click="goPrevious"
                            :disabled="activeTab === 0"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Previous
                        </Button>

                        <div class="flex items-center gap-4">
                            <div class="text-sm text-gray-500">Step {{ activeTab + 1 }} of {{ tabs.length }}</div>
                            <div class="flex gap-2">
                                <div
                                    v-for="(tab, index) in tabs"
                                    :key="index"
                                    class="h-2 w-8 rounded-full transition-all"
                                    :class="index <= activeTab ? 'bg-blue-600' : 'bg-gray-200'"
                                ></div>
                            </div>
                        </div>

                        <Button
                            type="button"
                            class="flex items-center gap-2 rounded-lg bg-gradient-to-r from-gray-800 to-gray-900 px-8 py-3 text-sm font-medium text-white shadow-md transition-all hover:from-gray-700 hover:to-gray-800 hover:shadow-lg"
                            @click="activeTab === tabs.length - 1 ? submit() : goNext()"
                        >
                            {{ activeTab === tabs.length - 1 ? 'Submit Entry' : 'Next Step' }}
                            <svg v-if="activeTab !== tabs.length - 1" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            <svg v-else class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Modern Multiselect Styling */
.multiselect-modern :deep(.multiselect__tags) {
    border-radius: 0.5rem;
    border: 0;
    background-color: #f9fafb;
    padding: 0.75rem 1rem;
    box-shadow: 0 0 0 1px #e5e7eb;
    transition: all 0.2s;
    min-height: 48px;
}

.multiselect-modern :deep(.multiselect__tags:focus-within) {
    background-color: white;
    box-shadow: 0 0 0 2px #3b82f6;
}

.multiselect-modern :deep(.multiselect__input) {
    color: #111827;
    padding: 0;
    margin: 0;
    background: transparent;
    border: none;
    outline: none;
}

.multiselect-modern :deep(.multiselect__input::placeholder) {
    color: #6b7280;
}

.multiselect-modern :deep(.multiselect__placeholder) {
    color: #6b7280;
    padding: 0;
    margin: 0;
}

.multiselect-modern :deep(.multiselect__single) {
    color: #111827;
    padding: 0;
    margin: 0;
    background: transparent;
    border: none;
}

.multiselect-modern :deep(.multiselect__content-wrapper) {
    border-radius: 0.75rem;
    border: 0;
    background-color: white;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    box-shadow: 0 0 0 1px #e5e7eb;
    margin-top: 8px;
}

.multiselect-modern :deep(.multiselect__content) {
    border-radius: 0.75rem;
}

.multiselect-modern :deep(.multiselect__option) {
    padding: 0.75rem 1rem;
    font-size: 0.875rem;
    transition: all 0.2s;
}

.multiselect-modern :deep(.multiselect__option--highlight) {
    background-color: #eff6ff;
    color: #1e40af;
}

.multiselect-modern :deep(.multiselect__option--selected) {
    background-color: #dbeafe;
    color: #1e40af;
}

.multiselect-modern :deep(.multiselect__tag) {
    border-radius: 0.375rem;
    background-color: #dbeafe;
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    color: #1e40af;
    margin: 2px;
}

.multiselect-modern :deep(.multiselect__tag-icon) {
    color: #2563eb;
}

.multiselect-modern :deep(.multiselect__tag-icon:hover) {
    background-color: #bfdbfe;
}

.multiselect-modern :deep(.multiselect__tag-icon:after) {
    color: #2563eb;
}

.multiselect-modern :deep(.multiselect__spinner) {
    border-color: #2563eb;
}

.multiselect-modern :deep(.multiselect__spinner:after) {
    border-color: #2563eb;
}

/* Glossy Purple Tab Styling */
.glossy-purple-tab {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 50%, #6d28d9 100%);
    box-shadow:
        0 4px 15px rgba(139, 92, 246, 0.4),
        inset 0 1px 0 rgba(255, 255, 255, 0.2),
        inset 0 -1px 0 rgba(0, 0, 0, 0.1);
    position: relative;
    overflow: hidden;
}

.glossy-purple-tab::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.glossy-purple-tab:hover::before {
    left: 100%;
}
</style>
