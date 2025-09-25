<script setup lang="ts">
import { Link, Head, useForm } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
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
    CheckCircle2,
    Trash2,
    Plus
} from 'lucide-vue-next'


// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Assign Batch', href: '/batch-assign' },
  { title: 'Edit', href: '' },
]

const props = defineProps<{
  batchAssign: {
    id: number;
    shed_receive_id: number;
    job_no: string;
    transaction_no: string;
    flock_no: number;
    flock_id: number;
    company_id: number;
    shed_id: number;
    level: number;
    batch_no: string;
    batch_female_qty: number;
    batch_male_qty: number;
    batch_total_qty: number;
    batch_received_female_qty: number;
    batch_received_male_qty: number;
    batch_received_total_qty: number;
    batch_female_mortality: number;
    batch_male_mortality: number;
    batch_total_mortality: number;
    batch_excess_female: number;
    batch_excess_male: number;
    batch_total_excess: number;
    batch_sortage_female: number;
    batch_sortage_male: number;
    batch_total_sortage: number;
    percentage: number;
    created_at: string;
    updated_at: string;
    shedReceive?: {
      id: number;
      transaction_no: string;
      flock_id: number;
      flock: string;
      shed_id: number;
      shed: string;
      company_id: number;
      company: string;
      shed_female_qty: number;
      shed_male_qty: number;
      shed_total_qty: number;
    };
  };
  shedReceives: Array<any>,
  flocks: Array<any>,
  companies: Array<any>,
  levels: Array<any>,
  batches: Array<any>
}>()

// Form state
const selectedShedReceiveId = ref<number | string>(props.batchAssign.shed_receive_id)
const shedReceiveInfo = ref<any>(props.batchAssign.shedReceive)
const showInfo = ref(true)
const isDetailsExpanded = ref(true)

// Modern dropdown states
const showShedReceiveDropdown = ref(false)
const shedReceiveSearchQuery = ref('')

// Filtered options
const filteredShedReceives = computed(() => {
    if (!shedReceiveSearchQuery.value) return props.shedReceives
    return props.shedReceives.filter(sr => 
        sr.transaction_no?.toLowerCase().includes(shedReceiveSearchQuery.value.toLowerCase()) ||
        sr.shed?.toLowerCase().includes(shedReceiveSearchQuery.value.toLowerCase()) ||
        sr.flock?.toLowerCase().includes(shedReceiveSearchQuery.value.toLowerCase())
    )
})

// Selected item display
const selectedShedReceive = computed(() => {
    return props.shedReceives.find(sr => sr.id === Number(selectedShedReceiveId.value))
})

// Close dropdowns on outside click
const handleClickOutside = (e: MouseEvent) => {
    if (!(e.target as HTMLElement).closest('.shed-receive-dropdown')) {
        showShedReceiveDropdown.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside)
})

const form = useForm({
  shed_receive_id: props.batchAssign.shed_receive_id,
  flock_no: props.batchAssign.flock_no,
  flock_id: props.batchAssign.flock_id,
  company_id: props.batchAssign.company_id,
  shed_id: props.batchAssign.shed_id,
  level: props.batchAssign.level,
  batch_no: Number(props.batchAssign.batch_no),
  batch_female_qty: props.batchAssign.batch_female_qty,
  batch_male_qty: props.batchAssign.batch_male_qty,
  batch_total_qty: props.batchAssign.batch_total_qty,
  batch_received_female_qty: props.batchAssign.batch_received_female_qty,
  batch_received_male_qty: props.batchAssign.batch_received_male_qty,
  batch_received_total_qty: props.batchAssign.batch_received_total_qty,
  batch_female_mortality: props.batchAssign.batch_female_mortality,
  batch_male_mortality: props.batchAssign.batch_male_mortality,
  batch_total_mortality: props.batchAssign.batch_total_mortality,
  batch_excess_female: props.batchAssign.batch_excess_female,
  batch_excess_male: props.batchAssign.batch_excess_male,
  batch_sortage_female: props.batchAssign.batch_sortage_female,
  batch_sortage_male: props.batchAssign.batch_sortage_male,
  percentage: props.batchAssign.percentage,
})

