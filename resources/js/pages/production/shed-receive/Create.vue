<script setup lang="ts">
import { Link, Head, useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { ArrowLeft } from 'lucide-vue-next'

// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Production Shed Receive', href: '/production-shed-receive' },
  { title: 'Create', href: '' },
]

// Props
const props = defineProps<{
  firmReceives: Array<any> // Batches generated from firm receive
  flocks: Array<any>
  companies: Array<any>
  sheds: Array<any>
}>()

// Form state
const selectedJobId = ref<number | string>('')
const selectedFlockId = ref<number | string>('')
const selectedShedid = ref<number | string>('')

const showInfo = ref(false)

const form = useForm({
  job_id: 0,
  flock_id: 0,
  shed_id:1,
  receiving_company_id: 0,
  shed_female_qty: 0,
  shed_male_qty: 0,
  shed_total_qty: 0,
  shed_sortage_male_box: 0,
  shed_sortage_female_box: 0,
  shed_sortage_box_qty: 0,
  shed_excess_male_box: 0,
  shed_excess_female_box: 0,
  shed_excess_box_qty: 0,
  remarks: '',
  status: 1,
})

// Watch for total boxes and auto-calc shortages/excess
watch(
  () => [form.shed_male_qty, form.shed_female_qty],
  () => {
    form.shed_total_qty =
      Number(form.shed_male_qty || 0) + Number(form.shed_female_qty || 0)

    // Total shortage
    form.shed_sortage_box_qty =
      Number(form.shed_sortage_male_box || 0) + Number(form.shed_sortage_female_box || 0)
    form.shed_excess_box_qty =
      Number(form.shed_excess_male_box || 0) + Number(form.shed_excess_female_box || 0)
  },
  { deep: true, immediate: true }
)

// Watch for flock selection
watch(selectedFlockId, (val) => {
  form.flock_id = val
})

// Watch for flock selection
watch(selectedShedid, (val) => {
  form.shed_id = val
})

watch(selectedJobId, (val) => {
  form.job_id = val
})

// Toggle Batch Info
function toggleInfo() {
  const selected = props.firmReceives.find((job) => job.id === Number(selectedJobId.value))
  if (!selected) {
    showInfo.value = false
    return
  }
  form.job_id = selected.id
  form.receiving_company_id = selected.receiving_company_id
  selectedFlockId.value = selected.flock_id
  showInfo.value = true
}

// Submit Shed Receive
function submit() {
  form.post(route('shed-receive.store'), {
    onSuccess: () => form.reset(),
    onError: () => {},
  })
}
</script>

