<script setup lang="ts">
import { Link, Head, useForm } from '@inertiajs/vue3'
import { ref, watch, computed, onMounted, onBeforeUnmount,reactive } from 'vue'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { 
    ArrowLeft, 
    Package, 
    Building2, 
    Users, 
    Plus, 
    Save, 
    Info, 
    ChevronDown,
    Search,
    X,
    AlertCircle,
    CheckCircle2
} from 'lucide-vue-next'

// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Parent Stock', href: '/ps-firm-receive' },
  { title: 'Firm Receive', href: '' },
]

// Props
const props = defineProps<{
  psReceives: Array<any>
  flocks: Array<any>
  breeds: Object
  companies: Array<any>
}>()

function getBreedNames(ids) {
  if (!ids || ids.length === 0) return ''
  return ids.map(id => props.breeds[id] || '').filter(Boolean).join(', ')
}

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
const shipmentTypes = {1: 'Local', 2: 'Foreign'}
const suppliers = {1: 'PBL', 2: 'PCL'} 
const breeds = {1: 'Broiler', 2: 'Layer'}
const transports = {1: 'Freezing Microbus', 2: 'Freezing Van',3:'Open Truck'}

// Modern dropdown states
const showPsDropdown = ref(false)
const showFlockDropdownList = ref(false)
const psSearchQuery = ref('')
const flockSearchQuery = ref('')

// Filtered options
const filteredPsReceives = computed(() => {
    if (!psSearchQuery.value) return props.psReceives
    return props.psReceives.filter(ps => 
        ps.pi_no.toLowerCase().includes(psSearchQuery.value.toLowerCase())
    )
})

const filteredFlocks = computed(() => {
    if (!flockSearchQuery.value) return props.flocks
    return props.flocks.filter(flock => 
        flock.name.toLowerCase().includes(flockSearchQuery.value.toLowerCase())
    )
})

// Selected items display
const selectedPs = computed(() => {
    return props.psReceives.find(ps => ps.id === Number(selectedPsId.value))
})

const selectedFlock = computed(() => {
    return props.flocks.find(flock => flock.id === Number(selectedFlockId.value))
})

// Close dropdowns on outside click
const handleClickOutside = (e: MouseEvent) => {
    if (!(e.target as HTMLElement).closest('.ps-dropdown, .flock-dropdown, .flock-modal')) {
        showPsDropdown.value = false
        showFlockDropdownList.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside)
})

// Debug function to check data
const debugDropdown = () => {
    console.log('PS Receives data:', props.psReceives)
    console.log('Filtered PS Receives:', filteredPsReceives.value)
    console.log('Show PS Dropdown state:', showPsDropdown.value)
}

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

