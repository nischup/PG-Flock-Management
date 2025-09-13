<script setup lang="ts">
import { Link, Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { 
    ArrowLeft, 
    Package, 
    Building2, 
    Users, 
    Calendar,
    FileText,
    Edit,
    Trash2,
    CheckCircle2,
    AlertCircle,
    Info
} from 'lucide-vue-next'
import dayjs from 'dayjs'

// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Production Shed Receive', href: '/production-shed-receive' },
  { title: 'View Details', href: '' },
]

// Props
const props = defineProps<{
  shedReceive: any
  firmReceive: any
}>()

// Computed properties for better data handling
const statusBadge = computed(() => {
  const status = props.shedReceive?.status
  if (status === 1) {
    return { text: 'Active', class: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }
  }
  return { text: 'Inactive', class: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' }
})

const totalShortage = computed(() => {
  return (props.shedReceive?.shed_sortage_male_box || 0) + (props.shedReceive?.shed_sortage_female_box || 0)
})

const totalExcess = computed(() => {
  return (props.shedReceive?.shed_excess_male_box || 0) + (props.shedReceive?.shed_excess_female_box || 0)
})
</script>

<template>
<AppLayout :breadcrumbs="breadcrumbs">
  <Head title="View Production Shed Receive Details" />

  <!-- Header Section -->
  <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-purple-600 via-purple-700 to-indigo-800 p-8 text-white shadow-2xl">
    <div class="absolute -right-4 -top-4 h-32 w-32 rounded-full bg-white/10"></div>
    <div class="absolute -bottom-4 -left-4 h-24 w-24 rounded-full bg-white/5"></div>
    
    <div class="relative z-10">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-4xl font-bold">Production Shed Receive Details</h1>
          <p class="mt-2 text-purple-100">View complete information about this production shed receive entry</p>
        </div>
        <div class="flex items-center gap-4">
          <Link 
            :href="route('production-shed-receive.edit', shedReceive.id)"
            class="group relative overflow-hidden rounded-xl bg-white/20 px-6 py-3 font-semibold text-white backdrop-blur-sm transition-all duration-300 hover:bg-white/30 hover:shadow-lg"
          >
            <span class="relative z-10 flex items-center gap-2">
              <Edit class="h-4 w-4" />
              Edit Record
            </span>
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-20 group-hover:translate-x-full"></div>
          </Link>
          <Link 
            href="/production-shed-receive" 
            class="group relative overflow-hidden rounded-xl bg-white/20 px-6 py-3 font-semibold text-white backdrop-blur-sm transition-all duration-300 hover:bg-white/30 hover:shadow-lg"
          >
            <span class="relative z-10 flex items-center gap-2">
              <ArrowLeft class="h-4 w-4" />
              Back to List
            </span>
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-20 group-hover:translate-x-full"></div>
          </Link>
        </div>
      </div>
    </div>
  </div>

  <div class="space-y-8">

    <!-- Basic Information Card -->
    <div class="relative rounded-2xl border-0 bg-gradient-to-br from-white via-purple-50 to-white p-8 shadow-xl ring-1 ring-gray-200 dark:from-gray-800 dark:via-purple-900/20 dark:to-gray-800 dark:ring-gray-700">
      <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-purple-500/20 to-indigo-500/20"></div>
      <div class="absolute -bottom-4 -left-4 h-32 w-32 rounded-full bg-gradient-to-br from-blue-500/10 to-purple-500/10"></div>
      
      <div class="relative">
        <div class="mb-8 flex items-center gap-3">
          <div class="rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 p-3 shadow-lg">
            <Info class="h-6 w-6 text-white" />
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Basic Information</h2>
            <p class="text-gray-600 dark:text-gray-400">Core details about this production shed receive entry</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Job Number</Label>
            <div class="rounded-xl bg-gray-50 px-4 py-3 text-gray-900 dark:bg-gray-700 dark:text-white">
              {{ shedReceive?.job_no || 'N/A' }}
            </div>
          </div>
          
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Transaction Number</Label>
            <div class="rounded-xl bg-gray-50 px-4 py-3 text-gray-900 dark:bg-gray-700 dark:text-white">
              {{ shedReceive?.transaction_no || 'N/A' }}
            </div>
          </div>

          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Status</Label>
            <div class="rounded-xl px-4 py-3">
              <span :class="statusBadge.class" class="inline-flex rounded-full px-3 py-1 text-sm font-medium">
                {{ statusBadge.text }}
              </span>
            </div>
          </div>

          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Receive Date</Label>
            <div class="rounded-xl bg-gray-50 px-4 py-3 text-gray-900 dark:bg-gray-700 dark:text-white">
              {{ dayjs(shedReceive?.created_at).format('MMM DD, YYYY') }}
            </div>
          </div>

          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Created By</Label>
            <div class="rounded-xl bg-gray-50 px-4 py-3 text-gray-900 dark:bg-gray-700 dark:text-white">
              {{ shedReceive?.created_by || 'System' }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Flock and Shed Information Card -->
    <div class="relative rounded-2xl border-0 bg-gradient-to-br from-white via-blue-50 to-white p-8 shadow-xl ring-1 ring-gray-200 dark:from-gray-800 dark:via-blue-900/20 dark:to-gray-800 dark:ring-gray-700">
      <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-blue-500/20 to-indigo-500/20"></div>
      <div class="absolute -bottom-4 -left-4 h-32 w-32 rounded-full bg-gradient-to-br from-blue-500/10 to-purple-500/10"></div>
      
      <div class="relative">
        <div class="mb-8 flex items-center gap-3">
          <div class="rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 p-3 shadow-lg">
            <Users class="h-6 w-6 text-white" />
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Flock & Shed Information</h2>
            <p class="text-gray-600 dark:text-gray-400">Details about the flock and production shed</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Flock Name</Label>
            <div class="rounded-xl bg-blue-50 px-4 py-3 text-blue-900 dark:bg-blue-900/20 dark:text-blue-100">
              {{ shedReceive?.flock?.name || shedReceive?.flock_name || 'N/A' }}
            </div>
          </div>

          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Production Shed</Label>
            <div class="rounded-xl bg-orange-50 px-4 py-3 text-orange-900 dark:bg-orange-900/20 dark:text-orange-100">
              {{ shedReceive?.shed?.name || shedReceive?.shed_name || 'N/A' }}
            </div>
          </div>

          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Company</Label>
            <div class="rounded-xl bg-purple-50 px-4 py-3 text-purple-900 dark:bg-purple-900/20 dark:text-purple-100">
              {{ shedReceive?.company?.name || shedReceive?.company_name || 'N/A' }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Chick Quantities Card -->
    <div class="relative rounded-2xl border-0 bg-gradient-to-br from-white via-emerald-50 to-white p-8 shadow-xl ring-1 ring-gray-200 dark:from-gray-800 dark:via-emerald-900/20 dark:to-gray-800 dark:ring-gray-700">
      <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-emerald-500/20 to-green-500/20"></div>
      <div class="absolute -bottom-4 -left-4 h-32 w-32 rounded-full bg-gradient-to-br from-teal-500/10 to-emerald-500/10"></div>
      
      <div class="relative">
        <div class="mb-8 flex items-center gap-3">
          <div class="rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 p-3 shadow-lg">
            <Package class="h-6 w-6 text-white" />
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Chick Quantities</h2>
            <p class="text-gray-600 dark:text-gray-400">Quantities received and calculated totals</p>
          </div>
        </div>

        <!-- Main Quantities -->
        <div class="mb-8">
          <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Main Quantities</h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="space-y-2">
              <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Female Chicks</Label>
              <div class="rounded-xl bg-pink-50 px-4 py-3 text-pink-900 dark:bg-pink-900/20 dark:text-pink-100 font-semibold">
                {{ shedReceive?.shed_female_qty || 0 }}
              </div>
            </div>
            <div class="space-y-2">
              <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Male Chicks</Label>
              <div class="rounded-xl bg-blue-50 px-4 py-3 text-blue-900 dark:bg-blue-900/20 dark:text-blue-100 font-semibold">
                {{ shedReceive?.shed_male_qty || 0 }}
              </div>
            </div>
            <div class="space-y-2">
              <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Total Chicks</Label>
              <div class="rounded-xl bg-gradient-to-r from-gray-100 to-gray-50 px-4 py-3 text-gray-900 dark:from-gray-700 dark:to-gray-800 dark:text-gray-100 font-bold text-lg">
                {{ shedReceive?.shed_total_qty || 0 }}
              </div>
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
                  <div class="rounded-xl bg-red-100 px-4 py-2 text-red-800 dark:bg-red-900/30 dark:text-red-200 font-semibold">
                    {{ shedReceive?.shed_sortage_male_box || 0 }}
                  </div>
                </div>
                <div class="space-y-2">
                  <Label class="text-sm font-semibold text-red-700 dark:text-red-300">Female Shortage</Label>
                  <div class="rounded-xl bg-red-100 px-4 py-2 text-red-800 dark:bg-red-900/30 dark:text-red-200 font-semibold">
                    {{ shedReceive?.shed_sortage_female_box || 0 }}
                  </div>
                </div>
              </div>
              <div class="space-y-2">
                <Label class="text-sm font-semibold text-red-700 dark:text-red-300">Total Shortage</Label>
                <div class="rounded-xl bg-gradient-to-r from-red-100 to-red-50 px-4 py-2 text-red-800 dark:from-red-800/50 dark:to-red-900/50 dark:text-red-200 font-bold">
                  {{ totalShortage }}
                </div>
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
                  <div class="rounded-xl bg-emerald-100 px-4 py-2 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200 font-semibold">
                    {{ shedReceive?.shed_excess_male_box || 0 }}
                  </div>
                </div>
                <div class="space-y-2">
                  <Label class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Female Excess</Label>
                  <div class="rounded-xl bg-emerald-100 px-4 py-2 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200 font-semibold">
                    {{ shedReceive?.shed_excess_female_box || 0 }}
                  </div>
                </div>
              </div>
              <div class="space-y-2">
                <Label class="text-sm font-semibold text-emerald-700 dark:text-emerald-300">Total Excess</Label>
                <div class="rounded-xl bg-gradient-to-r from-emerald-100 to-emerald-50 px-4 py-2 text-emerald-800 dark:from-emerald-800/50 dark:to-emerald-900/50 dark:text-emerald-200 font-bold">
                  {{ totalExcess }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Firm Receive Information Card (if available) -->
    <div v-if="firmReceive" class="relative rounded-2xl border-0 bg-gradient-to-br from-white via-amber-50 to-white p-8 shadow-xl ring-1 ring-gray-200 dark:from-gray-800 dark:via-amber-900/20 dark:to-gray-800 dark:ring-gray-700">
      <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-amber-500/20 to-orange-500/20"></div>
      <div class="absolute -bottom-4 -left-4 h-32 w-32 rounded-full bg-gradient-to-br from-yellow-500/10 to-amber-500/10"></div>
      
      <div class="relative">
        <div class="mb-8 flex items-center gap-3">
          <div class="rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 p-3 shadow-lg">
            <FileText class="h-6 w-6 text-white" />
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Related Firm Receive</h2>
            <p class="text-gray-600 dark:text-gray-400">Information about the source firm receive</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Firm Receive Job No</Label>
            <div class="rounded-xl bg-amber-50 px-4 py-3 text-amber-900 dark:bg-amber-900/20 dark:text-amber-100">
              {{ firmReceive?.job_no || 'N/A' }}
            </div>
          </div>
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Firm Female Qty</Label>
            <div class="rounded-xl bg-amber-50 px-4 py-3 text-amber-900 dark:bg-amber-900/20 dark:text-amber-100">
              {{ firmReceive?.firm_female_qty || 0 }}
            </div>
          </div>
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Firm Male Qty</Label>
            <div class="rounded-xl bg-amber-50 px-4 py-3 text-amber-900 dark:bg-amber-900/20 dark:text-amber-100">
              {{ firmReceive?.firm_male_qty || 0 }}
            </div>
          </div>
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Firm Total Qty</Label>
            <div class="rounded-xl bg-gradient-to-r from-amber-100 to-amber-50 px-4 py-3 text-amber-900 dark:from-amber-800/50 dark:to-amber-900/50 dark:text-amber-100 font-bold">
              {{ firmReceive?.firm_total_qty || 0 }}
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Remarks Section -->
    <div v-if="shedReceive?.remarks" class="relative rounded-2xl border-0 bg-gradient-to-br from-white via-gray-50 to-white p-8 shadow-xl ring-1 ring-gray-200 dark:from-gray-800 dark:via-gray-900/20 dark:to-gray-800 dark:ring-gray-700">
      <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-gray-500/20 to-gray-600/20"></div>
      <div class="absolute -bottom-4 -left-4 h-32 w-32 rounded-full bg-gradient-to-br from-gray-500/10 to-gray-600/10"></div>
      
      <div class="relative">
        <div class="mb-6 flex items-center gap-3">
          <div class="rounded-xl bg-gradient-to-br from-gray-500 to-gray-600 p-3 shadow-lg">
            <Info class="h-6 w-6 text-white" />
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Additional Remarks</h2>
            <p class="text-gray-600 dark:text-gray-400">Notes and observations about this entry</p>
          </div>
        </div>

        <div class="space-y-2">
          <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Remarks</Label>
          <div class="rounded-xl border border-gray-300 bg-white px-4 py-3 text-gray-900 dark:border-gray-600 dark:bg-gray-700 dark:text-white whitespace-pre-wrap">
            {{ shedReceive?.remarks }}
          </div>
        </div>
      </div>
    </div>

  </div>
</AppLayout>
</template>