// Load Shed Receive Info and toggle display
function loadShedReceiveInfo() {
  const selected = props.shedReceives.find(
    (s) => s.id === Number(selectedShedReceiveId.value)
  )
  
  if (!selected) {
    shedReceiveInfo.value = null
    showInfo.value = false
    return
  }
  
  shedReceiveInfo.value = selected
  form.shed_receive_id = Number(selectedShedReceiveId.value)
  form.flock_id = selected.flock_id || 0
  form.company_id = selected.company_id || 0
  form.shed_id = selected.shed_id || 0
  showInfo.value = true
}

// Watch for shed receive selection
watch(selectedShedReceiveId, (val) => {
  if (val) {
    loadShedReceiveInfo()
  } else {
    shedReceiveInfo.value = null
    showInfo.value = false
  }
})

// Debug function to check data
const debugDropdown = () => {
    console.log('Shed Receives data:', props.shedReceives)
    console.log('Levels data:', props.levels)
    console.log('Batches data:', props.batches)
    console.log('Current batch assign:', props.batchAssign)
}

// Single batch editing - no need for add/remove functions

const submit = () => {
  form.put(`/batch-assign/${props.batchAssign.id}`, {
    onSuccess: () => {
      // Redirect to list page after successful update
      window.location.href = '/batch-assign'
    }
  })
}

// Computed: disable submit if level is empty or no shed receive selected
const isSubmitDisabled = computed(() => {
  return !selectedShedReceiveId.value || !form.level
})

// Auto-calculate total quantities for the single batch
watch(
  [
    () => form.batch_received_female_qty,
    () => form.batch_received_male_qty,
    () => form.batch_female_mortality,
    () => form.batch_male_mortality,
    () => form.batch_excess_female,
    () => form.batch_excess_male,
    () => form.batch_sortage_female,
    () => form.batch_sortage_male,
  ],
  () => {
    // Auto-calculate totals
    form.batch_received_total_qty = (form.batch_received_female_qty || 0) + (form.batch_received_male_qty || 0)
    form.batch_total_mortality = (form.batch_female_mortality || 0) + (form.batch_male_mortality || 0)
    
    form.batch_female_qty = (form.batch_received_female_qty || 0) + (form.batch_excess_female || 0) - (form.batch_female_mortality || 0) - (form.batch_sortage_female || 0)
    form.batch_male_qty = (form.batch_received_male_qty || 0) + (form.batch_excess_male || 0) - (form.batch_male_mortality || 0) - (form.batch_sortage_male || 0)
    
    form.batch_total_qty = (form.batch_female_qty || 0) + (form.batch_male_qty || 0)

    if (form.batch_received_female_qty > 0) {
      form.percentage = (form.batch_excess_female / form.batch_received_female_qty) * 100
    }
  },
  { immediate: true }
)
</script>

