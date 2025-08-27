<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { ref, computed, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AppLayout from '@/layouts/AppLayout.vue'
import Datepicker from '@vuepic/vue-datepicker'
import { useAgeCalculator } from '@/composables/useAgeCalculator'
import '@vuepic/vue-datepicker/dist/main.css'
import { type BreadcrumbItem } from '@/types'
import { useNotifier } from "@/composables/useNotifier"
// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Flock Management', href: '/flocks' },
  { title: 'Daily Operation', href: '' },
]

// Props
const props = defineProps<{
  flocks: Array<any>
  feeds?: Array<any>
}>()

const { showInfo } = useNotifier(); // auto-shows flash messages

// Tabs (keys must match below validations)
const tabs = [
  { key: 'daily_mortality', label: 'Mortality' },
  { key: 'feed_consumption', label: 'Feed' },
  { key: 'water_consumption', label: 'Water' },
  { key: 'light_hour', label: 'Light' },
  { key: 'destroy', label: 'Destroy' },       // uses `destroy` (number)
  { key: 'cull', label: 'Cull' },             // uses `cull` (number)
  { key: 'sexing_error', label: 'Sexing Error' }, // uses `sexing_error` (number)
  { key: 'weight', label: 'Weight' },         // uses `weight` (number)
  { key: 'temperature', label: 'Temperature' }, // uses `temperature` (number)
  { key: 'humidity', label: 'Humidity' },     // uses `humidity` (number)
  { key: 'medicine', label: 'Medicine' },     // optional in this form model
  { key: 'vaccine', label: 'Vaccine' },       // optional in this form model
]

// Active Tab + progress
const activeTabIndex = ref(0)
const totalTabs = tabs.length
const currentStep = computed(() => activeTabIndex.value + 1)
const progress = computed(() => (currentStep.value / totalTabs) * 100)
// Compute gradient for full progress bar


const progressBarBackground = computed(() => {
  const segmentPercent = 100 / tabs.length
  const segments: string[] = []

  tabs.forEach((tab, index) => {
    const key = tab.key
    const mainFields = mainFieldsByTab[key] || []
    const noteField = noteFieldByTab[key]

    const allMainEmpty = mainFields.every(f => {
      const val = form[f]
      if (typeof val === 'number') return val === 0
      if (typeof val === 'string') return val.trim() === ''
      return !val
    })

    const noteHasValue = noteField && (form[noteField] || '').toString().trim() !== ''

    // Determine segment color
    const color = allMainEmpty && noteHasValue ? '#ef4444' : '#facc15' // red : yellow

    const start = index * segmentPercent
    const end = (index + 1) * segmentPercent

    // Each segment as "color start% end%"
    segments.push(`${color} ${start}% ${end}%`)
  })

  // Join segments and return a proper linear-gradient
  return `linear-gradient(to right, ${segments.join(', ')})`
})


const activeTab = computed(() => tabs[activeTabIndex.value].key)

// Form (exactly as you stated)
const form = useForm({
  flock_id: '',
  operation_date: new Date().toISOString().substr(0, 10),
  female_mortality: 0,
  male_mortality: 0,
  water_quantity:0,
  female_reason: '',
  male_reason: '',
  mortalitynote: '',
  feed_type_id: '',
  feed_quantity: 0,
  feed_unit: '',
  feed_note: '',
  light_hour: 0,
  destroy: 0,
  cull_male_qty: 0,
  cull_female_qty: 0,
  cull_male_reason:'',
  cull_female_reason:'',
  sexing_error: 0,
  egg_collection: 0,
  weight: 0,
  temperature: 0,
  humidity: 0,
  water_note: '',
  light_note: '',
  destroy_note: '',
  culling_note: '',
  serror_note: '',
  weight_note: '',
  temperature_note: '',
  humidity_note: '',
})

// Errors
const errors = ref<Record<string, string>>({})

