<script setup lang="ts">
import { ref, watch } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import InputError from '@/components/InputError.vue'
import FileUploader from '@/components/FileUploader.vue'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import type { BreadcrumbItem } from '@/types'
import { Input } from '@/components/ui/input'
// Props
const props = defineProps<{ labTest: any }>()

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Lab Tests', href: '/ps-lab-test' },
  { title: 'Edit', href: `/ps-lab-test/${props.labTest.id}/edit` },
]





// Form
const form = useForm({
  ps_receive_id: props.labTest.ps_receive_id,
  lab_receive_female_qty: Number(props.labTest.lab_receive_female_qty ?? 0),
  lab_receive_male_qty: Number(props.labTest.lab_receive_male_qty ?? 0),
  lab_receive_total_qty:
    Number(props.labTest.lab_receive_female_qty ?? 0) + Number(props.labTest.lab_receive_male_qty ?? 0),
  mortality_qty: Number(props.labTest.mortality_qty ?? 0),
  status: props.labTest.status ?? '1',
  notes: props.labTest.notes ?? '',
  file: props.labTest.files ?? [],
})

console.log(props.labTest);

const psReceive = ref({
  pi_no: props.labTest.ps_receive?.pi_no ?? '',
  pi_date: props.labTest.ps_receive?.pi_date ?? '',
  order_no: props.labTest.ps_receive?.order_no ?? '',
  order_date: props.labTest.ps_receive?.order_date ?? '',
  lc_no: props.labTest.ps_receive?.lc_no ?? '',
  lc_date: props.labTest.ps_receive?.lc_date ?? '',
  remarks: props.labTest.ps_receive?.remarks ?? '',
  created_at:props.labTest.created_at ?? '',
})

// Lab Send Accordion
const labSend = ref({
  lab_type: props.labTest.lab_type ?? 'Gov Lab',
  lab_send_female_qty: Number(props.labTest.lab_send_female_qty ?? 0),
  lab_send_male_qty: Number(props.labTest.lab_send_male_qty ?? 0),
  lab_send_total_qty:
    Number(props.labTest.lab_send_female_qty ?? 0) + Number(props.labTest.lab_send_male_qty ?? 0),
})



// Accordion toggles
const showPsReceive = ref(true)
const showLabSend = ref(true)

// Auto-calculate lab_receive_total_qty whenever female/male qty changes
watch(
  [() => form.lab_receive_female_qty, () => form.lab_receive_male_qty],
  ([female, male]) => {
    form.lab_receive_total_qty = Number(female ?? 0) + Number(male ?? 0)
  },
  { immediate: true }
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
      <div class="border rounded-lg p-4 shadow-sm bg-white p-4 rounded">
        <div class="flex justify-between items-center cursor-pointer" @click="showPsReceive = !showPsReceive">
          <h2 class="font-semibold text-lg">PS Receive Info</h2>
        </div>
        <transition enter-active-class="transition-all duration-500 ease-in-out"
                    leave-active-class="transition-all duration-500 ease-in-out"
                    enter-from-class="max-h-0 opacity-0"
                    enter-to-class="max-h-screen opacity-100"
                    leave-from-class="max-h-screen opacity-100"
                    leave-to-class="max-h-0 opacity-0">
          <div v-if="showPsReceive" class="grid grid-cols-3 gap-4 text-sm mt-4 overflow-hidden">
            <div><span class="font-medium">PI No:</span> {{ psReceive.pi_no }}</div>
            <div><span class="font-medium">PI Date:</span> {{ psReceive.pi_date }}</div>
            <div><span class="font-medium">Order No:</span> {{ psReceive.order_no }}</div>
            <div><span class="font-medium">Order Date:</span> {{ psReceive.order_date }}</div>
            <div><span class="font-medium">Lab Send Date:</span> {{ psReceive.created_at }}</div>
            
            <div class="col-span-3"><span class="font-medium">Remarks:</span> {{ psReceive.remarks }}</div>
          </div>
        </transition>
      </div>

      <!-- Lab Send Accordion -->
      <div class="border rounded-lg p-4 shadow-sm bg-white p-4 rounded">
        <div class="flex justify-between items-center cursor-pointer" @click="showLabSend = !showLabSend">
          <h2 class="font-semibold text-lg">Lab Send Info</h2>
        </div>
        <transition enter-active-class="transition-all duration-500 ease-in-out"
                    leave-active-class="transition-all duration-500 ease-in-out"
                    enter-from-class="max-h-0 opacity-0"
                    enter-to-class="max-h-screen opacity-100"
                    leave-from-class="max-h-screen opacity-100"
                    leave-to-class="max-h-0 opacity-0">
          <div v-if="showLabSend" class="grid grid-cols-3 gap-4 text-sm mt-4 overflow-hidden">
            <div><span class="font-medium">Lab Type:</span> {{ labSend.lab_type == 1 ? 'Gov Lab' : labSend.lab_type == 2 ? 'Provita Lab' : '' }}</div>
            <div><span class="font-medium">Female Qty:</span> {{ labSend.lab_send_female_qty }}</div>
            <div><span class="font-medium">Male Qty:</span> {{ labSend.lab_send_male_qty }}</div>
            <div><span class="font-medium">Total Qty:</span> {{ labSend.lab_send_total_qty }}</div>
          </div>
        </transition>
      </div>

      <!-- Lab Receive Section -->
      <div class="border rounded-lg p-4 shadow-sm mt-4 bg-white p-4 rounded">
        <h2 class="font-semibold text-lg mb-4">Lab Receive Details</h2>

        <div class="grid grid-cols-3 gap-6 mb-4">
          <div class="flex flex-col">
            <Label>Lab Receive Female Qty</Label>
            <Input v-model.number="form.lab_receive_female_qty" type="number" class="border rounded px-3 py-2 w-full" />
            <InputError :message="form.errors.lab_receive_female_qty" class="mt-1" />
          </div>

          <div class="flex flex-col">
            <Label>Lab Receive Male Qty</Label>
            <Input v-model.number="form.lab_receive_male_qty" type="number" class="border rounded px-3 py-2 w-full" />
            <InputError :message="form.errors.lab_receive_male_qty" class="mt-1" />
          </div>

          <div class="flex flex-col">
            <Label>Lab Receive Total Qty</Label>
            <Input v-model.number="form.lab_receive_total_qty" type="number" readonly class="border rounded px-3 py-2 w-full bg-gray-100" />
            <InputError :message="form.errors.lab_receive_total_qty" class="mt-1" />
          </div>
        </div>

        <div class="grid grid-cols-3 gap-6 mb-4 ">
          <div class="flex flex-col">
            <Label>Mortality Qty</Label>
            <Input v-model.number="form.mortality_qty" type="number" class="mt-2 border rounded px-3 py-2 w-full" />
            <InputError :message="form.errors.mortality_qty" class="mt-1" />
          </div>

          <div class="flex flex-col">
            <Label>Status</Label>
            <select v-model="form.status" class="mt-2 border rounded px-3 py-2 w-full">
              <option value="1">Receive</option>
              <option value="2">Test Complete</option>
            </select>
            <InputError :message="form.errors.status" class="mt-1" />
          </div>
        </div>
      </div>

      <!-- Notes & Attachments -->
      <div class="border rounded-lg p-4 shadow-sm mt-4 bg-white p-4 rounded">
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
