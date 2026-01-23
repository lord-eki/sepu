<template>
  <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">

    <Head title="Admin Dashboard" />

    <div class="min-h-screen p-4 sm:p-6 bg-gradient-to-br 
      from-gray-100 to-gray-200 
      dark:from-gray-900 dark:to-gray-950">

      <!-- TOP HEADER -->
      <div class="rounded-3xl px-6 sm:px-8 py-8 mb-10 relative overflow-hidden backdrop-blur-xl
        shadow-[0_8px_35px_rgba(0,0,0,0.15)]
        bg-gradient-to-br from-[#091d39] via-[#0b2549] to-[#0e3264]
        border border-white/10 dark:border-gray-700/20">
        <div class="relative z-10">
          <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight leading-tight">
            SEPU <span class="text-orange-400">SACCO</span> — Admin Dashboard
          </h1>

          <p class="mt-2 text-gray-200 text-sm sm:text-base tracking-wide">
            Smart insights, real-time operations & intelligent system overview.
          </p>
        </div>

        <!-- floating orb -->
        <div class="absolute right-6 sm:right-10 top-6 w-24 sm:w-36 h-24 sm:h-36 
          bg-orange-400/20 rounded-full blur-3xl"></div>
      </div>


      <!-- QUICK STATS -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-7">
        <div v-for="stat in quickStats" :key="stat.title" class="p-5 sm:p-6 rounded-2xl bg-gradient-to-br from-white to-[#f3f4f6]
          dark:from-[#0f1e33] dark:to-[#0a1628]
          shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all cursor-pointer
          border border-gray-200 dark:border-gray-700 flex flex-col gap-4 backdrop-blur-sm">
          <div class="flex justify-between items-center">
            <component :is="stat.icon" class="h-7 w-7 sm:h-8 sm:w-8 text-orange-500" />
            <span class="text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-300">
              {{ stat.title }}
            </span>
          </div>

          <h2 class="text-xl sm:text-2xl font-semibold text-[#0a2342] dark:text-white">
            {{ stat.value }}
          </h2>

          <p class="text-xs sm:text-base text-gray-500 dark:text-gray-400">
            {{ stat.sub }}
          </p>
        </div>
      </div>

      <!-- PENDING APPROVALS -->
      <div class="mt-10 sm:mt-12">
        <h2
          class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-gray-100 mb-3 sm:mb-4 flex items-center gap-2">
          <FileWarning class="h-5 w-5 text-orange-500" />
          Pending Approvals
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
          <div v-for="item in approvalItems" :key="item.label" class="group relative p-5 rounded-2xl shadow-lg 
            bg-gradient-to-br from-white to-gray-100
            dark:from-[#0f1e33] dark:to-[#0a1628]
            border border-gray-200 dark:border-gray-700
            hover:shadow-xl hover:-translate-y-1 transition-all cursor-pointer" @click="$inertia.visit(item.link)">

            <!-- Tooltip (mobile-safe: positioned above instead of below) -->
            <div class="absolute -top-6 left-1/2 -translate-x-1/2 
                px-2 sm:px-3 py-1 text-[10px] sm:text-xs rounded-lg 
                bg-black/80 text-white opacity-0 group-hover:opacity-100 
                pointer-events-none transition-opacity whitespace-nowrap">
              Click to see
              {{
    item.label === 'Member Activation'
      ? 'members'
      : item.label.toLowerCase()
  }}
              {{
      item.label === 'Member Activation'
        ? 'awaiting activation'
        : 'awaiting approval'
    }}
            </div>

            <div class="flex justify-between items-center">
              <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                {{ item.label }}
              </span>

              <span :class="item.badge">
                {{ item.value }}
              </span>
            </div>

            <p v-if="item.label === 'Member activation'" class="text-xs mt-2 text-gray-500 dark:text-gray-400">
              Awaiting activation
            </p>

            <p v-else class="text-xs mt-2 text-gray-500 dark:text-gray-400">
              Awaiting approval
            </p>
          </div>
        </div>
      </div>



      <!-- ACTIVITIES + SYSTEM -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8 mt-10 sm:mt-12 items-start">

        <!-- RECENT ACTIVITY -->
        <div class="lg:col-span-2 flex flex-col">
          <h2 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-gray-100 mb-3 sm:mb-4">
            Recent Activities
          </h2>

          <div class="h-[300px] sm:h-[380px] overflow-y-auto rounded-2xl bg-white dark:bg-gray-800
            border border-gray-200 dark:border-gray-700 shadow-lg backdrop-blur-lg 
            divide-y divide-gray-100 dark:divide-gray-700 custom-scroll p-4">
            <div
                v-for="(item, i) in recentActivities"
                :key="i"
                class="flex justify-between items-center p-3 sm:p-4 rounded-xl transition"
                :class="[
                  item.status === 'pending'
                    ? 'cursor-pointer hover:bg-orange-50 dark:hover:bg-orange-900/20 mb-1 ring-1 ring-orange-100 dark:ring-orange-700/30'
                    : 'hover:bg-gray-50 dark:hover:bg-gray-700/40'
                ]"
                @click="item.status === 'pending' && item.link ? $inertia.visit(item.link) : null"
              >

              <div class="flex items-start gap-3 sm:gap-4">
                <ArrowRightCircle class="h-5 w-5 sm:h-6 sm:w-6 text-gray-600" />
                <div>
                  <p class="text-sm text-gray-800 dark:text-gray-200">
                    {{ item.description }}
                  </p>
                  <!-- STATUS BADGE -->
                  <span
                    v-if="item.status"
                    class="inline-block mt-1 px-2 py-0.5 text-[10px] font-semibold rounded-full"
                    :class="{
                      'bg-orange-100 text-orange-700 dark:bg-orange-700/30 dark:text-orange-300': item.status === 'pending',
                      'bg-green-100 text-green-700 dark:bg-green-700/30 dark:text-green-300': item.status === 'completed',
                      'bg-blue-100 text-blue-700 dark:bg-blue-700/30 dark:text-blue-300': item.status === 'approved',
                      'bg-red-100 text-red-700 dark:bg-red-700/30 dark:text-red-300': item.status === 'failed',
                      'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300': item.status === 'reversed'
                    }"
                  >
                    {{ item.status }}
                  </span>
                  <span class="text-xs text-gray-500 dark:text-gray-400">
                    {{ new Date(item.time).toLocaleString() }}
                  </span>
                </div>
              </div>

              <span
                  v-if="item.amount"
                  class="text-sm font-bold"
                  :class="{
                    'text-orange-600 dark:text-orange-400': item.status === 'pending',
                    'text-green-600 dark:text-green-400': item.status === 'completed',
                    'text-red-600 dark:text-red-400': item.status === 'failed',
                    'text-gray-700 dark:text-gray-300': item.status === 'reversed'
                  }"
                >
                  Ksh {{ item.amount }}
                </span>

            </div>
          </div>
        </div>

        <!-- SYSTEM OVERVIEW -->
          <div class="flex flex-col">
            <!-- Header -->
            <h2
              class="flex items-center gap-2 mb-4 text-lg sm:text-xl font-semibold text-gray-900 dark:text-gray-100">
              <Settings class="h-5 w-5 text-orange-500" />
              System Overview
            </h2>

            <!-- Card -->
            <div
              class="h-[300px] sm:h-[380px] overflow-y-auto rounded-2xl
                    bg-white dark:bg-gray-900
                    border border-gray-200 dark:border-gray-700
                    shadow-xl backdrop-blur-xl
                    divide-y divide-gray-100 dark:divide-gray-800
                    p-5 sm:p-6 custom-scroll">

              <!-- Database Status -->
              <div class="flex items-center justify-between py-3">
                <div class="flex items-center gap-3">
                  <Database class="h-5 w-5 text-green-600" />
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Database Status
                  </span>
                </div>

                <span
                  class="px-3 py-1 text-xs font-semibold rounded-full
                        bg-green-100 text-green-700
                        dark:bg-green-900/40 dark:text-green-400">
                  {{ systemHealth.database_status }}
                </span>
              </div>

              <!-- Last Backup -->
              <div class="flex items-center justify-between py-3">
                <div class="flex items-center gap-3">
                  <Clock class="h-5 w-5 text-blue-600" />
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Last Backup
                  </span>
                </div>

                <span class="text-sm text-gray-600 dark:text-gray-400">
                  {{ new Date(systemHealth.last_backup).toLocaleString() }}
                </span>
              </div>

              <!-- Active Users -->
              <div class="flex items-center justify-between py-3">
                <div class="flex items-center gap-3">
                  <Users class="h-5 w-5 text-indigo-600" />
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Active Users
                  </span>
                </div>

                <span
                  class="text-lg font-bold text-gray-900 dark:text-gray-100">
                  {{ systemHealth.active_users }}
                </span>
              </div>

              <!-- System Errors -->
              <div class="flex items-center justify-between py-3">
                <div class="flex items-center gap-3">
                  <AlertTriangle class="h-5 w-5 text-red-600" />
                  <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    System Errors
                  </span>
                </div>

                <span
                  class="px-3 py-1 text-xs font-semibold rounded-full
                        bg-red-100 text-red-700
                        dark:bg-red-900/40 dark:text-red-400">
                  {{ systemHealth.system_errors }}
                </span>
              </div>

            </div>
          </div>

      </div>


      <!-- SETUP -->
      <div class="mt-12 sm:mt-14">
        <h2
          class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-gray-100 mb-5 sm:mb-6 flex items-center gap-2">
          <Settings class="h-5 w-5 text-orange-500" /> Setup & Configuration
        </h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
          <div v-for="setup in setupItems" :key="setup.title" class="p-4 sm:p-5 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700
            rounded-2xl shadow-lg hover:shadow-xl hover:-translate-y-1 transition cursor-pointer"
            @click="$inertia.visit(setup.link)">
            <div class="flex justify-between">
              <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                {{ setup.title }}
              </h3>
              <component :is="setup.icon" class="h-6 w-6 text-orange-500" />
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
              {{ setup.desc }}
            </p>
          </div>
        </div>
      </div>

      <!-- FOOTER -->
      <footer class="text-center mt-10 text-xs text-gray-500 dark:text-gray-400 pb-4">
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

console.log("het", props.recentActivities);

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
