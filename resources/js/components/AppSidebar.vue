<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue'
import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'
import { Sidebar,SidebarContent,SidebarFooter,SidebarHeader,SidebarMenu,SidebarMenuButton,SidebarMenuItem } from '@/components/ui/sidebar'
import { type NavItem } from '@/types'
import { Link,usePage } from '@inertiajs/vue3'
import { BookOpen, Folder, LayoutGrid, User, Users, Syringe, Building, Egg, Package, Tablet } from 'lucide-vue-next'
import AppLogo from './AppLogo.vue'
import { BabyChick } from '@/icons/BabyChick'
import { BabyChickMultiple } from '@/icons/BabyChickMultiple'
import { usePermissions } from '@/composables/usePermissions';

const page = usePage();
const { permissions, can } = usePermissions();
const mainNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutGrid
  },
  {
    title: 'Parent Stock (PS)',
    icon: BabyChickMultiple ,
    iconClass: 'text-yellow-500',
    children: [
      {
        title: 'PS Receive',
        href: '/ps-receive',
        icon: BabyChick
      },
      {
        title: 'PS Lab Test',
        href: '/ps-lab-test',
        icon: BabyChick
      },
      {
        title: 'PS Firm Receive',
        href: '/ps-firm-receive',
        icon: BabyChick
      }
    ]
  },
  {
    title: 'Shed',
    icon: Building ,
    iconClass: 'text-yellow-500',
    children: [
      {
        title: 'Shed Receive',
        href: '/receive',
        icon: BabyChick
      },
      {
        title: 'Flock Assign',
        href: '/flock-assign',
        icon: BabyChick
      },
    ]
  },
  {
    title: 'Farm Operation',
    icon: BabyChickMultiple ,
    iconClass: 'bg-chicken',
    children: [
      {
        title: 'Brooding',
        href: '/daily-operation',
        icon: BabyChick
      },
      {
        title: 'Growing',
        href: '/daily-operation',
        icon: BabyChick
      },
    ]
  },
  {
    title: 'Production',
    icon: Egg ,
    iconClass: 'text-yellow-500',
    children: [
      {
        title: 'Farm Receive',
        href: '/production-firm-receive',
        icon: BabyChick
      },
      {
        title: 'Shed Assign',
        href: '/flock-assign',
        icon: BabyChick
      },
      {
        title: 'Daily Operation',
        href: '/production/daily-operation',
        icon: BabyChick
      },
    ]
  },
  {
    title: 'Vaccine',
    icon: Syringe ,
    iconClass: 'text-yellow',
    children: [
      {
        title: 'Vaccine Schedule',
        href: '/vaccine-schedule',
        icon: Syringe
      },
    ]
  },

  {
    title: 'User Management',
    icon: Users,
    children: [
      {
        title: 'User Register',
        href: '/user-register',
        icon: User,
        permission: 'user.view',
      },
      {
        title: 'User Role Management',
        href: '/user-role',
        icon: User,
        permission: 'role.view',
      }
    ]
  },
  {
    title: 'Master Setup',
    icon: LayoutGrid,
    children: [
        { title: 'Feed', href: '/feed', icon: Package },
        { title: 'Unit', href: '/unit', icon: BookOpen },
        { title: 'Shed', href: '/shed', icon: Building },
        { title: 'Vaccine', href: '/vaccine', icon: BookOpen },
        { title: 'Vaccine Type', href: '/vaccine-type', icon: Syringe },
        { title: 'Disease', href: '/disease', icon: BookOpen },
        { title: 'Medicine', href: '/medicine', icon: Tablet },
        { title: 'Company', href: '/company', icon: BookOpen },
        { title: 'Chicks Type', href: '/chick-type', icon: BabyChick },
        { title: 'Feed Type', href: '/feed-type', icon: Package },
        { title: 'Supplier', href: '/supplier', icon: Users },

    ]
  }
]

const filteredMainNavItems = mainNavItems
  .map(item => {
    // If parent has children, filter children by permission
    if (item.children) {
      const filteredChildren = item.children.filter(child => can(child.permission));
      if (filteredChildren.length === 0) return null; // no child has permission => hide parent
      return { ...item, children: filteredChildren };
    }
    // If parent itself has a permission key
    if (can(item.permission)) return item;

    // If parent has no children and no permission => keep by default
    return item.permission ? null : item;
  })
  .filter(Boolean) as NavItem[];

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
      <NavMain :items="filteredMainNavItems" />
    </SidebarContent>

    <!-- Footer Navigation -->
    <SidebarFooter>
      <NavFooter :items="footerNavItems" />
      <NavUser />
    </SidebarFooter>
  </Sidebar>
  <slot />
</template>
