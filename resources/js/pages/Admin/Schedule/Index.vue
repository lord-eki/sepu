<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import {
  CalendarDays,
  Landmark,
  Wallet,
  BadgeDollarSign,
  Clock3,
  AlertTriangle,
  CheckCircle2,
  ArrowRight,
  TrendingUp,
  Activity,
  FileSpreadsheet,
} from 'lucide-vue-next'

interface RecentLog {
  id: number
  schedule_type: string
  schedule_type_label: string
  period_label: string
  execution_date: string
  executed_by: string | null
  total_records_processed: number
  total_records_failed: number
  total_amount_posted: number
  status: 'completed' | 'partial' | 'failed'
}

defineProps<{
  recentLogs: RecentLog[]
  currentMonth: string
  currentYear: number
}>()

const scheduleModules = [
  {
    title: 'Monthly Deposits',
    description:
      'Automatically process scheduled member monthly contributions and savings deposits.',
    icon: Wallet,
    route: '/schedule/monthly-deposit',
    color:
      'from-blue-600 to-cyan-500',
    statsLabel: 'Recurring Contributions',
  },
  {
    title: 'Loan Repayments',
    description:
      'Process automatic loan deductions, repayments, overdue balances, and repayment schedules.',
    icon: Landmark,
    route: '/schedule/loan-repayment',
    color:
      'from-orange-500 to-amber-500',
    statsLabel: 'Loan Recovery',
  },
  {
    title: 'Loan Disbursements',
    description:
      'Disburse approved loans, activate balances, and track member financing operations.',
    icon: TrendingUp,
    route: '/schedule/loan-disbursement',
    color:
      'from-emerald-500 to-green-600',
    statsLabel: 'Loan Activation',
  },
  {
    title: 'Dividend Payments',
    description:
      'Distribute approved dividends to eligible members and manage payout schedules.',
    icon: BadgeDollarSign,
    route: '/schedule/dividend-payment',
    color:
      'from-purple-600 to-fuchsia-500',
    statsLabel: 'Annual Distribution',
  },
]

const getStatusClasses = (status: string) => {
  switch (status) {
    case 'completed':
      return 'bg-emerald-100 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20'

    case 'partial':
      return 'bg-amber-100 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:text-amber-400 dark:border-amber-500/20'

    case 'failed':
      return 'bg-red-100 text-red-700 border border-red-200 dark:bg-red-500/10 dark:text-red-400 dark:border-red-500/20'

    default:
      return 'bg-gray-100 text-gray-700 border border-gray-200'
  }
}

const getStatusIcon = (status: string) => {
  switch (status) {
    case 'completed':
      return CheckCircle2

    case 'partial':
      return AlertTriangle

    default:
      return AlertTriangle
  }
}
</script>

