<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { BarChart3, Calendar, TrendingUp } from 'lucide-vue-next'

const props = defineProps<{
  monthly_breakdown: Record<string, any>
  quarterly_breakdown: Record<string, any>
  summary: any
  year: number
}>()

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
  { title: 'Reports', href: route('reports.index') },
  { title: 'Transaction Reports', href: route('reports.transactions.index') },
  { title: 'Annual Transactions' },
]
</script>

<template>
  <Head title="Annual Transactions" />

  <AppLayout title="Annual Transactions" :breadcrumbs="breadcrumbs">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
          Annual Transactions
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Yearly transaction performance and growth overview
        </p>
      </div>

      <input
        type="number"
        min="2000"
        max="2100"
        :value="year"
        @change="changeYear"
        class="w-28 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
      />
    </div>

    <!-- Summary -->
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-6 mb-10">
      <div class="p-5 rounded-xl bg-white dark:bg-gray-900 border shadow-sm">
        <p class="text-sm text-gray-500">Total Transactions</p>
        <p class="text-xl font-bold">{{ summary.total_transactions }}</p>
      </div>

      <div class="p-5 rounded-xl bg-white dark:bg-gray-900 border shadow-sm">
        <p class="text-sm text-gray-500">Total Amount</p>
        <p class="text-xl font-bold">{{ money(summary.total_amount) }}</p>
      </div>

      <div class="p-5 rounded-xl bg-white dark:bg-gray-900 border shadow-sm">
        <p class="text-sm text-gray-500">Avg Monthly Volume</p>
        <p class="text-xl font-bold">{{ money(summary.average_monthly_volume) }}</p>
      </div>

      <div class="p-5 rounded-xl bg-white dark:bg-gray-900 border shadow-sm">
        <p class="text-sm text-gray-500">Growth Rate</p>
        <p class="text-xl font-bold text-green-600">
          {{ summary.growth_rate }}%
        </p>
      </div>
    </div>

    <!-- Quarterly Breakdown -->
    <div class="mb-10">
      <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
        <BarChart3 class="h-5 w-5 text-orange-500" />
        Quarterly Breakdown
      </h3>

      <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
        <div
          v-for="(data, quarter) in quarterly_breakdown"
          :key="quarter"
          class="p-5 rounded-xl bg-white dark:bg-gray-900 border shadow-sm"
        >
          <p class="text-sm text-gray-500">{{ quarter }}</p>
          <p class="text-xl font-bold">{{ data.count }} txns</p>
          <p class="text-sm text-gray-600">{{ money(data.total_amount) }}</p>
        </div>
      </div>
    </div>

    <!-- Monthly Breakdown -->
    <div>
      <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
        <Calendar class="h-5 w-5 text-orange-500" />
        Monthly Breakdown
      </h3>

      <div class="overflow-x-auto rounded-xl border">
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
              class="border-t dark:border-gray-700"
            >
              <td class="px-4 py-2">{{ month }}</td>
              <td class="px-4 py-2 text-right">{{ data.count }}</td>
              <td class="px-4 py-2 text-right">{{ money(data.deposits) }}</td>
              <td class="px-4 py-2 text-right">{{ money(data.withdrawals) }}</td>
              <td class="px-4 py-2 text-right font-semibold">
                {{ money(data.total_amount) }}
              </td>
            </tr>

            <tr v-if="Object.keys(monthly_breakdown).length === 0">
              <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                No transaction data available
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
