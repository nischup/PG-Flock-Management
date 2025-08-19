<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

import HeadingSmall from '@/components/HeadingSmall.vue'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import FileUploader from '@/components/FileUploader.vue'
import Datepicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import { watch } from 'vue'


// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'PS Receive', href: '/ps/ps-receive' },
]


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
  breed_type: '',
  country_of_origin: '',
  transport_type: '',
  remarks: '',
  file: [], // file upload
  net_weight:0,
  gross_weight:0,

  
  ps_male_rec_box: 0,             // Male Box Receive Qty
  ps_male_qty: 0,                 // Male Chicks Qty (can be auto-calculated if needed)

  // Female
  ps_female_rec_box: 0,           // Female Box Receive Qty
  ps_female_qty: 0,               // Female Chicks Qty

  // Totals
  ps_total_qty: 0,                // Total Chicks Qty (Male + Female)
  ps_total_re_box_qty: 0,         // Total Box Qty (Male + Female)

  // Challan
  ps_challan_box_qty: 0,          // Challan Box Qty

  // Weight
  ps_gross_weight: 0,
  ps_net_weight: 0,
})

const suppliers = ref([
  { id: 1, name: 'Hubbard Breeders' },
  { id: 2, name: 'Kazi' },
])


function submit() {

    console.log(form.file);

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
  () => [
    form.ps_male_qty,
    form.ps_female_qty,
    form.ps_male_rec_box,
    form.ps_female_rec_box
  ],
  () => {
    // Total chicks quantity
    form.ps_total_qty = Number(form.ps_male_qty || 0) + Number(form.ps_female_qty || 0);

    // Total box quantity
    form.ps_total_re_box_qty = Number(form.ps_male_rec_box || 0) + Number(form.ps_female_rec_box || 0);
  },
  { deep: true, immediate: true }
);


