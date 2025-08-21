<script setup lang="ts">
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import AppLayout from '@/layouts/AppLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useNotifier } from '@/composables/useNotifier'
import { useListFilters } from '@/composables/useListFilters'
import { usePermissions } from '@/composables/usePermissions'

interface Supplier {
  id: number
  name: string
  address: string | null
  origin: string | null
  contact_person: string | null
  contact_person_email: string | null
  contact_person_mobile: string | null
  status: number
  created_at: string
}

const props = defineProps<{
  suppliers: Supplier[]
  filters: { search?: string; per_page?: number; page?: number }
}>()

useListFilters({
  routeName: '/supplier',
  filters: props.filters,
})

const { can } = usePermissions()
const suppliers = ref<Supplier[]>([...props.suppliers])

// Modal state
const showModal = ref(false)
const editingSupplier = ref<Supplier | null>(null)

// Form
const form = useForm({
  supplier: {
    name: '',
    address: '',
    origin: '',
    contact_person: '',
    contact_person_email: '',
    contact_person_mobile: '',
    status: 1,
  }
})

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

// Open modal
const openModal = (supplier: Supplier | null = null) => {
  if (supplier) {
    editingSupplier.value = supplier
    form.supplier = { ...supplier }
  } else {
    editingSupplier.value = null
    form.reset()
    form.supplier.status = 1
  }
  showModal.value = true
}

// Reset form
const resetForm = () => {
  form.reset()
  form.supplier.status = 1
  editingSupplier.value = null
  showModal.value = false
}

useNotifier()

// Submit
const submit = () => {
  if (!form.supplier.name.trim()) return

  if (editingSupplier.value) {
    form.put(route('supplier.update', editingSupplier.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        const i = suppliers.value.findIndex(s => s.id === editingSupplier.value!.id)
        if (i !== -1) {
          suppliers.value[i] = { ...suppliers.value[i], ...form.supplier }
        }
        resetForm()
      },
    })
  } else {
    form.post(route('supplier.store'), {
      preserveScroll: true,
      onSuccess: (page) => {
        if ((page as any).props?.suppliers) {
          suppliers.value = (page as any).props.suppliers
        } else {
          suppliers.value.unshift({
            id: Date.now(),
            ...form.supplier,
            created_at: new Date().toISOString(),
          })
        }
        resetForm()
      },
    })
  }
}

// Dropdown for actions
const openDropdownId = ref<number | null>(null)
const toggleDropdown = (id: number) => {
  openDropdownId.value = openDropdownId.value === id ? null : id
}

// Toggle status
const toggleStatus = (supplier: Supplier) => {
  const newStatus = supplier.status === 1 ? 0 : 1
  router.put(route('supplier.update', supplier.id), { supplier: { ...supplier, status: newStatus } }, {
    preserveScroll: true,
    onSuccess: () => {
      const i = suppliers.value.findIndex(s => s.id === supplier.id)
      if (i !== -1) suppliers.value[i].status = newStatus
    },
    onError: () => {
      Swal.fire('Error!', 'Could not update status.', 'error')
    },
    onFinish: () => {
      openDropdownId.value = null
    },
  })
}

// Breadcrumbs
const breadcrumbs = [
  { title: 'Master Setup', href: '/master-setup' },
  { title: 'Supplier', href: '/master-setup/supplier' },
]
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Suppliers" />

    <div class="px-4 py-6">
      <div class="flex items-center justify-between mb-4">
        <HeadingSmall title="Supplier List" />
        <Button v-if="can('supplier.create')" class="bg-chicken hover:bg-yellow-600 text-white" @click="openModal()">
          + Add New
        </Button>
      </div>

      <!-- Table -->
      <table class="w-full border">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-2 border text-left">#</th>
            <th class="p-2 border text-left">Name</th>
            <th class="p-2 border text-left">Address</th>
            <th class="p-2 border text-left">Origin</th>
            <th class="p-2 border text-left">Contact</th>
            <th class="p-2 border text-left">Mobile</th>
            <th class="p-2 border text-left">Status</th>
            <th class="p-2 border text-left">Created At</th>
            <th class="p-2 border text-left">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(supplier, index) in suppliers" :key="supplier.id">
            <td class="p-2 border">{{ index + 1 }}</td>
            <td class="p-2 border">{{ supplier.name }}</td>
            <td class="p-2 border">{{ supplier.address }}</td>
            <td class="p-2 border">{{ supplier.origin }}</td>
            <td class="p-2 border">{{ supplier.contact_person }}</td>
            <td class="p-2 border">{{ supplier.contact_person_mobile }}</td>
            <td class="p-2 border">
              <span :class="supplier.status === 1 ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                {{ supplier.status === 1 ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="p-2 border">{{ supplier.created_at }}</td>
            <td class="p-2 border relative">
              <Button size="sm" class="bg-gray-500 hover:bg-gray-600 text-white" @click="toggleDropdown(supplier.id)">
                Actions ▼
              </Button>
              <div
                v-if="openDropdownId === supplier.id"
                class="absolute right-0 mt-1 w-40 bg-white border rounded shadow-md z-10"
                @click.stop
              >
                <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="openModal(supplier)">✏ Edit</button>
                <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="toggleStatus(supplier)">
                  {{ supplier.status === 1 ? 'Deactivate' : 'Activate' }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Draggable Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex justify-center pt-6" @click.self="showModal = false">
      <div
        ref="modalRef"
        class="bg-white rounded-lg border border-gray-300 shadow-lg w-full max-w-2xl"
        style="top: 100px; position: absolute;"
      >
        <div
          class="flex items-center justify-between p-4 border-b border-gray-200 cursor-move"
          @mousedown="startDrag"
        >
          <h3 class="text-xl font-semibold text-gray-900">
            {{ editingSupplier ? 'Edit Supplier' : 'Add New Supplier' }}
          </h3>
          <button
            type="button"
            class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex justify-center items-center"
            @click="resetForm"
          >
            ✕
          </button>
        </div>

        <div class="p-4 space-y-4">
          <div>
            <Label for="name">Name</Label>
            <Input v-model="form.supplier.name" id="name" />
            <span v-if="form.errors['supplier.name']" class="text-red-600 text-sm">{{ form.errors['supplier.name'] }}</span>
          </div>

          <div>
            <Label for="address">Address</Label>
            <Input v-model="form.supplier.address" id="address" />
          </div>

          <div>
            <Label for="origin">Origin</Label>
            <Input v-model="form.supplier.origin" id="origin" />
          </div>

          <div>
            <Label for="contact_person">Contact Person</Label>
            <Input v-model="form.supplier.contact_person" id="contact_person" />
          </div>

          <div>
            <Label for="contact_person_email">Email</Label>
            <Input v-model="form.supplier.contact_person_email" id="contact_person_email" />
          </div>

          <div>
            <Label for="contact_person_mobile">Mobile</Label>
            <Input v-model="form.supplier.contact_person_mobile" id="contact_person_mobile" />
          </div>

          <div>
            <Label for="status">Status</Label>
            <select v-model="form.supplier.status" id="status" class="w-full border rounded p-2">
              <option :value="1">Active</option>
              <option :value="0">Inactive</option>
            </select>
          </div>
        </div>

        <div class="flex justify-end p-4 border-t border-gray-200">
          <Button class="bg-gray-300 text-black mr-2" @click="resetForm">Cancel</Button>
          <Button class="bg-chicken text-white" @click="submit">
            {{ editingSupplier ? 'Update' : 'Save' }}
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
