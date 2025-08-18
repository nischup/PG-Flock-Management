<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { type BreadcrumbItem } from '@/types';
import InputError from '@/components/InputError.vue';
import { useNotifier } from "@/composables/useNotifier"
const props = defineProps({
  roles: Array,
  permissions: Array, // [{ id: 1, name: 'user.view' }, { id: 2, name: 'user.edit' }]
  companies: Array,
  sheds: Array,
});

const form = useForm({
  name: '',
  email: '',
  role: '',
  password: '',
  permissions: [],
  company_id: 0,
  shed_id: 0,
});

const { showSuccess, showError } = useNotifier(); // auto-shows flash messages

function submit() {
  form.post(route('users.store'), {
    onSuccess: () => {
      showSuccess('User created successfully.');
    },
    onError: () => {
      if (form.errors.general) {
        showError(form.errors.general);
      } else {
        showError('Validation failed. Please check the form.');
      }
    },
  });
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Users', href: '/user-register' },
  { title: 'Add User', href: '/user-register/create' },
];

// Group permissions by prefix before "."
const groupedPermissions = computed(() => {
  const groups: Record<string, { id: number; name: string; label: string }[]> = {};
  props.permissions.forEach((perm: any) => {
    const [prefix, action] = perm.name.split('.');
    if (!groups[prefix]) groups[prefix] = [];
    groups[prefix].push({
      id: perm.id,
      name: perm.name,
      label: actionLabel(action, prefix), // nice label
    });
  });
  return groups;
});

function actionLabel(action: string, prefix: string) {
  const map: Record<string, string> = {
    view: `View ${capitalize(prefix)}`,
    create: `Create ${capitalize(prefix)}`,
    edit: `Edit ${capitalize(prefix)}`,
    delete: `Delete ${capitalize(prefix)}`,
  };
  return map[action] || `${capitalize(action)} ${capitalize(prefix)}`;
}

function capitalize(s: string) {
  return s.charAt(0).toUpperCase() + s.slice(1);
}

// Accordion state
const expandedGroups = ref<string[]>([]);

function toggleGroup(group: string) {
  expandedGroups.value.includes(group)
    ? expandedGroups.value = expandedGroups.value.filter(g => g !== group)
    : expandedGroups.value.push(group);
}

function isGroupExpanded(group: string) {
  return expandedGroups.value.includes(group);
}

function isGroupAllSelected(group: string) {
  const perms = groupedPermissions.value[group]?.map(p => p.name) || [];
  return perms.length > 0 && perms.every(p => form.permissions.includes(p));
}

function toggleSelectAll(group: string) {
  const perms = groupedPermissions.value[group]?.map(p => p.name) || [];
  if (isGroupAllSelected(group)) {
    form.permissions = form.permissions.filter(p => !perms.includes(p));
  } else {
    form.permissions = Array.from(new Set([...form.permissions, ...perms]));
  }
}
</script>

<template>
  <Head title="Add User" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full p-6">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold">Add User</h2>
        <Link
          href="/user-register"
          class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-md text-sm"
        >
          ← Back
        </Link>
      </div>

      <form @submit.prevent="submit" class="space-y-6 bg-white shadow p-6 rounded-xl border border-gray-200">
        <!-- Name -->
        <div>
          <label class="block mb-1 font-medium">Name</label>
          <input v-model="form.name" type="text" class="w-full border rounded px-3 py-2" />
          
          <InputError :message="form.errors.name" class="mt-1" />
        </div>

        <!-- Email -->
        <div>
          <label class="block mb-1 font-medium">Email</label>
          <input v-model="form.email" type="email" class="w-full border rounded px-3 py-2" />
          <InputError :message="form.errors.email" class="mt-1" />
        </div>

        <!-- Password -->
        <div>
          <label class="block mb-1 font-medium">Password</label>
          <input
            v-model="form.password"
            type="password"
            class="w-full border rounded px-3 py-2"
            placeholder="Enter password"
          />
          <InputError :message="form.errors.password" class="mt-1" />
        </div>

        <!-- Company -->
        <div>
          <label class="block mb-2 font-medium">Assign Company</label>
          <select v-model="form.company_id" class="w-full border rounded px-3 py-2">
            <option value="0">-- All Company --</option>
            <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>

        <!-- Shed -->
        <div>
          <label class="block mb-2 font-medium">Assign Shed</label>
          <select v-model="form.shed_id" class="w-full border rounded px-3 py-2">
            <option value="0">-- All Shed --</option>
            <option v-for="s in sheds" :key="s.id" :value="s.id">{{ s.name }}</option>
          </select>
        </div>

        <!-- Role -->
        <div>
          <label class="block mb-2 font-medium">Assign Role</label>
          <select v-model="form.role" class="w-full border rounded px-3 py-2">
            <option value="">-- Select Role --</option>
            <option v-for="role in roles" :key="role.id" :value="role.name">{{ role.name }}</option>
          </select>
        </div>

        <!-- Permissions Accordion -->
        <div>
          <label class="block mb-2 font-medium">Direct Permissions</label>
          <div
            v-for="(perms, group) in groupedPermissions"
            :key="group"
            class="border rounded mb-2 overflow-hidden"
          >
            <!-- Group Header -->
            <div
              class="flex justify-between items-center px-4 py-2 bg-gray-100 cursor-pointer"
              @click="toggleGroup(group)"
            >
              <span class="capitalize font-semibold">{{ group }}</span>
              <label class="inline-flex items-center space-x-2" @click.stop>
                <input
                  type="checkbox"
                  :checked="isGroupAllSelected(group)"
                  @change="toggleSelectAll(group)" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600"
                />
                  <span class="text-sm text-gray-600 dark:text-gray-300">Select All</span>
                </label>
            </div>

            <!-- Group Permissions -->
            <div v-show="isGroupExpanded(group)" class="px-4 py-2 grid grid-cols-2 gap-2">
              <div v-for="perm in perms" :key="perm.id" class="flex items-center space-x-2">
                <input
                  type="checkbox"
                  :value="perm.name"
                  v-model="form.permissions"
                />
                <span>{{ perm.label }}</span>
              </div>
            </div>
          </div>
        </div>

        <button
          type="submit"
          class="bg-chicken hover:bg-yellow-600 text-white px-5 py-2 rounded-md"
        >
          Create User
        </button>
      </form>
    </div>
  </AppLayout>
</template>
