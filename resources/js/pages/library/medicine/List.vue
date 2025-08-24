<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'
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

// Medicine interface
interface Medicine {
  id: number
  name: string
  status: number
  created_at: string
}

// Props
const props = defineProps<{
  medicines: Medicine[]
  filters: { search?: string; per_page?: number; page?: number }
}>()

useListFilters({
  routeName: '/medicine',
  filters: props.filters,
})

const { can } = usePermissions()
const medicines = ref<Medicine[]>([...props.medicines])

// Modal state
const showModal = ref(false)
const editingMedicine = ref<Medicine | null>(null)

// Form
const form = useForm({
  name: '',
  status: 1,
})

// Draggable modal
const modalRef = ref<HTMLElement | null>(null)
let offsetX = 0,
  offsetY = 0,
  isDragging = false

const startDrag = (event: MouseEvent) => {
  if (!modalRef.value) return
  isDragging = true
  const rect = modalRef.value.getBoundingClientRect()
  offsetX = event.clientX - rect.left
  offsetY = event.clientY - rect.top
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
}

// Dropdown state
const openDropdownId = ref<number | null>(null)
const toggleDropdown = (id: number) => {
  openDropdownId.value = openDropdownId.value === id ? null : id
}

// Click outside dropdown to close
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement
  if (
    !target.closest('.dropdown-menu') &&
    !target.closest('.actions-button')
  ) {
    openDropdownId.value = null
  }
}

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

// Open modal
const openModal = (medicine: Medicine | null = null) => {
  if (medicine) {
    editingMedicine.value = medicine
    form.name = medicine.name
    form.status = medicine.status
  } else {
    editingMedicine.value = null
    form.reset()
    form.status = 1
  }
  showModal.value = true
}

// Reset form
const resetForm = () => {
  form.reset()
  form.status = 1
  editingMedicine.value = null
  showModal.value = false
}

useNotifier()

// Submit (Create/Update)
const submit = () => {
  if (!form.name.trim()) return

  if (editingMedicine.value) {
    form.put(route('medicine.update', editingMedicine.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        const i = medicines.value.findIndex(m => m.id === editingMedicine.value!.id)
        if (i !== -1) {
          medicines.value[i] = {
            ...medicines.value[i],
            name: form.name,
            status: form.status,
          }
        }
        resetForm()
      },
    })
  } else {
    form.post(route('medicine.store'), {
      preserveScroll: true,
      onSuccess: (page) => {
        if ((page as any).props?.medicines) {
          medicines.value = (page as any).props.medicines
        } else {
          medicines.value.unshift({
            id: Date.now(),
            name: form.name,
            status: form.status,
            created_at: new Date().toISOString(),
          })
        }
        resetForm()
      },
    })
  }
}

// Toggle status
const toggleStatus = (medicine: Medicine) => {
  const newStatus = medicine.status === 1 ? 0 : 1

  router.put(
    route('medicine.update', medicine.id),
    { name: medicine.name, status: newStatus },
    {
      preserveScroll: true,
      onSuccess: () => {
        const i = medicines.value.findIndex(m => m.id === medicine.id)
        if (i !== -1) medicines.value[i].status = newStatus
      },
      onError: () => {
        Swal.fire('Error!', 'Could not update status.', 'error')
      },
      onFinish: () => {
        openDropdownId.value = null
      },
    }
  )
}

// Breadcrumbs
const breadcrumbs = [
  { title: 'Library', href: '/library' },
  { title: 'Medicine', href: '/library/medicine' },
]
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Medicines" />

    <!-- Filters -->
    <FilterControls :filters="props.filters" routeName="/medicine" />

    <div class="px-4 py-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-4">
        <HeadingSmall title="Medicines List" />
        <Button
          v-if="can('medicine.create')"
          class="bg-chicken hover:bg-yellow-600 text-white"
          @click="openModal()"
        >
          + Add New
        </Button>
      </div>

      <!-- Table -->
      <table class="w-full border">
        <thead>
          <tr class="bg-gray-100">
            <th class="p-2 border text-left">#</th>
            <th class="p-2 border text-left">Name</th>
            <th class="p-2 border text-left">Status</th>
            <th class="p-2 border text-left">Created At</th>
            <th class="p-2 border text-left">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(medicine, index) in medicines" :key="medicine.id">
            <td class="p-2 border">{{ index + 1 }}</td>
            <td class="p-2 border">{{ medicine.name }}</td>
            <td class="p-2 border">
              <span
                :class="medicine.status === 1 ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'"
              >
                {{ medicine.status === 1 ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="p-2 border">{{ medicine.created_at }}</td>
            <td class="p-2 border relative">
              <Button
                size="sm"
                class="actions-button bg-gray-500 hover:bg-gray-600 text-white"
                @click="toggleDropdown(medicine.id)"
              >
                Actions ▼
              </Button>

              <!-- Dropdown -->
              <div
                v-if="openDropdownId === medicine.id"
                class="dropdown-menu absolute mt-1 w-40 bg-white border rounded shadow-md z-10"
                @click.stop
              >
                <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="openModal(medicine)">
                  ✏ Edit
                </button>

                <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="toggleStatus(medicine)">
                  {{ medicine.status === 1 ? 'Inactive' : 'Activate' }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Draggable Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 z-50 flex justify-center pt-6 bg-black/30"
      @click.self="resetForm"
    >
      <div
        ref="modalRef"
        class="bg-white rounded-lg border border-gray-300 shadow-lg w-full max-w-2xl"
        style="top: 100px; position: absolute"
      >
        <div
          class="flex items-center justify-between p-4 border-b border-gray-200 cursor-move"
          @mousedown="startDrag"
        >
          <h3 class="text-xl font-semibold text-gray-900">
            {{ editingMedicine ? 'Edit Medicine' : 'Add New Medicine' }}
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
            <Label for="name" class="mb-2">Medicine Name</Label>
            <Input v-model="form.name" id="name" />
            <span v-if="form.errors.name" class="text-red-600 text-sm">{{ form.errors.name }}</span>
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
          <Button class="bg-chicken text-white" @click="submit">
            {{ editingMedicine ? 'Update' : 'Save' }}
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
