<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

import InputError from '@/components/InputError.vue'
import FileUploader from '@/components/FileUploader.vue'
import Datepicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import { ArrowLeft } from 'lucide-vue-next'
import type { BreadcrumbItem } from '@/types'

// Props from controller
const props = defineProps<{ psReceive: any }>()

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'PS', href: '/ps-receive' },
  { title: 'Edit', href: `/ps-receive/${props.psReceive.id}/edit` },
]

// Form initialization with existing data
const form = useForm({
  shipment_type_id: props.psReceive.shipment_type_id || 1,
  pi_no: props.psReceive.pi_no || '',
  pi_date: props.psReceive.pi_date || null,
  order_no: props.psReceive.order_no || '',
  order_date: props.psReceive.order_date || null,
  lc_no: props.psReceive.lc_no || '',
  lc_date: props.psReceive.lc_date || null,
  supplier_id: props.psReceive.supplier_id || '',
  breed_type: props.psReceive.breed_type || '',
  country_of_origin: props.psReceive.country_of_origin || '',
  transport_type: props.psReceive.transport_type || '',
  company_id: props.psReceive.company_id || 0,
  remarks: props.psReceive.remarks || '',
  file: [], // for new uploads
  labfile: [],
  ps_male_rec_box: props.psReceive.ps_male_rec_box || 0,
  ps_male_qty: props.psReceive.ps_male_qty || 0,
  ps_female_rec_box: props.psReceive.ps_female_rec_box || 0,
  ps_female_qty: props.psReceive.ps_female_qty || 0,
  ps_total_qty: props.psReceive.ps_total_qty || 0,
  ps_total_re_box_qty: props.psReceive.ps_total_re_box_qty || 0,
  ps_challan_box_qty: props.psReceive.ps_challan_box_qty || 0,
  ps_gross_weight: props.psReceive.ps_gross_weight || 0,
  ps_net_weight: props.psReceive.ps_net_weight || 0,
  lab_type: props.psReceive.lab_type || 'Gov Lab',
  lab_send_female_qty: props.psReceive.lab_send_female_qty || 0,
  lab_send_male_qty: props.psReceive.lab_send_male_qty || 0,
  provita_lab_male_qty:0,
  provita_lab_type:2,
  provita_lab_female_qty:0,
  lab_send_total_qty: (props.psReceive.lab_send_female_qty || 0) + (props.psReceive.lab_send_male_qty || 0),
})

// Example suppliers
const suppliers = ref([
  { id: 1, name: 'Hubbard Breeders' },
  { id: 2, name: 'Kazi' },
])

// Watchers to calculate totals
watch(
  () => [form.ps_male_qty, form.ps_female_qty, form.ps_male_rec_box, form.ps_female_rec_box],
  () => {
    form.ps_total_qty = Number(form.ps_male_qty || 0) + Number(form.ps_female_qty || 0)
    form.ps_total_re_box_qty = Number(form.ps_male_rec_box || 0) + Number(form.ps_female_rec_box || 0)
  },
  { deep: true, immediate: true }
)

watch(
  () => [form.lab_send_male_qty, form.lab_send_female_qty],
  () => {
    form.lab_send_total_qty = Number(form.lab_send_male_qty || 0) + Number(form.lab_send_female_qty || 0)
  },
  { deep: true, immediate: true }
)

