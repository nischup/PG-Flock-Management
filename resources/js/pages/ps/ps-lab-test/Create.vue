<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import InputError from '@/components/InputError.vue'
import FileUploader from '@/components/FileUploader.vue'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { ChevronDown, FileText, FlaskConical, Package, Upload, AlertCircle, CheckCircle2 } from 'lucide-vue-next'
import type { BreadcrumbItem } from '@/types'

// Props from controller
const props = defineProps<{ psReceives: Array<any> }>()

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Lab Tests', href: '/ps-lab-test' },
  { title: 'Create', href: '/ps-lab-test/create' },
]

// Form
const form = useForm({
  ps_receive_id: "",
  lab_type: 'Gov Lab',
  lab_send_female_qty: 0,
  lab_send_male_qty: 0,
  lab_send_total_qty: 0,
  mortality_qty: 0,
  status: 'receive', // default status
  file: [],
})

// Selected PS Receive & accordion toggle
const selectedPSReceive = ref<any>(null)
const showInfo = ref(false)

// Watch female/male to update total
watch(
  () => [form.lab_send_female_qty, form.lab_send_male_qty],
  () => {
    form.lab_send_total_qty =
      Number(form.lab_send_female_qty || 0) + Number(form.lab_send_male_qty || 0)
  }
)

// When PI No changes, show info in accordion
function onSelectPSReceive(psReceiveId: number) {
  const ps = props.psReceives.find(p => p.id === psReceiveId) || null
  selectedPSReceive.value = ps
  form.ps_receive_id = ps?.id || null
  showInfo.value = !!ps

  // If Lab Test exists, pre-fill form with its data
  if (ps?.labTest) {
    form.lab_type = ps.labTest.lab_type
    form.lab_send_female_qty = ps.labTest.lab_send_female_qty
    form.lab_send_male_qty = ps.labTest.lab_send_male_qty
    form.lab_send_total_qty = ps.labTest.lab_send_total_qty
    form.mortality_qty = ps.labTest.mortality_qty
    form.status = ps.labTest.status
  } else {
    // reset form if no Lab Test exists
    form.lab_type = 'Gov Lab'
    form.lab_send_female_qty = 0
    form.lab_send_male_qty = 0
    form.lab_send_total_qty = 0
    form.mortality_qty = 0
    form.status = 'receive'
    form.file = []
  }
}

// Check if Lab Test already exists for this PS Receive
const isExistingLabTest = computed(() => !!selectedPSReceive.value?.labTest)

