<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import InputError from '@/components/InputError.vue'
import FileUploader from '@/components/FileUploader.vue'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import type { BreadcrumbItem } from '@/types'

// Props from controller: PS Receives list
const props = defineProps<{ psReceives: Array<any> }>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Lab Tests', href: '/ps-lab-test' },
  { title: 'Create', href: '/ps-lab-test/create' },
]

// Modal visibility
const showModal = ref(false)

// Selected PS Receive
const selectedPSReceive = ref<any>(null)

// Form
const form = useForm({
  ps_receive_id: null,
  lab_type: 'Gov Lab',
  lab_send_female_qty: 0,
  lab_send_male_qty: 0,
  lab_send_total_qty: 0,
  mortality_qty: 0,
  status: 'receive', // default status
  file: [],
})

// Watch to calculate Lab Send Total Qty
watch(
  () => [form.lab_send_female_qty, form.lab_send_male_qty],
  () => {
    form.lab_send_total_qty = Number(form.lab_send_female_qty || 0) + Number(form.lab_send_male_qty || 0)
  }
)

// When PI No is selected, show modal with PS Receive info
function onSelectPSReceive(psReceiveId: number) {
  selectedPSReceive.value = props.psReceives.find(p => p.id === psReceiveId)
  form.ps_receive_id = selectedPSReceive.value?.id
  showModal.value = true
}

// Submit
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

  form.post(route('ps-lab-test.store'), {
    data: formData,
    headers: { 'Content-Type': 'multipart/form-data' },
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Create Lab Test" />

    <div class="px-4 py-6 space-y-8">

      <!-- PI Selection -->
      <div class="flex flex-col mb-4">
        <Label>Select PI No</Label>
        <select v-model="form.ps_receive_id" @change="onSelectPSReceive(Number(form.ps_receive_id))" class="mt-2 border rounded px-3 py-2">
          <option value="">Select PI No</option>
          <option v-for="ps in props.psReceives" :key="ps.id" :value="ps.id">{{ ps.pi_no }}</option>
        </select>
        <InputError :message="form.errors.ps_receive_id" class="mt-1" />
      </div>

      <!-- Modal -->
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
        <div class="bg-white dark:bg-gray-900 p-6 rounded-xl w-full max-w-2xl relative">
          <h2 class="text-lg font-semibold mb-4">PS Receive Info</h2>
          <button @click="showModal = false" class="absolute top-2 right-2 text-gray-500 hover:text-gray-800">✖</button>
          <div class="grid grid-cols-3 gap-4">
            <div><Label>PI No</Label><Input :value="selectedPSReceive?.pi_no" disabled class="mt-2" /></div>
            <div><Label>Order No</Label><Input :value="selectedPSReceive?.order_no" disabled class="mt-2" /></div>
            <div><Label>Receive Date</Label><Input :value="selectedPSReceive?.created_at" disabled class="mt-2" /></div>
          </div>
        </div>
      </div>

      <!-- Lab Send Inputs -->
      <div class="grid grid-cols-3 gap-6 mt-4">
        <div class="flex flex-col mb-4">
          <Label>Lab Type</Label>
          <select v-model="form.lab_type" class="mt-2 border rounded px-3 py-2">
            <option value="Gov Lab">Gov Lab</option>
            <option value="Provita Lab">Provita Lab</option>
          </select>
          <InputError :message="form.errors.lab_type" class="mt-1" />
        </div>

        <div class="flex flex-col mb-4">
          <Label>Lab Send Female Qty</Label>
          <Input v-model.number="form.lab_send_female_qty" type="number" class="mt-2" />
          <InputError :message="form.errors.lab_send_female_qty" class="mt-1" />
        </div>

        <div class="flex flex-col mb-4">
          <Label>Lab Send Male Qty</Label>
          <Input v-model.number="form.lab_send_male_qty" type="number" class="mt-2" />
          <InputError :message="form.errors.lab_send_male_qty" class="mt-1" />
        </div>
      </div>

      <div class="grid grid-cols-3 gap-6 mb-4">
        <div class="flex flex-col">
          <Label>Lab Send Total Qty</Label>
          <Input v-model.number="form.lab_send_total_qty" type="number" class="mt-2" readonly />
        </div>
        <div class="flex flex-col">
          <Label>Mortality Qty</Label>
          <Input v-model.number="form.mortality_qty" type="number" class="mt-2" />
        </div>
        <div class="flex flex-col">
          <Label>Status</Label>
          <select v-model="form.status" class="mt-2 border rounded px-3 py-2 w-full">
            <option value="receive">Receive</option>
            <option value="complete">Test Complete</option>
          </select>
        </div>
      </div>

      <!-- File Upload -->
      <div class="flex flex-col mb-4">
        <FileUploader v-model="form.file" label="Upload Files" :max-files="3" accept=".jpg,.jpeg,.png,.pdf" />
        <InputError :message="form.errors.file" class="mt-1" />
      </div>

      <div class="flex justify-end mt-6">
        <Button type="submit" @click="submit" class="px-6 py-2">Create</Button>
      </div>

    </div>
  </AppLayout>
</template>
