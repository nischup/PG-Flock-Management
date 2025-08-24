<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref } from "vue";

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Shed', href: '/shed' },
  { title: 'Flock', href: '/flock' },
  { title: 'Assign', href: '/flock/assign' },
];

// Dummy stats
const totalFlock = ref(50);
const assignShed = ref(250);
const assignBatch = ref(350);

// Dummy flock list
const flocks = ref([
  { id: 1, name: "Flock-001", shed: "Shed 1", batch: "Batch A, Batch B" },
  { id: 2, name: "Flock-002", shed: "Shed 2", batch: "Batch B" },
  { id: 3, name: "Flock-003", shed: "Shed 3", batch: "Batch C" },
]);

// Modal state
const showModal = ref(false);
const newFlockName = ref("");

// Dragging state for Add Flock modal
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

const stopDrag = () => {
  dragging.value = false;
};

const addShed = () => {
  sheds.value.push("");
};
const addBatch = () => {
  batches.value.push("");
};

// Close modal on background click
const closeModal = (event: MouseEvent) => {
  const modalContent = document.getElementById("modal-content");
  if (modalContent && !modalContent.contains(event.target as Node)) {
    showModal.value = false;
  }
};

// shed
const sheds = ref<string[]>([""]);
const shedOptions = ["Shed 1", "Shed 2", "Shed 3"];

// batch
const batches = ref<string[]>([""]);
const batchOptions = ["Batch A", "Batch B", "Batch C"];

// Save flock
const saveFlock = () => {
  if (!newFlockName.value.trim()) return;

  flocks.value.push({
    id: flocks.value.length + 1,
    name: newFlockName.value,
    shed: sheds.value.filter(Boolean).join(", "),
    batch: batches.value.filter(Boolean).join(", "),
  });

  // Reset form
  newFlockName.value = "";
  sheds.value = [""];
  batches.value = [""];
  showModal.value = false;
  totalFlock.value++;
};

// -------------------
// Pan Assign Modal
// -------------------
const showPanModal = ref(false);
const selectedFlock = ref<{ id: number; name: string; shed: string; batch: string } | null>(null);

// Draggable state for Pan Assign modal
const panPosition = ref({ x: 0, y: 0 });
const panOffset = ref({ x: 0, y: 0 });
const panDragging = ref(false);

const startPanDrag = (event: MouseEvent) => {
  panDragging.value = true;
  panOffset.value = {
    x: event.clientX - panPosition.value.x,
    y: event.clientY - panPosition.value.y,
  };
};

const onPanDrag = (event: MouseEvent) => {
  if (!panDragging.value) return;
  panPosition.value = {
    x: event.clientX - panOffset.value.x,
    y: event.clientY - panOffset.value.y,
  };
};

const stopPanDrag = () => {
  panDragging.value = false;
};

// Open Pan Assign modal
const openPanModal = (flock: typeof flocks.value[0]) => {
  selectedFlock.value = { ...flock };
  showPanModal.value = true;
};

// Save Pan Assign changes
const savePanAssign = () => {
  if (!selectedFlock.value) return;

  const index = flocks.value.findIndex(f => f.id === selectedFlock.value!.id);
  if (index !== -1) {
    flocks.value[index] = { ...selectedFlock.value };
  }
  showPanModal.value = false;
};
</script>

<template>
  <Head title="Flock List" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">

      <!-- Add Flock Button -->
      <div class="flex justify-end">
        <button
          @click="showModal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          + Add New Flock
        </button>
      </div>

      <!-- Cards -->
      <div class="grid gap-4 md:grid-cols-3">
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
      </div>

      <!-- List Table -->
      <div class="overflow-x-auto rounded-xl shadow bg-white dark:bg-gray-800 mt-4">
        <table class="w-full text-left border-collapse">
          <thead class="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th class="px-4 py-2 border-b">#SL</th>
              <th class="px-4 py-2 border-b">Flock Name</th>
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
                <button class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600 mr-2">Edit</button>
                <button
                  class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600"
                  @click="openPanModal(flock)"
                >
                  Pan Assign
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
          class="bg-white rounded-lg shadow-lg w-[650px] max-w-full"
          :style="{ transform: `translate(${position.x}px, ${position.y}px)` }"
          @click.stop
        >
          <div
            class="p-3 bg-gray-200 cursor-move rounded-t-lg"
            @mousedown="startDrag"
          >
            <h2 class="font-bold">Add New Flock</h2>
          </div>

          <div class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium mb-1">Flock Name</label>
              <input
                v-model="newFlockName"
                type="text"
                placeholder="Enter flock name"
                class="w-full border rounded px-3 py-2"
              />
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Shed No</label>
              <div v-for="(shed, index) in sheds" :key="index" class="flex items-center gap-2 mb-2">
                <select v-model="sheds[index]" class="w-full border rounded px-3 py-2">
                  <option disabled value="">Select shed</option>
                  <option v-for="shedOption in shedOptions" :key="shedOption" :value="shedOption">
                    {{ shedOption }}
                  </option>
                </select>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Batch No</label>
              <div v-for="(batch, index) in batches" :key="index" class="flex items-center gap-2 mb-2">
                <select v-model="batches[index]" class="w-full border rounded px-3 py-2">
                  <option disabled value="">Select batch</option>
                  <option v-for="batchOption in batchOptions" :key="batchOption" :value="batchOption">
                    {{ batchOption }}
                  </option>
                </select>
                <button
                  v-if="index === batches.length - 1"
                  @click="addBatch"
                  class="px-3 py-2 bg-green-500 text-white rounded hover:bg-green-600"
                >+</button>
              </div>
            </div>
          </div>

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

      <!-- Draggable Pan Assign Modal -->
      <div
        v-if="showPanModal"
        class="fixed inset-0 bg-black/20 flex items-center justify-center z-50"
        @click="showPanModal = false"
        @mousemove="onPanDrag"
        @mouseup="stopPanDrag"
      >
        <div
          class="bg-white rounded-lg shadow-lg w-[500px] max-w-full"
          :style="{ transform: `translate(${panPosition.x}px, ${panPosition.y}px)` }"
          @click.stop
        >
          <div class="p-3 bg-gray-200 cursor-move rounded-t-lg" @mousedown="startPanDrag">
            <h2 class="font-bold">Pan Assign - Flock - {{ selectedFlock?.name }} ,  {{ selectedFlock?.shed }}</h2>
          </div>

          <div class="p-6 space-y-4">

            <div>
              <label class="block text-sm font-medium mb-1">Batch</label>
              <select v-model="selectedFlock.batch" class="w-full border rounded px-3 py-2">
                <option disabled value="">Select batch</option>
                <option v-for="batchOption in batchOptions" :key="batchOption" :value="batchOption">
                  {{ batchOption }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Pan</label>
              <select v-model="selectedFlock.batch" class="w-full border rounded px-3 py-2">
                <option disabled value="">Select Pan</option>
                <option> Pan A</option>
                <option> Pan B</option>
                <option> Pan C</option>
                <option> Pan D</option>
                <option> Pan E</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium mb-1">Qty</label>
              <input
                v-model="newFlockName"
                type="text"
                placeholder="Enter Pan Qty"
                class="w-full border rounded px-3 py-2"
              />
            </div>

          </div>

          <div class="p-4 flex justify-end border-t">
            <button
              class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 mr-2"
              @click="showPanModal = false"
            >
              Cancel
            </button>
            <button
              class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
              @click="savePanAssign"
            >
              Save
            </button>
          </div>
        </div>
      </div>

    </div>
  </AppLayout>
</template>
