<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'

import DashboardCard from '@/components/DashboardCard.vue'
import ProgressInfoBar from '@/components/BigProgressbar.vue'
import CircleProgress from '@/components/CircularProgress.vue'
import BirdStage from '@/components/BirdStage.vue'

import Datepicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

import { User, Drumstick, ShieldX, Egg, FlaskConical, PackageSearch, Factory, Syringe, Archive } from "lucide-vue-next"
import { BabyChick } from '@/icons/BabyChick'

// --- Breadcrumbs
const breadcrumbs = [{ title: 'Dashboard', href: '/dashboard' }]

// --- Tabs
const alltabs = ['Dashboard','Company','Project','Flock','Shed','Batch']
const activeTab = ref('Dashboard')

// --- Shared card dataset
const defaultCards = [
  { title: 'Total Flock', value: 120, icon: User },
  { title: 'Total Chicks', value: 500, icon: Drumstick },
  { title: 'Total Mortality', value: 50, icon: ShieldX },
  { title: 'Total Egg Collection', value: 3000, icon: Egg },
  { title: 'Total Sent for Lab', value: 25, icon: FlaskConical },
  { title: 'Total Male Chicks', value: 230, icon: BabyChick },
  { title: 'Total Female Chicks', value: 220, icon: FlaskConical },
  { title: 'Total Hatching Egg', value: 1500, icon: PackageSearch },
  { title: 'Total Commercial Egg', value: 1500, icon: PackageSearch },
  { title: 'Total Feed Consumption', value: 1000, icon: Factory },
  { title: 'Total Vaccination', value: 75, icon: Syringe },
  { title: 'Total Active Sheds', value: 8, icon: Archive },
]

// --- Filter options
const filterOptions = {
  company: ["pcl", "phl", "pbl"],
  project: ["phl1", "phl2"],
  flock: ["12", "13"],
  shed: ["1", "2", "3"],
  batch: ["A", "B"],
  date: ["Last 7 Days", "Last 1 Month", "Custom"]
}

// --- Filters state
const filters = ref({
  company: "",
  project: "",
  flock: "",
  shed: "",
  batch: "",
  date: "",
  dateRange: [null, null], // <-- Vue Datepicker range
})

// --- Tab configuration
const tabConfig = {
  Dashboard: { filters: [], cards: defaultCards },
  Company: { filters: ["company", "date"], cards: defaultCards },
  Project: { filters: ["company","project","date"], cards: defaultCards },
  Flock: { filters: ["company","project","flock","shed"], cards: defaultCards },
  Shed: { filters: ["company","project","flock","shed","date"], cards: defaultCards },
  Batch: { filters: ["company","project","flock","shed","batch","date"], cards: defaultCards },
}

// --- Active tab content
const activeContent = computed(() => tabConfig[activeTab.value] || { filters: [], cards: [] })

// --- Reactive data
const filteredCards = ref([...defaultCards])
const progressBars = ref([
  { title: "Total Eggs", progress: 85, extra: "Goal: 1000 eggs" },
  { title: "Hatchable Eggs", progress: 65, extra: "Goal: 1000 eggs" },
  { title: "Commercial", progress: 20, extra: "Goal: 1000 eggs" },
])
const circleBars = ref([
  { title:'Mortality', value:5, type:'rounded' },
  { title:'Culling', value:2, type:'rounded' },
  { title:'Male Chicks', value:8, type:'rounded' },
  { title:'Female Chicks', value:92, type:'straight' },
  { title:'Excess', value:10, type:'straight' },
  { title:'Worker', value:80, type:'straight' },
])
const birdStage = ref({ bordingTotal:120, growingTotal:180, productionTotal:250 })

// --- Watch filters & tab to update all data
watch([filters, activeTab], () => {
  // Cards
  filteredCards.value = defaultCards.map(card => ({
    ...card,
    value: Math.floor(Math.random() * 1000) + 50
  }))

  // Progress bars
  progressBars.value = progressBars.value.map(pb => ({
    ...pb,
    progress: Math.floor(Math.random() * 100)
  }))

  // Circle bars
  circleBars.value = circleBars.value.map(c => ({
    ...c,
    value: Math.floor(Math.random() * 100)
  }))

  // Bird Stage
  birdStage.value = {
    bordingTotal: Math.floor(Math.random() * 200),
    growingTotal: Math.floor(Math.random() * 200),
    productionTotal: Math.floor(Math.random() * 300)
  }
}, { deep: true })
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
          class="flex-1 text-center px-3 py-2 text-sm font-medium transition"
          :class="activeTab === tab ? 'bg-black text-white' : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'"
        >
          {{ tab }}
        </button>
      </div>
    </div>

    <!-- Filters -->
    <div v-if="activeContent.filters.length" class="flex gap-4 p-4 flex-wrap">
      <template v-for="f in activeContent.filters" :key="f">
        <select
          v-if="f !== 'date'"
          v-model="filters[f]"
          class="border rounded px-2 py-1"
        >
          <option disabled value="">Select {{ f }}</option>
          <option v-for="opt in filterOptions[f]" :key="opt" :value="opt">{{ opt }}</option>
        </select>

        <div v-else>
          <select v-model="filters.date" class="border rounded px-2 py-1">
            <option disabled value="">Select Date Range</option>
            <option v-for="opt in filterOptions.date" :key="opt" :value="opt">{{ opt }}</option>
          </select>

          <div v-if="filters.date === 'Custom'" class="flex gap-2 mt-2">
            <Datepicker
              v-model="filters.dateRange"
              :range="true"
              format="yyyy-MM-dd"
              :input-class="'border rounded px-3 py-2 w-full'"
              placeholder="Select Date Range"
              :auto-apply="true"
            />
          </div>
        </div>
      </template>
    </div>

    <!-- Progress Bars -->
    <div class="p-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <ProgressInfoBar
          v-for="(pb, i) in progressBars"
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
          :bordingTotal="birdStage.bordingTotal"
          :growingTotal="birdStage.growingTotal"
          :productionTotal="birdStage.productionTotal"
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
          v-for="(card, i) in filteredCards"
          :key="i"
          :title="card.title"
          :value="card.value"
          :icon="card.icon"
          :index="i"
        />
      </div>
    </div>

    <!-- Circles -->
    <div class="flex justify-start p-4">
      <div class="grid grid-cols-6 gap-4 w-full">
        <CircleProgress
          v-for="(c,i) in circleBars"
          :key="i"
          v-bind="c"
          colorFrom="#34D399"
          colorTo="#10B981"
        />
      </div>
    </div>

  </AppLayout>
</template>
