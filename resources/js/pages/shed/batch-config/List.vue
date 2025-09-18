<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import dayjs from 'dayjs';
import { Button } from '@/components/ui/button';
import { useNotifier } from '@/composables/useNotifier';
interface BatchConfig {
  id: number;
  batch_assign: {
    id: number;
    transaction_no: string;
    batch: {
      id: number;
      name: string;
    };
  };
  area_sqft: number;
  num_workers: number;
  density_per_sqft: number;
  feeders: number;
  drinkers: number;
  temperature_target: number;
  humidity_target: number;
  effective_from: string;
  effective_to?: string | null;
  note?: string | null;
}
const { confirmDelete,confirmUpdate } = useNotifier();
const props = defineProps<{
  batchConfigs: BatchConfig[];
}>();

const batchConfigs = ref([...props.batchConfigs]);

const breadcrumbs = [
  { title: 'Batch Configuration', href: '/batch-config' },
];

const goToCreate = () => router.get(route('batch-config.create'));
const goToEdit = (id: number) => router.get(route('batch-config.edit', id));
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Batch Configuration" />

    <div class="px-4 py-6">
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800">Batch Configurations</h2>
        <Button class="bg-chicken text-white" @click="goToCreate">+ Add New</Button>
      </div>

      <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="px-6 py-3 text-left font-semibold">#</th>
              <th class="px-6 py-3 text-left font-semibold">Company</th>
              <th class="px-6 py-3 text-left font-semibold">Project</th>
              <th class="px-6 py-3 text-left font-semibold">Shed</th>
              <th class="px-6 py-3 text-left font-semibold">Transaction No</th>
              <th class="px-6 py-3 text-left font-semibold">Batch Name</th>
              <th class="px-6 py-3 text-left font-semibold">Area (sqft)</th>
              <th class="px-6 py-3 text-left font-semibold">Workers</th>
              <th class="px-6 py-3 text-left font-semibold">Effective From</th>
              <th class="px-6 py-3 text-left font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200 bg-white">
            <tr v-for="(item, index) in batchConfigs" :key="item.id">
               <td class="px-6 py-4">{{ index + 1 }}</td>
               <td>{{ item.batch_assign.company.name }}</td>
               <td>{{ item.batch_assign.project.name }}</td>
               <td>{{ item.batch_assign.shed.name }}</td>
               <td class="px-6 py-4">{{ item.batch_assign.transaction_no }}</td>
               <td class="px-6 py-4">{{ item.batch_assign.batch.name }}</td>
               <td class="px-6 py-4">{{ item.area_sqft }}</td>
               <td class="px-6 py-4">{{ item.num_workers }}</td>
               <td class="px-6 py-4">{{ dayjs(item.effective_from).format('YYYY-MM-DD') }}</td>
               <td class="px-6 py-4">
                 <Button size="sm" class="bg-gray-500 text-white" @click="goToEdit(item.id)">Edit</Button>
               </td>
            </tr>

            <tr v-if="batchConfigs.length === 0">
              <td colspan="7" class="px-6 py-6 text-center text-gray-500">No batch configurations found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
