<script setup lang="ts">
import { Link, Head, useForm } from '@inertiajs/vue3'
import { ref, watch, computed, onMounted, onBeforeUnmount } from 'vue'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { 
    ArrowLeft, 
    Package, 
    Building2, 
    Users, 
    Save, 
    Info, 
    ChevronDown,
    Search,
    X,
    AlertCircle,
    CheckCircle2
} from 'lucide-vue-next'

// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Parent Stock', href: '/ps-firm-receive' },
  { title: 'Edit Firm Receive', href: '' },
]

// Props
const props = defineProps<{
  psFirmReceive: any
  psReceives: Array<any>
  flocks: Array<any>
  companies: Array<any>
}>()

// Main form state
const selectedPsId = ref<number | string>(props.psFirmReceive.ps_receive_id || '')
const selectedFlockId = ref<number | string>(props.psFirmReceive.flock_id || '')
const selectedCompanyId = ref<number | string>(props.psFirmReceive.receiving_company_id || '')
const showInfo = ref(false)    
const isLabData = ref(false)
const labMessage = ref('') 
const labInput = ref(false)
const messageType = ref('') 

const form = useForm({
  ps_receive_id: props.psFirmReceive.ps_receive_id || '', 
  flock_id: props.psFirmReceive.flock_id || 0,
  job_no: props.psFirmReceive.job_no || '',
  receiving_company_id: props.psFirmReceive.receiving_company_id || 0,
  firm_female_box_qty: props.psFirmReceive.firm_female_qty || 0,
  firm_male_box_qty: props.psFirmReceive.firm_male_qty || 0,
  firm_total_box_qty: props.psFirmReceive.firm_total_qty || 0,
  firm_sortage_male_box: 0,
  firm_sortage_female_box: 0,
  firm_sortage_box_qty: 0,   // total shortage
  firm_excess_male_box: 0,
  firm_excess_female_box: 0,
  firm_excess_box_qty: 0,   
  remarks: props.psFirmReceive.remarks || '',
  status: props.psFirmReceive.status || 1,
  send_female_qty: 0,
  send_male_qty: 0,
  send_total_qty: 0,
  lab_type: 1,
})

// Company options
const shipmentTypes = ['Air', 'Sea', 'Road']
const suppliers = {1: 'PBL', 2: 'PCL'} 
const breeds = {1: 'Broiler', 2: 'Layer'}
const transports = {1: 'Freezing Microbus', 2: 'Freezing Van'}

// Modern dropdown states
const showPsDropdown = ref(false)
const showFlockDropdownList = ref(false)
const showCompanyDropdown = ref(false)
const psSearchQuery = ref('')
const flockSearchQuery = ref('')
const companySearchQuery = ref('')

// Filtered options
const filteredPsReceives = computed(() => {
    if (!psSearchQuery.value) return props.psReceives
    return props.psReceives.filter(ps => 
        ps.pi_no.toLowerCase().includes(psSearchQuery.value.toLowerCase())
    )
})

const filteredFlocks = computed(() => {
    if (!flockSearchQuery.value) return props.flocks
    return props.flocks.filter(flock => 
        flock.name.toLowerCase().includes(flockSearchQuery.value.toLowerCase())
    )
})

const filteredCompanies = computed(() => {
    if (!companySearchQuery.value) return props.companies
    return props.companies.filter(company => 
        company.name.toLowerCase().includes(companySearchQuery.value.toLowerCase())
    )
})

// Selected items display
const selectedPs = computed(() => {
    return props.psReceives.find(ps => ps.id === Number(selectedPsId.value))
})

const selectedFlock = computed(() => {
    return props.flocks.find(flock => flock.id === Number(selectedFlockId.value))
})

const selectedCompany = computed(() => {
    return props.companies.find(company => company.id === Number(selectedCompanyId.value))
})

// Auto-calculate total
watch([() => form.firm_female_box_qty, () => form.firm_male_box_qty], () => {
    form.firm_total_box_qty = Number(form.firm_female_box_qty) + Number(form.firm_male_box_qty)
})

// Watch for PS selection changes
watch(selectedPsId, (newPsId) => {
    form.ps_receive_id = newPsId
    if (selectedPs.value) {
        showInfo.value = true
        // Check if lab data exists
        isLabData.value = selectedPs.value.labTest && selectedPs.value.labTest.length > 0
        if (isLabData.value) {
            labMessage.value = 'Lab test data found for this PS Receive'
            messageType.value = 'success'
        } else {
            labMessage.value = 'No lab test data found'
            messageType.value = 'warning'
        }
    }
})

// Watch for flock selection changes
watch(selectedFlockId, (newFlockId) => {
    form.flock_id = newFlockId
})

// Watch for company selection changes
watch(selectedCompanyId, (newCompanyId) => {
    form.receiving_company_id = newCompanyId
})

// Close dropdowns on outside click
const handleClick = (e: MouseEvent) => {
    if (!(e.target as HTMLElement).closest('.ps-dropdown, .flock-dropdown, .company-dropdown')) {
        showPsDropdown.value = false
        showFlockDropdownList.value = false
        showCompanyDropdown.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClick)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClick)
})

