<script setup lang="ts">
import { ref } from 'vue'
import { Head, useForm, router } from '@inertiajs/vue3'
import Swal from 'sweetalert2'
import AppLayout from '@/layouts/AppLayout.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

interface Shed {
  id: number
  name: string
  status: number        // 1 = Active, 0 = Inactive
  created_at: string
}

const props = defineProps<{ sheds: Shed[] }>()
const sheds = ref<Shed[]>([...props.sheds])

// Modal state
const showModal = ref(false)
const editingShed = ref<Shed | null>(null)

// Modal form (Add/Edit)
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
const openModal = (shed: Shed | null = null) => {
  if (shed) {
    editingShed.value = shed
    form.name = shed.name
    form.status = shed.status
  } else {
    editingShed.value = null
    form.reset()
    form.status = 1
  }
  showModal.value = true
}

// Reset form
const resetForm = () => {
  form.reset()
  form.status = 1
  editingShed.value = null
  showModal.value = false
}

// Submit (Create/Update)
const submit = () => {
  if (!form.name.trim()) {
    Swal.fire('Validation Error', 'The shed name is required.', 'warning')
    return
  }

  if (editingShed.value) {
    form.put(route('shed.update', editingShed.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        const i = sheds.value.findIndex(s => s.id === editingShed.value!.id)
        if (i !== -1) {
          sheds.value[i] = { ...sheds.value[i], name: form.name, status: form.status }
        }
        Swal.fire('Success!', 'Shed updated successfully.', 'success')
        resetForm()
      },
      onError: () => {
        Swal.fire('Error!', 'Failed to update shed.', 'error')
      }
    })
  } else {
    form.post(route('shed.store'), {
      preserveScroll: true,
      onSuccess: (page) => {
        // Update local list if controller returns sheds
        if ((page as any).props?.sheds) {
          sheds.value = (page as any).props.sheds
        } else {
          sheds.value.unshift({
            id: Date.now(),
            name: form.name,
            status: form.status,
            created_at: new Date().toISOString(),
          })
        }
        Swal.fire('Success!', 'Shed created successfully.', 'success')
        resetForm()
      },
      onError: () => {
        Swal.fire('Error!', 'Failed to create shed.', 'error')
      }
    })
  }
}

// Dropdown for actions
const openDropdownId = ref<number | null>(null)
const toggleDropdown = (id: number) => {
  openDropdownId.value = openDropdownId.value === id ? null : id
}

// Toggle status
const toggleStatus = (shed: Shed) => {
  const newStatus = shed.status === 1 ? 0 : 1

  router.put(
    route('shed.update', shed.id),
    { name: shed.name, status: newStatus },
    {
      preserveScroll: true,
      onSuccess: () => {
        const i = sheds.value.findIndex(s => s.id === shed.id)
        if (i !== -1) sheds.value[i].status = newStatus
        Swal.fire('Updated!', newStatus === 1 ? 'Shed activated' : 'Shed Inactive', 'success')
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

// Delete shed
const deleteShed = (shed: Shed) => {
  Swal.fire({
    title: 'Are you sure?',
    text: `You are about to delete shed "${shed.name}"!`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!',
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('shed.destroy', shed.id), {
        preserveScroll: true,
        onSuccess: () => {
          sheds.value = sheds.value.filter(s => s.id !== shed.id)
          Swal.fire('Deleted!', 'Shed has been deleted.', 'success')
        },
        onError: () => {
          Swal.fire('Error!', 'Could not delete shed.', 'error')
        },
        onFinish: () => {
          openDropdownId.value = null
        },
      })
    }
  })
}

// Breadcrumbs
const breadcrumbs = [
  { title: 'Master Setup', href: '/master-setup' },
  { title: 'Shed', href: '/master-setup/shed' },
]
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Sheds" />

    <div class="px-4 py-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-4">
        <HeadingSmall title="Sheds List" />
        <Button class="bg-chicken hover:bg-yellow-600 text-white" @click="openModal()">
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
          <tr v-for="(shed, index) in sheds" :key="shed.id">
            <td class="p-2 border">{{ index + 1 }}</td>
            <td class="p-2 border">{{ shed.name }}</td>
            <td class="p-2 border">
              <span :class="shed.status === 1 ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                {{ shed.status === 1 ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="p-2 border">{{ shed.created_at }}</td>
            <td class="p-2 border relative">
              <Button size="sm" class="bg-gray-500 hover:bg-gray-600 text-white" @click="toggleDropdown(shed.id)">
                Actions ▼
              </Button>

              <!-- Dropdown -->
              <div
                v-if="openDropdownId === shed.id"
                class="absolute right-0 mt-1 w-40 bg-white border rounded shadow-md z-10"
                @click.stop
              >
                <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="openModal(shed)">✏ Edit</button>
                <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="toggleStatus(shed)">
                  {{ shed.status === 1 ? 'Inactive' : 'Activate' }}
                </button>
                <button class="w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600" @click="deleteShed(shed)">
                  🗑 Delete
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
        style="top: 100px; position: absolute;"
      >
        <div
          class="flex items-center justify-between p-4 border-b border-gray-200 cursor-move"
          @mousedown="startDrag"
        >
          <h3 class="text-xl font-semibold text-gray-900">
            {{ editingShed ? 'Edit Shed' : 'Add New Shed' }}
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
            <Label for="name" class="mb-2">Shed Name</Label>
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
            {{ editingShed ? 'Update' : 'Save' }}
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
