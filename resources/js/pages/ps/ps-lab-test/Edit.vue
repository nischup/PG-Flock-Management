<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3'
import { watch } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { ArrowLeft } from 'lucide-vue-next'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import InputError from '@/components/InputError.vue'
import type { BreadcrumbItem } from '@/types'

// Props from controller
const props = defineProps<{ labTest: any }>()

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Lab Tests', href: '/ps-lab-test' },
  { title: 'Edit', href: `/ps-lab-test/${props.labTest.id}/edit` },
]

// Form initialization
const form = useForm({
  lab_type: props.labTest.lab_type || 'Gov Lab',
  lab_send_female_qty: props.labTest.lab_send_female_qty || 0,
  lab_send_male_qty: props.labTest.lab_send_male_qty || 0,
  lab_send_total_qty: (props.labTest.lab_send_female_qty || 0) + (props.labTest.lab_send_male_qty || 0),
  lab_receive_female_qty: props.labTest.lab_receive_female_qty || 0,
  lab_receive_male_qty: props.labTest.lab_receive_male_qty || 0,
  lab_receive_total_qty: (props.labTest.lab_receive_female_qty || 0) + (props.labTest.lab_receive_male_qty || 0),
  mortality_qty: props.labTest.mortality_qty || 0,
  notes: props.labTest.notes || '',
  status: props.labTest.status || 1,
  pi_no: props.labTest.ps_receive?.pi_no || '',
  order_no: props.labTest.ps_receive?.order_no || '',
  receive_date: props.labTest.ps_receive?.created_at || null,
})

// Auto-calculate Lab Receive Total Qty
watch(
  () => [form.lab_receive_female_qty, form.lab_receive_male_qty],
  () => {
    form.lab_receive_total_qty = Number(form.lab_receive_female_qty || 0) + Number(form.lab_receive_male_qty || 0)
  },
  { deep: true, immediate: true }
)

// Auto-calculate Lab Send Total Qty
watch(
  () => [form.lab_send_female_qty, form.lab_send_male_qty],
  () => {
    form.lab_send_total_qty = Number(form.lab_send_female_qty || 0) + Number(form.lab_send_male_qty || 0)
  },
  { deep: true, immediate: true }
)

// Submit update
function submit() {
  form.put(route('ps-lab-test.update', props.labTest.id))
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Edit Lab Test" />

    <div class="px-4 py-6 space-y-8">
      <form @submit.prevent="submit" class="space-y-8">
        
        <!-- Header -->
        <div class="space-y-6 border-b pb-3 mb-6 flex items-center justify-between">
          <h2 class="text-xl font-semibold">Lab Test Info</h2>
          <Link href="/ps-lab-test" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded shadow gap-1">
            <ArrowLeft class="w-4 h-4" /> Back
          </Link>
        </div>

        <!-- PS Receive Info -->
        <div class="grid grid-cols-3 gap-6">
          <div class="flex flex-col mb-4">
            <Label>PI No</Label>
            <Input v-model="form.pi_no" type="text" class="mt-2 bg-gray-100" readonly />
          </div>
          <div class="flex flex-col mb-4">
            <Label>Order No</Label>
            <Input v-model="form.order_no" type="text" class="mt-2 bg-gray-100" readonly />
          </div>
          <div class="flex flex-col mb-4">
            <Label>Receive Date</Label>
            <Input v-model="form.receive_date" type="date" class="mt-2 bg-gray-100" readonly />
          </div>
        </div>

        <!-- Lab Send Info -->
        <div class="grid grid-cols-3 gap-6">
          <div class="flex flex-col mb-4">
            <Label>Lab Type</Label>
            <select v-model="form.lab_type" class="mt-2 border rounded px-3 py-2 bg-gray-100" readonly>
              <option value="Gov Lab">Gov Lab</option>
              <option value="Provita Lab">Provita Lab</option>
            </select>
            <InputError :message="form.errors.lab_type" class="mt-1" />
          </div>

          <div class="flex flex-col mb-4">
            <Label>Lab Transfer Female Qty</Label>
            <Input v-model.number="form.lab_send_female_qty" type="number" class="mt-2 bg-gray-100" />
            <InputError :message="form.errors.lab_send_female_qty" class="mt-1" />
          </div>

          <div class="flex flex-col mb-4">
            <Label>Lab Transfer Male Qty</Label>
            <Input v-model.number="form.lab_send_male_qty" type="number" class="mt-2 bg-gray-100" />
            <InputError :message="form.errors.lab_send_male_qty" class="mt-1" />
          </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
          <div class="flex flex-col mb-4">
            <Label>Lab Transfer Total Qty</Label>
            <Input v-model.number="form.lab_send_total_qty" type="number" class="mt-2 bg-gray-100" readonly />
          </div>
        </div>

        <!-- Lab Receive Info -->
        <div class="grid grid-cols-4 gap-6 mt-4">
          <div class="flex flex-col mb-4">
            <Label>Lab Receive Female Qty</Label>
            <Input v-model.number="form.lab_receive_female_qty" type="number" class="mt-2" />
            <InputError :message="form.errors.lab_receive_female_qty" class="mt-1" />
          </div>
          <div class="flex flex-col mb-4">
            <Label>Lab Receive Male Qty</Label>
            <Input v-model.number="form.lab_receive_male_qty" type="number" class="mt-2" />
            <InputError :message="form.errors.lab_receive_male_qty" class="mt-1" />
          </div>
          <div class="flex flex-col mb-4">
            <Label>Lab Receive Total Qty</Label>
            <Input v-model.number="form.lab_receive_total_qty" type="number" class="mt-2" readonly />
          </div>
          <div class="flex flex-col mb-4">
            <Label>Mortality Qty</Label>
            <Input v-model.number="form.mortality_qty" type="number" class="mt-2" />
            <InputError :message="form.errors.mortality_qty" class="mt-1" />
          </div>
        </div>

        <!-- Notes & Status -->
        <div class="grid grid-cols-2 gap-6 mt-4">
          <div class="flex flex-col mb-4">
            <Label>Notes</Label>
            <textarea v-model="form.notes" class="border rounded px-3 py-2 mt-2"></textarea>
          </div>

          <div class="flex flex-col mb-4">
            <Label>Status</Label>
            <select v-model="form.status" class="mt-2 border rounded px-3 py-2 w-full">
              <option value="1">Receive</option>
                <option value="2">Test Complete</option>
            </select>
          </div>
        </div>

        <div class="flex justify-end mt-6">
          <Button type="submit" class="px-6 py-2">Update</Button>
        </div>

      </form>
    </div>
  </AppLayout>
</template>
