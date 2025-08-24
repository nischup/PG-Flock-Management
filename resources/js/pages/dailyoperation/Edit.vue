<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'

// Breadcrumb
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Flock Management', href: '/flocks' },
  { title: 'Mortality Entry', href: '' },
]

// Props from controller
const props = defineProps<{
  flocks: Array<{ id: number; flock_code: string }>
}>()

// Form
const form = useForm({
  flock_id: '',
  female_mortality: 0,
  male_mortality: 0,
  reason: '',
  note: '',
})

// Flock info
const flockInfo = ref({
  opening: 0,
  current: 0,
  age: '',
})

// Dummy flock data
const flockMap: Record<number, { opening: number; current: number; start_date: string }> = {
  1: { opening: 200, current: 180, start_date: '2025-07-12' },
  2: { opening: 220, current: 200, start_date: '2025-06-12' },
  3: { opening: 250, current: 230, start_date: '2025-07-30' },
}

// Calculate age in weeks and days
function calculateAge(start: string) {
  const startDt = new Date(start)
  const today = new Date()
  const diffDays = Math.floor((today.getTime() - startDt.getTime()) / (1000 * 60 * 60 * 24))
  const weeks = Math.floor(diffDays / 7)
  const days = diffDays % 7
  return `${weeks} weeks ${days} days`
}

// Watch flock selection
watch(() => form.flock_id, (newFlock) => {
  if (!newFlock) {
    flockInfo.value.opening = 0
    flockInfo.value.current = 0
    flockInfo.value.age = '0 weeks 0 days'
    return
  }

  const data = flockMap[newFlock]
  if (data) {
    flockInfo.value.opening = data.opening
    flockInfo.value.current = data.current
    flockInfo.value.age = calculateAge(data.start_date)
  }
})

// Submit
function submit() {
  form.post(route('mortality.store'), {
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Mortality Entry" />

    <form @submit.prevent="submit" class="p-6 space-y-6 border rounded-lg shadow-sm">

      <!-- Flock Selection -->
      <div>
        <Label>Select Flock</Label>
        <select v-model="form.flock_id" class="w-full mt-1 border rounded px-3 py-2">
          <option value="">-- Select Flock --</option>
          <option v-for="f in props.flocks" :key="f.id" :value="f.id">{{ f.flock_code }}</option>
        </select>
      </div>

      <!-- Flock Info -->
      <div class="bg-gray-50 p-4 rounded shadow-sm mt-4">
        <h3 class="font-semibold mb-2">Flock Info</h3>
        <div class="grid grid-cols-3 gap-4 text-center">
          <div>
            <p class="font-medium">Opening Chicks</p>
            <p>{{ flockInfo.opening }}</p>
          </div>
          <div>
            <p class="font-medium">Current Chicks</p>
            <p>{{ flockInfo.current }}</p>
          </div>
          <div>
            <p class="font-medium">Current Age</p>
            <p>{{ flockInfo.age }}</p>
          </div>
        </div>
      </div>

      <!-- Mortality Inputs -->
    
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div class="flex flex-col">
                <Label>Female Mortality</Label>
                <Input v-model.number="form.female_mortality" type="number" min="0" class="mt-2"/>
            </div>
            <div class="flex flex-col">
                <Label>Female Mortality Reason</Label>
                <Input v-model="form.female_reason" type="text" class="mt-2"/>
            </div>
            <div class="flex flex-col">
                <Label>Male Mortality</Label>
                <Input v-model.number="form.male_mortality" type="number" min="0" class="mt-2"/>
            </div>
            <div class="flex flex-col">
                <Label>Male Mortality Reason</Label>
                <Input v-model="form.male_reason" type="text" class="mt-2"/>
            </div>
            <div class="flex flex-col md:col-span-2">
                <Label>Note</Label>
                <textarea v-model="form.note" class="border rounded px-3 py-2 mt-2"></textarea>
            </div>
        </div>

      <!-- Submit -->
      <div class="mt-4">
        <Button type="submit" class="bg-chicken w-full">Submit</Button>
      </div>
    </form>
  </AppLayout>
</template>
