<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AppLayout from '@/layouts/AppLayout.vue'
import FileUploader from '@/components/FileUploader.vue'
import Datepicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

import { type BreadcrumbItem } from '@/types'

// Props from controller
const props = defineProps<{
  psReceive: any
  suppliers: Array<{ id: number; name: string }>
}>()
console.log(props.psReceive);
// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'PS Receive', href: '/ps/ps-receive' },
  { title: 'Edit', href: '' },
]

// Form data pre-filled from props
const form = useForm({
  shipment_type_id: props.psReceive.shipment_type_id ?? 1,
  pi_no: props.psReceive.pi_no ?? '',
  pi_date: props.psReceive.pi_date,
  order_no: props.psReceive.order_no ?? '',
  order_date: props.psReceive.order_date,
  lc_no: props.psReceive.lc_no ?? '',
  lc_date: props.psReceive.lc_date,
  supplier_id: props.psReceive.supplier_id ?? '',
  breed_type: props.psReceive.breed_type ?? '',
  country_of_origin: props.psReceive.country_of_origin ?? '',
  transport_type: props.psReceive.transport_type ?? '',
  remarks: props.psReceive.remarks ?? '',
  file: [], // optional: existing files can be mapped here
  ps_male_rec_box: props.psReceive.ps_male_rec_box ?? 0,
  ps_male_qty: props.psReceive.ps_male_qty ?? 0,
  ps_female_rec_box: props.psReceive.ps_female_rec_box ?? 0,
  ps_female_qty: props.psReceive.ps_female_qty ?? 0,
  ps_total_qty: props.psReceive.ps_total_qty ?? 0,
  ps_total_re_box_qty: props.psReceive.ps_total_re_box_qty ?? 0,
  ps_challan_box_qty: props.psReceive.ps_challan_box_qty ?? 0,
  ps_gross_weight: props.psReceive.ps_gross_weight ?? 0,
  ps_net_weight: props.psReceive.ps_net_weight ?? 0,
})

// Watch to recalc totals automatically
watch(
  () => [form.ps_male_qty, form.ps_female_qty, form.ps_male_rec_box, form.ps_female_rec_box],
  () => {
    form.ps_total_qty = Number(form.ps_male_qty || 0) + Number(form.ps_female_qty || 0)
    form.ps_total_re_box_qty = Number(form.ps_male_rec_box || 0) + Number(form.ps_female_rec_box || 0)
  },
  { deep: true, immediate: true }
)

// Submit function
function submit() {
  const formData = new FormData()

  for (const key in form) {
    const value = form[key as keyof typeof form]
    if (key === 'file') {
      (value as File[]).forEach(f => formData.append('file[]', f))
    } else {
      formData.append(key, value != null ? String(value) : '')
    }
  }

  form.put(route('ps-receive.update', props.psReceive.id), {
    data: formData,
    headers: { 'Content-Type': 'multipart/form-data' },
    onSuccess: () => {
      // Optional: show success notification
    },
    onError: () => {
      // Optional: handle validation errors
    },
  })
}

const suppliers = ref([
  { id: 1, name: 'Hubbard Breeders' },
  { id: 2, name: 'Kazi' },
])
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Edit PS Receive" />

    <div class="px-4 py-6 space-y-8">
      <form @submit.prevent="submit" class="space-y-8" enctype="multipart/form-data">

        <!-- PS Information -->
        <div class="space-y-6">
          <h2 class="text-xl font-semibold">PS Information</h2>
          <div class="grid grid-cols-3 gap-6">

            <div class="flex flex-col mb-4">
              <Label>Shipment Type</Label>
              <select v-model="form.shipment_type_id" class="mt-2 border rounded px-3 py-2">
                <option :value="1">Local</option>
                <option :value="2">Foreign</option>
              </select>
              <InputError :message="form.errors.shipment_type_id" class="mt-1" />
            </div>

            <div class="flex flex-col mb-4">
              <Label>PI No</Label>
              <Input v-model="form.pi_no" type="text" class="mt-2" placeholder="Enter PI No" />
              <InputError :message="form.errors.pi_no" class="mt-1" />
            </div>

            <div class="flex flex-col mb-4">
              <Label>PI Date</Label>
              <Datepicker v-model="form.pi_date" format="yyyy-MM-dd" :input-class="'mt-2 border rounded px-3 py-2'" />
              <InputError :message="form.errors.pi_date" class="mt-1" />
            </div>

            <div class="flex flex-col mb-4">
              <Label>Order No</Label>
              <Input v-model="form.order_no" type="text" class="mt-2" />
              <InputError :message="form.errors.order_no" class="mt-1" />
            </div>

            <div class="flex flex-col mb-4">
              <Label>Order Date</Label>
              <Datepicker
                v-model="form.order_date"
                format="yyyy-MM-dd"
                model-type="yyyy-MM-dd"
                :input-class="'mt-2 border rounded px-3 py-2 w-full'"
              />
              <InputError :message="form.errors.order_date" class="mt-1" />
            </div>

            <div v-if="form.shipment_type_id != 1" class="flex flex-col mb-4">
              <Label>LC No</Label>
              <Input v-model="form.lc_no" type="text" class="mt-2" />
              <InputError :message="form.errors.lc_no" class="mt-1" />
            </div>

            <div v-if="form.shipment_type_id != 1" class="flex flex-col mb-4">
              <Label>LC Date</Label>
              <Datepicker
                v-model="form.lc_date"
                format="yyyy-MM-dd"
                model-type="yyyy-MM-dd"
                :input-class="'mt-2 border rounded px-3 py-2 w-full'"
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

            <div class="flex flex-col mb-4 col-span-3">
              <Label>Remarks</Label>
              <textarea v-model="form.remarks" class="w-full border rounded px-3 py-2 mt-2"></textarea>
              <InputError :message="form.errors.remarks" class="mt-1" />
            </div>

            <!-- File Upload -->
            <div class="flex flex-col mb-4 col-span-3">
              <FileUploader v-model="form.file" label="Upload Files" :max-files="3" accept=".jpg,.jpeg,.png,.pdf" />
              <InputError :message="form.errors.file" class="mt-1" />
            </div>

          </div>
        </div>

        <!-- Chick Counts & Weights -->
        <div class="space-y-4">
          <h2 class="text-xl font-semibold">Chick Counts & Weights</h2>

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
              <Input v-model="form.ps_total_qty" type="number" readonly class="mt-2" />
            </div>
          </div>

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
              <Input v-model="form.ps_total_re_box_qty" type="number" readonly class="mt-2" />
            </div>
          </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end">
          <Button type="submit" class="px-6 py-2">Update</Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
