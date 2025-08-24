<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import AppLayout from '@/layouts/AppLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

interface Supplier {
  id: number
  name: string
  supplier_type: 'local' | 'foreign'
  address: string | null
  origin: string | null
  contact_person: string | null
  contact_person_email: string | null
  contact_person_mobile: string | null
  status: number
  created_at: string
}

const props = defineProps<{ suppliers: Supplier[] }>()
const suppliers = ref<Supplier[]>([...props.suppliers])

// Modal
const showModal = ref(false)
const editingSupplier = ref<Supplier | null>(null)

const form = useForm({
  name: '',
  supplier_type: 'local',
  address: '',
  origin: '',
  contact_person: '',
  contact_person_email: '',
  contact_person_mobile: '',
  status: 1,
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
    Object.assign(form, supplier)
  } else {
    editingSupplier.value = null
    form.reset()
    form.status = 1
  }
  showModal.value = true
}

const resetForm = () => {
  form.reset()
  form.status = 1
  editingSupplier.value = null
  showModal.value = false
}

// SweetAlert helper
const alert = async (title: string, text: string, icon: 'success' | 'error') => {
  await new Promise(resolve => setTimeout(resolve, 0))
  await Swal.fire(title, text, icon)
}

// Submit (Create/Update)
const submit = async () => {
  if (!form.name.trim()) {
    await alert('Validation', 'Supplier name is required.', 'error')
    return
  }

  const data = form.data()
  const supplierType = data.supplier_type as 'local' | 'foreign'

  try {
    if (editingSupplier.value) {
      // Update
      await form.put(route('supplier.update', editingSupplier.value.id), { preserveScroll: true })

      const i = suppliers.value.findIndex(s => s.id === editingSupplier.value!.id)
      if (i !== -1) {
        suppliers.value[i] = {
          ...suppliers.value[i],
          name: data.name,
          supplier_type: supplierType,
          address: data.address || null,
          origin: data.origin || null,
          contact_person: data.contact_person || null,
          contact_person_email: data.contact_person_email || null,
          contact_person_mobile: data.contact_person_mobile || null,
          status: data.status,
        }
      }

      resetForm()
      await alert('Success', 'Supplier updated successfully.', 'success')
    } else {
      // Create
      const newSupplier: Supplier = {
        id: Date.now(),
        name: data.name,
        supplier_type: supplierType,
        address: data.address || null,
        origin: data.origin || null,
        contact_person: data.contact_person || null,
        contact_person_email: data.contact_person_email || null,
        contact_person_mobile: data.contact_person_mobile || null,
        status: data.status,
        created_at: new Date().toISOString(),
      }

      await form.post(route('supplier.store'), { preserveScroll: true })
      suppliers.value.unshift(newSupplier)
      resetForm()
      await alert('Success', 'Supplier added successfully.', 'success')
    }
  } catch (error) {
    await alert('Error', 'Something went wrong. Please try again.', 'error')
  }
}

// Dropdown & status
const openDropdownId = ref<number | null>(null)
const toggleDropdown = (id: number) => {
  openDropdownId.value = openDropdownId.value === id ? null : id
}

// Close dropdown when clicking outside
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement
  const dropdowns = document.querySelectorAll('.dropdown, .dropdown-button')
  for (let i = 0; i < dropdowns.length; i++) {
    if (dropdowns[i].contains(target)) return
  }
  openDropdownId.value = null
}

onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))

// Toggle status
const toggleStatus = async (supplier: Supplier) => {
  const newStatus = supplier.status === 1 ? 0 : 1
  try {
    await router.put(route('supplier.update', supplier.id), { ...supplier, status: newStatus }, { preserveScroll: true })

    const i = suppliers.value.findIndex(s => s.id === supplier.id)
    if (i !== -1) suppliers.value[i].status = newStatus

    openDropdownId.value = null
    await alert('Success', `Supplier ${newStatus === 1 ? 'activated' : 'deactivated'} successfully.`, 'success')
  } catch (error) {
    await alert('Error', 'Could not update status.', 'error')
  }
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
        <Button class="bg-chicken hover:bg-yellow-600 text-white dropdown-button" @click="openModal()">
          + Add New
        </Button>
      </div>

      <table class="w-full border">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-2 border text-left">#</th>
            <th class="p-2 border text-left">Name</th>
            <th class="p-2 border text-left">Supplier Type</th>
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
            <td class="p-2 border">{{ supplier.address }}</td>
            <td class="p-2 border">{{ supplier.contact_person ?? '-' }}</td>
            <td class="p-2 border">
              <span :class="supplier.status === 1 ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                {{ supplier.status === 1 ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="p-2 border">{{ supplier.created_at }}</td>
            <td class="p-2 border relative">
              <Button size="sm" class="relative bg-gray-500 hover:bg-gray-600 text-white dropdown-button" @click.stop="toggleDropdown(supplier.id)">
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
      <div ref="modalRef" class="bg-white rounded-lg border border-gray-300 shadow-lg w-full max-w-2xl" style="top: 100px; position: absolute; margin:0;">
        <div class="flex items-center justify-between p-4 border-b border-gray-200 cursor-move" @mousedown="startDrag">
          <h3 class="text-xl font-semibold text-gray-900">{{ editingSupplier ? 'Edit Supplier' : 'Add New Supplier' }}</h3>
          <button type="button" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg w-8 h-8 flex justify-center items-center" @click="resetForm">✕</button>
        </div>

        <div class="p-4 space-y-4">
          <div><Label for="name" class="mb-2">Name</Label><Input v-model="form.name" id="name" /></div>
          <div>
            <Label for="supplier_type" class="mb-2">Supplier Type</Label>
            <select v-model="form.supplier_type" id="supplier_type" class="w-full border rounded p-2">
              <option value="local">Local</option>
              <option value="foreign">Foreign</option>
            </select>
          </div>
          <div><Label for="address" class="mb-2">Address</Label><Input v-model="form.address" id="address" /></div>
          <div><Label for="origin" class="mb-2">Origin</Label><Input v-model="form.origin" id="origin" /></div>
          <div><Label for="contact_person" class="mb-2">Contact Person</Label><Input v-model="form.contact_person" id="contact_person" /></div>
          <div><Label for="contact_person_email" class="mb-2">Email</Label><Input v-model="form.contact_person_email" id="contact_person_email" /></div>
          <div><Label for="contact_person_mobile" class="mb-2">Mobile</Label><Input v-model="form.contact_person_mobile" id="contact_person_mobile" /></div>
          <div>
            <Label for="status" class="mb-2">Status</Label>
            <select v-model="form.status" id="status" class="w-full border rounded p-2 mb-2">
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
