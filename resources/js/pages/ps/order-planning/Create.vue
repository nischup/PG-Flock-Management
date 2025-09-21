<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import { ref,watch } from 'vue';
const form = useForm({
  order_from: '',
  order_to: '',
  cc: '',
  subject: '',
  message: '',
  attachment: null,
  items: [
    { order_volume: '', shipping_date: '', supply_base: '' }
  ]
});
const quillContent = ref(form.message || '');

watch(quillContent, (val) => {
  form.message = val; // this ensures form.message is always updated
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
    <div class="bg-white shadow-lg rounded-lg p-6 w-full">
      <h1 class="text-2xl font-bold mb-6">Create Order Planning</h1>

      <form @submit.prevent="form.post(route('order-plans.store'))" class="space-y-6 w-full">

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
          <label class="block font-medium mb-1">CC (comma separated)</label>
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
          <QuillEditor
            v-model="quillContent"
            class="bg-white border rounded"
            style="height: 250px;"
            :formats="['bold','italic','underline','strike','link','image','list','bullet']"
          />
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

          <!-- Add Item Button aligned with last item row -->
          <div class="flex justify-end mt-2">
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
