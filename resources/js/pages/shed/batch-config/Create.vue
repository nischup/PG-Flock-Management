<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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

    <div class="p-6 bg-white rounded shadow max-w-full space-y-4">
      <h2 class="text-xl font-semibold mb-4">Create Batch Config</h2>

      <!-- Select Batch -->
      <div>
        <Label for="batch_assign_id">Select Batch</Label>
        <select
          v-model="form.batch_assign_id"
          id="batch_assign_id"
          class="w-full border rounded p-2"
        >
          <option value="">-- Select Batch --</option>
          <option
            v-for="b in props.batchAssigns"
            :key="b.id"
            :value="b.id"
          >
            {{ b.transaction_no }} - {{ b.batch_name }}
          </option>
        </select>
        <span class="text-red-600 text-sm">{{ form.errors.batch_assign_id }}</span>
      </div>

      <!-- Area & Workers -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <Label for="area_sqft">Area (sqft)</Label>
          <Input v-model="form.area_sqft" type="number" id="area_sqft" />
          <span class="text-red-600 text-sm">{{ form.errors.area_sqft }}</span>
        </div>

        <div>
          <Label for="num_workers">Number of Workers</Label>
          <Input v-model="form.num_workers" type="number" id="num_workers" />
        </div>

        <div>
          <Label for="density_per_sqft">Density per sqft</Label>
          <Input v-model="form.density_per_sqft" type="number" id="density_per_sqft" />
        </div>
      </div>

      <!-- Feeders, Drinkers, Temp, Humidity -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <Label for="feeders">Feeders</Label>
          <Input v-model="form.feeders" type="number" id="feeders" />
        </div>

        <div>
          <Label for="drinkers">Drinkers</Label>
          <Input v-model="form.drinkers" type="number" id="drinkers" />
        </div>

        <div>
          <Label for="temperature_target">Temperature Target</Label>
          <Input v-model="form.temperature_target" type="number" step="0.01" id="temperature_target" />
        </div>

        <div>
          <Label for="humidity_target">Humidity Target</Label>
          <Input v-model="form.humidity_target" type="number" step="0.01" id="humidity_target" />
        </div>
      </div>

      <!-- Note -->
      <div>
        <Label for="note">Note</Label>
        <textarea v-model="form.note" id="note" class="w-full border rounded p-2"></textarea>
      </div>

      <!-- Effective Dates -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <Label for="effective_from">Effective From</Label>
          <Input v-model="form.effective_from" type="date" id="effective_from" />
        </div>

        <div>
          <Label for="effective_to">Effective To</Label>
          <Input v-model="form.effective_to" type="date" id="effective_to" />
        </div>
      </div>

      <!-- Actions -->
      <div class="mt-6 flex gap-2">
        <Button class="bg-gray-300 text-black" @click="router.visit(route('batch-config.index'))">Cancel</Button>
        <Button class="bg-green-600 text-white" @click="submit" :disabled="form.processing">
          {{ form.processing ? 'Saving...' : 'Save' }}
        </Button>
      </div>
    </div>
  </AppLayout>
</template>
