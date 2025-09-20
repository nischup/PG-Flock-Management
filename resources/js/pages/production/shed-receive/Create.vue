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
  { title: 'Production Shed Receive', href: '/production-shed-receive' },
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
const selectedJobId = ref<number | string>('')
const selectedFlockId = ref<number | string>('')
const selectedShedid = ref<number | string>('')

const showInfo = ref(false)

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
    return props.firmReceives.filter(fr => 
        fr.transaction_no?.toLowerCase().includes(firmReceiveSearchQuery.value.toLowerCase()) ||
        fr.flock_name?.toLowerCase().includes(firmReceiveSearchQuery.value.toLowerCase())
    )
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
    return props.firmReceives.find(fr => fr.id === Number(selectedJobId.value))
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
  job_id: 0,
  flock_id: 0,
  shed_id:1,
  receiving_company_id: 0,
  shed_female_qty: 0,
  shed_male_qty: 0,
  shed_total_qty: 0,
  shed_sortage_male_box: 0,
  shed_sortage_female_box: 0,
  shed_sortage_box_qty: 0,
  shed_excess_male_box: 0,
  shed_excess_female_box: 0,
  shed_excess_box_qty: 0,
  remarks: '',
  status: 1,
})

// Watch for total boxes and auto-calc shortages/excess
watch(
  () => [form.shed_male_qty, form.shed_female_qty,form.shed_sortage_male_box,form.shed_sortage_female_box,form.shed_excess_male_box,form.shed_excess_female_box],
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
  form.flock_id = val
})

// Watch for flock selection
watch(selectedShedid, (val) => {
  form.shed_id = val
})

watch(selectedJobId, (val) => {
  form.job_id = val
})

// Toggle Batch Info
function toggleInfo() {
  const selected = props.firmReceives.find((job) => job.id === Number(selectedJobId.value))
  if (!selected) {
    showInfo.value = false
    return
  }
  form.job_id = selected.id
  form.receiving_company_id = selected.receiving_company_id
  selectedFlockId.value = selected.flock_id
  showInfo.value = true
}

// Submit Shed Receive
function submit() {
  form.post(route('production-shed-receive.store'), {
    onSuccess: () => form.reset(),
    onError: () => {},
  })
}
</script>

