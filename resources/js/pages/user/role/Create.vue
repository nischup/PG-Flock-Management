<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ref, computed, reactive, watch } from 'vue';
import type { BreadcrumbItem } from '@/types';
import { useNotifier } from "@/composables/useNotifier";
import { Shield, Users, ChevronDown, ChevronRight, Check, LoaderCircle, ArrowLeft, Plus, Eye, Edit, Trash2 } from 'lucide-vue-next';

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
  { title: 'Create Role', href: '/user-role/create' },
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

// Is module partially selected?
function isModulePartiallySelected(module: string) {
  const moduleKeys = groupedPermissions.value[module].map(p => p.key);
  const selectedCount = moduleKeys.filter(k => form.permissions.includes(k)).length;
  return selectedCount > 0 && selectedCount < moduleKeys.length;
}

// Get module selection count
function getModuleSelectionCount(module: string) {
  const moduleKeys = groupedPermissions.value[module].map(p => p.key);
  return moduleKeys.filter(k => form.permissions.includes(k)).length;
}

// Get total permissions count
const totalPermissions = computed(() => flatPermissions.value.length);
const selectedPermissionsCount = computed(() => form.permissions.length);

const { showSuccess, showError } = useNotifier(); // auto-shows flash messages

function submit() {
  form.post(route('user-role.store'), {
    onSuccess: () => {
      showSuccess('Role created successfully.');
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
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 p-6">
      <div class="max-w-6xl mx-auto">
        <!-- Header Section -->
        <div class="mb-8">
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-4">
              <div class="p-3 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl shadow-lg">
                <Shield class="w-8 h-8 text-white" />
              </div>
              <div>
                <h1 class="text-3xl font-bold text-slate-900 dark:text-white">Create New Role</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1">Define role permissions and access levels for your users</p>
              </div>
            </div>
            <Link
              href="/user-role"
              class="group relative overflow-hidden rounded-lg px-6 py-3 text-sm font-semibold text-slate-700 dark:text-slate-300 shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-slate-500 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700"
            >
              <span class="relative z-10 flex items-center gap-2">
                <ArrowLeft class="w-4 h-4 transition-transform duration-300 group-hover:-translate-x-1" />
                Back to Roles
              </span>
              <div class="absolute inset-0 bg-gradient-to-r from-transparent via-slate-100 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-20 group-hover:translate-x-full"></div>
            </Link>
          </div>

          <!-- Progress Indicator -->
          <div class="flex items-center gap-4 mb-6">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 bg-indigo-500 text-white rounded-full flex items-center justify-center text-sm font-semibold">1</div>
              <span class="text-sm font-medium text-slate-700 dark:text-slate-300">Role Information</span>
            </div>
            <div class="flex-1 h-0.5 bg-slate-200 dark:bg-slate-700"></div>
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400 rounded-full flex items-center justify-center text-sm font-semibold">2</div>
              <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Permissions</span>
            </div>
            <div class="flex-1 h-0.5 bg-slate-200 dark:bg-slate-700"></div>
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 bg-slate-200 dark:bg-slate-700 text-slate-500 dark:text-slate-400 rounded-full flex items-center justify-center text-sm font-semibold">3</div>
              <span class="text-sm font-medium text-slate-500 dark:text-slate-400">Review & Create</span>
            </div>
          </div>
        </div>

        <!-- Main Form -->
        <form @submit.prevent="submit" class="space-y-8">
          <!-- Role Information Card -->
          <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-indigo-500/5 via-indigo-600/10 to-indigo-500/5 border-b border-slate-200 dark:border-slate-700">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-500/20 rounded-lg">
                  <Users class="w-5 h-5 text-indigo-600" />
                </div>
                <div>
                  <h2 class="text-lg font-bold text-slate-900 dark:text-white">Role Information</h2>
                  <p class="text-slate-600 dark:text-slate-400 text-sm mt-1">Enter the basic details for this role</p>
                </div>
              </div>
            </div>
            <div class="p-6">
              <div class="space-y-4">
                <div>
                  <Label for="role-name" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2 mb-2">
                    <div class="w-1.5 h-1.5 bg-indigo-500 rounded-full"></div>
                    Role Name
                  </Label>
                  <Input
                    id="role-name"
                    v-model="form.name"
                    type="text"
                    placeholder="Enter role name (e.g., Manager, Admin, User)"
                    class="w-full"
                  />
                  <InputError :message="form.errors.name" class="mt-2" />
                </div>
              </div>
            </div>
          </div>

          <!-- Permissions Card -->
          <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-purple-500/5 via-purple-600/10 to-purple-500/5 border-b border-slate-200 dark:border-slate-700">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-purple-500/20 rounded-lg">
                    <Shield class="w-5 h-5 text-purple-600" />
                  </div>
                  <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Role Permissions</h2>
                    <p class="text-slate-600 dark:text-slate-400 text-sm mt-1">
                      Select permissions for this role ({{ selectedPermissionsCount }}/{{ totalPermissions }} selected)
                    </p>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <div class="text-sm text-slate-500 dark:text-slate-400">
                    {{ selectedPermissionsCount }} of {{ totalPermissions }} permissions
                  </div>
                  <div class="w-32 bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                    <div 
                      class="bg-gradient-to-r from-purple-500 to-indigo-500 h-2 rounded-full transition-all duration-300"
                      :style="{ width: `${(selectedPermissionsCount / totalPermissions) * 100}%` }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="p-6">
              <div class="space-y-4">
                <!-- Permission Modules -->
                <div v-for="(perms, module) in groupedPermissions" :key="module" class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden bg-gradient-to-r from-slate-50/50 to-slate-100/50 dark:from-slate-700/50 dark:to-slate-800/50 shadow-sm">
                  <!-- Module Header -->
                  <div
                    class="flex items-center justify-between p-4 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors duration-200"
                    @click="openAccordion[module] = !openAccordion[module]"
                  >
                    <div class="flex items-center gap-3">
                      <div class="p-1.5 bg-slate-100 dark:bg-slate-600 rounded-lg">
                        <ChevronDown 
                          v-if="openAccordion[module]" 
                          class="w-4 h-4 text-slate-600 dark:text-slate-300 transition-transform duration-200" 
                        />
                        <ChevronRight 
                          v-else 
                          class="w-4 h-4 text-slate-600 dark:text-slate-300 transition-transform duration-200" 
                        />
                      </div>
                      <div>
                        <h3 class="font-semibold text-slate-900 dark:text-white capitalize">{{ module.replace('-', ' ') }}</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-400">
                          {{ getModuleSelectionCount(module) }} of {{ perms.length }} permissions selected
                        </p>
                      </div>
                    </div>
                    <div class="flex items-center gap-3">
                      <!-- Module Selection Indicator -->
                      <div class="flex items-center gap-2">
                        <div class="w-16 bg-slate-200 dark:bg-slate-600 rounded-full h-1.5">
                          <div 
                            class="bg-gradient-to-r from-purple-500 to-indigo-500 h-1.5 rounded-full transition-all duration-300"
                            :style="{ width: `${(getModuleSelectionCount(module) / perms.length) * 100}%` }"
                          ></div>
                        </div>
                        <span class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                          {{ getModuleSelectionCount(module) }}/{{ perms.length }}
                        </span>
                      </div>
                      <!-- Select All Toggle -->
                      <label class="inline-flex items-center gap-2 cursor-pointer" @click.stop>
                        <div class="relative">
                          <input
                            type="checkbox"
                            :checked="isModuleAllSelected(module)"
                            :indeterminate="isModulePartiallySelected(module)"
                            @change.prevent="toggleModule(module)"
                            class="sr-only"
                          />
                          <div class="w-5 h-5 rounded border-2 transition-all duration-200 flex items-center justify-center"
                               :class="{
                                 'bg-purple-500 border-purple-500': isModuleAllSelected(module),
                                 'bg-purple-100 border-purple-300 dark:bg-purple-900/30 dark:border-purple-600': isModulePartiallySelected(module),
                                 'bg-white border-slate-300 dark:bg-slate-700 dark:border-slate-600': !isModuleAllSelected(module) && !isModulePartiallySelected(module)
                               }">
                            <Check v-if="isModuleAllSelected(module)" class="w-3 h-3 text-white" />
                            <div v-else-if="isModulePartiallySelected(module)" class="w-2 h-0.5 bg-purple-500 rounded"></div>
                          </div>
                        </div>
                        <span class="text-sm font-medium text-slate-600 dark:text-slate-300">Select All</span>
                      </label>
                    </div>
                  </div>

                  <!-- Module Permissions -->
                  <div v-show="openAccordion[module]" class="border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                    <div class="p-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                      <div v-for="perm in perms" :key="perm.key" class="group">
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 dark:border-slate-600 hover:border-purple-300 dark:hover:border-purple-500 hover:bg-purple-50 dark:hover:bg-purple-900/20 cursor-pointer transition-all duration-200"
                               :class="{ 'border-purple-300 bg-purple-50 dark:border-purple-500 dark:bg-purple-900/20': form.permissions.includes(perm.key) }">
                          <div class="relative">
                            <input
                              type="checkbox"
                              v-model="form.permissions"
                              :value="perm.key"
                              class="sr-only"
                            />
                            <div class="w-4 h-4 rounded border-2 transition-all duration-200 flex items-center justify-center"
                                 :class="{
                                   'bg-purple-500 border-purple-500': form.permissions.includes(perm.key),
                                   'bg-white border-slate-300 dark:bg-slate-700 dark:border-slate-600 group-hover:border-purple-300': !form.permissions.includes(perm.key)
                                 }">
                              <Check v-if="form.permissions.includes(perm.key)" class="w-2.5 h-2.5 text-white" />
                            </div>
                          </div>
                          <span class="text-sm font-medium text-slate-700 dark:text-slate-300 group-hover:text-purple-700 dark:group-hover:text-purple-300 transition-colors duration-200">
                            {{ formatAction(perm.action) }}
                          </span>
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Submit Section -->
          <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
              <div class="text-center sm:text-left">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Ready to Create Role?</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">
                  Review the role name and selected permissions, then click create to add the new role
                </p>
              </div>
              <div class="flex gap-3">
                <Link
                  href="/user-role"
                  class="px-6 py-3 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white font-semibold transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-sm"
                >
                  Cancel
                </Link>
                <Button
                  type="submit"
                  :disabled="form.processing || !form.name.trim()"
                  class="group relative overflow-hidden rounded-lg px-8 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
                  style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);"
                >
                  <span class="relative z-10 flex items-center gap-2">
                    <LoaderCircle v-if="form.processing" class="w-4 h-4 animate-spin" />
                    <Plus v-else class="w-4 h-4 transition-transform duration-300 group-hover:rotate-90" />
                    {{ form.processing ? 'Creating...' : 'Create Role' }}
                  </span>
                  <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-20 group-hover:translate-x-full"></div>
                </Button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
