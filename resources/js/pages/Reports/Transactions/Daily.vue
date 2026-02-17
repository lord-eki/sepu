<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { CalendarDays, ArrowDownCircle, ArrowUpCircle, ArrowRightLeft } from 'lucide-vue-next'

const props = defineProps<{
  transactions: any[]
  summary?: any
  hourly_breakdown: Record<string, { count: number; total_amount: number }>
  date: string
}>()

const summary = props.summary ?? {
  total_transactions: 0,
  total_amount: 0,
  deposits: [],
  withdrawals: []
}

const date = ref(props.date)

function money(value: number) {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES',
  }).format(value ?? 0)
}

function changeDate(e: Event) {
  const value = (e.target as HTMLInputElement).value
  router.get(route('reports.transactions.daily'), { date: value }, { preserveState: true })
}

const breadcrumbs = [
  { title: 'Transaction Reports', href: route('reports.transactionsReport.index') },
  { title: 'Daily Transactions' },
]
</script>

<template>
  <Head title="Daily Transactions" />

  <AppLayout title="Daily Transactions" :breadcrumbs="breadcrumbs">

    <!-- Header + Date Picker -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mt-5 mb-8 mx-5">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
          Daily Transactions
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
          All transactions recorded on a specific day
        </p>
      </div>

      <div class="flex items-center gap-2">
        <CalendarDays class="h-5 w-5 text-orange-500" />
        <input type="date" v-model="date" @change="changeDate"
          class="rounded-lg border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 px-3 py-2" />
      </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-10 mx-5">
      <!-- Total Transactions -->
      <div class="p-5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition">
        <div class="flex items-center gap-3">
          <ArrowRightLeft class="h-6 w-6 text-orange-500" />
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Transactions</p>
            <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ summary.total_transactions }}</p>
            <p class="text-sm text-gray-600 dark:text-gray-300">{{ money(summary.total_amount) }}</p>
          </div>
        </div>
      </div>

      <!-- Deposits -->
      <div class="p-5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition">
        <div class="flex items-center gap-3">
          <ArrowDownCircle class="h-6 w-6 text-green-600" />
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Deposits</p>
            <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ summary.deposits?.length ?? 0 }}</p>
          </div>
        </div>
      </div>

      <!-- Withdrawals -->
      <div class="p-5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm hover:shadow-md transition">
        <div class="flex items-center gap-3">
          <ArrowUpCircle class="h-6 w-6 text-red-500" />
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Withdrawals</p>
            <p class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ summary.withdrawals?.length ?? 0 }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Hourly Breakdown -->
    <div class="mb-10 mx-5">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Hourly Breakdown</h3>
      <div class="overflow-x-auto rounded-xl border dark:border-gray-700">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
              <th class="px-4 py-2 text-left">Hour</th>
              <th class="px-4 py-2 text-right">Transactions</th>
              <th class="px-4 py-2 text-right">Total Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(data, hour) in hourly_breakdown" :key="hour" class="border-t dark:border-gray-700">
              <td class="px-4 py-2">{{ hour }}</td>
              <td class="px-4 py-2 text-right">{{ data.count }}</td>
              <td class="px-4 py-2 text-right">{{ money(data.total_amount) }}</td>
            </tr>
            <tr v-if="Object.keys(hourly_breakdown).length === 0">
              <td colspan="3" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                No hourly data
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Transactions Table -->
    <div class="mb-10 mx-5">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">Transactions</h3>
      <div class="overflow-x-auto rounded-xl border dark:border-gray-700">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
              <th class="px-4 py-2 text-left">ID</th>
              <th class="px-4 py-2 text-left">Member</th>
              <th class="px-4 py-2 text-left">Type</th>
              <th class="px-4 py-2 text-right">Amount</th>
              <th class="px-4 py-2 text-left">Method</th>
              <th class="px-4 py-2 text-left">Status</th>
              <th class="px-4 py-2 text-left">Time</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="tx in transactions" :key="tx.id" class="border-t dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
              <td class="px-4 py-2 font-mono">{{ tx.transaction_id }}</td>
              <td class="px-4 py-2">{{ tx.member?.full_name ?? '-' }}</td>
              <td class="px-4 py-2 capitalize">{{ tx.transaction_type }}</td>
              <td class="px-4 py-2 text-right">{{ money(tx.amount) }}</td>
              <td class="px-4 py-2 capitalize">{{ tx.payment_method }}</td>
              <td class="px-4 py-2">
                <span class="px-2 py-1 rounded-full text-xs font-medium" :class="{
                  'bg-green-100 text-green-700': tx.status === 'completed',
                  'bg-yellow-100 text-yellow-700': tx.status === 'pending',
                  'bg-red-100 text-red-700': tx.status === 'failed',
                }">
                  {{ tx.status }}
                </span>
              </td>
              <td class="px-4 py-2">{{ new Date(tx.created_at).toLocaleTimeString() }}</td>
            </tr>
            <tr v-if="transactions.length === 0">
              <td colspan="7" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                No transactions found for this date
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </AppLayout>
</template>
