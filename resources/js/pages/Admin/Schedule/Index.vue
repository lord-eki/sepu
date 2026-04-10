<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { Calendar, TrendingUp, Users, Banknote } from 'lucide-vue-next'

const props = defineProps<{
  recentLogs: any[]
  currentMonth: string
  currentYear: number
}>()

const cards = [
  {
    title: 'Monthly Deposits',
    icon: Banknote,
    link: '/schedule/monthly-deposit',
    desc: 'Automate member monthly contributions'
  },
  {
    title: 'Loan Repayments',
    icon: TrendingUp,
    link: '/schedule/loan-repayment',
    desc: 'Process scheduled loan deductions'
  },
  {
    title: 'Loan Disbursement',
    icon: Users,
    link: '/schedule/loan-disbursement',
    desc: 'Release approved loans to members'
  },
  {
    title: 'Dividends',
    icon: Calendar,
    link: '/schedule/dividend-payment',
    desc: 'Distribute annual dividends'
  }
]
</script>

<template>
  <AppLayout :breadcrumbs="[
    { title: 'Schedules', href: route('schedule.index') }
  ]">

    <Head title="Schedules Dashboard" />

    <div class="p-6 space-y-8">
      <!-- HEADER -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">Schedules Dashboard</h1>
          <p class="text-gray-500 mt-1">
            Manage automated SACCO financial operations and track execution history
          </p>
        </div>

        <div class="bg-white px-4 py-2 rounded-xl shadow text-sm">
          <p><strong>Current Month:</strong> {{ currentMonth }}</p>
          <p><strong>Year:</strong> {{ currentYear }}</p>
        </div>
      </div>

      <!-- ACTION CARDS -->
      <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <Link v-for="card in cards" :key="card.title" :href="card.link"
          class="group bg-white rounded-2xl p-5 shadow hover:shadow-lg transition">
        <div class="flex items-center justify-between">
          <component :is="card.icon" class="w-8 h-8 text-blue-600" />
          <span class="text-xs text-gray-400 group-hover:text-blue-600">
            Open →
          </span>
        </div>

        <h3 class="mt-4 text-lg font-semibold text-gray-800">
          {{ card.title }}
        </h3>

        <p class="text-sm text-gray-500 mt-1">
          {{ card.desc }}
        </p>
        </Link>
      </div>

      <!-- RECENT LOGS -->
      <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-lg font-semibold text-gray-800">
            Recent Schedule Executions
          </h2>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="text-left border-b text-gray-500">
                <th class="py-2">Type</th>
                <th>Period</th>
                <th>Date</th>
                <th>Processed</th>
                <th>Failed</th>
                <th>Amount</th>
                <th>Status</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="log in recentLogs" :key="log.id" class="border-b hover:bg-gray-50 transition">
                <td class="py-2 font-medium">
                  {{ log.schedule_type_label }}
                </td>

                <td>{{ log.period_label }}</td>

                <td>{{ log.execution_date }}</td>

                <td class="text-green-600">
                  {{ log.total_records_processed }}
                </td>

                <td class="text-red-500">
                  {{ log.total_records_failed }}
                </td>

                <td class="font-semibold">
                  KES {{ log.total_amount_posted.toLocaleString() }}
                </td>

                <td>
                  <span class="px-2 py-1 rounded-full text-xs font-medium" :class="{
    'bg-green-100 text-green-700': log.status === 'completed',
    'bg-yellow-100 text-yellow-700': log.status === 'partial',
    'bg-red-100 text-red-700': log.status === 'failed'
  }">
                    {{ log.status }}
                  </span>
                </td>
              </tr>

              <tr v-if="recentLogs.length === 0">
                <td colspan="7" class="text-center py-6 text-gray-400">
                  No schedule executions yet
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
