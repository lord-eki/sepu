<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import {
  Users,
  LineChart,
  CreditCard,
  Droplet,
  Landmark,
  Shield,
} from 'lucide-vue-next'

defineProps<{
  reports: Record<string, any>
  year: number
  quarter?: string | null
  periods: any
}>()

const breadcrumbs = [
  { title: 'Regulatory Reports', href: route('reports.regulatoryReport.index') },
  { title: 'Statutory Reports' },
]

const sections = [
  {
    key: 'membership_report',
    title: 'Membership Report',
    icon: Users,
  },
  {
    key: 'financial_performance',
    title: 'Financial Performance',
    icon: LineChart,
  },
  {
    key: 'loan_portfolio_report',
    title: 'Loan Portfolio',
    icon: CreditCard,
  },
  {
    key: 'liquidity_report',
    title: 'Liquidity Position',
    icon: Droplet,
  },
  {
    key: 'capital_adequacy',
    title: 'Capital Adequacy',
    icon: Landmark,
  },
  {
    key: 'governance_report',
    title: 'Governance & Management',
    icon: Shield,
  },
]
</script>

<template>
  <Head title="Statutory Reports" />

  <AppLayout title="Statutory Reports" :breadcrumbs="breadcrumbs">
    <!-- Header -->
    <div class="mb-8">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
        Statutory Reports
      </h2>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Reporting Period:
        <span class="font-medium text-gray-700 dark:text-gray-300">
          {{ year }}
          <span v-if="quarter">— {{ quarter }}</span>
        </span>
      </p>
    </div>

    <!-- Report Sections -->
    <div class="space-y-6">
      <div
        v-for="section in sections"
        :key="section.key"
        class="rounded-2xl border border-gray-200 dark:border-gray-700 
               bg-white dark:bg-gray-900 p-6 shadow-sm"
      >
        <!-- Section Header -->
        <div class="flex items-center gap-3 mb-4">
          <div
            class="p-2 rounded-lg bg-orange-50 dark:bg-orange-900/20 
                   text-orange-600"
          >
            <component :is="section.icon" class="h-5 w-5" />
          </div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
            {{ section.title }}
          </h3>
        </div>

        <!-- Section Content -->
        <pre
          class="text-sm text-gray-700 dark:text-gray-300 
                 bg-gray-50 dark:bg-gray-800 rounded-xl p-4 overflow-x-auto"
        >
{{ JSON.stringify(reports[section.key], null, 2) }}
        </pre>
      </div>
    </div>

    <!-- Footer Note -->
    <div
      class="mt-10 rounded-xl border border-blue-200 dark:border-blue-800 
             bg-blue-50 dark:bg-blue-900/20 p-5"
    >
      <p class="text-sm text-blue-800 dark:text-blue-200">
        ℹ️ These statutory reports are generated automatically based on system data
        and comply with regulatory reporting standards.
      </p>
    </div>
  </AppLayout>
</template>
