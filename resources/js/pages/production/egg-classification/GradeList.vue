<script setup lang="ts">
import { defineProps } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

const props = defineProps<{
  grades: Array<any> | null
}>()

// Group grades by classification_id
const grouped = props.grades?.reduce((acc: any, grade: any) => {
  const id = grade.classification?.id
  if (!acc[id]) acc[id] = { classification: grade.classification, grades: [] }
  acc[id].grades.push(grade)
  return acc
}, {}) ?? {}

const groupedArray = Object.values(grouped)
</script>

<template>
  <AppLayout>
    <Head title="Egg Classification Grades List" />

    <div class="p-6 m-5 bg-white rounded-xl shadow-md">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-semibold">Egg Classification Grades</h1>
        <!-- Add Button -->
        <Link
          href="/egg-classification-grades/create"
          class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800"
        >
          + Add
        </Link>
      </div>

      <table class="w-full border-collapse">
        <thead>
          <tr class="bg-gray-100">
            <th class="border px-4 py-2 text-left">Classification ID</th>
            <th class="border px-4 py-2 text-left">Batch / Transaction</th>
            <th class="border px-4 py-2 text-left">Classification Date</th>
            <th class="border px-4 py-2 text-left">Grades / Quantity</th>
            <th class="border px-4 py-2 text-left">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in groupedArray" :key="item.classification.id">
            <td class="border px-4 py-2">{{ item.classification?.id ?? '-' }}</td>
            <td class="border px-4 py-2">
              {{ item.classification?.batch_assign?.transaction_no ?? '-' }} -
              {{ item.classification?.batch_assign?.batch?.name ?? '-' }}
            </td>
            <td class="border px-4 py-2">{{ item.classification?.classification_date ?? '-' }}</td>
            <td class="border px-4 py-2">
              <ul>
                <li v-for="g in item.grades" :key="g.id">
                  {{ g.grade?.name ?? '-' }}: {{ g.quantity }}
                </li>
              </ul>
            </td>
            <td class="border px-4 py-2">
              <Link
                :href="`/egg-classification-grades/${item.classification.id}/edit`"
                class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600"
              >
                Edit
              </Link>
            </td>
          </tr>

          <tr v-if="groupedArray.length === 0">
            <td colspan="5" class="text-center py-4">No grades found</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>
