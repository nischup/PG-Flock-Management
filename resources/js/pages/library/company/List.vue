<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useNotifier } from '@/composables/useNotifier'

interface Company {
  id: number
  name: string
  location?: string
  status: number
  created_at: string
}

// Props & state
const props = defineProps<{ companies: Company[] }>()
const companies = ref<Company[]>([...props.companies])
const showModal = ref(false)
const editingCompany = ref<Company | null>(null)
const form = useForm({ name: '', location: '', status: 1 })
const openDropdownId = ref<number | null>(null)
const modalRef = ref<HTMLElement | null>(null)

// Draggable modal state
let offsetX = 0
let offsetY = 0
let isDragging = false

// Breadcrumbs
const breadcrumbs = [
  { title: 'Master Setup', href: '/master-setup' },
  { title: 'Company', href: '/master-setup/company' },
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
const openModal = (company: Company | null = null) => {
  if (company) {
    editingCompany.value = company
    Object.assign(form, { name: company.name, location: company.location || '', status: company.status })
  } else {
    resetForm()
  }
  showModal.value = true
}

const resetForm = () => {
  form.reset()
  Object.assign(form, { status: 1, location: '' })
  editingCompany.value = null
  showModal.value = false
}

// -----------------------
// CRUD Functions
// -----------------------
useNotifier()

const submit = () => {
  if (!form.name.trim()) return

  const payload = { name: form.name, location: form.location, status: form.status }

  if (editingCompany.value) {
    form.put(route('company.update', editingCompany.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        const idx = companies.value.findIndex(c => c.id === editingCompany.value!.id)
        if (idx !== -1) companies.value[idx] = { ...companies.value[idx], ...payload }
        resetForm()
      },
    })
  } else {
    form.post(route('company.store'), {
      preserveScroll: true,
      onSuccess: () => {
        companies.value.unshift({ id: Date.now(), ...payload, created_at: new Date().toISOString().split('T')[0] })
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

const toggleStatus = (company: Company) => {
  const newStatus = company.status === 1 ? 0 : 1

  router.put(
    route('company.update', company.id),
    {
      name: company.name,
      location: company.location || '',
      status: newStatus,
    },
    {
      preserveScroll: true,
      onSuccess: () => {
        const idx = companies.value.findIndex(c => c.id === company.id)
        if (idx !== -1) companies.value[idx].status = newStatus
      },
      onFinish: () => (openDropdownId.value = null),
    }
  )
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Companies" />

    <div class="px-4 py-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-4">
        <HeadingSmall title="Companies List" />
        <Button class="bg-chicken hover:bg-yellow-600 text-white" @click="openModal()">+ Add New</Button>
      </div>

      <!-- Table -->
      <table class="w-full border">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-2 border text-left">#</th>
            <th class="p-2 border text-left">Name</th>
            <th class="p-2 border text-left">Location</th>
            <th class="p-2 border text-left">Status</th>
            <th class="p-2 border text-left">Created At</th>
            <th class="p-2 border text-left">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(company, index) in companies" :key="company.id">
            <td class="p-2 border">{{ index + 1 }}</td>
            <td class="p-2 border">{{ company.name }}</td>
            <td class="p-2 border">{{ company.location || '-' }}</td>
            <td class="p-2 border">
              <span :class="company.status === 1 ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                {{ company.status === 1 ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="p-2 border">{{ company.created_at }}</td>
            <td class="p-2 border relative">
              <Button
                size="sm"
                class="bg-gray-500 hover:bg-gray-600 text-white dropdown-button"
                @click.stop="toggleDropdown(company.id)"
              >
                Actions ▼
              </Button>

              <div
                v-if="openDropdownId === company.id"
                class="absolute mt-1 w-40 bg-white border rounded shadow-md z-10 dropdown"
                @click.stop
              >
                <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="openModal(company)">✏ Edit</button>
                <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="toggleStatus(company)">
                  {{ company.status === 1 ? 'Deactivate' : 'Activate' }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Draggable Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex justify-center pt-6" @click.self="resetForm">
      <div
        ref="modalRef"
        class="bg-white rounded-lg border border-gray-300 shadow-lg w-full max-w-2xl"
        style="top: 100px; position: absolute;"
      >
        <div class="flex items-center justify-between p-4 border-b border-gray-200 cursor-move" @mousedown="startDrag">
          <h3 class="text-xl font-semibold text-gray-900">
            {{ editingCompany ? 'Edit Company' : 'Add New Company' }}
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
            <Label for="name" class="mb-2">Company Name</Label>
            <Input v-model="form.name" id="name" />
            <span v-if="form.errors.name" class="text-red-600 text-sm">{{ form.errors.name }}</span>
          </div>

          <div>
            <Label for="location" class="mb-2">Location</Label>
            <Input v-model="form.location" id="location" />
            <span v-if="form.errors.location" class="text-red-600 text-sm">{{ form.errors.location }}</span>
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
          <Button class="bg-chicken text-white" @click="submit">{{ editingCompany ? 'Update' : 'Save' }}</Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