<template>
<AppLayout :breadcrumbs="breadcrumbs">
  <Head title="Edit Batch Assign" />

  <!-- Header Section -->
  <div class="mb-8">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Batch Assign</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">Update batch assignment details and quantities</p>
      </div>
      <Link 
        href="/batch-assign" 
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

    <!-- Shed Receive Selection Card -->
    <div class="relative rounded-2xl border-0 bg-gradient-to-br from-white via-blue-50 to-white p-8 shadow-xl ring-1 ring-gray-200 dark:from-gray-800 dark:via-blue-900/20 dark:to-gray-800 dark:ring-gray-700">
      <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-blue-500/20 to-purple-500/20"></div>
      <div class="absolute -bottom-4 -left-4 h-32 w-32 rounded-full bg-gradient-to-br from-emerald-500/10 to-blue-500/10"></div>
      
      <div class="relative">
        <div class="mb-8 flex items-center gap-3">
          <div class="rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 p-3 shadow-lg">
            <Package class="h-6 w-6 text-white" />
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Shed Receive Selection</h2>
            <p class="text-gray-600 dark:text-gray-400">Choose the shed receive to assign batches to</p>
          </div>
        </div>


        <!-- Shed Receive Dropdown -->
        <div class="space-y-2">
          <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
            <Building2 class="h-4 w-4" />
            Shed Receive
          </Label>
          <div class="shed-receive-dropdown relative">
            <button
              type="button"
              @click.stop="showShedReceiveDropdown = !showShedReceiveDropdown"
              class="flex w-full items-center justify-between rounded-xl border border-gray-300 bg-white px-4 py-3 shadow-sm transition-all duration-200 hover:border-blue-500 hover:shadow-md focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
              <span class="flex items-center gap-3">
                <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                <span v-if="selectedShedReceive" class="text-blue-600 dark:text-blue-400 font-semibold">
                  Rcv-{{ String(selectedShedReceive.id).padStart(6, '0') }}-{{ selectedShedReceive.company_short_name }}-{{ selectedShedReceive.project_name }}-{{ selectedShedReceive.flock_code || selectedShedReceive.flock }}
                </span>
                <span v-else>Select Shed Receive</span>
              </span>
              <ChevronDown class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showShedReceiveDropdown }" />
            </button>
            
            <!-- Shed Receive Dropdown -->
            <div 
              v-if="showShedReceiveDropdown" 
              class="fixed inset-0 z-[9999] flex items-start justify-center pt-20"
              @click="showShedReceiveDropdown = false"
            >
              <div 
                class="w-full max-w-md rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-600 dark:bg-gray-800"
                @click.stop
              >
                <!-- Header -->
                <div class="border-b border-gray-200 p-4 dark:border-gray-600">
                  <h3 class="font-semibold text-gray-900 dark:text-white">Select Shed Receive</h3>
                  <div class="relative mt-3">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    <input
                      v-model="shedReceiveSearchQuery"
                      type="text"
                      placeholder="Search shed receives..."
                      class="w-full rounded-lg border border-gray-300 bg-gray-50 pl-10 pr-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                      @click.stop
                    />
                  </div>
                </div>

                <!-- Shed Receive List -->
                <div class="max-h-96 overflow-y-auto">
                  <div v-if="(props.shedReceives?.length || 0) === 0" class="px-6 py-8 text-center">
                    <AlertCircle class="mx-auto h-8 w-8 text-red-500" />
                    <div class="mt-2 font-medium text-red-600">No Shed Receives Available</div>
                    <div class="text-sm text-gray-500">Please create shed receives first</div>
                  </div>
                  <button
                    v-for="sr in filteredShedReceives"
                    :key="sr.id"
                    type="button"
                    @click.stop="selectedShedReceiveId = sr.id; showShedReceiveDropdown = false"
                    class="flex w-full items-center gap-4 px-6 py-4 text-left hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors duration-200 border-b border-gray-100 dark:border-gray-600 last:border-b-0"
                    :class="{ 'bg-blue-100 dark:bg-blue-900': selectedShedReceiveId == sr.id }"
                  >
                    <div class="h-3 w-3 rounded-full bg-blue-500 flex-shrink-0"></div>
                    <div class="flex-1">
                      <div class="font-semibold text-gray-900 dark:text-white">
                        <span class="text-blue-600 dark:text-blue-400">
                          Rcv-{{ String(sr.id).padStart(6, '0') }}-{{ sr.company_short_name }}-{{ sr.project_name }}-{{ sr.flock_code || sr.flock }}
                        </span>
                      </div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">Shed: {{ sr.shed }}</div>
                      <div class="text-xs text-gray-400 dark:text-gray-500">
                        <span class="font-medium">Total Qty:</span> {{ sr.shed_total_qty }} {{ sr.receive_type === 'pcs' ? 'Pcs' : 'Boxes' }}
                      </div>
                    </div>
                    <CheckCircle2 v-if="selectedShedReceiveId == sr.id" class="h-4 w-4 text-blue-500 flex-shrink-0" />
                  </button>
                  <div v-if="filteredShedReceives.length === 0 && (props.shedReceives?.length || 0) > 0" class="px-6 py-8 text-center text-gray-500">
                    <Search class="mx-auto h-6 w-6 text-gray-400" />
                    <div class="mt-2 text-sm">No results found for "{{ shedReceiveSearchQuery }}"</div>
                  </div>
                </div>

                <!-- Close Button -->
                <div class="border-t border-gray-200 p-4 dark:border-gray-600">
                  <Button 
                    type="button"
                    @click="showShedReceiveDropdown = false"
                    class="w-full rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                  >
                    Close
                  </Button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Shed Receive Info Display -->
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
                Shed Receive Details
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
                  <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Farm Receive Code:</span><div class="text-blue-600 dark:text-blue-400 font-semibold">Rcv-{{ String(shedReceiveInfo?.id || 0).padStart(6, '0') }}-{{ shedReceiveInfo?.company_short_name }}-{{ shedReceiveInfo?.project_name }}-{{ shedReceiveInfo?.flock_code || shedReceiveInfo?.flock }}</div></div>
                  <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Flock:</span><div class="text-gray-900 dark:text-gray-100">{{ shedReceiveInfo?.flock_code || shedReceiveInfo?.flock }}</div></div>
                  <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Shed:</span><div class="text-gray-900 dark:text-gray-100">{{ shedReceiveInfo?.shed }}</div></div>
                  <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Company:</span><div class="text-gray-900 dark:text-gray-100">{{ shedReceiveInfo?.company }}</div></div>
                  <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Female Qty:</span><div class="text-gray-900 dark:text-gray-100">{{ shedReceiveInfo?.shed_female_qty }}  {{ shedReceiveInfo?.receive_type === 'pcs' ? 'Pcs' : 'Boxes' }}</div></div>
                  <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Male Qty:</span><div class="text-gray-900 dark:text-gray-100">{{ shedReceiveInfo?.shed_male_qty }}  {{ shedReceiveInfo?.receive_type === 'pcs' ? 'Pcs' : 'Boxes' }}</div></div>
                  <div v-if="shedReceiveInfo?.receive_type !== 'pcs'" class="space-y-1">
                    <span class="font-semibold text-gray-600 dark:text-gray-300">Challan Chicks Qty:</span>
                    <div class="font-bold text-blue-900 dark:text-blue-100">
                      {{ shedReceiveInfo?.total_chicks }}
                    </div>
                  </div>
                  <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Total Qty:</span><div class="font-bold text-blue-900 dark:text-blue-100">{{ shedReceiveInfo?.shed_total_qty }} {{ shedReceiveInfo?.receive_type === 'pcs' ? 'Pcs' : 'Boxes' }}</div></div>
                </div>
              </div>
            </transition>
          </div>
        </transition>
      </div>
    </div>

    <!-- Batch Assignment Cards -->
    <div class="relative rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-600 dark:bg-gray-800">
      <div class="mb-4 flex items-center gap-2">
        <div class="rounded-lg bg-emerald-500 p-2">
          <Users class="h-4 w-4 text-white" />
        </div>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Batch Assignment</h2>
      </div>

      <!-- Single Batch Form -->
      <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-600 dark:bg-gray-700">

          <!-- Level & Batch Selection -->
          <div class="mb-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="space-y-1">
              <Label class="text-xs font-medium text-gray-700 dark:text-gray-300">Level</Label>
              <select v-model="form.level" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option disabled value="">Select Level</option>
                <option v-for="level in props.levels" :key="level.id" :value="level.id">
                  {{ level.name }}
                </option>
              </select>
            </div>

            <div class="space-y-1">
              <Label class="text-xs font-medium text-gray-700 dark:text-gray-300">Batch</Label>
              <select v-model="form.batch_no" class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                <option disabled value="">Select Batch</option>
                <option v-for="batchItem in props.batches" :key="batchItem.id" :value="batchItem.id">
                  {{ batchItem.name }}
                </option>
              </select>
            </div>
          </div>

          <!-- Main Quantities Section -->
          <div class="mb-4">
            <h4 class="mb-2 text-sm font-medium text-gray-900 dark:text-white">Main Quantities</h4>
            <div class="grid grid-cols-3 gap-3">
              <div class="space-y-1">
                <Label class="text-xs font-medium text-gray-700 dark:text-gray-300">Received Female Qty</Label>
                <Input 
                  v-model.number="form.batch_received_female_qty" 
                  type="number" 
                  min="0"
                  class="rounded-md border-pink-300 bg-pink-50 px-2 py-1 text-sm focus:border-pink-500 focus:ring-1 focus:ring-pink-500 dark:border-pink-600 dark:bg-pink-900/20" 
                />
              </div>
              <div class="space-y-1">
                <Label class="text-xs font-medium text-gray-700 dark:text-gray-300">Received Male Qty</Label>
                <Input 
                  v-model.number="form.batch_received_male_qty" 
                  type="number" 
                  min="0"
                  class="rounded-md border-blue-300 bg-blue-50 px-2 py-1 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-blue-600 dark:bg-blue-900/20" 
                />
              </div>
              <div class="space-y-1">
                <Label class="text-xs font-medium text-gray-700 dark:text-gray-300">Received Total Qty</Label>
                <Input 
                  type="number" 
                  :value="form.batch_received_total_qty" 
                  readonly 
                  class="rounded-md border-gray-300 bg-gray-100 px-2 py-1 text-sm font-medium text-gray-700 cursor-not-allowed dark:border-gray-600 dark:bg-gray-600 dark:text-gray-300" 
                />
              </div>
            </div>
          </div>

          <!-- Combined Metrics Section -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <!-- Mortality -->
            <div class="rounded-md border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-900/20">
              <h5 class="mb-2 text-xs font-medium text-red-800 dark:text-red-200">Mortality</h5>
              <div class="space-y-2">
                <div class="grid grid-cols-2 gap-2">
                  <div>
                    <Label class="text-xs text-red-700 dark:text-red-300">Female</Label>
                    <Input 
                      type="number" 
                      v-model.number="form.batch_female_mortality" 
                      min="0"
                      class="rounded border-red-300 bg-red-100 px-2 py-1 text-xs text-red-800 focus:border-red-500 focus:ring-1 focus:ring-red-500 dark:border-red-600 dark:bg-red-800/30 dark:text-red-200" 
                    />
                  </div>
                  <div>
                    <Label class="text-xs text-red-700 dark:text-red-300">Male</Label>
                    <Input 
                      type="number" 
                      v-model.number="form.batch_male_mortality" 
                      min="0"
                      class="rounded border-red-300 bg-red-100 px-2 py-1 text-xs text-red-800 focus:border-red-500 focus:ring-1 focus:ring-red-500 dark:border-red-600 dark:bg-red-800/30 dark:text-red-200" 
                    />
                  </div>
                </div>
                <div>
                  <Label class="text-xs text-red-700 dark:text-red-300">Total</Label>
                  <Input 
                    type="number" 
                    :value="form.batch_total_mortality" 
                    readonly 
                    class="rounded border-red-300 bg-red-100 px-2 py-1 text-xs font-medium text-red-800 cursor-not-allowed dark:border-red-600 dark:bg-red-800/30 dark:text-red-200"
                  />
                </div>
              </div>
            </div>

            <!-- Excess -->
            <div class="rounded-md border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-800 dark:bg-emerald-900/20">
              <h5 class="mb-2 text-xs font-medium text-emerald-800 dark:text-emerald-200">Excess</h5>
              <div class="space-y-2">
                <div class="grid grid-cols-2 gap-2">
                  <div>
                    <Label class="text-xs text-emerald-700 dark:text-emerald-300">Female</Label>
                    <Input 
                      type="number" 
                      v-model.number="form.batch_excess_female" 
                      min="0"
                      class="rounded border-emerald-300 bg-emerald-100 px-2 py-1 text-xs text-emerald-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:border-emerald-600 dark:bg-emerald-800/30 dark:text-emerald-200" 
                    />
                  </div>
                  <div>
                    <Label class="text-xs text-emerald-700 dark:text-emerald-300">Male</Label>
                    <Input 
                      type="number" 
                      v-model.number="form.batch_excess_male" 
                      min="0"
                      class="rounded border-emerald-300 bg-emerald-100 px-2 py-1 text-xs text-emerald-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 dark:border-emerald-600 dark:bg-emerald-800/30 dark:text-emerald-200" 
                    />
                  </div>
                </div>
                <!-- Percentage -->
                <div class="rounded-md border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-900/20">
                  <Label class="text-xs font-medium text-amber-700 dark:text-amber-300">Female Excess Percentage (%)</Label>
                  <Input 
                    v-model.number="form.percentage" 
                    type="number" 
                    min="0" 
                    max="100" 
                    step="0.01"
                    class="mt-1 rounded border-amber-300 bg-amber-100 px-2 py-1 text-xs text-amber-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 dark:border-amber-600 dark:bg-amber-800/30 dark:text-amber-200" 
                  />
                </div>
              </div>
            </div>

            <!-- Shortage & Percentage -->
            <div class="space-y-3">
              <!-- Shortage -->
              <div class="rounded-md border border-orange-200 bg-orange-50 p-3 dark:border-orange-800 dark:bg-orange-900/20">
                <h5 class="mb-2 text-xs font-medium text-orange-800 dark:text-orange-200">Shortage</h5>
                <div class="space-y-2">
                  <div class="grid grid-cols-2 gap-2">
                    <div>
                      <Label class="text-xs text-orange-700 dark:text-orange-300">Female</Label>
                      <Input 
                        type="number" 
                        v-model.number="form.batch_sortage_female" 
                        min="0"
                        class="rounded border-orange-300 bg-orange-100 px-2 py-1 text-xs text-orange-800 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 dark:border-orange-600 dark:bg-orange-800/30 dark:text-orange-200" 
                      />
                    </div>
                    <div>
                      <Label class="text-xs text-orange-700 dark:text-orange-300">Male</Label>
                      <Input 
                        type="number" 
                        v-model.number="form.batch_sortage_male" 
                        min="0"
                        class="rounded border-orange-300 bg-orange-100 px-2 py-1 text-xs text-orange-800 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 dark:border-orange-600 dark:bg-orange-800/30 dark:text-orange-200" 
                      />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="mb-4 mt-5">
            <h4 class="mb-2 text-sm font-medium text-gray-900 dark:text-white">Assign Quantities</h4>
            <div class="grid grid-cols-3 gap-3">
              <div class="space-y-1">
                <Label class="text-xs font-medium text-gray-700 dark:text-gray-300">Female</Label>
                <Input 
                  v-model.number="form.batch_female_qty" 
                  type="number" 
                  min="0"
                  class="rounded-md border-pink-300 bg-pink-50 px-2 py-1 text-sm focus:border-pink-500 focus:ring-1 focus:ring-pink-500 dark:border-pink-600 dark:bg-pink-900/20" 
                />
              </div>
              <div class="space-y-1">
                <Label class="text-xs font-medium text-gray-700 dark:text-gray-300">Male</Label>
                <Input 
                  v-model.number="form.batch_male_qty" 
                  type="number" 
                  min="0"
                  class="rounded-md border-blue-300 bg-blue-50 px-2 py-1 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500 dark:border-blue-600 dark:bg-blue-900/20" 
                />
              </div>
              <div class="space-y-1">
                <Label class="text-xs font-medium text-gray-700 dark:text-gray-300">Total</Label>
                <Input 
                  type="number" 
                  :value="form.batch_total_qty" 
                  readonly 
                  class="rounded-md border-gray-300 bg-gray-100 px-2 py-1 text-sm font-medium text-gray-700 cursor-not-allowed dark:border-gray-600 dark:bg-gray-600 dark:text-gray-300" 
                />
              </div>
            </div>
          </div>
      </div>
    </div>

    <!-- Submit Section -->
    <div class="flex items-center justify-end gap-4 rounded-2xl bg-gradient-to-r from-gray-50 to-white p-6 dark:from-gray-800 dark:to-gray-900">
      <Button 
        type="submit" 
        :disabled="form.processing || isSubmitDisabled"
        class="group relative overflow-hidden rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 px-8 py-3 font-semibold text-white shadow-lg transition-all duration-300 hover:from-emerald-700 hover:to-emerald-800 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/50 disabled:opacity-50"
      >
        <span class="relative z-10 flex items-center gap-2">
          <Save class="h-4 w-4" />
          {{ form.processing ? 'Saving...' : 'Update' }}
        </span>
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-20 group-hover:translate-x-full"></div>
      </Button>
    </div>

  </form>
</AppLayout>
</template>
