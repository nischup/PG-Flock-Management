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

const props = defineProps<{
  psReceives: Array<any>
}>()

const selectedPsId = ref<number | string>('')
const showModal = ref(false)
const modalData = ref<any>(null)

function openModal() {
  if (!selectedPsId.value) return

  const selected = props.psReceives.find(ps => ps.id === Number(selectedPsId.value))
  if (selected) {
    modalData.value = selected
    showModal.value = true
  }
}
// Form data
const form = useForm({
  ps_receive_id: '',             // Parent PS Receive
  job_no: '',
  receiving_company_id: 0,
  firm_female_box_qty: 0,
  firm_male_box_qty: 0,
  firm_total_box_qty: 0,
  firm_sortage_box_qty:0,
  remarks: '',
  status: 1,
})



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
            <select v-model="selectedPsId" @change="openModal" class="mt-2 border rounded px-3 py-2">
                <option value="">Select PS Receive</option>
                <option v-for="ps in props.psReceives" :key="ps.id" :value="ps.id">
                {{ ps.pi_no }}
                </option>
            </select>
          <InputError :message="form.errors.ps_receive_id" class="mt-1" />
        </div>

        <!-- Job No
        <div class="flex flex-col mb-4">
          <Label>Job No</Label>
          <Input v-model="form.job_no" type="text" placeholder="Enter Job No" class="mt-2" />
          <InputError :message="form.errors.job_no" class="mt-1" />
        </div>  -->

        <!-- Receiving Company -->
        <div class="flex flex-col mb-4">
          <Label>Receiving Company</Label>
          <select v-model="form.receiving_company_id" class="mt-2 border rounded px-3 py-2">
            <option value="0">Select Company</option>
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
        <div class="flex flex-col">
            <Label>Sortage Box Qty</Label>
            <Input v-model.number="form.firm_sortage_box_qty" type="number" readonly class="mt-2" />
          </div>
        <!-- Remarks -->
        <div class="flex flex-col mb-4">
          <Label>Remarks</Label>
          <textarea v-model="form.remarks" class="mt-2 border rounded px-3 py-2 w-full"></textarea>
          <InputError :message="form.errors.remarks" class="mt-1" />
        </div>

        <!-- Status 
        <div class="flex flex-col mb-4">
          <Label>Status</Label>
          <select v-model="form.status" class="mt-2 border rounded px-3 py-2">
            <option :value="1">Active</option>
            <option :value="0">Inactive</option>
          </select>
          <InputError :message="form.errors.status" class="mt-1" />
        </div> -->

        <!-- Submit -->
        <div class="flex justify-end">
          <Button type="submit" class="px-6 py-2">Save</Button>
        </div>
      </form>
    </div>
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
        <div class="bg-white rounded shadow-lg w-full max-w-md p-6 relative">
            <button @click="showModal = false" class="absolute top-2 right-2 text-gray-500">✕</button>
            <h2 class="text-lg font-semibold mb-4">PS Receive Details</h2>

            <div v-if="modalData">
            <p><strong>PI No:</strong> {{ modalData.pi_no }}</p>
            <p><strong>Total Chicks Qty:</strong> {{ modalData.total_chicks_qty }}</p>
            <p><strong>Total Box Qty:</strong> {{ modalData.total_box_qty }}</p>
            <p><strong>Male Box:</strong> {{ modalData.male_box_qty }}</p>
            <p><strong>Female Box:</strong> {{ modalData.female_box_qty }}</p>
            </div>
        </div>
    </div>
  </AppLayout>
</template>
