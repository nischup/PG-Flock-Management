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

const showModal = ref(false);
const editingFeed = ref<Feed | null>(null);
const openDropdownId = ref<number | null>(null);

const form = useForm({
  feed_type: 'Broiler',
  feed_name: '',
  status: 'Active'
});

// Add or Update Feed
const submit = () => {
  if (!form.feed_name.trim()) {
    form.setError('feed_name', 'The feed name is required.');
    return;
  }

  if (editingFeed.value) {
    editingFeed.value.feed_type = form.feed_type as 'Broiler' | 'Layer';
    editingFeed.value.feed_name = form.feed_name;
    editingFeed.value.status = form.status as 'Active' | 'Deactivated';
  } else {
    feeds.value.push({
      id: feeds.value.length + 1,
      feed_type: form.feed_type as 'Broiler' | 'Layer',
      feed_name: form.feed_name,
      created_at: new Date().toISOString().slice(0, 10),
      status: form.status as 'Active' | 'Deactivated'
    });
  }

  form.reset();
  form.feed_type = 'Broiler';
  editingFeed.value = null;
  showModal.value = false;
};

// Edit Feed
const editFeed = (feed: Feed) => {
  editingFeed.value = feed;
  form.feed_type = feed.feed_type;
  form.feed_name = feed.feed_name;
  form.status = feed.status;
  showModal.value = true;
  openDropdownId.value = null;
};

// Toggle Active / Deactivated
const toggleStatus = (feed: Feed) => {
  feed.status = feed.status === 'Active' ? 'Deactivated' : 'Active';
  openDropdownId.value = null;
};

// Toggle dropdown menu
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
      <div class="flex items-center justify-between mb-4">
        <HeadingSmall title="Feeds List" />
        <Button class="bg-chicken hover:bg-yellow-600 text-white" @click="showModal = true">
          Add New Feed
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
                <button
                  class="w-full text-left px-4 py-2 hover:bg-gray-100"
                  @click="toggleStatus(feed)"
                >
                  {{ feed.status === 'Active' ? 'Deactivate' : 'Activate' }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
<div v-if="showModal" class="fixed inset-0 bg-black/20 backdrop-blur-sm flex items-center justify-center">
  <div class="bg-white p-6 rounded-lg shadow-lg w-96">
    <h2 class="text-lg font-bold mb-4">{{ editingFeed ? 'Edit Feed' : 'Add Feed' }}</h2>

    <!-- Feed Type -->
    <Label for="feed_type" class="mb-1 block">Feed Type</Label>
    <select
      v-model="form.feed_type"
      id="feed_type"
      class="mb-3 border border-gray-300 rounded-lg px-3 py-2 w-full text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500"
    >
      <option value="Broiler">Broiler</option>
      <option value="Layer">Layer</option>
    </select>

    <!-- Feed Name -->
    <Label for="feed_name" class="mb-1 block">Feed Name</Label>
    <Input
      v-model="form.feed_name"
      id="feed_name"
      placeholder="Enter feed name"
      class="mb-3 border border-gray-300 rounded-lg px-3 py-2 w-full text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500"
    />
    <div v-if="form.errors.feed_name" class="text-red-500 text-sm mb-3">
      {{ form.errors.feed_name }}
    </div>

    <!-- Status -->
    <Label for="status" class="mb-1 block">Status</Label>
    <select
      v-model="form.status"
      id="status"
      class="mb-4 border border-gray-300 rounded-lg px-3 py-2 w-full text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500"
    >
      <option value="Active">Active</option>
      <option value="Deactivated">Deactivated</option>
    </select>

    <!-- Buttons -->
    <div class="flex justify-end gap-2">
      <Button variant="secondary" @click="showModal = false; editingFeed = null">Cancel</Button>
      <Button @click="submit">{{ editingFeed ? 'Update' : 'Save' }}</Button>
    </div>
  </div>
</div>
  </AppLayout>
</template>
