<script setup lang="ts">
import { ref, computed,watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Select } from '@/components/ui/select'
import { Separator } from '@/components/ui/separator'
import { useNotifier } from '@/composables/useNotifier'
import { useDropdownOptions } from '@/composables/dropdownOptions'
import { 
  ArrowRightIcon, 
  BuildingOfficeIcon, 
  CalendarIcon, 
  ExclamationTriangleIcon,
  InformationCircleIcon,
  PlusIcon,
  UserGroupIcon
} from '@heroicons/vue/24/outline'

const { showInfo } = useNotifier()

// Overlay states
const showBatchOverlay = ref(false)
const showCompanyOverlay = ref(false)
const showProjectOverlay = ref(false)
const showFlockOverlay = ref(false)
const showShedOverlay = ref(false)

// Props from backend
const props = defineProps<{
  batchAssign: any,
  flocks: any[],
  companies: any[],
  sheds: any[],
  batches: any[],
  projects:any[],
}>()

// Get batch dropdown from composable
const { batchOptions } = useDropdownOptions()

// Batch options from database
const batchOptionsFromDB = computed(() => 
  props.batches.map(batch => ({
    value: batch.id,
    label: batch.name
  }))
)

// Selected batch display name
const selectedBatchName = computed(() => {
  const selectedBatch = props.batches.find(batch => batch.id === form.batch_id)
  return selectedBatch ? selectedBatch.name : 'Select batch'
})

// Selected company display name
const selectedCompanyName = computed(() => {
  const selectedCompany = props.companies.find(company => company.id === form.to_company_id)
  return selectedCompany ? selectedCompany.name : 'Select company'
})

// Selected company display name
const selectedProjectName = computed(() => {
  const selectedProject = props.projects.find(project => project.id === form.to_project_id)
  return selectedProject ? selectedProject.name : 'Select Project'
})


