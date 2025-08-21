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

interface ChickType {
  id: number
  name: string
  status: number // 1 = Active, 0 = Inactive
  created_at: string
}

const props = defineProps<{
  chickTypes: ChickType[]
  filters: { search?: string; per_page?: number; page?: number }
}>()

useListFilters({
  routeName: '/chick-type',
  filters: props.filters,
})

const { can } = usePermissions()
const chickTypes = ref<ChickType[]>([...props.chickTypes])

// Modal state
const showModal = ref(false)
const editingChickType = ref<ChickType | null>(null)

// Modal form
const form = useForm({
  name: '',
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
const openModal = (chickType: ChickType | null = null) => {
  if (chickType) {
    editingChickType.value = chickType
    form.name = chickType.name
    form.status = chickType.status
  } else {
    editingChickType.value = null
    form.reset()
    form.status = 1
  }
  showModal.value = true
}

// Reset form
const resetForm = () => {
  form.reset()
  form.status = 1
  editingChickType.value = null
  showModal.value = false
}

useNotifier()

// Submit (Create/Update)
const submit = () => {
  if (!form.name.trim()) return

  if (editingChickType.value) {
    form.put(route('chick-type.update', editingChickType.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        const i = chickTypes.value.findIndex(c => c.id === editingChickType.value!.id)
        if (i !== -1) {
          chickTypes.value[i] = { ...chickTypes.value[i], name: form.name, status: form.status }
        }
        resetForm()
      },
    })
  } else {
    form.post(route('chick-type.store'), {
      preserveScroll: true,
      onSuccess: (page) => {
        if ((page as any).props?.chickTypes) {
          chickTypes.value = (page as any).props.chickTypes
        } else {
          chickTypes.value.unshift({
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

// Dropdown for actions
const openDropdownId = ref<number | null>(null)
const toggleDropdown = (id: number) => {
  openDropdownId.value = openDropdownId.value === id ? null : id
}

// Toggle status
const toggleStatus = (chickType: ChickType) => {
  const newStatus = chickType.status === 1 ? 0 : 1
  router.put(
    route('chick-type.update', chickType.id),
    { name: chickType.name, status: newStatus },
    {
      preserveScroll: true,
      onSuccess: () => {
        const i = chickTypes.value.findIndex(c => c.id === chickType.id)
        if (i !== -1) chickTypes.value[i].status = newStatus
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

// Delete chick type
const destroy = (chickType: ChickType) => {
  Swal.fire({
    title: 'Are you sure?',
    text: `Delete "${chickType.name}"? This action cannot be undone.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Yes, delete it!',
  }).then(result => {
    if (result.isConfirmed) {
      router.delete(route('chick-type.destroy', chickType.id), {
        preserveScroll: true,
        onSuccess: () => {
          chickTypes.value = chickTypes.value.filter(c => c.id !== chickType.id)
          openDropdownId.value = null
        },
      })
    }
  })
}

// Breadcrumbs
const breadcrumbs = [
  { title: 'Master Setup', href: '/master-setup' },
  { title: 'Chick Type', href: '/master-setup/chick-type' },
]
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Chick Types" />

    <!-- Filters -->
    <FilterControls :filters="props.filters" routeName="/chick-type" />

    <div class="px-4 py-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-4">
        <HeadingSmall title="Chick Types List" />
        <Button
          v-if="can('chick-type.create')"
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
          <tr v-for="(chickType, index) in chickTypes" :key="chickType.id">
            <td class="p-2 border">{{ index + 1 }}</td>
            <td class="p-2 border">{{ chickType.name }}</td>
            <td class="p-2 border">
              <span
                :class="chickType.status === 1 ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'"
              >
                {{ chickType.status === 1 ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="p-2 border">{{ chickType.created_at }}</td>
            <td class="p-2 border relative">
              <Button size="sm" class="bg-gray-500 hover:bg-gray-600 text-white" @click="toggleDropdown(chickType.id)">
                Actions ▼
              </Button>
              <!-- Dropdown -->
              <div
                v-if="openDropdownId === chickType.id"
                class="absolute right-0 mt-1 w-40 bg-white border rounded shadow-md z-10"
                @click.stop
              >
                <button
                  v-if="can('chick-type.edit')"
                  class="w-full text-left px-4 py-2 hover:bg-gray-100"
                  @click="openModal(chickType)"
                >
                  ✏ Edit
                </button>
                <button
                  v-if="can('chick-type.edit')"
                  class="w-full text-left px-4 py-2 hover:bg-gray-100"
                  @click="toggleStatus(chickType)"
                >
                  {{ chickType.status === 1 ? 'Inactive' : 'Activate' }}
                </button>
                <button
                  v-if="can('chick-type.delete')"
                  class="w-full text-left px-4 py-2 text-red-600 hover:bg-gray-100"
                  @click="destroy(chickType)"
                >
                  🗑 Delete
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
        <div class="flex items-center justify-between p-4 border-b border-gray-200 cursor-move" @mousedown="startDrag">
          <h3 class="text-xl font-semibold text-gray-900">
            {{ editingChickType ? 'Edit Chick Type' : 'Add New Chick Type' }}
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
            <Label for="name" class="mb-2">Chick Type Name</Label>
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
            {{ editingChickType ? 'Update' : 'Save' }}
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
