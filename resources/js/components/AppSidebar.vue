<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue'
import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'
import { Sidebar,SidebarContent,SidebarFooter,SidebarHeader,SidebarMenu,SidebarMenuButton,SidebarMenuItem } from '@/components/ui/sidebar'
import { type NavItem } from '@/types'
import { Link } from '@inertiajs/vue3'
import { BookOpen, Folder, LayoutGrid, User, Users } from 'lucide-vue-next'
import AppLogo from './AppLogo.vue'
import { BabyChick } from '@/icons/BabyChick'
import { BabyChickMultiple } from '@/icons/BabyChickMultiple'

const mainNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutGrid
  },
  {
    title: 'DOC',
    icon: BabyChickMultiple ,
    iconClass: 'text-yellow-500',
    children: [
      {
        title: 'DOC Receive',
        href: '/doc-reciive',
        icon: BabyChick
      },
      {
        title: 'DOC Lab Test',
        href: '/doc-lab-test',
        icon: BabyChick
      },
      {
        title: 'DOC Factory Receive',
        href: '/user-role',
        icon: BabyChick
      }
    ]
  },
  {
    title: 'User Management',
    icon: Users,
    children: [
      {
        title: 'User Register',
        href: '/user-register',
        icon: User
      },
      {
        title: 'User Role Management',
        href: '/user-role',
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
      { title: 'Shed', href: '/shed', icon: BookOpen },
      { title: 'Vaccine', href: '/vaccine', icon: BookOpen },
      { title: 'Medicine', href: '/medicine', icon: BookOpen },
      { title: 'Company', href: '/company', icon: BookOpen },
      { title: 'Chicks Type', href: '/chick-type', icon: BookOpen },
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
