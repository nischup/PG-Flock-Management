<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AppLayout from '@/layouts/AppLayout.vue'
import Datepicker from '@vuepic/vue-datepicker'
import { useAgeCalculator } from '@/composables/useAgeCalculator'
import '@vuepic/vue-datepicker/dist/main.css'
import { type BreadcrumbItem } from '@/types'
import { useNotifier } from "@/composables/useNotifier"
import { useDropdownOptions } from '@/composables/dropdownOptions'
import {
  ChevronLeft,
  ChevronRight,
  Save,
  Trash2,
  Scissors,
  AlertTriangle,
  Weight,
  Thermometer,
  Cloud,
  Egg,
  Pill,
  Syringe,
  ChevronDown,
  Search,
  CheckCircle2,
  AlertCircle,
  Building2,
  Calendar,
  X
} from 'lucide-vue-next'
// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Flock Management', href: '/flocks' },
  { title: 'Daily Operation', href: '' },
]

// Props
const props = defineProps<{
  flocks: Array<any>
  feeds?: Array<any>
  vaccines?:Array<any>
  medicines?:Array<any>
  waters?:Array<any>
  units?:Array<any>
  stage?: string
  todayVaccineSchedules?: Array<any>
}>()

const { showInfo } = useNotifier(); // auto-shows flash messages

// Tabs (keys must match below validations)
const tabs = [
  { key: 'daily_mortality', label: 'Mortality' },
  { key: 'destroy', label: 'Destroy' }, 
  { key: 'sexing_error', label: 'Sexing Error' },
  { key: 'cull', label: 'Cull' }, 
  { key: 'feed_consumption', label: 'Feed' },
  { key: 'water_consumption', label: 'Water' },
  { key: 'light_hour', label: 'Light' },  
  { key: 'medicine', label: 'Medicine' },     // optional in this form model
  { key: 'vaccine', label: 'Vaccine' }, // uses `cull` (number)
   // uses `sexing_error` (number)
  { key: 'weight', label: 'Weight' },         // uses `weight` (number)
  { key: 'temperature', label: 'Temperature' }, // uses `temperature` (number)
  { key: 'feedingprogram', label: 'Feeding Program' }, // uses `temperature` (number)
  { key: 'feedFinishingtime', label: 'Finishing Time' }, // uses `temperature` (number)
  { key: 'humidity', label: 'Humidity' },     // uses `humidity` (number)
  { key: 'egg_collection', label: 'Egg collection' },      // optional in this form model
]

const { batchOptions } = useDropdownOptions()

const batchWithLabel = computed(() =>
  props.flocks?.map(flock => {
    const batch = batchOptions.find(b => b.value === flock.batch_no)
    return {
      ...flock,
      batch_label: batch?.label || '', // safe access
      display_label: `${flock.label}-${batch?.label || ''}`, // fallback
    }
  }) || []
)
console.log(batchWithLabel);
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
    let color = '#facc15' // default yellow
    
    if (activeTabIndex.value === index) {
      // Active tab - use glossy green
      color = '#22c55e' // green-500
    } else if (allMainEmpty && noteHasValue) {
      // Has note but no main fields - use red
      color = '#ef4444' // red-500
    }

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
  batchassign_id: '',
  operation_date: new Date().toISOString().substr(0, 10),
  female_mortality: 0,
  male_mortality: 0,
  water_type:'',
  water_quantity:0,
  female_reason: '',
  male_reason: '',
  mortalitynote: '',
  feed_type_id: '',
  feed_quantity: 0,
  feed_unit: '',
  feed_note: '',
  light_hour: 0,
  light_minute: 0,
  destroy_male: 0,
  destroy_female: 0,
  destroy_male_reason:'',
  destroy_female_reason: '',
  cull_male_qty: 0,
  cull_female_qty: 0,
  cull_male_reason:'',
  cull_female_reason:'',
  sexing_error_male: 0,
  sexing_error_female: 0,
  egg_collection: 0,
  weight_male: 0,
  weight_female: 0,
  temp_inside: 0,
  temp_inside_std: 0,
  temp_outside: 0,
  temp_outside_std: 0,
  humidity_today: 0,
  humidity_std: 0,
  water_note: '',
  light_note: '',
  destroy_note: '',
  culling_note: '',
  serror_note: '',
  weight_note: '',
  temperature_note: '',
  humidity_note: '',
  medicine_id:0,
  medicine_qty:0,
  medicine_unit:0,
  medicine_dose:0,
  medicine_note:'',
  vaccine_schedule_detail_id:'',
  vaccine_id:'',
  vaccine_dose:'',
  vaccine_unit:0,
  vaccine_note:'',
  feeding_pro_male:0,
  feeding_pro_female:0,
  feeding_pro_note:'',
  finishtime_male:0,
  finishtime_female:0,
  finishtime_note:0,
  eggcollection_note:'',
  vaccine_file: [] as File[],

})

// Errors
const errors = ref<Record<string, string>>({})

// Shed & flock info (real data)
const shedQty = ref({ opening: 0, current: 0 })
const flockInfo = ref<{ age: string }>({ age: '0 weeks 0 days' })

// Modern dropdown states
const showFlockDropdown = ref(false)
const flockSearchQuery = ref('')

// Date picker overlay states
const showDateOverlay = ref(false)

// Feed dropdown states
const showFeedTypeDropdown = ref(false)
const feedTypeSearchQuery = ref('')
const showUnitDropdown = ref(false)
const unitSearchQuery = ref('')

// Selected vaccine schedule
const selectedVaccineSchedule = computed(() => {
  if (!form.vaccine_schedule_detail_id) return null
  return props.todayVaccineSchedules?.find(schedule => schedule.id == form.vaccine_schedule_detail_id)
})

// Filtered flock options
const filteredFlocks = computed(() => {
  if (!flockSearchQuery.value) return batchWithLabel.value
  return batchWithLabel.value.filter(flock => 
    flock.label?.toLowerCase().includes(flockSearchQuery.value.toLowerCase()) ||
    flock.batch_label?.toLowerCase().includes(flockSearchQuery.value.toLowerCase()) ||
    flock.display_label?.toLowerCase().includes(flockSearchQuery.value.toLowerCase())
  )
})

// Selected flock display
const selectedFlock = computed(() => {
  return batchWithLabel.value.find(flock => flock.id === Number(form.batchassign_id))
})

// Filtered feed types
const filteredFeedTypes = computed(() => {
  if (!feedTypeSearchQuery.value) return props.feeds || []
  return (props.feeds || []).filter(feed => 
    feed.feed_name.toLowerCase().includes(feedTypeSearchQuery.value.toLowerCase())
  )
})

// Filtered units
const filteredUnits = computed(() => {
  if (!unitSearchQuery.value) return props.units || []
  return (props.units || []).filter(unit => 
    unit.name.toLowerCase().includes(unitSearchQuery.value.toLowerCase())
  )
})

// Selected feed type display
const selectedFeedType = computed(() => {
  return (props.feeds || []).find(feed => feed.id === form.feed_type_id) || null
})

