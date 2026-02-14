<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { BarChart3, Calendar, TrendingUp } from 'lucide-vue-next'

const props = defineProps<{
  monthly_breakdown: Record<string, any>
  quarterly_breakdown: Record<string, any>
  summary?: any
  year: number
}>()

const year = ref(props.year)

const summary = props.summary ?? {
  total_transactions: 0,
  total_amount: 0,
  average_monthly_volume: 0,
  growth_rate: 0
}

function money(value: number) {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES',
  }).format(value ?? 0)
}

function changeYear(e: Event) {
  const value = (e.target as HTMLInputElement).value
  router.get(route('reports.transactions.annual'), { year: value }, { preserveState: true })
}

const breadcrumbs = [
  { title: 'Transaction Reports', href: route('reports.transactionsReport.index') },
  { title: 'Annual Transactions' },
]
</script>

<template>
  <Head title="Annual Transactions" />

  <AppLayout title="Annual Transactions" :breadcrumbs="breadcrumbs">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mt-5 mb-8 mx-5">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
          Annual Transactions
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
          Yearly transaction performance and growth overview
        </p>
      </div>

      <div class="flex items-center gap-2">
        <input
          type="number"
          min="2000"
          max="2100"
          v-model="year"
          @change="changeYear"
          class="w-28 rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 px-3 py-2"
        />
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 mb-10 mx-5">
      <div class="p-5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition">
        <p class="text-sm text-gray-500">Total Transactions</p>
        <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ summary.total_transactions }}</p>
      </div>

      <div class="p-5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition">
        <p class="text-sm text-gray-500">Total Amount</p>
        <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ money(summary.total_amount) }}</p>
      </div>

      <div class="p-5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition">
        <p class="text-sm text-gray-500">Avg Monthly Volume</p>
        <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ money(summary.average_monthly_volume) }}</p>
      </div>

      <div class="p-5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition">
        <p class="text-sm text-gray-500">Growth Rate</p>
        <p class="text-xl font-bold text-green-600">{{ summary.growth_rate }}%</p>
      </div>
    </div>

    <!-- Quarterly Breakdown -->
    <div class="mb-10 mx-5">
      <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
        <BarChart3 class="h-5 w-5 text-orange-500" />
        Quarterly Breakdown
      </h3>

      <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
        <div
          v-for="(data, quarter) in quarterly_breakdown"
          :key="quarter"
          class="p-5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition"
        >
          <p class="text-sm text-gray-500">{{ quarter }}</p>
          <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ data.count }} txns</p>
          <p class="text-sm text-gray-600 dark:text-gray-400">{{ money(data.total_amount) }}</p>
        </div>
      </div>
    </div>

    <!-- Monthly Breakdown Table -->
    <div class="mb-10 mx-5">
      <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
        <Calendar class="h-5 w-5 text-orange-500" />
        Monthly Breakdown
      </h3>

      <div class="overflow-x-auto rounded-xl border dark:border-gray-700">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
              <th class="px-4 py-2 text-left">Month</th>
              <th class="px-4 py-2 text-right">Transactions</th>
              <th class="px-4 py-2 text-right">Deposits</th>
              <th class="px-4 py-2 text-right">Withdrawals</th>
              <th class="px-4 py-2 text-right">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(data, month) in monthly_breakdown"
              :key="month"
              class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition"
            >
              <td class="px-4 py-2">{{ month }}</td>
              <td class="px-4 py-2 text-right">{{ data.count }}</td>
              <td class="px-4 py-2 text-right">{{ money(data.deposits) }}</td>
              <td class="px-4 py-2 text-right">{{ money(data.withdrawals) }}</td>
              <td class="px-4 py-2 text-right font-semibold">{{ money(data.total_amount) }}</td>
            </tr>

            <tr v-if="Object.keys(monthly_breakdown).length === 0">
              <td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                No transaction data available
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
