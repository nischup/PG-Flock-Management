<script setup lang="ts">
import { ref, watch, computed, onMounted } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { useNotifier } from '@/composables/useNotifier'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

// Props from controller
const props = defineProps<{
  classification: {
    id: number
    classification_date: string
    total_eggs: number
    commercial_eggs: number
    hatching_eggs: number
    transaction_no: string
    batch_name: string
    type: 'commercial' | 'hatching'
    grades: { egg_grade_id: number; quantity: number }[]
    flock_name?: string
    shed_name?: string
  }
  classifications: Array<{
    id: number
    classification_date: string
    total_eggs: number
    commercial_egg: number
    hatching_egg: number
    transaction_no: string
    batch_name: string
    flock_name?: string
    shed_name?: string
  }>
  grades: Array<{ id: number; name: string; type: number; min_weight: number | null; max_weight: number | null }>
}>()

const notifier = useNotifier()

// State
const selectedClassification = ref<number | null>(props.classification.id)
const selectedType = ref<string | null>(props.classification.type)
const filteredGrades = ref<Array<{ id: number; name: string; min_weight: number | null; max_weight: number | null }>>([])

// Real egg data
const eggData = ref({
  total_eggs: props.classification.total_eggs,
  commercial_eggs: props.classification.commercial_eggs,
  hatching_eggs: props.classification.hatching_eggs,
})

// Form
const form = useForm({
  classification_id: props.classification?.id ?? null,
  type: props.classification?.type ?? '',
  grades: props.classification?.grades?.map(g => ({
    egg_grade_id: g.egg_grade_id,
    quantity: g.quantity,
  })) ?? [],
})

// Computed: selected classification object
const selectedClass = computed(() =>
  props.classifications.find(c => c.id === selectedClassification.value)
)

// Watch batch selection to fetch real egg data
watch(selectedClassification, async (batchId) => {
  if (!batchId) return
  try {
    const response = await fetch(`/egg-classification-grades/batch/${batchId}/egg-data`)
    const data = await response.json()
    if (data.egg_data) {
      eggData.value = {
        total_eggs: data.egg_data.total_eggs || 0,
        hatching_eggs: data.egg_data.hatching_eggs || 0,
        commercial_eggs: data.egg_data.commercial_eggs || 0,
      }
    }
  } catch (error) {
    console.error('Error fetching egg data:', error)
  }
})

// Watch category selection to filter grades
watch(selectedType, (val) => {
  if (!val) return
  const typeNum = val === 'commercial' ? 1 : 2
  filteredGrades.value = props.grades.filter(g => g.type === typeNum)

  // Prefill quantities from form.grades if exists
  form.grades = filteredGrades.value.map(g => {
    const existing = form.grades.find(f => f.egg_grade_id === g.id)
    return {
      egg_grade_id: g.id,
      quantity: existing?.quantity || 0
    }
  })
})

// Computed totals for cards
const relevantEggs = computed(() => {
  if (!selectedType.value) return 0
  return selectedType.value === 'commercial'
    ? eggData.value.commercial_eggs
    : eggData.value.hatching_eggs
})

const gradedTotal = computed(() =>
  form.grades.reduce((sum, g) => sum + (g.quantity || 0), 0)
)
const ungraded = computed(() => relevantEggs.value - gradedTotal.value)

// Submit handler
function submit() {
  if (!selectedClassification.value || !selectedType.value) {
    notifier.showError('Please select batch and egg category')
    return
  }

  form.classification_id = selectedClassification.value
  form.type = selectedType.value

  form.put(route('egg-classification-grades.update', props.classification.id), {
    onSuccess: () => notifier.showSuccess('Grades updated successfully'),
    onError: () => notifier.showError('Failed to update grades'),
  })
}

// Prefill filtered grades on mount
onMounted(() => {
  if (selectedType.value) {
    const typeNum = selectedType.value === 'commercial' ? 1 : 2
    filteredGrades.value = props.grades.filter(g => g.type === typeNum)
  }
})
</script>

<template>
  <AppLayout>
    <Head title="Edit Egg Grading" />

    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900">
      <div class="container mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="mb-8">
          <div class="flex items-center gap-4 mb-2">
            <div class="p-3 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl shadow-lg">
              <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C8.5 2 6 4.5 6 8c0 2.5 1.5 4.5 3 6l3 3 3-3c1.5-1.5 3-3.5 3-6 0-3.5-2.5-6-6-6z"/>
              </svg>
            </div>
            <div>
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Egg Grading</h1>
              <p class="text-gray-600 dark:text-gray-400 mt-1">Update egg grades for the selected batch</p>
            </div>
          </div>
        </div>

        <!-- Main Content Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
          <form @submit.prevent="submit" class="p-4">
            <!-- Selection Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
              <!-- Batch Selection -->
              <div class="space-y-3">
                <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                  Select Batch / Transaction
                </Label>
                <select
                  v-model="selectedClassification"
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white text-sm"
                >
                  <option value=null disabled>Choose a batch</option>
                  <option
                    v-for="c in props.classifications"
                    :key="c.id"
                    :value="c.id"
                  >
                    {{ c.transaction_no }} - {{ c.batch_name }} - {{ c.classification_date }}
                    <span v-if="c.flock_name">({{ c.flock_name }} - {{ c.shed_name }})</span>
                  </option>
                </select>
              </div>

              <!-- Egg Category Selection -->
              <div class="space-y-3">
                <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                  Egg Category
                </Label>
                <select
                  v-model="selectedType"
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white text-sm"
                >
                  <option value=null disabled>Select egg category</option>
                  <option value="commercial">Commercial Eggs</option>
                  <option value="hatching">Hatching Eggs</option>
                </select>
              </div>
            </div>

            <!-- Summary Cards -->
            <div v-if="selectedClassification && selectedType" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
              <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-4 text-white shadow-md">
                <p class="text-xs font-medium">Total Eggs</p>
                <p class="text-2xl font-bold mt-1">{{ eggData.total_eggs.toLocaleString() }}</p>
              </div>
              <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-4 text-white shadow-md">
                <p class="text-xs font-medium">{{ selectedType === 'commercial' ? 'Commercial Eggs' : 'Hatching Eggs' }}</p>
                <p class="text-2xl font-bold mt-1">{{ relevantEggs.toLocaleString() }}</p>
              </div>
              <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg p-4 text-white shadow-md">
                <p class="text-xs font-medium">Ungraded Eggs</p>
                <p class="text-2xl font-bold mt-1">{{ ungraded.toLocaleString() }}</p>
              </div>
            </div>

            <!-- Grades Input Section -->
            <div v-if="filteredGrades.length > 0" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="(grade, index) in filteredGrades" :key="grade.id" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                  <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Grade {{ grade.name }}</h3>
                  <p class="text-xs text-gray-600 dark:text-gray-400">Weight: {{ grade.min_weight }}mg - {{ grade.max_weight }}mg</p>
                  <div class="mt-2">
                    <Label class="text-xs font-medium text-gray-700 dark:text-gray-300">Quantity</Label>
                    <Input
                      type="number"
                      min="0"
                      :max="relevantEggs"
                      v-model="form.grades[index].quantity"
                      class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-sm"
                      placeholder="Enter quantity"
                    />
                    <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400">
                      <span>Min: 0</span>
                      <span>Max: {{ relevantEggs.toLocaleString() }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700 mt-6">
              <Button
                type="submit"
                :disabled="form.processing || !selectedClassification || !selectedType"
                class="px-6 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-lg"
              >
                {{ form.processing ? 'Saving...' : 'Update Grades' }}
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
