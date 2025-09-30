<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from "vue";

// Props
interface Props {
  vaccineTypes: Array<{
    id: number;
    name: string;
  }>;
  companies: Array<{
    id: number;
    name: string;
  }>;
  projects: Array<{
    id: number;
    company_id: number;
    name: string;
    code: string;
  }>;
  sheds: Array<{
    id: number;
    name: string;
  }>;
  breedTypes: Array<{
    id: number;
    name: string;
  }>;
  diseases: Array<{
    id: number;
    name: string;
  }>;
  vaccines: Array<{
    id: number;
    name: string;
  }>;
  flocks: Array<{
    id: number;
    name: string;
    code: string;
  }>;
  batches: Array<{
    id: number;
    name: string;
  }>;
  upcomingVaccines: Array<{
    id: number;
    company_id: number;
    company_name: string;
    project_id: number;
    project_name: string;
    project_code: string;
    flock_id: number;
    flock_name: string;
    flock_code: string;
    flock_no: string;
    shed_id: number;
    shed_name: string;
    batch_id: number;
    batch_name: string;
    batch_no: string;
    breed_type_id: number;
    breed_type_name: string;
    status: number;
    created_at: string;
    updated_at: string;
    details: Array<{
      id: number;
      disease_id: number;
      disease_name: string;
      vaccine_id: number;
      vaccine_name: string;
      age: string;
      vaccination_date: string;
      next_vaccination_date: string | null;
      status: string;
      notes: string | null;
      administered_by: string | null;
      is_active: number;
    }>;
  }>;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Vaccine', href: '/flock' },
  { title: 'Upcoming Vaccine', href: '/upcomming-vaccine' },
];

// Table state
const expandedRows = ref<Set<number>>(new Set());

// Toggle row expansion
const toggleRow = (scheduleId: number) => {
  if (expandedRows.value.has(scheduleId)) {
    expandedRows.value.delete(scheduleId);
  } else {
    expandedRows.value.add(scheduleId);
  }
};

// Check if row is expanded
const isRowExpanded = (scheduleId: number) => {
  return expandedRows.value.has(scheduleId);
};

// Filter upcoming vaccines by urgency (next 7 days, next 15 days, etc.)
const urgentVaccines = computed(() => {
  const today = new Date();
  const next7Days = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000);
  
  return props.upcomingVaccines.filter(schedule => 
    schedule.details.some(detail => {
      if (!detail.next_vaccination_date) return false;
      const nextDate = new Date(detail.next_vaccination_date);
      return nextDate <= next7Days;
    })
  );
});

const moderateVaccines = computed(() => {
  const today = new Date();
  const next7Days = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000);
  const next15Days = new Date(today.getTime() + 15 * 24 * 60 * 60 * 1000);
  
  return props.upcomingVaccines.filter(schedule => 
    schedule.details.some(detail => {
      if (!detail.next_vaccination_date) return false;
      const nextDate = new Date(detail.next_vaccination_date);
      return nextDate > next7Days && nextDate <= next15Days;
    })
  );
});

const normalVaccines = computed(() => {
  const today = new Date();
  const next15Days = new Date(today.getTime() + 15 * 24 * 60 * 60 * 1000);
  const next30Days = new Date(today.getTime() + 30 * 24 * 60 * 60 * 1000);
  
  return props.upcomingVaccines.filter(schedule => 
    schedule.details.some(detail => {
      if (!detail.next_vaccination_date) return false;
      const nextDate = new Date(detail.next_vaccination_date);
      return nextDate > next15Days && nextDate <= next30Days;
    })
  );
});

// Get urgency level for a detail
const getUrgencyLevel = (nextDate: string | null) => {
  if (!nextDate) return 'normal';
  
  const today = new Date();
  const nextVaccineDate = new Date(nextDate);
  const daysUntil = Math.ceil((nextVaccineDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));
  
  if (daysUntil <= 7) return 'urgent';
  if (daysUntil <= 15) return 'moderate';
  return 'normal';
};

// Get urgency color
const getUrgencyColor = (level: string) => {
  switch (level) {
    case 'urgent': return 'text-red-600 bg-red-100 dark:bg-red-900 dark:text-red-200';
    case 'moderate': return 'text-yellow-600 bg-yellow-100 dark:bg-yellow-900 dark:text-yellow-200';
    default: return 'text-green-600 bg-green-100 dark:bg-green-900 dark:text-green-200';
  }
};

// Format date
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

// Get days until vaccination
const getDaysUntil = (nextDate: string | null) => {
  if (!nextDate) return 'N/A';
  
  const today = new Date();
  const nextVaccineDate = new Date(nextDate);
  const daysUntil = Math.ceil((nextVaccineDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));
  
  if (daysUntil === 0) return 'Today';
  if (daysUntil === 1) return 'Tomorrow';
  if (daysUntil < 0) return `${Math.abs(daysUntil)} days overdue`;
  return `${daysUntil} days`;
};
</script>

