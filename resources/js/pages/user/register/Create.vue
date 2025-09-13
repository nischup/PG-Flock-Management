<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref, computed } from 'vue';
import { type BreadcrumbItem } from '@/types';
import InputError from '@/components/InputError.vue';
import { useNotifier } from "@/composables/useNotifier"
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { UserPlus, ArrowLeft, Building2, Users, Shield, ChevronDown, ChevronRight, Check, LoaderCircle } from 'lucide-vue-next';
interface Role {
  id: number;
  name: string;
}

interface Permission {
  id: number;
  name: string;
}

interface Company {
  id: number;
  name: string;
}

interface Shed {
  id: number;
  name: string;
}

const props = defineProps<{
  roles: Role[];
  permissions: Permission[];
  companies: Company[];
  sheds: Shed[];
}>();

const form = useForm<{
  name: string;
  email: string;
  role: string;
  password: string;
  permissions: string[];
  company_id: number;
  shed_id: number;
}>({
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
        showError('Validation failed. Please check the form.');
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
  props.permissions?.forEach((perm: Permission) => {
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
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 p-3 md:p-4">
      <div class="max-w-4xl mx-auto">
        <!-- Header Section -->
        <div class="mb-6">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-3">
              <div class="p-3 bg-gradient-to-br from-primary/20 to-primary/10 rounded-xl shadow-md">
                <UserPlus class="w-8 h-8 text-primary" />
              </div>
              <div>
                <h1 class="text-2xl lg:text-3xl font-bold text-slate-900 dark:text-white">Add New User</h1>
                <p class="text-slate-600 dark:text-slate-400 mt-1 text-sm">Create a new user account with roles and permissions</p>
              </div>
            </div>
        <Link
          href="/user-register"
              class="inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg border border-slate-200 dark:border-slate-700 transition-all duration-200 shadow-sm hover:shadow-md"
        >
              <ArrowLeft class="w-4 h-4" />
              Back to Users
        </Link>
      </div>
        </div>

        <!-- Progress Indicator -->
        <div class="mb-6">
          <div class="bg-white dark:bg-slate-800 rounded-xl p-4 shadow-md border border-slate-200 dark:border-slate-700">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-base font-semibold text-slate-900 dark:text-white">Setup Progress</h3>
              <span class="text-xs text-slate-500 dark:text-slate-400">Step 1 of 4</span>
            </div>
            <div class="flex items-center space-x-3">
              <div class="flex items-center space-x-2">
                <div class="w-6 h-6 bg-primary text-white rounded-full flex items-center justify-center text-xs font-semibold">1</div>
                <span class="text-xs font-medium text-slate-900 dark:text-white">Basic Info</span>
              </div>
              <div class="flex-1 h-1 bg-slate-200 dark:bg-slate-700 rounded-full">
                <div class="h-1 bg-primary rounded-full w-1/4"></div>
              </div>
              <div class="flex items-center space-x-2">
                <div class="w-6 h-6 bg-slate-200 dark:bg-slate-700 text-slate-500 rounded-full flex items-center justify-center text-xs font-semibold">2</div>
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Organization</span>
              </div>
              <div class="flex-1 h-1 bg-slate-200 dark:bg-slate-700 rounded-full"></div>
              <div class="flex items-center space-x-2">
                <div class="w-6 h-6 bg-slate-200 dark:bg-slate-700 text-slate-500 rounded-full flex items-center justify-center text-xs font-semibold">3</div>
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Role</span>
              </div>
              <div class="flex-1 h-1 bg-slate-200 dark:bg-slate-700 rounded-full"></div>
              <div class="flex items-center space-x-2">
                <div class="w-6 h-6 bg-slate-200 dark:bg-slate-700 text-slate-500 rounded-full flex items-center justify-center text-xs font-semibold">4</div>
                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Permissions</span>
              </div>
            </div>
          </div>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <!-- Basic Information and Organization Assignment Row -->
          <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <!-- Basic Information Card -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
              <div class="px-6 py-4 bg-gradient-to-r from-primary/5 via-primary/10 to-primary/5 border-b border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-primary/20 rounded-lg">
                    <UserPlus class="w-5 h-5 text-primary" />
                  </div>
                  <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Basic Information</h2>
                    <p class="text-slate-600 dark:text-slate-400 text-xs mt-1">Enter the user's personal details and credentials</p>
                  </div>
                </div>
              </div>
              <div class="p-6">
                <div class="space-y-4">
                  <!-- Name -->
                  <div class="space-y-2">
                    <Label for="name" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                      <div class="w-1.5 h-1.5 bg-primary rounded-full"></div>
                      Full Name
                    </Label>
                    <Input 
                      id="name" 
                      v-model="form.name" 
                      type="text" 
                      placeholder="Enter full name"
                      class="h-10 text-sm"
                    />
                    <InputError :message="form.errors.name" />
        </div>

        <!-- Email -->
                  <div class="space-y-2">
                    <Label for="email" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                      <div class="w-1.5 h-1.5 bg-primary rounded-full"></div>
                      Email Address
                    </Label>
                    <Input 
                      id="email" 
                      v-model="form.email" 
                      type="email" 
                      placeholder="Enter email address"
                      class="h-10 text-sm"
                    />
                    <InputError :message="form.errors.email" />
        </div>

        <!-- Password -->
                  <div class="space-y-2">
                    <Label for="password" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                      <div class="w-1.5 h-1.5 bg-primary rounded-full"></div>
                      Password
                    </Label>
                    <div class="relative">
                      <Input 
                        id="password" 
            v-model="form.password"
            type="password"
                        placeholder="Enter secure password"
                        class="h-10 text-sm pr-10"
                      />
                      <div class="absolute right-3 top-1/2 transform -translate-y-1/2">
                        <Shield class="w-4 h-4 text-slate-400" />
                      </div>
                    </div>
                    <InputError :message="form.errors.password" />
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                      Password must be at least 8 characters long
                    </div>
                  </div>
                </div>
              </div>
        </div>

            <!-- Organization Assignment Card -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
              <div class="px-6 py-4 bg-gradient-to-r from-blue-500/5 via-blue-600/10 to-blue-500/5 border-b border-slate-200 dark:border-slate-700">
                <div class="flex items-center gap-3">
                  <div class="p-2 bg-blue-500/20 rounded-lg">
                    <Building2 class="w-5 h-5 text-blue-600" />
                  </div>
                  <div>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Organization Assignment</h2>
                    <p class="text-slate-600 dark:text-slate-400 text-xs mt-1">Assign user to specific company and shed locations</p>
                  </div>
                </div>
              </div>
              <div class="p-6">
                <div class="space-y-4">
        <!-- Company -->
                  <div class="space-y-2">
                    <Label for="company" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                      <div class="w-1.5 h-1.5 bg-blue-500 rounded-full"></div>
                      Company
                    </Label>
                    <div class="relative">
                      <select 
                        id="company"
                        v-model="form.company_id" 
                        class="flex h-10 w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm shadow-sm transition-all duration-200 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-50"
                      >
                        <option value="0">-- Select Company --</option>
                        <option v-for="c in companies" :key="(c as Company).id" :value="(c as Company).id">{{ (c as Company).name }}</option>
          </select>
                      <Building2 class="absolute right-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                    </div>
        </div>

        <!-- Shed -->
                  <div class="space-y-2">
                    <Label for="shed" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                      <div class="w-1.5 h-1.5 bg-blue-500 rounded-full"></div>
                      Shed
                    </Label>
                    <div class="relative">
                      <select 
                        id="shed"
                        v-model="form.shed_id" 
                        class="flex h-10 w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm shadow-sm transition-all duration-200 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 disabled:cursor-not-allowed disabled:opacity-50"
                      >
                        <option value="0">-- Select Shed --</option>
                        <option v-for="s in sheds" :key="(s as Shed).id" :value="(s as Shed).id">{{ (s as Shed).name }}</option>
          </select>
                      <Building2 class="absolute right-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Role Assignment Card -->
          <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-emerald-500/5 via-emerald-600/10 to-emerald-500/5 border-b border-slate-200 dark:border-slate-700">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-emerald-500/20 rounded-lg">
                  <Users class="w-5 h-5 text-emerald-600" />
                </div>
                <div>
                  <h2 class="text-lg font-bold text-slate-900 dark:text-white">Role Assignment</h2>
                  <p class="text-slate-600 dark:text-slate-400 text-xs mt-1">Assign a role to define user access levels and permissions</p>
                </div>
              </div>
            </div>
            <div class="p-6">
              <div class="space-y-2">
                <Label for="role" class="text-sm font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-2">
                  <div class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></div>
                  User Role
                </Label>
                <div class="relative">
                  <select 
                    id="role"
                    v-model="form.role" 
                    class="flex h-10 w-full rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800 px-3 py-2 text-sm shadow-sm transition-all duration-200 outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 disabled:cursor-not-allowed disabled:opacity-50"
                  >
                    <option value="">-- Select a Role --</option>
                    <option v-for="role in roles" :key="(role as Role).id" :value="(role as Role).name">{{ (role as Role).name }}</option>
          </select>
                  <Users class="absolute right-3 top-1/2 transform -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                </div>
                <div class="text-xs text-slate-500 dark:text-slate-400">
                  Roles determine what actions the user can perform in the system
                </div>
              </div>
            </div>
          </div>

          <!-- Direct Permissions Card -->
          <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-purple-500/5 via-purple-600/10 to-purple-500/5 border-b border-slate-200 dark:border-slate-700">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-500/20 rounded-lg">
                  <Shield class="w-5 h-5 text-purple-600" />
                </div>
                <div>
                  <h2 class="text-lg font-bold text-slate-900 dark:text-white">Direct Permissions</h2>
                  <p class="text-slate-600 dark:text-slate-400 text-xs mt-1">Assign specific permissions to fine-tune user access</p>
                </div>
              </div>
            </div>
            <div class="p-6">
              <div class="space-y-4">
          <div
            v-for="(perms, group) in groupedPermissions"
            :key="group"
                  class="border border-slate-200 dark:border-slate-700 rounded-xl overflow-hidden bg-gradient-to-r from-slate-50/50 to-slate-100/50 dark:from-slate-700/50 dark:to-slate-800/50 shadow-sm"
          >
            <!-- Group Header -->
            <div
                    class="flex justify-between items-center px-4 py-3 bg-white dark:bg-slate-800 cursor-pointer hover:bg-slate-50 dark:hover:bg-slate-700 transition-all duration-200"
              @click="toggleGroup(group)"
            >
                    <div class="flex items-center gap-3">
                      <div class="p-1.5 bg-purple-500/10 rounded-lg">
                        <Shield class="w-4 h-4 text-purple-600" />
                      </div>
                      <div>
                        <span class="font-bold text-slate-900 dark:text-white capitalize text-sm">{{ group }}</span>
                        <div class="flex items-center gap-2 mt-1">
                          <span class="text-xs bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 px-2 py-0.5 rounded-full font-medium">
                            {{ perms.length }} permissions
                          </span>
                          <span class="text-xs text-slate-500 dark:text-slate-400">
                            {{ form.permissions.filter(p => perms.some(perm => perm.name === p)).length }} selected
                          </span>
                        </div>
                      </div>
                    </div>
                    <div class="flex items-center gap-3">
                      <label class="inline-flex items-center gap-1 cursor-pointer" @click.stop>
                <input
                  type="checkbox"
                  :checked="isGroupAllSelected(group)"
                          @change="toggleSelectAll(group)"
                          class="w-4 h-4 text-purple-600 bg-white border-slate-300 rounded focus:ring-purple-500 focus:ring-1"
                />
                        <span class="text-xs text-slate-600 dark:text-slate-400 font-semibold">All</span>
                </label>
                      <div class="p-1 hover:bg-slate-100 dark:hover:bg-slate-600 rounded-lg transition-colors">
                        <ChevronDown v-if="isGroupExpanded(group)" class="w-4 h-4 text-slate-500" />
                        <ChevronRight v-else class="w-4 h-4 text-slate-500" />
                      </div>
                    </div>
            </div>

            <!-- Group Permissions -->
                  <div v-show="isGroupExpanded(group)" class="px-4 py-3 bg-gradient-to-r from-slate-50/50 to-slate-100/50 dark:from-slate-700/50 dark:to-slate-800/50">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                      <div 
                        v-for="perm in perms" 
                        :key="perm.id" 
                        class="flex items-center gap-2 p-2 bg-white dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 hover:border-purple-300 dark:hover:border-purple-600 hover:shadow-sm transition-all duration-200 group"
                      >
                <input
                  type="checkbox"
                  :value="perm.name"
                  v-model="form.permissions"
                          class="w-3.5 h-3.5 text-purple-600 bg-white border-slate-300 rounded focus:ring-purple-500 focus:ring-1"
                        />
                        <span class="text-xs text-slate-700 dark:text-slate-300 font-medium flex-1">{{ perm.label }}</span>
                        <Check v-if="form.permissions.includes(perm.name)" class="w-3.5 h-3.5 text-purple-600 group-hover:scale-110 transition-transform" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Submit Button -->
          <div class="bg-white dark:bg-slate-800 rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 p-6">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
              <div class="text-center sm:text-left">
                <h3 class="text-base font-semibold text-slate-900 dark:text-white">Ready to Create User?</h3>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-1">Review the information above and click create to add the new user</p>
        </div>
              <div class="flex gap-3">
                <Link
                  href="/user-register"
                  class="px-4 py-2 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white font-semibold transition-colors rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-sm"
                >
                  Cancel
                </Link>
                <Button 
          type="submit"
                  :disabled="form.processing"
                  class="px-6 py-2 h-auto bg-gradient-to-r from-primary to-primary/90 hover:from-primary/90 hover:to-primary text-white font-semibold shadow-md hover:shadow-lg transition-all duration-200 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed text-sm"
                >
                  <LoaderCircle v-if="form.processing" class="w-4 h-4 animate-spin mr-2" />
                  <UserPlus v-else class="w-4 h-4 mr-2" />
                  {{ form.processing ? 'Creating...' : 'Create User' }}
                </Button>
              </div>
            </div>
          </div>
      </form>
      </div>
    </div>
  </AppLayout>
</template>
