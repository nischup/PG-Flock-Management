<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed, reactive, watch } from 'vue';
import type { BreadcrumbItem } from '@/types';

// Props from backend
const props = defineProps<{
  role: { id: number; name: string };
  permissions: Array<{ id: number; name: string }>;
  rolePermissions: string[]; // names of permissions assigned to role
}>();

// Form initialization
const form = useForm({
  name: props.role.name,
  permissions: [...props.rolePermissions], // preselect current role permissions
});

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'User Roles', href: '/user-role' },
  { title: 'Edit', href: `/user-role/${props.role.id}/edit` },
];

// Group permissions by prefix (module)
const groupedPermissions = computed(() => {
  const groups: Record<string, { id: number; name: string; label: string }[]> = {};
  props.permissions.forEach((perm) => {
    const [module, action] = perm.name.split('.');
    if (!groups[module]) groups[module] = [];
    groups[module].push({
      id: perm.id,
      name: perm.name,
      label: actionLabel(action, module),
    });
  });
  return groups;
});

// Helper: format action label
function actionLabel(action: string, module: string) {
  const map: Record<string, string> = {
    view: `View ${capitalize(module)}`,
    create: `Create ${capitalize(module)}`,
    edit: `Edit ${capitalize(module)}`,
    delete: `Delete ${capitalize(module)}`,
  };
  return map[action] || `${capitalize(action)} ${capitalize(module)}`;
}

function capitalize(s: string) {
  return s.charAt(0).toUpperCase() + s.slice(1);
}

// Accordion state
const expandedGroups = reactive<Record<string, boolean>>({});
watch(groupedPermissions, (gp) => {
  Object.keys(gp).forEach((module, i) => {
    if (!(module in expandedGroups)) expandedGroups[module] = i === 0; // first module expanded
  });
}, { immediate: true });

// Toggle accordion
function toggleGroup(group: string) {
  expandedGroups[group] = !expandedGroups[group];
}

// Check if all permissions in a module are selected
function isGroupAllSelected(group: string) {
  const perms = groupedPermissions.value[group].map(p => p.name);
  return perms.every(p => form.permissions.includes(p));
}

// Toggle all permissions in a module
function toggleSelectAll(group: string) {
  const perms = groupedPermissions.value[group].map(p => p.name);
  if (isGroupAllSelected(group)) {
    form.permissions = form.permissions.filter(p => !perms.includes(p));
  } else {
    form.permissions = Array.from(new Set([...form.permissions, ...perms]));
  }
}

// Submit form
function updateRole() {
  form.put(`/user-role/${props.role.id}`, { preserveScroll: true });
}
</script>

<template>
  <Head title="Edit Role" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <h2 class="text-2xl font-bold mb-4">Edit Role</h2>

      <form @submit.prevent="updateRole" class="space-y-6 bg-white p-6 rounded-lg shadow">
        <!-- Role Name -->
        <div>
          <label class="block text-sm font-medium mb-1">Role Name</label>
          <input v-model="form.name" type="text" class="w-full rounded border px-3 py-2" />
          <div v-if="form.errors.name" class="text-red-500 text-sm">{{ form.errors.name }}</div>
        </div>

        <!-- Permissions -->
        <div>
          <label class="block text-sm font-medium mb-2">Assign Permissions</label>
          <div v-for="(perms, group) in groupedPermissions" :key="group" class="border rounded mb-2">
            <!-- Accordion Header -->
            <div
              class="px-4 py-2 font-semibold bg-gray-100 flex justify-between items-center cursor-pointer"
              @click="toggleGroup(group)"
            >
              <span class="capitalize">{{ group }}</span>
              <label class="inline-flex items-center space-x-2" @click.stop>
                <input
                  type="checkbox"
                  :checked="isGroupAllSelected(group)"
                  @change="toggleSelectAll(group)"
                  class="rounded border-gray-300 text-indigo-600"
                />
                <span class="text-sm text-gray-600">Select All</span>
              </label>
            </div>

            <!-- Accordion Body -->
            <div v-show="expandedGroups[group]" class="px-4 py-2 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
              <div v-for="perm in perms" :key="perm.id" class="flex items-center space-x-2">
                <input type="checkbox" :value="perm.name" v-model="form.permissions" class="rounded border-gray-300 text-indigo-600" />
                <span>{{ perm.label }}</span>
              </div>
            </div>
          </div>

          <div v-if="form.errors.permissions" class="text-red-500 text-sm">{{ form.errors.permissions }}</div>
        </div>

        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded">
          Update Role
        </button>
      </form>
    </div>
  </AppLayout>
</template>
