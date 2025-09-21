<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { useForm } from '@inertiajs/vue3';
// @ts-ignore
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';
import { ref, watch, computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import type { BreadcrumbItemType } from '@/types';

const breadcrumbs: BreadcrumbItemType[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Production System', href: '/ps' },
  { title: 'Order Planning', href: route('order-plans.index') },
  { title: 'Create', href: '#' }
];

const form = useForm({
  order_from: '',
  order_to: '',
  cc: '',
  subject: '',
  message: '',
  attachment: null as File | null,
  items: [
    { order_volume: '', shipping_date: '', supply_base: '' }
  ]
});

const quillContent = ref(form.message || '');
const isSubmitting = ref(false);
const showSuccess = ref(false);

watch(quillContent, (val) => {
  form.message = val;
});

const isValidEmail = (email: string) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return emailRegex.test(email);
};

const isFormValid = computed(() => {
  return form.order_from && 
         form.order_to && 
         form.subject && 
         isValidEmail(form.order_from) && 
         isValidEmail(form.order_to) &&
         form.items.some(item => item.order_volume && item.shipping_date && item.supply_base);
});

function addItem() {
  form.items.push({ order_volume: '', shipping_date: '', supply_base: '' });
}

function removeItem(i: number) {
  if (form.items.length > 1) {
    form.items.splice(i, 1);
  }
}

function handleSubmit() {
  if (isFormValid.value) {
    isSubmitting.value = true;
    form.post(route('order-plans.store'), {
      onSuccess: () => {
        showSuccess.value = true;
        setTimeout(() => {
          showSuccess.value = false;
        }, 3000);
      },
      onFinish: () => {
        isSubmitting.value = false;
      }
    });
  }
}
</script>

