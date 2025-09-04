<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

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

const props = defineProps<{
    psReceive: any;
    suppliers: Array<{ id: number; name: string }>;
    breedTypes: Array<{ id: number; name: string }>;
    companies: Array<{ id: number; name: string }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Parent Stock', href: '/ps-receive' },
    { title: 'Edit Receive', href: `/ps-receive/${props.psReceive.id}/edit` },
];

// Map saved breed_type IDs to objects from props.breedTypes
const selectedBreeds = props.breedTypes.filter((breed) => (props.psReceive.breed_type ?? []).includes(breed.id));
// Form prefilled with existing data
const form = useForm({
    shipment_type_id: props.psReceive.shipment_type_id,
    pi_no: props.psReceive.pi_no,
    pi_date: props.psReceive.pi_date,
    order_no: props.psReceive.order_no,
    order_date: props.psReceive.order_date,
    lc_no: props.psReceive.lc_no,
    lc_date: props.psReceive.lc_date,
    supplier_id: props.psReceive.supplier_id,
    breed_type: selectedBreeds,
    country_of_origin: props.psReceive.country_of_origin,
    transport_type: props.psReceive.transport_type,
    vehicle_inside_temp: props.psReceive.transport_inside_temp,
    remarks: props.psReceive.remarks,
    company_id: props.psReceive.company_id,
    ps_bonus_qty: props.psReceive.ps_bonus_qty ?? 0,

    // Chick Counts
    ps_male_rec_box: props.psReceive.chick_counts?.ps_male_rec_box ?? 0,
    ps_male_qty: props.psReceive.chick_counts?.ps_male_qty ?? 0,
    ps_female_rec_box: props.psReceive.chick_counts?.ps_female_rec_box ?? 0,
    ps_female_qty: props.psReceive.chick_counts?.ps_female_qty ?? 0,
    ps_total_qty: props.psReceive.chick_counts?.ps_total_qty ?? 0,
    ps_total_re_box_qty: props.psReceive.chick_counts?.ps_total_re_box_qty ?? 0,
    ps_challan_box_qty: props.psReceive.chick_counts?.ps_challan_box_qty ?? 0,
    ps_gross_weight: props.psReceive.chick_counts?.ps_gross_weight ?? 0,
    ps_net_weight: props.psReceive.chick_counts?.ps_net_weight ?? 0,

    gov_lab_send_female_qty: props.psReceive.labTransfers?.find((l) => Number(l.lab_type) === 1)?.lab_send_female_qty ?? 0,
    gov_lab_send_male_qty: props.psReceive.labTransfers?.find((l) => Number(l.lab_type) === 1)?.lab_send_male_qty ?? 0,
    gov_lab_send_total_qty: props.psReceive.labTransfers?.find((l) => Number(l.lab_type) === 1)?.lab_send_total_qty ?? 0,

    provita_lab_send_female_qty: props.psReceive.labTransfers?.find((l) => Number(l.lab_type) === 2)?.lab_send_female_qty ?? 0,
    provita_lab_send_male_qty: props.psReceive.labTransfers?.find((l) => Number(l.lab_type) === 2)?.lab_send_male_qty ?? 0,
    provita_lab_send_total_qty: props.psReceive.labTransfers?.find((l) => Number(l.lab_type) === 2)?.lab_send_total_qty ?? 0,
    // Files
    file: [],
    labfile: [],
});

watch(
    () => [form.ps_male_qty, form.ps_female_qty, form.ps_male_rec_box, form.ps_female_rec_box],
    () => {
        form.ps_total_qty = Number(form.ps_male_qty || 0) + Number(form.ps_female_qty || 0);
        form.ps_total_re_box_qty = Number(form.ps_male_rec_box || 0) + Number(form.ps_female_rec_box || 0);
    },
    { deep: true, immediate: true },
);

