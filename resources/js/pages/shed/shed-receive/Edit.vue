<script setup lang="ts">
import { Link, Head, useForm } from '@inertiajs/vue3'
import { ref, watch, computed, onMounted, onBeforeUnmount } from 'vue'
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
    Home,
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
  { title: 'Shed Receive', href: '/shed-receive' },
  { title: 'Edit', href: '' },
]

// Props
const props = defineProps<{
  shedReceive: any // The record being edited
  firmReceives: Array<any> // Batches generated from firm receive
  flocks: Array<any>
  companies: Array<any>
  sheds: Array<any>
}>()

// Form state
const selectTransactionid = ref<number | string>(props.shedReceive?.receive_id || '')
const selectedFlockId = ref<number | string>(props.shedReceive?.flock_id || '')
const selectedShedid = ref<number | string>(props.shedReceive?.shed_id || '')

const showInfo = ref(!!props.shedReceive?.receive_id)

// Modern dropdown states
const showFirmReceiveDropdown = ref(false)
const showFlockDropdown = ref(false) 
const showShedDropdown = ref(false)
const firmReceiveSearchQuery = ref('')
const flockSearchQuery = ref('')
const shedSearchQuery = ref('')

// Filtered options
const filteredFirmReceives = computed(() => {
    if (!firmReceiveSearchQuery.value) return props.firmReceives
    return props.firmReceives.filter(fr => {
        const searchTerm = firmReceiveSearchQuery.value.toLowerCase()
        const formattedId = `rcv-${String(fr.id).padStart(6, '0')}`
        const flockCode = fr.flock_code || props.flocks.find(f => f.id === fr.flock_id)?.code || fr.flock_name
        const fullFormat = `rcv-${String(fr.id).padStart(6, '0')}-${fr.company_short_name}-${fr.project_name}-${flockCode}`
        
        return fr.transaction_no?.toLowerCase().includes(searchTerm) ||
               fr.job_no?.toLowerCase().includes(searchTerm) ||
               fr.flock_name?.toLowerCase().includes(searchTerm) ||
               fr.flock_code?.toLowerCase().includes(searchTerm) ||
               fr.company_name?.toLowerCase().includes(searchTerm) ||
               fr.company_short_name?.toLowerCase().includes(searchTerm) ||
               fr.project_name?.toLowerCase().includes(searchTerm) ||
               fr.id.toString().includes(searchTerm) ||
               formattedId.includes(searchTerm) ||
               fullFormat.includes(searchTerm)
    })
})

const filteredFlocks = computed(() => {
    if (!flockSearchQuery.value) return props.flocks
    return props.flocks.filter(flock => 
        flock.code.toLowerCase().includes(flockSearchQuery.value.toLowerCase()) ||
        flock.name.toLowerCase().includes(flockSearchQuery.value.toLowerCase())
    )
})

const filteredSheds = computed(() => {
    if (!shedSearchQuery.value) return props.sheds
    return props.sheds.filter(shed => 
        shed.name.toLowerCase().includes(shedSearchQuery.value.toLowerCase())
    )
})

// Selected items display
const selectedFirmReceive = computed(() => {
    return props.firmReceives.find(fr => fr.id === Number(selectTransactionid.value))
})

const selectedFlock = computed(() => {
    return props.flocks.find(flock => flock.id === Number(selectedFlockId.value))
})

const selectedShed = computed(() => {
    return props.sheds.find(shed => shed.id === Number(selectedShedid.value))
})

