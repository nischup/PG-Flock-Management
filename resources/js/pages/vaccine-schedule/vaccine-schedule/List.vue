<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref } from "vue";

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Vaccine', href: '/flock' },
  { title: 'Vaccine-Schedule', href: '/flock/assign' },
];

// Table data
const flocks = ref<any[]>([]);

// Dropdown options
const projectOptions = ["PBL", "PCL"];
const flockOptions = ["000001", "000002", "000003"];
const batchOptions = ["Batch A", "Batch B", "Batch C"];
const diseaseOptions = ["New Castle", "IBD", "Marek"];
const vaccineOptions = ["Lasota", "Gumboro", "HVT"];

// Modal states
const showScheduleModal = ref(false);
const showVaccineModal = ref(false);

// Form data (schedule)
const newSchedule = ref({
  project: "",
  flock: "",
  batch: "",
  disease: "",
  vaccine: "",
  age: "",
  lastVaccination: "",
  nextVaccination: "",
});

// Form data (vaccine)
const newVaccine = ref({
  name: "",
  type: "",
  applicator: "",
  dose: "",
  notes: "",
});

// Save new schedule
const saveSchedule = () => {
  flocks.value.push({
    id: flocks.value.length + 1,
    ...newSchedule.value,
  });

  // Reset form
  newSchedule.value = {
    project: "",
    flock: "",
    batch: "",
    disease: "",
    vaccine: "",
    age: "",
    lastVaccination: "",
    nextVaccination: "",
  };

  showScheduleModal.value = false;
};

// Save new vaccine
const saveVaccine = () => {
  // Add vaccine to options dynamically
  vaccineOptions.push(newVaccine.value.name);

  // Reset form
  newVaccine.value = {
    name: "",
    type: "",
    applicator: "",
    dose: "",
    notes: "",
  };

  showVaccineModal.value = false;
};
</script>

