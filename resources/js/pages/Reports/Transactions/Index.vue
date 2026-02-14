<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import {
  CalendarDays,
  CalendarRange,
  CalendarClock,
  ArrowRightLeft,
} from 'lucide-vue-next'

const props = defineProps<{
  summary?: {
    today: number
    today_amount: number
    this_month: number
    this_month_amount: number
    this_year: number
    this_year_amount: number
  }
}>()

// Always have a summary object
const summary = props.summary ?? {
  today: 0,
  today_amount: 0,
  this_month: 0,
  this_month_amount: 0,
  this_year: 0,
  this_year_amount: 0,
}

function money(value: number) {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES',
  }).format(value ?? 0)
}

const breadcrumbs = [
  { title: 'Reports', href: route('reports.index') },
  { title: 'Transaction Reports' },
]
</script>

<template>
  <Head title="Transaction Reports" />

  <AppLayout title="Transaction Reports" :breadcrumbs="breadcrumbs">
    <!-- Header -->
    <div class="mb-8 mx-5 mt-5">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
        Transaction Reports
      </h2>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
        Analyze daily, monthly, and annual transaction performance
      </p>
    </div>

    <!-- Summary Cards -->
    <div
      class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 mx-5 gap-6 mb-10"
    >
      <div class="p-5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex items-center gap-3">
          <ArrowRightLeft class="h-6 w-6 text-blue-500" />
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">Today</p>
            <p class="font-bold text-gray-900 dark:text-gray-100">
              {{ summary.today }} transactions
            </p>
            <p class="text-sm text-gray-700 dark:text-gray-300">
              {{ money(summary.today_amount) }}
            </p>
          </div>
        </div>
      </div>

      <div class="p-5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex items-center gap-3">
          <CalendarRange class="h-6 w-6 text-orange-500" />
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">This Month</p>
            <p class="font-bold text-gray-900 dark:text-gray-100">
              {{ summary.this_month }} transactions
            </p>
            <p class="text-sm text-gray-700 dark:text-gray-300">
              {{ money(summary.this_month_amount) }}
            </p>
          </div>
        </div>
      </div>

      <div class="p-5 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="flex items-center gap-3">
          <CalendarClock class="h-6 w-6 text-orange-500" />
          <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">This Year</p>
            <p class="font-bold text-gray-900 dark:text-gray-100">
              {{ summary.this_year }} transactions
            </p>
            <p class="text-sm text-gray-700 dark:text-gray-300">
              {{ money(summary.this_year_amount) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Report Links -->
    <div class="grid grid-cols-1 md:grid-cols-2 mx-5 gap-6">
      <!-- Daily -->
      <Link
        :href="route('reports.transactions.daily')"
        class="group p-6 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm hover:border-orange-500 transition"
      >
        <div class="flex items-center justify-between">
          <div>
            <CalendarDays class="h-8 w-8 text-orange-500 mb-3" />
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              Daily Transactions
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              View all transactions for a selected day
            </p>
          </div>
        </div>
      </Link>

      <!-- Monthly -->
      <Link
        :href="route('reports.transactions.monthly')"
        class="group p-6 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm hover:border-orange-500 transition"
      >
        <CalendarRange class="h-8 w-8 text-orange-500 mb-3" />
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
          Monthly Transactions
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
          Analyze transaction trends by month
        </p>
      </Link>

      <!-- Annual -->
      <Link
        :href="route('reports.transactions.annual')"
        class="group p-6 rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 shadow-sm hover:border-orange-500 transition"
      >
        <CalendarClock class="h-8 w-8 text-orange-500 mb-3" />
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
          Annual Transactions
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
          Yearly transaction performance & growth
        </p>
      </Link>
    </div>
  </AppLayout>
</template>
