<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
  order_from: '',
  order_to: '',
  cc: '',
  items: [
    { order_volume: '', shipping_date: '', supply_base: '' }
  ]
});

function addItem() {
  form.items.push({ order_volume: '', shipping_date: '', supply_base: '' });
}

function removeItem(i: number) {
  form.items.splice(i, 1);
}
</script>

<template>
  <AppLayout title="Create Order Planning">
    <h1 class="text-xl font-bold mb-4">Create Order Planning</h1>

    <form @submit.prevent="form.post(route('order-plans.store'))" class="space-y-4">
      <div>
        <label>Order From (Email)</label>
        <input v-model="form.order_from" type="email" class="border p-2 w-full" />
      </div>
      <div>
        <label>Order To (Email)</label>
        <input v-model="form.order_to" type="email" class="border p-2 w-full" />
      </div>
      <div>
        <label>CC (comma separated)</label>
        <input v-model="form.cc" type="text" class="border p-2 w-full" />
      </div>

      <div>
        <h2 class="font-bold mb-2">Items</h2>
        <div v-for="(item, i) in form.items" :key="i" class="grid grid-cols-4 gap-2 mb-2">
          <input v-model="item.order_volume" placeholder="Order Volume" class="border p-2" />
          <input v-model="item.shipping_date" type="date" class="border p-2" />
          <input v-model="item.supply_base" placeholder="Supply Base" class="border p-2" />
          <button type="button" @click="removeItem(i)" class="bg-red-500 text-white px-2 rounded">X</button>
        </div>
        <button type="button" @click="addItem" class="bg-green-500 text-white px-3 py-1 rounded">+ Add Item</button>
      </div>

      <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
    </form>
  </AppLayout>
</template>
