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

interface Vaccine {
  id: number
  vaccine_type_id: number
  vaccine_type_name: string
  name: string
  status: number
  created_at: string
  applicator: string
  dose: string
  note: string
}

const props = defineProps<{
  vaccines: Vaccine[]
  vaccineTypes: Array<{ id: number; name: string }>
  filters: { search?: string; per_page?: number; page?: number }
}>()

useNotifier()
const { can } = usePermissions()

// Local state
const vaccines = ref<Vaccine[]>([...props.vaccines])

// Modal state
const showModal = ref(false)
const editingVaccine = ref<Vaccine | null>(null)

// Modal form
const form = useForm({
  vaccine_type_id: props.vaccineTypes.length ? props.vaccineTypes[0].id : 0,
  name: '',
  applicator: '',
  dose: '',
  note: '',
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
    form.vaccine_type_id = vaccine.vaccine_type_id
    form.name = vaccine.name
    form.applicator = vaccine.applicator
    form.dose = vaccine.dose
    form.note = vaccine.note
    form.status = vaccine.status
  } else {
    editingVaccine.value = null
    form.reset()
    form.vaccine_type_id = props.vaccineTypes.length ? props.vaccineTypes[0].id : 0
    form.status = 1
  }
  showModal.value = true
}

// Reset modal
const resetForm = () => {
  form.reset()
  form.vaccine_type_id = props.vaccineTypes.length ? props.vaccineTypes[0].id : 0
  form.status = 1
  editingVaccine.value = null
  showModal.value = false
}

// Submit (Create/Update)
const submit = () => {
  if (!form.name.trim()) return

  if (editingVaccine.value) {
    form.put(route('vaccine.update', editingVaccine.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        const i = vaccines.value.findIndex(v => v.id === editingVaccine.value!.id)
        if (i !== -1) {
          vaccines.value[i] = {
            ...vaccines.value[i],
            vaccine_type_id: form.vaccine_type_id,
            vaccine_type_name: props.vaccineTypes.find(vt => vt.id === form.vaccine_type_id)?.name || '',
            name: form.name,
            applicator: form.applicator,
            dose: form.dose,
            note: form.note,
            status: form.status,
          }
        }
        resetForm()
      },
      onError: () => Swal.fire('Error!', 'Could not update vaccine.', 'error'),
    })
  } else {
    form.post(route('vaccine.store'), {
      preserveScroll: true,
      onSuccess: (page) => {
        if ((page as any).props?.vaccines) {
          vaccines.value = (page as any).props.vaccines
        } else {
          vaccines.value.unshift({
            id: Date.now(),
            vaccine_type_id: form.vaccine_type_id,
            vaccine_type_name: props.vaccineTypes.find(vt => vt.id === form.vaccine_type_id)?.name || '',
            name: form.name,
            applicator: form.applicator,
            dose: form.dose,
            note: form.note,
            status: form.status,
            created_at: new Date().toISOString(),
          })
        }
        resetForm()
      },
      onError: () => Swal.fire('Error!', 'Could not create vaccine.', 'error'),
    })
  }
}

// Dropdown
const openDropdownId = ref<number | null>(null)
const toggleDropdown = (id: number) => {
  openDropdownId.value = openDropdownId.value === id ? null : id
}
const handleClickOutside = (event: MouseEvent) => {
  const target = event.target as HTMLElement
  if (!target.closest('.dropdown-menu') && !target.closest('.actions-button')) {
    openDropdownId.value = null
  }
}
onMounted(() => document.addEventListener('click', handleClickOutside))
onBeforeUnmount(() => document.removeEventListener('click', handleClickOutside))

