<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { HandCoins, ClipboardList, CheckCircle2, Clock } from 'lucide-vue-next'
import { computed } from 'vue'

const props = defineProps<{
  stats: any
  pendingApplications: any[]
  overdueLoans: any[]
  loanPerformanceMetrics: any
}>()

// Currency Formatter
const fmt = (v: any) => {
  const num = Number(v)
  if (isNaN(num)) return 'Ksh 0'
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES',
    maximumFractionDigits: 0,
  }).format(num)
}

// Computed Summary Stats
const dashboardStats = computed(() => ({
  pending: props.stats.loans?.pending_applications || 0,
  underReview: props.stats.loans?.under_review || 0,
  approved: props.stats.loans?.approved_pending_disbursement || 0,
  active: props.stats.loans?.active_loans || 0,
  totalDisbursed: props.stats.portfolio?.total_disbursed || 0,
  outstanding: props.stats.portfolio?.outstanding_balance || 0,
  overdue: props.stats.portfolio?.overdue_amount || 0,
  target: props.stats.targets?.monthly_disbursement_target || 0,
  achieved: props.stats.targets?.monthly_disbursed || 0,
}))
</script>

<template>
   <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">
    <Head title="Loan Officer Dashboard" />

    <div class="space-y-10 p-6 animate-fadeIn bg-gradient-to-b from-white via-blue-50 to-orange-50 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 min-h-screen">
      <!-- Header -->
      <header class="text-center space-y-2">
        <div class="bg-gradient-to-r from-[#0b1b3f] to-blue-900 p-5 rounded-2xl">
            <h1 class="text-2xl font-bold text-white dark:text-white tracking-tight">
            Loan Officer Dashboard
            </h1>
            <p class="text-orange-500 dark:text-orange-400 text-base">
            Monitor loan performance, portfolio health, and monthly targets.
            </p>
        </div>
      </header>

      <!-- Loan Summary Cards -->
      <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <Card
          v-for="(card, i) in [
            { title: 'Pending Applications', icon: ClipboardList, color: 'from-blue-700 to-blue-900', value: dashboardStats.pending },
            { title: 'Under Review', icon: Clock, color: 'from-orange-500 to-orange-700', value: dashboardStats.underReview },
            { title: 'Approved (Pending Disbursement)', icon: CheckCircle2, color: 'from-green-600 to-green-800', value: dashboardStats.approved },
            { title: 'Active Loans', icon: HandCoins, color: 'from-[#0b1b3f] to-blue-900', value: dashboardStats.active },
          ]"
          :key="i"
          class="relative overflow-hidden group rounded-2xl border border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-800/60 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300 backdrop-blur-sm"
        >
          <div class="absolute inset-0 bg-gradient-to-r opacity-10 group-hover:opacity-20 transition-all duration-500" :class="card.color"></div>

          <CardHeader class="flex flex-row items-center justify-between pb-1 relative z-10">
            <CardTitle class="text-sm font-semibold text-gray-700 dark:text-gray-200">
              {{ card.title }}
            </CardTitle>
            <component :is="card.icon" class="h-6 w-6 text-white p-1.5 rounded-md bg-gradient-to-r" :class="card.color" />
          </CardHeader>

          <CardContent class="relative z-10">
            <div class="text-3xl font-bold text-[#0b1b3f] dark:text-white">
              {{ card.value }}
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Portfolio & Progress Section -->
      <div class="grid gap-6 xl:grid-cols-2">
        <!-- Portfolio Summary -->
        <Card class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-800/60 shadow-sm hover:shadow-md transition-all backdrop-blur-sm">
          <CardHeader>
            <CardTitle class="text-lg font-semibold text-[#0b1b3f] dark:text-white">
              Portfolio Overview
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
              <div class="bg-blue-50 dark:bg-gray-800 rounded-xl p-4 hover:bg-blue-100 dark:hover:bg-gray-700 transition">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Total Disbursed</p>
                <p class="text-lg font-semibold text-[#0b1b3f] dark:text-blue-400 mt-1">
                  {{ fmt(dashboardStats.totalDisbursed) }}
                </p>
              </div>
              <div class="bg-indigo-50 dark:bg-gray-800 rounded-xl p-4 hover:bg-indigo-100 dark:hover:bg-gray-700 transition">
                <p class="text-sm font-medium text-gray-600">Outstanding Balance</p>
                <p class="text-lg font-semibold text-blue-900 dark:text-indigo-400 mt-1">
                  {{ fmt(dashboardStats.outstanding) }}
                </p>
              </div>
              <div class="bg-orange-50 dark:bg-gray-800 rounded-xl p-4 hover:bg-orange-100 dark:hover:bg-gray-700 transition">
                <p class="text-sm font-medium text-gray-600 dark:text-gray-300">Overdue Amount</p>
                <p class="text-lg font-semibold text-orange-700 dark:text-orange-400 mt-1">
                  {{ fmt(dashboardStats.overdue) }}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Monthly Target Progress -->
        <Card class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-800/60 shadow-sm hover:shadow-md transition-all backdrop-blur-sm">
          <CardHeader>
            <CardTitle class="text-lg font-semibold text-[#0b1b3f] dark:text-white">
              Monthly Disbursement Progress
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
              <p>Target: {{ fmt(dashboardStats.target) }}</p>
              <p>Achieved: {{ fmt(dashboardStats.achieved) }}</p>
            </div>

            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
              <div
                class="bg-gradient-to-r from-[#ff7b00] to-orange-600 h-3 rounded-full transition-all duration-700 ease-in-out"
                :style="{ width: `${Math.min((dashboardStats.achieved / dashboardStats.target) * 100, 100)}%` }"
              ></div>
            </div>

            <p class="text-right text-xs text-gray-500 mt-1">
              {{ Math.min((dashboardStats.achieved / dashboardStats.target) * 100, 100).toFixed(1) }}% of target reached
            </p>
          </CardContent>
        </Card>
      </div>

      <!-- Recent Pending Applications -->
      <div>
        <h2 class="text-lg font-semibold mb-3 text-[#0b1b3f] dark:text-white">
          Recent Pending Loan Applications
        </h2>
        <Card class="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700 shadow-sm bg-white/90 dark:bg-gray-800/70 backdrop-blur-sm">
          <CardContent class="p-0">
            <table class="min-w-full text-sm text-left border-collapse">
              <thead class="bg-gradient-to-r from-[#0b1b3f] to-blue-900 text-white">
                <tr>
                  <th class="py-3 px-4 font-medium">Member</th>
                  <th class="py-3 px-4 font-medium">Product</th>
                  <th class="py-3 px-4 font-medium">Amount</th>
                  <th class="py-3 px-4 font-medium">Date</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(loan, i) in pendingApplications"
                  :key="i"
                  class="border-b dark:border-gray-700 hover:bg-orange-50 dark:hover:bg-gray-700 transition-colors"
                >
                  <td class="py-3 px-4">{{ loan.member?.first_name }} {{ loan.member?.last_name }}</td>
                  <td class="py-3 px-4">{{ loan.loan_product?.name || '—' }}</td>
                  <td class="py-3 px-4">{{ fmt(loan.applied_amount) }}</td>
                  <td class="py-3 px-4">{{ new Date(loan.created_at).toLocaleDateString() }}</td>
                </tr>

                <tr v-if="!pendingApplications?.length">
                  <td colspan="4" class="text-center text-gray-400 py-5">
                    No pending loan applications
                  </td>
                </tr>
              </tbody>
            </table>
          </CardContent>
        </Card>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.animate-fadeIn {
  animation: fadeIn 0.6s ease-out;
}
</style>
