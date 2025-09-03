<script setup lang="ts">
import { Link, Head, useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { ArrowLeft } from 'lucide-vue-next'

// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Parent Stock', href: '/ps-firm-receive' },
  { title: 'Firm Receive', href: '' },
]

// Props
const props = defineProps<{
  psReceives: Array<any>
  flocks: Array<any>
}>()

// Main form state
const selectedPsId = ref<number | string>('')
const selectedFlockId = ref<number | string>('')
const showInfo = ref(false)    
const isLabData = ref(false)
const labMessage = ref('') 
const labInput = ref(false)
const messageType = ref('') 

const form = useForm({
  ps_receive_id: '', 
  flock_id: 0,
  job_no: '',
  receiving_company_id: 0,
  firm_female_box_qty: 0,
  firm_male_box_qty: 0,
  firm_total_box_qty: 0,
  firm_sortage_male_box: 0,
  firm_sortage_female_box: 0,
  firm_sortage_box_qty: 0,   // total shortage
  firm_excess_male_box: 0,
  firm_excess_female_box: 0,
  firm_excess_box_qty: 0,   
  remarks: '',
  status: 1,
  send_female_qty: 0,
  send_male_qty: 0,
  send_total_qty: 0,
  lab_type: 1,
})

// Company options
const shipmentTypes = ['Air', 'Sea', 'Road']
const suppliers = {1: 'PBL', 2: 'PCL'} 
const breeds = {1: 'Broiler', 2: 'Layer'}
const transports = {1: 'Freezing Microbus', 2: 'Freezing Van'}

// Watch total boxes
watch(() => [form.firm_male_box_qty, form.firm_female_box_qty], () => {
  form.firm_total_box_qty = Number(form.firm_male_box_qty || 0) + Number(form.firm_female_box_qty || 0)
  // Total shortage
    form.firm_sortage_box_qty = form.firm_sortage_male_box + form.firm_sortage_female_box
    form.firm_excess_box_qty = form.firm_excess_male_box + form.firm_excess_female_box
}, { deep: true, immediate: true })

watch(() => [form.send_female_qty, form.send_male_qty], () => {
  form.send_total_qty = Number(form.send_female_qty || 0) + Number(form.send_male_qty || 0)
}, { deep: true, immediate: true })
// Watch for dropdown changes
watch(selectedFlockId, (val) => {
    form.flock_id = val;
});
// Submit PS Firm Receive
function submit() {
  form.post(route('ps-firm-receive.store'), {
    onSuccess: () => form.reset(),
    onError: () => {}
  })
}

// Toggle PS info
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
  if (!selected.labTest || selected.labTest.length === 0) {
    labMessage.value = 'No Chicks Transfer for Lab Test.'
    isLabData.value = true
    labInput.value = true
    messageType.value = 'info'
  } else {
    labMessage.value = 'This PS Receive has already been sent for Lab Test.'
    isLabData.value = true
    messageType.value = 'error'
    labInput.value = false
  }

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

// Flock modal state
const showFlockModal = ref(false)
const flockFormError = ref('')

const flockForm = useForm({
  name: '',
  parent_flock_id: null
})

// Draggable Flock modal
const flockModalRef = ref<HTMLElement | null>(null)
let flockDragOffset = { x: 0, y: 0 }
function startDragFlock(event: MouseEvent) {
  if (!flockModalRef.value) return
  flockDragOffset.x = event.clientX - flockModalRef.value.getBoundingClientRect().left
  flockDragOffset.y = event.clientY - flockModalRef.value.getBoundingClientRect().top
  function onMouseMove(e: MouseEvent) {
    if (!flockModalRef.value) return
    flockModalRef.value.style.left = `${e.clientX - flockDragOffset.x}px`
    flockModalRef.value.style.top = `${e.clientY - flockDragOffset.y}px`
  }
  function onMouseUp() {
    window.removeEventListener('mousemove', onMouseMove)
    window.removeEventListener('mouseup', onMouseUp)
  }
  window.addEventListener('mousemove', onMouseMove)
  window.addEventListener('mouseup', onMouseUp)
}

// Reset Flock form
function resetFlockForm() {
  flockForm.reset()
  flockFormError.value = ''
  showFlockModal.value = false
}

// Add new Flock via Inertia
function addNewFlock() {
  flockForm.post(route('flocks.store'), {
    onSuccess: (page) => {
      // page.props won't automatically contain the new flock
      // Instead, use the flashed 'flock' from the backend
      const newFlock = page.props.flock || page.props.flash.flock;
      if (newFlock) {
        props.flocks.push(newFlock);  // Add to dropdown list
        selectedFlockId.value = newFlock.id;
        form.flock_id = newFlock.id;
      }
      resetFlockForm();
    },
    onError: (errors) => {
      flockFormError.value = errors.name || 'Something went wrong';
    },
  });
}
</script>