// Submit form
function submit() {
  form.post(route('ps-lab-test.store'))
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Create Lab Test" />

        <!-- Header -->
        <div class="m-3 rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
            <div class="mb-8 relative">
                <!-- Back Button - Top Right -->
                <div class="absolute top-0 right-0">
                    <Button
                        type="button"
                        variant="outline"
                        @click="$inertia.visit('/ps-lab-test')"
                        class="group px-4 py-2 text-sm border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-gray-500/20 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800 transition-all duration-200"
                    >
                        <span class="flex items-center gap-2">
                            <svg class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                            </svg>
                            Back to List
                        </span>
                    </Button>
                </div>

                <!-- Centered Content -->
                <div class="text-center">
                    <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg">
                        <FlaskConical class="h-8 w-8" />
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create Lab Test</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Create a new lab test record for PS receive data</p>
                </div>
            </div>
        </div>

        <form @submit.prevent="submit" class="mx-3 space-y-6">
            <!-- PI Selection Card -->
            <div class="rounded-xl bg-white p-6 shadow-lg ring-1 ring-gray-200/50 dark:bg-gray-900 dark:ring-gray-700/50 transition-all duration-200 hover:shadow-xl hover:ring-gray-300/50 dark:hover:ring-gray-600/50">
                <!-- Card Header -->
                <div class="mb-6 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-md">
                        <Package class="h-5 w-5" />
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Select PS Receive</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Choose the parent stock receive record</p>
                    </div>
                </div>

      <!-- PI Selection -->
                <div class="space-y-4">
                    <div>
                        <Label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">PI Number</Label>
                        <div class="relative">
                            <select 
                                v-model="form.ps_receive_id" 
                                @change="onSelectPSReceive(Number(form.ps_receive_id))"
                                class="block w-full appearance-none rounded-lg border border-gray-300 bg-white px-4 py-3 pl-12 pr-10 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:focus:border-blue-400 transition-all duration-200"
                                :class="form.errors.ps_receive_id ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''"
                            >
                                <option value="">Select PI Number</option>
                                <option v-for="ps in props.psReceives" :key="ps.id" :value="ps.id">
                                    {{ ps.pi_no }} - {{ ps.order_no }}
                                </option>
          </select>
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                <FileText class="h-5 w-5 text-gray-400" />
                            </div>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                <ChevronDown class="h-5 w-5 text-gray-400" />
                            </div>
                        </div>
                        <InputError :message="form.errors.ps_receive_id" class="mt-2" />
        </div>

                    <!-- PS Receive Info Accordion -->
        <transition
                        enter-active-class="transition-all duration-500 ease-out"
                        leave-active-class="transition-all duration-300 ease-in"
                        enter-from-class="max-h-0 opacity-0 transform -translate-y-2"
                        enter-to-class="max-h-96 opacity-100 transform translate-y-0"
                        leave-from-class="max-h-96 opacity-100 transform translate-y-0"
                        leave-to-class="max-h-0 opacity-0 transform -translate-y-2"
                    >
                        <div v-if="showInfo" class="overflow-hidden rounded-lg bg-gradient-to-r from-blue-50 to-indigo-50 p-4 dark:from-blue-900/20 dark:to-indigo-900/20">
                            <h3 class="mb-3 text-sm font-medium text-gray-900 dark:text-white">PS Receive Information</h3>
                            <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2 lg:grid-cols-3">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-2 w-2 rounded-full bg-blue-500"></span>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">PI No:</span> 
                                    <span class="text-gray-900 dark:text-white">{{ selectedPSReceive?.pi_no }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-2 w-2 rounded-full bg-green-500"></span>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Order No:</span> 
                                    <span class="text-gray-900 dark:text-white">{{ selectedPSReceive?.order_no || '-' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-2 w-2 rounded-full bg-purple-500"></span>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Receive Date:</span> 
                                    <span class="text-gray-900 dark:text-white">{{ selectedPSReceive?.created_at }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-2 w-2 rounded-full bg-orange-500"></span>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Supplier:</span> 
                                    <span class="text-gray-900 dark:text-white">{{ selectedPSReceive?.supplier?.name || '-' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-2 w-2 rounded-full bg-teal-500"></span>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Total Chicks:</span> 
                                    <span class="text-gray-900 dark:text-white">{{ selectedPSReceive?.total_chicks_qty || '-' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex h-2 w-2 rounded-full bg-pink-500"></span>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">Total Box:</span> 
                                    <span class="text-gray-900 dark:text-white">{{ selectedPSReceive?.total_box_qty || '-' }}</span>
                                </div>
                            </div>
          </div>
        </transition>
      </div>

                <!-- Existing Lab Test Warning -->
                <div v-if="isExistingLabTest" class="mt-4 rounded-lg bg-red-50 border border-red-200 p-4 dark:bg-red-900/20 dark:border-red-800">
                    <div class="flex items-center gap-3">
                        <AlertCircle class="h-5 w-5 text-red-600 dark:text-red-400 flex-shrink-0" />
                        <div>
                            <h4 class="font-medium text-red-800 dark:text-red-200">Lab Test Already Exists</h4>
                            <p class="text-sm text-red-700 dark:text-red-300 mt-1">A lab test for this PI number already exists. You cannot create a duplicate record.</p>
                        </div>
                    </div>
                </div>
      </div>

            <!-- Lab Send Details Card -->
            <div class="rounded-xl bg-white p-6 shadow-lg ring-1 ring-gray-200/50 dark:bg-gray-900 dark:ring-gray-700/50 transition-all duration-200 hover:shadow-xl hover:ring-gray-300/50 dark:hover:ring-gray-600/50">
                <!-- Card Header -->
                <div class="mb-6 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-md">
                        <FlaskConical class="h-5 w-5" />
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Lab Send Details</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Enter lab test quantities and type</p>
                    </div>
                </div>

                <!-- Lab Type Selection Row -->
                <div class="mb-6">
                    <Label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Lab Type</Label>
                    <div class="max-w-md">
                        <div class="relative">
                            <select 
                                v-model="form.lab_type" 
                                :disabled="isExistingLabTest"
                                class="block w-full appearance-none rounded-lg border border-gray-300 bg-white px-4 py-3 pl-12 pr-10 text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:focus:border-blue-400 dark:disabled:bg-gray-700 transition-all duration-200"
                                :class="form.errors.lab_type ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''"
                            >
                                <option value="Gov Lab">Government Lab</option>
                                <option value="Provita Lab">Provita Lab</option>
                            </select>
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                                <FlaskConical class="h-5 w-5 text-gray-400" />
                            </div>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                                <ChevronDown class="h-5 w-5 text-gray-400" />
                            </div>
                        </div>
                        <InputError :message="form.errors.lab_type" class="mt-2" />
                    </div>
                </div>

                <!-- Quantity Fields Row -->
                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <!-- Female Quantity -->
                    <div class="space-y-2">
                        <Label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Female Quantity</Label>
                        <div class="relative">
                            <Input 
                                v-model.number="form.lab_send_female_qty" 
                                :disabled="isExistingLabTest"
                                type="number" 
                                min="0"
                                placeholder="Enter female quantity"
                                class="pl-10 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:focus:border-blue-400 dark:disabled:bg-gray-700 transition-all duration-200"
                                :class="form.errors.lab_send_female_qty ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''"
                            />
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <span class="text-pink-500 font-medium">♀</span>
                            </div>
                        </div>
                        <InputError :message="form.errors.lab_send_female_qty" class="mt-1" />
                    </div>

                    <!-- Male Quantity -->
                    <div class="space-y-2">
                        <Label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Male Quantity</Label>
                        <div class="relative">
                            <Input 
                                v-model.number="form.lab_send_male_qty" 
                                :disabled="isExistingLabTest"
                                type="number" 
                                min="0"
                                placeholder="Enter male quantity"
                                class="pl-10 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 disabled:bg-gray-100 disabled:text-gray-500 disabled:cursor-not-allowed dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:focus:border-blue-400 dark:disabled:bg-gray-700 transition-all duration-200"
                                :class="form.errors.lab_send_male_qty ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''"
                            />
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <span class="text-blue-500 font-medium">♂</span>
                            </div>
                        </div>
                        <InputError :message="form.errors.lab_send_male_qty" class="mt-1" />
                    </div>

                    <!-- Total Quantity (Auto-calculated) -->
                    <div class="space-y-2">
                        <Label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total Quantity</Label>
                        <div class="relative">
                            <Input 
                                v-model.number="form.lab_send_total_qty" 
                                readonly 
                                placeholder="Auto-calculated"
                                class="pl-10 bg-gray-50 rounded-lg border-gray-300 shadow-sm text-gray-900 font-medium dark:bg-gray-700 dark:border-gray-600 dark:text-white cursor-not-allowed"
                            />
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <CheckCircle2 class="h-5 w-5 text-green-500" />
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Automatically calculated from female + male quantities</p>
                    </div>
                </div>
          </div>
          
            <!-- File Upload Card -->
            <div class="rounded-xl bg-white p-6 shadow-lg ring-1 ring-gray-200/50 dark:bg-gray-900 dark:ring-gray-700/50 transition-all duration-200 hover:shadow-xl hover:ring-gray-300/50 dark:hover:ring-gray-600/50">
                <!-- Card Header -->
                <div class="mb-6 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-r from-purple-500 to-pink-600 text-white shadow-md">
                        <Upload class="h-5 w-5" />
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Attachments</h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Upload supporting documents (max 3 files)</p>
        </div>
      </div>

                <div class="space-y-4">
                    <FileUploader 
                        v-model="form.file" 
                        :disabled="isExistingLabTest" 
                        label="Upload Files" 
                        :max-files="3"
                        accept=".jpg,.jpeg,.png,.pdf" 
                        class="rounded-lg"
                    />
                    <InputError :message="form.errors.file" class="mt-2" />
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Supported formats: JPG, JPEG, PNG, PDF • Maximum file size: 5MB each
                    </div>
                </div>
      </div>

      <!-- Submit Button -->
            <div v-if="!isExistingLabTest" class="flex justify-center">
                <Button
                    type="submit"
                    :disabled="form.processing"
                    class="group relative overflow-hidden px-8 py-3 text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-gray-500/20 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                    style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%, #0f172a 100%); box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.1);"
                >
                    <span class="relative z-10 flex items-center gap-2">
                        <FlaskConical v-if="!form.processing" class="h-4 w-4 transition-transform duration-300 group-hover:rotate-12" />
                        <svg v-if="form.processing" class="h-4 w-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        {{ form.processing ? 'Creating...' : 'Create Lab Test' }}
                    </span>
                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-25 group-hover:translate-x-full transform -skew-x-12"></div>
                </Button>
      </div>
    </form>
  </AppLayout>
</template>
