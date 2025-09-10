<script setup lang="ts">
import { defineProps } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps<{
  grades: Array<any>
}>()
</script>

<template>
  <AppLayout>
    <Head title="Egg Classification Grades List" />

    <div class="p-6 m-5 bg-white rounded-xl shadow-md">
      <h1 class="text-2xl font-semibold mb-4">Egg Classification Grades</h1>

      <table class="w-full border-collapse">
        <thead>
          <tr class="bg-gray-100">
            <th class="border px-4 py-2 text-left">ID</th>
            <th class="border px-4 py-2 text-left">Batch / Transaction</th>
            <th class="border px-4 py-2 text-left">Classification Date</th>
            <th class="border px-4 py-2 text-left">Grade</th>
            <th class="border px-4 py-2 text-left">Type</th>
            <th class="border px-4 py-2 text-left">Quantity</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="grade in props.grades" :key="grade.id">
            <td class="border px-4 py-2">{{ grade.id }}</td>
            <td class="border px-4 py-2">
              {{ grade.classification.batchAssign.batch.transaction_no }} —
              {{ grade.classification.batchAssign.batch.name }}
            </td>
            <td class="border px-4 py-2">{{ grade.classification.classification_date }}</td>
            <td class="border px-4 py-2">{{ grade.eggGrade.name }}</td>
            <td class="border px-4 py-2">{{ grade.eggGrade.type === 1 ? 'Commercial' : 'Hatching' }}</td>
            <td class="border px-4 py-2">{{ grade.quantity }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>
