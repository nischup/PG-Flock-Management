<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div class="max-w-4xl mx-auto px-4 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Notifications</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-2">Manage your notifications and stay updated</p>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm border border-gray-200 dark:border-gray-700">
          <div class="flex items-center">
            <div class="p-2 bg-blue-100 dark:bg-blue-900/20 rounded-lg">
              <Bell class="w-6 h-6 text-blue-600 dark:text-blue-400" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ stats.total }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm border border-gray-200 dark:border-gray-700">
          <div class="flex items-center">
            <div class="p-2 bg-red-100 dark:bg-red-900/20 rounded-lg">
              <AlertCircle class="w-6 h-6 text-red-600 dark:text-red-400" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Unread</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ stats.unread }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm border border-gray-200 dark:border-gray-700">
          <div class="flex items-center">
            <div class="p-2 bg-green-100 dark:bg-green-900/20 rounded-lg">
              <CheckCircle class="w-6 h-6 text-green-600 dark:text-green-400" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Read</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ stats.read }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm border border-gray-200 dark:border-gray-700">
          <div class="flex items-center">
            <div class="p-2 bg-purple-100 dark:bg-purple-900/20 rounded-lg">
              <BarChart3 class="w-6 h-6 text-purple-600 dark:text-purple-400" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-600 dark:text-gray-400">This Week</p>
              <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ weeklyCount }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters and Actions -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 mb-6">
        <div class="p-6">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <!-- Filters -->
            <div class="flex flex-wrap gap-4">
              <select
                v-model="filters.type"
                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
              >
                <option value="">All Types</option>
                <option value="approval">Approval</option>
                <option value="flock">Flock</option>
                <option value="system">System</option>
                <option value="alert">Alert</option>
              </select>

              <select
                v-model="filters.priority"
                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
              >
                <option value="">All Priorities</option>
                <option value="urgent">Urgent</option>
                <option value="high">High</option>
                <option value="normal">Normal</option>
                <option value="low">Low</option>
              </select>

              <select
                v-model="filters.status"
                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
              >
                <option value="">All Status</option>
                <option value="unread">Unread</option>
                <option value="read">Read</option>
              </select>
            </div>

            <!-- Actions -->
            <div class="flex gap-2">
              <button
                @click="markAllAsRead"
                :disabled="stats.unread === 0"
                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm"
              >
                Mark All Read
              </button>
              <button
                @click="refreshNotifications"
                class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 text-sm"
              >
                Refresh
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Notifications List -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div v-if="filteredNotifications.length > 0" class="divide-y divide-gray-200 dark:divide-gray-700">
          <div
            v-for="notification in filteredNotifications"
            :key="notification.id"
            class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
            :class="{ 'bg-blue-50 dark:bg-blue-900/20': !notification.is_read }"
          >
            <div class="flex items-start gap-4">
              <!-- Icon -->
              <div class="flex-shrink-0">
                <div class="p-2 rounded-lg" :class="getIconBgClass(notification.type)">
                  <component
                    :is="getIcon(notification.icon)"
                    class="w-6 h-6"
                    :class="getIconClass(notification.type)"
                  />
                </div>
              </div>

              <!-- Content -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                      {{ notification.title }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                      {{ notification.message }}
                    </p>
                  </div>

                  <div class="flex items-center gap-2 ml-4">
                    <!-- Priority Badge -->
                    <span
                      class="px-2 py-1 text-xs font-medium rounded-full"
                      :class="getPriorityClasses(notification.priority)"
                    >
                      {{ notification.priority }}
                    </span>

                    <!-- Type Badge -->
                    <span
                      class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200"
                    >
                      {{ notification.type }}
                    </span>
                  </div>
                </div>

                <!-- Meta Information -->
                <div class="flex items-center justify-between mt-4">
                  <div class="flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                    <span>{{ formatTime(notification.created_at) }}</span>
                    <span v-if="notification.action_url" class="text-blue-600 dark:text-blue-400">
                      Click to view details
                    </span>
                  </div>

                  <div class="flex items-center gap-2">
                    <!-- Read Status -->
                    <span
                      v-if="!notification.is_read"
                      class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full bg-blue-100 dark:bg-blue-900/20 text-blue-800 dark:text-blue-200"
                    >
                      Unread
                    </span>

                    <!-- Actions -->
                    <div class="flex items-center gap-1">
                      <button
                        v-if="!notification.is_read"
                        @click="markAsRead(notification)"
                        class="p-1 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400"
                        title="Mark as read"
                      >
                        <CheckCircle class="w-4 h-4" />
                      </button>
                      <button
                        @click="deleteNotification(notification)"
                        class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400"
                        title="Delete"
                      >
                        <Trash2 class="w-4 h-4" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div v-else class="p-12 text-center">
          <Bell class="w-16 h-16 text-gray-400 mx-auto mb-4" />
          <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No notifications found</h3>
          <p class="text-gray-500 dark:text-gray-400">Try adjusting your filters or check back later.</p>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="notifications.links" class="mt-6">
        <nav class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="text-sm text-gray-700 dark:text-gray-300">
              Showing {{ notifications.from }} to {{ notifications.to }} of {{ notifications.total }} results
            </span>
          </div>
          <div class="flex items-center gap-1">
            <a
              v-for="link in notifications.links"
              :key="link.label"
              :href="link.url"
              v-html="link.label"
              class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700"
              :class="{
                'bg-blue-600 text-white border-blue-600': link.active,
                'text-gray-500 dark:text-gray-400': !link.url
              }"
            />
          </div>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
