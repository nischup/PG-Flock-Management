<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from "vue";

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Production', href: '/flock' },
  { title: 'Farm Receive', href: '/flock/assign' },
];

// Dummy stats
const totalFlock = ref(50);
const assignShed = ref(250);
const assignBatch = ref(350);

// Dummy flock list
const flocks = ref([
  { id: 1, name: "000001", shed: "Shed 1", batch: "Batch A, Batch B" },
  { id: 2, name: "000002", shed: "Shed 2", batch: "Batch B" },
  { id: 3, name: "000003", shed: "Shed 3", batch: "Batch C" },
]);

const flockOptions = ref([
  { id: 1, name: "000001" },
  { id: 2, name: "000002" },
  { id: 3, name: "000003" },
]);

// Modal state
const showModal = ref(false);
const newFlockName = ref("");

// Dragging state
const position = ref({ x: 0, y: 0 });
const offset = ref({ x: 0, y: 0 });
const dragging = ref(false);

const startDrag = (event: MouseEvent) => {
  dragging.value = true;
  offset.value = {
    x: event.clientX - position.value.x,
    y: event.clientY - position.value.y,
  };
};
const onDrag = (event: MouseEvent) => {
  if (!dragging.value) return;
  position.value = {
    x: event.clientX - offset.value.x,
    y: event.clientY - offset.value.y,
  };
};
const stopDrag = () => (dragging.value = false);

// Shed
const selectedShed = ref("");
const shedOptions = ["Shed 1", "Shed 2", "Shed 3"];

// Batch (corrected as array of objects)
const batches = ref([{ batchNo: "", femaleQty: 0, maleQty: 0 }]);
const batchOptions = ["Batch A", "Batch B", "Batch C", "Batch D", "Batch E"];

const addBatch = () => {
  batches.value.push({ batchNo: "", femaleQty: 0, maleQty: 0 });
};

// Total qty across all batches
const overallTotal = computed(() =>
  batches.value.reduce((sum, b) => sum + b.femaleQty + b.maleQty, 0)
);

// Save flock
const saveFlock = () => {
  if (!newFlockName.value.trim()) return;

  flocks.value.push({
    id: flocks.value.length + 1,
    name: newFlockName.value,
    shed: selectedShed.value,
    batch: batches.value
      .map(b => `${b.batchNo} (F:${b.femaleQty}, M:${b.maleQty}, T:${b.femaleQty + b.maleQty})`)
      .join(", ")
  });

  // Reset form
  newFlockName.value = "";
  selectedShed.value = "";
  batches.value = [{ batchNo: "", femaleQty: 0, maleQty: 0 }];
  showModal.value = false;
  totalFlock.value++;
};

// Close modal on background click
const closeModal = (event: MouseEvent) => {
  const modalContent = document.getElementById("modal-content");
  if (modalContent && !modalContent.contains(event.target as Node)) {
    showModal.value = false;
  }
};


// Transfer Modal state
const showTransferModal = ref(false);
const transferFlock = ref<any>(null);

// Company list
const companyOptions = ref([
  { id: 1, name: "PBL" },
  { id: 2, name: "PCL" },

]);

const selectedCompany = ref("");

// Open transfer modal with flock data
const openTransferModal = (flock: any) => {
  transferFlock.value = flock;
  selectedCompany.value = "";
  batches.value = [{ batchNo: "", femaleQty: 0, maleQty: 0 }];
  showTransferModal.value = true;
};

// Save transfer logic
const saveTransfer = () => {
  if (!selectedCompany.value) return;

  console.log("Transferring", transferFlock.value, "to", selectedCompany.value);

  // Example: you could push to another list or send API request
  showTransferModal.value = false;
};



</script>