function updateTotalQty() {
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
    if (!completedTabs.value.includes(activeTab.value)) completedTabs.value.push(activeTab.value);
    if (activeTab.value < tabs.length - 1) activeTab.value++;
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

function submit() {
    const formData = new FormData();
    for (const key in form) {
        const value = form[key as keyof typeof form];
        if (key === 'file' || key === 'labfile') {
            (value as File[]).forEach((f) => formData.append(key + '[]', f));
        } else {
            formData.append(key, value != null ? String(value) : '');
        }
    }
    form.post(route('ps-receive.update', props.psReceive.id), {
        data: formData,
        headers: { 'Content-Type': 'multipart/form-data' },
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Edit Parent Stock Receive" />

        <div class="space-y-8 px-4 py-6">
            <form @submit.prevent="submit" class="space-y-8" enctype="multipart/form-data">
                <!-- Tabs Navigation -->
                <div class="mb-6 flex items-center justify-between">
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

                    <Link href="/ps-receive" class="flex items-center gap-1 rounded-md bg-gray-100 px-3 py-2 text-sm text-gray-700 hover:bg-gray-200">
                        <ArrowLeft class="h-4 w-4" /> List
                    </Link>
                </div>

                <!-- MASTER TAB -->
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
                            <Input v-model="form.pi_no" type="text" placeholder="Enter PI No" class="mt-2" />
                            <InputError :message="form.errors.pi_no" class="mt-1" />
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
                            <Label>Supplier Name</Label>
                            <select v-model="form.supplier_id" class="mt-2 rounded border px-3 py-2">
                                <option value="">Select One</option>
                                <option v-for="supplier in props.suppliers" :key="supplier.id" :value="supplier.id">{{ supplier.name }}</option>
                            </select>
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
                            <select v-model="form.country_of_origin" class="mt-2 rounded border px-3 py-2">
                                <option value="">Select One</option>
                                <option value="1">France</option>
                                <option value="2">India</option>
                            </select>
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
                            <Label>Vehicle Temperature</Label>
                            <Input v-model="form.vehicle_inside_temp" type="text" placeholder="Enter Inside Temperature" class="mt-2" />
                            <InputError :message="form.errors.vehicle_inside_temp" class="mt-1" />
                        </div>

                        <div class="flex flex-col">
                            <Label>Ship To</Label>
                            <select v-model="form.company_id" class="mt-2 rounded border px-3 py-2">
                                <option value="0">Select One</option>
                                <option v-for="company in props.companies" :key="company.id" :value="company.id">{{ company.name }}</option>
                            </select>
                            <InputError :message="form.errors.company_id" class="mt-1" />
                        </div>

                        <div class="col-span-3 flex flex-col">
                            <Label>Note</Label>
                            <textarea v-model="form.remarks" class="mt-2 rounded border px-3 py-2" placeholder="Enter any Note"></textarea>
                            <InputError :message="form.errors.remarks" class="mt-1" />
                        </div>

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

                <!-- RECEIVE TAB -->
                <div v-if="activeTab === 1" class="space-y-4 rounded-lg border bg-white p-4 shadow-sm">
                    <h2 class="text-xl font-semibold">Receive Quantity</h2>
                    <div class="grid grid-cols-3 items-center gap-4">
                        <div class="flex flex-col">
                            <Label>Challan Box Qty</Label>
                            <Input v-model.number="form.ps_challan_box_qty" type="number" class="mt-2" />
                        </div>
                        <div class="flex flex-col">
                            <Label>Gross Weight</Label>
                            <Input v-model.number="form.ps_gross_weight" type="number" class="mt-2" />
                        </div>
                        <div class="flex flex-col">
                            <Label>Net Weight</Label>
                            <Input v-model.number="form.ps_net_weight" type="number" class="mt-2" />
                        </div>

                        <div class="flex flex-col">
                            <Label>Male Rec. Box</Label>
                            <Input v-model.number="form.ps_male_rec_box" type="number" class="mt-2" />
                        </div>
                        <div class="flex flex-col">
                            <Label>Male Qty</Label>
                            <Input v-model.number="form.ps_male_qty" type="number" class="mt-2" />
                        </div>
                        <div class="flex flex-col">
                            <Label>Female Rec. Box</Label>
                            <Input v-model.number="form.ps_female_rec_box" type="number" class="mt-2" />
                        </div>
                        <div class="flex flex-col">
                            <Label>Female Qty</Label>
                            <Input v-model.number="form.ps_female_qty" type="number" class="mt-2" />
                        </div>
                        <div class="flex flex-col">
                            <Label>Total Qty</Label>
                            <Input v-model.number="form.ps_total_qty" type="number" class="mt-2" readonly />
                        </div>
                        <div class="flex flex-col">
                            <Label>Total Rec Box</Label>
                            <Input v-model.number="form.ps_total_re_box_qty" type="number" class="mt-2" readonly />
                        </div>

                        <div class="flex flex-col">
                            <Label>Bonus Qty</Label>
                            <Input v-model.number="form.ps_bonus_qty" type="number" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- LAB TAB -->
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
                        {{ activeTab === tabs.length - 1 ? 'Update' : 'Next' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
