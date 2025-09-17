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
  { title: 'Egg Classification', href: '' },
]

// Props
const props = defineProps<{
  batchAssign: Array<any>
  stage?: string
}>()

const { showInfo } = useNotifier(); // auto-shows flash messages

// Tabs (keys must match below validations)
const rejectedTabs = [
  { key: 'double_yolk', label: 'Double Yolk' },
  { key: 'double_yolk_broken', label: 'Double Yolk Broken' },
  { key: 'commercial', label: 'Single Yolk Broken' },
  { key: 'commercial_broken', label: 'Commercial Broken' },
  { key: 'liquid', label: 'Liquid' },
  { key: 'damage', label: 'Damage' },
]

const technicalTabs = [
  { key: 'floor_egg', label: 'Floor Egg' },
  { key: 'thin_egg', label: 'Thin Egg' },
  { key: 'misshape_egg', label: 'Misshape Egg' },
  { key: 'white_egg', label: 'White Egg' },
  { key: 'dirty_egg', label: 'Dirty Egg' },
]

// Combined tabs for navigation
const tabs = [...rejectedTabs, ...technicalTabs]

const { batchOptions } = useDropdownOptions()

const batchWithLabel = computed(() =>
  props.batchAssign?.map(batch => {
    const batchOption = batchOptions.find(b => b.value === batch.batch_no)
    return {
      ...batch,
      batch_label: batchOption?.label || '',
      display_label: `${batch.label}-${batchOption?.label || ''}`,
    }
  }) || []
)

// Active Tab + progress
const activeTabIndex = ref(0)
const totalTabs = tabs.length
const currentStep = computed(() => activeTabIndex.value + 1)
const progress = computed(() => (currentStep.value / totalTabs) * 100)

