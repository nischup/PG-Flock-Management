<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

// Props from controller
const props = defineProps<{
  plan: {
    id: number;
    order_from: string;
    order_to: string;
    cc: string;
    subject: string;
    message: string;
    attachment?: string;
    details?: { id: number; order_volume: string; shipping_date: string; supply_base: string }[];
  };
}>();

// Initialize form with safe defaults
const form = useForm({
  order_from: props.plan.order_from || '',
  order_to: props.plan.order_to || '',
  cc: props.plan.cc || '',
  subject: props.plan.subject || '',
  message: props.plan.message || '',
  attachment: null,
  items: (props.plan.details || []).map(d => ({
    id: d.id,
    order_volume: d.order_volume,
    shipping_date: d.shipping_date,
    supply_base: d.supply_base
  }))
});

function addItem() {
  form.items.push({ order_volume: '', shipping_date: '', supply_base: '' });
}

function removeItem(i: number) {
  form.items.splice(i, 1);
}

function submit() {
  form.put(route('order-plans.update', props.plan.id));
}
</script>

<template>
  <AppLayout title="Edit Order Planning">
    <div class="bg-white shadow-lg rounded-lg p-6 w-full">
      <h1 class="text-2xl font-bold mb-6">Edit Order Planning</h1>

      <form @submit.prevent="submit" class="space-y-6 w-full">

        <!-- Order From -->
        <div>
          <label class="block font-medium mb-1">Order From (Email)</label>
          <input v-model="form.order_from" type="email"
                 class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-gray-400" />
        </div>

        <!-- Order To -->
        <div>
          <label class="block font-medium mb-1">Order To (Email)</label>
          <input v-model="form.order_to" type="email"
                 class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-gray-400" />
        </div>

        <!-- CC -->
        <div>
          <label class="block font-medium mb-1">CC</label>
          <input v-model="form.cc" type="text"
                 class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-gray-400" />
        </div>

        <!-- Subject -->
        <div>
          <label class="block font-medium mb-1">Subject</label>
          <input v-model="form.subject" type="text"
                 class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-gray-400" />
        </div>

        <!-- Message Body -->
        <div>
          <label class="block font-medium mb-1">Message Body</label>
          <QuillEditor v-model="form.message" class="bg-white border rounded" style="height: 250px;" />
        </div>

        <!-- Items Grid -->
        <div>
          <h2 class="font-semibold mb-2">Items</h2>
          <div v-for="(item, i) in form.items" :key="i" class="grid grid-cols-4 gap-2 mb-3 items-center">
            <input v-model="item.order_volume" placeholder="Order Volume"
                   class="w-full border rounded px-2 py-2 focus:outline-none focus:ring focus:border-gray-400" />
            <input v-model="item.shipping_date" type="date"
                   class="w-full border rounded px-2 py-2 focus:outline-none focus:ring focus:border-gray-400" />
            <input v-model="item.supply_base" placeholder="Supply Base"
                   class="w-full border rounded px-2 py-2 focus:outline-none focus:ring focus:border-gray-400" />
            <button type="button" @click="removeItem(i)"
                    class="bg-red-500 hover:bg-red-600 text-white text-xs rounded-full w-6 h-6 flex items-center justify-center">
              ✕
            </button>
          </div>

          <!-- Add Item button aligned with last row -->
          <div class="flex justify-end mt-1">
            <button type="button" @click="addItem"
                    class="bg-gray-800 hover:bg-black text-white px-3 py-1 rounded">
              + Add Item
            </button>
          </div>
        </div>

        <!-- Attachment -->
        <div>
          <label class="block font-medium mb-1">Attachment (optional)</label>
          <input type="file" @change="e => form.attachment = e.target.files[0]"
                 class="w-full border rounded px-3 py-2 focus:outline-none focus:ring focus:border-gray-400" />
          <div v-if="props.plan.attachment" class="mt-1 text-sm text-gray-600">
            Current file: 
            <a :href="`/storage/${props.plan.attachment}`" target="_blank" class="underline text-blue-600">
              View
            </a>
          </div>
        </div>

        <!-- Submit Button on right -->
        <div class="flex justify-end">
          <button type="submit"
                  class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded text-sm">
            Submit
          </button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
