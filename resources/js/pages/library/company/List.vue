<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface Company {
  id: number;
  name: string;
  location: string;
  created_at: string;
}

const companies = ref<Company[]>([
  { id: 1, name: 'ABC Poultry Ltd.', location: 'Dhaka', created_at: '2025-08-01' },
  { id: 2, name: 'Fresh Eggs Co.', location: 'Chattogram', created_at: '2025-08-02' }
]);

const showModal = ref(false);
const editingCompany = ref<Company | null>(null);

const form = useForm({
  name: '',
  location: ''
});

// Add or Update Company
const submit = () => {
  if (!form.name.trim()) {
    form.setError('name', 'The company name is required.');
    return;
  }

  if (!form.location.trim()) {
    form.setError('location', 'The company location is required.');
    return;
  }

  if (editingCompany.value) {
    editingCompany.value.name = form.name;
    editingCompany.value.location = form.location;
  } else {
    companies.value.push({
      id: companies.value.length + 1,
      name: form.name,
      location: form.location,
      created_at: new Date().toISOString().slice(0, 10)
    });
  }

  form.reset();
  editingCompany.value = null;
  showModal.value = false;
};

// Edit Company
const editCompany = (company: Company) => {
  editingCompany.value = company;
  form.name = company.name;
  form.location = company.location;
  showModal.value = true;
};

// Breadcrumbs
const breadcrumbs = [
  { title: 'Master Setup', href: '/master-setup' },
  { title: 'Company', href: '/master-setup/company' }
];
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Companies" />
    <div class="px-4 py-6">
      <div class="flex items-center justify-between mb-4">
        <HeadingSmall title="Companies List" />
        <Button class="bg-chicken hover:bg-yellow-600 text-white" @click="showModal = true">
          Add New Company
        </Button>
      </div>

      <!-- Companies Table -->
      <table class="w-full border">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-2 border text-left">#</th>
            <th class="p-2 border text-left">Company Name</th>
            <th class="p-2 border text-left">Location</th>
            <th class="p-2 border text-left">Created At</th>
            <th class="p-2 border text-left">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(company, index) in companies" :key="company.id">
            <td class="p-2 border">{{ index + 1 }}</td>
            <td class="p-2 border">{{ company.name }}</td>
            <td class="p-2 border">{{ company.location }}</td>
            <td class="p-2 border">{{ company.created_at }}</td>
            <td class="p-2 border">
              <Button size="sm" class="bg-gray-500 hover:bg-gray-600 text-white" @click="editCompany(company)">
                Edit
              </Button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 bg-black/20 backdrop-blur-sm flex items-center justify-center">
      <div class="bg-white p-6 rounded-lg shadow-lg w-96">
        <h2 class="text-lg font-bold mb-4">{{ editingCompany ? 'Edit Company' : 'Add Company' }}</h2>

        <!-- Company Name -->
        <Label for="name" class="mb-1 block">Company Name</Label>
        <Input
          v-model="form.name"
          id="name"
          placeholder="Enter company name"
          class="mb-3 border border-gray-300 rounded-lg px-3 py-2 w-full text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500"
        />
        <div v-if="form.errors.name" class="text-red-500 text-sm mb-3">{{ form.errors.name }}</div>

        <!-- Company Location -->
        <Label for="location" class="mb-1 block">Company Location</Label>
        <Input
          v-model="form.location"
          id="location"
          placeholder="Enter company location"
          class="mb-3 border border-gray-300 rounded-lg px-3 py-2 w-full text-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-500"
        />
        <div v-if="form.errors.location" class="text-red-500 text-sm mb-3">{{ form.errors.location }}</div>

        <!-- Buttons -->
        <div class="flex justify-end gap-2">
          <Button variant="secondary" @click="showModal = false; editingCompany = null">Cancel</Button>
          <Button @click="submit">{{ editingCompany ? 'Update' : 'Save' }}</Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
