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
import { usePermissions } from '@/composables/usePermissions'

interface Feed {
  id: number
  feed_type_id: number
  feed_type_name: string
  feed_name: string
  status: number
  created_at: string
}

const props = defineProps<{
  feeds: Feed[]
  feedTypes: Array<{ id: number; name: string }>
  filters: { search?: string; per_page?: number; page?: number }
}>()

useNotifier()
const { can } = usePermissions()

// Local state
const feeds = ref<Feed[]>([...props.feeds])

// Modal state
const showModal = ref(false)
const editingFeed = ref<Feed | null>(null)

// Modal form
const form = useForm({
  feed_type_id: props.feedTypes.length ? props.feedTypes[0].id : null,
  feed_name: '',
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
const openModal = (feed: Feed | null = null) => {
  if (feed) {
    editingFeed.value = feed
    form.feed_type_id = feed.feed_type_id
    form.feed_name = feed.feed_name
    form.status = feed.status
  } else {
    editingFeed.value = null
    form.reset()
    form.feed_type_id = props.feedTypes.length ? props.feedTypes[0].id : null
    form.status = 1
  }
  showModal.value = true
}

// Reset modal
const resetForm = () => {
  form.reset()
  form.feed_type_id = props.feedTypes.length ? props.feedTypes[0].id : null
  form.status = 1
  editingFeed.value = null
  showModal.value = false
}

// Submit
const submit = () => {
  if (!form.feed_name.trim()) return

  if (editingFeed.value) {
    // Update
    form.put(route('feed.update', editingFeed.value.id), {
      preserveScroll: true,
      onSuccess: (page) => {
        const i = feeds.value.findIndex(f => f.id === editingFeed.value!.id)
        if (i !== -1) {
          feeds.value[i] = {
            ...feeds.value[i],
            feed_type_id: form.feed_type_id,
            feed_name: form.feed_name,
            status: form.status,
          }
        }
        resetForm()
      },
      onError: () => Swal.fire('Error!', 'Could not update feed.', 'error'),
    })
  } else {
    // Create
    form.post(route('feed.store'), {
      preserveScroll: true,
      onSuccess: (page) => {
        if ((page as any).props?.feeds) {
          feeds.value = (page as any).props.feeds
        } else {
          feeds.value.unshift({
            id: Date.now(),
            feed_type_id: form.feed_type_id!,
            feed_type_name: props.feedTypes.find(ft => ft.id === form.feed_type_id!)?.name || '',
            feed_name: form.feed_name,
            status: form.status,
            created_at: new Date().toISOString(),
          })
        }
        resetForm()
      },
      onError: () => Swal.fire('Error!', 'Could not create feed.', 'error'),
    })
  }
}

// Dropdown
const openDropdownId = ref<number | null>(null)
const toggleDropdown = (id: number) => {
  openDropdownId.value = openDropdownId.value === id ? null : id
}

// Toggle status
const toggleStatus = (feed: Feed) => {
  const newStatus = feed.status === 1 ? 0 : 1
  router.put(route('feed.update', feed.id), { ...feed, status: newStatus }, {
    preserveScroll: true,
    onSuccess: () => {
      const i = feeds.value.findIndex(f => f.id === feed.id)
      if (i !== -1) feeds.value[i].status = newStatus
    },
    onError: () => Swal.fire('Error!', 'Could not update status.', 'error'),
    onFinish: () => { openDropdownId.value = null },
  })
}

// Delete
const deleteFeed = (feed: Feed) => {
  Swal.fire({
    title: 'Are you sure?',
    text: `You are about to delete feed "${feed.feed_name}"!`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!',
  }).then((result) => {
    if (result.isConfirmed) {
      router.delete(route('feed.destroy', feed.id), {
        preserveScroll: true,
        onSuccess: () => {
          feeds.value = feeds.value.filter(f => f.id !== feed.id)
          Swal.fire('Deleted!', 'Feed has been deleted.', 'success')
        },
        onError: () => Swal.fire('Error!', 'Could not delete feed.', 'error'),
        onFinish: () => { openDropdownId.value = null },
      })
    }
  })
}

// Breadcrumbs
const breadcrumbs = [
  { title: 'Master Setup', href: '/master-setup' },
  { title: 'Feed', href: '/master-setup/feed' },
]
</script>

<template>
<AppLayout :breadcrumbs="breadcrumbs">
  <Head title="Feeds" />

  <div class="px-4 py-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-4">
      <HeadingSmall title="Feeds List" />
      <Button v-if="can('feed.create')" class="bg-chicken hover:bg-yellow-600 text-white" @click="openModal()">
        + Add New
      </Button>
    </div>

    <!-- Table -->
    <table class="w-full border">
      <thead>
        <tr class="bg-gray-100">
          <th class="p-2 border text-left">#</th>
          <th class="p-2 border text-left">Feed Type</th>
          <th class="p-2 border text-left">Feed Name</th>
          <th class="p-2 border text-left">Status</th>
          <th class="p-2 border text-left">Created At</th>
          <th class="p-2 border text-left">Action</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(feed, index) in feeds" :key="feed.id">
          <td class="p-2 border">{{ index + 1 }}</td>
          <td class="p-2 border">{{ feed.feed_type_name }}</td>
          <td class="p-2 border">{{ feed.feed_name }}</td>
          <td class="p-2 border">
            <span :class="feed.status === 1 ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
              {{ feed.status === 1 ? 'Active' : 'Inactive' }}
            </span>
          </td>
          <td class="p-2 border">{{ feed.created_at }}</td>
          <td class="p-2 border relative">
            <Button size="sm" class="bg-gray-500 hover:bg-gray-600 text-white" @click="toggleDropdown(feed.id)">
              Actions ▼
            </Button>
            <div v-if="openDropdownId === feed.id" class="absolute right-0 mt-1 w-40 bg-white border rounded shadow-md z-10" @click.stop>
              <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="openModal(feed)">✏ Edit</button>
              <button class="w-full text-left px-4 py-2 hover:bg-gray-100" @click="toggleStatus(feed)">
                {{ feed.status === 1 ? 'Inactive' : 'Activate' }}
              </button>
              <button v-if="can('feed.delete')" class="w-full text-left px-4 py-2 hover:bg-gray-100 text-red-600" @click="deleteFeed(feed)">
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
    <div ref="modalRef" class="bg-white rounded-lg border border-gray-300 shadow-lg w-full max-w-2xl" style="top: 100px; position: absolute">
      <!-- Modal Header -->
      <div class="flex items-center justify-between p-4 border-b border-gray-200 cursor-move" @mousedown="startDrag">
        <h3 class="text-xl font-semibold text-gray-900">{{ editingFeed ? 'Edit Feed' : 'Add New Feed' }}</h3>
        <button type="button" class="text-gray-400 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex justify-center items-center" @click="resetForm">✕</button>
      </div>

      <!-- Modal Body -->
      <div class="p-4 space-y-4">
        <div>
          <Label for="feed_type" class="mb-2">Feed Type</Label>
          <select v-model="form.feed_type_id" id="feed_type" class="w-full border rounded p-2">
            <option v-for="ft in props.feedTypes" :key="ft.id" :value="ft.id">{{ ft.name }}</option>
          </select>
        </div>

        <div>
          <Label for="feed_name" class="mb-2">Feed Name</Label>
          <Input v-model="form.feed_name" id="feed_name" />
          <span v-if="form.errors.feed_name" class="text-red-600 text-sm">{{ form.errors.feed_name }}</span>
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
        <Button class="bg-chicken text-white" @click="submit">{{ editingFeed ? 'Update' : 'Save' }}</Button>
      </div>
    </div>
  </div>
</AppLayout>
</template>
