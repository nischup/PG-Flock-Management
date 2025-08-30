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

interface ChickType {
  id: number
  name: string
  status: number
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

  // Prevent drag if clicking a button
  const target = event.target as HTMLElement
  if (target.closest('button')) return

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

// Open modal
const openModal = (chickType: ChickType | null = null) => {
  if (chickType) {
    editingChickType.value = chickType
    Object.assign(form, { ...chickType })
  } else {
    resetForm()
  }
  showModal.value = true
}

// Reset form
const resetForm = () => {
  form.reset()
  Object.assign(form, { status: 1 })
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
        if (i !== -1) chickTypes.value[i] = { ...chickTypes.value[i], ...form }
        resetForm()
      },
    })
  } else {
    form.post(route('chick-type.store'), {
      preserveScroll: true,
      onSuccess: (page) => {
        if ((page as any).props?.chickTypes) {
          chickTypes.value = (page as any).props.chickTypes
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

// Lifecycle
onMounted(() => {
  document.addEventListener('click', handleClickOutside)
  document.addEventListener('mousemove', onDrag)
  document.addEventListener('mouseup', stopDrag)
})
onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
  document.removeEventListener('mousemove', onDrag)
  document.removeEventListener('mouseup', stopDrag)
})

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
        openDropdownId.value = null
      },
      onError: () => {
        Swal.fire('Error!', 'Could not update status.', 'error')
      },
    }
  )
}

// Breadcrumbs
const breadcrumbs = [
  { title: 'Master Setup', href: '/master-setup' },
  { title: 'Chick Type', href: '/master-setup/chick-type' },
]
</script>

<template>
  <AppLayout>
    <template #breadcrumbs>
      <nav class="flex flex-wrap items-center gap-2 text-sm sm:text-base text-gray-600">
        <template v-for="(crumb, index) in breadcrumbs" :key="index">
          <a :href="crumb.href" class="hover:underline truncate max-w-[120px] sm:max-w-[200px] block">
            {{ crumb.title }}
          </a>
          <span v-if="index < breadcrumbs.length - 1">/</span>
        </template>
      </nav>
    </template>

    <Head title="Chick Types" />

    <div class="px-2 sm:px-4 py-6">
      <!-- Header -->
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between mb-4 gap-2">
        <HeadingSmall title="Chick Types List" />
        <Button
          v-if="can('chick-type.create')"
          class="bg-chicken hover:bg-yellow-600 text-white w-full sm:w-auto"
          @click="openModal()"
        >
          + Add New
        </Button>
      </div>

      <!-- Table wrapper -->
      <div class="overflow-x-auto md:overflow-x-visible border-t border-gray-300 w-full">
        <table class="min-w-[700px] w-full">
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
                <span :class="chickType.status === 1 ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                  {{ chickType.status === 1 ? 'Active' : 'Inactive' }}
                </span>
              </td>
              <td class="p-2 border">{{ chickType.created_at }}</td>
              <td class="p-2 border relative">
                <Button
                  size="sm"
                  class="bg-gray-500 hover:bg-gray-600 text-white dropdown-button"
                  @click.stop="toggleDropdown(chickType.id)"
                >
                  Actions ▼
                </Button>

                <div
                  v-if="openDropdownId === chickType.id"
                  class="absolute mt-1 w-40 bg-white border rounded shadow-md z-10 dropdown"
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
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Draggable & Responsive Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-start justify-center pt-6 px-2 sm:px-4" @click.self="resetForm">
      <div
        ref="modalRef"
        class="bg-white rounded-lg border border-gray-300 shadow-lg w-full max-w-full sm:max-w-2xl md:max-w-4xl max-h-[90vh] overflow-y-auto"
        style="top: 100px; position: absolute;"
      >
        <!-- Header -->
        <div class="flex items-center justify-between p-4 border-b border-gray-200 cursor-move" @mousedown="startDrag">
          <h3 class="text-lg sm:text-xl font-semibold text-gray-900">
            {{ editingChickType ? 'Edit Chick Type' : 'Add New Chick Type' }}
          </h3>
          <button type="button" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex justify-center items-center" @click="resetForm">
            ✕
          </button>
        </div>

        <!-- Form -->
        <div class="p-4 sm:p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
        </div>

        <!-- Footer -->
        <div class="flex flex-col sm:flex-row justify-end p-4 border-t border-gray-200 gap-2 sm:gap-2">
          <Button class="bg-gray-300 text-black w-full sm:w-auto" @click="resetForm">Cancel</Button>
          <Button class="bg-chicken text-white w-full sm:w-auto" @click="submit">
            {{ editingChickType ? 'Update' : 'Save' }}
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
