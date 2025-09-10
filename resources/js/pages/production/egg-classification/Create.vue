<script setup lang="ts">
import { ref, computed, watch, reactive  } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import Datepicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'
import { type BreadcrumbItem } from '@/types';
import { useNotifier } from "@/composables/useNotifier"

// Props
const props = defineProps<{ batchAssign: Array<any> }>()
const { showInfo } = useNotifier()


const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Production', href: '/flock' },
  { title: 'Egg Classification', href: '/production/egg-classification' },
];

// Form
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
watch(() => form.batchassign_id, (id) => {
  form.total_egg = id ? flockEggData[id] || 0 : 0
})

function submit() {
  if (!form.batchassign_id) return showInfo("Please select a Batch")
  form.post(route('production/egg-classification.store'), {
    onSuccess: () => showInfo("Egg classification saved successfully")
  })
}



// Map each tab to its main field and note field
const mainFieldsByTab: Record<string, string[]> = {
  double_yolk: ['double_yolk'],
  double_yolk_broken: ['double_yolk_broken'],
  commercial: ['commercial'],
  commercial_broken: ['commercial_broken'],
  liquid: ['liquid'],
  damage: ['damage'],
}

const noteFieldByTab: Record<string, string> = {
  double_yolk: 'double_yolk_note',
  double_yolk_broken: 'double_yolk_broken_note',
  commercial: 'commercial_note',
  commercial_broken: 'commercial_broken_note',
  liquid: 'liquid_note',
  damage: 'damage_note',
}

const progressBarBackground = computed(() => {
  const segmentPercent = 100 / rejectedTabs.length
  const segments: string[] = []

  rejectedTabs.forEach((tab, index) => {
    const key = tab.key
    const mainFields = mainFieldsByTab[key] || []
    const noteField = noteFieldByTab[key]

    const mainHasValue = mainFields.some(f => form[f] > 0)
    const noteHasValue = noteField && (form[noteField] || '').toString().trim() !== ''

    let color = '#04A12B' // default yellow

    // Red if note is filled but main fields are all empty/0
    if (noteHasValue && !mainHasValue) {
      color = '#ef4444'
    }

    const start = index * segmentPercent
    const end = (index + 1) * segmentPercent
    segments.push(`${color} ${start}% ${end}%`)
  })

  return `linear-gradient(to right, ${segments.join(', ')})`
})



const commercial_total = computed(() => form.commercial);

// Total number of tabs
const totalTabs = rejectedTabs.length

// Current step = activeRejectedTab + 1
const currentStep = computed(() => activeRejectedTab.value + 1)

// Progress bar width = percentage of current step
const progress = computed(() => {
  return ((activeRejectedTab.value + 1) / totalTabs) * 100
})


const errors = reactive<{ [key: string]: string }>({})

// Validation for next tab
function canGoToNextRejectedTab(tabIndex: number): boolean {
  const tabKey = rejectedTabs[tabIndex].key
  const mainFields = mainFieldsByTab[tabKey] || []
  const noteField = noteFieldByTab[tabKey]

  const mainHasValue = mainFields.some(f => form[f] > 0)
  const noteValue = (form[noteField] || '').toString().trim()

  if (!mainHasValue && noteValue === '') {
    errors[noteField] = 'Please provide a note or fill at least one quantity field.'
    // Focus on note field
    const textarea = document.getElementById(noteField) as HTMLTextAreaElement
    if (textarea) textarea.focus()
    return false
  }

  // Clear error if validation passes
  if (errors[noteField]) delete errors[noteField]
  return true
}

function goNext() {
  if (activeRejectedTab.value < rejectedTabs.length - 1) {
    if (!canGoToNextRejectedTab(activeRejectedTab.value)) return
    activeRejectedTab.value++
  }
}

function goPrevious() {
  if (activeRejectedTab.value > 0) activeRejectedTab.value--
}


function tabCompleted(tabKey: string) {
  const mainFields = mainFieldsByTab[tabKey] || [];
  const noteField = noteFieldByTab[tabKey];

  const mainHasValue = mainFields.some(f => form[f] > 0);
  const noteHasValue = (form[noteField] || '').toString().trim() !== '';

  return mainHasValue || noteHasValue;
}