<template>
  <AppLayout title="Create Order Planning" :breadcrumbs="breadcrumbs">
    <!-- Success Notification -->
    <Transition
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-100"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="showSuccess" class="fixed top-4 right-4 z-50">
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 shadow-lg">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium text-green-800">Order plan created successfully!</p>
            </div>
          </div>
        </div>
      </div>
    </Transition>

    <div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50">
      <!-- Header Section -->
      <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-6">
        <div class="max-w-4xl mx-auto">
          <div class="flex items-center justify-between">
            <div class="text-center flex-1">
              <h1 class="text-3xl font-bold text-white mb-2">Create Order Planning</h1>
              <p class="text-lg text-blue-100">Plan and manage your orders efficiently</p>
            </div>
            <div class="flex-shrink-0">
              <Link 
                :href="route('order-plans.index')" 
                class="inline-flex items-center px-4 py-2 bg-white/10 hover:bg-white/20 text-white font-medium rounded-lg transition-all duration-200 backdrop-blur-sm border border-white/20 hover:border-white/30 focus:outline-none focus:ring-2 focus:ring-white/50"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to List
              </Link>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Form Card -->
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-4 relative z-10">
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
          <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
            <!-- Email Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Order From -->
              <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700">
                  Order From (Email) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                    </svg>
                  </div>
                  <input 
                    v-model="form.order_from" 
                    type="email"
                    :class="[
                      'w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200',
                      form.order_from && !isValidEmail(form.order_from) ? 'border-red-300 bg-red-50' : 'border-gray-300 hover:border-gray-400'
                    ]"
                    placeholder="sender@company.com"
                  />
                  <div v-if="form.order_from && !isValidEmail(form.order_from)" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                  </div>
                </div>
                <p v-if="form.order_from && !isValidEmail(form.order_from)" class="text-sm text-red-600">Please enter a valid email address</p>
              </div>

              <!-- Order To -->
              <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700">
                  Order To (Email) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <input 
                    v-model="form.order_to" 
                    type="email"
                    :class="[
                      'w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200',
                      form.order_to && !isValidEmail(form.order_to) ? 'border-red-300 bg-red-50' : 'border-gray-300 hover:border-gray-400'
                    ]"
                    placeholder="recipient@company.com"
                  />
                  <div v-if="form.order_to && !isValidEmail(form.order_to)" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                  </div>
                </div>
                <p v-if="form.order_to && !isValidEmail(form.order_to)" class="text-sm text-red-600">Please enter a valid email address</p>
              </div>
            </div>

            <!-- CC and Subject -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- CC -->
              <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700">CC (comma separated)</label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                  </div>
                  <input 
                    v-model="form.cc" 
                    type="text"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
                    placeholder="cc1@company.com, cc2@company.com"
                  />
                </div>
              </div>

              <!-- Subject -->
              <div class="space-y-2">
                <label class="block text-sm font-semibold text-gray-700">
                  Subject <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                  </div>
                  <input 
                    v-model="form.subject" 
                    type="text"
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
                    placeholder="Order planning for Q1 2024"
                  />
                </div>
              </div>
            </div>

            <!-- Message Body -->
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700">Message Body</label>
              <div class="border border-gray-300 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-blue-500 focus-within:border-transparent transition-all duration-200">
                <QuillEditor
                  v-model="quillContent"
                  class="bg-white"
                  style="height: 200px;"
                  :formats="['bold','italic','underline','strike','link','image','list','bullet']"
                />
              </div>
            </div>

            <!-- Items Section -->
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Order Items</h3>
                <button 
                  type="button" 
                  @click="addItem"
                  class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                >
                  <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                  </svg>
                  Add Item
                </button>
              </div>

              <div class="space-y-3">
                <div v-for="(item, i) in form.items" :key="i" class="bg-gray-50 rounded-lg p-3 border border-gray-200 hover:border-gray-300 transition-colors duration-200">
                  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    <div class="space-y-1">
                      <label class="block text-xs font-medium text-gray-600">Order Volume</label>
                      <input 
                        v-model="item.order_volume" 
                        placeholder="e.g., 1000 units"
                        class="w-full px-3 py-1.5 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                      />
                    </div>
                    <div class="space-y-1">
                      <label class="block text-xs font-medium text-gray-600">Shipping Date</label>
                      <input 
                        v-model="item.shipping_date" 
                        type="date"
                        class="w-full px-3 py-1.5 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                      />
                    </div>
                    <div class="space-y-1">
                      <label class="block text-xs font-medium text-gray-600">Supply Base</label>
                      <input 
                        v-model="item.supply_base" 
                        placeholder="e.g., Warehouse A"
                        class="w-full px-3 py-1.5 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                      />
                    </div>
                    <div class="flex justify-end">
                      <button 
                        type="button" 
                        @click="removeItem(i)"
                        :disabled="form.items.length === 1"
                        :class="[
                          'p-2 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2',
                          form.items.length === 1 
                            ? 'text-gray-400 cursor-not-allowed' 
                            : 'text-red-600 hover:bg-red-50 hover:text-red-700'
                        ]"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Attachment Section -->
            <div class="space-y-2">
              <label class="block text-sm font-semibold text-gray-700">Attachment (optional)</label>
              <div class="relative">
                <input 
                  type="file" 
                  @change="e => form.attachment = (e.target as HTMLInputElement)?.files?.[0] || null"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 hover:border-gray-400 file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                />
              </div>
            </div>

            <!-- Form Actions -->
            <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-gray-200 bg-gray-50 -mx-6 px-6 py-4 rounded-b-2xl">
              <Link 
                :href="route('order-plans.index')" 
                class="flex-1 sm:flex-none inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200"
              >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Cancel
              </Link>
              <button 
                type="submit"
                :disabled="!isFormValid || isSubmitting"
                :class="[
                  'flex-1 sm:flex-none inline-flex items-center justify-center px-8 py-3 font-medium rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2',
                  isFormValid && !isSubmitting
                    ? 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5'
                    : 'bg-gray-300 text-gray-500 cursor-not-allowed'
                ]"
              >
                <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-3 h-5 w-5" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                {{ isSubmitting ? 'Creating...' : 'Create Order Plan' }}
              </button>
            </div>
          </form>
        </div>
      </div>
      
      <!-- Bottom spacing -->
      <div class="h-8"></div>
    </div>
  </AppLayout>
</template>