</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="PS Receive" />

    <div class="px-4 py-6 space-y-8">
      <!-- <HeadingSmall title="ps Receive Entry" description="Record shipment and chick details" /> -->

      <form @submit.prevent="submit" class="space-y-8" enctype="multipart/form-data">

        <!-- Master Section -->
        <div class="space-y-6">
          <h2 class="text-xl font-semibold">PS Information</h2>
          <div class="grid grid-cols-3 gap-6">
            <div class="flex flex-col mb-4">
              <Label>Shipment Type</Label>
              <select
                v-model="form.shipment_type_id"
                class="mt-2 border rounded px-3 py-2"
            >
                
                <option :value="1">Local</option>
                <option :value="2">Foreign</option>
                
            </select>
              <InputError :message="form.errors.shipment_type_id" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4">
              <Label>PI No</Label>
              <Input 
                v-model="form.pi_no" 
                type="text" 
                placeholder="Enter PI No" 
                class="mt-2 border rounded px-3 py-2 w-full" 
              />
              <InputError :message="form.errors.pi_no" class="mt-1" />
            </div>

            <div class="flex flex-col mb-4">
              <Label class="mb-2">PI Date</Label>
              <Datepicker
                v-model="form.pi_date"
                format="yyyy-MM-dd"      
                model-type="yyyy-MM-dd"  
                :input-class="'mt-2 border rounded px-3 py-2 w-full'"
                placeholder="Select PI Date"
                :auto-apply="true"
              />
              <InputError :message="form.errors.pi_date" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4">
              <Label>Order No</Label>
              <Input v-model="form.order_no" type="text" placeholder="Enter Order No" class="mt-2" />
              <InputError :message="form.errors.order_no" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4">
              <Label class="mb-2">Order Date</Label>
              
              <Datepicker
                v-model="form.order_date"
                format="yyyy-MM-dd"
                model-type="yyyy-MM-dd"
                :input-class="'mt-2 border rounded px-3 py-2 w-full'"
                placeholder="Select Order Date"
                :auto-apply="true"
              />
              <InputError :message="form.errors.order_date" class="mt-1" />
            </div>
            <div v-if="form.shipment_type_id != 1" class="flex flex-col mb-4">
              <Label>LC No</Label>
              <Input v-model="form.lc_no" type="text" placeholder="Enter LC No" class="mt-2" />
              <InputError :message="form.errors.lc_no" class="mt-1" />
            </div>
            <div v-if="form.shipment_type_id != 1" class="flex flex-col mb-4">
              <Label class="mb-2">LC Date</Label>
              <Datepicker
                v-model="form.lc_date"
                format="yyyy-MM-dd"
                model-type="yyyy-MM-dd"
                :input-class="'mt-2 border rounded px-3 py-2 w-full'"
                placeholder="Select LC Date"
                :auto-apply="true"
              />
              <InputError :message="form.errors.lc_date" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4">
              <Label>Supplier Name</Label>
              <select
                id="supplier"
                v-model="form.supplier_id"
                class="mt-2 border rounded px-3 py-2"
              >
                <option value="">Select One</option>
                <option 
                  v-for="supplier in suppliers" 
                  :key="supplier.id" 
                  :value="supplier.id"
                >
                  {{ supplier.name }}
                </option>
              </select>
              <InputError :message="form.errors.supplier_id" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4">
              <Label>Breed Type</Label>
              <select
                    v-model="form.breed_type"
                    class="mt-2 border rounded px-3 py-2"
                >
                    <option value="">Select One</option>
                    <option value="1">Rhode Island Red</option>
                    <option value="2">Cobb 500</option>
                </select>
              <InputError :message="form.errors.breed_type" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4" v-if="form.shipment_type_id == 2">
              <Label>Country of Origin</Label>
                <select
                    v-model="form.country_of_origin"
                    class="mt-2 border rounded px-3 py-2"
                >
                    <option value="">Select One</option>
                    <option value="1">France</option>
                    <option value="2">India</option>
                </select>
              <InputError :message="form.errors.country_of_origin" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4">
              <Label>Transport Type</Label>
                <select
                    v-model="form.transport_type"
                    class="mt-2 border rounded px-3 py-2"
                >
                    <option value="">Select One</option>
                    <option value="1">Freezing Microbas</option>
                    <option value="2">Freezing Bus</option>
                    <option value="3">Open Truck</option>
                </select>
              <InputError :message="form.errors.transport_type" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4 col-span-3">
              <Label>Note</Label>
              <textarea v-model="form.remarks" class="w-full border rounded px-3 py-2 mt-2" placeholder="Enter any Note"></textarea>
              <InputError :message="form.errors.remarks" class="mt-1" />
            </div>
            <!-- File upload section -->
            <div class="flex flex-col mb-4 col-span-3">
                <FileUploader
                    v-model="form.file"
                    label="Upload Files"
                    :max-files="3"
                    accept=".jpg,.jpeg,.png,.pdf"
                    wrapper-class="flex flex-col mb-4 col-span-3"
                    />
                <InputError :message="form.errors.file" class="mt-1" />
            </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 z-50 flex justify-center pt-6" @click.self="showModal = false">
  <div ref="modalRef" class="bg-white rounded-lg border border-gray-300 shadow-lg w-full max-w-2xl" style="top: 100px; position: absolute;">
    
    <!-- Modal Header -->
    <div class="flex items-center justify-between p-4 border-b border-gray-200 cursor-move" @mousedown="startDrag">
      <h3 class="text-xl font-semibold text-gray-900">
        {{ editingShed ? 'Edit Shed' : 'Add New Shed' }}
      </h3>
      <button type="button" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex justify-center items-center" @click="resetForm">✕</button>
    </div>

    <!-- Modal Body -->
    <div class="p-4 space-y-4">
      
      <!-- Shed Info -->
      <div>
        <Label for="name" class="mb-2">Shed Name</Label>
        <Input v-model="form.name" id="name" />
        <span v-if="form.errors.name" class="text-red-600 text-sm">{{ form.errors.name }}</span>
      </div>

      <div>
        <Label for="status" class="mb-2">Status</Label>
        <select v-model="form.status" id="status" class="w-full border rounded p-2">
          <option :value="1">Active</option>
          <option :value="0">Inactive</option>
        </select>
      </div>

      <!-- ✅ Chick Counts Section -->
      <div class="space-y-4 mt-4">
        <h2 class="text-xl font-semibold">Chick Counts</h2>

        <!-- Challan and Weights -->
        <div class="grid grid-cols-3 gap-4 items-center">
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

        <!-- Chicks Section -->
        <div class="grid grid-cols-3 gap-4 mb-6">
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

        <!-- Box Count Section -->
        <div class="grid grid-cols-3 gap-4 mb-6">
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
        </div>
      </div>

    </div>

    <!-- Modal Footer -->
    <div class="flex justify-end p-4 border-t border-gray-200">
      <button type="button" class="bg-gray-300 text-black mr-2 px-4 py-2 rounded" @click="resetForm">Cancel</button>
      <button type="button" class="bg-chicken text-white px-4 py-2 rounded" @click="submit">
        {{ editingShed ? 'Update' : 'Save' }}
      </button>
    </div>

  </div>
</div>
  </AppLayout>
</template>
