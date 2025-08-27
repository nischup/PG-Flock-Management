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

interface BreedType {
  id: number
  name: string
  status: number
  created_at: string
}

const props = defineProps<{
  breedTypes: BreedType[]
  filters: { search?: string; per_page?: number; page?: number }
}>()

useListFilters({
  routeName: '/breed-type',
  filters: props.filters,
})

const { can } = usePermissions()
const breedTypes = ref<BreedType[]>([...props.breedTypes])

// Modal state
const showModal = ref(false)
const editingBreedType = ref<BreedType | null>(null)

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
const openModal = (breedType: BreedType | null = null) => {
  if (breedType) {
    editingBreedType.value = breedType
    form.name = breedType.name
    form.status = breedType.status
  } else {
    editingBreedType.value = null
    form.reset()
    form.status = 1
  }
  showModal.value = true
}

// Reset form
const resetForm = () => {
  form.reset()
  form.status = 1
  editingBreedType.value = null
  showModal.value = false
}

useNotifier()

// Submit (Create/Update)
const submit = () => {
  if (!form.name.trim()) return

  if (editingBreedType.value) {
    form.put(route('breed-type.update', editingBreedType.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        const i = breedTypes.value.findIndex(c => c.id === editingBreedType.value!.id)
        if (i !== -1) {
          breedTypes.value[i] = { ...breedTypes.value[i], name: form.name, status: form.status }
        }
        resetForm()
      },
    })
  } else {
    form.post(route('breed-type.store'), {
      preserveScroll: true,
      onSuccess: (page) => {
        if ((page as any).props?.breedTypes) {
          breedTypes.value = (page as any).props.breedTypes
        } else {
          breedTypes.value.unshift({
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

// Close dropdown when clicking outside
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement
  const dropdowns = document.querySelectorAll('.dropdown, .dropdown-button')
  for (let i = 0; i < dropdowns.length; i++) {
    if (dropdowns[i].contains(target)) return
  }
  openDropdownId.value = null
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})
onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

// Toggle status
const toggleStatus = (breedType: BreedType) => {
  const newStatus = breedType.status === 1 ? 0 : 1
  router.put(
    route('breed-type.update', breedType.id),
    { name: breedType.name, status: newStatus },
    {
      preserveScroll: true,
      onSuccess: () => {
        const i = breedTypes.value.findIndex(c => c.id === breedType.id)
        if (i !== -1) breedTypes.value[i].status = newStatus
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
  { title: 'Master Setup', href: '/master-setup' },
  { title: 'Breed Type', href: '/master-setup/breed-type' },
]
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Breed Types" />

    <div class="px-4 py-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-4">
        <HeadingSmall title="Breed Types List" />
        <Button
          v-if="can('breed-type.create')"
          class="bg-chicken hover:bg-yellow-600 text-white dropdown-button"
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
          <tr v-for="(breedType, index) in breedTypes" :key="breedType.id">
            <td class="p-2 border">{{ index + 1 }}</td>
            <td class="p-2 border">{{ breedType.name }}</td>
            <td class="p-2 border">
              <span
                :class="breedType.status === 1 ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'"
              >
                {{ breedType.status === 1 ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="p-2 border">{{ breedType.created_at }}</td>
            <td class="p-2 border relative">
              <Button
                size="sm"
                class="bg-gray-500 hover:bg-gray-600 text-white dropdown-button"
                @click.stop="toggleDropdown(breedType.id)"
              >
                Actions ▼
              </Button>

              <div
                v-if="openDropdownId === breedType.id"
                class="absolute mt-1 w-40 bg-white border rounded shadow-md z-10 dropdown"
                @click.stop
              >
                <button
                  v-if="can('breed-type.edit')"
                  class="w-full text-left px-4 py-2 hover:bg-gray-100"
                  @click="openModal(breedType)"
                >
                  ✏ Edit
                </button>
                <button
                  v-if="can('breed-type.edit')"
                  class="w-full text-left px-4 py-2 hover:bg-gray-100"
                  @click="toggleStatus(breedType)"
                >
                  {{ breedType.status === 1 ? 'Inactive' : 'Activate' }}
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
            {{ editingBreedType ? 'Edit Breed Type' : 'Add New Breed Type' }}
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
            <Label for="name" class="mb-2">Breed Type Name</Label>
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
            {{ editingBreedType ? 'Update' : 'Save' }}
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
