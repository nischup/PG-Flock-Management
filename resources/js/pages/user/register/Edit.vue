<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';

const props = defineProps({
  user: Object,
  roles: Array,
  permissions: Array,
  userRole: String,          // single role name
  userPermissions: Array,    // array of permission names
  companies: Array,          // array of { id, name }
  sheds: Array, 
});

const form = useForm({
  name: props.user.name || '',
  email: props.user.email || '',
  role: props.userRole || '',
  permissions: props.userPermissions || [],
  company_id: props.user.company_id || '',  
  shed_id: props.user.shed_id || '', 
});

function update() {
  form.put(`/users/${props.user.id}`, {
    preserveScroll: true,
  });
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Users', href: '/users' },
  { title: 'Edit User', href: `/users/${props.user.id}/edit` },
];
</script>

<template>
  <Head title="Edit User" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full p-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Edit User</h2>
        <Link
          href="/users"
          class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-md text-gray-700 dark:text-gray-200 text-sm font-medium transition"
        >
          ← Back
        </Link>
      </div>

      <!-- Form -->
      <form @submit.prevent="update" class="space-y-6 bg-white dark:bg-gray-900 shadow p-6 rounded-xl border border-gray-200 dark:border-gray-700">
        <!-- Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Name</label>
          <input
            v-model="form.name"
            type="text"
            class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-4 py-2"
          />
          <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
        </div>

        <!-- Email -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-4 py-2"
          />
          <div v-if="form.errors.email" class="text-red-500 text-sm mt-1">{{ form.errors.email }}</div>
        </div>


        <!-- Company -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Company</label>
            <select
              v-model="form.company_id"
              class="w-full rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-4 py-2"
            >
              <option value="0">-- All Company --</option>
              <option v-for="company in companies" :key="company.id" :value="company.id">
                {{ company.name }}
              </option>
            </select>
            <div v-if="form.errors.company_id" class="text-red-500 text-sm mt-1">{{ form.errors.company_id }}</div>
          </div>

        <!-- Shed -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Shed</label>
          <select
            v-model="form.shed_id"
            class="w-full rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-4 py-2"
          >
            <option value="0">-- All Shed --</option>
            <option v-for="shed in sheds" :key="shed.id" :value="shed.id">
              {{ shed.name }}
            </option>
          </select>
          <div v-if="form.errors.shed_id" class="text-red-500 text-sm mt-1">{{ form.errors.shed_id }}</div>
        </div>

        <!-- Role -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Assign Role</label>
          <select
            v-model="form.role"
            class="w-full rounded border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-4 py-2"
          >
            <option value="">-- Select a Role --</option>
            <option v-for="role in roles" :key="role.id" :value="role.name">
              {{ role.name }}
            </option>
          </select>
          <div v-if="form.errors.role" class="text-red-500 text-sm mt-1">{{ form.errors.role }}</div>
        </div>

        <!-- Permissions -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">Direct Permissions</label>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
            <div v-for="perm in permissions" :key="perm.id">
              <label class="inline-flex items-center space-x-2">
                <input
                  type="checkbox"
                  :value="perm.name"
                  v-model="form.permissions"
                  class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500"
                />
                <span class="text-gray-700 dark:text-gray-200">{{ perm.name }}</span>
              </label>
            </div>
          </div>
          <div v-if="form.errors.permissions" class="text-red-500 text-sm mt-1">{{ form.errors.permissions }}</div>
        </div>

        <!-- Submit -->
        <button
          type="submit"
          class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-md font-medium"
        >
          Update User
        </button>
      </form>
    </div>
  </AppLayout>
</template>
