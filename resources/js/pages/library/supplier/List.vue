<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useNotifier } from '@/composables/useNotifier'

interface Supplier {
  id: number
  name: string
  supplier_type: 'Local' | 'Foreign'
  address?: string
  origin?: string
  contact_person?: string
  contact_person_email?: string
  contact_person_mobile?: string
  status: number
  created_at: string
}

// Props & state
const props = defineProps<{ suppliers: Supplier[] }>()
const suppliers = ref<Supplier[]>([...props.suppliers])
const showModal = ref(false)
const editingSupplier = ref<Supplier | null>(null)
const form = useForm({
  name: '',
  supplier_type: 'Local',
  address: '',
  origin: '',
  contact_person: '',
  contact_person_email: '',
  contact_person_mobile: '',
  status: 1,
})
const openDropdownId = ref<number | null>(null)
const modalRef = ref<HTMLElement | null>(null)

// Draggable modal state
let offsetX = 0
let offsetY = 0
let isDragging = false

// Breadcrumbs
const breadcrumbs = [
  { title: 'Master Setup', href: '/master-setup' },
  { title: 'Supplier', href: '/master-setup/supplier' },
]

// -----------------------
// Modal Drag Functions
// -----------------------
const startDrag = (event: MouseEvent) => {
  if (!modalRef.value) return
  isDragging = true
  const rect = modalRef.value.getBoundingClientRect()
  offsetX = event.clientX - rect.left
  offsetY = event.clientY - rect.top
  event.preventDefault()
}

const onDrag = (event: MouseEvent) => {
  if (!isDragging || !modalRef.value) return
  Object.assign(modalRef.value.style, {
    left: `${event.clientX - offsetX}px`,
    top: `${event.clientY - offsetY}px`,
    position: 'absolute',
    margin: '0',
  })
}

const stopDrag = () => (isDragging = false)

// -----------------------
// Handle Click Outside
// -----------------------
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement
  const dropdowns = document.querySelectorAll('.dropdown, .dropdown-button')
  for (let i = 0; i < dropdowns.length; i++) {
    if (dropdowns[i].contains(target)) return
  }
  const modalEl = modalRef.value
  if (modalEl && modalEl.contains(target)) return
  openDropdownId.value = null
}

// -----------------------
// Lifecycle
// -----------------------
onMounted(() => {
  document.addEventListener('mousemove', onDrag)
  document.addEventListener('mouseup', stopDrag)
  document.addEventListener('click', handleClickOutside)
})
onBeforeUnmount(() => {
  document.removeEventListener('mousemove', onDrag)
  document.removeEventListener('mouseup', stopDrag)
  document.removeEventListener('click', handleClickOutside)
})

// -----------------------
// Modal Functions
// -----------------------
const openModal = (supplier: Supplier | null = null) => {
  if (supplier) {
    editingSupplier.value = supplier
    Object.assign(form, { ...supplier })
  } else {
    resetForm()
  }
  showModal.value = true
}

const resetForm = () => {
  form.reset()
  Object.assign(form, {
    supplier_type: 'Local',
    status: 1,
    address: '',
    origin: '',
    contact_person: '',
    contact_person_email: '',
    contact_person_mobile: '',
  })
  editingSupplier.value = null
  showModal.value = false
}

// -----------------------
// CRUD Functions
// -----------------------
useNotifier()

const submit = () => {
  if (!form.name.trim()) return

  if (editingSupplier.value) {
    form.put(route('supplier.update', editingSupplier.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        const idx = suppliers.value.findIndex(s => s.id === editingSupplier.value!.id)
        if (idx !== -1) {
          suppliers.value[idx] = { ...suppliers.value[idx], ...form }
        }
        resetForm()
      },
    })
  } else {
    form.post(route('supplier.store'), {
      preserveScroll: true,
      onSuccess: () => {
        router.get(route('supplier.index'), {}, {
          preserveState: true,
          onSuccess: (page) => {
            if (page.props.suppliers) suppliers.value = [...page.props.suppliers]
          }
        })
        resetForm()
      },
    })
  }
}

// -----------------------
// Dropdown & Status Toggle
// -----------------------
const toggleDropdown = (id: number) => {
  openDropdownId.value = openDropdownId.value === id ? null : id
}