// Submit update
function submit() {
  const formData = new FormData()
  for (const key in form) {
    const value = form[key as keyof typeof form]
    if (key === 'file' || key === 'labfile') {
      (value as File[]).forEach(f => formData.append(`${key}[]`, f))
    } else {
      formData.append(key, value != null ? String(value) : '')
    }
  }

  form.put(route('ps-receive.update', props.psReceive.id), {
    data: formData,
    headers: { 'Content-Type': 'multipart/form-data' },
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Edit PS Receive" />

    <div class="px-4 py-6 space-y-8">
      <form @submit.prevent="submit" class="space-y-8" enctype="multipart/form-data">
        
        <!-- Header -->
        <div class="space-y-6 border-b">
          <div class="pb-3 mb-6 flex items-center justify-between">
            <h2 class="text-xl font-semibold">PS Receiving Info</h2>
            <Link href="/ps-receive" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded shadow gap-1">
              <ArrowLeft class="w-4 h-4" /> Back
            </Link>
          </div>

          <!-- Form fields (mirrors Add page) -->
          <div class="grid grid-cols-3 gap-6">
            <!-- Shipment Type -->
            <div class="flex flex-col mb-4">
              <Label>Shipment Type</Label>
              <select v-model="form.shipment_type_id" class="mt-2 border rounded px-3 py-2">
                <option :value="1">Local</option>
                <option :value="2">Foreign</option>
              </select>
              <InputError :message="form.errors.shipment_type_id" class="mt-1" />
            </div>

            <!-- PI No -->
            <div class="flex flex-col mb-4">
              <Label>PI No</Label>
              <Input v-model="form.pi_no" type="text" class="mt-2" />
              <InputError :message="form.errors.pi_no" class="mt-1" />
            </div>

            <!-- PI Date -->
            <div class="flex flex-col mb-4">
              <Label>PI Date</Label>
              <Datepicker v-model="form.pi_date" format="yyyy-MM-dd" :input-class="'mt-2 border rounded px-3 py-2 w-full'" />
              <InputError :message="form.errors.pi_date" class="mt-1" />
            </div>

            <!-- Order No & Order Date -->
            <div class="flex flex-col mb-4">
              <Label>Order No</Label>
              <Input v-model="form.order_no" type="text" class="mt-2" />
              <InputError :message="form.errors.order_no" class="mt-1" />
            </div>
            <div class="flex flex-col mb-4">
              <Label>Order Date</Label>
              <Datepicker v-model="form.order_date" format="yyyy-MM-dd" :input-class="'mt-2 border rounded px-3 py-2 w-full'" />
              <InputError :message="form.errors.order_date" class="mt-1" />
            </div>

            <!-- LC No & LC Date (if Foreign) -->
            <div v-if="form.shipment_type_id != 1" class="flex flex-col mb-4">
              <Label>LC No</Label>
              <Input v-model="form.lc_no" type="text" class="mt-2" />
              <InputError :message="form.errors.lc_no" class="mt-1" />
            </div>
            <div v-if="form.shipment_type_id != 1" class="flex flex-col mb-4">
              <Label>LC Date</Label>
              <Datepicker v-model="form.lc_date" format="yyyy-MM-dd" :input-class="'mt-2 border rounded px-3 py-2 w-full'" />
              <InputError :message="form.errors.lc_date" class="mt-1" />
            </div>

            <!-- Supplier -->
            <div class="flex flex-col mb-4">
              <Label>Supplier</Label>
              <select v-model="form.supplier_id" class="mt-2 border rounded px-3 py-2">
                <option value="">Select One</option>
                <option v-for="s in suppliers" :key="s.id" :value="s.id">{{ s.name }}</option>
              </select>
              <InputError :message="form.errors.supplier_id" class="mt-1" />
            </div>

            <!-- Breed Type -->
            <div class="flex flex-col mb-4">
              <Label>Breed Type</Label>
              <select v-model="form.breed_type" class="mt-2 border rounded px-3 py-2">
                <option value="">Select One</option>
                <option value="1">Rhode Island Red</option>
                <option value="2">Cobb 500</option>
              </select>
              <InputError :message="form.errors.breed_type" class="mt-1" />
            </div>

            <!-- Country of Origin -->
            <div v-if="form.shipment_type_id == 2" class="flex flex-col mb-4">
              <Label>Country of Origin</Label>
              <select v-model="form.country_of_origin" class="mt-2 border rounded px-3 py-2">
                <option value="">Select One</option>
                <option value="1">France</option>
                <option value="2">India</option>
              </select>
              <InputError :message="form.errors.country_of_origin" class="mt-1" />
            </div>

            <!-- Transport Type -->
            <div class="flex flex-col mb-4">
              <Label>Transport Type</Label>
              <select v-model="form.transport_type" class="mt-2 border rounded px-3 py-2">
                <option value="">Select One</option>
                <option value="1">Freezing Microbas</option>
                <option value="2">Freezing Bus</option>
                <option value="3">Open Truck</option>
              </select>
              <InputError :message="form.errors.transport_type" class="mt-1" />
            </div>

            <!-- Shift To -->
            <div class="flex flex-col mb-4">
              <Label>Shift To</Label>
              <select v-model="form.company_id" class="mt-2 border rounded px-3 py-2">
                <option value="0">Select One</option>
                <option value="1">PBL</option>
                <option value="2">PCL</option>
              </select>
              <InputError :message="form.errors.company_id" class="mt-1" />
            </div>
          </div>

          <!-- Notes & File Upload -->
          <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col mb-4">
              <Label>Remarks</Label>
              <textarea v-model="form.remarks" class="border rounded px-3 py-2 mt-2"></textarea>
            </div>
            <div class="flex flex-col mb-4">
              <FileUploader v-model="form.file" label="Upload Files" :max-files="3" accept=".jpg,.jpeg,.png,.pdf" />
              <InputError :message="form.errors.file" class="mt-1" />
            </div>
          </div>
        </div>

        <!-- Chick Counts Section -->
        <div class="space-y-4 border-b py-4">
          <h2 class="text-xl font-semibold">Receive Total</h2>

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

        <!-- Lab Test Section -->
        <div class="space-y-4 py-4">
          <h2 class="text-xl font-semibold">Transfer Lab (For Test)</h2>

          <div class="grid grid-cols-3 gap-4 items-center">
            <div class="flex flex-col">
              <Label>Lab Type</Label>
              <select v-model="form.lab_type" class="mt-2 border rounded px-3 py-2">
                <option value="Gov Lab">Gov Lab</option>
                <option value="Provita Lab">Provita Lab</option>
              </select>
            </div>

            <div class="flex flex-col">
              <Label>Gov Lab Female Transfer Qty</Label>
              <Input v-model.number="form.lab_send_female_qty" type="number" class="mt-2" @input="form.lab_send_total_qty = Number(form.lab_send_female_qty) + Number(form.lab_send_male_qty)" />
            </div>

            <div class="flex flex-col">
              <Label>Gov Lab Male Transfer Qty</Label>
              <Input v-model.number="form.lab_send_male_qty" type="number" class="mt-2" @input="form.lab_send_total_qty = Number(form.lab_send_female_qty) + Number(form.lab_send_male_qty)" />
            </div>
          </div>

          <div class="grid grid-cols-3 gap-4 items-center">
            <div class="flex flex-col">
             <Label>Lab Type</Label>
              <select v-model="form.provita_lab_type" class="mt-2 border rounded px-3 py-2">
                <option value="2">Provita Lab</option>
              </select>
            </div>

            <div class="flex flex-col">
              <Label>Provita Lab Female Transfer Qty</Label>
              <Input v-model.number="form.provita_lab_send_female_qty" type="number" class="mt-2" @input="updateTotalQty" />
            </div>
            <div class="flex flex-col">
              <Label>Provita Lab Male Transfer Qty</Label>
              <Input v-model.number="form.provita_lab_send_male_qty" type="number" class="mt-2"  @input="updateTotalQty" />
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="flex flex-col">
              <Label>Lab Total Transfer Qty</Label>
              <Input v-model.number="form.lab_send_total_qty" type="number" class="mt-2" readonly />
            </div>

            <div class="flex flex-col">
              <Label>Remarks</Label>
              <textarea v-model="form.remarks" class="border rounded px-3 py-2 mt-2"></textarea>
            </div>
          </div>

          <!-- Lab File Upload -->
          <div class="flex flex-col mb-4">
            <FileUploader v-model="form.labfile" label="Upload Lab Files" :max-files="1" accept=".jpg,.jpeg,.png,.pdf" />
            <InputError :message="form.errors.labfile" class="mt-1" />
          </div>
        </div>

        <div class="flex justify-end">
          <Button type="submit" class="px-6 py-2">Update</Button>
        </div>

      </form>
    </div>
  </AppLayout>
</template>
