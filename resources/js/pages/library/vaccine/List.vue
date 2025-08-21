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
import { usePermissions } from '@/composables/usePermissions'
import { useListFilters } from '@/composables/useListFilters'

interface Vaccine {
  id: number
  name: string
  status: number // 1 = Active, 0 = Inactive
  created_at: string
}

const props = defineProps<{
  vaccines: Vaccine[]
  filters: { search?: string; per_page?: number; page?: number }
}>()

useListFilters({
  routeName: '/vaccine',
  filters: props.filters,
})

const { can } = usePermissions()
const vaccines = ref<Vaccine[]>([...props.vaccines])

// Modal state
const showModal = ref(false)
const editingVaccine = ref<Vaccine | null>(null)

// Form
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
const openModal = (vaccine: Vaccine | null = null) => {
  if (vaccine) {
    editingVaccine.value = vaccine
    form.name = vaccine.name
    form.status = vaccine.status
  } else {
    editingVaccine.value = null
    form.reset()
    form.status = 1
  }
  showModal.value = true
}

// Reset form
const resetForm = () => {
  form.reset()
  form.status = 1
  editingVaccine.value = null
  showModal.value = false
}

useNotifier()

// Submit (Create/Update)
const submit = () => {
  if (!form.name.trim()) return

  if (editingVaccine.value) {
    form.put(route('vaccine.update', editingVaccine.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        const i = vaccines.value.findIndex(v => v.id === editingVaccine.value!.id)
        if (i !== -1) {
          vaccines.value[i] = { ...vaccines.value[i], name: form.name, status: form.status }
        }
        resetForm()
      },
    })
  } else {
    form.post(route('vaccine.store'), {
      preserveScroll: true,
      onSuccess: (page) => {
        if ((page as any).props?.vaccines) vaccines.value = (page as any).props.vaccines
        else {
          vaccines.value.unshift({
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

// Close dropdown on outside click
const closeDropdown = (event: MouseEvent) => {
  if (!(event.target as HTMLElement).closest('.relative.inline-block')) {
    openDropdownId.value = null
  }
}

onMounted(() => document.addEventListener('click', closeDropdown))
onBeforeUnmount(() => document.removeEventListener('click', closeDropdown))

// Toggle status
const toggleStatus = (vaccine: Vaccine) => {
  const newStatus = vaccine.status === 1 ? 0 : 1

  router.put(
    route('vaccine.update', vaccine.id),
    { name: vaccine.name, status: newStatus },
    {
      preserveScroll: true,
      onSuccess: () => {
        const i = vaccines.value.findIndex(v => v.id === vaccine.id)
        if (i !== -1) vaccines.value[i].status = newStatus
      },
      onError: () => Swal.fire('Error!', 'Could not update status.', 'error'),
      onFinish: () => openDropdownId.value = null,
    }
  )
}

// Breadcrumbs
const breadcrumbs = [
  { title: 'Master Setup', href: '/master-setup' },
  { title: 'Vaccine', href: '/master-setup/vaccine' },
]
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Vaccines" />

    <div class="px-4 py-6">
      <div class="flex items-center justify-between mb-4">
        <HeadingSmall title="Vaccine List" />
        <Button
          v-if="can('vaccine.create')"
          class="bg-chicken hover:bg-yellow-600 text-white"
          @click="openModal()"
        >
          + Add New
        </Button>
      </div>

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
          <tr v-for="(vaccine, index) in vaccines" :key="vaccine.id">
            <td class="p-2 border">{{ index + 1 }}</td>
            <td class="p-2 border">{{ vaccine.name }}</td>
            <td class="p-2 border">
              <span :class="vaccine.status === 1 ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                {{ vaccine.status === 1 ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="p-2 border">{{ vaccine.created_at }}</td>
            <td class="p-2 border">
              <div class="relative inline-block text-left">
                <Button size="sm" class="bg-gray-500 hover:bg-gray-600 text-white" @click.stop="toggleDropdown(vaccine.id)">
                  Actions ▼
                </Button>

                <div v-if="openDropdownId === vaccine.id" class="absolute right-0 mt-1 w-40 bg-white border rounded shadow-md z-10" @click.stop>
                  <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="openModal(vaccine)">✏ Edit</button>
                  <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="toggleStatus(vaccine)">
                    {{ vaccine.status === 1 ? 'Inactive' : 'Activate' }}
                  </button>
                </div>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Draggable Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex justify-center pt-6 bg-black/30" @click.self="resetForm">
      <div ref="modalRef" class="bg-white rounded-lg border border-gray-300 shadow-lg w-full max-w-2xl" style="top: 100px; position: absolute;">
        <div class="flex items-center justify-between p-4 border-b border-gray-200 cursor-move" @mousedown="startDrag">
          <h3 class="text-xl font-semibold text-gray-900">{{ editingVaccine ? 'Edit Vaccine' : 'Add New Vaccine' }}</h3>
          <button type="button" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex justify-center items-center" @click="resetForm">
            ✕
          </button>
        </div>

        <div class="p-4 space-y-4">
          <div>
            <Label for="name" class="mb-2">Vaccine Name</Label>
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
          <Button class="bg-chicken text-white" @click="submit">{{ editingVaccine ? 'Update' : 'Save' }}</Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
