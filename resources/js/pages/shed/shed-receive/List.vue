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
  psReceives?: {
    data: Array<{
      id: number
      pi_no: string
      receive_date: string
      supplier: { id: number; name: string } | null
      remarks?: string | null

      // Chick counts (hasOne)
      chick_counts?: {
        id: number
        ps_total_qty: number
        ps_total_re_box_qty: number
      } | null
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
  { title: 'Shed', href: '/shed-receive' },
  { title: 'Shed Receive', href: '/shed-receive' },

];



const openDropdownId = ref<number | null>(null)
const toggleDropdown = (id: number) => {
  openDropdownId.value = openDropdownId.value === id ? null : id
}



// Demo card data grouped by PI No
const piCardData: Record<string, any[]> = {
  PI001: [
    
    { title: 'Opening Chicks', value: 12500},
    { title: 'Total Chicks', value: 12000 },
    { title: 'Male Chicks', value: 2000 },
    { title: 'Female Chicks', value: 10000 },
    { title: 'Mortality', value: 500 },
  ],
  PI002: [
    
    { title: 'Opening Chicks', value: 13000},
    { title: 'Total Chicks', value: 12500 },
    { title: 'Male Chicks', value: 2000 },
    { title: 'Female Chicks', value: 10000 },
    { title: 'Mortality', value: 500 },
  ],
  PI003: [
    { title: 'Opening Chicks', value: 11000},
    { title: 'Total Chicks', value: 10500 },
    { title: 'Male Chicks', value: 2000 },
    { title: 'Female Chicks', value: 10000 },
    { title: 'Mortality', value: 500 },
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
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">Shed Receive Info.</h1>
        <Link
          href="/shed-receive/create"
          class="inline-flex items-center px-4 py-2 bg-chicken hover:bg-chicken text-white text-sm font-semibold rounded shadow transition"
        >
          + Add
        </Link>
      </div>

      <FilterControls :filters="props.filters" routeName="/shed-receive" />

      <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
            <tr class="throw">
              <th class="px-6 py-3 text-left font-bold">Project</th>
              <th class="px-6 py-3 text-left font-bold">Flock No</th>
              <th class="px-6 py-3 text-left font-bold">Shed No</th>
              <th class="px-6 py-3 text-left font-bold">Receive Box Qty</th>
              <th class="px-6 py-3 text-left font-bold">Receive Date</th>
              <th class="px-6 py-3 text-left font-bold">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="item in props.psReceives?.data ?? []" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-800 odd:bg-white even:bg-gray-100">
             <td class="px-6 py-4 text-gray-800 dark:text-gray-100">
              {{ item.shipment_type_id === 1 ? 'Local' : 'Foreign' }}
            </td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ item.pi_no }}</td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ item.lc_no ?? 'N/A' }}</td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ item.chick_counts?.ps_total_re_box_qty ?? '-' }}</td>
              <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ dayjs(item.receive_date).format('YYYY-MM-DD') }}</td>
              <td class="px-6 py-4 flex gap-4 items-center">
                <Button size="sm" class="bg-gray-500 hover:bg-gray-600 text-white" @click="toggleDropdown(item.id)">
                  Actions ▼
                </Button>
                <div
                    v-if="openDropdownId === item.id"
                    class="absolute right-0 mt-1 w-40 bg-white border rounded shadow-md z-10 flex flex-col"
                    @click.stop
                  >
                   <!-- Edit -->
                      <Link
                        v-if="can('ps.receive.edit')"
                        :href="`/ps-receive/${item.id}/edit`"
                        class="px-4 py-2 text-left hover:bg-blue-50 text-blue-600 flex items-center gap-2"
                      >
                        <Pencil class="w-4 h-4" />
                        <span>Edit</span>
                      </Link>

                      <!-- Delete -->
                      <button
                        v-if="can('ps.receive.delete')"
                        @click="deleteReceive(item.id)"
                        class="px-4 py-2 text-left hover:bg-red-50 text-red-600 flex items-center gap-2 w-full"
                      >
                        <FileText class="w-4 h-4" />
                        <span>Report</span>
                      </button>
                  </div>
              </td>
            </tr>
            <tr v-if="(props.psReceives?.data ?? []).length === 0">
              <td colspan="6" class="px-6 py-6 text-center text-gray-500 dark:text-gray-400">No PS Receives found.</td>
            </tr>
          </tbody>
        </table>
      </div>
      <Pagination :meta="props.psReceives?.meta ?? {}" class="mt-6" />
    </div>

    
  </AppLayout>
</template>
