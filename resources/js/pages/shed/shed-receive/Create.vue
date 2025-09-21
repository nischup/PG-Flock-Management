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
  { title: 'Create', href: '' },
]

// Props
const props = defineProps<{
  firmReceives: Array<any> // Batches generated from firm receive
  flocks: Array<any>
  companies: Array<any>
  sheds: Array<any>
}>()

// Form state
const selectTransactionid = ref<number | string>('')
const selectedFlockId = ref<number | string>('')
const selectedShedid = ref<number | string>('')

const showInfo = ref(false)
const isDetailsExpanded = ref(true)

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
}

const form = useForm({
  transaction_id: null,
  flock_id: null,
  shed_id: null,
  receiving_company_id: null,
  shed_female_qty: null,
  shed_male_qty: null,
  shed_total_qty: 0,
  
  shed_sortage_male_box: 0,
  shed_sortage_female_box: 0,
  shed_sortage_box_qty: 0,
 
  shed_male_mortality: 0,
  shed_female_mortality: 0,
  shed_total_mortality: 0,
  
  shed_excess_male_box: 0,
  shed_excess_female_box: 0,
  shed_excess_box_qty: 0,

  remarks: '',
  status: 1,
})

// Watch for total boxes and auto-calc shortages/excess
watch(
  () => [form.shed_male_qty, form.shed_female_qty, form.shed_sortage_male_box, form.shed_sortage_female_box, form.shed_male_mortality, form.shed_female_mortality, form.shed_excess_male_box, form.shed_excess_female_box],
  () => {
    form.shed_total_qty =
      Number(form.shed_male_qty || 0) + Number(form.shed_female_qty || 0)

    // Calculate total mortality for shortage
    form.shed_total_mortality =
      Number(form.shed_male_mortality || 0) + Number(form.shed_female_mortality || 0)
    
    // Calculate total mortality for excess
    form.shed_excess_box_qty =
      Number(form.shed_excess_male_box || 0) + Number(form.shed_excess_female_box || 0)

    // Total shortage
    form.shed_sortage_box_qty =
      Number(form.shed_sortage_male_box || 0) + Number(form.shed_sortage_female_box || 0)
    
  },
  { deep: true, immediate: true }
)

// Watch for flock selection
watch(selectedFlockId, (val) => {
  form.flock_id = val
})

// Watch for flock selection
watch(selectedShedid, (val) => {
  form.shed_id = val
})

