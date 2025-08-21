<script setup lang="ts">
import { ref, reactive } from 'vue'
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

interface FeedType {
  id: number
  name: string
  status: number
  created_at: string
}

const props = defineProps<{
  feedTypes: FeedType[]
  filters: { search?: string; per_page?: number; page?: number }
}>()

useListFilters({ routeName: '/feed-type', filters: props.filters })
const { can } = usePermissions()

// Reactive state
const feedTypes = ref<FeedType[]>([...props.feedTypes])
const showModal = ref(false)
const editingFeedType = ref<FeedType | null>(null)

const form = useForm({
  name: '',
  status: 1,
})

const modalRef = ref<HTMLElement | null>(null)
let offsetX = 0, offsetY = 0, isDragging = false

const startDrag = (e: MouseEvent) => {
  if (!modalRef.value) return
  isDragging = true
  const rect = modalRef.value.getBoundingClientRect()
  offsetX = e.clientX - rect.left
  offsetY = e.clientY - rect.top
  document.addEventListener('mousemove', onDrag)
  document.addEventListener('mouseup', stopDrag)
}

const onDrag = (e: MouseEvent) => {
  if (!isDragging || !modalRef.value) return
  modalRef.value.style.left = `${e.clientX - offsetX}px`
  modalRef.value.style.top = `${e.clientY - offsetY}px`
  modalRef.value.style.position = 'absolute'
  modalRef.value.style.margin = '0'
}

const stopDrag = () => {
  isDragging = false
  document.removeEventListener('mousemove', onDrag)
  document.removeEventListener('mouseup', stopDrag)
}

// Open modal
const openModal = (ft: FeedType | null = null) => {
  if (ft) {
    editingFeedType.value = ft
    form.name = ft.name
    form.status = ft.status
  } else {
    editingFeedType.value = null
    form.reset()
    form.status = 1
  }
  showModal.value = true
}

const resetForm = () => {
  form.reset()
  form.status = 1
  editingFeedType.value = null
  showModal.value = false
}

useNotifier()

const submit = () => {
  if (!form.name.trim()) return

  if (editingFeedType.value) {
    // Update
    form.put(route('feed-type.update', editingFeedType.value.id), {
      preserveScroll: true,
      onSuccess: () => {
        const index = feedTypes.value.findIndex(f => f.id === editingFeedType.value!.id)
        if (index !== -1) {
          feedTypes.value[index] = {
            ...feedTypes.value[index],
            name: form.name,
            status: form.status,
          }
        }
        resetForm()
      },
    })
  } else {
    // Create
    form.post(route('feed-type.store'), {
      preserveScroll: true,
      onSuccess: (page) => {
        // Refresh the list if server returns updated feedTypes
        if ((page as any).props?.feedTypes) {
          feedTypes.value = (page as any).props.feedTypes
        } else {
          feedTypes.value.unshift({
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

// Dropdown & status
const openDropdownId = ref<number | null>(null)
const toggleDropdown = (id: number) => {
  openDropdownId.value = openDropdownId.value === id ? null : id
}

const toggleStatus = (ft: FeedType) => {
  const newStatus = ft.status === 1 ? 0 : 1
  router.put(route('feed-type.update', ft.id), { name: ft.name, status: newStatus }, {
    preserveScroll: true,
    onSuccess: () => {
      const index = feedTypes.value.findIndex(f => f.id === ft.id)
      if (index !== -1) feedTypes.value[index].status = newStatus
    },
    onError: () => Swal.fire('Error', 'Could not update status', 'error'),
    onFinish: () => openDropdownId.value = null,
  })
}

const deleteFeedType = (ft: FeedType) => {
  Swal.fire({
    title: 'Are you sure?',
    text: `Delete "${ft.name}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!',
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('feed-type.destroy', ft.id), {
        preserveScroll: true,
        onSuccess: () => {
          feedTypes.value = feedTypes.value.filter(f => f.id !== ft.id)
          Swal.fire('Deleted!', 'Feed type has been deleted.', 'success')
        },
        onError: () => Swal.fire('Error', 'Could not delete feed type.', 'error'),
        onFinish: () => openDropdownId.value = null,
      })
    }
  })
}

// Breadcrumbs
const breadcrumbs = [
  { title: 'Master Setup', href: '/master-setup' },
  { title: 'Feed Type', href: '/master-setup/feed-type' },
]
</script>


<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="Feed Types" />

    <!-- Filters -->
    <FilterControls :filters="props.filters" routeName="/feed-type" />

    <div class="px-4 py-6">
      <!-- Header -->
      <div class="flex items-center justify-between mb-4">
        <HeadingSmall title="Feed Types List" />
        <Button
          v-if="can('feed-type.create')"
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
          <tr v-for="(feedType, index) in feedTypes" :key="feedType.id">
            <td class="p-2 border">{{ index + 1 }}</td>
            <td class="p-2 border">{{ feedType.name }}</td>
            <td class="p-2 border">
              <span
                :class="feedType.status === 1 ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'"
              >
                {{ feedType.status === 1 ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="p-2 border">{{ feedType.created_at }}</td>
            <td class="p-2 border relative">
              <Button
                size="sm"
                class="bg-gray-500 hover:bg-gray-600 text-white"
                @click="toggleDropdown(feedType.id)"
              >
                Actions ▼
              </Button>

              <!-- Dropdown -->
              <div
                v-if="openDropdownId === feedType.id"
                class="absolute right-0 mt-1 w-40 bg-white border rounded shadow-md z-10"
                @click.stop
              >
                <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="openModal(feedType)">
                  ✏ Edit
                </button>
                <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="toggleStatus(feedType)">
                  {{ feedType.status === 1 ? 'Inactive' : 'Activate' }}
                </button>
                <button
                  v-if="can('feed-type.delete')"
                  class="w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600"
                  @click="deleteFeedType(feedType)"
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
        <!-- Modal Header -->
        <div
          class="flex items-center justify-between p-4 border-b border-gray-200 cursor-move"
          @mousedown="startDrag"
        >
          <h3 class="text-xl font-semibold text-gray-900">
            {{ editingFeedType ? 'Edit Feed Type' : 'Add New Feed Type' }}
          </h3>
          <button
            type="button"
            class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex justify-center items-center"
            @click="resetForm"
          >
            ✕
          </button>
        </div>

        <!-- Modal Body -->
        <div class="p-4 space-y-4">
          <div>
            <Label for="name" class="mb-2">Feed Type Name</Label>
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

        <!-- Modal Footer -->
        <div class="flex justify-end p-4 border-t border-gray-200">
          <Button class="bg-gray-300 text-black mr-2" @click="resetForm">Cancel</Button>
          <Button class="bg-chicken text-white" @click="submit">
            {{ editingFeedType ? 'Update' : 'Save' }}
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