// Close dropdowns on outside click
const handleClickOutside = (e: MouseEvent) => {
    if (!(e.target as HTMLElement).closest('.firm-receive-dropdown, .flock-dropdown, .shed-dropdown')) {
        showFirmReceiveDropdown.value = false
        showFlockDropdown.value = false
        showShedDropdown.value = false
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
    console.log('Firm Receives data:', props.firmReceives)
    console.log('Flocks data:', props.flocks)
    console.log('Sheds data:', props.sheds)
    console.log('Shed Receive data:', props.shedReceive)
}

const form = useForm({
  transaction_id: props.shedReceive?.receive_id || 0,
  flock_id: props.shedReceive?.flock_id || 0,
  shed_id: props.shedReceive?.shed_id || 1,
  receiving_company_id: props.shedReceive?.receiving_company_id || 0,
  shed_female_qty: props.shedReceive?.shed_female_qty || 0,
  shed_male_qty: props.shedReceive?.shed_male_qty || 0,
  shed_total_qty: props.shedReceive?.shed_total_qty || 0,
  shed_sortage_male_box: props.shedReceive?.shed_sortage_male_box || 0,
  shed_sortage_female_box: props.shedReceive?.shed_sortage_female_box || 0,
  shed_sortage_box_qty: props.shedReceive?.shed_sortage_box_qty || 0,
  shed_excess_male_box: props.shedReceive?.shed_excess_male_box || 0,
  shed_excess_female_box: props.shedReceive?.shed_excess_female_box || 0,
  shed_excess_box_qty: props.shedReceive?.shed_excess_box_qty || 0,
  remarks: props.shedReceive?.remarks || '',
  status: props.shedReceive?.status || 1,
})

// Watch for total boxes and auto-calc shortages/excess
watch(
  () => [form.shed_male_qty, form.shed_female_qty],
  () => {
    form.shed_total_qty =
      Number(form.shed_male_qty || 0) + Number(form.shed_female_qty || 0)

    // Total shortage
    form.shed_sortage_box_qty =
      Number(form.shed_sortage_male_box || 0) + Number(form.shed_sortage_female_box || 0)
    form.shed_excess_box_qty =
      Number(form.shed_excess_male_box || 0) + Number(form.shed_excess_female_box || 0)
  },
  { deep: true, immediate: true }
)

// Watch for flock selection
watch(selectedFlockId, (val) => {
  if (val) {
    form.flock_id = Number(val)
    // Auto-select company if available
    const flock = props.flocks.find(f => f.id === Number(val))
    if (flock && flock.company_id) {
      form.receiving_company_id = flock.company_id
    }
  }
})

// Watch for firm receive selection
watch(selectTransactionid, (val) => {
  if (val) {
    form.transaction_id = Number(val)
    showInfo.value = true
  }
})

// Watch for shed selection
watch(selectedShedid, (val) => {
  if (val) {
    form.shed_id = Number(val)
  }
})

// Toggle Batch Info
function toggleInfo() {
  const selected = props.firmReceives.find((job) => job.id === Number(selectTransactionid.value))
  if (!selected) {
    showInfo.value = false
    return
  }
  form.transaction_id = selected.id
  form.receiving_company_id = selected.receiving_company_id
  selectedFlockId.value = selected.flock_id
  showInfo.value = true
}

const submit = () => {
  form.put(route('shed-receive.update', props.shedReceive.id))
}
</script>

<template>
<AppLayout :breadcrumbs="breadcrumbs">
  <Head title="Edit Shed Receive" />

  <!-- Header Section -->
  <div class="relative overflow-hidden rounded-xl bg-gradient-to-br from-gray-800 via-gray-900 to-black p-4 text-white shadow-lg">
    <div class="absolute -right-2 -top-2 h-16 w-16 rounded-full bg-white/5"></div>
    <div class="absolute -bottom-2 -left-2 h-12 w-12 rounded-full bg-white/3"></div>
    
    <div class="relative z-10">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold">Edit Shed Receive</h1>
          <p class="mt-1 text-gray-300 text-sm">Update the shed receive entry</p>
        </div>
        <Link 
          href="/shed-receive" 
          class="group relative overflow-hidden rounded-lg bg-black/40 px-4 py-2 text-sm font-semibold text-white backdrop-blur-sm transition-all duration-300 hover:bg-black/60 hover:shadow-lg border border-gray-600"
        >
          <span class="relative z-10 flex items-center gap-1">
            <ArrowLeft class="h-3 w-3" />
            Back to List
          </span>
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-10 group-hover:translate-x-full"></div>
        </Link>
      </div>
    </div>
  </div>

  <form @submit.prevent="submit" class="space-y-4">

    <!-- Firm Receive Selection Card -->
    <div class="relative rounded-lg border-0 bg-gradient-to-br from-white via-gray-50 to-white p-4 shadow-md ring-1 ring-gray-200 dark:from-gray-800 dark:via-gray-700/20 dark:to-gray-800 dark:ring-gray-700">
      <div class="absolute -right-2 -top-2 h-12 w-12 rounded-full bg-gradient-to-br from-gray-500/10 to-gray-600/10"></div>
      <div class="absolute -bottom-2 -left-2 h-16 w-16 rounded-full bg-gradient-to-br from-gray-400/5 to-gray-500/5"></div>
      
      <div class="relative">
        <div class="mb-4 flex items-center gap-2">
          <div class="rounded-lg bg-gradient-to-br from-gray-600 to-gray-700 p-2 shadow-md">
            <Package class="h-4 w-4 text-white" />
          </div>
          <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Farm Receive & Shed Information</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">Select farm receive and destination shed</p>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
          
          <!-- Firm Receive Code Dropdown -->
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
              <Package class="h-4 w-4" />
              Select Farm Receive
            </Label>
            <div class="firm-receive-dropdown relative">
              <button
                type="button"
                @click.stop="showFirmReceiveDropdown = !showFirmReceiveDropdown"
                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition-all duration-200 hover:border-gray-500 hover:shadow-md focus:border-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <span class="flex items-center gap-3">
                  <div class="h-2 w-2 rounded-full bg-purple-500"></div>
                  <span v-if="selectedFirmReceive" class="text-purple-600 dark:text-purple-400 font-semibold">
                    Rcv-{{ String(selectedFirmReceive.id).padStart(6, '0') }}-{{ selectedFirmReceive.company_short_name }}-{{ selectedFirmReceive.project_name }}-{{ selectedFirmReceive.flock_code || props.flocks.find(f => f.id === selectedFirmReceive.flock_id)?.code || selectedFirmReceive.flock_name }}
                  </span>
                  <span v-else>Select Farm Receive</span>
                </span>
                <ChevronDown class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showFirmReceiveDropdown }" />
              </button>
              
              <!-- Firm Receive Dropdown -->
              <div 
                v-if="showFirmReceiveDropdown" 
                class="fixed inset-0 z-[9999] flex items-start justify-center pt-20"
                @click="showFirmReceiveDropdown = false"
              >
                <div 
                  class="w-full max-w-md rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-600 dark:bg-gray-800"
                  @click.stop
                >
                  <!-- Header -->
                  <div class="border-b border-gray-200 p-4 dark:border-gray-600">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Select Farm Receive</h3>
                    <div class="relative mt-3">
                      <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                      <input
                        v-model="firmReceiveSearchQuery"
                        type="text"
                        placeholder="Search farm receives..."
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 pl-10 pr-4 py-2 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        @click.stop
                      />
                    </div>
                  </div>

                  <!-- Firm Receive List -->
                  <div class="max-h-96 overflow-y-auto">
                    <div v-if="(props.firmReceives?.length || 0) === 0" class="px-6 py-8 text-center">
                      <AlertCircle class="mx-auto h-8 w-8 text-red-500" />
                      <div class="mt-2 font-medium text-red-600">No Farm Receives Available</div>
                      <div class="text-sm text-gray-500">Please create farm receives first</div>
                    </div>
                    <button
                      v-for="fr in filteredFirmReceives"
                      :key="fr.id"
                      type="button"
                      @click.stop="selectTransactionid = fr.id; showFirmReceiveDropdown = false; toggleInfo()"
                      class="flex w-full items-center gap-4 px-6 py-4 text-left hover:bg-purple-50 dark:hover:bg-gray-700 transition-colors duration-200 border-b border-gray-100 dark:border-gray-600 last:border-b-0"
                      :class="{ 'bg-purple-100 dark:bg-purple-900': selectTransactionid == fr.id }"
                    >
                      <div class="h-3 w-3 rounded-full bg-purple-500 flex-shrink-0"></div>
                      <div class="flex-1">
                        <div class="font-semibold text-gray-900 dark:text-white">
                          <span class="text-purple-600 dark:text-purple-400">
                            Rcv-{{ String(fr.id).padStart(6, '0') }}-{{ fr.company_short_name }}-{{ fr.project_name }}-{{ fr.flock_code || props.flocks.find(f => f.id === fr.flock_id)?.code || fr.flock_name }}
                          </span>
                        </div>
                        <div class="text-xs text-gray-400 dark:text-gray-500">
                          <span class="font-medium">Total Qty:</span> {{ fr.firm_total_qty }} Boxes
                          <span v-if="fr.assigned_qty > 0" class="ml-2 text-amber-600 dark:text-amber-400">
                            ({{ fr.assigned_qty }} assigned)
                          </span>
                        </div>
                        <div class="text-xs font-medium text-green-600 dark:text-green-400">
                          <span class="font-semibold">Available:</span> {{ fr.remaining_qty || fr.firm_total_qty }} Boxes
                          <span v-if="fr.status_text" class="ml-2 text-xs px-2 py-0.5 rounded-full" 
                                :class="fr.status_text === 'Pending' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200'">
                            {{ fr.status_text }}
                          </span>
                        </div>
                      </div>
                      <CheckCircle2 v-if="selectTransactionid == fr.id" class="h-4 w-4 text-purple-500 flex-shrink-0" />
                    </button>
                    <div v-if="filteredFirmReceives.length === 0 && (props.firmReceives?.length || 0) > 0" class="px-6 py-8 text-center text-gray-500">
                      <Search class="mx-auto h-6 w-6 text-gray-400" />
                      <div class="mt-2 text-sm">No results found for "{{ firmReceiveSearchQuery }}"</div>
                    </div>
                  </div>

                  <!-- Close Button -->
                  <div class="border-t border-gray-200 p-4 dark:border-gray-600">
                    <Button 
                      type="button"
                      @click="showFirmReceiveDropdown = false"
                      class="w-full rounded-lg bg-gradient-to-r from-gray-800 to-black text-white hover:from-gray-900 hover:to-gray-800 shadow-lg border border-gray-600 transition-all duration-300"
                    >
                      Close
                    </Button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Flock Selection Dropdown -->
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
              <Users class="h-4 w-4" />
              Flock Selection
            </Label>
            <div class="flock-dropdown relative">
              <button
                type="button"
                @click.stop="showFlockDropdown = !showFlockDropdown"
                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition-all duration-200 hover:border-gray-500 hover:shadow-md focus:border-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <span class="flex items-center gap-3">
                  <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                  {{ selectedFlock ? selectedFlock.code : 'Select Flock' }}
                </span>
                <ChevronDown class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showFlockDropdown }" />
              </button>
              
              <!-- Flock Dropdown -->
              <div 
                v-if="showFlockDropdown" 
                class="fixed inset-0 z-[9999] flex items-start justify-center pt-20"
                @click="showFlockDropdown = false"
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
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 pl-10 pr-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        @click.stop
                      />
                    </div>
                  </div>

                  <!-- Flock List -->
                  <div class="max-h-96 overflow-y-auto">
                    <button
                      v-for="flock in filteredFlocks"
                      :key="flock.id"
                      type="button"
                      @click.stop="selectedFlockId = flock.id; showFlockDropdown = false"
                      class="flex w-full items-center gap-4 px-6 py-4 text-left hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors duration-200 border-b border-gray-100 dark:border-gray-600 last:border-b-0"
                      :class="{ 'bg-blue-100 dark:bg-blue-900': selectedFlockId == flock.id }"
                    >
                      <div class="h-3 w-3 rounded-full bg-blue-500 flex-shrink-0"></div>
                      <div class="flex-1">
                        <div class="font-semibold text-gray-900 dark:text-white">{{ flock.code }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ flock.name }}</div>
                      </div>
                      <CheckCircle2 v-if="selectedFlockId == flock.id" class="h-4 w-4 text-blue-500 flex-shrink-0" />
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
                      @click="showFlockDropdown = false"
                      class="w-full rounded-lg bg-gradient-to-r from-gray-800 to-black text-white hover:from-gray-900 hover:to-gray-800 shadow-lg border border-gray-600 transition-all duration-300"
                    >
                      Close
                    </Button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Shed Selection Dropdown -->
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
              <Building2 class="h-4 w-4" />
              Production Shed
            </Label>
            <div class="shed-dropdown relative">
              <button
                type="button"
                @click.stop="showShedDropdown = !showShedDropdown"
                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition-all duration-200 hover:border-gray-500 hover:shadow-md focus:border-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <span class="flex items-center gap-3">
                  <div class="h-2 w-2 rounded-full bg-orange-500"></div>
                  {{ selectedShed ? selectedShed.name : 'Select Production Shed' }}
                </span>
                <ChevronDown class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showShedDropdown }" />
              </button>
              
              <!-- Shed Dropdown -->
              <div 
                v-if="showShedDropdown" 
                class="fixed inset-0 z-[9999] flex items-start justify-center pt-20"
                @click="showShedDropdown = false"
              >
                <div 
                  class="w-full max-w-md rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-600 dark:bg-gray-800"
                  @click.stop
                >
                  <!-- Header -->
                  <div class="border-b border-gray-200 p-4 dark:border-gray-600">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Select Production Shed</h3>
                    <div class="relative mt-3">
                      <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                      <input
                        v-model="shedSearchQuery"
                        type="text"
                        placeholder="Search sheds..."
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 pl-10 pr-4 py-2 text-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        @click.stop
                      />
                    </div>
                  </div>

                  <!-- Shed List -->
                  <div class="max-h-96 overflow-y-auto">
                    <button
                      v-for="shed in filteredSheds"
                      :key="shed.id"
                      type="button"
                      @click.stop="selectedShedid = shed.id; showShedDropdown = false"
                      class="flex w-full items-center gap-4 px-6 py-4 text-left hover:bg-orange-50 dark:hover:bg-gray-700 transition-colors duration-200 border-b border-gray-100 dark:border-gray-600 last:border-b-0"
                      :class="{ 'bg-orange-100 dark:bg-orange-900': selectedShedid == shed.id }"
                    >
                      <div class="h-3 w-3 rounded-full bg-orange-500 flex-shrink-0"></div>
                      <div class="flex-1">
                        <div class="font-semibold text-gray-900 dark:text-white">{{ shed.name }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">ID: {{ shed.id }}</div>
                      </div>
                      <CheckCircle2 v-if="selectedShedid == shed.id" class="h-4 w-4 text-orange-500 flex-shrink-0" />
                    </button>
                    <div v-if="filteredSheds.length === 0 && (props.sheds?.length || 0) > 0" class="px-6 py-8 text-center text-gray-500">
                      <Search class="mx-auto h-6 w-6 text-gray-400" />
                      <div class="mt-2 text-sm">No results found for "{{ shedSearchQuery }}"</div>
                    </div>
                  </div>

                  <!-- Close Button -->
                  <div class="border-t border-gray-200 p-4 dark:border-gray-600">
                    <Button 
                      type="button"
                      @click="showShedDropdown = false"
                      class="w-full rounded-lg bg-gradient-to-r from-gray-800 to-black text-white hover:from-gray-900 hover:to-gray-800 shadow-lg border border-gray-600 transition-all duration-300"
                    >
                      Close
                    </Button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Firm Receive Info Display -->
        <transition 
          enter-active-class="transition-all duration-500 ease-out"
          leave-active-class="transition-all duration-500 ease-in"
          enter-from-class="opacity-0 scale-95 -translate-y-4"
          enter-to-class="opacity-100 scale-100 translate-y-0"
          leave-from-class="opacity-100 scale-100 translate-y-0"
          leave-to-class="opacity-0 scale-95 -translate-y-4"
        >
          <div v-if="showInfo" class="mt-8 rounded-xl border border-purple-200 bg-gradient-to-br from-purple-50 to-indigo-50 p-6 dark:border-purple-800 dark:from-purple-900/20 dark:to-indigo-900/20">
            <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-purple-900 dark:text-purple-100">
              <CheckCircle2 class="h-5 w-5" />
              Farm Receive Details
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Farm Receive Code:</span><div class="text-purple-600 dark:text-purple-400 font-semibold">Rcv-{{ String(selectedFirmReceive?.id || 0).padStart(6, '0') }}-{{ selectedFirmReceive?.company_short_name }}-{{ selectedFirmReceive?.project_name }}-{{ selectedFirmReceive?.flock_code || props.flocks.find(f => f.id === selectedFirmReceive?.flock_id)?.code || selectedFirmReceive?.flock_name }}</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Flock:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedFlock?.code || selectedFirmReceive?.flock_code || selectedFirmReceive?.flock_name }}</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Company:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedFirmReceive?.company_name || props.companies.find(c => c.id === form.receiving_company_id)?.name }}</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Female Box Qty:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedFirmReceive?.firm_female_qty }}</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Male Box Qty:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedFirmReceive?.firm_male_qty }}</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Total Box Qty:</span><div class="font-bold text-purple-900 dark:text-purple-100">{{ selectedFirmReceive?.firm_total_qty }}</div></div>
            </div>
          </div>
        </transition>
      </div>
    </div>

    <!-- Production Shed Receive Quantities Card -->
    <div class="relative overflow-hidden rounded-lg border-0 bg-gradient-to-br from-white via-gray-50 to-white p-4 shadow-md ring-1 ring-gray-200 dark:from-gray-800 dark:via-gray-700/20 dark:to-gray-800 dark:ring-gray-700">
      <div class="absolute -right-2 -top-2 h-12 w-12 rounded-full bg-gradient-to-br from-gray-500/10 to-gray-600/10"></div>
      <div class="absolute -bottom-2 -left-2 h-16 w-16 rounded-full bg-gradient-to-br from-gray-400/5 to-gray-500/5"></div>
      
      <div class="relative">
        <div class="mb-4 flex items-center gap-2">
          <div class="rounded-lg bg-gradient-to-br from-gray-600 to-gray-700 p-2 shadow-md">
            <Users class="h-4 w-4 text-white" />
          </div>
          <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white"> Shed Assign Boxes </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">Enter Chick boxes receive in shed</p>
          </div>
        </div>

        <!-- Main Chick Quantities -->
        <div class="mb-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Female Box Qty</Label>
              <Input 
                v-model.number="form.shed_female_qty" 
                type="number" 
                min="0"
                :max="selectedFirmReceive?.remaining_female_qty || selectedFirmReceive?.firm_female_qty || 0"
                class="rounded-lg border-pink-300 bg-pink-50 px-3 py-2 text-sm shadow-sm focus:border-pink-500 focus:ring-pink-500/20 dark:border-pink-600 dark:bg-pink-900/20" 
              />
              <!-- Available Box Information - Positioned directly under input -->
              <div v-if="selectedFirmReceive" class="mt-2 p-2 bg-pink-50 dark:bg-pink-900/20 rounded-md border border-pink-200 dark:border-pink-800 relative">
                <!-- Connecting line to input field -->
                <div class="absolute -top-2 left-4 w-0.5 h-2 bg-pink-300 dark:bg-pink-600"></div>
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <span class="text-xs font-medium text-pink-700 dark:text-pink-300">Available Female Boxes:</span>
                    <span class="text-sm font-bold text-pink-600 dark:text-pink-400">{{ selectedFirmReceive.remaining_female_qty || selectedFirmReceive.firm_female_qty || 0 }}</span>
                  </div>
                  <div v-if="selectedFirmReceive.assigned_female_qty > 0" class="text-xs text-amber-600 dark:text-amber-400">
                    ({{ selectedFirmReceive.assigned_female_qty }} assigned)
                  </div>
                </div>
              </div>
            </div>
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Male Box Qty</Label>
              <Input 
                v-model.number="form.shed_male_qty" 
                type="number" 
                min="0"
                :max="selectedFirmReceive?.remaining_male_qty || selectedFirmReceive?.firm_male_qty || 0"
                class="rounded-lg border-blue-300 bg-blue-50 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500/20 dark:border-blue-600 dark:bg-blue-900/20" 
              />
              <!-- Available Box Information - Positioned directly under input -->
              <div v-if="selectedFirmReceive" class="mt-2 p-2 bg-blue-50 dark:bg-blue-900/20 rounded-md border border-blue-200 dark:border-blue-800 relative">
                <!-- Connecting line to input field -->
                <div class="absolute -top-2 left-4 w-0.5 h-2 bg-blue-300 dark:bg-blue-600"></div>
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <span class="text-xs font-medium text-blue-700 dark:text-blue-300">Available Male Boxes:</span>
                    <span class="text-sm font-bold text-blue-600 dark:text-blue-400">{{ selectedFirmReceive.remaining_male_qty || selectedFirmReceive.firm_male_qty || 0 }}</span>
                  </div>
                  <div v-if="selectedFirmReceive.assigned_male_qty > 0" class="text-xs text-amber-600 dark:text-amber-400">
                    ({{ selectedFirmReceive.assigned_male_qty }} assigned)
                  </div>
                </div>
              </div>
            </div>
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 dark:text-gray-300">Total Box Qty</Label>
              <Input 
                type="number" 
                :value="form.shed_total_qty" 
                readonly 
                class="rounded-lg border-gray-300 bg-gradient-to-r from-gray-100 to-gray-50 px-3 py-2 text-sm font-bold text-gray-700 shadow-sm cursor-not-allowed dark:border-gray-600 dark:from-gray-700 dark:to-gray-800 dark:text-gray-300" 
              />
            </div>
          </div>
        </div>

        <!-- Shortage & Excess Quantities -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <!-- Shortage Section -->
          <div class="rounded-lg border border-red-200 bg-gradient-to-br from-red-50 to-pink-50 p-4 dark:border-red-800 dark:from-red-900/20 dark:to-pink-900/20">
            <h3 class="mb-3 flex items-center gap-2 text-base font-semibold text-red-800 dark:text-red-200">
              <AlertCircle class="h-4 w-4" />
              Shortage Box
            </h3>
            <div class="space-y-3">
              <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1">
                  <Label class="text-xs font-semibold text-red-700 dark:text-red-300">Male Shortage</Label>
                  <Input 
                    type="number" 
                    v-model.number="form.shed_sortage_male_box" 
                    min="0"
                    class="rounded-lg border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800 focus:border-red-500 focus:ring-red-500/20 dark:border-red-600 dark:bg-red-900/30 dark:text-red-200" 
                  />
                </div>
                <div class="space-y-1">
                  <Label class="text-xs font-semibold text-red-700 dark:text-red-300">Female Shortage</Label>
                  <Input 
                    type="number" 
                    v-model.number="form.shed_sortage_female_box" 
                    min="0"
                    class="rounded-lg border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800 focus:border-red-500 focus:ring-red-500/20 dark:border-red-600 dark:bg-red-900/30 dark:text-red-200" 
                  />
                </div>
              </div>
              <div class="space-y-1">
                <Label class="text-xs font-semibold text-red-700 dark:text-red-300">Total Shortage</Label>
                <Input 
                  type="number" 
                  :value="form.shed_sortage_box_qty" 
                  readonly 
                  class="rounded-lg border-red-300 bg-gradient-to-r from-red-100 to-red-50 px-3 py-2 text-sm font-bold text-red-800 cursor-not-allowed dark:border-red-600 dark:from-red-800/50 dark:to-red-900/50 dark:text-red-200"
                />
              </div>
            </div>
          </div>

          <!-- Excess Section -->
          <div class="rounded-lg border border-emerald-200 bg-gradient-to-br from-emerald-50 to-green-50 p-4 dark:border-emerald-800 dark:from-emerald-900/20 dark:to-green-900/20">
            <h3 class="mb-3 flex items-center gap-2 text-base font-semibold text-emerald-800 dark:text-emerald-200">
              <CheckCircle2 class="h-4 w-4" />
              Excess Box
            </h3>
            <div class="space-y-3">
              <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1">
                  <Label class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">Male Excess</Label>
                  <Input 
                    type="number" 
                    v-model.number="form.shed_excess_male_box" 
                    min="0"
                    class="rounded-lg border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-800 focus:border-emerald-500 focus:ring-emerald-500/20 dark:border-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-200" 
                  />
                </div>
                <div class="space-y-1">
                  <Label class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">Female Excess</Label>
                  <Input 
                    type="number" 
                    v-model.number="form.shed_excess_female_box" 
                    min="0"
                    class="rounded-lg border-emerald-300 bg-emerald-50 px-3 py-2 text-sm text-emerald-800 focus:border-emerald-500 focus:ring-emerald-500/20 dark:border-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-200" 
                  />
                </div>
              </div>
              <div class="space-y-1">
                <Label class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">Total Excess</Label>
                <Input 
                  type="number" 
                  :value="form.shed_excess_box_qty" 
                  readonly 
                  class="rounded-lg border-emerald-300 bg-gradient-to-r from-emerald-100 to-emerald-50 px-3 py-2 text-sm font-bold text-emerald-800 cursor-not-allowed dark:border-emerald-600 dark:from-emerald-800/50 dark:to-emerald-900/50 dark:text-emerald-200"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Remarks Section -->
        <div class="mt-4">
          <div class="space-y-1">
            <Label class="text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-1">
              <Info class="h-3 w-3" />
              Remarks
            </Label>
            <textarea 
              v-model="form.remarks" 
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white resize-none transition-all duration-200" 
              rows="2" 
              placeholder="Write your notes here..."
            ></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- Submit Section -->
    <div class="flex items-center justify-end gap-3 rounded-lg bg-gradient-to-r from-gray-50 to-white p-4 dark:from-gray-800 dark:to-gray-900">

      <Button 
        type="submit" 
        :disabled="form.processing"
        class="group relative overflow-hidden rounded-lg bg-gradient-to-r from-gray-800 to-black px-6 py-2 text-sm font-semibold text-white shadow-lg border border-gray-600 transition-all duration-300 hover:from-gray-900 hover:to-gray-800 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-gray-500/50 disabled:opacity-50"
      >
        <span class="relative z-10 flex items-center gap-1">
          <Save class="h-3 w-3" />
          {{ form.processing ? 'Updating...' : 'Update' }}
        </span>
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-10 group-hover:translate-x-full"></div>
      </Button>
    </div>

  </form>
</AppLayout>
</template>