// Shed & flock info (demo)
const shedQty = ref({ opening: 0, current: 0 })
const flockInfo = ref<{ age: string }>({ age: '0 weeks 0 days' })
const shedInfo = {
  1: { opening: 12000, current: 11500, start_date: '2025-07-12' },
  2: { opening: 11500, current: 11450, start_date: '2025-05-12' },
  3: { opening: 11000, current: 10000, start_date: '2025-06-12' },
}
const tabCountsData = {
  1: { daily_mortality: 10, feed_consumption: "200 Kg", water_consumption: "150 L", light_hour: "80 H", destroy: 5, cull: 3, sexing_error: 2, weight: "1600 gm", temperature: 28, humidity: 70, egg_collection: 9000 },
  2: { daily_mortality: 15, feed_consumption: "180 Kg", water_consumption: "130 L", light_hour: "80 H", destroy: 4, cull: 2, sexing_error: 1, weight: "1500 gm", temperature: 29, humidity: 65, egg_collection: 8900 },
  3: { daily_mortality: 25, feed_consumption: "190 Kg", water_consumption: "160 L", light_hour: "70 H", destroy: 6, cull: 5, sexing_error: 3, weight: "1400 gm", temperature: 27, humidity: 72, egg_collection: 8000 },
}
const counts = ref<Record<string, number | string>>({})

// Watch flock change (demo behavior)
watch(() => form.flock_id, (id) => {
  if (!id) {
    shedQty.value = { opening: 0, current: 0 }
    flockInfo.value.age = '0 weeks 0 days'
    counts.value = {}
    return
  }
  const data = (shedInfo as any)[id]
  if (data) {
    shedQty.value = { opening: data.opening, current: data.current }
    counts.value = (tabCountsData as any)[id]
    flockInfo.value.age = useAgeCalculator(data.start_date)
  }
})

// ---------- Validation Setup ----------
type Rule = { field: keyof typeof form; label: string; kind: 'string' | 'number'; min?: number }

// Basic note field rules for each tab
const rulesByTab: Record<string, Rule[]> = {
  daily_mortality: [{ field: 'mortalitynote', label: 'Mortality note', kind: 'string' }],
  feed_consumption: [{ field: 'feed_note', label: 'Feed note', kind: 'string' }],
  water_consumption: [{ field: 'water_note', label: 'Water note', kind: 'string' }],
  light_hour: [{ field: 'light_note', label: 'Light note', kind: 'string' }],
  destroy: [{ field: 'destroy_note', label: 'Destroy note', kind: 'string' }],
  cull: [{ field: 'culling_note', label: 'Culling note', kind: 'string' }],
  sexing_error: [{ field: 'serror_note', label: 'Sexing note', kind: 'string' }],
  weight: [{ field: 'weight_note', label: 'Weight note', kind: 'string' }],
  temperature: [{ field: 'temperature_note', label: 'Temperature note', kind: 'string' }],
  humidity: [{ field: 'humidity_note', label: 'Humidity note', kind: 'string' }],
  medicine: [{ field: 'medicine_note', label: 'Medicine note', kind: 'string' }],
  vaccine: [{ field: 'vaccine_note', label: 'Vaccine note', kind: 'string' }],
}

// ---------- Main Fields Mapping ----------
const mainFieldsByTab: Record<string, (keyof typeof form)[]> = {
  daily_mortality: ['female_mortality', 'male_mortality', 'female_reason', 'male_reason'],
  feed_consumption: ['feed_type_id', 'feed_quantity', 'feed_unit'],
  water_consumption: ['water_quantity'],
  light_hour: ['light_hour'],
  destroy: ['destroy_male_qty'],
  cull: ['cull_male_qty'],
  sexing_error: ['sexing_error'],
  weight: ['weight'],
  temperature: ['temperature'],
  humidity: ['humidity'],
  medicine: ['medicine_id', 'medicine_quantity', 'medicine_unit'],
  vaccine: ['vaccine_id', 'vaccine_dose', 'vaccine_unit'],
}

// ---------- Note Field Mapping ----------
const noteFieldByTab: Record<string, keyof typeof form> = {
  daily_mortality: 'mortalitynote',
  feed_consumption: 'feed_note',
  water_consumption: 'water_note',
  light_hour: 'light_note',
  destroy: 'destroy_note',
  cull: 'culling_note',
  seerror_error: 'serror_note',
  weight: 'weight_note',
  temperature: 'temperature_note',
  humidity: 'humidity_note',
  medicine: 'medicine_note',
  vaccine: 'vaccine_note',
}

// ---------- Clear Errors ----------
function clearErrorsForTab(tabKey: string) {
  const rules = rulesByTab[tabKey] || []
  for (const r of rules) {
    delete errors.value[r.field as string]
  }
}

