<script setup lang="ts">
import { ref } from 'vue'
import {
  SidebarGroup,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem
} from '@/components/ui/sidebar'
import { type NavItem } from '@/types'
import { Link, usePage } from '@inertiajs/vue3'

const props = defineProps<{
  items: NavItem[]
}>()

const page = usePage()
const openMenus = ref<string[]>([])

function toggleMenu(title: string) {
  if (openMenus.value.includes(title)) {
    openMenus.value = openMenus.value.filter(t => t !== title)
  } else {
    openMenus.value.push(title)
  }
}

// ✅ add this helper here so stroke colors work
function getIconColor(iconClass?: string) {
  switch (iconClass) {
    case 'text-yellow-500': return '#facc15'
    case 'text-blue-500': return '#3b82f6'
    case 'text-gray-700': return '#374151'
    case 'text-green-500': return '#22c55e'
    case 'bg-chicken': return '#fbbf24'
    default: return '#000000'
  }
}
</script>

<template>
  <SidebarGroup class="px-2 py-0">
    <SidebarGroupLabel>Application V-1.0.0</SidebarGroupLabel>
    <SidebarMenu>
      <template v-for="item in props.items" :key="item.title">
        <SidebarMenuItem>
          <!-- Simple item -->
          <SidebarMenuButton
            v-if="!item.children"
            as-child
            :is-active="item.href === page.url"
            :tooltip="item.title"
          >
            <Link :href="item.href">
              <component 
                :is="item.icon" 
                class="w-5 h-5" 
                :stroke="getIconColor(item.iconClass)" 
              />
              <span>{{ item.title }}</span>
            </Link>
          </SidebarMenuButton>

          <!-- Parent item with children -->
          <div
            v-else
            class="flex items-center cursor-pointer px-3 py-2 rounded"
            @click="toggleMenu(item.title)"
          >
            <component 
              :is="item.icon" 
              class="w-5 h-5 mr-2" 
              :stroke="getIconColor(item.iconClass)" 
            />
            <span class="flex-1">{{ item.title }}</span>
            <svg
              class="w-4 h-4 transition-transform"
              :class="{ 'rotate-90': openMenus.includes(item.title) }"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </div>

          <!-- Child links -->
          <div
            v-if="item.children"
            v-show="openMenus.includes(item.title)"
            class="ml-6 mt-1 space-y-1"
          >
            <SidebarMenuItem
              v-for="child in item.children"
              :key="child.title"
            >
              <SidebarMenuButton
                as-child
                :is-active="child.href === page.url"
                :tooltip="child.title"
              >
                <Link :href="child.href">
                  <component 
                    :is="child.icon" 
                    class="w-5 h-5" 
                    :stroke="getIconColor(child.iconClass)" 
                  />
                  <span>{{ child.title }}</span>
                </Link>
              </SidebarMenuButton>
            </SidebarMenuItem>
          </div>
        </SidebarMenuItem>
      </template>
    </SidebarMenu>
  </SidebarGroup>
</template>
