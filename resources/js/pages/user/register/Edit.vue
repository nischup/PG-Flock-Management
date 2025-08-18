<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import type { BreadcrumbItem } from '@/types';
import InputError from '@/components/InputError.vue';
const props = defineProps({
  user: Object,             // User object
  roles: Array,             // All roles
  permissions: Array,       // All permissions [{id, name}]
  userRole: String,         // User's current role
  userPermissions: Array,   // User's current direct permissions (names)
  companies: Array,         // [{id, name}]
  sheds: Array,             // [{id, name}]
});

// Form initialization with user's current values
const form = useForm({
  name: props.user.name || '',
  email: props.user.email || '',
  role: props.userRole || '',
  permissions: props.userPermissions || [],
  company_id: props.user.company_id || 0,
  shed_id: props.user.shed_id || 0,
  password: '',  
});

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Users', href: '/user-register' },
  { title: 'Edit User', href: `/user-register/${props.user.id}/edit` },
];

// --- Permissions Accordion Logic ---

// Group permissions by prefix (before ".")
const groupedPermissions = computed(() => {
  const groups: Record<string, { id: number; name: string; label: string }[]> = {};
  props.permissions.forEach((perm: any) => {
    const [prefix, action] = perm.name.split('.');
    if (!groups[prefix]) groups[prefix] = [];
    groups[prefix].push({
      id: perm.id,
      name: perm.name,
      label: actionLabel(action, prefix),
    });
  });
  return groups;
});

// Map action name to friendly label
function actionLabel(action: string, prefix: string) {
  const map: Record<string, string> = {
    view: `View ${capitalize(prefix)}`,
    create: `Create ${capitalize(prefix)}`,
    edit: `Edit ${capitalize(prefix)}`,
    delete: `Delete ${capitalize(prefix)}`,
  };
  return map[action] || `${capitalize(action)} ${capitalize(prefix)}`;
}

// Capitalize first letter
function capitalize(s: string) {
  return s.charAt(0).toUpperCase() + s.slice(1);
}

// Accordion open states
const expandedGroups = ref<string[]>([]);
function toggleGroup(group: string) {
  expandedGroups.value.includes(group)
    ? (expandedGroups.value = expandedGroups.value.filter((g) => g !== group))
    : expandedGroups.value.push(group);
}
function isGroupExpanded(group: string) {
  return expandedGroups.value.includes(group);
}

// Select all / deselect all for group
function isGroupAllSelected(group: string) {
  const perms = groupedPermissions.value[group]?.map((p) => p.name) || [];
  return perms.length > 0 && perms.every((p) => form.permissions.includes(p));
}

function toggleSelectAll(group: string) {
  const perms = groupedPermissions.value[group]?.map((p) => p.name) || [];
  if (isGroupAllSelected(group)) {
    form.permissions = form.permissions.filter((p) => !perms.includes(p));
  } else {
    form.permissions = Array.from(new Set([...form.permissions, ...perms]));
  }
}

// --- Form Submit ---
function update() {
  form.put(`/user-register/${props.user.id}`, { preserveScroll: true });
}
</script>

<template>
  <Head title="Edit User" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full p-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Edit User</h2>
        <Link
          href="/user-register"
          class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-md text-gray-700 dark:text-gray-200 text-sm font-medium transition"
        >
          ← Back
        </Link>
      </div>

      <!-- Form -->
      <form @submit.prevent="update" class="space-y-6 bg-white dark:bg-gray-900 shadow p-6 rounded-xl border border-gray-200 dark:border-gray-700">
        <!-- Name -->
        <div>
          <label class="block text-sm font-medium mb-1">Name</label>
          <input v-model="form.name" type="text" class="w-full rounded-md border border-gray-300 dark:border-gray-700 px-4 py-2" />
          
          <InputError :message="form.errors.name" class="mt-1" />
        </div>

        <!-- Email -->
        <div>
          <label class="block text-sm font-medium mb-1">Email</label>
          <input v-model="form.email" type="email" class="w-full rounded-md border border-gray-300 dark:border-gray-700 px-4 py-2" />
          <InputError :message="form.errors.email" class="mt-1" />
        </div>
        <!-- Password -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">New Password</label>
          <input
            v-model="form.password"
            type="password"
            placeholder="New password"
            class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-4 py-2"
          />
          <InputError :message="form.errors.password" class="mt-1" />
        </div>
        <!-- Company -->
        <div>
          <label class="block text-sm font-medium mb-1">Company</label>
          <select v-model="form.company_id" class="w-full rounded border border-gray-300 dark:border-gray-700 px-4 py-2">
            <option value="0">-- All Company --</option>
            <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <InputError :message="form.errors.company_id" class="mt-1" />
        </div>

        <!-- Shed -->
        <div>
          <label class="block text-sm font-medium mb-1">Shed</label>
          <select v-model="form.shed_id" class="w-full rounded border border-gray-300 dark:border-gray-700 px-4 py-2">
            <option value="0">-- All Shed --</option>
            <option v-for="s in sheds" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
           <InputError :message="form.errors.shed_id" class="mt-1" />
        </div>

        <!-- Role -->
        <div>
          <label class="block text-sm font-medium mb-1">Assign Role</label>
          <select v-model="form.role" class="w-full rounded border border-gray-300 dark:border-gray-700 px-4 py-2">
            <option value="">-- Select Role --</option>
            <option v-for="r in roles" :key="r.id" :value="r.name">{{ r.name }}</option>
          </select>
          <InputError :message="form.errors.role" class="mt-1" />
        </div>

        <!-- Permissions Accordion -->
        <div>
          <label class="block text-sm font-medium mb-2">Direct Permissions</label>
          <div v-for="(perms, group) in groupedPermissions" :key="group" class="border rounded mb-2 overflow-hidden">
            <!-- Header -->
            <div class="flex justify-between items-center px-4 py-2 bg-gray-100 cursor-pointer"
                 @click="toggleGroup(group)">
              <span class="capitalize font-semibold">{{ group }}</span>
              <label class="inline-flex items-center space-x-2" @click.stop>
                <input type="checkbox" :checked="isGroupAllSelected(group)" @change="toggleSelectAll(group)" class="rounded border-gray-300 text-indigo-600" />
                <span class="text-sm text-gray-600">Select All</span>
              </label>
            </div>

            <!-- Body -->
            <div v-show="isGroupExpanded(group)" class="px-4 py-2 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
              <div v-for="perm in perms" :key="perm.id" class="flex items-center space-x-2">
                <input type="checkbox" :value="perm.name" v-model="form.permissions" class="rounded border-gray-300 text-indigo-600" />
                <span>{{ perm.label }}</span>
              </div>
            </div>
          </div>
          <InputError :message="form.errors.permissions" class="mt-1" />
        </div>

        <!-- Submit -->
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-md font-medium">
          Update User
        </button>
      </form>
    </div>
  </AppLayout>
</template>
