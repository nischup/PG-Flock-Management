<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { useNotifier } from '@/composables/useNotifier';

const { confirmDelete } = useNotifier();

const props = defineProps<{
  firmLabTests: Array<{
    id: number;
    batch_assign: {
      id: number;
      transaction_no: string;
      batch: { id: number; name: string };
      company: { id: number; name: string };
      project: { id: number; name: string };
      shed: { id: number; name: string };
    };
    firm_lab_send_female_qty: number;
    firm_lab_send_male_qty: number;
    firm_lab_send_total_qty: number;
    firm_lab_receive_female_qty: number;
    firm_lab_receive_male_qty: number;
    firm_lab_receive_total_qty: number;
    note?: string | null;
    remarks?: string | null;
    status: number;
  }>;
}>();

const breadcrumbs = [
  { title: 'Firm Lab Tests', href: '/firm-lab' },
];

// ✅ create an Inertia form instance
const form = useForm({});

const doDelete = (id: number) =>
  confirmDelete(() => form.delete(`/firm-lab-tests/${id}`));

</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Firm Lab Tests" />

    <div class="px-4 py-6">
      <!-- Header -->
      <div class="mb-4 flex items-center justify-between">
        <h2 class="text-xl font-semibold text-gray-800">Firm Lab Tests</h2>
        <Link href="/firm-lab-tests/create">
          <Button class="bg-chicken text-white">+ Add New</Button>
        </Link>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
          <thead class="bg-gray-50 text-gray-600">
            <tr>
              <th class="px-6 py-3 text-left font-semibold">#</th>
              <th class="px-6 py-3 text-left font-semibold">Company</th>
              <th class="px-6 py-3 text-left font-semibold">Project</th>
              <th class="px-6 py-3 text-left font-semibold">Shed</th>
              <th class="px-6 py-3 text-left font-semibold">Transaction No</th>
              <th class="px-6 py-3 text-left font-semibold">Batch</th>
              <th class="px-6 py-3 text-left font-semibold">Send Female</th>
              <th class="px-6 py-3 text-left font-semibold">Send Male</th>
              <th class="px-6 py-3 text-left font-semibold">Send Total</th>
              <th class="px-6 py-3 text-left font-semibold">Receive Female</th>
              <th class="px-6 py-3 text-left font-semibold">Receive Male</th>
              <th class="px-6 py-3 text-left font-semibold">Receive Total</th>
              <th class="px-6 py-3 text-left font-semibold">Actions</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-200 bg-white">
            <tr v-for="(item, index) in props.firmLabTests" :key="item.id">
              <td class="px-6 py-4">{{ index + 1 }}</td>
              <td>{{ item.batch_assign.company.name }}</td>
              <td>{{ item.batch_assign.project.name }}</td>
              <td>{{ item.batch_assign.shed.name }}</td>
              <td>{{ item.batch_assign.transaction_no }}</td>
              <td>{{ item.batch_assign.batch.name }}</td>
              <td>{{ item.firm_lab_send_female_qty }}</td>
              <td>{{ item.firm_lab_send_male_qty }}</td>
              <td>{{ item.firm_lab_send_total_qty }}</td>
              <td>{{ item.firm_lab_receive_female_qty }}</td>
              <td>{{ item.firm_lab_receive_male_qty }}</td>
              <td>{{ item.firm_lab_receive_total_qty }}</td>
              <td class="px-6 py-4 flex gap-2">
                <Link :href="`/firm-lab-tests/${item.id}/edit`">
                  <Button size="sm" class="bg-gray-500 text-white">
                    Edit
                  </Button>
                </Link>
                <Button
                  size="sm"
                  class="bg-red-500 text-white"
                  @click="doDelete(item.id)"
                >
                  Delete
                </Button>
              </td>
            </tr>

            <tr v-if="props.firmLabTests.length === 0">
              <td colspan="13" class="px-6 py-6 text-center text-gray-500">
                No firm lab tests found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