const displayInfo = reactive({
  shipment_type: '',
  pi_no: '',
  pi_date: '',
  order_no: '',
  order_date: '',
  lc_no: '',
  lc_date: '',
  receive_type: '',
  breed: '',
  transport: '',
  rnote: '',
  challan_box: 0,
  gross_weight: 0,
  net_weight: 0,
  female_chicks: 0,
  male_chicks: 0,
  female_chicks_box: 0,
  male_chicks_box:0,
  female_box_qty:0,
  
})

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
  form.receiving_company_id= selected.company_id;
  Object.assign(displayInfo, {
    shipment_type: shipmentTypes[selected.shipment_type_id] || '',
    pi_no: selected.pi_no || '',
    pi_date: selected.pi_date || '',
    order_no: selected.order_no || '',
    order_date: selected.order_date || '',
    lc_no: selected.lc_no || '',
    lc_date: selected.lc_date || '',
    receive_type:selected.receive_type || '',
    breed: getBreedNames(selected.breed_type) || '',
    transport: transports[selected.transport_type] || '',
    rnote: selected.remarks || '',
    challan_box: selected.total_box_qty || 0,
    gross_weight: selected.gross_weight || 0,
    net_weight: selected.net_weight || 0,
    female_chicks_box:selected.female_box_qty|| 0,
    male_chicks_box:selected.male_box_qty|| 0,
    female_chicks: selected.female_chicks || 0,
    male_chicks: selected.male_chicks || 0,
    total_chicks: selected.total_chicks_qty || 0,
    
  })

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

  <!-- Header Section -->
  <div class="mb-8">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create Farm Receive</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Add a new parent stock farm receive record</p>
      </div>
        <Link 
          href="/ps-firm-receive" 
        class="group relative overflow-hidden rounded-xl px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl"
        style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);"
      >
        <span class="relative z-10 flex items-center gap-2">
          <ArrowLeft class="h-4 w-4" />
          Back to List
        </span>
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-20 group-hover:translate-x-full"></div>
        </Link>
    </div>
      </div>

  <form @submit.prevent="submit" class="space-y-8">
        
    <!-- Parent Stock Selection Card -->
    <div class="relative rounded-2xl border-0 bg-gradient-to-br from-white via-gray-50 to-white p-8 shadow-xl ring-1 ring-gray-200 dark:from-gray-800 dark:via-gray-900 dark:to-gray-800 dark:ring-gray-700">
      <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-blue-500/20 to-purple-500/20"></div>
      <div class="absolute -bottom-4 -left-4 h-32 w-32 rounded-full bg-gradient-to-br from-emerald-500/10 to-blue-500/10"></div>
        
      <div class="relative">
        <div class="mb-8 flex items-center gap-3">
          <div class="rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 p-3 shadow-lg">
            <Package class="h-6 w-6 text-white" />
          </div>
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Parent Stock Information</h2>
            <p class="text-gray-600 dark:text-gray-400">Select PS receive and flock details</p>
          </div>
        </div>

        <!-- Data Status Indicator -->
        <div class="mb-4 flex items-center justify-between rounded-lg border border-green-200 bg-green-50 p-3 dark:border-green-800 dark:bg-green-900/20">
          <div class="flex items-center gap-2 text-sm">
            <div class="h-2 w-2 rounded-full bg-green-500"></div>
            <span class="font-medium text-green-800 dark:text-green-200">
              Data Status: {{ props.psReceives?.length || 0 }} PS Receives | {{ props.flocks?.length || 0 }} Flocks loaded
            </span>
          </div>
          <button 
            type="button"
            @click="debugDropdown" 
            class="rounded bg-green-600 px-3 py-1 text-xs text-white hover:bg-green-700"
          >
            Debug Console
          </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          
          <!-- PS Receive Dropdown -->
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
              <Info class="h-4 w-4" />
              PS Receive Number
            </Label>
            <div class="ps-dropdown relative">
              <button
                type="button"
                @click.stop="showPsDropdown = !showPsDropdown"
                class="flex w-full items-center justify-between rounded-xl border border-gray-300 bg-white px-4 py-3 shadow-sm transition-all duration-200 hover:border-blue-500 hover:shadow-md focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <span class="flex items-center gap-3">
                  <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                  {{ selectedPs ? selectedPs.pi_no : 'Select PS Receive Number' }}
                </span>
                <ChevronDown class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showPsDropdown }" />
              </button>
              
              <!-- PS Receive Dropdown -->
              <div 
                v-if="showPsDropdown" 
                class="fixed inset-0 z-[9999] flex items-start justify-center pt-20"
                @click="showPsDropdown = false"
              >
                <div 
                  class="w-full max-w-md rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-600 dark:bg-gray-800"
                  @click.stop
                >
                  <!-- Header -->
                  <div class="border-b border-gray-200 p-4 dark:border-gray-600">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Select PS Receive</h3>
                    <div class="relative mt-3">
                      <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                      <input
                        v-model="psSearchQuery"
                        type="text"
                        placeholder="Search PS numbers..."
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 pl-10 pr-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        @click.stop
                      />
                    </div>
                  </div>

                  <!-- PS Receive List -->
                  <div class="max-h-96 overflow-y-auto">
                    <div v-if="(props.psReceives?.length || 0) === 0" class="px-6 py-8 text-center">
                      <AlertCircle class="mx-auto h-8 w-8 text-red-500" />
                      <div class="mt-2 font-medium text-red-600">No PS Receives Available</div>
                      <div class="text-sm text-gray-500">Please add PS receives first</div>
                    </div>
                    <button
                      v-for="ps in filteredPsReceives"
                      :key="ps.id"
                      type="button"
                      @click.stop="selectedPsId = ps.id; showPsDropdown = false; toggleInfo()"
                      class="flex w-full items-center gap-4 px-6 py-4 text-left hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors duration-200 border-b border-gray-100 dark:border-gray-600 last:border-b-0"
                      :class="{ 'bg-blue-100 dark:bg-blue-900': selectedPsId == ps.id }"
                    >
                      <div class="h-3 w-3 rounded-full bg-blue-500 flex-shrink-0"></div>
                      <div class="flex-1">
                        <div class="font-semibold text-gray-900 dark:text-white">{{ ps.pi_no }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Date: {{ ps.pi_date || 'N/A' }}</div>
                        <div class="text-xs text-gray-400 dark:text-gray-500">Order: {{ ps.order_no || 'N/A' }}</div>
                      </div>
                      <CheckCircle2 v-if="selectedPsId == ps.id" class="h-4 w-4 text-blue-500 flex-shrink-0" />
                    </button>
                    <div v-if="filteredPsReceives.length === 0 && (props.psReceives?.length || 0) > 0" class="px-6 py-8 text-center text-gray-500">
                      <Search class="mx-auto h-6 w-6 text-gray-400" />
                      <div class="mt-2 text-sm">No results found for "{{ psSearchQuery }}"</div>
                    </div>
                  </div>

                  <!-- Close Button -->
                  <div class="border-t border-gray-200 p-4 dark:border-gray-600">
                    <Button 
                      type="button"
                      @click="showPsDropdown = false"
                      class="w-full rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                    >
                      Close
                    </Button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Flock Dropdown -->
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
              <Users class="h-4 w-4" />
              Flock Selection
            </Label>
            <div class="flex gap-3">
              <div class="flock-dropdown relative flex-1">
                <button
                  type="button"
                  @click.stop="showFlockDropdownList = !showFlockDropdownList"
                  class="flex w-full items-center justify-between rounded-xl border border-gray-300 bg-white px-4 py-3 shadow-sm transition-all duration-200 hover:border-emerald-500 hover:shadow-md focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                >
                  <span class="flex items-center gap-3">
                    <div class="h-2 w-2 rounded-full bg-emerald-500"></div>
                    {{ selectedFlock ? selectedFlock.name : 'Select Flock' }}
                  </span>
                  <ChevronDown class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showFlockDropdownList }" />
                </button>
                
                <!-- Flock Dropdown Overlay -->
                <div 
                  v-if="showFlockDropdownList" 
                  class="fixed inset-0 z-[9998] flex items-start justify-center pt-20"
                  @click="showFlockDropdownList = false"
                >
                  <div 
                    class="w-full max-w-md rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-600 dark:bg-gray-800"
                    @click.stop
                  >
                    <!-- Header -->
                    <div class="border-b border-gray-200 p-4 dark:border-gray-600">
                      <h3 class="font-semibold text-gray-900 dark:text-white">Select Flock</h3>
                      <div class="relative mt-3">
                        <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                        <input
                          v-model="flockSearchQuery"
                          type="text"
                          placeholder="Search flocks..."
                          class="w-full rounded-lg border border-gray-300 bg-gray-50 pl-10 pr-4 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                          @click.stop
                        />
                      </div>
                    </div>

                    <!-- Flock List -->
                    <div class="max-h-96 overflow-y-auto">
                      <div v-if="(props.flocks?.length || 0) === 0" class="px-6 py-8 text-center">
                        <AlertCircle class="mx-auto h-8 w-8 text-red-500" />
                        <div class="mt-2 font-medium text-red-600">No Flocks Available</div>
                        <div class="text-sm text-gray-500">Please add flocks first</div>
                      </div>
                      <button
                        v-for="flock in filteredFlocks"
                        :key="flock.id"
                        type="button"
                        @click.stop="selectedFlockId = flock.id; showFlockDropdownList = false"
                        class="flex w-full items-center gap-4 px-6 py-4 text-left hover:bg-emerald-50 dark:hover:bg-gray-700 transition-colors duration-200 border-b border-gray-100 dark:border-gray-600 last:border-b-0"
                        :class="{ 'bg-emerald-100 dark:bg-emerald-900': selectedFlockId == flock.id }"
                      >
                        <div class="h-3 w-3 rounded-full bg-emerald-500 flex-shrink-0"></div>
                        <div class="flex-1">
                          <div class="font-semibold text-gray-900 dark:text-white">{{ flock.name }}</div>
                          <div class="text-sm text-gray-500 dark:text-gray-400">ID: {{ flock.id }}</div>
                        </div>
                        <CheckCircle2 v-if="selectedFlockId == flock.id" class="h-4 w-4 text-emerald-500 flex-shrink-0" />
                      </button>
                      <div v-if="filteredFlocks.length === 0 && (props.flocks?.length || 0) > 0" class="px-6 py-8 text-center text-gray-500">
                        <Search class="mx-auto h-6 w-6 text-gray-400" />
                        <div class="mt-2 text-sm">No results found for "{{ flockSearchQuery }}"</div>
                      </div>
                    </div>

                    <!-- Close Button -->
                    <div class="border-t border-gray-200 p-4 dark:border-gray-600">
                      <Button 
                        type="button"
                        @click="showFlockDropdownList = false"
                        class="w-full rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                      >
                        Close
                      </Button>
                    </div>
                  </div>
                </div>
              </div>
              
              <button
                type="button"
                @click.stop="showFlockModal = true"
                class="group relative overflow-hidden rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 px-4 py-3 text-white shadow-lg transition-all duration-300 hover:from-emerald-600 hover:to-emerald-700 hover:shadow-xl"
              >
                <span class="relative z-10 flex items-center gap-2">
                  <Plus class="h-4 w-4" />
                  Add
                </span>
              </button>
            </div>
          </div>
        </div>

        <!-- Lab Message -->
        <div v-if="labMessage" class="mt-6 flex items-start gap-3 rounded-xl border p-4"
          :class="messageType === 'error' 
            ? 'border-red-200 bg-red-50 text-red-800 dark:border-red-800 dark:bg-red-900/20 dark:text-red-200' 
            : 'border-blue-200 bg-blue-50 text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-200'"
        >
          <component 
            :is="messageType === 'error' ? AlertCircle : Info" 
            class="h-5 w-5 mt-0.5 flex-shrink-0" 
          />
          <p class="font-medium">{{ labMessage }}</p>
        </div>

        <!-- PS Info Display -->
        <transition 
          enter-active-class="transition-all duration-500 ease-out"
          leave-active-class="transition-all duration-500 ease-in"
          enter-from-class="opacity-0 scale-95 -translate-y-4"
          enter-to-class="opacity-100 scale-100 translate-y-0"
          leave-from-class="opacity-100 scale-100 translate-y-0"
          leave-to-class="opacity-0 scale-95 -translate-y-4"
        >
          <div v-if="showInfo" class="mt-8 rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50 to-indigo-50 p-6 dark:border-blue-800 dark:from-blue-900/20 dark:to-indigo-900/20">
            <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-blue-900 dark:text-blue-100">
              <CheckCircle2 class="h-5 w-5" />
              Parent Stock Details
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Shipment Type:</span><div class="text-gray-900 dark:text-gray-100">{{ displayInfo.shipment_type }}</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">PI No:</span><div class="text-gray-900 dark:text-gray-100">{{ displayInfo.pi_no }}</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">LC No:</span><div class="text-gray-900 dark:text-gray-100">{{ displayInfo.lc_no }}</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Order No:</span><div class="text-gray-900 dark:text-gray-100">{{ displayInfo.order_no }}</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Receive Type:</span><div class="text-gray-900 dark:text-gray-100">{{ displayInfo.receive_type }}</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Breed:</span><div class="text-gray-900 dark:text-gray-100">{{ displayInfo.breed }}</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Transport:</span><div class="text-gray-900 dark:text-gray-100">{{ displayInfo.transport }}</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Challan Box:</span><div class="text-gray-900 dark:text-gray-100">{{ displayInfo.challan_box }}</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Female Chicks Box:</span><div class="text-gray-900 dark:text-gray-100">{{ displayInfo.female_chicks_box }}</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Male Chicks Box:</span><div class="text-gray-900 dark:text-gray-100">{{ displayInfo.male_chicks_box }}</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Gross Weight:</span><div class="text-gray-900 dark:text-gray-100">{{ displayInfo.gross_weight }} kg</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Net Weight:</span><div class="text-gray-900 dark:text-gray-100">{{ displayInfo.net_weight }} kg</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Female Chicks:</span><div class="text-gray-900 dark:text-gray-100">{{ displayInfo.female_chicks }}</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Male Chicks:</span><div class="text-gray-900 dark:text-gray-100">{{ displayInfo.male_chicks }}</div></div>
              
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Total Chicks:</span><div class="font-bold text-blue-900 dark:text-blue-100">{{ displayInfo.total_chicks }}</div></div>
      </div>
        </div>
      </transition>
      </div>
    </div>

    <!-- Company & Boxes Card -->
    <div class="relative overflow-hidden rounded-2xl border-0 bg-gradient-to-br from-white via-orange-50 to-white p-8 shadow-xl ring-1 ring-gray-200 dark:from-gray-800 dark:via-orange-900/20 dark:to-gray-800 dark:ring-gray-700">
      <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-orange-500/20 to-red-500/20"></div>
      <div class="absolute -bottom-4 -left-4 h-32 w-32 rounded-full bg-gradient-to-br from-yellow-500/10 to-orange-500/10"></div>
      
      <div class="relative">
        <div class="mb-8 flex items-center gap-3">
          <div class="rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 p-3 shadow-lg">
            <Building2 class="h-6 w-6 text-white" />
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Receiving Company & Boxes</h2>
            <p class="text-gray-600 dark:text-gray-400">Enter box quantities and receiving details</p>
          </div>
        </div>

        <!-- Receiving Company -->
        <div class="mb-8 space-y-2">
          <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
            <Building2 class="h-4 w-4" />
            Receiving Company
          </Label>
          <select 
            v-model="form.receiving_company_id" 
            class="w-full rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 text-gray-600 shadow-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 cursor-not-allowed"
            disabled
          >
          <option value="0">Select Company</option>
          <option 
            v-for="company in companies" 
            :key="company.id" 
            :value="company.id"
          >
            {{ company.name }}
          </option>
        </select>
      </div>

        <!-- Main Box Quantities -->
        <div class="mb-8">
          <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Main Box Quantities</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="space-y-2">
              <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Female Box Qty</Label>
              <Input 
                v-model.number="form.firm_female_box_qty" 
                type="number" 
                min="0"
                class="rounded-xl border-pink-300 bg-pink-50 px-4 py-3 shadow-sm focus:border-pink-500 focus:ring-pink-500/20 dark:border-pink-600 dark:bg-pink-900/20" 
              />
            </div>
            <div class="space-y-2">
              <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Male Box Qty</Label>
              <Input 
                v-model.number="form.firm_male_box_qty" 
                type="number" 
                min="0"
                class="rounded-xl border-blue-300 bg-blue-50 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-blue-500/20 dark:border-blue-600 dark:bg-blue-900/20" 
              />
            </div>
            <div class="space-y-2">
              <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Total Box Qty</Label>
              <Input 
                v-model.number="form.firm_total_box_qty" 
                type="number" 
                readonly 
                class="rounded-xl border-gray-300 bg-gradient-to-r from-gray-100 to-gray-50 px-4 py-3 font-bold text-gray-700 shadow-sm cursor-not-allowed dark:border-gray-600 dark:from-gray-700 dark:to-gray-800 dark:text-gray-300" 
              />
            </div>
      </div>
        </div>

        <!-- Shortage & Excess Quantities -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Shortage Boxes -->
          <div class="rounded-xl border border-red-200 bg-gradient-to-br from-red-50 to-pink-50 p-6 dark:border-red-800 dark:from-red-900/20 dark:to-pink-900/20">
            <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-red-800 dark:text-red-200">
              <AlertCircle class="h-5 w-5" />
              Shortage Boxes
            </h3>
            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label class="text-sm font-semibold text-red-700 dark:text-red-300">Male Shortage</Label>
                  <Input 
                    type="number" 
                    v-model.number="form.firm_sortage_male_box" 
                    min="0"
                    class="rounded-xl border-red-300 bg-red-50 px-4 py-2 text-red-800 focus:border-red-500 focus:ring-red-500/20 dark:border-red-600 dark:bg-red-900/30 dark:text-red-200" 
                  />
                </div>
                <div class="space-y-2">
                  <Label class="text-sm font-semibold text-red-700 dark:text-red-300">Female Shortage</Label>
                  <Input 
                    type="number" 
                    v-model.number="form.firm_sortage_female_box" 
                    min="0"
                    class="rounded-xl border-red-300 bg-red-50 px-4 py-2 text-red-800 focus:border-red-500 focus:ring-red-500/20 dark:border-red-600 dark:bg-red-900/30 dark:text-red-200" 
                  />
                </div>
        </div>
              <div class="space-y-2">
                <Label class="text-sm font-semibold text-red-700 dark:text-red-300">Total Shortage</Label>
          <Input 
            type="number" 
            :value="Number(form.firm_sortage_male_box || 0) + Number(form.firm_sortage_female_box || 0)" 
            readonly 
                  class="rounded-xl border-red-300 bg-gradient-to-r from-red-100 to-red-50 px-4 py-2 font-bold text-red-800 cursor-not-allowed dark:border-red-600 dark:from-red-800/50 dark:to-red-900/50 dark:text-red-200"
          />
              </div>
            </div>
        </div>

        <!-- Excess Boxes -->
          <div class="rounded-xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-green-50 p-6 dark:border-emerald-800 dark:from-emerald-900/20 dark:to-green-900/20">
            <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-emerald-800 dark:text-emerald-200">
              <Plus class="h-5 w-5" />
              Excess Boxes
            </h3>
            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Male Excess</Label>
                  <Input 
                    type="number" 
                    v-model.number="form.firm_excess_male_box" 
                    min="0"
                    class="rounded-xl border-emerald-300 bg-emerald-50 px-4 py-2 text-emerald-800 focus:border-emerald-500 focus:ring-emerald-500/20 dark:border-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-200" 
                  />
                </div>
                <div class="space-y-2">
                  <Label class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Female Excess</Label>
                  <Input 
                    type="number" 
                    v-model.number="form.firm_excess_female_box" 
                    min="0"
                    class="rounded-xl border-emerald-300 bg-emerald-50 px-4 py-2 text-emerald-800 focus:border-emerald-500 focus:ring-emerald-500/20 dark:border-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-200" 
                  />
        </div>
        </div>
              <div class="space-y-2">
                <Label class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Total Excess</Label>
          <Input 
            type="number" 
            :value="Number(form.firm_excess_male_box || 0) + Number(form.firm_excess_female_box || 0)" 
            readonly 
                  class="rounded-xl border-emerald-300 bg-gradient-to-r from-emerald-100 to-emerald-50 px-4 py-2 font-bold text-emerald-800 cursor-not-allowed dark:border-emerald-600 dark:from-emerald-800/50 dark:to-emerald-900/50 dark:text-emerald-200"
          />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Lab Test Section -->
    <div v-if="labInput" class="relative overflow-hidden rounded-2xl border-0 bg-gradient-to-br from-white via-purple-50 to-white p-8 shadow-xl ring-1 ring-gray-200 dark:from-gray-800 dark:via-purple-900/20 dark:to-gray-800 dark:ring-gray-700">
      <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-purple-500/20 to-pink-500/20"></div>
      <div class="absolute -bottom-4 -left-4 h-32 w-32 rounded-full bg-gradient-to-br from-indigo-500/10 to-purple-500/10"></div>
      
      <div class="relative">
        <div class="mb-8 flex items-center gap-3">
          <div class="rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 p-3 shadow-lg">
            <Search class="h-6 w-6 text-white" />
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Lab Test Send Information</h2>
            <p class="text-gray-600 dark:text-gray-400">Configure lab testing parameters</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Lab Type</Label>
            <select 
              v-model="form.lab_type" 
              class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 shadow-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
              <option value="">Select Lab</option>
              <option value="1">Gov Lab</option>
              <option value="2">Provita Lab</option>
            </select>
          </div>
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Send Female Qty</Label>
            <Input 
              v-model.number="form.send_female_qty" 
              type="number" 
              min="0"
              class="rounded-xl border-pink-300 bg-pink-50 px-4 py-3 shadow-sm focus:border-pink-500 focus:ring-pink-500/20 dark:border-pink-600 dark:bg-pink-900/20" 
            />
          </div>
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Send Male Qty</Label>
            <Input 
              v-model.number="form.send_male_qty" 
              type="number" 
              min="0"
              class="rounded-xl border-blue-300 bg-blue-50 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-blue-500/20 dark:border-blue-600 dark:bg-blue-900/20" 
            />
          </div>
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Total Qty</Label>
            <Input 
              v-model.number="form.send_total_qty" 
              type="number" 
              readonly 
              class="rounded-xl border-gray-300 bg-gradient-to-r from-gray-100 to-gray-50 px-4 py-3 font-bold text-gray-700 shadow-sm cursor-not-allowed dark:border-gray-600 dark:from-gray-700 dark:to-gray-800 dark:text-gray-300" 
            />
          </div>
        </div>
      </div>
    </div>

    <!-- Notes Section -->
    <div class="relative overflow-hidden rounded-2xl border-0 bg-gradient-to-br from-white via-amber-50 to-white p-8 shadow-xl ring-1 ring-gray-200 dark:from-gray-800 dark:via-amber-900/20 dark:to-gray-800 dark:ring-gray-700">
      <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-amber-500/20 to-orange-500/20"></div>
      <div class="absolute -bottom-4 -left-4 h-32 w-32 rounded-full bg-gradient-to-br from-yellow-500/10 to-amber-500/10"></div>
      
      <div class="relative">
        <div class="mb-6 flex items-center gap-3">
          <div class="rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 p-3 shadow-lg">
            <Info class="h-6 w-6 text-white" />
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Additional Notes</h2>
            <p class="text-gray-600 dark:text-gray-400">Add any relevant remarks or observations</p>
          </div>
        </div>

        <div class="space-y-2">
          <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Remarks</Label>
          <textarea 
            v-model="form.remarks" 
            class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white resize-none transition-all duration-200" 
            rows="4" 
            placeholder="Write your notes here..."
          ></textarea>
        </div>
      </div>
    </div>

    <!-- Submit Section -->
    <div class="flex items-center justify-end gap-4 rounded-2xl bg-gradient-to-r from-gray-50 to-white p-6 dark:from-gray-800 dark:to-gray-900">
      <Link 
        href="/ps-firm-receive"
        class="rounded-xl border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
      >
        Cancel
      </Link>
      <Button 
        type="submit" 
        :disabled="form.processing"
        class="group relative overflow-hidden rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-3 font-semibold text-white shadow-lg transition-all duration-300 hover:from-blue-700 hover:to-blue-800 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50 disabled:opacity-50"
      >
        <span class="relative z-10 flex items-center gap-2">
          <Save class="h-4 w-4" />
          {{ form.processing ? 'Saving...' : 'Save & Submit' }}
        </span>
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-20 group-hover:translate-x-full"></div>
      </Button>
    </div>

    <!-- Modern Flock Modal -->
    <div v-if="showFlockModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="resetFlockForm">
      <div 
        ref="flockModalRef" 
        class="flock-modal relative w-full max-w-lg transform rounded-2xl bg-white shadow-2xl ring-1 ring-gray-200 transition-all duration-300 dark:bg-gray-800 dark:ring-gray-700"
      >
        <!-- Modal Header -->
        <div 
          class="flex cursor-move items-center justify-between rounded-t-2xl bg-gradient-to-r from-emerald-500 to-emerald-600 px-6 py-4 text-white" 
          @mousedown="startDragFlock"
        >
          <div class="flex items-center gap-3">
            <div class="rounded-lg bg-white/20 p-2">
              <Users class="h-5 w-5" />
            </div>
            <div>
              <h3 class="text-lg font-bold">Add New Flock</h3>
              <p class="text-sm text-emerald-100">Create a new flock record</p>
            </div>
          </div>
          <button 
            type="button" 
            @click="resetFlockForm"
            class="rounded-lg bg-white/20 p-1 text-white hover:bg-white/30 focus:outline-none focus:ring-2 focus:ring-white/50"
          >
            <X class="h-4 w-4" />
          </button>
        </div>

        <!-- Modal Content -->
        <div class="space-y-6 p-6">
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
              <Users class="h-4 w-4" />
              Flock Name
            </Label>
            <Input 
              v-model="flockForm.name" 
              placeholder="Enter flock name..." 
              class="rounded-xl border-gray-300 bg-white px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-emerald-500/20 dark:border-gray-600 dark:bg-gray-700" 
            />
            <InputError :message="flockFormError" />
          </div>
          
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
              <Package class="h-4 w-4" />
              Parent Flock
            </Label>
            <select 
              v-model="flockForm.parent_flock_id" 
              class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
              <option value="">Select Parent Flock</option>
              <option v-for="flock in props.flocks" :key="flock.id" :value="flock.id">{{ flock.name }}</option>
            </select>
          </div>
        </div>

        <!-- Modal Actions -->
        <div class="flex items-center justify-end gap-3 rounded-b-2xl border-t border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-600 dark:bg-gray-700">
          <Button 
            type="button"
            @click="resetFlockForm" 
            class="rounded-xl border border-gray-300 bg-white px-6 py-2 font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
          >
            Cancel
          </Button>
          <Button 
            type="button"
            @click="addNewFlock" 
            :disabled="flockForm.processing || !flockForm.name"
            class="group relative overflow-hidden rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 px-6 py-2 font-semibold text-white shadow-lg transition-all duration-300 hover:from-emerald-700 hover:to-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500/50 disabled:opacity-50"
          >
            <span class="relative z-10 flex items-center gap-2">
              <Save class="h-4 w-4" />
              {{ flockForm.processing ? 'Saving...' : 'Save Flock' }}
            </span>
          </Button>
        </div>
      </div>
    </div>

  </form>
</AppLayout>
</template>
