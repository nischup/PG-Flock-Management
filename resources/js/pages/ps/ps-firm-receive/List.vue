<script setup lang="ts">
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import FilterControls from '@/components/FilterControls.vue'
import Pagination from '@/components/Pagination.vue'
import { useListFilters } from '@/composables/useListFilters'
import { useNotifier } from '@/composables/useNotifier'
import { usePermissions } from '@/composables/usePermissions'
import { Button } from '@/components/ui/button'
import { Pencil, Trash2, FlaskConical } from "lucide-vue-next";

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
  { title: 'Parent Stock', href: '/ps-receive' },
  { title: 'Farm Receive', href: '/ps-receive' },
  
];



const openDropdownId = ref<number | null>(null)
const toggleDropdown = (id: number) => {
  openDropdownId.value = openDropdownId.value === id ? null : id
}


</script>

<template>
  <Head title="PS Receives" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 m-3 bg-white dark:bg-gray-900 rounded-xl shadow-md">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">Parent Stock Farm Receive Info.</h1>
        <Link
          v-if="can('ps.receive.create')"
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
            <tr class="throw">
              <th class="px-6 py-3 text-left font-bold">Receiving Project</th>
              <th class="px-6 py-3 text-left font-bold">Flock No</th>
              <th class="px-6 py-3 text-left font-bold">LC No</th>
              <th class="px-6 py-3 text-left font-bold">PI No</th>
              <th class="px-6 py-3 text-left font-bold">Supplier</th>
              <th class="px-6 py-3 text-left font-bold">Total Box Qty</th>
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
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ item.supplier?.name ?? 'N/A' }}</td>
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
