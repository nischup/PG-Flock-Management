<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue'
import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'
import { Sidebar,SidebarContent,SidebarFooter,SidebarHeader,SidebarMenu,SidebarMenuButton,SidebarMenuItem } from '@/components/ui/sidebar'
import { type NavItem } from '@/types'
import { Link } from '@inertiajs/vue3'
import { BookOpen, Folder, LayoutGrid, User } from 'lucide-vue-next'
import AppLogo from './AppLogo.vue'

const mainNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutGrid
  },
  {
    title: 'User Management',
    icon: User,
    children: [
      {
        title: 'User Register',
        href: '/user-register',
        icon: User
      },
      {
        title: 'User Role Management',
        href: '/user-role-management',
        icon: User
      }
    ]
  },
  {
    title: 'Master Setup',
    icon: LayoutGrid,
    children: [
      { title: 'Feed', href: '/feed', icon: BookOpen },
      { title: 'Unit', href: '/unit', icon: BookOpen },
      { title: 'Shade', href: '/shade', icon: BookOpen },
      { title: 'Vaccine', href: '/vaccine', icon: BookOpen },
      { title: 'Medicine', href: '/medicine', icon: BookOpen },
      { title: 'Compnay Location', href: '/location', icon: BookOpen },
      { title: 'Bird Type', href: '/bird-type', icon: BookOpen },
      { title: 'Feed Type', href: '/feed-type', icon: BookOpen },
    ]
  }
]

</script>

<template>
  <Sidebar collapsible="icon" variant="inset" >
    <!-- Header with Logo -->
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton size="lg" as-child>
            <Link :href="route('dashboard')">
              <AppLogo />
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>

    <!-- Main Navigation -->
    <SidebarContent>
      <NavMain :items="mainNavItems" />
    </SidebarContent>

    <!-- Footer Navigation -->
    <SidebarFooter>
      <NavFooter :items="footerNavItems" />
      <NavUser />
    </SidebarFooter>
  </Sidebar>
  <slot />
</template>
