<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { 
  ArrowLeft, 
  FlaskConical, 
  Package, 
  Send, 
  Download, 
  FileText, 
  ChevronDown,
  Calculator,
  AlertCircle,
  CheckCircle2,
  Building2,
  Users
} from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

// Props
const props = defineProps<{
  batchAssigns: Array<{
    id: number;
    transaction_no: string;
    batch_no: string; // plain, no nested object
  }>;
}>();

// Form
const form = useForm({
  batch_assign_id: '',
  firm_lab_send_female_qty: 0,
  firm_lab_send_male_qty: 0,
  firm_lab_send_total_qty: 0,
  firm_lab_receive_female_qty: 0,
  firm_lab_receive_male_qty: 0,
  firm_lab_receive_total_qty: 0,
  note: '',
  remarks: '',
});

// State
const selectedBatch = ref<any>(null);
const showBatchInfo = ref(false);

// Auto-calculate totals
watch(
  () => [form.firm_lab_send_female_qty, form.firm_lab_send_male_qty],
  () => {
    form.firm_lab_send_total_qty = 
      Number(form.firm_lab_send_female_qty || 0) + Number(form.firm_lab_send_male_qty || 0);
  }
);

watch(
  () => [form.firm_lab_receive_female_qty, form.firm_lab_receive_male_qty],
  () => {
    form.firm_lab_receive_total_qty = 
      Number(form.firm_lab_receive_female_qty || 0) + Number(form.firm_lab_receive_male_qty || 0);
  }
);

// When batch changes, show info
function onSelectBatch(batchId: number) {
  selectedBatch.value = props.batchAssigns.find(b => b.id === batchId);
  showBatchInfo.value = !!selectedBatch.value;
}

