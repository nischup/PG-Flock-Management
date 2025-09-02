<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'

import DashboardCard from '@/components/DashboardCard.vue'
import ProgressInfoBar from '@/components/BigProgressbar.vue'
import CircleProgress from '@/components/CircularProgress.vue'
import BirdStage from '@/components/BirdStage.vue'

import Datepicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

// --- Import Lucide icons
import { User, Drumstick, ShieldX, Egg, FlaskConical, PackageSearch, Factory, Syringe, Archive } from "lucide-vue-next"
import { BabyChick } from '@/icons/BabyChick'

// --- Props from backend
const props = defineProps<{
  filterOptions: Record<string,string[]>,
  cards: any[],
  progressBars: any[],
  circleBars: any[],
  birdStage: any,
  filters: Record<string,string>
}>()

// --- Breadcrumbs
const breadcrumbs = [{ title: 'Dashboard', href: '/dashboard' }]

// --- Tabs
const alltabs = ['Dashboard','Company','Project','Flock','Shed','Batch']
const activeTab = ref('Dashboard')

// --- Filters reactive
const filters = ref({ ...props.filters })

// --- Icon mapping
const iconMap: Record<string, any> = {
  User, Drumstick, ShieldX, Egg, FlaskConical, PackageSearch, Factory, Syringe, Archive, BabyChick
}

// --- Watch filters & send backend request
watch(filters, (newFilters) => {
  router.get('/dashboard', newFilters, { preserveState: true, replace: true })
}, { deep: true })

// --- Tab configuration
const tabConfig = {
  Dashboard: { filters: [], cards: props.cards },
  Company: { filters: ["company", "date"], cards: props.cards },
  Project: { filters: ["company","project","date"], cards: props.cards },
  Flock: { filters: ["company","project","flock","shed"], cards: props.cards },
  Shed: { filters: ["company","project","flock","shed","date"], cards: props.cards },
  Batch: { filters: ["company","project","flock","shed","batch","date"], cards: props.cards },
}

// --- Active tab content
const activeContent = computed(() => tabConfig[activeTab.value] || { filters: [], cards: [] })
</script>

<template>
  <Head title="Dashboard" />
  <AppLayout :breadcrumbs="breadcrumbs">

    <!-- Tabs -->
    <div class="flex justify-end p-4">
      <div class="flex bg-white dark:bg-gray-800 rounded-full shadow overflow-hidden">
        <button
          v-for="tab in alltabs"
          :key="tab"
          @click="activeTab = tab"
          class="flex-1 text-center px-4 py-2 text-sm font-medium transition"
          :class="activeTab === tab ? 'bg-black text-white' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'"
        >
          {{ tab }}
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div v-if="activeContent.filters.length" class="flex gap-4 p-4 flex-wrap items-center">
      <template v-for="f in activeContent.filters" :key="f">
        <!-- Normal select filters -->
        <select
          v-if="f !== 'date'"
          v-model="filters[f]"
          class="border rounded-md shadow-md px-4 py-2 bg-white text-gray-800 hover:bg-black hover:text-white transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-black focus:ring-opacity-50 cursor-pointer"
        >
          <option disabled value="">Select {{ f }}</option>
          <option v-for="opt in props.filterOptions[f]" :key="opt" :value="opt">{{ opt }}</option>
        </select>

        <!-- Date select + Datepicker -->
        <div v-else class="flex items-center gap-2">
          <select
            v-model="filters.date"
            class="border rounded-md shadow-md px-4 py-2 bg-white text-gray-800 hover:bg-black hover:text-white transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-black focus:ring-opacity-50 cursor-pointer"
          >
            <option disabled value="">Select Date Range</option>
            <option v-for="opt in props.filterOptions.date" :key="opt" :value="opt">{{ opt }}</option>
          </select>

          <!-- Custom Datepicker: appears to the right if "Custom" is selected -->
          <Datepicker
            v-if="filters.date === 'Custom'"
            v-model="filters.dateRange"
            :range="true"
            format="yyyy-MM-dd"
            :input-class="'border rounded-md shadow-md px-4 py-2 w-64 bg-white text-gray-800 hover:bg-black hover:text-white transition-colors duration-300 focus:outline-none focus:ring-2 focus:ring-black focus:ring-opacity-50 cursor-pointer'"
            placeholder="Select Date Range"
            :auto-apply="true"
            :calendar-position="'right-start'"
          />
        </div>
      </template>
    </div>

    <!-- Progress Bars -->
    <div class="p-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <ProgressInfoBar
          v-for="(pb, i) in props.progressBars"
          :key="i"
          :title="pb.title"
          :progress="pb.progress"
          colorFrom="#34d399"
          colorTo="#10b981"
          :extra="pb.extra"
          :tooltip="pb.progress + '%'"
        />
        <BirdStage
          title="Birds Distribution"
          :bordingTotal="props.birdStage.bordingTotal"
          :growingTotal="props.birdStage.growingTotal"
          :productionTotal="props.birdStage.productionTotal"
          bordingColor="#fbbf24"
          growingColor="#22c55e"
          productionColor="#3b82f6"
        />
      </div>
    </div>

    <!-- Cards -->
    <div class="p-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <DashboardCard
          v-for="(card, i) in props.cards"
          :key="i"
          :title="card.title"
          :value="card.value"
          :icon="iconMap[card.icon]"
          :index="i"
        />
      </div>
    </div>

    <!-- Circle Bars -->
    <div class="flex justify-start p-4">
      <div class="grid grid-cols-6 gap-4 w-full">
        <CircleProgress
          v-for="(c,i) in props.circleBars"
          :key="i"
          v-bind="c"
          colorFrom="#34D399"
          colorTo="#10B981"
        />
      </div>
    </div>

  </AppLayout>
</template>