// ---------- Dynamic Validation ----------
function validateTab(tabKey: string): boolean {
  clearErrorsForTab(tabKey)
  const rules = rulesByTab[tabKey] || []
  let ok = true

  for (const r of rules) {
    const v = (form as any)[r.field]

    // Check dynamic note requirement
    if (noteFieldByTab[tabKey] && r.field === noteFieldByTab[tabKey]) {
      const mainFields = mainFieldsByTab[tabKey] || []

      const allEmpty = mainFields.every(f => {
        const val = form[f]
        if (typeof val === 'number') return val === 0
        if (typeof val === 'string') return val.trim() === ''
        return !val
      })

      if (allEmpty && (!v || v.trim() === '')) {
        errors.value[r.field as string] = `${r.label} is required`
        showInfo("If any field is skipped, please provide a brief note explaining why.")
        ok = false
      }

      continue
    }

    // Normal validation
    if (r.kind === 'string') {
      if (!v || (typeof v === 'string' && v.trim() === '')) {
        errors.value[r.field as string] = `${r.label} is required`
        ok = false
      }
    } else if (r.kind === 'number') {
      const n = Number(v)
      const needsMin = typeof r.min === 'number'
      if (Number.isNaN(n) || (needsMin ? n < r.min! : v === null || v === undefined)) {
        errors.value[r.field as string] = `${r.label} must be greater than ${needsMin ? r.min : 'or equal to 0'}`
        ok = false
      }
    }
  }

  return ok
}

// Navigation (guarded)
function nextTab() {
  const key = activeTab.value
  if (!validateTab(key)) return
  if (activeTabIndex.value < tabs.length - 1) activeTabIndex.value++
}

function prevTab() {
  if (activeTabIndex.value > 0) activeTabIndex.value--
}

