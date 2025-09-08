<script setup lang="ts">
import { ref, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { useNotifier } from '@/composables/useNotifier'
import { useDropdownOptions } from '@/composables/dropdownOptions'

const { showInfo } = useNotifier()

// Props from backend
const props = defineProps<{
  batchAssign: any,
  flocks: any[],
  companies: any[],
  sheds: any[],
}>()

// Get batch dropdown from composable
const { batchOptions } = useDropdownOptions()

// Form pre-filled with backend data
const form = useForm({
  batch_assign_id: props.batchAssign.id,
  flock_id: props.batchAssign.flock_id,
  from_company_id: props.batchAssign.company_id,
  from_shed_id: props.batchAssign.shed_id,
  to_company_id: null,
  to_shed_id: null,

  total_bird: props.batchAssign.batch_total_qty,
  male_qty: props.batchAssign.batch_male_qty - props.batchAssign.batch_male_mortality,
  female_qty: props.batchAssign.batch_female_qty - props.batchAssign.batch_female_mortality,

  transfer_male_qty: 0,
  transfer_female_qty: 0,
  transfer_total_qty: 0,
  transfer_date: '', 

  medical_male_qty: 0,
  medical_female_qty: 0,
  medical_total_qty: 0,

  deviation_male_qty: 0,
  deviation_female_qty: 0,
  deviation_total_qty: 0,

  transfer_note: '',
})

// Computed values
const current_male_chicks = computed(() => form.male_qty)
const current_female_chicks = computed(() => form.female_qty)
const current_total_chicks = computed(() => current_male_chicks.value + current_female_chicks.value)

const total_transfer_chicks = computed(() => form.transfer_male_qty + form.transfer_female_qty)
const total_medical_bird = computed(() => form.medical_male_qty + form.medical_female_qty)

const deviation_male = computed(() => current_male_chicks.value - form.transfer_male_qty - form.medical_male_qty)
const deviation_female = computed(() => current_female_chicks.value - form.transfer_female_qty - form.medical_female_qty)
const deviation_total = computed(() => deviation_male.value + deviation_female.value)

// Show shed only if same company
const showShed = computed(() => props.batchAssign.company_id === form.to_company_id)

// Submit
function submit() {
  if (!form.flock_id) return showInfo("Please select a flock")
  if (!form.to_company_id) return showInfo("Please select a transfer company")

  form.post(route('bird-transfer.store'), {
    data: {
      ...form.data(),
      transfer_total_qty: total_transfer_chicks.value,
      medical_total_qty: total_medical_bird.value,
      deviation_male_qty: deviation_male.value,
      deviation_female_qty: deviation_female.value,
      deviation_total_qty: deviation_total.value,
    },
    onSuccess: () => showInfo("Bird transfer saved successfully")
  })
}
</script>

<template>
  <AppLayout>
    <form @submit.prevent="submit" class="space-y-6 p-6 rounded m-5 bg-white">

      <!-- Selection -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Flock -->
        <div>
          <Label>Select Flock</Label>
          <select v-model.number="form.flock_id" class="w-full mt-1 border rounded px-3 py-2">
            <option value="">Select Flock</option>
            <option v-for="flock in props.flocks" :key="flock.id" :value="flock.id">
              {{ flock.name }}
            </option>
          </select>
        </div>

        <!-- Batch -->
        <div>
          <Label>Select Batch</Label>
          <select v-model.number="form.batch_assign_id" class="w-full mt-1 border rounded px-3 py-2">
            <option value="">Select Batch</option>
            <option v-for="batch in batchOptions" :key="batch.value" :value="Number(batch.value)">
              {{ batch.label }}
            </option>
          </select>
        </div>

        <!-- Company -->
        <div>
          <Label>Transfer To Company</Label>
          <select v-model.number="form.to_company_id" class="w-full mt-1 border rounded px-3 py-2">
            <option value="">Select Company</option>
            <option v-for="company in props.companies" :key="company.id" :value="company.id">
              {{ company.name }}
            </option>
          </select>
        </div>
        <div>
          <Label>Transfer Date</Label>
          <Input v-model="form.transfer_date" type="date" class="w-full mt-1" />
        </div>
      </div>

      <!-- Shed -->
      <div v-if="showShed" class="mt-4">
        <Label>Select Shed</Label>
        <select v-model.number="form.to_shed_id" class="w-full mt-1 border rounded px-3 py-2">
          <option value="">Select Shed</option>
          <option v-for="shed in props.sheds" :key="shed.shed_id" :value="shed.shed_id">
            {{ shed.name }}
          </option>
        </select>
        
      </div>

      <!-- Info Cards -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mt-4">
        <div class="bg-yellow-100 p-4 rounded shadow text-center">
          <p>Total Birds</p>
          <p class="text-2xl font-bold">{{ form.total_bird }}</p>
        </div>
        <div class="bg-red-100 p-4 rounded shadow text-center">
          <p>Mortality</p>
          <p class="text-2xl font-bold">{{ form.total_bird - current_total_chicks }}</p>
        </div>
        <div class="bg-green-100 p-4 rounded shadow text-center">
          <p>Female Birds</p>
          <p class="text-2xl font-bold">{{ current_female_chicks }}</p>
        </div>
        <div class="bg-blue-100 p-4 rounded shadow text-center">
          <p>Male Birds</p>
          <p class="text-2xl font-bold">{{ current_male_chicks }}</p>
        </div>
        <div class="bg-purple-100 p-4 rounded shadow text-center">
          <p>Current Chicks</p>
          <p class="text-2xl font-bold">{{ current_total_chicks }}</p>
        </div>
      </div>

      <!-- Transfer Inputs -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
        <div>
          <Label>Transfer Female Qty</Label>
          <Input v-model.number="form.transfer_female_qty" type="number" min="0" />
        </div>
        <div>
          <Label>Transfer Male Qty</Label>
          <Input v-model.number="form.transfer_male_qty" type="number" min="0" />
        </div>
        <div>
          <Label>Total Transfer Chicks</Label>
          <Input :value="total_transfer_chicks" type="number" readonly class="bg-gray-100" />
        </div>
      </div>

      <!-- Medical Birds -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <div>
          <Label>Medical Female</Label>
          <Input v-model.number="form.medical_female_qty" type="number" min="0" />
        </div>
        <div>
          <Label>Medical Male</Label>
          <Input v-model.number="form.medical_male_qty" type="number" min="0" />
        </div>
        <div>
          <Label>Total Medical Birds</Label>
          <Input :value="total_medical_bird" type="number" readonly class="bg-gray-100" />
        </div>
      </div>

      <!-- Deviations -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
        <div>
          <Label>Deviation Female</Label>
          <Input :value="deviation_female" type="number" readonly class="bg-gray-100" />
        </div>
        <div>
          <Label>Deviation Male</Label>
          <Input :value="deviation_male" type="number" readonly class="bg-gray-100" />
        </div>
        <div>
          <Label>Deviation Total</Label>
          <Input :value="deviation_total" type="number" readonly class="bg-gray-100" />
        </div>
      </div>

      <!-- Note -->
      <div>
        <Label>Note</Label>
        <textarea v-model="form.transfer_note" rows="2" class="border rounded px-3 py-2 w-full" placeholder="Optional note"></textarea>
      </div>

      <!-- Submit -->
      <div class="flex justify-end mt-4">
        <Button type="submit" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
          Transfer
        </Button>
      </div>
    </form>
  </AppLayout>
</template>
