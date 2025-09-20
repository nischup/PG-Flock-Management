<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

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

// Submit
const submit = () => {
  router.post(route('firm-lab-tests.store'), form);
};

// Breadcrumbs
const breadcrumbs = [
  { title: 'Firm Lab Tests', href: route('firm-lab-tests.index') },
  { title: 'Create', href: '' },
];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Create Firm Lab Test" />

    <div class="p-8 bg-white rounded shadow w-full min-h-screen">
      <h2 class="text-3xl font-bold mb-8">Create Firm Lab Test</h2>

      <form class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Batch Select -->
        <div class="col-span-2">
          <Label for="batch_assign_id">Select Batch</Label>
          <select
            v-model="form.batch_assign_id"
            id="batch_assign_id"
            class="w-full border rounded p-3"
          >
            <option value="">-- Select Batch --</option>
            <option
              v-for="b in props.batchAssigns"
              :key="b.id"
              :value="b.id"
            >
              {{ b.transaction_no }} - {{ b.batch_no }}
            </option>
          </select>
          <span class="text-red-600 text-sm">{{ form.errors.batch_assign_id }}</span>
        </div>

        <!-- Send Quantities -->
        <div>
          <Label for="firm_lab_send_female_qty">Send Female Qty</Label>
          <Input v-model="form.firm_lab_send_female_qty" type="number" id="firm_lab_send_female_qty" />
          <span class="text-red-600 text-sm">{{ form.errors.firm_lab_send_female_qty }}</span>
        </div>

        <div>
          <Label for="firm_lab_send_male_qty">Send Male Qty</Label>
          <Input v-model="form.firm_lab_send_male_qty" type="number" id="firm_lab_send_male_qty" />
          <span class="text-red-600 text-sm">{{ form.errors.firm_lab_send_male_qty }}</span>
        </div>

        <div>
          <Label for="firm_lab_send_total_qty">Send Total Qty</Label>
          <Input v-model="form.firm_lab_send_total_qty" type="number" id="firm_lab_send_total_qty" />
          <span class="text-red-600 text-sm">{{ form.errors.firm_lab_send_total_qty }}</span>
        </div>

        <!-- Receive Quantities -->
        <div>
          <Label for="firm_lab_receive_female_qty">Receive Female Qty</Label>
          <Input v-model="form.firm_lab_receive_female_qty" type="number" id="firm_lab_receive_female_qty" />
          <span class="text-red-600 text-sm">{{ form.errors.firm_lab_receive_female_qty }}</span>
        </div>

        <div>
          <Label for="firm_lab_receive_male_qty">Receive Male Qty</Label>
          <Input v-model="form.firm_lab_receive_male_qty" type="number" id="firm_lab_receive_male_qty" />
          <span class="text-red-600 text-sm">{{ form.errors.firm_lab_receive_male_qty }}</span>
        </div>

        <div>
          <Label for="firm_lab_receive_total_qty">Receive Total Qty</Label>
          <Input v-model="form.firm_lab_receive_total_qty" type="number" id="firm_lab_receive_total_qty" />
          <span class="text-red-600 text-sm">{{ form.errors.firm_lab_receive_total_qty }}</span>
        </div>

        <!-- Note & Remarks -->
        <div class="col-span-2">
          <Label for="note">Note</Label>
          <textarea v-model="form.note" id="note" class="w-full border rounded p-3 h-28"></textarea>
          <span class="text-red-600 text-sm">{{ form.errors.note }}</span>
        </div>

        <div class="col-span-2">
          <Label for="remarks">Remarks</Label>
          <textarea v-model="form.remarks" id="remarks" class="w-full border rounded p-3 h-28"></textarea>
          <span class="text-red-600 text-sm">{{ form.errors.remarks }}</span>
        </div>
      </form>

      <!-- Actions -->
      <div class="mt-8 flex gap-4">
        <Button class="bg-gray-300 text-black" @click="router.visit(route('firm-lab-tests.index'))">Cancel</Button>
        <Button class="bg-green-600 text-white" @click="submit" :disabled="form.processing">
          {{ form.processing ? 'Saving...' : 'Save' }}
        </Button>
      </div>
    </div>
  </AppLayout>
</template>
