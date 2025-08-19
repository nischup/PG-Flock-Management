<script setup lang="ts">
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link,  useForm} from '@inertiajs/vue3';
import FilterControls from '@/components/FilterControls.vue';
import Pagination from '@/components/Pagination.vue';
import { useListFilters } from '@/composables/useListFilters';
import { useNotifier } from '@/composables/useNotifier';
import { type BreadcrumbItem } from '@/types';
import { usePermissions } from '@/composables/usePermissions';
import dayjs from 'dayjs'
const props = defineProps<{
  psReceives: {
    data: Array<{
      id: number;
      pi_no: string;
      receive_date: string;
      supplier: { id: number; name: string } | null;
      quantity: number;
      remarks?: string | null;
    }>;
    meta: {
      current_page: number;
      last_page: number;
      per_page: number;
      total: number;
    };
  };
  filters: { search?: string; per_page?: number };
}>();

useListFilters({
  routeName: '/ps-receive',
  filters: props.filters,
});

const { confirmDelete } = useNotifier();
const { can } = usePermissions();
// Modal form (Add/Edit)
const form = useForm({
  name: '',
  status: 1,
})

const deleteReceive = (id: number) => {
  confirmDelete({
    url: `/ps-receive/${id}`,
    text: 'This will permanently delete the record.',
    successMessage: 'PS Receive deleted.',
  });
};

const supplierMap: Record<string, string> = {
  '1': 'Hubbard Breeders',
  '2': 'Kazi'
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'PS Receives', href: '/ps-receive' },
];


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
const showModal = ref(false)
const editingShed = ref<Shed | null>(null)
// Open modal
const openModal = (shed: Shed | null = null) => {
  if (shed) {
    editingShed.value = shed
    form.name = shed.name
    form.status = shed.status
  } else {
    editingShed.value = null
    form.reset()
    form.status = 1
  }
  showModal.value = true
}
</script>

