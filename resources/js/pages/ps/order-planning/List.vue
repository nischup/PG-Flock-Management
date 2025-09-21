<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
  plans: {
    data: Array<{
      id: number;
      order_from: string;
      order_to: string;
      cc: string | null;
      items: Array<{ order_volume: string; shipping_date: string; supply_base: string }>;
    }>;
    links: Array<{ url: string | null; label: string; active: boolean }>;
  };
}>();
</script>

<template>
  <AppLayout title="Order Planning">
    <div class="bg-white shadow-lg rounded-lg p-6 w-full">
      <!-- Header -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Order Planning List</h1>
        <Link href="/order-plans/create" class="px-4 py-2 bg-gray-900 hover:bg-black text-white rounded">
          + Create
        </Link>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full border border-gray-200 rounded-lg">
          <thead class="bg-gray-100">
            <tr>
              <th class="p-3 text-left border-b">#</th>
              <th class="p-3 text-left border-b">Order From</th>
              <th class="p-3 text-left border-b">Order To</th>
              <th class="p-3 text-left border-b">CC</th>
              <th class="p-3 text-left border-b">Items</th>
              <th class="p-3 text-left border-b">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(plan, i) in props.plans.data" :key="plan.id" class="hover:bg-gray-50">
              <td class="p-3 border-b">{{ i + 1 }}</td>
              <td class="p-3 border-b">{{ plan.order_from }}</td>
              <td class="p-3 border-b">{{ plan.order_to }}</td>
              <td class="p-3 border-b">{{ plan.cc || '-' }}</td>
              <td class="p-3 border-b">
                <ul class="space-y-1 text-sm">
                  <li v-for="(item, j) in plan.items" :key="j">
                    <span class="font-medium">{{ item.order_volume }}</span> | 
                    <span>{{ item.shipping_date }}</span> | 
                    <span>{{ item.supply_base }}</span>
                  </li>
                </ul>
              </td>
              <td class="p-3 border-b">
                <Link :href="`/order-plans/${plan.id}/edit`" 
                      class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded text-sm">
                  Edit
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      
    </div>
  </AppLayout>
</template>