<template>
<AppLayout :breadcrumbs="breadcrumbs">
  <Head title="Create Production Shed Receive" />

  <!-- Header Section -->
  <div class="mb-8">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create Production Shed Receive</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Transfer chicks from farm to production shed facility</p>
      </div>
      <Link 
        href="/production-shed-receive" 
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

    <!-- Firm Receive Selection Card -->
    <div class="relative rounded-2xl border-0 bg-gradient-to-br from-white via-purple-50 to-white p-8 shadow-xl ring-1 ring-gray-200 dark:from-gray-800 dark:via-purple-900/20 dark:to-gray-800 dark:ring-gray-700">
      <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-purple-500/20 to-indigo-500/20"></div>
      <div class="absolute -bottom-4 -left-4 h-32 w-32 rounded-full bg-gradient-to-br from-blue-500/10 to-purple-500/10"></div>
      
      <div class="relative">
        <div class="mb-8 flex items-center gap-3">
          <div class="rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 p-3 shadow-lg">
            <Package class="h-6 w-6 text-white" />
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Farm Receive & Production Shed</h2>
            <p class="text-gray-600 dark:text-gray-400">Select firm receive code and production shed destination</p>
          </div>
        </div>

        <!-- Data Status Indicator -->
        <div class="mb-6 flex items-center justify-between rounded-lg border border-green-200 bg-green-50 p-3 dark:border-green-800 dark:bg-green-900/20">
          <div class="flex items-center gap-2 text-sm">
            <div class="h-2 w-2 rounded-full bg-green-500"></div>
            <span class="font-medium text-green-800 dark:text-green-200">
              Data Status: {{ props.firmReceives?.length || 0 }} Firm Receives | {{ props.flocks?.length || 0 }} Flocks | {{ props.sheds?.length || 0 }} Sheds loaded
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          
          <!-- Firm Receive Code Dropdown -->
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
              <Package class="h-4 w-4" />
              Firm Receive Transaction No
            </Label>
            <div class="firm-receive-dropdown relative">
              <button
                type="button"
                @click.stop="showFirmReceiveDropdown = !showFirmReceiveDropdown"
                class="flex w-full items-center justify-between rounded-xl border border-gray-300 bg-white px-4 py-3 shadow-sm transition-all duration-200 hover:border-purple-500 hover:shadow-md focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <span class="flex items-center gap-3">
                  <div class="h-2 w-2 rounded-full bg-purple-500"></div>
                  {{ selectedFirmReceive ? selectedFirmReceive.transaction_no : 'Select Firm Receive Transaction No' }}
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
                    <h3 class="font-semibold text-gray-900 dark:text-white">Select Firm Receive</h3>
                    <div class="relative mt-3">
                      <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                      <input
                        v-model="firmReceiveSearchQuery"
                        type="text"
                        placeholder="Search firm receives..."
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 pl-10 pr-4 py-2 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
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
                      @click.stop="selectedJobId = fr.id; showFirmReceiveDropdown = false; toggleInfo()"
                      class="flex w-full items-center gap-4 px-6 py-4 text-left hover:bg-purple-50 dark:hover:bg-gray-700 transition-colors duration-200 border-b border-gray-100 dark:border-gray-600 last:border-b-0"
                      :class="{ 'bg-purple-100 dark:bg-purple-900': selectedJobId == fr.id }"
                    >
                      <div class="h-3 w-3 rounded-full bg-purple-500 flex-shrink-0"></div>
                      <div class="flex-1">
                        <div class="font-semibold text-gray-900 dark:text-white">{{ fr.transaction_no }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Flock: {{ fr.flock_name }}</div>
                        <div class="text-xs text-gray-400 dark:text-gray-500">Total: {{ fr.firm_total_qty }}</div>
                      </div>
                      <CheckCircle2 v-if="selectedJobId == fr.id" class="h-4 w-4 text-purple-500 flex-shrink-0" />
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
          </div>

          <!-- Flock Display (Auto-selected) -->
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
              <Users class="h-4 w-4" />
              Flock (Auto-selected)
            </Label>
            <div class="rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 shadow-sm dark:border-gray-600 dark:bg-gray-700">
              <span class="flex items-center gap-3 text-gray-600 dark:text-gray-400">
                <div class="h-2 w-2 rounded-full bg-emerald-500"></div>
                {{ selectedFlock ? selectedFlock.name : (selectedFirmReceive ? selectedFirmReceive.flock_name : 'Select Firm Receive First') }}
              </span>
            </div>
          </div>

          <!-- Shed Dropdown -->
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
              <Home class="h-4 w-4" />
              Production Shed
            </Label>
            <div class="shed-dropdown relative">
              <button
                type="button"
                @click.stop="showShedDropdown = !showShedDropdown"
                class="flex w-full items-center justify-between rounded-xl border border-gray-300 bg-white px-4 py-3 shadow-sm transition-all duration-200 hover:border-orange-500 hover:shadow-md focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
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
                class="fixed inset-0 z-[9997] flex items-start justify-center pt-20"
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
                        placeholder="Search production sheds..."
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 pl-10 pr-4 py-2 text-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        @click.stop
                      />
                    </div>
                  </div>

                  <!-- Shed List -->
                  <div class="max-h-96 overflow-y-auto">
                    <div v-if="(props.sheds?.length || 0) === 0" class="px-6 py-8 text-center">
                      <AlertCircle class="mx-auto h-8 w-8 text-red-500" />
                      <div class="mt-2 font-medium text-red-600">No Production Sheds Available</div>
                      <div class="text-sm text-gray-500">Please add production sheds first</div>
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
              Firm Receive Details
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 text-sm">
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Transaction No:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedFirmReceive?.transaction_no }}</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Flock Name:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedFirmReceive?.flock_name }}</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Company:</span><div class="text-gray-900 dark:text-gray-100">{{ props.companies.find(c => c.id === form.receiving_company_id)?.name }}</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Female Qty:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedFirmReceive?.firm_female_qty }}</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Male Qty:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedFirmReceive?.firm_male_qty }}</div></div>
              <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Total Qty:</span><div class="font-bold text-purple-900 dark:text-purple-100">{{ selectedFirmReceive?.firm_total_qty }}</div></div>
            </div>
          </div>
        </transition>
      </div>
    </div>

    <!-- Production Shed Receive Quantities Card -->
    <div class="relative overflow-hidden rounded-2xl border-0 bg-gradient-to-br from-white via-emerald-50 to-white p-8 shadow-xl ring-1 ring-gray-200 dark:from-gray-800 dark:via-emerald-900/20 dark:to-gray-800 dark:ring-gray-700">
      <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-emerald-500/20 to-green-500/20"></div>
      <div class="absolute -bottom-4 -left-4 h-32 w-32 rounded-full bg-gradient-to-br from-teal-500/10 to-emerald-500/10"></div>
      
      <div class="relative">
        <div class="mb-8 flex items-center gap-3">
          <div class="rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 p-3 shadow-lg">
            <Users class="h-6 w-6 text-white" />
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Production Shed Chick Quantities</h2>
            <p class="text-gray-600 dark:text-gray-400">Enter chick quantities received by production shed</p>
          </div>
        </div>

        <!-- Main Chick Quantities -->
        <div class="mb-8">
          <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Main Chick Quantities</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="space-y-2">
              <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Female Chick Qty</Label>
              <Input 
                v-model.number="form.shed_female_qty" 
                type="number" 
                min="0"
                class="rounded-xl border-pink-300 bg-pink-50 px-4 py-3 shadow-sm focus:border-pink-500 focus:ring-pink-500/20 dark:border-pink-600 dark:bg-pink-900/20" 
              />
            </div>
            <div class="space-y-2">
              <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Male Chick Qty</Label>
              <Input 
                v-model.number="form.shed_male_qty" 
                type="number" 
                min="0"
                class="rounded-xl border-blue-300 bg-blue-50 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-blue-500/20 dark:border-blue-600 dark:bg-blue-900/20" 
              />
            </div>
            <div class="space-y-2">
              <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Total Chick Qty</Label>
              <Input 
                type="number" 
                :value="form.shed_total_qty" 
                readonly 
                class="rounded-xl border-gray-300 bg-gradient-to-r from-gray-100 to-gray-50 px-4 py-3 font-bold text-gray-700 shadow-sm cursor-not-allowed dark:border-gray-600 dark:from-gray-700 dark:to-gray-800 dark:text-gray-300" 
              />
            </div>
          </div>
        </div>

        <!-- Shortage & Excess Quantities -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Shortage Section -->
          <div class="rounded-xl border border-red-200 bg-gradient-to-br from-red-50 to-pink-50 p-6 dark:border-red-800 dark:from-red-900/20 dark:to-pink-900/20">
            <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-red-800 dark:text-red-200">
              <AlertCircle class="h-5 w-5" />
              Shortage Chicks
            </h3>
            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label class="text-sm font-semibold text-red-700 dark:text-red-300">Male Shortage</Label>
                  <Input 
                    type="number" 
                    v-model.number="form.shed_sortage_male_box" 
                    min="0"
                    class="rounded-xl border-red-300 bg-red-50 px-4 py-2 text-red-800 focus:border-red-500 focus:ring-red-500/20 dark:border-red-600 dark:bg-red-900/30 dark:text-red-200" 
                  />
                </div>
                <div class="space-y-2">
                  <Label class="text-sm font-semibold text-red-700 dark:text-red-300">Female Shortage</Label>
                  <Input 
                    type="number" 
                    v-model.number="form.shed_sortage_female_box" 
                    min="0"
                    class="rounded-xl border-red-300 bg-red-50 px-4 py-2 text-red-800 focus:border-red-500 focus:ring-red-500/20 dark:border-red-600 dark:bg-red-900/30 dark:text-red-200" 
                  />
                </div>
              </div>
              <div class="space-y-2">
                <Label class="text-sm font-semibold text-red-700 dark:text-red-300">Total Shortage</Label>
                <Input 
                  type="number" 
                  v-model.number="form.shed_sortage_box_qty" 
                  readonly 
                  class="rounded-xl border-red-300 bg-gradient-to-r from-red-100 to-red-50 px-4 py-2 font-bold text-red-800 cursor-not-allowed dark:border-red-600 dark:from-red-800/50 dark:to-red-900/50 dark:text-red-200"
                />
              </div>
            </div>
          </div>

          <!-- Excess Section -->
          <div class="rounded-xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-green-50 p-6 dark:border-emerald-800 dark:from-emerald-900/20 dark:to-green-900/20">
            <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-emerald-800 dark:text-emerald-200">
              <CheckCircle2 class="h-5 w-5" />
              Excess Chicks
            </h3>
            <div class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                  <Label class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Male Excess</Label>
                  <Input 
                    type="number" 
                    v-model.number="form.shed_excess_male_box" 
                    min="0"
                    class="rounded-xl border-emerald-300 bg-emerald-50 px-4 py-2 text-emerald-800 focus:border-emerald-500 focus:ring-emerald-500/20 dark:border-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-200" 
                  />
                </div>
                <div class="space-y-2">
                  <Label class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Female Excess</Label>
                  <Input 
                    type="number" 
                    v-model.number="form.shed_excess_female_box" 
                    min="0"
                    class="rounded-xl border-emerald-300 bg-emerald-50 px-4 py-2 text-emerald-800 focus:border-emerald-500 focus:ring-emerald-500/20 dark:border-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-200" 
                  />
                </div>
              </div>
              <div class="space-y-2">
                <Label class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Total Excess</Label>
                <Input 
                  type="number" 
                  v-model.number="form.shed_excess_box_qty" 
                  readonly 
                  class="rounded-xl border-emerald-300 bg-gradient-to-r from-emerald-100 to-emerald-50 px-4 py-2 font-bold text-emerald-800 cursor-not-allowed dark:border-emerald-600 dark:from-emerald-800/50 dark:to-emerald-900/50 dark:text-emerald-200"
                />
              </div>
            </div>
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
        href="/production-shed-receive"
        class="rounded-xl border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
      >
        Cancel
      </Link>
      <Button 
        type="submit" 
        :disabled="form.processing"
        class="group relative overflow-hidden rounded-xl bg-gradient-to-r from-purple-600 to-purple-700 px-8 py-3 font-semibold text-white shadow-lg transition-all duration-300 hover:from-purple-700 hover:to-purple-800 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-purple-500/50 disabled:opacity-50"
      >
        <span class="relative z-10 flex items-center gap-2">
          <Save class="h-4 w-4" />
          {{ form.processing ? 'Saving...' : 'Save & Submit' }}
        </span>
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-20 group-hover:translate-x-full"></div>
      </Button>
    </div>

  </form>
</AppLayout>
</template>
