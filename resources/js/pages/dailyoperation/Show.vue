<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import dayjs from 'dayjs'

// Props
const props = defineProps<{
  dailyOperation: any
  stage: string
}>()

// Map stage → display titles
const stageTitles: Record<string, string> = {
  brooding: 'Brooding',
  growing: 'Growing',
  laying: 'Laying / Production',
}

const currentTitle = stageTitles[props.stage] ?? props.stage

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Farm Operations', href: '/daily-operation' },
  { title: currentTitle, href: `/daily-operation/stage/${props.stage}` },
  { title: 'View Details', href: '' },
]
</script>

<template>
  <Head :title="`Daily Operation - ${currentTitle} - View`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="m-3 rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
      <!-- Header -->
      <div class="mb-6 flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">
          {{ currentTitle }} Operation Details
        </h1>
        <Link
          :href="`/daily-operation/stage/${props.stage}/${props.dailyOperation.id}/edit`"
          class="group relative overflow-hidden rounded-md px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl focus:ring-2 focus:ring-gray-500 focus:outline-none"
          style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3)"
        >
          <span class="relative z-10 flex items-center gap-2">
            <svg
              class="h-4 w-4 transition-transform duration-300 group-hover:rotate-12"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Edit Operation
          </span>
        </Link>
      </div>

      <!-- Operation Details -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Basic Information -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
          <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Basic Information</h3>
          <div class="space-y-3">
            <div>
              <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Operation Date</label>
              <p class="text-gray-900 dark:text-white">{{ dayjs(props.dailyOperation.operation_date).format('MMM DD, YYYY') }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Flock</label>
              <p class="text-gray-900 dark:text-white">{{ props.dailyOperation.batch_assign?.flock?.name || 'N/A' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Shed</label>
              <p class="text-gray-900 dark:text-white">{{ props.dailyOperation.batch_assign?.shed?.name || 'N/A' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Company</label>
              <p class="text-gray-900 dark:text-white">{{ props.dailyOperation.batch_assign?.company?.name || 'N/A' }}</p>
            </div>
            <div>
              <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Batch</label>
              <p class="text-gray-900 dark:text-white">{{ props.dailyOperation.batch_assign?.batch?.name || 'N/A' }}</p>
            </div>
          </div>
        </div>

        <!-- Mortality Information -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
          <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Mortality</h3>
          <div class="space-y-3">
            <div v-if="props.dailyOperation.mortalities?.length">
              <div v-for="mortality in props.dailyOperation.mortalities" :key="mortality.id" class="space-y-2">
                <div>
                  <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Male Mortality</label>
                  <p class="text-gray-900 dark:text-white">{{ mortality.male_qty || 0 }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Female Mortality</label>
                  <p class="text-gray-900 dark:text-white">{{ mortality.female_qty || 0 }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Mortality</label>
                  <p class="text-gray-900 dark:text-white">{{ (mortality.male_qty || 0) + (mortality.female_qty || 0) }}</p>
                </div>
              </div>
            </div>
            <div v-else>
              <p class="text-gray-500 dark:text-gray-400">No mortality data recorded</p>
            </div>
          </div>
        </div>

        <!-- Feed Information -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
          <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Feed Consumption</h3>
          <div class="space-y-3">
            <div v-if="props.dailyOperation.feeds?.length">
              <div v-for="feed in props.dailyOperation.feeds" :key="feed.id" class="space-y-2">
                <div>
                  <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Feed Type</label>
                  <p class="text-gray-900 dark:text-white">{{ feed.feed_type?.name || 'N/A' }}</p>
                </div>
                <div>
                  <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Quantity</label>
                  <p class="text-gray-900 dark:text-white">{{ feed.qty || 0 }} {{ feed.unit?.name || '' }}</p>
                </div>
              </div>
            </div>
            <div v-else>
              <p class="text-gray-500 dark:text-gray-400">No feed data recorded</p>
            </div>
          </div>
        </div>

        <!-- Water Information -->
        <div class="rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
          <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Water Consumption</h3>
          <div class="space-y-3">
            <div v-if="props.dailyOperation.waters?.length">
              <div v-for="water in props.dailyOperation.waters" :key="water.id" class="space-y-2">
                <div>
                  <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Quantity</label>
                  <p class="text-gray-900 dark:text-white">{{ water.qty || 0 }} {{ water.unit?.name || 'L' }}</p>
                </div>
              </div>
            </div>
            <div v-else>
              <p class="text-gray-500 dark:text-gray-400">No water data recorded</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Created By -->
      <div class="mt-6 rounded-lg border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800">
        <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Record Information</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <div>
            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Created By</label>
            <p class="text-gray-900 dark:text-white">{{ props.dailyOperation.creator?.name || 'N/A' }}</p>
          </div>
          <div>
            <label class="text-sm font-medium text-gray-500 dark:text-gray-400">Created At</label>
            <p class="text-gray-900 dark:text-white">{{ dayjs(props.dailyOperation.created_at).format('MMM DD, YYYY HH:mm') }}</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
