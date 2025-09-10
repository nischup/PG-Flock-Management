<script setup lang="ts">
import { ref, watch } from 'vue'
import { useForm, Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { useNotifier } from '@/composables/useNotifier'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const props = defineProps<{
  classifications: Array<{ id: number; classification_date: string }>
  grades: Array<{ id: number; name: string; type: number }>
}>();

const notifier = useNotifier()

// Selected classification and egg category
const selectedClassification = ref<number | null>(null)
const selectedType = ref<string | null>(null) // 'commercial' or 'hatching'

// Filtered grades for selected type
const filteredGrades = ref<Array<{ id: number; name: string }>>([])

// Form
const form = useForm({
  classification_id: null,
  type: '',
  grades: [] as { egg_grade_id: number; quantity: number }[],
})

// Watch for category change
watch(selectedType, (val) => {
  if (!val) return
  const typeNum = val === 'commercial' ? 1 : 2
  filteredGrades.value = props.grades.filter(g => g.type === typeNum)
  form.grades = filteredGrades.value.map(g => ({
    egg_grade_id: g.id,
    quantity: 0
  }))
})

function submit() {
  if (!selectedClassification.value || !selectedType.value) {
    notifier.error('Please select classification and egg category')
    return
  }
  form.classification_id = selectedClassification.value
  form.type = selectedType.value

  form.post(route('egg-classification-grades.store', selectedClassification.value), {
    onSuccess: () => notifier.success('Grades saved successfully'),
    onError: () => notifier.error('Failed to save grades'),
  })
}
</script>

<template>
  <AppLayout>
    <Head title="Grade Eggs" />

    <div class="max-w-4xl mx-auto py-6 space-y-6">
      <h1 class="text-2xl font-semibold">Egg Grading</h1>

      <!-- Select Classification -->
      <div>
        <Label>Select Classification</Label>
        <select
          v-model="selectedClassification"
          class="w-full mt-2 border rounded p-2"
        >
          <option value="" disabled>Select a classification</option>
          <option
            v-for="c in props.classifications"
            :key="c.id"
            :value="c.id"
          >
            ID: {{ c.id }} — {{ c.classification_date }}
          </option>
        </select>
      </div>

      <!-- Select Egg Category -->
      <div>
        <Label>Egg Category</Label>
        <select
          v-model="selectedType"
          class="w-full mt-2 border rounded p-2"
        >
          <option value="" disabled>Select egg category</option>
          <option value="commercial">Commercial</option>
          <option value="hatching">Hatching</option>
        </select>
      </div>

      <!-- Grades Input -->
      <div v-if="filteredGrades.length > 0" class="space-y-4 mt-4">
        <h2 class="font-medium text-lg">Enter quantities for grades:</h2>
        <div v-for="(grade, index) in filteredGrades" :key="grade.id" class="flex items-center gap-4">
          <span class="w-32 font-medium">{{ grade.name }}</span>
          <Input
            type="number"
            min="0"
            v-model="form.grades[index].quantity"
            class="flex-1"
          />
        </div>
      </div>

      <!-- Submit Button -->
      <div class="flex justify-end mt-6">
        <Button type="button" @click="submit">Save Grades</Button>
      </div>
    </div>
  </AppLayout>
</template>
