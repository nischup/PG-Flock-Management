<script setup lang="ts">
import { ref, watch, reactive, computed } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { useNotifier } from "@/composables/useNotifier"

const { showInfo } = useNotifier()

// Dummy data for flocks and batches
const flocks = [
  { id: 1, flock_code: 'Flock 22', total_bird: 500, male_qty: 198, female_qty: 297, mortality: 5 },
  { id: 2, flock_code: 'Flock 23', total_bird: 600, male_qty: 250, female_qty: 348, mortality: 2 },
]

const batches = [
  { id: 1, batch_code: 'Batch A', flock_id: 1, total_bird: 100, male_qty: 20, female_qty: 80 , mortality:1 },
  { id: 2, batch_code: 'Batch B', flock_id: 1, total_bird: 200, male_qty: 15, female_qty: 185 , mortality:2 },
  { id: 3, batch_code: 'Batch B', flock_id: 2, total_bird: 200, male_qty: 10, female_qty: 190, mortality: 0 },
]

const companies = [
  { id: 1, code: 'PCL', name: 'PCL' },
  { id: 2, code: 'PBL', name: 'PBL' },
]

// Form
const form = useForm({
  flock_id: '',
  batch_id: '',
  total_bird: 0,
  male_qty: 0,
  female_qty: 0,
  mortality: 0,
  transfer_male_qty: 0,
  transfer_female_qty: 0,
  transfer_note: '',
  transfer_company: '',
   transfer_shed: '',
})

// Computed: Total transfer chicks
const total_transfer_chicks = computed(() => {
  return (form.transfer_male_qty || 0) + (form.transfer_female_qty || 0)
})

// Watch flock selection and update card values
watch(() => form.flock_id, (id) => {
  if (!id) {
    form.total_bird = 0
    form.male_qty = 0
    form.female_qty = 0
    form.mortality = 0
    form.batch_id = ''
    return
  }

  const flock = flocks.find(f => f.id == id)
  if (flock) {
    form.total_bird = flock.total_bird
    form.male_qty = flock.male_qty
    form.female_qty = flock.female_qty
    form.mortality = flock.mortality
  }
  form.batch_id = '' // reset batch when flock changes
})

// Watch batch selection and override card values if batch selected
watch(() => form.batch_id, (id) => {
  if (!id) return
  const batch = batches.find(b => b.id == id)
  if (batch) {
    form.total_bird = batch.total_bird
    form.male_qty = batch.male_qty
    form.female_qty = batch.female_qty
    form.mortality = batch.mortality // usually batch mortality not needed here
  }
})

function submit() {
  if (!form.flock_id) return showInfo("Please select a flock")
  if (!form.transfer_company) return showInfo("Please select a transfer company")

  // Add total transfer chicks to payload
  const payload = { ...form.data(), total_transfer_chicks: total_transfer_chicks.value }

  form.post(route('bird-transfer.store'), {
    data: payload,
    onSuccess: () => showInfo("Bird transfer saved successfully")
  })
}
</script>

<template>
  <AppLayout>
    <form @submit.prevent="submit" class="space-y-6 p-6 rounded m-5 bg-white">

      <!-- Flock and Batch Selection -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <Label>Select Flock</Label>
          <select v-model="form.flock_id" class="w-full mt-1 border rounded px-3 py-2">
            <option value="">Select Flock</option>
            <option v-for="flock in flocks" :key="flock.id" :value="flock.id">
              {{ flock.flock_code }}
            </option>
          </select>
        </div>

        <div>
          <Label>Select Batch (Optional)</Label>
          <select v-model="form.batch_id" class="w-full mt-1 border rounded px-3 py-2" :disabled="!form.flock_id">
            <option value="">Select Batch</option>
            <option v-for="batch in batches.filter(b => b.flock_id == form.flock_id)" :key="batch.id" :value="batch.id">
              {{ batch.batch_code }}
            </option>
          </select>
        </div>

        <div>
          <Label>Transfer To Company</Label>
          <select v-model="form.transfer_company" class="w-full mt-1 border rounded px-3 py-2">
            <option value="">Select Company</option>
            <option v-for="company in companies" :key="company.id" :value="company.code">
              {{ company.name }}
            </option>
          </select>
        </div>
      </div>
      <div v-if="form.transfer_company" class="mt-4">
        <Label>Select Shed</Label>
        <select v-model="form.transfer_shed" class="w-full mt-1 border rounded px-3 py-2">
          <option value="">Select Shed</option>
          <option v-for="shed in sheds" :key="shed.id" :value="shed.id">
            {{ shed.name }}
          </option>
        </select>
      </div>

      <!-- Card Section -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
        <div class="bg-yellow-100 p-4 rounded shadow text-center">
          <p class="text-gray-700 font-medium">Total Birds</p>
          <p class="text-2xl font-bold">{{ form.total_bird }}</p>
        </div>
        <div class="bg-red-100 p-4 rounded shadow text-center">
          <p class="text-gray-700 font-medium">Mortality</p>
          <p class="text-2xl font-bold">{{ form.mortality }}</p>
        </div>
        <div class="bg-blue-100 p-4 rounded shadow text-center">
          <p class="text-gray-700 font-medium">Male Birds</p>
          <p class="text-2xl font-bold">{{ form.male_qty }}</p>
        </div>
        <div class="bg-green-100 p-4 rounded shadow text-center">
          <p class="text-gray-700 font-medium">Female Birds</p>
          <p class="text-2xl font-bold">{{ form.female_qty }}</p>
        </div>
      </div>

      <!-- Transfer Inputs -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
        <div>
          <Label>Transfer Male Qty</Label>
          <Input v-model.number="form.transfer_male_qty" type="number" min="0" />
        </div>
        <div>
          <Label>Transfer Female Qty</Label>
          <Input v-model.number="form.transfer_female_qty" type="number" min="0" />
        </div>
        <div>
          <Label>Total Transfer Chicks</Label>
          <Input :value="total_transfer_chicks" type="number" readonly class="bg-gray-100" />
        </div>
        
      </div>
        <div>
          <Label>Note</Label>
          <textarea v-model="form.transfer_note" rows="2" class="border rounded px-3 py-2 w-full" placeholder="Optional note"></textarea>
        </div>
      <!-- Submit Button -->
      <div class="flex justify-end mt-4">
        <Button type="submit" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800">
          Transfer
        </Button>
      </div>
    </form>
  </AppLayout>
</template>
