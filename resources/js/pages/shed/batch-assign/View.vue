<script setup lang="ts">
import { Link, Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { 
    ArrowLeft, 
    Package, 
    Building2, 
    Users, 
    Home,
    Edit,
    Calendar,
    FileText,
    AlertCircle,
    CheckCircle2,
    Info,
    TrendingUp,
    TrendingDown,
    Activity
} from 'lucide-vue-next'
import dayjs from 'dayjs'

// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Assign Batch', href: '/batch-assign' },
  { title: 'View', href: '' },
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
    } | null;
  };
}>()

// Computed properties for better data handling
const levelName = computed(() => {
  // This would typically come from a levels prop, but for now we'll use the level number
  return `Level ${props.batchAssign.level}`
})

const batchName = computed(() => {
  // This would typically come from a batches prop, but for now we'll use the batch_no
  return `Batch ${props.batchAssign.batch_no}`
})

const mortalityRate = computed(() => {
  if (props.batchAssign.batch_total_qty === 0) return 0
  return ((props.batchAssign.batch_total_mortality / props.batchAssign.batch_total_qty) * 100).toFixed(2)
})

const excessRate = computed(() => {
  if (props.batchAssign.batch_total_qty === 0) return 0
  return ((props.batchAssign.batch_total_excess / props.batchAssign.batch_total_qty) * 100).toFixed(2)
})

const shortageRate = computed(() => {
  if (props.batchAssign.batch_total_qty === 0) return 0
  return ((props.batchAssign.batch_total_sortage / props.batchAssign.batch_total_qty) * 100).toFixed(2)
})

const netQuantity = computed(() => {
  return props.batchAssign.batch_total_qty - props.batchAssign.batch_total_mortality + props.batchAssign.batch_total_excess - props.batchAssign.batch_total_sortage
})
</script>

