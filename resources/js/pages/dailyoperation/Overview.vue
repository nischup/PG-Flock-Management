<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps<{
  flocks: Array<{ id: number; flock_code: string }>;
  dummySummary: Record<number, Record<string, number>>;
}>();

// Tabs (all your daily operation types)
const tabs = [
  { key: 'mortality', label: 'Mortality', bg: 'bg-yellow-100', hover: 'hover:bg-yellow-200' },
  { key: 'feed', label: 'Feed (kg)', bg: 'bg-green-100', hover: 'hover:bg-green-200' },
  { key: 'water', label: 'Water (L)', bg: 'bg-blue-100', hover: 'hover:bg-blue-200' },
  { key: 'light', label: 'Light Hour', bg: 'bg-purple-100', hover: 'hover:bg-purple-200' },
  { key: 'destroy', label: 'Destroy', bg: 'bg-pink-100', hover: 'hover:bg-pink-200' },
  { key: 'cull', label: 'Cull', bg: 'bg-red-100', hover: 'hover:bg-red-200' },
  { key: 'sexing_error', label: 'Sexing Error', bg: 'bg-indigo-100', hover: 'hover:bg-indigo-200' },
  { key: 'weight', label: 'Weight', bg: 'bg-gray-100', hover: 'hover:bg-gray-200' },
  { key: 'temperature', label: 'Temperature', bg: 'bg-teal-100', hover: 'hover:bg-teal-200' },
  { key: 'humidity', label: 'Humidity', bg: 'bg-cyan-100', hover: 'hover:bg-cyan-200' },
  { key: 'medicine', label: 'Medicine', bg: 'bg-lime-100', hover: 'hover:bg-lime-200' },
  { key: 'vaccine', label: 'Vaccine', bg: 'bg-amber-100', hover: 'hover:bg-amber-200' },
];

const selectedFlock = ref('');
const startDate = ref(new Date().toISOString().substr(0, 10));
const endDate = ref(new Date().toISOString().substr(0, 10));

// Computed summary for selected flock
const summaryData = computed(() => {
  if (!selectedFlock.value) return null;
  return props.dummySummary[selectedFlock.value];
});

// Watch flock/date change to update summary (dummy example, replace with API if needed)
watch([selectedFlock, startDate, endDate], () => {
  console.log('Flock/date changed:', selectedFlock.value, startDate.value, endDate.value);
});
</script>

<template>
  <AppLayout>
    <Head title="Daily Operation Overview" />

    <div class="p-6 space-y-6">
      <!-- Flock & Date Range Selection -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label>Select Flock</label>
          <select v-model="selectedFlock" class="w-full border rounded px-3 py-2 mt-1">
            <option value="">Select Flock</option>
            <option v-for="flock in flocks" :key="flock.id" :value="flock.id">
              {{ flock.flock_code }}
            </option>
          </select>
        </div>

        <div>
          <label>Start Date</label>
          <input type="date" v-model="startDate" class="w-full border rounded px-3 py-2 mt-1" />
        </div>

        <div>
          <label>End Date</label>
          <input type="date" v-model="endDate" class="w-full border rounded px-3 py-2 mt-1" />
        </div>
      </div>

      <!-- Summary Cards -->
      <div v-if="summaryData" class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
        <Link
          v-for="tab in tabs"
          :key="tab.key"
          :href="`/details/${selectedFlock}/${tab.key}?start_date=${startDate}&end_date=${endDate}`"
          class="p-6 rounded-lg shadow cursor-pointer"
          :class="[tab.bg, tab.hover]"
        >
          <p class="text-gray-600 font-semibold">{{ tab.label }}</p>
          <p class="text-2xl font-bold">{{ summaryData[tab.key] ?? 0 }}</p>
        </Link>
      </div>

    </div>
  </AppLayout>
</template>
