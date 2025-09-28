<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { usePermissions } from '@/composables/usePermissions';
import { BabyChick } from '@/icons/BabyChick';
import { BabyChickMultiple } from '@/icons/BabyChickMultiple';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import {
    Building,
    Building2,
    CheckCircle,
    ClockArrowUp,
    Egg,
    EggFried,
    EggOff,
    FileText,
    LayoutGrid,
    Package,
    PencilRuler,
    Pill,
    Pipette,
    Settings,
    Skull,
    Syringe,
    TestTubes,
    User,
    Users,
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const { can } = usePermissions();

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
                icon: ClockArrowUp,
                iconClass: 'text-yellow-500',
                permission: 'order-plans.view',
            },
            {
                title: 'PS Receive',
                href: '/ps-receive',
                icon: BabyChick,
                iconClass: 'text-yellow-500',
                permission: 'ps-receive.view',
            },
            {
                title: 'PS Farm Receive',
                href: '/ps-firm-receive',
                icon: BabyChick,
                iconClass: 'text-yellow-500',
                permission: 'ps-firm-receive.view',
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
                permission: 'shed-receive.view',
            },
            {
                title: 'Batch Assign',
                href: '/batch-assign',
                icon: BabyChick,
                iconClass: 'text-yellow-500',
                permission: 'batch-assign.view',
            },
            {
                title: 'Batch Config',
                href: '/batch-config',
                icon: Settings,
                iconClass: 'text-yellow-500',
                permission: 'batch-config.view',
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
                permission: 'brooding.view',
            },
            {
                title: 'Growing',
                href: '/daily-operation/stage/growing',
                icon: BabyChick,
                iconClass: 'text-yellow-500',
                permission: 'growing.view',
            },
            {
                title: 'Bird Transfer',
                href: '/bird-transfer',
                icon: BabyChick,
                iconClass: 'text-yellow-500',
                permission: 'bird-transfer.view',
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
                permission: 'production-farm-receive.view',
            },
            {
                title: 'Shed Receive',
                href: '/production-shed-receive',
                icon: BabyChick,
                iconClass: 'text-yellow-500',
                permission: 'production-shed-receive.view',
            },
            {
                title: 'Batch Assign',
                href: '/batch-assign',
                icon: BabyChick,
                iconClass: 'text-yellow-500',
                permission: 'batch-assign.view',
            },
            {
                title: 'Laying',
                href: '/daily-operation/stage/laying',
                icon: BabyChick,
                iconClass: 'text-yellow-500',
                permission: 'laying.view',
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
                permission: 'egg-classification.view',
            },
            {
                title: 'Egg Grade',
                href: '/egg-classification-grades',
                icon: EggOff,
                iconClass: 'text-yellow-500',
                permission: 'egg-grade.view',
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
                permission: 'ps-lab-test.view',
            },
            {
                title: 'Firm Lab Transfer',
                href: '/firm-lab-tests',
                icon: Pipette,
                iconClass: 'text-yellow-500',
                permission: 'firm-lab-tests.view',
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
                permission: 'vaccine-schedule.view',
            },
            {
                title: 'Upcomming Vaccine',
                href: '/upcomming-vaccine',
                icon: Syringe,
                iconClass: 'text-yellow-500',
                permission: 'upcomming-vaccine.view',
            },
            {
                title: 'Vaccine Routing',
                href: '/vaccine-routing',
                icon: Syringe,
                iconClass: 'text-yellow-500',
                permission: 'vaccine-routing.view',
            },
            {
                title: 'Vaccine ',
                href: '/vaccine',
                icon: Syringe,
                iconClass: 'text-yellow-500',
                permission: 'vaccine.view',
            },
            {
                title: 'Vaccine Type ',
                href: '/vaccine-type',
                icon: Syringe,
                iconClass: 'text-yellow-500',
                permission: 'vaccine-type.view',
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
                permission: 'daily-flock-report.view',
            },
            {
                title: 'Transfer and Receive Report',
                href: '/bird-transfer-receive-report',
                icon: FileText,
                iconClass: 'text-yellow-500',
                permission: 'bird-transfer-receive-report.view',
            },
            {
                title: 'Egg Receive & Grading Report',
                href: '/egg-receive-and-grading-report',
                icon: FileText,
                iconClass: 'text-yellow-500',
                permission: 'egg-receive-and-grading-report.view',
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
                permission: 'audit-log.view',
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
            { title: 'Unit', href: '/unit', icon: PencilRuler, iconClass: 'text-yellow-500', permission: 'unit.view' },
            { title: 'Feed', href: '/feed', icon: Package, iconClass: 'text-yellow-500', permission: 'feed.view' },
            { title: 'Feed Type', href: '/feed-type', icon: Package, iconClass: 'text-yellow-500', permission: 'feed-type.view' },
            { title: 'Shed', href: '/shed', icon: Building, iconClass: 'text-yellow-500', permission: 'shed.view' },
            { title: 'Disease', href: '/disease', icon: Skull, iconClass: 'text-yellow-500', permission: 'disease.view' },
            { title: 'Medicine', href: '/medicine', icon: Pill, iconClass: 'text-yellow-500', permission: 'medicine.view' },
            { title: 'Company', href: '/company', icon: Building2, iconClass: 'text-yellow-500', permission: 'company.view' },
            { title: 'Project', href: '/project', icon: Building2, iconClass: 'text-yellow-500', permission: 'project.view' },
            { title: 'Supplier', href: '/supplier', icon: Users, iconClass: 'text-yellow-500', permission: 'supplier.view' },
            { title: 'Chicks Type', href: '/chick-type', icon: BabyChick, iconClass: 'text-yellow-500', permission: 'chick-type.view' },
            { title: 'Breed Type', href: '/breed-type', icon: Package, iconClass: 'text-yellow-500', permission: 'breed-type.view' },
            {
                title: 'Approval Matrix',
                href: '/approval-matrix-config',
                icon: CheckCircle,
                iconClass: 'text-yellow-500',
                permission: 'approval-matrix-config.view',
            },
        ],
    },
];

// Recursive filtering function
function filterNavItems(items: NavItem[]): NavItem[] {
    return items
        .map((item) => {
            if (item.children) {
                const filteredChildren = filterNavItems(item.children).filter((child) => can(child.permission));
                if (filteredChildren.length === 0) return null; // hide parent if no children accessible
                return { ...item, children: filteredChildren };
            }
            if (can(item.permission)) return item;
            return item.permission ? null : item; // hide if permission required but not present
        })
        .filter(Boolean) as NavItem[];
}

const filteredMainNavItems = filterNavItems(mainNavItems);

const footerNavItems: NavItem[] = [
    { title: 'Documentation', href: 'https://laravel.com/docs', icon: FileText, iconClass: 'text-blue-500' },
    { title: 'GitHub', href: 'https://github.com/laravel/laravel', icon: FileText, iconClass: 'text-gray-500' },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link href="/dashboard">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="filteredMainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
