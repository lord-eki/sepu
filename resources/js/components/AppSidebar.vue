<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    Folder,
    LayoutGrid,
    HandCoins,
    ClipboardList,
    Ticket,
    BriefcaseConveyorBelt,
    Users,
    NotebookTabs,
    FileText,
    User,
    ArrowRightLeft,
    Calculator,
    FileSignature,
} from 'lucide-vue-next';
import type { LucideIcon } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import type { AppPageProps } from '@/types';

/**
 * ---- Types ----
 */
export interface NavItem {
    title: string;
    href?: string;
    icon?: LucideIcon;
    isActive?: boolean;
    children?: NavItem[];
}

/**
 * ---- Navigation Items ----
 */

// Member navigation
const memberNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Accounts',
        href: '/my-accounts',
        icon: NotebookTabs
    },
    {
        title: 'Loans',
        icon: BriefcaseConveyorBelt,
        children: [
            {
                title: 'My Loans',
                href: '/my-loans',
                icon: FileText,
            },
            {
                title: 'Loan Calculator',
                href: '/loan-calculator',
                icon: Calculator,
            },
            {
                title: 'Loan Application',
                href: '/loans/create',
                icon: FileSignature,
            },
            
        ]
    },
    {
        title: 'Transactions',
        href: '/my-transactions',
        icon: ArrowRightLeft,
    },
    {
        title: 'Dividends',
        href: '/my-dividends',
        icon: HandCoins,
    },
    {
        title: 'My Profile',
        href: '/profile/show',
        icon: User,
    }
];

// Admin navigation
const adminNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Dividends',
        href: '/dividends',
        icon: HandCoins,
    },
    {
        title: 'Budgets',
        href: '/budgets',
        icon: ClipboardList,
    },
    {
        title: 'Payment Vouchers',
        href: '/vouchers',
        icon: Ticket,
    },
    {
        title: 'Loans',
        href: '/loans',
        icon: BriefcaseConveyorBelt,
    },
    {
        title: 'Members',
        href: '/members',
        icon: Users,
    },
    {
        title: 'Accounts',
        href: '/accounts',
        icon: NotebookTabs
    },
    {
        title: 'Transactions',
        href: '/transactions',
        icon: ArrowRightLeft,
    }
];

/**
 * ---- Role detection ----
 */
const page = usePage<AppPageProps>();
const userRole = page.props.auth.user.role;
const isAdmin = userRole === 'admin';

const footerNavItems: NavItem[] = [
    {
        title: 'Terms of Service',
        href: '/terms',
        icon: Folder,
    },
    {
        title: 'About Us',
        href: '/about',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
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

        <SidebarContent>
            <NavMain :items="isAdmin ? adminNavItems : memberNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>