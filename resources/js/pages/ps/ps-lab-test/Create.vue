<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import InputError from '@/components/InputError.vue'
import FileUploader from '@/components/FileUploader.vue'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import type { BreadcrumbItem } from '@/types'

// Props from controller
const props = defineProps<{ psReceives: Array<any> }>()

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Lab Tests', href: '/ps-lab-test' },
  { title: 'Create', href: '/ps-lab-test/create' },
]

// Form
const form = useForm({
  ps_receive_id: "",
  lab_type: 'Gov Lab',
  lab_send_female_qty: 0,
  lab_send_male_qty: 0,
  lab_send_total_qty: 0,
  mortality_qty: 0,
  status: 'receive', // default status
  file: [],
})

// Selected PS Receive & accordion toggle
const selectedPSReceive = ref<any>(null)
const showInfo = ref(false)

// Watch female/male to update total
watch(
  () => [form.lab_send_female_qty, form.lab_send_male_qty],
  () => {
    form.lab_send_total_qty =
      Number(form.lab_send_female_qty || 0) + Number(form.lab_send_male_qty || 0)
  }
)

// When PI No changes, show info in accordion
function onSelectPSReceive(psReceiveId: number) {
  const ps = props.psReceives.find(p => p.id === psReceiveId) || null
  selectedPSReceive.value = ps
  form.ps_receive_id = ps?.id || null
  showInfo.value = !!ps

  // If Lab Test exists, pre-fill form with its data
  if (ps?.labTest) {
    form.lab_type = ps.labTest.lab_type
    form.lab_send_female_qty = ps.labTest.lab_send_female_qty
    form.lab_send_male_qty = ps.labTest.lab_send_male_qty
    form.lab_send_total_qty = ps.labTest.lab_send_total_qty
    form.mortality_qty = ps.labTest.mortality_qty
    form.status = ps.labTest.status
  } else {
    // reset form if no Lab Test exists
    form.lab_type = 'Gov Lab'
    form.lab_send_female_qty = 0
    form.lab_send_male_qty = 0
    form.lab_send_total_qty = 0
    form.mortality_qty = 0
    form.status = 'receive'
    form.file = []
  }
}

// Check if Lab Test already exists for this PS Receive
const isExistingLabTest = computed(() => !!selectedPSReceive.value?.labTest)

// Submit form
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

    <form @submit.prevent="submit" class="p-6 space-y-6">

      <!-- PI Selection -->
      <div class="border rounded-lg p-4 shadow-sm">
        <h2 class="font-semibold text-lg mb-4">Select PS Receive</h2>
        <div class="flex flex-col mb-4">
          <Label>PI No</Label>
          <select v-model="form.ps_receive_id" @change="onSelectPSReceive(Number(form.ps_receive_id))"
            class="mt-2 border rounded px-3 py-2 w-full">
            <option value="">Select PI No</option>
            <option v-for="ps in props.psReceives" :key="ps.id" :value="ps.id">{{ ps.pi_no }}</option>
          </select>
          <InputError :message="form.errors.ps_receive_id" class="mt-1" />
        </div>

        <!-- Accordion: PS Receive Info -->
        <transition
          enter-active-class="transition-all duration-500 ease-in-out"
          leave-active-class="transition-all duration-500 ease-in-out"
          enter-from-class="max-h-0 opacity-0"
          enter-to-class="max-h-screen opacity-100"
          leave-from-class="max-h-screen opacity-100"
          leave-to-class="max-h-0 opacity-0"
        >
          <div v-if="showInfo" class="grid grid-cols-3 gap-4 text-sm mt-5 overflow-hidden">
            <div><span class="font-medium">PI No:</span> {{ selectedPSReceive?.pi_no }}</div>
            <div><span class="font-medium">Order No:</span> {{ selectedPSReceive?.order_no }}</div>
            <div><span class="font-medium">Receive Date:</span> {{ selectedPSReceive?.created_at }}</div>
            <div><span class="font-medium">Supplier:</span> {{ selectedPSReceive?.supplier?.name }}</div>
            <div><span class="font-medium">Total Chicks:</span> {{ selectedPSReceive?.total_chicks_qty }}</div>
            <div><span class="font-medium">Total Box:</span> {{ selectedPSReceive?.total_box_qty }}</div>
          </div>
        </transition>
      </div>

      <!-- Show message if Lab Test exists -->
      <div v-if="isExistingLabTest" class="text-red-600 font-medium mt-2">
        Lab Test for this PI No already exists. You cannot create a duplicate.
      </div>

      <!-- Lab Send Section -->
      <div class="border rounded-lg p-4 shadow-sm">
        <h2 class="font-semibold text-lg mb-4">Lab Send Details</h2>

        <div class="grid grid-cols-3 gap-6 mb-4">
          <div class="flex flex-col">
            <Label>Lab Type</Label>
            <select v-model="form.lab_type" :disabled="isExistingLabTest"
              class="mt-2 border rounded px-3 py-2"
              :class="isExistingLabTest ? 'bg-gray-100 cursor-not-allowed' : ''">
              <option value="Gov Lab">Gov Lab</option>
              <option value="Provita Lab">Provita Lab</option>
            </select>
            <InputError :message="form.errors.lab_type" class="mt-1" />
          </div>

          <div class="flex flex-col">
            <Label>Gov Lab Send Female Qty</Label>
            <Input v-model.number="form.lab_send_female_qty" :disabled="isExistingLabTest"
              type="number" class="mt-2"
              :class="isExistingLabTest ? 'bg-gray-100' : ''" />
            <InputError :message="form.errors.lab_send_female_qty" class="mt-1" />
          </div>

          <div class="flex flex-col">
            <Label>Gov Lab Send Male Qty</Label>
            <Input v-model.number="form.lab_send_male_qty" :disabled="isExistingLabTest"
              type="number" class="mt-2"
              :class="isExistingLabTest ? 'bg-gray-100' : ''" />
            <InputError :message="form.errors.lab_send_male_qty" class="mt-1" />
          </div>
        </div>

        <div class="grid grid-cols-3 gap-6 mb-4">
          <div class="flex flex-col">
            <Label>Lab Send Total Qty</Label>
            <Input v-model.number="form.lab_send_total_qty" readonly class="mt-2 bg-gray-100" />
          </div>
          
          
        </div>
      </div>

      <!-- File Upload -->
      <div class="border rounded-lg p-4 shadow-sm">
        <h2 class="font-semibold text-lg mb-4">Attachments</h2>
        <FileUploader v-model="form.file" :disabled="isExistingLabTest" label="Upload Files" :max-files="3"
          accept=".jpg,.jpeg,.png,.pdf" />
        <InputError :message="form.errors.file" class="mt-1" />
      </div>

      <!-- Submit Button -->
      <div class="flex justify-end" v-if="!isExistingLabTest">
        <Button type="submit" class="px-6 py-2">Create</Button>
      </div>

    </form>
  </AppLayout>
</template>
