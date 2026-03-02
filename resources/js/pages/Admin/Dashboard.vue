<template>
  <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">

    <Head title="Admin Dashboard" />

    <div
      class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-gray-950 dark:via-gray-900 dark:to-gray-950">

      <!-- TOP BAR -->
      <div
        class="relative px-8 py-6 bg-gradient-to-r from-blue-900 to-orange-500 rounded-b-3xl overflow-hidden dark:from-blue-800 dark:to-orange-600">
        <div class="absolute inset-0 bg-white/10 backdrop-blur-sm dark:bg-gray-800/20"></div>
        <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
          <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">
              SEPU <span class="text-orange-400 dark:text-orange-300">SACCO</span>
            </h1>
            <p class="text-sm md:text-base text-white/70 dark:text-white/60 mt-1">
              Administrative Financial Overview
            </p>
          </div>
          <div class="flex items-center gap-6">
            <div class="text-sm text-white/80 dark:text-white/60">
              {{ new Date().toDateString() }}
            </div>
            <div
              class="w-10 h-10 rounded-full bg-white/20 dark:bg-gray-700/30 text-white flex items-center justify-center font-semibold shadow-lg">
              A
            </div>
          </div>
        </div>
      </div>

      <!-- KPI CARDS -->
      <div class="px-8 py-10 grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
        <div v-for="stat in quickStats" :key="stat.title"
          class="relative p-6 rounded-3xl bg-white/70 dark:bg-gray-800/50 backdrop-blur-xl border border-gray-200 dark:border-gray-700 shadow-xl hover:shadow-2xl hover:scale-[1.02] transition-all duration-500 cursor-pointer">

          <div class="flex justify-between items-center">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-300">{{ stat.title }}</div>
            <div class="p-3 rounded-xl bg-gradient-to-br from-orange-400 to-orange-200 text-white shadow-sm">
              <component :is="stat.icon" class="h-5 w-5" />
            </div>
          </div>

          <div class="mt-5">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ stat.value }}</h2>
            <p class="text-xs text-gray-500 dark:text-gray-300 mt-1">{{ stat.sub }}</p>
          </div>

          <div class="absolute -top-5 -right-5 w-16 h-16 bg-blue-100/40 dark:bg-blue-800/30 rounded-3xl blur-2xl"></div>
          <div class="absolute -bottom-5 -left-5 w-16 h-16 bg-orange-100/40 dark:bg-orange-700/30 rounded-3xl blur-2xl">
          </div>
        </div>
      </div>

      <!-- MAIN GRID -->
      <div class="px-8 py-4 grid grid-cols-1 xl:grid-cols-3 gap-8">

        <!-- LEFT: Pending & Activity -->
        <div class="xl:col-span-2 space-y-8">

          <!-- Pending Approvals -->
          <div
            class="bg-white/70 dark:bg-gray-800/50 backdrop-blur-xl border border-gray-200 dark:border-gray-700 rounded-3xl p-6 shadow-lg hover:shadow-2xl transition-all duration-500">
            <div class="flex justify-between items-center mb-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Pending Approvals</h3>
              <span class="text-xs text-gray-400 dark:text-gray-300 uppercase tracking-wide">Quick Review</span>
            </div>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
              <div v-for="item in approvalItems" :key="item.label" @click="$inertia.visit(item.link)"
                class="cursor-pointer rounded-2xl p-4 border border-gray-200 dark:border-gray-700 hover:border-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20 transition flex flex-col gap-2">
                <div class="flex justify-between items-center">
                  <span class="text-sm text-blue-900 dark:text-gray-300">{{ item.label }}</span>
                  <span class="font-bold text-orange-500">{{ item.value }}</span>
                </div>
                <div class="text-xs text-gray-400 dark:text-gray-300 mt-1">Click to review →</div>
              </div>
            </div>
          </div>

          <!-- Recent Activity -->
