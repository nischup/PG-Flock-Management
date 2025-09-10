<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { useNotifier } from '@/composables/useNotifier'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

// Props from controller
const props = defineProps<{
  classifications: Array<{
    id: number
    classification_date: string
    total_eggs: number
    commercial_egg: number
    hatching_egg: number
    transaction_no: string
    batch_name: string
  }>
  grades: Array<{ id: number; name: string; type: number }>
}>()

const notifier = useNotifier()

// State
const selectedClassification = ref<number | null>(null)
const selectedType = ref<string | null>(null) // "commercial" | "hatching"
const filteredGrades = ref<Array<{ id: number; name: string }>>([])

// Form
const form = useForm({
  classification_id: null,
  type: '',
  grades: [] as { egg_grade_id: number; quantity: number }[],
})

// Computed: selected classification object
const selectedClass = computed(() =>
  props.classifications.find(c => c.id === selectedClassification.value)
)

// Watch category selection
watch(selectedType, (val) => {
  if (!val) return
  const typeNum = val === 'commercial' ? 1 : 2
  filteredGrades.value = props.grades.filter(g => g.type === typeNum)
  form.grades = filteredGrades.value.map(g => ({
    egg_grade_id: g.id,
    quantity: 0,
  }))
})

// Computed totals for cards
const relevantEggs = computed(() => {
  if (!selectedClass.value || !selectedType.value) return 0
  return selectedType.value === 'commercial'
    ? selectedClass.value.commercial_egg
    : selectedClass.value.hatching_egg
})

const gradedTotal = computed(() =>
  form.grades.reduce((sum, g) => sum + (g.quantity || 0), 0)
)
const ungraded = computed(() => relevantEggs.value - gradedTotal.value)

// Submit handler
function submit() {
  if (!selectedClassification.value || !selectedType.value) {
    notifier.error('Please select batch and egg category')
    return
  }
  form.classification_id = selectedClassification.value
  form.type = selectedType.value

  form.post(route('egg-classification-grades.store'), {
    onSuccess: () => notifier.success('Grades saved successfully'),
    onError: () => notifier.error('Failed to save grades'),
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Egg Grading" />

    <div class="p-6 m-5 bg-white dark:bg-gray-900 rounded-xl shadow-md">
      <!-- Header -->
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">
          Egg Grading
        </h1>
      </div>

      <!-- Form -->
      <form @submit.prevent="submit" class="space-y-6">
        <!-- Select Classification -->
        <div>
          <Label>Select Batch / Transaction</Label>
          <select
            v-model="selectedClassification"
            class="w-full mt-2 border rounded-lg px-3 py-2 focus:ring focus:outline-none"
          >
            <option value="" disabled>Select a batch</option>
            <option
              v-for="c in props.classifications"
              :key="c.id"
              :value="c.id"
            >
              {{ c.transaction_no }}-{{ c.batch_name }}-{{ c.classification_date }}
            </option>
          </select>
        </div>

        <!-- Select Egg Category -->
        <div>
          <Label>Egg Category</Label>
          <select
            v-model="selectedType"
            class="w-full mt-2 border rounded-lg px-3 py-2 focus:ring focus:outline-none"
          >
            <option value="" disabled>Select egg category</option>
            <option value="commercial">Commercial</option>
            <option value="hatching">Hatching</option>
          </select>
        </div>

        <!-- Summary Cards -->
        <div
          v-if="selectedClassification && selectedType"
          class="grid grid-cols-1 md:grid-cols-3 gap-6"
        >
          <div class="p-4 border rounded-lg bg-gray-50 dark:bg-gray-800 text-center">
            <p class="font-medium">Total Eggs</p>
            <p class="text-2xl font-bold">{{ selectedClass?.total_eggs }}</p>
          </div>
          <div class="p-4 border rounded-lg bg-gray-50 dark:bg-gray-800 text-center">
            <p class="font-medium">
              {{ selectedType === 'commercial' ? 'Commercial Eggs' : 'Hatching Eggs' }}
            </p>
            <p class="text-2xl font-bold">{{ relevantEggs }}</p>
          </div>
          <div class="p-4 border rounded-lg bg-gray-50 dark:bg-gray-800 text-center">
            <p class="font-medium">Ungraded Eggs</p>
            <p class="text-2xl font-bold">{{ ungraded }}</p>
          </div>
        </div>

        <!-- Grades Input -->
        <div v-if="filteredGrades.length > 0" class="space-y-4">
          <h2 class="font-medium text-lg">Enter quantities for grades:</h2>
          <div
            v-for="(grade, index) in filteredGrades"
            :key="grade.id"
            class="flex items-center gap-6"
          >
            <span class="w-40 font-medium">{{ grade.name }}</span>
            <Input
              type="number"
              min="0"
              v-model="form.grades[index].quantity"
              class="flex-1"
            />
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
          <Button type="submit">Save Grades</Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