// Toggle status
const toggleStatus = (vaccine: Vaccine) => {
  const newStatus = vaccine.status === 1 ? 0 : 1
  router.put(route('vaccine.update', vaccine.id), { ...vaccine, status: newStatus }, {
    preserveScroll: true,
    onSuccess: () => {
      const i = vaccines.value.findIndex(v => v.id === vaccine.id)
      if (i !== -1) vaccines.value[i].status = newStatus
    },
    onError: () => Swal.fire('Error!', 'Could not update status.', 'error'),
    onFinish: () => { openDropdownId.value = null },
  })
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
      <Button v-if="can('vaccine.create')" class="bg-chicken hover:bg-yellow-600 text-white" @click="openModal()">
        + Add New
      </Button>
    </div>

    <table class="w-full border">
      <thead>
        <tr class="bg-gray-100">
          <th class="p-2 border text-left">#</th>
          <th class="p-2 border text-left">Vaccine Type</th>
          <th class="p-2 border text-left">Name</th>
          <th class="p-2 border text-left">Applicator</th>
          <th class="p-2 border text-left">Dose</th>
          <th class="p-2 border text-left">Note</th>
          <th class="p-2 border text-left">Status</th>
          <th class="p-2 border text-left">Created At</th>
          <th class="p-2 border text-left">Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(vaccine, index) in vaccines" :key="vaccine.id">
          <td class="p-2 border">{{ index + 1 }}</td>
          <td class="p-2 border">{{ vaccine.vaccine_type_name }}</td>
          <td class="p-2 border">{{ vaccine.name }}</td>
          <td class="p-2 border">{{ vaccine.applicator }}</td>
          <td class="p-2 border">{{ vaccine.dose }}</td>
          <td class="p-2 border">{{ vaccine.note }}</td>
          <td class="p-2 border">
            <span :class="vaccine.status === 1 ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
              {{ vaccine.status === 1 ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td class="p-2 border">{{ vaccine.created_at }}</td>
          <td class="p-2 border relative">
            <Button
              size="sm"
              class="bg-gray-500 hover:bg-gray-600 text-white actions-button"
              @click.stop="toggleDropdown(vaccine.id)"
            >
              Actions ▼
            </Button>
            <div
              v-if="openDropdownId === vaccine.id"
              class="absolute mt-1 w-40 bg-white border rounded shadow-md z-10 dropdown-menu"
              @click.stop
            >
              <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="openModal(vaccine)">✏ Edit</button>
              <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="toggleStatus(vaccine)">
                {{ vaccine.status === 1 ? 'Inactive' : 'Activate' }}
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <div v-if="showModal" class="fixed inset-0 z-50 flex justify-center pt-6 bg-black/30" @click.self="resetForm">
    <div ref="modalRef" class="bg-white rounded-lg border border-gray-300 shadow-lg w-full max-w-2xl" style="top: 100px; position: absolute;">
      <div class="flex items-center justify-between p-4 border-b border-gray-200 cursor-move" @mousedown="startDrag">
        <h3 class="text-xl font-semibold text-gray-900">{{ editingVaccine ? 'Edit Vaccine' : 'Add New Vaccine' }}</h3>
        <button type="button" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg w-8 h-8 flex justify-center items-center" @click="resetForm">
          ✕
        </button>
      </div>

      <div class="p-4 space-y-4">
        <div>
          <Label for="vaccine_type_id" class="mb-2">Vaccine Type</Label>
          <select v-model="form.vaccine_type_id" id="vaccine_type_id" class="border rounded-md w-full p-2">
            <option v-for="vt in vaccineTypes" :key="vt.id" :value="vt.id">{{ vt.name }}</option>
          </select>
        </div>

        <div>
          <Label for="name" class="mb-2">Vaccine Name</Label>
          <Input v-model="form.name" id="name" />
        </div>

        <div>
          <Label for="applicator" class="mb-2">Applicator</Label>
          <Input v-model="form.applicator" id="applicator" />
        </div>

        <div>
          <Label for="dose" class="mb-2">Dose</Label>
          <Input v-model="form.dose" id="dose" />
        </div>

        <div>
          <Label for="note" class="mb-2">Note</Label>
          <textarea v-model="form.note" id="note" class="w-full border rounded p-2"></textarea>
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
