<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import { CalendarDays } from 'lucide-vue-next'
import type { BreadcrumbItem } from '@/types'
import { route } from 'ziggy-js'

const props = defineProps<{
  accounts: { name: string; debit: number; credit: number }[]
  totals: {
    debits: number
    credits: number
    difference: number
  }
  date: string
}>()

const selectedDate = ref(props.date)

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Financial Reports', href: route('reports.financial.index') },
  { title: 'Trial Balance' },
]

function applyDateFilter() {
  router.get(
    route('reports.financial.trial-balance'),
    { date: selectedDate.value },
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

  <Head title="Trial Balance" />

  <AppLayout title="Trial Balance" :breadcrumbs="breadcrumbs">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 mx-6 mt-6">
      <div>
        <h2 class="text-2xl font-extrabold text-[#0a2342] dark:text-blue-400">
          Trial Balance
        </h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
          Debits and Credits as at selected date
        </p>
      </div>

      <!-- Date Picker -->
      <div class="flex items-center gap-2">
        <CalendarDays class="h-5 w-5 text-orange-500" />
        <input type="date" v-model="selectedDate" @change="applyDateFilter" class="rounded-xl border border-gray-300 dark:border-gray-700
                 bg-white dark:bg-gray-900 px-3 py-2 text-sm
                 text-gray-700 dark:text-gray-200
                 focus:outline-none focus:ring-2 focus:ring-orange-500" />
      </div>
    </div>

    <!-- Table Card -->
    <div
      class="overflow-x-auto rounded-3xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-md mx-6">
      <table class="min-w-full text-left text-sm text-gray-700 dark:text-gray-300">
        <thead class="bg-blue-50 dark:bg-[#0a2342]">
          <tr>
            <th class="px-6 py-3 font-semibold text-[#0a2342] dark:text-blue-400">Account</th>
            <th class="px-6 py-3 font-semibold text-[#0a2342] dark:text-blue-400">Debit</th>
            <th class="px-6 py-3 font-semibold text-[#0a2342] dark:text-blue-400">Credit</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          <tr v-for="account in accounts" :key="account.name"
            class="hover:bg-blue-50 dark:hover:bg-gray-800 transition-colors">
            <td class="px-6 py-3">{{ account.name }}</td>
            <td class="px-6 py-3 font-medium">{{ money(account.debit) }}</td>
            <td class="px-6 py-3 font-medium">{{ money(account.credit) }}</td>
          </tr>
        </tbody>

        <!-- Totals Row -->
        <tfoot class="bg-orange-50 dark:bg-gray-800 font-bold text-[#0a2342] dark:text-orange-400">
          <tr>
            <td class="px-6 py-3">Totals</td>
            <td class="px-6 py-3">{{ money(totals.debits) }}</td>
            <td class="px-6 py-3">{{ money(totals.credits) }}</td>
          </tr>
          <tr>
            <td class="px-6 py-3" colspan="2">Difference</td>
            <td class="px-6 py-3">{{ money(totals.difference) }}</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </AppLayout>
</template>
