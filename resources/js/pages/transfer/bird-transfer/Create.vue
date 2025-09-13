<script setup lang="ts">
import { ref, computed } from 'vue'
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
  to_company_id: undefined,
  to_shed_id: undefined,

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

// Dropdown options
const flockOptions = computed(() => 
  props.flocks.map(flock => ({
    value: flock.id,
    label: flock.name
  }))
)

const companyOptions = computed(() => 
  props.companies.map(company => ({
    value: company.id,
    label: company.name
  }))
)

const shedOptions = computed(() => 
  props.sheds.map(shed => ({
    value: shed.shed_id,
    label: shed.name
  }))
)

// Submit
function submit() {
  if (!form.flock_id) return showInfo("Please select a flock")
  if (!form.to_company_id) return showInfo("Please select a transfer company")

  form.post(route('bird-transfer.store'), {
    onSuccess: () => showInfo("Bird transfer saved successfully")
  })
}
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
            <div class="flex items-center space-x-3 bg-white/80 backdrop-blur-sm rounded-2xl px-4 py-3 shadow-lg border border-white/20">
              <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
              <span class="text-sm font-medium text-slate-700">Batch #{{ batchAssign.id }}</span>
            </div>
          </div>
        </div>

        <div class="space-y-8">
          <!-- Current Status Section -->
          <div class="bg-white/80 backdrop-blur-sm rounded-3xl p-6 shadow-xl border border-white/20">
            <h3 class="text-xl font-bold text-slate-800 mb-6 flex items-center gap-2">
              <UserGroupIcon class="h-6 w-6 text-blue-600" />
              Current Status
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
              <div class="flex items-center justify-between p-4 bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl border border-amber-200">
                <div>
                  <p class="text-sm font-medium text-amber-700">Total Birds</p>
                  <p class="text-2xl font-bold text-amber-800">{{ form.total_bird }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
                  <UserGroupIcon class="h-6 w-6 text-amber-600" />
                </div>
              </div>

              <div class="flex items-center justify-between p-4 bg-gradient-to-r from-red-50 to-pink-50 rounded-2xl border border-red-200">
                <div>
                  <p class="text-sm font-medium text-red-700">Mortality</p>
                  <p class="text-2xl font-bold text-red-800">{{ form.total_bird - current_total_chicks }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                  <ExclamationTriangleIcon class="h-6 w-6 text-red-600" />
                </div>
              </div>

              <div class="p-4 bg-gradient-to-r from-emerald-50 to-green-50 rounded-2xl border border-emerald-200 text-center">
                <p class="text-sm font-medium text-emerald-700">Female</p>
                <p class="text-xl font-bold text-emerald-800">{{ current_female_chicks }}</p>
              </div>

              <div class="p-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-2xl border border-blue-200 text-center">
                <p class="text-sm font-medium text-blue-700">Male</p>
                <p class="text-xl font-bold text-blue-800">{{ current_male_chicks }}</p>
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
                <h2 class="text-2xl font-bold text-slate-800 mb-2">Transfer Configuration</h2>
                <p class="text-slate-600">Configure your bird transfer details</p>
              </div>

              <div class="space-y-8">
                <!-- Basic Info Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                  <div class="space-y-1">
                    <Label class="text-xs font-medium text-slate-600">Flock</Label>
                    <Select
                      v-model="form.flock_id"
                      :options="flockOptions"
                      placeholder="Select flock"
                      class="w-full h-9 text-sm"
                    />
                  </div>

                  <div class="space-y-1">
                    <Label class="text-xs font-medium text-slate-600">Batch</Label>
                    <Select
                      v-model="form.batch_assign_id"
                      :options="batchOptions"
                      placeholder="Select batch"
                      class="w-full h-9 text-sm"
                    />
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
                    <Select
                      v-model="form.to_company_id"
                      :options="companyOptions"
                      placeholder="Select company"
                      class="w-full h-9 text-sm"
                    />
                  </div>
                </div>

                <!-- Shed Selection -->
                <div v-if="showShed" class="space-y-1 max-w-xs">
                  <Label class="text-xs font-medium text-slate-600">Destination Shed</Label>
                  <Select
                    v-model="form.to_shed_id"
                    :options="shedOptions"
                    placeholder="Select shed"
                    class="w-full h-9 text-sm"
                  />
                </div>

                <!-- Transfer Quantities -->
                <div class="space-y-4">
                  <h3 class="text-base font-semibold text-slate-800 border-b border-slate-200 pb-2">Transfer Quantities</h3>
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
                        :value="total_transfer_chicks" 
                        type="number" 
                        readonly 
                        class="w-full h-9 text-sm bg-slate-50"
                      />
                    </div>
                  </div>
                </div>

                <!-- Medical Birds -->
                <div class="space-y-4">
                  <h3 class="text-base font-semibold text-slate-800 border-b border-slate-200 pb-2">Medical Birds</h3>
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
                        :value="total_medical_bird" 
                        type="number" 
                        readonly 
                        class="w-full h-9 text-sm bg-slate-50"
                      />
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
                  class="px-8 py-3 h-12 text-base font-semibold rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg hover:shadow-xl transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <PlusIcon class="mr-2 h-5 w-5" />
                  {{ form.processing ? 'Processing...' : 'Create Transfer' }}
                </Button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
