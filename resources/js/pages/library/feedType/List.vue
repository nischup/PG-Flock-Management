<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface FeedType {
  id: number;
  name: string;
  created_at: string;
  status: 'Active' | 'Deactivated';
}

const feedTypes = ref<FeedType[]>([
  { id: 1, name: 'Starter Feed', created_at: '2025-08-01', status: 'Active' },
  { id: 2, name: 'Grower Feed', created_at: '2025-08-02', status: 'Active' }
]);

const showModal = ref(false);
const editingFeedType = ref<FeedType | null>(null);

const form = useForm({
  name: '',
  status: 'Active'
});

// Add or Update Feed Type
const submit = () => {
  if (!form.name.trim()) {
    form.setError('name', 'The feed type name is required.');
    return;
  }

  if (editingFeedType.value) {
    editingFeedType.value.name = form.name;
    editingFeedType.value.status = form.status as 'Active' | 'Deactivated';
  } else {
    feedTypes.value.push({
      id: feedTypes.value.length + 1,
      name: form.name,
      created_at: new Date().toISOString().slice(0, 10),
      status: form.status as 'Active' | 'Deactivated'
    });
  }

  form.reset();
  editingFeedType.value = null;
  showModal.value = false;
};

// Edit Feed Type
const editFeedType = (feedType: FeedType) => {
  editingFeedType.value = feedType;
  form.name = feedType.name;
  form.status = feedType.status;
  showModal.value = true;
};

// Toggle Status
const toggleStatus = (feedType: FeedType) => {
  feedType.status = feedType.status === 'Active' ? 'Deactivated' : 'Active';
};

// Breadcrumbs
const breadcrumbs = [
  { title: 'Master Setup', href: '/master-setup' },
  { title: 'Feed Type', href: '/master-setup/feed-type' }
];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Feed Types" />
    <div class="px-4 py-6">
      <div class="flex items-center justify-between mb-4">
        <HeadingSmall title="Feed Types List" />
        <Button class="bg-chicken hover:bg-yellow-600 text-white" @click="showModal = true">
          Add New Feed Type
        </Button>
      </div>

      <!-- Table -->
      <table class="w-full border">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-2 border text-left">#</th>
            <th class="p-2 border text-left">Feed Type</th>
            <th class="p-2 border text-left">Status</th>
            <th class="p-2 border text-left">Created At</th>
            <th class="p-2 border text-left">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(feedType, index) in feedTypes" :key="feedType.id">
            <td class="p-2 border">{{ index + 1 }}</td>
            <td class="p-2 border">{{ feedType.name }}</td>
            <td class="p-2 border">
              <span :class="feedType.status === 'Active' ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                {{ feedType.status }}
              </span>
            </td>
            <td class="p-2 border">{{ feedType.created_at }}</td>
            <td class="p-2 border">
              <Button size="sm" class="bg-gray-500 hover:bg-gray-600 text-white" @click="editFeedType(feedType)">
                Edit
              </Button>
              <Button size="sm" class="bg-gray-500 hover:bg-gray-600 text-white ml-1" @click="toggleStatus(feedType)">
                {{ feedType.status === 'Active' ? 'Deactivate' : 'Activate' }}
              </Button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/20 backdrop-blur-sm flex items-center justify-center">
      <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h2 class="text-lg font-bold mb-4">{{ editingFeedType ? 'Edit Feed Type' : 'Add Feed Type' }}</h2>

        <!-- Name -->
        <Label for="name" class="mb-1 block">Feed Type</Label>
        <Input
          v-model="form.name"
          id="name"
          placeholder="Enter feed type name"
          class="mb-3 border border-gray-300 rounded-lg px-3 py-2 w-full text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500"
        />
        <div v-if="form.errors.name" class="text-red-500 text-sm mb-3">{{ form.errors.name }}</div>

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
          <Button variant="secondary" @click="showModal = false; editingFeedType = null">Cancel</Button>
          <Button @click="submit">{{ editingFeedType ? 'Update' : 'Save' }}</Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