// ✅ Corrected Status Toggle
const toggleStatus = (supplier: Supplier) => {
  const newStatus = supplier.status === 1 ? 0 : 1

  router.put(route('supplier.update', supplier.id), { status: newStatus }, {
    preserveScroll: true,
    onSuccess: () => {
      const idx = suppliers.value.findIndex(s => s.id === supplier.id)
      if (idx !== -1) suppliers.value[idx].status = newStatus
      openDropdownId.value = null
    },
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Suppliers" />

    <div class="px-4 py-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-4">
        <HeadingSmall title="Suppliers List" />
        <Button class="bg-chicken hover:bg-yellow-600 text-white" @click="openModal()">+ Add New</Button>
      </div>

      <!-- Table -->
      <table class="w-full border">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-2 border text-left">#</th>
            <th class="p-2 border text-left">Name</th>
            <th class="p-2 border text-left">Type</th>
            <th class="p-2 border text-left">Address</th>
            <th class="p-2 border text-left">Contact</th>
            <th class="p-2 border text-left">Status</th>
            <th class="p-2 border text-left">Created At</th>
            <th class="p-2 border text-left">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(supplier, index) in suppliers" :key="supplier.id">
            <td class="p-2 border">{{ index + 1 }}</td>
            <td class="p-2 border">{{ supplier.name }}</td>
            <td class="p-2 border">{{ supplier.supplier_type }}</td>
            <td class="p-2 border">{{ supplier.address || '-' }}</td>
            <td class="p-2 border">{{ supplier.contact_person || '-' }}</td>
            <td class="p-2 border">
              <span :class="supplier.status === 1 ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                {{ supplier.status === 1 ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="p-2 border">{{ supplier.created_at }}</td>
            <td class="p-2 border relative">
              <Button size="sm" class="bg-gray-500 hover:bg-gray-600 text-white dropdown-button" @click.stop="toggleDropdown(supplier.id)">
                Actions ▼
              </Button>
              <div v-if="openDropdownId === supplier.id" class="absolute mt-1 w-40 bg-white border rounded shadow-md z-10 dropdown" @click.stop>
                <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="openModal(supplier)">✏ Edit</button>
                <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="toggleStatus(supplier)">
                  {{ supplier.status === 1 ? 'Inactive' : 'Activate' }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Draggable Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex justify-center pt-6" @click.self="resetForm">
      <div ref="modalRef" class="bg-white rounded-lg border border-gray-300 shadow-lg w-full max-w-2xl" style="top: 100px; position: absolute;">
        <div class="flex items-center justify-between p-4 border-b border-gray-200 cursor-move" @mousedown="startDrag">
          <h3 class="text-xl font-semibold text-gray-900">
            {{ editingSupplier ? 'Edit Supplier' : 'Add New Supplier' }}
          </h3>
          <button type="button" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex justify-center items-center" @click="resetForm">✕</button>
        </div>

        <div class="p-4 space-y-4">
          <div>
            <Label for="name" class="mb-2">Supplier Name</Label>
            <Input v-model="form.name" id="name" />
          </div>
          <div>
            <Label for="supplier_type" class="mb-2">Supplier Type</Label>
            <select v-model="form.supplier_type" id="supplier_type" class="w-full border rounded p-2">
              <option value="Local">Local</option>
              <option value="Foreign">Foreign</option>
            </select>
          </div>
          <div>
            <Label for="address" class="mb-2">Address</Label>
            <Input v-model="form.address" id="address" />
          </div>
          <div>
            <Label for="origin" class="mb-2">Origin</Label>
            <Input v-model="form.origin" id="origin" />
          </div>
          <div>
            <Label for="contact_person" class="mb-2">Contact Person</Label>
            <Input v-model="form.contact_person" id="contact_person" />
          </div>
          <div>
            <Label for="contact_person_email" class="mb-2">Email</Label>
            <Input v-model="form.contact_person_email" id="contact_person_email" />
          </div>
          <div>
            <Label for="contact_person_mobile" class="mb-2">Mobile</Label>
            <Input v-model="form.contact_person_mobile" id="contact_person_mobile" />
          </div>
          <div>
            <Label for="status" class="mb-2">Status</Label>
            <select v-model="form.status" id="status" class="w-full border rounded p-2">
              <option :value="1">Active</option>
              <option :value="0">Inactive</option>
            </select>
          </div>
        </div>

        <div class="flex justify-end p-4 border-t border-gray-200">
          <Button class="bg-gray-300 text-black mr-2" @click="resetForm">Cancel</Button>
          <Button class="bg-chicken text-white" @click="submit">{{ editingSupplier ? 'Update' : 'Save' }}</Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