// Form (exactly as you stated)
const form = useForm({
  batchassign_id: '',
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

const activeTab = computed(() => tabs[activeTabIndex.value].key)

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

// Close dropdown on outside click
const handleClickOutside = (e: MouseEvent) => {
  if (!(e.target as HTMLElement).closest('.flock-dropdown')) {
    showFlockDropdown.value = false
  }
  if (!(e.target as HTMLElement).closest('.date-overlay')) {
    showDateOverlay.value = false
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
  
  try {
    // Fetch real batch data
    const response = await fetch(`/production/egg-classification/batch/${id}/data`)
    const data = await response.json()
    
    if (data.batch) {
      shedQty.value = { 
        opening: data.batch.total_birds || 0, 
        current: data.batch.current_birds || 0 
      }
      flockInfo.value.age = data.batch.age || '0 weeks 0 days'
      
      // Set statistics data
      counts.value = {
        total_eggs: data.statistics.total_eggs || 0,
        daily_mortality: data.statistics.daily_mortality || 0,
        destroy: data.statistics.destroy || 0,
        sexing_error: data.statistics.sexing_error || 0,
        cull: data.statistics.cull || 0,
        feed_consumption: data.statistics.feed_consumption || '0 Kg',
        water_consumption: data.statistics.water_consumption || '0 L',
        light_hour: data.statistics.light_hour || '0 H',
        weight: data.statistics.weight || '0 gm',
        temperature: data.statistics.temperature || 0,
        humidity: data.statistics.humidity || 0,
        egg_collection: data.statistics.egg_collection || 0,
        medicine: data.statistics.medicine || 0,
        vaccine: data.statistics.vaccine || 0,
      }
    }
  } catch (error) {
    console.error('Error fetching batch data:', error)
    shedQty.value = { opening: 0, current: 0 }
    flockInfo.value.age = '0 weeks 0 days'
    counts.value = {}
  }
})

watch(
  () => [form.batchassign_id, form.operation_date],
  async ([batchId, operationDate]) => {
    if (!batchId || !operationDate) {
      form.total_egg = 0
      return
    }

    try {
      // Use Inertia GET for AJAX-like behavior
      const page = await form.get(route('egg-classification.total-eggs'), {
        preserveState: true,
        preserveScroll: true,
        only: ['total_egg'], // only return the total_egg prop
      })


     
      // Update reactive form value
      form.total_egg = 6000
    } catch (error) {
      form.total_egg = 0
    }
  },
  { immediate: true }
)
// Totals
const rejected_total = computed(() => {
  const rejectedTabs = ['double_yolk', 'double_yolk_broken', 'commercial', 'commercial_broken', 'liquid', 'damage']
  return rejectedTabs.reduce((sum, key) => sum + ((form as any)[key] || 0), 0)
})

const tech_total = computed(() => {
  const techTabs = ['floor_egg', 'thin_egg', 'misshape_egg', 'white_egg', 'dirty_egg']
  return techTabs.reduce((sum, key) => sum + ((form as any)[key] || 0), 0)
})

const hatching_egg = computed(() => form.total_egg - rejected_total.value)

function submit() {
  if (!form.batchassign_id) return showInfo("Please select a Batch")
  form.post(route('egg-classification.store'), {
    onSuccess: () => showInfo("Egg classification saved successfully")
  })
}



// ---------- Validation Setup ----------
type Rule = { field: keyof typeof form; label: string; kind: 'string' | 'number'; min?: number }

// Basic note field rules for each tab
const rulesByTab: Record<string, Rule[]> = {
  double_yolk: [{ field: 'double_yolk_note', label: 'Double Yolk note', kind: 'string' }],
  double_yolk_broken: [{ field: 'double_yolk_broken_note', label: 'Double Yolk Broken note', kind: 'string' }],
  commercial: [{ field: 'commercial_note', label: 'Commercial note', kind: 'string' }],
  commercial_broken: [{ field: 'commercial_broken_note', label: 'Commercial Broken note', kind: 'string' }],
  liquid: [{ field: 'liquid_note', label: 'Liquid note', kind: 'string' }],
  damage: [{ field: 'damage_note', label: 'Damage note', kind: 'string' }],
  floor_egg: [{ field: 'floor_egg_note', label: 'Floor Egg note', kind: 'string' }],
  thin_egg: [{ field: 'thin_egg_note', label: 'Thin Egg note', kind: 'string' }],
  misshape_egg: [{ field: 'misshape_egg_note', label: 'Misshape Egg note', kind: 'string' }],
  white_egg: [{ field: 'white_egg_note', label: 'White Egg note', kind: 'string' }],
  dirty_egg: [{ field: 'dirty_egg_note', label: 'Dirty Egg note', kind: 'string' }],
}

// ---------- Main Fields Mapping ----------
const mainFieldsByTab: Record<string, (keyof typeof form)[]> = {
  double_yolk: ['double_yolk'],
  double_yolk_broken: ['double_yolk_broken'],
  commercial: ['commercial'],
  commercial_broken: ['commercial_broken'],
  liquid: ['liquid'],
  damage: ['damage'],
  floor_egg: ['floor_egg'],
  thin_egg: ['thin_egg'],
  misshape_egg: ['misshape_egg'],
  white_egg: ['white_egg'],
  dirty_egg: ['dirty_egg'],
}

// ---------- Note Field Mapping ----------
const noteFieldByTab: Record<string, keyof typeof form> = {
  double_yolk: 'double_yolk_note',
  double_yolk_broken: 'double_yolk_broken_note',
  commercial: 'commercial_note',
  commercial_broken: 'commercial_broken_note',
  liquid: 'liquid_note',
  damage: 'damage_note',
  floor_egg: 'floor_egg_note',
  thin_egg: 'thin_egg_note',
  misshape_egg: 'misshape_egg_note',
  white_egg: 'white_egg_note',
  dirty_egg: 'dirty_egg_note',
}

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



</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Egg Classification" />

    <!-- Back to List Button -->
    <div class="flex justify-end mt-2 px-6">
      <a 
        href="/production/egg-classification" 
        class="inline-flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-gray-900 to-black hover:from-gray-800 hover:to-gray-900 text-white rounded-lg shadow-lg hover:shadow-xl transition-all duration-200 text-sm font-medium"
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
                <span class="text-white font-bold text-xs">🥚</span>
              </div>
              <div>
                <h2 class="text-lg font-bold text-white">Egg Classification Information</h2>
                <p class="text-blue-100 text-xs">Select flock and record egg classification</p>
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
                Select Batch
              </Label>
              <div class="flock-dropdown relative">
                <button
                  type="button"
                  @click.stop="showFlockDropdown = !showFlockDropdown"
                  class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 shadow-sm transition-all duration-200 hover:border-blue-500 hover:shadow-md focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 text-xs h-10"
                >
                  <span class="flex items-center gap-2">
                    <div class="h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                    {{ selectedFlock ? selectedFlock.display_label : 'Select Batch' }}
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
                      <h3 class="font-semibold text-gray-900 text-sm">Select Batch</h3>
                      <div class="relative mt-2">
                        <Search class="absolute left-2 top-1/2 h-3 w-3 -translate-y-1/2 text-gray-400" />
                        <input
                          v-model="flockSearchQuery"
                          type="text"
                          placeholder="Search Batch..."
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
                Classification Date
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
                        <h3 class="font-semibold text-gray-900 text-sm">Select Classification Date</h3>
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
                Egg Classification Statistics
              </h3>
              </div>
            <div class="grid grid-cols-4 gap-0">
              <div class="p-2 text-center border-r border-gray-200">
                <div class="w-6 h-6 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center mx-auto mb-1">
                  <span class="text-white text-xs">🥚</span>
              </div>
                <p class="text-gray-600 font-medium text-xs mb-0.5">Total Eggs</p>
                <p class="text-xs font-bold text-gray-900">{{ (counts.total_eggs || form.total_egg).toLocaleString() }}</p>
              </div>
              <div class="p-2 text-center border-r border-gray-200">
                <div class="w-6 h-6 bg-gradient-to-br from-red-400 to-red-500 rounded-full flex items-center justify-center mx-auto mb-1">
                  <span class="text-white text-xs">❌</span>
            </div>
                <p class="text-gray-600 font-medium text-xs mb-0.5">Commercial Egg</p>
                <p class="text-xs font-bold text-gray-900">{{ rejected_total.toLocaleString() }}</p>
          </div>
              <div class="p-2 text-center border-r border-gray-200">
                <div class="w-6 h-6 bg-gradient-to-br from-blue-400 to-blue-500 rounded-full flex items-center justify-center mx-auto mb-1">
                  <span class="text-white text-xs">🔧</span>
                </div>
                <p class="text-gray-600 font-medium text-xs mb-0.5">Technical</p>
                <p class="text-xs font-bold text-gray-900">{{ tech_total.toLocaleString() }}</p>
              </div>
              <div class="p-2 text-center">
                <div class="w-6 h-6 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center mx-auto mb-1">
                  <span class="text-white text-xs">✅</span>
                </div>
                <p class="text-gray-600 font-medium text-xs mb-0.5">Hatching</p>
                <p class="text-xs font-bold text-gray-900">{{ hatching_egg.toLocaleString() }}</p>
              </div>
          </div>
          </div>

          <!-- Additional Statistics from Daily Operations -->
          <div v-if="Object.keys(counts).length > 0" class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden mt-4">
            <div class="bg-gradient-to-r from-gray-50 to-green-50 px-2 py-1.5 border-b border-gray-200">
              <h3 class="text-xs font-semibold text-gray-700 flex items-center">
                <div class="w-1 h-1 bg-green-500 rounded-full mr-1.5"></div>
                Daily Operations Data
              </h3>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-0">
              <div class="p-2 text-center border-r border-gray-200">
                <div class="w-6 h-6 bg-gradient-to-br from-red-400 to-red-500 rounded-full flex items-center justify-center mx-auto mb-1">
                  <span class="text-white text-xs">💀</span>
                </div>
                <p class="text-gray-600 font-medium text-xs mb-0.5">Mortality</p>
                <p class="text-xs font-bold text-gray-900">{{ counts.daily_mortality || 0 }}</p>
              </div>
              <div class="p-2 text-center border-r border-gray-200">
                <div class="w-6 h-6 bg-gradient-to-br from-orange-400 to-orange-500 rounded-full flex items-center justify-center mx-auto mb-1">
                  <span class="text-white text-xs">🗑️</span>
                </div>
                <p class="text-gray-600 font-medium text-xs mb-0.5">Destroy</p>
                <p class="text-xs font-bold text-gray-900">{{ counts.destroy || 0 }}</p>
              </div>
              <div class="p-2 text-center border-r border-gray-200">
                <div class="w-6 h-6 bg-gradient-to-br from-purple-400 to-purple-500 rounded-full flex items-center justify-center mx-auto mb-1">
                  <span class="text-white text-xs">⚠️</span>
                </div>
                <p class="text-gray-600 font-medium text-xs mb-0.5">Sexing Error</p>
                <p class="text-xs font-bold text-gray-900">{{ counts.sexing_error || 0 }}</p>
              </div>
              <div class="p-2 text-center">
                <div class="w-6 h-6 bg-gradient-to-br from-gray-400 to-gray-500 rounded-full flex items-center justify-center mx-auto mb-1">
                  <span class="text-white text-xs">✂️</span>
                </div>
                <p class="text-gray-600 font-medium text-xs mb-0.5">Cull</p>
                <p class="text-xs font-bold text-gray-900">{{ counts.cull || 0 }}</p>
              </div>
            </div>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-0 border-t border-gray-200">
              <div class="p-2 text-center border-r border-gray-200">
                <div class="w-6 h-6 bg-gradient-to-br from-yellow-400 to-yellow-500 rounded-full flex items-center justify-center mx-auto mb-1">
                  <span class="text-white text-xs">🌾</span>
                </div>
                <p class="text-gray-600 font-medium text-xs mb-0.5">Feed</p>
                <p class="text-xs font-bold text-gray-900">{{ counts.feed_consumption || '0 Kg' }}</p>
              </div>
              <div class="p-2 text-center border-r border-gray-200">
                <div class="w-6 h-6 bg-gradient-to-br from-blue-400 to-blue-500 rounded-full flex items-center justify-center mx-auto mb-1">
                  <span class="text-white text-xs">💧</span>
                </div>
                <p class="text-gray-600 font-medium text-xs mb-0.5">Water</p>
                <p class="text-xs font-bold text-gray-900">{{ counts.water_consumption || '0 L' }}</p>
              </div>
              <div class="p-2 text-center border-r border-gray-200">
                <div class="w-6 h-6 bg-gradient-to-br from-indigo-400 to-indigo-500 rounded-full flex items-center justify-center mx-auto mb-1">
                  <span class="text-white text-xs">💡</span>
                </div>
                <p class="text-gray-600 font-medium text-xs mb-0.5">Light</p>
                <p class="text-xs font-bold text-gray-900">{{ counts.light_hour || '0 H' }}</p>
              </div>
              <div class="p-2 text-center">
                <div class="w-6 h-6 bg-gradient-to-br from-pink-400 to-pink-500 rounded-full flex items-center justify-center mx-auto mb-1">
                  <span class="text-white text-xs">⚖️</span>
                </div>
                <p class="text-gray-600 font-medium text-xs mb-0.5">Weight</p>
                <p class="text-xs font-bold text-gray-900">{{ counts.weight || '0 gm' }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Two Column Layout -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Left Column - Tab Cards -->
        <div class="space-y-6">
          <!-- Rejected Eggs Section -->
          <div class="space-y-4">
            <div class="bg-gradient-to-r from-red-50 to-pink-50 rounded-lg p-4 border border-red-200">
              <h3 class="text-lg font-bold text-gray-800 mb-1 flex items-center">
                <div class="w-6 h-6 bg-red-600 rounded flex items-center justify-center mr-2">
                  <span class="text-white font-bold text-xs">❌</span>
                </div>
                Rejected Eggs (1-6)
              </h3>
              <p class="text-xs text-gray-600">Select a category to record rejected egg details</p>
            </div>
            
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-2">
              <div
            v-for="(tab, index) in rejectedTabs"
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
                <div v-if="(form as any)[tab.key] !== undefined" class="text-xs font-bold">
              {{ (form as any)[tab.key] }}
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

          <!-- Technical Reject Section -->
          <div class="space-y-4">
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
              <h3 class="text-lg font-bold text-gray-800 mb-1 flex items-center">
                <div class="w-6 h-6 bg-blue-600 rounded flex items-center justify-center mr-2">
                  <span class="text-white font-bold text-xs">🔧</span>
                </div>
                Technical Reject (7-11)
              </h3>
              <p class="text-xs text-gray-600">Select a category to record technical reject details</p>
        </div>

            <div class="grid grid-cols-2 lg:grid-cols-3 gap-2">
              <div
                v-for="(tab, index) in technicalTabs"
                :key="tab.key"
                @click="goToTab(rejectedTabs.length + index)"
                class="group cursor-pointer p-2 border-2 rounded-lg transition-all duration-300 hover:shadow-md hover:scale-[1.01] text-center"
            :class="[
                  activeTabIndex === (rejectedTabs.length + index)
                        ? 'bg-gradient-to-r from-blue-500 to-indigo-500 text-white border-blue-500 shadow-md scale-[1.01]'
                    : completedTabs.includes(rejectedTabs.length + index) 
                          ? 'bg-gradient-to-r from-green-500 to-emerald-500 text-white border-green-500 shadow-sm'
                          : 'bg-white text-gray-700 border-gray-200 hover:border-gray-300'
                  ]"
              >
                <!-- Number badge -->
                <div class="w-6 h-6 rounded flex items-center justify-center text-xs font-bold mx-auto mb-1.5"
  :class="[
                    activeTabIndex === (rejectedTabs.length + index)
                      ? 'bg-white/20 text-white'
                      : completedTabs.includes(rejectedTabs.length + index) 
                        ? 'bg-white/20 text-white'
                        : 'bg-gray-100 text-gray-600'
                  ]"
                >
                  {{ rejectedTabs.length + index + 1 }}
                </div>
                
                <!-- Tab label -->
                <div class="font-semibold text-xs mb-1 leading-tight">{{ tab.label }}</div>
                
                <!-- Status -->
                <div class="text-xs opacity-75 mb-1">
                  {{ completedTabs.includes(rejectedTabs.length + index) ? '✓' : activeTabIndex === (rejectedTabs.length + index) ? '●' : '○' }}
                </div>
                
                <!-- Count value -->
                <div v-if="(form as any)[tab.key] !== undefined" class="text-xs font-bold">
              {{ (form as any)[tab.key] }}
                </div>
                <div v-else class="text-xs opacity-75">
                  ---
                </div>
                
                <!-- Progress indicator -->
                <div class="mt-1.5 w-full bg-black/10 rounded-full h-0.5">
                  <div 
                    class="h-0.5 rounded-full transition-all duration-300"
                    :class="[
                      activeTabIndex === (rejectedTabs.length + index)
                        ? 'bg-white w-full'
                        : completedTabs.includes(rejectedTabs.length + index) 
                          ? 'bg-white w-full'
                          : 'bg-gray-300 w-0'
                    ]"
                  ></div>
                </div>
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
                <div class="w-6 h-6 rounded flex items-center justify-center"
                  :class="activeTabIndex < rejectedTabs.length ? 'bg-red-600' : 'bg-blue-600'">
                  <span class="text-white font-bold text-xs">{{ activeTabIndex + 1 }}</span>
                </div>
                <div>
                  <h3 class="text-lg font-bold text-gray-800">{{ tabs[activeTabIndex]?.label }}</h3>
                  <p class="text-xs text-gray-600">
                    {{ activeTabIndex < rejectedTabs.length ? 'Rejected Eggs Category' : 'Technical Reject Category' }} - Fill in the details below
                  </p>
                </div>
              </div>
              <div class="text-right">
                <div class="text-xs text-gray-500">Step {{ currentStep }} of {{ totalTabs }}</div>
                <div class="w-20 bg-gray-200 rounded-full h-1.5 mt-1">
                  <div 
                    class="h-1.5 rounded-full transition-all duration-300"
                    :class="activeTabIndex < rejectedTabs.length ? 'bg-red-500' : 'bg-blue-500'"
                    :style="{ width: (currentStep / totalTabs) * 100 + '%' }"
                  ></div>
                </div>
              </div>
        </div>
      </div>

      <!-- Tab Content -->
          <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
        <!-- Dynamic Tab Content -->
        <div class="p-4">
          <div class="space-y-4">
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 flex items-center">
                <div class="w-1.5 h-1.5 rounded-full mr-2"
                  :class="activeTabIndex < rejectedTabs.length ? 'bg-red-500' : 'bg-blue-500'"></div>
                {{ tabs[activeTabIndex]?.label }} Quantity
              </Label>
              <Input 
                v-model.number="(form as any)[activeTab]" 
                type="number" 
                placeholder="Enter quantity..."
                :class="[
                  'h-10 border-gray-300 focus:border-transparent',
                  activeTabIndex < rejectedTabs.length 
                    ? 'focus:ring-2 focus:ring-red-500' 
                    : 'focus:ring-2 focus:ring-blue-500'
                ]"
              />
            </div>
            
            <div class="space-y-1">
              <Label class="text-xs font-semibold text-gray-700 flex items-center">
                <span class="text-red-500 mr-1">*</span>
                Additional Notes
              </Label>
              <textarea
                v-model="(form as any)[activeTab + '_note']"
                :placeholder="`Add any additional notes about ${tabs[activeTabIndex]?.label.toLowerCase()} eggs...`"
                rows="4"
                :class="[
                  'w-full px-3 py-2 border border-gray-300 rounded-lg transition-all duration-200 resize-none',
                  activeTabIndex < rejectedTabs.length 
                    ? 'focus:ring-2 focus:ring-red-500 focus:border-transparent' 
                    : 'focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                  errors[activeTab + '_note'] ? 'border-red-500 ring-2 ring-red-200' : ''
                ]"
              ></textarea>
              <p v-if="errors[activeTab + '_note']" class="text-red-600 text-xs mt-1 flex items-center">
                <AlertTriangle class="w-4 h-4 mr-1" />
                {{ errors[activeTab + '_note'] }}
              </p>
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
                  <span>Save Classification</span>
</Button>
              </div>
            </div>
    </div>
        </div>
      </div>
    </form>
  </AppLayout>
</template>