// Selected unit display
const selectedUnit = computed(() => {
  return (props.units || []).find(unit => unit.id === form.feed_unit) || null
})

// Close dropdown on outside click
const handleClickOutside = (e: MouseEvent) => {
  if (!(e.target as HTMLElement).closest('.flock-dropdown')) {
    showFlockDropdown.value = false
  }
  if (!(e.target as HTMLElement).closest('.date-overlay')) {
    showDateOverlay.value = false
  }
  if (!(e.target as HTMLElement).closest('.feed-type-dropdown')) {
    showFeedTypeDropdown.value = false
  }
  if (!(e.target as HTMLElement).closest('.unit-dropdown')) {
    showUnitDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

const counts = ref<Record<string, number | string>>({})

// Watch flock change (real data)
watch(() => form.batchassign_id, async (id) => {
  if (!id) {
    shedQty.value = { opening: 0, current: 0 }
    flockInfo.value.age = '0 weeks 0 days'
    counts.value = {}
    return
  }
  
  // Find the selected flock from the props
  const selectedFlock = batchWithLabel.value.find(flock => flock.id === Number(id))
  if (selectedFlock) {
    shedQty.value = { 
      opening: selectedFlock.total_birds || 0, 
      current: selectedFlock.current_birds || 0 
    }
    flockInfo.value.age = selectedFlock.age || '0 weeks 0 days'
    
    // Fetch real tab data from the API
    try {
      const response = await fetch(`/daily-operation/batch/${id}/data`)
      const data = await response.json()
      
      if (data.tabData) {
        counts.value = {
          daily_mortality: data.tabData.daily_mortality || 0,
          feed_consumption: data.tabData.feed_consumption || "0 Kg",
          water_consumption: data.tabData.water_consumption || "0 L",
          light_hour: data.tabData.light_hour || "0 H",
          destroy: data.tabData.destroy || 0,
          cull: data.tabData.cull || 0,
          sexing_error: data.tabData.sexing_error || 0,
          weight: data.tabData.weight || "0 gm",
          temperature: data.tabData.temperature || 0,
          humidity: data.tabData.humidity || 0,
          egg_collection: data.tabData.egg_collection || 0,
          medicine: data.tabData.medicine || 0,
          vaccine: data.tabData.vaccine || 0
        }
      } else {
        // Fallback to basic counts if no data
        counts.value = {
          daily_mortality: selectedFlock.batch_female_mortality + selectedFlock.batch_male_mortality,
          feed_consumption: "0 Kg",
          water_consumption: "0 L",
          light_hour: "0 H",
          destroy: 0,
          cull: 0,
          sexing_error: 0,
          weight: "0 gm",
          temperature: 0,
          humidity: 0,
          egg_collection: 0,
          medicine: 0,
          vaccine: 0
        }
      }
    } catch (error) {
      console.error('Error fetching batch data:', error)
      // Fallback to basic counts on error
      counts.value = {
        daily_mortality: selectedFlock.batch_female_mortality + selectedFlock.batch_male_mortality,
        feed_consumption: "0 Kg",
        water_consumption: "0 L",
        light_hour: "0 H",
        destroy: 0,
        cull: 0,
        sexing_error: 0,
        weight: "0 gm",
        temperature: 0,
        humidity: 0,
        egg_collection: 0,
        medicine: 0,
        vaccine: 0
      }
    }
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
  destroy: ['destroy_male'],
  cull: ['cull_male_qty'],
  sexing_error: ['sexing_error_male'],
  weight: ['weight_male'],
  temperature: ['temp_inside'],
  humidity: ['humidity_today'],
  medicine: ['medicine_id', 'medicine_qty', 'medicine_unit'],
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
        showInfo("Please provide a brief explanation for any fields that are intentionally left blank or not applicable.")
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

const completedTabs = ref<number[]>([])

// Navigation (guarded)
function nextTab() {
  const key = activeTab.value
  if (!validateTab(key)) return
  

  // mark current tab as completed
  if (!completedTabs.value.includes(activeTabIndex.value)) {
    completedTabs.value.push(activeTabIndex.value)
  }


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

// Handle vaccine schedule selection
function onVaccineScheduleChange() {
  const selectedScheduleId = form.vaccine_schedule_detail_id
  if (!selectedScheduleId) {
    // Clear vaccine fields if no schedule selected
    form.vaccine_id = ''
    form.vaccine_dose = ''
    form.vaccine_unit = ''
    return
  }

  // Find the selected schedule
  const selectedSchedule = props.todayVaccineSchedules?.find(schedule => schedule.id == selectedScheduleId)
  if (selectedSchedule) {
    // Auto-populate vaccine fields
    form.vaccine_id = selectedSchedule.vaccine_id
    form.vaccine_dose = '' // Let user enter dose
    form.vaccine_unit = '' // Let user select unit
    form.vaccine_note = selectedSchedule.notes || ''
  }
}
// Submit
function submit() {
  // Validate last active tab before submit (optional)
  if (!validateTab(activeTab.value)) return

  // Optionally, validate all tabs before post:
  // for (const t of tabs) { if (!validateTab(t.key)) { activeTabIndex.value = tabs.findIndex(x => x.key === t.key); return } }

  form.post(route('daily-operation.store'), {
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Daily Operation" />

    <!-- Back to List Button -->
    <div class="flex justify-end mt-2 px-6">
      <a 
        :href="props.stage ? `/daily-operation/stage/${props.stage}` : '/overview'" 
        class="inline-flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-gray-900 to-black hover:from-gray-800 hover:to-gray-900 text-white rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 text-sm font-medium"
        @click="console.log('Stage:', props.stage, 'Navigating to:', props.stage ? `/daily-operation/stage/${props.stage}` : '/overview')"
      >
        <ChevronLeft class="w-4 h-4" />
        <span>Back to List</span>
      </a>
    </div>

    <form @submit.prevent="submit" class="p-6 space-y-4">
      <!-- Flock Info -->
      <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200 shadow-sm overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-r from-gray-900 to-black px-3 py-2">
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
              <div class="w-6 h-6 bg-white/20 rounded flex items-center justify-center">
                <span class="text-white font-bold text-xs">🐔</span>
              </div>
              <div>
                <h2 class="text-lg font-bold text-white">Flock Information</h2>
                <p class="text-blue-100 text-xs">Select flock and record daily operations</p>
              </div>
            </div>
            <div class="text-right">
              <div class="text-white/80 text-xs">Progress</div>
              <div class="text-white font-bold text-xs">{{ currentStep }} / {{ totalTabs }}</div>
            </div>
          </div>
        </div>

        <!-- Content -->
        <div class="p-4 space-y-4">
        <!-- Progress Bar -->
          <div class="space-y-1">
            <div class="flex justify-between text-xs text-gray-600">
              <span>Overall Progress</span>
              <span>{{ Math.round(progress) }}% Complete</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
              <div
                class="h-full flex items-center justify-center text-white font-bold text-xs transition-all duration-500 rounded-full"
            :style="{ width: progress + '%', background: progressBarBackground }"
          >
              </div>
          </div>
        </div>

          <!-- Form Fields -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <!-- Flock Select -->
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 flex items-center">
                <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-1.5"></div>
                Select Flock
              </Label>
              <div class="flock-dropdown relative">
                <button
                  type="button"
                  @click.stop="showFlockDropdown = !showFlockDropdown"
                  class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 shadow-sm transition-all duration-200 hover:border-blue-500 hover:shadow-md focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 text-xs h-10"
                >
                  <span class="flex items-center gap-2">
                    <div class="h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                    {{ selectedFlock ? selectedFlock.display_label : 'Select Flock' }}
                  </span>
                  <ChevronDown class="h-3 w-3 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showFlockDropdown }" />
                </button>
                
                <!-- Flock Dropdown -->
                <div 
                  v-if="showFlockDropdown" 
                  class="fixed inset-0 z-[9999] flex items-start justify-center pt-20"
                  @click="showFlockDropdown = false"
                >
                  <div 
                    class="w-full max-w-md rounded-lg border border-gray-200 bg-white shadow-2xl"
                    @click.stop
                  >
                    <!-- Header -->
                    <div class="border-b border-gray-200 p-3">
                      <h3 class="font-semibold text-gray-900 text-sm">Select Flock</h3>
                      <div class="relative mt-2">
                        <Search class="absolute left-2 top-1/2 h-3 w-3 -translate-y-1/2 text-gray-400" />
                        <input
                          v-model="flockSearchQuery"
                          type="text"
                          placeholder="Search flocks..."
                          class="w-full rounded border border-gray-300 bg-gray-50 pl-7 pr-3 py-1.5 text-xs focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                          @click.stop
                        />
                      </div>
                    </div>

                    <!-- Flock List -->
                    <div class="max-h-80 overflow-y-auto">
                      <div v-if="(batchWithLabel?.length || 0) === 0" class="px-4 py-6 text-center">
                        <AlertCircle class="mx-auto h-6 w-6 text-red-500" />
                        <div class="mt-2 font-medium text-red-600 text-sm">No Flocks Available</div>
                        <div class="text-xs text-gray-500">Please create flocks first</div>
                      </div>
                      <button
                        v-for="flock in filteredFlocks"
                        :key="flock.id"
                        type="button"
                        @click.stop="form.batchassign_id = flock.id; showFlockDropdown = false"
                        class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-blue-50 transition-colors duration-200 border-b border-gray-100 last:border-b-0"
                        :class="{ 'bg-blue-100': form.batchassign_id == flock.id }"
                      >
                        <div class="h-2 w-2 rounded-full bg-blue-500 flex-shrink-0"></div>
                        <div class="flex-1">
                          <div class="font-semibold text-gray-900 text-sm">{{ flock.label }}</div>
                          <div class="text-xs text-gray-500">Batch: {{ flock.batch_label }}</div>
                        </div>
                        <CheckCircle2 v-if="form.batchassign_id == flock.id" class="h-3 w-3 text-blue-500 flex-shrink-0" />
                      </button>
                      <div v-if="filteredFlocks.length === 0 && (batchWithLabel?.length || 0) > 0" class="px-4 py-6 text-center text-gray-500">
                        <Search class="mx-auto h-5 w-5 text-gray-400" />
                        <div class="mt-2 text-xs">No results found for "{{ flockSearchQuery }}"</div>
                      </div>
                    </div>

                    <!-- Close Button -->
                    <div class="border-t border-gray-200 p-3">
                      <Button 
                        type="button"
                        @click="showFlockDropdown = false"
                        class="w-full rounded bg-gray-100 text-gray-700 hover:bg-gray-200 text-xs py-2"
                      >
                        Close
                      </Button>
                    </div>
                  </div>
                </div>
              </div>
          </div>

          <!-- Date Picker -->
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 flex items-center">
                <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></div>
                Operation Date
              </Label>
              <div class="date-overlay relative">
                <button
                  type="button"
                  @click.stop="showDateOverlay = !showDateOverlay"
                  class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 shadow-sm transition-all duration-200 hover:border-green-500 hover:shadow-md focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500/20 text-xs h-10"
                >
                  <span class="flex items-center gap-2">
                    <div class="h-1.5 w-1.5 rounded-full bg-green-500"></div>
                    {{ form.operation_date ? new Date(form.operation_date).toLocaleDateString() : 'Select Date' }}
                  </span>
                  <Calendar class="h-3 w-3 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showDateOverlay }" />
                </button>
                
                <!-- Date Overlay -->
                <div 
                  v-if="showDateOverlay" 
                  class="fixed inset-0 z-[9999] flex items-start justify-center pt-20"
                  @click="showDateOverlay = false"
                >
                  <div 
                    class="w-full max-w-sm rounded-lg border border-gray-200 bg-white shadow-2xl"
                    @click.stop
                  >
                    <!-- Header -->
                    <div class="border-b border-gray-200 p-3">
                      <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900 text-sm">Select Operation Date</h3>
                        <button
                          type="button"
                          @click="showDateOverlay = false"
                          class="p-1 hover:bg-gray-100 rounded transition-colors duration-200"
                        >
                          <X class="h-4 w-4 text-gray-400" />
                        </button>
                      </div>
                    </div>

                    <!-- Date Picker -->
                    <div class="p-4">
            <Datepicker
              v-model="form.operation_date"
              format="yyyy-MM-dd"
                        :input-class="'hidden'"
              placeholder="Select Date"
              :auto-apply="true"
                        @update:model-value="showDateOverlay = false"
                        inline
            />
          </div>

                    <!-- Close Button -->
                    <div class="border-t border-gray-200 p-3">
                      <Button 
                        type="button"
                        @click="showDateOverlay = false"
                        class="w-full rounded bg-gray-100 text-gray-700 hover:bg-gray-200 text-xs py-2"
                      >
                        Close
                      </Button>
              </div>
              </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Shed Info Cards -->
          <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-blue-50 px-2 py-1.5 border-b border-gray-200">
              <h3 class="text-xs font-semibold text-gray-700 flex items-center">
                <div class="w-1 h-1 bg-purple-500 rounded-full mr-1.5"></div>
                Flock Statistics
              </h3>
              </div>
            <div class="grid grid-cols-3 gap-0">
              <div class="p-2 text-center border-r border-gray-200 last:border-r-0">
                <div class="w-6 h-6 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center mx-auto mb-1">
                  <span class="text-white text-xs">📊</span>
              </div>
                <p class="text-gray-600 font-medium text-xs mb-0.5">Total</p>
                <p class="text-xs font-bold text-gray-900">{{ shedQty.opening.toLocaleString() }}</p>
              </div>
              <div class="p-2 text-center border-r border-gray-200 last:border-r-0">
                <div class="w-6 h-6 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center mx-auto mb-1">
                  <span class="text-white text-xs">🐣</span>
            </div>
                <p class="text-gray-600 font-medium text-xs mb-0.5">Current</p>
                <p class="text-xs font-bold text-gray-900">{{ shedQty.current.toLocaleString() }}</p>
          </div>
              <div class="p-2 text-center">
                <div class="w-6 h-6 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-full flex items-center justify-center mx-auto mb-1">
                  <span class="text-white text-xs">⏰</span>
                </div>
                <p class="text-gray-600 font-medium text-xs mb-0.5">Age</p>
                <p class="text-xs font-bold text-gray-900">{{ flockInfo.age }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Two Column Layout -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Left Column - Tab Cards -->
        <div class="space-y-4">
          <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
            <h3 class="text-lg font-bold text-gray-800 mb-1 flex items-center">
              <div class="w-6 h-6 bg-blue-600 rounded flex items-center justify-center mr-2">
                <span class="text-white font-bold text-xs">📊</span>
              </div>
              Operation Categories
            </h3>
            <p class="text-xs text-gray-600">Select a category to record daily operations</p>
          </div>
          
          <div class="grid grid-cols-2 lg:grid-cols-5 gap-2">
        <div
          v-for="(tab, index) in tabs"
          :key="tab.key"
          @click="goToTab(index)"
              class="group cursor-pointer p-2 border-2 rounded-lg transition-all duration-300 hover:shadow-md hover:scale-[1.01] text-center"
            :class="[
              activeTabIndex === index 
                    ? 'bg-gradient-to-r from-orange-500 to-red-500 text-white border-orange-500 shadow-md scale-[1.01]'
                : completedTabs.includes(index) 
                      ? 'bg-gradient-to-r from-green-500 to-emerald-500 text-white border-green-500 shadow-sm'
                      : 'bg-white text-gray-700 border-gray-200 hover:border-gray-300'
                ]"
            >
              <!-- Number badge -->
              <div class="w-6 h-6 rounded flex items-center justify-center text-xs font-bold mx-auto mb-1.5"
                :class="[
                  activeTabIndex === index 
                    ? 'bg-white/20 text-white'
                    : completedTabs.includes(index) 
                      ? 'bg-white/20 text-white'
                      : 'bg-gray-100 text-gray-600'
                ]"
              >
                {{ index + 1 }}
              </div>
              
              <!-- Tab label -->
              <div class="font-semibold text-xs mb-1 leading-tight">{{ tab.label }}</div>
              
              <!-- Status -->
              <div class="text-xs opacity-75 mb-1">
                {{ completedTabs.includes(index) ? '✓' : activeTabIndex === index ? '●' : '○' }}
              </div>
              
              <!-- Count value -->
              <div v-if="counts[tab.key] !== undefined" class="text-xs font-bold">
            {{ counts[tab.key] }}
              </div>
              <div v-else class="text-xs opacity-75">
                ---
              </div>
              
              <!-- Progress indicator -->
              <div class="mt-1.5 w-full bg-black/10 rounded-full h-0.5">
                <div 
                  class="h-0.5 rounded-full transition-all duration-300"
                  :class="[
                    activeTabIndex === index 
                      ? 'bg-white w-full'
                      : completedTabs.includes(index) 
                        ? 'bg-white w-full'
                        : 'bg-gray-300 w-0'
                  ]"
                ></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column - Input Forms -->
        <div class="space-y-4">
          <!-- Tab Content Header -->
          <div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-2">
                <div class="w-6 h-6 bg-blue-600 rounded flex items-center justify-center">
                  <span class="text-white font-bold text-xs">{{ activeTabIndex + 1 }}</span>
                </div>
                <div>
                  <h3 class="text-lg font-bold text-gray-800">{{ tabs[activeTabIndex]?.label }}</h3>
                  <p class="text-xs text-gray-600">Fill in the details below</p>
                </div>
              </div>
              <div class="text-right">
                <div class="text-xs text-gray-500">Step {{ currentStep }} of {{ totalTabs }}</div>
                <div class="w-20 bg-gray-200 rounded-full h-1.5 mt-1">
                  <div 
                    class="bg-blue-500 h-1.5 rounded-full transition-all duration-300"
                    :style="{ width: (currentStep / totalTabs) * 100 + '%' }"
                  ></div>
                </div>
              </div>
        </div>
      </div>

      <!-- Tab Content -->
          <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <!-- Mortality -->
        <div v-if="activeTab === 'daily_mortality'" class="p-4">
          <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-pink-500 rounded-full mr-2"></div>
                  Female Chick Mortality
                </Label>
                <Input 
                  v-model.number="form.female_mortality" 
                  type="number" 
                  placeholder="Enter quantity..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                />
            </div>
              <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div>
                  Male Chick Mortality
                </Label>
                <Input 
                  v-model.number="form.male_mortality" 
                  type="number" 
                  placeholder="Enter quantity..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
            </div>
              <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700">Female Reason</Label>
                <Input 
                  v-model="form.female_reason" 
                  type="text" 
                  placeholder="Enter reason..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                />
            </div>
              <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700">Male Reason</Label>
                <Input 
                  v-model="form.male_reason" 
                  type="text" 
                  placeholder="Enter reason..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
            </div>
            </div>
            
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 flex items-center">
                <span class="text-red-500 mr-1">*</span>
                Additional Notes
              </Label>
              <textarea
                v-model="form.mortalitynote"
                placeholder="Add any additional notes about mortality..."
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 resize-none"
                :class="errors.mortalitynote ? 'border-red-500 ring-2 ring-red-200' : ''"
              ></textarea>
              <p v-if="errors.mortalitynote" class="text-red-600 text-xs mt-1 flex items-center">
                <AlertTriangle class="w-4 h-4 mr-1" />
                {{ errors.mortalitynote }}
              </p>
            </div>
          </div>
        </div>

        <!-- Feed Consumption -->
        <div v-if="activeTab === 'feed_consumption'" class="p-4">
          <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-2"></div>
                  Feed Type
                </Label>
                <div class="feed-type-dropdown relative">
                  <button
                    type="button"
                    @click.stop="showFeedTypeDropdown = !showFeedTypeDropdown"
                    class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 shadow-sm transition-all duration-200 hover:border-green-500 hover:shadow-md focus:border-green-500 focus:outline-none focus:ring-2 focus:ring-green-500/20 text-xs h-10"
                    :class="errors.feed_type_id ? 'border-red-500 ring-2 ring-red-200' : ''"
                  >
                    <span class="flex items-center gap-2">
                      <div class="h-1.5 w-1.5 rounded-full bg-green-500"></div>
                      {{ selectedFeedType ? selectedFeedType.feed_name : 'Select Feed Type' }}
                    </span>
                    <ChevronDown class="h-3 w-3 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showFeedTypeDropdown }" />
                  </button>
                  
                  <!-- Feed Type Dropdown -->
                  <div 
                    v-if="showFeedTypeDropdown" 
                    class="fixed inset-0 z-[9999] flex items-start justify-center pt-20"
                    @click="showFeedTypeDropdown = false"
                  >
                    <div 
                      class="w-full max-w-md rounded-lg border border-gray-200 bg-white shadow-2xl"
                      @click.stop
                    >
                      <!-- Header -->
                      <div class="flex items-center justify-between p-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-900 text-sm">Select Feed Type</h3>
                        <button
                          type="button"
                          @click="showFeedTypeDropdown = false"
                          class="p-1 hover:bg-gray-100 rounded transition-colors duration-200"
                        >
                          <X class="h-4 w-4 text-gray-400" />
                        </button>
            </div>
                      
                      <!-- Search -->
                      <div class="p-4 border-b border-gray-200">
                        <div class="relative">
                          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                          <input
                            v-model="feedTypeSearchQuery"
                            type="text"
                            placeholder="Search feed types..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm"
                          />
                        </div>
                      </div>
                      
                      <!-- Options -->
                      <div class="max-h-60 overflow-y-auto">
                        <button
                          v-for="feed in filteredFeedTypes"
                          :key="feed.id"
                          type="button"
                          @click.stop="form.feed_type_id = feed.id; showFeedTypeDropdown = false"
                          class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-green-50 transition-colors duration-200 border-b border-gray-100 last:border-b-0"
                          :class="{ 'bg-green-100': form.feed_type_id == feed.id }"
                        >
                          <div class="flex-shrink-0">
                            <div class="w-2 h-2 rounded-full bg-green-500"></div>
                          </div>
                          <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900">{{ feed.feed_name }}</div>
                          </div>
                          <div v-if="form.feed_type_id == feed.id" class="flex-shrink-0">
                            <CheckCircle2 class="h-4 w-4 text-green-600" />
                          </div>
                        </button>
                      </div>
                      
                      <!-- Footer -->
                      <div class="border-t border-gray-200 p-3">
                        <Button 
                          type="button"
                          @click="showFeedTypeDropdown = false"
                          class="w-full rounded bg-gray-100 text-gray-700 hover:bg-gray-200 text-xs py-2"
                        >
                          Close
                        </Button>
                      </div>
                    </div>
                  </div>
                </div>
                <p v-if="errors.feed_type_id" class="text-red-600 text-xs mt-1 flex items-center">
                  <AlertTriangle class="w-4 h-4 mr-1" />
                  {{ errors.feed_type_id }}
                </p>
            </div>
              <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-orange-500 rounded-full mr-2"></div>
                  Quantity
                </Label>
              <Input
                v-model.number="form.feed_quantity"
                type="number"
                  placeholder="Enter quantity..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                  :class="errors.feed_quantity ? 'border-red-500 ring-2 ring-red-200' : ''"
              />
                <p v-if="errors.feed_quantity" class="text-red-600 text-xs mt-1 flex items-center">
                  <AlertTriangle class="w-4 h-4 mr-1" />
                  {{ errors.feed_quantity }}
                </p>
            </div>
              <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-purple-500 rounded-full mr-2"></div>
                  Unit
                </Label>
                <div class="unit-dropdown relative">
                  <button
                    type="button"
                    @click.stop="showUnitDropdown = !showUnitDropdown"
                    class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 shadow-sm transition-all duration-200 hover:border-purple-500 hover:shadow-md focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 text-xs h-10"
                  >
                    <span class="flex items-center gap-2">
                      <div class="h-1.5 w-1.5 rounded-full bg-purple-500"></div>
                      {{ selectedUnit ? selectedUnit.name : 'Select Unit' }}
                    </span>
                    <ChevronDown class="h-3 w-3 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showUnitDropdown }" />
                  </button>
                  
                  <!-- Unit Dropdown -->
                  <div 
                    v-if="showUnitDropdown" 
                    class="fixed inset-0 z-[9999] flex items-start justify-center pt-20"
                    @click="showUnitDropdown = false"
                  >
                    <div 
                      class="w-full max-w-md rounded-lg border border-gray-200 bg-white shadow-2xl"
                      @click.stop
                    >
                      <!-- Header -->
                      <div class="flex items-center justify-between p-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-900 text-sm">Select Unit</h3>
                        <button
                          type="button"
                          @click="showUnitDropdown = false"
                          class="p-1 hover:bg-gray-100 rounded transition-colors duration-200"
                        >
                          <X class="h-4 w-4 text-gray-400" />
                        </button>
            </div>
                      
                      <!-- Search -->
                      <div class="p-4 border-b border-gray-200">
                        <div class="relative">
                          <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                          <input
                            v-model="unitSearchQuery"
                            type="text"
                            placeholder="Search units..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm"
                          />
                        </div>
                      </div>
                      
                      <!-- Options -->
                      <div class="max-h-60 overflow-y-auto">
                        <button
                          v-for="unit in filteredUnits"
                          :key="unit.id"
                          type="button"
                          @click.stop="form.feed_unit = unit.id; showUnitDropdown = false"
                          class="flex w-full items-center gap-3 px-4 py-3 text-left hover:bg-purple-50 transition-colors duration-200 border-b border-gray-100 last:border-b-0"
                          :class="{ 'bg-purple-100': form.feed_unit == unit.id }"
                        >
                          <div class="flex-shrink-0">
                            <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                          </div>
                          <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-gray-900">{{ unit.name }}</div>
                          </div>
                          <div v-if="form.feed_unit == unit.id" class="flex-shrink-0">
                            <CheckCircle2 class="h-4 w-4 text-purple-600" />
                          </div>
                        </button>
                      </div>
                      
                      <!-- Footer -->
                      <div class="border-t border-gray-200 p-3">
                        <Button 
                          type="button"
                          @click="showUnitDropdown = false"
                          class="w-full rounded bg-gray-100 text-gray-700 hover:bg-gray-200 text-xs py-2"
                        >
                          Close
                        </Button>
                      </div>
                    </div>
                  </div>
                </div>
            </div>
            </div>
            
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 flex items-center">
                <span class="text-red-500 mr-1">*</span>
                Additional Notes
              </Label>
              <textarea
                v-model="form.feed_note"
                placeholder="Add any additional notes about feed consumption..."
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 resize-none"
                :class="errors.feed_note ? 'border-red-500 ring-2 ring-red-200' : ''"
              ></textarea>
              <p v-if="errors.feed_note" class="text-red-600 text-xs mt-1 flex items-center">
                <AlertTriangle class="w-4 h-4 mr-1" />
                {{ errors.feed_note }}
              </p>
            </div>
          </div>
        </div>

        
        <!-- Water Consumption -->
        <div v-if="activeTab === 'water_consumption'" class="p-4">
          <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div>
                  Water Type
                </Label>
                <select 
                  v-model="form.water_type" 
                  class="w-full h-10 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white"
                >
                  <option value="">Select Water Type</option>
                  <option v-for="water in props.waters" :key="water.id" :value="water.id">
                  {{ water.name }}
                  </option>
              </select>
            </div>
              <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-cyan-500 rounded-full mr-2"></div>
                  Quantity
                </Label>
                <Input 
                  v-model.number="form.water_quantity" 
                  type="number" 
                  placeholder="Enter quantity..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-cyan-500 focus:border-transparent"
                />
            </div>
            </div>
            
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 flex items-center">
                <span class="text-red-500 mr-1">*</span>
                Additional Notes
              </Label>
                <textarea
                  v-model="form.water_note"
                placeholder="Add any additional notes about water consumption..."
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 resize-none"
                :class="errors.water_note ? 'border-red-500 ring-2 ring-red-200' : ''"
                ></textarea>
              <p v-if="errors.water_note" class="text-red-600 text-xs mt-1 flex items-center">
                <AlertTriangle class="w-4 h-4 mr-1" />
                {{ errors.water_note }}
              </p>
            </div>
            </div>
        </div>

      <!-- Light Hour -->
        <div v-if="activeTab === 'light_hour'" class="p-4">
          <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-yellow-500 rounded-full mr-2"></div>
                  Hours
                </Label>
                <Input 
                  v-model.number="form.light_hour" 
                  type="number" 
                  placeholder="Enter hours..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                />
            </div>
              <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-amber-500 rounded-full mr-2"></div>
                  Minutes
                </Label>
                <Input 
                  v-model.number="form.light_minute" 
                  type="number" 
                  placeholder="Enter minutes..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                />
            </div>
            </div>
            
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 flex items-center">
                <span class="text-red-500 mr-1">*</span>
                Additional Notes
              </Label>
                <textarea
                v-model="form.light_note"
                placeholder="Add any additional notes about light hours..."
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent transition-all duration-200 resize-none"
                :class="errors.light_note ? 'border-red-500 ring-2 ring-red-200' : ''"
              ></textarea>
              <p v-if="errors.light_note" class="text-red-600 text-xs mt-1 flex items-center">
                <AlertTriangle class="w-4 h-4 mr-1" />
                {{ errors.light_note }}
              </p>
            </div>
            </div>
      </div>


            <!-- Destroy -->
        <div v-if="activeTab === 'destroy'" class="p-4">
          <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-pink-500 rounded-full mr-2"></div>
                  Destroy Female Qty
                </Label>
                  <Input 
                    v-model.number="form.destroy_female" 
                    type="number" 
                    min="0" 
                    placeholder="Enter quantity..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                  />
                </div>
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div>
                  Destroy Male Qty
                </Label>
                  <Input 
                    v-model.number="form.destroy_male" 
                    type="number" 
                    min="0" 
                    placeholder="Enter quantity..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
                <div class="space-y-1">
                  <Label class="text-xs font-semibold text-gray-700">Destroy Female Reason</Label>
                  <Input 
                    v-model="form.destroy_female_reason" 
                    type="text" 
                    placeholder="Enter reason..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                  />
                </div>
                <div class="space-y-1">
                  <Label class="text-xs font-semibold text-gray-700">Destroy Male Reason</Label>
                  <Input 
                    v-model="form.destroy_male_reason" 
                    type="text" 
                    placeholder="Enter reason..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
            </div>
            
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 flex items-center">
                <span class="text-red-500 mr-1">*</span>
                Additional Notes
              </Label>
                  <textarea
                    v-model="form.destroy_note"
                    placeholder="Add any additional notes about destroyed chicks..."
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 resize-none"
                    :class="errors.destroy_note ? 'border-red-500 ring-2 ring-red-200' : ''"
                  ></textarea>
                  <p v-if="errors.destroy_note" class="text-red-600 text-xs mt-1 flex items-center">
                    <AlertTriangle class="w-4 h-4 mr-1" />
                    {{ errors.destroy_note }}
                  </p>
                </div>
              </div>
            </div>


            <!-- Cull -->
        <div v-if="activeTab === 'cull'" class="p-4">
          <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-pink-500 rounded-full mr-2"></div>
                  Cull Female Qty
                </Label>
                  <Input 
                    v-model.number="form.cull_female_qty" 
                    type="number" 
                    min="0" 
                    placeholder="Enter quantity..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                  />
                </div>
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div>
                  Cull Male Qty
                </Label>
                  <Input 
                    v-model.number="form.cull_male_qty" 
                    type="number" 
                    min="0" 
                    placeholder="Enter quantity..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
                <div class="space-y-1">
                  <Label class="text-xs font-semibold text-gray-700">Cull Female Reason</Label>
                  <Input 
                    v-model="form.cull_female_reason" 
                    type="text" 
                    placeholder="Enter reason..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                  />
                </div>
                <div class="space-y-1">
                  <Label class="text-xs font-semibold text-gray-700">Cull Male Reason</Label>
                  <Input 
                    v-model="form.cull_male_reason" 
                    type="text" 
                    placeholder="Enter reason..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
            </div>
            
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 flex items-center">
                <span class="text-red-500 mr-1">*</span>
                Additional Notes
              </Label>
                  <textarea
                    v-model="form.culling_note"
                    placeholder="Add any additional notes about culled chicks..."
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200 resize-none"
                    :class="errors.culling_note ? 'border-red-500 ring-2 ring-red-200' : ''"
                  ></textarea>
                  <p v-if="errors.culling_note" class="text-red-600 text-xs mt-1 flex items-center">
                    <AlertTriangle class="w-4 h-4 mr-1" />
                    {{ errors.culling_note }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Sexing Error -->
        <div v-if="activeTab === 'sexing_error'" class="p-4">
          <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-pink-500 rounded-full mr-2"></div>
                  Sexing Error Female Qty
                </Label>
                  <Input 
                    v-model.number="form.sexing_error_female" 
                    type="number" 
                    min="0" 
                    placeholder="Enter quantity..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                  />
                </div>
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div>
                  Sexing Error Male Qty
                </Label>
                  <Input 
                    v-model.number="form.sexing_error_male" 
                    type="number" 
                    min="0" 
                    placeholder="Enter quantity..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
            </div>
            
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 flex items-center">
                <span class="text-red-500 mr-1">*</span>
                Additional Notes
              </Label>
                  <textarea
                    v-model="form.serror_note"
                    placeholder="Add any additional notes about sexing errors..."
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200 resize-none"
                    :class="errors.serror_note ? 'border-red-500 ring-2 ring-red-200' : ''"
                  ></textarea>
                  <p v-if="errors.serror_note" class="text-red-600 text-xs mt-1 flex items-center">
                    <AlertTriangle class="w-4 h-4 mr-1" />
                    {{ errors.serror_note }}
                  </p>
                </div>
              </div>
            </div>


            <!-- Weight -->
        <div v-if="activeTab === 'weight'" class="p-4">
          <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div>
                  Weight Male (g)
                </Label>
                  <Input 
                    v-model.number="form.weight_male" 
                    type="number" 
                    min="0" 
                    step="0.1"
                    placeholder="Enter weight..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-pink-500 rounded-full mr-2"></div>
                  Weight Female (g)
                </Label>
                  <Input 
                    v-model.number="form.weight_female" 
                    type="number" 
                    min="0" 
                    step="0.1"
                    placeholder="Enter weight..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                  />
                </div>
            </div>
            
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 flex items-center">
                <span class="text-red-500 mr-1">*</span>
                Additional Notes
              </Label>
                  <textarea
                    v-model="form.weight_note"
                    placeholder="Add any additional notes about weight measurements..."
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent transition-all duration-200 resize-none"
                    :class="errors.weight_note ? 'border-red-500 ring-2 ring-red-200' : ''"
                  ></textarea>
                  <p v-if="errors.weight_note" class="text-red-600 text-xs mt-1 flex items-center">
                    <AlertTriangle class="w-4 h-4 mr-1" />
                    {{ errors.weight_note }}
                  </p>
                </div>
              </div>
            </div>


            <!-- Temperature -->
        <div v-if="activeTab === 'temperature'" class="p-4">
          <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-red-500 rounded-full mr-2"></div>
                  Inside Temperature (°C)
                </Label>
                  <Input 
                    v-model.number="form.temp_inside" 
                    type="number" 
                    step="0.1" 
                    placeholder="Enter temperature..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                  />
                </div>
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-orange-500 rounded-full mr-2"></div>
                  Std Inside Temperature (°C)
                </Label>
                  <Input 
                    v-model.number="form.temp_inside_std" 
                    type="number" 
                    step="0.1" 
                    placeholder="Enter standard temp..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                  />
                </div>
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div>
                  Outside Temperature (°C)
                </Label>
                  <Input 
                    v-model.number="form.temp_outside" 
                    type="number" 
                    step="0.1" 
                    placeholder="Enter temperature..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-purple-500 rounded-full mr-2"></div>
                  Std Outside Temperature (°C)
                </Label>
                  <Input 
                    v-model.number="form.temp_outside_std" 
                    type="number" 
                    step="0.1" 
                    placeholder="Enter standard temp..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                  />
                </div>
            </div>
            
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 flex items-center">
                <span class="text-red-500 mr-1">*</span>
                Additional Notes
              </Label>
                  <textarea
                    v-model="form.temperature_note"
                    placeholder="Add any additional notes about temperature readings..."
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all duration-200 resize-none"
                    :class="errors.temperature_note ? 'border-red-500 ring-2 ring-red-200' : ''"
                  ></textarea>
                  <p v-if="errors.temperature_note" class="text-red-600 text-xs mt-1 flex items-center">
                    <AlertTriangle class="w-4 h-4 mr-1" />
                    {{ errors.temperature_note }}
                  </p>
                </div>
              </div>
            </div>





        <!-- Feeding Program -->
        <div v-if="activeTab === 'feedingprogram'" class="p-4">
          <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-pink-500 rounded-full mr-2"></div>
                  Feeding Program Female
                </Label>
                <Input 
                  v-model.number="form.feeding_pro_female" 
                  type="number" 
                  min="0" 
                  placeholder="Enter quantity..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                />
                </div>
              <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div>
                  Feeding Program Male
                </Label>
                <Input 
                  v-model.number="form.feeding_pro_male" 
                  type="number" 
                  min="0" 
                  placeholder="Enter quantity..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                </div>
                </div>

            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 flex items-center">
                <span class="text-red-500 mr-1">*</span>
                Additional Notes
              </Label>
                <textarea
                v-model="form.feeding_pro_note"
                placeholder="Add any additional notes about feeding program..."
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 resize-none"
                :class="errors.feeding_pro_note ? 'border-red-500 ring-2 ring-red-200' : ''"
              ></textarea>
              <p v-if="errors.feeding_pro_note" class="text-red-600 text-xs mt-1 flex items-center">
                <AlertTriangle class="w-4 h-4 mr-1" />
                {{ errors.feeding_pro_note }}
              </p>
          </div>
          </div>
        </div>



        <!-- Feed Finishing Time -->
        <div v-if="activeTab === 'feedFinishingtime'" class="p-4">
          <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-pink-500 rounded-full mr-2"></div>
                  Finishing Time Female
                </Label>
                <Input 
                  v-model.number="form.finishtime_female" 
                  type="number" 
                  min="0" 
                  placeholder="Enter time..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-pink-500 focus:border-transparent"
                />
                </div>
              <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div>
                  Finishing Time Male
                </Label>
                <Input 
                  v-model.number="form.finishtime_male" 
                  type="number" 
                  min="0" 
                  placeholder="Enter time..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
                </div>
                </div>

            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 flex items-center">
                <span class="text-red-500 mr-1">*</span>
                Additional Notes
              </Label>
                <textarea
                v-model="form.finishtime_note"
                placeholder="Add any additional notes about finishing time..."
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all duration-200 resize-none"
                :class="errors.finishtime_note ? 'border-red-500 ring-2 ring-red-200' : ''"
              ></textarea>
              <p v-if="errors.finishtime_note" class="text-red-600 text-xs mt-1 flex items-center">
                <AlertTriangle class="w-4 h-4 mr-1" />
                {{ errors.finishtime_note }}
              </p>
          </div>
                </div>
              </div>

        <!-- Humidity -->
        <div v-if="activeTab === 'humidity'" class="p-4">
          <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-sky-500 rounded-full mr-2"></div>
                  Today Humidity (%)
                </Label>
                  <Input 
                    v-model.number="form.humidity_today" 
                    type="number" 
                    step="0.1" 
                    placeholder="Enter humidity..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-sky-500 focus:border-transparent"
                  />
                </div>
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div>
                  Std Humidity (%)
                </Label>
                  <Input 
                    v-model.number="form.humidity_std" 
                    type="number" 
                    step="0.1" 
                    placeholder="Enter standard humidity..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
            </div>
            
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 flex items-center">
                <span class="text-red-500 mr-1">*</span>
                Additional Notes
              </Label>
                  <textarea
                    v-model="form.humidity_note"
                    placeholder="Add any additional notes about humidity readings..."
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-transparent transition-all duration-200 resize-none"
                    :class="errors.humidity_note ? 'border-red-500 ring-2 ring-red-200' : ''"
                  ></textarea>
                  <p v-if="errors.humidity_note" class="text-red-600 text-xs mt-1 flex items-center">
                    <AlertTriangle class="w-4 h-4 mr-1" />
                    {{ errors.humidity_note }}
                  </p>
                </div>
              </div>
            </div>


            <!-- Egg Collection -->
        <div v-if="activeTab === 'egg_collection'" class="p-4">
          <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-amber-500 rounded-full mr-2"></div>
                  Egg Quantity
                </Label>
                  <Input 
                    v-model.number="form.egg_collection" 
                    type="number" 
                    step="0.1" 
                    placeholder="Enter quantity..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-amber-500 focus:border-transparent"
                  />
                </div>
            </div>
            
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 flex items-center">
                <span class="text-red-500 mr-1">*</span>
                Additional Notes
              </Label>
                  <textarea 
                    v-model="form.eggcollection_note" 
                    placeholder="Add any additional notes about egg collection..."
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-transparent transition-all duration-200 resize-none"
                  ></textarea>
                </div>
              </div>
            </div>

            <!-- Medicine -->
        <div v-if="activeTab === 'medicine'" class="p-4">
          <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-purple-500 rounded-full mr-2"></div>
                  Medicine
                </Label>
                  <select 
                    v-model="form.medicine_id" 
                    class="w-full h-10 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 bg-white"
                  >
                    <option value="">Select medicine...</option>
                    <option v-for="medicine in props.medicines" :key="medicine.id" :value="medicine.id">
                      {{ medicine.name }}
                    </option>
                  </select>
                </div>
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-2"></div>
                  Quantity
                </Label>
                  <Input 
                    v-model.number="form.medicine_qty" 
                    type="number" 
                    min="0" 
                    placeholder="Enter quantity..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  />
                </div>
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div>
                  Unit
                </Label>
                  <select 
                    v-model="form.medicine_unit" 
                  class="w-full h-10 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white"
                  >
                    <option value="">Select unit...</option>
                    <option v-for="unit in units" :key="unit.id" :value="unit.id">
                      {{ unit.name }}
                    </option>
                  </select>
                </div>
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-orange-500 rounded-full mr-2"></div>
                  Dose
                </Label>
                  <Input 
                    v-model="form.medicine_dose" 
                    type="text" 
                    placeholder="Enter dose..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                  />
                </div>
            </div>
            
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 flex items-center">
                <span class="text-red-500 mr-1">*</span>
                Additional Notes
              </Label>
                  <textarea 
                    v-model="form.medicine_note" 
                    placeholder="Add any additional notes about medicine administration..."
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 resize-none"
                  ></textarea>
                </div>
              </div>
            </div>

            <!-- Vaccine -->
        <div v-if="activeTab === 'vaccine'" class="p-4">
          <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-indigo-500 rounded-full mr-2"></div>
                  Today's Vaccine Schedule
                </Label>
                  <select 
                    v-model="form.vaccine_schedule_detail_id" 
                    @change="onVaccineScheduleChange"
                    class="w-full h-10 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 bg-white"
                  >
                    <option value="">Select today's vaccine schedule...</option>
                    <option v-for="schedule in props.todayVaccineSchedules" :key="schedule.id" :value="schedule.id">
                      {{ schedule.display_name }} - {{ schedule.flock_name }} ({{ schedule.shed_name }})
                    </option>
                  </select>
                </div>
                
                <!-- Selected Schedule Info -->
                <div v-if="form.vaccine_schedule_detail_id" class="col-span-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                  <div class="text-sm text-blue-800">
                    <div class="font-semibold mb-1">Selected Schedule Details:</div>
                    <div v-if="selectedVaccineSchedule" class="space-y-1">
                      <div><span class="font-medium">Vaccine:</span> {{ selectedVaccineSchedule.vaccine_name }}</div>
                      <div><span class="font-medium">Disease:</span> {{ selectedVaccineSchedule.disease_name }}</div>
                      <div><span class="font-medium">Age:</span> {{ selectedVaccineSchedule.age }}</div>
                      <div><span class="font-medium">Flock:</span> {{ selectedVaccineSchedule.flock_name }}</div>
                      <div><span class="font-medium">Shed:</span> {{ selectedVaccineSchedule.shed_name }}</div>
                      <div v-if="selectedVaccineSchedule.notes"><span class="font-medium">Notes:</span> {{ selectedVaccineSchedule.notes }}</div>
                    </div>
                  </div>
                </div>
                
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-green-500 rounded-full mr-2"></div>
                  Dose
                </Label>
                  <Input 
                    v-model.number="form.vaccine_dose" 
                    type="number" 
                    min="0" 
                    placeholder="Enter dose..."
                  class="h-10 border-gray-300 focus:ring-2 focus:ring-green-500 focus:border-transparent"
                  />
                </div>
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-2"></div>
                  Unit
                </Label>
                  <select 
                    v-model="form.vaccine_unit" 
                  class="w-full h-10 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 bg-white"
                  >
                    <option value="">Select unit...</option>
                    <option v-for="unit in units" :key="unit.id" :value="unit.id">
                      {{ unit.name }}
                    </option>
                  </select>
                </div>
                <div class="space-y-1">
                <Label class="text-xs font-semibold text-gray-700 flex items-center">
                  <div class="w-1.5 h-1.5 bg-purple-500 rounded-full mr-2"></div>
                  Upload File
                </Label>
                  <input 
                    type="file" 
                  @change="(event) => { const target = event.target as HTMLInputElement; form.vaccine_file = target.files?.[0] ? [target.files[0]] : [] }" 
                  class="w-full h-10 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 bg-white file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100"
                  />
                </div>
            </div>
            
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 flex items-center">
                <span class="text-red-500 mr-1">*</span>
                Additional Notes
              </Label>
                  <textarea 
                    v-model="form.vaccine_note" 
                    placeholder="Add any additional notes about vaccine administration..."
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all duration-200 resize-none"
                  ></textarea>
                </div>
              </div>
            </div>
          </div>

        <!-- Navigation -->
          <div class="bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl p-6 border border-gray-200">
            <div class="flex items-center justify-between">
              <Button 
                type="button" 
                @click="prevTab" 
                :disabled="activeTabIndex === 0"
                variant="outline"
                class="flex items-center space-x-2 px-6 py-3 h-10 rounded-lg border-2 transition-all duration-200"
                :class="activeTabIndex === 0 ? 'opacity-50 cursor-not-allowed border-gray-300' : 'hover:bg-gray-50 border-gray-400 hover:border-gray-500'"
              >
                <ChevronLeft class="w-4 h-4" />
                <span>Previous</span>
              </Button>
              
              <div class="flex items-center space-x-4">
                <div class="text-xs font-medium text-gray-600">
                  Step {{ currentStep }} of {{ totalTabs }}
                </div>
                <div class="w-32 bg-gray-200 rounded-full h-2">
                  <div 
                    class="bg-gradient-to-r from-blue-500 to-blue-600 h-2 rounded-full transition-all duration-300"
                    :style="{ width: progress + '%' }"
                  ></div>
                </div>
              </div>

              <div class="flex gap-3">
                <Button 
                  v-if="activeTabIndex < tabs.length - 1" 
                  type="button" 
                  @click="nextTab"
                  class="flex items-center space-x-2 px-6 py-3 h-10 bg-gradient-to-r from-gray-900 to-black hover:from-gray-800 hover:to-gray-900 text-white rounded-lg shadow-lg hover:shadow-xl transition-all duration-200"
                >
                  <span>Next</span>
                  <ChevronRight class="w-4 h-4" />
                </Button>
                <Button 
                  v-else 
                  type="submit" 
                  class="flex items-center space-x-2 px-6 py-3 h-10 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white rounded-lg shadow-lg hover:shadow-xl transition-all duration-200"
                >
                  <Save class="w-4 h-4" />
                  <span>Save Operations</span>
                </Button>
              </div>
            </div>
    </div>
        </div>
      </div>
    </form>
  </AppLayout>
</template>
