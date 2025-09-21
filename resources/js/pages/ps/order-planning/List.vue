<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';

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
    <div class="flex justify-between mb-4">
      <h1 class="text-xl font-bold">Order Planning List</h1>
      <Link href="/order-plans/create" class="px-4 py-2 bg-blue-600 text-white rounded">+ Create</Link>
    </div>

    <table class="w-full border">
      <thead class="bg-gray-100">
        <tr>
          <th class="p-2 border">#</th>
          <th class="p-2 border">Order From</th>
          <th class="p-2 border">Order To</th>
          <th class="p-2 border">CC</th>
          <th class="p-2 border">Items</th>
          <th class="p-2 border">Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(plan, i) in plans.data" :key="plan.id">
          <td class="border p-2">{{ i + 1 }}</td>
          <td class="border p-2">{{ plan.order_from }}</td>
          <td class="border p-2">{{ plan.order_to }}</td>
          <td class="border p-2">{{ plan.cc }}</td>
          <td class="border p-2">
            <ul>
              <li v-for="(item, j) in plan.items" :key="j">
                {{ item.order_volume }} | {{ item.shipping_date }} | {{ item.supply_base }}
              </li>
            </ul>
          </td>
          <td class="border p-2">
            <Link :href="`/order-plans/${plan.id}/edit`" class="px-2 py-1 bg-yellow-500 text-white rounded">Edit</Link>
          </td>
        </tr>
      </tbody>
    </table>
  </AppLayout>
</template>
