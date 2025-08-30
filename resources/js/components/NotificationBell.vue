<script setup lang="ts">
import { ref } from 'vue'
import { Bell, User } from 'lucide-vue-next'

interface Notification {
  id: number
  userId: number
  userName: string
  message: string
  avatar?: string
}

const props = defineProps<{
  notifications: Notification[]
}>()

const isOpen = ref(false)
</script>

<template>
  <div class="relative">
    <!-- Bell Button -->
    <button
      @click="isOpen = !isOpen"
      class="relative p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800"
    >
      <Bell class="w-6 h-6" />
      <!-- Red notification dot -->
      <span
        v-if="props.notifications.length > 0"
        class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-red-500 rounded-full"
      />
    </button>

    <!-- Dropdown -->
    <div
      v-if="isOpen"
      class="absolute right-0 mt-2 w-72 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50"
    >
      <div v-if="props.notifications.length > 0">
        <div
          v-for="note in props.notifications"
          :key="note.id"
          class="flex items-start gap-3 px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-800"
        >
          <!-- Avatar (fallback to icon) -->
          <img
            v-if="note.avatar"
            :src="note.avatar"
            class="w-8 h-8 rounded-full object-cover"
          />
          <User v-else class="w-8 h-8 text-gray-500" />

          <!-- Message -->
          <div>
            <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
              {{ note.userName }}
            </p>
            <p class="text-sm text-gray-600 dark:text-gray-400">
              {{ note.message }}
            </p>
          </div>
        </div>
      </div>

      <div v-else class="px-4 py-3 text-sm text-gray-500 text-center">
        No notifications
      </div>
    </div>
  </div>
</template>