// Form pre-filled with backend data
const form = useForm({
  batch_assign_id: props.batchAssign.id,
  batch_id: props.batchAssign.batch_no, // Use batch_no as the batch_id
  flock_id: props.batchAssign.flock_id,
  from_company_id: props.batchAssign.company_id,
  from_shed_id: props.batchAssign.shed_id,
  to_company_id: undefined,
  to_shed_id: undefined,
  to_project_id: undefined,
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

const transfer_total_qty = computed(() => form.transfer_male_qty + form.transfer_female_qty)
const medical_total_qty = computed(() => form.medical_male_qty + form.medical_female_qty)
const deviation_total = computed(() => form.deviation_total_qty)

// Show shed only if same company
const showShed = computed(() => props.batchAssign.company_id === form.to_company_id)

// Dropdown options
const flockOptions = computed(() => 
  props.flocks.map(flock => ({
    value: flock.id,
    label: flock.name
  }))
)

// Selected flock name for display
const selectedFlockName = computed(() => {
  const flock = props.flocks.find(f => f.id === form.flock_id)
  return flock ? flock.name : 'Select flock'
})

const companyOptions = computed(() => 
  props.companies.map(company => ({
    value: company.id,
    label: company.name
  }))
)

const shedOptions = computed(() => 
  props.sheds.map(shed => ({
    value: shed.id,
    label: shed.name
  }))
)

// Selected shed name for display
const selectedShedName = computed(() => {
  const shed = props.sheds.find(s => s.id === form.to_shed_id)
  return shed ? shed.name : 'Select shed'
})

// Submit
function submit() {
  if (!form.flock_id) return showInfo("Please select a flock")
  if (!form.to_company_id) return showInfo("Please select a transfer company")

  form.post(route('bird-transfer.store'), {
    onSuccess: () => showInfo("Bird transfer saved successfully")
  })
}

// Filter projects based on selected company
const projectOptions = computed(() => {
  if (!form.to_company_id) return [];

 
  return props.projects
    .filter(project => project.company_id === form.to_company_id) // filter by company
    .map(project => ({
      id: project.id,
      name: project.name,
      code: project.code,
      location: project.location
      
    }));
});

watch(() => form.to_company_id, () => {
  form.to_project_id = undefined; // reset project whenever company changes
});

watch(
  [
    () => form.transfer_male_qty,
    () => form.transfer_female_qty,
    () => form.medical_male_qty,
    () => form.medical_female_qty,
  ],
  () => {
    // Compute deviation dynamically and sync with form

    form.transfer_total_qty = (form.transfer_male_qty || 0) + (form.transfer_female_qty || 0);
    form.medical_total_qty = form.medical_male_qty + form.medical_female_qty;

    form.deviation_male_qty = current_male_chicks.value - form.transfer_male_qty - form.medical_male_qty;
    form.deviation_female_qty = current_female_chicks.value - form.transfer_female_qty - form.medical_female_qty;
    form.deviation_total_qty = form.deviation_male_qty + form.deviation_female_qty;
  },
  { immediate: true }
);

</script>

<template>
  <AppLayout>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50/30">
      <div class="container mx-auto px-4 py-8">
        <!-- Modern Header -->
        <div class="mb-8">
          <div class="flex items-center justify-between">
            <div class="space-y-1">
              <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                Bird Transfer
              </h1>
              <p class="text-slate-600 text-lg">Seamlessly transfer birds between locations</p>
            </div>
            <Button
              @click="$inertia.visit(route('bird-transfer.index'))"
              variant="outline"
              class="px-4 py-2 bg-white/80 backdrop-blur-sm border border-white/20 hover:bg-white/90 transition-all duration-200"
            >
              <ArrowRightIcon class="mr-2 h-4 w-4 rotate-180" />
              Back to List
            </Button>
          </div>
        </div>

        <div class="space-y-8">
          <!-- Current Status Section -->
          <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-white/20">
            <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
              <BuildingOfficeIcon class="h-6 w-6 text-blue-600" />
              Current Status of - ( {{ batchAssign.company?.short_name}}-{{ batchAssign.flock?.name || 'Flock-' + batchAssign.flock_id }}-{{ batchAssign.shed?.name || 'Shed-' + batchAssign.shed_id }}-Batch {{ batchAssign.batch_name || batchAssign.id }} )
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
              <div class="flex items-center justify-between p-4 bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl border border-amber-200">
                <div>
                  <p class="text-sm font-medium text-amber-700">Batch Total</p>
                  <p class="text-2xl font-bold text-amber-800">{{ batchAssign.batch_total_qty }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                  <UserGroupIcon class="h-6 w-6 text-amber-600" />
                </div>
              </div>

              <div class="flex items-center justify-between p-4 bg-gradient-to-r from-red-50 to-pink-50 rounded-2xl border border-red-200">
                <div>
                  <p class="text-sm font-medium text-red-700">Total Mortality</p>
                  <p class="text-2xl font-bold text-red-800">{{ (batchAssign.batch_female_mortality || 0) + (batchAssign.batch_male_mortality || 0) }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                  <ExclamationTriangleIcon class="h-6 w-6 text-red-600" />
                </div>
              </div>

              <div class="p-4 bg-gradient-to-r from-emerald-50 to-green-50 rounded-2xl border border-emerald-200 text-center">
                <p class="text-sm font-medium text-emerald-700">Batch Female</p>
                <p class="text-xl font-bold text-emerald-800">{{ batchAssign.batch_female_qty }}</p>
              </div>

              <div class="p-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-2xl border border-blue-200 text-center">
                <p class="text-sm font-medium text-blue-700">Batch Male</p>
                <p class="text-xl font-bold text-blue-800">{{ batchAssign.batch_male_qty }}</p>
              </div>

              <div class="p-4 bg-gradient-to-r from-purple-50 to-indigo-50 rounded-2xl border border-purple-200 text-center">
                <p class="text-sm font-medium text-purple-700">Available</p>
                <p class="text-2xl font-bold text-purple-800">{{ current_total_chicks }}</p>
              </div>
            </div>

            <!-- Deviation Alert -->
            <div v-if="deviation_total < 0" class="mt-6 bg-gradient-to-r from-red-50 to-pink-50 rounded-2xl p-4 border border-red-200">
              <div class="flex items-center gap-3 mb-2">
                <ExclamationTriangleIcon class="h-5 w-5 text-red-600" />
                <h3 class="text-base font-bold text-red-800">Over-allocation Alert</h3>
              </div>
              <p class="text-red-700 text-sm">
                You've allocated {{ Math.abs(deviation_total) }} more birds than available. Please adjust your quantities.
              </p>
            </div>
          </div>

          <!-- Transfer Configuration Form -->
          <form @submit.prevent="submit" class="space-y-6">
            <!-- Main Form Card -->
            <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-8 shadow-xl border border-white/20">
              <div class="mb-8">
                <!-- <h2 class="text-2xl font-bold text-slate-800 mb-2">Transfer Details</h2> -->
                <p class="text-slate-600">Bird Transfer Details</p>
              </div>

              <div class="space-y-8">
                <!-- Basic Info Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                  <div class="space-y-1">
                    <Label class="text-xs font-medium text-slate-600">Flock No</Label>
                    <Button
                      type="button"
                      @click="showFlockOverlay = true"
                      class="w-full h-9 text-sm bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 justify-start"
                    >
                      <UserGroupIcon class="mr-2 h-4 w-4" />
                      {{ selectedFlockName }}
                    </Button>
                  </div>

                  <div class="space-y-1">
                    <Label class="text-xs font-medium text-slate-600">Batch</Label>
                    <Button
                      type="button"
                      @click="showBatchOverlay = true"
                      class="w-full h-9 text-sm bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 justify-start"
                    >
                      <BuildingOfficeIcon class="mr-2 h-4 w-4" />
                      {{ selectedBatchName }}
                    </Button>
                  </div>

                  <div class="space-y-1">
                    <Label class="text-xs font-medium text-slate-600">Transfer Date</Label>
                    <div class="relative">
                      <CalendarIcon class="absolute left-2 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                      <Input 
                        v-model="form.transfer_date" 
                        type="date" 
                        class="pl-8 h-9 text-sm" 
                      />
                    </div>
                  </div>

                  <div class="space-y-1">
                    <Label class="text-xs font-medium text-slate-600">Destination Company</Label>
                    <Button
                      type="button"
                      @click="showCompanyOverlay = true"
                      class="w-full h-9 text-sm bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 justify-start"
                    >
                      <BuildingOfficeIcon class="mr-2 h-4 w-4" />
                      {{ selectedCompanyName }}
                    </Button>
                  </div>
                  <div class="space-y-1">
                    <Label class="text-xs font-medium text-slate-600">Destination Project</Label>
                    <Button
                      type="button"
                      @click="showProjectOverlay = true"
                      class="w-full h-9 text-sm bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 justify-start"
                    >
                      <BuildingOfficeIcon class="mr-2 h-4 w-4" />
                      {{ selectedProjectName }}
                    </Button>
                  </div>
                </div>

                <!-- Shed Selection -->
                <div v-if="showShed" class="space-y-1 max-w-xs">
                  <Label class="text-xs font-medium text-slate-600">Destination Shed</Label>
                  <Button
                    type="button"
                    @click="showShedOverlay = true"
                    class="w-full h-9 text-sm bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 justify-start"
                  >
                    <BuildingOfficeIcon class="mr-2 h-4 w-4" />
                    {{ selectedShedName }}
                  </Button>
                </div>

                <!-- Transfer Quantities -->
                <div class="space-y-4">
                  <!-- <h3 class="text-base font-semibold text-slate-800 border-b border-slate-200 pb-2">Transfer Quantities</h3> -->
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-1">
                      <Label class="text-xs font-medium text-slate-600">Female Birds</Label>
                      <Input 
                        v-model.number="form.transfer_female_qty" 
                        type="number" 
                        min="0" 
                        :max="current_female_chicks"
                        class="w-full h-9 text-sm"
                      />
                      <p class="text-xs text-slate-500">Max: {{ current_female_chicks }}</p>
                    </div>
                    <div class="space-y-1">
                      <Label class="text-xs font-medium text-slate-600">Male Birds</Label>
                      <Input 
                        v-model.number="form.transfer_male_qty" 
                        type="number" 
                        min="0" 
                        :max="current_male_chicks"
                        class="w-full h-9 text-sm"
                      />
                      <p class="text-xs text-slate-500">Max: {{ current_male_chicks }}</p>
                    </div>
                    <div class="space-y-1">
                      <Label class="text-xs font-medium text-slate-600">Total Transfer</Label>
                      <Input 
                        v-model.number="transfer_total_qty" 
                        type="number" 
                        readonly 
                        class="w-full h-9 text-sm bg-slate-50"
                      />
                    </div>
                  </div>
                </div>

                <!-- Medical Birds -->
                <div class="space-y-4">
                  <!-- <h3 class="text-base font-semibold text-slate-800 border-b border-slate-200 pb-2">Medical Birds</h3> -->
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="space-y-1">
                      <Label class="text-xs font-medium text-slate-600">Medical Female</Label>
                      <Input 
                        v-model.number="form.medical_female_qty" 
                        type="number" 
                        min="0" 
                        :max="current_female_chicks - form.transfer_female_qty"
                        class="w-full h-9 text-sm"
                      />
                      <p class="text-xs text-slate-500">Max: {{ current_female_chicks - form.transfer_female_qty }}</p>
                    </div>
                    <div class="space-y-1">
                      <Label class="text-xs font-medium text-slate-600">Medical Male</Label>
                      <Input 
                        v-model.number="form.medical_male_qty" 
                        type="number" 
                        min="0" 
                        :max="current_male_chicks - form.transfer_male_qty"
                        class="w-full h-9 text-sm"
                      />
                      <p class="text-xs text-slate-500">Max: {{ current_male_chicks - form.transfer_male_qty }}</p>
                    </div>
                    <div class="space-y-1">
                      <Label class="text-xs font-medium text-slate-600">Total Medical</Label>
                      <Input 
                        v-model.number="medical_total_qty" 
                        type="number" 
                        readonly 
                        class="w-full h-9 text-sm bg-slate-50"
                      />
                    </div>
                  </div>
                </div>

                <!-- Deviation Birds -->
                <div class="space-y-4">
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Deviation Female -->
                    <div class="space-y-1">
                      <Label class="text-xs font-medium text-slate-600">Deviation Female</Label>
                      <Input 
                        v-model.number="form.deviation_female_qty" 
                        type="number" 
                        readonly
                        class="w-full h-9 text-sm bg-yellow-50"
                      />
                      <p class="text-xs text-slate-500">Auto-calculated from female</p>
                    </div>

                    <!-- Deviation Male -->
                    <div class="space-y-1">
                      <Label class="text-xs font-medium text-slate-600">Deviation Male</Label>
                      <Input 
                        v-model.number="form.deviation_male_qty" 
                        type="number" 
                        readonly
                        class="w-full h-9 text-sm bg-yellow-50"
                      />
                      <p class="text-xs text-slate-500">Auto-calculated from male</p>
                    </div>

                    <!-- Deviation Total -->
                    <div class="space-y-1">
                      <Label class="text-xs font-medium text-slate-600">Deviation Total</Label>
                      <Input 
                        v-model.number="form.deviation_total_qty" 
                        type="number" 
                        readonly
                        class="w-full h-9 text-sm bg-yellow-100 font-semibold"
                      />
                      <p class="text-xs text-slate-500">Male + Female</p>
                    </div>
                  </div>
                </div>


                <!-- Notes -->
                <div class="space-y-1">
                  <Label class="text-xs font-medium text-slate-600">Transfer Notes</Label>
                  <textarea 
                    v-model="form.transfer_note" 
                    rows="3" 
                    class="w-full rounded-lg border border-slate-200 bg-transparent px-3 py-2 text-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                    placeholder="Add any additional notes about this transfer..."
                  />
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex items-center justify-end gap-4 pt-6">
                <Button 
                  type="button" 
                  variant="outline" 
                  class="px-8 py-3 h-12 text-base font-semibold rounded-xl border-2 hover:bg-slate-50"
                  @click="$inertia.visit(route('bird-transfer.index'))"
                >
                  Cancel
                </Button>
                <Button 
                  type="submit" 
                  :disabled="form.processing || deviation_total < 0"
                  class="px-8 py-3 h-12 text-base font-semibold rounded-xl bg-black hover:bg-gray-800 text-white shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed relative overflow-hidden"
                  style="background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%); box-shadow: 0 4px 15px rgba(0,0,0,0.3), inset 0 1px 0 rgba(255,255,255,0.1);"
                >
                  <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent transform -skew-x-12 -translate-x-full hover:translate-x-full transition-transform duration-1000"></div>
                  <PlusIcon class="mr-2 h-5 w-5 relative z-10" />
                  <span class="relative z-10">{{ form.processing ? 'Processing...' : 'Create Transfer' }}</span>
                </Button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Shed Selection Overlay -->
    <div v-if="showShedOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
      <div class="bg-white rounded-2xl p-6 shadow-2xl border border-white/20 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xl font-bold text-slate-800">Select Destination Shed</h3>
          <Button
            type="button"
            @click="showShedOverlay = false"
            variant="outline"
            class="h-8 w-8 p-0 rounded-full"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </Button>
        </div>

        <div class="space-y-3">
          <div>
            <Label class="text-sm font-semibold text-slate-700">Available Sheds</Label>
            <div class="max-h-60 overflow-y-auto space-y-2">
              <button
                v-for="shed in props.sheds"
                :key="shed.id"
                @click="form.to_shed_id = shed.id; showShedOverlay = false"
                class="w-full text-left p-3 rounded-lg border border-slate-200 hover:bg-blue-50 hover:border-blue-300 transition-colors"
                :class="{ 'bg-blue-100 border-blue-300': form.to_shed_id === shed.id }"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <div class="font-medium text-slate-800">{{ shed.name }}</div>
                    <div class="text-xs text-slate-500">ID: {{ shed.id }}</div>
                  </div>
                  <div v-if="form.to_shed_id === shed.id" class="text-blue-600">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  </div>
                </div>
              </button>
            </div>
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
            <Button 
              type="button" 
              variant="outline" 
              @click="showShedOverlay = false"
              class="px-4 py-2"
            >
              Cancel
            </Button>
            <Button 
              type="button" 
              @click="showShedOverlay = false"
              class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white"
            >
              Confirm
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Flock Selection Overlay -->
    <div v-if="showFlockOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
      <div class="bg-white rounded-2xl p-6 shadow-2xl border border-white/20 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xl font-bold text-slate-800">Select Flock No</h3>
          <Button
            type="button"
            @click="showFlockOverlay = false"
            variant="outline"
            class="h-8 w-8 p-0 rounded-full"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </Button>
        </div>

        <div class="space-y-3">
          <div>
            <Label class="text-sm font-semibold text-slate-700">Available Flocks</Label>
            <div class="max-h-60 overflow-y-auto space-y-2">
              <button
                v-for="flock in props.flocks"
                :key="flock.id"
                @click="form.flock_id = flock.id; showFlockOverlay = false"
                class="w-full text-left p-3 rounded-lg border border-slate-200 hover:bg-blue-50 hover:border-blue-300 transition-colors"
                :class="{ 'bg-blue-100 border-blue-300': form.flock_id === flock.id }"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <div class="font-medium text-slate-800">{{ flock.name }}</div>
                    <div class="text-xs text-slate-500">ID: {{ flock.id }}</div>
                  </div>
                  <div v-if="form.flock_id === flock.id" class="text-blue-600">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  </div>
                </div>
              </button>
            </div>
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
            <Button 
              type="button" 
              variant="outline" 
              @click="showFlockOverlay = false"
              class="px-4 py-2"
            >
              Cancel
            </Button>
            <Button 
              type="button" 
              @click="showFlockOverlay = false"
              class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white"
            >
              Confirm
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Batch Selection Overlay -->
    <div v-if="showBatchOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
      <div class="bg-white rounded-2xl p-6 shadow-2xl border border-white/20 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xl font-bold text-slate-800">Select Batch</h3>
          <Button
            type="button"
            @click="showBatchOverlay = false"
            variant="outline"
            class="h-8 w-8 p-0 rounded-full"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </Button>
        </div>

        <div class="space-y-3">
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-slate-700">Available Batches</Label>
            <div class="max-h-60 overflow-y-auto space-y-2">
              <button
                v-for="batch in props.batches"
                :key="batch.id"
                @click="form.batch_assign_id = batch.id; showBatchOverlay = false"
                class="w-full text-left p-3 rounded-lg border border-slate-200 hover:bg-blue-50 hover:border-blue-300 transition-colors"
                :class="{ 'bg-blue-100 border-blue-300': form.batch_assign_id === batch.id }"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <div class="font-medium text-slate-800">{{ batch.name }}</div>
                    <div class="text-xs text-slate-500">ID: {{ batch.id }}</div>
                  </div>
                  <div v-if="form.batch_assign_id === batch.id" class="text-blue-600">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  </div>
                </div>
              </button>
            </div>
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
            <Button 
              type="button" 
              variant="outline" 
              @click="showBatchOverlay = false"
              class="px-4 py-2"
            >
              Cancel
            </Button>
            <Button 
              type="button" 
              @click="showBatchOverlay = false"
              class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white"
            >
              Confirm
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Company Selection Overlay -->
    <div v-if="showCompanyOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
      <div class="bg-white rounded-2xl p-6 shadow-2xl border border-white/20 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xl font-bold text-slate-800">Select Destination Company</h3>
          <Button
            type="button"
            @click="showCompanyOverlay = false"
            variant="outline"
            class="h-8 w-8 p-0 rounded-full"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </Button>
        </div>

        <div class="space-y-3">
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-slate-700">Available Companies</Label>
            <div class="max-h-60 overflow-y-auto space-y-2">
              <button
                v-for="company in props.companies"
                :key="company.id"
                @click="form.to_company_id = company.id; showCompanyOverlay = false"
                class="w-full text-left p-3 rounded-lg border border-slate-200 hover:bg-blue-50 hover:border-blue-300 transition-colors"
                :class="{ 'bg-blue-100 border-blue-300': form.to_company_id === company.id }"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <div class="font-medium text-slate-800">{{ company.name }}</div>
                    <div class="text-xs text-slate-500">{{ company.code }} • {{ company.location }}</div>
                  </div>
                  <div v-if="form.to_company_id === company.id" class="text-blue-600">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  </div>
                </div>
              </button>
            </div>
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
            <Button 
              type="button" 
              variant="outline" 
              @click="showCompanyOverlay = false"
              class="px-4 py-2"
            >
              Cancel
            </Button>
            <Button 
              type="button" 
              @click="showCompanyOverlay = false"
              class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white"
            >
              Confirm
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Company Project Overlay -->
    <div v-if="showProjectOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
      <div class="bg-white rounded-2xl p-6 shadow-2xl border border-white/20 w-full max-w-md mx-4">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-xl font-bold text-slate-800">Select Destination Project</h3>
          <Button
            type="button"
            @click="showProjectOverlay = false"
            variant="outline"
            class="h-8 w-8 p-0 rounded-full"
          >
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </Button>
        </div>

        <div class="space-y-3">
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-slate-700">Available Companies</Label>
            <div class="max-h-60 overflow-y-auto space-y-2">
              <button
                v-for="project in projectOptions"
                :key="project.id"
                @click="form.to_project_id = project.id; showProjectOverlay = false"
                class="w-full text-left p-3 rounded-lg border border-slate-200 hover:bg-blue-50 hover:border-blue-300 transition-colors"
                :class="{ 'bg-blue-100 border-blue-300': form.to_project_id === project.id }"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <div class="font-medium text-slate-800">{{ project.name }}</div>
                    <div class="text-xs text-slate-500">{{ project.code }} • {{ project.location }}</div>
                  </div>
                  <div v-if="form.to_project_id === project.id" class="text-blue-600">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                  </div>
                </div>
              </button>
            </div>
          </div>

          <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
            <Button 
              type="button" 
              variant="outline" 
              @click="showProjectOverlay = false"
              class="px-4 py-2"
            >
              Cancel
            </Button>
            <Button 
              type="button" 
              @click="showProjectOverlay = false"
              class="px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white"
            >
              Confirm
            </Button>
          </div>
        </div>
      </div>
    </div>

  </AppLayout>
</template>
