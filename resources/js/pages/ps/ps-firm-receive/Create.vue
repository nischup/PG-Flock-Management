<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'

import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Parent Stock', href: '/ps-firm-receive' },
  { title: 'Firm Receive', href: '' },
]

const props = defineProps<{
  psReceives: Array<any>
}>()

const selectedPsId = ref<number | string>('')
const showInfo = ref(false)       // 👈 replaces modal toggle
const form = useForm({
  ps_receive_id: '',             // Parent PS Receive
  job_no: '',
  receiving_company_id: 0,
  firm_female_box_qty: 0,
  firm_male_box_qty: 0,
  firm_total_box_qty: 0,
  firm_sortage_box_qty: 0,
  remarks: '',
  status: 1,
})

// Toggle details when dropdown changes
function toggleInfo() {
  if (!selectedPsId.value) {
    showInfo.value = false
    return
  }

  const selected = props.psReceives.find(ps => ps.id === Number(selectedPsId.value))
  if (selected) {
    // assign all matching fields to form (if they exist in ps)
    form.shipment_type = selected.shipment_type || ''
    form.pi_no = selected.pi_no || ''
    form.lc_no = selected.lc_no || ''
    form.order_no = selected.order_no || ''
    form.supplier = selected.supplier || ''
    form.breed = selected.breed || ''
    form.transport = selected.transport || ''
    form.rnote = selected.rnote || ''
    form.challan_box = selected.challan_box || ''
    form.gross_weight = selected.gross_weight || ''
    form.net_weight = selected.net_weight || ''
    form.female_chicks = selected.female_chicks || 0
    form.male_chicks = selected.male_chicks || 0
    form.total_chicks = selected.total_chicks || 0

    // toggle show/hide on reselection
    showInfo.value = !showInfo.value
  }
}

// Company options
const companies = ref([
  { id: 1, name: 'PBL' },
  { id: 2, name: 'PCL' },
])

// Watch total boxes
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

    <form @submit.prevent="submit" class="p-6 space-y-6">
          
     <!-- Section: Receiving Information -->
    <div class="border rounded-lg p-4 shadow-sm">
      <h2 class="font-semibold text-lg mb-4">Receiving Information</h2>

      <div class="grid grid-cols-1 gap-4">
        <div>
          <Label>PS Receive No</Label>
          <select v-model="selectedPsId" @change="toggleInfo"
            class="w-full mt-1 border rounded px-3 py-2">
            <option value="">Select PS Receive</option>
            <option v-for="ps in props.psReceives" :key="ps.id" :value="ps.id">
              {{ ps.pi_no }}
            </option>
          </select>
        </div>
      </div>

      <!-- Smooth transition wrapper -->
      <transition
        enter-active-class="transition-all duration-500 ease-in-out"
        leave-active-class="transition-all duration-500 ease-in-out"
        enter-from-class="max-h-0 opacity-0"
        enter-to-class="max-h-screen opacity-100"
        leave-from-class="max-h-screen opacity-100"
        leave-to-class="max-h-0 opacity-0"
      >
        <div v-if="showInfo" class="grid grid-cols-3 gap-4 text-sm mt-5 overflow-hidden">
          <!-- Row 1 -->
          <div>
            <span class="font-medium">Shipment Type:</span>
            <span class="ml-1">{{ form.shipment_type }}</span>
          </div>
          <div>
            <span class="font-medium">PI No:</span>
            <span class="ml-1">{{ form.pi_no }}</span>
          </div>
          <div>
            <span class="font-medium">LC No:</span>
            <span class="ml-1">{{ form.lc_no }}</span>
          </div>

          <!-- Row 2 -->
          <div>
            <span class="font-medium">Order No:</span>
            <span class="ml-1">{{ form.order_no }}</span>
          </div>
          <div>
            <span class="font-medium">Supplier:</span>
            <span class="ml-1">{{ form.supplier }}</span>
          </div>
          <div>
            <span class="font-medium">Breed:</span>
            <span class="ml-1">{{ form.breed }}</span>
          </div>

          <!-- Row 3 -->
          <div>
            <span class="font-medium">Transport:</span>
            <span class="ml-1">{{ form.transport }}</span>
          </div>
          <div>
            <span class="font-medium">RNote:</span>
            <span class="ml-1">{{ form.rnote }}</span>
          </div>
          <div>
            <span class="font-medium">Challan Box:</span>
            <span class="ml-1">{{ form.challan_box }}</span>
          </div>

          <!-- Row 4 -->
          <div>
            <span class="font-medium">Gross Weight:</span>
            <span class="ml-1">{{ form.gross_weight }}</span>
          </div>
          <div>
            <span class="font-medium">Net Weight:</span>
            <span class="ml-1">{{ form.net_weight }}</span>
          </div>
          <div>
            <span class="font-medium">Female Chicks:</span>
            <span class="ml-1">{{ form.female_chicks }}</span>
          </div>

          <!-- Row 5 -->
          <div>
            <span class="font-medium">Male Chicks:</span>
            <span class="ml-1">{{ form.male_chicks }}</span>
          </div>
          <div>
            <span class="font-medium">Total Chicks:</span>
            <span class="ml-1">{{ form.total_chicks }}</span>
          </div>
        </div>
      </transition>
    </div>


      <!-- Section: Company & Boxes -->
      <div class="border rounded-lg p-4 shadow-sm">
        <h2 class="font-semibold text-lg mb-4">Receiving Company & Boxes</h2>

        <div class="mb-4">
          <Label>Receiving Company</Label>
          <select v-model="form.receiving_company_id" class="w-full mt-1 border rounded px-3 py-2">
            <option value="0">Select Company</option>
            <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>

        <div class="grid grid-cols-3 gap-4">
          <div>
            <Label>Female Box Qty</Label>
            <Input v-model.number="form.firm_female_box_qty" type="number" class="mt-1" />
          </div>
          <div>
            <Label>Male Box Qty</Label>
            <Input v-model.number="form.firm_male_box_qty" type="number" class="mt-1" />
          </div>
          <div>
            <Label>Total Box Qty</Label>
            <Input 
              v-model.number="form.firm_total_box_qty" 
              type="number" 
              readonly 
              class="mt-1 bg-gray-100 text-gray-700 cursor-not-allowed" 
            />
          </div>
        </div>

        
        <div class="grid grid-cols-3 gap-4 mt-5">
          <div>
            <Label>Sortage Box Qty</Label>
            <Input  type="number" class="mt-1" />
          </div>
          <div>
            <Label>Excess Box Qty</Label>
            <Input type="number" class="mt-1" />
          </div>
        </div>

      </div>

      <!-- Section: Remarks -->
      <div class="border rounded-lg p-4 shadow-sm">
        <h2 class="font-semibold text-lg mb-4">Notes</h2>
        <textarea v-model="form.remarks"
          class="w-full border rounded px-3 py-2"
          rows="3"
          placeholder="Write notes here..."></textarea>
      </div>

      <!-- Submit -->
      <div class="flex justify-end">
        <Button type="submit" class="px-6 py-2">Save & receive</Button>
      </div>
    </form>
  </AppLayout>
</template>
