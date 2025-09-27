<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { type BreadcrumbItem } from '@/types'
import {
  ChevronLeft,
  Edit,
  Trash2,
  Egg,
  Calendar,
  Building2,
  MapPin,
  Users,
  Package,
  AlertTriangle,
  CheckCircle2,
  XCircle,
  Wrench
} from 'lucide-vue-next'

const props = defineProps<{
  classification: {
    id: number
    classification_date: string
    total_eggs: number
    hatching_eggs: number
    commercial_eggs: number
    rejected_eggs: number
    technical_eggs: number
    batchAssign: {
      transaction_no?: string
      shed?: { name?: string }
      batch?: { name?: string }
      flock?: { name?: string; code?: string }
      company?: { name?: string; short_name?: string }
      project?: { name?: string }
    } | null
    technicalEggs: Array<{
      id: number
      quantity: number
      note?: string
      eggType: { name?: string } | null
    }>
    rejectedEggs: Array<{
      id: number
      quantity: number
      note?: string
      eggType: { name?: string } | null
    }>
  }
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Production', href: '/production' },
  { title: 'Egg Classification', href: '/production/egg-classification' },
  { title: 'View Details', href: '' },
]

// Format date
function formatDate(dateString: string) {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Get status color based on egg counts
function getStatusColor() {
  const total = props.classification.total_eggs || 0
  const hatching = props.classification.hatching_eggs || 0
  const rejected = props.classification.rejected_eggs || 0
  const technical = props.classification.technical_eggs || 0
  
  if (hatching > total * 0.8) return 'text-green-600 bg-green-50'
  if (rejected > total * 0.3) return 'text-red-600 bg-red-50'
  if (technical > total * 0.2) return 'text-yellow-600 bg-yellow-50'
  return 'text-blue-600 bg-blue-50'
}

function getStatusText() {
  const total = props.classification.total_eggs || 0
  const hatching = props.classification.hatching_eggs || 0
  const rejected = props.classification.rejected_eggs || 0
  const technical = props.classification.technical_eggs || 0
  
  if (hatching > total * 0.8) return 'Excellent'
  if (rejected > total * 0.3) return 'High Rejection'
  if (technical > total * 0.2) return 'Technical Issues'
  return 'Normal'
}
</script>

<template>
  <Head title="Egg Classification Details" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200 shadow-sm overflow-hidden mb-6">
      <div class="bg-gradient-to-r from-gray-900 to-black px-6 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-white/20 rounded flex items-center justify-center">
              <Egg class="w-5 h-5 text-white" />
            </div>
            <div>
              <h1 class="text-2xl font-bold text-white">Egg Classification Details</h1>
              <p class="text-blue-100 text-sm">{{ formatDate(classification.classification_date) }}</p>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <Link
              href="/production/egg-classification"
              class="inline-flex items-center px-4 py-2 bg-white/10 border border-white/20 text-white hover:bg-white/20 rounded-lg transition-all duration-200 text-sm font-medium"
            >
              <ChevronLeft class="w-4 h-4 mr-2" />
              Back to List
            </Link>
            <Link
              :href="`/production/egg-classification/${classification.id}/edit`"
              class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 text-sm font-medium"
            >
              <Edit class="w-4 h-4 mr-2" />
              Edit
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Batch Information -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden mb-6">
      <div class="bg-gradient-to-r from-gray-50 to-blue-50 px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800 flex items-center">
          <Building2 class="w-5 h-5 mr-2 text-blue-600" />
          Batch Information
        </h2>
      </div>
      <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div class="space-y-2">
            <div class="flex items-center text-sm text-gray-600">
              <Package class="w-4 h-4 mr-2" />
              Transaction Number
            </div>
            <p class="text-lg font-semibold text-gray-900">{{ classification.batchAssign?.transaction_no || 'N/A' }}</p>
          </div>
          <div class="space-y-2">
            <div class="flex items-center text-sm text-gray-600">
              <MapPin class="w-4 h-4 mr-2" />
              Shed
            </div>
            <p class="text-lg font-semibold text-gray-900">{{ classification.batchAssign?.shed?.name || 'N/A' }}</p>
          </div>
          <div class="space-y-2">
            <div class="flex items-center text-sm text-gray-600">
              <Users class="w-4 h-4 mr-2" />
              Batch
            </div>
            <p class="text-lg font-semibold text-gray-900">{{ classification.batchAssign?.batch?.name || 'N/A' }}</p>
          </div>
          <div class="space-y-2">
            <div class="flex items-center text-sm text-gray-600">
              <Building2 class="w-4 h-4 mr-2" />
              Company
            </div>
            <p class="text-lg font-semibold text-gray-900">{{ classification.batchAssign?.company?.name || 'N/A' }}</p>
          </div>
          <div class="space-y-2">
            <div class="flex items-center text-sm text-gray-600">
              <Calendar class="w-4 h-4 mr-2" />
              Project
            </div>
            <p class="text-lg font-semibold text-gray-900">{{ classification.batchAssign?.project?.name || 'N/A' }}</p>
          </div>
          <div class="space-y-2">
            <div class="flex items-center text-sm text-gray-600">
              <Egg class="w-4 h-4 mr-2" />
              Flock
            </div>
            <p class="text-lg font-semibold text-gray-900">{{ classification.batchAssign?.flock?.name || 'N/A' }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Egg Classification Summary -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Total Eggs Card -->
      <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg p-6 border border-blue-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-blue-600 text-sm font-medium">Total Eggs</p>
            <p class="text-3xl font-bold text-blue-900">{{ classification.total_eggs?.toLocaleString() || 0 }}</p>
          </div>
          <div class="w-16 h-16 bg-blue-500 rounded-lg flex items-center justify-center">
            <Egg class="w-8 h-8 text-white" />
          </div>
        </div>
      </div>

      <!-- Status Card -->
      <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-lg p-6 border border-green-200">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-green-600 text-sm font-medium">Classification Status</p>
            <p class="text-3xl font-bold text-green-900">{{ getStatusText() }}</p>
          </div>
          <div class="w-16 h-16 bg-green-500 rounded-lg flex items-center justify-center">
            <CheckCircle2 class="w-8 h-8 text-white" />
          </div>
        </div>
      </div>
    </div>

    <!-- Egg Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <!-- Hatching Eggs -->
      <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-green-50 to-green-100 px-4 py-3 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-green-800 flex items-center">
            <CheckCircle2 class="w-5 h-5 mr-2" />
            Hatching Eggs
          </h3>
        </div>
        <div class="p-4">
          <p class="text-2xl font-bold text-green-600">{{ classification.hatching_eggs?.toLocaleString() || 0 }}</p>
          <p class="text-sm text-gray-600 mt-1">
            {{ classification.total_eggs > 0 ? Math.round((classification.hatching_eggs / classification.total_eggs) * 100) : 0 }}% of total
          </p>
        </div>
      </div>

      <!-- Commercial Eggs -->
      <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-red-50 to-red-100 px-4 py-3 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-red-800 flex items-center">
            <XCircle class="w-5 h-5 mr-2" />
            Commercial Eggs
          </h3>
        </div>
        <div class="p-4">
          <p class="text-2xl font-bold text-red-600">{{ classification.commercial_eggs?.toLocaleString() || 0 }}</p>
          <p class="text-sm text-gray-600 mt-1">
            {{ classification.total_eggs > 0 ? Math.round((classification.commercial_eggs / classification.total_eggs) * 100) : 0 }}% of total
          </p>
        </div>
      </div>

      <!-- Technical Eggs -->
      <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 px-4 py-3 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-yellow-800 flex items-center">
            <Wrench class="w-5 h-5 mr-2" />
            Technical Eggs
          </h3>
        </div>
        <div class="p-4">
          <p class="text-2xl font-bold text-yellow-600">{{ classification.technical_eggs?.toLocaleString() || 0 }}</p>
          <p class="text-sm text-gray-600 mt-1">
            {{ classification.total_eggs > 0 ? Math.round((classification.technical_eggs / classification.total_eggs) * 100) : 0 }}% of total
          </p>
        </div>
      </div>
    </div>

    <!-- Detailed Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Rejected Eggs Details -->
      <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-red-50 to-red-100 px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-red-800 flex items-center">
            <XCircle class="w-5 h-5 mr-2" />
            Rejected Eggs Details
          </h3>
        </div>
        <div class="p-6">
          <div v-if="classification.rejectedEggs.length > 0" class="space-y-4">
            <div
              v-for="rejected in classification.rejectedEggs"
              :key="rejected.id"
              class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200"
            >
              <div>
                <p class="font-medium text-red-800">{{ rejected.eggType?.name || 'Unknown' }}</p>
                <p v-if="rejected.note" class="text-sm text-red-600">{{ rejected.note }}</p>
              </div>
              <span class="text-lg font-bold text-red-600">{{ rejected.quantity?.toLocaleString() || 0 }}</span>
            </div>
          </div>
          <div v-else class="text-center py-8">
            <XCircle class="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <p class="text-gray-500">No rejected eggs recorded</p>
          </div>
        </div>
      </div>

      <!-- Technical Eggs Details -->
      <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-yellow-800 flex items-center">
            <Wrench class="w-5 h-5 mr-2" />
            Technical Eggs Details
          </h3>
        </div>
        <div class="p-6">
          <div v-if="classification.technicalEggs.length > 0" class="space-y-4">
            <div
              v-for="technical in classification.technicalEggs"
              :key="technical.id"
              class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-200"
            >
              <div>
                <p class="font-medium text-yellow-800">{{ technical.eggType?.name || 'Unknown' }}</p>
                <p v-if="technical.note" class="text-sm text-yellow-600">{{ technical.note }}</p>
              </div>
              <span class="text-lg font-bold text-yellow-600">{{ technical.quantity?.toLocaleString() || 0 }}</span>
            </div>
          </div>
          <div v-else class="text-center py-8">
            <Wrench class="w-12 h-12 text-gray-400 mx-auto mb-4" />
            <p class="text-gray-500">No technical eggs recorded</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
