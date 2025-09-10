<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { useNotifier } from '@/composables/useNotifier'
// Props from Laravel
const props = defineProps<{
  transferBirds: Array<any>
  companies: Array<any>
  flocks: Array<any>
}>()
const { confirmDelete } = useNotifier();
// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Production', href: '/flock' },
  { title: 'Farm Receive', href: '/flock/assign' },
]

// Modal state
const showTransferModal = ref(false)

// Form
const form = useForm({
  transfer_bird_id: null,
  flock_id: 0,
  receive_company_id: null,
  transfer_date: '',
  receive_date: '',
  challan_female_qty: 0,
  challan_male_qty: 0,
  challan_total_qty: 0,
  receive_female_qty: 0,
  receive_male_qty: 0,
  receive_total_qty: 0,
  deviation_female_qty: 0,
  deviation_male_qty: 0,
  deviation_total_qty: 0,
  note: '',
})

// Open modal with transfer data
const openTransferModal = (transfer: any) => {
  form.transfer_bird_id = transfer.id
  form.flock_id = transfer.flock_id
  form.receive_company_id = transfer.to_company_id
  form.transfer_date = transfer.transfer_date
  form.receive_date = new Date().toISOString().split('T')[0]

  form.challan_female_qty = transfer.transfer_female_qty
  form.challan_male_qty = transfer.transfer_male_qty
  form.challan_total_qty = transfer.transfer_total_qty

  form.receive_female_qty = 0
  form.receive_male_qty = 0
  form.receive_total_qty = 0
  form.deviation_female_qty = 0
  form.deviation_male_qty = 0
  form.deviation_total_qty = 0
  form.note = ''

  showTransferModal.value = true
}

// Save transfer
const saveTransfer = () => {
  form.receive_total_qty = (form.receive_female_qty || 0) + (form.receive_male_qty || 0)
  form.deviation_female_qty = form.challan_female_qty - form.receive_female_qty
  form.deviation_male_qty = form.challan_male_qty - form.receive_male_qty
  form.deviation_total_qty = form.challan_total_qty - form.receive_total_qty
  
  form.post(route('production-firm-receive.store'), {
    onSuccess: () => {
      showTransferModal.value = false
    },
  })
}
</script>

<template>
  <Head title="Production Firm Receive" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <!-- Title -->
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800 dark:text-white">
          Transfer Birds
        </h1>
      </div>

      <!-- List Table -->
      <div class="overflow-x-auto rounded-xl shadow bg-white dark:bg-gray-800">
        <table class="w-full text-left border-collapse">
          <thead class="bg-gray-100 dark:bg-gray-700">
            <tr>
              <th class="px-4 py-2 border-b">#</th>
              <th class="px-4 py-2 border-b">Transaction No</th>
              <th class="px-4 py-2 border-b">Flock No</th>
              <th class="px-4 py-2 border-b">Female Qty</th>
              <th class="px-4 py-2 border-b">Male Qty</th>
              <th class="px-4 py-2 border-b">Total Qty</th>
              <th class="px-4 py-2 border-b">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(transfer, index) in props.transferBirds"
              :key="transfer.id"
              class="hover:bg-gray-50 dark:hover:bg-gray-700"
            >
              <td class="px-4 py-2 border-b">{{ index + 1 }}</td>
              <td class="px-4 py-2 border-b">{{ transfer.transaction_no }}</td>
              <td class="px-4 py-2 border-b">{{ transfer.flock_no }}</td>
              <td class="px-4 py-2 border-b">{{ transfer.transfer_female_qty }}</td>
              <td class="px-4 py-2 border-b">{{ transfer.transfer_male_qty }}</td>
              <td class="px-4 py-2 border-b font-bold">{{ transfer.transfer_total_qty }}</td>
              <td class="px-4 py-2 border-b">
                <button
                  class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600"
                  @click="openTransferModal(transfer)"
                >
                  Receive
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Transfer Modal -->
      <div
        v-if="showTransferModal"
        class="fixed inset-0 bg-black/20 flex items-center justify-center z-50"
        @click="showTransferModal = false"
      >
        <div
          class="bg-white rounded-lg shadow-lg w-[800px] max-w-full"
          @click.stop
        >
          <!-- Header -->
          <div class="p-3 bg-gray-200 rounded-t-lg">
            <h2 class="font-bold">Receive Flock</h2>
          </div>

          <!-- Body -->
          <div class="p-6 space-y-4">
            <!-- Project & Flock -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">Receive Project</label>
                <select v-model="form.receive_company_id" class="w-full border rounded px-3 py-2">
                  <option value="">Select Company</option>
                  <option v-for="c in props.companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Flock</label>
                <select v-model="form.flock_id" class="w-full border rounded px-3 py-2">
                  <option value="">Select Flock</option>
                  <option v-for="f in props.flocks" :key="f.id" :value="f.id">{{ f.name }}</option>
                </select>
              </div>
            </div>

            <!-- Dates -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">Transfer Date</label>
                <input type="date" v-model="form.transfer_date" class="w-full border rounded px-3 py-2" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Receive Date</label>
                <input type="date" v-model="form.receive_date" class="w-full border rounded px-3 py-2" />
              </div>
            </div>

            <!-- Challan -->
            <div class="grid grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">Challan Female</label>
                <input type="number" v-model="form.challan_female_qty" readonly class="w-full border rounded px-3 py-2 bg-gray-100" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Challan Male</label>
                <input type="number" v-model="form.challan_male_qty" readonly class="w-full border rounded px-3 py-2 bg-gray-100" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Challan Total</label>
                <input type="number" v-model="form.challan_total_qty" readonly class="w-full border rounded px-3 py-2 bg-gray-100" />
              </div>
            </div>

            <!-- Receive -->
            <div class="grid grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">Receive Female</label>
                <input type="number" v-model.number="form.receive_female_qty" class="w-full border rounded px-3 py-2" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Receive Male</label>
                <input type="number" v-model.number="form.receive_male_qty" class="w-full border rounded px-3 py-2" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Total Receive</label>
                <input type="number" :value="form.receive_female_qty + form.receive_male_qty" readonly class="w-full border rounded px-3 py-2 bg-gray-100" />
              </div>
            </div>

            <!-- Deviation -->
            <div class="grid grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium mb-1">Deviation Female</label>
                <input type="number" :value="form.challan_female_qty - (form.receive_female_qty || 0)" readonly class="w-full border rounded px-3 py-2 bg-gray-100" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Deviation Male</label>
                <input type="number" :value="form.challan_male_qty - (form.receive_male_qty || 0)" readonly class="w-full border rounded px-3 py-2 bg-gray-100" />
              </div>
              <div>
                <label class="block text-sm font-medium mb-1">Deviation Total</label>
                <input type="number" :value="form.challan_total_qty - ((form.receive_female_qty || 0) + (form.receive_male_qty || 0))" readonly class="w-full border rounded px-3 py-2 bg-gray-100" />
              </div>
            </div>

            <!-- Note -->
            <div>
              <label class="block text-sm font-medium mb-1">Note</label>
              <textarea v-model="form.note" rows="2" class="w-full border rounded px-3 py-2"></textarea>
            </div>
          </div>

          <!-- Footer -->
          <div class="p-4 flex justify-end border-t">
            <button
              class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400 mr-2"
              @click="showTransferModal = false"
            >
              Cancel
            </button>
            <button
              class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
              @click="saveTransfer"
              :disabled="form.processing"
            >
              Receive
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
