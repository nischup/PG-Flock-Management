<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue'
import NavMain from '@/components/NavMain.vue'
import NavUser from '@/components/NavUser.vue'
import { Sidebar,SidebarContent,SidebarFooter,SidebarHeader,SidebarMenu,SidebarMenuButton,SidebarMenuItem } from '@/components/ui/sidebar'
import { type NavItem } from '@/types'
import { Link,usePage } from '@inertiajs/vue3'
import { BookOpen, Folder, LayoutGrid, User, Users, Syringe, Building, Egg,EggOff, Package, PencilRuler, Pill,Skull,Building2 } from 'lucide-vue-next'
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
    icon: LayoutGrid,
    iconClass: 'text-yellow-500',
  },
  {
    title: 'Parent Stock (PS)',
    icon: BabyChickMultiple ,
    iconClass: 'text-yellow-500',
    children: [
      {
        title: 'PS Receive',
        href: '/ps-receive',
        icon: BabyChick,
        iconClass: 'text-yellow-500',
      },
      {
        title: 'PS Lab Test',
        href: '/ps-lab-test',
        icon: BabyChick,
        iconClass: 'text-yellow-500',
      },
      {
        title: 'PS Firm Receive',
        href: '/ps-firm-receive',
        icon: BabyChick,
         iconClass: 'text-yellow-500',
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
        href: '/shed-receive',
        icon: BabyChick,
        iconClass: 'text-yellow-500',
      },
      {
        title: 'Batch Assign',
        href: '/flock-assign',
        icon: BabyChick,
        iconClass: 'text-yellow-500',
      },
    ]
  },
  {
    title: 'Farm Operation',
    icon: BabyChickMultiple ,
     iconClass: 'text-yellow-500',
    children: [
      {
        title: 'Brooding',
        href: '/daily-operation/stage/brooding',
        icon: BabyChick,
        iconClass: 'text-yellow-500',
      },
      {
        title: 'Growing',
        href: '/daily-operation/stage/growing',
        icon: BabyChick,
        iconClass: 'text-yellow-500',
      },
      {
        title: 'Bird Transfer',
        href: '/bird-transfer',
        icon: BabyChick,
        iconClass: 'text-yellow-500',

      },
    ]
  },
  {
    title: 'Production Farm',
    icon: Egg ,
    iconClass: 'text-yellow-500',
    children: [
      {
        title: 'Bird Receive',
        href: '/production-firm-receive',
        icon: BabyChick,
        iconClass: 'text-yellow-500',
      },
      {
        title: 'Shed Assign',
        href: '/flock-assign',
        icon: BabyChick,
        iconClass: 'text-yellow-500',
      },
      {
        title: 'Daily Operation',
        href: '/daily-operation/stage/laying',
        icon: BabyChick,
        iconClass: 'text-yellow-500',
      },
      {
        title: 'Egg Classification',
        href: '/production/egg-classification',
        icon: EggOff ,
        iconClass: 'text-yellow-500',
      },
    ]
  },
  {
    title: 'Vaccine',
    icon: Syringe ,
    iconClass: 'text-yellow-500',
    children: [
      {
        title: 'Vaccine Schedule',
        href: '/vaccine-schedule',
        icon: Syringe,
        iconClass: 'text-yellow-500',
      },
    ]
  },

  {
    title: 'User Management',
    icon: Users,
    iconClass: 'text-yellow-500',
    children: [
      {
        title: 'User Register',
        href: '/user-register',
        icon: User,
        iconClass: 'text-yellow-500',
        permission: 'user.view',
      },
      {
        title: 'User Role Management',
        href: '/user-role',
        icon: User,
        iconClass: 'text-yellow-500',
        permission: 'role.view',
      }
    ]
  },
  {
    title: 'Master Setup',
    icon: LayoutGrid,
    iconClass: 'text-yellow-500',
    children: [
        { title: 'Unit', href: '/unit', icon: PencilRuler , iconClass: 'text-yellow-500' },
        { title: 'Feed', href: '/feed', icon: Package, iconClass: 'text-yellow-500' },
        { title: 'Feed Type', href: '/feed-type', icon: Package, iconClass: 'text-yellow-500' },
        { title: 'Shed', href: '/shed', icon: Building, iconClass: 'text-yellow-500' },
        { title: 'Disease', href: '/disease', icon: Skull, iconClass: 'text-yellow-500' },
        { title: 'Medicine', href: '/medicine', icon: Pill, iconClass: 'text-yellow-500' },
        { title: 'Vaccine', href: '/vaccine', icon: Syringe, iconClass: 'text-yellow-500' },
        { title: 'Vaccine Type', href: '/vaccine-type', icon: Syringe,iconClass: 'text-yellow-500' },
        { title: 'Company', href: '/company', icon: Building2 , iconClass: 'text-yellow-500' },
        { title: 'Supplier', href: '/supplier', icon: Users, iconClass: 'text-yellow-500' },
        { title: 'Chicks Type', href: '/chick-type', icon: BabyChick, iconClass: 'text-yellow-500' },
        { title: 'Breed Type', href: '/breed-type', icon: Package, iconClass: 'text-yellow-500' },

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
