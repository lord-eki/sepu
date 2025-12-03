<template>
  <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">
    <Head title="Admin Dashboard" />

    <div class="min-h-screen p-8 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-900 dark:to-gray-950">

    <!-- TOP HEADER -->
<div
  class="rounded-3xl px-8 py-8 mb-10 relative overflow-hidden backdrop-blur-xl
         shadow-[0_8px_35px_rgba(0,0,0,0.15)]
         bg-gradient-to-br from-[#091d39] via-[#0b2549] to-[#0e3264]
         dark:from-[#091d39] dark:via-[#0b2549] dark:to-[#0e3264]
         border border-white/10 dark:border-gray-700/20">

  <div class="relative z-10">
    <h1 class="text-4xl font-black text-white tracking-tight">
      SEPU <span class="text-orange-400">SACCO</span> — Admin Dashboard
    </h1>

    <p class="mt-2 text-gray-200 text-sm tracking-wide">
      Smart insights, real-time operations & intelligent system overview.
    </p>
  </div>

  <!-- floating orb -->
  <div class="absolute right-10 top-6 w-36 h-36 bg-orange-400/20 rounded-full blur-3xl"></div>
</div>


    <!-- QUICK STATS -->
  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-7">
  <div
    v-for="stat in quickStats"
    :key="stat.title"
    class="p-6 rounded-2xl bg-gradient-to-br from-white to-[#f3f4f6]
           dark:from-[#0f1e33] dark:to-[#0a1628]
           shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all cursor-pointer
           border border-gray-200 dark:border-gray-700 flex flex-col gap-4 backdrop-blur-sm"
  >
    <div class="flex justify-between items-center">
      <component :is="stat.icon" class="h-8 w-8 text-orange-500" />
      <span class="text-xs font-medium text-gray-600 dark:text-gray-300">{{ stat.title }}</span>
    </div>

    <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ stat.value }}</h2>
    <p class="text-xs text-gray-500 dark:text-gray-400">{{ stat.sub }}</p>
  </div>
</div>

<!-- PENDING APPROVALS -->
<div class="mt-12">
  <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
    <FileWarning class="h-5 w-5 text-orange-500" />
    Pending Approvals
  </h2>

  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <div
      v-for="item in approvalItems"
      :key="item.label"
      class="p-5 rounded-2xl shadow-lg bg-gradient-to-br from-white to-gray-100
             dark:from-[#0f1e33] dark:to-[#0a1628]
             border border-gray-200 dark:border-gray-700
             hover:shadow-xl hover:-translate-y-1 transition-all cursor-pointer"
      @click="$inertia.visit(item.link)"
    >
      <div class="flex justify-between items-center">
        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">
          {{ item.label }}
        </span>

        <span :class="item.badge">
          {{ item.value }}
        </span>
      </div>

      <p class="text-xs mt-2 text-gray-500 dark:text-gray-400">
        Awaiting approval
      </p>
    </div>
  </div>
</div>


<!-- ACTIVITIES + SYSTEM -->
<div class="grid lg:grid-cols-3 gap-8 mt-12 items-start">

  <!-- RECENT ACTIVITIES -->
  <div class="lg:col-span-2 flex flex-col">
    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
      Recent Activity
    </h2>

    <div
      class="h-[380px] overflow-y-auto rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
             shadow-lg backdrop-blur-lg divide-y divide-gray-100 dark:divide-gray-700 custom-scroll p-4"
    >
      <div
        v-for="(item, i) in recentActivities"
        :key="i"
        class="flex justify-between items-center p-4 hover:bg-gray-50 dark:hover:bg-gray-700/40 transition"
      >
        <div class="flex items-start gap-4">
          <ArrowRightCircle class="h-6 w-6 text-orange-500" />
          <div>
            <p class="text-sm text-gray-800 dark:text-gray-200">{{ item.description }}</p>
            <span class="text-xs text-gray-500 dark:text-gray-400">
              {{ new Date(item.time).toLocaleString() }}
            </span>
          </div>
        </div>
        <span class="text-sm font-bold text-gray-900 dark:text-gray-100">
          Ksh {{ item.amount }}
        </span>
      </div>
    </div>
  </div>

  <!-- SYSTEM OVERVIEW -->
  <div class="flex flex-col">
    <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100 mb-4 flex items-center gap-2">
      <Settings class="h-5 w-5 text-orange-500" /> System Overview
    </h2>

    <ul
      class="h-[380px] overflow-y-auto space-y-4 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
             shadow-lg backdrop-blur-xl p-6 custom-scroll"
    >
      <li class="flex items-center gap-3">
        <Database class="h-5 w-5 text-green-600" />
        <span class="text-gray-800 dark:text-gray-200">Database:</span>
        <span class="font-bold text-green-600 dark:text-green-400">
          {{ systemHealth.database_status }}
        </span>
      </li>

      <li class="flex items-center gap-3">
        <Clock class="h-5 w-5 text-blue-600" />
        <span class="text-gray-800 dark:text-gray-200">Last Backup:</span>
        <span class="text-gray-700 dark:text-gray-400">
          {{ new Date(systemHealth.last_backup).toLocaleString() }}
        </span>
      </li>

      <li class="flex items-center gap-3">
        <Users class="h-5 w-5 text-gray-700 dark:text-gray-300" />
        <span class="text-gray-800 dark:text-gray-200">Active Users:</span>
        <span class="font-bold">{{ systemHealth.active_users }}</span>
      </li>

      <li class="flex items-center gap-3">
        <AlertTriangle class="h-5 w-5 text-red-600" />
        <span class="text-gray-800 dark:text-gray-200">Errors:</span>
        <span class="font-bold text-red-600">{{ systemHealth.system_errors }}</span>
      </li>
    </ul>
  </div>
</div>



      <!-- SETUP SECTION -->
      <div class="mt-14">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
          <Settings class="h-5 w-5 text-orange-500" /> Setup & Configuration
        </h2>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div
            v-for="setup in setupItems"
            :key="setup.title"
            class="p-5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
                   rounded-2xl shadow-lg hover:shadow-xl hover:-translate-y-1 transition cursor-pointer"
            @click="$inertia.visit(setup.link)"
          >
            <div class="flex justify-between">
              <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ setup.title }}</h3>
              <component :is="setup.icon" class="h-6 w-6 text-orange-500" />
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
              {{ setup.desc }}
            </p>
          </div>
        </div>
      </div>

      <!-- FOOTER -->
      <footer class="text-center mt-10 text-xs text-gray-500 dark:text-gray-400">
        © {{ new Date().getFullYear() }} SEPU SACCO — Smart Cooperative Admin Suite.
      </footer>
    </div>
  </AppLayout>
</template>


<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Activity, Users, Banknote, FileWarning, ArrowRightCircle, Clock, Database, AlertTriangle, Settings, ShieldCheck, BarChart3, Handshake } from 'lucide-vue-next'

const props = defineProps([
  'stats', 'recentActivities', 'pendingApprovals', 'systemHealth'
])

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
    value: props.stats.financial.total_share_deposits?.toLocaleString(),
    sub: `Share Capital: ${props.stats.financial.total_share_capital?.toLocaleString()}`,
    icon: Banknote,
    color: 'bg-orange-100 text-orange-600 dark:bg-orange-700/30 dark:text-orange-300'
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
    label: 'Pending Activation',
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

<style scoped>
.custom-scroll::-webkit-scrollbar {
  width: 6px;
}
.custom-scroll::-webkit-scrollbar-thumb {
  background-color: #f97316;
  border-radius: 9999px;
}
.custom-scroll::-webkit-scrollbar-track {
  background-color: #f5f7fb;
}

.animate-fadeIn {
  animation: fadeIn 0.5s ease-in-out;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
