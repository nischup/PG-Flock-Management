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
import axios from 'axios'


// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'PS Receive', href: '/ps/ps-receive' },
]

// Active tab state
const activeTab = ref<'male' | 'female'>('male')

// Form data
const form = useForm({
  shipment_type_id: 2,
  pi_no: '',
  pi_date: '', 
  order_no: '',
  order_date: '',
  lc_no: '',
  lc_date: '', 
  supplier_id: '',
  breed_type: '',
  country_of_origin: '',
  transport_type: '',
  remarks: '',
  file: [], // file upload

  // Male
  ps_male_box: 0,
  ps_male_approximate_qty: 0,
  ps_male_totalqty: 0,
  ps_male_challan_qty: 0,
  ps_male_rate: 0,
  ps_male_value_total: 0,

  // Female
  ps_female_box: 0,
  ps_female_approximate_qty: 0,
  ps_female_totalqty: 0,
  ps_challan_qty: 0,
  ps_female_rate: 0,
  ps_female_value_total: 0,

  // Totals
  ps_totalbox: 0,
  ps_value_total: 0,
})




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
                <option :value="1">Foreign</option>
                <option :value="2">Local</option>
            </select>
              <InputError :message="form.errors.shipment_type_id" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4">
              <Label>PI No</Label>
              <Input v-model="form.pi_no" type="text" placeholder="Enter PI No" class="mt-2" />
              <InputError :message="form.errors.pi_no" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4">
              <Label>PI Date</Label>
              <Input v-model="form.pi_date" type="date" class="mt-2" />
              <InputError :message="form.errors.pi_date" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4">
              <Label>Order No</Label>
              <Input v-model="form.order_no" type="text" placeholder="Enter Order No" class="mt-2" />
              <InputError :message="form.errors.order_no" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4">
              <Label>Order Date</Label>
              <Input v-model="form.order_date" type="date" class="mt-2" />
              <InputError :message="form.errors.order_date" class="mt-1" />
            </div>
            <div v-if="form.shipment_type_id != 2" class="flex flex-col mb-4">
              <Label>LC No</Label>
              <Input v-model="form.lc_no" type="text" placeholder="Enter LC No" class="mt-2" />
              <InputError :message="form.errors.lc_no" class="mt-1" />
            </div>
            <div v-if="form.shipment_type_id != 2" class="flex flex-col mb-4">
              <Label>LC Date</Label>
              <Input v-model="form.lc_date" type="date" class="mt-2" />
              <InputError :message="form.errors.lc_date" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4">
              <Label>Supplier Name</Label>
              <Input v-model="form.supplier_id" type="number" placeholder="Enter Supplier" class="mt-2" />
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
            <div class="flex flex-col mb-4" v-if="form.shipment_type_id == 1">
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
                    <option value="1">Freezing Van</option>
                    <option value="2">Freezing Bus</option>
                </select>
              <InputError :message="form.errors.transport_type" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4 col-span-3">
              <Label>Remarks</Label>
              <textarea v-model="form.remarks" class="w-full border rounded px-3 py-2 mt-2" placeholder="Enter any remarks"></textarea>
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

        <!-- Chick Counts Tabs -->
        <div class="space-y-4">
          <h2 class="text-xl font-semibold">Chick Counts</h2>
          <div class="flex border-b mb-4">
            <button
              type="button"
              @click="activeTab = 'male'"
              :class="[
                'px-4 py-2 font-medium',
                activeTab === 'male'
                  ? 'border-b-2 border-blue-600 text-blue-600'
                  : 'text-gray-500 hover:text-gray-700'
              ]"
            >
              Male
            </button>
            <button
              type="button"
              @click="activeTab = 'female'"
              :class="[
                'px-4 py-2 font-medium',
                activeTab === 'female'
                  ? 'border-b-2 border-blue-600 text-blue-600'
                  : 'text-gray-500 hover:text-gray-700'
              ]"
            >
              Female
            </button>
          </div>

          <!-- Male Section -->
          <div v-if="activeTab === 'male'" class="grid grid-cols-3 gap-4">
            <div class="flex flex-col mb-2"><Label>Male Box</Label><Input v-model="form.ps_male_box" type="number" class="mt-2" /><InputError :message="form.errors.ps_male_box" class="mt-1" /></div>
            <div class="flex flex-col mb-2"><Label>Approx. Chicks Qty (Per Box)</Label><Input v-model="form.ps_male_approximate_qty" type="number" class="mt-2" /><InputError :message="form.errors.ps_male_approximate_qty" class="mt-1" /></div>
            <div class="flex flex-col mb-2"><Label>Approx Total Chicks Qty</Label><Input v-model="form.ps_male_totalqty" type="number" step="0.01" class="mt-2" /><InputError :message="form.errors.ps_male_totalqty" class="mt-1" /></div>
            <div class="flex flex-col mb-2"><Label>Challan Box Qty</Label><Input v-model="form.ps_male_challan_qty" type="number" step="0.01" class="mt-2" /><InputError :message="form.errors.ps_male_challan_qty" class="mt-1" /></div>
            <div class="flex flex-col mb-2"><Label>Rate</Label><Input v-model="form.ps_male_rate" type="number" step="0.01" class="mt-2" /><InputError :message="form.errors.ps_male_rate" class="mt-1" /></div>
            <div class="flex flex-col mb-2"><Label>Value Total</Label><Input v-model="form.ps_male_value_total" type="number" step="0.01" class="mt-2" /><InputError :message="form.errors.ps_male_value_total" class="mt-1" /></div>
          </div>

          <!-- Female Section -->
          <div v-if="activeTab === 'female'" class="grid grid-cols-3 gap-4">
            <div class="flex flex-col mb-2"><Label>Box</Label><Input v-model="form.ps_female_box" type="number" class="mt-2" /><InputError :message="form.errors.ps_female_box" class="mt-1" /></div>
            <div class="flex flex-col mb-2"><Label>Approx Qty</Label><Input v-model="form.ps_female_approximate_qty" type="number" class="mt-2" /><InputError :message="form.errors.ps_female_approximate_qty" class="mt-1" /></div>
            <div class="flex flex-col mb-2"><Label>Total Qty</Label><Input v-model="form.ps_female_totalqty" type="number" step="0.01" class="mt-2" /><InputError :message="form.errors.ps_female_totalqty" class="mt-1" /></div>
            <div class="flex flex-col mb-2"><Label>Challan Qty</Label><Input v-model="form.ps_challan_qty" type="number" step="0.01" class="mt-2" /><InputError :message="form.errors.ps_challan_qty" class="mt-1" /></div>
            <div class="flex flex-col mb-2"><Label>Rate</Label><Input v-model="form.ps_female_rate" type="number" step="0.01" class="mt-2" /><InputError :message="form.errors.ps_female_rate" class="mt-1" /></div>
            <div class="flex flex-col mb-2"><Label>Value Total</Label><Input v-model="form.ps_female_value_total" type="number" step="0.01" class="mt-2" /><InputError :message="form.errors.ps_female_value_total" class="mt-1" /></div>
          </div>
        </div>

        <!-- Totals Section -->
        <div class="grid grid-cols-2 gap-4 items-center">
          <div class="flex flex-col mb-2">
            <Label>Total Box</Label>
            <Input v-model="form.ps_totalbox" type="number" step="0.01" class="mt-2" />
            <InputError :message="form.errors.ps_totalbox" class="mt-1" />
          </div>
          <div class="flex flex-col mb-2">
            <Label>Total Value</Label>
            <Input v-model="form.ps_value_total" type="number" step="0.01" class="mt-2" />
            <InputError :message="form.errors.ps_value_total" class="mt-1" />
          </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end">
          <Button type="submit" class="px-6 py-2">Save</Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
