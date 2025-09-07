<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from "vue";
import listInfocard from '@/components/ListinfoCard.vue'
import { useAgeCalculator } from '@/composables/useAgeCalculator'
import { usePermissions } from '@/composables/usePermissions'
import Pagination from '@/components/Pagination.vue'
import { Trash2, Pencil } from 'lucide-vue-next'
import dayjs from 'dayjs'
import { useDropdownOptions } from '@/composables/dropdownOptions'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Shed', href: '/shed' },
  { title: 'Assign Batch', href: '/shed/batch-assign' },
];

const { batchOptions, levelOptions  } = useDropdownOptions()

function getBatchLabel(value: number | string) {
  const found = batchOptions.find(b => b.value === Number(value)) // ensure both work
  return found ? found.label : '-'
}


function getLevelLabel(value: number) {
  const found = levelOptions.find(l => l.value === value)
  return found ? found.label : `Level ${value}`
}

const props = defineProps<{
  batchAssigns: any // paginated data from controller
}>()



const deleteBatch = (id: number) => {
  if (!confirm('Are you sure you want to delete this batch?')) return
  // call your delete route via Inertia or Axios
  // router.delete(`/batch-assign/${id}`)
}
// Dummy stats
const totalFlock = ref(50);
const assignShed = ref(250);
const assignBatch = ref(350);
const { can } = usePermissions()

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
    
    { title: 'Total Chicks', value: 12500},
    { title: 'Current Chicks', value: 12000 },
    { title: 'Male Chicks', value: 2000 },
    { title: 'Female Chicks', value: 10000 },
    { title: 'Mortality', value: 500 },
    { title: 'Age', value: useAgeCalculator("2025-07-21") },
  ],
  PI002: [
    
    { title: 'Total Chicks', value: 13000},
    { title: 'Current Chicks', value: 12500 },
    { title: 'Male Chicks', value: 2000 },
    { title: 'Female Chicks', value: 10000 },
    { title: 'Mortality', value: 500 },
    { title: 'Age', value: useAgeCalculator("2025-06-12") },
  ],
  PI003: [
    { title: 'Total Chicks', value: 11000},
    { title: 'Current Chicks', value: 10500 },
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


const openDropdownId = ref<number | null>(null)

function toggleDropdown(id: number) {
  openDropdownId.value = openDropdownId.value === id ? null : id
}

// Close dropdown when clicking outside
function handleClickOutside(e: MouseEvent) {
  if (!(e.target as HTMLElement).closest('.dropdown-wrapper')) {
    openDropdownId.value = null
  }
}
document.addEventListener('click', handleClickOutside)




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
        <Link
          href="/batch-assign/create"
          class="inline-flex items-center gap-2 px-4 py-2 bg-chicken hover:bg-chicken text-white text-sm font-semibold rounded shadow transition"
        >
          <span class="text-lg">+</span>
          <span>Assign Batch</span>
        </Link>


      </div>
      <listInfocard :cards="cardData" />

      <!-- Table -->
      <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
            <tr>
              
              <th class="px-6 py-3 text-left font-bold">Flock</th>
              <th class="px-6 py-3 text-left font-bold">Shed</th>
              <th class="px-6 py-3 text-left font-bold">Female Qty</th>
              <th class="px-6 py-3 text-left font-bold">Male Qty</th>
              <th class="px-6 py-3 text-left font-bold">Total Qty</th>
              <th class="px-6 py-3 text-left font-bold">Level</th>
              <th class="px-6 py-3 text-left font-bold">Batch No</th>
              <th class="px-6 py-3 text-left font-bold">Company</th>
              <th class="px-6 py-3 text-left font-bold">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="batch in props.batchAssigns ?? []" :key="batch.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 odd:bg-white even:bg-gray-100">
              
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ batch.flock_name ?? '-' }}</td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ batch.shed_name ?? '-' }}</td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ batch.batch_female_qty ?? 0 }}</td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ batch.batch_male_qty ?? 0 }}</td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100 font-bold">{{ batch.batch_total_qty ?? 0 }}</td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ getLevelLabel(batch.level ?? '-') }}</td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ getBatchLabel(batch.batch_no ?? '-') }}</td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ batch.company_name ?? '-' }}</td>
              <td class="px-6 py-4 relative">
                <div class="dropdown-wrapper relative">
                  <Button
                    size="sm"
                    class="bg-gray-500 hover:bg-gray-600 text-white p-2 rounded"
                    @click.stop="toggleDropdown(batch.id)"
                  >
                    Actions ▼
                  </Button>

                  <div
                    v-if="openDropdownId === batch.id"
                    class="absolute right-0 mt-1 w-40 bg-white border rounded shadow-md z-10 flex flex-col"
                  >
                    <!-- Edit -->
                    <Link
                      :href="`/batch-assign/${batch.id}/edit`"
                      class="px-4 py-2 text-left hover:bg-blue-50 text-blue-600 flex items-center gap-2"
                    >
                      <Pencil class="w-4 h-4" />
                      <span>Edit</span>
                    </Link>

                    <!-- Transfer (optional) -->
                    <Link
                      :href="route('bird-transfers.create', batch.id)"
                      class="px-4 py-2 text-left hover:bg-yellow-50 text-yellow-600 flex items-center gap-2"
                    >
                      <span>Transfer</span>
                    </Link>

                    <!-- Delete -->
                    <button
                      @click="deleteBatch(batch.id)"
                      class="px-4 py-2 text-left hover:bg-red-50 text-red-600 flex items-center gap-2 w-full"
                    >
                      <Trash2 class="w-4 h-4" />
                      <span>Delete</span>
                    </button>
                  </div>
                </div>
              </td>
            </tr>

            <tr v-if="(props.batchAssigns?.data ?? []).length === 0">
              <td colspan="10" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">
                No batch assigned yet.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <Pagination :meta="props.batchAssigns?.meta ?? {}" class="mt-6" />

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
