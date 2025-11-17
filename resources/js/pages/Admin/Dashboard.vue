<template>
  <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">
    <Head title="Admin Dashboard" />

    <div class="min-h-screen p-6 space-y-10 transition-colors duration-300 bg-gray-50 dark:bg-gray-900 animate-fadeIn">

      <!-- Header -->
      <header
        class="relative overflow-hidden rounded-3xl p-6 text-white shadow-xl flex flex-col sm:flex-row sm:items-center sm:justify-between
               bg-gradient-to-r from-[#0a2342] to-[#12345a] dark:from-gray-800 dark:to-gray-700">
        <div class="absolute inset-0 opacity-20 dark:opacity-30 bg-[url('/patterns/mesh.svg')] bg-cover"></div>

        <div class="relative z-10">
          <h1 class="text-3xl font-extrabold tracking-tight drop-shadow-sm">
            SEPU <span class="text-orange-500">SACCO</span>
          </h1>
          <p class="text-sm text-blue-100 dark:text-gray-300 mt-1">
            Admin panel for insights, performance analytics and real-time SACCO operations.
          </p>
        </div>

        <div class="relative z-10 mt-4 sm:mt-0 flex flex-col items-center gap-2">
          <Handshake class="w-7 h-7 text-orange-400 animate-bounce" />
          <div class="h-1.5 w-24 bg-orange-500 rounded-full animate-pulse"></div>
        </div>
      </header>

      <!-- Quick Stats -->
      <section class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <Card
          v-for="stat in quickStats"
          :key="stat.title"
          class="rounded-2xl border border-gray-200 dark:border-gray-800 shadow-md bg-white dark:bg-gray-800/80 backdrop-blur-sm
                 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
          <CardHeader class="flex items-center justify-between">
            <CardTitle class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ stat.title }}</CardTitle>
            <div class="p-2 rounded-xl" :class="stat.color">
              <component :is="stat.icon" class="h-5 w-5" />
            </div>
          </CardHeader>
          <CardContent>
            <div class="text-xl sm:text-2xl font-bold text-[#0a2342] dark:text-white">{{ stat.value }}</div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ stat.sub }}</p>
          </CardContent>
        </Card>
      </section>

      <!-- Activities & System Info -->
      <section class="grid gap-6 lg:grid-cols-3">

        <!-- Recent Activities -->
        <div class="lg:col-span-2">
          <h2 class="text-lg font-semibold mb-3 text-[#0a2342] dark:text-gray-200">Recent Activities</h2>
          <div
            class="rounded-2xl border border-gray-200 dark:border-gray-800 shadow-md bg-white dark:bg-gray-800/80 backdrop-blur-sm
                   h-[350px] overflow-y-auto custom-scroll divide-y divide-gray-100 dark:divide-gray-700">
            <div
              v-for="(item, i) in recentActivities"
              :key="i"
              class="flex items-center justify-between p-4 hover:bg-orange-50 dark:hover:bg-gray-700 transition">
              <div class="flex items-start gap-3">
                <ArrowRightCircle class="h-5 w-5 text-orange-500" />
                <div>
                  <p class="text-sm text-gray-800 dark:text-gray-200 leading-tight">{{ item.description }}</p>
                  <p class="text-xs text-gray-500 dark:text-gray-400">{{ new Date(item.time).toLocaleString() }}</p>
                </div>
              </div>
              <span class="text-sm text-[#0a2342] dark:text-white">Ksh. {{ item.amount }}</span>
            </div>
          </div>
        </div>

        <!-- Approvals & System Health -->
        <Card
          class="rounded-2xl border border-gray-200 dark:border-gray-800 shadow-md bg-white dark:bg-gray-800/80 backdrop-blur-sm">
          <CardHeader>
            <CardTitle class="text-base font-semibold text-[#0a2342] dark:text-gray-200 flex items-center gap-2">
              <Clock class="h-4 w-4 text-orange-500" /> Pending Approvals
            </CardTitle>
          </CardHeader>

          <CardContent class="pb-0">
            <ul class="space-y-2 text-sm mb-4">
              <li
                v-for="item in approvalItems"
                :key="item.label"
                class="flex justify-between items-center cursor-pointer hover:text-orange-600 dark:hover:text-orange-400"
                @click="item.link && $inertia.visit(item.link)">
                <span class="dark:text-gray-300">{{ item.label }}</span>
                <span :class="item.badge">{{ item.value }}</span>
              </li>
            </ul>

            <hr class="border-gray-200 dark:border-gray-700 mb-4" />

            <CardTitle class="text-base font-semibold text-[#0a2342] dark:text-gray-200 flex items-center gap-2">
              <Database class="h-4 w-4 text-blue-800 dark:text-blue-400" /> System Health
            </CardTitle>

            <ul class="space-y-3 text-sm mt-5">
              <li class="flex items-center gap-2 dark:text-gray-300">
                <Database class="h-4 w-4 text-green-600" /> Status:
                <span class="font-medium text-green-700 dark:text-green-400">{{ systemHealth.database_status }}</span>
              </li>
              <li class="flex items-center gap-2 dark:text-gray-300">
                <Clock class="h-4 w-4 text-gray-500" /> Last Backup:
                <span>{{ new Date(systemHealth.last_backup).toLocaleString() }}</span>
              </li>
              <li class="flex items-center gap-2 dark:text-gray-300">
                <Users class="h-4 w-4 text-[#0a2342] dark:text-gray-100" /> Active Users:
                <span class="font-medium">{{ systemHealth.active_users }}</span>
              </li>
              <li class="flex items-center gap-2 dark:text-gray-300">
                <AlertTriangle class="h-4 w-4 text-red-600" /> Errors:
                <span class="font-medium text-red-600">{{ systemHealth.system_errors }}</span>
              </li>
            </ul>
          </CardContent>
        </Card>
      </section>

      <!-- Setup -->
      <section>
        <h2 class="text-lg font-semibold mb-4 text-[#0a2342] dark:text-gray-200 flex items-center gap-2">
          <Settings class="h-5 w-5 text-orange-500" /> System Setup & Configuration
        </h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <Card
            v-for="setup in setupItems"
            :key="setup.title"
            class="rounded-2xl border border-gray-200 dark:border-gray-800 shadow-md bg-white dark:bg-gray-800/80
                   hover:shadow-xl hover:-translate-y-1 transition-all duration-300 cursor-pointer"
            @click="$inertia.visit(setup.link)">
            <CardHeader class="flex items-center justify-between pb-2">
              <CardTitle class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ setup.title }}</CardTitle>
              <div class="p-2 rounded-xl " :class="setup.color">
                <component :is="setup.icon" class="h-5 w-5 " />
              </div>
            </CardHeader>
            <CardContent>
              <p class="text-xs text-gray-500 dark:text-gray-400">{{ setup.desc }}</p>
            </CardContent>
          </Card>
        </div>
      </section>

      <!-- Footer -->
      <footer class="text-center text-xs text-gray-500 dark:text-gray-400 pt-8 pb-4">
        © {{ new Date().getFullYear() }}
        <span class="font-semibold text-[#0a2342] dark:text-gray-200">SEPU SACCO</span> — All Rights Reserved.
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