// import { Link } from '@inertiajs/vue3' // Not needed for this page
import { 
  Bell, 
  CheckCircle, 
  AlertCircle, 
  BarChart3, 
  Clock, 
  XCircle, 
  Feather, 
  Trash2,
  Info
} from 'lucide-vue-next'

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

interface Stats {
  total: number
  unread: number
  read: number
  by_type: Record<string, number>
  by_priority: Record<string, number>
}

interface Props {
  notifications: {
    data: Notification[]
    links: any[]
    from: number
    to: number
    total: number
  }
  stats: Stats
}

const props = defineProps<Props>()

// Filters
const filters = ref({
  type: '',
  priority: '',
  status: ''
})

// Computed properties
const filteredNotifications = computed(() => {
  let filtered = props.notifications.data

  if (filters.value.type) {
    filtered = filtered.filter(n => n.type === filters.value.type)
  }

  if (filters.value.priority) {
    filtered = filtered.filter(n => n.priority === filters.value.priority)
  }

  if (filters.value.status === 'unread') {
    filtered = filtered.filter(n => !n.is_read)
  } else if (filters.value.status === 'read') {
    filtered = filtered.filter(n => n.is_read)
  }

  return filtered
})

const weeklyCount = computed(() => {
  const weekAgo = new Date()
  weekAgo.setDate(weekAgo.getDate() - 7)
  
  return props.notifications.data.filter(n => 
    new Date(n.created_at) >= weekAgo
  ).length
})

// Methods
const getIcon = (iconName?: string) => {
  const icons = {
    'clock': Clock,
    'check-circle': CheckCircle,
    'x-circle': XCircle,
    'alert-circle': AlertCircle,
    'feather': Feather,
    'info': Info,
  }
  return icons[iconName as keyof typeof icons] || Bell
}

const getIconBgClass = (type: string) => {
  const classes = {
    'approval': 'bg-blue-100 dark:bg-blue-900/20',
    'flock': 'bg-green-100 dark:bg-green-900/20',
    'system': 'bg-purple-100 dark:bg-purple-900/20',
    'alert': 'bg-red-100 dark:bg-red-900/20',
  }
  return classes[type as keyof typeof classes] || 'bg-gray-100 dark:bg-gray-900/20'
}

const getIconClass = (type: string) => {
  const classes = {
    'approval': 'text-blue-600 dark:text-blue-400',
    'flock': 'text-green-600 dark:text-green-400',
    'system': 'text-purple-600 dark:text-purple-400',
    'alert': 'text-red-600 dark:text-red-400',
  }
  return classes[type as keyof typeof classes] || 'text-gray-600 dark:text-gray-400'
}

const getPriorityClasses = (priority: string) => {
  const classes = {
    'urgent': 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-200',
    'high': 'bg-orange-100 text-orange-800 dark:bg-orange-900/20 dark:text-orange-200',
    'normal': 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-200',
    'low': 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-200',
  }
  return classes[priority as keyof typeof classes] || classes.normal
}

const formatTime = (dateString: string) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInMinutes = Math.floor((now.getTime() - date.getTime()) / (1000 * 60))
  
  if (diffInMinutes < 1) return 'Just now'
  if (diffInMinutes < 60) return `${diffInMinutes}m ago`
  if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h ago`
  if (diffInMinutes < 10080) return `${Math.floor(diffInMinutes / 1440)}d ago`
  return date.toLocaleDateString()
}

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
    
    notification.is_read = true
  } catch (error) {
    console.error('Failed to mark notification as read:', error)
  }
}

const markAllAsRead = async () => {
  try {
    await fetch('/api/notifications/mark-all-read', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    })
    
    // Refresh the page to update the data
    window.location.reload()
  } catch (error) {
    console.error('Failed to mark all notifications as read:', error)
  }
}

const deleteNotification = async (notification: Notification) => {
  if (!confirm('Are you sure you want to delete this notification?')) return
  
  try {
    await fetch(`/api/notifications/${notification.id}`, {
      method: 'DELETE',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    })
    
    // Refresh the page to update the data
    window.location.reload()
  } catch (error) {
    console.error('Failed to delete notification:', error)
  }
}

const refreshNotifications = () => {
  window.location.reload()
}
</script>