<div
  class="bg-white/70 dark:bg-gray-800/50 backdrop-blur-xl border border-gray-200 dark:border-gray-700 rounded-3xl p-6 shadow-lg hover:shadow-2xl transition-all duration-500">

  <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
    Recent Activity
  </h3>

  <div class="space-y-4 max-h-[450px] overflow-y-auto overflow-x-hidden custom-scroll">

    <div
      v-for="(item, i) in recentActivities"
      :key="i"
      @click="item.link ? $inertia.visit(item.link) : null"
      class="flex justify-between items-start gap-4 p-4 rounded-xl border transition-all duration-300 cursor-pointer hover:shadow-md"
      :class="[
        item.status === 'pending'
          ? 'border-orange-200 dark:border-orange-900 hover:bg-orange-50 dark:hover:bg-orange-900/20'
          : item.status === 'approved'
            ? 'border-green-200 dark:border-green-700 hover:bg-green-50 dark:hover:bg-green-900/20'
            : item.status === 'rejected'
              ? 'border-red-200 dark:border-red-700 hover:bg-red-50 dark:hover:bg-red-900/20'
              : 'border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-900/20'
      ]"
    >

      <!-- LEFT CONTENT -->
      <div class="min-w-0 flex-1">

        <!-- Description -->
        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 break-words">
          {{ item.description }}
        </p>

        <!-- Meta Row -->
        <div class="flex flex-wrap items-center gap-2 mt-2 text-xs text-gray-400 dark:text-gray-400">

          <span>
            {{ new Date(item.time).toLocaleString() }}
          </span>

          <!-- Status Badge -->
          <span
            v-if="item.status"
            :class="[
              'px-2 py-0.5 rounded-full font-semibold text-xs whitespace-nowrap',
              item.status === 'pending'
                ? 'bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-300'
                : item.status === 'approved'
                  ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-300'
                  : item.status === 'rejected'
                    ? 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-300'
                    : ''
            ]"
          >
            {{ item.status }}
          </span>

          <!-- Click to View -->
          <span
            v-if="item.status === 'pending'"
            class="text-orange-500 dark:text-orange-300 font-semibold whitespace-nowrap"
          >
            Click to view →
          </span>

        </div>

      </div>

      <!-- RIGHT SIDE (Amount) -->
      <div class="flex-shrink-0 text-sm font-semibold text-gray-700 dark:text-gray-300 text-right whitespace-nowrap">
        <span v-if="item.amount">
          Ksh {{ Number(item.amount).toLocaleString() }}
        </span>
      </div>

    </div>

  </div>
