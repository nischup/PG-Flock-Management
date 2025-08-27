<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, computed } from "vue";
import listInfocard from '@/components/ListinfoCard.vue'
import { useAgeCalculator } from '@/composables/useAgeCalculator'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Shed', href: '/shed' },
  { title: 'Assign Batch', href: '/flock/assign' },
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

const piCardData: Record<string, any[]> = {
  PI001: [
    
    { title: 'Opening Chicks', value: 12500},
    { title: 'Total Chicks', value: 12000 },
    { title: 'Male Chicks', value: 2000 },
    { title: 'Female Chicks', value: 10000 },
    { title: 'Mortality', value: 500 },
    { title: 'Age', value: useAgeCalculator("2025-07-21") },
  ],
  PI002: [
    
    { title: 'Opening Chicks', value: 13000},
    { title: 'Total Chicks', value: 12500 },
    { title: 'Male Chicks', value: 2000 },
    { title: 'Female Chicks', value: 10000 },
    { title: 'Mortality', value: 500 },
    { title: 'Age', value: useAgeCalculator("2025-06-12") },
  ],
  PI003: [
    { title: 'Opening Chicks', value: 11000},
    { title: 'Total Chicks', value: 10500 },
    { title: 'Male Chicks', value: 2000 },
    { title: 'Female Chicks', value: 10000 },
    { title: 'Mortality', value: 500 },
    { title: 'Age', value: useAgeCalculator("2025-07-10") },
  ]
}

// Selected PI
const selectedPI = ref('PI001')

// Cards to show based on selected PI
const cardData = computed(() => piCardData[selectedPI.value] || [])

</script>


<template>
  <Head title="Flock List" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">

      <div class="w-full flex items-center justify-between p-5 ">
        <!-- Select Box -->
        <select
          v-model="selectedPI"
          class="w-64 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none sm:text-sm"
        >
          <option value="" disabled selected>Select Batch</option>
          <option value="PI001">Pcl-Shed-1-A</option>
          <option value="PI002">Pcl-Shed-1-B</option>
          <option value="PI003">Pcl-Shed-1-C</option>
        </select>
        <!-- Button (fully right) -->
        <button
          @click="showModal = true"
          class="px-4 py-2 bg-chicken text-white rounded-lg hover:bg-yellow-700"
        >
          + Assign Batch
        </button>
      </div>
      <listInfocard :cards="cardData" />

      <!-- List Table -->
      <div class="overflow-x-auto rounded-xl shadow bg-white dark:bg-gray-800 mt-4">
        <table class="w-full text-left border-collapse">
          <thead class="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th class="px-4 py-2 border-b">#SL</th>
              <th class="px-4 py-2 border-b">Flock No</th>
              <th class="px-4 py-2 border-b">Shed</th>
              <th class="px-4 py-2 border-b">Batch</th>
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
              <td class="px-4 py-2 border-b">{{ flock.name }}</td>
              <td class="px-4 py-2 border-b">{{ flock.shed }}</td>
              <td class="px-4 py-2 border-b">{{ flock.batch }}</td>
              <td class="px-4 py-2 border-b">
                <button class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-chicken mr-2">Edit</button>
                <button
                  class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 mr-2"
                  @click="openTransferModal(flock)"
                >
                  Transfer
                </button>

              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Draggable Add Flock Modal -->
     <div
  v-if="showModal"
  class="fixed inset-0 bg-black/20 flex items-center justify-center z-50"
  @click="closeModal"
  @mousemove="onDrag"
  @mouseup="stopDrag"
>
  <div
    id="modal-content"
    class="bg-white rounded-lg shadow-lg w-[700px] max-w-full"
    :style="{ transform: `translate(${position.x}px, ${position.y}px)` }"
    @click.stop
  >
    <!-- Header -->
    <div
      class="p-3 bg-gray-200 cursor-move rounded-t-lg"
      @mousedown="startDrag"
    >
      <h2 class="font-bold">Assign Batch</h2>
    </div>

    <!-- Body -->
    <div class="p-6 space-y-4">
      <!-- Flock No -->
      <div>
        <label class="block text-sm font-medium mb-1">Flock No</label>
        <select v-model="newFlockName" class="w-full border rounded px-3 py-2">
          <option disabled value="">Select Flock No</option>
          <option v-for="flock in flockOptions" :key="flock.id" :value="flock.name">
            {{ flock.name }}
          </option>
        </select>
      </div>

      <!-- Shed No -->
      <div>
        <label class="block text-sm font-medium mb-1">Shed No</label>
        <select v-model="selectedShed" class="w-full border rounded px-3 py-2">
          <option disabled value="">Select Shed</option>
          <option v-for="shed in shedOptions" :key="shed" :value="shed">{{ shed }}</option>
        </select>
      </div>

      <!-- Batch + Quantities -->
<div>
  <label class="block text-sm font-medium mb-2">Batch Details</label>

  <!-- Border Box -->
  <div class="border rounded-lg p-3 space-y-2">
    <!-- Header Row -->
    <div class="grid grid-cols-5 gap-3 font-semibold text-sm text-gray-600 border-b pb-2">
      <span>Batch</span>
      <span>Female Qty</span>
      <span>Male Qty</span>
      <span>Total</span>
      <span></span>
    </div>

    <!-- Rows -->
    <div
      v-for="(batch, index) in batches"
      :key="index"
      class="grid grid-cols-5 gap-3 items-center"
    >
      <!-- Batch Dropdown -->
      <select v-model="batch.batchNo" class="border rounded px-3 py-2">
        <option disabled value="">Select Batch</option>
        <option v-for="batchOption in batchOptions" :key="batchOption" :value="batchOption">
          {{ batchOption }}
        </option>
      </select>

      <!-- Female Qty -->
      <input
        v-model.number="batch.femaleQty"
        type="number"
        placeholder="Female Qty"
        class="border rounded px-3 py-2"
      />

      <!-- Male Qty -->
      <input
        v-model.number="batch.maleQty"
        type="number"
        placeholder="Male Qty"
        class="border rounded px-3 py-2"
      />

      <!-- Total Qty (readonly) -->
      <input
        :value="(batch.femaleQty || 0) + (batch.maleQty || 0)"
        type="number"
        readonly
        class="border rounded px-3 py-2 bg-gray-100"
      />

      <!-- Add Batch Button (last row only) -->
      <button
        v-if="index === batches.length - 1"
        @click="addBatch"
        class="px-3 py-2 bg-green-500 text-white rounded hover:bg-green-600"
      >
        +
      </button>
    </div>
  </div>
</div>

    </div>

    <!-- Footer -->
    <div class="p-4 flex justify-end border-t">
      <button
        class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 mr-2"
        @click="showModal = false"
      >
        Cancel
      </button>
      <button
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        @click="saveFlock"
      >
        Save
      </button>
    </div>
  </div>
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
      <h2 class="font-bold">Transfer Flock</h2>
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
        Transfer
      </button>
    </div>
  </div>
</div>



    </div>
  </AppLayout>
</template>