watch(selectTransactionid, (val) => {
  form.transaction_id = val
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

// Submit Shed Receive
function submit() {
  // Basic client-side validation
  if (!form.shed_female_qty || form.shed_female_qty <= 0) {
    form.setError('shed_female_qty', 'Female box quantity is required and must be greater than 0.')
    return
  }
  
  if (!form.shed_male_qty || form.shed_male_qty <= 0) {
    form.setError('shed_male_qty', 'Male box quantity is required and must be greater than 0.')
    return
  }

  form.post(route('shed-receive.store'), {
    onSuccess: () => {
      form.reset()
      // Reset dropdown selections
      selectTransactionid.value = ''
      selectedFlockId.value = ''
      selectedShedid.value = ''
      showInfo.value = false
    },
    onError: (errors) => {
      console.log('Validation errors:', errors)
    },
  })
}
</script>

<template>
<AppLayout :breadcrumbs="breadcrumbs">
  <Head title="Create Shed Receive" />

  <!-- Header Section -->
  <div class="mb-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Create Shed Receive</h1>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Transfer chicks from farm to shed facility</p>
      </div>
      <Link 
        href="/shed-receive" 
        class="group relative overflow-hidden rounded-lg px-4 py-2 text-xs font-semibold text-white shadow-md transition-all duration-300 hover:scale-105 hover:shadow-lg"
        style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); box-shadow: 0 2px 8px rgba(107, 114, 128, 0.3);"
      >
        <span class="relative z-10 flex items-center gap-1">
          <ArrowLeft class="h-3 w-3" />
          Back to List
        </span>
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-20 group-hover:translate-x-full"></div>
      </Link>
    </div>
  </div>

  <form @submit.prevent="submit" class="space-y-6">

    <!-- Firm Receive Selection Card -->
    <div class="relative rounded-xl border-0 bg-gradient-to-br from-white via-blue-50 to-white p-6 shadow-lg ring-1 ring-gray-200 dark:from-gray-800 dark:via-blue-900/20 dark:to-gray-800 dark:ring-gray-700">
      <div class="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-gradient-to-br from-blue-500/20 to-purple-500/20"></div>
      <div class="absolute -bottom-3 -left-3 h-20 w-20 rounded-full bg-gradient-to-br from-emerald-500/10 to-blue-500/10"></div>
      
      <div class="relative">
        <div class="mb-6 flex items-center gap-2">
          <div class="rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 p-2 shadow-md">
            <Package class="h-4 w-4 text-white" />
          </div>
          <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Farm Receive & Shed Information</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">Select firm receive code and destination shed</p>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          
          <!-- Firm Receive Code Dropdown -->
          <div class="space-y-2">
            <Label class="text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-1">
              <Package class="h-3 w-3" />
              Firm Receive Code
              <span class="text-red-500 ml-1">*</span>
            </Label>
            <div class="firm-receive-dropdown relative">
              <button
                type="button"
                @click.stop="showFirmReceiveDropdown = !showFirmReceiveDropdown"
                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition-all duration-200 hover:border-blue-500 hover:shadow-md focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <span class="flex items-center gap-2">
                  <div class="h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                  <span v-if="selectedFirmReceive" class="text-blue-600 dark:text-blue-400 font-semibold">
                    Rcv-{{ String(selectedFirmReceive.id).padStart(6, '0') }}-{{ selectedFirmReceive.company_short_name }}-{{ selectedFirmReceive.project_name }}-{{ selectedFirmReceive.flock_code || props.flocks.find(f => f.id === selectedFirmReceive.flock_id)?.code || selectedFirmReceive.flock_name }}
                  </span>
                  <span v-else>Select Firm Receive Code</span>
                </span>
                <ChevronDown class="h-3 w-3 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showFirmReceiveDropdown }" />
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
                    <h3 class="font-semibold text-gray-900 dark:text-white">Select Firm Receive</h3>
                    <div class="relative mt-3">
                      <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                      <input
                        v-model="firmReceiveSearchQuery"
                        type="text"
                        placeholder="Search firm receives..."
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 pl-10 pr-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        @click.stop
                      />
                    </div>
                  </div>

                  <!-- Firm Receive List -->
                  <div class="max-h-96 overflow-y-auto">
                    <div v-if="(props.firmReceives?.length || 0) === 0" class="px-6 py-8 text-center">
                      <AlertCircle class="mx-auto h-8 w-8 text-red-500" />
                      <div class="mt-2 font-medium text-red-600">No Firm Receives Available</div>
                      <div class="text-sm text-gray-500">Please create firm receives first</div>
                    </div>
                    <button
                      v-for="fr in filteredFirmReceives"
                      :key="fr.id"
                      type="button"
                      @click.stop="selectTransactionid = fr.id; showFirmReceiveDropdown = false; toggleInfo()"
                      class="flex w-full items-center gap-4 px-6 py-4 text-left hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors duration-200 border-b border-gray-100 dark:border-gray-600 last:border-b-0"
                      :class="{ 'bg-blue-100 dark:bg-blue-900': selectTransactionid == fr.id }"
                    >
                      <div class="h-3 w-3 rounded-full bg-blue-500 flex-shrink-0"></div>
                      <div class="flex-1">
                        <div class="font-semibold text-gray-900 dark:text-white">
                          <span class="text-blue-600 dark:text-blue-400">
                            Rcv-{{ String(fr.id).padStart(6, '0') }}-{{ fr.company_short_name }}-{{ fr.project_name }}-{{ fr.flock_code || props.flocks.find(f => f.id === fr.flock_id)?.code || fr.flock_name }}
                          </span>
                        </div>
                        <div class="text-xs text-gray-400 dark:text-gray-500">
                          <span class="font-medium">Total Qty:</span> {{ fr.firm_total_qty }} Boxes
                        </div>
                      </div>
                      <CheckCircle2 v-if="selectTransactionid == fr.id" class="h-4 w-4 text-blue-500 flex-shrink-0" />
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
                      class="w-full rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                    >
                      Close
                    </Button>
                  </div>
                </div>
              </div>
            </div>
            <InputError :message="form.errors.transaction_id" class="mt-1" />
          </div>

          <!-- Flock Dropdown -->
          <div class="space-y-2">
            <Label class="text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-1">
              <Users class="h-3 w-3" />
              Flock Selection
              <span class="text-red-500 ml-1">*</span>
            </Label>
            <div class="flock-dropdown relative">
              <button
                type="button"
                @click.stop="showFlockDropdown = !showFlockDropdown"
                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm shadow-sm transition-all duration-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white cursor-not-allowed"
                :disabled="!selectedFirmReceive"
              >
                <span class="flex items-center gap-2">
                  <div class="h-1.5 w-1.5 rounded-full bg-emerald-500"></div>
                  {{ selectedFlock ? selectedFlock.code : (selectedFirmReceive ? selectedFirmReceive.flock_name : 'Select Firm Receive First') }}
                </span>
                <ChevronDown class="h-3 w-3 text-gray-400" />
              </button>
            </div>
            <InputError :message="form.errors.flock_id" class="mt-1" />
          </div>

          <!-- Shed Dropdown -->
          <div class="space-y-2">
            <Label class="text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-1">
              <Home class="h-3 w-3" />
              Shed Selection
              <span class="text-red-500 ml-1">*</span>
            </Label>
            <div class="shed-dropdown relative">
              <button
                type="button"
                @click.stop="showShedDropdown = !showShedDropdown"
                class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm transition-all duration-200 hover:border-orange-500 hover:shadow-md focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <span class="flex items-center gap-2">
                  <div class="h-1.5 w-1.5 rounded-full bg-orange-500"></div>
                  {{ selectedShed ? selectedShed.name : 'Select Destination Shed' }}
                </span>
                <ChevronDown class="h-3 w-3 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showShedDropdown }" />
              </button>
              
              <!-- Shed Dropdown -->
              <div 
                v-if="showShedDropdown" 
                class="fixed inset-0 z-[9997] flex items-start justify-center pt-20"
                @click="showShedDropdown = false"
              >
                <div 
                  class="w-full max-w-md rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-600 dark:bg-gray-800"
                  @click.stop
                >
                  <!-- Header -->
                  <div class="border-b border-gray-200 p-4 dark:border-gray-600">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Select Destination Shed</h3>
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
                    <div v-if="(props.sheds?.length || 0) === 0" class="px-6 py-8 text-center">
                      <AlertCircle class="mx-auto h-8 w-8 text-red-500" />
                      <div class="mt-2 font-medium text-red-600">No Sheds Available</div>
                      <div class="text-sm text-gray-500">Please add sheds first</div>
                    </div>
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
                      class="w-full rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                    >
                      Close
                    </Button>
                  </div>
                </div>
              </div>
            </div>
            <InputError :message="form.errors.shed_id" class="mt-1" />
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
          <div v-if="showInfo" class="mt-8 rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50 to-indigo-50 p-6 dark:border-blue-800 dark:from-blue-900/20 dark:to-indigo-900/20">
            <div class="mb-4 flex items-center justify-between">
              <h3 class="flex items-center gap-2 text-lg font-semibold text-blue-900 dark:text-blue-100">
                <CheckCircle2 class="h-5 w-5" />
                Firm Receive Details
              </h3>
              <button
                type="button"
                @click="isDetailsExpanded = !isDetailsExpanded"
                class="flex items-center gap-1 rounded-lg px-3 py-2 text-sm font-medium text-blue-700 bg-blue-100 hover:bg-blue-200 dark:text-blue-300 dark:bg-blue-800/30 dark:hover:bg-blue-800/50 transition-colors duration-200 border border-blue-200 dark:border-blue-700"
              >
                <ChevronDown 
                  class="h-3 w-3 transition-transform duration-200" 
                  :class="{ 'rotate-180': isDetailsExpanded }" 
                />
                {{ isDetailsExpanded ? 'Hide' : 'Show' }}
              </button>
            </div>
            <transition
              enter-active-class="transition-all duration-300 ease-out"
              leave-active-class="transition-all duration-300 ease-in"
              enter-from-class="opacity-0 max-h-0"
              enter-to-class="opacity-100 max-h-96"
              leave-from-class="opacity-100 max-h-96"
              leave-to-class="opacity-0 max-h-0"
            >
              <div v-if="isDetailsExpanded" class="overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
                  <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Farm Receive Code:</span><div class="text-blue-600 dark:text-blue-400 font-semibold">Rcv-{{ String(selectedFirmReceive?.id || 0).padStart(6, '0') }}-{{ selectedFirmReceive?.company_short_name }}-{{ selectedFirmReceive?.project_name }}-{{ selectedFirmReceive?.flock_code || props.flocks.find(f => f.id === selectedFirmReceive?.flock_id)?.code || selectedFirmReceive?.flock_name }}</div></div>
                  <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Flock:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedFlock?.code || selectedFirmReceive?.flock_code || selectedFirmReceive?.flock_name }}</div></div>
                  <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Company:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedFirmReceive?.company_name || props.companies.find(c => c.id === form.receiving_company_id)?.name }}</div></div>
                  <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Female Box Qty:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedFirmReceive?.firm_female_qty }}</div></div>
                  <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Male Box Qty:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedFirmReceive?.firm_male_qty }}</div></div>
                  <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Total Box Qty:</span><div class="font-bold text-blue-900 dark:text-blue-100">{{ selectedFirmReceive?.firm_total_qty }}</div></div>
                </div>
              </div>
            </transition>
          </div>
        </transition>
      </div>
    </div>

    <!-- Shed Receive Quantities Card -->
    <div class="relative overflow-hidden rounded-xl border-0 bg-gradient-to-br from-white via-emerald-50 to-white p-6 shadow-lg ring-1 ring-gray-200 dark:from-gray-800 dark:via-emerald-900/20 dark:to-gray-800 dark:ring-gray-700">
      <div class="absolute -right-3 -top-3 h-16 w-16 rounded-full bg-gradient-to-br from-emerald-500/20 to-green-500/20"></div>
      <div class="absolute -bottom-3 -left-3 h-20 w-20 rounded-full bg-gradient-to-br from-teal-500/10 to-emerald-500/10"></div>
      
      <div class="relative">
        <div class="mb-6 flex items-center gap-2">
          <div class="rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-600 p-2 shadow-md">
            <Users class="h-4 w-4 text-white" />
          </div>
          <div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Shed Chick Quantities</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">Enter chick quantities received by shed</p>
          </div>
        </div>

        <!-- Main Chick Quantities -->
        <div class="mb-6">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                Female Box Qty
                <span class="text-red-500 ml-1">*</span>
              </Label>
              <Input 
                v-model.number="form.shed_female_qty" 
                type="number" 
                min="0"
                required
                class="rounded-lg border-pink-300 bg-pink-50 px-3 py-2 text-sm shadow-sm focus:border-pink-500 focus:ring-pink-500/20 dark:border-pink-600 dark:bg-pink-900/20" 
              />
              <InputError :message="form.errors.shed_female_qty" class="mt-1" />
            </div>
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                Male Box Qty
                <span class="text-red-500 ml-1">*</span>
              </Label>
              <Input 
                v-model.number="form.shed_male_qty" 
                type="number" 
                min="0"
                required
                class="rounded-lg border-blue-300 bg-blue-50 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500/20 dark:border-blue-600 dark:bg-blue-900/20" 
              />
              <InputError :message="form.errors.shed_male_qty" class="mt-1" />
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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Shortage Section -->
          <div class="rounded-lg border border-red-200 bg-gradient-to-br from-red-50 to-pink-50 p-4 dark:border-red-800 dark:from-red-900/20 dark:to-pink-900/20">
            <div class="space-y-3">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
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
              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="space-y-1">
                  <Label class="text-xs font-semibold text-red-700 dark:text-red-300">Male Mortality</Label>
                  <Input 
                    type="number" 
                    v-model.number="form.shed_male_mortality" 
                    min="0"
                    class="rounded-lg border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800 focus:border-red-500 focus:ring-red-500/20 dark:border-red-600 dark:bg-red-900/30 dark:text-red-200" 
                  />
                </div>
                <div class="space-y-1">
                  <Label class="text-xs font-semibold text-red-700 dark:text-red-300">Female Mortality</Label>
                  <Input 
                    type="number" 
                    v-model.number="form.shed_female_mortality" 
                    min="0"
                    class="rounded-lg border-red-300 bg-red-50 px-3 py-2 text-sm text-red-800 focus:border-red-500 focus:ring-red-500/20 dark:border-red-600 dark:bg-red-900/30 dark:text-red-200" 
                  />
                </div>
                <div class="space-y-1">
                  <Label class="text-xs font-semibold text-red-700 dark:text-red-300">Total Mortality</Label>
                <Input 
                  type="number" 
                    v-model.number="form.shed_total_mortality" 
                  readonly 
                    class="rounded-lg border-red-300 bg-gradient-to-r from-red-100 to-red-50 px-3 py-2 text-sm font-bold text-red-800 cursor-not-allowed dark:border-red-600 dark:from-red-800/50 dark:to-red-900/50 dark:text-red-200"
                />
                </div>
              </div>
            </div>
          </div>

          <!-- Excess Section -->
          <div class="rounded-lg border border-emerald-200 bg-gradient-to-br from-emerald-50 to-green-50 p-4 dark:border-emerald-800 dark:from-emerald-900/20 dark:to-green-900/20">
            <div class="space-y-3">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
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
                <div class="space-y-1">
                  <Label class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">Total Excess</Label>
                <Input 
                  type="number" 
                  v-model.number="form.shed_excess_box_qty" 
                  readonly 
                    class="rounded-lg border-emerald-300 bg-gradient-to-r from-emerald-100 to-emerald-50 px-3 py-2 text-sm font-bold text-emerald-800 cursor-not-allowed dark:border-emerald-600 dark:from-emerald-800/50 dark:to-emerald-900/50 dark:text-emerald-200"
                />
                </div>
              </div>
            
            </div>
          </div>
        </div>

        <!-- Remarks Section -->
        <div class="mt-6">
          <div class="space-y-1">
            <Label class="text-xs font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-1">
              <Info class="h-3 w-3" />
              Remarks
            </Label>
          <textarea 
            v-model="form.remarks" 
              class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white resize-none transition-all duration-200" 
              rows="2" 
            placeholder="Write your notes here..."
          ></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- Submit Section -->
    <div class="flex items-center justify-end gap-3 rounded-lg bg-gradient-to-r from-gray-50 to-white p-4 dark:from-gray-800 dark:to-gray-900">
      <Link 
        href="/shed-receive"
        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
      >
        Cancel
      </Link>
      <Button 
        type="submit" 
        :disabled="form.processing"
        class="group relative overflow-hidden rounded-lg bg-gradient-to-r from-gray-800 to-black px-6 py-2 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:from-gray-900 hover:to-gray-800 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-gray-500/50 disabled:opacity-50"
        style="background: linear-gradient(135deg, #1f2937 0%, #000000 100%); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);"
      >
        <span class="relative z-10 flex items-center gap-1">
          <Save class="h-3 w-3" />
          {{ form.processing ? 'Submitting...' : 'Submit' }}
        </span>
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-10 group-hover:translate-x-full"></div>
      </Button>
    </div>

  </form>
</AppLayout>
</template>