<template>
  <Head title="Vaccine Schedule" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-4 p-4">

      <!-- Action Buttons -->
      <div class="flex justify-end gap-3">
        <!-- Add New Vaccine -->
        <button
          @click="showVaccineModal = true"
          class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Add New Vaccine
        </button>

        <!-- Vaccination Schedule -->
        <button
          @click="showScheduleModal = true"
          class="flex items-center gap-2 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-6 4h6M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          Vaccination Schedule
        </button>

        <button
            class="flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6h6v6m-6-6V7h6v4m-6 0H5l7-7 7 7h-4" />
            </svg>
            Vaccine Routing
        </button>

        <!-- View Used Vaccine -->
        <button
            class="flex items-center gap-2 px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            View Used Vaccine
        </button>

      </div>

      <!-- Table -->
      <div class="overflow-x-auto rounded-xl shadow bg-white dark:bg-gray-800 mt-4">
        <table class="w-full text-left border-collapse">
          <thead class="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th class="px-4 py-2 border-b">#SL</th>
              <th class="px-4 py-2 border-b">Project Name</th>
              <th class="px-4 py-2 border-b">Flock No</th>
              <th class="px-4 py-2 border-b">Batch No</th>
              <th class="px-4 py-2 border-b">Breed Type</th>
              <th class="px-4 py-2 border-b">Disease</th>
              <th class="px-4 py-2 border-b">Vaccine</th>
              <th class="px-4 py-2 border-b">Age</th>
              <th class="px-4 py-2 border-b">Last Vaccination</th>
              <th class="px-4 py-2 border-b">Next Vaccination</th>
              <th class="px-4 py-2 border-b">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(flock, index) in flocks"
              :key="flock.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              <td class="px-4 py-2 border-b">{{ index + 1 }}</td>
              <td class="px-4 py-2 border-b">{{ flock.project }}</td>
              <td class="px-4 py-2 border-b">{{ flock.flock }}</td>
              <td class="px-4 py-2 border-b">{{ flock.batch }}</td>
              <td class="px-4 py-2 border-b">{{ flock.disease }}</td>
              <td class="px-4 py-2 border-b">{{ flock.vaccine }}</td>
              <td class="px-4 py-2 border-b">{{ flock.age }}</td>
              <td class="px-4 py-2 border-b">{{ flock.lastVaccination }}</td>
              <td class="px-4 py-2 border-b">{{ flock.nextVaccination }}</td>
              <td class="px-4 py-2 border-b">
                <button class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 mr-2">
                  Edit
                </button>
              </td>
            </tr>
            <tr v-if="flocks.length === 0">
              <td colspan="10" class="text-center py-4 text-gray-500">No schedules found.</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Schedule Modal -->
      <div v-if="showScheduleModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-[700px] max-w-full p-6">
          <h2 class="text-lg font-bold mb-4">Add Vaccine Schedule</h2>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm mb-1">Project</label>
              <select v-model="newSchedule.project" class="w-full border rounded px-3 py-2">
                <option value="">Select Project</option>
                <option v-for="p in projectOptions" :key="p">{{ p }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm mb-1">Flock No</label>
              <select v-model="newSchedule.flock" class="w-full border rounded px-3 py-2">
                <option value="">Select Flock</option>
                <option v-for="f in flockOptions" :key="f">{{ f }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm mb-1">Batch No</label>
              <select v-model="newSchedule.batch" class="w-full border rounded px-3 py-2">
                <option value="">Select Batch</option>
                <option v-for="b in batchOptions" :key="b">{{ b }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm mb-1">Breed</label>
              <select v-model="newSchedule.disease" class="w-full border rounded px-3 py-2">
                <option value="">Select Breed Type</option>
                <option>EP</option>
                <option>IR</option>
              </select>
            </div>
            <div>
              <label class="block text-sm mb-1">Disease</label>
              <select v-model="newSchedule.disease" class="w-full border rounded px-3 py-2">
                <option value="">Select Disease</option>
                <option v-for="d in diseaseOptions" :key="d">{{ d }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm mb-1">Vaccine</label>
              <select v-model="newSchedule.vaccine" class="w-full border rounded px-3 py-2">
                <option value="">Select Vaccine</option>
                <option v-for="v in vaccineOptions" :key="v">{{ v }}</option>
              </select>
            </div>
            <div>
              <label class="block text-sm mb-1">Age</label>
              <input v-model="newSchedule.age" type="text" class="w-full border rounded px-3 py-2" />
            </div>
            <div>
              <label class="block text-sm mb-1">Next Vaccination</label>
              <input v-model="newSchedule.nextVaccination" type="date" class="w-full border rounded px-3 py-2" />
            </div>
          </div>

          <div class="flex justify-end mt-6">
            <button class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 mr-2" @click="showScheduleModal = false">
              Cancel
            </button>
            <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" @click="saveSchedule">
              Save
            </button>
          </div>
        </div>
      </div>

      <!-- Vaccine Modal -->
      <div v-if="showVaccineModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-[600px] max-w-full p-6">
          <h2 class="text-lg font-bold mb-4">Add New Vaccine</h2>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm mb-1">Vaccine Name</label>
              <input v-model="newVaccine.name" type="text" class="w-full border rounded px-3 py-2" />
            </div>
            <div>
              <label class="block text-sm mb-1">Vaccine Type</label>
                 <select v-model="newSchedule.vaccine" class="w-full border rounded px-3 py-2">
                <option value="">Select Vaccine</option>
                <option> Live </option>
                <option> Killed </option>
              </select>
            </div>
            <div>
              <label class="block text-sm mb-1">Applicator</label>
              <input v-model="newVaccine.applicator" type="text" class="w-full border rounded px-3 py-2" />
            </div>
            <div>
              <label class="block text-sm mb-1">Dose</label>
              <input v-model="newVaccine.dose" type="text" class="w-full border rounded px-3 py-2" />
            </div>
            <div class="col-span-2">
              <label class="block text-sm mb-1">Notes</label>
              <textarea v-model="newVaccine.notes" class="w-full border rounded px-3 py-2"></textarea>
            </div>
          </div>

          <div class="flex justify-end mt-6">
            <button class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 mr-2" @click="showVaccineModal = false">
              Cancel
            </button>
            <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700" @click="saveVaccine">
              Save
            </button>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>
