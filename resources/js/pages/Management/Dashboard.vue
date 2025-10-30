<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { TrendingUp, Wallet, HandCoins, ArrowUpRight, BarChart3, ClipboardCheck, AlertTriangle } from 'lucide-vue-next'

const props = defineProps<{
  stats: {
    financial_summary: {
      total_assets: number
      loan_portfolio: number
      monthly_income: number
      monthly_expenses: number
    }
    performance: {
      portfolio_at_risk: number
      loan_recovery_rate: number
      member_growth_rate: number
    }
    compliance: {
      overdue_vouchers: number
      pending_reviews: number
    }
  }
  loanPortfolioAnalysis: Array<{ name: string; count: number; total_outstanding: number }>
}>()

const fmt = (v: number) =>
  new Intl.NumberFormat('en-KE', { style: 'currency', currency: 'KES', maximumFractionDigits: 0 }).format(v)
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">
    <Head title="Management Dashboard" />

    <div class="min-h-screen bg-[#f5f7fb] p-6 space-y-10">

      <!-- Header Banner -->
      <div class="bg-gradient-to-r from-[#0B2B40] to-[#133263] rounded-3xl shadow-lg p-6 text-white flex flex-col sm:flex-row justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Management <span class="text-orange-500">Dashboard</span></h1>
          <p class="text-blue-100 text-sm">Strategic overview and SACCO performance</p>
        </div>
        <div class="flex items-center gap-3 mt-3 sm:mt-0">
          <div class="h-1.5 w-24 bg-orange-500 rounded-full"></div>
        </div>
      </div>

      <!-- Financial Overview -->
      <section>
        <h2 class="text-lg font-semibold text-[#0a2342] mb-3">Financial Summary</h2>
        <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
          <Card
            v-for="item in [
              { title: 'Total Assets', value: fmt(stats.financial_summary.total_assets), icon: Wallet },
              { title: 'Loan Portfolio', value: fmt(stats.financial_summary.loan_portfolio), icon: HandCoins },
              { title: 'Monthly Income', value: fmt(stats.financial_summary.monthly_income), icon: TrendingUp },
              { title: 'Monthly Expenses', value: fmt(stats.financial_summary.monthly_expenses), icon: ArrowUpRight },
            ]"
            :key="item.title"
            class="bg-white rounded-2xl border border-gray-100 shadow-md hover:shadow-lg hover:-translate-y-1 transition-all duration-300"
          >
            <CardHeader class="flex justify-between items-center">
              <CardTitle class="text-sm font-medium text-gray-700">{{ item.title }}</CardTitle>
              <div class="p-2 rounded-xl bg-orange-100 text-orange-600">
                <component :is="item.icon" class="h-5 w-5" />
              </div>
            </CardHeader>
            <CardContent>
              <div class="text-lg font-semibold text-[#0a2342]">{{ item.value }}</div>
            </CardContent>
          </Card>
        </div>
      </section>

      <!-- Performance Overview -->
      <section>
        <h2 class="text-lg font-semibold text-[#0a2342] mb-3">Performance Indicators</h2>
        <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
          <Card class="rounded-2xl bg-white border border-gray-100 shadow-md hover:shadow-lg transition-all">
            <CardHeader class="flex justify-between items-center">
              <CardTitle class="text-sm font-medium text-gray-700">Portfolio at Risk</CardTitle>
              <AlertTriangle class="h-5 w-5 text-red-600" />
            </CardHeader>
            <CardContent>
              <p class="text-3xl font-semibold text-red-600">{{ stats.performance.portfolio_at_risk }}%</p>
              <p class="text-sm text-gray-500 mt-1">Lower is better</p>
            </CardContent>
          </Card>

          <Card class="rounded-2xl bg-white border border-gray-100 shadow-md hover:shadow-lg transition-all">
            <CardHeader class="flex justify-between items-center">
              <CardTitle class="text-sm font-medium text-gray-700">Loan Recovery Rate</CardTitle>
              <ClipboardCheck class="h-5 w-5 text-green-600" />
            </CardHeader>
            <CardContent>
              <p class="text-3xl font-semibold text-green-600">{{ stats.performance.loan_recovery_rate }}%</p>
              <p class="text-sm text-gray-500 mt-1">Higher is better</p>
            </CardContent>
          </Card>

          <Card class="rounded-2xl bg-white border border-gray-100 shadow-md hover:shadow-lg transition-all">
            <CardHeader class="flex justify-between items-center">
              <CardTitle class="text-sm font-medium text-gray-700">Member Growth Rate</CardTitle>
              <BarChart3 class="h-5 w-5 text-blue-600" />
            </CardHeader>
            <CardContent>
              <p class="text-3xl font-semibold text-blue-600">{{ stats.performance.member_growth_rate }}%</p>
              <p class="text-sm text-gray-500 mt-1">Year-over-year growth</p>
            </CardContent>
          </Card>
        </div>
      </section>

      <!-- Compliance Summary -->
      <section>
        <h2 class="text-lg font-semibold text-[#0a2342] mb-3">Compliance Summary</h2>
        <div class="grid gap-6 sm:grid-cols-2">
          <Card class="bg-white rounded-2xl border border-gray-100 shadow-md hover:shadow-lg transition-all">
            <CardHeader class="flex justify-between items-center">
              <CardTitle class="text-sm font-medium text-gray-700">Overdue Vouchers</CardTitle>
              <AlertTriangle class="h-5 w-5 text-orange-500" />
            </CardHeader>
            <CardContent>
              <p class="text-3xl font-semibold text-[#0a2342]">{{ stats.compliance.overdue_vouchers }}</p>
              <p class="text-sm text-gray-500 mt-1">Requires urgent action</p>
            </CardContent>
          </Card>

          <Card class="bg-white rounded-2xl border border-gray-100 shadow-md hover:shadow-lg transition-all">
            <CardHeader class="flex justify-between items-center">
              <CardTitle class="text-sm font-medium text-gray-700">Pending Reviews</CardTitle>
              <ClipboardCheck class="h-5 w-5 text-blue-600" />
            </CardHeader>
            <CardContent>
              <p class="text-3xl font-semibold text-[#0a2342]">{{ stats.compliance.pending_reviews }}</p>
              <p class="text-sm text-gray-500 mt-1">Awaiting management feedback</p>
            </CardContent>
          </Card>
        </div>
      </section>

      <!-- Loan Portfolio Analysis -->
      <section>
        <h2 class="text-lg font-semibold text-[#0a2342] mb-3">Loan Portfolio Analysis</h2>
        <div class="overflow-x-auto rounded-2xl border border-gray-100 bg-white shadow-md">
          <table class="w-full text-sm">
            <thead class="bg-[#0B2B40] text-white uppercase text-xs tracking-wide">
              <tr>
                <th class="p-3 text-left">Product</th>
                <th class="p-3 text-left">Count</th>
                <th class="p-3 text-left">Outstanding</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="p in loanPortfolioAnalysis"
                :key="p.name"
                class="border-t hover:bg-orange-50 transition"
              >
                <td class="p-3 font-medium text-[#0a2342]">{{ p.name }}</td>
                <td class="p-3">{{ p.count }}</td>
                <td class="p-3 text-orange-600 font-semibold">{{ fmt(p.total_outstanding) }}</td>
              </tr>
              <tr v-if="!loanPortfolioAnalysis.length">
                <td colspan="3" class="p-6 text-center text-gray-500">No data available</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </AppLayout>
</template>
