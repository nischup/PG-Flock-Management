<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import DashboardCard from '@/components/DashboardCard.vue'
import { ref, computed } from 'vue'
import ChartCard from "../components/ChartCard.vue";
import CircleProgress from '@/components/CircularProgress.vue'
import ProgressInfoBar from '@/components/BigProgressbar.vue'
import BirdStage from '@/components/BirdStage.vue'
import { Home, User, Settings } from "lucide-vue-next"
// Breadcrumbs
const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' }
]

// Labels: last 7 days
const chartLabels = Array.from({ length: 7 }, (_, i) => {
  const d = new Date();
  d.setDate(d.getDate() - (6 - i));
  return d.toISOString().split('T')[0]; // YYYY-MM-DD
});

// Dummy data for charts
const eggCollection = [{ name: 'Egg Collection', data: [5520, 8950, 5420, 7966, 2550, 4120, 6340] }];
const Hatching = [{ name: 'Hatching', data: [5420, 4120, 6340, 5520, 5520, 8950, 2550] }];
const Revenue = [{ name: 'Revenue', data: [17000000, 120000000, 1100000000, 160000000, 1300000000, 1700000000, 120000000] }];



// Tabs
const alltabs = [
  { name: 'Dashboard' },
  { name: 'Company' },
  { name: 'Project' },
  { name: 'Flock' },
  { name: 'Shed' },
  { name: 'Batch' },
]

// Active tab
const activeTab = ref('Dashboard')

// Select tab function
function selectTab(tabName: string) {
  activeTab.value = tabName
}

// Dashboard cards
const dashboardCards = [
  { title: 'Total Flock', value: 120,icon: User },
  { title: 'Total Chicks', value: 500,icon: User },
  { title: 'Total Mortality', value: 50,icon: User },
  { title: 'Total Egg Collection', value: 3000,icon: User },
  { title: 'Total Sent for Lab', value: 25,icon: User },
  { title: 'Total Male Chicks', value: 230 ,icon: User},
  { title: 'Total Female Chicks', value: 220,icon: User },
  { title: 'Total Hatching Egg', value: 1500,icon: User },
  { title: 'Total Commercial Egg', value: 1500,icon: User },
  { title: 'Total Feed Consumption', value: 1000,icon: User },
  { title: 'Total Vaccination', value: 75,icon: User },
  { title: 'Total Active Sheds', value: 8,icon: User },
]

// Example: Shed cards (replace with your real data)
const shedCards = [
  { title: 'Shed A Chicks', value: 120 },
  { title: 'Shed A Mortality', value: 5 },
  { title: 'Shed A Egg Collection', value: 300 },
  { title: 'Shed A Feed Consumption', value: 200 },
]

// Add other tab cards if needed
const companyCards = [
  { title: 'Total Companies', value: 10 },
  { title: 'Active Projects', value: 5 },
]
const projectCards = [
  { title: 'Total Projects', value: 20 },
  { title: 'Active Flocks', value: 8 },
]
const flockCards = [
  { title: 'Total Flocks', value: 12 },
  { title: 'Current Chicks', value: 600 },
]

// Computed active cards based on active tab
const activeCards = computed(() => {
  switch (activeTab.value) {
    case 'Dashboard':
      return dashboardCards
    case 'Shed':
      return shedCards
    case 'Company':
      return companyCards
    case 'Project':
      return projectCards
    case 'Flock':
      return flockCards
    default:
      return []
  }
})
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <!-- Tabs -->
    <div class="flex justify-end p-4">
      <div class="flex bg-white dark:bg-gray-800 rounded-full shadow overflow-hidden">
        <button
          v-for="item in alltabs"
          :key="item.name"
          @click="selectTab(item.name)"
          :class="[
            'flex-1 text-center px-3 py-2 text-sm font-medium transition',
            activeTab === item.name
              ? 'bg-black text-white'
              : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700'
          ]"
        >
          {{ item.name }}
        </button>
      </div>
    </div>
    <div class="p-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">

         <ProgressInfoBar
  title="Total Eggs"
  :progress="85"
  colorFrom="#34d399"
  colorTo="#10b981"
  extra="Goal: 1000 eggs"
  tooltip="85%"
/>
    <ProgressInfoBar
  title="Hatchable Eggs"
  :progress="65"
  colorFrom="#34d399"
  colorTo="#10b981"
  extra="Goal: 1000 eggs"
  tooltip="65%"
/>

<ProgressInfoBar
  title="Commercial "
  :progress="20"
  colorFrom="#34d399"
  colorTo="#10b981"
  extra="Goal: 1000 eggs"
  tooltip="20%"
/>

<BirdStage
title="Birds Distribution"
  :bordingTotal="120"
  :growingTotal="180"
  :productionTotal="250"
  bordingColor="#fbbf24"
  growingColor="#22c55e"
  productionColor="#3b82f6"
/>



</div>
</div>
    <!-- Dashboard Cards -->
    <div class="p-6">
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <DashboardCard
          v-for="(card, index) in activeCards"
          :key="index"
          :title="card.title"
          :value="card.value"
          :index="index"
          :icon="card.icon"
        />
      </div>
      
    </div>
    <div class="flex justify-start p-4">
      <div class="grid grid-cols-6 gap-4 w-full">
        <CircleProgress
          :title="'Mortality'"
          :value="5"
          type="rounded"
          colorFrom="#ffffff"
          colorTo="#000000"
        />
        <CircleProgress
          :title="'Culling'"
          :value="2"
          type="rounded"
          colorFrom="#ffffff"
          colorTo="#000000"
        />
        <CircleProgress
          :title="'Male Chicks'"
          :value="8"
          type="rounded"
          colorFrom="#ffffff"
          colorTo="#000000"
        />
        <CircleProgress
          :title="'Female Chicks'"
          :value="92"
          type="straight"
          colorFrom="#ffe680"
          colorTo="#ff9900"
        />
        <CircleProgress
          :title="'Excess'"
          :value="10"
          type="straight"
          colorFrom="#ffe680"
          colorTo="#ff9900"
        />
        <CircleProgress
          :title="'Worker'"
          :value="80"
          type="straight"
          colorFrom="#ffe680"
          colorTo="#ff9900"
        />
        
      </div>
        </div>
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <!-- <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"> -->
                    <!-- Bar Chart -->
                    <!-- <ChartCard
                    :series="eggCollection"
                    :labels="chartLabels"
                    type="bar"
                    title="Daily Egg Collection"
                    /> -->
                <!-- </div> -->
                <!-- <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"> -->
                    <!-- Line Chart -->
                    <!-- <ChartCard
                    :series="Hatching"
                    :labels="chartLabels"
                    type="line"
                    title="Daily Hatchable Egg"
                    /> -->
                <!-- </div> -->
                <!-- <div class="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border"> -->
                    <!-- Area Chart -->
                    <!-- <ChartCard
                    :series="Revenue"
                    :labels="chartLabels"
                    type="area"
                    title="Daily Revenue"
                    /> -->
                <!-- </div> -->
            </div>
            <div class="relative min-h-[100vh] flex-1 rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border">
                <PlaceholderPattern />
            </div>
      
  </AppLayout>
</template>
