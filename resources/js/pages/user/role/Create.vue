<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { ref, computed, reactive, watch } from 'vue';
import type { BreadcrumbItem } from '@/types';
import { useNotifier } from "@/composables/useNotifier"
// Accept flexible shapes coming from Spatie
type PermInput = string | { id: number | string; name: string; [k: string]: any };

const props = defineProps<{
  // Can be an array or a grouped record
  permissions: PermInput[] | Record<string, PermInput[]>;
}>();

// Form
const form = useForm({
  name: '',
  permissions: [] as string[], // selected permission NAMES
});

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'User Roles', href: '/user-role' },
  { title: 'Create', href: '/user-role/create' },
];

// Flatten props.permissions into a single array of items
const flatPermissions = computed<PermInput[]>(() => {
  return Array.isArray(props.permissions)
    ? props.permissions
    : Object.values(props.permissions).flat();
});

// Helper to extract the permission name safely
function getPermName(p: PermInput): string {
  if (typeof p === 'string') return p;
  if (p && typeof p === 'object' && typeof p.name === 'string') return p.name;
  return '';
}

// Group by module (text before the dot)
const groupedPermissions = computed(() => {
  const groups: Record<string, { key: string; action: string }[]> = {};
  for (const item of flatPermissions.value) {
    const full = getPermName(item);
    if (!full) continue;
    // e.g. "doc-receive.view" => module="doc-receive", action="view"
    const [module = 'other', action = 'unknown'] = full.split('.');
    if (!groups[module]) groups[module] = [];
    groups[module].push({ key: full, action });
  }
  return groups;
});

// Pretty print action label
function formatAction(s: string) {
  return s.replace(/[-_]/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}

// Accordion open/close state
const openAccordion = reactive<Record<string, boolean>>({});
watch(groupedPermissions, (gp) => {
  // Open first module by default; keep existing states
  const modules = Object.keys(gp);
  modules.forEach((m, i) => {
    if (!(m in openAccordion)) openAccordion[m] = i === 0;
  });
}, { immediate: true });

// Toggle all permissions in a module
function toggleModule(module: string) {
  const moduleKeys = groupedPermissions.value[module].map(p => p.key);
  const allSelected = moduleKeys.every(k => form.permissions.includes(k));
  if (allSelected) {
    // remove all from this module
    form.permissions = form.permissions.filter(k => !moduleKeys.includes(k));
  } else {
    // add all from this module
    form.permissions = Array.from(new Set([...form.permissions, ...moduleKeys]));
  }
}

// Is module fully selected?
function isModuleAllSelected(module: string) {
  return groupedPermissions.value[module].every(p => form.permissions.includes(p.key));
}


const { showSuccess, showError } = useNotifier(); // auto-shows flash messages

function submit() {
  form.post(route('user-role.store'), {
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
</script>

<template>
  <Head title="Create Role" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full p-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Create Role</h2>
        <Link
          href="/user-role"
          class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-md text-gray-700 dark:text-gray-200 text-sm font-medium transition"
        >
          ← Back
        </Link>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-6 bg-white dark:bg-gray-900 shadow p-6 rounded-xl border border-gray-200 dark:border-gray-700">
        <!-- Role Name -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Role Name</label>
          <input
            v-model="form.name"
            type="text"
            class="w-full rounded-md border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-4 py-2"
          />
          <InputError :message="form.errors.name" class="mt-1" />
        </div>

        <!-- Accordions per module -->
        <div v-for="(perms, module) in groupedPermissions" :key="module" class="mb-4 border rounded-md">
          <!-- Accordion Header -->
          <div
            class="flex justify-between items-center px-4 py-2 bg-gray-100 dark:bg-gray-800 cursor-pointer"
            @click="openAccordion[module] = !openAccordion[module]"
          >
            <span class="font-medium capitalize">{{ module }}</span>
            <label class="inline-flex items-center space-x-2" @click.stop>
              <input
                type="checkbox"
                :checked="isModuleAllSelected(module)"
                @change.prevent="toggleModule(module)"
                class="rounded border-gray-300 dark:border-gray-600 text-indigo-600"
              />
              <span class="text-sm text-gray-600 dark:text-gray-300">Select All</span>
            </label>
          </div>

          <!-- Accordion Body -->
          <div v-show="openAccordion[module]" class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
            <div v-for="perm in perms" :key="perm.key">
              <label class="inline-flex items-center space-x-2">
                <input
                  type="checkbox"
                  v-model="form.permissions"
                  :value="perm.key"
                  class="rounded border-gray-300 dark:border-gray-600 text-indigo-600"
                />
                <span class="text-gray-700 dark:text-gray-200">{{ formatAction(perm.action) }}</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Submit -->
        <button
          type="submit"
          class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-md font-medium"
        >
          Create Role
        </button>
      </form>
    </div>
  </AppLayout>
</template>