<template>
  <AppLayout
    :breadcrumbs="[
      {
        title: 'Schedule Management',
        href: '/schedule',
      },
    ]"
  >
    <Head title="Schedule Management" />

    <div
      class="min-h-screen bg-gradient-to-br from-slate-50 via-white to-blue-50 dark:from-slate-950 dark:via-slate-900 dark:to-slate-950"
    >
      <!-- HERO SECTION -->
      <section
        class="relative overflow-hidden rounded-2xl border-b border-slate-200/70 dark:border-slate-800 bg-gradient-to-br from-blue-950 via-blue-900 to-orange-500"
      >
        <div
          class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(59,130,246,0.12),transparent_30%),radial-gradient(circle_at_bottom_left,rgba(249,115,22,0.12),transparent_30%)]"
        />

        <div class="relative px-6 py-10 lg:px-10">
          <div
            class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between"
          >
            <div class="max-w-3xl">
              <h1
                class="text-3xl text-white font-bold tracking-tight text-slate-900 dark:text-white md:text-4xl"
              >
                SACCO Schedule Management
              </h1>

              <p
                class="mt-4 max-w-2xl text-sm leading-7 text-white dark:text-slate-400"
              >
                Automate deposits, loan repayments, disbursements, dividend
                distributions, and financial scheduling workflows with full
                audit tracking and execution monitoring.
              </p>
            </div>

            <!-- SUMMARY -->
            <div
              class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:min-w-[500px]"
            >
              <div
                class="rounded-2xl border border-slate-200 bg-white/90 p-5 shadow-sm backdrop-blur dark:border-slate-800 dark:bg-slate-900/80"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <p
                      class="text-sm font-medium text-slate-500 dark:text-slate-400"
                    >
                      Current Month
                    </p>

                    <h3
                      class="mt-1 text-lg font-bold text-slate-900 dark:text-white"
                    >
                      {{ currentMonth }}
                    </h3>
                  </div>

                  <div
                    class="rounded-xl bg-blue-100 p-3 dark:bg-blue-500/10"
                  >
                    <CalendarDays
                      class="h-5 w-5 text-blue-600 dark:text-blue-400"
                    />
                  </div>
                </div>
              </div>

              <div
                class="rounded-2xl border border-slate-200 bg-white/90 p-5 shadow-sm backdrop-blur dark:border-slate-800 dark:bg-slate-900/80"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <p
                      class="text-sm font-medium text-slate-500 dark:text-slate-400"
                    >
                      Financial Year
                    </p>

                    <h3
                      class="mt-1 text-lg font-bold text-slate-900 dark:text-white"
                    >
                      {{ currentYear }}
                    </h3>
                  </div>

                  <div
                    class="rounded-xl bg-orange-100 p-3 dark:bg-orange-500/10"
                  >
                    <Activity
                      class="h-5 w-5 text-orange-600 dark:text-orange-400"
                    />
                  </div>
                </div>
              </div>

              <div
                class="rounded-2xl border border-slate-200 bg-white/90 p-5 shadow-sm backdrop-blur dark:border-slate-800 dark:bg-slate-900/80"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <p
                      class="text-sm font-medium text-slate-500 dark:text-slate-400"
                    >
                      Total Logs
                    </p>

                    <h3
                      class="mt-1 text-lg font-bold text-slate-900 dark:text-white"
                    >
                      {{ recentLogs.length }}
                    </h3>
                  </div>

                  <div
                    class="rounded-xl bg-emerald-100 p-3 dark:bg-emerald-500/10"
                  >
                    <FileSpreadsheet
                      class="h-5 w-5 text-emerald-600 dark:text-emerald-400"
                    />
                  </div>
                </div>
              </div>

              <div
                class="rounded-2xl border border-slate-200 bg-white/90 p-5 shadow-sm backdrop-blur dark:border-slate-800 dark:bg-slate-900/80"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <p
                      class="text-sm font-medium text-slate-500 dark:text-slate-400"
                    >
                      Active Modules
                    </p>

                    <h3
                      class="mt-1 text-lg font-bold text-slate-900 dark:text-white"
                    >
                      {{ scheduleModules.length }}
                    </h3>
                  </div>

                  <div
                    class="rounded-xl bg-purple-100 p-3 dark:bg-purple-500/10"
                  >
                    <Clock3
                      class="h-5 w-5 text-purple-600 dark:text-purple-400"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- MODULE CARDS -->
      <section class="px-6 py-8 lg:px-10">
        <div
          class="mb-6 flex flex-col gap-2 md:flex-row md:items-center md:justify-between"
        >
          <div>
            <h2
              class="text-2xl font-bold text-slate-900 dark:text-white"
            >
              Financial Schedule Modules
            </h2>

            <p class="mt-1 text-slate-600 dark:text-slate-400">
              Manage automated SACCO financial operations and scheduled
              transaction workflows.
            </p>
          </div>
        </div>

        <div
          class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-4"
        >
          <Link
            v-for="module in scheduleModules"
            :key="module.title"
            :href="module.route"
            class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-2xl dark:border-slate-800 dark:bg-slate-900"
          >
            <div
              class="absolute inset-0 opacity-0 transition-opacity duration-300 group-hover:opacity-100"
              :class="`bg-gradient-to-br ${module.color}`"
            />

            <div class="relative p-6">
              <div
                class="mb-5 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 transition-all duration-300 group-hover:bg-white/20 dark:bg-slate-800"
              >
                <component
                  :is="module.icon"
                  class="h-7 w-7 text-slate-700 transition-colors duration-300 group-hover:text-white dark:text-slate-300"
                />
              </div>

              <div>
                <div
                  class="mb-2 inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 transition-all duration-300 group-hover:bg-white/20 group-hover:text-white dark:bg-slate-800 dark:text-slate-300"
                >
                  {{ module.statsLabel }}
                </div>

                <h3
                  class="text-xl font-bold text-slate-900 transition-colors duration-300 group-hover:text-white dark:text-white"
                >
                  {{ module.title }}
                </h3>

                <p
                  class="mt-3 text-sm leading-6 text-slate-600 transition-colors duration-300 group-hover:text-slate-100 dark:text-slate-400"
                >
                  {{ module.description }}
                </p>

                <div
                  class="mt-6 flex items-center gap-2 text-sm font-semibold text-blue-600 transition-colors duration-300 group-hover:text-white dark:text-blue-400"
                >
                  Open Module

                  <ArrowRight
                    class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                  />
                </div>
              </div>
            </div>
          </Link>
        </div>
      </section>

      <!-- RECENT EXECUTION LOGS -->
      <section class="px-6 pb-10 lg:px-10">
        <div
          class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
        >
          <!-- HEADER -->
          <div
            class="border-b border-slate-200 px-6 py-5 dark:border-slate-800"
          >
            <div
              class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between"
            >
              <div>
                <h2
                  class="text-xl font-bold text-slate-900 dark:text-white"
                >
                  Recent Schedule Executions
                </h2>

                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                  Latest automated financial operations and execution activity.
                </p>
              </div>

              <div
                class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-2 text-sm font-medium text-slate-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300"
              >
                Last {{ recentLogs.length }} records
              </div>
            </div>
          </div>

          <!-- TABLE -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
              <thead class="bg-slate-50 dark:bg-slate-800/50">
                <tr>
                  <th
                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                  >
                    Schedule
                  </th>

                  <th
                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                  >
                    Period
                  </th>

                  <th
                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                  >
                    Executed By
                  </th>

                  <th
                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                  >
                    Records
                  </th>

                  <th
                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                  >
                    Amount
                  </th>

                  <th
                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                  >
                    Status
                  </th>

                  <th
                    class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400"
                  >
                    Execution Date
                  </th>
                </tr>
              </thead>

              <tbody
                class="divide-y divide-slate-100 dark:divide-slate-800"
              >
                <tr
                  v-for="log in recentLogs"
                  :key="log.id"
                  class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/40"
                >
                  <td class="px-6 py-5">
                    <div>
                      <div
                        class="font-semibold text-slate-900 dark:text-white"
                      >
                        {{ log.schedule_type_label }}
                      </div>

                      <div
                        class="mt-1 text-xs text-slate-500 dark:text-slate-400"
                      >
                        {{ log.schedule_type }}
                      </div>
                    </div>
                  </td>

                  <td
                    class="px-6 py-5 text-sm font-medium text-slate-700 dark:text-slate-300"
                  >
                    {{ log.period_label }}
                  </td>

                  <td
                    class="px-6 py-5 text-sm text-slate-600 dark:text-slate-400"
                  >
                    {{ log.executed_by || 'System' }}
                  </td>

                  <td class="px-6 py-5">
                    <div class="flex flex-col gap-1">
                      <span
                        class="text-sm font-semibold text-slate-900 dark:text-white"
                      >
                        {{ log.total_records_processed }} Processed
                      </span>

                      <span
                        v-if="log.total_records_failed > 0"
                        class="text-xs text-red-600 dark:text-red-400"
                      >
                        {{ log.total_records_failed }} Failed
                      </span>
                    </div>
                  </td>

                  <td
                    class="px-6 py-5 text-sm font-bold text-emerald-600 dark:text-emerald-400"
                  >
                    KES
                    {{ Number(log.total_amount_posted).toLocaleString() }}
                  </td>

                  <td class="px-6 py-5">
                    <div
                      :class="getStatusClasses(log.status)"
                      class="inline-flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-semibold"
                    >
                      <component
                        :is="getStatusIcon(log.status)"
                        class="h-3.5 w-3.5"
                      />

                      {{ log.status }}
                    </div>
                  </td>

                  <td
                    class="px-6 py-5 text-sm text-slate-600 dark:text-slate-400"
                  >
                    {{ log.execution_date }}
                  </td>
                </tr>

                <!-- EMPTY -->
                <tr v-if="recentLogs.length === 0">
                  <td colspan="7" class="px-6 py-16 text-center">
                    <div class="flex flex-col items-center justify-center">
                      <div
                        class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 dark:bg-slate-800"
                      >
                        <FileSpreadsheet
                          class="h-8 w-8 text-slate-400"
                        />
                      </div>

                      <h3
                        class="text-lg font-semibold text-slate-900 dark:text-white"
                      >
                        No Schedule Logs Found
                      </h3>

                      <p
                        class="mt-2 max-w-md text-sm text-slate-500 dark:text-slate-400"
                      >
                        Financial schedule execution records will appear here
                        once schedules are processed.
                      </p>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </section>
    </div>
  </AppLayout>
</template>