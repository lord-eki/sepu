<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { BarChart3, TrendingUp, Wallet, Scale } from 'lucide-vue-next'

const breadcrumbs = [
  { title: 'Reports', href: '/reports' },
  { title: 'Financial Reports' },
]

const reports = [
  {
    title: 'Balance Sheet',
    description: 'Assets, Liabilities & Equity',
    icon: Scale,
    href: route('reports.financial.balance-sheet'),
  },
  {
    title: 'Income Statement',
    description: 'Revenue & Expenses',
    icon: TrendingUp,
    href: route('reports.financial.income-statement'),
  },
  {
    title: 'Cash Flow',
    description: 'Operating, Investing & Financing',
    icon: Wallet,
    href: route('reports.financial.cash-flow'),
  },
  {
    title: 'Trial Balance',
    description: 'Debits & Credits',
    icon: BarChart3,
    href: route('reports.financial.trial-balance'),
  },
]
</script>

<template>

  <Head title="Financial Reports" />

  <AppLayout :breadcrumbs="breadcrumbs" title="Financial Reports">
    <!-- Header -->
    <div class="mb-6 mt-5 mx-6">
      <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">
        Financial Reports
      </h2>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        View and analyze financial performance and statements
      </p>
    </div>

    <!-- Reports Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 m-5 lg:m-10">
      <Link v-for="report in reports" :key="report.title" :href="report.href" class="group relative overflow-hidden rounded-2xl border border-[#0a2342]/20
               dark:border-gray-700 bg-white dark:bg-gray-900 p-6
               transition-all duration-300 hover:-translate-y-1 hover:shadow-xl">
      <!-- Gradient Glow -->
      <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition
                 bg-gradient-to-br from-orange-500/10 via-transparent to-blue-500/10" />

      <!-- Icon -->
      <div class="relative flex h-12 w-12 items-center justify-center rounded-xl
                 bg-orange-50 dark:bg-orange-500/10 mb-4">
        <component :is="report.icon" class="h-6 w-6 text-orange-600 dark:text-orange-400" />
      </div>

      <!-- Text -->
      <div class="relative">
        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100
                   group-hover:text-orange-600 dark:group-hover:text-orange-400 transition">
          {{ report.title }}
        </h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
          {{ report.description }}
        </p>
      </div>

      <!-- Arrow -->
      <span class="absolute bottom-5 right-5 text-gray-300 dark:text-gray-600
                 group-hover:text-orange-500 transition">
        →
      </span>
      </Link>
    </div>
  </AppLayout>
</template>