<template>
<AppLayout :breadcrumbs="breadcrumbs">
  <Head title="Create Shed Receive" />

  <form @submit.prevent="submit" class="p-6 space-y-6">

    <!-- Batch Info -->
    <div class="border rounded-lg p-4 shadow-sm bg-white">
      <div class="pb-3 mb-6 flex items-center justify-between">
        <h2 class="text-xl font-semibold">Production Shed Receive Info</h2>
        <Link 
          href="/shed-receive" 
          class="px-3 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md flex items-center gap-1"
        >
          <ArrowLeft class="w-4 h-4" /> List
        </Link>
      </div>

      <div class="grid grid-cols-1 gap-4">
        <!-- Batch Dropdown -->
        <div>
          <Label>Firm Receive Code</Label>
          <select v-model="selectedJobId" @change="toggleInfo" class="w-full mt-1 border rounded px-3 py-2">
            <option value="">Select Firm Receive Code</option>
            <option v-for="job in props.firmReceives" :key="job.id" :value="job.id">
              {{ job.job_no }}
            </option>
          </select>
        </div>

        <!-- Flock -->
        <div>
          <Label>Flock</Label>
          <select v-model="selectedFlockId" class="w-full mt-1 border rounded px-3 py-2"  disabled>
            <option value="">Select Flock</option>
            <option v-for="flock in props.flocks" :key="flock.id" :value="flock.id">{{ flock.name }}</option>
          </select>
        </div>

        <!-- Flock -->
        <div>
          <Label>Shed</Label>
          <select v-model="selectedShedid" class="w-full mt-1 border rounded px-3 py-2">
            <option value="">Select Shed</option>
            <option v-for="shed in props.sheds" :key="shed.id" :value="shed.id">{{ shed.name }}</option>
          </select>
        </div>
      </div>

      <!-- Show batch info -->
      <transition enter-active-class="transition-all duration-500 ease-in-out" leave-active-class="transition-all duration-500 ease-in-out"
        enter-from-class="max-h-0 opacity-0" enter-to-class="max-h-screen opacity-100"
        leave-from-class="max-h-screen opacity-100" leave-to-class="max-h-0 opacity-0">
        <div v-if="showInfo" class="grid grid-cols-3 gap-4 text-sm mt-5 overflow-hidden">
          <div><span class="font-medium">Job No:</span> <span class="ml-1">{{ props.firmReceives.find(b => b.id === selectedJobId)?.job_no }}</span></div>
          <div><span class="font-medium">Receiving Company:</span> <span class="ml-1">{{ props.companies.find(c => c.id === form.receiving_company_id)?.name }}</span></div>
          <div><span class="font-medium">Female Chicks Qty:</span> <span class="ml-1">{{ props.firmReceives.find(b => b.id === selectedJobId)?.firm_female_qty }}</span></div>
          <div><span class="font-medium">Male Chicks Qty:</span> <span class="ml-1">{{ props.firmReceives.find(b => b.id === selectedJobId)?.firm_male_qty }}</span></div>
          <div><span class="font-medium">Total Chicks Qty:</span> <span class="ml-1">{{ props.firmReceives.find(b => b.id === selectedJobId)?.firm_total_qty }}</span></div>
        </div>
      </transition>
    </div>

    <!-- Shed Receive Boxes -->
    <div class="border rounded-lg p-4 shadow-sm bg-white mt-4">
      <h2 class="font-semibold text-lg mb-4">Chicks Receive by Shed</h2>

      <div class="grid grid-cols-3 gap-4">
        <div><Label>Female Chicks Qty</Label><Input v-model.number="form.shed_female_qty" type="number" class="mt-1" /></div>
        <div><Label>Male Chicks Qty</Label><Input v-model.number="form.shed_male_qty" type="number" class="mt-1" /></div>
        <div><Label>Total Chicks Qty</Label><Input type="number" :value="form.shed_total_qty" readonly class="mt-1 bg-gray-100 text-gray-700 cursor-not-allowed" /></div>
      </div>

      <div class="grid grid-cols-3 gap-4 mt-5">
        <!-- Shortage Boxes -->
        <div>
          <Label>Shortage Male Chicks</Label>
          <Input type="number" v-model.number="form.shed_sortage_male_box" class="mt-1" />
        </div>
        <div>
          <Label>Shortage Female Chicks</Label>
          <Input type="number" v-model.number="form.shed_sortage_female_box" class="mt-1" />
        </div>
        <div>
          <Label>Shortage Total Chicks</Label>
          <Input type="number" :value="form.shed_sortage_box_qty" readonly class="mt-1 bg-gray-100 text-gray-700 cursor-not-allowed" />
        </div>

        <!-- Excess Boxes -->
        <div>
          <Label>Excess Male Chicks</Label>
          <Input type="number" v-model.number="form.shed_excess_male_box" class="mt-1" />
        </div>
        <div>
          <Label>Excess Female Chicks</Label>
          <Input type="number" v-model.number="form.shed_excess_female_box" class="mt-1" />
        </div>
        <div>
          <Label>Excess Total Chicks</Label>
          <Input type="number" :value="form.shed_excess_box_qty" readonly class="mt-1 bg-gray-100 text-gray-700 cursor-not-allowed" />
        </div>
      </div>
    </div>

    <!-- Notes -->
    <div class="border rounded-lg p-4 shadow-sm bg-white mt-4">
      <h2 class="font-semibold text-lg mb-4">Notes</h2>
      <textarea v-model="form.remarks" class="w-full border rounded px-3 py-2" rows="3" placeholder="Write notes here..."></textarea>
    </div>

    <!-- Submit -->
    <div class="flex justify-end">
      <Button type="submit" class="px-6 py-2">Save & Submit</Button>
    </div>
  </form>
</AppLayout>
</template>
