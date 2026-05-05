<template>
  <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">
    <Head title="Admin Dashboard" />

    <div class="min-h-screen bg-slate-50 dark:bg-[#081122]">
      <!-- TOP BAR -->
      <div class="relative overflow-hidden border-b border-white/10 bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900 px-6 py-6 sm:px-8 rounded-b-3xl shadow-2xl shadow-blue-950/20">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(249,115,22,0.18),_transparent_30%)]" />
        <div class="relative flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
          <div>
            <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-white">
              SEPU <span class="text-orange-400">SACCO</span>
            </h1>
            <p class="mt-1 text-sm md:text-base text-slate-300">
              Administrative Financial Overview
            </p>
          </div>

          <div class="flex items-center gap-4">
            <div class="hidden sm:block text-sm text-slate-300">
              {{ new Date().toDateString() }}
            </div>
            <div class="flex h-11 w-11 items-center justify-center rounded-2xl border border-white/10 bg-white/10 text-sm font-semibold text-white shadow-lg backdrop-blur-xl">
              A
            </div>
          </div>
        </div>
      </div>

      <!-- KPI CARDS -->
      <div class="px-6 py-8 sm:px-8">
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">
          <div
            v-for="stat in quickStats"
            :key="stat.title"
            class="group relative overflow-hidden rounded-3xl border border-slate-200/70 bg-white/90 p-5 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900/80"
          >
            <div class="absolute inset-0 bg-gradient-to-br from-transparent via-transparent to-slate-50/40 dark:to-slate-800/20" />

            <div class="relative flex items-start justify-between">
              <div>
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                  {{ stat.title }}
                </p>
                <h2 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 dark:text-white">
                  {{ stat.value }}
                </h2>
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                  {{ stat.sub }}
                </p>
              </div>

              <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-to-br from-orange-500 to-orange-400 text-white shadow-lg shadow-orange-500/20">
                <component :is="stat.icon" class="h-5 w-5" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- MAIN GRID -->
      <div class="grid grid-cols-1 gap-6 px-6 pb-8 sm:px-8 xl:grid-cols-3">
        <!-- LEFT -->
        <div class="space-y-6 xl:col-span-2">
          <!-- Pending Approvals -->
          <div class="rounded-3xl border border-slate-200/70 bg-white/90 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
            <div class="mb-5 flex items-center justify-between">
              <h3 class="text-lg font-semibold text-slate-900 dark:text-white">
                Pending Approvals
              </h3>
              <span class="text-xs font-medium uppercase tracking-[0.2em] text-slate-400">
                Quick Review
              </span>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
              <div
                v-for="item in approvalItems"
                :key="item.label"
                @click="$inertia.visit(item.link)"
                class="group cursor-pointer rounded-2xl border border-slate-200 bg-slate-50/80 p-4 transition-all duration-300 hover:border-orange-300 hover:bg-orange-50 dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-orange-500/40 dark:hover:bg-orange-500/5"
              >
                <div class="flex items-center justify-between">
                  <span class="text-sm font-medium text-slate-700 dark:text-slate-300">
                    {{ item.label }}
                  </span>
                  <span class="text-lg font-bold text-orange-500">
                    {{ item.value }}
                  </span>
                </div>
                <p class="mt-3 text-xs text-slate-400 group-hover:text-orange-500">
                  Click to review →
                </p>
              </div>
            </div>
          </div>

          <!-- Recent Activity -->
          <div class="rounded-3xl border border-slate-200/70 bg-white/90 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
            <h3 class="mb-5 text-lg font-semibold text-slate-900 dark:text-white">
              Recent Activity
            </h3>

            <div class="custom-scroll max-h-[460px] space-y-3 overflow-y-auto pr-1">
              <div
                v-for="(item, i) in recentActivities"
                :key="i"
                @click="item.link ? $inertia.visit(item.link) : null"
                class="cursor-pointer rounded-2xl border px-4 py-3 transition-all duration-300 hover:shadow-md"
                :class="[
                  item.status === 'pending'
                    ? 'border-orange-200 hover:bg-orange-50 dark:border-orange-900/40 dark:hover:bg-orange-500/5'
                    : item.status === 'approved'
                      ? 'border-emerald-200 hover:bg-emerald-50 dark:border-emerald-900/40 dark:hover:bg-emerald-500/5'
                      : item.status === 'rejected'
                        ? 'border-rose-200 hover:bg-rose-50 dark:border-rose-900/40 dark:hover:bg-rose-500/5'
                        : 'border-slate-200 hover:bg-slate-50 dark:border-slate-800 dark:hover:bg-slate-800/40'
                ]"
              >
                <div class="flex items-start justify-between gap-4">
                  <div class="min-w-0 flex-1">
                    <p class="break-words text-sm font-medium text-slate-700 dark:text-slate-300">
                      {{ item.description }}
                    </p>

                    <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-slate-400">
                      <span>{{ new Date(item.time).toLocaleString() }}</span>

                      <span
                        v-if="item.status"
                        class="rounded-full px-2 py-0.5 text-xs font-semibold capitalize"
                        :class="[
                          item.status === 'pending'
                            ? 'bg-orange-100 text-orange-600 dark:bg-orange-500/10 dark:text-orange-300'
                            : item.status === 'approved'
                              ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300'
                              : item.status === 'rejected'
                                ? 'bg-rose-100 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300'
                                : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300'
                        ]"
                      >
                        {{ item.status }}
                      </span>

                      <span
                        v-if="item.status === 'pending'"
                        class="font-medium text-orange-500"
                      >
                        Click to view →
                      </span>
                    </div>
                  </div>

                  <div class="shrink-0 whitespace-nowrap text-sm font-semibold text-slate-700 dark:text-slate-300">
                    <span v-if="item.amount">
                      Ksh {{ Number(item.amount).toLocaleString() }}
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT -->
        <div class="space-y-6">
          <!-- System Health -->
          <div class="rounded-3xl border border-slate-200/70 bg-white/90 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
            <h3 class="mb-6 text-lg font-semibold text-slate-900 dark:text-white">
              System Health
            </h3>

            <div class="space-y-4 text-sm">
              <div class="flex items-center justify-between">
                <span class="text-slate-500 dark:text-slate-400">Database</span>
                <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                  {{ systemHealth.database_status }}
                </span>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-slate-500 dark:text-slate-400">Active Users</span>
                <span class="font-bold text-slate-900 dark:text-white">
                  {{ systemHealth.active_users }}
                </span>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-slate-500 dark:text-slate-400">System Errors</span>
                <span class="font-semibold text-rose-500 dark:text-rose-400">
                  {{ systemHealth.system_errors }}
                </span>
              </div>
            </div>
          </div>

          <!-- Admin Tools -->
          <div class="rounded-3xl border border-slate-200/70 bg-white/90 p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/80">
            <h3 class="mb-6 text-lg font-semibold text-slate-900 dark:text-white">
              Administrative Tools
            </h3>

            <div class="space-y-3">
              <div
                v-for="setup in setupItems"
                :key="setup.title"
                @click="$inertia.visit(setup.link)"
                class="flex cursor-pointer items-center justify-between rounded-2xl border border-slate-200 bg-slate-50/70 p-4 transition-all duration-300 hover:border-orange-300 hover:bg-orange-50 dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-orange-500/40 dark:hover:bg-orange-500/5"
              >
                <div>
                  <p class="text-sm font-medium text-slate-700 dark:text-slate-300">
                    {{ setup.title }}
                  </p>
                  <p class="text-xs text-slate-400">
                    {{ setup.desc }}
                  </p>
                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-orange-100 text-orange-500 dark:bg-orange-500/10 dark:text-orange-300">
                  <component :is="setup.icon" class="h-5 w-5" />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="border-t border-slate-200 px-6 py-5 text-center text-xs text-slate-400 dark:border-slate-800 dark:text-slate-500 sm:px-8">
        © {{ new Date().getFullYear() }} SEPU SACCO — Administrative Suite
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import {
  Activity,
  Users,
  Banknote,
  FileWarning,
  Clock,
  ShieldCheck,
  BarChart3
} from 'lucide-vue-next'

