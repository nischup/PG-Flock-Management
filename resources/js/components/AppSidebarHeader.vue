<script setup lang="ts">
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import Clock from '@/components/Clock.vue' // reusable clock component
import { ref, onMounted, watch } from 'vue';
import type { BreadcrumbItemType } from '@/types';
import WeatherWidget from '../components/WehatherWidget.vue';
import NotificationBell from '../components/NotificationBell.vue';
import { useNotificationSound } from '@/composables/useNotificationSound';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItemType[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

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

const userNotifications = ref<Notification[]>([])
const previousNotificationCount = ref(0)

// Initialize notification sound
const { playSound, preloadAudio } = useNotificationSound({
  volume: 0.5,
  preload: true
})

// Fetch recent notifications
const fetchNotifications = async () => {
  try {
    const response = await fetch('/api/notifications/recent', {
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    })
    
    if (response.ok) {
      const data = await response.json()
      const newCount = data.filter((n: Notification) => !n.is_read).length
      const previousCount = previousNotificationCount.value
      
      // Play sound if new notifications arrived (count increased)
      if (newCount > previousCount && previousCount > 0) {
        playSound()
      }
      
      userNotifications.value = data
      previousNotificationCount.value = newCount
    }
  } catch (error) {
    console.error('Failed to fetch notifications:', error)
  }
}

// Fetch notifications on component mount
onMounted(() => {
  fetchNotifications()
  preloadAudio() // Preload the sound file
  
  // Refresh notifications every 30 seconds
  setInterval(fetchNotifications, 30000)
})
</script>

<template>
    <!-- <header
        class="flex h-16 shrink-0 items-center gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        
        <div class="flex items-center gap-6">
           
            <button
            class="relative text-gray-600 hover:text-gray-800 transition"
            aria-label="Notifications"
            >
            <svg xmlns="http://www.w3.org/2000/svg"
                class="w-6 h-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="2"
            >
                <path stroke-linecap="round" stroke-linejoin="round"
                d="M14.25 18.75a2.25 2.25 0 01-4.5 0m9-6v-2.25a6.75 6.75 
                    0 00-5.21-6.57 1.5 1.5 0 00-2.58 0A6.75 6.75 0 006 
                    10.5v2.25l-1.5 3v.75h15v-.75l-1.5-3z"/>
            </svg>
            
            <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-500 rounded-full"></span>
            </button>

            
            <Clock />
        </div>
    </header> -->

    <header
  class="flex h-16 shrink-0 items-center justify-between border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-10 md:px-4"
>
  <!-- Left side: Sidebar + Breadcrumbs -->
  <div class="flex items-center gap-2">
    <SidebarTrigger class="-ml-1" />
    <template v-if="breadcrumbs && breadcrumbs.length > 0">
      <Breadcrumbs :breadcrumbs="breadcrumbs" />
    </template>
  </div>

  <!-- Right side: Bell + Clock -->
  <div class="flex items-center gap-6">
    <WeatherWidget/>
    <!-- Bell Icon -->
    <button
      class="relative text-gray-600 hover:text-black-800 transition"
      aria-label="Notifications"
    >
      
       
      <NotificationBell :notifications="userNotifications" />
      
    </button>

    <!-- Clock -->
    <Clock class="mr-[10px]"/>
  </div>
</header>
</template>
