<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref } from 'vue'
import type { BreadcrumbItem } from '@/types'

// Lucide Icons
import {
  Landmark,
  Users,
  Banknote,
  Receipt,
  FileBadge,
  Settings
} from 'lucide-vue-next'

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Reports' }
]

const reportCards = ref([
  {
    title: 'Financial Reports',
    description: 'Balance sheet, income statement, cash flow & trial balance.',
    icon: Landmark,
    route: route('reports.financial.index'),
  },
  {
    title: 'Member Reports',
    description: 'Member register, shares, savings & loans.',
    icon: Users,
    route: route('reports.membersReport.index'),
  },
  {
    title: 'Loan Reports',
    description: 'Portfolio, arrears, disbursements & collections.',
    icon: Banknote,
    route: route('reports.loansReport.index'),
  },
  {
    title: 'Transaction Reports',
    description: 'Daily, monthly & annual transactions.',
    icon: Receipt,
    route: route('reports.transactionsReport.index'),
  },
  {
    title: 'Regulatory Reports',
    description: 'Compliance and statutory reporting.',
    icon: FileBadge,
    route: route('reports.regulatoryReport.index'),
  },
  {
    title: 'Custom Reports',
    description: 'Build and export custom reports using filters.',
    icon: Settings,
    route: route('reports.custom.builder'),
  }
])
</script>

<template>

  <Head title="Reports" />

  <AppLayout :breadcrumbs="breadcrumbs" title="Reports">
    <div class="px-6 py-6 space-y-8">

      <!-- Header -->
      <div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0a2342] dark:text-blue-400">
          Reports
        </h1>
        <p class="text-sm sm:text-base text-gray-500 dark:text-gray-400 mt-2 max-w-3xl">
          Access, analyze, and export reports across financial, member, loan, and transaction data.
        </p>
      </div>

      <!-- Cards Grid -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        <Link v-for="card in reportCards" :key="card.title" :href="card.route" class="group relative overflow-hidden rounded-3xl
                 border border-gray-200 dark:border-gray-700
                 bg-white dark:bg-gray-900
                 p-6 shadow-sm hover:shadow-xl
                 transition-all duration-300 hover:-translate-y-1">
        <!-- Gradient Hover Glow -->
        <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition
                   bg-gradient-to-br from-orange-500/10 via-transparent to-blue-500/10" />

        <!-- Icon -->
        <div class="relative flex h-14 w-14 items-center justify-center rounded-2xl
                   bg-orange-50 dark:bg-orange-500/10 mb-4">
          <component :is="card.icon" class="h-7 w-7 text-orange-600 dark:text-orange-400" />
        </div>

        <!-- Text -->
        <div class="relative">
          <h2 class="text-lg font-semibold text-[#0a2342] dark:text-gray-100
                     group-hover:text-orange-600 dark:group-hover:text-orange-400
                     transition">
            {{ card.title }}
          </h2>

          <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
            {{ card.description }}
          </p>
        </div>

        <!-- Arrow -->
        <span class="absolute bottom-5 right-5 text-gray-300 dark:text-gray-600
                   group-hover:text-orange-500 transition">
          →
        </span>
        </Link>
      </div>

    </div>
  </AppLayout>
</template>