<template>
  <Head title="Upcoming Vaccines" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-4 p-4">

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- Urgent Vaccines (Next 7 days) -->
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6">
          <div class="flex items-center">
            <div class="p-3 bg-red-100 dark:bg-red-800 rounded-lg">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-red-600 dark:text-red-400">Urgent (Next 7 days)</p>
              <p class="text-2xl font-bold text-red-900 dark:text-red-100">{{ urgentVaccines.length }}</p>
            </div>
          </div>
        </div>

        <!-- Moderate Vaccines (Next 8-15 days) -->
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6">
          <div class="flex items-center">
            <div class="p-3 bg-yellow-100 dark:bg-yellow-800 rounded-lg">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-yellow-600 dark:text-yellow-400">Moderate (8-15 days)</p>
              <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ moderateVaccines.length }}</p>
            </div>
          </div>
        </div>

        <!-- Normal Vaccines (Next 16-30 days) -->
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-6">
          <div class="flex items-center">
            <div class="p-3 bg-green-100 dark:bg-green-800 rounded-lg">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-green-600 dark:text-green-400">Normal (16-30 days)</p>
              <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ normalVaccines.length }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Upcoming Vaccines Table -->
      <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Upcoming Vaccinations</h2>
          <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Vaccines scheduled for the next 30 days</p>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full border-collapse text-left">
            <thead>
              <tr>
                <th class="border-b px-4 py-2 bg-blue-500 text-white font-semibold text-sm whitespace-nowrap">
                  S/N
                </th>
                <th class="border-b px-4 py-2 bg-green-500 text-white font-semibold text-sm whitespace-nowrap">
                  Company
                </th>
                <th class="border-b px-4 py-2 bg-purple-500 text-white font-semibold text-sm whitespace-nowrap">
                  Project
                </th>
                <th class="border-b px-4 py-2 bg-orange-500 text-white font-semibold text-sm whitespace-nowrap">
                  Flock Details
                </th>
                <th class="border-b px-4 py-2 bg-pink-500 text-white font-semibold text-sm whitespace-nowrap">
                  Breed Type
                </th>
                <th class="border-b px-4 py-2 bg-indigo-500 text-white font-semibold text-sm whitespace-nowrap">
                  Status
                </th>
                <th class="border-b px-4 py-2 bg-red-500 text-white font-semibold text-sm whitespace-nowrap">
                  Created
                </th>
                <th class="border-b px-4 py-2 bg-gray-600 text-white font-semibold text-sm whitespace-nowrap">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody>
              <template v-for="(schedule, index) in props.upcomingVaccines" :key="schedule.id">
                <!-- Main Schedule Row -->
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer" @click="toggleRow(schedule.id)">
                  <td class="border-b px-4 py-2 whitespace-nowrap">
                    <div class="flex items-center">
                      <button class="mr-2 p-1 hover:bg-gray-200 dark:hover:bg-gray-600 rounded">
                        <svg 
                          xmlns="http://www.w3.org/2000/svg" 
                          class="h-4 w-4 transition-transform duration-200"
                          :class="{ 'rotate-90': isRowExpanded(schedule.id) }"
                          fill="none" 
                          viewBox="0 0 24 24" 
                          stroke="currentColor"
                        >
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                      </button>
                      <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ index + 1 }}
                      </div>
                    </div>
                  </td>
                  <td class="border-b px-4 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ schedule.company_name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ schedule.details.length }} upcoming</div>
                  </td>
                  <td class="border-b px-4 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ schedule.project_name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ schedule.project_code }}</div>
                  </td>
                  <td class="border-b px-4 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ schedule.flock_name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ schedule.flock_code }} | Batch: {{ schedule.batch_name }}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Shed: {{ schedule.shed_name }}</div>
                  </td>
                  <td class="border-b px-4 py-2 whitespace-nowrap">
                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ schedule.breed_type_name }}</div>
                  </td>
                  <td class="border-b px-4 py-2 whitespace-nowrap">
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                          :class="schedule.status === 1 ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'">
                      {{ schedule.status === 1 ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td class="border-b px-4 py-2 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                    {{ new Date(schedule.created_at).toLocaleDateString() }}
                  </td>
                  <td class="border-b px-4 py-2 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center gap-2">
                      <button 
                        class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 transition-colors duration-200"
                        title="View Details"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Expanded Details Row -->
                <tr v-if="isRowExpanded(schedule.id)" class="bg-gray-50 dark:bg-gray-700">
                  <td colspan="8" class="border-b px-4 py-4">
                    <div class="space-y-4">
                      <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-3">Upcoming Vaccination Details</h4>
                      
                      <div class="grid gap-4">
                        <div v-for="detail in schedule.details" :key="detail.id" 
                             class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                          <div class="flex items-start justify-between">
                            <div class="flex-1">
                              <div class="flex items-center gap-3 mb-2">
                                <h5 class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                  {{ detail.vaccine_name }}
                                </h5>
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                                      :class="getUrgencyColor(getUrgencyLevel(detail.next_vaccination_date))">
                                  {{ getUrgencyLevel(detail.next_vaccination_date).toUpperCase() }}
                                </span>
                              </div>
                              
                              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                                <div>
                                  <span class="text-gray-500 dark:text-gray-400">Disease:</span>
                                  <p class="font-medium text-gray-900 dark:text-gray-100">{{ detail.disease_name }}</p>
                                </div>
                                <div>
                                  <span class="text-gray-500 dark:text-gray-400">Age:</span>
                                  <p class="font-medium text-gray-900 dark:text-gray-100">{{ detail.age }}</p>
                                </div>
                                <div>
                                  <span class="text-gray-500 dark:text-gray-400">Next Vaccination:</span>
                                  <p class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ detail.next_vaccination_date ? formatDate(detail.next_vaccination_date) : 'N/A' }}
                                  </p>
                                </div>
                                <div>
                                  <span class="text-gray-500 dark:text-gray-400">Days Until:</span>
                                  <p class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ getDaysUntil(detail.next_vaccination_date) }}
                                  </p>
                                </div>
                              </div>
                              
                              <div v-if="detail.notes" class="mt-2">
                                <span class="text-gray-500 dark:text-gray-400 text-sm">Notes:</span>
                                <p class="text-sm text-gray-900 dark:text-gray-100">{{ detail.notes }}</p>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="props.upcomingVaccines.length === 0" class="text-center py-12">
          <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No upcoming vaccines</h3>
          <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No vaccines are scheduled for the next 30 days.</p>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
