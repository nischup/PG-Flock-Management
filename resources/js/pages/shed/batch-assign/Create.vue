<script setup lang="ts">
import { Link, Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { ArrowLeft, Trash2 } from 'lucide-vue-next'
// Removed useDropdownOptions import as we're now using database data


const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Assign Batch', href: '/batch-assign' },
  { title: 'Create', href: '' },
]
// Remove hardcoded dropdown options - now using database data from props

const props = defineProps<{
  shedReceives: Array<any>,
  flocks: Array<any>,
  companies: Array<any>,
  levels: Array<any>,
  batches: Array<any>
}>()

const selectedShedReceiveId = ref<number | string>("")
const shedReceiveInfo = ref<any>(null)


const form = useForm({
  shed_receive_id: '',
  flock_no: 0,
  flock_id: 0,
  company_id: 0,
  shed_id: 0,
  batches: [
    {
      level: '',
      batch_no: '',
      batch_female_qty: 0,
      batch_male_qty: 0,
      batch_total_qty: 0,
      batch_female_mortality: 0,
      batch_male_mortality: 0,
      batch_total_mortality: 0,
      batch_excess_female: 0,
      batch_excess_male: 0,
      batch_total_excess: 0,
      batch_sortage_female: 0,
      batch_sortage_male: 0,
      batch_total_sortage: 0,
      percentage: 0,
    }
  ]
})

function loadShedReceiveInfo() {
  shedReceiveInfo.value = props.shedReceives.find(
    (s) => s.id === Number(selectedShedReceiveId.value)
  ) || null

  form.shed_receive_id = selectedShedReceiveId.value // 👈 set in form
}


const flockOptions = ref([
  { id: 1, name: 'Flock A' },
  { id: 2, name: 'Flock B' }
])



const addBatch = () => {
  form.batches.push({
    level: '',
    batch_no: '',
    batch_female_qty: 0,
    batch_male_qty: 0,
    batch_total_qty: 0,
    batch_female_mortality: 0,
    batch_male_mortality: 0,
    batch_total_mortality: 0,
    batch_excess_female: 0,
    batch_excess_male: 0,
    batch_total_excess: 0,
    batch_sortage_female: 0,
    batch_sortage_male: 0,
    batch_total_sortage: 0,
    percentage: 0,
  })
}

const removeBatch = (index: number) => {
  form.batches.splice(index, 1)
}

const submit = () => {
  form.post('/batch-assign', {
    onSuccess: () => form.reset()
  })
}

