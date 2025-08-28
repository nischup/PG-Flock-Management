<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import Datepicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import { useNotifier } from "@/composables/useNotifier"

// Props
const props = defineProps<{ flocks: Array<any> }>()
const { showInfo } = useNotifier()

// Form
const form = useForm({
  flock_id: '',
  operation_date: new Date().toISOString().substr(0, 10),
  total_egg: 0,

  // Rejected Egg Details
  double_yolk: 0,
  double_yolk_note: '',
  double_yolk_broken: 0,
  double_yolk_broken_note: '',
  commercial: 0,
  commercial_note: '',
  commercial_broken: 0,
  commercial_broken_note: '',
  liquid: 0,
  liquid_note: '',
  damage: 0,
  damage_note: '',

  // Technical Information
  floor_egg: 0,
  floor_egg_note: '',
  thin_egg: 0,
  thin_egg_note: '',
  misshape_egg: 0,
  misshape_egg_note: '',
  white_egg: 0,
  white_egg_note: '',
  dirty_egg: 0,
  dirty_egg_note: '',
})

// Tabs
const rejectedTabs = [
  { key: 'double_yolk', label: 'Double Yolk' },
  { key: 'double_yolk_broken', label: 'Double Yolk Broken' },
  { key: 'commercial', label: 'Commercial' },
  { key: 'commercial_broken', label: 'Commercial Broken' },
  { key: 'liquid', label: 'Liquid' },
  { key: 'damage', label: 'Damage' },
]
const techTabs = [
  { key: 'floor_egg', label: 'Floor Egg' },
  { key: 'thin_egg', label: 'Thin Egg' },
  { key: 'misshape_egg', label: 'Misshape Egg' },
  { key: 'white_egg', label: 'White Egg' },
  { key: 'dirty_egg', label: 'Dirty Egg' },
]

const activeRejectedTab = ref(0)
const activeTechTab = ref(0)

// Totals
const rejected_total = computed(() =>
  rejectedTabs.reduce((sum, t) => sum + form[t.key], 0)
)
const tech_total = computed(() =>
  techTabs.reduce((sum, t) => sum + form[t.key], 0)
)
const hatching_egg = computed(() => form.total_egg - rejected_total.value)

// Example flock data
const flockEggData = { 1: 12000, 2: 11500, 3: 11000 }
watch(() => form.flock_id, (id) => {
  form.total_egg = id ? flockEggData[id] || 0 : 0
})

function submit() {
  if (!form.flock_id) return showInfo("Please select a flock")
  form.post(route('egg-classification.store'), {
    onSuccess: () => showInfo("Egg classification saved successfully")
  })
}
</script>

<template>
  <AppLayout>
    <form @submit.prevent="submit" class="space-y-6">

      <!-- Egg Classification Section -->
      <div class="m-5 p-6 bg-white rounded-xl shadow border">
        <h2 class="text-xl font-semibold mb-4">Egg Classification</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label>Select Flock</Label>
            <select v-model="form.flock_id" class="w-full mt-1 border rounded px-3 py-2">
              <option value="">Select Flock</option>
              <option v-for="flock in props.flocks" :key="flock.id" :value="flock.id">
                {{ flock.flock_code }}
              </option>
            </select>
          </div>
          <div>
            <Label>Date</Label>
            <Datepicker v-model="form.operation_date" format="yyyy-MM-dd"
              :input-class="'mt-2 border rounded px-3 py-2 w-full'" />
          </div>
        </div>

        <!-- Summary Boxes -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
          <div class="bg-yellow-100 p-4 rounded shadow text-center">
            <p class="text-gray-700 font-medium">Total Eggs</p>
            <p class="text-2xl font-bold">{{ form.total_egg }}</p>
          </div>
          <div class="bg-red-100 p-4 rounded shadow text-center">
            <p class="text-gray-700 font-medium">Rejected Eggs</p>
            <p class="text-2xl font-bold">{{ rejected_total }}</p>
          </div>
          <div class="bg-blue-100 p-4 rounded shadow text-center">
            <p class="text-gray-700 font-medium">Technical Info</p>
            <p class="text-2xl font-bold">{{ tech_total }}</p>
          </div>
          <div class="bg-green-100 p-4 rounded shadow text-center">
            <p class="text-gray-700 font-medium">Hatching Eggs</p>
            <p class="text-2xl font-bold">{{ hatching_egg }}</p>
          </div>
        </div>
      </div>

      <!-- Rejected Egg Details Section -->
      <div class="m-5 p-6 bg-white rounded-xl shadow border">
        <h3 class="font-semibold mb-2">Rejected Egg Details</h3>
        <div class="grid grid-cols-2 md:grid-cols-6 gap-2 mb-4">
          <button v-for="(tab, index) in rejectedTabs" :key="tab.key" type="button"
            @click="activeRejectedTab = index"
            class="p-3 rounded border font-medium text-center"
            :class="activeRejectedTab === index ? 'bg-chicken text-white' : 'bg-white text-gray-700'">
            {{ tab.label }} <br /> <span class="text-xl font-bold">{{ form[tab.key] }}</span>
          </button>
        </div>
        <div>
          <Label>Qty</Label>
          <Input v-model.number="form[rejectedTabs[activeRejectedTab].key]" type="number" min="0" />
          <Label class="mt-2">Note</Label>
          <textarea v-model="form[rejectedTabs[activeRejectedTab].key + '_note']" class="border rounded px-3 py-2 w-full" rows="2" placeholder="Optional note"></textarea>
        </div>
      </div>

      <!-- Technical Information Section -->
      <div class="m-5 p-6 bg-white rounded-xl shadow border">
        <h3 class="font-semibold mb-2">Technical Information</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-2 mb-4">
          <button v-for="(tab, index) in techTabs" :key="tab.key" type="button"
            @click="activeTechTab = index"
            class="p-3 rounded border font-medium text-center"
            :class="activeTechTab === index ? 'bg-chicken text-white' : 'bg-white text-gray-700'">
            {{ tab.label }} <br /> <span class="text-xl font-bold">{{ form[tab.key] }}</span>
          </button>
        </div>
        <div>
          <Label>Qty</Label>
          <Input v-model.number="form[techTabs[activeTechTab].key]" type="number" min="0" />
          <Label class="mt-2">Note</Label>
          <textarea v-model="form[techTabs[activeTechTab].key + '_note']" class="border rounded px-3 py-2 w-full" rows="2" placeholder="Optional note"></textarea>
        </div>
      </div>

      <!-- Submit -->
      <div class="flex justify-end mt-4 m-5">
        <Button type="submit" class="bg-chicken">Save Classification</Button>
      </div>
    </form>
  </AppLayout>
</template>
