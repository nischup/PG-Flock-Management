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
  projects: Array<any>
  breeds: Object
}>()

function getBreedNames(ids: any) {
  if (!ids || ids.length === 0) return ''
  return ids.map((id: any) => (props.breeds as any)[id] || '').filter(Boolean).join(', ')
}

// Main form state
const selectedPsId = ref<number | string>(props.psFirmReceive.ps_receive_id || '')
const selectedFlockId = ref<number | string>(props.psFirmReceive.flock_id || '')
const selectedCompanyId = ref<number | string>(props.psFirmReceive.receiving_company_id || '')
const selectedProjectId = ref<number | string>(props.psFirmReceive.project_id || '')
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
  project_id: props.psFirmReceive.project_id || 0,
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
const showCompanyDropdown = ref(false)
const showProjectDropdown = ref(false)
const companySearchQuery = ref('')
const projectSearchQuery = ref('')

// Filtered options
const filteredCompanies = computed(() => {
    if (!companySearchQuery.value) return props.companies
    return props.companies.filter(company => 
        company.name.toLowerCase().includes(companySearchQuery.value.toLowerCase())
    )
})

const filteredProjects = computed(() => {
    // First filter by selected company ID
    let projects = props.projects
    if (selectedCompanyId.value) {
        projects = props.projects.filter(project => 
            project.company_id === Number(selectedCompanyId.value)
        )
    }
    
    // Then filter by search query
    if (!projectSearchQuery.value) return projects
    return projects.filter(project => 
        project.name.toLowerCase().includes(projectSearchQuery.value.toLowerCase())
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

const selectedProject = computed(() => {
    return props.projects.find(project => project.id === Number(selectedProjectId.value))
})

// Auto-calculate total
watch([() => form.firm_female_box_qty, () => form.firm_male_box_qty], () => {
    form.firm_total_box_qty = Number(form.firm_female_box_qty) + Number(form.firm_male_box_qty)
})

// Watch for company selection changes
watch(selectedCompanyId, (newCompanyId) => {
    form.receiving_company_id = newCompanyId
    
    // Clear selected project if it doesn't belong to the new company
    if (selectedProjectId.value) {
        const currentProject = props.projects.find(project => project.id === Number(selectedProjectId.value))
        if (currentProject && currentProject.company_id !== Number(newCompanyId)) {
            selectedProjectId.value = ''
            form.project_id = 0
        }
    }
})

// Watch for project selection changes
watch(selectedProjectId, (newProjectId) => {
    form.project_id = newProjectId
})

// Close dropdowns on outside click
const handleClick = (e: MouseEvent) => {
    if (!(e.target as HTMLElement).closest('.company-dropdown, .project-dropdown')) {
        showCompanyDropdown.value = false
        showProjectDropdown.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClick)
    
    // Initialize PS receive info display
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
    
    <!-- Header Section -->
    <div class="mb-4">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Edit Farm Receive</h1>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Update parent stock farm receive record</p>
          <div class="mt-1 flex items-center gap-2">
            <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-200">
              Job No: {{ psFirmReceive.job_no }}
            </span>
          </div>
        </div>
        <Link 
          :href="route('ps-firm-receive.index')" 
          class="group relative overflow-hidden rounded-xl px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl"
          style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);"
        >
          <span class="relative z-10 flex items-center gap-2">
            <ArrowLeft class="h-4 w-4" />
            Back to List
          </span>
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-20 group-hover:translate-x-full"></div>
        </Link>
      </div>
    </div>

    <form @submit.prevent="submit" class="space-y-4">
      <!-- Parent Stock Selection Card -->
      <div class="relative rounded-xl border-0 bg-gradient-to-br from-white via-gray-50 to-white p-4 shadow-lg ring-1 ring-gray-200 dark:from-gray-800 dark:via-gray-900 dark:to-gray-800 dark:ring-gray-700">
        <div class="absolute -right-2 -top-2 h-16 w-16 rounded-full bg-gradient-to-br from-blue-500/20 to-purple-500/20"></div>
        <div class="absolute -bottom-2 -left-2 h-20 w-20 rounded-full bg-gradient-to-br from-emerald-500/10 to-blue-500/10"></div>
        
        <div class="relative">
          <div class="mb-4 flex items-center gap-2">
            <div class="rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 p-2 shadow-md">
              <Package class="h-4 w-4 text-white" />
            </div>
            <div>
              <h2 class="text-lg font-bold text-gray-900 dark:text-white">Parent Stock Information</h2>
              <p class="text-sm text-gray-600 dark:text-gray-400">Select PS receive and flock details</p>
            </div>
          </div>
          
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            
            <!-- PS Receive Dropdown (Disabled) -->
            <div class="space-y-2">
              <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                <Info class="h-4 w-4" />
                PS Receive Number
              </Label>
              <div class="ps-dropdown relative">
                <div
                  class="flex w-full items-center justify-between rounded-xl border border-gray-300 bg-gray-100 px-4 py-3 shadow-sm cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"
                >
                  <span class="flex items-center gap-3">
                    <div class="h-2 w-2 rounded-full bg-gray-400"></div>
                    {{ selectedPs ? (selectedPs.shipment_type_id === 1 ? ( "Invoice No - " + (selectedPs.order_no || 'N/A')) : ( "LC NO - " + (selectedPs.lc_no || 'N/A'))) + ' - ' + (selectedPs.shipment_type_id === 1 ? 'Local' : 'Foreign') : 'No PS Receive Selected' }}
                  </span>
                  <ChevronDown class="h-4 w-4 text-gray-400" />
                </div>
              </div>
              <InputError :message="form.errors.ps_receive_id" class="mt-1" />
            </div>

            <!-- Flock Dropdown (Disabled) -->
            <div class="space-y-2">
              <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                <Users class="h-4 w-4" />
                Flock Selection
              </Label>
              <div class="flock-dropdown relative">
                <div
                  class="flex w-full items-center justify-between rounded-xl border border-gray-300 bg-gray-100 px-4 py-3 shadow-sm cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-gray-400"
                >
                  <span class="flex items-center gap-3">
                    <div class="h-2 w-2 rounded-full bg-gray-400"></div>
                    {{ selectedFlock ? selectedFlock.code : 'No Flock Selected' }}
                  </span>
                  <ChevronDown class="h-4 w-4 text-gray-400" />
                </div>
              </div>
              <InputError :message="form.errors.flock_id" class="mt-1" />
            </div>
          </div>

          <!-- PS Receive Info -->
          <transition 
            enter-active-class="transition-all duration-300 ease-out"
            leave-active-class="transition-all duration-300 ease-in"
            enter-from-class="opacity-0 scale-95 -translate-y-2"
            enter-to-class="opacity-100 scale-100 translate-y-0"
            leave-from-class="opacity-100 scale-100 translate-y-0"
            leave-to-class="opacity-0 scale-95 -translate-y-2"
          >
            <div v-if="selectedPs && showInfo" class="mt-4 rounded-lg border border-blue-200 bg-gradient-to-br from-blue-50 to-indigo-50 p-4 dark:border-blue-800 dark:from-blue-900/20 dark:to-indigo-900/20">
              <h3 class="mb-3 flex items-center gap-2 text-base font-semibold text-blue-900 dark:text-blue-100">
                <CheckCircle2 class="h-4 w-4" />
                Parent Stock Details
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 text-sm">
                <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Shipment Type:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedPs.shipment_type_id === 1 ? 'Local' : 'Foreign' }}</div></div>
                <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">PI No:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedPs.pi_no }}</div></div>
                <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">LC No:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedPs.lc_no || 'N/A' }}</div></div>
                <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Order No:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedPs.order_no || 'N/A' }}</div></div>
                <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Receive Type:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedPs.receive_type || 'box' }}</div></div>
                <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Breed:</span><div class="text-gray-900 dark:text-gray-100">{{ getBreedNames(selectedPs.breed_type) || 'N/A' }}</div></div>
                <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Transport:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedPs.transport_type || 'N/A' }}</div></div>
                <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Challan Box:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedPs.ps_challan_box_qty || 0 }}</div></div>
                <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Female Receive Box:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedPs.female_box_qty || 0 }}</div></div>
                <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Male Receive Box:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedPs.male_box_qty || 0 }}</div></div>
                <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Gross Weight:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedPs.gross_weight || 0 }} kg</div></div>
                <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Net Weight:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedPs.net_weight || 0 }} kg</div></div>
                <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Female Chicks:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedPs.female_chicks || 0 }}</div></div>
                <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Male Chicks:</span><div class="text-gray-900 dark:text-gray-100">{{ selectedPs.male_chicks || 0 }}</div></div>
                <div class="space-y-1"><span class="font-semibold text-gray-600 dark:text-gray-300">Total Chicks:</span><div class="font-bold text-blue-900 dark:text-blue-100">{{ selectedPs.total_chicks_qty || 0 }}</div></div>
              </div>
              
              <!-- Lab Test Status -->
              <div v-if="isLabData" class="mt-4 flex items-center gap-2 rounded-lg bg-green-100 p-3 dark:bg-green-900/20">
                <CheckCircle2 class="h-5 w-5 text-green-600" />
                <span class="text-sm font-medium text-green-800 dark:text-green-200">{{ labMessage }}</span>
              </div>
              <div v-else class="mt-4 flex items-center gap-2 rounded-lg bg-yellow-100 p-3 dark:bg-yellow-900/20">
                <AlertCircle class="h-5 w-5 text-yellow-600" />
                <span class="text-sm font-medium text-yellow-800 dark:text-yellow-200">{{ labMessage }}</span>
              </div>
            </div>
          </transition>
        </div>
      </div>

      <!-- Company & Boxes Card -->
      <div class="relative overflow-hidden rounded-xl border-0 bg-gradient-to-br from-white via-orange-50 to-white p-4 shadow-lg ring-1 ring-gray-200 dark:from-gray-800 dark:via-orange-900/20 dark:to-gray-800 dark:ring-gray-700">
        <div class="absolute -right-2 -top-2 h-16 w-16 rounded-full bg-gradient-to-br from-orange-500/20 to-red-500/20"></div>
        <div class="absolute -bottom-2 -left-2 h-20 w-20 rounded-full bg-gradient-to-br from-yellow-500/10 to-orange-500/10"></div>
        
        <div class="relative">
          <div class="mb-4 flex items-center gap-2">
            <div class="rounded-lg bg-gradient-to-br from-orange-500 to-orange-600 p-2 shadow-md">
              <Building2 class="h-4 w-4 text-white" />
            </div>
            <div>
              <h2 class="text-lg font-bold text-gray-900 dark:text-white">Receiving Company & Boxes</h2>
              <p class="text-sm text-gray-600 dark:text-gray-400">Enter box quantities and receiving details</p>
            </div>
          </div>

          <!-- Company & Project Selection -->
          <div class="mb-4">
            <h3 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Receiving Details</h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
              <!-- Company Selection -->
              <div class="space-y-2">
                <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                  <Building2 class="h-4 w-4" />
                  Receiving Company
                </Label>
                <div class="company-dropdown relative">
                  <button
                    type="button"
                    @click.stop="showCompanyDropdown = !showCompanyDropdown"
                    class="flex w-full items-center justify-between rounded-xl border border-gray-300 bg-white px-4 py-3 shadow-sm transition-all duration-200 hover:border-orange-500 hover:shadow-md focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                  >
                    <span class="flex items-center gap-3">
                      <div class="h-2 w-2 rounded-full bg-orange-500"></div>
                      {{ selectedCompany ? selectedCompany.name : 'Select Company' }}
                    </span>
                    <ChevronDown class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showCompanyDropdown }" />
                  </button>
                  
                  <!-- Company Dropdown Overlay -->
                  <div 
                    v-if="showCompanyDropdown" 
                    class="fixed inset-0 z-[9997] flex items-start justify-center pt-20"
                    @click="showCompanyDropdown = false"
                  >
                    <div 
                      class="w-full max-w-md rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-600 dark:bg-gray-800"
                      @click.stop
                    >
                      <!-- Header -->
                      <div class="border-b border-gray-200 p-4 dark:border-gray-600">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Select Company</h3>
                        <div class="relative mt-3">
                          <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                          <input
                            v-model="companySearchQuery"
                            type="text"
                            placeholder="Search companies..."
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 pl-10 pr-4 py-2 text-sm focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            @click.stop
                          />
                        </div>
                      </div>

                      <!-- Company List -->
                      <div class="max-h-96 overflow-y-auto">
                        <div v-if="(props.companies?.length || 0) === 0" class="px-6 py-8 text-center">
                          <AlertCircle class="mx-auto h-8 w-8 text-red-500" />
                          <div class="mt-2 font-medium text-red-600">No Companies Available</div>
                          <div class="text-sm text-gray-500">Please add companies first</div>
                        </div>
                        <button
                          v-for="company in filteredCompanies"
                          :key="company.id"
                          type="button"
                          @click.stop="selectedCompanyId = company.id; showCompanyDropdown = false"
                          class="flex w-full items-center gap-4 px-6 py-4 text-left hover:bg-orange-50 dark:hover:bg-gray-700 transition-colors duration-200 border-b border-gray-100 dark:border-gray-600 last:border-b-0"
                          :class="{ 'bg-orange-100 dark:bg-orange-900': selectedCompanyId == company.id }"
                        >
                          <div class="h-3 w-3 rounded-full bg-orange-500 flex-shrink-0"></div>
                          <div class="flex-1">
                            <div class="font-semibold text-gray-900 dark:text-white">{{ company.name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">ID: {{ company.id }}</div>
                          </div>
                          <CheckCircle2 v-if="selectedCompanyId == company.id" class="h-4 w-4 text-orange-500 flex-shrink-0" />
                        </button>
                        <div v-if="filteredCompanies.length === 0 && (props.companies?.length || 0) > 0" class="px-6 py-8 text-center text-gray-500">
                          <Search class="mx-auto h-6 w-6 text-gray-400" />
                          <div class="mt-2 text-sm">No results found for "{{ companySearchQuery }}"</div>
                        </div>
                      </div>

                      <!-- Close Button -->
                      <div class="border-t border-gray-200 p-4 dark:border-gray-600">
                        <Button 
                          type="button"
                          @click="showCompanyDropdown = false"
                          class="w-full rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                        >
                          Close
                        </Button>
                      </div>
                    </div>
                  </div>
                </div>
                <InputError :message="form.errors.receiving_company_id" class="mt-1" />
              </div>
              
              <!-- Project Selection -->
              <div class="space-y-2">
                <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                  <Building2 class="h-4 w-4" />
                  Project
                </Label>
                <div class="project-dropdown relative">
                  <button
                    type="button"
                    @click.stop="selectedCompanyId && (showProjectDropdown = !showProjectDropdown)"
                    :disabled="!selectedCompanyId"
                    class="flex w-full items-center justify-between rounded-xl border border-gray-300 px-4 py-3 shadow-sm transition-all duration-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    :class="selectedCompanyId 
                      ? 'bg-white hover:border-purple-500 hover:shadow-md focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20' 
                      : 'bg-gray-100 cursor-not-allowed text-gray-400 dark:bg-gray-800 dark:text-gray-500'"
                  >
                    <span class="flex items-center gap-3">
                      <div class="h-2 w-2 rounded-full bg-purple-500"></div>
                      {{ selectedProject ? selectedProject.name : (selectedCompanyId ? 'Select Project' : 'Select Company First') }}
                    </span>
                    <ChevronDown class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showProjectDropdown }" />
                  </button>
                  
                  <!-- Project Dropdown Overlay -->
                  <div 
                    v-if="showProjectDropdown" 
                    class="fixed inset-0 z-[9996] flex items-start justify-center pt-20"
                    @click="showProjectDropdown = false"
                  >
                    <div 
                      class="w-full max-w-md rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-600 dark:bg-gray-800"
                      @click.stop
                    >
                      <!-- Header -->
                      <div class="border-b border-gray-200 p-4 dark:border-gray-600">
                        <h3 class="font-semibold text-gray-900 dark:text-white">Select Project</h3>
                        <div class="relative mt-3">
                          <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                          <input
                            v-model="projectSearchQuery"
                            type="text"
                            placeholder="Search projects..."
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 pl-10 pr-4 py-2 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            @click.stop
                          />
                        </div>
                      </div>

                      <!-- Project List -->
                      <div class="max-h-96 overflow-y-auto">
                        <div v-if="(props.projects?.length || 0) === 0" class="px-6 py-8 text-center">
                          <AlertCircle class="mx-auto h-8 w-8 text-red-500" />
                          <div class="mt-2 font-medium text-red-600">No Projects Available</div>
                          <div class="text-sm text-gray-500">Please add projects first</div>
                        </div>
                        <div v-else-if="filteredProjects.length === 0 && selectedCompanyId" class="px-6 py-8 text-center">
                          <AlertCircle class="mx-auto h-8 w-8 text-yellow-500" />
                          <div class="mt-2 font-medium text-yellow-600">No Projects for Selected Company</div>
                          <div class="text-sm text-gray-500">Please select a different company or add projects for this company</div>
                        </div>
                        <button
                          v-for="project in filteredProjects"
                          :key="project.id"
                          type="button"
                          @click.stop="selectedProjectId = project.id; showProjectDropdown = false"
                          class="flex w-full items-center gap-4 px-6 py-4 text-left hover:bg-purple-50 dark:hover:bg-gray-700 transition-colors duration-200 border-b border-gray-100 dark:border-gray-600 last:border-b-0"
                          :class="{ 'bg-purple-100 dark:bg-purple-900': selectedProjectId == project.id }"
                        >
                          <div class="h-3 w-3 rounded-full bg-purple-500 flex-shrink-0"></div>
                          <div class="flex-1">
                            <div class="font-semibold text-gray-900 dark:text-white">{{ project.name }}</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">ID: {{ project.id }}</div>
                          </div>
                          <CheckCircle2 v-if="selectedProjectId == project.id" class="h-4 w-4 text-purple-500 flex-shrink-0" />
                        </button>
                        <div v-if="filteredProjects.length === 0 && (props.projects?.length || 0) > 0" class="px-6 py-8 text-center text-gray-500">
                          <Search class="mx-auto h-6 w-6 text-gray-400" />
                          <div class="mt-2 text-sm">No results found for "{{ projectSearchQuery }}"</div>
                        </div>
                      </div>

                      <!-- Close Button -->
                      <div class="border-t border-gray-200 p-4 dark:border-gray-600">
                        <Button 
                          type="button"
                          @click="showProjectDropdown = false"
                          class="w-full rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                        >
                          Close
                        </Button>
                      </div>
                    </div>
                  </div>
                </div>
                <InputError :message="form.errors.project_id" class="mt-1" />
              </div>
            </div>
          </div>

          <!-- Main Box Quantities -->
          <div class="mb-4">
            <h3 class="mb-3 text-base font-semibold text-gray-900 dark:text-white">Main Box Quantities</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div class="space-y-2">
                <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Female Box Qty</Label>
                <Input 
                  v-model.number="form.firm_female_box_qty" 
                  type="number" 
                  min="0"
                  class="rounded-xl border-pink-300 bg-pink-50 px-4 py-3 shadow-sm focus:border-pink-500 focus:ring-pink-500/20 dark:border-pink-600 dark:bg-pink-900/20" 
                />
                <InputError :message="form.errors.firm_female_box_qty" class="mt-1" />
              </div>
              <div class="space-y-2">
                <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Male Box Qty</Label>
                <Input 
                  v-model.number="form.firm_male_box_qty" 
                  type="number" 
                  min="0"
                  class="rounded-xl border-blue-300 bg-blue-50 px-4 py-3 shadow-sm focus:border-blue-500 focus:ring-blue-500/20 dark:border-blue-600 dark:bg-blue-900/20" 
                />
                <InputError :message="form.errors.firm_male_box_qty" class="mt-1" />
              </div>
              <div class="space-y-2">
                <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Total Box Qty</Label>
                <Input 
                  v-model.number="form.firm_total_box_qty" 
                  type="number" 
                  readonly 
                  class="rounded-xl border-gray-300 bg-gradient-to-r from-gray-100 to-gray-50 px-4 py-3 font-bold text-gray-700 shadow-sm cursor-not-allowed dark:border-gray-600 dark:from-gray-700 dark:to-gray-800 dark:text-gray-300" 
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Notes Section -->
      <div class="relative overflow-hidden rounded-xl border-0 bg-gradient-to-br from-white via-amber-50 to-white p-4 shadow-lg ring-1 ring-gray-200 dark:from-gray-800 dark:via-amber-900/20 dark:to-gray-800 dark:ring-gray-700">
        <div class="absolute -right-2 -top-2 h-16 w-16 rounded-full bg-gradient-to-br from-amber-500/20 to-orange-500/20"></div>
        <div class="absolute -bottom-2 -left-2 h-20 w-20 rounded-full bg-gradient-to-br from-yellow-500/10 to-amber-500/10"></div>
        
        <div class="relative">
          <div class="mb-4 flex items-center gap-2">
            <div class="rounded-lg bg-gradient-to-br from-amber-500 to-amber-600 p-2 shadow-md">
              <Info class="h-4 w-4 text-white" />
            </div>
            <div>
              <h2 class="text-lg font-bold text-gray-900 dark:text-white">Additional Notes</h2>
              <p class="text-sm text-gray-600 dark:text-gray-400">Add any relevant remarks or observations</p>
            </div>
          </div>

          <div class="space-y-4">
            <div class="space-y-2">
              <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Remarks</Label>
              <textarea 
                v-model="form.remarks" 
                class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white resize-none transition-all duration-200" 
                rows="4" 
                placeholder="Write your notes here..."
              ></textarea>
              <InputError :message="form.errors.remarks" class="mt-1" />
            </div>
          </div>
        </div>
      </div>

      <!-- Submit Section -->
      <div class="flex items-center justify-end gap-4 rounded-xl bg-gradient-to-r from-gray-50 to-white p-4 dark:from-gray-800 dark:to-gray-900">
        <Link 
          :href="route('ps-firm-receive.index')"
          class="rounded-xl border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
        >
          Cancel
        </Link>
        <Button 
          type="submit" 
          :disabled="form.processing"
          class="group relative overflow-hidden rounded-xl bg-gradient-to-r from-gray-800 to-black px-8 py-3 font-semibold text-white shadow-2xl transition-all duration-300 hover:from-gray-900 hover:to-gray-800 hover:shadow-3xl focus:outline-none focus:ring-2 focus:ring-gray-500/50 disabled:opacity-50"
          style="background: linear-gradient(135deg, #1f2937 0%, #000000 50%, #374151 100%); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.1);"
        >
          <span class="relative z-10 flex items-center gap-2">
            <Save class="h-4 w-4" />
            {{ form.processing ? 'Updating...' : 'Update' }}
          </span>
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-10 group-hover:translate-x-full"></div>
        </Button>
      </div>
    </form>
  </AppLayout>
</template>
