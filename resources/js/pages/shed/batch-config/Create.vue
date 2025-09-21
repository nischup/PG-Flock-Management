<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { 
  ArrowLeft, 
  Settings, 
  Building2, 
  Users, 
  Thermometer, 
  Droplets, 
  Ruler, 
  Calendar,
  FileText,
  Save,
  AlertCircle,
  CheckCircle2,
  ChevronDown,
  Search,
  X
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import dayjs from 'dayjs';

// Props from controller
const props = defineProps<{
  batchAssigns: Array<{
    id: number;
    transaction_no: string;
    batch_name: string;
  }>;
}>();

// Form initialization
const form = useForm({
  batch_assign_id: '',
  area_sqft: '',
  num_workers: 0,
  density_per_sqft: '',
  feeders: '',
  drinkers: '',
  temperature_target: '',
  humidity_target: '',
  note: '',
  effective_from: dayjs().format('YYYY-MM-DD'),
  effective_to: '',
});

// Modern dropdown states
const showBatchDropdown = ref(false);
const batchSearchQuery = ref('');

// Filtered batch options
const filteredBatches = computed(() => {
  if (!batchSearchQuery.value) return props.batchAssigns;
  return props.batchAssigns.filter(batch => {
    const searchTerm = batchSearchQuery.value.toLowerCase();
    return batch.transaction_no.toLowerCase().includes(searchTerm) ||
           batch.batch_name.toLowerCase().includes(searchTerm);
  });
});

// Selected batch display
const selectedBatch = computed(() => {
  return props.batchAssigns.find(batch => batch.id === Number(form.batch_assign_id));
});

// Submit
const submit = () => {
  form.post(route('batch-config.store'));
};

// Breadcrumbs
const breadcrumbs = [
  { title: 'Batch Configuration', href: route('batch-config.index') },
  { title: 'Create', href: '' },
];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Create Batch Config" />

    <!-- Header Section -->
    <div class="mb-8">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create Batch Configuration</h1>
          <p class="mt-2 text-gray-600 dark:text-gray-400">Configure batch settings for optimal flock management</p>
        </div>
        <Button 
          @click="router.visit(route('batch-config.index'))"
          class="group relative overflow-hidden rounded-xl px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl"
          style="background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%); box-shadow: 0 4px 15px rgba(107, 114, 128, 0.3);"
        >
          <span class="relative z-10 flex items-center gap-2">
            <ArrowLeft class="h-4 w-4" />
            Back to List
          </span>
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-20 group-hover:translate-x-full"></div>
        </Button>
      </div>
    </div>

    <form @submit.prevent="submit" class="space-y-8">
      <!-- Batch Selection Card -->
      <Card class="relative overflow-hidden border-0 shadow-xl ring-1 ring-gray-200 dark:ring-gray-700">
        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-blue-500/20 to-purple-500/20"></div>
        <div class="absolute -bottom-4 -left-4 h-32 w-32 rounded-full bg-gradient-to-br from-emerald-500/10 to-blue-500/10"></div>
        
        <CardHeader class="relative">
          <div class="flex items-center gap-3">
            <div class="rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 p-3 shadow-lg">
              <Building2 class="h-6 w-6 text-white" />
            </div>
            <div>
              <CardTitle class="text-2xl font-bold text-gray-900 dark:text-white">Batch Selection</CardTitle>
              <CardDescription class="text-gray-600 dark:text-gray-400">Choose the batch to configure settings for</CardDescription>
            </div>
          </div>
        </CardHeader>

        <CardContent class="relative">
          <div class="space-y-2">
            <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
              <Settings class="h-4 w-4" />
              Select Batch
            </Label>
            
            <!-- Modern Dropdown -->
            <div class="relative">
              <button
                type="button"
                @click="showBatchDropdown = !showBatchDropdown"
                class="flex w-full items-center justify-between rounded-xl border border-gray-300 bg-white px-4 py-3 shadow-sm transition-all duration-200 hover:border-blue-500 hover:shadow-md focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
              >
                <span class="flex items-center gap-3">
                  <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                  <span v-if="selectedBatch" class="text-blue-600 dark:text-blue-400 font-semibold">
                    {{ selectedBatch.transaction_no }} - {{ selectedBatch.batch_name }}
                  </span>
                  <span v-else>Select Batch</span>
                </span>
                <ChevronDown class="h-4 w-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showBatchDropdown }" />
              </button>
              
              <!-- Dropdown Menu -->
              <div 
                v-if="showBatchDropdown" 
                class="fixed inset-0 z-[9999] flex items-start justify-center pt-20"
                @click="showBatchDropdown = false"
              >
                <div 
                  class="w-full max-w-md rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-600 dark:bg-gray-800"
                  @click.stop
                >
                  <!-- Header -->
                  <div class="border-b border-gray-200 p-4 dark:border-gray-600">
                    <h3 class="font-semibold text-gray-900 dark:text-white">Select Batch</h3>
                    <div class="relative mt-3">
                      <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                      <input
                        v-model="batchSearchQuery"
                        type="text"
                        placeholder="Search batches..."
                        class="w-full rounded-lg border border-gray-300 bg-gray-50 pl-10 pr-4 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        @click.stop
                      />
                    </div>
                  </div>

                  <!-- Batch List -->
                  <div class="max-h-96 overflow-y-auto">
                    <div v-if="(props.batchAssigns?.length || 0) === 0" class="px-6 py-8 text-center">
                      <AlertCircle class="mx-auto h-8 w-8 text-red-500" />
                      <div class="mt-2 font-medium text-red-600">No Batches Available</div>
                      <div class="text-sm text-gray-500">Please create batch assignments first</div>
                    </div>
                    <button
                      v-for="batch in filteredBatches"
                      :key="batch.id"
                      type="button"
                      @click.stop="form.batch_assign_id = batch.id; showBatchDropdown = false"
                      class="flex w-full items-center gap-4 px-6 py-4 text-left hover:bg-blue-50 dark:hover:bg-gray-700 transition-colors duration-200 border-b border-gray-100 dark:border-gray-600 last:border-b-0"
                      :class="{ 'bg-blue-100 dark:bg-blue-900': form.batch_assign_id == batch.id }"
                    >
                      <div class="h-3 w-3 rounded-full bg-blue-500 flex-shrink-0"></div>
                      <div class="flex-1">
                        <div class="font-semibold text-gray-900 dark:text-white">
                          <span class="text-blue-600 dark:text-blue-400">
                            {{ batch.transaction_no }} - {{ batch.batch_name }}
                          </span>
                        </div>
                      </div>
                      <CheckCircle2 v-if="form.batch_assign_id == batch.id" class="h-4 w-4 text-blue-500 flex-shrink-0" />
                    </button>
                    <div v-if="filteredBatches.length === 0 && (props.batchAssigns?.length || 0) > 0" class="px-6 py-8 text-center text-gray-500">
                      <Search class="mx-auto h-6 w-6 text-gray-400" />
                      <div class="mt-2 text-sm">No results found for "{{ batchSearchQuery }}"</div>
                    </div>
                  </div>

                  <!-- Close Button -->
                  <div class="border-t border-gray-200 p-4 dark:border-gray-600">
                    <Button 
                      type="button"
                      @click="showBatchDropdown = false"
                      class="w-full rounded-xl bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                    >
                      Close
                    </Button>
                  </div>
                </div>
              </div>
            </div>
            
            <div v-if="form.errors.batch_assign_id" class="flex items-center gap-2 text-red-600 text-sm">
              <AlertCircle class="h-4 w-4" />
              {{ form.errors.batch_assign_id }}
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Configuration Settings Card -->
      <Card class="relative overflow-hidden border-0 shadow-xl ring-1 ring-gray-200 dark:ring-gray-700">
        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-gradient-to-br from-emerald-500/20 to-blue-500/20"></div>
        <div class="absolute -bottom-4 -left-4 h-32 w-32 rounded-full bg-gradient-to-br from-purple-500/10 to-pink-500/10"></div>
        
        <CardHeader class="relative">
          <div class="flex items-center gap-3">
            <div class="rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 p-3 shadow-lg">
              <Settings class="h-6 w-6 text-white" />
            </div>
            <div>
              <CardTitle class="text-2xl font-bold text-gray-900 dark:text-white">Configuration Settings</CardTitle>
              <CardDescription class="text-gray-600 dark:text-gray-400">Configure area, workers, and equipment settings</CardDescription>
            </div>
          </div>
        </CardHeader>

        <CardContent class="relative space-y-6">
          <!-- Area & Workers Section -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
              <Ruler class="h-5 w-5 text-blue-500" />
              Area & Workforce
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div class="space-y-2">
                <Label for="area_sqft" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Area (sqft)</Label>
                <Input 
                  v-model="form.area_sqft" 
                  type="number" 
                  id="area_sqft" 
                  placeholder="Enter area in square feet"
                  class="rounded-xl border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                />
                <div v-if="form.errors.area_sqft" class="flex items-center gap-2 text-red-600 text-sm">
                  <AlertCircle class="h-4 w-4" />
                  {{ form.errors.area_sqft }}
                </div>
              </div>

              <div class="space-y-2">
                <Label for="num_workers" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Number of Workers</Label>
                <Input 
                  v-model="form.num_workers" 
                  type="number" 
                  id="num_workers" 
                  placeholder="Enter number of workers"
                  class="rounded-xl border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                />
              </div>

              <div class="space-y-2">
                <Label for="density_per_sqft" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Density per sqft</Label>
                <Input 
                  v-model="form.density_per_sqft" 
                  type="number" 
                  id="density_per_sqft" 
                  placeholder="Enter density per sqft"
                  class="rounded-xl border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                />
              </div>
            </div>
          </div>

          <!-- Equipment Section -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
              <Building2 class="h-5 w-5 text-emerald-500" />
              Equipment & Environment
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <div class="space-y-2">
                <Label for="feeders" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Feeders</Label>
                <Input 
                  v-model="form.feeders" 
                  type="number" 
                  id="feeders" 
                  placeholder="Number of feeders"
                  class="rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                />
              </div>

              <div class="space-y-2">
                <Label for="drinkers" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Drinkers</Label>
                <Input 
                  v-model="form.drinkers" 
                  type="number" 
                  id="drinkers" 
                  placeholder="Number of drinkers"
                  class="rounded-xl border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20"
                />
              </div>

              <div class="space-y-2">
                <Label for="temperature_target" class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                  <Thermometer class="h-4 w-4 text-orange-500" />
                  Temperature Target (°C)
                </Label>
                <Input 
                  v-model="form.temperature_target" 
                  type="number" 
                  step="0.01" 
                  id="temperature_target" 
                  placeholder="Target temperature"
                  class="rounded-xl border-gray-300 focus:border-orange-500 focus:ring-2 focus:ring-orange-500/20"
                />
              </div>

              <div class="space-y-2">
                <Label for="humidity_target" class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                  <Droplets class="h-4 w-4 text-blue-500" />
                  Humidity Target (%)
                </Label>
                <Input 
                  v-model="form.humidity_target" 
                  type="number" 
                  step="0.01" 
                  id="humidity_target" 
                  placeholder="Target humidity"
                  class="rounded-xl border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20"
                />
              </div>
            </div>
          </div>

          <!-- Note Section -->
          <div class="space-y-2">
            <Label for="note" class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
              <FileText class="h-4 w-4 text-purple-500" />
              Additional Notes
            </Label>
            <textarea 
              v-model="form.note" 
              id="note" 
              rows="4"
              placeholder="Enter any additional notes or special instructions..."
              class="w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm focus:border-purple-500 focus:outline-none focus:ring-2 focus:ring-purple-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            ></textarea>
          </div>

          <!-- Effective Dates Section -->
          <div class="space-y-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
              <Calendar class="h-5 w-5 text-indigo-500" />
              Effective Period
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                <Label for="effective_from" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Effective From</Label>
                <Input 
                  v-model="form.effective_from" 
                  type="date" 
                  id="effective_from" 
                  class="rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                />
              </div>

              <div class="space-y-2">
                <Label for="effective_to" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Effective To (Optional)</Label>
                <Input 
                  v-model="form.effective_to" 
                  type="date" 
                  id="effective_to" 
                  class="rounded-xl border-gray-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                />
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Action Buttons -->
      <div class="flex items-center justify-end gap-4 rounded-2xl bg-gradient-to-r from-gray-50 to-white p-6 dark:from-gray-800 dark:to-gray-900">
        <Button 
          type="button"
          @click="router.visit(route('batch-config.index'))"
          class="rounded-xl border border-gray-300 bg-white px-6 py-3 font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500/20 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
        >
          Cancel
        </Button>
        <Button 
          type="submit" 
          :disabled="form.processing"
          class="group relative overflow-hidden rounded-xl bg-gradient-to-r from-emerald-600 to-emerald-700 px-8 py-3 font-semibold text-white shadow-lg transition-all duration-300 hover:from-emerald-700 hover:to-emerald-800 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/50 disabled:opacity-50"
          style="background: linear-gradient(135deg, #059669 0%, #047857 100%); box-shadow: 0 4px 15px rgba(5, 150, 105, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.1);"
        >
          <span class="relative z-10 flex items-center gap-2">
            <Save class="h-4 w-4" />
            {{ form.processing ? 'Saving...' : 'Save Configuration' }}
          </span>
          <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-20 group-hover:translate-x-full"></div>
        </Button>
      </div>
    </form>
  </AppLayout>
</template>
