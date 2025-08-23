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
const showInfo = ref(false)    
const isLabData = ref(false)    // 👈 replaces modal toggle
const labMessage = ref('') 
const labInput = ref(false)
const messageType = ref('') 
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
  send_female_qty:0,
  send_male_qty:0,
  send_total_qty:0,
  lab_type:1,
})

function toggleInfo() {
    const selected = props.psReceives.find(ps => ps.id === Number(selectedPsId.value))

    if (!selected) {

     
      showInfo.value = false
      isLabData.value = false
      labMessage.value = ''
      labInput.value = false
      return
    }

    form.ps_receive_id = selected.id
    // check if lab data exists
   // check if lab data exists
    if (!selected.labTest || selected.labTest.length === 0) {
      // labTest is null OR empty
      labMessage.value = 'No Chicks Transfer for Lab Test.'
      isLabData.value = true
      labInput.value = true
      messageType.value = 'info'
    } else {
      // labTest has records
      labMessage.value = 'This PS Receive has already been sent for Lab Test.'
      isLabData.value = true
      messageType.value = 'error'
      labInput.value = false
    }

    console.log(props.psReceives);


    form.shipment_type = shipmentTypes[selected.shipment_type_id] || ''
    form.pi_no = selected.pi_no || ''
    form.pi_date = selected.pi_date || ''
    form.order_no = selected.order_no || ''
    form.order_date = selected.order_date || ''
    form.lc_no = selected.lc_no || ''
    form.lc_date = selected.lc_date || ''
    form.supplier = suppliers[selected.supplier_id] || ''
    form.breed = breeds[selected.breed_type] || ''
    form.transport = transports[selected.transport_type] || ''
    form.rnote = selected.remarks || ''
    form.challan_box = selected.total_box_qty || 0
    form.gross_weight = selected.gross_weight || 0
    form.net_weight = selected.net_weight || 0
    form.female_chicks = selected.female_chicks || 0
    form.male_chicks = selected.male_chicks || 0
    form.total_chicks = selected.total_chicks_qty || 0
    form.receiving_company_id = selected.company_id

    showInfo.value = true
  }

// Company options
const shipmentTypes = ['Air', 'Sea', 'Road']
const suppliers = {1: 'PBL', 2: 'PCL'} 
const breeds = {1: 'Broiler', 2: 'Layer'}
const transports = {1: 'Freezing Microbus', 2: 'Freezing Van'}

// Watch total boxes
watch(
  () => [form.firm_male_box_qty, form.firm_female_box_qty],
  () => {
    form.firm_total_box_qty = Number(form.firm_male_box_qty || 0) + Number(form.firm_female_box_qty || 0)
  },
  { deep: true, immediate: true }
)

watch(
  () => [form.send_female_qty, form.send_male_qty],
  () => {
    form.send_total_qty =
      Number(form.send_female_qty || 0) +
      Number(form.send_male_qty || 0)
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
        <div v-if="labMessage" 
          :class="[
            'font-medium mt-2',
            messageType === 'error' ? 'text-red-600' : 'text-green-600'
          ]">
          {{ labMessage }}
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
            <span class="font-medium">Note:</span>
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
          <select v-model="form.receiving_company_id" class="w-full mt-1 border rounded px-3 py-2 bg-gray-100" disabled>
            <option value="0">Select Company</option>
                <option value="1">PBL</option>
                <option value="2">PCL</option>
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

      <!-- Lab Test Section -->
      <div v-if="labInput" class="border rounded-lg p-4 shadow-sm mt-4">
        <h2 class="font-semibold text-lg mb-4">Lab Test Send Info</h2>

        <div class="grid grid-cols-4 gap-4">
          <div class="flex flex-col">
            <Label>Lab Type</Label>
            <select v-model="form.lab_type" class="mt-1 border rounded px-3 py-2 w-full">
              <option value="">Select Lab</option>
              <option value="1">Gov Lab</option>
              <option value="2">Provita Lab</option>
            </select>
          </div>

          <div class="flex flex-col">
            <Label>Send Female Chicks Qty</Label>
            <Input v-model.number="form.send_female_qty" type="number" class="mt-1" />
          </div>

          <div class="flex flex-col">
            <Label>Send Male Chicks Qty</Label>
            <Input v-model.number="form.send_male_qty" type="number" class="mt-1" />
          </div>

          <div class="flex flex-col">
            <Label>Total Box Qty</Label>
            <Input 
              v-model.number="form.send_total_qty" 
              type="number" 
              readonly 
              class="mt-1 bg-gray-100 text-gray-700 cursor-not-allowed" 
            />
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
        <Button type="submit" class="px-6 py-2">Save & Submit</Button>
      </div>
    </form>
  </AppLayout>
</template>
