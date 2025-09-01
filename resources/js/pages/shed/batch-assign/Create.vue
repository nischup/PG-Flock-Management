<script setup lang="ts">
import { Link, Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { ArrowLeft, Trash2 } from 'lucide-vue-next'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Assign Batch', href: '/batch-assign' },
  { title: 'Create', href: '' },
]

const form = useForm({
  flock_no: '',
  shed_no: '',
  batches: [
    {
      label: '',
      batchNo: '',
      femaleQty: 0,
      maleQty: 0,
      femaleMortality: 0,
      maleMortality: 0,
      access: '',
      percentage: 0,
      shortage: 0,
    }
  ]
})

const flockOptions = ref([
  { id: 1, name: 'Flock A' },
  { id: 2, name: 'Flock B' }
])

const shedOptions = ref(['Shed 1', 'Shed 2', 'Shed 3'])
const batchOptions = ref(['Batch A', 'Batch B', 'Batch C'])
const labelOptions = ref(['Label-1', 'Label-2', 'Label-3']) // Dropdown options

const addBatch = () => {
  form.batches.push({
    label: '',
    batchNo: '',
    femaleQty: 0,
    maleQty: 0,
    femaleMortality: 0,
    maleMortality: 0,
    access: '',
    percentage: 0,
    shortage: 0,
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
  return form.batches.length <= 0 || form.batches.some(b => !b.label)
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

      <!-- White Card for flock + shed -->
      <div class="border rounded-lg p-6 shadow-sm bg-white space-y-6">
        <div>
          <label class="block text-sm font-medium mb-1">Flock No</label>
          <select v-model="form.flock_no" class="w-full border rounded px-3 py-2">
            <option disabled value="">Select Flock No</option>
            <option v-for="flock in flockOptions" :key="flock.id" :value="flock.name">
              {{ flock.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium mb-1">Shed No</label>
          <select v-model="form.shed_no" class="w-full border rounded px-3 py-2">
            <option disabled value="">Select Shed</option>
            <option v-for="shed in shedOptions" :key="shed" :value="shed">{{ shed }}</option>
          </select>
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
                <label class="block text-sm mb-1">Label</label>
                <select v-model="batch.label" class="w-full border rounded px-3 py-2">
                <option disabled value="">Select Label</option>
                <option v-for="option in labelOptions" :key="option" :value="option">{{ option }}</option>
                </select>
            </div>

            <div>
                <label class="block text-sm mb-1">Batch</label>
                <select v-model="batch.batchNo" class="w-full border rounded px-3 py-2">
                <option disabled value="">Select Batch</option>
                <option v-for="option in batchOptions" :key="option" :value="option">{{ option }}</option>
                </select>
            </div>
            </div>

            <!-- Row 2: Female, Male, Total Qty -->
            <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm mb-1">Female Qty</label>
                <input v-model.number="batch.femaleQty" type="number" class="w-full border rounded px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm mb-1">Male Qty</label>
                <input v-model.number="batch.maleQty" type="number" class="w-full border rounded px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm mb-1">Total Qty</label>
                <input
                :value="(batch.femaleQty || 0) + (batch.maleQty || 0)"
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
                <input v-model.number="batch.femaleMortality" type="number" class="w-full border rounded px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm mb-1">Male Mortality</label>
                <input v-model.number="batch.maleMortality" type="number" class="w-full border rounded px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm mb-1">Total Mortality</label>
                <input
                :value="(batch.femaleMortality || 0) + (batch.maleMortality || 0)"
                type="number"
                readonly
                class="w-full border rounded px-3 py-2 bg-gray-100"
                />
            </div>
            </div>

            <!-- Row 4: Access, Shortage, Percentage -->
            <div class="grid grid-cols-3 gap-4">
            <div>
                <label class="block text-sm mb-1">Access</label>
                <input v-model="batch.access" type="text" class="w-full border rounded px-3 py-2" />
            </div>
            <div>
                <label class="block text-sm mb-1">Shortage</label>
                <input v-model.number="batch.shortage" type="number" class="w-full border rounded px-3 py-2" />
            </div>
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
