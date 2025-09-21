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
    flock_name?: string
    shed_name?: string
    is_batch_assign?: boolean
  }>
  grades: Array<{ id: number; name: string; type: number; min_weight: number | null; max_weight: number | null }>
}>()

const notifier = useNotifier()

// State
const selectedClassification = ref<number | null>(null)
const selectedType = ref<string | null>(null) // "commercial" | "hatching"
const filteredGrades = ref<Array<{ id: number; name: string; min_weight: number | null; max_weight: number | null }>>([])

// Real egg data
const eggData = ref({
  total_eggs: 0,
  hatching_eggs: 0,
  commercial_eggs: 0,
})

// Form
const form = useForm({
  classification_id: null as number | null,
  type: '',
  grades: [] as { egg_grade_id: number; quantity: number }[],
})

// Computed: selected classification object
const selectedClass = computed(() =>
  props.classifications.find(c => c.id === selectedClassification.value)
)

// Watch batch selection to fetch real egg data
watch(selectedClassification, async (batchId) => {
  if (!batchId) {
    eggData.value = { total_eggs: 0, hatching_eggs: 0, commercial_eggs: 0 }
    return
  }

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
    eggData.value = { total_eggs: 0, hatching_eggs: 0, commercial_eggs: 0 }
  }
})

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

  form.post(route('egg-classification-grades.store'), {
    onSuccess: () => notifier.showSuccess('Grades saved successfully'),
    onError: () => notifier.showError('Failed to save grades'),
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Egg Grading" />

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
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                Egg Grading System
        </h1>
              <p class="text-gray-600 dark:text-gray-400 mt-1">
                Classify and grade eggs by weight and quality
              </p>
            </div>
          </div>
      </div>

        <!-- Main Content Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
      <!-- Form -->
          <form @submit.prevent="submit" class="p-4">
            <!-- Selection Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
              <!-- Batch Selection -->
              <div class="space-y-3">
                <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C8.5 2 6 4.5 6 8c0 2.5 1.5 4.5 3 6l3 3 3-3c1.5-1.5 3-3.5 3-6 0-3.5-2.5-6-6-6z"/>
                  </svg>
                  Select Batch / Transaction
                </Label>
          <select
            v-model="selectedClassification"
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-gray-900 dark:text-white text-sm"
          >
                  <option value=null disabled>Choose a batch to grade</option>
            <option
              v-for="c in props.classifications"
              :key="c.id"
              :value="c.id"
                    class="py-2"
            >
                    {{ c.transaction_no }} - {{ c.batch_name }} - {{ c.classification_date }}
                    <span v-if="c.is_batch_assign"> ({{ c.flock_name }} - {{ c.shed_name }})</span>
            </option>
          </select>
        </div>

              <!-- Egg Category Selection -->
              <div class="space-y-3">
                <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">
                  <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C8.5 2 6 4.5 6 8c0 2.5 1.5 4.5 3 6l3 3 3-3c1.5-1.5 3-3.5 3-6 0-3.5-2.5-6-6-6z"/>
                  </svg>
                  Egg Category
                </Label>
          <select
            v-model="selectedType"
                  class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 text-gray-900 dark:text-white text-sm"
          >
            <option value=null disabled>Select egg category</option>
                  <option value="commercial" class="py-2">Commercial Eggs</option>
                  <option value="hatching" class="py-2">Hatching Eggs</option>
          </select>
              </div>
        </div>

        <!-- Summary Cards -->
        <div
          v-if="selectedClassification && selectedType"
              class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6"
            >
              <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-4 text-white shadow-md transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-blue-100 text-xs font-medium">Total Eggs</p>
                    <p class="text-2xl font-bold mt-1">{{ eggData.total_eggs.toLocaleString() }}</p>
                  </div>
                  <div class="p-2 bg-white/20 rounded-lg">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2C8.5 2 6 4.5 6 8c0 2.5 1.5 4.5 3 6l3 3 3-3c1.5-1.5 3-3.5 3-6 0-3.5-2.5-6-6-6z"/>
                    </svg>
                  </div>
                </div>
          </div>

              <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-4 text-white shadow-md transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-green-100 text-xs font-medium">
                      {{ selectedType === 'commercial' ? 'Commercial Eggs' : 'Hatching Eggs' }}
                    </p>
                    <p class="text-2xl font-bold mt-1">{{ relevantEggs.toLocaleString() }}</p>
                  </div>
                  <div class="p-2 bg-white/20 rounded-lg">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2C8.5 2 6 4.5 6 8c0 2.5 1.5 4.5 3 6l3 3 3-3c1.5-1.5 3-3.5 3-6 0-3.5-2.5-6-6-6z"/>
                    </svg>
                  </div>
                </div>
              </div>

              <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg p-4 text-white shadow-md transform hover:scale-105 transition-transform duration-200">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-orange-100 text-xs font-medium">Ungraded Eggs</p>
                    <p class="text-2xl font-bold mt-1">{{ ungraded.toLocaleString() }}</p>
                  </div>
                  <div class="p-2 bg-white/20 rounded-lg">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 2C8.5 2 6 4.5 6 8c0 2.5 1.5 4.5 3 6l3 3 3-3c1.5-1.5 3-3.5 3-6 0-3.5-2.5-6-6-6z"/>
                    </svg>
                  </div>
                </div>
              </div>
        </div>

            <!-- Grades Input Section -->
            <div v-if="filteredGrades.length > 0" class="space-y-4">
              <div class="flex items-center gap-3 mb-4">
                <div class="p-2 bg-indigo-100 dark:bg-indigo-900 rounded-lg">
                  <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C8.5 2 6 4.5 6 8c0 2.5 1.5 4.5 3 6l3 3 3-3c1.5-1.5 3-3.5 3-6 0-3.5-2.5-6-6-6z"/>
                  </svg>
                </div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                  Enter Quantities for Each Grade
                </h2>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div
                  v-for="(grade, index) in filteredGrades"
                  :key="grade.id"
                  class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow duration-200"
                >
                  <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                      <div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center text-white font-bold text-sm">
                        {{ grade.name }}
                      </div>
                      <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Grade {{ grade.name }}</h3>
                        <p class="text-xs text-gray-600 dark:text-gray-400">
                          Weight: {{ grade.min_weight }}mg - {{ grade.max_weight }}mg
                        </p>
                      </div>
                    </div>
                  </div>
                  
                  <div class="space-y-1">
                    <Label class="text-xs font-medium text-gray-700 dark:text-gray-300">Quantity</Label>
                    <Input
                      type="number"
                      min="0"
                      :max="relevantEggs"
                      v-model="form.grades[index].quantity"
                      class="w-full px-3 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all duration-200 text-sm"
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
                class="px-6 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transform hover:scale-105 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none text-sm"
              >
                <svg v-if="form.processing" class="w-4 h-4 mr-2 animate-spin" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2C8.5 2 6 4.5 6 8c0 2.5 1.5 4.5 3 6l3 3 3-3c1.5-1.5 3-3.5 3-6 0-3.5-2.5-6-6-6z"/>
                </svg>
                <svg v-else class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 2C8.5 2 6 4.5 6 8c0 2.5 1.5 4.5 3 6l3 3 3-3c1.5-1.5 3-3.5 3-6 0-3.5-2.5-6-6-6z"/>
                </svg>
                {{ form.processing ? 'Saving...' : 'Save Grades' }}
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
