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

// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'DOC Receive', href: '/doc/doc-receive' },
]

// Active tab state
const activeTab = ref<'male' | 'female'>('male')

// Form data
const form = useForm({
  shipment_type_id: '',
  invoice_no: '',
  order_no: '',
  lc_no: '',
  supplier_id: '',
  breed_type: '',
  country_of_origin: '',
  transport_type: '',
  remarks: '',
  file: null, // file upload

  // Male
  doc_male_box: 0,
  doc_male_approximate_qty: 0,
  doc_male_totalqty: 0,
  doc_male_challan_qty: 0,
  doc_male_rate: 0,
  doc_male_value_total: 0,

  // Female
  doc_female_box: 0,
  doc_female_approximate_qty: 0,
  doc_female_totalqty: 0,
  doc_challan_qty: 0,
  doc_female_rate: 0,
  doc_female_value_total: 0,

  // Totals
  doc_totalbox: 0,
  doc_value_total: 0,
})

function submit() {
  form.post(route('doc-receives.store'))
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="DOC Receive" />

    <div class="px-4 py-6 space-y-8">
      <!-- <HeadingSmall title="DOC Receive Entry" description="Record shipment and chick details" /> -->

      <form @submit.prevent="submit" class="space-y-8" enctype="multipart/form-data">

        <!-- Master Section -->
        <div class="space-y-6">
          <h2 class="text-xl font-semibold">Master Information</h2>
          <div class="grid grid-cols-3 gap-6">
            <div class="flex flex-col mb-4">
              <Label>Shipment Type ID</Label>
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
              <Label>Invoice No</Label>
              <Input v-model="form.invoice_no" type="text" placeholder="Enter Invoice No" class="mt-2" />
              <InputError :message="form.errors.invoice_no" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4">
              <Label>Order No</Label>
              <Input v-model="form.order_no" type="text" placeholder="Enter Order No" class="mt-2" />
              <InputError :message="form.errors.order_no" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4">
              <Label>LC No</Label>
              <Input v-model="form.lc_no" type="text" placeholder="Enter LC No" class="mt-2" />
              <InputError :message="form.errors.lc_no" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4">
              <Label>Supplier ID</Label>
              <Input v-model="form.supplier_id" type="number" placeholder="Enter Supplier ID" class="mt-2" />
              <InputError :message="form.errors.supplier_id" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4">
              <Label>Breed Type</Label>
              <Input v-model="form.breed_type" type="number" placeholder="Enter Breed Type" class="mt-2" />
              <InputError :message="form.errors.breed_type" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4">
              <Label>Country of Origin</Label>
              <Input v-model="form.country_of_origin" type="number" placeholder="Enter Country of Origin" class="mt-2" />
              <InputError :message="form.errors.country_of_origin" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4">
              <Label>Transport Type</Label>
              <Input v-model="form.transport_type" type="number" placeholder="Enter Transport Type" class="mt-2" />
              <InputError :message="form.errors.transport_type" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4 col-span-3">
              <Label>Remarks</Label>
              <textarea v-model="form.remarks" class="w-full border rounded px-3 py-2 mt-2" placeholder="Enter any remarks"></textarea>
              <InputError :message="form.errors.remarks" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4 col-span-3">
              <Label>Upload File</Label>
              <Input type="file" @change="e => form.file = e.target.files[0]" class="mt-2" />
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
            <div class="flex flex-col mb-2"><Label>Box</Label><Input v-model="form.doc_male_box" type="number" class="mt-2" /><InputError :message="form.errors.doc_male_box" class="mt-1" /></div>
            <div class="flex flex-col mb-2"><Label>Approx Qty</Label><Input v-model="form.doc_male_approximate_qty" type="number" class="mt-2" /><InputError :message="form.errors.doc_male_approximate_qty" class="mt-1" /></div>
            <div class="flex flex-col mb-2"><Label>Total Qty</Label><Input v-model="form.doc_male_totalqty" type="number" step="0.01" class="mt-2" /><InputError :message="form.errors.doc_male_totalqty" class="mt-1" /></div>
            <div class="flex flex-col mb-2"><Label>Challan Qty</Label><Input v-model="form.doc_male_challan_qty" type="number" step="0.01" class="mt-2" /><InputError :message="form.errors.doc_male_challan_qty" class="mt-1" /></div>
            <div class="flex flex-col mb-2"><Label>Rate</Label><Input v-model="form.doc_male_rate" type="number" step="0.01" class="mt-2" /><InputError :message="form.errors.doc_male_rate" class="mt-1" /></div>
            <div class="flex flex-col mb-2"><Label>Value Total</Label><Input v-model="form.doc_male_value_total" type="number" step="0.01" class="mt-2" /><InputError :message="form.errors.doc_male_value_total" class="mt-1" /></div>
          </div>

          <!-- Female Section -->
          <div v-if="activeTab === 'female'" class="grid grid-cols-3 gap-4">
            <div class="flex flex-col mb-2"><Label>Box</Label><Input v-model="form.doc_female_box" type="number" class="mt-2" /><InputError :message="form.errors.doc_female_box" class="mt-1" /></div>
            <div class="flex flex-col mb-2"><Label>Approx Qty</Label><Input v-model="form.doc_female_approximate_qty" type="number" class="mt-2" /><InputError :message="form.errors.doc_female_approximate_qty" class="mt-1" /></div>
            <div class="flex flex-col mb-2"><Label>Total Qty</Label><Input v-model="form.doc_female_totalqty" type="number" step="0.01" class="mt-2" /><InputError :message="form.errors.doc_female_totalqty" class="mt-1" /></div>
            <div class="flex flex-col mb-2"><Label>Challan Qty</Label><Input v-model="form.doc_challan_qty" type="number" step="0.01" class="mt-2" /><InputError :message="form.errors.doc_challan_qty" class="mt-1" /></div>
            <div class="flex flex-col mb-2"><Label>Rate</Label><Input v-model="form.doc_female_rate" type="number" step="0.01" class="mt-2" /><InputError :message="form.errors.doc_female_rate" class="mt-1" /></div>
            <div class="flex flex-col mb-2"><Label>Value Total</Label><Input v-model="form.doc_female_value_total" type="number" step="0.01" class="mt-2" /><InputError :message="form.errors.doc_female_value_total" class="mt-1" /></div>
          </div>
        </div>

        <!-- Totals Section -->
        <div class="grid grid-cols-2 gap-4 items-center">
          <div class="flex flex-col mb-2">
            <Label>Total Box</Label>
            <Input v-model="form.doc_totalbox" type="number" step="0.01" class="mt-2" />
            <InputError :message="form.errors.doc_totalbox" class="mt-1" />
          </div>
          <div class="flex flex-col mb-2">
            <Label>Total Value</Label>
            <Input v-model="form.doc_value_total" type="number" step="0.01" class="mt-2" />
            <InputError :message="form.errors.doc_value_total" class="mt-1" />
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
