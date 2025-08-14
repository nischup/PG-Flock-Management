<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import type { BreadcrumbItem } from '@/types';

// Props from Inertia
const props = defineProps<{
  role: { id: number; name: string };
  permissions: { id: number; name: string }[];
  rolePermissions: string[]; // Existing permissions
}>();

// Form state
const form = useForm({
  name: props.role.name || '',
  permissions: props.rolePermissions || [], // pre-selected permissions
});

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'User Role', href: '/user-role' },
  { title: 'Edit', href: `/user-role/${props.role.id}/edit` },
];

// Group permissions by module (prefix before ".")
const groupedPermissions = computed(() => {
  const groups: Record<string, { id: number; name: string; label: string }[]> = {};
  props.permissions.forEach((perm) => {
    const [prefix, action] = perm.name.split('.');
    if (!groups[prefix]) groups[prefix] = [];
    groups[prefix].push({
      id: perm.id,
      name: perm.name,
      label: formatLabel(action, prefix),
    });
  });
  return groups;
});

// Label formatting
function formatLabel(action: string, prefix: string) {
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

// Accordion states
const expandedGroups = ref<string[]>([]);
function toggleGroup(group: string) {
  expandedGroups.value.includes(group)
    ? (expandedGroups.value = expandedGroups.value.filter((g) => g !== group))
    : expandedGroups.value.push(group);
}
function isGroupExpanded(group: string) {
  return expandedGroups.value.includes(group);
}

// Select all / deselect all per group
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

// Submit update
function updateRole() {
  form.put(`/user-role/${props.role.id}`, { preserveScroll: true });
}
</script>

<template>
  <Head title="Edit Role" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full p-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Edit Role</h2>
        <Link
          href="/user-role"
          class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-md text-gray-700 dark:text-gray-200 text-sm font-medium transition"
        >
          ← Back
        </Link>
      </div>

      <!-- Form -->
      <form @submit.prevent="updateRole" class="space-y-6 bg-white dark:bg-gray-900 shadow p-6 rounded-xl border border-gray-200 dark:border-gray-700">
        <!-- Role Name -->
        <div>
          <label class="block text-sm font-medium mb-1">Role Name</label>
          <input
            v-model="form.name"
            type="text"
            class="w-full rounded-md border border-gray-300 dark:border-gray-700 px-4 py-2"
          />
          <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
        </div>

        <!-- Permissions Accordion -->
        <div>
          <label class="block text-sm font-medium mb-2">Permissions</label>
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
          <div v-if="form.errors.permissions" class="text-red-500 text-sm mt-1">{{ form.errors.permissions }}</div>
        </div>

        <!-- Submit -->
        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-md font-medium">
          Update Role
        </button>
      </form>
    </div>
  </AppLayout>
</template>
