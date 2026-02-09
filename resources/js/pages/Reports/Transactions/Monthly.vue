<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { Calendar, Layers, TrendingUp } from 'lucide-vue-next'

const props = defineProps<{
  daily_breakdown: Record<string, any>
  type_breakdown: Record<string, any>
  summary: any
  month: string
}>()

function money(value: number) {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES',
  }).format(value ?? 0)
}

function changeMonth(e: Event) {
  const value = (e.target as HTMLInputElement).value
  router.get(route('reports.transactions.monthly'), { month: value }, { preserveState: true })
}

const breadcrumbs = [
  { title: 'Reports', href: route('reports.index') },
  { title: 'Transaction Reports', href: route('reports.transactions.index') },
  { title: 'Monthly Transactions' },
]
</script>

<template>
  <Head title="Monthly Transactions" />

  <AppLayout title="Monthly Transactions" :breadcrumbs="breadcrumbs">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
          Monthly Transactions
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400">
          Transaction performance for the selected month
        </p>
      </div>

      <input
        type="month"
        :value="month"
        @change="changeMonth"
        class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100"
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
        <p class="text-sm text-gray-500">Successful</p>
        <p class="text-xl font-bold text-green-600">
          {{ summary.successful_transactions }}
        </p>
      </div>

      <div class="p-5 rounded-xl bg-white dark:bg-gray-900 border shadow-sm">
        <p class="text-sm text-gray-500">Failed</p>
        <p class="text-xl font-bold text-red-600">
          {{ summary.failed_transactions }}
        </p>
      </div>
    </div>

    <!-- Type Breakdown -->
    <div class="mb-10">
      <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
        <Layers class="h-5 w-5 text-orange-500" />
        Transaction Type Breakdown
      </h3>

      <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div
          v-for="(data, type) in type_breakdown"
          :key="type"
          class="p-5 rounded-xl bg-white dark:bg-gray-900 border shadow-sm"
        >
          <p class="text-sm uppercase text-gray-500">{{ type }}</p>
          <p class="text-xl font-bold">{{ data.count }}</p>
          <p class="text-sm text-gray-600">{{ money(data.total_amount) }}</p>
        </div>
      </div>
    </div>

    <!-- Daily Breakdown -->
    <div>
      <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
        <TrendingUp class="h-5 w-5 text-orange-500" />
        Daily Breakdown
      </h3>

      <div class="overflow-x-auto rounded-xl border">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
              <th class="px-4 py-2 text-left">Date</th>
              <th class="px-4 py-2 text-right">Transactions</th>
              <th class="px-4 py-2 text-right">Deposits</th>
              <th class="px-4 py-2 text-right">Withdrawals</th>
              <th class="px-4 py-2 text-right">Total</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(data, date) in daily_breakdown"
              :key="date"
              class="border-t dark:border-gray-700"
            >
              <td class="px-4 py-2">{{ date }}</td>
              <td class="px-4 py-2 text-right">{{ data.count }}</td>
              <td class="px-4 py-2 text-right">{{ money(data.deposits) }}</td>
              <td class="px-4 py-2 text-right">{{ money(data.withdrawals) }}</td>
              <td class="px-4 py-2 text-right font-semibold">
                {{ money(data.total_amount) }}
              </td>
            </tr>

            <tr v-if="Object.keys(daily_breakdown).length === 0">
              <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                No transactions for this month
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>
