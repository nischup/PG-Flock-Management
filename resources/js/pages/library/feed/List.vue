<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Feed {
  id: number;
  feed_type: 'Broiler' | 'Layer';
  feed_name: string;
  created_at: string;
  status: 'Active' | 'Deactivated';
}

const feeds = ref<Feed[]>([
  { id: 1, feed_type: 'Broiler', feed_name: 'Starter Feed', created_at: '2025-08-01', status: 'Active' },
  { id: 2, feed_type: 'Layer', feed_name: 'Grower Feed', created_at: '2025-08-02', status: 'Active' },
  { id: 3, feed_type: 'Broiler', feed_name: 'Finisher Feed', created_at: '2025-08-03', status: 'Deactivated' }
]);

// Modal + form state
const showModal = ref(false);
const editingFeed = ref<Feed | null>(null);
const openDropdownId = ref<number | null>(null);

const form = useForm({
  feed_type: 'Broiler',
  feed_name: '',
  status: 'Active'
});

// For draggable modal
const modalRef = ref<HTMLElement | null>(null);
let offsetX = 0;
let offsetY = 0;
let isDragging = false;

const startDrag = (event: MouseEvent) => {
  if (!modalRef.value) return;
  isDragging = true;
  const rect = modalRef.value.getBoundingClientRect();
  offsetX = event.clientX - rect.left;
  offsetY = event.clientY - rect.top;
  document.addEventListener('mousemove', onDrag);
  document.addEventListener('mouseup', stopDrag);
};

const onDrag = (event: MouseEvent) => {
  if (!isDragging || !modalRef.value) return;
  modalRef.value.style.left = `${event.clientX - offsetX}px`;
  modalRef.value.style.top = `${event.clientY - offsetY}px`;
  modalRef.value.style.position = 'absolute';
  modalRef.value.style.margin = '0'; // prevent centering
};

const stopDrag = () => {
  isDragging = false;
  document.removeEventListener('mousemove', onDrag);
  document.removeEventListener('mouseup', stopDrag);
};

// Save or Update feed
const submit = () => {
  if (!form.feed_name.trim()) {
    form.setError('feed_name', 'The feed name is required.');
    return;
  }

  if (editingFeed.value) {
    // Update
    editingFeed.value.feed_type = form.feed_type as 'Broiler' | 'Layer';
    editingFeed.value.feed_name = form.feed_name;
    editingFeed.value.status = form.status as 'Active' | 'Deactivated';
  } else {
    // Add new
    feeds.value.push({
      id: feeds.value.length + 1,
      feed_type: form.feed_type as 'Broiler' | 'Layer',
      feed_name: form.feed_name,
      created_at: new Date().toISOString().slice(0, 10),
      status: form.status as 'Active' | 'Deactivated'
    });
  }

  resetForm();
};

// Reset form
const resetForm = () => {
  form.reset();
  form.feed_type = 'Broiler';
  form.status = 'Active';
  editingFeed.value = null;
  showModal.value = false;
};

// Edit feed
const editFeed = (feed: Feed) => {
  editingFeed.value = feed;
  form.feed_type = feed.feed_type;
  form.feed_name = feed.feed_name;
  form.status = feed.status;
  showModal.value = true;
  openDropdownId.value = null;
};

// Toggle Active/Deactivated
const toggleStatus = (feed: Feed) => {
  feed.status = feed.status === 'Active' ? 'Deactivated' : 'Active';
  openDropdownId.value = null;
};

// Toggle dropdown
const toggleDropdown = (id: number) => {
  openDropdownId.value = openDropdownId.value === id ? null : id;
};

// Breadcrumbs
const breadcrumbs = [
  { title: 'Master Setup', href: '/master-setup' },
  { title: 'Feed', href: '/master-setup/feed' }
];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Feeds" />
    <div class="px-4 py-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-4">
        <HeadingSmall title="Feeds List" />
        <Button class="bg-chicken hover:bg-yellow-600 text-white" @click="showModal = true">
          + Add New
        </Button>
      </div>

      <!-- Feeds Table -->
      <table class="w-full border">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-2 border text-left">#</th>
            <th class="p-2 border text-left">Feed Type</th>
            <th class="p-2 border text-left">Feed Name</th>
            <th class="p-2 border text-left">Status</th>
            <th class="p-2 border text-left">Created At</th>
            <th class="p-2 border text-left">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(feed, index) in feeds" :key="feed.id">
            <td class="p-2 border">{{ index + 1 }}</td>
            <td class="p-2 border">{{ feed.feed_type }}</td>
            <td class="p-2 border">{{ feed.feed_name }}</td>
            <td class="p-2 border">
              <span :class="feed.status === 'Active' ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                {{ feed.status }}
              </span>
            </td>
            <td class="p-2 border">{{ feed.created_at }}</td>
            <td class="p-2 border relative">
              <!-- Dropdown Toggle -->
              <Button size="sm" class="bg-gray-500 hover:bg-gray-600 text-white" @click="toggleDropdown(feed.id)">
                Actions ▼
              </Button>

              <!-- Dropdown Menu -->
              <div
                v-if="openDropdownId === feed.id"
                class="absolute right-0 mt-1 w-40 bg-white border rounded shadow-md z-10"
              >
                <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="editFeed(feed)">✏ Edit</button>
                <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="toggleStatus(feed)">
                  {{ feed.status === 'Active' ? 'Deactivate' : 'Activate' }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Vue Modal (draggable + shaded border) -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex justify-center pt-6"
      @click.self="showModal = false"
    >
      <div
        ref="modalRef"
        class="bg-white rounded-lg border border-gray-300 shadow-lg w-full max-w-2xl"
        style="top: 100px; position: absolute;"
      >
        <!-- Modal header (drag handle) -->
        <div
          class="flex items-center justify-between p-4 border-b border-gray-200 cursor-move"
          @mousedown="startDrag"
        >
          <h3 class="text-xl font-semibold text-gray-900">
            {{ editingFeed ? 'Edit Feed' : 'Add New Feed' }}
          </h3>
          <button
            type="button"
            class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex justify-center items-center"
            @click="resetForm"
          >
            ✕
          </button>
        </div>

        <!-- Modal body -->
        <div class="p-4 space-y-4">
          <div>
            <Label for="feed_type" class="mb-2">Feed Type</Label>
            <select v-model="form.feed_type" id="feed_type" class="w-full border rounded p-2">
              <option value="Broiler">Broiler</option>
              <option value="Layer">Layer</option>
            </select>
          </div>

          <div>
            <Label for="feed_name" class="mb-2">Feed Name</Label>
            <Input v-model="form.feed_name" id="feed_name" />
            <span v-if="form.errors.feed_name" class="text-red-600 text-sm">{{ form.errors.feed_name }}</span>
          </div>

          <div>
            <Label for="status" class="mb-2">Status</Label>
            <select v-model="form.status" id="status" class="w-full border rounded p-2">
              <option value="Active">Active</option>
              <option value="Deactivated">Deactivated</option>
            </select>
          </div>
        </div>

        <!-- Modal footer -->
        <div class="flex justify-end p-4 border-t border-gray-200">
          <Button class="bg-gray-300 text-black mr-2" @click="resetForm">
            Cancel
          </Button>
          <Button class="bg-chicken text-white" @click="submit">
            {{ editingFeed ? 'Update' : 'Save' }}
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