// Computed: disable submit if any batch label is empty or only one batch
const isSubmitDisabled = () => {
  return form.batches.length <= 0 || form.batches.some(b => !b.level)
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Create Batch Assign" />

    <form @submit.prevent="submit" class="p-6 space-y-6">
      <!-- Header -->
      <div class="pb-3 mb-6 flex items-center justify-between">
        <h2 class="text-xl font-semibold">Assign Batch Details</h2>
        <Link 
          href="/batch-assign" 
          class="px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md flex items-center gap-1"
        >
          <ArrowLeft class="w-4 h-4" /> List
        </Link>
      </div>

      <!-- Shed Receive Dropdown -->
    <div class="bg-gray-50 p-5 rounded-lg">
      <label class="block font-medium ">Shed Receive</label>
      <select v-model="selectedShedReceiveId" @change="loadShedReceiveInfo"
              class="w-full mt-1 border rounded px-3 py-2">
        <option value="">Select Shed Receive</option>
        <option v-for="shed in props.shedReceives" :key="shed.id" :value="shed.id">
          {{ shed.transaction_no }}-{{ shed.shed }}
        </option>
      </select>
 

    <!-- Info Panel -->
    <div v-if="shedReceiveInfo" class="border rounded-lg p-4 mt-4 bg-gray-50 text-sm grid grid-cols-3 gap-4">
      <div><strong>Flock:</strong> {{ shedReceiveInfo.flock }}</div>
      <div><strong>Shed:</strong> {{ shedReceiveInfo.shed }}</div>
      <div><strong>Company:</strong> {{ shedReceiveInfo.company }}</div>
      <div><strong>Receive Type:</strong> {{ shedReceiveInfo.receive_type }}</div>
      <div><strong>Female Qty:</strong> {{ shedReceiveInfo.shed_female_qty }}</div>
      <div><strong>Male Qty:</strong> {{ shedReceiveInfo.shed_male_qty }}</div>
      <div><strong>Total Qty:</strong> {{ shedReceiveInfo.shed_total_qty }}</div>
    </div>
</div>
        <!-- Dynamic Batch Boxes -->
        <div class="space-y-6">
        <div
            v-for="(batch, index) in form.batches"
            :key="index"
            class="relative border rounded-lg p-6 shadow bg-white space-y-4"
        >
            <!-- Left Badge -->
            <div
            v-if="form.batches.length > 1"
            class="absolute -top-3 -left-3 bg-gray-400 text-white w-8 h-8 flex items-center justify-center rounded-full font-bold"
            >
            {{ index + 1 }}
            </div>

            <!-- Right Delete Icon -->
            <button
            v-if="form.batches.length > 1"
            @click="removeBatch(index)"
            class="absolute -top-3 -right-3 w-8 h-8 bg-red-500 text-white flex items-center justify-center rounded-full hover:bg-red-600"
            title="Remove Batch"
            >
            <Trash2 class="w-4 h-4" />
            </button>

            <!-- Row 1: Label & Batch -->
            <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm mb-1">Level</label>
                <select v-model="batch.level" class="w-full border rounded px-3 py-2">
                <option disabled value="">Select Level</option>
                <option v-for="level in props.levels" :key="level.id" :value="level.id">
                  {{ level.name }}
                </option>
                </select>
            </div>

            <div>
                <label class="block text-sm mb-1">Batch</label>
                <select v-model="batch.batch_no" class="w-full border rounded px-3 py-2">
                <option disabled value="">Select Batch</option>
                <option v-for="batchItem in props.batches" :key="batchItem.id" :value="batchItem.id">
                  {{ batchItem.name }}
                </option>
                </select>
            </div>
            </div>

            <!-- Row 2: Female, Male, Total Qty -->
<div class="grid grid-cols-3 gap-4">
  <div>
      <label class="block text-sm mb-1">Female Qty</label>
      <input v-model.number="batch.batch_female_qty" type="number" class="w-full border rounded px-3 py-2" />
  </div>
  <div>
      <label class="block text-sm mb-1">Male Qty</label>
      <input v-model.number="batch.batch_male_qty" type="number" class="w-full border rounded px-3 py-2" />
  </div>
  <div>
      <label class="block text-sm mb-1">Total Qty</label>
      <input
        :value="(batch.batch_female_qty || 0) + (batch.batch_male_qty || 0)"
        type="number"
        readonly
        class="w-full border rounded px-3 py-2 bg-gray-100"
      />
  </div>
</div>

<!-- Row 3: Female Mortality, Male Mortality, Total Mortality -->
<div class="grid grid-cols-3 gap-4">
  <div>
      <label class="block text-sm mb-1">Female Mortality</label>
      <input v-model.number="batch.batch_female_mortality" type="number" class="w-full border rounded px-3 py-2" />
  </div>
  <div>
      <label class="block text-sm mb-1">Male Mortality</label>
      <input v-model.number="batch.batch_male_mortality" type="number" class="w-full border rounded px-3 py-2" />
  </div>
  <div>
      <label class="block text-sm mb-1">Total Mortality</label>
      <input
        :value="(batch.batch_female_mortality || 0) + (batch.batch_male_mortality || 0)"
        type="number"
        readonly
        class="w-full border rounded px-3 py-2 bg-gray-100"
      />
  </div>
</div>

<!-- Row 4: Excess -->
<div class="grid grid-cols-3 gap-4 mt-2">
  <div>
    <label class="block text-sm mb-1">Female Excess Qty</label>
    <input v-model.number="batch.batch_excess_female" type="number" class="w-full border rounded px-3 py-2" />
  </div>

  <div>
    <label class="block text-sm mb-1">Male Excess Qty</label>
    <input v-model.number="batch.batch_excess_male" type="number" class="w-full border rounded px-3 py-2" />
  </div>

  <div>
    <label class="block text-sm mb-1">Total Excess Qty</label>
    <input 
      type="number" 
      :value="(batch.batch_excess_female || 0) + (batch.batch_excess_male || 0)" 
      readonly 
      class="w-full border rounded px-3 py-2 bg-gray-100"
    />
  </div>
</div>

<!-- Row 5: Shortage -->
<div class="grid grid-cols-3 gap-4 mt-2">
  <div>
    <label class="block text-sm mb-1">Female Shortage Qty</label>
    <input v-model.number="batch.batch_sortage_female" type="number" class="w-full border rounded px-3 py-2" />
  </div>

  <div>
    <label class="block text-sm mb-1">Male Shortage Qty</label>
    <input v-model.number="batch.batch_sortage_male" type="number" class="w-full border rounded px-3 py-2" />
  </div>

  <div>
    <label class="block text-sm mb-1">Total Shortage Qty</label>
    <input 
      type="number" 
      :value="(batch.batch_sortage_female || 0) + (batch.batch_sortage_male || 0)" 
      readonly 
      class="w-full border rounded px-3 py-2 bg-gray-100"
    />
  </div>
</div>

<!-- Row 6: Percentage -->
<div class="grid grid-cols-1 gap-4 mt-2">
  <div>
    <label class="block text-sm mb-1">Percentage</label>
    <input v-model.number="batch.percentage" type="number" class="w-full border rounded px-3 py-2" />
  </div>
</div>

            <!-- Add More Button -->
            <div class="flex justify-end">
            <button
                v-if="index === form.batches.length - 1"
                type="button"
                @click="addBatch"
                class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600"
            >
                + Add More
            </button>
            </div>
        </div>
        </div>

      <!-- Submit -->
      <div class="flex justify-end">
        <button
          type="submit"
          :disabled="isSubmitDisabled()"
          :class="['px-6 py-2 rounded', isSubmitDisabled() ? 'bg-gray-400 cursor-not-allowed' : 'bg-blue-600 hover:bg-blue-700 text-white']"
        >
          Submit
        </button>
      </div>
    </form>
  </AppLayout>
</template>
