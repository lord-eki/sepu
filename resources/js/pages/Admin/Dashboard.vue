<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Activity, Users, Banknote, FileWarning, ArrowRightCircle, Clock, Database, AlertTriangle } from 'lucide-vue-next'

interface Stats {
  members: { total: number; active: number; new_this_month: number }
  financial: { total_savings: number; total_shares: number; total_deposits: number }
  loans: { total_portfolio: number; active_loans: number; pending_applications: number; arrears_amount: number }
  transactions: { today: number; this_week: number; this_month: number }
}

defineProps<{
  stats: Stats
  recentActivities: Array<any>
  pendingApprovals: Record<string, number>
  systemHealth: Record<string, any>
}>()
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">
    <Head title="Admin Dashboard" />

    <div class="p-6 min-h-screen space-y-10 bg-gray-50">
      <!-- Header -->
      <header class="mb-6">
        <h1 class="text-lg sm:text-xl font-bold text-gray-900">Admin Dashboard</h1>
        <p class="text-gray-500 text-sm">System overview and monitoring</p>
      </header>

      <!-- Stats Grid -->
      <section class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <Card v-for="stat in [
          { title: 'Total Members', value: stats.members.total, sub: `Active: ${stats.members.active}`, icon: Users, color: 'bg-blue-100 text-blue-600' },
          { title: 'Total Savings', value: stats.financial.total_savings.toLocaleString(), sub: `Shares: ${stats.financial.total_shares}`, icon: Banknote, color: 'bg-green-100 text-green-600' },
          { title: 'Active Loans', value: stats.loans.active_loans, sub: `Pending: ${stats.loans.pending_applications}`, icon: Activity, color: 'bg-yellow-100 text-yellow-600' },
          { title: 'Transactions Today', value: stats.transactions.today, sub: `This Month: ${stats.transactions.this_month}`, icon: FileWarning, color: 'bg-purple-100 text-purple-600' }
        ]" :key="stat.title" class="bg-white rounded-2xl shadow-sm hover:shadow-md transition">
          <CardHeader class="flex items-center justify-between pb-2">
            <CardTitle class="text-sm font-medium text-gray-700">{{ stat.title }}</CardTitle>
            <div class="p-2 rounded-xl" :class="stat.color">
              <component :is="stat.icon" class="h-5 w-5" />
            </div>
          </CardHeader>
          <CardContent>
            <div class="text-lg sm:text-xl font-bold text-gray-900">{{ stat.value }}</div>
            <p class="text-xs text-gray-500">{{ stat.sub }}</p>
          </CardContent>
        </Card>
      </section>

      <!-- Recent Activities -->
      <section>
        <h2 class="text-lg font-semibold mb-3 text-gray-900">Recent Activities</h2>
        <div class="bg-white rounded-2xl shadow-sm divide-y">
          <div v-for="(item, i) in recentActivities" :key="i" class="flex items-center justify-between p-4 hover:bg-gray-50">
            <div class="flex items-start gap-3">
              <ArrowRightCircle class="h-5 w-5 text-blue-500 mt-0.5" />
              <div>
                <p class="text-sm font-medium text-gray-800">{{ item.description }}</p>
                <p class="text-xs text-gray-500">{{ new Date(item.time).toLocaleString() }}</p>
              </div>
            </div>
            <span class="text-sm font-semibold text-green-700">Ksh. {{ item.amount }}</span>
          </div>
        </div>
      </section>

      <!-- Pending Approvals & System Health -->
      <section class="grid gap-6 md:grid-cols-2">
        <Card class="bg-white rounded-2xl shadow-sm">
          <CardHeader>
            <CardTitle class="text-base font-semibold text-gray-900">Pending Approvals</CardTitle>
          </CardHeader>
          <CardContent>
            <ul class="space-y-2 text-sm">
              <li class="flex justify-between">Loans <span class="px-2 py-0.5 rounded-lg bg-yellow-100 text-yellow-700 font-semibold">{{ pendingApprovals.loans }}</span></li>
              <li class="flex justify-between">Vouchers <span class="px-2 py-0.5 rounded-lg bg-blue-100 text-blue-700 font-semibold">{{ pendingApprovals.vouchers }}</span></li>
              <li class="flex justify-between">Members <span class="px-2 py-0.5 rounded-lg bg-green-100 text-green-700 font-semibold">{{ pendingApprovals.member_applications }}</span></li>
            </ul>
          </CardContent>
        </Card>

        <Card class="bg-white rounded-2xl shadow-sm">
          <CardHeader>
            <CardTitle class="text-base font-semibold text-gray-900">System Health</CardTitle>
          </CardHeader>
          <CardContent>
            <ul class="space-y-2 text-sm">
              <li class="flex items-center gap-2"><Database class="h-4 w-4 text-green-600" /> Status: <span class="font-medium text-green-700">{{ systemHealth.database_status }}</span></li>
              <li class="flex items-center gap-2"><Clock class="h-4 w-4 text-gray-500" /> Last Backup: <span>{{ new Date(systemHealth.last_backup).toLocaleString() }}</span></li>
              <li class="flex items-center gap-2"><Users class="h-4 w-4 text-blue-600" /> Active Users: <span class="font-medium">{{ systemHealth.active_users }}</span></li>
              <li class="flex items-center gap-2"><AlertTriangle class="h-4 w-4 text-red-600" /> Errors: <span class="font-medium text-red-600">{{ systemHealth.system_errors }}</span></li>
            </ul>
          </CardContent>
        </Card>
      </section>
    </div>
  </AppLayout>
</template>
