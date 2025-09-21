<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { usePermissions } from '@/composables/usePermissions';
import { BabyChick } from '@/icons/BabyChick';
import { BabyChickMultiple } from '@/icons/BabyChickMultiple';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { Building, Building2, Pipette, TestTubes,EggFried,ClockArrowUp ,Egg, EggOff, LayoutGrid, Package, PencilRuler, Pill, Skull, Syringe, User, Users, FileText,Settings  } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

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
        icon: BabyChickMultiple,
        iconClass: 'text-yellow-500',
        children: [
            {
                title: 'Order Planing',
                href: '/order-plans',
                icon: ClockArrowUp ,
                iconClass: 'text-yellow-500',
            },
            {
                title: 'PS Receive',
                href: '/ps-receive',
                icon: BabyChick,
                iconClass: 'text-yellow-500',
            },
            
            {
                title: 'PS Farm Receive',
                href: '/ps-firm-receive',
                icon: BabyChick,
                iconClass: 'text-yellow-500',
            },
        ],
    },
    {
        title: 'Shed',
        icon: Building,
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
                href: '/batch-assign',
                icon: BabyChick,
                iconClass: 'text-yellow-500',
            },
            {
                title: 'Batch Config',
                href: '/batch-config',
                icon: Settings ,
                iconClass: 'text-yellow-500',
            },
            
        ],
    },
    {
        title: 'Farm Operation',
        icon: BabyChickMultiple,
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
        ],
    },
    {
        title: 'Production Farm',
        icon: Egg,
        iconClass: 'text-yellow-500',
        children: [
            {
                title: 'Farm Receive',
                href: '/production-farm-receive',
                icon: BabyChick,
                iconClass: 'text-yellow-500',
            },
            {
                title: 'Shed Receive',
                href: '/production-shed-receive',
                icon: BabyChick,
                iconClass: 'text-yellow-500',
            },
            {
                title: 'Batch Assign',
                href: '/batch-assign',
                icon: BabyChick,
                iconClass: 'text-yellow-500',
            },
            {
                title: 'Laying',
                href: '/daily-operation/stage/laying',
                icon: BabyChick,
                iconClass: 'text-yellow-500',
            },
        ],
    },
    {
        title: 'Egg',
        icon: EggFried,
        iconClass: 'text-yellow-500',
        children: [
            {
                title: 'Egg Classification',
                href: '/production/egg-classification',
                icon: EggOff,
                iconClass: 'text-yellow-500',
            },
            {
                title: 'Egg Grade',
                href: '/egg-classification-grades',
                icon: EggOff,
                iconClass: 'text-yellow-500',
            },
        ],
    },
    {
        title: 'Lab Test',
        icon: TestTubes,
        iconClass: 'text-yellow-500',
        children: [
            {
                title: 'PS Lab Test',
                href: '/ps-lab-test',
                icon: BabyChick,
                iconClass: 'text-yellow-500',
            },
            {
                title: 'Firm Lab Transfer',
                href: '/firm-lab-tests',
                icon: Pipette ,
                iconClass: 'text-yellow-500',
            },
        ],
    },
    {
        title: 'Vaccine',
        icon: Syringe,
        iconClass: 'text-yellow-500',
        children: [
            {
                title: 'Vaccine Schedule',
                href: '/vaccine-schedule',
                icon: Syringe,
                iconClass: 'text-yellow-500',
            },
        ],
    },
    {
        title: 'Report',
        icon: FileText,
        iconClass: 'text-yellow-500',
        children: [
            {
                title: 'Daily-Flock-Report',
                href: '/daily-flock-report',
                icon: FileText,
                iconClass: 'text-yellow-500',
            },
 
            {
                title: 'Transfer and Receive Report',
                href: '/bird-transfer-receive-report',
                icon: FileText,
                iconClass: 'text-yellow-500',
            },
        ],
    },
    {
        title: 'Audit Log',
        icon: FileText,
        iconClass: 'text-yellow-500',
        children: [
            {
                title: 'Audit Log',
                href: '/audit-log',
                icon: FileText,
                iconClass: 'text-yellow-500',
            },
        ],
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
            },
        ],
    },
    {
        title: 'Master Setup',
        icon: LayoutGrid,
        iconClass: 'text-yellow-500',
        children: [
            { title: 'Unit', href: '/unit', icon: PencilRuler, iconClass: 'text-yellow-500' },
            { title: 'Feed', href: '/feed', icon: Package, iconClass: 'text-yellow-500' },
            { title: 'Feed Type', href: '/feed-type', icon: Package, iconClass: 'text-yellow-500' },
            { title: 'Shed', href: '/shed', icon: Building, iconClass: 'text-yellow-500' },
            { title: 'Disease', href: '/disease', icon: Skull, iconClass: 'text-yellow-500' },
            { title: 'Medicine', href: '/medicine', icon: Pill, iconClass: 'text-yellow-500' },
            { title: 'Vaccine', href: '/vaccine', icon: Syringe, iconClass: 'text-yellow-500' },
            { title: 'Vaccine Type', href: '/vaccine-type', icon: Syringe, iconClass: 'text-yellow-500' },
            { title: 'Company', href: '/company', icon: Building2, iconClass: 'text-yellow-500' },
            { title: 'Project', href: '/project', icon: Building2, iconClass: 'text-yellow-500' },
            { title: 'Supplier', href: '/supplier', icon: Users, iconClass: 'text-yellow-500' },
            { title: 'Chicks Type', href: '/chick-type', icon: BabyChick, iconClass: 'text-yellow-500' },
            { title: 'Breed Type', href: '/breed-type', icon: Package, iconClass: 'text-yellow-500' },
        ],
    },
];

const filteredMainNavItems = mainNavItems
    .map((item) => {
        // If parent has children, filter children by permission
        if (item.children) {
            const filteredChildren = item.children.filter((child) => can(child.permission));
            if (filteredChildren.length === 0) return null; // no child has permission => hide parent
            return { ...item, children: filteredChildren };
        }
        // If parent itself has a permission key
        if (can(item.permission)) return item;

        // If parent has no children and no permission => keep by default
        return item.permission ? null : item;
    })
    .filter(Boolean) as NavItem[];

const footerNavItems: NavItem[] = [
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs',
        icon: FileText,
        iconClass: 'text-blue-500',
    },
    {
        title: 'GitHub',
        href: 'https://github.com/laravel/laravel',
        icon: FileText,
        iconClass: 'text-gray-500',
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
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
