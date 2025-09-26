<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Activity, Users, Banknote, FileWarning } from 'lucide-vue-next'

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

    <div class="p-6 min-h-screen space-y-8 bg-gray-100">
      <!-- Header -->
      <header class="border-b pb-3 mb-6">
        <h1 class="text-lg sm:text-xl font-bold text-gray-900">Admin Dashboard</h1>
        <p class="text-gray-600 text-sm">System overview and monitoring</p>
      </header>

      <!-- Stats Grid -->
      <section class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <Card class="border border-gray-300 bg-white rounded-xl">
          <CardHeader class="flex justify-between pb-1">
            <CardTitle class="text-sm font-semibold text-gray-800">Total Members</CardTitle>
            <Users class="h-4 w-4 text-blue-600" />
          </CardHeader>
          <CardContent>
            <div class="text-xl font-bold text-green-900">{{ stats.members.total }}</div>
            <p class="text-xs text-gray-600">Active: {{ stats.members.active }}</p>
          </CardContent>
        </Card>

        <Card class="border border-gray-300 bg-white rounded-xl">
          <CardHeader class="flex justify-between pb-1">
            <CardTitle class="text-sm font-semibold text-gray-800">Total Savings</CardTitle>
            <Banknote class="h-4 w-4 text-blue-600" />
          </CardHeader>
          <CardContent>
            <div class="text-xl font-bold text-green-900">
              {{ stats.financial.total_savings.toLocaleString() }}
            </div>
            <p class="text-xs text-gray-600">Shares: {{ stats.financial.total_shares }}</p>
          </CardContent>
        </Card>

        <Card class="border border-gray-300 bg-white rounded-xl">
          <CardHeader class="flex justify-between pb-1">
            <CardTitle class="text-sm font-semibold text-gray-800">Active Loans</CardTitle>
            <Activity class="h-4 w-4 text-blue-600" />
          </CardHeader>
          <CardContent>
            <div class="text-xl font-bold text-green-900">{{ stats.loans.active_loans }}</div>
            <p class="text-xs text-gray-600">Pending: {{ stats.loans.pending_applications }}</p>
          </CardContent>
        </Card>

        <Card class="border border-gray-300 bg-white rounded-xl">
          <CardHeader class="flex justify-between pb-1">
            <CardTitle class="text-sm font-semibold text-gray-800">Transactions Today</CardTitle>
            <FileWarning class="h-4 w-4 text-blue-600" />
          </CardHeader>
          <CardContent>
            <div class="text-xl font-bold text-green-900">{{ stats.transactions.today }}</div>
            <p class="text-xs text-gray-600">This Month: {{ stats.transactions.this_month }}</p>
          </CardContent>
        </Card>
      </section>

      <!-- Recent Activities -->
      <section>
        <h2 class="text-lg font-bold mb-2 text-gray-900 border-b pb-2">Recent Activities</h2>
        <div class="bg-white p-4 rounded-lg border border-gray-300 divide-y divide-gray-200">
          <div v-for="(item, i) in recentActivities" :key="i" class="flex justify-between p-2">
            <div>
              <p class="font-medium text-sm sm:text-base text-gray-800">{{ item.description }}</p>
              <p class="text-xs text-gray-600">{{ new Date(item.time).toLocaleString() }}</p>
            </div>
            <div class="text-sm sm:text-base text-green-900">Ksh. {{ item.amount }}</div>
          </div>
        </div>
      </section>

      <!-- Pending Approvals & System Health -->
      <section class="grid gap-4 md:grid-cols-2">
        <Card class="border border-gray-300 bg-white rounded-lg">
          <CardHeader>
            <CardTitle class="text-base font-semibold text-gray-900">Pending Approvals</CardTitle>
          </CardHeader>
          <CardContent>
            <ul class="space-y-1 text-sm text-gray-800">
              <li>Loans: <span class="font-bold">{{ pendingApprovals.loans }}</span></li>
              <li>Vouchers: <span class="font-bold">{{ pendingApprovals.vouchers }}</span></li>
              <li>Members: <span class="font-bold">{{ pendingApprovals.member_applications }}</span></li>
            </ul>
          </CardContent>
        </Card>

        <Card class="border border-gray-300 bg-white rounded-lg">
          <CardHeader>
            <CardTitle class="text-base font-semibold text-gray-900">System Health</CardTitle>
          </CardHeader>
          <CardContent>
            <ul class="space-y-1 text-sm text-gray-800">
              <li>Status: <span class="font-semibold text-green-700">{{ systemHealth.database_status }}</span></li>
              <li>Last Backup: <span>{{ new Date(systemHealth.last_backup).toLocaleString() }}</span></li>
              <li>Active Users: <span class="font-semibold">{{ systemHealth.active_users }}</span></li>
              <li>Errors: <span class="font-semibold text-red-600">{{ systemHealth.system_errors }}</span></li>
            </ul>
          </CardContent>
        </Card>
      </section>
    </div>
  </AppLayout>
</template>