</div>
        </div>

        <!-- RIGHT: System & Tools -->
        <div class="space-y-8">

          <!-- System Health -->
          <div
            class="bg-white/80 dark:bg-gray-800/50 backdrop-blur-xl border border-gray-200 dark:border-gray-700 rounded-3xl p-6 shadow-lg hover:shadow-2xl transition-all duration-500">
            <h3 class="text-lg font-semibold mb-6 text-gray-900 dark:text-white">System Health</h3>
            <div class="space-y-4 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-300">Database</span>
                <span class="text-green-600 dark:text-green-700 font-semibold">{{ systemHealth.database_status }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-300">Active Users</span>
                <span class="font-bold dark:text-white">{{ systemHealth.active_users }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-300">System Errors</span>
                <span class="text-red-400 dark:text-red-500 font-semibold">{{ systemHealth.system_errors }}</span>
              </div>
            </div>
          </div>

          <!-- Admin Tools -->
          <div
            class="bg-white/70 dark:bg-gray-800/50 backdrop-blur-xl border border-gray-200 dark:border-gray-700 rounded-3xl p-6 shadow-lg hover:shadow-2xl transition-all duration-500">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Administrative Tools</h3>
            <div class="space-y-4">
              <div v-for="setup in setupItems" :key="setup.title" @click="$inertia.visit(setup.link)"
                class="flex justify-between items-center p-3 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-orange-400 dark:hover:border-orange-500 hover:bg-orange-50 dark:hover:bg-orange-900/20 cursor-pointer transition hover:scale-[1.02]">
                <span class="text-sm text-gray-600 dark:text-gray-300">{{ setup.title }}</span>
                <component :is="setup.icon" class="h-5 w-5 text-orange-500 dark:text-orange-300" />
              </div>
            </div>
          </div>

        </div>

      </div>

      <!-- Footer -->
      <div
        class="text-center text-xs text-gray-400 dark:text-gray-500 pt-6 border-t border-gray-200 dark:border-gray-700">
        © {{ new Date().getFullYear() }} SEPU SACCO — Administrative Suite
      </div>

    </div>
  </AppLayout>
</template>

<style scoped>
.custom-scroll::-webkit-scrollbar {
  width: 6px;
}

.custom-scroll::-webkit-scrollbar-thumb {
  background-color: rgba(248, 113, 24, 0.4);
  border-radius: 9999px;
}

.custom-scroll::-webkit-scrollbar-track {
  background-color: transparent;
}

.animate-fadeIn {
  animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }

  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>


<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Activity, Users, Banknote, FileWarning, ArrowRightCircle, Clock, Database, AlertTriangle, Settings, ShieldCheck, BarChart3, Handshake } from 'lucide-vue-next'

const props = defineProps([
  'stats', 'recentActivities', 'pendingApprovals', 'systemHealth'
])

const formatMoney = (num) => {
  if (num == null) return 'Ksh 0';
  return 'Ksh ' + Number(num).toLocaleString();
};


const quickStats = [
  {
    title: 'Total Members',
    value: props.stats.members.total,
    sub: `Active: ${props.stats.members.active}`,
    icon: Users,
    color: 'bg-blue-900/10 text-blue-900 dark:bg-blue-900/30 dark:text-blue-300'
  },
  {
    title: 'Total Share Deposits',
    value: formatMoney(props.stats.financial.total_share_deposits),
    sub: `Share Capital: ${formatMoney(props.stats.financial.total_share_capital)}`,
    icon: Banknote,
  },
  {
    title: 'Active Loans',
    value: props.stats.loans.active_loans,
    sub: `Pending: ${props.stats.loans.pending_applications}`,
    icon: Activity,
    color: 'bg-blue-900/10 text-blue-900 dark:bg-blue-900/30 dark:text-blue-300'
  },
  {
    title: 'Transactions Today',
    value: props.stats.transactions.today,
    sub: `This Month: ${props.stats.transactions.this_month}`,
    icon: FileWarning,
    color: 'bg-orange-100 text-orange-600 dark:bg-orange-700/30 dark:text-orange-300'
  }
]


const approvalItems = [
  {
    label: 'Loans',
    value: props.pendingApprovals.loans,
    badge: 'px-2 pb-0.5 rounded-lg bg-orange-100 text-orange-700 font-semibold dark:bg-orange-700/30 dark:text-orange-300',
    link: '/loans'
  },
  {
    label: 'Vouchers',
    value: props.pendingApprovals.vouchers,
    badge: 'px-2 py-0.5 rounded-lg bg-blue-900/10 text-blue-900 font-semibold dark:bg-blue-900/40 dark:text-blue-300',
    link: '/vouchers'
  },
  {
    label: 'Members',
    value: props.pendingApprovals.member_applications,
    badge: 'px-2 py-0.5 rounded-lg bg-green-100 text-green-700 font-semibold dark:bg-green-700/30 dark:text-green-300',
    link: '/admin/pending-members'
  },
  {
    label: 'Member activation',
    value: props.pendingApprovals.pending_activation,
    badge: 'px-2 py-0.5 rounded-lg bg-yellow-100 text-yellow-700 font-semibold dark:bg-yellow-700/30 dark:text-yellow-300',
    link: '/admin/pending-members'
  }
]


const setupItems = [
  {
    title: 'Loan Rates',
    desc: 'Define loan interest and limits',
    icon: BarChart3,
    color: 'bg-orange-100 text-orange-600 dark:bg-orange-700/30 dark:text-orange-300',
    link: '/admin/settings/loan'
  },
  {
    title: 'Repayment Periods',
    desc: 'Set repayment durations',
    icon: Clock,
    color: 'bg-blue-900/10 text-blue-900 dark:bg-blue-900/30 dark:text-blue-300',
    link: '/admin/settings/loan'
  },
  {
    title: 'System Users',
    desc: 'Manage user roles',
    icon: Users,
    color: 'bg-green-100 text-green-700 dark:bg-green-700/30 dark:text-green-300',
    link: '/system-users'
  },
  {
    title: 'System Approvals',
    desc: 'Define workflows',
    icon: ShieldCheck,
    color: 'bg-blue-100 text-blue-800 dark:bg-blue-800/30 dark:text-blue-300',
    link: '/admin/settings'
  }
]

</script>
