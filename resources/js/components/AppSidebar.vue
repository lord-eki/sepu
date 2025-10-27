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
    Bell,
    Settings,
    Package,
    UserCog,
    Shield,
} from 'lucide-vue-next';
import type { LucideIcon } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import type { AppPageProps } from '@/types';

const page = usePage<AppPageProps>()
const user = page.props.auth.user
const member = page.props.auth?.member ?? null




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
    href: route('dashboard'),
    routeName: 'dashboard',
    icon: LayoutGrid,
  },
  {
    title: 'Accounts',
    href: member ? route('members.accounts', member.id) : '#',
    routeName: 'members.accounts',
    icon: NotebookTabs,
  },
  {
    title: 'Loans',
    icon: BriefcaseConveyorBelt,
    children: [
      {
        title: 'My Loans',
        href: member ? route('members.loans', member.id) : '#',
        routeName: 'members.loans',
        icon: FileText,
      },
      {
        title: 'Loan Calculator',
        href: route('loan-calculator.index'),
        routeName: 'loan-calculator.index',
        icon: Calculator,
        },

      {
        title: 'Loan Application',
        href: route('loans.create'),
        routeName: 'loans.create',
        icon: FileSignature,
      },
    ],
  },
  {
    title: 'Transactions',
    href: member ? route('members.transactions', member.id) : '#',
    routeName: 'members.transactions',
    icon: ArrowRightLeft,
  },
  {
    title: 'Dividends',
    href: member ? route('members.dividends', member.id) : '#',
    routeName: 'members.dividends',
    icon: HandCoins,
  },
  {
    title: 'My Profile',
    href: route('member.profile'),
    routeName: 'member.profile',
    icon: User,
  },
];


// Admin navigation
const adminNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: route('dashboard'),
    routeName: 'dashboard',
    icon: LayoutGrid,
  },
  {
    title: 'Dividends',
    href: route('dividends.index'),
    routeName: 'dividends.index',
    icon: HandCoins,
  },
  {
    title: 'Budgets',
    href: route('budgets.index'),
    routeName: 'budgets.index',
    icon: ClipboardList,
  },
  {
    title: 'Vouchers',
    href: route('vouchers.index'),
    routeName: 'vouchers.index',
    icon: Ticket,
  },
  {
    title: 'Loans',
    icon: BriefcaseConveyorBelt,
    children: [
      {
        title: 'Loans Overview',
        href: route('loans.index'),
        routeName: 'loans.index',
        icon: FileText,
      },
      {
        title: 'Loan Products',
        href: route('loan-products.index'),
        routeName: 'loan-products.index',
        icon: Package,
      },
      {
        title: 'Loan Calculator',
        href: route('loan-calculator.index'),
        routeName: 'loan-calculator.index',
        icon: Calculator,
      },
      {
        title: 'Loan Application',
        href: route('loans.create'),
        routeName: 'loans.create',
        icon: FileSignature,
      },
    ],
  },
  {
    title: 'Members',
    href: route('members.index'),
    routeName: 'members.index',
    icon: Users,
  },
  {
    title: 'Accounts',
    href: route('accounts.index'),
    routeName: 'accounts.index',
    icon: NotebookTabs,
  },
  {
    title: 'Transactions',
    href: route('transactions.index'),
    routeName: 'transactions.index',
    icon: ArrowRightLeft,
  },
  {
    title: 'Reports',
    href: route('reports.index'),
    routeName: 'reports.index',
    icon: FileText,
  },
  {
    title: 'System Users',
    href: '',
    routeName: 'system-users.index',
    icon: UserCog,
    children: [
      {
        title: 'All Users',
        href: '',
        routeName: '',
        icon: Users,
      },
      {
        title: 'Roles & Permissions',
        href: '',
        routeName: '',
        icon: Shield,
      },
    ],
  },
  {
    title: 'Notifications',
    href: '/na',
    routeName: '#',
    icon: Bell,
  },
  {
    title: 'Settings',
    href: '/na',
    routeName: '#',
    icon: Settings,
  },
];

/**
 * ---- Role detection ----
 */

const userRole = page.props.auth.user.role;
const isAdmin = userRole === 'admin';

const footerNavItems: NavItem[] = [
   
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