<template>
<AppLayout :breadcrumbs="breadcrumbs">
  <Head title="Create Firm Receive" />

  <form @submit.prevent="submit" class="p-6 space-y-6">

    <!-- Parent Stock Info -->
    <div class="border rounded-lg p-4 shadow-sm bg-white">
      <div class="pb-3 mb-6 flex items-center justify-between">
        <h2 class="text-xl font-semibold">Parent Stock Farm Receiving Info.</h2>
        <Link 
          href="/ps-firm-receive" 
          class="px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md flex items-center gap-1"
        >
          <ArrowLeft class="w-4 h-4" /> List
        </Link>
      </div>

      <div class="grid grid-cols-1 gap-4">
        
        
        <div>
          <Label>PS Receive No</Label>
          <select v-model="selectedPsId" @change="toggleInfo" class="w-full mt-1 border rounded px-3 py-2">
            <option value="">Select PS Receive</option>
            <option v-for="ps in props.psReceives" :key="ps.id" :value="ps.id">{{ ps.pi_no }}</option>
          </select>
        </div>

        

        <div>
          <Label>Flock</Label>
          <div class="flex gap-2 items-center">
            <select v-model="selectedFlockId" class="w-full mt-1 border rounded px-3 py-2">
              <option value="">Select Flock</option>
              <option v-for="flock in props.flocks" :key="flock.id" :value="flock.id">{{ flock.name }}</option>
            </select>
            <Button class="bg-chicken text-white px-4 py-1" @click.prevent="showFlockModal = true">Add Flock</Button>
          </div>
        </div>

        <div v-if="labMessage" :class="['font-medium mt-2', messageType === 'error' ? 'text-red-600' : 'text-green-600']">
          {{ labMessage }}
        </div>
      </div>

      <!-- PS Info -->
      <transition enter-active-class="transition-all duration-500 ease-in-out" leave-active-class="transition-all duration-500 ease-in-out"
        enter-from-class="max-h-0 opacity-0" enter-to-class="max-h-screen opacity-100"
        leave-from-class="max-h-screen opacity-100" leave-to-class="max-h-0 opacity-0">
        <div v-if="showInfo" class="grid grid-cols-3 gap-4 text-sm mt-5 overflow-hidden">
          <div><span class="font-medium">Shipment Type:</span> <span class="ml-1">{{ form.shipment_type }}</span></div>
          <div><span class="font-medium">PI No:</span> <span class="ml-1">{{ form.pi_no }}</span></div>
          <div><span class="font-medium">LC No:</span> <span class="ml-1">{{ form.lc_no }}</span></div>
          <div><span class="font-medium">Order No:</span> <span class="ml-1">{{ form.order_no }}</span></div>
          <div><span class="font-medium">Supplier:</span> <span class="ml-1">{{ form.supplier }}</span></div>
          <div><span class="font-medium">Breed:</span> <span class="ml-1">{{ form.breed }}</span></div>
          <div><span class="font-medium">Transport:</span> <span class="ml-1">{{ form.transport }}</span></div>
          <div><span class="font-medium">Note:</span> <span class="ml-1">{{ form.rnote }}</span></div>
          <div><span class="font-medium">Challan Box:</span> <span class="ml-1">{{ form.challan_box }}</span></div>
          <div><span class="font-medium">Gross Weight:</span> <span class="ml-1">{{ form.gross_weight }}</span></div>
          <div><span class="font-medium">Net Weight:</span> <span class="ml-1">{{ form.net_weight }}</span></div>
          <div><span class="font-medium">Female Chicks:</span> <span class="ml-1">{{ form.female_chicks }}</span></div>
          <div><span class="font-medium">Male Chicks:</span> <span class="ml-1">{{ form.male_chicks }}</span></div>
          <div><span class="font-medium">Total Chicks:</span> <span class="ml-1">{{ form.total_chicks }}</span></div>
        </div>
      </transition>
    </div>

    <!-- Company & Boxes -->
    <div class="border rounded-lg p-4 shadow-sm bg-white">
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
        <div><Label>Female Box Qty</Label><Input v-model.number="form.firm_female_box_qty" type="number" class="mt-1" /></div>
        <div><Label>Male Box Qty</Label><Input v-model.number="form.firm_male_box_qty" type="number" class="mt-1" /></div>
        <div><Label>Total Box Qty</Label><Input v-model.number="form.firm_total_box_qty" type="number" readonly class="mt-1 bg-gray-100 text-gray-700 cursor-not-allowed" /></div>
      </div>
      <div class="grid grid-cols-3 gap-4 mt-5">
        <!-- Sortage Boxes -->
        <div>
          <Label>Sortage Male Box</Label>
          <Input type="number" v-model.number="form.firm_sortage_male_box" class="mt-1" />
        </div>
        <div>
          <Label>Sortage Female Box</Label>
          <Input type="number" v-model.number="form.firm_sortage_female_box" class="mt-1" />
        </div>
        <div>
          <Label>Sortage Total Box</Label>
          <Input 
            type="number" 
            :value="Number(form.firm_sortage_male_box || 0) + Number(form.firm_sortage_female_box || 0)" 
            readonly 
            class="mt-1 bg-gray-100 text-gray-700 cursor-not-allowed"
          />
        </div>

        <!-- Excess Boxes -->
        <div>
          <Label>Excess Male Box</Label>
          <Input type="number" v-model.number="form.firm_excess_male_box" class="mt-1" />
        </div>
        <div>
          <Label>Excess Female Box</Label>
          <Input type="number" v-model.number="form.firm_excess_female_box" class="mt-1" />
        </div>
        <div>
          <Label>Excess Total Box</Label>
          <Input 
            type="number" 
            :value="Number(form.firm_excess_male_box || 0) + Number(form.firm_excess_female_box || 0)" 
            readonly 
            class="mt-1 bg-gray-100 text-gray-700 cursor-not-allowed"
          />
        </div>
      </div>
    </div>

    <!-- Lab Test -->
    <div v-if="labInput" class="border rounded-lg p-4 shadow-sm mt-4 bg-white">
      <h2 class="font-semibold text-lg mb-4">Lab Test Send Info</h2>
      <div class="grid grid-cols-4 gap-4">
        <div class="flex flex-col"><Label>Lab Type</Label><select v-model="form.lab_type" class="mt-1 border rounded px-3 py-2 w-full"><option value="">Select Lab</option><option value="1">Gov Lab</option><option value="2">Provita Lab</option></select></div>
        <div class="flex flex-col"><Label>Send Female Chicks Qty</Label><Input v-model.number="form.send_female_qty" type="number" class="mt-1" /></div>
        <div class="flex flex-col"><Label>Send Male Chicks Qty</Label><Input v-model.number="form.send_male_qty" type="number" class="mt-1" /></div>
        <div class="flex flex-col"><Label>Total Box Qty</Label><Input v-model.number="form.send_total_qty" type="number" readonly class="mt-1 bg-gray-100 text-gray-700 cursor-not-allowed" /></div>
      </div>
    </div>

    <!-- Notes -->
    <div class="border rounded-lg p-4 shadow-sm bg-white">
      <h2 class="font-semibold text-lg mb-4">Notes</h2>
      <textarea v-model="form.remarks" class="w-full border rounded px-3 py-2" rows="3" placeholder="Write notes here..."></textarea>
    </div>

    <!-- Submit -->
    <div class="flex justify-end">
      <Button type="submit" class="px-6 py-2">Save & Submit</Button>
    </div>

    <!-- Flock Modal -->
    <div v-if="showFlockModal" class="fixed inset-0 z-50 flex justify-center pt-6" @click.self="resetFlockForm">
      <div ref="flockModalRef" class="bg-white rounded-lg border border-gray-300 shadow-lg w-full max-w-md" style="top:100px;position:absolute;">
        <div class="flex items-center justify-between p-4 border-b border-gray-200 cursor-move" @mousedown="startDragFlock">
          <h3 class="text-xl font-semibold text-gray-900">Add New Flock</h3>
          <button type="button" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg w-8 h-8 flex justify-center items-center" @click="resetFlockForm">✕</button>
        </div>
        <div class="p-4 space-y-4">
          <div><Label>Name</Label><Input v-model="flockForm.name" placeholder="Flock Name" /></div>
          <div><Label>Parent Flock</Label><select v-model="flockForm.parent_flock_id" class="w-full border rounded px-3 py-2"><option value="">Select Parent</option><option v-for="flock in props.flocks" :key="flock.id" :value="flock.id">{{ flock.name }}</option></select></div>
          <div v-if="flockFormError" class="text-red-600 text-sm">{{ flockFormError }}</div>
        </div>
        <div class="flex justify-end p-4 border-t border-gray-200">
          <Button class="bg-gray-300 text-black mr-2" @click="resetFlockForm">Cancel</Button>
          <Button class="bg-chicken text-white" @click.prevent="addNewFlock">Save</Button>
        </div>
      </div>
    </div>

  </form>
</AppLayout>
</template>
