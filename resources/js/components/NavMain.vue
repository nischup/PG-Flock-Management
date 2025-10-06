<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import {
  SidebarGroup,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem
} from '@/components/ui/sidebar'
import { useSidebar } from '@/components/ui/sidebar/utils'
import { type NavItem } from '@/types'
import { Link, usePage } from '@inertiajs/vue3'

const props = defineProps<{
  items: NavItem[]
}>()

const page = usePage()
const { state } = useSidebar()
const openMenus = ref<string[]>([])

// Function to check if a URL matches a menu item (handles route patterns)
function isUrlActive(menuHref: string, currentUrl: string): boolean {
  // Exact match
  if (menuHref === currentUrl) return true
  
  // Check if current URL starts with menu href (for nested routes)
  // e.g., /shed-receive/create should match /shed-receive
  if (currentUrl.startsWith(menuHref + '/')) return true
  
  return false
}

// Function to find parent menu titles that contain the current active route
function findActiveParentMenus(items: NavItem[], currentUrl: string): string[] {
  const activeParents: string[] = []
  
  for (const item of items) {
    if (item.children) {
      // Check if any child matches the current URL
      const hasActiveChild = item.children.some(child => isUrlActive(child.href, currentUrl))
      if (hasActiveChild) {
        activeParents.push(item.title)
      }
      // Recursively check nested children
      const nestedParents = findActiveParentMenus(item.children, currentUrl)
      activeParents.push(...nestedParents)
    }
  }
  
  return activeParents
}

// Computed property to get active parent menus based on current URL
const activeParentMenus = computed(() => {
  return findActiveParentMenus(props.items, page.url)
})

// Watch for URL changes and update open menus
watch(() => page.url, () => {
  const activeParents = findActiveParentMenus(props.items, page.url)
  // Merge with existing open menus, avoiding duplicates
  const newOpenMenus = [...new Set([...openMenus.value, ...activeParents])]
  openMenus.value = newOpenMenus
}, { immediate: true })

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
    <SidebarGroupLabel v-if="state === 'expanded'">Application V-1.0.0</SidebarGroupLabel>
    <SidebarMenu>
      <template v-for="item in props.items" :key="item.title">
        <SidebarMenuItem>
          <!-- Simple item -->
          <SidebarMenuButton
            v-if="!item.children"
            as-child
            :is-active="isUrlActive(item.href, page.url)"
            :tooltip="state === 'collapsed' ? item.title : undefined"
            class="hover:yellow-300"
          >
            <Link :href="item.href">
              <component 
                :is="item.icon" 
                class="w-5 h-5" 
                :stroke="getIconColor(item.iconClass)" 
              />
              <span v-if="state === 'expanded'">{{ item.title }}</span>
            </Link>
          </SidebarMenuButton>

          <!-- Parent item with children -->
          <SidebarMenuButton
            v-else
            :is-active="activeParentMenus.includes(item.title)"
            :tooltip="state === 'collapsed' ? item.title : undefined"
            class="hover:yellow-300"
            @click="toggleMenu(item.title)"
          >
            <component 
              :is="item.icon" 
              class="w-5 h-5" 
              :stroke="getIconColor(item.iconClass)" 
            />
            <span v-if="state === 'expanded'" class="flex-1">{{ item.title }}</span>
            <svg
              v-if="state === 'expanded'"
              class="w-4 h-4 transition-transform"
              :class="{ 'rotate-90': openMenus.includes(item.title) }"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
            </svg>
          </SidebarMenuButton>

          <!-- Child links -->
          <div
            v-if="item.children && state === 'expanded'"
            v-show="openMenus.includes(item.title)"
            class="ml-6 mt-1 space-y-1"
          >
            <SidebarMenuItem
              v-for="child in item.children"
              :key="child.title"
            >
              <SidebarMenuButton
                as-child
                :is-active="isUrlActive(child.href, page.url)"
                :tooltip="state === 'collapsed' ? child.title : undefined"
              >
                <Link :href="child.href">
                  <component 
                    :is="child.icon" 
                    class="w-5 h-5" 
                    :stroke="getIconColor(child.iconClass)" 
                  />
                  <span v-if="state === 'expanded'">{{ child.title }}</span>
                </Link>
              </SidebarMenuButton>
            </SidebarMenuItem>
          </div>
        </SidebarMenuItem>
      </template>
    </SidebarMenu>
  </SidebarGroup>
</template>
