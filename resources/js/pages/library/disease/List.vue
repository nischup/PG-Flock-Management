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

// Interface
interface Disease {
  id: number
  name: string
  status: string // 'Active' | 'Inactive'
  created_at: string
}

const props = defineProps<{
  diseases: Disease[]
  filters: { search?: string; per_page?: number; page?: number }
}>()

useListFilters({
  routeName: '/disease',
  filters: props.filters,
})

const { can } = usePermissions()
const diseases = ref<Disease[]>([...props.diseases])

// Modal state
const showModal = ref(false)
const editingDisease = ref<Disease | null>(null)

// Form
const form = useForm({
  name: '',
  status: 'Active',
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
const openModal = (disease: Disease | null = null) => {
  if (disease) {
    editingDisease.value = disease
    form.name = disease.name
    form.status = disease.status
  } else {
    editingDisease.value = null
    form.reset()
    form.status = 'Active'
  }
  showModal.value = true
}

// Reset form
const resetForm = () => {
  form.reset()
  form.status = 'Active'
  editingDisease.value = null
  showModal.value = false
}
useNotifier()

// Submit
const submit = () => {
  if (!form.name.trim()) return

  if (editingDisease.value) {
    form.put(route('disease.update', editingDisease.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        const i = diseases.value.findIndex((d) => d.id === editingDisease.value!.id)
        if (i !== -1) {
          diseases.value[i] = {
            ...diseases.value[i],
            name: form.name,
            status: form.status,
          }
        }
        resetForm()
      },
    })
  } else {
    form.post(route('disease.store'), {
      preserveScroll: true,
      onSuccess: (page) => {
        if ((page as any).props?.diseases) {
          diseases.value = (page as any).props.diseases
        } else {
          diseases.value.unshift({
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

// Dropdown
const openDropdownId = ref<number | null>(null)
const toggleDropdown = (id: number) => {
  openDropdownId.value = openDropdownId.value === id ? null : id
}

// 🔑 Close dropdown when clicking outside
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
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

// Toggle status
const toggleStatus = (disease: Disease) => {
  const newStatus = disease.status === 'Active' ? 'Inactive' : 'Active'

  router.put(
    route('disease.update', disease.id),
    { name: disease.name, status: newStatus },
    {
      preserveScroll: true,
      onSuccess: () => {
        const i = diseases.value.findIndex((d) => d.id === disease.id)
        if (i !== -1) diseases.value[i].status = newStatus
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

const breadcrumbs = [
  { title: 'Library', href: '/library' },
  { title: 'Disease', href: '/library/disease' },
]
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Diseases" />

    <!-- Filters -->
    <FilterControls :filters="props.filters" routeName="/disease" />

    <div class="px-4 py-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-4">
        <HeadingSmall title="Diseases List" />
        <Button
          v-if="can('disease.create')"
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
          <tr v-for="(disease, index) in diseases" :key="disease.id">
            <td class="p-2 border">{{ index + 1 }}</td>
            <td class="p-2 border">{{ disease.name }}</td>
            <td class="p-2 border">
              <span
                :class="disease.status === 'Active'
                  ? 'text-green-600 font-semibold'
                  : 'text-red-600 font-semibold'"
              >
                {{ disease.status }}
              </span>
            </td>
            <td class="p-2 border">{{ disease.created_at }}</td>
            <td class="p-2 border relative">
              <Button
                size="sm"
                class="relative bg-gray-500 hover:bg-gray-600 text-white actions-button"
                @click.stop="toggleDropdown(disease.id)"
              >
                Actions ▼
              </Button>

              <!-- Dropdown -->
              <div
                v-if="openDropdownId === disease.id"
                class="absolute mt-1 w-40 bg-white border rounded shadow-md z-10 dropdown-menu"
                @click.stop
              >
                <button
                  class="w-full text-left px-4 py-2 hover:bg-gray-100"
                  @click="openModal(disease)"
                >
                  ✏ Edit
                </button>
                <button
                  class="w-full text-left px-4 py-2 hover:bg-gray-100"
                  @click="toggleStatus(disease)"
                >
                  {{ disease.status === 'Active' ? 'Inactive' : 'Activate' }}
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
      class="fixed inset-0 z-50 flex justify-center pt-6"
      @click.self="showModal = false"
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
            {{ editingDisease ? 'Edit Disease' : 'Add New Disease' }}
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
            <Label for="name" class="mb-2">Disease Name</Label>
            <Input v-model="form.name" id="name" />
            <span v-if="form.errors.name" class="text-red-600 text-sm">
              {{ form.errors.name }}
            </span>
          </div>

          <div>
            <Label for="status" class="mb-2">Status</Label>
            <select v-model="form.status" id="status" class="w-full border rounded p-2">
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
            </select>
          </div>
        </div>

        <div class="flex justify-end p-4 border-t border-gray-200">
          <Button class="bg-gray-300 text-black mr-2" @click="resetForm">Cancel</Button>
          <Button class="bg-chicken text-white" @click="submit">
            {{ editingDisease ? 'Update' : 'Save' }}
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