const props = defineProps([
  'stats',
  'recentActivities',
  'pendingApprovals',
  'systemHealth'
])

const formatMoney = (num: number | null) => {
  if (num == null) return 'Ksh 0'
  return 'Ksh ' + Number(num).toLocaleString()
}

const quickStats = [
  {
    title: 'Total Members',
    value: props.stats.members.total,
    sub: `Active: ${props.stats.members.active}`,
    icon: Users
  },
  {
    title: 'Total Share Deposits',
    value: formatMoney(props.stats.financial.total_share_deposits),
    sub: `Share Capital: ${formatMoney(props.stats.financial.total_share_capital)}`,
    icon: Banknote
  },
  {
    title: 'Active Loans',
    value: props.stats.loans.active_loans,
    sub: `Pending: ${props.stats.loans.pending_applications}`,
    icon: Activity
  },
  {
    title: 'Transactions Today',
    value: props.stats.transactions.today,
    sub: `This Month: ${props.stats.transactions.this_month}`,
    icon: FileWarning
  }
]

const approvalItems = [
  {
    label: 'Loans',
    value: props.pendingApprovals.loans,
    link: '/loans'
  },
  {
    label: 'Vouchers',
    value: props.pendingApprovals.vouchers,
    link: '/vouchers'
  },
  {
    label: 'Members',
    value: props.pendingApprovals.member_applications,
    link: '/admin/pending-members'
  },
  {
    label: 'Member Activation',
    value: props.pendingApprovals.pending_activation,
    link: '/admin/pending-members'
  }
]

const setupItems = [
  {
    title: 'Loan Rates',
    desc: 'Define loan interest and limits',
    icon: BarChart3,
    link: '/admin/settings/loan'
  },
  {
    title: 'Repayment Periods',
    desc: 'Set repayment durations',
    icon: Clock,
    link: '/admin/settings/loan'
  },
  {
    title: 'System Users',
    desc: 'Manage user roles',
    icon: Users,
    link: '/system-users'
  },
  {
    title: 'System Approvals',
    desc: 'Define workflows',
    icon: ShieldCheck,
    link: '/admin/settings'
  }
]
</script>

<style scoped>
.custom-scroll::-webkit-scrollbar {
  width: 6px;
}

.custom-scroll::-webkit-scrollbar-thumb {
  background-color: rgba(249, 115, 22, 0.35);
  border-radius: 9999px;
}

.custom-scroll::-webkit-scrollbar-track {
  background-color: transparent;
}
</style>