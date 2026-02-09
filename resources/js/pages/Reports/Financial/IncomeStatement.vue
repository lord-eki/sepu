<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { CalendarDays } from 'lucide-vue-next'
import type { BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'

const props = defineProps<{
  revenue: Record<string, number>
  expenses: Record<string, number>
  totals: {
    revenue: number
    expenses: number
    net_income: number
  }
  start_date: string
  end_date: string
}>()

const startDate = ref(props.start_date)
const endDate = ref(props.end_date)

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Financial Reports', href: route('reports.financial.index') },
  { title: 'Income Statement' },
]

function applyDateFilter() {
  router.get(
    route('reports.financial.income-statement'),
    { start_date: startDate.value, end_date: endDate.value },
    { preserveState: true, replace: true }
  )
}

function money(value: number) {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES',
  }).format(value ?? 0)
}
</script>

<template>
  <Head title="Income Statement" />

  <AppLayout title="Income Statement" :breadcrumbs="breadcrumbs">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8 mx-6 mt-6">
      <div>
        <h2 class="text-2xl font-extrabold text-[#0a2342] dark:text-blue-400">
          Income Statement
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
          Performance report from selected period
        </p>
      </div>

      <!-- Date Range Picker -->
      <div class="flex items-center gap-2">
        <CalendarDays class="h-5 w-5 text-orange-500" />
        <input
          type="date"
          v-model="startDate"
          @change="applyDateFilter"
          class="rounded-xl border border-gray-300 dark:border-gray-700
                 bg-white dark:bg-gray-900 px-3 py-2 text-sm
                 text-gray-700 dark:text-gray-200
                 focus:outline-none focus:ring-2 focus:ring-orange-500"
        />
        <span class="text-gray-500 dark:text-gray-400">to</span>
        <input
          type="date"
          v-model="endDate"
          @change="applyDateFilter"
          class="rounded-xl border border-gray-300 dark:border-gray-700
                 bg-white dark:bg-gray-900 px-3 py-2 text-sm
                 text-gray-700 dark:text-gray-200
                 focus:outline-none focus:ring-2 focus:ring-orange-500"
        />
      </div>
    </div>

    <!-- Main Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mx-6">
      <!-- Revenue Card -->
      <div class="rounded-3xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-md">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-t-3xl">
          <h3 class="text-lg font-bold text-[#0a2342] dark:text-blue-400">Revenue</h3>
        </div>

        <div class="p-6 space-y-3">
          <div v-for="(value, key) in revenue" :key="key" class="flex justify-between text-sm text-gray-700 dark:text-gray-300">
            <span>{{ key.replace(/_/g, ' ').toUpperCase() }}</span>
            <span class="font-medium">{{ money(value) }}</span>
          </div>
        </div>

        <div class="flex justify-between items-center px-6 py-4 border-t-2 border-orange-400 font-bold text-base text-[#0a2342] dark:text-orange-400 bg-orange-50 dark:bg-gray-800 rounded-b-3xl">
          <span>Total Revenue</span>
          <span>{{ money(totals.revenue) }}</span>
        </div>
      </div>

      <!-- Expenses Card -->
      <div class="rounded-3xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-md">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 rounded-t-3xl">
          <h3 class="text-lg font-bold text-[#0a2342] dark:text-blue-400">Expenses</h3>
        </div>

        <div class="p-6 space-y-3">
          <div v-for="(value, key) in expenses" :key="key" class="flex justify-between text-sm text-gray-700 dark:text-gray-300">
            <span>{{ key.replace(/_/g, ' ').toUpperCase() }}</span>
            <span class="font-medium">{{ money(value) }}</span>
          </div>
        </div>

        <div class="flex justify-between items-center px-6 py-4 border-t-2 border-orange-400 font-bold text-base text-[#0a2342] dark:text-orange-400 bg-orange-50 dark:bg-gray-800 rounded-b-3xl">
          <span>Total Expenses</span>
          <span>{{ money(totals.expenses) }}</span>
        </div>
      </div>
    </div>

    <!-- Net Income -->
    <div class="mt-8 rounded-3xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 p-6 shadow-md flex justify-between items-center font-bold text-lg text-[#0a2342] dark:text-blue-400 mx-6">
      <span>Net Income</span>
      <span>{{ money(totals.net_income) }}</span>
    </div>
  </AppLayout>
</template>
