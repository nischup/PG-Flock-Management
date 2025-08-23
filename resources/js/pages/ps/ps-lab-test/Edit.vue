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

// Props from controller
const props = defineProps<{ labTest: any }>()

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Lab Tests', href: '/ps-lab-test' },
  { title: 'Edit', href: `/ps-lab-test/${props.labTest.id}/edit` },
]

// Form
const form = useForm({
  ps_receive_id: props.labTest.ps_receive_id,
  lab_type: props.labTest.lab_type || 'Gov Lab',
  lab_send_female_qty: props.labTest.lab_send_female_qty || 0,
  lab_send_male_qty: props.labTest.lab_send_male_qty || 0,
  lab_send_total_qty:
    (props.labTest.lab_send_female_qty || 0) + (props.labTest.lab_send_male_qty || 0),
  lab_receive_female_qty: props.labTest.lab_receive_female_qty || 0,
  lab_receive_male_qty: props.labTest.lab_receive_male_qty || 0,
  lab_receive_total_qty:
    (props.labTest.lab_receive_female_qty || 0) + (props.labTest.lab_receive_male_qty || 0),
  mortality_qty: props.labTest.mortality_qty || 0,
  notes: props.labTest.notes || '',
  status: props.labTest.status || 'receive',
  file: props.labTest.files || [],
})

// PS Receive info for accordion
const selectedPSReceive = ref({
  pi_no: props.labTest.ps_receive?.pi_no || '',
  order_no: props.labTest.ps_receive?.order_no || '',
  created_at: props.labTest.ps_receive?.created_at || '',
  supplier: props.labTest.ps_receive?.supplier?.name || '',
  total_chicks_qty: props.labTest.ps_receive?.total_chicks_qty || 0,
  total_box_qty: props.labTest.ps_receive?.total_box_qty || 0,
  male_box_qty: props.labTest.ps_receive?.male_box_qty || 0,
  female_box_qty: props.labTest.ps_receive?.female_box_qty || 0,
})

// Accordion toggle (expanded by default)
const showInfo = ref(true)

// Auto-calculate totals
watch(
  () => [form.lab_send_female_qty, form.lab_send_male_qty],
  () => {
    form.lab_send_total_qty = Number(form.lab_send_female_qty || 0) + Number(form.lab_send_male_qty || 0)
  }
)

watch(
  () => [form.lab_receive_female_qty, form.lab_receive_male_qty],
  () => {
    form.lab_receive_total_qty =
      Number(form.lab_receive_female_qty || 0) + Number(form.lab_receive_male_qty || 0)
  }
)

// Submit update
function submit() {
  form.put(route('ps-lab-test.update', props.labTest.id))
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Edit Lab Test" />

    <form @submit.prevent="submit" class="p-6 space-y-6">

      <!-- PS Receive Accordion -->
      <div class="border rounded-lg p-4 shadow-sm">
        <div class="flex justify-between items-center cursor-pointer" @click="showInfo = !showInfo">
          <h2 class="font-semibold text-lg">PS Receive Info</h2>
        </div>

        <transition
          enter-active-class="transition-all duration-500 ease-in-out"
          leave-active-class="transition-all duration-500 ease-in-out"
          enter-from-class="max-h-0 opacity-0"
          enter-to-class="max-h-screen opacity-100"
          leave-from-class="max-h-screen opacity-100"
          leave-to-class="max-h-0 opacity-0"
        >
          <div v-if="showInfo" class="grid grid-cols-3 gap-4 text-sm mt-4 overflow-hidden">
            <div><span class="font-medium">PI No:</span> {{ selectedPSReceive.pi_no }}</div>
            <div><span class="font-medium">Order No:</span> {{ selectedPSReceive.order_no }}</div>
            <div><span class="font-medium">Receive Date:</span> {{ selectedPSReceive.created_at }}</div>
            <div><span class="font-medium">Supplier:</span> {{ selectedPSReceive.supplier }}</div>
            <div><span class="font-medium">Total Chicks:</span> {{ selectedPSReceive.total_chicks_qty }}</div>
            <div><span class="font-medium">Total Box:</span> {{ selectedPSReceive.total_box_qty }}</div>
            <div><span class="font-medium">Male Box Qty:</span> {{ selectedPSReceive.male_box_qty }}</div>
            <div><span class="font-medium">Female Box Qty:</span> {{ selectedPSReceive.female_box_qty }}</div>
          </div>
        </transition>
      </div>

      <!-- Lab Send Section -->
      <div class="border rounded-lg p-4 shadow-sm mt-4">
        <h2 class="font-semibold text-lg mb-4">Lab Send Details</h2>
        <div class="grid grid-cols-3 gap-6 mb-4">
          <div>
            <Label>Lab Type</Label>
            <select v-model="form.lab_type" class="mt-2 border rounded px-3 py-2 bg-gray-100" readonly>
              <option value="Gov Lab">Gov Lab</option>
              <option value="Provita Lab">Provita Lab</option>
            </select>
            <InputError :message="form.errors.lab_type" class="mt-1" />
          </div>
          <div>
            <Label>Lab Send Female Qty</Label>
            <Input v-model.number="form.lab_send_female_qty" type="number" readonly class="mt-2 bg-gray-100" />
          </div>
          <div>
            <Label>Lab Send Male Qty</Label>
            <Input v-model.number="form.lab_send_male_qty" type="number" readonly class="mt-2 bg-gray-100" />
          </div>
        </div>

        <div class="grid grid-cols-3 gap-6">
          <div>
            <Label>Lab Send Total Qty</Label>
            <Input v-model.number="form.lab_send_total_qty" type="number" readonly class="mt-2 bg-gray-100" />
          </div>
          <div>
            <Label>Mortality Qty</Label>
            <Input v-model.number="form.mortality_qty" type="number" class="mt-2" />
          </div>
          <div>
            <Label>Status</Label>
            <select v-model="form.status" class="mt-2 border rounded px-3 py-2 w-full">
              <option value="receive">Receive</option>
              <option value="complete">Test Complete</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Lab Receive Section -->
      <div class="border rounded-lg p-4 shadow-sm mt-4">
        <h2 class="font-semibold text-lg mb-4">Lab Receive Details</h2>
        <div class="grid grid-cols-3 gap-6">
          <div>
            <Label>Lab Receive Female Qty</Label>
            <Input v-model.number="form.lab_receive_female_qty" type="number" />
          </div>
          <div>
            <Label>Lab Receive Male Qty</Label>
            <Input v-model.number="form.lab_receive_male_qty" type="number" />
          </div>
          <div>
            <Label>Lab Receive Total Qty</Label>
            <Input v-model.number="form.lab_receive_total_qty" type="number" readonly class="bg-gray-100 mt-2" />
          </div>
        </div>
      </div>

      <!-- Notes & Attachments -->
      <div class="border rounded-lg p-4 shadow-sm mt-4">
        <Label>Notes</Label>
        <textarea v-model="form.notes" class="border rounded px-3 py-2 mt-2 w-full"></textarea>
        <InputError :message="form.errors.notes" class="mt-1" />

        <div class="mt-4">
          <FileUploader v-model="form.file" label="Attachments" :max-files="3" accept=".jpg,.jpeg,.png,.pdf" />
          <InputError :message="form.errors.file" class="mt-1" />
        </div>
      </div>

      <!-- Submit Button -->
      <div class="flex justify-end mt-4">
        <Button type="submit" class="px-6 py-2">Update</Button>
      </div>

    </form>
  </AppLayout>
</template>