// Submit
const submit = () => {
  router.post(route('firm-lab-tests.store'), form);
};

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Firm Lab Tests', href: route('firm-lab-tests.index') },
  { title: 'Create', href: '' },
];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Create Firm Lab Test" />

    <!-- Header -->
    <div class="m-3 rounded-xl bg-white p-6 shadow-md dark:bg-gray-900">
      <div class="mb-8 relative">
        <!-- Back Button - Top Right -->
        <div class="absolute top-0 right-0">
          <Button
            type="button"
            variant="outline"
            @click="router.visit(route('firm-lab-tests.index'))"
            class="group px-4 py-2 text-sm border-gray-300 text-gray-700 hover:bg-gray-50 focus:ring-2 focus:ring-gray-500/20 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800 transition-all duration-200"
          >
            <span class="flex items-center gap-2">
              <ArrowLeft class="h-4 w-4 transition-transform duration-200 group-hover:-translate-x-1" />
              Back to List
            </span>
          </Button>
        </div>

        <!-- Centered Content -->
        <div class="text-center">
          <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-lg">
            <FlaskConical class="h-8 w-8" />
          </div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create Firm Lab Test</h1>
          <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Create a new firm lab test record for batch assignment</p>
        </div>
      </div>
    </div>

    <form @submit.prevent="submit" class="mx-3 space-y-6">
      <!-- Single Form Card -->
      <div class="rounded-xl bg-white p-6 shadow-lg ring-1 ring-gray-200/50 dark:bg-gray-900 dark:ring-gray-700/50 transition-all duration-200 hover:shadow-xl hover:ring-gray-300/50 dark:hover:ring-gray-600/50">
        <!-- Card Header -->
        <div class="mb-6 flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-md">
            <FlaskConical class="h-5 w-5" />
          </div>
          <div>
            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Firm Lab Test Details</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400">Enter all lab test information in one form</p>
          </div>
        </div>

        <!-- Form Fields -->
        <div class="space-y-6">
          <!-- Batch Selection -->
          <div>
            <Label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Batch Assignment</Label>
            <div class="relative">
              <select
                v-model="form.batch_assign_id"
                @change="onSelectBatch(Number(form.batch_assign_id))"
                id="batch_assign_id"
                class="block w-full appearance-none rounded-lg border border-gray-300 bg-white px-3 py-2 pl-10 pr-8 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:focus:border-blue-400 transition-all duration-200"
                :class="form.errors.batch_assign_id ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''"
              >
                <option value="">Select Batch Assignment</option>
                <option
                  v-for="b in props.batchAssigns"
                  :key="b.id"
                  :value="b.id"
                >
                  {{ b.transaction_no }} - {{ b.batch_no }}
                </option>
              </select>
              <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                <Building2 class="h-4 w-4 text-gray-400" />
              </div>
              <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <ChevronDown class="h-4 w-4 text-gray-400" />
              </div>
            </div>
            <InputError :message="form.errors.batch_assign_id" class="mt-1" />
          </div>

          <!-- Batch Info Accordion -->
          <transition
            enter-active-class="transition-all duration-500 ease-out"
            leave-active-class="transition-all duration-300 ease-in"
            enter-from-class="max-h-0 opacity-0 transform -translate-y-2"
            enter-to-class="max-h-32 opacity-100 transform translate-y-0"
            leave-from-class="max-h-32 opacity-100 transform translate-y-0"
            leave-to-class="max-h-0 opacity-0 transform -translate-y-2"
          >
            <div v-if="showBatchInfo" class="overflow-hidden rounded-lg bg-gradient-to-r from-blue-50 to-indigo-50 p-3 dark:from-blue-900/20 dark:to-indigo-900/20">
              <h3 class="mb-2 text-xs font-medium text-gray-900 dark:text-white">Batch Information</h3>
              <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="flex items-center gap-1">
                  <span class="inline-flex h-1.5 w-1.5 rounded-full bg-blue-500"></span>
                  <span class="font-medium text-gray-700 dark:text-gray-300">Transaction:</span> 
                  <span class="text-gray-900 dark:text-white">{{ selectedBatch?.transaction_no }}</span>
                </div>
                <div class="flex items-center gap-1">
                  <span class="inline-flex h-1.5 w-1.5 rounded-full bg-green-500"></span>
                  <span class="font-medium text-gray-700 dark:text-gray-300">Batch No:</span> 
                  <span class="text-gray-900 dark:text-white">{{ selectedBatch?.batch_no }}</span>
                </div>
              </div>
            </div>
          </transition>

          <!-- Send Quantities Section -->
          <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <Send class="h-4 w-4 text-orange-500" />
              Send Quantities
            </h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
              <div class="space-y-1">
                <Label for="firm_lab_send_female_qty" class="text-xs font-medium text-gray-700 dark:text-gray-300">
                  Female Qty
                </Label>
                <div class="relative">
                  <Input 
                    v-model="form.firm_lab_send_female_qty" 
                    type="number" 
                    id="firm_lab_send_female_qty"
                    class="pl-8 py-2 text-sm"
                    :class="form.errors.firm_lab_send_female_qty ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''"
                  />
                  <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                    <Users class="h-3 w-3 text-gray-400" />
                  </div>
                </div>
                <InputError :message="form.errors.firm_lab_send_female_qty" />
              </div>

              <div class="space-y-1">
                <Label for="firm_lab_send_male_qty" class="text-xs font-medium text-gray-700 dark:text-gray-300">
                  Male Qty
                </Label>
                <div class="relative">
                  <Input 
                    v-model="form.firm_lab_send_male_qty" 
                    type="number" 
                    id="firm_lab_send_male_qty"
                    class="pl-8 py-2 text-sm"
                    :class="form.errors.firm_lab_send_male_qty ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''"
                  />
                  <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                    <Users class="h-3 w-3 text-gray-400" />
                  </div>
                </div>
                <InputError :message="form.errors.firm_lab_send_male_qty" />
              </div>

              <div class="space-y-1">
                <Label for="firm_lab_send_total_qty" class="text-xs font-medium text-gray-700 dark:text-gray-300">
                  Total Qty
                </Label>
                <div class="relative">
                  <Input 
                    v-model="form.firm_lab_send_total_qty" 
                    type="number" 
                    id="firm_lab_send_total_qty"
                    class="pl-8 py-2 text-sm bg-gray-50 dark:bg-gray-800"
                    readonly
                  />
                  <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                    <Calculator class="h-3 w-3 text-gray-400" />
                  </div>
                </div>
                <InputError :message="form.errors.firm_lab_send_total_qty" />
              </div>
            </div>
          </div>

          <!-- Receive Quantities Section -->
          <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <Download class="h-4 w-4 text-green-500" />
              Receive Quantities
            </h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
              <div class="space-y-1">
                <Label for="firm_lab_receive_female_qty" class="text-xs font-medium text-gray-700 dark:text-gray-300">
                  Female Qty
                </Label>
                <div class="relative">
                  <Input 
                    v-model="form.firm_lab_receive_female_qty" 
                    type="number" 
                    id="firm_lab_receive_female_qty"
                    class="pl-8 py-2 text-sm"
                    :class="form.errors.firm_lab_receive_female_qty ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''"
                  />
                  <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                    <Users class="h-3 w-3 text-gray-400" />
                  </div>
                </div>
                <InputError :message="form.errors.firm_lab_receive_female_qty" />
              </div>

              <div class="space-y-1">
                <Label for="firm_lab_receive_male_qty" class="text-xs font-medium text-gray-700 dark:text-gray-300">
                  Male Qty
                </Label>
                <div class="relative">
                  <Input 
                    v-model="form.firm_lab_receive_male_qty" 
                    type="number" 
                    id="firm_lab_receive_male_qty"
                    class="pl-8 py-2 text-sm"
                    :class="form.errors.firm_lab_receive_male_qty ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''"
                  />
                  <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                    <Users class="h-3 w-3 text-gray-400" />
                  </div>
                </div>
                <InputError :message="form.errors.firm_lab_receive_male_qty" />
              </div>

              <div class="space-y-1">
                <Label for="firm_lab_receive_total_qty" class="text-xs font-medium text-gray-700 dark:text-gray-300">
                  Total Qty
                </Label>
                <div class="relative">
                  <Input 
                    v-model="form.firm_lab_receive_total_qty" 
                    type="number" 
                    id="firm_lab_receive_total_qty"
                    class="pl-8 py-2 text-sm bg-gray-50 dark:bg-gray-800"
                    readonly
                  />
                  <div class="absolute inset-y-0 left-0 flex items-center pl-2 pointer-events-none">
                    <Calculator class="h-3 w-3 text-gray-400" />
                  </div>
                </div>
                <InputError :message="form.errors.firm_lab_receive_total_qty" />
              </div>
            </div>
          </div>

          <!-- Notes & Remarks Section -->
          <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
              <FileText class="h-4 w-4 text-purple-500" />
              Additional Information
            </h3>
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
              <div class="space-y-1">
                <Label for="note" class="text-xs font-medium text-gray-700 dark:text-gray-300">
                  Note
                </Label>
                <textarea 
                  v-model="form.note" 
                  id="note" 
                  rows="3"
                  class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:focus:border-blue-400 transition-all duration-200"
                  :class="form.errors.note ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''"
                  placeholder="Enter any additional notes..."
                ></textarea>
                <InputError :message="form.errors.note" />
              </div>

              <div class="space-y-1">
                <Label for="remarks" class="text-xs font-medium text-gray-700 dark:text-gray-300">
                  Remarks
                </Label>
                <textarea 
                  v-model="form.remarks" 
                  id="remarks" 
                  rows="3"
                  class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:focus:border-blue-400 transition-all duration-200"
                  :class="form.errors.remarks ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : ''"
                  placeholder="Enter any remarks or comments..."
                ></textarea>
                <InputError :message="form.errors.remarks" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col gap-4 sm:flex-row sm:justify-end">
        <Button
          type="button"
          variant="outline"
          @click="router.visit(route('firm-lab-tests.index'))"
          class="w-full sm:w-auto px-6 py-3 text-gray-700 border-gray-300 hover:bg-gray-50 focus:ring-2 focus:ring-gray-500/20 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800 transition-all duration-200"
        >
          <ArrowLeft class="h-4 w-4 mr-2" />
          Cancel
        </Button>
        <Button
          type="submit"
          :disabled="form.processing"
          class="w-full sm:w-auto px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white shadow-lg hover:shadow-xl focus:ring-2 focus:ring-blue-500/20 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
        >
          <div v-if="form.processing" class="flex items-center gap-2">
            <div class="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></div>
            Saving...
          </div>
          <div v-else class="flex items-center gap-2">
            <CheckCircle2 class="h-4 w-4" />
            Save Lab Test
          </div>
        </Button>
      </div>
    </form>
  </AppLayout>
</template>
