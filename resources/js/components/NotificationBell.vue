<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import { Bell, User, CheckCircle, XCircle, Clock, AlertCircle, Feather } from 'lucide-vue-next'
// import { router } from '@inertiajs/vue3' // Not needed for this component

interface Notification {
  id: number
  type: string
  title: string
  message: string
  icon?: string
  priority: string
  is_read: boolean
  action_url?: string
  created_at: string
  data?: any
}

const props = defineProps<{
  notifications: Notification[]
}>()

const isOpen = ref(false)
const unreadCount = ref(0)

// Update unread count when notifications change
const updateUnreadCount = () => {
  unreadCount.value = props.notifications.filter(n => !n.is_read).length
}

// Watch for changes in notifications
onMounted(() => {
  updateUnreadCount()
})

// Get icon component based on icon name
const getIcon = (iconName?: string) => {
  const icons = {
    'clock': Clock,
    'check-circle': CheckCircle,
    'x-circle': XCircle,
    'alert-circle': AlertCircle,
    'feather': Feather,
  }
  return icons[iconName as keyof typeof icons] || Bell
}

// Get priority color classes
const getPriorityClasses = (priority: string) => {
  const classes = {
    'urgent': 'bg-red-100 text-red-800 border-red-200',
    'high': 'bg-orange-100 text-orange-800 border-orange-200',
    'normal': 'bg-blue-100 text-blue-800 border-blue-200',
    'low': 'bg-gray-100 text-gray-800 border-gray-200',
  }
  return classes[priority as keyof typeof classes] || classes.normal
}

// Handle notification click
const handleNotificationClick = (notification: Notification) => {
  if (notification.action_url) {
    window.location.href = notification.action_url
  }
  isOpen.value = false
}

// Mark notification as read
const markAsRead = async (notification: Notification) => {
  if (notification.is_read) return
  
  try {
    await fetch(`/api/notifications/${notification.id}/mark-read`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    })
    
    // Update local state
    notification.is_read = true
    updateUnreadCount()
  } catch (error) {
    console.error('Failed to mark notification as read:', error)
  }
}

// Mark all as read
const markAllAsRead = async () => {
  try {
    await fetch('/api/notifications/mark-all-read', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    })
    
    // Update local state
    props.notifications.forEach(n => n.is_read = true)
    updateUnreadCount()
  } catch (error) {
    console.error('Failed to mark all notifications as read:', error)
  }
}

// Format time
const formatTime = (dateString: string) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInMinutes = Math.floor((now.getTime() - date.getTime()) / (1000 * 60))
  
  if (diffInMinutes < 1) return 'Just now'
  if (diffInMinutes < 60) return `${diffInMinutes}m ago`
  if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h ago`
  return date.toLocaleDateString()
}
</script>

<template>
  <div class="relative">
    <!-- Bell Button -->
    <button
      @click="isOpen = !isOpen"
      class="relative p-2 rounded-full hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
    >
      <Bell class="w-6 h-6" />
      <!-- Red notification dot -->
      <span
        v-if="unreadCount > 0"
        class="absolute -top-0.5 -right-0.5 w-2.5 h-2.5 bg-red-500 rounded-full text-xs text-white flex items-center justify-center"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown -->
    <Transition
      enter-active-class="transition ease-out duration-200"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition ease-in duration-150"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="isOpen"
        class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50"
      >
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
          <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">Notifications</h3>
          <button
            v-if="unreadCount > 0"
            @click="markAllAsRead"
            class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
          >
            Mark all read
          </button>
        </div>

        <!-- Notifications List -->
        <div class="max-h-96 overflow-y-auto">
          <div v-if="props.notifications.length > 0">
            <div
              v-for="notification in props.notifications"
              :key="notification.id"
              @click="handleNotificationClick(notification)"
              class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800 cursor-pointer border-b border-gray-100 dark:border-gray-800 last:border-b-0"
              :class="{ 'bg-blue-50 dark:bg-blue-900/20': !notification.is_read }"
            >
              <!-- Icon -->
              <div class="flex-shrink-0 mt-1">
                <component
                  :is="getIcon(notification.icon)"
                  class="w-5 h-5"
                  :class="notification.is_read ? 'text-gray-400' : 'text-blue-600'"
                />
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100">
                      {{ notification.title }}
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                      {{ notification.message }}
                    </p>
                  </div>
                  
                  <!-- Priority Badge -->
                  <span
                    class="ml-2 px-2 py-1 text-xs rounded-full border"
                    :class="getPriorityClasses(notification.priority)"
                  >
                    {{ notification.priority }}
                  </span>
                </div>
                
                <!-- Time and Read Status -->
                <div class="flex items-center justify-between mt-2">
                  <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ formatTime(notification.created_at) }}
                  </span>
                  
                  <!-- Unread indicator -->
                  <div
                    v-if="!notification.is_read"
                    class="w-2 h-2 bg-blue-600 rounded-full"
                  />
                </div>
              </div>
            </div>
          </div>

          <div v-else class="px-4 py-8 text-center">
            <Bell class="w-12 h-12 text-gray-400 mx-auto mb-2" />
            <p class="text-sm text-gray-500 dark:text-gray-400">No notifications</p>
          </div>
        </div>

        <!-- Footer -->
        <div v-if="props.notifications.length > 0" class="px-4 py-2 border-t border-gray-200 dark:border-gray-700">
          <a
            href="/notifications"
            class="text-xs text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
            @click="isOpen = false"
          >
            View all notifications
          </a>
        </div>
      </div>
    </Transition>
  </div>
</template>
