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

    <div class="p-6 min-h-screen space-y-10">
      <!-- Header -->
      <header>
        <h1 class="text-lg md:text-xl font-bold tracking-tight text-gray-700">
          Admin Dashboard
        </h1>
        <p class="text-gray-600/80 text-sm md:text-base">Full system overview & monitoring</p>
      </header>

      <!-- Stats Grid -->
      <section class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <Card
          class="shadow-md hover:shadow-lg transition rounded-xl bg-gradient-to-br from-white to-blue-50 border border-blue-50">
          <CardHeader class="flex flex-row items-center justify-between pb-2">
            <CardTitle class="text-sm font-medium text-blue-700">Total Members</CardTitle>
            <Users class="h-5 w-5 text-blue-500" />
          </CardHeader>
          <CardContent>
            <div class="text-lg md:text-xl font-bold text-blue-900">{{ stats.members.total }}</div>
            <p class="text-xs text-blue-600/70">Active: {{ stats.members.active }}</p>
          </CardContent>
        </Card>

        <Card
          class="shadow-md hover:shadow-lg transition rounded-xl bg-gradient-to-br from-white to-blue-50 border border-blue-100">
          <CardHeader class="flex flex-row items-center justify-between pb-2">
            <CardTitle class="text-sm font-medium text-blue-700">Total Savings</CardTitle>
            <Banknote class="h-5 w-5 text-green-500" />
          </CardHeader>
          <CardContent>
            <div class="text-lg md:text-xl font-bold text-blue-900">{{ stats.financial.total_savings.toLocaleString() }}
            </div>
            <p class="text-xs text-blue-600/70">Shares: {{ stats.financial.total_shares }}</p>
          </CardContent>
        </Card>

        <Card
          class="shadow-md hover:shadow-lg transition rounded-xl bg-gradient-to-br from-white to-blue-50 border border-blue-100">
          <CardHeader class="flex flex-row items-center justify-between pb-2">
            <CardTitle class="text-sm font-medium text-blue-700">Active Loans</CardTitle>
            <Activity class="h-5 w-5 text-purple-500" />
          </CardHeader>
          <CardContent>
            <div class="text-lg md:text-xl font-bold text-blue-900">{{ stats.loans.active_loans }}</div>
            <p class="text-xs text-blue-600/70">Pending: {{ stats.loans.pending_applications }}</p>
          </CardContent>
        </Card>

        <Card
          class="shadow-md hover:shadow-lg transition rounded-xl bg-gradient-to-br from-white to-blue-50 border border-blue-100">
          <CardHeader class="flex flex-row items-center justify-between pb-2">
            <CardTitle class="text-sm font-medium text-blue-700">Transactions Today</CardTitle>
            <FileWarning class="h-5 w-5 text-orange-500" />
          </CardHeader>
          <CardContent>
            <div class="text-lg md:text-xl font-bold text-blue-900">{{ stats.transactions.today }}</div>
            <p class="text-xs text-blue-600/70">This Month: {{ stats.transactions.this_month }}</p>
          </CardContent>
        </Card>
      </section>

      <!-- Recent Activities -->
      <section>
        <h2 class="text-lg font-semibold mb-3 text-gray-700">Recent Activities</h2>
        <div class="bg-white rounded-xl shadow border border-blue-100 divide-y divide-blue-100">
          <div v-for="(item, i) in recentActivities" :key="i"
            class="flex justify-between p-3 hover:bg-blue-50 transition">
            <div>
              <p class="font-medium text-gray-600">{{ item.description }}</p>
              <p class="text-xs text-blue-600/70">{{ new Date(item.time).toLocaleString() }}</p>
            </div>
            <div class="font-semibold text-blue-900">Ksh. {{ item.amount }}</div>
          </div>
        </div>
      </section>

      <!-- Pending Approvals & System Health -->
      <section class="grid gap-6 md:grid-cols-2">
        <Card class="shadow-md rounded-xl border border-blue-100">
          <CardHeader>
            <CardTitle class="text-gray-700">Pending Approvals</CardTitle>
          </CardHeader>
          <CardContent>
            <ul class="space-y-2 text-sm text-blue-900">
              <li>Loans: <span class="font-semibold">{{ pendingApprovals.loans }}</span></li>
              <li>Vouchers: <span class="font-semibold">{{ pendingApprovals.vouchers }}</span></li>
              <li>Members: <span class="font-semibold">{{ pendingApprovals.member_applications }}</span></li>
            </ul>
          </CardContent>
        </Card>

        <Card class="shadow-md rounded-xl border border-blue-100">
          <CardHeader>
            <CardTitle class="text-gray-700">System Health</CardTitle>
          </CardHeader>
          <CardContent>
            <ul class="space-y-2 text-sm text-blue-900">
              <li>Status: <span class="font-semibold text-green-600">{{ systemHealth.database_status }}</span></li>
              <li>Last Backup: <span>{{ new Date(systemHealth.last_backup).toLocaleString() }}</span></li>
              <li>Active Users: <span class="font-semibold">{{ systemHealth.active_users }}</span></li>
              <li>Errors: <span class="font-semibold text-red-500">{{ systemHealth.system_errors }}</span></li>
            </ul>
          </CardContent>
        </Card>
      </section>
    </div>
  </AppLayout>
</template>