const isLastTabCompleted = computed(() => {
  const lastTab = rejectedTabs[rejectedTabs.length - 1];
  return tabCompleted(lastTab.key);
});
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">

    

    <form @submit.prevent="submit" class="space-y-6">
      
      <!-- Egg Classification Section -->
      <div class="m-5 p-6 bg-white rounded-xl shadow border">
        <h2 class="text-xl font-semibold mb-4">Egg Classification</h2>
    
        <!-- Progress Bar -->
        <div class="w-full bg-gray-200 rounded-xl h-6 overflow-hidden mb-4">
          <div
            class="h-full flex items-center justify-center text-white font-bold text-sm transition-all duration-500 rounded-xl"
            :style="{ width: progress + '%', background: progressBarBackground }"
          >
            {{ currentStep }} / {{ totalTabs }}
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label>Select Batch</Label>
            <select v-model="form.batchassign_id" class="w-full mt-1 border rounded px-3 py-2">
              <option value="">Select Batch</option>
              <option v-for="batchassinid in props.batchAssign" :key="batchassinid.id" :value="batchassinid.id">
                {{ batchassinid.label }}
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
          <!-- Commercial Egg Card -->
          <div class="bg-purple-100 p-4 rounded shadow text-center">
            <p class="text-gray-700 font-medium">Commercial Eggs</p>
            <p class="text-2xl font-bold">{{ commercial_total }}</p>
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

      <div class="m-5 p-6 bg-white rounded-xl shadow border">
        <h3 class="font-semibold mb-2">Rejected Egg Details</h3>

        <!-- Tabs -->
        <div class="grid grid-cols-2 md:grid-cols-6 gap-2 mb-4">
          <button
            v-for="(tab, index) in rejectedTabs"
            :key="tab.key"
            type="button"
            @click="() => {
              // Validate before moving forward
              if (index > activeRejectedTab.value) {
                if (!canGoToNextRejectedTab(activeRejectedTab.value)) return;
              }
              activeRejectedTab.value = index;
            }"
            :class="[
              'p-3 rounded border font-medium text-center',
              tabCompleted(tab.key)
                ? 'bg-gradient-to-r from-green-400 to-green-600 text-white'
                : activeRejectedTab === index
                ? 'bg-gradient-to-br from-gray-800 to-gray-900 text-white'
                : 'bg-white text-gray-700'
            ]"
        >
        {{ tab.label }} <br />
    <span class="text-xl font-bold">{{ form[tab.key] }}</span>
  </button>
        </div>

        <!-- Qty and Note -->
        <div>
          <Label class="mb-2">Qty</Label>
          <Input v-model.number="form[rejectedTabs[activeRejectedTab].key]" type="number" min="0" />
          
          <Label class="mt-2 mb-2">Note</Label>
          <textarea
            :id="noteFieldByTab[rejectedTabs[activeRejectedTab].key]"
            v-model="form[rejectedTabs[activeRejectedTab].key + '_note']"
            :class="{'border-red-500 focus:border-red-500 focus:ring-red-500': errors[rejectedTabs[activeRejectedTab].key + '_note']}"
            class="border rounded px-3 py-2 w-full"
            rows="2"
            placeholder="Optional note"
          ></textarea>
          <p v-if="errors[rejectedTabs[activeRejectedTab].key + '_note']" class="text-red-600 text-sm mt-1">
            {{ errors[rejectedTabs[activeRejectedTab].key + '_note'] }}
          </p>
        </div>

        <!-- Previous / Next Buttons -->
        <div class="flex justify-between mt-4">
         <Button
            type="button"
            @click="goPrevious"
            :disabled="activeRejectedTab === 0"
            :class="[
              'px-4 py-2 rounded-md font-medium transition-all',
              activeRejectedTab === 0
                ? 'bg-gray-300 text-black cursor-not-allowed'
                : 'bg-black text-white hover:bg-gray-800'
            ]"
          >
            Previous
          </Button>

          <!-- Next Button -->
          <Button
  type="button"
  @click="goNext"
  :disabled="activeRejectedTab === rejectedTabs.length - 1 && tabCompleted(rejectedTabs[activeRejectedTab].key)"
  :class="[
    'px-4 py-2 rounded-md font-medium transition-all',
    activeRejectedTab === rejectedTabs.length - 1 && tabCompleted(rejectedTabs[activeRejectedTab].key)
      ? 'bg-gray-300 text-gray-600 cursor-not-allowed' // readonly when complete
      : 'bg-black text-white hover:bg-gray-800'        // active
  ]"
>
  {{ activeRejectedTab === rejectedTabs.length - 1 && tabCompleted(rejectedTabs[activeRejectedTab].key) ? 'Complete' : 'Next' }}
</Button>
        </div>
      </div>

      <!-- Technical Information Section -->
      <div class="m-5 p-6 bg-white rounded-xl shadow border">
        <h3 class="font-semibold mb-2">Technical Information</h3>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-2 mb-4">
          <button v-for="(tab, index) in techTabs" :key="tab.key" type="button"
            @click="activeTechTab = index"
            class="p-3 rounded border font-medium text-center"
            :class="activeTechTab === index ? 'bg-gradient-to-br from-gray-800 to-gray-900 text-white' : 'bg-white text-gray-700'">
            {{ tab.label }} <br /> <span class="text-xl font-bold">{{ form[tab.key] }}</span>
          </button>
        </div>
        <div>
          <Label class="mb-2">Qty</Label>
          <Input v-model.number="form[techTabs[activeTechTab].key]" type="number" min="0" />
          <Label class="mb-2 mt-2">Note</Label>
          <textarea v-model="form[techTabs[activeTechTab].key + '_note']" class="border rounded px-3 py-2 w-full" rows="2" placeholder="Optional note"></textarea>
        </div>
      </div>

      <!-- Submit -->
      <div class="flex justify-end mt-4 m-5">
        <Button
  type="submit"
  :disabled="!isLastTabCompleted"
  :class="[
    'px-4 py-2 rounded-md font-medium transition-all',
    isLastTabCompleted ? 'bg-chicken text-white hover:bg-black-600' : 'bg-gray-300 text-gray-600 cursor-not-allowed'
  ]"
>
  Submit
</Button>
      </div>
    </form>
  </AppLayout>
</template>
