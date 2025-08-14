<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm,} from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Unit {
  id: number;
  name: string;
  created_at: string;
  status: 'Active' | 'Deactivated';
}

const units = ref<Unit[]>([
  { id: 1, name: 'Kilogram', created_at: '2025-08-01', status: 'Active' },
  { id: 2, name: 'Gram', created_at: '2025-08-02', status: 'Active' },
  { id: 3, name: 'Liter', created_at: '2025-08-03', status: 'Deactivated' },
  { id: 4, name: 'Milliliter', created_at: '2025-08-04', status: 'Active' },
  { id: 5, name: 'Packet', created_at: '2025-08-05', status: 'Deactivated' },
  { id: 6, name: 'Box', created_at: '2025-08-06', status: 'Active' },
  { id: 7, name: 'Dozen', created_at: '2025-08-07', status: 'Active' },
  { id: 8, name: 'Piece', created_at: '2025-08-08', status: 'Deactivated' },
  { id: 9, name: 'Meter', created_at: '2025-08-09', status: 'Active' },
  { id: 10, name: 'Centimeter', created_at: '2025-08-10', status: 'Active' },
]);

const showModal = ref(false);
const editingUnit = ref<Unit | null>(null);
const openDropdownId = ref<number | null>(null);

const form = useForm({
  name: '',
  status: 'Active'
});

// Add or Update Unit
const submit = () => {
  if (!form.name.trim()) {
    form.setError('name', 'The name field is required.');
    return;
  }

  if (editingUnit.value) {
    editingUnit.value.name = form.name;
    editingUnit.value.status = form.status as 'Active' | 'Deactivated';
  } else {
    units.value.push({
      id: units.value.length + 1,
      name: form.name,
      created_at: new Date().toISOString().slice(0, 10),
      status: form.status as 'Active' | 'Deactivated'
    });
  }

  form.reset();
  editingUnit.value = null;
  showModal.value = false;
};

// Edit Unit
const editUnit = (unit: Unit) => {
  editingUnit.value = unit;
  form.name = unit.name;
  form.status = unit.status;
  showModal.value = true;
  openDropdownId.value = null;
};

// Toggle Active / Deactivated
const toggleStatus = (unit: Unit) => {
  unit.status = unit.status === 'Active' ? 'Deactivated' : 'Active';
  openDropdownId.value = null;
};

// Delete Unit
const deleteUnit = (id: number) => {
  units.value = units.value.filter(u => u.id !== id);
  openDropdownId.value = null;
};

const toggleDropdown = (id: number) => {
  openDropdownId.value = openDropdownId.value === id ? null : id;
};

const breadcrumbs = [{ title: 'Unit', href: '/unit' }];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Units" />
    <div class="px-4 py-6">
      <div class="flex items-center justify-between mb-4">
        <HeadingSmall title="Units List" />
        <Button class="bg-chicken hover:bg-yellow-600 text-white" @click="showModal = true">
          Add New Unit
        </Button>
      </div>

      <!-- Units Table -->
      <table class="w-full border">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-2 border text-left">#</th>
            <th class="p-2 border text-left">Name</th>
            <th class="p-2 border text-left">Status</th>
            <th class="p-2 border text-left">Created At</th>
            <th class="p-2 border text-left">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(unit, index) in units" :key="unit.id">
            <td class="p-2 border">{{ index + 1 }}</td>
            <td class="p-2 border">{{ unit.name }}</td>
            <td class="p-2 border">
              <span :class="unit.status === 'Active' ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                {{ unit.status }}
              </span>
            </td>
            <td class="p-2 border">{{ unit.created_at }}</td>
            <td class="p-2 border relative">
              <!-- Dropdown Toggle -->
              <Button size="sm" class="bg-gray-500 hover:bg-gray-600 text-white" @click="toggleDropdown(unit.id)">
                Actions ▼
              </Button>

              <!-- Dropdown Menu -->
              <div
                v-if="openDropdownId === unit.id"
                class="absolute right-0 mt-1 w-40 bg-white border rounded shadow-md z-10"
              >
                <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="editUnit(unit)">✏ Edit</button>
                <button
                  class="w-full text-left px-4 py-2 hover:bg-gray-100"
                  @click="toggleStatus(unit)"
                >
                  {{ unit.status === 'Active' ? 'Deactivate' : 'Activate' }}
                </button>
                <button
                  class="w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600"
                  @click="deleteUnit(unit.id)"
                >
                  🗑 Delete
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-white bg-opacity-50 flex items-center justify-center">
      <div class="bg-white p-6 rounded shadow w-96">
        <h2 class="text-lg font-bold mb-4">{{ editingUnit ? 'Edit Unit' : 'Add Unit' }}</h2>
        <Label for="name">Name</Label>
        <Input v-model="form.name" id="name" placeholder="Enter unit name" class="mb-2" />
        <div v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</div>

        <Label for="status">Status</Label>
        <select v-model="form.status" id="status" class="mb-2 border rounded px-2 py-1 w-full">
          <option value="Active">Active</option>
          <option value="Deactivated">Deactivated</option>
        </select>

        <div class="mt-4 flex justify-end gap-2">
          <Button variant="secondary" @click="showModal = false; editingUnit = null">Cancel</Button>
          <Button @click="submit">{{ editingUnit ? 'Update' : 'Save' }}</Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
