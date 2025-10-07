<script setup lang="ts">
import { ref, watch, computed, onMounted, onBeforeUnmount } from 'vue'
import { useForm, Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { useNotifier } from '@/composables/useNotifier'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { ChevronDown, Search, CheckCircle2, AlertCircle } from 'lucide-vue-next'

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

// Dropdown states
const showFlockDropdown = ref(false)
const flockSearchQuery = ref('')

// Real egg data fetched from backend
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

// Filtered classifications for dropdown
const filteredClassifications = computed(() => {
  if (!flockSearchQuery.value) return props.classifications
  return props.classifications.filter(c =>
    c.transaction_no?.toLowerCase().includes(flockSearchQuery.value.toLowerCase()) ||
    c.batch_name?.toLowerCase().includes(flockSearchQuery.value.toLowerCase())
  )
})

// Selected classification display
const selectedClassificationDisplay = computed(() =>
  props.classifications.find(c => c.id === selectedClassification.value)
)

// Close dropdown on outside click
const handleClickOutside = (e: MouseEvent) => {
  if (!(e.target as HTMLElement).closest('.flock-dropdown')) {
    showFlockDropdown.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

// Watch selected batch to fetch egg data
watch(selectedClassification, async (classificationId) => {
  if (!classificationId) {
    eggData.value = { total_eggs: 0, hatching_eggs: 0, commercial_eggs: 0 }
    return
  }

  try {
    const response = await fetch(`/egg-classification-grades/batch/${classificationId}/egg-data`)
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

// Watch egg category selection to filter grades
watch(selectedType, (val) => {
  if (!val) return
  const typeNum = val === 'commercial' ? 1 : 2
  filteredGrades.value = props.grades.filter(g => g.type === typeNum)
  form.grades = filteredGrades.value.map(g => ({ egg_grade_id: g.id, quantity: 0 }))
})

// Computed totals for cards
const relevantEggs = computed(() => {
  if (!selectedType.value) return 0
  return selectedType.value === 'commercial' ? eggData.value.commercial_eggs : eggData.value.hatching_eggs
})

const gradedTotal = computed(() => form.grades.reduce((sum, g) => sum + (g.quantity || 0), 0))
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
        <div class="mb-8 flex items-center justify-between">
          <div class="flex items-center gap-4">
            <div class="p-3 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-xl shadow-lg">
              <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C8.5 2 6 4.5 6 8c0 2.5 1.5 4.5 3 6l3 3 3-3c1.5-1.5 3-3.5 3-6 0-3.5-2.5-6-6-6z"/>
              </svg>
            </div>
            <div>
              <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Egg Grading System</h1>
              <p class="text-gray-600 dark:text-gray-400 mt-1">Classify and grade eggs by weight and quality</p>
            </div>
          </div>
          <Link
            href="/egg-classification-grades"
            class="group relative overflow-hidden rounded-lg px-6 py-3 text-sm font-semibold text-white shadow-lg transition-all duration-300 hover:scale-105 hover:shadow-xl"
            style="background: linear-gradient(135deg, #1f2937 0%, #111827 100%);"
          >
            <span class="relative z-10 flex items-center gap-2">
              <svg class="h-4 w-4 transition-transform duration-300 group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
              </svg>
              Back to List
            </span>
          </Link>
        </div>

        <!-- Main Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
          <form @submit.prevent="submit" class="p-4">
            <!-- Selection Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
              <!-- Batch Selection -->
              <div class="space-y-3">
                  <Label class="text-xs font-semibold text-gray-700 flex items-center">
                    <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mr-1.5"></div>
                    Select Batch
                  </Label>
                  <div class="flock-dropdown relative">
                    <button
                      type="button"
                      @click.stop="showFlockDropdown = !showFlockDropdown"
                      class="flex w-full items-center justify-between rounded-lg border border-gray-300 bg-white px-3 py-2 shadow-sm text-xs h-10"
                    >
                      <span class="flex items-center gap-2">
                        <div class="h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                        {{ selectedClassificationDisplay 
                            ? `${selectedClassificationDisplay.company} | ${selectedClassificationDisplay.project} | ${selectedClassificationDisplay.flock_name} | ${selectedClassificationDisplay.shed_name} | ${selectedClassificationDisplay.batch_name} | ${selectedClassificationDisplay.transaction_no} | ${selectedClassificationDisplay.classification_date}` 
                            : 'Select Batch' }}
                      </span>
                      <ChevronDown class="h-3 w-3 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': showFlockDropdown }" />
                    </button>

                    <!-- Dropdown -->
                    <div
                      v-if="showFlockDropdown"
                      class="fixed inset-0 z-[9999] flex items-start justify-center pt-20"
                      @click="showFlockDropdown = false"
                    >
                      <div class="w-full max-w-md rounded-lg border border-gray-200 bg-white shadow-2xl" @click.stop>
                        <div class="border-b border-gray-200 p-3">
                          <h3 class="font-semibold text-gray-900 text-sm">Select Batch</h3>
                          <div class="relative mt-2">
                            <Search class="absolute left-2 top-1/2 h-3 w-3 -translate-y-1/2 text-gray-400" />
                            <input
                              v-model="flockSearchQuery"
                              type="text"
                              placeholder="Search Batch..."
                              class="w-full rounded border border-gray-300 bg-gray-50 pl-7 pr-3 py-1.5 text-xs"
                            />
                          </div>
                        </div>
                        <div class="max-h-80 overflow-y-auto">
                          <button
                            v-for="classification in filteredClassifications"
                            :key="classification.id"
                            type="button"
                            @click.stop="selectedClassification = classification.id; showFlockDropdown = false"
                            class="flex w-full items-center justify-between px-4 py-3 text-left hover:bg-blue-50 transition-colors duration-200 border-b border-gray-100 last:border-b-0 text-xs"
                          >
                            <span class="flex-1 truncate">
                              {{ classification.company }} | {{ classification.project }} | {{ classification.flock_name }} | {{ classification.shed_name }} | {{ classification.batch_name }} | {{ classification.transaction_no }} | {{ classification.classification_date }}
                            </span>
                            <CheckCircle2 v-if="selectedClassification == classification.id" class="h-3 w-3 text-blue-500 flex-shrink-0" />
                          </button>
                          <div
                            v-if="filteredClassifications.length === 0"
                            class="px-4 py-6 text-center text-gray-500 text-xs"
                          >
                            <Search class="mx-auto h-5 w-5 text-gray-400" />
                            <div class="mt-2">No results found for "{{ flockSearchQuery }}"</div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>


              <!-- Egg Category -->
              <div class="space-y-3">
                <Label class="text-sm font-semibold text-gray-700 dark:text-gray-300 flex items-center gap-2">Egg Category</Label>
                <select v-model="selectedType" class="w-full px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm">
                  <option value=null disabled>Select egg category</option>
                  <option value="commercial">Commercial Eggs</option>
                  <option value="hatching">Hatching Eggs</option>
                </select>
              </div>
            </div>

            <!-- Summary Cards -->
            <div v-if="selectedClassification && selectedType" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
              <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg p-4 text-white shadow-md">
                <p class="text-blue-100 text-xs font-medium">Total Eggs</p>
                <p class="text-2xl font-bold mt-1">{{ eggData.total_eggs.toLocaleString() }}</p>
              </div>

              <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg p-4 text-white shadow-md">
                <p class="text-green-100 text-xs font-medium">{{ selectedType === 'commercial' ? 'Commercial Eggs' : 'Hatching Eggs' }}</p>
                <p class="text-2xl font-bold mt-1">{{ relevantEggs.toLocaleString() }}</p>
              </div>

              <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg p-4 text-white shadow-md">
                <p class="text-orange-100 text-xs font-medium">Ungraded Eggs</p>
                <p class="text-2xl font-bold mt-1">{{ ungraded.toLocaleString() }}</p>
              </div>
            </div>

            <!-- Grades Input Section -->
            <div v-if="filteredGrades.length > 0" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="(grade, index) in filteredGrades" :key="grade.id" class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                  <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Grade {{ grade.name }}</h3>
                  <p class="text-xs text-gray-600 dark:text-gray-400">Weight: {{ grade.min_weight }}mg - {{ grade.max_weight }}mg</p>
                  <Label class="text-xs font-medium text-gray-700 dark:text-gray-300 mt-2">Quantity</Label>
                  <Input type="number" min="0" :max="relevantEggs" v-model="form.grades[index].quantity" class="w-full mt-1 text-sm" placeholder="Enter quantity" />
                </div>
              </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end pt-4 border-t border-gray-200 dark:border-gray-700 mt-6">
              <Button type="submit" :disabled="form.processing || !selectedClassification || !selectedType">
                {{ form.processing ? 'Saving...' : 'Save Grades' }}
              </Button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