<template>
  <Head title="PS Receives" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 bg-white dark:bg-gray-900 rounded-xl shadow-md">
      <!-- Header -->
      <div
        class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 gap-4"
      >
        <h1 class="text-3xl font-semibold text-gray-800 dark:text-white">
          PS Receives
        </h1>
        <Link
          v-if="can('ps.receive.create')"
          href="/ps-receive/create"
          class="inline-flex items-center px-4 py-2 bg-chicken hover:bg-chicken text-white text-sm font-semibold rounded shadow transition"
        >
          + Add
        </Link>
      </div>

      <!-- Filters -->
      <FilterControls :filters="props.filters" routeName="/ps-receive" />

      <!-- Table -->
      <div
        class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700"
      >
        <table
          class="min-w-full divide-y divide-gray-200 dark:divide-gray-700 text-sm"
        >
          <thead
            class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-300"
          >
            <tr>
              <th class="px-6 py-3 text-left font-semibold">PI No</th>
              <th class="px-6 py-3 text-left font-semibold">Receive Date</th>
              <th class="px-6 py-3 text-left font-semibold">Supplier</th>
              <th class="px-6 py-3 text-left font-semibold">Quantity</th>
              <th class="px-6 py-3 text-left font-semibold">Remarks</th>
              <th class="px-6 py-3 text-left font-semibold">Actions</th>
            </tr>
          </thead>
          <tbody
            class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700"
          >
            <tr
              v-for="item in psReceives.data"
              :key="item.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-800"
            >
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">
                {{ item.pi_no }}
              </td>
              <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                {{ dayjs(item.created_at).format('YYYY-MM-DD') }}
              </td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">
                {{ supplierMap[item.supplier_id] || 'N/A' }}
              </td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">
                {{ item.quantity }}
              </td>
              <td class="px-6 py-4 text-gray-800 dark:text-gray-100">
                {{ item.remarks || '-' }}
              </td>
              <td class="px-6 py-4 flex gap-4 items-center">
                <Link
                  v-if="can('ps.receive.edit')"
                  :href="`/ps-receive/${item.id}/edit`"
                  class="text-indigo-600 hover:underline font-medium"
                >
                  Edit
                </Link>
                <button
                  @click="deleteReceive(item.id)"
                  class="text-red-600 hover:underline font-medium"
                  v-if="can('ps.receive.delete')"
                >
                  Delete
                </button>
                <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="openModal(shed)">✏ Labtest</button>
              </td>
            </tr>
            <tr v-if="psReceives.data.length === 0">
              <td
                colspan="6"
                class="px-6 py-6 text-center text-gray-500 dark:text-gray-400"
              >
                No PS Receives found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <Pagination :meta="psReceives.meta" class="mt-6" />
    </div>

    <div v-if="showModal" class="fixed inset-0 z-50 flex justify-center pt-6" @click.self="showModal = false">
  <div ref="modalRef" class="bg-white rounded-lg border border-gray-300 shadow-lg w-full max-w-2xl" style="top: 100px; position: absolute;">
    
    <!-- Modal Header -->
    <div class="flex items-center justify-between p-4 border-b border-gray-200 cursor-move" @mousedown="startDrag">
      <h3 class="text-xl font-semibold text-gray-900">
            {{ editingShed ? 'Edit Shed' : 'Add New Shed' }}
          </h3>
          <button type="button" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex justify-center items-center" @click="resetForm">✕</button>
        </div>

        <!-- Modal Body -->
        <div class="p-4 space-y-4">

          <!-- ✅ Chick Counts Section -->
          <div class="space-y-4 mt-4">
            <h2 class="text-xl font-semibold">Chick Counts</h2>

            <!-- Challan and Weights -->
            <div class="grid grid-cols-3 gap-4 items-center">
              <div class="flex flex-col">
                <Label>Challan Box Qty</Label>
                <Input v-model.number="form.ps_challan_box_qty" type="number" class="mt-2" />
              </div>

              <div class="flex flex-col">
                <Label>Gross Weight</Label>
                <Input v-model.number="form.ps_gross_weight" type="number" step="0.01" class="mt-2" />
              </div>

              <div class="flex flex-col">
                <Label>Net Weight</Label>
                <Input v-model.number="form.ps_net_weight" type="number" step="0.01" class="mt-2" />
              </div>
            </div>

            <!-- Chicks Section -->
            <div class="grid grid-cols-3 gap-4 mb-6">
              <div class="flex flex-col">
                <Label>Female Chicks Qty</Label>
                <Input v-model.number="form.ps_female_qty" type="number" class="mt-2" />
              </div>

              <div class="flex flex-col">
                <Label>Male Chicks Qty</Label>
                <Input v-model.number="form.ps_male_qty" type="number" class="mt-2" />
              </div>

              <div class="flex flex-col">
                <Label>Total Chicks Qty</Label>
                <Input v-model.number="form.ps_total_qty" type="number" class="mt-2" readonly />
              </div>
            </div>

            <!-- Box Count Section -->
            <div class="grid grid-cols-3 gap-4 mb-6">
              <div class="flex flex-col">
                <Label>Female Box Receive Qty</Label>
                <Input v-model.number="form.ps_female_rec_box" type="number" class="mt-2" />
              </div>

              <div class="flex flex-col">
                <Label>Male Box Receive Qty</Label>
                <Input v-model.number="form.ps_male_rec_box" type="number" class="mt-2" />
              </div>

              <div class="flex flex-col">
                <Label>Total Box Qty</Label>
                <Input v-model.number="form.ps_total_re_box_qty" type="number" class="mt-2" readonly />
              </div>
            </div>
          </div>

        </div>

        <!-- Modal Footer -->
        <div class="flex justify-end p-4 border-t border-gray-200">
          <button type="button" class="bg-gray-300 text-black mr-2 px-4 py-2 rounded" @click="resetForm">Cancel</button>
          <button type="button" class="bg-chicken text-white px-4 py-2 rounded" @click="submit">
            {{ editingShed ? 'Update' : 'Save' }}
          </button>
        </div>

      </div>
    

    </div>
  </AppLayout>
</template>