function goToTab(index: number) {
  if (index <= activeTabIndex.value) {
    activeTabIndex.value = index
    return
  }
  const key = activeTab.value
  if (!validateTab(key)) return
  activeTabIndex.value = index
}
// Submit
function submit() {
  // Validate last active tab before submit (optional)
  if (!validateTab(activeTab.value)) return

  // Optionally, validate all tabs before post:
  // for (const t of tabs) { if (!validateTab(t.key)) { activeTabIndex.value = tabs.findIndex(x => x.key === t.key); return } }

  form.post(route('daily-operations.store'), {
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Daily Operation" />

    <form @submit.prevent="submit" class="p-6 space-y-6">
      <!-- Flock Info -->
      <div class="border rounded-lg p-4 shadow-sm bg-white">
        <h2 class="font-semibold text-lg mb-4">Flock Information</h2>

        <!-- Progress Bar -->
        <div class="w-full bg-gray-200 rounded-xl h-6 overflow-hidden mb-4">
          <div
            class="h-full flex items-center justify-center text-black font-bold text-sm transition-all duration-500 rounded-xl"
            :style="{ width: progress + '%', background: progressBarBackground }"
          >
            {{ currentStep }} / {{ totalTabs }}
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Flock Select -->
          <div>
            <Label>Select Flock</Label>
            <select v-model="form.flock_id" class="w-full mt-1 border rounded px-3 py-2">
              <option value="">Select Flock</option>
              <option v-for="flock in props.flocks" :key="flock.id" :value="flock.id">
                {{ flock.flock_code }}
              </option>
            </select>
          </div>

          <!-- Date Picker -->
          <div>
            <Label>Date</Label>
            <Datepicker
              v-model="form.operation_date"
              format="yyyy-MM-dd"
              :input-class="'mt-2 border rounded px-3 py-2 w-full'"
              placeholder="Select Date"
              :auto-apply="true"
            />
          </div>

          <!-- Shed Info -->
          <div class="col-span-1 md:col-span-2 mt-2 border rounded shadow-sm bg-white p-1">
            <div class="grid grid-cols-3 gap-4 text-center">
              <div class="bg-yellow-100 p-4">
                <p class="text-gray-700 font-medium">Total Chicks</p>
                <p class="text-xl font-bold">{{ shedQty.opening }}</p>
              </div>
              <div class="bg-green-100 p-4">
                <p class="text-gray-700 font-medium">Current Chicks</p>
                <p class="text-xl font-bold">{{ shedQty.current }}</p>
              </div>
              <div class="bg-blue-100 p-4">
                <p class="text-gray-700 font-medium">Current Age</p>
                <p class="text-xl font-bold">{{ flockInfo.age }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabs -->
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-4">
        <div
          v-for="(tab, index) in tabs"
          :key="tab.key"
          @click="goToTab(index)"
          class="cursor-pointer p-6 border rounded-lg shadow text-center font-semibold transition-transform hover:scale-105"
          :class="activeTabIndex === index ? 'bg-chicken text-white' : 'bg-white text-gray-700'"
        >
          {{ tab.label }}
          <span v-if="counts[tab.key] !== undefined" class="block mt-2 text-black text-2xl font-bold">
            {{ counts[tab.key] }}
          </span>
        </div>
      </div>

      <!-- Tab Content -->
      <div class="border rounded-lg p-4 shadow-sm mt-4 bg-white">
        <!-- Mortality -->
        <div v-if="activeTab === 'daily_mortality'">
          <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col">
              <Label class="mb-2">Female Chick Mortality</Label>
              <Input v-model.number="form.female_mortality" type="number" />
            </div>
            <div class="flex flex-col">
              <Label class="mb-2">Male Chick Mortality</Label>
              <Input v-model.number="form.male_mortality" type="number" />
            </div>
            <div class="flex flex-col">
              <Label class="mb-2">Female Reason</Label>
              <Input v-model="form.female_reason" type="text" />
            </div>
            <div class="flex flex-col">
              <Label class="mb-2">Male Reason</Label>
              <Input v-model="form.male_reason" type="text" />
            </div>
            <div class="flex flex-col col-span-2">
              <Label class="mb-2">Note <span class="text-red-500">*</span></Label>
              <textarea
                v-model="form.mortalitynote"
                class="border rounded px-3 py-2"
                :class="errors.mortalitynote ? 'border-red-500 ring-1 ring-red-500' : ''"
              ></textarea>
              <p v-if="errors.mortalitynote" class="text-red-600 text-sm mt-1">{{ errors.mortalitynote }}</p>
            </div>
          </div>
        </div>

        <!-- Feed (kept aligned to your form fields) -->
        <div v-if="activeTab === 'feed_consumption'">
          <div class="grid grid-cols-3 gap-4">
            <div>
              <Label class="mb-2">Feed Type</Label>
              <select
                v-model="form.feed_type_id"
                class="w-full mt-1 border rounded px-3 py-2"
                :class="errors.feed_type_id ? 'border-red-500 ring-1 ring-red-500' : ''"
              >
                <option value="">Select Feed</option>
                <option v-for="feed in props.feeds" :key="feed.id" :value="feed.id">
                  {{ feed.name }}
                </option>
              </select>
              <p v-if="errors.feed_type_id" class="text-red-600 text-sm mt-1">{{ errors.feed_type_id }}</p>
            </div>
            <div>
              <Label class="mb-2">Quantity</Label>
              <Input
                v-model.number="form.feed_quantity"
                type="number"
                :class="errors.feed_quantity ? 'border-red-500 ring-1 ring-red-500' : ''"
              />
              <p v-if="errors.feed_quantity" class="text-red-600 text-sm mt-1">{{ errors.feed_quantity }}</p>
            </div>
            <div>
              <Label class="mb-2">Unit</Label>
              <Input
                v-model="form.feed_unit"
                type="text"
                :class="errors.feed_unit ? 'border-red-500 ring-1 ring-red-500' : ''"
              />
              <p v-if="errors.feed_unit" class="text-red-600 text-sm mt-1">{{ errors.feed_unit }}</p>
            </div>
            <div class="flex flex-col col-span-3">
              <Label class="mb-2">Note</Label>
              <textarea
                v-model="form.feed_note"
                class="border rounded px-3 py-2"
                :class="errors.feed_note ? 'border-red-500 ring-1 ring-red-500' : ''"
              ></textarea>
              <p v-if="errors.feed_note" class="text-red-600 text-sm mt-1">{{ errors.feed_note }}</p>
            </div>
          </div>
        </div>

        <!-- Water (aligned: only water_consumption exists in form) -->
        <!-- Water Tab -->
      <div v-if="activeTab === 'water_consumption'">
        <div class="grid grid-cols-3 gap-4">
          <div>
            <Label class="mb-2">Water</Label>
            <select v-model="form.water_type_id" class="w-full mt-1 border rounded px-3 py-2">
                <option value="">Select Water</option>
                <option v-for="water in props.waters" :key="water.id" :value="water.id">
                {{ water.name }}
                </option>
            </select>
            </div>

            <div>
            <Label class="mb-2">Quantity</Label>
            <Input v-model.number="form.water_quantity" type="number" />
            </div>
            
        </div>
          <div class="flex flex-col mt-5">
            <Label class="mb-2">Note</Label>
              <textarea
                v-model="form.water_note"
                class="border rounded px-3 py-2"
                :class="errors.water_note ? 'border-red-500 ring-1 ring-red-500' : ''"
              ></textarea>
              <p v-if="errors.water_note" class="text-red-600 text-sm mt-1">{{ errors.water_note }}</p>
          </div>
      </div>

      <!-- Water Tab -->
      <div v-if="activeTab === 'light_hour'">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <Label>Light Hour</Label>
                <Input v-model.number="form.light_hour" type="number" />
            </div>
            
            </div>
            <div class="flex flex-col mt-5">
                <Label class="mb-2">Note</Label>
                <textarea
                v-model="form.light_note"
                class="border rounded px-3 py-2"
                :class="errors.light_note ? 'border-red-500 ring-1 ring-red-500' : ''"
              ></textarea>
              <p v-if="errors.light_note" class="text-red-600 text-sm mt-1">{{ errors.light_note }}</p>
            </div>
      </div>


    <!-- destroy Tab -->
      <div v-if="activeTab === 'destroy'">

        <div class="grid grid-cols-2 gap-4">
            <!-- Destroy Male Quantity -->
            <div class="flex flex-col">
                <Label class="mb-2">Destroy Male Qty</Label>
                <Input v-model.number="form.destroy_male_qty" type="number" min="0" />
            </div>

            <!-- Destroy Male Reason -->
            <div class="flex flex-col">
                <Label class="mb-2">Destroy Male Reason</Label>
                <Input v-model="form.destroy_male_reason" type="text" />
            </div>

            <!-- Destroy Female Quantity -->
            <div class="flex flex-col">
                <Label class="mb-2">Destroy Female Qty</Label>
                <Input v-model.number="form.destroy_female_qty" type="number" min="0" />
            </div>

            <!-- Destroy Female Reason -->
            <div class="flex flex-col">
                <Label class="mb-2">Destroy Female Reason</Label>
                <Input v-model="form.destroy_female_reason" type="text" />
            </div>
            </div>

            <!-- Note -->
            <div class="flex flex-col mt-4">
            <Label class="mb-2">Note</Label>
              <textarea
                v-model="form.destroy_note"
                class="border rounded px-3 py-2"
                :class="errors.destroy_note ? 'border-red-500 ring-1 ring-red-500' : ''"
              ></textarea>
              <p v-if="errors.destroy_note" class="text-red-600 text-sm mt-1">{{ errors.destroy_note }}</p>
            </div>

        </div>


        <!-- culling Tab -->
      <div v-if="activeTab === 'cull'">

            <div class="grid grid-cols-2 gap-4">
                <!-- Cull Male Quantity -->
                <div class="flex flex-col">
                    <Label class="mb-2">Cull Male Qty</Label>
                    <Input v-model.number="form.cull_male_qty" type="number" min="0" />
                </div>

                <!-- Cull Male Reason -->
                <div class="flex flex-col">
                    <Label class="mb-2">Cull Male Reason</Label>
                    <Input v-model="form.cull_male_reason" type="text" />
                </div>

                <!-- Cull Female Quantity -->
                <div class="flex flex-col">
                    <Label class="mb-2">Cull Female Qty</Label>
                    <Input v-model.number="form.cull_female_qty" type="number" min="0" />
                </div>

                <!-- Cull Female Reason -->
                <div class="flex flex-col">
                    <Label class="mb-2">Cull Female Reason</Label>
                    <Input v-model="form.cull_female_reason" type="text" />
                </div>
                </div>

                <!-- Note -->
                <div class="flex flex-col mt-4">
                <Label class="mb-2">Note</Label>
                <textarea
                v-model="form.culling_note"
                class="border rounded px-3 py-2"
                :class="errors.culling_note ? 'border-red-500 ring-1 ring-red-500' : ''"
              ></textarea>
              <p v-if="errors.culling_note" class="text-red-600 text-sm mt-1">{{ errors.culling_note }}</p>
                </div>

        </div>

        <!-- sexxing error Tab -->
        <div v-if="activeTab === 'sexing_error'">
            <div class="grid grid-cols-2 gap-4">
                <!-- Sexing Error Male Qty -->
                <div class="flex flex-col">
                    <Label class="mb-2">Sexing Error Male Qty</Label>
                    <Input v-model.number="form.sexing_error_male_qty" type="number" min="0" />
                </div>

                

                <!-- Sexing Error Female Qty -->
                <div class="flex flex-col">
                    <Label class="mb-2">Sexing Error Female Qty</Label>
                    <Input v-model.number="form.sexing_error_female_qty" type="number" min="0" />
                </div>
             </div>

            <!-- Note -->
            <div class="flex flex-col mt-4">
            <Label class="mb-2">Note</Label>
            <textarea
                v-model="form.serror_note"
                class="border rounded px-3 py-2"
                :class="errors.serror_note ? 'border-red-500 ring-1 ring-red-500' : ''"
              ></textarea>
              <p v-if="errors.serror_note" class="text-red-600 text-sm mt-1">{{ errors.serror_note }}</p>
            </div>  
        </div>


        <!-- weight Tab -->
        <div v-if="activeTab === 'weight'">
            <div class="grid grid-cols-2 gap-4">
                <!-- Weight Male Qty -->
                <div class="flex flex-col">
                    <Label class="mb-2">Weight Male Qty</Label>
                    <Input v-model.number="form.weight_male_qty" type="number" min="0" />
                </div>

                <!-- Weight Female Qty -->
                <div class="flex flex-col">
                    <Label class="mb-2">Weight Female Qty</Label>
                    <Input v-model.number="form.weight_female_qty" type="number" min="0" />
                </div>
                </div>

                <!-- Note -->
                <div class="flex flex-col mt-4">
                <Label class="mb-2">Note</Label>
                <textarea
                v-model="form.weight_note"
                class="border rounded px-3 py-2"
                :class="errors.weight_note ? 'border-red-500 ring-1 ring-red-500' : ''"
              ></textarea>
              <p v-if="errors.weight_note" class="text-red-600 text-sm mt-1">{{ errors.weight_note }}</p>
                </div>

        </div>


        <!-- temperature Tab -->
        <div v-if="activeTab === 'temperature'">
            <div class="grid grid-cols-2 gap-4">
                <!-- Inside Temperature -->
                <div class="flex flex-col">
                    <Label class="mb-2">Inside Temperature</Label>
                    <Input v-model.number="form.inside_temp" type="number" step="0.1" />
                </div>

                <!-- Std Inside Temperature -->
                <div class="flex flex-col">
                    <Label class="mb-2">Std Inside Temperature</Label>
                    <Input v-model.number="form.std_inside_temp" type="number" step="0.1" />
                </div>

                <!-- Outside Temperature -->
                <div class="flex flex-col">
                    <Label class="mb-2">Outside Temperature</Label>
                    <Input v-model.number="form.outside_temp" type="number" step="0.1" />
                </div>

                <!-- Std Outside Temperature -->
                <div class="flex flex-col">
                    <Label class="mb-2">Std Outside Temperature</Label>
                    <Input v-model.number="form.std_outside_temp" type="number" step="0.1" />
                </div>
                </div>

                <!-- Note -->
                <div class="flex flex-col mt-4">
                <Label class="mb-2">Note</Label>
                <textarea
                v-model="form.temperature_note"
                class="border rounded px-3 py-2"
                :class="errors.temperature_note ? 'border-red-500 ring-1 ring-red-500' : ''"
              ></textarea>
              <p v-if="errors.temperature_note" class="text-red-600 text-sm mt-1">{{ errors.temperature_note }}</p>
                </div>

        </div>


        <!-- humidity Tab -->
        <div v-if="activeTab === 'humidity'">
            <div class="grid grid-cols-2 gap-4">
            <!-- Today Humidity -->
            <div class="flex flex-col">
                <Label class="mb-2">Today Humidity (%)</Label>
                <Input v-model.number="form.today_humidity" type="number" step="0.1" />
            </div>

            <!-- Std Humidity -->
            <div class="flex flex-col">
                <Label class="mb-2">Std Humidity (%)</Label>
                <Input v-model.number="form.std_humidity" type="number" step="0.1" />
            </div>
            </div>

            <!-- Note -->
            <div class="flex flex-col mt-4">
            <Label class="mb-2">Note</Label>
            <textarea
                v-model="form.humidity_note"
                class="border rounded px-3 py-2"
                :class="errors.humidity_note ? 'border-red-500 ring-1 ring-red-500' : ''"
              ></textarea>
              <p v-if="errors.humidity_note" class="text-red-600 text-sm mt-1">{{ errors.humidity_note }}</p>
            </div>
        </div>



         <!-- medicine Tab -->
        <div v-if="activeTab === 'medicine'">

            <div class="grid grid-cols-2 gap-4">
                <!-- Medicine Name -->
                <div class="flex flex-col">
                    <Label class="mb-2">Medicine</Label>
                    <select v-model="form.medicine_id" class="w-full mt-1 border rounded px-3 py-2">
                    <option value="">Select Medicine</option>
                    <option v-for="medicine in props.medicines" :key="medicine.id" :value="medicine.id">
                        {{ medicine.name }}
                    </option>
                    </select>
                </div>

                <!-- Quantity -->
                <div class="flex flex-col">
                    <Label class="mb-2">Quantity</Label>
                    <Input v-model.number="form.medicine_quantity" type="number" min="0" />
                </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-4">
                <!-- Unit -->
                <div class="flex flex-col">
                    <Label class="mb-2">Unit</Label>
                    <Input v-model="form.medicine_unit" type="text" placeholder="ml / gm / tablet" />
                </div>

                <!-- Time -->
                <div class="flex flex-col">
                    <Label class="mb-2">Time</Label>
                    <Input v-model="form.medicine_time" type="time" />
                </div>
                </div>

                <!-- Note -->
                <div class="flex flex-col mt-4">
                <Label class="mb-2">Note</Label>
                <textarea v-model="form.medicine_note" class="border rounded px-3 py-2"></textarea>
                </div>
            </div> 
            
            <div v-if="activeTab === 'vaccine'">

        
                <div class="grid grid-cols-2 gap-4">
                <!-- Vaccine Name -->
                <div class="flex flex-col">
                    <Label class="mb-2">Vaccine</Label>
                    <select v-model="form.vaccine_id" class="w-full mt-1 border rounded px-3 py-2">
                    <option value="">Select Vaccine</option>
                    <option v-for="vaccine in props.vaccines" :key="vaccine.id" :value="vaccine.id">
                        {{ vaccine.name }}
                    </option>
                    </select>
                </div>
                <div class="flex flex-col">
                    <Label class="mb-2">Dose</Label>
                    <Input v-model.number="form.vaccine_dose" type="number" min="0" />
                </div>
                <!-- Unit -->
                <div class="flex flex-col">
                    <Label class="mb-2">Unit</Label>
                    <Input v-model="form.vaccine_unit" type="text" placeholder="ml / dose" />
                </div>
                <!-- File Upload -->
                 <div class="flex flex-col"></div>
                <Label class="mb-2">Upload File</Label>
                <input 
                    type="file" 
                    @change="form.vaccine_file = $event.target.files[0]" 
                    class="w-full border rounded px-3 py-2 mt-1"
                />
                </div>

                <!-- Note -->
                <div class="flex flex-col mt-4">
                <Label class="mb-2">Note</Label>
                <textarea v-model="form.vaccine_note" class="border rounded px-3 py-2"></textarea>
                </div>
     
            
        </div> 
         </div> 
      <!-- Navigation -->
      <div class="flex justify-between mt-4">
        <Button type="button" @click="prevTab" :disabled="activeTabIndex === 0">Previous</Button>
        <div class="flex gap-2">
          <Button v-if="activeTabIndex < tabs.length - 1" type="button" @click="nextTab">Next</Button>
          <Button v-else type="submit" class="bg-chicken">Save</Button>
        </div>
      </div>
    </form>
  </AppLayout>
</template>
