<script setup lang="ts">
import { ref,computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import FilterControls from '@/components/FilterControls.vue'
import Pagination from '@/components/Pagination.vue'
import { useListFilters } from '@/composables/useListFilters'
import { useNotifier } from '@/composables/useNotifier'
import { usePermissions } from '@/composables/usePermissions'
import { Button } from '@/components/ui/button'
import { Pencil, Trash2, FlaskConical } from "lucide-vue-next";
import listInfocard from '@/components/ListinfoCard.vue'
import dayjs from 'dayjs'
import { type BreadcrumbItem } from '@/types'

const props = defineProps<{
  psFirmReceives?: {
    data: Array<{
      id: number
      ps_receive_id: number
      job_no: string
      receipt_type: string
      source_type: string
      source_id: number
      flock_id: number
      flock_name: string
      receiving_company_id: number
      company_name: string
      firm_female_qty: number
      firm_male_qty: number
      firm_total_qty: number
      remarks?: string | null
      created_by: number
      status: number
      receive_date: string
    }>
    meta: {
      current_page: number
      last_page: number
      per_page: number
      total: number
    }
  }
  filters?: { search?: string; per_page?: number }
}>()


console.log(props.psFirmReceives);
useListFilters({ routeName: '/ps-receive', filters: props.filters })
const { confirmDelete } = useNotifier()
const { can } = usePermissions()

const deleteReceive = (id: number) => {
  confirmDelete({
    url: `/ps-receive/${id}`,
    text: 'This will permanently delete the record.',
    successMessage: 'PS Receive deleted.'
  })
}


const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Parent Stock', href: '/ps-receive' },
  { title: 'Farm Receive', href: '/ps-receive' },
  
];


const openDropdownId = ref<number | null>(null)
const toggleDropdown = (id: number) => {
  openDropdownId.value = openDropdownId.value === id ? null : id
}


// Demo card data grouped by PI No
const piCardData: Record<string, any[]> = {
  PI001: [
    
    { title: 'Receive Box', value: 12},
    { title: 'Male Box', value: 2 },
    { title: 'Female Box', value: 10 },
    { title: 'Sortage Box', value: 12 },
    { title: 'Excess Box', value: 12 },
  ],
  PI002: [
    { title: 'Receive Box', value: 18 },
    { title: 'Male Box', value: 5 },
    { title: 'Female Box', value: 13 },
    { title: 'Sortage Box', value: 1 },
    { title: 'Excess Box', value: 0 },
  ],
  PI003: [
   
    { title: 'Receive Box', value: 18 },
    { title: 'Male Box', value: 5 },
    { title: 'Female Box', value: 13 },
    { title: 'Sortage Box', value: 0 },
    { title: 'Excess Box', value: 1 },
  ]
}

// Selected PI
const selectedPI = ref('PI001')

// Cards to show based on selected PI
const cardData = computed(() => piCardData[selectedPI.value] || [])

</script>

<template>
  <Head title="PS Receives" />
    
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="w-full max-w-sm m-5">
    <select
        v-model="selectedPI"
        class="block w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-4 py-2 text-gray-900 dark:text-gray-100 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none sm:text-sm"
      >
        <option value="" disabled selected>Select PI No</option>
        <option value="PI001">PI001</option>
        <option value="PI002">PI002</option>
        <option value="PI003">PI003</option>
      </select>
    </div>
    <listInfocard :cards="cardData" />
    <div class="p-6 m-3 bg-white dark:bg-gray-900 rounded-xl shadow-md">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">Parent Stock Farm Receive Info.</h1>
        <Link
          v-if="can('ps-receive.create')"
          href="/ps-firm-receive/create"
          class="inline-flex items-center px-4 py-2 bg-chicken hover:bg-chicken text-white text-sm font-semibold rounded shadow transition"
        >
          + Add
        </Link>
      </div>

      <FilterControls :filters="props.filters" routeName="/ps-receive" />

      <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
            <tr>
           
              <th class="px-6 py-3 text-left font-bold">Flock Name</th>
              <th class="px-6 py-3 text-left font-bold">Company</th>
              <th class="px-6 py-3 text-left font-bold">Male Qty</th>
              <th class="px-6 py-3 text-left font-bold">Female Qty</th>
              <th class="px-6 py-3 text-left font-bold">Total Qty</th>
              <th class="px-6 py-3 text-left font-bold">Remarks</th>
              <th class="px-6 py-3 text-left font-bold">Receive Date</th>
              <th class="px-6 py-3 text-left font-bold">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="item in props.psFirmReceives?.data ?? []" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 odd:bg-white even:bg-gray-100">
            
              <td class="px-6 py-4">{{ item.flock_name }}</td>
              <td class="px-6 py-4">{{ item.company_name }}</td>
              <td class="px-6 py-4">{{ item.firm_male_qty }}</td>
              <td class="px-6 py-4">{{ item.firm_female_qty }}</td>
              <td class="px-6 py-4">{{ item.firm_total_qty }}</td>
              <td class="px-6 py-4">{{ item.remarks ?? '-' }}</td>
              <td class="px-6 py-4">{{ item.receive_date }}</td>
              <td class="px-6 py-4 flex gap-4">
                <Link
                  v-if="can('ps-receive.edit')"
                  :href="`/ps-firm-receive/${item.id}/edit`"
                  class="text-blue-600 hover:underline"
                >
                  Edit
                </Link>
                <button
                  v-if="can('ps-receive.delete')"
                  @click="deleteReceive(item.id)"
                  class="text-red-600 hover:underline"
                >
                  Delete
                </button>
              </td>
            </tr>
            <tr v-if="(props.psFirmReceives?.data ?? []).length === 0">
              <td colspan="9" class="text-center text-gray-500 dark:text-gray-400">No PS Firm Receives found.</td>
            </tr>
          </tbody>
        </table>
      </div>
      <Pagination :meta="props.psReceives?.meta ?? {}" class="mt-6" />
    </div>

    
  </AppLayout>
</template>