// Submit form
const submit = () => {
    form.put(route('ps-firm-receive.update', props.psFirmReceive.id))
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Edit PS Firm Receive" />
    
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit PS Firm Receive</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Job No: {{ psFirmReceive.job_no }}
          </p>
        </div>
        <Link
          :href="route('ps-firm-receive.index')"
          class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
        >
          <ArrowLeft class="h-4 w-4 mr-2" />
          Back to List
        </Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- PS Receive Selection -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg p-6">
          <div class="flex items-center mb-4">
            <Package class="h-5 w-5 text-blue-600 mr-2" />
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">PS Receive Selection</h2>
          </div>
          
          <div class="space-y-4">
            <div class="relative">
              <Label for="ps_receive" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Select PS Receive *
              </Label>
              <div class="relative ps-dropdown">
                <button
                  type="button"
                  @click="showPsDropdown = !showPsDropdown"
                  class="w-full flex items-center justify-between px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-left focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                >
                  <span class="block truncate">
                    {{ selectedPs ? `PI-${selectedPs.pi_no}` : 'Select PS Receive' }}
                  </span>
                  <ChevronDown class="h-4 w-4 text-gray-400" />
                </button>
                
                <div v-if="showPsDropdown" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none dark:bg-gray-800">
                  <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                    <div class="relative">
                      <Search class="h-4 w-4 absolute left-3 top-3 text-gray-400" />
                      <Input
                        v-model="psSearchQuery"
                        placeholder="Search PS Receive..."
                        class="pl-10"
                        @click.stop
                      />
                    </div>
                  </div>
                  <div
                    v-for="ps in filteredPsReceives"
                    :key="ps.id"
                    @click="selectedPsId = ps.id; showPsDropdown = false"
                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-50 dark:hover:bg-gray-700"
                    :class="{ 'bg-blue-100 dark:bg-gray-700': selectedPsId === ps.id }"
                  >
                    <div class="flex items-center">
                      <span class="font-medium">PI-{{ ps.pi_no }}</span>
                      <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ ps.pi_date ? new Date(ps.pi_date).toLocaleDateString() : '' }}
                      </span>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                      Order: {{ ps.order_no || 'N/A' }} | LC: {{ ps.lc_no || 'N/A' }}
                    </div>
                  </div>
                </div>
              </div>
              <InputError :message="form.errors.ps_receive_id" class="mt-1" />
            </div>

            <!-- PS Receive Info -->
            <div v-if="selectedPs && showInfo" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
              <div class="flex items-start">
                <Info class="h-5 w-5 text-blue-600 mr-2 mt-0.5" />
                <div class="flex-1">
                  <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">PS Receive Information</h3>
                  <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                      <span class="font-medium text-blue-700 dark:text-blue-300">PI No:</span>
                      <span class="ml-2 text-blue-600 dark:text-blue-400">PI-{{ selectedPs.pi_no }}</span>
                    </div>
                    <div>
                      <span class="font-medium text-blue-700 dark:text-blue-300">Order No:</span>
                      <span class="ml-2 text-blue-600 dark:text-blue-400">{{ selectedPs.order_no || 'N/A' }}</span>
                    </div>
                    <div>
                      <span class="font-medium text-blue-700 dark:text-blue-300">LC No:</span>
                      <span class="ml-2 text-blue-600 dark:text-blue-400">{{ selectedPs.lc_no || 'N/A' }}</span>
                    </div>
                    <div>
                      <span class="font-medium text-blue-700 dark:text-blue-300">Total Chicks:</span>
                      <span class="ml-2 text-blue-600 dark:text-blue-400">{{ selectedPs.total_chicks_qty || 0 }}</span>
                    </div>
                  </div>
                  
                  <!-- Lab Test Status -->
                  <div v-if="isLabData" class="mt-3 flex items-center">
                    <CheckCircle2 class="h-4 w-4 text-green-600 mr-2" />
                    <span class="text-sm text-green-700 dark:text-green-300">{{ labMessage }}</span>
                  </div>
                  <div v-else class="mt-3 flex items-center">
                    <AlertCircle class="h-4 w-4 text-yellow-600 mr-2" />
                    <span class="text-sm text-yellow-700 dark:text-yellow-300">{{ labMessage }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Flock & Company Selection -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg p-6">
          <div class="flex items-center mb-4">
            <Users class="h-5 w-5 text-green-600 mr-2" />
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Flock & Company Selection</h2>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Flock Selection -->
            <div class="relative">
              <Label for="flock" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Select Flock *
              </Label>
              <div class="relative flock-dropdown">
                <button
                  type="button"
                  @click="showFlockDropdownList = !showFlockDropdownList"
                  class="w-full flex items-center justify-between px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-left focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                >
                  <span class="block truncate">
                    {{ selectedFlock ? selectedFlock.name : 'Select Flock' }}
                  </span>
                  <ChevronDown class="h-4 w-4 text-gray-400" />
                </button>
                
                <div v-if="showFlockDropdownList" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none dark:bg-gray-800">
                  <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                    <div class="relative">
                      <Search class="h-4 w-4 absolute left-3 top-3 text-gray-400" />
                      <Input
                        v-model="flockSearchQuery"
                        placeholder="Search flock..."
                        class="pl-10"
                        @click.stop
                      />
                    </div>
                  </div>
                  <div
                    v-for="flock in filteredFlocks"
                    :key="flock.id"
                    @click="selectedFlockId = flock.id; showFlockDropdownList = false"
                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-50 dark:hover:bg-gray-700"
                    :class="{ 'bg-blue-100 dark:bg-gray-700': selectedFlockId === flock.id }"
                  >
                    <span class="block truncate">{{ flock.name }}</span>
                  </div>
                </div>
              </div>
              <InputError :message="form.errors.flock_id" class="mt-1" />
            </div>

            <!-- Company Selection -->
            <div class="relative">
              <Label for="company" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Select Company *
              </Label>
              <div class="relative company-dropdown">
                <button
                  type="button"
                  @click="showCompanyDropdown = !showCompanyDropdown"
                  class="w-full flex items-center justify-between px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-left focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                >
                  <span class="block truncate">
                    {{ selectedCompany ? selectedCompany.name : 'Select Company' }}
                  </span>
                  <ChevronDown class="h-4 w-4 text-gray-400" />
                </button>
                
                <div v-if="showCompanyDropdown" class="absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none dark:bg-gray-800">
                  <div class="px-3 py-2 border-b border-gray-200 dark:border-gray-700">
                    <div class="relative">
                      <Search class="h-4 w-4 absolute left-3 top-3 text-gray-400" />
                      <Input
                        v-model="companySearchQuery"
                        placeholder="Search company..."
                        class="pl-10"
                        @click.stop
                      />
                    </div>
                  </div>
                  <div
                    v-for="company in filteredCompanies"
                    :key="company.id"
                    @click="selectedCompanyId = company.id; showCompanyDropdown = false"
                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-50 dark:hover:bg-gray-700"
                    :class="{ 'bg-blue-100 dark:bg-gray-700': selectedCompanyId === company.id }"
                  >
                    <span class="block truncate">{{ company.name }}</span>
                  </div>
                </div>
              </div>
              <InputError :message="form.errors.receiving_company_id" class="mt-1" />
            </div>
          </div>
        </div>

        <!-- Quantity Information -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg p-6">
          <div class="flex items-center mb-4">
            <Package class="h-5 w-5 text-purple-600 mr-2" />
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Quantity Information</h2>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <Label for="firm_female_box_qty" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Female Box Quantity *
              </Label>
              <Input
                id="firm_female_box_qty"
                v-model="form.firm_female_box_qty"
                type="number"
                min="0"
                class="w-full"
                :class="{ 'border-red-500': form.errors.firm_female_box_qty }"
              />
              <InputError :message="form.errors.firm_female_box_qty" class="mt-1" />
            </div>
            
            <div>
              <Label for="firm_male_box_qty" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Male Box Quantity *
              </Label>
              <Input
                id="firm_male_box_qty"
                v-model="form.firm_male_box_qty"
                type="number"
                min="0"
                class="w-full"
                :class="{ 'border-red-500': form.errors.firm_male_box_qty }"
              />
              <InputError :message="form.errors.firm_male_box_qty" class="mt-1" />
            </div>
            
            <div>
              <Label for="firm_total_box_qty" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Total Box Quantity
              </Label>
              <Input
                id="firm_total_box_qty"
                v-model="form.firm_total_box_qty"
                type="number"
                min="0"
                readonly
                class="w-full bg-gray-50 dark:bg-gray-800"
              />
            </div>
          </div>
        </div>

        <!-- Additional Information -->
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg p-6">
          <div class="flex items-center mb-4">
            <Info class="h-5 w-5 text-orange-600 mr-2" />
            <h2 class="text-lg font-medium text-gray-900 dark:text-white">Additional Information</h2>
          </div>
          
          <div class="space-y-4">
            <div>
              <Label for="remarks" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Remarks
              </Label>
              <textarea
                id="remarks"
                v-model="form.remarks"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
                :class="{ 'border-red-500': form.errors.remarks }"
              ></textarea>
              <InputError :message="form.errors.remarks" class="mt-1" />
            </div>
            
            <div>
              <Label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Status
              </Label>
              <select
                id="status"
                v-model="form.status"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white"
              >
                <option :value="1">Active</option>
                <option :value="0">Inactive</option>
              </select>
              <InputError :message="form.errors.status" class="mt-1" />
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-3">
          <Link
            :href="route('ps-firm-receive.index')"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700"
          >
            Cancel
          </Link>
          <Button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <Save class="h-4 w-4 mr-2" />
            {{ form.processing ? 'Updating...' : 'Update Firm Receive' }}
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
