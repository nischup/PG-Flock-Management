<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AppLayout from '@/layouts/AppLayout.vue'
import Datepicker from '@vuepic/vue-datepicker'
import '@vuepic/vue-datepicker/dist/main.css'

import { type BreadcrumbItem } from '@/types'

// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Flock Management', href: '/flocks' },
  { title: 'Daily Operation', href: '' },
]

const props = defineProps<{
  flocks: Array<any>
  feeds?: Array<any>
}>()

// Tabs
const tabs = [
  { key: 'daily_mortality', label: 'Mortality' },
  { key: 'feed_consumption', label: 'Feed' },
  { key: 'water_consumption', label: 'Water' },
  { key: 'light_consumption', label: 'Light' },
  { key: 'destroy', label: 'Destroy' },
  { key: 'culling', label: 'Culling' },
  { key: 'sexing_error', label: 'Sexing Error' },
  { key: 'egg_collection', label: 'Egg Collection' },
  { key: 'weight', label: 'Weight' },
  { key: 'inside_temperature', label: 'Temperature' },
  { key: 'humidity', label: 'Humidity' },
]

// Active tab
const activeTabIndex = ref(0)
const activeTab = computed(() => tabs[activeTabIndex.value].key)

// Form data
const form = useForm({
  flock_id: '',
  operation_date: new Date().toISOString().substr(0, 10),
  female_mortality: 0,
  male_mortality: 0,
  female_reason: '',
  male_reason: '',
  mortalitynote: '',
  feed_type_id: '',
  feed_quantity: 0,
  feed_unit: '',
  feed_note: '',
  water_consumption: 0,
  light_consumption: 0,
  destroy: 0,
  culling: 0,
  sexing_error: 0,
  egg_collection: 0,
  weight: 0,
  inside_temperature: 0,
  humidity: 0,
})

// Navigation
function nextTab() {
  if (activeTabIndex.value < tabs.length - 1) activeTabIndex.value++
}

function prevTab() {
  if (activeTabIndex.value > 0) activeTabIndex.value--
}

// Submit
function submit() {
  form.post(route('daily-operations.store'), {
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Daily Operation" />

    <form @submit.prevent="submit" class="p-6 space-y-6">

      <!-- Flock Info -->
      <div class="border rounded-lg p-4 shadow-sm">
        <h2 class="font-semibold text-lg mb-4">Flock Information</h2>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <Label>Select Shed</Label>
            <select v-model="form.flock_id" class="w-full mt-1 border rounded px-3 py-2">
              <option value="">Select Shed</option>
              <option v-for="flock in props.flocks" :key="flock.id" :value="flock.id">{{ flock.flock_code }}</option>
            </select>
          </div>
          <div>
            <Label>Select Batch</Label>
            <select v-model="form.flock_id" class="w-full mt-1 border rounded px-3 py-2">
              <option value="">Select Batch</option>
              <option v-for="flock in props.flocks" :key="flock.id" :value="flock.id">{{ flock.flock_code }}</option>
            </select>
          </div>
          <div>
            <Label>Date</Label>
            <Datepicker
              v-model="form.operation_date"
              format="yyyy-MM-dd"
              :input-class="'mt-2 border rounded px-3 py-2 w-full'"
              placeholder="Select Date"
              :auto-apply="true"
            />
          </div>
        </div>
      </div>

      <!-- Card Menu Tabs -->
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
        <div 
          v-for="(tab, index) in tabs" 
          :key="tab.key"
          @click="activeTabIndex = index"
          class="cursor-pointer p-10 border rounded-lg shadow text-center font-semibold transition-transform hover:scale-105"
          :class="activeTabIndex === index ? 'bg-chicken text-black' : 'bg-white-100 text-gray-700'"
        >
          {{ tab.label }}
        </div>
      </div>

      <!-- Tab Content -->
      <div class="border rounded-lg p-4 shadow-sm mt-4">
        <div v-if="activeTab === 'daily_mortality'">
          <div class="grid grid-cols-2 gap-4">
            <div class="flex flex-col">
              <Label>Female Chick Mortality</Label>
              <Input v-model.number="form.female_mortality" type="number"/>
            </div>
            <div class="flex flex-col">
              <Label>Male Chick Mortality</Label>
              <Input v-model.number="form.male_mortality" type="number"/>
            </div>
            <div class="flex flex-col">
              <Label>Female Reason</Label>
              <Input v-model="form.female_reason" type="text"/>
            </div>
            <div class="flex flex-col">
              <Label>Male Reason</Label>
              <Input v-model="form.male_reason" type="text"/>
            </div>
            <div class="flex flex-col col-span-2">
              <Label>Note</Label>
              <textarea v-model="form.mortalitynote" class="border rounded px-3 py-2"></textarea>
            </div>
          </div>
        </div>

        <div v-if="activeTab === 'feed_consumption'">
          <div class="grid grid-cols-3 gap-4">
            <div>
              <Label>Feed Type</Label>
              <select v-model="form.feed_type_id" class="w-full mt-1 border rounded px-3 py-2">
                <option value="">Select Feed</option>
                <option v-for="feed in props.feeds" :key="feed.id" :value="feed.id">{{ feed.name }}</option>
              </select>
            </div>
            <div>
              <Label>Quantity</Label>
              <Input v-model.number="form.feed_quantity" type="number"/>
            </div>
            <div>
              <Label>Unit</Label>
              <Input v-model="form.feed_unit" type="text"/>
            </div>
            <div class="col-span-3 mt-2">
              <Label>Note</Label>
              <textarea v-model="form.feed_note" class="w-full border rounded px-3 py-2"></textarea>
            </div>
          </div>
        </div>

        <!-- Repeat your other tab contents here like water, light, destroy, etc. -->

      </div>

      <!-- Navigation Buttons -->
      <div class="flex justify-between mt-4">
        <Button type="button" @click="prevTab" :disabled="activeTabIndex === 0">Previous</Button>
        <Button v-if="activeTabIndex < tabs.length - 1" type="button" @click="nextTab">Next</Button>
        <Button v-else type="submit" class="bg-chicken">Save</Button>
      </div>
    </form>
  </AppLayout>
</template>