<template>
  <Head title="Flock List" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">

      <!-- Add Flock Button -->
      <!-- <div class="flex justify-end">
        <button
          @click="showModal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          + Assign Flock
        </button>
      </div> -->

      <!-- Cards -->
      <!-- <div class="grid gap-4 md:grid-cols-3">
        <div class="p-5 rounded-xl shadow bg-white dark:bg-gray-800">
          <p class="text-sm font-semibold">Total Flock</p>
          <p class="text-3xl font-bold mt-2">{{ totalFlock }}</p>
        </div>
        <div class="p-5 rounded-xl shadow bg-white dark:bg-gray-800">
          <p class="text-sm font-semibold">Assign Shed</p>
          <p class="text-3xl font-bold mt-2">{{ assignShed }}</p>
        </div>
        <div class="p-5 rounded-xl shadow bg-white dark:bg-gray-800">
          <p class="text-sm font-semibold">Assign Batch</p>
          <p class="text-3xl font-bold mt-2">{{ assignBatch }}</p>
        </div>
      </div> -->

      <!-- List Table -->
      <div class="overflow-x-auto rounded-xl shadow bg-white dark:bg-gray-800 mt-4">
        <table class="w-full text-left border-collapse">
          <thead class="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th class="px-4 py-2 border-b">#SL</th>
              <th class="px-4 py-2 border-b">From Project</th>
              <th class="px-4 py-2 border-b">Receive Project</th>
              <th class="px-4 py-2 border-b">Flock No</th>
              <th class="px-4 py-2 border-b">Female Qty</th>
              <th class="px-4 py-2 border-b">Male Qty</th>
              <th class="px-4 py-2 border-b">Total Qty</th>
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
              <td class="px-4 py-2 border-b">PBL</td>
              <td class="px-4 py-2 border-b">PCL</td>
              <td class="px-4 py-2 border-b">{{ flock.name }}</td>
              <td class="px-4 py-2 border-b">10000</td>
              <td class="px-4 py-2 border-b">200</td>
              <td class="px-4 py-2 border-b">10200</td>
              <td class="px-4 py-2 border-b">
                <button class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-chicken mr-2">Edit</button>
                <button
                  class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 mr-2"
                  @click="openTransferModal(flock)"
                >
                  Receive
                </button>

              </td>
            </tr>
          </tbody>
        </table>
      </div>

<!-- Transfer Modal -->
<div
  v-if="showTransferModal"
  class="fixed inset-0 bg-black/20 flex items-center justify-center z-50"
  @click="showTransferModal = false"
>
  <div
    class="bg-white rounded-lg shadow-lg w-[700px] max-w-full"
    @click.stop
  >
    <!-- Header -->
    <div class="p-3 bg-gray-200 rounded-t-lg">
      <h2 class="font-bold">Receive Flock</h2>
    </div>

    <!-- Body -->
    <div class="p-6 space-y-4">
      <!-- Company -->
 <!-- Company (From & To) -->
<div>

  <div class="grid grid-cols-2 gap-4">
    <!-- From Company -->
    <div>
      <label class="block text-xs font-medium mb-1">From</label>
      <select v-model="transferFromCompany" class="w-full border rounded px-3 py-2">
        <option disabled value="">Select From Project</option>
        <option v-for="company in companyOptions" :key="company.id" :value="company.name">
          {{ company.name }}
        </option>
      </select>
    </div>

    <!-- To Company -->
    <div>
      <label class="block text-xs font-medium mb-1">To</label>
      <select v-model="transferToCompany" class="w-full border rounded px-3 py-2">
        <option disabled value="">Select To Project</option>
        <option v-for="company in companyOptions" :key="company.id" :value="company.name">
          {{ company.name }}
        </option>
      </select>
    </div>
  </div>
</div>

      <!-- Flock (readonly to show which one is being transferred) -->
      <div>
        <label class="block text-sm font-medium mb-1">Flock No</label>
         <select v-model="newFlockName" class="w-full border rounded px-3 py-2">
          <option disabled value="">Select Flock No</option>
          <option v-for="flock in flockOptions" :key="flock.id" :value="flock.name">
            {{ flock.name }}
          </option>
        </select>
      </div>

  <div class="grid grid-cols-3 gap-4">
    <!-- Female Company -->
    <div>
      <div>
        <label class="block text-sm font-medium mb-1">Female Qty</label>
        <input
          type="number"
          class="w-full border rounded px-3 py-2 bg-white-100"
        />
      </div>
    </div>

    <!-- To Male -->
    <div>
      <div>
        <label class="block text-sm font-medium mb-1">Male Qty</label>
        <input
          type="number"
          class="w-full border rounded px-3 py-2 bg-white-100"
        />
      </div>
    </div>

      <div>
        <label class="block text-sm font-medium mb-1">Total Qty</label>
        <input
          type="number"
          class="w-full border rounded px-3 py-2 bg-gray-100"
        />
      </div>

  </div>


    </div>

    <!-- Footer -->
    <div class="p-4 flex justify-end border-t">
      <button
        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 mr-2"
        @click="showTransferModal = false"
      >
        Cancel
      </button>
      <button
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        @click="saveTransfer"
      >
        Receive
      </button>
    </div>
  </div>
</div>



    </div>
  </AppLayout>
</template>
