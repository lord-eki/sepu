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
  ContactRound,
  UserCheck,
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
  Banknote,
  BookMarked,
  Scale,
  ReceiptText,
} from 'lucide-vue-next';
import type { LucideIcon } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import type { AppPageProps } from '@/types';

const page = usePage<AppPageProps>()
const user = page.props?.auth?.user ?? null
const member = page.props?.auth?.member ?? null




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
          title: 'My Guarantees',  
          href: route('my-guarantees'),
          routeName: 'my-guarantees',
          icon: Shield,
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
          title: 'Loan Guarantors',
          href: route('loans.all-guarantors'),
          routeName: 'loans.all-guarantors',
          icon: Shield,
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
    icon: Users,
    children: [
      {
        title: 'Members Overview',
        href: route('members.index'),
        routeName: 'members.index',
        icon: ContactRound,
      },
      {
        title: 'Member Approval',
        href: route('admin.pending-members'),
        routeName: 'admin.pending-members',
        icon: UserCheck,
      },
    ],
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
    title: 'Finance',
    icon: Banknote,
    children: [

      {
        title: 'Chart of Accounts',
        href: route('chart-of-accounts.index'),
        routeName: 'chart-of-accounts.index',
        icon: BookMarked,
      },
    ],
  },
  {
    title: 'Schedule',
    icon: ClipboardList,
    href: route('schedule.index'),
    routeName: 'schedule.index',
  },
   {
    title: 'System Users',
    icon: UserCog,
    children: [
      {
        title: 'All Users',
        href: route('system-users.index'),
        routeName: 'system-users.index',
        icon: Users,
      },
      {
        title: 'Roles & Permissions',
        href: route('system-users.roles'),
        routeName: 'system-users.roles',
        icon: Shield,
      },
    ],
  },
  {
    title: 'Reports',
    icon: FileText,
    children: [


      {
        title: 'Reports Dashboard',
        href: route('reports.index'),
        routeName: 'reports.index',
        icon: FileText,
      },

      {
        title: 'Financial Reports',
        href: route('reports.financial.index'),
        routeName: 'reports.financial.index',
        icon: Calculator,
      },
      {
        title: 'Member Reports',
        href: route('reports.membersReport.index'),
        routeName: 'reports.membersReport.index',
        icon: Users,
      },
      {
        title: 'Loan Reports',
        href: route('reports.loansReport.index'),
        routeName: 'reports.loansReport.index',
        icon: BriefcaseConveyorBelt,
      },
      {
        title: 'Transaction Reports',
        href: route('reports.transactionsReport.index'),
        routeName: 'reports.transactionsReport.index',
        icon: ArrowRightLeft,
      },
      {
        title: 'Regulatory Reports',
        href: route('reports.regulatoryReport.index'),
        routeName: 'reports.regulatoryReport.index',
        icon: Shield,
      },
      {
        title: 'Custom Reports',
        href: route('reports.custom.builder'),
        routeName: 'reports.custom.builder',
        icon: Folder,
      },
    ],
  },
  {
    title: 'Notifications',
    href: route('notifications.index'),
    routeName: 'notifications.index',
    icon: Bell,
  },
  {
    title: 'System Settings',
    href: route('admin.settings.index'),
    routeName: 'admin.settings.index',
    icon: Settings,
  },

];

/**
 * ---- Role detection ----
 */

const userRole = user?.role ?? null
const isAdmin = userRole === 'admin'

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