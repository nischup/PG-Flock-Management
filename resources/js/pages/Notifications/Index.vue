<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Bell, AlertCircle, CheckCircle, Clock, XCircle, Feather, Info, RefreshCw, Filter } from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import HeadingSmall from '@/components/HeadingSmall.vue';

const breadcrumbItems: BreadcrumbItem[] = [
  {
    title: 'Notifications',
    href: '/settings/notifications',
  },
];

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
    'urgent': 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/20 dark:text-red-400 dark:border-red-800',
    'high': 'bg-orange-100 text-orange-800 border-orange-200 dark:bg-orange-900/20 dark:text-orange-400 dark:border-orange-800',
    'normal': 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:border-blue-800',
    'low': 'bg-gray-100 text-gray-800 border-gray-200 dark:bg-gray-900/20 dark:text-gray-400 dark:border-gray-800',
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
  return date.toLocaleDateString()
}

const clearFilters = () => {
  filters.value = {
    type: '',
    priority: '',
    status: ''
  }
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
    
    // Update local state
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

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Notifications" />

    <SettingsLayout>
      <div class="space-y-6">
        <HeadingSmall title="Notifications" description="Manage your notifications and stay updated" />
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
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
                <Clock class="w-6 h-6 text-purple-600 dark:text-purple-400" />
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">This Week</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ weeklyCount }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm border border-gray-200 dark:border-gray-700">
          <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex flex-wrap gap-4 items-center">
              <div class="flex items-center gap-2">
                <Filter class="w-4 h-4 text-gray-500" />
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Filters:</span>
              </div>
              
              <select v-model="filters.type" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 min-w-[120px]">
                <option value="">All Types</option>
                <option value="approval">Approval</option>
                <option value="flock">Flock</option>
                <option value="system">System</option>
                <option value="alert">Alert</option>
              </select>

              <select v-model="filters.priority" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 min-w-[140px]">
                <option value="">All Priorities</option>
                <option value="urgent">Urgent</option>
                <option value="high">High</option>
                <option value="normal">Normal</option>
                <option value="low">Low</option>
              </select>

              <select v-model="filters.status" class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 min-w-[120px]">
                <option value="">All Status</option>
                <option value="unread">Unread</option>
                <option value="read">Read</option>
              </select>
            </div>

            <div class="flex items-center gap-2">
              <button
                @click="clearFilters"
                class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700"
              >
                Clear Filters
              </button>
            </div>
          </div>
        </div>

        <!-- Notifications List -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
          <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              Notifications ({{ filteredNotifications.length }})
            </h3>
            <div class="flex items-center gap-2">
              <button
                @click="refreshNotifications"
                class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                title="Refresh"
              >
                <RefreshCw class="w-4 h-4" />
              </button>
              <button
                v-if="stats.unread > 0"
                @click="markAllAsRead"
                class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700"
              >
                Mark All Read
              </button>
            </div>
          </div>

          <div class="divide-y divide-gray-200 dark:divide-gray-700">
            <div v-if="filteredNotifications.length > 0">
              <div
                v-for="notification in filteredNotifications"
                :key="notification.id"
                class="p-6 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
              >
                <div class="flex items-start gap-4">
                  <!-- Icon -->
                  <div class="flex-shrink-0">
                    <div
                      class="w-12 h-12 rounded-full flex items-center justify-center"
                      :class="getIconBgClass(notification.type)"
                    >
                      <component
                        :is="getIcon(notification.icon)"
                        class="w-6 h-6"
                        :class="getIconClass(notification.type)"
                      />
                    </div>
                  </div>

                  <!-- Content -->
                  <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-4">
                      <div class="flex-1 min-w-0">
                        <h4 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-2">
                          {{ notification.title }}
                        </h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                          {{ notification.message }}
                        </p>
                      </div>
                      
                      <!-- Priority Badge -->
                      <div class="flex-shrink-0">
                        <span
                          class="px-3 py-1 text-xs font-medium rounded-full border"
                          :class="getPriorityClasses(notification.priority)"
                        >
                          {{ notification.priority }}
                        </span>
                      </div>
                    </div>
                    
                    <!-- Time and Actions -->
                    <div class="flex items-center justify-between mt-4">
                      <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                          {{ formatTime(notification.created_at) }}
                        </span>
                        <!-- Unread indicator -->
                        <div
                          v-if="!notification.is_read"
                          class="flex items-center gap-1 text-xs text-blue-600 dark:text-blue-400"
                        >
                          <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                          Unread
                        </div>
                      </div>
                      
                      <!-- Actions -->
                      <div class="flex items-center gap-2">
                        <button
                          v-if="!notification.is_read"
                          @click="markAsRead(notification)"
                          class="px-3 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 border border-blue-200 dark:border-blue-800 rounded-md hover:bg-blue-50 dark:hover:bg-blue-900/20"
                        >
                          Mark Read
                        </button>
                        <button
                          @click="deleteNotification(notification)"
                          class="px-3 py-1 text-xs font-medium text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 border border-red-200 dark:border-red-800 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20"
                        >
                          Delete
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="p-8 text-center">
              <Bell class="w-12 h-12 text-gray-400 mx-auto mb-4" />
              <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No notifications</h3>
              <p class="text-gray-500 dark:text-gray-400">You're all caught up! No notifications to show.</p>
            </div>
          </div>
        </div>
      </div>
    </SettingsLayout>
  </AppLayout>
</template>