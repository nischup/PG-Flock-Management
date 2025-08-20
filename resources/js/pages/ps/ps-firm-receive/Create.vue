<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Firm Receive', href: '/ps-firm-receive' },
  { title: 'Create', href: '' },
]

// Form data
const form = useForm({
  ps_receive_id: '',             // Parent PS Receive
  job_no: '',
  receiving_company_id: 0,
  firm_female_box_qty: 0,
  firm_male_box_qty: 0,
  firm_total_box_qty: 0,
  remarks: '',
  status: 1,
})

// Options
const psReceives = ref([
  { id: 1, pi_no: '12300' },
  { id: 2, pi_no: '12301' },
])

const companies = ref([
  { id: 1, name: 'PBL' },
  { id: 2, name: 'PCL' },
])

// Watch total boxes
import { watch } from 'vue'
watch(
  () => [form.firm_male_box_qty, form.firm_female_box_qty],
  () => {
    form.firm_total_box_qty = Number(form.firm_male_box_qty || 0) + Number(form.firm_female_box_qty || 0)
  },
  { deep: true, immediate: true }
)

// Submit function
function submit() {
  form.post(route('ps-firm-receive.store'), {
    onSuccess: () => form.reset(),
    onError: () => {},
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Create Firm Receive" />

    <div class="px-4 py-6 space-y-8">
      <form @submit.prevent="submit" class="space-y-6">

        <!-- Parent PS Receive -->
        <div class="flex flex-col mb-4">
          <Label>PS Receive</Label>
          <select v-model="form.ps_receive_id" class="mt-2 border rounded px-3 py-2">
            <option value="">Select PS Receive</option>
            <option v-for="ps in psReceives" :key="ps.id" :value="ps.id">
              {{ ps.pi_no }}
            </option>
          </select>
          <InputError :message="form.errors.ps_receive_id" class="mt-1" />
        </div>

        <!-- Job No -->
        <div class="flex flex-col mb-4">
          <Label>Job No</Label>
          <Input v-model="form.job_no" type="text" placeholder="Enter Job No" class="mt-2" />
          <InputError :message="form.errors.job_no" class="mt-1" />
        </div>

        <!-- Receiving Company -->
        <div class="flex flex-col mb-4">
          <Label>Receiving Company</Label>
          <select v-model="form.receiving_company_id" class="mt-2 border rounded px-3 py-2">
            <option value="">Select Company</option>
            <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <InputError :message="form.errors.receiving_company_id" class="mt-1" />
        </div>

        <!-- Box Quantities -->
        <div class="grid grid-cols-3 gap-4 mb-4">
          <div class="flex flex-col">
            <Label>Female Box Qty</Label>
            <Input v-model.number="form.firm_female_box_qty" type="number" class="mt-2" />
          </div>
          <div class="flex flex-col">
            <Label>Male Box Qty</Label>
            <Input v-model.number="form.firm_male_box_qty" type="number" class="mt-2" />
          </div>
          <div class="flex flex-col">
            <Label>Total Box Qty</Label>
            <Input v-model.number="form.firm_total_box_qty" type="number" readonly class="mt-2" />
          </div>
        </div>

        <!-- Remarks -->
        <div class="flex flex-col mb-4">
          <Label>Remarks</Label>
          <textarea v-model="form.remarks" class="mt-2 border rounded px-3 py-2 w-full"></textarea>
          <InputError :message="form.errors.remarks" class="mt-1" />
        </div>

        <!-- Status -->
        <div class="flex flex-col mb-4">
          <Label>Status</Label>
          <select v-model="form.status" class="mt-2 border rounded px-3 py-2">
            <option :value="1">Active</option>
            <option :value="0">Inactive</option>
          </select>
          <InputError :message="form.errors.status" class="mt-1" />
        </div>

        <!-- Submit -->
        <div class="flex justify-end">
          <Button type="submit" class="px-6 py-2">Save</Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