<template>
<AppLayout :breadcrumbs="breadcrumbs">
  <Head title="View Batch Assign" />

  <!-- Header Section -->
  <div class="mb-8">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Batch Assignment Details</h1>
        <p class="mt-2 text-gray-600 dark:text-gray-400">View detailed information about this batch assignment</p>
      </div>
      <div class="flex items-center gap-3">
        <Link 
          :href="`/batch-assign/${props.batchAssign.id}/edit`"
          class="group relative overflow-hidden rounded-xl px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl"
          style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);"
        >
          <span class="relative z-10 flex items-center gap-2">
            <Edit class="h-4 w-4" />
            Edit
          </span>
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-20 group-hover:translate-x-full"></div>
        </Link>
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
  </div>

  <div class="space-y-8">
    <!-- Basic Information Card -->
    <div class="relative rounded-2xl border-0 bg-gradient-to-br from-white via-blue-50 to-white p-8 shadow-xl ring-1 ring-gray-200 dark:from-gray-800 dark:via-blue-900/20 dark:to-gray-800 dark:ring-gray-700">
      <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-blue-500/20 to-purple-500/20"></div>
      <div class="absolute -bottom-4 -left-4 h-32 w-32 rounded-full bg-gradient-to-br from-emerald-500/10 to-blue-500/10"></div>
      
      <div class="relative">
        <div class="mb-8 flex items-center gap-3">
          <div class="rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 p-3 shadow-lg">
            <Info class="h-6 w-6 text-white" />
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Basic Information</h2>
            <p class="text-gray-600 dark:text-gray-400">Essential details about this batch assignment</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Transaction Info -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
              <FileText class="h-5 w-5 text-blue-500" />
              Transaction Details
            </h3>
            <div class="space-y-3">
              <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Transaction No:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ props.batchAssign.transaction_no || 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Job No:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ props.batchAssign.job_no || 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Batch No:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ props.batchAssign.batch_no || 'N/A' }}</span>
              </div>
            </div>
          </div>

          <!-- Location Info -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
              <Building2 class="h-5 w-5 text-green-500" />
              Location Details
            </h3>
            <div class="space-y-3">
              <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Flock:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ props.batchAssign.shedReceive?.flock || 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Shed:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ props.batchAssign.shedReceive?.shed || 'N/A' }}</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Company:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ props.batchAssign.shedReceive?.company || 'N/A' }}</span>
              </div>
            </div>
          </div>

          <!-- Assignment Info -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
              <Package class="h-5 w-5 text-purple-500" />
              Assignment Details
            </h3>
            <div class="space-y-3">
              <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Level:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ levelName }}</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Percentage:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ props.batchAssign.percentage }}%</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Created:</span>
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ dayjs(props.batchAssign.created_at).format('MMM DD, YYYY') }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quantities Summary Card -->
    <div class="relative rounded-2xl border-0 bg-gradient-to-br from-white via-emerald-50 to-white p-8 shadow-xl ring-1 ring-gray-200 dark:from-gray-800 dark:via-emerald-900/20 dark:to-gray-800 dark:ring-gray-700">
      <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-emerald-500/20 to-green-500/20"></div>
      <div class="absolute -bottom-4 -left-4 h-32 w-32 rounded-full bg-gradient-to-br from-teal-500/10 to-emerald-500/10"></div>
      
      <div class="relative">
        <div class="mb-8 flex items-center gap-3">
          <div class="rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 p-3 shadow-lg">
            <Users class="h-6 w-6 text-white" />
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Quantities Summary</h2>
            <p class="text-gray-600 dark:text-gray-400">Overview of all quantity information</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Main Quantities -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
              <Activity class="h-5 w-5 text-blue-500" />
              Main Quantities
            </h3>
            <div class="space-y-3">
              <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Female:</span>
                <span class="text-sm font-semibold text-pink-600 dark:text-pink-400">{{ props.batchAssign.batch_female_qty }}</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Male:</span>
                <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">{{ props.batchAssign.batch_male_qty }}</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Total:</span>
                <span class="text-lg font-bold text-gray-900 dark:text-white">{{ props.batchAssign.batch_total_qty }}</span>
              </div>
            </div>
          </div>

          <!-- Mortality -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
              <AlertCircle class="h-5 w-5 text-red-500" />
              Mortality
            </h3>
            <div class="space-y-3">
              <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Female:</span>
                <span class="text-sm font-semibold text-red-600 dark:text-red-400">{{ props.batchAssign.batch_female_mortality }}</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Male:</span>
                <span class="text-sm font-semibold text-red-600 dark:text-red-400">{{ props.batchAssign.batch_male_mortality }}</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Total:</span>
                <span class="text-lg font-bold text-red-600 dark:text-red-400">{{ props.batchAssign.batch_total_mortality }}</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Rate:</span>
                <span class="text-sm font-semibold text-red-600 dark:text-red-400">{{ mortalityRate }}%</span>
              </div>
            </div>
          </div>

          <!-- Excess -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
              <TrendingUp class="h-5 w-5 text-green-500" />
              Excess
            </h3>
            <div class="space-y-3">
              <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Female:</span>
                <span class="text-sm font-semibold text-green-600 dark:text-green-400">{{ props.batchAssign.batch_excess_female }}</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Male:</span>
                <span class="text-sm font-semibold text-green-600 dark:text-green-400">{{ props.batchAssign.batch_excess_male }}</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Total:</span>
                <span class="text-lg font-bold text-green-600 dark:text-green-400">{{ props.batchAssign.batch_total_excess }}</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Rate:</span>
                <span class="text-sm font-semibold text-green-600 dark:text-green-400">{{ excessRate }}%</span>
              </div>
            </div>
          </div>

          <!-- Shortage -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white flex items-center gap-2">
              <TrendingDown class="h-5 w-5 text-orange-500" />
              Shortage
            </h3>
            <div class="space-y-3">
              <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Female:</span>
                <span class="text-sm font-semibold text-orange-600 dark:text-orange-400">{{ props.batchAssign.batch_sortage_female }}</span>
              </div>
              <div class="flex justify-between items-center py-2 border-b border-gray-200 dark:border-gray-600">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Male:</span>
                <span class="text-sm font-semibold text-orange-600 dark:text-orange-400">{{ props.batchAssign.batch_sortage_male }}</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Total:</span>
                <span class="text-lg font-bold text-orange-600 dark:text-orange-400">{{ props.batchAssign.batch_total_sortage }}</span>
              </div>
              <div class="flex justify-between items-center py-2">
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Rate:</span>
                <span class="text-sm font-semibold text-orange-600 dark:text-orange-400">{{ shortageRate }}%</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Summary Statistics Card -->
    <div class="relative rounded-2xl border-0 bg-gradient-to-br from-white via-purple-50 to-white p-8 shadow-xl ring-1 ring-gray-200 dark:from-gray-800 dark:via-purple-900/20 dark:to-gray-800 dark:ring-gray-700">
      <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-purple-500/20 to-pink-500/20"></div>
      <div class="absolute -bottom-4 -left-4 h-32 w-32 rounded-full bg-gradient-to-br from-indigo-500/10 to-purple-500/10"></div>
      
      <div class="relative">
        <div class="mb-8 flex items-center gap-3">
          <div class="rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 p-3 shadow-lg">
            <CheckCircle2 class="h-6 w-6 text-white" />
          </div>
          <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Summary Statistics</h2>
            <p class="text-gray-600 dark:text-gray-400">Key metrics and performance indicators</p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Net Quantity -->
          <div class="text-center p-6 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20">
            <div class="text-3xl font-bold text-blue-600 dark:text-blue-400 mb-2">{{ netQuantity }}</div>
            <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Net Quantity</div>
            <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">After adjustments</div>
          </div>

          <!-- Mortality Rate -->
          <div class="text-center p-6 rounded-xl bg-gradient-to-br from-red-50 to-pink-50 dark:from-red-900/20 dark:to-pink-900/20">
            <div class="text-3xl font-bold text-red-600 dark:text-red-400 mb-2">{{ mortalityRate }}%</div>
            <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Mortality Rate</div>
            <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">Loss percentage</div>
          </div>

          <!-- Efficiency Score -->
          <div class="text-center p-6 rounded-xl bg-gradient-to-br from-green-50 to-emerald-50 dark:from-green-900/20 dark:to-emerald-900/20">
            <div class="text-3xl font-bold text-green-600 dark:text-green-400 mb-2">
              {{ Math.max(0, 100 - parseFloat(mortalityRate) - parseFloat(shortageRate)).toFixed(1) }}%
            </div>
            <div class="text-sm font-medium text-gray-600 dark:text-gray-400">Efficiency Score</div>
            <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">Performance metric</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center justify-center gap-4 rounded-2xl bg-gradient-to-r from-gray-50 to-white p-6 dark:from-gray-800 dark:to-gray-900">
      <Link 
        :href="`/batch-assign/${props.batchAssign.id}/edit`"
        class="group relative overflow-hidden rounded-xl bg-gradient-to-r from-blue-600 to-blue-700 px-8 py-3 font-semibold text-white shadow-lg transition-all duration-300 hover:from-blue-700 hover:to-blue-800 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-500/50"
      >
        <span class="relative z-10 flex items-center gap-2">
          <Edit class="h-4 w-4" />
          Edit Assignment
        </span>
        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-20 group-hover:translate-x-full"></div>
      </Link>
      <Link 
        href="/batch-assign"
        class="rounded-xl border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
      >
        Back to List
      </Link>
    </div>
  </div>
</AppLayout>
</template>
