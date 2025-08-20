<script setup lang="ts">
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import FilterControls from '@/components/FilterControls.vue'
import Pagination from '@/components/Pagination.vue'
import { useListFilters } from '@/composables/useListFilters'
import { useNotifier } from '@/composables/useNotifier'
import { usePermissions } from '@/composables/usePermissions'
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

// Provide fallback so template doesn’t break
const psReceivesData = props.psReceives ?? {
  data: [],
  meta: { current_page: 1, last_page: 1, per_page: 10, total: 0 }
}

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

const breadcrumbs: BreadcrumbItem[] = [{ title: 'PS Receives', href: '/ps-receive' }]

// Modal state
const showModal = ref(false)
const modalTitle = ref('Lab Test')

// Default form
const defaultForm = {
  id: null,
  pi_no: '',
  pi_date: '',
  order_no: '',
  order_date: '',
  lc_no: '',
  lc_date: '',
  supplier_id: '',
  breed_type: '',
  country_of_origin: '',
  transport_type: '',
  ps_receive_id: null,
  lab_type: 'Gov Lab',
  remarks: '',
  status: 1,
  ps_male_rec_box: 0,
  ps_male_qty: 0,
  ps_female_rec_box: 0,
  ps_female_qty: 0,
  ps_total_qty: 0,
  ps_total_re_box_qty: 0,
  ps_challan_box_qty: 0,
  ps_gross_weight: 0,
  ps_net_weight: 0,
  // new fields for lab test
  female_qty: 0,
  male_qty: 0,
  total_qty: 0,
}

const form = useForm({ ...defaultForm })

// Draggable modal
const modalRef = ref<HTMLElement | null>(null)
let offsetX = 0, offsetY = 0, isDragging = false

const startDrag = (event: MouseEvent) => {
  if (!modalRef.value) return
  isDragging = true
  const rect = modalRef.value.getBoundingClientRect()
  offsetX = event.clientX - rect.left
  offsetY = event.clientY - rect.top
  document.addEventListener('mousemove', onDrag)
  document.addEventListener('mouseup', stopDrag)
}
const onDrag = (event: MouseEvent) => {
  if (!isDragging || !modalRef.value) return
  modalRef.value.style.left = `${event.clientX - offsetX}px`
  modalRef.value.style.top = `${event.clientY - offsetY}px`
  modalRef.value.style.position = 'absolute'
  modalRef.value.style.margin = '0'
}
const stopDrag = () => {
  isDragging = false
  document.removeEventListener('mousemove', onDrag)
  document.removeEventListener('mouseup', stopDrag)
}

// Open modal and fetch data
const openModal = (id: number) => {
  form.get(`/ps-receive/${id}/data`, {
    preserveState: true,
    onSuccess: (page) => {
      const psReceive = page.props.psReceive ?? {}
      Object.assign(form, { ...defaultForm, ...psReceive, ps_receive_id: id  })
      modalTitle.value = 'Lab test'
      showModal.value = true
    }
  })
}

// Save form
const saveForm = () => {
  form.post('/ps-receive/storelab')
  showModal.value = false
}

function updateTotalQty() {
  form.total_qty = Number(form.female_qty) + Number(form.male_qty);
}
</script>

<template>
  <Head title="PS Receives" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow-md">
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4">
        <h1 class="text-3xl font-semibold text-gray-800 dark:text-white">PS Receives</h1>
        <Link
          v-if="can('ps.receive.create')"
          href="/ps-receive/create"
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
              <th class="px-6 py-3 text-left font-semibold">PI No</th>
              <th class="px-6 py-3 text-left font-semibold">Receive Date</th>
              <th class="px-6 py-3 text-left font-semibold">Supplier</th>
              <th class="px-6 py-3 text-left font-semibold">Total Quantity</th>
              <th class="px-6 py-3 text-left font-semibold">Total Box</th>
              <th class="px-6 py-3 text-left font-semibold">Remarks</th>
              <th class="px-6 py-3 text-left font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            <tr v-for="item in props.psReceives?.data ?? []" :key="item.id" class="hover:bg-gray-50 dark:hover:bg-gray-800">
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ item.pi_no }}</td>
              <td class="px-6 py-4 text-gray-600 dark:text-gray-300">{{ dayjs(item.receive_date).format('YYYY-MM-DD') }}</td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ item.supplier?.name ?? 'N/A' }}</td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ item.chick_counts?.ps_total_qty ?? '-' }}</td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ item.chick_counts?.ps_total_re_box_qty ?? '-' }}</td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">{{ item.remarks ?? '-' }}</td>
              <td class="px-6 py-4 flex gap-4 items-center">
                <Link
                  v-if="can('ps.receive.edit')"
                  :href="`/ps-receive/${item.id}/edit`"
                  class="text-indigo-600 hover:underline font-medium"
                >
                  Edit
                </Link>
                <button v-if="can('ps.receive.delete')" @click="deleteReceive(item.id)" class="text-red-600 hover:underline font-medium">Delete</button>
                <button  @click="openModal(item.id)" class="text-indigo-600 hover:underline font-medium">
                  Lab Test
                </button>
             
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

    <!-- Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex justify-center pt-6 bg-black/50"
      @click.self="showModal = false"
    >
      <div
        ref="modalRef"
        class="bg-white rounded-lg w-full max-w-3xl shadow-lg p-4"
        style="top: 100px; position: absolute;"
      >
        <div
          class="flex justify-between items-center cursor-move border-b p-2"
          @mousedown="startDrag"
        >
          <h3 class="text-xl font-semibold">{{ modalTitle }}</h3>
          <button @click="showModal = false" class="text-gray-500 hover:text-gray-900">✕</button>
        </div>

        <div class="p-4 grid grid-cols-2 gap-4">
          <div>
            <label>PI No</label>
            <input v-model="form.pi_no" type="text" class="w-full border p-2" disabled />
          </div>

          <div>
            <label>PI Date</label>
            <input v-model="form.pi_date" type="date" class="w-full border p-2" />
          </div>
          <div>
            <label>Lab Type</label>
            <select v-model="form.lab_type" class="w-full border p-2">
              <option value="Gov Lab">Gov Lab</option>
              <option value="Company Lab">Company Lab</option>
            </select>
          </div>
          <div>
            <label>Female Receive Qty</label>
            <input v-model="form.ps_female_qty" type="number" class="w-full border p-2" />
          </div>

          <div>
            <label>Male Receive Qty</label>
            <input v-model="form.ps_male_qty" type="number" class="w-full border p-2" />
          </div>

          <div>
            <label>Receive Total Qty</label>
            <input v-model="form.ps_total_qty" type="number" class="w-full border p-2" />
          </div>


          <div>
            <label>Lab Female Qty</label>
            <input v-model.number="form.female_qty" type="number" class="w-full border p-2" @input="updateTotalQty" />
          </div>

          <div>
            <label>Lab Male Qty</label>
            <input v-model.number="form.male_qty" type="number" class="w-full border p-2" @input="updateTotalQty" />
          </div>

          <div>
            <label>Lab Total Qty</label>
            <input v-model.number="form.total_qty" type="number" class="w-full border p-2" disabled />
          </div>

          <div>
            <label>Remarks</label>
            <textarea v-model="form.remarks" class="w-full border p-2"></textarea>
          </div>
        </div>

        <div class="flex justify-end gap-2 p-4 border-t">
          <button class="bg-gray-300 p-2 rounded" @click="showModal = false">Cancel</button>
          <button class="bg-chicken text-white p-2 rounded" @click="saveForm">Save</button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
