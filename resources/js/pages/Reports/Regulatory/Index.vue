<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ShieldCheck, FileText } from 'lucide-vue-next'

const breadcrumbs = [
  { title: 'Reports', href: route('reports.index') },
  { title: 'Regulatory Reports' },
]

const reports = [
  {
    title: 'Statutory Reports',
    description: 'Official statutory and regulator-required reports',
    icon: FileText,
    route: 'reports.regulatory.statutory',
  },
  {
    title: 'Compliance Reports',
    description: 'KYC, AML, liquidity, capital and compliance monitoring',
    icon: ShieldCheck,
    route: 'reports.regulatory.compliance',
  },
]

function goTo(routeName: string) {
  router.get(route(routeName))
}
</script>

<template>
  <Head title="Regulatory Reports" />

  <AppLayout title="Regulatory Reports" :breadcrumbs="breadcrumbs">
    <!-- Intro -->
    <div class="mb-8">
      <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
        Regulatory & Compliance Reports
      </h2>
      <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Reports required for regulators, audits, and compliance oversight
      </p>
    </div>

    <!-- Report Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
      <div
        v-for="report in reports"
        :key="report.title"
        @click="goTo(report.route)"
        class="cursor-pointer rounded-2xl border border-gray-200 dark:border-gray-700 
               bg-white dark:bg-gray-900 p-6 shadow-sm hover:shadow-lg transition-all"
      >
        <div class="flex items-start gap-4">
          <div
            class="p-3 rounded-xl bg-orange-50 dark:bg-orange-900/20 text-orange-600"
          >
            <component :is="report.icon" class="h-6 w-6" />
          </div>

          <div>
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
              {{ report.title }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
              {{ report.description }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Compliance Notice -->
    <div
      class="mt-10 rounded-xl border border-yellow-200 dark:border-yellow-800 
             bg-yellow-50 dark:bg-yellow-900/20 p-5"
    >
      <p class="text-sm text-yellow-800 dark:text-yellow-200">
        ⚠️ Regulatory reports should be reviewed before submission to regulators.
        Ensure all data is verified and approved.
      </p>
    </div>
  </AppLayout>
</template>
